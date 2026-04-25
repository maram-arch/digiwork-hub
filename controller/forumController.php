<?php

require_once "model/Forum.php";

class ForumController
{
    private $forum;

    public function __construct($db)
    {
        $this->forum = new Forum($db);
    }

    // 🔵 LISTE PUBLICATIONS
    public function index()
    {
        $forums = $this->forum->getAll();
        require "view/frontoffice/listPublication.php";
    }

    // ➕ AJOUT
    public function add()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $titre = trim($_POST['titre'] ?? '');
            $contenu = trim($_POST['contenu'] ?? '');

            if (empty($titre) || empty($contenu)) {
                die("❌ Tous les champs sont obligatoires");
            }

            $this->forum->add($titre, $contenu, 1);

            header("Location: index.php?action=front");
            exit;
        }

        require "view/frontoffice/addPublication.php";
    }

    // ❌ DELETE
    public function delete()
    {
        if (!isset($_GET['id'])) {
            die("ID manquant");
        }

        $this->forum->delete($_GET['id']);

        header("Location: index.php?action=front");
        exit;
    }

    // ✏️ EDIT
    public function edit()
    {
        if (!isset($_GET['id'])) {
            die("ID manquant");
        }

        $forum = $this->forum->getById($_GET['id']);

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $titre = trim($_POST['titre']);
            $contenu = trim($_POST['contenu']);

            if (empty($titre) || empty($contenu)) {
                die("❌ Champs obligatoires");
            }

            $this->forum->update($_GET['id'], $titre, $contenu);

            header("Location: index.php?action=front");
            exit;
        }

        require "view/frontoffice/editPublication.php";
    }
}