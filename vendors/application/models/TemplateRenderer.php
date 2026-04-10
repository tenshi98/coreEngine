<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Clase TemplateRenderer
 * * Gestiona la carga de archivos de plantilla, la asignación de variables
 * dinámicas y la generación de contenido HTML procesado mediante el uso
 * de búferes de salida.
 */
class TemplateRenderer{
    /** @var string Ruta del archivo de plantilla a procesar */
    private $templatePath;
    /** @var array Almacén de datos para ser extraídos en la plantilla */
    private $data = [];

    /**
     * Define la ruta del archivo de plantilla y verifica su existencia.
     * @param string $templatePath Ruta relativa o absoluta al archivo.
     * @return string|void Retorna un mensaje de error si el archivo no existe.
     */
    public function templatePath($templatePath){
        // Verifica la integridad de la ruta antes de la asignación
        if (!file_exists($templatePath)) {
            return "PLantilla no encontrada: " . $templatePath;
        }
        $this->templatePath = $templatePath;
    }

    /**
     * Asigna valores al diccionario de datos de la plantilla.
     * @param string $key Nombre de la variable dentro de la plantilla.
     * @param mixed $value Valor asociado a la variable.
     * @return void
     */
    public function assign($key, $value){
        // Inserta o actualiza el par clave-valor en el arreglo interno
        $this->data[$key] = $value;
    }

    /**
     * Procesa la plantilla y retorna el contenido generado.
     * @return string El contenido procesado de la plantilla (HTML/Texto).
     */
    public function render(){
        // Convierte el arreglo asociativo en variables locales individuales
        extract($this->data);

        // Inicia el almacenamiento temporal en memoria de la salida generada
        ob_start();

        // Ejecuta e incluye el archivo de plantilla
        include $this->templatePath;

        // Recupera el contenido acumulado en el búfer y finaliza el almacenamiento
        $output = ob_get_clean();

        // Devuelve la cadena final resultante
        return $output;
    }
}
