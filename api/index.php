<?php require_once "conexionBBDD.php"; ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lector RSS</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- ✅ FIX: method="POST" -->
    <form action="index.php" method="POST">
        <fieldset>
            <legend>FILTRO</legend>
            <label>PERIODICO: </label>
            <select name="periodicos">
                <option value="elpais">El Pais</option>
                <option value="elmundo" selected>El Mundo</option>
            </select>
            <label>CATEGORÍA: </label>
            <select type="selector" name="categoria" value="">
                <option name=""></option>
                <option name="Política">Política</option>
                <option name="Deportes">Deportes</option>
                <option name="Ciencia">Ciencia</option>
                <option name="España">España</option>
                <option name="Economía">Economía</option>
                <option name="Música">Música</option>
                <option name="Cine">Cine</option>
                <option name="Europa">Europa</option>
                <option name="Justicia">Justicia</option>
            </select>
            <label>FECHA : </label>
            <input type="date" name="fecha" value=""></input>
            <label style="margin-left: 5vw;">AMPLIAR FILTRO (la descripción contenga la palabra) : </label>
            <input type="text" name="buscar" value=""></input>
            <input type="submit" name="filtrar" value="Filtrar">
        </fieldset>
    </form>

    <?php
    if (!isset($link)) die("Error: \$link no encontrada.");

    function filtros($sql, $link, $params = [])
    {
        try {
            $stmt = $link->prepare($sql);
            $stmt->execute($params);
            if ($stmt->rowCount() == 0) {
                echo "<tr><td colspan='5'>No se encontraron noticias.</td></tr>";
                return;
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
                echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
                echo "<td>" . htmlspecialchars($row['categoria']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($row['link']) . "' target='_blank'>Ver</a></td>";
                echo "<td>" . ($row['fPubli'] ? date('d-M-Y', strtotime($row['fPubli'])) : 'Sin fecha') . "</td>";
                echo "</tr>";
            }
        } catch (Exception $e) {
            echo "<tr><td colspan='5'>Error: " . $e->getMessage() . "</td></tr>";
        }
    }

    echo "<table style='border: 5px #ca75d9ff solid;'>";
    echo "<tr><th>TITULO</th><th>DESCRIPCIÓN</th><th>CATEGORÍA</th><th>ENLACE</th><th>FECHA</th></tr>";

    //FIX: $_POST + validación
    if (isset($_POST['filtrar']) && $_POST) {
        $periodicosMin = strtolower($_POST['periodicos'] ?? 'elmundo');
        $cat = $_POST['categoria'] ?? '';
        $f = $_POST['fecha'] ?? '';
        $palabra = $_POST['buscar'] ?? '';

        // 8 casos simplificados (PostgreSQL)
        if ($cat == "" && $f == "" && $palabra == "") {
            $sql = "SELECT * FROM $periodicosMin ORDER BY fPubli DESC LIMIT 50";
            filtros($sql, $link, []);
        } elseif ($palabra != "") {
            $sql = "SELECT * FROM $periodicosMin WHERE descripcion ILIKE :p";
            if ($cat != "") $sql .= " AND categoria ILIKE :c";
            if ($f != "") $sql .= " AND DATE(fPubli) = :f";
            $sql .= " ORDER BY fPubli DESC LIMIT 50";
            filtros($sql, $link, [':p' => "%$palabra%"] +
                ($cat ? [':c' => "%$cat%"] : []) +
                ($f ? [':f' => $f] : []));
        } elseif ($cat != "" || $f != "") {
            $sql = "SELECT * FROM $periodicosMin WHERE 1=1";
            if ($cat) $sql .= " AND categoria ILIKE :c";
            if ($f) $sql .= " AND DATE(fPubli) = :f";
            $sql .= " ORDER BY fPubli DESC LIMIT 50";
            $params = ($cat ? [':c' => "%$cat%"] : []) + ($f ? [':f' => $f] : []);
            filtros($sql, $link, $params);
        }
    } else {
        filtros("SELECT * FROM elmundo ORDER BY fPubli DESC LIMIT 50", $link, []);
    }
    echo "</table>";
    ?>
</body>

</html>