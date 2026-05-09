<?php
// Preserve id_event param if present
$idEvent = isset($_GET['id_event']) ? '&id_event=' . (int)$_GET['id_event'] : '';
header('Location: /projectttttttt/index.php?page=inscription' . $idEvent);
exit;
