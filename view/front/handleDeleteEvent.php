<?php
require_once __DIR__ . '/../../controller/EventController.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $eventController = new EventController();
    $eventController->deleteEvent($id);
    
    // Redirect back to event list after deletion
    header('Location: event.php?message=deleted');
    exit();
} else {
    // If no ID provided, redirect back to event list
    header('Location: event.php');
    exit();
}
?>
