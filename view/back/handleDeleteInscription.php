<?php
require_once __DIR__ . '/../../controller/InscriptionController.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: manageInscriptions.php');
    exit;
}

$id = intval($_GET['id']);
$inscriptionController = new InscriptionController();
$inscriptionController->deleteInscription($id);

header('Location: manageInscriptions.php?message=deleted');
exit;
