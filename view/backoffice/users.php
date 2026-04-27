<?php
session_start();

require_once __DIR__ . '/../../controller/UserController.php';

$loggedInUser = null;
if (isset($_SESSION['user_id'])) {
    try {
        $loggedInUser = (new UserController())->findUser((int) $_SESSION['user_id']);
    } catch (Throwable $e) {
        $loggedInUser = null;
    }
}

// Vérifier si l'utilisateur est connecté et est admin
if (!$loggedInUser || ($loggedInUser['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// No front-only gate: allow admins to log in from front or backoffice.

try {
    (new UserController())->touchUserSession((int) $loggedInUser['id_user']);
} catch (Throwable $e) {
}

if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    try {
        (new UserController())->markUserOffline((int) $loggedInUser['id_user']);
    } catch (Throwable $e) {
    }
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

$errors = [];
$message = '';
$editUser = null;
$users = [];

try {
    $controller = new UserController();
    $allowedRoles = $controller->getAllowedRoles();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $result = $controller->createUser($_POST);
        } elseif ($action === 'update') {
            $id = (int) ($_POST['id_user'] ?? 0);
            $result = $controller->updateUser($id, $_POST);
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id_user'] ?? 0);
            $result = $controller->deleteUser($id);
        } else {
            $result = ['success' => false, 'errors' => ['Action non supportee.']];
        }

        if (!empty($result['success'])) {
            header('Location: users.php?msg=' . urlencode($result['message']));
            exit;
        }

        $errors = $result['errors'] ?? ['Une erreur est survenue.'];
    }

    if (isset($_GET['msg'])) {
        $message = (string) $_GET['msg'];
    }

    if (isset($_GET['edit'])) {
        $editUser = $controller->findUser((int) $_GET['edit']);
    }

    $users = $controller->listUsers();
} catch (Throwable $e) {
    $errors[] = 'Base de donnees indisponible. Demarrez MySQL dans XAMPP puis rechargez la page.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BackOffice - CRUD Utilisateurs</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/backoffice-users-crud.css">
    <style>
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: #f5f5f5;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 10px;
        }

        .admin-badge {
            display: inline-block;
            background: #00A651;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div id="app">
    <?php $activePage = 'users'; require __DIR__ . '/layouts/sidebar.php'; ?>

    <div id="main">
        <nav class="navbar navbar-header navbar-expand navbar-light">
            <a class="sidebar-toggler" href="#"><span class="navbar-toggler-icon"></span></a>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav d-flex align-items-center navbar-light ml-auto">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <div class="avatar mr-1">
                                <img src="assets/images/avatar/avatar-s-1.png" alt="Admin DigiWork Hub">
                            </div>
                            <div class="d-none d-md-block d-lg-inline-block"><?= htmlspecialchars($loggedInUser['email'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></div>
                            <span class="admin-badge ml-2">Connecte</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="index.php"><i data-feather="home"></i> Dashboard</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="?logout=1"><i data-feather="log-out"></i> Deconnexion</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="main-content container-fluid">
            <div class="page-title">
                <h3>CRUD Utilisateurs - BackOffice</h3>
            </div>

            <?php if ($message !== ''): ?>
                <div class="success-box mb-3"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-box mb-3"><?= htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="row">
        <div class="col-lg-4">
            <div class="card crud-card">
                <div class="card-header">
                    <h5 class="mb-0"><?= $editUser ? 'Modifier utilisateur' : 'Ajouter utilisateur' ?></h5>
                </div>
                <div class="card-body">
                    <form id="backofficeUserForm" method="post" novalidate>
                        <input type="hidden" name="action" value="<?= $editUser ? 'update' : 'create' ?>">
                        <?php if ($editUser): ?>
                            <input type="hidden" name="id_user" value="<?= (int) $editUser['id_user'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input class="form-control" type="text" name="email" id="boEmail" value="<?= htmlspecialchars($editUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="exemple@domaine.com">
                            <div class="validation-error" id="boEmailError"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mot de passe <?= $editUser ? '(laisser vide pour conserver)' : '' ?></label>
                            <input class="form-control" type="password" name="password" id="boPassword" value="" placeholder="Min 6 caracteres">
                            <div class="validation-error" id="boPasswordError"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-control" name="role" id="boRole">
                                <?php foreach ($allowedRoles as $role): ?>
                                    <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>" <?= (($editUser['role'] ?? 'condidat') === $role) ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="validation-error" id="boRoleError"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telephone</label>
                            <input class="form-control" type="text" name="tel" id="boTel" value="<?= htmlspecialchars((string) ($editUser['tel'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="8 chiffres">
                            <div class="validation-error" id="boTelError"></div>
                        </div>

                        <button class="btn btn-success w-100" type="submit"><?= $editUser ? 'Mettre a jour' : 'Ajouter' ?></button>
                        <?php if ($editUser): ?>
                            <a class="btn btn-secondary w-100 mt-2" href="users.php">Annuler</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card crud-card">
                <div class="card-header">
                    <h5 class="mb-0">Liste des utilisateurs</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Telephone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= (int) $user['id_user'] ?></td>
                                <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($user['role'] ?? 'condidat', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $user['tel'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="users.php?edit=<?= (int) $user['id_user'] ?>">Modifier</a>
                                    <form method="post" style="display:inline-block;" onsubmit="return confirm('Confirmer la suppression ?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id_user" value="<?= (int) $user['id_user'] ?>">
                                        <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="5" class="text-center">Aucun utilisateur</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
</div>

<script src="assets/js/feather-icons/feather.min.js"></script>
<script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/backoffice-users-crud-validation.js"></script>
</body>
</html>
