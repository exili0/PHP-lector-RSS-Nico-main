<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<pre>INICIANDO DEBUG...\n";
?>

<?php require_once "conexionBBDD.php"; ?>

<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>DEBUG RSS</title></head>
<body>
<h1>DEBUG INFO</h1>

<?php

echo "<h3>1. Conexión DB:</h3>";
if (isset($link)) {
    echo "\$link existe<br>";
    echo "Tipo: " . gettype($link) . "<br>";
    
    // Test query simple
    try {
        $test = $link->query("SELECT 1 as test")->fetch();
        echo "Query test OK: " . print_r($test, true) . "<br>";
        
        // Lista tablas
        $tables = $link->query("SELECT tablename FROM pg_tables WHERE schemaname='public'")->fetchAll();
        echo "Tablas encontradas: " . implode(', ', array_column($tables, 'tablename')) . "<br>";
    } catch (Exception $e) {
        echo " ERROR DB: " . $e->getMessage() . "<br>";
    }
} else {
    echo " \$link NO existe<br>";
}

// 2. TEST POST DATA
echo "<h3>2. Datos POST:</h3>";
echo "\$_REQUEST: " . print_r($_REQUEST, true) . "<br>";
echo "\$_POST: " . print_r($_POST, true) . "<br>";
echo "\$_GET: " . print_r($_GET, true) . "<br>";

// 3. TEST DATABASE_URL
echo "<h3>3. DATABASE_URL:</h3>";
echo "DATABASE_URL: " . (getenv('DATABASE_URL') ? '✅ EXISTE' : '❌ VACÍA') . "<br>";

echo "<hr><h2>Si llegas aquí, el problema NO es conexión DB</h2>";
?>

<!-- Tu form aquí (está perfecto) -->
<form action="index.php">
    <!-- ... tu form igual ... -->
</form>

<?php echo "</pre>"; ?>
</body></html>
