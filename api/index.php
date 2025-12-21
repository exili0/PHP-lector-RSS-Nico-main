<?php require_once "conexionBBDD.php"; ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lector RSS</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <form action="index.php" method="POST">
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
        die("Error: Variable \$link no encontrada. Revisa conexionBBDD.php");
    }

    function filtros($sql, $link, $params = [])
    {
        $stmt = $link->prepare($sql);
        $stmt->execute($params);  // Usa $params si existen

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
            $fPubli = $arrayFiltro['fPubli'] ?? '0000-00-00';
            $fecha = date_create($fPubli ?: '1970-01-01');
            echo "<td>" . ($fPubli ? date_format($fecha, 'd-M-Y') : 'Sin fecha') . "</td>";
            echo "</tr>";
        }
    }

    echo "<table style='border: 5px #ca75d9ff solid;'>";
    echo "<tr><th>TITULO</th><th>DESCRIPCIÓN</th><th>CATEGORÍA</th><th>ENLACE</th><th>FECHA</th></tr>";

    if (isset($_POST['filtrar'])) {
        $periodicos_permitidos = ['elmundo', 'elpais'];
        $periodicos = strtolower($_POST['periodicos']);
        if (!in_array($periodicos, $periodicos_permitidos)) {
            $periodicos = 'elmundo';  // Default seguro
        }

        $cat = $_POST['categoria'] ?? '';
        $f = $_POST['fecha'] ?? '';
        $palabra = $_POST['buscar'] ?? '';

        $sql_base = "SELECT * FROM $periodicos WHERE 1=1";
        $params = [];

        if ($cat) {
            $sql_base .= " AND categoria ILIKE :cat";
            $params[':cat'] = "%$cat%";
        }
        if ($f) {
            $sql_base .= " AND DATE(fPubli) = :fecha";
            $params[':fecha'] = $f;
        }
        if ($palabra) {
            $sql_base .= " AND descripcion ILIKE :palabra";
            $params[':palabra'] = "%$palabra%";
        }

        $sql_base .= " ORDER BY fPubli DESC LIMIT 50";  // LIMIT para evitar timeouts
        filtros($sql_base, $link, $params);
    } else {
        $sql = "SELECT * FROM elmundo ORDER BY fPubli DESC LIMIT 50";
        filtros($sql, $link, []);
    }

    echo "</table>";
    ?>
</body>

</html>