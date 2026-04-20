<?php

require_once "model/Forum.php";

class ForumController
{
    private $forum;

    public function __construct($db)
    {
        $this->forum = new Forum($db);
    }

    public function index()
    {
        $forums = $this->forum->getAll();
        require "view/frontoffice/listPublication.php";
    }

    public function add()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->forum->add($_POST['titre'], $_POST['contenu'], 1);
            header("Location: index.php?action=list");
        }
        require "view/frontoffice/addPublication.php";
    }

    public function delete()
    {
        $this->forum->delete($_GET['id']);
        header("Location: index.php?action=list");
    }

    public function edit()
    {
        $forum = $this->forum->getById($_GET['id']);

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->forum->update($_GET['id'], $_POST['titre'], $_POST['contenu']);
            header("Location: index.php?action=list");
        }

        require "view/frontoffice/editPublication.php";
    }
}