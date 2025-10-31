<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-img"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-permisos"><i class="bi bi-exclamation-diamond"></i> Permisos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-obs" onclick="tabObsLoadList()"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('usuariosListado-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['email'] ?? '';
                                $x2  = $data['rowData']['Nombre'] ?? '';
                                $x3  = $data['rowData']['Rut'] ?? '';
                                $x4  = $data['rowData']['fNacimiento'] ?? '';
                                $x5  = $data['rowData']['Fono'] ?? '';
                                $x6  = $data['rowData']['idCiudad'] ?? '';
                                $x7  = $data['rowData']['idComuna'] ?? '';
                                $x8  = $data['rowData']['Direccion'] ?? '';
                                $x9  = $data['rowData']['idTipoUsuario'] ?? '';
                                $x10 = $data['rowData']['idEstado'] ?? '';
                                $x11 = $data['rowData']['Social_X'] ?? '';
                                $x12 = $data['rowData']['Social_Facebook'] ?? '';
                                $x13 = $data['rowData']['Social_Instagram'] ?? '';
                                $x14 = $data['rowData']['Social_Linkedin'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Datos Personales']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',               'Name'  => 'email',        'Id'  => 'Edit_email',       'Value'  => $x1,  'Required'  => 2, 'Icon' => 'bx bx-mail-send']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',              'Name'  => 'Nombre',       'Id'  => 'Edit_Nombre',      'Value'  => $x2,  'Required'  => 2]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',                 'Name'  => 'Rut',          'Id'  => 'Edit_Rut',         'Value'  => $x3,  'Required'  => 1, 'Icon' => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Nacimiento', 'Name'  => 'fNacimiento',  'Id'  => 'Edit_fNacimiento', 'Value'  => $x4,  'Required'  => 1, 'Icon' => 'bi bi-calendar3']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono',                'Name'  => 'Fono',         'Id'  => 'Edit_Fono',        'Value'  => $x5,  'Required'  => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formSelectDepend([          'Placeholder1' => 'Ciudad',             'Name1' => 'idCiudad',     'Id1' => 'Edit_idCiudad',    'Value1' => $x6,  'Required1' => 1, 'arrData1' => $data['arrCiudad'],
                                                                                       'Placeholder2' => 'Comuna',             'Name2' => 'idComuna',     'Id2' => 'Edit_idComuna',    'Value2' => $x7,  'Required2' => 1, 'arrData2' => $data['arrComuna'],
                                                                                       'FormName' => 'FormEditData']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Dirección', 'Name' => 'Direccion',  'Id' => 'Edit_Direccion', 'Value' => $x8,'Required' => 1,'Icon' => 'bi bi-geo-alt-fill']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Configuración']);
                                $data['Fnc_FormInputs']->formSelectFilter([         'Placeholder' => 'Tipo de Usuario',  'Name' => 'idTipoUsuario', 'Id' => 'Edit_idTipoUsuario',  'Value' => $x9, 'Required' => 2,'arrData' => $data['arrTipoUsuario'], 'BASE' => $BASE]);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Social']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'X (Twitter)', 'Name' => 'Social_X',          'Id' => 'Edit_Social_X',         'Value' => $x11, 'Required' => 1, 'Icon' => 'bi bi-x']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Facebook',    'Name' => 'Social_Facebook',   'Id' => 'Edit_Social_Facebook',  'Value' => $x12, 'Required' => 1, 'Icon' => 'bi bi-facebook']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Instagram',   'Name' => 'Social_Instagram',  'Id' => 'Edit_Social_Instagram', 'Value' => $x13, 'Required' => 1, 'Icon' => 'bi bi-instagram']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Linkedin',    'Name' => 'Social_Linkedin',   'Id' => 'Edit_Social_Linkedin',  'Value' => $x14, 'Required' => 1, 'Icon' => 'bi bi-linkedin']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Administración']);
                                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',       'Name' => 'idEstado',   'Id' => 'Edit_idEstado',  'Value'  => $x10,  'Required' => 2,'arrData' => $data['arrEstado']]);
                                $data['Fnc_FormInputs']->formPostData(1, 4, 'exclamation-circle', 0, '<strong>Contraseña: </strong> permite modificar arbitrariamente la contraseña.');
                                $data['Fnc_FormInputs']->formInput(['FormType' => 3,  'Placeholder' => 'Contraseña',   'Name' => 'password',   'Id' => 'Edit_password',  'Value'  => '',    'Required' => 1,'Icon' => 'bi bi-key']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario','Value' => $data['rowData']['idUsuario'],'Required' => 2]);
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
                                <?php $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Direccion_img','URL' => $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update','ExtraData' => '"idUsuario": '.$data['rowData']['idUsuario']]);?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="tab-pane fade" id="resumen-permisos">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Permisos de <?php echo $data['rowData']['Nombre']; ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <?php require_once('usuariosListado-Resumen-Permisos-List.php'); ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-obs">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Observaciones de <?php echo $data['rowData']['Nombre']; ?>
                            <button type="button" class="btn btn-success"  onclick="tabObsNew('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idUsuario']); ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
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
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idUsuario']); ?>'},
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
                    "idUsuario": <?php echo $data['rowData']['idUsuario']; ?>,
                    "Direccion_img": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idUsuario']); ?>',
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
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idUsuario']); ?>';
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
                        {Div:'#tabObsDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idUsuario']); ?>', refreshTbl:'true'}
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