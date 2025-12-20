<?php
//CAMBIO PARA MIGRAR A POSTGRE
// Vercel inyecta POSTGRES_URL. Parseamos la URL para extraer los datos.
$dbUrl = getenv('DATABASE_URL');

if (!$dbUrl) {
    die("No se encontró la configuración de la base de datos.");
}

$url = parse_url($dbUrl);

$url = parse_url($dbUrl);
$host = $url['host'];
$db = ltrim($url['path'], '/');
$user = $url['user'];
$pass = $url['pass'];

$port = $url['port'] ?: 5432; //Puerto por defecto

$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

try {
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // $link tiene que ser global, para que index.php lo vea
    $link = $pdo;

    //$pdo->exec("SET NAMES 'UTF8'");
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
