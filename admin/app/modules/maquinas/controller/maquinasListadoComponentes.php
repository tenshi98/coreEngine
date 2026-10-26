<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class maquinasListadoComponentes extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $WidgetsCommon;
    private $WidgetsViews;
    private $WidgetsPreview;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'maquinasListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->WidgetsViews  = new UIWidgetsViews();
		$this->WidgetsPreview  = new UIWidgetsPreview();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Crear nuevo (raíz)
    /*******************************************************************/
    public function New($f3, $params){

        /************************************/
        // Se obtiene el ID
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMaquina,Nombre',
            'table'   => 'maquinas_listado',
            'join'    => '',
            'where'   => 'idMaquina = ?',
            'params'  => [$MaquinaID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true) {
            /************************************/
            // Máquina inexistente (enlace obsoleto)
            if (empty($rowData['data'])) {
                $this->showError(2, $f3, ['errors' => ['Registro inválido'], 'data' => []]);
                return;
            }

            /************************************/
            // Catálogo de tareas (fallo de BD → página de error)
            $Catalogo = $this->getCatalogoTareas();
            if ($Catalogo['status'] !== true) {
                $this->showError(2, $f3, $this->mergeResponses([$Catalogo]));
                return;
            }

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'   => $this->FormInputs,
                'Fnc_Codification' => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'    => $rowData['data'],
                'arrTareas'  => $Catalogo['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Componentes-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Crear nuevo subcomponente
    /*******************************************************************/
    public function NewSub($f3, $params){

        /************************************/
        // Se obtiene el ID (idPadre cifrado)
        $PadreID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PadreID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idComponentes,idMaquina,Nivel,Nombre',
            'table'   => 'maquinas_listado_componentes',
            'join'    => '',
            'where'   => 'idComponentes = ?',
            'params'  => [$PadreID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Si falla la consulta se despliega el error
        if ($rowData['status'] === false) {
            $result = $this->mergeResponses([$rowData]);
            $this->showError(2, $f3, $result);
            return;
        }

        /************************************/
        // Padre inexistente
        if (empty($rowData['data'])) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // RN-001: los subcomponentes no pueden exceder el nivel 5
        if ((int)$rowData['data']['Nivel'] >= 5) {
            Response::error('No se permite exceder los 5 niveles de profundidad', 400);
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        /************************************/
        // Catálogo de tareas (fallo de BD → página de error)
        $Catalogo = $this->getCatalogoTareas();
        if ($Catalogo['status'] !== true) {
            $this->showError(2, $f3, $this->mergeResponses([$Catalogo]));
            return;
        }

        /************************************/
        // Datos enviados a la pagina
        $f3->data = [
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            'Fnc_Codification' => $this->Codification,
            /*=========== Datos Consultados ===========*/
            'rowData'    => $rowData['data'],
            'arrTareas'  => $Catalogo['data'],
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Componentes-formNewSub.php');
    }

    /*******************************************************************/
    // Actualizar Listar (raíces del árbol)
    /*******************************************************************/
    public function UpdateList($f3, $params){

        /************************************/
        // Se obtiene el ID
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                idComponentes,
                Nombre,
                Codigo,
                Marca,
                Modelo,
                Serie,
                Nivel,
                NoUtilizable,
                (SELECT COUNT(idComponentes) FROM maquinas_listado_componentes h WHERE h.idPadre = maquinas_listado_componentes.idComponentes) AS Hijos',
            'table'   => 'maquinas_listado_componentes',
            'join'    => '',
            'where'   => 'idMaquina = ? AND idPadre IS NULL',
            'params'  => [$MaquinaID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams       = ['query' => $query];
        // Ejecuto la query
        $arrComponentes = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrComponentes['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification' => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrComponentes' => $arrComponentes['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Componentes-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrComponentes]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Hijos (lazy-load de un nivel)
    /*******************************************************************/
    public function Hijos($f3, $params){

        /************************************/
        // Se obtiene el ID
        $ComponenteID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ComponenteID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                idComponentes,
                Nombre,
                Codigo,
                Marca,
                Modelo,
                Serie,
                Nivel,
                NoUtilizable,
                (SELECT COUNT(idComponentes) FROM maquinas_listado_componentes h WHERE h.idPadre = maquinas_listado_componentes.idComponentes) AS Hijos',
            'table'   => 'maquinas_listado_componentes',
            'join'    => '',
            'where'   => 'idPadre = ?',
            'params'  => [$ComponenteID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams       = ['query' => $query];
        // Ejecuto la query
        $arrComponentes = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrComponentes['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification' => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrComponentes' => $arrComponentes['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Componentes-Hijos.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrComponentes]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se obtiene el ID
        $ComponenteID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ComponenteID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                maquinas_listado_componentes.idComponentes,
                maquinas_listado_componentes.idMaquina,
                maquinas_listado_componentes.idPadre,
                maquinas_listado_componentes.Nivel,
                maquinas_listado_componentes.NoUtilizable,
                maquinas_listado_componentes.Nombre,
                maquinas_listado_componentes.Codigo,
                maquinas_listado_componentes.Marca,
                maquinas_listado_componentes.Modelo,
                maquinas_listado_componentes.CodigoSaf,
                maquinas_listado_componentes.Numero,
                maquinas_listado_componentes.AnioFabricacion,
                maquinas_listado_componentes.Serie,
                maquinas_listado_componentes.NombreArchivo,
                maquinas_listado.Nombre AS NombreMaquina',
            'table'   => 'maquinas_listado_componentes',
            'join'    => 'LEFT JOIN maquinas_listado ON maquinas_listado.idMaquina = maquinas_listado_componentes.idMaquina',
            'where'   => 'maquinas_listado_componentes.idComponentes = ?',
            'params'  => [$ComponenteID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true) {
            /************************************/
            // Componente inexistente (enlace obsoleto)
            if (empty($rowData['data'])) {
                $this->showError(2, $f3, ['errors' => ['Registro inválido'], 'data' => []]);
                return;
            }

            /************************************/
            // Ruta jerárquica y tareas del componente (fallo de BD → página de error)
            $Ruta = $this->getRutaJerarquica($ComponenteID['data']);
            if ($Ruta['status'] !== true) {
                $this->showError(2, $f3, $this->mergeResponses([$Ruta]));
                return;
            }
            $TareasSel = $this->getTareasComponente($ComponenteID['data']);
            if ($TareasSel['status'] !== true) {
                $this->showError(2, $f3, $this->mergeResponses([$TareasSel]));
                return;
            }

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_WidgetsCommon' => $this->WidgetsCommon,
                'Fnc_WidgetsViews'  => $this->WidgetsViews,
                'Fnc_WidgetsPreview' => $this->WidgetsPreview,
                'Fnc_Codification'  => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'      => $rowData['data'],
                'arrRuta'      => $Ruta['data'],
                'arrTareasSel' => $TareasSel['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Componentes-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Editar
    /*******************************************************************/
    public function GetID($f3, $params){

        /************************************/
        // Se obtiene el ID
        $ComponenteID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ComponenteID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idComponentes,idMaquina,idPadre,Nivel,NoUtilizable,Nombre,Codigo,Marca,Modelo,CodigoSaf,Numero,AnioFabricacion,Serie,NombreArchivo',
            'table'   => 'maquinas_listado_componentes',
            'join'    => '',
            'where'   => 'idComponentes = ?',
            'params'  => [$ComponenteID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true) {
            /************************************/
            // Componente inexistente (enlace obsoleto)
            if (empty($rowData['data'])) {
                $this->showError(2, $f3, ['errors' => ['Registro inválido'], 'data' => []]);
                return;
            }

            /************************************/
            // Catálogo de tareas y selección previa (fallo de BD → página de error)
            $Catalogo = $this->getCatalogoTareas();
            if ($Catalogo['status'] !== true) {
                $this->showError(2, $f3, $this->mergeResponses([$Catalogo]));
                return;
            }
            $TareasSel = $this->getTareasComponente($ComponenteID['data']);
            if ($TareasSel['status'] !== true) {
                $this->showError(2, $f3, $this->mergeResponses([$TareasSel]));
                return;
            }

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'   => $this->FormInputs,
                'Fnc_Codification' => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'      => $rowData['data'],
                'arrTareas'    => $Catalogo['data'],
                'arrTareasSel' => array_column($TareasSel['data'], 'idTarea'),
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Componentes-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Insertar
    /*******************************************************************/
    public function Insert(){

        /************************************/
        // RN-001/RN-003: se resuelve el padre (si existe) para calcular
        // Nivel e idMaquina en el servidor; el idMaquina del POST se ignora
        $idPadre = ($_POST['idPadre'] ?? '');
        if ($idPadre !== '') {

            /************************************/
            // Se genera la query del padre
            $query = [
                'data'    => 'idComponentes,idMaquina,Nivel',
                'table'   => 'maquinas_listado_componentes',
                'join'    => '',
                'where'   => 'idComponentes = ?',
                'params'  => [$idPadre],
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            // Preparo los datos
            $xParams = ['query' => $query];
            // Ejecuto la query
            $rowData = $this->Base_GetByID($xParams);

            /************************************/
            // Error de BD
            if ($rowData['status'] === false) {
                $this->failOperation($rowData);
            }

            /************************************/
            // Padre inexistente
            if (empty($rowData['data'])) {
                Response::error('Registro inválido', 400);
            }

            /************************************/
            // RN-001: profundidad máxima de 5 niveles
            if ((int)$rowData['data']['Nivel'] >= 5) {
                Response::error('No se permite exceder los 5 niveles de profundidad', 400);
            }

            /************************************/
            // RN-003: idMaquina heredado del padre
            $_POST['idMaquina'] = $rowData['data']['idMaquina'];
            $_POST['Nivel']     = (int)$rowData['data']['Nivel'] + 1;
        } else {
            // Raíz: Nivel 1
            $_POST['Nivel'] = 1;
        }

        /************************************/
        // Contrato del widget formCheckInputGen_1: 2 = marcado, 1 = sin marcar
        $_POST['NoUtilizable'] = (($_POST['NoUtilizable'] ?? '') == 2) ? 1 : 0;

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idMaquina,idPadre,Nivel,NoUtilizable,Nombre,Codigo,Marca,Modelo,CodigoSaf,Numero,AnioFabricacion,Serie',
            'required'  => 'idMaquina,Nombre',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'maquinas_listado_componentes',
            'Post'      => $_POST,
            'files'     => [
                [
                    'Identificador' => 'NombreArchivo',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => $this->generarNombreArchivo('MaquinasComponentesDoc'),
                    'SufijoArchivo' => '',
                    'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music',
                    'ValidarPeso'   => 10,
                    'Base64'        => false
                ],
            ]
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Se conserva el id del nuevo componente
        $NuevoID = $Response['data'];

        /************************************/
        // RN-007: reemplazo del set de tareas (alta inicial)
        $Response = $this->saveTareas((int)$NuevoID);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();

        /************************************/
        // Si es un ID numérico, se envía con código 200 (OK)
        Response::success($NuevoID);

    }

    /*******************************************************************/
    // Editar por post (modificar datos y tareas; sin archivo en v1)
    /*******************************************************************/
    public function Update(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // RN-005: el idPadre y el Nivel no se modifican
        // Contrato del widget formCheckInputGen_1: 2 = marcado, 1 = sin marcar
        $_POST['NoUtilizable'] = (($_POST['NoUtilizable'] ?? '') == 2) ? 1 : 0;

        /************************************/
        // El componente debe existir (si no, se evitarían filas pivote
        // huérfanas en maquinas_componentes_tareas_rel)
        $query = [
            'data'    => 'idComponentes',
            'table'   => 'maquinas_listado_componentes',
            'join'    => '',
            'where'   => 'idComponentes = ?',
            'params'  => [($_POST['idComponentes'] ?? '')],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);
        // Error de BD
        if ($rowData['status'] === false) {
            $this->failOperation($rowData);
        }
        // Componente inexistente
        if (empty($rowData['data'])) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query (sin idPadre/Nivel: sin reparenting en v1;
        // sin 'files': la edición no admite reemplazo de archivo en v1)
        $query = [
            'data'      => 'idComponentes,idMaquina,NoUtilizable,Nombre,Codigo,Marca,Modelo,CodigoSaf,Numero,AnioFabricacion,Serie',
            'required'  => 'idComponentes,idMaquina,Nombre',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'maquinas_listado_componentes',
            'where'     => 'idComponentes',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // RN-007: se eliminan las relaciones previas del componente
        $Response = $this->queryExecuteRaw(
            'DELETE FROM maquinas_componentes_tareas_rel WHERE idComponente = ?',
            [(int)$_POST['idComponentes']]
        );

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // RN-007: se insertan las tareas nuevas
        $Response = $this->saveTareas((int)$_POST['idComponentes']);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar subárbol, relaciones y archivos
    /*******************************************************************/
    public function Delete(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataDelete);

        /************************************/
        // Se obtiene el ID
        $ComponenteID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idComponentes'] ?? '');
        if (!$this->isValidDecrypted($ComponenteID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // RN-004: BFS del subárbol (ids + archivos)
        $Subarbol = $this->getSubarbol((int)$ComponenteID['data']);
        if ($Subarbol['status'] !== true) {
            // Error de BD durante el recorrido: se revierte sin borrar nada
            $this->failOperation($Subarbol);
        }
        $arrNodos = $Subarbol['data'];
        $arrIds   = array_map('intval', array_column($arrNodos, 'idComponentes'));

        /************************************/
        // Si hay nodos, se limpia la pivote, los archivos y los nodos
        if (!empty($arrIds)) {
            /************************************/
            // Se generan los placeholders de la lista de ids
            $strIds = implode(',', array_fill(0, count($arrIds), '?'));

            /************************************/
            // Se eliminan las relaciones de tareas del subárbol
            $Response = $this->queryExecuteRaw(
                'DELETE FROM maquinas_componentes_tareas_rel WHERE idComponente IN ('.$strIds.')',
                $arrIds
            );

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->failOperation($Response);
            }

            /************************************/
            // Se borran los archivos de cada nodo (mientras el registro exista)
            foreach ($arrNodos as $nodo) {
                if (!empty($nodo['NombreArchivo'])) {
                    /************************************/
                    // Se genera la query
                    $query = [
                        'files'      => 'NombreArchivo',
                        'table'      => 'maquinas_listado_componentes',
                        'where'      => 'idComponentes',
                        'SubCarpeta' => '',
                        'Post'       => [
                            'idComponentes' => $nodo['idComponentes'],
                            'NombreArchivo' => $nodo['NombreArchivo'],
                        ]
                    ];
                    // Preparo los datos
                    $xParams     = ['query' => $query];
                    // Ejecuto la query
                    $ResponseDel = $this->Base_delFiles($xParams);

                    /************************************/
                    // Si falla la ejecucion, se revierte de inmediato
                    if ($ResponseDel['status'] === false) {
                        $this->failOperation($ResponseDel);
                    }
                }
            }

            /************************************/
            // Se eliminan los nodos del subárbol
            $Response = $this->queryExecuteRaw(
                'DELETE FROM maquinas_listado_componentes WHERE idComponentes IN ('.$strIds.')',
                $arrIds
            );

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->failOperation($Response);
            }
        }

        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data'] ?? []);

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => 'idMaquina,idPadre,AnioFabricacion',
            'ValidarEntero'              => 'idMaquina,idPadre,AnioFabricacion,NoUtilizable',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => 'Nombre',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [
                'Numero'    => 100,
                'Nombre'    => 255,
                'Codigo'    => 255,
                'Marca'     => 255,
                'Modelo'    => 255,
                'CodigoSaf' => 255,
                'Serie'     => 255,
            ],
            'ValidarPalabrasCensuradas' => 'Nombre,Codigo,Marca,Modelo,CodigoSaf,Serie',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => '',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => '',
            'ValidarEdadMinima'         => '',
            'ValidarJSON'               => '',
            'ValidarUUID'               => '',
            'ValidarIP'                 => '',
            'ValidarSoloAlfanumerico'   => '',
            'ValidarSoloLetras'         => '',
            'Post'                      => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

    /*******************************************************************/
    // Catálogo de tareas (orden de visualización)
    /*******************************************************************/
    private function getCatalogoTareas(){
        // Se genera la query
        $query = [
            'data'    => 'idTarea AS ID, Nombre',
            'table'   => 'maquinas_componentes_tareas',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Orden ASC, idTarea ASC'
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query (forma completa: status/data/error)
        return $this->Base_GetList($xParams);
    }

    /*******************************************************************/
    // Tareas relacionadas con un componente
    /*******************************************************************/
    private function getTareasComponente($idComponentes){
        // Se genera la query
        $query = [
            'data'    => 'maquinas_componentes_tareas_rel.idTarea, maquinas_componentes_tareas.Nombre',
            'table'   => 'maquinas_componentes_tareas_rel',
            'join'    => 'LEFT JOIN maquinas_componentes_tareas ON maquinas_componentes_tareas.idTarea = maquinas_componentes_tareas_rel.idTarea',
            'where'   => 'maquinas_componentes_tareas_rel.idComponente = ?',
            'params'  => [$idComponentes],
            'group'   => '',
            'having'  => '',
            'order'   => 'maquinas_componentes_tareas.Orden ASC, maquinas_componentes_tareas.idTarea ASC'
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query (forma completa: status/data/error)
        return $this->Base_GetList($xParams);
    }

    /*******************************************************************/
    // Ruta jerárquica desde la raíz hasta el nodo (BFS hacia arriba)
    /*******************************************************************/
    private function getRutaJerarquica($idComponentes){
        // Variables
        $arrRuta   = [];
        $idActual  = $idComponentes;
        // Recorro hacia arriba hasta la raíz (máx 5 niveles)
        for ($i = 0; $i < 5; $i++) {
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'idComponentes,Nombre,idPadre',
                'table'   => 'maquinas_listado_componentes',
                'join'    => '',
                'where'   => 'idComponentes = ?',
                'params'  => [$idActual],
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            // Preparo los datos
            $xParams = ['query' => $query];
            // Ejecuto la query
            $rowData = $this->Base_GetByID($xParams);

            /************************************/
            // Error de BD: se propaga para que la vista falle en lugar de truncar la ruta
            if ($rowData['status'] !== true) { return $rowData; }
            // Nodo inexistente o fin de la ruta
            if (empty($rowData['data'])) { break; }

            /************************************/
            // Se agrega al inicio de la ruta
            array_unshift($arrRuta, [
                'idComponentes' => $rowData['data']['idComponentes'],
                'Nombre'        => $rowData['data']['Nombre'],
            ]);

            /************************************/
            // Se llega a la raíz
            if (empty($rowData['data']['idPadre'])) { break; }

            /************************************/
            // Continuo con el padre
            $idActual = $rowData['data']['idPadre'];
        }
        // Retorno los datos (forma completa: status/data/error)
        return ['status' => true, 'data' => $arrRuta];
    }

    /*******************************************************************/
    // Subárbol completo (BFS hacia abajo: id + NombreArchivo)
    /*******************************************************************/
    private function getSubarbol($idComponentes){
        /************************************/
        // Se genera la query del nivel 0
        $query = [
            'data'    => 'idComponentes,NombreArchivo',
            'table'   => 'maquinas_listado_componentes',
            'join'    => '',
            'where'   => 'idComponentes = ?',
            'params'  => [$idComponentes],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $Response  = $this->Base_GetList($xParams);
        // Error de BD: se propaga para revertir la operación completa
        if ($Response['status'] !== true) { return $Response; }
        // Nodo inexistente: subárbol vacío (no-op)
        if (empty($Response['data'])) { return ['status' => true, 'data' => []]; }

        /************************************/
        // Acumulo los nodos por nivel (profundidad máx 5 ⇒ ≤5 iteraciones)
        $arrNodos    = $Response['data'];
        $arrActual   = [$idComponentes];
        $iteraciones = 0;
        while (!empty($arrActual) && $iteraciones < 5) {
            /************************************/
            // Se generan los placeholders de los ids del nivel actual
            $iteraciones++;
            $strIds     = implode(',', array_fill(0, count($arrActual), '?'));
            // Se genera la query del nivel siguiente
            $query = [
                'data'    => 'idComponentes,NombreArchivo',
                'table'   => 'maquinas_listado_componentes',
                'join'    => '',
                'where'   => 'idPadre IN ('.$strIds.')',
                'params'  => $arrActual,
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            // Preparo los datos
            $xParams  = ['query' => $query];
            // Ejecuto la query
            $Response = $this->Base_GetList($xParams);

            /************************************/
            // Error de BD: se propaga para revertir la operación completa
            if ($Response['status'] !== true) { return $Response; }
            // Fin del árbol
            if (empty($Response['data'])) { break; }

            /************************************/
            // Acumulo y preparo el siguiente nivel
            $arrNodos  = array_merge($arrNodos, $Response['data']);
            $arrActual = array_column($Response['data'], 'idComponentes');
        }
        // Retorno los datos
        return ['status' => true, 'data' => $arrNodos];
    }

    /*******************************************************************/
    // Reemplazo del set de tareas del componente (RN-007)
    /*******************************************************************/
    private function saveTareas($idComponente){
        // Variables
        $arrRows  = [];
        $arrTareas = ($_POST['idTareas'] ?? []);
        // Recorro los ids recibidos
        if (is_array($arrTareas)) {
            foreach ($arrTareas as $idTarea) {
                // Solo ids numéricos positivos, sin duplicados
                if (!is_numeric($idTarea)) { continue; }
                $idTarea = (int)$idTarea;
                if ($idTarea <= 0) { continue; }
                if (isset($arrRows[$idTarea])) { continue; }
                $arrRows[$idTarea] = ['idComponente' => $idComponente, 'idTarea' => $idTarea];
            }
        }

        /************************************/
        // Sin tareas seleccionadas: nada que insertar
        if (empty($arrRows)) {
            return ['status' => true, 'data' => []];
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idComponente,idTarea',
            'required'  => 'idComponente,idTarea',
            'table'     => 'maquinas_componentes_tareas_rel',
            'rows'      => array_values($arrRows),
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        return $this->Base_insertMultiple($xParams);
    }

    /*******************************************************************/
    // Ejecuta SQL crudo con parametros bindeados (pivote y subárbol)
    /*******************************************************************/
    private function queryExecuteRaw(string $sql, array $params = []){

        /************************************
         * ControllerBase::$queryBuilder es privado y Base_queryExecute no admite
         * parametros bindeados, por lo que se instancia un QueryBuilder propio
         * sobre la misma conexion: asi participa de la transaccion activa.
         */
        $QueryBuilder = new QueryBuilder();
        /************************************/
        // Ejecuto la query
        return $QueryBuilder->queryExecute($sql, $this->getDBConn(), false, false, $params);
    }

}
