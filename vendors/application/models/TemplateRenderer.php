<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class TemplateRenderer{
    private $templatePath;
    private $data = [];

    public function __construct(){
    }

    public function templatePath($templatePath){
        if (!file_exists($templatePath)) {
            throw new Exception("PLantilla no encontrada: " . $templatePath);
        }
        $this->templatePath = $templatePath;
    }

    public function assign($key, $value){
        $this->data[$key] = $value;
    }

    public function render(){
        // Extract data into local variables for the template
        extract($this->data);
        // Start output buffering to capture the template's output
        ob_start();
        include $this->templatePath;
        $output = ob_get_clean(); // Get the buffered content and clear the buffer
        return $output;
    }
}

?>