<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FileManager{

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $CommonData;

	/******************************************************************************/
	//Instancias
	public function __construct(){
		$this->CommonData      = new FunctionsCommonData();
    }

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
    public function validateFiles($SIS_FILES, $arrArchivos, $PostData = null){
        //Variable de errores
        $errors = [];
        //Recorro los archivos
        foreach ($arrArchivos as $archivo) {
            /***************************************************/
            //Valido el tipo de archivo subido
            switch ($archivo['Base64']) {
                /***************************************************/
                //Si el archivo se envia codificado
                case true:
                    //No se valida nada
                    break;
                /***************************************************/
                //Si es un archivo normal
                case false:
                    /***************************************************/
                    //Verifico si el dato post existe
                    if(isset($PostData[$archivo['Identificador']])){
                        /***************************************************/
                        //Verifico la existencia del archivo a subir
                        if (empty($SIS_FILES[$archivo['Identificador']])) {
                            $errors[] = ["message" => $archivo['Identificador'].' es obligatorio'];
                        }
                        /***************************************************/
                        //Verifico si hay errores
                        if ($SIS_FILES[$archivo['Identificador']]["error"] > 0){
                            $errors[] = ["message" => $this->uploadPHPError($SIS_FILES[$archivo['Identificador']]["error"])];
                        }
                        /***************************************************/
                        //Verifico si tiene la extension permitida
                        //Separo los datos
                        $arrTipo   = $this->CommonData->parseDataCommas($archivo['ValidarTipo']); //Separacion por comas
                        $dataTypes = array();
                        // Optimización: Usar un array asociativo para los tipos MIME
                        $mimeTypes = [
                            'word'       => ['application/msword','application/vnd.ms-word','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/x-abiword','application/vnd.oasis.opendocument.text'],
                            'excel'      => ['application/msexcel','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','text/csv','application/vnd.oasis.opendocument.spreadsheet'],
                            'powerpoint' => ['application/mspowerpoint','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.oasis.opendocument.presentation'],
                            'pdf'        => ['application/pdf','application/octet-stream','application/x-real','application/vnd.adobe.xfdf','application/vnd.fdf','binary/octet-stream','application/epub+zip'],
                            'image'      => ['image/jpg','image/jpeg','image/gif','image/png','image/bmp','image/webp'],
                            'txt'        => ['text/plain','text/richtext','application/rtf'],
                            'zip'        => ['application/x-zip-compressed','application/zip','multipart/x-zip','application/x-7z-compressed','application/x-rar-compressed','application/gzip','application/x-gzip','application/x-gtar','application/x-tgz','application/octet-stream','application/x-bzip','application/x-bzip2'],
                            'video'      => ['video/x-msvideo','video/mpeg','video/ogg','video/webm','application/mp4'],
                            'music'      => ['audio/aac','audio/midi','audio/ogg','audio/x-wav','audio/webm']
                        ];
                        //Se recorren los autorizados
                        foreach ($arrTipo as $tipo) {
                            if (isset($mimeTypes[$tipo])) {
                                $dataTypes = array_merge($dataTypes, $mimeTypes[$tipo]);
                            }
                        }
                        //Verifico si el archivo subido esta entre los autorizados
                        if (!in_array($SIS_FILES[$archivo['Identificador']]['type'], $dataTypes, true)) {
                            $errors[] = ["message" => 'Tipo de archivo no permitido'];
                        }
                        /***************************************************/
                        //Verifico el peso del archivo no pasa del maximo establecido (expresado en megas)
                        if ($SIS_FILES[$archivo['Identificador']]['size'] >= ($archivo['ValidarPeso'] * 1048576)){
                            $errors[] = ["message" => 'Archivo excede el tamaño permitido'];
                        }
                        /***************************************************/
                        //Verifico la existencia del archivo en el servidor
                        // Construye el nombre del archivo
                        if (!empty($archivo['NombreArchivo'])) {
                            $NombreArchivo = $archivo['NombreArchivo'].'.'.pathinfo($SIS_FILES[$archivo['Identificador']]['name'], PATHINFO_EXTENSION);
                        } elseif (!empty($archivo['SufijoArchivo'])) {
                            $NombreArchivo = $archivo['SufijoArchivo'].$SIS_FILES[$archivo['Identificador']]['name'];
                        } else {
                            $NombreArchivo = $SIS_FILES[$archivo['Identificador']]['name'];
                        }
                        // Construye la ruta
                        $rutaArchivo = '../upload/';
                        if (!empty($archivo['SubCarpeta'])) {
                            $rutaArchivo .= str_replace('../', '',$archivo['SubCarpeta']) . '/';
                        }
                        $rutaArchivo .= $NombreArchivo;
                        // Verifica existencia
                        if (file_exists($rutaArchivo)) {
                            $errors[] = ["message" => 'El archivo ' . $SIS_FILES[$archivo['Identificador']]['name'] . ' ya existe en el servidor'];
                        }
                    }
                    break;
            }
        }
        //si no hay errores
        return (empty($errors)) ? true : $errors;

    }
    /******************************************************************************/
    public function uploadFile($SIS_FILES, $arrArchivos, $PostData = null){
        //Variables
        $Data             = [];
        $Data['Nombres']  = '';
        $Data['Archivos'] = '';
        $Data['Update']   = '';
        //Recorro los archivos
        foreach ($arrArchivos as $archivo) {
            /***************************************************/
            //Valido el tipo de archivo subido
            switch ($archivo['Base64']) {
                /***************************************************/
                //Si el archivo se envia codificado
                case true:
                    /***************************************************/
                    //Verifico si el dato post existe
                    if(isset($PostData[$archivo['Identificador']])){
                        //Se obtiene la imagen
                        $dIMG = base64_decode(str_replace(['data:image/png;base64,', ' '], ['', '+'], $PostData[$archivo['Identificador']]));
                        //se guarda el archivo
                        $NombreArchivo = '';
                        if(isset($archivo['NombreArchivo'])&&$archivo['NombreArchivo']!=''){
                            $NombreArchivo = $archivo['NombreArchivo'].'.png';
                        }elseif((!isset($archivo['NombreArchivo']) OR $archivo['NombreArchivo']=='')&&isset($archivo['SufijoArchivo'])&&$archivo['SufijoArchivo']!=''){
                            $NombreArchivo = $archivo['SufijoArchivo'].time().'.png';
                        }
                        //Genero la ruta
                        $rutaArchivo = ConfigAPP::APP["uploadFolder"]; //Carpeta upload por defecto (ruta completa en el servidor)
                        if(isset($archivo['SubCarpeta'])&&$archivo['SubCarpeta']!=''){$rutaArchivo.= str_replace('../', '',$archivo['SubCarpeta']).'/';}
                        //Verifico la existencia del archivo
                        if (!file_exists($rutaArchivo.$NombreArchivo)){
                            //Se cambian los permisos de la carpeta
                            if (!is_dir($rutaArchivo)) {
                                mkdir($rutaArchivo, 0755, true);
                            }
                            //Se mueve el archivo a la carpeta previamente configurada
                            $move_result = file_put_contents($rutaArchivo.$NombreArchivo, $dIMG);
                            if ($move_result){
                                //Se guardan los nombres
                                $Data['Nombres']  .= ",".$archivo['Identificador'];
                                $Data['Archivos'] .= ",'".$NombreArchivo."'";
                                $Data['Update']   .= ",".$archivo['Identificador']." = '".$NombreArchivo."'";
                            }
                        }
                    }
                    break;
                /***************************************************/
                //Si es un archivo normal
                case false:
                    /***************************************************/
                    //Verifico si el dato post existe
                    if(isset($SIS_FILES[$archivo['Identificador']]['name'])){
                        //se guarda el archivo
                        $NombreArchivo = '';
                        if(isset($archivo['NombreArchivo'])&&$archivo['NombreArchivo']!=''){
                            $NombreArchivo = $archivo['NombreArchivo'].'.'.pathinfo($SIS_FILES[$archivo['Identificador']]['name'], PATHINFO_EXTENSION);
                        }elseif((!isset($archivo['NombreArchivo']) OR $archivo['NombreArchivo']=='')&&isset($archivo['SufijoArchivo'])&&$archivo['SufijoArchivo']!=''){
                            $NombreArchivo = $archivo['SufijoArchivo'].$SIS_FILES[$archivo['Identificador']]['name'];
                        } else {
                            $NombreArchivo = $SIS_FILES[$archivo['Identificador']]['name'];
                        }
                        //Genero la ruta
                        $rutaArchivo = ConfigAPP::APP["uploadFolder"]; //Carpeta upload por defecto (ruta completa en el servidor)
                        if(isset($archivo['SubCarpeta'])&&$archivo['SubCarpeta']!=''){$rutaArchivo.= str_replace('../', '',$archivo['SubCarpeta']).'/';}
                        //Verifico la existencia del archivo
                        if (!file_exists($rutaArchivo.$NombreArchivo)){
                            //Se cambian los permisos de la carpeta
                            if (!is_dir($rutaArchivo)) {
                                mkdir($rutaArchivo, 0777, true);
                            }
                            //Se mueve el archivo a la carpeta previamente configurada
                            $move_result = @move_uploaded_file($SIS_FILES[$archivo['Identificador']]["tmp_name"], $rutaArchivo.$NombreArchivo);
                            if ($move_result){
                                //Se guardan los nombres
                                $Data['Nombres']  .= ",".$archivo['Identificador'];
                                $Data['Archivos'] .= ",'".$NombreArchivo."'";
                                $Data['Update']   .= ",".$archivo['Identificador']." = '".$NombreArchivo."'";
                            }
                        }
                    }
                    break;
            }
        }
        //Devuelvo los resultados
        return $Data;
    }
    /******************************************************************************/
    public function deleteFile($SIS_File, $SIS_Carpeta){
        /******************************************/
        //Se generan las rutas
        $rutaArchivo = ConfigAPP::APP["uploadFolder"]; //Carpeta upload por defecto (ruta completa en el servidor)
        if(isset($SIS_Carpeta)&&$SIS_Carpeta!=''){$rutaArchivo.= $SIS_Carpeta.'/';}
        //se elimina el archivo y Verifico la existencia del archivo
        if(isset($SIS_File)&&$SIS_File!=''&&file_exists($rutaArchivo.$SIS_File)){
            unlink($rutaArchivo.$SIS_File);
        }

        //Devuelvo los resultados
        return true;
    }
    /******************************************************************************/
    public function deleteFilesMasive($SIS_Files, $SIS_Carpeta, $Result){
        /******************************************/
        //Separo los datos
        $arrFiles = $this->CommonData->parseDataCommas($SIS_Files); //Separacion por comas
        /******************************************/
        //Se generan las rutas
        $rutaArchivo = ConfigAPP::APP["uploadFolder"]; //Carpeta upload por defecto (ruta completa en el servidor)
        if(isset($SIS_Carpeta)&&$SIS_Carpeta!=''){$rutaArchivo.= $SIS_Carpeta.'/';}
        //recorro los archivos a borrar
        foreach ($arrFiles as $file) {
            //se elimina el archivo y Verifico la existencia del archivo
            if(isset($Result[$file])&&$Result[$file]!=''&&file_exists($rutaArchivo.$Result[$file])){
                unlink($rutaArchivo.$Result[$file]);
            }
        }

        //Devuelvo los resultados
        return true;
    }
    /******************************************************************************/
    /******************************************************************************/
    private function uploadPHPError($error) {
        switch ($error) {
            case 0: return "No hay error, el archivo se cargó con éxito"; break;
            case 1: return "El archivo cargado supera la directiva upload_max_filesize en php.ini"; break;
            case 2: return "El archivo cargado excede la directiva MAX_FILE_SIZE que se especificó en el formulario HTML"; break;
            case 3: return "El archivo cargado solo se cargó parcialmente"; break;
            case 4: return "No se cargó ningún archivo"; break;
            case 6: return "Falta una carpeta temporal"; break;
            case 7: return "Error al escribir el archivo en el disco"; break;
            case 8: return "Una extensión PHP detuvo la carga del archivo"; break;
        }
    }

}

