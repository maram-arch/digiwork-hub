<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/project.php';
require_once __DIR__ . '/UserController.php';

class ProjetC
{
    public function addHistorique($action, $entite, $description)
    {
        UserController::requireAdmin();
        $db = config::getConnexion();

        $sql = "INSERT INTO historique (action, entite, description)
                VALUES (:action, :entite, :description)";

        $query = $db->prepare($sql);
        $query->execute([
            'action' => $action,
            'entite' => $entite,
            'description' => $description
        ]);
    }

    public function listHistorique()
    {
        UserController::requireAdmin();
        $db = config::getConnexion();

        $sql = "SELECT * FROM historique ORDER BY date_action DESC";
        return $db->query($sql);
    }

    public function listProjets($sort = 'id-projet', $direction = 'ASC', $searchId = '')
    {
        $db = config::getConnexion();

        $allowedSorts = ['id-projet', 'titre', 'budget', 'statut', 'id-user', 'id-offre'];
        $allowedDirections = ['ASC', 'DESC'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id-projet';
        }

        $direction = strtoupper($direction);

        if (!in_array($direction, $allowedDirections)) {
            $direction = 'ASC';
        }

        if (!empty($searchId)) {
            $sql = "SELECT * FROM projet 
                    WHERE `id-projet` = :searchId
                    ORDER BY `$sort` $direction";

            $query = $db->prepare($sql);
            $query->execute(['searchId' => $searchId]);
            return $query;
        }

        $sql = "SELECT * FROM projet ORDER BY `$sort` $direction";
        return $db->query($sql);
    }

    public function listProjetsLimited($limit = 3)
    {
        $db = config::getConnexion();

        $sql = "SELECT * FROM projet ORDER BY `id-projet` DESC LIMIT :limit";
        $query = $db->prepare($sql);
        $query->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $query->execute();

        return $query;
    }

    public function listSponsors($sort = 'id_user', $direction = 'ASC', $searchIdUser = '')
    {
        $db = config::getConnexion();

        $allowedSorts = ['id_user', 'nom', 'type'];
        $allowedDirections = ['ASC', 'DESC'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id_user';
        }

        $direction = strtoupper($direction);

        if (!in_array($direction, $allowedDirections)) {
            $direction = 'ASC';
        }

        if (!empty($searchIdUser)) {
            $sql = "SELECT * FROM sponsor 
                    WHERE id_user = :searchIdUser
                    ORDER BY `$sort` $direction";

            $query = $db->prepare($sql);
            $query->execute(['searchIdUser' => $searchIdUser]);
            return $query;
        }

        $sql = "SELECT * FROM sponsor ORDER BY `$sort` $direction";
        return $db->query($sql);
    }

    public function getProjectStatsByStatus()
    {
        $db = config::getConnexion();

        $sql = "SELECT statut, COUNT(*) AS total
                FROM projet
                GROUP BY statut";

        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMostSponsoredStats()
    {
        $db = config::getConnexion();

        $sql = "SELECT nom, COUNT(*) AS total
                FROM sponsor
                GROUP BY nom
                ORDER BY total DESC";

        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjetById($id)
    {
        $sql = "SELECT * FROM projet WHERE `id-projet` = :id";
        $db = config::getConnexion();

        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function addProjet($projet)
    {
        UserController::requireAdmin();
        $sql = "INSERT INTO projet (`titre`, `discription`, `budget`, `statut`, `id-user`, `id-offre`)
                VALUES (:titre, :discription, :budget, :statut, :id_user, :id_offre)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $projet->getTitre(),
                'discription' => $projet->getDiscription(),
                'budget' => $projet->getBudget(),
                'statut' => $projet->getStatut(),
                'id_user' => $projet->getIdUser(),
                'id_offre' => $projet->getIdOffre()
            ]);

            $this->addHistorique(
                "Ajout",
                "Projet",
                "Le projet '" . $projet->getTitre() . "' a été ajouté."
            );

        } catch (Exception $e) {
            die('Erreur addProjet : ' . $e->getMessage());
        }
    }

