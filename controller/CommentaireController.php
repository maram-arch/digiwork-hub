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

    // ==============================================================
    // CRUD DE BASE
    // ==============================================================

    
    public function addCommentaire(Commentaire $c): bool
    {
        
        $contenuNettoye = $this->sanitizeComment($c->getContenu());
        $c->setContenu($contenuNettoye);
        return $c->addCommentaire();
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
    public function toggleLikeCommentaireAjax() {
    header('Content-Type: application/json');
    if (!isset($_SESSION['id_user'])) {
        echo json_encode(['success' => false]);
        exit;
    }
    $id_commentaire = (int)($_POST['id_commentaire'] ?? 0);
    $id_user = $_SESSION['id_user'];
    if (!$id_commentaire) {
        echo json_encode(['success' => false]);
        exit;
    }
    $result = Commentaire::toggleLikeCommentaire($id_commentaire, $id_user);
    echo json_encode(['success' => true, 'action' => $result['action'], 'nb_likes' => $result['nb_likes']]);
    exit;
}
    // ==============================================================
    // ★ MÉTHODES AVEC JOINTURE
    // ==============================================================

    public function getAllCommentaires(): array
    {
        $model = new Commentaire();
        return $model->getAllCommentairesWithUserAndPublication();
    }

    public function getCommentairesByPublication(int $id_publication): array
    {
        $model = new Commentaire();
        return $model->getCommentairesByPublicationWithUser($id_publication);
    }

    public function getCommentaireById(int $id_commentaire): ?array
    {
        $model = new Commentaire($id_commentaire);
        return $model->getCommentaireByIdWithUser($id_commentaire);
    }

    public function getCommentairesByUser(int $id_user): array
    {
        $model = new Commentaire();
        return $model->getCommentairesByUserWithPublication($id_user);
    }

    public function getNbCommentairesParPublication(): array
    {
        $model = new Commentaire();
        return $model->getNbCommentairesParPublication();
    }



    public function likeCommentaireAjax() {
    header('Content-Type: application/json');
    if (!isset($_SESSION['id_user'])) { echo json_encode(['success' => false]); exit; }
    $id_commentaire = (int)($_POST['id_commentaire'] ?? 0);
    if (!$id_commentaire) { echo json_encode(['success' => false]); exit; }
    $result = Commentaire::toggleLikeCommentaire($id_commentaire, $_SESSION['id_user']);
    echo json_encode(['success' => true, 'action' => $result['action'], 'nb_likes' => $result['nb_likes']]);
    exit;
}

public function addReponseAjax() {
    header('Content-Type: application/json');
    if (!isset($_SESSION['id_user'])) { echo json_encode(['success' => false]); exit; }
    $id_publication = (int)($_POST['id_publication'] ?? 0);
    $parent_id = (int)($_POST['parent_id'] ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');
    if (!$id_publication || !$parent_id || strlen($contenu) < 3) { echo json_encode(['success' => false]); exit; }
    $ok = Commentaire::addReponse(htmlspecialchars($contenu), $id_publication, $_SESSION['id_user'], $parent_id);
    echo json_encode(['success' => $ok]);
    exit;
}
}
?>