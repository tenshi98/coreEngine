<?php
/*******************************************************************************************************************/
/*                                              Requisitos                                                         */
/*******************************************************************************************************************/
/**
 * Carga el sistema de almacenamiento multi-driver.
 * Se incluye aquí para garantizar disponibilidad sin importar
 * el orden de carga del framework (storage/ no está en AUTOLOAD).
 */
require_once __DIR__ . '/../storage/StorageDriverInterface.php';
require_once __DIR__ . '/../storage/LocalStorageDriver.php';
require_once __DIR__ . '/../storage/S3StorageDriver.php';
require_once __DIR__ . '/../storage/GCSStorageDriver.php';
require_once __DIR__ . '/../storage/SFTPStorageDriver.php';
require_once __DIR__ . '/../storage/StorageDriverFactory.php';

/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class FileUploader
 *
 * Pipeline de validación y subida de archivos (multipart/form-data y Base64).
 * Extraída de FileManager (RF-009a).
 *
 * Mejoras heredadas:
 *  - Validación de MIME real con finfo (evita spoofing del header HTTP).
 *  - Extensión final derivada del MIME real (no del cliente).
 *  - Doble barrera: validación previa (`validateFiles`) + verificación en la subida real.
 *
 * Depende del driver de almacenamiento (inyectado) y de `FileInspector`.
 */
class FileUploader {

    /*******************************************************************************************************************/
    /*                                                Instancias                                                       */
    /*******************************************************************************************************************/

    private StorageDriverInterface $storage;    // ← driver activo (local | s3 | gcs | sftp)
    private FileInspector          $inspector;
    private static array           $pathCache = [];

    /*******************************************************************************************************************/
    /*                                                Constructor                                                      */
    /*******************************************************************************************************************/

    /**
     * @param StorageDriverInterface $storage   Driver de almacenamiento activo.
     * @param FileInspector          $inspector Detector de MIME real (finfo) compartido.
     */
    public function __construct(StorageDriverInterface $storage, FileInspector $inspector) {
        $this->storage   = $storage;
        $this->inspector = $inspector;
    }

