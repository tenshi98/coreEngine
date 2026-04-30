<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="row">

    <?php
    /**************************************/
    //Cuadro de bienvenida
    require_once('main-principal-bienvenida.php'); //Cuadro Perfil
    if($data['UserData']["Config_Principal_Meteo"]==2){ require_once('main-principal-meteo.php');}      //Widget meteorologico
    if($data['UserData']["Config_Principal_Feed"]==2){  require_once('main-principal-feeds.php');}    //Widget con noticias

    /**************************************/
    //Se cargan los widgets
    foreach ($data['MainViewData'] as $value) {
        require_once($value);
    }

    ?>
</div>

