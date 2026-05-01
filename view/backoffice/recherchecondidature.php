<?php
$params = [];

foreach (['id_user', 'offre', 'statut', 'date', 'tri', 'ordre'] as $key) {
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        $params[$key] = $_GET[$key];
    }
}

$query = http_build_query($params);
header('Location: listCandidatures.php' . ($query ? '?' . $query : ''));
exit;
?>
