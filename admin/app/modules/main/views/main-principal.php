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
    // Aviso de contraseña desactualizada (PasswordUltimoCambio vs. fecha actual).
    // - Sin fecha registrada: nunca cambió la contraseña.
    // - Con más de 60 días desde el último cambio: toca renovarla.
    // No bloquea el acceso: sólo informa y da un botón hacia /perfil, igual que
    // el aviso de PasswordForzarCambio (ese caso redirige /principal a /perfil).
    // Sesiones activas anteriores a este cambio no traen la clave: se omite el aviso
    // hasta el proximo login (o hasta que updateSession refresque DataInfo).
    $PasswordDataOk       = array_key_exists('PasswordUltimoCambio', (array)($data['UserData'] ?? []));
    $PasswordForzada      = (int)($data['UserData']['PasswordForzarCambio'] ?? 0) === 1;
    $PasswordUltimoCambio = trim((string)($data['UserData']['PasswordUltimoCambio'] ?? ''));
    $PasswordDiasLimite   = 60;
    $PasswordSinFecha     = ($PasswordUltimoCambio === '');
    $TsUltimoCambio       = $PasswordSinFecha ? false : strtotime($PasswordUltimoCambio);
    $PasswordVencida      = (!$PasswordSinFecha && $TsUltimoCambio !== false)
                          && ((time() - $TsUltimoCambio) > ($PasswordDiasLimite * 86400));

    // Se emite el aviso sólo si corresponde
    if ($PasswordDataOk && !$PasswordForzada && ($PasswordVencida || $PasswordSinFecha)) {
        // Texto distinto según si hay fecha registrada o no
        $PasswordTexto = $PasswordSinFecha
            ? 'Nunca has cambiado tu contraseña. Te recomendamos actualizarla.'
            : 'Tu contraseña tiene más de ' . $PasswordDiasLimite . ' días sin cambiarse. Te recomendamos actualizarla.';
        // Alerta con botón hacia el perfil (pestaña «Cambiar Password»)
        $Btn_Text = '<span>'
            . htmlspecialchars($PasswordTexto, ENT_QUOTES, 'UTF-8')
            . '</span>'
            . '<a class="btn btn-warning" href="' . htmlspecialchars($BASE . '/perfil', ENT_QUOTES, 'UTF-8') . '">'
            . '<i class="bi bi-key"></i> Cambiar contraseña</a>';
        // Mostrar alerta
        $data['Fnc_WidgetsCommon']->printAlertData(5, 4, 'bi bi-exclamation-triangle-fill', 0, $Btn_Text);
    }

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
    // Se cargan los widgets de los modulos, agrupados por bloque.
    // Cada bloque emite su titulo una sola vez y sus sub-widgets dentro del
    // mismo contenedor. Las rutas llegan como '../app/modules/<modulo>/widgets/<vista>.php'
    // y se resuelven a ruta absoluta usando la raiz de la aplicacion.
    $BlockIndex = 0;
    foreach ((array)($data['MainViewBlocks'] ?? []) as $Bloque) {

        // Se valida la estructura del bloque
        if(!is_array($Bloque) || empty($Bloque['Rutas']) || !is_array($Bloque['Rutas'])){
            continue;
        }
        $TituloBloque = !empty($Bloque['Titulo']) ? $Bloque['Titulo'] : 'Widgets';
        $BlockIndex++;

        // Titulo del bloque (una sola vez)
        echo '<h1 class="title-divider"><span class="divider-text">'
            . htmlspecialchars($TituloBloque, ENT_QUOTES, 'UTF-8')
            . '</span></h1>';

        // Contenedor unico del bloque con sus sub-widgets dentro
        echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="bloque-widgets-' . $BlockIndex . '">'
            .'<div class="row">';

        // Se incluyen las vistas de los sub-widgets del bloque
        foreach ($Bloque['Rutas'] as $value) {

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

        // Cierre del contenedor del bloque
        echo '</div>'
        .'</div>';
    }
    ?>
</div>
