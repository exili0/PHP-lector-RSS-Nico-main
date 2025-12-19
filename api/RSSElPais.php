<?php

require_once "conexionRSS.php";

$sXML = download("http://ep00.epimg.net/rss/elpais/portada.xml");

if (empty($sXML)) {
    throw new Exception("No se pudo obtener el contenido del RSS");
}

$oXML = new SimpleXMLElement($sXML);

require_once "conexionBBDD.php";

if (pg_last_error()) {
    printf("Conexión a el periódico El País ha fallado");
} else {

    $contador = 0;
    $categoria = ["Política", "Deportes", "Ciencia", "España", "Economía", "Música", "Cine", "Europa", "Justicia"];
    //$categoriaFiltro="";  <-- NOOOO INICIALIZAR AQUÍ! SE DEBE INICIALIZAR DENTRO DEL BUCLE

    foreach ($oXML->channel->item as $item) {


        $categoriaFiltro="";  // INICIALIZAR AQUÍ! ya que es por cada item
        for ($i = 0; $i < count($item->category); $i++) {

            for ($j = 0; $j < count($categoria); $j++) {

                if ($item->category[$i] == $categoria[$j]) {
                    $categoriaFiltro = "[" . $categoria[$j] . "]" . $categoriaFiltro;
                }
            }
        }



        $fPubli = strtotime($item->pubDate);
        $new_fPubli = date('Y-m-d', $fPubli);


        $content = $item->children("content", true);
        $encoded = $content->encoded;


        $sql = "SELECT link FROM elpais";
        $result = pg_query($link, $sql);

        while ($sqlCompara = pg_fetch_assoc($result)) {


            if ($sqlCompara['link'] == $item->link) {

                $Repit = true;
                $contador = $contador + 1;
                $contadorTotal = $contador;
                break;
            } else {
                $Repit = false;
            }
        }
        if ($Repit == false && $categoriaFiltro <> "") {

            $sql = "INSERT INTO elpais VALUES('','$item->title','$item->link','$item->description','$categoriaFiltro','$new_fPubli','$encoded')";
            $result = pg_query($link, $sql);
        }

        $categoriaFiltro = "";
    }
}
