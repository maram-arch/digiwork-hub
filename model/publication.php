<?php
// model/Publication.php

require_once __DIR__ . '/../config/config.php';

class Publication
{
    public static function getAllWithFilters($categorie, $tri, $page, $perPage)
    {
        $pdo = Config::getConnexion();
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT f.*, u.nom, u.prenom
                FROM forums f
                LEFT JOIN user u ON f.id_user = u.id_user";

        $params = [];
        if ($categorie !== 'all') {
            $sql .= " WHERE f.categorie = :categorie";
            $params[':categorie'] = $categorie;
        }

        switch ($tri) {
            case 'likes':
                $sql .= " ORDER BY f.nb_likes DESC, f.date_publication DESC";
                break;
            case 'vues':
                $sql .= " ORDER BY f.nb_vues DESC, f.date_publication DESC";
                break;
            default:
                $sql .= " ORDER BY f.date_publication DESC";
        }

        $sql .= " LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countWithFilters($categorie)
    {
        $pdo = Config::getConnexion();
        if ($categorie !== 'all') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM forums WHERE categorie = :categorie");
            $stmt->execute([':categorie' => $categorie]);
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM forums");
        }
        return (int) $stmt->fetchColumn();
    }

    public static function toggleLike($id_publication, $id_users)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM publication_likes WHERE id_publication = :id_pub AND id_user = :id_user");
        $stmt->execute([':id_pub' => $id_publication, ':id_user' => $id_users]);
        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            $stmt = $pdo->prepare("DELETE FROM publication_likes WHERE id_publication = :id_pub AND id_user = :id_user");
            $stmt->execute([':id_pub' => $id_publication, ':id_user' => $id_users]);
            $pdo->prepare("UPDATE forums SET nb_likes = nb_likes - 1 WHERE id_publication = :id_pub")->execute([':id_pub' => $id_publication]);
            $action = 'unliked';
        } else {
            $stmt = $pdo->prepare("INSERT INTO publication_likes (id_publication, id_user) VALUES (:id_pub, :id_user)");
            $stmt->execute([':id_pub' => $id_publication, ':id_user' => $id_users]);
            $pdo->prepare("UPDATE forums SET nb_likes = nb_likes + 1 WHERE id_publication = :id_pub")->execute([':id_pub' => $id_publication]);
            $action = 'liked';
        }

        $stmt = $pdo->prepare("SELECT nb_likes FROM forums WHERE id_publication = :id_pub");
        $stmt->execute([':id_pub' => $id_publication]);
        $nb_likes = (int) $stmt->fetchColumn();
        return ['action' => $action, 'nb_likes' => $nb_likes];
    }

    public static function hasLiked($id_publication, $id_users)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM publication_likes WHERE id_publication = :id_pub AND id_user = :id_user");
        $stmt->execute([':id_pub' => $id_publication, ':id_user' => $id_users]);
        return $stmt->fetchColumn() > 0;
    }

    public static function isFavori($id_publication, $id_users)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE id_publication = :id_pub AND id_user = :id_user");
        $stmt->execute([':id_pub' => $id_publication, ':id_user' => $id_users]);
        return $stmt->fetchColumn() > 0;
    }

    public static function addFavori($id_publication, $id_users)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("INSERT IGNORE INTO favoris (id_publication, id_user) VALUES (:id_pub, :id_user)");
        $stmt->execute([':id_pub' => $id_publication, ':id_user' => $id_users]);
    }

    public static function removeFavori($id_publication, $id_users)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM favoris WHERE id_publication = :id_pub AND id_user = :id_user");
        $stmt->execute([':id_pub' => $id_publication, ':id_user' => $id_users]);
    }

    public static function getByIdWithUser($id)
    {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT f.*, u.nom, u.prenom FROM forums f LEFT JOIN user u ON f.id_user = u.id_user WHERE f.id_publication = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function incrementVues($id_publication, $id_users = null)
    {
        $pdo = Config::getConnexion();
        $pdo->prepare("UPDATE forums SET nb_vues = nb_vues + 1 WHERE id_publication = :id")->execute([':id' => $id_publication]);
    }

    public static function getTopPublications($limit = 5)
    {
        $pdo = Config::getConnexion();
        $sql = "SELECT f.*, u.nom, u.prenom,
                       (SELECT COUNT(*) FROM commentaire WHERE id_publication = f.id_publication) as nb_commentaires,
                       (f.nb_likes + f.nb_vues) as score
                FROM forums f
                LEFT JOIN user u ON f.id_user = u.id_user
                ORDER BY score DESC
                LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTrending($limit = 5, $jours = 7)
    {
        $pdo = Config::getConnexion();
        $sql = "SELECT f.*, u.nom, u.prenom,
                       (SELECT COUNT(*) FROM commentaire WHERE id_publication = f.id_publication) as nb_commentaires,
                       (f.nb_likes + f.nb_vues) as score
                FROM forums f
                LEFT JOIN user u ON f.id_user = u.id_user
                WHERE f.date_publication >= DATE_SUB(NOW(), INTERVAL :jours DAY)
                ORDER BY score DESC
                LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':jours', $jours, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTopUsers($limit = 5)
    {
        $pdo = Config::getConnexion();
        $sql = "SELECT u.id_user, u.nom, u.prenom,
                       COUNT(DISTINCT f.id_publication) as nb_publications,
                       COUNT(DISTINCT c.id_commentaire) as nb_commentaires,
                       COALESCE(SUM(f.nb_likes), 0) as total_likes_recus,
                       (COUNT(DISTINCT f.id_publication) * 2 + COUNT(DISTINCT c.id_commentaire) + COALESCE(SUM(f.nb_likes), 0)) as score
                FROM user u
                LEFT JOIN forums f ON u.id_user = f.id_user
                LEFT JOIN commentaire c ON u.id_user = c.id_user
                GROUP BY u.id_user
                ORDER BY score DESC
                LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEngagementByCategory()
    {
        $pdo = Config::getConnexion();
        $sql = "SELECT categorie,
                       COUNT(*) as nb_pubs,
                       SUM(nb_likes) as total_likes
                FROM forums
                GROUP BY categorie
                ORDER BY nb_pubs DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getActivityTimeline($jours = 7)
    {
        $pdo = Config::getConnexion();
        $sqlPubs = "SELECT DATE(date_publication) as jour, COUNT(*) as nb
                    FROM forums
                    WHERE date_publication >= DATE_SUB(CURDATE(), INTERVAL :jours DAY)
                    GROUP BY DATE(date_publication)";
        $stmt = $pdo->prepare($sqlPubs);
        $stmt->bindValue(':jours', $jours, PDO::PARAM_INT);
        $stmt->execute();
        $publications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sqlComs = "SELECT DATE(date_commentaire) as jour, COUNT(*) as nb
                    FROM commentaire
                    WHERE date_commentaire >= DATE_SUB(CURDATE(), INTERVAL :jours DAY)
                    GROUP BY DATE(date_commentaire)";
        $stmt = $pdo->prepare($sqlComs);
        $stmt->bindValue(':jours', $jours, PDO::PARAM_INT);
        $stmt->execute();
        $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'publications' => $publications,
            'commentaires' => $commentaires
        ];
    }
}
?>