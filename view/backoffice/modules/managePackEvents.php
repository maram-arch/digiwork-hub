<?php
// Redirect to the unified backoffice template
if (!defined('BACKOFFICE_LAYOUT_LOADED')) {
    header('Location: /projectttttttt/view/backoffice/index.php?page=pack_events');
    exit;
}
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /projectttttttt/view/backoffice/login.php');
    exit();
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../model/PackEvent.php';
require_once __DIR__ . '/../../../model/Pack.php';
require_once __DIR__ . '/../../../model/Event.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Relations Pack-Événement - DigiWork Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
        }
        .status-actif { background-color: #d4edda; color: #155724; }
        .status-inactif { background-color: #f8d7da; color: #721c24; }
        .status-en_attente { background-color: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar p-4">
                <h4 class="text-white mb-4">
                    <i class="fas fa-cube"></i> DigiWork Hub
                </h4>
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action text-white bg-transparent">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                    <a href="manageEvents.php" class="list-group-item list-group-item-action text-white bg-transparent">
                        <i class="fas fa-calendar"></i> Événements
                    </a>
                    <a href="dashboard_packs.php" class="list-group-item list-group-item-action text-white bg-transparent">
                        <i class="fas fa-box"></i> Packs
                    </a>
                    <a href="managePackEvents.php" class="list-group-item list-group-item-action text-white bg-info">
                        <i class="fas fa-link"></i> Relations Pack-Événement
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="fas fa-link"></i> Gestion des Relations Pack-Événement
                    </h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRelationModal">
                        <i class="fas fa-plus"></i> Ajouter une relation
                    </button>
                </div>

                <!-- Relations List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="relationsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pack</th>
                                        <th>Événement</th>
                                        <th>Statut</th>
                                        <th>Date création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $relations = PackEvent::getAll();
                                    foreach ($relations as $relation):
                                    ?>
                                    <tr>
                                        <td><?= $relation['id_pack_event'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($relation['nom_pack'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($relation['event_titre'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $relation['statut'] ?>">
                                                <?= ucfirst($relation['statut']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($relation['date_creation'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning edit-relation" 
                                                    data-id="<?= $relation['id_pack_event'] ?>"
                                                    data-pack="<?= $relation['id_pack'] ?>"
                                                    data-event="<?= $relation['id_event'] ?>"
                                                    data-statut="<?= $relation['statut'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-relation" 
                                                    data-id="<?= $relation['id_pack_event'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Relation Modal -->
    <div class="modal fade" id="addRelationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une relation Pack-Événement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRelationForm">
                        <div class="mb-3">
                            <label for="packSelect" class="form-label">Pack</label>
                            <select class="form-select" id="packSelect" required>
                                <option value="">Sélectionner un pack</option>
                                <?php
                                try {
                                    $pdo = config::getConnexion();
                                    $stmt = $pdo->query("SELECT * FROM pack ORDER BY nom_pack");
                                    while ($pack = $stmt->fetch()) {
                                        echo "<option value='{$pack['id_pack']}'>" . htmlspecialchars($pack['nom_pack']) . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option>Erreur de chargement</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="eventSelect" class="form-label">Événement</label>
                            <select class="form-select" id="eventSelect" required>
                                <option value="">Sélectionner un événement</option>
                                <?php
                                try {
                                    $pdo = config::getConnexion();
                                    $stmt = $pdo->query("SELECT * FROM evente ORDER BY titre");
                                    while ($event = $stmt->fetch()) {
                                        echo "<option value='{$event['id_event']}'>" . htmlspecialchars($event['titre']) . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option>Erreur de chargement</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="statutSelect" class="form-label">Statut</label>
                            <select class="form-select" id="statutSelect">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="en_attente">En attente</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="saveRelation">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Relation Modal -->
    <div class="modal fade" id="editRelationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier la relation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editRelationForm">
                        <input type="hidden" id="editRelationId">
                        <div class="mb-3">
                            <label for="editPackSelect" class="form-label">Pack</label>
                            <select class="form-select" id="editPackSelect" required>
                                <option value="">Sélectionner un pack</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editEventSelect" class="form-label">Événement</label>
                            <select class="form-select" id="editEventSelect" required>
                                <option value="">Sélectionner un événement</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editStatutSelect" class="form-label">Statut</label>
                            <select class="form-select" id="editStatutSelect">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="en_attente">En attente</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="updateRelation">Mettre à jour</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Ajouter une relation
        document.getElementById('saveRelation').addEventListener('click', function() {
            const id_pack = document.getElementById('packSelect').value;
            const id_event = document.getElementById('eventSelect').value;
            const statut = document.getElementById('statutSelect').value;

            if (!id_pack || !id_event) {
                alert('Veuillez sélectionner un pack et un événement');
                return;
            }

            fetch('../../controller/PackEventController.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_pack: parseInt(id_pack),
                    id_event: parseInt(id_event),
                    statut: statut
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Relation ajoutée avec succès!');
                    location.reload();
                } else {
                    alert('Erreur: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de l\'ajout');
            });
        });

        // Modifier une relation
        document.querySelectorAll('.edit-relation').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const pack = this.dataset.pack;
                const event = this.dataset.event;
                const statut = this.dataset.statut;

                document.getElementById('editRelationId').value = id;
                document.getElementById('editPackSelect').value = pack;
                document.getElementById('editEventSelect').value = event;
                document.getElementById('editStatutSelect').value = statut;

                new bootstrap.Modal(document.getElementById('editRelationModal')).show();
            });
        });

        // Mettre à jour une relation
        document.getElementById('updateRelation').addEventListener('click', function() {
            const id = document.getElementById('editRelationId').value;
            const id_pack = document.getElementById('editPackSelect').value;
            const id_event = document.getElementById('editEventSelect').value;
            const statut = document.getElementById('editStatutSelect').value;

            fetch(`../../controller/PackEventController.php?action=update&id=${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_pack: parseInt(id_pack),
                    id_event: parseInt(id_event),
                    statut: statut
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Relation mise à jour avec succès!');
                    location.reload();
                } else {
                    alert('Erreur: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la mise à jour');
            });
        });

        // Supprimer une relation
        document.querySelectorAll('.delete-relation').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Êtes-vous sûr de vouloir supprimer cette relation?')) {
                    const id = this.dataset.id;
                    
                    fetch(`../../controller/PackEventController.php?action=delete&id=${id}`, {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Relation supprimée avec succès!');
                            location.reload();
                        } else {
                            alert('Erreur: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erreur lors de la suppression');
                    });
                }
            });
        });
    </script>
</body>
</html>
