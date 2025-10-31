<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-img"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-file"><i class="bi bi-upload"></i> Subir Archivo</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-obs" onclick="tabObsLoadList()"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('crudResumen-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['Email'] ?? '';
                                $x2  = $data['rowData']['Numero'] ?? '';
                                $x3  = $data['rowData']['Rut'] ?? '';
                                $x4  = $data['rowData']['Patente'] ?? '';
                                $x5  = $data['rowData']['Fecha'] ?? '';
                                $x6  = $data['rowData']['Hora'] ?? '';
                                $x7  = $data['rowData']['Palabra'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',   'Name' => 'Email',    'Id' => 'Edit_Email',    'Value' => $x1,'Required' => 2,'Icon' => 'bx bx-mail-send']);
                                $data['Fnc_FormInputs']->formNumberSpinner([   'Placeholder' => 'Numero',  'Name' => 'Numero',   'Id' => 'Edit_Numero',   'Value' => $x2,'Required' => 2,'Min' => 1,'Max' => 20,'Step' => 1,'Ndecimal' => 0]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',     'Name' => 'Rut',      'Id' => 'Edit_Rut',      'Value' => $x3,'Required' => 2,'Icon' => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Patente', 'Name' => 'Patente',  'Id' => 'Edit_Patente',  'Value' => $x4,'Required' => 2,'Icon' => 'ri-car-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha',   'Name' => 'Fecha',    'Id' => 'Edit_Fecha',    'Value' => $x5,'Required' => 2,'Icon' => 'bi bi-calendar3']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 10, 'Placeholder' => 'Hora',    'Name' => 'Hora',     'Id' => 'Edit_Hora',     'Value' => $x6,'Required' => 2,'Icon' => 'bi bi-clock']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Palabra', 'Name' => 'Palabra',  'Id' => 'Edit_Palabra',  'Value' => $x7,'Required' => 2]);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCrud','Value' => $data['rowData']['idCrud'],'Required' => 2]);
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
                            Imagen de <?php echo $data['rowData']['Email']; ?>
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
                                <?php $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Direccion_img','URL' => $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update','ExtraData' => '"idCrud": '.$data['rowData']['idCrud']]);?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="tab-pane fade" id="resumen-file">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            archivos de <?php echo $data['rowData']['Email']; ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <?php
                    if(isset($data['rowData']['File'])&&$data['rowData']['File']!=''){ ?>
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                <div class="d-flex justify-content-center">
                                    <?php $data['Fnc_WidgetsCommon']->previewDocs($BASE, '/upload', $data['rowData']['File']); ?>
                                </div>
                                <div class="d-flex justify-content-center pt-2">
                                    <button  onclick="delFile('<?php echo $data['rowData']['File']; ?>')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Borrar Archivo</button>
                                </div>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <form id="FormEditFile" name="FormEditFile" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                            <div class="d-flex justify-content-center pt-4">
                                <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                    <?php
                                    //se dibujan los inputs
                                    $data['Fnc_FormInputs']->formUploadMultiple(['Placeholder' => 'Subir archivos','Name' => 'File','MaxFiles' => 1,'TypeFiles' => '"jpg", "png", "gif", "jpeg", "bmp", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "mp3", "wav", "pdf", "txt", "rtf", "mp2", "mpeg", "mpg", "mov", "avi", "gz", "gzip", "7Z", "zip", "rar"']);

                                    //datos ocultos
                                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCrud','Value' => $data['rowData']['idCrud'],'Required' => 2]);
                                    ?>
                                    <div class="text-center pt-4">
                                        <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php } ?>
                </div>

                <div class="tab-pane fade" id="resumen-obs">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Observaciones de <?php echo $data['rowData']['Email']; ?>
                            <button type="button" class="btn btn-success"  onclick="tabObsNew('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCrud']); ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
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
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCrud']); ?>'},
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
                    "idCrud": <?php echo $data['rowData']['idCrud']; ?>,
                    "Direccion_img": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCrud']); ?>',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /*********************************************************************/
    /*                             ARCHIVOS                              */
    /*********************************************************************/
    /******************************************/
    $("#FormEditFile").submit(function(e) {
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
            let Informacion = appendFiles('#FormEditFile', 'File', 1);
            const Options     = {
                Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCrud']); ?>',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataFormsFiles(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    function delFile(File) {
        Swal.fire({
            title: "Borrar Archivo",
            text: "Esta a punto de borrar el Archivo, ¿Desea continuar?",
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
                    "idCrud": <?php echo $data['rowData']['idCrud']; ?>,
                    "File": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCrud']); ?>',
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
    /******************************************/
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
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCrud']); ?>';
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
                        {Div:'#tabObsDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCrud']); ?>', refreshTbl:'true'}
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