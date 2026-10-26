<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Descubre los widgets de los modulos y resuelve su orden / estado de activacion.
 *
 * La configuracion se guarda en core_sistemas.Config_Principal_Widgets como JSON jerarquico:
 *   [
 *     {"key":"<Bloque = Menu_Name>","activo":1,"items":[
 *        {"key":"<Nombre del sub-widget>","activo":1}, ...
 *     ]}, ...
 *   ]
 * El orden del arreglo (de bloques y de items dentro de cada bloque) es el orden de
 * despliegue. NULL, '' o '[]' = orden por defecto (alfabetico por carpeta de modulo,
 * todos activos). Tambien se acepta el formato plano antiguo [{"key":"Menu|Nombre",
 * "activo":1}] y se migra automaticamente al jerarquico.
 *
 * Cada sub-widget puede declarar un 'Permiso' distinto a su 'Nombre' (ver discover()):
 * el Nombre identifica de forma unica el widget en la configuracion, mientras que el
 * Permiso es el Nombre de la transaccion en SESSION.arrMenu contra la que se valida el
 * acceso del usuario. Esto permite exponer varios widgets que comparten una sola
 * transaccion de permiso (por ejemplo, los 4 widgets de Campanas dependen todos de
 * "Campanas - Listado").
 *
 * Es estatica y no usa la Base de Datos, para poder invocarla desde la pantalla
 * principal sin abrir conexiones adicionales.
 */
class WidgetsOrderService {

    /*******************************************************************/
    // Separador de la clave de un widget (Menu_Name|Nombre) - solo formato antiguo
    /*******************************************************************/
    const KEY_SEPARATOR = '|';

    /*******************************************************************/
    // Se listan las clases controladoras de widgets
    /*******************************************************************/
    public static function discoverClasses(): array {

        /************************************/
        // Variable vacia
        $array = [];

        /************************************/
        // Carpeta raiz de los modulos (ruta ABSOLUTA, no depende del CWD)
        $modulesPath = realpath(__DIR__ . '/../modules');
        if($modulesPath===false || !is_dir($modulesPath)){
            error_log('[coreEngine][WidgetsOrderService] No se pudo resolver la ruta de modulos desde ' . __DIR__);
            return $array;
        }

        /************************************/
        // glob() devuelve false ante error (tipicamente open_basedir en produccion)
        $files = glob($modulesPath . '/*/widgets/*Widgets.php');
        if(!is_array($files)){
            error_log('[coreEngine][WidgetsOrderService] glob() no pudo listar widgets en ' . $modulesPath);
            return $array;
        }

        /************************************/
        // Orden alfabetico (orden por defecto)
        sort($files);

        /************************************/
        // Recorro los archivos encontrados
        foreach ($files as $file) {
            // Nombre de la clase (igual al nombre del archivo)
            $class = basename($file, '.php');
            // Se carga de forma explicita (el autoload de F3 resuelve rutas relativas al CWD)
            if(!class_exists($class, false)){
                require_once $file;
            }
            // Se valida que la clase exista y tenga loadWidgets
            if(!class_exists($class)){
                error_log('[coreEngine][WidgetsOrderService] Widget ignorado (clase inexistente): ' . $class);
                continue;
            }
            if(!method_exists($class, 'loadWidgets')){
                error_log('[coreEngine][WidgetsOrderService] Widget ignorado (sin loadWidgets): ' . $class);
                continue;
            }
            $array[] = $class;
        }

        /************************************/
        // Retorno los datos
        return $array;
    }

    /*******************************************************************/
    // Invoca loadWidgets() SIN ejecutar el constructor (evita abrir una
    // conexion PDO por cada modulo)
    /*******************************************************************/
    public static function loadWidgetsSinConexion($Class): array {

        /************************************/
        // Validacion temprana
        if(!is_string($Class) || $Class==='' || !class_exists($Class) || !method_exists($Class, 'loadWidgets')){
            return [];
        }

        /************************************/
        // Instancia sin constructor
        try {
            $reflection = new ReflectionClass($Class);
            $instancia  = $reflection->newInstanceWithoutConstructor();
            $resultado  = $instancia->loadWidgets();
        } catch (Throwable $e) {
            error_log('[coreEngine][WidgetsOrderService] ' . $Class . ' -> ' . $e->getMessage());
            return [];
        }

        /************************************/
        // Retorno datos
        return is_array($resultado) ? $resultado : [];
    }

