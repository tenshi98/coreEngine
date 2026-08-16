<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class CheckData
 *
 * Orquesta la validación de datos de formularios a través de un array de configuración declarativo.
 *
 * Responsabilidades:
 *  - Validar campos obligatorios (emptyData)
 *  - Encriptar campos sensibles antes de procesarlos (encode)
 *  - Ejecutar reglas de validación dinámicas (email, rut, fecha, largo, etc.)
 *  - Validar coincidencias entre pares de campos (passwords, confirmaciones)
 *
 * Delega la lógica de cada validación a servicios especializados:
 *  - FunctionsDataValidations      : validaciones de formato (email, rut, fecha, etc.)
 *  - FunctionsDataText             : validaciones de contenido textual (palabras censuradas)
 *  - FunctionsSecurityCodification : encriptación/desencriptación de datos
 *  - FunctionsCommonData           : utilidades de parseo de strings
 *
 * Uso típico:
 * @see CheckData::checkingData()
 */
class CheckData{

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $DataValidations;
	private $DataText;
	private $Codification;
	private $CommonData;

	/******************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
		$this->DataText        = new FunctionsDataText();
		$this->Codification    = new FunctionsSecurityCodification();
		$this->CommonData      = new FunctionsCommonData();

	}

    /******************************************************************/
    /**
     * Valida un conjunto de datos de formulario según reglas declaradas en un array de configuración.
     * * Este método implementa un motor de validación robusto con tres fases críticas:
     * 1. Control de presencia (Early Return para campos obligatorios).
     * 2. Validación de tipos, formatos y reglas de negocio dinámicas.
     * 3. Encriptación de datos sensibles para persistencia segura.
     *
     * El método sigue un pipeline ordenado:
     * 1. Valida campos obligatorios → corta inmediatamente si hay vacíos (Early Return)
     * 2. Encripta los campos indicados en 'encode' antes de continuar
     * 3. Ejecuta todas las reglas de validación restantes sobre los campos indicados
     *
     * Notas de comportamiento:
     * - 'emptyData' usa Early Return: si hay campos vacíos, no se ejecutan las demás validaciones.
     * - 'encode' muta $config['Post'] con los valores encriptados; los validadores posteriores operan sobre el valor ya encriptado.
     * - Los campos que no existan en 'Post' o estén vacíos son ignorados por las validaciones de formato (no generan error si no son obligatorios).
     * - 'ValidarCoincidencias' ignora el par si ambos campos están vacíos.
     *
     * @param array $config Arreglo con reglas (ValidarEmail, ValidarRut, etc.) y los datos 'Post'.
     * @return array|bool Retorna un arreglo de errores con clave 'message' o false si la validación es exitosa.
     *
	 * @example
	 * ```php
	 * $DataCheck = [
     *   'emptyData'                 => 'password',    -> Validar campos obligatorios: no pueden estar vacíos
     *   'encode'                    => 'oldPassword', -> Codificar los datos sensibles antes de procesarlos
     *   'ValidarEmail'              => 'email',       -> Validar formato de email
     *   'ValidarNumero'             => '',            -> Validar si es un número
     *   'ValidarEntero'             => '',            -> Validar si es un número entero
     *   'ValidarRut'                => 'Rut',         -> Validar si es un Rut chileno válido
     *   'ValidarPatente'            => '',            -> Validar si es una patente vehicular
     *   'ValidarFecha'              => 'fNacimiento', -> Validar si es una fecha válida
     *   'ValidarHora'               => '',            -> Validar si es una hora válida
     *   'ValidarURL'                => '',            -> Validar si es una URL válida
     *   'ValidarLargoMinimo'        => '',            -> Validar longitud mínima de caracteres
     *   'ValidarLargoMinimoN'       => 3,             -> Número mínimo de caracteres
     *   'ValidarLargoMaximo'        => '',            -> Validar longitud máxima de caracteres
     *   'ValidarLargoMaximoN'       => 255,           -> Número máximo de caracteres
     *   'ValidarPalabrasCensuradas' => '',            -> Validar si contiene palabras censuradas
     *   'ValidarEspaciosVacios'     => 'password',    -> Validar que no existan espacios en blanco
     *   'ValidarMayusculas'         => '',            -> Validar que no existan letras mayúsculas
     *   'ValidarCoincidencias'      => 'mainPassword-oldPassword', -> Validar coincidencia entre pares de campos
     *
     *   // --- Nuevas validaciones ---
     *   'ValidarDominioEmail'      => 'email',              -> Validar dominio de email: comprobar que el dominio existe con checkdnsrr()
     *   'ValidarPasswordSegura'    => 'password',           -> Validar contraseñas seguras: mínimo 8 caracteres, mayúscula, número y símbolo
     *   'ValidarFechaRango'        => 'fNacimiento',        -> Validar rango de fechas: entre 1900 y la fecha actual
     *   'ValidarEdadMinima'        => 'fNacimiento',        -> Validar edad mínima: calcular edad y verificar que sea >= 18 años
     *   'ValidarJSON'              => 'jsonData',           -> Validar que el campo contenga un JSON válido (json_decode)
     *   'ValidarUUID'              => 'uuid',               -> Validar que el campo sea un UUID válido
     *   'ValidarIP'                => 'ipAddress',          -> Validar dirección IP con filter_var
     *   'ValidarSoloAlfanumerico'  => 'username',           -> Validar que el campo contenga solo caracteres alfanuméricos
     *   'ValidarSoloLetras'        => 'firstName,lastName', -> Validar que el campo contenga solo letras
     *
     *   // --- Datos del formulario ---
     *   'Post' => $DataPOST, -> Datos entregados por el formulario
     * ];
	 * ```
	 *
     */
    public function checkingData(array $config): array {

        /******************************************************************/
        // Variables iniciales
        $errors   = [];
        $postData = $config['Post'] ?? [];

        /******************************************************************/
        // PASO 1: Validación de campos obligatorios (Presencia)
        /**
         * Implementa 'Early Return': Si detecta que un campo requerido está vacío,
         * detiene el proceso y retorna los errores inmediatamente. Esto ahorra
         * procesamiento al no ejecutar reglas de formato sobre datos inexistentes.
         */
        if (!empty($config['emptyData'])) {
            $requiredFields = $this->CommonData->parseDataCommas($config['emptyData']);
            foreach ($requiredFields as $field) {
                // Verificación estricta: debe existir en el array y no ser un string vacío
                if (!isset($postData[$field]) || $postData[$field] === '') {
                    $errors[] = ["message" => "No ha llenado el campo obligatorio: $field."];
                }
            }
            // Si existen errores, retornar respuesta estándar
            if (!empty($errors)) {
                return [
                    'status' => false,
                    'error'  => $errors
                ];
            }
        }

        /******************************************************************/
        // PASO 2: Ejecución de reglas de validación dinámicas (Formato y Lógica)
        /**
         * Recorre un mapa de reglas predefinidas. Cada regla se activa solo si
         * está presente en el array de configuración $config.
         */

        // Se obtienen las reglas
        $rules = $this->getValidationRules();

        // Recorro las reglas
        foreach ($rules as $key => $rule) {
            // Saltar regla si no fue declarada en la configuración actual
            if (empty($config[$key])) { continue; }
            // Separacion
            $fields = $this->CommonData->parseDataCommas($config[$key]);
            foreach ($fields as $field) {

                // Caso Especial: Comparación de pares (Passwords, Emails de confirmación)
                if ($key === 'ValidarCoincidencias') {
                    if (!$this->validateMatches($field, $postData)) {
                        $errors[] = ["message" => $rule['errorMsg']];
                    }
                    continue;
                }

                // Obtener el valor; si es opcional y está vacío, se omite la regla de formato
                $value = $postData[$field] ?? null;
                if ($value === null || $value === '') { continue; }

                // Ejecución técnica de la validación (Regex, filtros PHP, lógica interna)
                if (!$this->executeValidation($rule, $value, $config)) {
                    $errorMsg = $rule['errorMsg'];

                    // Inyección de parámetros dinámicos en el mensaje (ej: "mínimo 5 carácteres")
                    if (isset($rule['param'])) {
                        $errorMsg .= " ({$config[$rule['param']]} carácteres)";
                    }
                    $errors[] = ["message" => "El dato en $field " . $errorMsg];
                }
            }
        }

        // Si se acumularon errores de formato, se retornan antes de proceder a la encriptación
        // Si existen errores de validación
        if (!empty($errors)) {
            return [
                'status' => false,
                'error'  => $errors
            ];
        }

        /******************************************************************/
        // PASO 3: Encriptación de campos sensibles (Seguridad)
        /**
         * Una vez que los datos son válidos, se procede a cifrar aquellos marcados
         * en 'encode'. Esto asegura que la información sensible viaje protegida
         * hacia la base de datos o siguientes capas del sistema.
         */
        if (!empty($config['encode'])) {
            $encodeFields = $this->CommonData->parseDataCommas($config['encode']);
            foreach ($encodeFields as $field) {
                if (!empty($postData[$field])) {
                    // Sobrescribe el valor original por el valor cifrado usando una llave segura
                    $config['Post'][$field] = $this->Codification->encryptDecrypt(
                        'encrypt',
                        $postData[$field],
                        ConfigToken::ENCODE_KEYS["KEY_1"]
                    );
                }
            }
        }

        // Retorno final: false indica "Sin Errores" (Lógica inversa para compatibilidad con condicionales)
        // Retorno exitoso
        return [
            'status' => true,
            'error'  => []
        ];
    }

