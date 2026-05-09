<?php
$params = [];
foreach (['titre', 'type', 'date_limite', 'tri', 'ordre'] as $key) {
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        $params[$key] = $_GET[$key];
    }
}

$query = http_build_query($params);
header('Location: index.php' . ($query ? '?' . $query : ''));
exit;
?>