    public function updateProjet($projet, $id)
    {
        UserController::requireAdmin();
        $sql = "UPDATE projet SET
                    `titre` = :titre,
                    `discription` = :discription,
                    `budget` = :budget,
                    `statut` = :statut,
                    `id-user` = :id_user,
                    `id-offre` = :id_offre
                WHERE `id-projet` = :id";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'titre' => $projet->getTitre(),
                'discription' => $projet->getDiscription(),
                'budget' => $projet->getBudget(),
                'statut' => $projet->getStatut(),
                'id_user' => $projet->getIdUser(),
                'id_offre' => $projet->getIdOffre()
            ]);

            $this->addHistorique(
                "Modification",
                "Projet",
                "Le projet '" . $projet->getTitre() . "' a été modifié."
            );

        } catch (Exception $e) {
            die('Erreur updateProjet : ' . $e->getMessage());
        }
    }

    public function deleteProjet($id)
    {
        UserController::requireAdmin();
        $sql = "DELETE FROM projet WHERE `id-projet` = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);

            $this->addHistorique(
                "Suppression",
                "Projet",
                "Le projet avec ID " . $id . " a été supprimé."
            );

        } catch (Exception $e) {
            die('Erreur deleteProjet : ' . $e->getMessage());
        }
    }

    /**
     * Génère et envoie un PDF listant tous les projets et sponsors.
     * Migré depuis exportProjectsSponsorsPDF.php (fichier racine supprimé).
     * Requiert le rôle admin.
     */
    public function exportProjectsSponsorsPDF(): void
    {
        UserController::requireAdmin();

        $fpdfPath = __DIR__ . '/../lib/fpdf/fpdf.php';
        if (!file_exists($fpdfPath)) {
            http_response_code(500);
            echo 'Erreur : lib/fpdf/fpdf.php introuvable.';
            return;
        }
        require_once $fpdfPath;

        $projects = $this->listProjets()->fetchAll(PDO::FETCH_ASSOC);
        $sponsors = $this->listSponsors()->fetchAll(PDO::FETCH_ASSOC);

        $pdf = new FPDF();
        $pdf->AddPage();

        // Titre
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'DigiWorkHub', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, 'Liste des Projets et Sponsorships', 0, 1, 'C');
        $pdf->Ln(5);

        // Section Projets
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(0, 10, 'Liste des Projets', 0, 1);

        $pdf->SetFillColor(30, 70, 140);
        $pdf->SetTextColor(255);
        $pdf->SetFont('Arial', 'B', 10);
        $headers = ['ID', 'Titre', 'Budget', 'Statut'];
        $widths  = [15, 80, 30, 40];
        foreach ($headers as $i => $col) {
            $pdf->Cell($widths[$i], 8, $col, 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0);
        foreach ($projects as $p) {
            $pdf->Cell(15, 8, (string) ($p['id-projet'] ?? ''), 1);
            $pdf->Cell(80, 8, substr((string) ($p['titre'] ?? ''), 0, 30), 1);
            $pdf->Cell(30, 8, (string) ($p['budget'] ?? ''), 1);
            $pdf->Cell(40, 8, (string) ($p['statut'] ?? ''), 1);
            $pdf->Ln();
        }
        $pdf->Ln(8);

        // Section Sponsors
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(0, 10, 'Liste des Sponsorships', 0, 1);

        $pdf->SetFillColor(30, 70, 140);
        $pdf->SetTextColor(255);
        $pdf->SetFont('Arial', 'B', 10);
        $headers2 = ['ID User', 'Nom', 'Type'];
        $widths2  = [40, 60, 80];
        foreach ($headers2 as $i => $col) {
            $pdf->Cell($widths2[$i], 8, $col, 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0);
        foreach ($sponsors as $s) {
            $pdf->Cell(40, 8, (string) ($s['id_user'] ?? ''), 1);
            $pdf->Cell(60, 8, substr((string) ($s['nom'] ?? ''), 0, 25), 1);
            $pdf->Cell(80, 8, substr((string) ($s['type'] ?? ''), 0, 35), 1);
            $pdf->Ln();
        }

        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Generated by DigiWorkHub - ' . date('Y-m-d H:i'), 0, 0, 'C');

        $pdf->Output();
    }
}
?>