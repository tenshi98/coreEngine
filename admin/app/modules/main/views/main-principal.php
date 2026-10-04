<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="row">

    <?php
    // Se resuelve la raiz de la aplicacion (carpeta que contiene public/) para no
    // depender del directorio de trabajo actual: este puede diferir entre localhost
    // y produccion, y eso hacia que require_once() no encontrara las vistas.
    $AppRoot = dirname(dirname(dirname(dirname(__DIR__))));  // .../admin

    /**************************************/
    // Cuadro de bienvenida (siempre visible)
    require_once(__DIR__ . '/main-principal-bienvenida.php');

    /**************************************/
    // Widget meteorologico (configurado en core_sistemas)
    // Se usa ?? para no generar warnings si la columna no existe en la BD de produccion
    if(($data['UserData']['Config_Principal_Meteo'] ?? 0)==2){
        require_once(__DIR__ . '/main-principal-meteo.php');
    }

    /**************************************/
    // Widget con noticias (configurado en core_sistemas)
    if(($data['UserData']['Config_Principal_Feed'] ?? 0)==2){
        require_once(__DIR__ . '/main-principal-feeds.php');
    }

    /**************************************/
    // Se cargan los widgets de los modulos.
    // Las rutas llegan como '../app/modules/<modulo>/widgets/<vista>.php' y antes se
    // resolvian respecto al CWD. Ahora se convierten a ruta absoluta usando la raiz
    // de la aplicacion y se validan con is_file() antes de incluirlas.
    foreach ((array)($data['MainViewData'] ?? []) as $value) {

        // Se ignoran entradas vacias o que no son string
        if(!is_string($value) || trim($value)===''){
            continue;
        }

        // Se arma la ruta absoluta a partir de la raiz de la aplicacion
        $RutaWidget = str_starts_with($value, '/')
            ? $value
            : $AppRoot . '/' . ltrim($value, './');

        // Validacion: el archivo debe existir antes de incluirlo
        if(!is_file($RutaWidget)){
            error_log('[coreEngine][main-principal] Widget no encontrado: ' . $RutaWidget);
            continue;
        }

        // Se incluye la vista del widget
        require_once($RutaWidget);
    }
    ?>
</div>
