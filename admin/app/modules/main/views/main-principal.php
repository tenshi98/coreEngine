<div class="row">

    <?php
    /**************************************/
    //Cuadro de bienvenida
    require_once('main-principal-bienvenida.php');
    require_once('main-principal-meteo.php');


    /**************************************/
    //Se cargan los widgets
    foreach ($data['MainViewData'] as $value) {
        require_once($value);
    }


    ?>
</div>

