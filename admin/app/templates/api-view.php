<?php
// Establece el código de respuesta HTTP correspondiente al error.
// Por ejemplo, 400 para "Bad Request", 404 para "Not Found", etc.
http_response_code(404);

// Crea un arreglo asociativo con el mensaje de error.
// Este mensaje será enviado al cliente en formato JSON.
$MSG['message'] = $data;

// Codifica el arreglo en formato JSON y lo devuelve como respuesta.
// Esto permite que el cliente reciba el mensaje de error de forma estructurada.
return json_encode($MSG);

?>