    /*******************************************************************************************************************/
    /*                                                  Métodos públicos                                               */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Valida la integridad, seguridad y requisitos técnicos de los archivos antes de su procesamiento.
     *
     * Verifica errores de subida nativos de PHP, bloquea extensiones peligrosas, valida el
     * tipo MIME real (inspeccionando el contenido y no solo la extensión), controla el peso
     * máximo permitido y comprueba la existencia de duplicados mediante el driver de
     * almacenamiento activo.
     *
     * @param array $SIS_FILES   Arreglo equivalente a $_FILES con los datos binarios.
     * @param array $arrArchivos Configuración detallada de cada archivo esperado.
     * @param array $PostData    Datos adicionales del contexto de la petición.
     * @return array ['success' => true] si pasa todas las pruebas, o un arreglo con el detalle de los errores.
     */
    public function validateFiles(array $SIS_FILES, array $arrArchivos, array $PostData = []): array {

        // Si no se definieron reglas de archivos, se asume validación exitosa por defecto
        if (empty($arrArchivos)) {
            return ['success' => true, 'message' => true];
        }

        // Acumulador de incidentes encontrados durante la validación
        $errors = [];

        // Procesamiento secuencial de cada archivo configurado
        foreach ($arrArchivos as $archivo) {
            /**
             * Filtro de entrada:
             * Solo se valida si el identificador está presente en PostData y NO es una carga vía Base64,
             * ya que el procesamiento de Base64 sigue un flujo de decodificación distinto.
             */
            if (!isset($PostData[$archivo['Identificador']]) || $archivo['Base64'] !== false) {
                continue;
            }

            $id = $archivo['Identificador'];

            /*************** Verificaciones de Existencia y Sistema ***************/

            // 1. Comprueba que el archivo físico exista en el buffer de subida
            if (empty($SIS_FILES[$id])) {
                $errors[] = ['success' => false, 'message' => $id . ' es obligatorio'];
                continue;
            }

            // 2. Valida códigos de error nativos de PHP (UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_PARTIAL, etc.)
            if ($SIS_FILES[$id]['error'] > 0) {
                $errors[] = ['success' => false, 'message' => $this->uploadPHPError($SIS_FILES[$id]['error'])];
                continue;
            }

            /*************** Verificaciones de Seguridad ***************/

            // 3. Bloquea archivos con extensiones ejecutables o peligrosas (.php, .exe, .sh, etc.)
            if (FilePolicy::forbiddenExtension($SIS_FILES[$id]['name'])) {
                $errors[] = ['success' => false, 'message' => 'Extensión de archivo no permitida por seguridad'];
                continue;
            }

            // 3b. Bloquea nombres sensibles/excluidos (.htaccess, .env, config.php, …)
            //     y cualquier archivo oculto (nombre que comienza con punto).
            if (FilePolicy::excludedName($SIS_FILES[$id]['name'])) {
                $errors[] = ['success' => false, 'message' => 'Nombre de archivo no permitido por seguridad'];
                continue;
            }

            // 4. Validación de Tipo MIME Real:
            // No confía en la extensión; inspecciona los bytes del archivo temporal (tmp_name)
            $allowedMimes = FilePolicy::allowedMimes($archivo['ValidarTipo']);
            $realMime     = $this->inspector->realMimeType($SIS_FILES[$id]['tmp_name']);

            if (!in_array($realMime, $allowedMimes, true)) {
                $errors[] = ['success' => false, 'message' => 'Tipo de archivo no permitido'];
                continue;
            }

            /*************** Verificaciones de Restricción ***************/

            // 5. Control de Peso: Convierte megabytes configurados a bytes para la comparación
            if ($SIS_FILES[$id]['size'] >= ($archivo['ValidarPeso'] * 1048576)) {
                $errors[] = ['success' => false, 'message' => 'Archivo excede el tamaño permitido'];
                continue;
            }

            // 6. Verificación de Duplicados en Almacenamiento:
            // Utiliza la abstracción de 'storage' para consultar si el nombre ya existe en el destino.
            // Se usa la extensión derivada del MIME real para que coincida con el nombre final
            // que handleNormalUpload() escribirá (evita falsos negativos por extensión del cliente).
            $nombreArchivo = $this->buildFileName($archivo, $SIS_FILES[$id]['name'], FilePolicy::extensionFromMime($realMime));
            $rutaRelativa  = $this->buildRelativePath($archivo) . $nombreArchivo;

            if ($this->storage->exists($rutaRelativa)) {
                $errors[] = ['success' => false, 'message' => 'El archivo ' . $SIS_FILES[$id]['name'] . ' ya existe en el servidor'];
            }
        }

        // Retorno final: se normaliza siempre a ['success' => bool, 'message' => string|true].
        return empty($errors)
            ? ['success' => true, 'message' => true]
            : ['success' => false, 'message' => implode('; ', array_column($errors, 'message'))];
    }

    /******************************************************************************************/
    /**
     * Gestiona la subida física de archivos al servidor o almacenamiento en la nube.
     *
     * @param array $SIS_FILES   Arreglo global $_FILES con los binarios.
     * @param array $arrArchivos Configuración de destino y reglas para cada archivo.
     * @param array $PostData    Datos adicionales (necesario para procesar Base64).
     * @return array {
     *      Nombres  : string  Fragmento SQL para columnas (ej: ", foto, cv").
     *      Archivos : string  Fragmento SQL para valores (ej: ", 'foto.jpg', 'doc.pdf'").
     *      Update   : string  Fragmento SQL para SET (ej: ", foto='foto.jpg', cv='doc.pdf'").
     *      success  : bool    Estado del proceso.
     * }
     */
    public function uploadFile(array $SIS_FILES, array $arrArchivos, array $PostData = []): array {

        // 'errors' acumula fallas reales de cada archivo; un campo simplemente
        // ausente (no enviado) NO se agrega aquí, para no bloquear el resto.
        $Data = [
            'Nombres'  => '',
            'Archivos' => '',
            'Update'   => '',
            'success'  => false,
            'message'  => '',
            'errors'   => [],
        ];

        // Itera sobre la configuración para decidir el método de subida.
        foreach ($arrArchivos as $archivo) {
            if ($archivo['Base64'] === true) {
                // Procesa strings Base64 (común en firmas o canvas)
                $this->handleBase64Upload($archivo, $PostData, $Data);
            } else {
                // Procesa subidas tradicionales (multipart/form-data)
                $this->handleNormalUpload($archivo, $SIS_FILES, $Data);
            }
        }

        // El resultado final depende de si algún archivo tuvo una falla real.
        if (!empty($Data['errors'])) {
            $Data['success'] = false;
            $Data['message'] = implode('; ', $Data['errors']);
        } else {
            $Data['success'] = true;
            $Data['message'] = $Data['message'] !== '' ? $Data['message'] : 'Archivo(s) subido(s) correctamente';
        }
        unset($Data['errors']);

        return $Data;
    }

