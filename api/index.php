<?php require_once "conexionBBDD.php"; ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lector RSS</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <form action="index.php">
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
    if (!isset($link)) {
        die("Error: Variable \$link no encontrada.");
    }

    // NUEVA función filtros compatible PostgreSQL
    function filtros($sql, $link, $params = [])
    {
        try {
            $stmt = $link->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() == 0) {
                echo "<tr><td colspan='5'>No se encontraron noticias.</td></tr>";
                return;
            }

            while ($arrayFiltro = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($arrayFiltro['titulo']) . "</td>";
                echo "<td>" . htmlspecialchars($arrayFiltro['descripcion']) . "</td>";
                echo "<td>" . htmlspecialchars($arrayFiltro['categoria']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($arrayFiltro['link']) . "' target='_blank'>Ver</a></td>";
                $fPubli = $arrayFiltro['fPubli'] ?? '1970-01-01';
                echo "<td>" . date('d-M-Y', strtotime($fPubli)) . "</td>";
                echo "</tr>";
            }
        } catch (Exception $e) {
            echo "<tr><td colspan='5'>Error: " . $e->getMessage() . "</td></tr>";
        }
    }

    echo "<table style='border: 5px #ca75d9ff solid;'>";
    echo "<tr><th>TITULO</th><th>DESCRIPCIÓN</th><th>CATEGORÍA</th><th>ENLACE</th><th>FECHA</th></tr>";

    if (isset($_REQUEST['filtrar'])) {
        $periodicosMin = strtolower(str_replace(' ', '', $_REQUEST['periodicos']));
        $cat = $_REQUEST['categoria'] ?? '';
        $f = $_REQUEST['fecha'] ?? '';
        $palabra = $_REQUEST['buscar'] ?? '';

        // ✅ PostgreSQL + Prepared Statements (9 casos como amigo)
        if ($cat == "" && $f == "" && $palabra == "") {
            $sql = "SELECT * FROM $periodicosMin ORDER BY fPubli DESC LIMIT 50";
            filtros($sql, $link, []);
        } elseif ($cat != "" && $f == "" && $palabra == "") {
            $sql = "SELECT * FROM $periodicosMin WHERE categoria ILIKE :cat ORDER BY fPubli DESC LIMIT 50";
            filtros($sql, $link, [':cat' => "%$cat%"]);
        } elseif ($cat == "" && $f != "" && $palabra == "") {
            $sql = "SELECT * FROM $periodicosMin WHERE DATE(fPubli) = :fecha ORDER BY fPubli DESC LIMIT 50";
            filtros($sql, $link, [':fecha' => $f]);
        } elseif ($cat != "" && $f != "" && $palabra == "") {
            $sql = "SELECT * FROM $periodicosMin WHERE categoria ILIKE :cat AND DATE(fPubli) = :fecha ORDER BY fPubli DESC LIMIT 50";
            filtros($sql, $link, [':cat' => "%$cat%", ':fecha' => $f]);
        } elseif ($cat != "" && $f != "" && $palabra != "") {
            $sql = "SELECT * FROM $periodicosMin WHERE descripcion ILIKE :palabra AND categoria ILIKE :cat AND DATE(fPubli) = :fecha ORDER BY fPubli DESC LIMIT 50";
            filtros($sql, $link, [':palabra' => "%$palabra%", ':cat' => "%$cat%", ':fecha' => $f]);
        } elseif ($cat != "" && $f == "" && $palabra != "") {
            $sql = "SELECT * FROM $periodicosMin WHERE descripcion ILIKE :palabra AND categoria ILIKE :cat ORDER BY fPubli DESC LIMIT 50";
            filtros($sql, $link, [':palabra' => "%$palabra%", ':cat' => "%$cat%"]);
        } elseif ($cat == "" && $f != "" && $palabra != "") {
            $sql = "SELECT * FROM $periodicosMin WHERE descripcion ILIKE :palabra AND DATE(fPubli) = :fecha ORDER BY fPubli DESC LIMIT 50";
            filtros($sql, $link, [':palabra' => "%$palabra%", ':fecha' => $f]);
        } elseif ($palabra != "" && $cat == "" && $f == "") {
            $sql = "SELECT * FROM $periodicosMin WHERE descripcion ILIKE :palabra ORDER BY fPubli DESC LIMIT 50";
            filtros($sql, $link, [':palabra' => "%$palabra%"]);
        }
    } else {
        $sql = "SELECT * FROM elmundo ORDER BY fPubli DESC LIMIT 50";
        filtros($sql, $link, []);
    }

    echo "</table>";
    ?>
</body>

</html>