<div class="row">

    <?php
    /**************************************/
    //Cuadro de bienvenida
    require_once('main-principal-bienvenida.php');
    require_once('main-principal-meteo.php');


    /**************************************/
    //Vista de las compras y ventas
    if(isset($data['MainViewData']['Count_DocMercantiles'])&&$data['MainViewData']['Count_DocMercantiles']!=0){
        require_once('../app/modules/facturacion/views/main-doc-mercantiles.php');
    }
    /**************************************/
    //Vista del stock de los productos
    if(isset($data['MainViewData']['Count_Bodegas'])&&$data['MainViewData']['Count_Bodegas']!=0){
        require_once('../app/modules/bodegas/views/main-bodega-stock.php');
    }
    /**************************************/
    //Vista de las campañas
    if(isset($data['MainViewData']['Count_Campanas'])&&$data['MainViewData']['Count_Campanas']!=0){
        require_once('../app/modules/campanas/views/main-campana.php');
    }

    ?>
</div>