    /*******************************************************************************************************************/
    /*                                              Métodos privados                                                   */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Maneja la subida de archivos en formato Base64.
     *
     * @param array $archivo   Configuración del archivo (identificador, nombre, ruta, etc.)
     * @param array $PostData  Datos recibidos (por ejemplo $_POST)
     * @param array &$Data     Referencia al array donde se almacenan los resultados
     */
    private function handleBase64Upload(array $archivo, array $PostData, array &$Data): void {

        // Obtiene el identificador único del archivo (clave en el POST)
        $id = $archivo['Identificador'];

        // Si no existe el dato en el POST, el campo simplemente no fue enviado — se omite.
        if (empty($PostData[$id])) {
            return;
        }

        // Base64 incrementa ~33% el tamaño del binario original; factor 1.37 agrega margen.
        $maxBase64Bytes = ($archivo['ValidarPeso'] ?? 10) * 1048576 * 1.37;
        if (strlen($PostData[$id]) > $maxBase64Bytes) {
            $Data['errors'][] = $id . ': archivo excede el tamaño permitido';
            return;
        }

        // Limpiar prefijo data URI dinámicamente (cualquier tipo MIME)
        $rawBase64 = $this->cleanBase64Payload($PostData[$id]);

        // Decodifica el contenido Base64 a binario (estricto)
        $dIMG = base64_decode($rawBase64, true);
        if ($dIMG === false) {
            $Data['errors'][] = $id . ': el contenido Base64 no es válido';
            return;
        }

        // Verificar MIME del binario decodificado antes de guardar
        $realMime = $this->inspector->bufferMimeType($dIMG);

        // Verificar si esta dentro de los Mime permitidos
        $allowed  = FilePolicy::allowedMimes($archivo['ValidarTipo'] ?? 'image');
        if (!in_array($realMime, $allowed, true)) {
            $Data['errors'][] = $id . ': tipo de archivo no permitido';
            return;
        }

        // Resolver extensión desde el MIME real detectado (no desde el cliente)
        $ext = FilePolicy::extensionFromMime($realMime);
        if ($ext === null) {
            $Data['errors'][] = $id . ': no se pudo determinar la extensión del archivo';
            return;
        }

        // Construir nombre final usando la extensión real del binario.
        $nombreArchivo = !empty($archivo['NombreArchivo'])
            ? $archivo['NombreArchivo'] . '.' . $ext
            : ($archivo['SufijoArchivo'] ?? '') . time() . '.' . $ext;

        // Sanitiza el nombre del archivo para evitar caracteres peligrosos
        $nombreArchivo = FilePathGuard::sanitizeFileName($nombreArchivo);

        // Construye la ruta completa donde se guardará el archivo
        $rutaRelativa  = $this->buildRelativePath($archivo);

        // Delegar guardado al método compartido
        $this->saveFileViaDriver($rutaRelativa, $nombreArchivo, $dIMG, true, $Data, $id);
    }

    /******************************************************************************************/
    /**
     * Maneja la subida de un archivo normal (multipart/form-data).
     *
     * @param array $archivo    Configuración del archivo (identificador, reglas, etc.)
     * @param array $SIS_FILES  Array de archivos subidos (equivalente a $_FILES)
     * @param array &$Data      Referencia al array donde se almacenan los resultados
     */
    private function handleNormalUpload(array $archivo, array $SIS_FILES, array &$Data): void {

        // Obtiene el identificador único del archivo (clave en $_FILES)
        $id = $archivo['Identificador'];

        // Si no existe el nombre del archivo, el campo simplemente no fue enviado — se omite.
        if (empty($SIS_FILES[$id]['name'])) {
            return;
        }

        // Verifica el MIME real del contenido (no confía en el header ni en la extensión).
        $realMime = $this->inspector->realMimeType($SIS_FILES[$id]['tmp_name']);

        // Lista blanca por categoría: el MIME debe pertenecer a las categorías permitidas.
        $allowed = FilePolicy::allowedMimes($archivo['ValidarTipo'] ?? 'image');
        if (!in_array($realMime, $allowed, true)) {
            $Data['errors'][] = $id . ': tipo de archivo no permitido';
            return;
        }

        // Deriva SIEMPRE la extensión final desde el MIME real detectado.
        $mimeExt = FilePolicy::extensionFromMime($realMime);
        if ($mimeExt === null) {
            $Data['errors'][] = $id . ': no se pudo determinar la extensión del archivo';
            return;
        }

        // Construye el nombre final reemplazando la extensión del cliente por la del MIME real.
        $nombreArchivo = FilePathGuard::sanitizeFileName(
            $this->buildFileName($archivo, $SIS_FILES[$id]['name'], $mimeExt)
        );

        // Construye la ruta donde se almacenará el archivo
        $rutaRelativa = $this->buildRelativePath($archivo);

        // Delegar guardado al método compartido (tmp_name como contenido)
        $this->saveFileViaDriver(
            $rutaRelativa,
            $nombreArchivo,
            $SIS_FILES[$id]['tmp_name'],
            false,
            $Data,
            $id
        );
    }

