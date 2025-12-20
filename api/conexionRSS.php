<?php
require_once 'conexionBBDD.php';  // Carga $link global

function guardarRSS($tabla, $titulo, $link, $descripcion, $categoria, $fPubli, $contenido) {
    global $link; 
    
    $stmt = $link->prepare("
        INSERT INTO $tabla (titulo, link, descripcion, categoria, fPubli, contenido) 
        VALUES (?, ?, ?, ?, ?, ?) 
        ON CONFLICT DO NOTHING
    ");
    
    $stmt->execute([
        $titulo, $link, $descripcion, $categoria, $fPubli, $contenido
    ]);
}
?>
