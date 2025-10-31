<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class Response {
    /******************************************************************************/
    //Se genera la respuesta
    public static function sendData($code,$message = "",$Extra = ""){
        /*
        Codigos de respuestas

        Errores en las solicitudes de los clientes
        400 — Solicitud incorrecta: estructura de URL no válida. El servidor no puede comprender la solicitud realizada por el usuario.
        401 — Autorización requerida: estos mensajes se muestran cuando los webmasters han restringido el acceso a la página.
        402 — Pago requerido (aún no se utiliza): si el inicio del pago no se realiza, generalmente se muestra este código.
        403 — Prohibido: la razón por la que aparece un error 403 es porque estás intentando acceder a un recurso que tiene permisos restringidos. Un sitio web muestra errores 403 prohibidos cuando los usuarios intentan acceder a una página que requiere autenticación.
        404 — No encontrado: 404 es una indicación clara para los usuarios de que la URL solicitada no está disponible en el sitio web. Puede deberse a un error tipográfico en la URL o a que la página se haya eliminado del sitio.
        405 — Método no permitido: este código de estado de respuesta HTTP indica que el servidor se ha negado a aceptar el método de solicitud a pesar de comprender el propósito de la solicitud.
        406 — No aceptable (codificación): esto suele suceder cuando el servidor no puede responder con la solicitud de encabezado de aceptación.
        407 — Se requiere autenticación de proxy: este error indica que la solicitud no se puede cumplir debido a la falta de autenticación del servidor proxy entre el navegador y el servidor.
        408 — Tiempo de solicitud agotado: este es uno de los errores HTTP más comunes que encuentran los webmasters cuando el servidor no recibe una solicitud completa del lado del cliente dentro del período de tiempo de espera asignado.
        409 — Solicitud conflictiva: este error se produce cuando el estado del recurso de destino entra en conflicto con el estado actual. Para resolver el error, identifique el conflicto y vuelva a enviar el documento.
        410 — Desactivado: este código de error representa que el acceso al recurso solicitado se ha eliminado permanentemente del servidor y permanecerá así en todo momento.
        411 — Longitud de contenido requerida: el error representa la incapacidad del servidor para aceptar la solicitud del cliente debido a la falla al definir el encabezado de longitud de contenido.
        412 — Condición previa fallida: se trata de un error causado por un conflicto de seguridad con una o varias de las configuraciones de seguridad que se han implementado en su servidor.
        413 — Entidad de solicitud demasiado larga: cuando el recurso solicitado es demasiado grande para que el servidor lo cargue, el usuario puede experimentar el error 413.
        414 — La URL de solicitud es demasiado larga: piense en una estructura de URL de más de 2048 caracteres. El servidor no puede descifrar el error 414 resultante.
        415 — Tipo de medio no compatible: este error aparece cuando el servidor se niega a cargar un recurso que está en un formato de medio no compatible.

        Errores del servidor
        500 — Error interno del servidor
        501 — No implementado
        502 — Puerta de enlace no válida
        503 — Servicio no disponible
        504 — Tiempo de espera de la puerta de enlace
        505 — Versión HTTP no compatible.

        */
        // Establece el código de respuesta HTTP.
        // Esto indica al cliente el estado de la solicitud (por ejemplo, 200 OK, 400 Bad Request, 500 Internal Server Error).
        http_response_code($code);

        // Inicializa un arreglo asociativo para construir el cuerpo de la respuesta.
        $MSG = [];

        // Agrega el mensaje principal al arreglo.
        // Este mensaje puede describir el resultado de la operación o el error ocurrido.
        $MSG['message'] = $message;

        // Verifica si existe información adicional para incluir en la respuesta.
        // Si la variable $Extra no está vacía, se agrega al arreglo bajo la clave 'Extra'.
        if (!empty($Extra) && $Extra != '') {
            $MSG['Extra'] = $Extra;
        }

        // Codifica el arreglo como una cadena JSON y la devuelve como respuesta.
        // Esto permite que el cliente reciba los datos en un formato estructurado y fácilmente interpretable.
        return json_encode($MSG);

    }
}
