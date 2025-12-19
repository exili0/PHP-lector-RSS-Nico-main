<?php require_once "conexionBBDD.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lector RSS</title>
    <style>
        th, td { border: 1px #E4CCE8 solid; padding: 5px; text-align: left; }
        table { border-collapse: collapse; width: 100%; }
    </style>
</head>
<body>
    <form action="index.php" method="GET">
        <fieldset>
            <legend>FILTRO</legend>
            <label>PERIODICO: </label>
            <select name="periodicos">
                <option value="elpais">El Pais</option>
                <option value="elmundo" selected>El Mundo</option>
            </select>
            
            <label>CATEGORIA: </label>
            <select name="categoria">
                <option value="">Todas</option>
                <option value="Política">Política</option>
                <option value="España">España</option>
                <option value="Europa">Europa</option>
            </select>
            
            <label>FECHA: </label>
            <input type="date" name="fecha">
            <label>DESCRIPCIÓN: </label>
            <input type="text" name="buscar" placeholder="palabra clave">
            <input type="submit" name="filtrar" value="Filtrar">
        </fieldset>
    </form>

    <?php
    require_once "RSSElPais.php";
    require_once "RSSElMundo.php";

    function filtros($sql, $link) {
        $stmt = $link->query($sql);
        if (!$stmt) {
            echo "<tr><td colspan='6'>Error: " . $link->errorInfo()[2] . "</td></tr>";
            return;
        }
        if ($stmt->rowCount() == 0) {
            echo "<tr><td colspan='6'>No se encontraron noticias.</td></tr>";
            return;
        }
        while ($arrayFiltro = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($arrayFiltro['titulo']) . "</td>";
            echo "<td>" . htmlspecialchars($arrayFiltro['descripcion']) . "</td>";
            echo "<td>" . htmlspecialchars($arrayFiltro['categoria']) . "</td>";
            echo "<td><a href='" . htmlspecialchars($arrayFiltro['link']) . "'>Ver</a></td>";
            $fecha = date_create($arrayFiltro['fPubli']);
            echo "<td>" . date_format($fecha, 'd-M-Y') . "</td>";
            echo "</tr>";
        }
    }

    if (!$link) {
        die("Error de conexión PostgreSQL");
    }

    echo "<table style='border: 5px #ca75d9ff solid;'>";
    echo "<tr><th>TITULO</th><th>DESCRIPCIÓN</th><th>CATEGORÍA</th><th>ENLACE</th><th>FECHA</th></tr>";

    if (isset($_REQUEST['filtrar'])) {
        $periodicos = strtolower($_REQUEST['periodicos']);
        $cat = $_REQUEST['categoria'];
        $f = $_REQUEST['fecha'];
        $palabra = $_REQUEST['buscar'];

        if ($cat && $f && $palabra) {
            $sql = "SELECT * FROM $periodicos WHERE descripcion ILIKE '%$palabra%' AND categoria ILIKE '%$cat%' AND fPubli='$f'";
        } elseif ($cat && $f) {
            $sql = "SELECT * FROM $periodicos WHERE categoria ILIKE '%$cat%' AND fPubli='$f'";
        } elseif ($palabra) {
            $sql = "SELECT * FROM $periodicos WHERE descripcion ILIKE '%$palabra%'";
        } elseif ($cat) {
            $sql = "SELECT * FROM $periodicos WHERE categoria ILIKE '%$cat%'";
        } elseif ($f) {
            $sql = "SELECT * FROM $periodicos WHERE fPubli='$f'";
        } else {
            $sql = "SELECT * FROM $periodicos ORDER BY fPubli DESC";
        }
        filtros($sql, $link);
    } else {
        $sql = "SELECT * FROM elmundo ORDER BY fPubli DESC LIMIT 50";
        filtros($sql, $link);
    }
    echo "</table>";
    
    ?>
</body>
</html>
