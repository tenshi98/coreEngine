<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4 col-xxl-4" id="main-doc-ventas" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="600"></div>

<script>
    (function () {
        /*********************************************************************/
        /*                     DOCUMENTOS MERCANTILES: VENTAS                 */
        /*********************************************************************/
        //Carga de datos
        function docVentas() {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#main-doc-ventas';
            let URL       = 'principal/docMercantilesVentas';
            const Options = {
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        //ONLOAD
        $(document).ready(function() {
            docVentas();
        });
    })();
</script>
