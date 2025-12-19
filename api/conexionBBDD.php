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
$port = $url['port'] ?: 5432;

$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    // Guardamos en una variable llamada $link para que sea similar a tu código previo
    $link = $pdo;
    $pdo->exec("SET NAMES 'UTF8'");
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}