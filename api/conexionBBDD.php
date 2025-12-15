<?php

$Repit = false;

// Leer las variables de entorno de Vercel
$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USER');
$password = getenv('DB_PASS');
$database = getenv('DB_NAME');

if (!$host || !$user || !$database) {
    die("Faltan variables de entorno de la base de datos");
}

// Conexión MySQL remota
$link = mysqli_connect($host, $user, $password, $database, (int)$port);

if (!$link) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Charset
$link->query("SET NAMES 'utf8'");
