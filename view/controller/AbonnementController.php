<?php
// Lightweight proxy for AbonnementController so requests from the docroot (/view)
// can be handled without include-path issues. This file mirrors the minimal
// API surface used by the front/back (GET action=getAll|getMine, POST action=delete|subscribe).

session_start();
require_once __DIR__ . '/../../model/Abonnement.php';
require_once __DIR__ . '/../../model/Pack.php';

$abo = new Abonnement();
$packModel = new Pack();

function redirectTo(string $path): void {
	header('Location: ' . $path);
	exit;
}

function jsonResponse(array $payload, int $status = 200): void {
	http_response_code($status);
	header('Content-Type: application/json');
	echo json_encode($payload);
	exit;
}

// GET actions
if (isset($_GET['action'])) {
	if ($_GET['action'] === 'getAll') {
		if (($_SESSION['role'] ?? '') !== 'admin') {
			jsonResponse(['status' => 'error', 'message' => 'Accès refusé'], 403);
		}
		$abo->updateExpiredStatus();
		jsonResponse($abo->getAllAbonnements());
	}

	if ($_GET['action'] === 'getMine') {
		$abo->updateExpiredStatus();
		if (!isset($_SESSION['user_id'])) {
			jsonResponse([]);
		}
		$userId = intval($_SESSION['user_id']);
		jsonResponse($abo->getByUser($userId));
	}
}

// POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	// Delete (admin)
	if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
		if (($_SESSION['role'] ?? '') !== 'admin') {
			jsonResponse(['status' => 'error', 'message' => 'Accès refusé'], 403);
		}
		$abo->delete(intval($_POST['id']));
		if (isset($_POST['ajax'])) {
			jsonResponse(['status' => 'success', 'message' => 'Abonnement supprimé']);
		}
		$_SESSION['flash'] = 'Abonnement supprimé';
		redirectTo('/back/dashboard_abonnements.php');
	}

	// Status update (admin)
	if ($_POST['action'] === 'setStatus' && isset($_POST['id']) && isset($_POST['status'])) {
		if (($_SESSION['role'] ?? '') !== 'admin') {
			jsonResponse(['status' => 'error', 'message' => 'Accès refusé'], 403);
		}
		$ok = $abo->setStatus((int)$_POST['id'], (string)$_POST['status']);
		if (!$ok) {
			jsonResponse(['status' => 'error', 'message' => 'Statut invalide'], 400);
		}
		jsonResponse(['status' => 'success', 'message' => 'Statut mis à jour']);
	}

	// Subscribe
	if ($_POST['action'] === 'subscribe') {
		$userId = $_SESSION['user_id'] ?? null;
		$packId = intval($_POST['pack_id']);
		if (!$userId) {
			if (isset($_POST['ajax'])) {
				jsonResponse(['status' => 'error', 'message' => 'Vous devez être connecté pour vous abonner.'], 401);
			}

			$_SESSION['flash'] = 'Vous devez être connecté pour vous abonner.';
			redirectTo('/front/login.php');
		}

		$pack = $packModel->getById($packId);
		if (!$pack) {
			if (isset($_POST['ajax'])) {
				jsonResponse(['status' => 'error', 'message' => 'Pack introuvable.'], 400);
			}
			$_SESSION['flash'] = 'Pack introuvable.';
			redirectTo('/front/packs.php');
		}

		$abId = $abo->subscribe($userId, $packId);
		if ($abId === false) {
			if (isset($_POST['ajax'])) {
				jsonResponse(['status' => 'error', 'message' => 'Abonnement déjà actif ou création impossible.'], 400);
			}
			$_SESSION['flash'] = 'Abonnement déjà actif ou création impossible.';
			redirectTo('/front/abonnement.php');
		}

		if (isset($_POST['ajax'])) {
			jsonResponse(['status' => 'success', 'message' => 'Abonnement créé', 'abonnement_id' => $abId]);
		}

		$_SESSION['flash'] = 'Abonnement créé avec succès';
		redirectTo('/front/abonnement.php');
	}
}
