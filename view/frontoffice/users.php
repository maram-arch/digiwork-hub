<?php
require_once __DIR__ . '/../../controller/UserController.php';

$errors = [];
$message = '';
$editUser = null;
$users = [];

try {
    $controller = new UserController();

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
<html class="no-js" lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FrontOffice - CRUD Utilisateurs</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-beta1.min.css">
    <link rel="stylesheet" href="assets/css/lindy-uikit.css">
    <link rel="stylesheet" href="assets/css/frontoffice-users-crud.css">
</head>
<body class="hero-light">
<div class="crud-wrap">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">CRUD Utilisateurs - FrontOffice</h2>
        <div>
            <a class="btn btn-outline-success" href="index.php">Accueil</a>
            <a class="btn btn-success" href="../backoffice/users.php">BackOffice</a>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="badge-ok mb-3"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="badge-err mb-3"><?= htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-soft">
                <div class="card-body p-4">
                    <h5 class="mb-3"><?= $editUser ? 'Modifier votre profil' : 'Creer un compte' ?></h5>
                    <form id="frontofficeUserForm" method="post" novalidate>
                        <input type="hidden" name="action" value="<?= $editUser ? 'update' : 'create' ?>">
                        <?php if ($editUser): ?>
                            <input type="hidden" name="id_user" value="<?= (int) $editUser['id_user'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input class="form-control" type="text" name="email" id="foEmail" value="<?= htmlspecialchars($editUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="exemple@domaine.com">
                            <div class="validation-error" id="foEmailError"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mot de passe <?= $editUser ? '(laisser vide pour conserver)' : '' ?></label>
                            <input class="form-control" type="password" name="password" id="foPassword" value="" placeholder="Min 6 caracteres">
                            <div class="validation-error" id="foPasswordError"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telephone</label>
                            <input class="form-control" type="text" name="tel" id="foTel" value="<?= htmlspecialchars((string) ($editUser['tel'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="8 chiffres">
                            <div class="validation-error" id="foTelError"></div>
                        </div>

                        <button class="btn btn-success w-100" type="submit"><?= $editUser ? 'Mettre a jour' : 'Enregistrer' ?></button>
                        <?php if ($editUser): ?>
                            <a class="btn btn-secondary w-100 mt-2" href="users.php">Annuler</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-soft">
                <div class="card-body p-4 table-responsive">
                    <h5 class="mb-3">Utilisateurs inscrits</h5>
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Telephone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= (int) $user['id_user'] ?></td>
                                <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $user['tel'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="users.php?edit=<?= (int) $user['id_user'] ?>">Modifier</a>
                                    <form method="post" style="display:inline-block;" onsubmit="return confirm('Confirmer la suppression ?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id_user" value="<?= (int) $user['id_user'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="4" class="text-center">Aucun utilisateur</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/frontoffice-users-crud-validation.js"></script>
</body>
</html>
