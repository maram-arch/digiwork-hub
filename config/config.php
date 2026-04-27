<?php



class Config{
private static $pdo;
private static bool $schemaEnsured = false;

public static function getConnexion(): PDO{
if(!isset(self::$pdo)){
$servername="localhost";
$port=3306;
$username="root";
$password="";
$dbname="digiwork-hub";
try{
self::$pdo=new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4",
$username,
$password,
[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
);
self::ensurePasswordColumn(self::$pdo);
self::ensureOnlineColumns(self::$pdo);
// echo "Database connected successfully";
}
catch(PDOException $e){
    throw new RuntimeException('Connexion base de donnees impossible: ' . $e->getMessage(), 0, $e);
}

}
if(!(self::$pdo instanceof PDO)){
    throw new RuntimeException('Connexion base de donnees indisponible.');
}
return self::$pdo;


}

private static function ensurePasswordColumn(PDO $pdo): void{
if(self::$schemaEnsured){
    return;
}

self::$schemaEnsured = true;

try{
    $stmt = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'mdp'");
    $column = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    $type = strtolower((string) ($column['Type'] ?? ''));

    if($type !== '' && !str_contains($type, 'varchar(255)')){
        $pdo->exec("ALTER TABLE `user` MODIFY mdp VARCHAR(255) NOT NULL");
    }
}catch(Throwable $e){
    // Ignore schema upgrade failures and continue with the existing database.
}
}

private static function ensureOnlineColumns(PDO $pdo): void
{
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'is_online'");
        $onlineColumn = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        if (!$onlineColumn) {
            $pdo->exec("ALTER TABLE `user` ADD COLUMN is_online TINYINT(1) NOT NULL DEFAULT 0 AFTER tel");
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'last_activity'");
        $activityColumn = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        if (!$activityColumn) {
            $pdo->exec("ALTER TABLE `user` ADD COLUMN last_activity DATETIME NULL DEFAULT NULL AFTER is_online");
        }
    } catch (Throwable $e) {
        // Ignore schema upgrade failures and continue with the existing database.
    }
}
}

// Config::getConnexion();