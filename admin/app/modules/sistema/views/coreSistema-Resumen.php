<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_1"><i class="bi bi-pencil-square"></i> Editar Datos Basicos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_2"><i class="bi bi-bookmark-check"></i> Editar Contacto</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_3"><i class="bi bi-person-square"></i> Editar Representante</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_4"><i class="bi bi-puzzle"></i> Editar APIS</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit_5"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('coreSistema-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit_1">

                    <form id="FormEditData_1" name="FormEditData_1" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['Sistema_Nombre'] ?? '';
                                $x2  = $data['rowData']['Sistema_Email'] ?? '';
                                $x3  = $data['rowData']['Sistema_Rut'] ?? '';
                                $x4  = $data['rowData']['Sistema_idCiudad'] ?? '';
                                $x5  = $data['rowData']['Sistema_idComuna'] ?? '';
                                $x6  = $data['rowData']['Sistema_Direccion'] ?? '';
                                $x7  = $data['rowData']['Sistema_NotiWhatsapp'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Nombre',    'Name'  => 'Sistema_Nombre',    'Value'  => $x1,'Required'  => 2]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder'  => 'Email',     'Name'  => 'Sistema_Email',     'Value'  => $x2,'Required'  => 1,'Icon' => 'bx bx-mail-send']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder'  => 'Rut',       'Name'  => 'Sistema_Rut',       'Value'  => $x3,'Required'  => 1,'Icon' => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formSelectDepend([           'Placeholder1' => 'Ciudad',    'Name1' => 'Sistema_idCiudad',  'Value1' => $x4,'Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                                                      'Placeholder2' => 'Comuna',    'Name2' => 'Sistema_idComuna',  'Value2' => $x5,'Required2' => 1,'arrData2' => $data['arrComuna']]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Dirección', 'Name'  => 'Sistema_Direccion', 'Value'  => $x6,'Required'  => 1,'Icon' => 'bi bi-geo-alt-fill']);
                                $data['Fnc_FormInputs']->formPostData(4, 4, 'exclamation-circle', 0, 'El numero debe ingresarse iniciando con 56, sin el simbolo + y sin espacios ni separaciones');
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder'  => 'Fono Noti Whatsapp',  'Name' => 'Sistema_NotiWhatsapp',  'Value' => $x7, 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idSistema','Value' => $data['rowData']['idSistema'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-edit_2">

                    <form id="FormEditData_2" name="FormEditData_2" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['Contacto_Nombre'] ?? '';
                                $x2  = $data['rowData']['Contacto_Fono1'] ?? '';
                                $x3  = $data['rowData']['Contacto_Fono2'] ?? '';
                                $x4  = $data['rowData']['Contacto_Fax'] ?? '';
                                $x5  = $data['rowData']['Contacto_Email'] ?? '';
                                $x6  = $data['rowData']['Contacto_Web'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre', 'Name' => 'Contacto_Nombre',   'Value' => $x1, 'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono 1', 'Name' => 'Contacto_Fono1',    'Value' => $x2, 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono 2', 'Name' => 'Contacto_Fono2',    'Value' => $x3, 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fax',    'Name' => 'Contacto_Fax',      'Value' => $x4, 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',  'Name' => 'Contacto_Email',    'Value' => $x5, 'Required' => 1, 'Icon' => 'bx bx-mail-send']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Web',    'Name' => 'Contacto_Web',      'Value' => $x6, 'Required' => 1, 'Icon' => 'ri-edge-fill']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idSistema','Value' => $data['rowData']['idSistema'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-edit_3">

                    <form id="FormEditData_3" name="FormEditData_3" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['RepresentanteNombre'] ?? '';
                                $x2  = $data['rowData']['RepresentanteRut'] ?? '';
                                $x3  = $data['rowData']['RepresentanteFono'] ?? '';
                                $x4  = $data['rowData']['RepresentanteEmail'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre', 'Name' => 'RepresentanteNombre',  'Value' => $x1, 'Required' => 1]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',    'Name' => 'RepresentanteRut',     'Value' => $x2, 'Required' => 1, 'Icon' => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono 1', 'Name' => 'RepresentanteFono',    'Value' => $x3, 'Required' => 1, 'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',  'Name' => 'RepresentanteEmail',   'Value' => $x4, 'Required' => 1, 'Icon' => 'bx bx-mail-send']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idSistema','Value' => $data['rowData']['idSistema'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-edit_4">

                    <form id="FormEditData_4" name="FormEditData_4" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                //$x1  = $data['rowData']['Config_API_GoogleMaps'] ?? '';
                                $x2  = $data['rowData']['Config_WhatsappToken'] ?? '';
                                $x3  = $data['rowData']['Config_WhatsappInstanceId'] ?? '';

                                //se dibujan los inputs
                                //$data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API GoogleMaps',           'Name' => 'Config_API_GoogleMaps',      'Value' => $x1, 'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API Whatsapp Token',       'Name' => 'Config_WhatsappToken',       'Value' => $x2, 'Required' => 1, 'Icon' => 'bi bi-puzzle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'API Whatsapp Instance Id', 'Name' => 'Config_WhatsappInstanceId',  'Value' => $x3, 'Required' => 1, 'Icon' => 'bi bi-puzzle']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idSistema','Value' => $data['rowData']['idSistema'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-edit_5">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Imagen de <?php echo $data['rowData']['Sistema_Nombre']; ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <?php
                    if(isset($data['rowData']['Sistema_IMGLogo'])&&$data['rowData']['Sistema_IMGLogo']!=''){ ?>
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-8 col-md-6 col-lg-6 col-xl-4 col-xxl-3">
                                <div class="d-flex justify-content-center">
                                    <img src="<?php echo $BASE.'/upload/'.$data['rowData']['Sistema_IMGLogo']; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
                                </div>
                                <div class="d-flex justify-content-center pt-2">
                                    <button  onclick="delIMG('<?php echo $data['rowData']['Sistema_IMGLogo']; ?>')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Borrar Imagen</button>
                                </div>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <div class="d-flex justify-content-center pt-3">
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-xl-6 col-xxl-5">
                                <?php $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Sistema_IMGLogo','URL' => $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update','ExtraData' => '"idSistema": '.$data['rowData']['idSistema']]);?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormEditData_1").submit(function(e) {
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
            let Informacion = $("#FormEditData_1").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate'; ?>'},
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    $("#FormEditData_2").submit(function(e) {
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
            let Informacion = $("#FormEditData_2").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate'; ?>'},
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    $("#FormEditData_3").submit(function(e) {
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
            let Informacion = $("#FormEditData_3").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate'; ?>'},
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    $("#FormEditData_4").submit(function(e) {
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
            let Informacion = $("#FormEditData_4").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate'; ?>'},
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
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
                    "idSistema": <?php echo $data['rowData']['idSistema']; ?>,
                    "Sistema_IMGLogo": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
</script>