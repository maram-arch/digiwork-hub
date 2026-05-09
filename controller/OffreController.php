<?php
require_once __DIR__ . '/../config/config.php';

class OffreController
{
    private function tableExists(PDO $db, string $table): bool
    {
        try {
            $stmt = $db->prepare('SHOW TABLES LIKE :table_name');
            $stmt->execute(['table_name' => $table]);
            return (bool)$stmt->fetchColumn();
        } catch (Exception $e) {
            return false;
        }
    }

    private function tableColumns(PDO $db, string $table): array
    {
        try {
            $stmt = $db->query('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`');
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
        } catch (Exception $e) {
            return [];
        }
    }

    private function schema(PDO $db): array
    {
        $table = $this->tableExists($db, 'offre') ? 'offre' : 'offer';
        $columns = $this->tableColumns($db, $table);

        return [
            'table' => $table,
            'id' => in_array('id_offer', $columns, true) ? 'id_offer' : 'id-offer',
            'description' => in_array('description', $columns, true) ? 'description' : 'discription',
            'competences' => in_array('competences', $columns, true) ? 'competences' : 'competence',
            'date_limite' => in_array('date_limite', $columns, true) ? 'date_limite' : 'date-limiter',
            'id_entreprise' => in_array('id_entreprise', $columns, true) ? 'id_entreprise' : 'id-enter',
        ];
    }

    private function col(string $column): string
    {
        return '`' . str_replace('`', '``', $column) . '`';
    }

    private function selectSql(PDO $db, string $where = '', string $order = 'ORDER BY id_offer DESC'): string
    {
        $schema = $this->schema($db);
        $table = $this->col($schema['table']);
        $id = $this->col($schema['id']);
        $description = $this->col($schema['description']);
        $competences = $this->col($schema['competences']);
        $dateLimite = $this->col($schema['date_limite']);
        $idEntreprise = $this->col($schema['id_entreprise']);

        return "SELECT
                    $id AS id_offer,
                    titre,
                    $description AS description,
                    $competences AS competences,
                    $dateLimite AS date_limite,
                    adresse,
                    type,
                    $idEntreprise AS id_entreprise
                FROM $table $where $order";
    }

    public function addOffre($offre)
    {
        $db = Config::getConnexion();
        $schema = $this->schema($db);
        $table = $this->col($schema['table']);
        $description = $this->col($schema['description']);
        $competences = $this->col($schema['competences']);
        $dateLimite = $this->col($schema['date_limite']);
        $idEntreprise = $this->col($schema['id_entreprise']);

        $sql = "INSERT INTO $table
                (titre, $description, $competences, $dateLimite, adresse, type, $idEntreprise)
                VALUES (:titre, :description, :competences, :date_limite, :adresse, :type, :id_entreprise)";

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre'         => $offre->getTitre(),
                'description'   => $offre->getDescription(),
                'competences'   => $offre->getCompetences(),
                'date_limite'   => $offre->getDateLimite(),
                'adresse'       => $offre->getAdresse(),
                'type'          => $offre->getType(),
                'id_entreprise' => $offre->getIdEntreprise(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function listOffre()
    {
        $db = Config::getConnexion();
        try {
            return $db->query($this->selectSql($db));
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function searchOffre($titre = '', $competences = '', $adresse = '')
    {
        $db = Config::getConnexion();
        $schema = $this->schema($db);
        $competencesColumn = $this->col($schema['competences']);
        $where = "WHERE (:titre = '' OR titre LIKE :titre_like)
                 AND (:competences = '' OR $competencesColumn LIKE :competences_like)
                 AND (:adresse = '' OR adresse LIKE :adresse_like)";

        try {
            $query = $db->prepare($this->selectSql($db, $where));
            $query->execute([
                'titre'            => $titre,
                'titre_like'       => '%' . $titre . '%',
                'competences'      => $competences,
                'competences_like' => '%' . $competences . '%',
                'adresse'          => $adresse,
                'adresse_like'     => '%' . $adresse . '%',
            ]);
            return $query;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getOffre($id)
    {
        $db = Config::getConnexion();
        $schema = $this->schema($db);
        $idColumn = $this->col($schema['id']);
        $where = "WHERE $idColumn = :id";

        try {
            $query = $db->prepare($this->selectSql($db, $where, ''));
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function updateOffre($offre, $id)
    {
        $db = Config::getConnexion();
        $schema = $this->schema($db);
        $table = $this->col($schema['table']);
        $id = $this->col($schema['id']);
        $description = $this->col($schema['description']);
        $competences = $this->col($schema['competences']);
        $dateLimite = $this->col($schema['date_limite']);
        $idEntreprise = $this->col($schema['id_entreprise']);
        $sql = "UPDATE $table SET
                    titre = :titre,
                    $description = :description,
                    $competences = :competences,
                    $dateLimite = :date_limite,
                    adresse = :adresse,
                    type = :type,
                    $idEntreprise = :id_entreprise
               WHERE $id = :id";

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id'            => $id,
                'titre'         => $offre->getTitre(),
                'description'   => $offre->getDescription(),
                'competences'   => $offre->getCompetences(),
                'date_limite'   => $offre->getDateLimite(),
                'adresse'       => $offre->getAdresse(),
                'type'          => $offre->getType(),
                'id_entreprise' => $offre->getIdEntreprise(),
            ]);
        } catch (PDOException $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function deleteOffre($id)
    {
        $db = Config::getConnexion();
        $schema = $this->schema($db);
        $table = $this->col($schema['table']);
        $id = $this->col($schema['id']);
        $sql = "DELETE FROM $table WHERE $id = :id";

        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
