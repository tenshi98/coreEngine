<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-costos"        onclick="tabCostosLoadList()"><i class="bi bi-bounding-box"></i> Costos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-partidas"      onclick="tabPartidasLoadList()"><i class="bi bi-box-seam"></i> Partidas</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-partidasFin"   onclick="tabPartidasFinLoadList()"><i class="bi bi-box-seam"></i> Partidas Finalizadas</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-perdidas"      onclick="tabPerdidasLoadList()"><i class="bi bi-currency-dollar"></i> Perdidas</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('gestionCampanas-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                switch ($data['rowData']['idEstado']) {
                                    /*****************************/
                                    //Abierto
                                    case 1:
                                        //Se verifican si existen los datos
                                        $x1 = $data['rowData']['Fecha'] ?? '';
                                        $x2 = $data['rowData']['Nombre'] ?? '';
                                        $x3 = $data['rowData']['idBodegas'] ?? '';
                                        $x4 = $data['rowData']['Observaciones'] ?? '';
                                        $x5 = $data['rowData']['idEstado'] ?? '';

                                        //se dibujan los inputs
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha',          'Name' => 'Fecha',           'Id' => 'Edit_Fecha',          'Value' => $x1, 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',         'Name' => 'Nombre',          'Id' => 'Edit_Nombre',         'Value' => $x2, 'Required' => 2]);
                                        $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Bodega',         'Name' => 'idBodegas',       'Id' => 'Edit_idBodegas',      'Value' => $x3, 'Required' => 2, 'arrData' => $data['arrBodegas'],   'BASE' => $BASE]);
                                        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observaciones',  'Name' => 'Observaciones',   'Id' => 'Edit_Observaciones',  'Value' => $x4, 'Required' => 1]);
                                        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',         'Name' => 'idEstado',        'Id' => 'Edit_idEstado',       'Value' => $x5, 'Required' => 2, 'arrData' => $data['arrEstados']]);

                                        break;
                                    /*****************************/
                                    //Cerrado
                                    case 2:
                                        //Se verifican si existen los datos
                                        $x1 = $data['rowData']['Fecha'] ?? '';
                                        $x2 = $data['rowData']['Nombre'] ?? '';
                                        $x3 = $data['rowData']['Observaciones'] ?? '';
                                        $x4 = $data['rowData']['EstadoNombre'] ?? '';

                                        //se dibujan los inputs
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Fecha',         'Name' => 'FechaFake',       'Id' => 'FechaFake',           'Value' => $x1, 'Required' => 3]);
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Nombre',        'Name' => 'NombreFake',      'Id' => 'NombreFake',          'Value' => $x2, 'Required' => 3]);
                                        $data['Fnc_FormInputs']->formTextarea([              'Placeholder' => 'Observaciones', 'Name' => 'Observaciones',   'Id' => 'Edit_Observaciones',  'Value' => $x3, 'Required' => 1]);
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Estado',        'Name' => 'EstadoFake',      'Id' => 'EstadoFake',          'Value' => $x4, 'Required' => 3]);

                                        break;

                                }

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCampana','Value' => $data['rowData']['idCampana'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <?php
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']);
                ?>

                <div class="tab-pane fade" id="resumen-costos">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Costos de <?php echo $data['rowData']['Nombre']; ?>
                            <div class="d-grid gap-2 d-md-flex">
                                <?php if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){ ?>
                                    <button type="button" class="btn btn-primary"    onclick="tabCostosNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Ingresar Nuevo</button>
                                    <button type="button" class="btn btn-secondary"  onclick="tabCostosNewDoc('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Ingresar Nuevo Doc</button>
                                <?php } ?>
                                <button type="button" class="btn btn-success"    onclick="exportTableToExcel('tableDataCostos', 'Costos')"><i class="ri-file-excel-2-line"></i> Exportar a Excel</button>
                            </div>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabCostosDataTable">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-partidas">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Partidas de <?php echo $data['rowData']['Nombre']; ?>
                            <div class="d-grid gap-2 d-md-flex">
                                <?php if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){ ?>
                                    <button type="button" class="btn btn-primary"    onclick="tabPartidasNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Partida</button>
                                    <button type="button" class="btn btn-secondary"  onclick="tabPartidasNewUnique('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Ingresar Entidad</button>
                                <?php } ?>
                                <button type="button" class="btn btn-success"    onclick="exportTableToExcel('tableDataPartidas', 'Partidas')"><i class="ri-file-excel-2-line"></i> Exportar a Excel</button>
                            </div>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabPartidasDataTable">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-partidasFin">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Partidas Finalizadas de <?php echo $data['rowData']['Nombre']; ?>
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="button" class="btn btn-primary"  onclick="dataPartida()"><i class="bi bi-arrow-repeat"></i> Actualizar</button>
                                <button type="button" class="btn btn-success"  onclick="exportTableToExcel('tableDataPartidasFin', 'Partidas Finalizadas')"><i class="ri-file-excel-2-line"></i> Exportar a Excel</button>
                            </div>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabPartidasFinDataTable">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-perdidas">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Perdidas de <?php echo $data['rowData']['Nombre']; ?>
                            <div class="d-grid gap-2 d-md-flex">
                                <?php if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){ ?>
                                    <button type="button" class="btn btn-primary"  onclick="tabPerdidasNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Ingresar Perdida</button>
                                <?php } ?>
                                <button type="button" class="btn btn-success"  onclick="exportTableToExcel('tableDataPerdidas', 'Perdidas')"><i class="ri-file-excel-2-line"></i> Exportar a Excel</button>
                            </div>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabPerdidasDataTable">

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="clearfix"></div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalContent">

        </div>
    </div>
