<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Clase Request
 * * Gestiona los datos de entrada de una petición HTTP, permitiendo el acceso
 * a parámetros y la recuperación de cuerpos de mensaje en formato JSON.
 */
class Request{
    /** @var array Parámetros de la petición */
    public $params;
    /** @var string Tipo de contenido (Content-Type) de la petición */
    public $contentType;

    /**
     * Inicializa una nueva instancia de la clase Request.
     * * @param array $params Conjunto inicial de parámetros para la petición.
     */
    public function __construct($params = []){
        // Asigna los parámetros proporcionados a la propiedad pública
        $this->params = $params;
        // Recupera el Content-Type desde las variables de servidor si existe
        $this->contentType = !empty($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    }

    /**
     * Obtiene y decodifica el cuerpo de la petición si el Content-Type es application/json.
     * * @return mixed Datos decodificados del JSON (normalmente un objeto o array).
     * @throws JsonException Si el cuerpo no puede ser decodificado o es inválido.
     */
    public function getJSON(){
        // Compara el tipo de contenido ignorando mayúsculas y minúsculas
        if (strcasecmp($this->contentType, 'application/json') !== 0) {
            // Retorna un arreglo vacío si el formato no coincide con el esperado
            return [];
        }

        // Lee el flujo de entrada de la petición y procesa la cadena JSON
        $data = json_decode(file_get_contents("php://input"));

        // Valida si la decodificación falló (resultado nulo)
        if($data == null){
            // Lanza una excepción específica para indicar error en el formato de datos
            throw new JsonException('Could not decode the data.');
        }

        // Retorna la estructura de datos resultante
        return $data;
    }
}
