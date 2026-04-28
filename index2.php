<?php

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'home':
        require_once('view/front/projets.html');
        break;

    case 'listProject':
        require_once('view/back/projectList.php');
        break;

    case 'projectCRUD':
        require_once('view/back/projectCRUD.php');
        break;

    case 'projectAction':
        require_once('controller/projectController.php');
        break;

    default:
        echo "Page not found";
        break;
}
?>