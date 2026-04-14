<?php
// Lightweight proxy for AbonnementController so requests from the docroot (/view)
// can be handled without include-path issues. This file mirrors the minimal
// API surface used by the front/back (GET action=getAll|getMine, POST action=delete|subscribe).

session_start();
require_once __DIR__ . '/../../model/Abonnement.php';

$abo = new Abonnement();

// GET actions
if (isset($_GET['action'])) {
	if ($_GET['action'] === 'getAll') {
		header('Content-Type: application/json');
		echo json_encode($abo->getAllAbonnements());
		exit;
	}

	if ($_GET['action'] === 'getMine') {
		header('Content-Type: application/json');
		if (!isset($_SESSION['user_id'])) {
			echo json_encode([]);
			exit;
		}
		$userId = intval($_SESSION['user_id']);
		echo json_encode($abo->getByUser($userId));
		exit;
	}
}

// POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	// Delete (admin)
	if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
		$abo->delete(intval($_POST['id']));
		if (isset($_POST['ajax'])) {
			header('Content-Type: application/json');
			echo json_encode(['status' => 'success', 'message' => 'Abonnement supprimé']);
			exit;
		}
		$_SESSION['flash'] = 'Abonnement supprimé';
		header('Location: /view/back/dashboard_abonnements.php');
		exit;
	}

	// Subscribe
	if ($_POST['action'] === 'subscribe') {
		$userId = $_SESSION['user_id'] ?? null;
		$packId = intval($_POST['pack_id']);
		if (!$userId) {
			if (isset($_POST['ajax'])) {
				header('Content-Type: application/json');
				echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté pour vous abonner.']);
				exit;
			}

			$_SESSION['flash'] = 'Vous devez être connecté pour vous abonner.';
			header('Location: /view/front/login.php');
			exit;
		}

		$abId = $abo->subscribe($userId, $packId);
		if (isset($_POST['ajax'])) {
			header('Content-Type: application/json');
			echo json_encode(['status' => 'success', 'message' => 'Abonnement créé', 'abonnement_id' => $abId]);
			exit;
		}

		$_SESSION['flash'] = 'Abonnement créé avec succès';
		header('Location: /view/front/abonnement.php');
		exit;
	}
}
