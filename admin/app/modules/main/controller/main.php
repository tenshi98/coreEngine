<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class main extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $ServerServer;
    private $DataDate;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
		$this->ServerServer = new FunctionsServerServer();
		$this->DataDate     = new FunctionsDataDate();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Vista - Login
    /*******************************************************************/
    public function login($f3){

        /************************************/
        //Se cargan los datos de la plataforma
        $query = [
            'data'   => 'Sistema_idTema',
            'table'  => 'core_sistemas',
            'join'   => '',
            'where'  => 'idSistema = ?',
            'params'  => [1],
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        //Verifico si hay un dato
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $rowOpciones = $this->Base_GetByID($xParams);

        /************************************/
        // Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Iniciar Sesión',
            'PageDescription' => 'Iniciar Sesión',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*=========== Datos Consultados ===========*/
            'rowOpciones'    => $rowOpciones['data'],

        ];

        /************************************/
        // Se instancia la vista
        $view     = new View;
        echo $view->render('../app/templates/guest-header.php');
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/main-login.php');
        echo $view->render('../app/templates/guest-footer.php');

    }

    /*******************************************************************/
    // Vista - Restablecer contraseña (enlace por correo)
    /*******************************************************************/
    public function resetPassword($f3){

        /************************************/
        //Se cargan los datos de la plataforma
        $query = [
            'data'   => 'Sistema_idTema',
            'table'  => 'core_sistemas',
            'join'   => '',
            'where'  => 'idSistema = ?',
            'params'  => [1],
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        //Verifico si hay un dato
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $rowOpciones = $this->Base_GetByID($xParams);

        /************************************/
        // El token no se valida en GET (la validación es en POST): la vista se
        // renderiza siempre, sin revelar si el enlace es válido (RF-002a)
        $Token = $f3->get('GET.token');
        $Token = is_string($Token) ? $Token : '';

        /************************************/
        // Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Restablecer Contraseña',
            'PageDescription' => 'Restablecer Contraseña',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*=========== Datos Consultados ===========*/
            'rowOpciones'    => $rowOpciones['data'],
            /*===========    Token recibido    ===========*/
            'Token'          => $Token,
        ];

        /************************************/
        // Se instancia la vista
        $view     = new View;
        echo $view->render('../app/templates/guest-header.php');
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/main-reset-password.php');
        echo $view->render('../app/templates/guest-footer.php');

    }

    /*******************************************************************/
    // Recuperar Contraseña
    /*******************************************************************/
    public function error404($f3){

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Página de error',
            'PageDescription' => 'Página de error',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
        ];

        // Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/pages-error404.php'); // Header
    }

    /*******************************************************************/
    // pantalla principal
    /*******************************************************************/
    public function principal($f3){

        /************************************/
        // Se llaman los datos
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /************************************/
        // Variable vacia
        $MainViewData   = [];
        $MainViewBlocks = [];

        /************************************/
        // Se normaliza $arrMenu a array para tolerar sesiones vacias o corruptas
        $arrMenu = is_array($arrMenu) ? $arrMenu : [];

        /************************************/
        // Se cargan los widgets y se combinan con el orden configurado en core_sistemas
        // (Config_Principal_Widgets). Sin configuracion = orden por defecto (alfabetico).
        // Los widgets nuevos quedan al final y, si ya hay configuracion, se persisten.
        $query = [
            'data'   => 'Config_Principal_Widgets',
            'table'  => 'core_sistemas',
            'join'   => '',
            'where'  => 'idSistema = ?',
            'params' => [1],
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        $xParams    = ['query' => $query];
        $rowWidgets = $this->Base_GetByID($xParams);
        // Si la columna aun no existe en la BD (query fallida) se usa el orden por defecto
        $jsonWidgets = ($rowWidgets['status']===true && is_array($rowWidgets['data'])) ? ($rowWidgets['data']['Config_Principal_Widgets'] ?? null) : null;
        $merged      = WidgetsOrderService::merge(WidgetsOrderService::discover(), $jsonWidgets);

        /************************************/
        // Si hay widgets nuevos, se guarda el orden resultante (quedan al final)
        if($rowWidgets['status']===true && $merged['hasNew']===true){
            $Post   = ['idSistema' => 1, 'Config_Principal_Widgets' => WidgetsOrderService::encode($merged['items'])];
            $query  = [
                'data'     => 'idSistema,Config_Principal_Widgets',
                'required' => 'idSistema',
                'unique'   => '',
                'encode'   => '',
                'table'    => 'core_sistemas',
                'where'    => 'idSistema',
                'Post'     => $Post,
            ];
            // Se inicia la transacción
            $this->Base_transactionBegin();
            $xParams = ['DataCheck' => [], 'query' => $query, 'novalidate' => true];
            $saved   = $this->Base_update($xParams);
            if($saved['status']===false){
                // Se revierte la transaccion activa (no-op si no hay transaccion)
                $this->Base_transactionRollback();
                // Se reporta el error
                error_log('[coreEngine][principal] No se pudo guardar el orden de widgets: ' . ($saved['error'] ?? ''));
            } else {
                $this->Base_transactionCommit();
            }
        }

        /************************************/
        // Se recorren los bloques y sus sub-widgets en orden: solo bloques y widgets
        // activos, y con permiso del usuario.
        //   - Bloque activo (switch maestro) && el usuario tiene acceso a la seccion.
        //   - Sub-widget activo (switch individual) && el permiso (Permiso) existe en el menu.
        foreach ($merged['items'] as $block) {
            // Bloque desactivado (switch maestro)
            if((int)$block['activo']===0){
                continue;
            }
            // Permiso de seccion: el bloque debe existir en el menu del usuario
            if(empty($arrMenu[$block['Menu_Name']])){
                continue;
            }
            // Se recorren los sub-widgets del bloque
            $RutasBloque = [];
            foreach ($block['items'] as $widget) {
                // Widget desactivado (switch individual)
                if((int)($widget['activo'] ?? 1)===0){
                    continue;
                }
                // Permiso: el Nombre de la transaccion (Permiso) debe existir en la seccion
                $Permiso = !empty($widget['Permiso']) ? $widget['Permiso'] : $widget['Nombre'];
                foreach ($arrMenu[$block['Menu_Name']] as $asd) {
                    if(isset($asd['Nombre']) && $asd['Nombre']===$Permiso){
                        $RutasBloque[]  = $widget['Ruta']; //Se guardan las URL
                        $MainViewData[] = $widget['Ruta'];
                        break;
                    }
                }
            }
            // Si el bloque aporta al menos un sub-widget, se registra el bloque
            // con su titulo para renderizarlo una sola vez como unidad
            $RutasBloque = array_values(array_unique($RutasBloque));
            if(count($RutasBloque) > 0){
                $MainViewBlocks[] = [
                    'key'    => $block['key'],
                    'Titulo' => !empty($block['Titulo']) ? $block['Titulo'] : $block['Menu_Name'],
                    'Rutas'  => $RutasBloque,
                ];
            }
        }

        //Se filtran para obtener datos unicos
        $MainViewData = array_values(array_unique($MainViewData));

        /************************************/
        // Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Principal',
            'PageDescription' => 'Principal',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'        => $this->getUserData($f3),
            /*===========   Funcionalidad   ===========*/
            'Fnc_ServerServer'    => $this->ServerServer,
            'Fnc_DataDate'        => $this->DataDate,
            'Fnc_WidgetsCommon'   => new UIWidgetsCommon(),
            'Fnc_WidgetsFeed'     => new UIWidgetsFeed(),
            'Fnc_WidgetsMedia'    => new UIWidgetsMedia(),
            'Fnc_WidgetsWeather'  => new UIWidgetsWeather(),
            /*=========== Datos Consultados ===========*/
            'MainViewData'    => $MainViewData,
            'MainViewBlocks'  => $MainViewBlocks,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/main-principal.php');
    }

    /*******************************************************************/
    // Se listan los controladores (la logica vive en WidgetsOrderService)
    /*******************************************************************/
    public function arrayWidgetViews(){
        return WidgetsOrderService::discoverClasses();
    }

}