    /*******************************************************************/
    // Se descubren todos los widgets agrupados por bloque, en el orden por defecto.
    // Retorna [BloqueKey => ['key','Menu_Name','items'=>[NombreKey => ['key','Nombre','Ruta','Permiso']]]]
    //
    // Cada entrada de Menu_Value puede ser:
    //   - string : la Ruta del widget (el Permiso a validar sera el propio Nombre).
    //   - array  : ['Ruta' => '...', 'Permiso' => '...'] para desacoplar el Nombre
    //              visual/identificador del widget frente al Nombre de la transaccion
    //              de permiso que se valida en SESSION.arrMenu.
    /*******************************************************************/
    public static function discover(): array {

        /************************************/
        // Variable vacia
        $blocks = [];

        /************************************/
        // Recorro los controladores
        foreach (self::discoverClasses() as $class) {
            $arrModules = self::loadWidgetsSinConexion($class);
            // Se valida el formato
            if(!is_array($arrModules) || !isset($arrModules['Menu_Name'], $arrModules['Menu_Value']) || !is_array($arrModules['Menu_Value'])){
                continue;
            }
            $Menu_Name = (string) $arrModules['Menu_Name'];
            // Se crea el bloque (un bloque por Menu_Name)
            if($Menu_Name===''){
                continue;
            }
            // Titulo del bloque (opcional): se muestra una sola vez sobre el bloque
            // en la pantalla principal. Si no se declara, se usa el Menu_Name.
            $Menu_Title = (isset($arrModules['Menu_Title']) && trim((string)$arrModules['Menu_Title'])!=='')
                ? (string) $arrModules['Menu_Title']
                : $Menu_Name;
            if(!isset($blocks[$Menu_Name])){
                $blocks[$Menu_Name] = [
                    'key'       => $Menu_Name,
                    'Menu_Name' => $Menu_Name,
                    'Titulo'    => $Menu_Title,
                    'items'     => [],
                ];
            } elseif(empty($blocks[$Menu_Name]['Titulo'])){
                $blocks[$Menu_Name]['Titulo'] = $Menu_Title;
            }
            // Recorro los sub-widgets del bloque
            foreach ($arrModules['Menu_Value'] as $nombre => $def) {
                $nombre = (string) $nombre;
                // Se ignora un sub-widget sin nombre
                if($nombre===''){
                    continue;
                }
                // Formato nuevo: array con Ruta y Permiso opcional
                if(is_array($def)){
                    $ruta   = isset($def['Ruta'])   ? (string) $def['Ruta']   : '';
                    $permiso= isset($def['Permiso']) ? (string) $def['Permiso']: $nombre;
                // Formato clasico: string con la Ruta (Permiso = Nombre)
                } else {
                    $ruta    = (string) $def;
                    $permiso = $nombre;
                }
                // Se ignoran duplicados (mismo identificador dentro del bloque)
                if(isset($blocks[$Menu_Name]['items'][$nombre])){
                    continue;
                }
                $blocks[$Menu_Name]['items'][$nombre] = [
                    'key'     => $nombre,
                    'Nombre'  => $nombre,
                    'Ruta'    => $ruta,
                    'Permiso' => ($permiso!=='') ? $permiso : $nombre,
                ];
            }
        }

        /************************************/
        // Se descartan bloques sin sub-widgets
        foreach ($blocks as $bKey => $block) {
            if(count($block['items'])===0){
                unset($blocks[$bKey]);
            }
        }

        /************************************/
        // Retorno los datos
        return $blocks;
    }

    /*******************************************************************/
    // Se decodifica la configuracion guardada. Retorna [] si no hay config
    // o si es invalida (=> orden por defecto).
    // Salida: [BloqueKey => ['key','activo','items'=>[ItemKey => ['key','activo']]]]
    // Acepta el formato jerarquico y tambien el formato plano antiguo
    // [{"key":"Menu|Nombre","activo":1}], que se migra agrupando por Menu.
    /*******************************************************************/
    public static function decode($json): array {

        /************************************/
        // Sin configuracion
        if(!is_string($json) || trim($json)===''){
            return [];
        }
        $arr = json_decode($json, true);
        if(!is_array($arr)){
            return [];
        }

        /************************************/
        // Se detecta el formato: jerarquico (con 'items') o plano (clave con separador)
        $isHierarchical = false;
        foreach ($arr as $item) {
            if(is_array($item) && isset($item['items']) && is_array($item['items'])){
                $isHierarchical = true;
                break;
            }
        }

        /************************************/
        // Variable de salida
        $result = [];

        /************************************/
        // Formato jerarquico
        if($isHierarchical){
            foreach ($arr as $block) {
                if(!is_array($block) || !isset($block['key']) || !is_string($block['key']) || $block['key']==='' || isset($result[$block['key']])){
                    continue;
                }
                $items = [];
                foreach ((isset($block['items']) && is_array($block['items'])) ? $block['items'] : [] as $it) {
                    if(is_array($it) && isset($it['key']) && is_string($it['key']) && $it['key']!=='' && !isset($items[$it['key']])){
                        $items[$it['key']] = ['key' => $it['key'], 'activo' => (isset($it['activo']) && (int)$it['activo']===0) ? 0 : 1];
                    }
                }
                $result[$block['key']] = [
                    'key'    => $block['key'],
                    'activo' => (isset($block['activo']) && (int)$block['activo']===0) ? 0 : 1,
                    'items'  => $items,
                ];
            }

        /************************************/
        // Formato plano antiguo: Menu|Nombre => se agrupa por bloque (Menu)
        } else {
            foreach ($arr as $item) {
                if(!is_array($item) || !isset($item['key']) || !is_string($item['key']) || $item['key']===''){
                    continue;
                }
                $parts = explode(self::KEY_SEPARATOR, $item['key'], 2);
                // Se ignoran claves sin separador (no validas en el formato plano)
                if(count($parts)!==2 || $parts[0]==='' || $parts[1]===''){
                    continue;
                }
                list($bloque, $nombre) = $parts;
                $activo = (isset($item['activo']) && (int)$item['activo']===0) ? 0 : 1;
                // El bloque hereda activo por defecto (el formato plano no tenia estado de bloque)
                if(!isset($result[$bloque])){
                    $result[$bloque] = ['key' => $bloque, 'activo' => 1, 'items' => []];
                }
                if(!isset($result[$bloque]['items'][$nombre])){
                    $result[$bloque]['items'][$nombre] = ['key' => $nombre, 'activo' => $activo];
                }
            }
        }

        /************************************/
        // Retorno los datos
        return $result;
    }

