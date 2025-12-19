<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>

<head>
    <meta charset="UTF-8">
    <title></title>
</head>

<style>
    th,
    td {
        border: 1px #E4CCE8 solid;
        padding: 5px;
        text-align: left;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }
</style>

<body>
    <form action="index.php" method="GET">
        <fieldset>
            <legend>FILTRO</legend>
            <label>PERIODICO : </label>
            <select type="selector" name="periodicos">
                <option name="elpais">El Pais</option>
                <option name="elmundo">El Mundo</option>
            </select>
            <label>CATEGORIA : </label>
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

            <input type="submit" name="filtrar">
        </fieldset>
    </form>

    <?php
    require_once "RSSElPais.php";
    require_once "RSSElMundo.php";

    function filtros($sql, $link)
    {
        $filtrar = pg_query($link, $sql);
        // Verificación de error de consulta

        if (!$filtrar) {
            echo "<tr><td colspan='6'>Error: " . pg_last_error($link) . "</td></tr>";
            return;
        }
        // Si no hay resultados
        if (pg_num_rows($filtrar) == 0) {
            echo "<tr><td colspan='6'>No se encontraron noticias.</td></tr>";
            return;
        }

        while ($arrayFiltro = pg_fetch_assoc($filtrar)) {

            echo "<tr>";
            echo "<th style='border: 1px #E4CCE8 solid;'>" . $arrayFiltro['titulo'] . "</th>";
            echo "<th style='border: 1px #E4CCE8 solid;'>" . $arrayFiltro['contenido'] . "</th>";
            echo "<th style='border: 1px #E4CCE8 solid;'>" . $arrayFiltro['descripcion'] . "</th>";
            echo "<th style='border: 1px #E4CCE8 solid;'>" . $arrayFiltro['categoria'] . "</th>";
            echo "<th style='border: 1px #E4CCE8 solid;'>" . $arrayFiltro['link'] . "</th>";
            $fecha = date_create($arrayFiltro['fPubli']);
            $fechaConversion = date_format($fecha, 'd-M-Y');
            //$fechaConversion=date('j-n-Y',srtotime($arrayFiltro['fPubli']));
            echo "<th style='border: 1px #E4CCE8 solid;'>" . $fechaConversion . "</th>";
            echo "</tr>";
        }
    }

    require_once "conexionBBDD.php";

    if (mysqli_connect_error()) {
        printf("Conexión fallida");
    } else {

        echo "<table style='border: 5px #E4CCE8 solid;'>";
        echo "<tr><th><p style='color: #66E9D9;'>TITULO</p ></th><th><p  style='color: #66E9D9;'>CONTENIDO</p ></th><th><p  style='color: #66E9D9;'>DESCRIPCIÓN</p ></th><th><p  style='color: #66E9D9;'>CATEGORÍA</p ></th><th><p  style='color: #66E9D9;'>ENLACE</p ></th><th><p  style='color: #66E9D9;'>FECHA DE PUBLICACIÓN</p ></th></tr>" . "<br>";




        if (isset($_REQUEST['filtrar'])) {

            // Limpieza básica de la entrada
            $periodicos = strtolower(str_replace(' ', '', $_REQUEST['periodicos']));
            $periodicosMin = strtolower($periodicos);


            $cat = $_REQUEST['categoria'];
            $f = $_REQUEST['fecha'];
            $palabra = $_REQUEST["buscar"];

            // CAMBIO DE LÓGICA: Usamos IF/ELSEIF para garantizar que solo se ejecuta UNA consulta.
            // Además, ajustamos el LIKE de 'categoria' a '%[cat]%' para coincidir con el formato de la bbdd

            // FILTRO TODO
            if ($cat != "" && $f != "" && $palabra != "") {
                $sql = "SELECT * FROM " . $periodicosMin . " WHERE descripcion LIKE '%$palabra%' AND categoria LIKE '%[$cat]%' AND fPubli='$f'";
                filtros($sql, $link);
            }

            // FILTRO CATEGORIA, FECHA
            elseif ($cat != "" && $f != "" && $palabra == "") {
                $sql = "SELECT * FROM " . $periodicosMin . " WHERE categoria LIKE '%[$cat]%' AND fPubli='$f'";
                filtros($sql, $link);
            }

            // FILTRO CATEGORIA, PALABRA
            elseif ($cat != "" && $f == "" && $palabra != "") {
                $sql = "SELECT * FROM " . $periodicosMin . " WHERE descripcion LIKE '%$palabra%' AND categoria LIKE '%[$cat]%'";
                filtros($sql, $link);
            }

            // FILTRO FECHA, PALABRA 
            elseif ($cat == "" && $f != "" && $palabra != "") {
                $sql = "SELECT * FROM " . $periodicosMin . " WHERE descripcion LIKE '%$palabra%' AND fPubli='$f'";
                filtros($sql, $link);
            }

            // FILTRO CATEGORIA
            elseif ($cat != "" && $f == "" && $palabra == "") {
                $sql = "SELECT * FROM " . $periodicosMin . " WHERE categoria LIKE '%[$cat]%'";
                filtros($sql, $link);
            }

            // FILTRO FECHA
            elseif ($cat == "" && $f != "" && $palabra == "") {
                $sql = "SELECT * FROM " . $periodicosMin . " WHERE fPubli='$f'";
                filtros($sql, $link);
            }

            // FILTRO PALABRA
            elseif ($palabra != "" && $cat == "" && $f == "") {
                $sql = "SELECT * FROM " . $periodicosMin . " WHERE descripcion LIKE '%$palabra%' ";
                filtros($sql, $link);
            }

            // FILTRO SOLO PERIODICO (ningún otro filtro aplicado)
            else {
                // Si no se cumple ninguna de las anteriores, pero sí se pulsó filtrar (botón)
                $sql = "SELECT * FROM " . $periodicosMin . " ORDER BY fPubli desc";
                filtros($sql, $link);
            }
        } else {

            $sql = "SELECT * FROM elpais ORDER BY fPubli desc"; // Cuadno no se aplica ningún filtro, mostrar todo ordenado por fecha descendente

            filtros($sql, $link);
        }
    }


    echo "</table>";






    ?>

</body>

</html>