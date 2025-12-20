<?php
require_once 'conexionBBDD.php';  // Carga $link

function download($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

$sXML = download("http://ep00.epimg.net/rss/elpais/portada.xml");

if (empty($sXML)) {
    die("No se pudo obtener el RSS de El País");
}

$oXML = new SimpleXMLElement($sXML);

$contador = 0;
$categoria = ["Política", "Deportes", "Ciencia", "España", "Economía", "Música", "Cine", "Europa", "Justicia"];

foreach ($oXML->channel->item as $item) {
    $categoriaFiltro = "";
    
    for ($i = 0; $i < count($item->category ?? []); $i++) {
        for ($j = 0; $j < count($categoria); $j++) {
            if ($item->category[$i] == $categoria[$j]) {
                $categoriaFiltro = "[" . $categoria[$j] . "]" . $categoriaFiltro;
            }
        }
    }

    $fPubli = strtotime($item->pubDate);
    $new_fPubli = date('Y-m-d', $fPubli);

    $stmt = $link->prepare("SELECT COUNT(*) FROM elpais WHERE link = ?");
    $stmt->execute([(string)$item->link]);
    
    if ($stmt->fetchColumn() == 0 && $categoriaFiltro <> "") {
        $stmt = $link->prepare("
            INSERT INTO elpais (titulo, link, descripcion, categoria, fPubli, contenido) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (string)$item->title,
            (string)$item->link,
            (string)$item->description,
            $categoriaFiltro,
            $new_fPubli,
            (string)$item->children("content", true)->encoded ?: (string)$item->description
        ]);
        $contador++;
    }
}

echo "El País: $contador noticias nuevas guardadas";
?>
