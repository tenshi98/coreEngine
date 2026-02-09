<h1 class="title-divider"><span class="divider-text">Campañas</span></h1>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                    <button type="button" class="btn btn-sm btn-primary" onclick="pastYear()"><i class="bi bi-arrow-left-circle"></i></button>
                    <strong>Resumen Campañas <strong id="anoCampana"></strong></strong>
                    <button type="button" class="btn btn-sm btn-primary" onclick="nexYear()"><i class="bi bi-arrow-right-circle"></i></button>
                </div>
            </h5>
            <div class="clearfix"></div>
            <div id="resumenCampana"></div>
        </div>
    </div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                    <button type="button" class="btn btn-sm btn-primary" onclick="pastYearListado()"><i class="bi bi-arrow-left-circle"></i></button>
                    <strong>Analisis Contable <strong id="anoCampanaListado"></strong></strong>
                    <button type="button" class="btn btn-sm btn-primary" onclick="nexYearListado()"><i class="bi bi-arrow-right-circle"></i></button>
                </div>
            </h5>
            <div class="clearfix"></div>
            <div class="table-responsive" id="campanaAnalisisContable"></div>
        </div>
    </div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                    <strong>Últimas partidas confirmadas</strong>
                    <button type="button" class="btn btn-sm btn-primary" onclick="partidaConfirmada()"><i class="bi bi-arrow-repeat"></i> Actualizar</button>
                </div>
            </h5>
            <div class="clearfix"></div>
            <div class="table-responsive" id="partidaConfirmada"></div>
        </div>
    </div>
</div>


<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    //Resumen Campañas
    function resumenCampana(ANO) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#resumenCampana';
        let URL       = 'principal/resumenCampana/'+ANO;
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    //Analisis Contable
    function campanaAnalisisContable(ANO) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#campanaAnalisisContable';
        let URL       = 'principal/campanaAnalisisContable/'+ANO;
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    //Últimas partidas confirmadas
    function partidaConfirmada() {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#partidaConfirmada';
        let URL       = 'principal/partidaConfirmada';
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }

    /*********************************************************************/
    /*                            VARIABLES                              */
    /*********************************************************************/
    //Variables
    let anoActual        = <?php echo $data['Fnc_ServerServer']->anoActual(); ?>;
    let anoActualListado = <?php echo $data['Fnc_ServerServer']->anoActual(); ?>;

    /*********************************************************************/
    /*                          FUNCIONALIDAD                            */
    /*********************************************************************/
    function pastYear() {
        anoActual--;
        resumenCampana(anoActual);
        //cambio del año
        document.getElementById("anoCampana").innerHTML = anoActual;
    }
    function nexYear() {
        anoActual++;
        resumenCampana(anoActual);
        //cambio del año
        document.getElementById("anoCampana").innerHTML = anoActual;
    }
    /**************************/
    function pastYearListado() {
        anoActualListado--;
        campanaAnalisisContable(anoActualListado);
        //cambio del año
        document.getElementById("anoCampanaListado").innerHTML = anoActualListado;
    }
    function nexYearListado() {
        anoActualListado++;
        campanaAnalisisContable(anoActualListado);
        //cambio del año
        document.getElementById("anoCampanaListado").innerHTML = anoActualListado;
    }

    /*********************************************************************/
    /*                              ONLOAD                               */
    /*********************************************************************/
    $(document).ready(function() {
        //cambio del año
        document.getElementById("anoCampana").innerHTML        = anoActual;
        document.getElementById("anoCampanaListado").innerHTML = anoActualListado;
        //Actualizacion de las partidas
        loadAsynch_campana();
    });
    //Carga asincrona
    async function loadAsynch_campana() {
        //Actualizacion de la campaña
        await resumenCampana(anoActual);
         //Actualizacion de la campaña
        await campanaAnalisisContable(anoActualListado);
        //Actualizacion de las partidas
        await partidaConfirmada();
    }

</script>