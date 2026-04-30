<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
    <?php
    $Options = [
        'BASE'      => $BASE,
        'Type'      => 1,
        'latitude'  => $data['UserData']['Latitud'],
        'longitude' => $data['UserData']['Longitud'],
    ];
    $data['Fnc_WidgetsCommon']->widget_meteo($Options);
    ?>
</div>