    /******************************************************************************************/
    /**
     * Guarda un archivo, verificando existencia y directorio previamente.
     * Centraliza la lógica común entre handleBase64Upload y handleNormalUpload.
     *
     * @param string $rutaRelativa  Ruta del directorio destino (con slash final)
     * @param string $nombreArchivo Nombre final del archivo
     * @param string $contenido     Contenido binario (Base64 decodificado) o ruta tmp_name
     * @param bool   $isBase64      true = contenido binario, false = move_uploaded_file
     * @param array  &$Data         Referencia al array de resultados
     * @param string $id            Identificador del archivo (clave en $Data)
     */
    private function saveFileViaDriver(string $rutaRelativa, string $nombreArchivo, string $contenido, bool $isBase64, array &$Data, string $id): void {

        // Verificación de seguridad de última línea: rechaza en la subida real los
        // nombres excluidos (.htaccess, .env, config.php, …) y cualquier archivo oculto.
        if (FilePolicy::excludedName($nombreArchivo)) {
            $Data['errors'][] = $id . ': nombre de archivo no permitido por seguridad';
            return;
        }

        // Evita sobrescribir archivos existentes en el servidor
        $fullRelative = ltrim($rutaRelativa . $nombreArchivo, '/');

        if ($this->storage->exists($fullRelative)) {
            $Data['errors'][] = $id . ': el archivo que intenta subir ya existe';
            return;
        }

        // Crea el "directorio" si el driver lo requiere (local y SFTP lo necesitan; S3/GCS no)
        $dirResult = $this->storage->createDirectory(ltrim($rutaRelativa, '/'));
        // No bloqueamos si el dir ya existía (success=false + "ya existe" es OK)
        if ($dirResult['success'] === false && !str_contains($dirResult['message'], 'ya existe')) {
            $Data['errors'][] = $id . ': el directorio donde intenta subir el archivo no existe';
            return;
        }

        // Sube el archivo vía driver
        $saved = $this->storage->upload($contenido, $fullRelative, $isBase64);

        // Registrar resultado en $Data
        if ($saved) {
            $this->appendToData($Data, $id, $nombreArchivo);
        } else {
            $Data['errors'][] = $id . ': no se pudo guardar el archivo "' . $nombreArchivo . '"';
        }
    }

    /******************************************************************************************/
    /**
     * Construye el nombre final del archivo según la configuración definida.
     *
     * Reglas:
     * - Si existe 'NombreArchivo', se usa como nombre base.
     * - Si existe 'SufijoArchivo', se antepone al nombre original.
     * - Si no hay configuración, se mantiene el nombre original.
     * - La extensión se toma del MIME real ($mimeExt) cuando se provee.
     *
     * @param array       $archivo      Configuración del archivo (NombreArchivo, SufijoArchivo, etc.)
     * @param string      $originalName Nombre original del archivo subido (incluye extensión)
     * @param string|null $mimeExt      Extensión derivada del MIME real (sin punto) o null si no aplica
     * @return string Nombre final del archivo
     */
    private function buildFileName(array $archivo, string $originalName, ?string $mimeExt = null): string {

        // Extensión enviada por el cliente (NO confiable: pht, php7, phps, shtml…)
        $clientExt = pathinfo($originalName, PATHINFO_EXTENSION);

        // Nombre original sin su extensión (base sobre la que se sustituye la extensión)
        $base = $clientExt !== ''
            ? substr($originalName, 0, -(strlen($clientExt) + 1))
            : $originalName;

        // Extensión efectiva: si se deriva del MIME real, se prioriza.
        $forced = $mimeExt !== null && $mimeExt !== '';
        $ext    = $forced ? $mimeExt : $clientExt;

        // Caso 1: Se define un nombre fijo para el archivo
        if (!empty($archivo['NombreArchivo'])) {
            return $ext !== ''
                ? $archivo['NombreArchivo'] . '.' . $ext
                : $archivo['NombreArchivo'];
        }

        // Caso 2: Se define un sufijo/prefijo para el archivo
        if (!empty($archivo['SufijoArchivo'])) {
            return $forced
                ? $archivo['SufijoArchivo'] . $base . ($ext !== '' ? '.' . $ext : '')
                : $archivo['SufijoArchivo'] . $originalName;
        }

        // Caso 3: No hay configuración adicional
        return $forced
            ? $base . ($ext !== '' ? '.' . $ext : '')
            : $originalName;
    }

