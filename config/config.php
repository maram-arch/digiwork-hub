<?php



class Config{
private static $pdo;

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
}

// Config::getConnexion();