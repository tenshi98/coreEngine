<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-img"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
                <?php if($data['UserData']["maquinasListadoVerDocumentos"]==2){ ?>  <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-documentos" onclick="tabDocumentosLoadList()"><i class="bi bi-file-text"></i> Documentos</button></li><?php } ?>
                <li class="nav-item flex-fill dropdown">
                    <button class="nav-link w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-plus-square"></i> Ver Mas
                    </button>
                    <ul class="dropdown-menu view-more-menu w-100">
                        <li><button class="dropdown-item w-100" data-bs-toggle="tab" data-bs-target="#resumen-obs" onclick="tabObsLoadList()"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
                        <?php if($data['UserData']["maquinasListadoComponentes"]==2){ ?>
                            <li><button class="dropdown-item w-100" data-bs-toggle="tab" data-bs-target="#resumen-componentes" onclick="tabComponentesLoadList()"><i class="bi bi-puzzle"></i> Componentes</button></li>
                        <?php } ?>
                        <?php if($data['UserData']["maquinasListadoTelemetria"]==2){ ?>
                            <li><button class="dropdown-item w-100" data-bs-toggle="tab" data-bs-target="#resumen-tel-config"><i class="bi bi-tools"></i> Configuración</button></li>
                            <?php if($data['rowData']['id_Sensores']==1){ ?>
                                <li><button class="dropdown-item w-100" data-bs-toggle="tab" data-bs-target="#resumen-tel-sensores" onclick="tabSensoresLoadList()"><i class="bi bi-diagram-3"></i> Sensores</button></li>
                                <li><button class="dropdown-item w-100" data-bs-toggle="tab" data-bs-target="#resumen-tel-alarmas"  onclick="tabAlarmasLoadList()"><i class="bi bi-megaphone"></i> Alarmas Personalizadas</button></li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </li>

            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('maquinasListado-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1 = $data['rowData']['Nombre'] ?? '';
                                $x2 = $data['rowData']['CodIdentificador'] ?? '';
                                $x3 = $data['rowData']['Descripcion'] ?? '';
                                $x4 = $data['rowData']['idEstado'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Básicos']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',         'Name' => 'Nombre',           'Id' => 'Edit_Nombre',            'Value' => $x1,'Required' => 2]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Identificador',  'Name' => 'CodIdentificador', 'Id' => 'Edit_CodIdentificador',  'Value' => $x2,'Required' => 1,'Icon' => 'ri-barcode-line']);
                                $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Descripcion',    'Name' => 'Descripcion',      'Id' => 'Edit_Descripcion',       'Value' => $x3,'Required' => 1]);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Administración']);
                                $data['Fnc_FormInputs']->formSelect([   'Placeholder' => 'Estado',  'Name' => 'idEstado', 'Id' => 'Edit_idEstado', 'Value' => $x4,'Required' => 2,'arrData' => $data['arrEstado']]);


                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idMaquina','Value' => $data['rowData']['idMaquina'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-img">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Imagen de <?php echo $data['rowData']['Nombre']; ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <?php
                    if(isset($data['rowData']['Direccion_img'])&&$data['rowData']['Direccion_img']!=''){ ?>
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-8 col-md-6 col-lg-6 col-xl-4 col-xxl-3">
                                <div class="d-flex justify-content-center">
                                    <img src="<?php echo $BASE.'/upload/'.$data['rowData']['Direccion_img']; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
                                </div>
                                <div class="d-flex justify-content-center pt-2">
                                    <button  onclick="delIMG('<?php echo $data['rowData']['Direccion_img']; ?>')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Borrar Imagen</button>
                                </div>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <div class="d-flex justify-content-center pt-3">
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-xl-6 col-xxl-5">
                                <?php $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Direccion_img','URL' => $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update','ExtraData' => '"idMaquina": '.$data['rowData']['idMaquina']]);?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <?php
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']);
                ?>

                <?php if($data['UserData']["maquinasListadoVerDocumentos"]==2){ ?>
                    <div class="tab-pane fade" id="resumen-documentos">
                        <h5 class="card-title">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                Documentos de <?php echo $data['rowData']['Nombre']; ?>
                                <button type="button" class="btn btn-success"  onclick="tabDocumentosNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                            </div>
                        </h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabDocumentosDataTable">

                        </div>
                    </div>
                <?php } ?>

                <div class="tab-pane fade" id="resumen-obs">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Observaciones de <?php echo $data['rowData']['Nombre']; ?>
                            <button type="button" class="btn btn-success"  onclick="tabObsNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabObsDataTable">

                    </div>
                </div>

                <?php if($data['UserData']["maquinasListadoComponentes"]==2){ ?>
                    <div class="tab-pane fade" id="resumen-componentes">
                        <h5 class="card-title">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                Componentes de <?php echo $data['rowData']['Nombre']; ?>
                                <button type="button" class="btn btn-success"  onclick="tabObsNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                            </div>
                        </h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabComponentesDataTable">

                        </div>
                    </div>
                <?php } ?>

                <?php if($data['UserData']["maquinasListadoTelemetria"]==2){ ?>

                    <div class="tab-pane fade" id="resumen-tel-config">
                        <h5 class="card-title">Configuración de <?php echo $data['rowData']['Nombre']; ?></h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabObsDataTable">
                            <?php require_once('maquinasListado-Resumen-Config.php'); ?>
                        </div>
                    </div>

                    <?php if($data['rowData']['id_Sensores']==1){ ?>
                        <div class="tab-pane fade" id="resumen-tel-sensores">
                            <h5 class="card-title">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                    Sensores de <?php echo $data['rowData']['Nombre']; ?>
                                    <button type="button" class="btn btn-success"  onclick="tabSensoresNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                                </div>
                            </h5>
                            <div class="clearfix"></div>
                            <div class="table-responsive" id="tabSensoresDataTable">

                            </div>
                        </div>

                        <div class="tab-pane fade" id="resumen-tel-alarmas">
                            <h5 class="card-title">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                    Alarmas Personalizadas de <?php echo $data['rowData']['Nombre']; ?>
                                    <button type="button" class="btn btn-success"  onclick="tabAlarmasNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                                </div>
                            </h5>
                            <div class="clearfix"></div>
                            <div class="table-responsive" id="tabAlarmasDataTable">

                            </div>
                        </div>
                    <?php } ?>
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
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>'},
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
    /*                             IMAGENES                              */
    /*********************************************************************/
    /******************************************/
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
                    "idMaquina": <?php echo $data['rowData']['idMaquina']; ?>,
                    "Direccion_img": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    <?php if($data['UserData']["maquinasListadoVerDocumentos"]==2){ ?>
        /*********************************************************************/
        /*                            DOCUMENTOS                             */
        /*********************************************************************/
        //Variables
        let DocumentosLoad = 0;
        /******************************************/
        function tabDocumentosLoadList() {
            //Comparo
            if(DocumentosLoad===0){
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#tabDocumentosDataTable';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>';
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
        /******************************************/
        function tabDocumentosNew(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/new/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabDocumentosView(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/view/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabDocumentosEdit(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/getID/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
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
                            {Div:'#tabDocumentosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>', refreshTbl:'true'}
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
    /*********************************************************************/
    /*                          OBSERVACIONES                            */
    /*********************************************************************/
    //Variables
    let ObsLoad = 0;
    /******************************************/
    function tabObsLoadList() {
        //Comparo
        if(ObsLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabObsDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>';
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
    /******************************************/
    function tabObsNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabObsView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabObsEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
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
                        {Div:'#tabObsDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>', refreshTbl:'true'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    <?php if($data['UserData']["maquinasListadoComponentes"]==2){ ?>
        /*********************************************************************/
        /*                            COMPONENTES                            */
        /*********************************************************************/
        //Variables
        let ComponentesLoad = 0;
        /******************************************/
        function tabComponentesLoadList() {
            //Comparo
            if(ComponentesLoad===0){
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#tabComponentesDataTable';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/componentes/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>';
                const Options = {
                    closeObject:'#PDloader',
                    refreshTables:'true',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
                //Indico que esta cargado
                ComponentesLoad = 1;
            }
        }
        /******************************************/
        function tabComponentesNew(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/componentes/new/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabComponentesView(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/componentes/view/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabComponentesEdit(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/componentes/getID/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabComponentesDel(ID, Dato) {
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
                    let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/componentes'; ?>';
                    let Informacion = {"idComponentes": ID};
                    const Options     = {
                        UpdateDiv : [
                            {Div:'#tabComponentesDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/componentes/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>', refreshTbl:'true'}
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
    <?php if($data['UserData']["maquinasListadoTelemetria"]==2){ ?>
        /*********************************************************************/
        /*                            Componentes                             */
        /*********************************************************************/
        $("#FormEditConfiguracion").submit(function(e) {
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
                let Informacion = $("#FormEditConfiguracion").serialize();
                const Options     = {
                    UpdateDiv : [
                        {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>'},
                    ],
                    showNoti:'Datos Editados Correctamente',
                    triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
        <?php if($data['rowData']['id_Sensores']==1){ ?>
            /*********************************************************************/
            /*                             SENSORES                              */
            /*********************************************************************/
            //Variables
            let SensoresLoad = 0;
            /******************************************/
            function tabSensoresLoadList() {
                //Comparo
                if(SensoresLoad===0){
                    //Cargo el loader
                    $('#PDloader').show();
                    //Ejecuto
                    let Div       = '#tabSensoresDataTable';
                    let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/sensores/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>';
                    const Options = {
                        closeObject:'#PDloader',
                        refreshTables:'true',
                    };
                    //Se envian los datos al formulario
                    UpdateContentId(Div, URL, Options);
                    //Indico que esta cargado
                    SensoresLoad = 1;
                }
            }
            /******************************************/
            function tabSensoresNew(ID) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#modalContent';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/sensores/new/'; ?>'+ID;
                const Options = {
                    showModal : '#viewModal',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
            }
            /******************************************/
            function tabSensoresView(ID) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#modalContent';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/sensores/view/'; ?>'+ID;
                const Options = {
                    showModal : '#viewModal',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
            }
            /******************************************/
            function tabSensoresEdit(ID) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#modalContent';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/sensores/getID/'; ?>'+ID;
                const Options = {
                    showModal : '#viewModal',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
            }
            /******************************************/
            function tabSensoresDel(ID, Dato) {
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
                        let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/sensores'; ?>';
                        let Informacion = {"idSensores": ID};
                        const Options     = {
                            UpdateDiv : [
                                {Div:'#tabSensoresDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/sensores/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>', refreshTbl:'true'}
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
            /*                              ALARMAS                              */
            /*********************************************************************/
            //Variables
            let AlarmasLoad = 0;
            /******************************************/
            function tabAlarmasLoadList() {
                //Comparo
                if(AlarmasLoad===0){
                    //Cargo el loader
                    $('#PDloader').show();
                    //Ejecuto
                    let Div       = '#tabAlarmasDataTable';
                    let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/alarmas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>';
                    const Options = {
                        closeObject:'#PDloader',
                        refreshTables:'true',
                    };
                    //Se envian los datos al formulario
                    UpdateContentId(Div, URL, Options);
                    //Indico que esta cargado
                    AlarmasLoad = 1;
                }
            }
            /******************************************/
            function tabAlarmasNew(ID) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#modalContent';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/alarmas/new/'; ?>'+ID;
                const Options = {
                    showModal : '#viewModal',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
            }
            /******************************************/
            function tabAlarmasView(ID) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#modalContent';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/alarmas/view/'; ?>'+ID;
                const Options = {
                    showModal : '#viewModal',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
            }
            /******************************************/
            function tabAlarmasEdit(ID) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#modalContent';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/alarmas/getID/'; ?>'+ID;
                const Options = {
                    showModal : '#viewModal',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
            }
            /******************************************/
            function tabAlarmasDel(ID, Dato) {
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
                        let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/alarmas'; ?>';
                        let Informacion = {"idAlarmas": ID};
                        const Options     = {
                            UpdateDiv : [
                                {Div:'#tabAlarmasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/alarmas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']); ?>', refreshTbl:'true'}
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
    <?php } ?>
</script>