    /******************************************************************************************/
    /**
     * Construye la ruta RELATIVA del archivo (sin la carpeta base del driver).
     *
     * Se mantiene la caché para evitar reprocesos dentro de la misma petición.
     *
     * @param array $archivo Configuración del archivo (incluye posible subcarpeta)
     * @return string Ruta final donde se guardará el archivo
     */
    private function buildRelativePath(array $archivo): string {

        $sub = $archivo['SubCarpeta'] ?? '';

        if (!isset(self::$pathCache[$sub])) {
            $path = '';
            if (!empty($archivo['SubCarpeta'])) {
                // Sanitiza la subcarpeta para evitar path traversal
                $path = FilePathGuard::sanitizePath($archivo['SubCarpeta']) . '/';
            }
            self::$pathCache[$sub] = $path;
        }

        return self::$pathCache[$sub];
    }

    /******************************************************************************************/
    /**
     * Agrega la información del archivo procesado al array de resultados.
     *
     * Estructura esperada en $Data:
     * - Nombres: lista de identificadores (columnas)
     * - Archivos: lista de valores (nombres de archivos)
     * - Update: expresiones tipo "col = 'valor'" para UPDATE SQL.
     *
     * @param array  &$Data          Array de resultados (por referencia)
     * @param string $id             Identificador del archivo (ej: nombre de columna)
     * @param string $NombreArchivo  Nombre final del archivo almacenado
     */
    private function appendToData(array &$Data, string $id, string $NombreArchivo): void {

        // Ejemplo: ",imagen,documento"
        $Data['Nombres']  .= ',' . $id;

        // Ejemplo: ",'file1.png','file2.pdf'"
        $Data['Archivos'] .= ",'" . $NombreArchivo . "'";

        // Ejemplo: ",imagen = 'file1.png',documento = 'file2.pdf'"
        $Data['Update']   .= ',' . $id . " = '" . $NombreArchivo . "'";
    }

    /******************************************************************************************/
    /**
     * Retorna el mensaje de error correspondiente a un código de subida de archivos en PHP.
     *
     * @param int $error Código de error de subida (UPLOAD_ERR_*)
     * @return string Mensaje descriptivo del error
     */
    private function uploadPHPError(int $error): string {

        return match ($error) {
            0 => 'No hay error, el archivo se cargó con éxito',
            1 => 'El archivo cargado supera la directiva upload_max_filesize en php.ini',
            2 => 'El archivo cargado excede la directiva MAX_FILE_SIZE del formulario HTML',
            3 => 'El archivo cargado solo se cargó parcialmente',
            4 => 'No se cargó ningún archivo',
            6 => 'Falta una carpeta temporal',
            7 => 'Error al escribir el archivo en el disco',
            8 => 'Una extensión PHP detuvo la carga del archivo',
            default => "Error desconocido al subir el archivo (código: $error)",
        };
    }

    /******************************************************************************************/
    /**
     * Limpia el prefijo data URI de un string Base64 sin importar el tipo.
     * Ejemplos de prefijos eliminados: `data:image/png;base64,`, `data:application/pdf;base64,`, …
     *
     * @param string $raw String Base64 crudo recibido del frontend
     * @return string     String Base64 limpio listo para decodificar
     */
    private function cleanBase64Payload(string $raw): string {

        // Elimina cualquier prefijo "data:...;base64," si existe
        if (str_contains($raw, ';base64,')) {
            $raw = substr($raw, strpos($raw, ';base64,') + 8);
        }
        // Normaliza espacios que los navegadores a veces insertan
        return str_replace(' ', '+', $raw);
    }
}
