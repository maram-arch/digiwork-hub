<?php
// Lightweight proxy/controller for Pack operations when the PHP built-in server serves from /view
// This avoids issues with require paths when including the real controller from a different cwd.
session_start();

require_once __DIR__ . '/../../model/Pack.php';

$pack = new Pack();

// Return JSON list if requested
if (isset($_GET['action']) && $_GET['action'] === 'getAll') {
	header('Content-Type: application/json');
	$packs = $pack->getAll()->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($packs);
	exit;
}

// Handle POST form submissions (Add / Update / Delete via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	// Delete Pack (form submit)
	if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
		$pack->delete(intval($_POST['id']));
		// If AJAX, return JSON
		if (isset($_POST['ajax'])) {
			header('Content-Type: application/json');
			echo json_encode(['status' => 'success', 'message' => 'Pack supprimé avec succès']);
			exit;
		}

		$_SESSION['flash'] = 'Pack supprimé avec succès';
		header('Location: /back/dashboard_packs.php');
		exit;
	}

	// Add Pack
	if ($_POST['action'] === 'add') {
		$ok = $pack->add(
			$_POST['nom'] ?? '',
			floatval($_POST['prix'] ?? 0),
			$_POST['duree'] ?? '',
			$_POST['description'] ?? '',
			intval($_POST['nb'] ?? 0),
			$_POST['support'] ?? ''
		);
		if (isset($_POST['ajax'])) {
			header('Content-Type: application/json');
			if ($ok) {
				echo json_encode(['status' => 'success', 'message' => 'Pack ajouté avec succès']);
			} else {
				echo json_encode(['status' => 'error', 'message' => 'Échec : données invalides ou trop longues. Vérifiez le nom et la description.']);
			}
			exit;
		}

		if ($ok) {
			$_SESSION['flash'] = 'Pack ajouté avec succès';
		} else {
			$_SESSION['flash'] = 'Erreur lors de l\'ajout du pack (taille/format des champs)';
		}
		header('Location: /back/dashboard_packs.php');
		exit;
	}

	// Update Pack
	if ($_POST['action'] === 'update') {
		$ok = $pack->update(
			$_POST['id-pack'] ?? 0,
			$_POST['nom'] ?? '',
			floatval($_POST['prix'] ?? 0),
			$_POST['duree'] ?? '',
			$_POST['description'] ?? '',
			intval($_POST['nb'] ?? 0),
			$_POST['support'] ?? ''
		);
		if (isset($_POST['ajax'])) {
			header('Content-Type: application/json');
			if ($ok) {
				echo json_encode(['status' => 'success', 'message' => 'Pack modifié avec succès']);
			} else {
				echo json_encode(['status' => 'error', 'message' => 'Échec lors de la modification (taille/format des champs)']);
			}
			exit;
		}

		if ($ok) {
			$_SESSION['flash'] = 'Pack modifié avec succès';
		} else {
			$_SESSION['flash'] = 'Erreur lors de la modification du pack (taille/format des champs)';
		}
		header('Location: /back/dashboard_packs.php');
		exit;
	}
}

// Support GET-based delete (admin link like PackController.php?delete=1&id=3)
if (isset($_GET['delete']) && isset($_GET['id'])) {
	$id = intval($_GET['id']);
	$pack->delete($id);
	$_SESSION['flash'] = 'Pack supprimé avec succès';
	header('Location: /back/dashboard_packs.php');
	exit;
}
