<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se obtiene el ID
$DataID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idVehiculo']);
?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-img"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-documentos" onclick="tabDocumentosLoadList()"><i class="bi bi-file-text"></i> Documentos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-obs"        onclick="tabObsLoadList()"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('vehiculosListado-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Básicos', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',  'Name' => 'Nombre',  'Id' => 'Edit_Nombre',   'Value'  => ($data['rowData']['Nombre'] ?? ''),  'Required' => 2]);
                                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo',    'Name' => 'idTipo',  'Id' => 'Edit_idTipo',   'Value'  => ($data['rowData']['idTipo'] ?? ''),  'Required' => 2,'arrData' => $data['arrTipo']]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Marca',   'Name' => 'Marca',   'Id' => 'Edit_Marca',    'Value'  => ($data['rowData']['Marca'] ?? ''),   'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Modelo',  'Name' => 'Modelo',  'Id' => 'Edit_Modelo',   'Value'  => ($data['rowData']['Modelo'] ?? ''),  'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Patente', 'Name' => 'Patente', 'Id' => 'Edit_Patente',  'Value'  => ($data['rowData']['Patente'] ?? ''), 'Required' => 1]);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Característicos', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formNumberSpinner([          'Placeholder' => 'Año de Fabricación',   'Name' => 'AnoFab',            'Id' => 'Edit_AnoFab',             'Value' => ($data['rowData']['AnoFab'] ?? ''),            'Required' => 1,'Min' => 1980,'Max' => $data['Fnc_ServerServer']->anoActual(),'Step' => 1,'Ndecimal' => 0]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Número de serie',      'Name' => 'Num_serie',         'Id' => 'Edit_Num_serie',          'Value' => ($data['rowData']['Num_serie'] ?? ''),         'Required' => 1]);
                                $data['Fnc_FormInputs']->formNumberSpinner([          'Placeholder' => 'Capacidad Pasajeros',  'Name' => 'CapacidadPersonas', 'Id' => 'Edit_CapacidadPersonas',  'Value' => ($data['rowData']['CapacidadPersonas'] ?? ''), 'Required' => 1,'Min' => 1,'Max' => 80,    'Step' => 1,'Ndecimal' => 0]);
                                $data['Fnc_FormInputs']->formNumberSpinner([          'Placeholder' => 'Capacidad (Kilos)',    'Name' => 'Capacidad',         'Id' => 'Edit_Capacidad',          'Value' => ($data['rowData']['Capacidad'] ?? ''),         'Required' => 1,'Min' => 1,'Max' => 30000, 'Step' => 1,'Ndecimal' => 0]);
                                $data['Fnc_FormInputs']->formNumberSpinner([          'Placeholder' => 'Metros Cubicos (M3)',  'Name' => 'MCubicos',          'Id' => 'Edit_MCubicos',           'Value' => ($data['rowData']['MCubicos'] ?? ''),          'Required' => 1,'Min' => 1,'Max' => 30000, 'Step' => 1,'Ndecimal' => 0]);
                                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo Carga',           'Name' => 'idTipoCarga',       'Id' => 'Edit_idTipoCarga',        'Value' => ($data['rowData']['idTipoCarga'] ?? ''),       'Required' => 1,'arrData' => $data['arrTipoCarga']]);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Administración', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formSelect([  'Placeholder' => 'Estado',  'Name' => 'idEstado',   'Id' => 'Edit_idEstado',  'Value'  => ($data['rowData']['idEstado'] ?? ''), 'Required' => 2,'arrData' => $data['arrEstado']]);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idVehiculo',     'Value' => $data['rowData']['idVehiculo'],              'Required' => 2]);

                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-img">
                    <div class="d-flex justify-content-center pt-4">
                        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                            <h4 class="title_h4 box-title text-color-red-dark">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                    Imagen de <?php echo $data['rowData']['Nombre']; ?>
                                </div>
                            </h4>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <?php
                    if(isset($data['rowData']['Direccion_img'])&&$data['rowData']['Direccion_img']!=''){ ?>
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-8 col-md-6 col-lg-6 col-xl-4 col-xxl-3">
                                <div class="d-flex justify-content-center">
                                    <img src="<?php echo $data['UserData']['MainPathUrl'].$data['rowData']['Direccion_img']; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
                                </div>
                                <div class="d-flex justify-content-center pt-2">
                                    <button  onclick="delIMG('<?php echo $data['rowData']['Direccion_img']; ?>')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Borrar Imagen</button>
                                </div>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <div class="d-flex justify-content-center pt-3">
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-xl-6 col-xxl-5">
                                <?php
                                // Se construye el dato
                                $ExtraData  = '"idVehiculo": '.$data['rowData']['idVehiculo'];
                                $ExtraData .= ',"idEstado": '.$data['rowData']['idEstado'];
                                $ExtraData .= ',"idTipo": '.$data['rowData']['idTipo'];
                                $ExtraData .= ',"Nombre": "'.$data['rowData']['Nombre'].'"';
                                // Se imprime el dato
                                $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Direccion_img','URL' => $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update','ExtraData' => $ExtraData]);?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="tab-pane fade" id="resumen-documentos">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Documentos de <?php echo $data['rowData']['Nombre']; ?>
                            <button type="button" class="btn btn-success"  onclick="tabDocumentosNew('<?php echo $DataID['data']; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabDocumentosDataTable">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-obs">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Observaciones de <?php echo $data['rowData']['Nombre']; ?>
                            <button type="button" class="btn btn-success"  onclick="tabObsNew('<?php echo $DataID['data']; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabObsDataTable">

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

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    /************************************/
    $("#FormEditData").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update'; ?>';
            let Informacion = $("#FormEditData").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$DataID['data']; ?>'},
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

    /*********************************************************************/
    /*                             IMAGENES                              */
    /*********************************************************************/
    /************************************/
    function delIMG(File) {
        Swal.fire({
            title: "Borrar Imagen",
            text: "Esta a punto de borrar la imagen, ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, borrar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'PUT';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/delFiles'; ?>';
                let Informacion = {
                    "idVehiculo": <?php echo $data['rowData']['idVehiculo']; ?>,
                    "Direccion_img": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$DataID['data']; ?>',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    /*********************************************************************/
    /*                            DOCUMENTOS                             */
    /*********************************************************************/
    // Variables
    let DocumentosLoad = 0;
    /************************************/
    function tabDocumentosLoadList() {
        //Comparo
        if(DocumentosLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabDocumentosDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/updateList/'.$DataID['data']; ?>';
            const Options = {
                closeObject:'#PDloader',
                refreshTables:'true',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
            //Indico que esta cargado
            DocumentosLoad = 1;
        }
    }
    /************************************/
    function tabDocumentosNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabDocumentosView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabDocumentosEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabDocumentosDel(ID, Dato) {
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos'; ?>';
                let Informacion = {"idDocumentos": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabDocumentosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/updateList/'.$DataID['data']; ?>', refreshTbl:'true'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    /*********************************************************************/
    /*                          OBSERVACIONES                            */
    /*********************************************************************/
    // Variables
    let ObsLoad = 0;
    /************************************/
    function tabObsLoadList() {
        //Comparo
        if(ObsLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabObsDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$DataID['data']; ?>';
            const Options = {
                closeObject:'#PDloader',
                refreshTables:'true',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
            //Indico que esta cargado
            ObsLoad = 1;
        }
    }
    /************************************/
    function tabObsNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabObsView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabObsEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabObsDel(ID, Dato) {
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones'; ?>';
                let Informacion = {"idObservaciones": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabObsDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$DataID['data']; ?>', refreshTbl:'true'}
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
