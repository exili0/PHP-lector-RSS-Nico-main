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
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <form action="/api/index.php" method="POST">
        <fieldset>
            <legend>FILTRO</legend>
            <label>PERIODICO: </label>
            <select name="periodicos">
                <option value="elpais" <?= ($_POST['periodicos'] ?? 'elmundo') == 'elpais' ? 'selected' : '' ?>>El Pais</option>
                <option value="elmundo" <?= ($_POST['periodicos'] ?? 'elmundo') == 'elmundo' ? 'selected' : '' ?>>El Mundo</option>
            </select>

            <label>CATEGORÍA: </label>
            <select name="categoria">
                <option value="">Todas</option>
                <option value="Política" <?= ($_POST['categoria'] ?? '') == 'Política' ? 'selected' : '' ?>>Política</option>
                <option value="Deportes" <?= ($_POST['categoria'] ?? '') == 'Deportes' ? 'selected' : '' ?>>Deportes</option>
                <option value="España" <?= ($_POST['categoria'] ?? '') == 'España' ? 'selected' : '' ?>>España</option>
                <option value="Economía" <?= ($_POST['categoria'] ?? '') == 'Economía' ? 'selected' : '' ?>>Economía</option>
                <option value="Europa" <?= ($_POST['categoria'] ?? '') == 'Europa' ? 'selected' : '' ?>>Europa</option>
                <option value="Justicia" <?= ($_POST['categoria'] ?? '') == 'Justicia' ? 'selected' : '' ?>>Justicia</option>
            </select>

            <label>FECHA: </label>
            <input type="date" name="fecha" value="<?= $_POST['fecha'] ?? '' ?>">

            <label>BUSCAR: </label>
            <input type="text" name="buscar" value="<?= $_POST['buscar'] ?? '' ?>" placeholder="en descripción">

            <input type="submit" name="filtrar" value="Filtrar">
        </fieldset>
    </form>

    <table style='border: 5px #092368ff solid; width:100%;'>
        <tr>
            <th>TITULO</th>
            <th>DESCRIPCIÓN</th>
            <th>CATEGORÍA</th>
            <th>ENLACE</th>
            <th>FECHA</th>
        </tr>
        <?php if (empty($resultados)): ?>
            <tr>
                <td colspan="5">No se encontraron noticias.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($resultados as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['titulo'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['descripcion'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['categoria'] ?? '') ?></td>
                    <td><a href="<?= htmlspecialchars($row['link'] ?? '') ?>" target="_blank">Ver</a></td>
                    <td><?= (isset($row['fPubli']) && $row['fPubli'] && $row['fPubli'] !== '0000-00-00') ? date('d-M-Y', strtotime($row['fPubli'])) : 'Sin fecha' ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</body>

</html>