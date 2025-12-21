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
        :root {
            --primary: #745ae6;
            --primary-light: #382eca;
            --bg: #d6e3ff;
            --text: #16191a;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg);
            margin: 0;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Formulario */
        form {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 1000px;
            margin-bottom: 30px;
        }

        fieldset {
            border: none;
            margin: 0;
            padding: 0;
        }

        legend {
            color: var(--primary);
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 180px;
        }

        .filter-group label {
            font-size: 11px;
            font-weight: 700;
            color: #636e72;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        select,
        input[type="date"],
        input[type="text"] {
            padding: 10px;
            border: 1px solid #dfe6e9;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        select:focus,
        input:focus {
            border-color: var(--primary-light);
        }

        input[type="submit"] {
            background: var(--primary);
            color: white;
            border: none;
            padding: 11px 25px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        input[type="submit"]:hover {
            background: #6c3483;
        }

        /* Tabla */
        .news-table {
            width: 100%;
            max-width: 1100px;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .news-table th {
            background: #f1f2f6;
            color: #2d3436;
            text-align: left;
            padding: 15px;
            font-size: 13px;
        }

        .news-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f2f6;
            color: #636e72;
            font-size: 14px;
            vertical-align: top;
        }

        .col-title {
            font-weight: 600;
            color: var(--text) !important;
            width: 20%;
        }

        .col-desc {
            font-size: 13px !important;
            width: 40%;
        }

        .badge {
            background: #eeeafb;
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        .btn-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        tr:hover {
            background-color: #fcfaff;
        }

        .filter-group.action {
            flex: 0;
            /* No deja que el botón se estire */
            min-width: 120px;
        }

        input[type="submit"] {
            width: auto;
            /* El botón solo ocupa lo que mide su texto */
            padding: 10px 30px;
            align-self: flex-end;
        }
    </style>
</head>

<body>
    <form action="/api/index.php" method="POST">
        <fieldset>
            <legend>FILTRO DE NOTICIAS</legend>
            <div class="filter-container">
                <div class="filter-group">
                    <label>PERIÓDICO:</label>
                    <select name="periodicos">
                        <option value="elpais" <?=($_POST['periodicos'] ?? 'elmundo' )=='elpais' ? 'selected' : '' ?>>El
                            Pais</option>
                        <option value="elmundo" <?=($_POST['periodicos'] ?? 'elmundo' )=='elmundo' ? 'selected' : '' ?>
                            >El Mundo</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>CATEGORÍA:</label>
                    <select name="categoria">
                        <option value="">Todas</option>
                        <option value="Política" <?=($_POST['categoria'] ?? '' )=='Política' ? 'selected' : '' ?>
                            >Política</option>
                        <option value="Deportes" <?=($_POST['categoria'] ?? '' )=='Deportes' ? 'selected' : '' ?>
                            >Deportes</option>
                        <option value="España" <?=($_POST['categoria'] ?? '' )=='España' ? 'selected' : '' ?>>España
                        </option>
                        <option value="Economía" <?=($_POST['categoria'] ?? '' )=='Economía' ? 'selected' : '' ?>
                            >Economía</option>
                        <option value="Europa" <?=($_POST['categoria'] ?? '' )=='Europa' ? 'selected' : '' ?>>Europa
                        </option>
                        <option value="Justicia" <?=($_POST['categoria'] ?? '' )=='Justicia' ? 'selected' : '' ?>
                            >Justicia</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>FECHA:</label>
                    <input type="date" name="fecha" value="<?= $_POST['fecha'] ?? '' ?>">
                </div>

                <div class="filter-group">
                    <label>BUSCAR:</label>
                    <input type="text" name="buscar" value="<?= $_POST['buscar'] ?? '' ?>" placeholder="en descripción">
                </div>

                <div class="filter-group action">
                    <input type="submit" name="filtrar" value="Filtrar">
                </div>
            </div>
        </fieldset>
    </form>

    <table class="news-table">
        <thead>
            <tr>
                <th>TITULO</th>
                <th>DESCRIPCIÓN</th>
                <th>CATEGORÍA</th>
                <th>ENLACE</th>
                <th>FECHA</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($resultados)): ?>
            <tr>
                <td colspan="5" class="empty-msg">No se encontraron noticias.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($resultados as $row): ?>
            <tr>
                <td class="col-title">
                    <?= htmlspecialchars($row['titulo'] ?? '') ?>
                </td>
                <td class="col-desc">
                    <?= htmlspecialchars($row['descripcion'] ?? '') ?>
                </td>
                <td><span class="badge">
                        <?= htmlspecialchars($row['categoria'] ?? '') ?>
                    </span></td>
                <td><a href="<?= htmlspecialchars($row['link'] ?? '') ?>" target="_blank" class="btn-link">Ver</a></td>
                <td class="col-date">
                    <?= !empty($row['fPubli']) ? date('d M, Y', strtotime($row['fPubli'])) : 'Sin fecha' ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>