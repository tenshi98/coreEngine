<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

$e = static function ($v) { return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); };
// Catalogo de alertas (1 ok, 2 bajo el limite, 3 bajo la demanda predicha)
$arrAlertas = [
    ['ID' => 1, 'Nombre' => 'Stock suficiente'],
    ['ID' => 2, 'Nombre' => 'Bajo el stock límite'],
    ['ID' => 3, 'Nombre' => 'Bajo la demanda predicha'],
];
// Opciones de semanas del recalculo (4-26, defecto 12)
$arrSemanas = [];
for ($s = 4; $s <= 26; $s++) { $arrSemanas[] = ['ID' => $s, 'Nombre' => $s.' semanas']; }
// Ultima ejecucion correcta
$arrRun = (isset($data['arrRun']) && is_array($data['arrRun'])) ? $data['arrRun'] : [];
?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-graph-up-arrow"></i> Última predicción</h5>
            <?php if (empty($arrRun)) { ?>
                <div class="alert alert-info mb-2">Sin resultados; pulse Recalcular.</div>
            <?php } else { ?>
                <p class="mb-2">
                    <strong>Calculada:</strong> <?php echo $e($data['Fnc_DataDate']->fechaEstandar($arrRun['FechaFin'])); ?>
                    · <strong>Algoritmo:</strong> <?php echo $e($arrRun['Algoritmo']); ?>
                    · <strong>Productos:</strong> <?php echo (int) $arrRun['Muestras']; ?> de <?php echo (int) $arrRun['MuestrasOrigen']; ?>
                </p>
            <?php } ?>
            <?php
            // Solo con nivel de edicion o superior
            if (($data['UserAccess']['LevelAccess'] ?? 0) >= 2) { ?>
                <div class="d-flex justify-content-center">
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                        <?php
                        $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Semanas de historia', 'Name' => 'semanas', 'Id' => 'Recalc_semanas', 'Value' => 12, 'Required' => 1, 'arrData' => $arrSemanas, 'BASE' => $BASE]);
                        ?>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button type="button" id="btnRecalcular" onclick="recalcularPrediccion()" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Recalcular</button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div id="listTableData" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center pt-3">
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                        <div class="text-center">
                            <h5 class="search-title"><i class="bi bi-search"></i> Filtrar Datos</h5>
                        </div>
                        <form id="FormSearchData" name="FormSearchData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
                            <?php
                            //se dibujan los inputs
                            $data['Fnc_FormInputs']->formSelectFilter([ 'Placeholder' => 'Producto', 'Name' => 'idProducto', 'Id' => 'Search_idProducto', 'Value' => '','Required' => 1,'arrData' => $data['arrProductos'], 'BASE' => $BASE]);
                            $data['Fnc_FormInputs']->formSelectFilter([ 'Placeholder' => 'Alerta',   'Name' => 'Alerta',     'Id' => 'Search_Alerta',     'Value' => '','Required' => 1,'arrData' => $arrAlertas,           'BASE' => $BASE]);
                            ?>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <button type="submit" class="btn btn-success"><i class="bi bi-search"></i> Filtrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      FORMULARIO DE BUSQUEDA                       */
    /*********************************************************************/
    /************************************/
    $("#FormSearchData").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            // Si ya se está ejecutando, salimos
            if (ejecutandoForm.valor) return;
            //Cambio los valores
            ejecutandoForm.valor = true;
            //Ejecucion normal
            e.preventDefault();
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Metodo      = 'POST';
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/search'; ?>';
            let Informacion = $("#FormSearchData").serialize();
            const Options     = {
                UpdateDivFrom : 'listTableData',
                colapseDiv : 'true',
                refreshTables : 'true',
                closeObject:'#PDloader',
                    changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    <?php
    // El boton y su JS solo existen con nivel de edicion o superior
    if (($data['UserAccess']['LevelAccess'] ?? 0) >= 2) { ?>
    /*********************************************************************/
    /*                      RECALCULAR PREDICCION                        */
    /*********************************************************************/
    /************************************/
    function recalcularPrediccion() {
        Swal.fire({
            title: "Recalcular predicción",
            text: "Se recalculará la demanda predicha con el historial de egresos. ¿Desea continuar?",
            icon: "question",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, recalcular",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                // Bloquea el boton mientras corre
                $('#btnRecalcular').prop('disabled', true);
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto (el token CSRF lo agrega functions_v2.js)
                let Metodo      = 'POST';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/recalcular'; ?>';
                let Informacion = {"semanas": $('#Recalc_semanas').val()};
                const Options     = {
                    Destino : '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>',
                    showNoti:'Recalculo completado',
                    closeObject:'#PDloader',
                    changeValForm: recalcularLock,
                };
                //Se envian los datos (en error muestra el message generico del backend)
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /************************************/
    // Objeto con setter: functions_v2.js hace `changeValForm.valor = false` al terminar (exito o error)
    const recalcularLock = {
        set valor(v) { if (!v) { $('#btnRecalcular').prop('disabled', false); } }
    };
    <?php } ?>
</script>
