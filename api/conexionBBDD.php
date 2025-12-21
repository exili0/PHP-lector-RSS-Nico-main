<?php
// Detecta entorno automáticamente
if (getenv('DATABASE_URL')) {
    // Vercel + Neon PostgreSQL
    $dbUrl = getenv('DATABASE_URL');
    $url = parse_url($dbUrl);
    $host = $url['host'];
    $db = ltrim($url['path'], '/');
    $user = $url['user'];
    $pass = $url['pass'];
    $port = $url['port'] ?? 5432;
    
    $link = new PDO("pgsql:host=$host;port=$port;dbname=$db;sslmode=require", 
                   $user, $pass, 
                   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} else {
    // Local MySQL (como amigo)
    $host = "localhost"; $user = "root"; $password = "";
    $link = mysqli_connect($host, $user, $password, 'periodicos');
    mysqli_query($link, "SET NAMES 'utf8'");
}
?>
