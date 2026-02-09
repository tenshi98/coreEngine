<h1 class="title-divider"><span class="divider-text">Bodegas</span></h1>

<div class="row" id="main-bodega-stock"></div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    //Últimas partidas confirmadas
    function bodegaStock() {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#main-bodega-stock';
        let URL       = 'principal/bodegaStock';
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }

    /*********************************************************************/
    /*                              ONLOAD                               */
    /*********************************************************************/
    $(document).ready(function() {
        loadAsynch_bodegaStock();
    });
    //Carga asincrona
    async function loadAsynch_bodegaStock() {
        //Actualizacion de las partidas
        await bodegaStock();
    }

</script>