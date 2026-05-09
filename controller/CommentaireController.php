<?php

require_once __DIR__ . '/../model/Commentaire.php';
require_once __DIR__ . '/../config/Config.php';

class CommentaireController
{
    private $badWords = [
        'merde', 'con', 'idiot', 'stupide', 'pute', 'nique',
        'fuck', 'shit', 'bitch', 'asshole', 'damn', 'connard',
        'salope', 'enculé', 'batard', 'zob', 'bite'
    ];

    private function filterBadWords($text) {
        foreach ($this->badWords as $word) {
            $replacement = str_repeat('*', strlen($word));
            $text = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', $replacement, $text);
        }
        return $text;
    }

    private function sanitizeComment($text) {
        $text = $this->filterBadWords($text);
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        return $text;
    }

    public function addCommentaire(Commentaire $c): bool
    {
        $contenuNettoye = $this->sanitizeComment($c->getContenu());
        $c->setContenu($contenuNettoye);
        $result = $c->addCommentaire();
        if ($result) {
            require_once __DIR__ . '/../model/WhatsAppNotifier.php';
            $notifier = new WhatsAppNotifier();
            $notifier->notifyOwner($c->getId_publication(), $c->getId_users(), 'comment');
        }
        return $result;
    }

    public function deleteCommentaire(int $id_commentaire): bool
    {
        $model = new Commentaire($id_commentaire);
        return $model->deleteCommentaire($id_commentaire);
    }

    public function updateCommentaire(int $id_commentaire, string $contenu): bool
    {
        $contenu = $this->sanitizeComment($contenu);
        if (strlen($contenu) < 2) return false;
        $model = new Commentaire($id_commentaire);
        return $model->updateCommentaire($id_commentaire, $contenu);
    }

    public function likeCommentaireAjax() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['id_users'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté']);
            exit;
        }
        $id_commentaire = filter_input(INPUT_POST, 'id_commentaire', FILTER_VALIDATE_INT);
        if (!$id_commentaire) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            exit;
        }
        $id_users = $_SESSION['id_users'];
        $pdo = Config::getConnexion();
        // Correction : colonne id_user (au lieu de id_users)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire_likes WHERE id_commentaire = ? AND id_user = ?");
        $stmt->execute([$id_commentaire, $id_users]);
        $liked = $stmt->fetchColumn() > 0;
        if ($liked) {
            $pdo->prepare("DELETE FROM commentaire_likes WHERE id_commentaire = ? AND id_user = ?")->execute([$id_commentaire, $id_users]);
            $action = 'unliked';
        } else {
            $pdo->prepare("INSERT INTO commentaire_likes (id_commentaire, id_user) VALUES (?, ?)")->execute([$id_commentaire, $id_users]);
            $action = 'liked';
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire_likes WHERE id_commentaire = ?");
        $stmt->execute([$id_commentaire]);
        $nb_likes = (int)$stmt->fetchColumn();
        echo json_encode(['success' => true, 'action' => $action, 'nb_likes' => $nb_likes]);
        exit;
    }

    public function addReponseAjax() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['id_users'])) { 
            echo json_encode(['success' => false]); 
            exit; 
        }
        $id_publication = (int)($_POST['id_publication'] ?? 0);
        $parent_id = (int)($_POST['parent_id'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');
        if (!$id_publication || !$parent_id || strlen($contenu) < 3) { 
            echo json_encode(['success' => false]); 
            exit; 
        }
        $ok = Commentaire::addReponse(htmlspecialchars($contenu), $id_publication, $_SESSION['id_users'], $parent_id);
        if ($ok) {
            require_once __DIR__ . '/../model/WhatsAppNotifier.php';
            $notifier = new WhatsAppNotifier();
            $notifier->notifyOwner($id_publication, $_SESSION['id_users'], 'comment');
        }
        echo json_encode(['success' => $ok]);
        exit;
    }

    public function getAllCommentaires(): array
    {
        $model = new Commentaire();
        return $model->getAllCommentairesWithUsersAndPublication();
    }

    public function getCommentairesByPublication(int $id_publication): array
    {
        $model = new Commentaire();
        return $model->getCommentairesByPublicationWithUsers($id_publication);
    }

    public function getCommentaireById(int $id_commentaire): ?array
    {
        $model = new Commentaire($id_commentaire);
        return $model->getCommentaireByIdWithUsers($id_commentaire);
    }

    public function getCommentairesByUser(int $id_users): array
    {
        $model = new Commentaire();
        return $model->getCommentairesByUsersWithPublication($id_users);
    }

    public function getNbCommentairesParPublication(): array
    {
        $model = new Commentaire();
        return $model->getNbCommentairesParPublication();
    }
}
?>