</div>

<div class="modal fade" id="viewModal_2" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="modalContent_2">

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
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'},
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
    /*                              Costos                               */
    /*********************************************************************/
    //Variables
    let CostosLoad = 0;
    /******************************************/
    function tabCostosLoadList() {
        //Comparo
        if(CostosLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabCostosDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/costos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>';
            const Options = {
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
            //Indico que esta cargado
            CostosLoad = 1;
        }
    }
    /******************************************/
    function tabCostosView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent_2';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/costos/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal_2',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabCostosNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/costos/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabCostosNewDoc(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent_2';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/costos/newDoc/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal_2',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabCostosEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/costos/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabCostosDel(ID, Dato) {
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/costos'; ?>';
                let Informacion = {"idExistencia": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabCostosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/costos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
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
    /*                            Partidas                               */
    /*********************************************************************/
    //Variables
    let PartidasLoad = 0;
    /******************************************/
    function tabPartidasLoadList() {
        //Comparo
        if(PartidasLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabPartidasDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>';
            const Options = {
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
            //Indico que esta cargado
            PartidasLoad = 1;
        }
    }
    /******************************************/
    function tabPartidasNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent_2';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/new_step1/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal_2',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPartidasNewUnique(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/new_unique/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPartidasEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent_2';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal_2',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPartidasSendMassive(CampanaID, Fecha, PartidaID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/sendCampanaMassive/'; ?>'+CampanaID+'/'+Fecha+'/'+PartidaID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPartidasSendWhatsapp(ExistenciaID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/sendCampanaWhatsapp/'; ?>'+ExistenciaID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPartidasSendInfo(ID, EntidadNombre, EntidadPartida, EntidadFono) {
        Swal.fire({
            title: "Enviar Notificacion",
            text: "Esta a punto de enviar una notificacion a " + EntidadNombre + ", ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, enviar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result2) => {
            if (result2.isConfirmed) {
                $('#PDloader').show();
                let Metodo      = 'POST';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/update'; ?>';
                let Informacion = {
                    "idExistencia": ID,
                    "idEstadoPartida": 2,
                    "idCampana": <?php echo $data['rowData']['idCampana']; ?>
                };
                let urlWhatsapp = "https://api.whatsapp.com/send/?phone="+EntidadFono+"&text=Hola+"+EntidadNombre+"!!%0ACUERPO+DE+MENSAJE%0Apara+reservar+puede+acceder+al+siguiente+enlace+<?php echo ConfigAPP::SOFTWARE["URL"]; ?>/partida/"+EntidadPartida+"%0A%0ARecuerda+que+puedes+hacer+nuevos+pedidos+a+traves+de+nuestro+canal+de+ventas&type=phone_number&app_absent=0";
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabPartidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                    ],
                    openNewTab:urlWhatsapp,
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /******************************************/
    function tabPartidasDel(ID, Dato) {
        Swal.fire({
            title: "Borrar Dato",
            text: "Esta a punto de eliminar la partida del cliente " + Dato + ", ¿Desea continuar?",
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas'; ?>';
                let Informacion = {"idExistencia": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabPartidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /******************************************/
    function tabPartidasDelMassive(idCampana, Fecha, idEstadoPartida, Texto) {
        Swal.fire({
            title: "Borrar Dato",
            text: "Esta a punto de eliminar todas las partidas " + Texto + ", ¿Desea continuar?",
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/delMassive'; ?>';
                let Informacion = {
                    "idCampana": idCampana,
                    "Fecha": Fecha,
                    "idEstadoPartida": idEstadoPartida
                };
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabPartidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /******************************************/
    function tabPartidasCall(Fono, Nombre) {
        Swal.fire({
            title: "Llamar a Cliente",
            text: "Esta a punto de llamar al cliente " + Nombre + ", ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, llamar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result2) => {
            if (result2.isConfirmed) {
                //Se carga el boton
                window.location.href = 'tel:' + Fono;
            }
        });
    }
    /*********************************************************************/
    /*                       Partidas Finalizadas                        */
    /*********************************************************************/
    //Variables
    let PartidasFinLoad = 0;
    /******************************************/
    function tabPartidasFinLoadList() {
        //Comparo
        if(PartidasFinLoad===0){
            //Cargo los datos
            dataPartida();
        }
    }
    /******************************************/
    function tabPartidasFinEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent_2';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidasFinalizadas/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal_2',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function dataPartida() {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#tabPartidasFinDataTable';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidasFinalizadas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>';
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
        //Indico que esta cargado
        PartidasFinLoad = 1;
    }
    /******************************************/
    function tabPartidasFinView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent_2';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidasFinalizadas/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal_2',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPartidasFinDel(ID, Dato) {
        Swal.fire({
            title: "Borrar Dato",
            text: "Esta a punto de eliminar la partida del cliente " + Dato + ", esta eliminacion es permanente, ¿Desea continuar?",
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas'; ?>';
                let Informacion = {"idExistencia": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabPartidasFinDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidasFinalizadas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
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
    /*                            Perdidas                               */
    /*********************************************************************/
    //Variables
    let PerdidasLoad = 0;
    /******************************************/
    function tabPerdidasLoadList() {
        //Comparo
        if(PerdidasLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabPerdidasDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/perdidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>';
            const Options = {
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
            //Indico que esta cargado
            PerdidasLoad = 1;
        }
    }
    /******************************************/
    function tabPerdidasNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent_2';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/perdidas/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal_2',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPerdidasEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent_2';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/perdidas/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal_2',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPerdidasView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent_2';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/perdidas/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal_2',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPerdidasDel(ID, Dato) {
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/perdidas'; ?>';
                let Informacion = {"idExistencia": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabPerdidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/perdidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
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
    /*                    Tablas dentro de los clientes                  */
    /*********************************************************************/
    /******************************************/
    function delRow(Element) {
        $(Element).parent().parent().parent().remove();
    }
    /******************************************/
    function submitForm(Element) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Metodo      = 'POST';
        let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas'; ?>';
        let Informacion = $("#FormNewPartidaStep2").serialize();
        const Options     = {
            UpdateDiv : [
                {Div:'#tabPartidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
            ],
            showNoti:'Dato Creado Correctamente',
            closeModal:'#viewModal_2',
            ClearForm:'FormNewPartidaStep2',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        SendDataForms(Metodo, Direccion, Informacion, Options);
    }
    /*********************************************************************/
    /*                    Ejecucion funciones invocadas                  */
    /*********************************************************************/
    /******************************************/
    function notiEntrega(Data) {
        //Si el pedido fue cerrado se alerta la entrega
        if(Data.idEstadoPartida==='6'){
            Swal.fire({
                title: "Enviar Notificacion",
                text: "Esta a punto de enviar una notificacion a " + Data.Cliente + ", ¿Desea continuar?",
                icon: "warning",
                confirmButtonColor: "#81A1C1",
                confirmButtonText: "<i class='bi bi-check-circle'></i> Si, enviar",
                showCancelButton: true,
                cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
                cancelButtonColor: "#EA5757",
                reverseButtons: true,
            }).then((result2) => {
                if (result2.isConfirmed) {
                    //URL
                    let urlWhatsapp = "https://api.whatsapp.com/send/?phone="+Data.Fono+"&text=Hola+"+Data.Cliente+"!!%0ATu+pedido+de+<?php echo ConfigAPP::SOFTWARE['SoftwareName']; ?>+ha+sido+entregado&type=phone_number&app_absent=0";
                    //Abrir nuevo tab
                    window.open(urlWhatsapp, '_blank');
                }
            });
        }
    }

</script>