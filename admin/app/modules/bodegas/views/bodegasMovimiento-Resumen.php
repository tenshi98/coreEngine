<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-prod" onclick="tabProdLoadList()"><i class="bi bi-arrow-left-right"></i> Movimientos</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('bodegasMovimiento-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['BodegaIngreso'] ?? '';
                                $x2  = $data['rowData']['BodegaEgreso'] ?? '';
                                $x3  = $data['rowData']['Creacion_fecha'] ?? '';
                                $x4  = $data['rowData']['Observaciones'] ?? '';

                                //se dibujan los inputs
                                //Se verifica movimiento
                                switch ($data['rowData']['idEstadoIngreso']) {
                                    /************************************/
                                    //Ingreso
                                    case 1:
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Bodega Ingreso',    'Name' => 'BodegaFake',    'Id' => 'BodegaFake',       'Value' => $x1,'Required' => 3]);
                                        break;
                                    /************************************/
                                    //Egreso
                                    case 2:
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Bodega Egreso',    'Name' => 'BodegaFake',    'Id' => 'BodegaFake',       'Value' => $x2,'Required' => 3]);
                                        break;
                                    /************************************/
                                    //Traspaso
                                    case 3:
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Bodega Egreso',    'Name' => 'BodegaFake1',    'Id' => 'BodegaFake1',       'Value' => $x2,'Required' => 3]);
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Bodega Ingreso',   'Name' => 'BodegaFake2',    'Id' => 'BodegaFake2',       'Value' => $x1,'Required' => 3]);
                                        break;
                                }

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Movimiento',  'Name' => 'Creacion_fecha',  'Id' => 'Edit_Creacion_fecha',  'Value' => $x3,'Required' => 2, 'Icon' => 'bi bi-calendar3']);
                                $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observaciones',        'Name' => 'Observaciones',   'Id' => 'Edit_Observaciones',   'Value' => $x4,'Required' => 1]);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idMovimiento','Value' => $data['rowData']['idMovimiento'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-prod">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Productos de <?php echo $data['rowData']['TipoMovimiento'].' '.$Movimiento; ?>
                            <button type="button" class="btn btn-success"  onclick="tabProdNew('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMovimiento']); ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabProdDataTable">

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="clearfix"></div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger float-end"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalContent">

        </div>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormEditData").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            e.preventDefault();
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Metodo      = 'POST';
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update'; ?>';
            let Informacion = $("#FormEditData").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMovimiento']); ?>'},
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

    /*********************************************************************/
    /*                            PRODUCTOS                              */
    /*********************************************************************/
    //Variables
    let ProdLoad = 0;
    /******************************************/
    function tabProdLoadList() {
        //Comparo
        if(ProdLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabProdDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMovimiento']); ?>';
            const Options = {
                closeObject:'#PDloader',
                refreshTables:'true',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
            //Indico que esta cargado
            ProdLoad = 1;
        }
    }
    /******************************************/
    function tabProdNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabProdEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabProdDel(ID, Dato) {
        Swal.fire({
            title: "Borrar Dato",
            text: "Esta a punto de eliminar el dato " + Dato + ", ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, borrar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result2) => {
            if (result2.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'DELETE';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos'; ?>';
                let Informacion = {"idExistencia": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabProdDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMovimiento']); ?>', refreshTbl:'true'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
</script>