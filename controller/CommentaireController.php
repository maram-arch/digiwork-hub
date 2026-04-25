<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/CommentaireModel.php';

class CommentaireController {

    private $badWords = [
        'badword1','badword2','idiot','stupid','merde','connard','putain',
        'salaud','imbécile','con','conne','abruti','débile','crétin',
    ];

    // ─────────────────────────────────────────────
    // FILTRE MOTS INTERDITS
    // ─────────────────────────────────────────────
    private function filterBadWords($text) {
        foreach ($this->badWords as $word) {
            $text = preg_replace(
                '/\b' . preg_quote($word, '/') . '\b/iu',
                '****',
                $text
            );
        }
        return $text;
    }

    // ─────────────────────────────────────────────
    // CREATE COMMENTAIRE
    // ─────────────────────────────────────────────
    public function addCommentaire($commentaire) {

        $db = getConnection();

        $query = $db->prepare("
            INSERT INTO commentaires (contenu, id_publication, date_creation)
            VALUES (:contenu, :id_publication, NOW())
        ");

        $query->execute([
            'contenu'        => $this->filterBadWords($commentaire->getContenu()),
            'id_publication' => $commentaire->getIdPublication(),
        ]);
    }

    // ─────────────────────────────────────────────
    // LIST COMMENTAIRES
    // ─────────────────────────────────────────────
    public function listCommentaires($id_publication) {

        $db = getConnection();

        $query = $db->prepare("
            SELECT *
            FROM commentaires
            WHERE id_publication = :id
            ORDER BY date_creation DESC
        ");

        $query->execute(['id' => $id_publication]);

        return $query->fetchAll();
    }

    // ─────────────────────────────────────────────
    // GET BY ID
    // ─────────────────────────────────────────────
    public function getCommentaireById($id) {

        $db = getConnection();

        $stmt = $db->prepare("
            SELECT *
            FROM commentaires
            WHERE id_commentaire = ?
        ");

        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    // ─────────────────────────────────────────────
    // DELETE COMMENTAIRE
    // ─────────────────────────────────────────────
    public function deleteCommentaire($id) {

        $db = getConnection();

        $query = $db->prepare("
            DELETE FROM commentaires
            WHERE id_commentaire = :id
        ");

        $query->execute(['id' => $id]);
    }

    // ─────────────────────────────────────────────
    // UPDATE COMMENTAIRE
    // ─────────────────────────────────────────────
    public function updateCommentaire($id, $contenu) {

        $db = getConnection();

        $query = $db->prepare("
            UPDATE commentaires
            SET contenu = :contenu,
                date_modification = NOW()
            WHERE id_commentaire = :id
        ");

        $query->execute([
            'contenu' => $this->filterBadWords($contenu),
            'id'      => $id,
        ]);
    }

    // ─────────────────────────────────────────────
    // EDIT ACTION
    // ─────────────────────────────────────────────
    public function editAction() {

        $id     = (int)($_GET['id'] ?? 0);
        $id_pub = (int)($_GET['pub'] ?? 0);

        if (!$id) {
            die("ID manquant");
        }

        $commentaire = $this->getCommentaireById($id);

        if (!$commentaire) {
            die("Commentaire introuvable");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $contenu = trim($_POST['contenu'] ?? '');

            if (strlen($contenu) < 3 || strlen($contenu) > 500) {
                $_SESSION['edit_error'] = "Le commentaire doit contenir entre 3 et 500 caractères.";
                header("Location: index.php?action=editComment&id=$id&pub=$id_pub");
                exit;
            }

            $this->updateCommentaire($id, $contenu);

            header("Location: index.php?action=detail&id=" . ($id_pub ?: $commentaire['id_publication']));
            exit;
        }

        require __DIR__ . '/../view/frontoffice/editCommentaire.php';
    }

    // ─────────────────────────────────────────────
    // LIKE COMMENTAIRE
    // ─────────────────────────────────────────────
    public function toggleLike($id_commentaire, $id_user) {

        $db = getConnection();

        $stmt = $db->prepare("
            SELECT id
            FROM commentaire_likes
            WHERE id_commentaire = ? AND id_user = ?
        ");

        $stmt->execute([$id_commentaire, $id_user]);

        if ($stmt->fetch()) {

            $db->prepare("
                DELETE FROM commentaire_likes
                WHERE id_commentaire = ? AND id_user = ?
            ")->execute([$id_commentaire, $id_user]);

            $db->prepare("
                UPDATE commentaires
                SET nb_likes = GREATEST(nb_likes - 1, 0)
                WHERE id_commentaire = ?
            ")->execute([$id_commentaire]);

            $action = 'unliked';

        } else {

            $db->prepare("
                INSERT INTO commentaire_likes (id_commentaire, id_user)
                VALUES (?, ?)
            ")->execute([$id_commentaire, $id_user]);

            $db->prepare("
                UPDATE commentaires
                SET nb_likes = nb_likes + 1
                WHERE id_commentaire = ?
            ")->execute([$id_commentaire]);

            $action = 'liked';
        }

        $nb = $db->prepare("
            SELECT nb_likes
            FROM commentaires
            WHERE id_commentaire = ?
        ");

        $nb->execute([$id_commentaire]);

        $row = $nb->fetch();

        return [
            'action'    => $action,
            'nb_likes'  => (int)($row['nb_likes'] ?? 0)
        ];
    }

    // ─────────────────────────────────────────────
    // BACKOFFICE LIST
    // ─────────────────────────────────────────────
    public function listBackoffice() {

        $db = getConnection();

        $stmt = $db->query("
            SELECT c.*, f.titre AS pub_titre
            FROM commentaires c
            LEFT JOIN publications f ON c.id_publication = f.id_publication
            ORDER BY c.date_creation DESC
        ");

        $commentaires = $stmt->fetchAll();

        require __DIR__ . '/../view/backoffice/listCommentaires.php';
    }
}