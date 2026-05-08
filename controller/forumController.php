<?php

require_once __DIR__ . '/../model/forum.php';
require_once __DIR__ . '/UserController.php';

class ForumController
{
    private $forum;

    public function __construct($db)
    {
        $this->forum = new Forum($db);
    }

    public function index()
    {
        UserController::requireLogin();
        $forums = $this->forum->getAll();
        require "view/frontoffice/listPublication.php";
    }

    public function add()
    {
        UserController::requireLogin();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->forum->add($_POST['titre'], $_POST['contenu'], UserController::getCurrentUserId());
            header("Location: index.php?action=forum_list");
        }
        require "view/frontoffice/addPublication.php";
    }

    public function delete()
    {
        UserController::requireAdmin();
        $this->forum->delete($_GET['id']);
        header("Location: index.php?action=forum_list");
    }

    public function edit()
    {
        UserController::requireLogin();
        $forum = $this->forum->getById($_GET['id']);

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->forum->update($_GET['id'], $_POST['titre'], $_POST['contenu']);
            header("Location: index.php?action=forum_list");
        }

        require "view/frontoffice/editPublication.php";
    }
}