<?php
require_once "conexionBBDD.php";
require_once "conexionRSS.php";

header('Content-Type: text/html; charset=UTF-8');

// Si es POST (filtrar), procesa y muestra tabla
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filtrar'])) {
    $periodicosMin = strtolower($_POST['periodicos'] ?? 'elmundo');
    $where = "WHERE 1=1";
    $params = [];

    if (!empty($_POST['categoria'])) {
        $where .= " AND categoria ILIKE ?";
        $params[] = '%' . $_POST['categoria'] . '%';
    }
    if (!empty($_POST['fecha'])) {
        $where .= " AND DATE(fPubli) = ?";
        $params[] = $_POST['fecha'];
    }
    if (!empty($_POST['buscar'])) {
        $where .= " AND descripcion ILIKE ?";
        $params[] = '%' . $_POST['buscar'] . '%';
    }

    $sql = "SELECT * FROM $periodicosMin $where ORDER BY fPubli DESC LIMIT 50";
    $stmt = $link->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Por defecto: últimas 50 de elmundo
    $stmt = $link->prepare("SELECT * FROM elmundo ORDER BY fPubli DESC LIMIT 50");
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lector RSS</title>
    <style>
        /* Estilo general */
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f4fb;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Contenedor principal del formulario */
        form {
            background: white;
            border: 2px solid #d79cfb;
            border-radius: 10px;
            padding: 20px 30px;
            margin: 25px 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 700px;
        }

        fieldset {
            border: none;
        }

        legend {
            font-size: 1.4em;
            font-weight: bold;
            color: #a43df0;
        }

        /* Etiquetas y selects */
        label {
            display: inline-block;
            width: 100px;
            font-weight: 600;
            margin-right: 10px;
            color: #5f0707;
        }

        select,
        input[type="date"],
        input[type="text"] {
            padding: 8px;
            margin: 5px 0 10px 0;
            border: 1px solid #be7f7f;
            border-radius: 6px;
            width: calc(100% - 120px);
            max-width: 300px;
            transition: 0.2s ease;
        }

        select:focus,
        input:focus {
            border-color: #b65df7;
            outline: none;
        }

        /* Botón del filtro */
        input[type="submit"] {
            background: linear-gradient(135deg, #b65df7, #9531f2);
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background: linear-gradient(135deg, #9531f2, #b65df7);
            transform: translateY(-1px);
        }

        /* Tabla de resultados */
        table {
            border-collapse: collapse;
            width: 90%;
            margin-bottom: 40px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        th {
            background-color: #a43df0;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        tr:nth-child(even) {
            background-color: #faf5ff;
        }

        tr:hover {
            background-color: #f0e5fb;
        }

        a {
            color: #9531f2;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <form action="/api/index.php" method="POST">
        <fieldset>
            <legend>FILTRO</legend><label>PERIODICO: </label><select name="periodicos">
                <option value="elpais" <?= ($_POST['periodicos'] ?? 'elmundo') == 'elpais' ? 'selected' : '' ?>>El Pais</option>
                <option value="elmundo" <?= ($_POST['periodicos'] ?? 'elmundo') == 'elmundo' ? 'selected' : '' ?>>El Mundo</option>
            </select><label>CATEGORÍA: </label><select name="categoria">
                <option value="">Todas</option>
                <option value="Política" <?= ($_POST['categoria'] ?? '') == 'Política' ? 'selected' : '' ?>>Política</option>
                <option value="Deportes" <?= ($_POST['categoria'] ?? '') == 'Deportes' ? 'selected' : '' ?>>Deportes</option>
                <option value="España" <?= ($_POST['categoria'] ?? '') == 'España' ? 'selected' : '' ?>>España</option>
                <option value="Economía" <?= ($_POST['categoria'] ?? '') == 'Economía' ? 'selected' : '' ?>>Economía</option>
                <option value="Europa" <?= ($_POST['categoria'] ?? '') == 'Europa' ? 'selected' : '' ?>>Europa</option>
                <option value="Justicia" <?= ($_POST['categoria'] ?? '') == 'Justicia' ? 'selected' : '' ?>>Justicia</option>
            </select><label>FECHA: </label><input type="date" name="fecha" value="<?= $_POST['fecha'] ?? '' ?>"><label>BUSCAR: </label><input type="text" name="buscar" value="<?= $_POST['buscar'] ?? '' ?>" placeholder="en descripción"><input type="submit" name="filtrar" value="Filtrar">
        </fieldset>
    </form>
    <table style='border: 5px #092368ff solid; width:100%;'>
        <tr>
            <th>TITULO</th>
            <th>DESCRIPCIÓN</th>
            <th>CATEGORÍA</th>
            <th>ENLACE</th>
            <th>FECHA</th>
        </tr><?php if (empty($resultados)): ?><tr>
                <td colspan="5">No se encontraron noticias.</td>
            </tr><?php else: ?><?php foreach ($resultados as $row): ?><tr>
                <td><?= htmlspecialchars($row['titulo'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['descripcion'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['categoria'] ?? '') ?></td>
                <td><a href="<?= htmlspecialchars($row['link'] ?? '') ?>" target="_blank">Ver</a></td>
                <td><?= !empty($row['fPubli']) ? date('d-M-Y', strtotime($row['fPubli'])) : 'Sin fecha' ?></td>
            </tr><?php endforeach; ?><?php endif; ?>
    </table>
</body>

</html>