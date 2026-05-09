<?php
// model/Recommendation.php
// Moteur de recommandation intelligente — embeddings + similarité cosinus

require_once __DIR__ . '/../config/config.php';

class Recommendation
{
    // ============================================================
    // 1. ENREGISTRER UNE INTERACTION UTILISATEUR
    // ============================================================
    public static function logInteraction($id_users, $id_publication, $type)
    {
        // type = 'vue' | 'like' | 'commentaire' | 'favori'
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("
            INSERT INTO users_interactions (id_user, id_publication, type_interaction)
            VALUES (:u, :p, :t)
            ON DUPLICATE KEY UPDATE created_at = NOW()
        ");
        $stmt->execute([':u' => $id_users, ':p' => $id_publication, ':t' => $type]);
    }

    // ============================================================
    // 2. GÉNÉRER L'EMBEDDING D'UN TEXTE (TF-IDF léger)
    // ============================================================
    public static function generateEmbedding($texte)
    {
        $texte = mb_strtolower(strip_tags($texte));
        $texte = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $texte);
        $mots  = preg_split('/\s+/', trim($texte), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($mots)) return null;

        $freq = array_count_values($mots);
        $total = array_sum($freq);
        $vecteur = array_fill(0, 128, 0.0);
        foreach ($freq as $mot => $count) {
            $tf = $count / $total;
            $hash1 = crc32($mot) % 128;
            $hash2 = crc32(strrev($mot)) % 128;
            $hash3 = crc32($mot . '_') % 128;
            $vecteur[abs($hash1)] += $tf;
            $vecteur[abs($hash2)] += $tf * 0.7;
            $vecteur[abs($hash3)] += $tf * 0.4;
        }
        $norme = sqrt(array_sum(array_map(fn($v) => $v * $v, $vecteur)));
        if ($norme > 0) $vecteur = array_map(fn($v) => $v / $norme, $vecteur);
        return $vecteur;
    }

    // ============================================================
    // 3. STOCKER L'EMBEDDING D'UNE PUBLICATION
    // ============================================================
    public static function storeEmbedding($id_publication, $texte)
    {
        $vecteur = self::generateEmbedding($texte);
        if (!$vecteur) return false;
        $pdo  = Config::getConnexion();
        $json = json_encode($vecteur);
        $stmt = $pdo->prepare("UPDATE forums SET embedding = :emb WHERE id_publication = :id");
        return $stmt->execute([':emb' => $json, ':id' => $id_publication]);
    }

    // ============================================================
    // 4. GÉNÉRER LES EMBEDDINGS MANQUANTS (batch)
    // ============================================================
    public static function generateMissingEmbeddings()
    {
        $pdo  = Config::getConnexion();
        $stmt = $pdo->query("SELECT id_publication, titre, contenu FROM forums WHERE embedding IS NULL LIMIT 50");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;
        foreach ($rows as $row) {
            $texte = $row['titre'] . ' ' . $row['contenu'];
            self::storeEmbedding($row['id_publication'], $texte);
            $count++;
        }
        return $count;
    }

    // ============================================================
    // 5. SIMILARITÉ COSINUS entre deux vecteurs
    // ============================================================
    public static function cosineSimilarity(array $a, array $b)
    {
        if (count($a) !== count($b)) return 0.0;
        $dot = $normA = $normB = 0.0;
        for ($i = 0; $i < count($a); $i++) {
            $dot   += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }
        if ($normA == 0 || $normB == 0) return 0.0;
        return $dot / (sqrt($normA) * sqrt($normB));
    }

    // ============================================================
    // 6. CONSTRUIRE LE PROFIL UTILISATEUR
    // ============================================================
    public static function buildUserProfile($id_users)
    {
        $pdo = Config::getConnexion();
        $poids = ['favori' => 3, 'like' => 2, 'commentaire' => 2, 'vue' => 1];

        $stmt = $pdo->prepare("
            SELECT f.embedding, ui.type_interaction
            FROM users_interactions ui
            JOIN forums f ON ui.id_publication = f.id_publication
            WHERE ui.id_user = :u
              AND f.embedding IS NOT NULL
            ORDER BY ui.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([':u' => $id_users]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) return null;

        $profil = array_fill(0, 128, 0.0);
        $totalPoids = 0;
        foreach ($rows as $row) {
            $emb = json_decode($row['embedding'], true);
            $p = $poids[$row['type_interaction']] ?? 1;
            if (!$emb || count($emb) !== 128) continue;
            for ($i = 0; $i < 128; $i++) $profil[$i] += $emb[$i] * $p;
            $totalPoids += $p;
        }
        if ($totalPoids === 0) return null;
        for ($i = 0; $i < 128; $i++) $profil[$i] /= $totalPoids;
        return $profil;
    }

    // ============================================================
    // 7. OBTENIR LES RECOMMANDATIONS POUR UN UTILISATEUR
    // ============================================================
    public static function getRecommendations($id_users, $limit = 4)
    {
        $pdo = Config::getConnexion();
        self::generateMissingEmbeddings();
        $profil = self::buildUserProfile($id_users);
        if (!$profil) {
            return self::getPopularFallback($id_users, $limit);
        }

        $stmt = $pdo->prepare("SELECT DISTINCT id_publication FROM users_interactions WHERE id_user = :u");
        $stmt->execute([':u' => $id_users]);
        $dejavu = $stmt->fetchColumn() ? array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id_publication') : [];

        $sql = "SELECT f.id_publication, f.titre, f.contenu, f.categorie,
                       f.nb_likes, f.nb_vues, f.date_publication, f.image,
                       f.is_event, f.embedding,
                       u.nom, u.prenom
                FROM forums f
                LEFT JOIN user u ON f.id_user = u.id_user
                WHERE f.embedding IS NOT NULL";
        if (!empty($dejavu)) {
            $placeholders = implode(',', array_fill(0, count($dejavu), '?'));
            $sql .= " AND f.id_publication NOT IN ($placeholders)";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute(!empty($dejavu) ? $dejavu : []);
        $toutes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($toutes)) {
            return self::getPopularFallback($id_users, $limit);
        }

        $scores = [];
        foreach ($toutes as $pub) {
            $emb = json_decode($pub['embedding'], true);
            if (!$emb || count($emb) !== 128) continue;
            $score = self::cosineSimilarity($profil, $emb);
            $pub['score'] = $score;
            $scores[] = $pub;
        }
        usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($scores, 0, $limit);
    }

    // ============================================================
    // 8. FALLBACK : Publications populaires si pas d'historique
    // ============================================================
    public static function getPopularFallback($id_users, $limit = 4)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("
            SELECT f.*, u.nom, u.prenom
            FROM forums f
            LEFT JOIN user u ON f.id_user = u.id_user
            WHERE f.id_user != :u
            ORDER BY (f.nb_likes + f.nb_vues) DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':u', $id_users, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>