<?php
require_once __DIR__  . '/../../controller/OffreController.php';
require_once __DIR__  . '/../../model/Offre.php';
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !empty($_POST["titre"])         &&
        !empty($_POST["description"])   &&
        !empty($_POST["competences"])   &&
        !empty($_POST["date_limite"])   &&
        !empty($_POST["adresse"])       &&
        !empty($_POST["type"])          &&
        !empty($_POST["id_entreprise"])
    ) {
        // Instanciation du modèle (id = null car AUTO_INCREMENT)
        $offre = new Offre(
            null,
            htmlspecialchars(trim($_POST["titre"])),
            htmlspecialchars(trim($_POST["description"])),
            htmlspecialchars(trim($_POST["competences"])),
            $_POST["date_limite"],
            htmlspecialchars(trim($_POST["adresse"])),
            htmlspecialchars(trim($_POST["type"])),
            intval($_POST["id_entreprise"])
        );
 
        $controller = new OffreController();
        $controller->addOffre($offre);
 
        header("Location: index.php?status=success&msg=" . urlencode("Offre ajoutée avec succès !"));
        exit;
    } else {
        header("Location: index.php?status=error&msg=" . urlencode("Tous les champs sont obligatoires."));
        exit;
    }
}
 
header("Location: index.php");
exit;
?>
 