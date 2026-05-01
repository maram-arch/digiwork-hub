<?php
$params = [];

foreach (['date', 'statut', 'tri', 'ordre'] as $key) {
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        $params[$key] = $_GET[$key];
    }
}

$query = http_build_query($params);
header('Location: mes_candidatures.php' . ($query ? '?' . $query : ''));
exit;
?>