    /*******************************************************************/
    // Se combina la configuracion guardada con los widgets descubiertos.
    // - Respeta el orden de bloques y el orden de items dentro de cada bloque.
    // - Descarta bloques/items que ya no existen.
    // - Agrega al final los nuevos (activos).
    // Retorna ['items' => [bloques ordenados con sus items], 'hasNew' => bool, 'configured' => bool]
    /*******************************************************************/
    public static function merge(array $discovered, $json): array {

        /************************************/
        // Configuracion guardada
        $saved      = self::decode($json);
        $configured = count($saved) > 0;
        $items      = [];
        $hasNew     = false;

        /************************************/
        // Bloques guardados que aun existen, en su orden
        foreach ($saved as $bloque => $block) {
            if(!isset($discovered[$bloque])){
                continue;
            }
            $disc      = $discovered[$bloque];
            $subs      = [];
            $savedItems= isset($block['items']) && is_array($block['items']) ? $block['items'] : [];
            $usedItems = [];
            // Items descubiertos en el orden guardado
            foreach ($savedItems as $itemKey => $it) {
                if(!isset($disc['items'][$itemKey])){
                    continue;
                }
                $subs[] = $disc['items'][$itemKey] + ['activo' => $it['activo']];
                $usedItems[$itemKey] = true;
            }
            // Items nuevos (o no guardados) al final, activos
            foreach ($disc['items'] as $itemKey => $item) {
                if(!isset($usedItems[$itemKey])){
                    $subs[] = $item + ['activo' => 1];
                    $hasNew = true;
                }
            }
            $items[] = [
                'key'       => $disc['key'],
                'Menu_Name' => $disc['Menu_Name'],
                'Titulo'    => isset($disc['Titulo']) ? $disc['Titulo'] : $disc['Menu_Name'],
                'activo'    => $block['activo'],
                'items'     => $subs,
            ];
        }

        /************************************/
        // Bloques nuevos (o todos, si no hay configuracion), al final
        foreach ($discovered as $bloque => $disc) {
            if(isset($saved[$bloque])){
                continue;
            }
            $subs = [];
            foreach ($disc['items'] as $item){
                $subs[] = $item + ['activo' => 1];
            }
            $items[] = [
                'key'       => $disc['key'],
                'Menu_Name' => $disc['Menu_Name'],
                'Titulo'    => isset($disc['Titulo']) ? $disc['Titulo'] : $disc['Menu_Name'],
                'activo'    => 1,
                'items'     => $subs,
            ];
            // Un bloque nuevo solo cuenta como "nuevo" si ya habia configuracion
            $hasNew = true;
        }

        /************************************/
        // Retorno los datos
        return [
            'items'      => $items,
            'hasNew'     => ($configured && $hasNew),
            'configured' => $configured,
        ];
    }

    /*******************************************************************/
    // Se serializa la lista de bloques a JSON para guardar.
    // Solo persiste la jerarquia key/activo (Ruta, Permiso y Nombre viven en discover()).
    /*******************************************************************/
    public static function encode(array $blocks): string {
        $out = [];
        foreach ($blocks as $block) {
            if(!isset($block['key']) || !is_string($block['key']) || $block['key']===''){
                continue;
            }
            $items = [];
            foreach ((isset($block['items']) && is_array($block['items'])) ? $block['items'] : [] as $item) {
                if(!isset($item['key']) || !is_string($item['key']) || $item['key']===''){
                    continue;
                }
                $items[] = ['key' => $item['key'], 'activo' => ((int)($item['activo'] ?? 1)===0) ? 0 : 1];
            }
            $out[] = [
                'key'    => $block['key'],
                'activo' => ((int)($block['activo'] ?? 1)===0) ? 0 : 1,
                'items'  => $items,
            ];
        }
        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }

}
