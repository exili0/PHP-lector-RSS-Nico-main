<?php

function download($ruta)
{
    // Inicializar cURL
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $ruta);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, false);

    // Agregar un User-Agent (simula un navegador)
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MyRSSApp/1.0;)');
    
    // Desactivar la verificación SSL (INSEGURO, pero necesario para funcionar en algunos entornos serverless)
    // Esto resuelve problemas comunes de certificados en Vercel.
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Habilitar el seguimiento de redireccionamientos 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    
    $salida = curl_exec($ch);
        
    curl_close($ch);
    
    return $salida;
}