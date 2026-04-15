<?php
require_once __DIR__ . '/../../controller/EventController.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $eventController = new EventController();
    $eventController->deleteEvent($id);
    
    // Redirect back to back manage page after deletion
    header('Location: manageEvents.php?message=deleted');
    exit();
} else {
    // If no ID provided, redirect back to back manage page
    header('Location: manageEvents.php');
    exit();
}
?>
