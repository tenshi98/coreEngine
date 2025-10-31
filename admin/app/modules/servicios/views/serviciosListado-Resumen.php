<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-img"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
                <?php if($data['UserData']["serviciosListadoVerDocumentos"]==2){ ?>  <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-documentos" onclick="tabDocumentosLoadList()"><i class="bi bi-file-text"></i> Documentos</button></li><?php } ?>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-obs" onclick="tabObsLoadList()"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('serviciosListado-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1 = $data['rowData']['idCategoria'] ?? '';
                                $x2 = $data['rowData']['Nombre'] ?? '';
                                $x3 = $data['rowData']['Codigo'] ?? '';
                                $x4 = $data['rowData']['Descripcion'] ?? '';
                                $x5 = $data['rowData']['ValorIngreso'] ?? '';
                                $x6 = $data['rowData']['ValorEgreso'] ?? '';
                                $x7 = $data['rowData']['idEstado'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Básicos']);
                                $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Categoria',      'Name' => 'idCategoria',      'Id' => 'Edit_idCategoria',       'Value' => $x1,'Required' => 2,'arrData' => $data['arrCategoria'], 'BASE' => $BASE]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',         'Name' => 'Nombre',           'Id' => 'Edit_Nombre',            'Value' => $x2,'Required' => 2]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Codigo',         'Name' => 'Codigo',           'Id' => 'Edit_Codigo',            'Value' => $x3,'Required' => 1,'Icon' => 'ri-barcode-line']);
                                $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Descripcion',    'Name' => 'Descripcion',      'Id' => 'Edit_Descripcion',       'Value' => $x4,'Required' => 1]);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Básicos']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 5,  'Placeholder' => 'Valor Ingreso',  'Name' => 'ValorIngreso',     'Id' => 'Edit_ValorIngreso',      'Value' => $x5, 'Required' => 1,'Icon' => 'bi bi-sort-numeric-down']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 5,  'Placeholder' => 'Valor Egreso',   'Name' => 'ValorEgreso',      'Id' => 'Edit_ValorEgreso',       'Value' => $x6, 'Required' => 1,'Icon' => 'bi bi-sort-numeric-down']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Administración']);
                                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',         'Name' => 'idEstado',         'Id' => 'Search_idEstado',        'Value' => $x7, 'Required' => 2,'arrData' => $data['arrEstado']]);


                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idServicio','Value' => $data['rowData']['idServicio'],'Required' => 2]);
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
                                <?php $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Direccion_img','URL' => $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update','ExtraData' => '"idServicio": '.$data['rowData']['idServicio']]);?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <?php
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idServicio']);
                ?>

                <?php if($data['UserData']["serviciosListadoVerDocumentos"]==2){ ?>
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
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idServicio']); ?>'},
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
                    "idServicio": <?php echo $data['rowData']['idServicio']; ?>,
                    "Direccion_img": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idServicio']); ?>',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    <?php if($data['UserData']["serviciosListadoVerDocumentos"]==2){ ?>
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
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idServicio']); ?>';
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
                            {Div:'#tabDocumentosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idServicio']); ?>', refreshTbl:'true'}
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
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idServicio']); ?>';
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
                        {Div:'#tabObsDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idServicio']); ?>', refreshTbl:'true'}
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