<h1 class="title-divider"><span class="divider-text">Compras / Ventas</span></h1>

<div class="row" id="main-doc-mercantiles"></div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    //Últimas partidas confirmadas
    function docMercantiles() {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#main-doc-mercantiles';
        let URL       = 'principal/docMercantiles';
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
        loadAsynch_docMerc();
    });
    //Carga asincrona
    async function loadAsynch_docMerc() {
        //Actualizacion de las partidas
        await docMercantiles();
    }

</script>