    /*******************************************************************************************************************/
    /*                                                                                                                 */
    /*                                                Métodos privados                                                 */
    /*                                                                                                                 */
    /*******************************************************************************************************************/

    /******************************************************************/
    /**
     * Retorna el mapa de reglas de validación disponibles.
     *
     * Cada entrada del mapa define cómo se ejecuta una regla:
     *
     * Tipos de provider:
     *  - 'validations' : delega a FunctionsDataValidations. Usa 'method' para llamar el método
     *                    correspondiente. Si define 'param', extrae el valor numérico de $config
     *                    y lo pasa como segundo argumento (ej: largo mínimo/máximo).
     *  - 'text'        : delega a FunctionsDataText. El método debe retornar 0 para ser válido
     *                    (ej: contarPalabrasCensuradas → 0 palabras encontradas = válido).
     *  - 'internal'    : lógica inline definida como closure en 'logic'. No depende de servicios externos.
     *  - 'special'     : indica que la regla tiene flujo propio en checkingData() y no pasa
     *                    por executeValidation() (ej: ValidarCoincidencias).
     *
     * Estructura de cada entrada:
     *  - provider  : string         Tipo de proveedor que ejecuta la validación
     *  - method    : string         (provider: validations|text) Nombre del método a invocar
     *  - param     : string         (opcional) Clave en $config que contiene el parámetro numérico
     *  - logic     : Closure        (provider: internal) Función inline que recibe el valor y retorna bool
     *  - errorMsg  : string         Mensaje de error parcial (se prefija con "El dato {campo} ingresado ")
     *
     * @return array<string, array>  Mapa indexado por clave de configuración
     */
    private function getValidationRules(): array {
        return [
            // --- Reglas existentes ---
            'ValidarEmail' => [
                'provider' => 'validations',
                'method'   => 'validarEmail',
                'errorMsg' => 'no tiene un formato de email correcto'
            ],
            'ValidarNumero' => [
                'provider' => 'validations',
                'method'   => 'validarNumero',
                'errorMsg' => 'no es validado como un número'
            ],
            'ValidarEntero' => [
                'provider' => 'validations',
                'method'   => 'validarEntero',
                'errorMsg' => 'no es un número entero'
            ],
            'ValidarRut' => [
                'provider' => 'validations',
                'method'   => 'validarRut',
                'errorMsg' => 'no es un Rut válido'
            ],
            'ValidarPatente' => [
                'provider' => 'validations',
                'method'   => 'validarPatente',
                'errorMsg' => 'no es una patente válida'
            ],
            'ValidarFecha' => [
                'provider' => 'validations',
                'method'   => 'validarFecha',
                'errorMsg' => 'no es una fecha válida'
            ],
            'ValidarHora' => [
                'provider' => 'validations',
                'method'   => 'validarHora',
                'errorMsg' => 'no es una hora válida'
            ],
            'ValidarURL' => [
                'provider' => 'validations',
                'method'   => 'validarURL',
                'errorMsg' => 'no es una URL válida'
            ],
            'ValidarLargoMinimo' => [
                'provider' => 'validations',
                'method'   => 'validarLargoMinimo',
                'param'    => 'ValidarLargoMinimoN',
                'errorMsg' => 'no tiene el mínimo de caracteres requerido'
            ],
            'ValidarLargoMaximo' => [
                'provider' => 'validations',
                'method'   => 'validarLargoMaximo',
                'param'    => 'ValidarLargoMaximoN',
                'errorMsg' => 'no tiene el máximo de caracteres requerido'
            ],
            'ValidarPalabrasCensuradas' => [
                'provider' => 'text',
                'method'   => 'contarPalabrasCensuradas',
                'errorMsg' => 'contiene palabras no permitidas'
            ],
            'ValidarEspaciosVacios' => [
                'provider' => 'internal',
                'logic'    => fn($v) => !str_contains((string)$v, ' '),
                'errorMsg' => 'contiene espacios vacíos'
            ],
            'ValidarMayusculas' => [
                'provider' => 'internal',
                'logic'    => fn($v) => strtolower((string)$v) === (string)$v,
                'errorMsg' => 'contiene mayúsculas'
            ],
            'ValidarCoincidencias' => [
                'provider' => 'special',
                'errorMsg' => 'Los datos ingresados no coinciden'
            ],

            // --- Reglas nuevas ---
            'ValidarPasswordSegura' => [
                'provider' => 'internal',
                'logic'    => fn($v) => preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $v),
                'errorMsg' => 'no cumple con los requisitos de seguridad (mínimo 8 caracteres, mayúscula, número y símbolo)'
            ],
            'ValidarFechaRango' => [
                'provider' => 'internal',
                'logic'    => fn($v) => strtotime($v) >= strtotime('1900-01-01') && strtotime($v) <= time(),
                'errorMsg' => 'está fuera del rango permitido'
            ],
            'ValidarEdadMinima' => [
                'provider' => 'internal',
                'logic'    => fn($v) => (time() - strtotime($v)) / (365*24*60*60) >= 18,
                'errorMsg' => 'indica que el usuario es menor de edad'
            ],
            'ValidarJSON' => [
                'provider' => 'internal',
                'logic'    => fn($v) => json_decode($v) !== null,
                'errorMsg' => 'no es un JSON válido'
            ],
            'ValidarUUID' => [
                'provider' => 'internal',
                'logic'    => fn($v) => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $v),
                'errorMsg' => 'no es un UUID válido'
            ],
            'ValidarIP' => [
                'provider' => 'internal',
                'logic'    => fn($v) => filter_var($v, FILTER_VALIDATE_IP) !== false,
                'errorMsg' => 'no es una dirección IP válida'
            ],
            'ValidarDominioEmail' => [
                'provider' => 'internal',
                'logic'    => fn($v) => str_contains($v, '@') && checkdnsrr(explode('@', $v)[1], 'MX'),
                'errorMsg' => 'el dominio del email no existe'
            ],
            'ValidarSoloAlfanumerico' => [
                'provider' => 'internal',
                'logic'    => fn($v) => ctype_alnum($v),
                'errorMsg' => 'contiene caracteres no alfanuméricos'
            ],
            'ValidarSoloLetras' => [
                'provider' => 'internal',
                'logic'    => fn($v) => ctype_alpha($v),
                'errorMsg' => 'contiene caracteres que no son letras'
            ],
        ];
    }

    /******************************************************************/
    /**
     * Ejecuta la validación de un campo según el provider definido en su regla.
     *
     * Actúa como dispatcher: según el tipo de provider, delega la ejecución
     * al servicio correspondiente o evalúa la lógica inline.
     *
     * Providers soportados:
     *  - 'validations' : llama a $this->DataValidations->{method}($value) o
     *                    $this->DataValidations->{method}($value, $config[param]) si tiene param.
     *  - 'text'        : llama a $this->DataText->{method}($value) y valida que retorne 0.
     *  - 'internal'    : evalúa el closure $rule['logic']($value) directamente.
     *  - default       : retorna true (regla desconocida = sin restricción).
     *
     * @param  array  $rule    Definición de la regla obtenida desde getValidationRules()
     * @param  mixed  $value   Valor del campo a validar (nunca null ni vacío en este punto)
     * @param  array  $config  Array de configuración completo (necesario para extraer parámetros)
     * @return bool            true si el valor es válido, false si falla la validación
     */
    private function executeValidation(array $rule, mixed $value, array $config): bool {
        // Retorno datos
        return match ($rule['provider']) {
            'validations' => isset($rule['param'])
                             ? (bool)$this->DataValidations->{$rule['method']}($value, $config[$rule['param']])
                             : (bool)$this->DataValidations->{$rule['method']}($value),
            'text'        => $this->DataText->{$rule['method']}($value) === 0,
            'internal'    => $rule['logic']($value),
            default       => true,
        };
    }

    /******************************************************************/
    /**
     * Valida que todos los campos de un par o grupo coincidan entre sí.
     *
     * Recibe un patrón de tipo 'campo1-campo2' (separados por guion),
     * extrae los valores de $postData y verifica que sean idénticos.
     *
     * Comportamiento en casos borde:
     *  - Si ninguno de los campos tiene valor, se considera válido (no hay qué comparar).
     *  - Si solo uno tiene valor y el otro está vacío, se considera válido
     *    (la obligatoriedad la controla 'emptyData', no esta regla).
     *  - Solo falla si hay al menos dos valores distintos entre sí.
     *
     * @example
     *  'ValidarCoincidencias' => 'password-rePassword,email-reEmail'
     *  → valida que password === rePassword y email === reEmail por separado
     *
     * @param  string  $fieldPattern  Par de campos separados por guion (ej: 'pass-rePass')
     * @param  array   $postData      Datos del formulario
     * @return bool                   true si todos los valores presentes son iguales, false si difieren
     */
    private function validateMatches(string $fieldPattern, array $postData): bool {

        // Separar el patrón en campos individuales (ej: 'pass-rePass' → ['pass', 'rePass'])
        $fieldsToCompare = $this->CommonData->parseDataSeparator($fieldPattern);
        $values          = [];

        // Recopilar solo los valores que existan y no estén vacíos
        foreach ($fieldsToCompare as $f) {
            if (isset($postData[$f]) && $postData[$f] !== '') {
                $values[] = $postData[$f];
            }
        }

        // Válido si no hay valores que comparar, o si todos los valores son idénticos
        return count($values) === 0 || count(array_unique($values)) === 1;
    }

}
