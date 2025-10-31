<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-items"     onclick="tabItemLoadList()"><i class="bi bi-bounding-box"></i> Items</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-productos" onclick="tabProdLoadList()"><i class="bi bi-box-seam"></i> Productos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-servicios" onclick="tabServLoadList()"><i class="bi bi-patch-check"></i> Servicios</button></li>
                <?php if(isset($data['rowData']['idDocumentos'])&&$data['rowData']['idDocumentos']==1){ ?>
                    <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-guias"     onclick="tabGuiaLoadList()"><i class="bi bi-file-check"></i> Guias</button></li>
                <?php } ?>
                <?php if(isset($data['rowData']['idDocumentos'])&&$data['rowData']['idDocumentos']!=3){ ?>
                    <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-pagos"     onclick="tabPagoLoadList()"><i class="bi bi-currency-dollar"></i> Pagos</button></li>
                <?php } ?>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('gestionDocumentos-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                switch ($data['rowData']['idEstadoPago']) {
                                    /*****************************/
                                    //No pagado
                                    case 1:
                                        //Se verifican si existen los datos
                                        $x1 = $data['rowData']['idDocumentos'] ?? '';
                                        $x2 = $data['rowData']['N_Doc'] ?? '';
                                        $x3 = $data['rowData']['idEntidad'] ?? '';
                                        $x4 = $data['rowData']['Creacion_fecha'] ?? '';
                                        $x5 = $data['rowData']['Observaciones'] ?? '';

                                        //se dibujan los inputs
                                        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Documento Mercantil', 'Name' => 'idDocumentos',    'Id' => 'Edit_idDocumentos',    'Value' => $x1, 'Required' => 2, 'arrData' => $data['arrDocumentos']]);
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Numero Documento',    'Name' => 'N_Doc',           'Id' => 'Edit_N_Doc',           'Value' => $x2, 'Required' => 1, 'Icon' => 'bi bi-sort-numeric-down']);
                                        $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Entidad',             'Name' => 'idEntidad',       'Id' => 'Edit_idEntidad',       'Value' => $x3, 'Required' => 2, 'arrData' => $data['arrEntidades'], 'BASE' => $BASE]);
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Creacion',   'Name' => 'Creacion_fecha',  'Id' => 'Edit_Creacion_fecha',  'Value' => $x4, 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
                                        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observaciones',       'Name' => 'Observaciones',   'Id' => 'Edit_Observaciones',   'Value' => $x5, 'Required' => 1]);

                                        break;
                                    /*****************************/
                                    //Pagado - Anulado
                                    case 2:
                                    case 3:
                                        //valido
                                        $Entidad  = '';
                                        $Entidad .= !empty($data['rowData']['EntidadesNombre'])
                                                    ? $data['rowData']['EntidadesNombre'].' '.$data['rowData']['EntidadesApellido']
                                                    : $data['rowData']['EntidadesRazonSocial'];
                                        $Entidad .= !empty($data['rowData']['EntidadesNick'])
                                                    ? ' ('.$data['rowData']['EntidadesNick'].')'
                                                    : '';

                                        //Se verifican si existen los datos
                                        $x1 = $data['rowData']['Documento'] ?? '';
                                        $x2 = $data['rowData']['N_Doc'] ?? '';
                                        $x3 = $Entidad ?? '';
                                        $x4 = $data['rowData']['Creacion_fecha'] ?? '';
                                        $x5 = $data['rowData']['Observaciones'] ?? '';

                                        //se dibujan los inputs
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Documento Mercantil', 'Name' => 'DocumentoFake',      'Id' => 'DocumentoFake',        'Value' => $x1,'Required' => 3]);
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Numero Documento',    'Name' => 'N_DocFake',          'Id' => 'N_DocFake',            'Value' => $x2,'Required' => 3]);
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Entidad',             'Name' => 'EntidadFake',        'Id' => 'EntidadFake',          'Value' => $x3,'Required' => 3]);
                                        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Fecha de Creacion',   'Name' => 'Creacion_fechaFake', 'Id' => 'Creacion_fechaFake',   'Value' => $x4,'Required' => 3]);
                                        $data['Fnc_FormInputs']->formTextarea([              'Placeholder' => 'Observaciones',       'Name' => 'Observaciones',      'Id' => 'Edit_Observaciones',   'Value' => $x5, 'Required' => 1]);

                                        break;

                                }

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idFacturacion','Value' => $data['rowData']['idFacturacion'],'Required' => 2]);
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
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']);
                ?>

                <div class="tab-pane fade" id="resumen-items">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Items de <?php echo $data['rowData']['Documento'].' '.($data['rowData']['N_Doc'] ?? 'nRef '.$data['rowData']['idFacturacion']); ?>
                            <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){ ?>
                                <button type="button" class="btn btn-success"  onclick="tabItemNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Ingresar Nuevo</button>
                            <?php } ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabItemDataTable">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-productos">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Productos de <?php echo $data['rowData']['Documento'].' '.($data['rowData']['N_Doc'] ?? 'nRef '.$data['rowData']['idFacturacion']); ?>
                            <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){ ?>
                                <button type="button" class="btn btn-success"  onclick="tabProdNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Ingresar Nuevo</button>
                            <?php } ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabProdDataTable">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-servicios">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Servicios de <?php echo $data['rowData']['Documento'].' '.($data['rowData']['N_Doc'] ?? 'nRef '.$data['rowData']['idFacturacion']); ?>
                            <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){ ?>
                                <button type="button" class="btn btn-success"  onclick="tabServNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Ingresar Nuevo</button>
                            <?php } ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabServDataTable">

                    </div>
                </div>

                <?php if(isset($data['rowData']['idDocumentos'])&&$data['rowData']['idDocumentos']==1){ ?>
                    <div class="tab-pane fade" id="resumen-guias">
                        <h5 class="card-title">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                Guia de Despacho de <?php echo $data['rowData']['Documento'].' '.($data['rowData']['N_Doc'] ?? 'nRef '.$data['rowData']['idFacturacion']); ?>
                                <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){ ?>
                                    <button type="button" class="btn btn-success"  onclick="tabGuiaNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Ingresar Nuevo</button>
                                <?php } ?>
                            </div>
                        </h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabGuiaDataTable">

                        </div>
                    </div>
                <?php } ?>

                <?php if(isset($data['rowData']['idDocumentos'])&&$data['rowData']['idDocumentos']!=3){ ?>
                    <div class="tab-pane fade" id="resumen-pagos">
                        <h5 class="card-title">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                Pagos Realizados de <?php echo $data['rowData']['Documento'].' '.($data['rowData']['N_Doc'] ?? 'nRef '.$data['rowData']['idFacturacion']); ?>
                                <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){ ?>
                                    <button type="button" class="btn btn-success"  onclick="tabPagoNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Ingresar Nuevo</button>
                                <?php } ?>
                            </div>
                        </h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabPagoDataTable">

                        </div>
                    </div>
                <?php } ?>

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
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>'},
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
    /*                              ITEMS                                */
    /*********************************************************************/
    //Variables
    let ItemLoad = 0;
    /******************************************/
    function tabItemLoadList() {
        //Comparo
        if(ItemLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabItemDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/items/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>';
            const Options = {
                closeObject:'#PDloader',
                refreshTables:'true',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
            //Indico que esta cargado
            ItemLoad = 1;
        }
    }
    /******************************************/
    function tabItemNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/items/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabItemEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/items/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabItemDel(ID, Dato) {
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/items'; ?>';
                let Informacion = {"idExistencia": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabItemDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/items/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>', refreshTbl:'true'}
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
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>';
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
                        {Div:'#tabProdDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>', refreshTbl:'true'}
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
    /*                            SERVICIOS                              */
    /*********************************************************************/
    //Variables
    let ServLoad = 0;
    /******************************************/
    function tabServLoadList() {
        //Comparo
        if(ServLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabServDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/servicios/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>';
            const Options = {
                closeObject:'#PDloader',
                refreshTables:'true',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
            //Indico que esta cargado
            ServLoad = 1;
        }
    }
    /******************************************/
    function tabServNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/servicios/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabServEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/servicios/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabServDel(ID, Dato) {
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/servicios'; ?>';
                let Informacion = {"idExistencia": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabServDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/servicios/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>', refreshTbl:'true'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    <?php if(isset($data['rowData']['idDocumentos'])&&$data['rowData']['idDocumentos']==1){ ?>
        /*********************************************************************/
        /*                        GUIAS DE DESPACHO                          */
        /*********************************************************************/
        //Variables
        let GuiaLoad = 0;
        /******************************************/
        function tabGuiaLoadList() {
            //Comparo
            if(GuiaLoad===0){
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#tabGuiaDataTable';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/guias/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>';
                const Options = {
                    closeObject:'#PDloader',
                    refreshTables:'true',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
                //Indico que esta cargado
                GuiaLoad = 1;
            }
        }
        /******************************************/
        function tabGuiaNew(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/guias/new/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabGuiaDel(ID, Dato) {
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
                    let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/guias'; ?>';
                    let Informacion = {"idExistencia": ID};
                    const Options     = {
                        UpdateDiv : [
                            {Div:'#tabGuiaDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/guias/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>', refreshTbl:'true'}
                        ],
                        showNoti:'Dato Borrado Correctamente',
                        closeObject:'#PDloader',
                    };
                    //Se envian los datos al formulario
                    SendDataForms(Metodo, Direccion, Informacion, Options);
                }
            });
        }
    <?php } ?>
    <?php if(isset($data['rowData']['idDocumentos'])&&$data['rowData']['idDocumentos']!=3){ ?>
        /*********************************************************************/
        /*                              PAGOS                                */
        /*********************************************************************/
        //Variables
        let PagoLoad = 0;
        /******************************************/
        function tabPagoLoadList() {
            //Comparo
            if(PagoLoad===0){
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#tabPagoDataTable';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>';
                const Options = {
                    closeObject:'#PDloader',
                    refreshTables:'true',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
                //Indico que esta cargado
                PagoLoad = 1;
            }
        }
        /******************************************/
        function tabPagoNew(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos/new/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabPagoEdit(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos/getID/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabPagoDel(ID, Dato) {
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
                    let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos'; ?>';
                    let Informacion = {"idPago": ID};
                    const Options     = {
                        Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>',
                        closeObject:'#PDloader',
                    };
                    //Se envian los datos al formulario
                    SendDataForms(Metodo, Direccion, Informacion, Options);
                }
            });
        }
    <?php } ?>


</script>