<section class="section profile">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">

            <div class="card">
                <div class="card-body profile-card pt-4 d-flex flex-column align-items-center" id="profile-card">
                    <?php require_once('miUsuario-data-UpdateCard.php'); ?>
                </div>
            </div>

        </div>

        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 col-xl-9 col-xxl-9" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

            <div class="card">
                <div class="card-body pt-3">

                    <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                        <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#profile-resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                        <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#profile-edit"><i class="bi bi-pencil-square"></i> Editar Perfil</button></li>
                        <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#profile-img"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
                        <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#settings"><i class="bi bi-wrench"></i> Configuración</button></li>
                        <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#profile-change-password"><i class="bi bi-key"></i> Cambiar Password</button></li>
                    </ul>
                    <div class="tab-content pt-2">

                        <div class="tab-pane fade show active profile-overview" id="profile-resumen">
                            <?php require_once('miUsuario-data-UpdateData.php'); ?>
                        </div>

                        <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                            <form id="FormData" name="FormData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">

                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['Nombre'] ?? '';
                                $x2  = $data['rowData']['Rut'] ?? '';
                                $x3  = $data['rowData']['fNacimiento'] ?? '';
                                $x4  = $data['rowData']['Fono'] ?? '';
                                $x5  = $data['rowData']['idCiudad'] ?? '';
                                $x6  = $data['rowData']['idComuna'] ?? '';
                                $x7  = $data['rowData']['Direccion'] ?? '';
                                $x8  = $data['rowData']['Social_X'] ?? '';
                                $x9  = $data['rowData']['Social_Facebook'] ?? '';
                                $x10 = $data['rowData']['Social_Instagram'] ?? '';
                                $x11 = $data['rowData']['Social_Linkedin'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Datos Personales']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Nombre',              'Name'  => 'Nombre',      'Value'  => $x1,'Required'  => 2]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder'  => 'Rut',                 'Name'  => 'Rut',         'Value'  => $x2,'Required'  => 2,'Icon' => 'bi bi-person-circle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder'  => 'Fecha de Nacimiento', 'Name'  => 'fNacimiento', 'Value'  => $x3,'Required'  => 1,'Icon' => 'bi bi-calendar3']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder'  => 'Fono',                'Name'  => 'Fono',        'Value'  => $x4,'Required'  => 1,'Icon' => 'bi bi-telephone-fill']);
                                $data['Fnc_FormInputs']->formSelectDepend([           'Placeholder1' => 'Ciudad',              'Name1' => 'idCiudad',    'Value1' => $x5,'Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                                                      'Placeholder2' => 'Comuna',              'Name2' => 'idComuna',    'Value2' => $x6,'Required2' => 1,'arrData2' => $data['arrComuna']]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Dirección',           'Name'  => 'Direccion',   'Value'  => $x7,'Required'  => 1,'Icon' => 'bi bi-geo-alt-fill']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Social']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'X (Twitter)', 'Name' => 'Social_X',         'Value' => $x8, 'Required' => 1, 'Icon' => 'bi bi-x']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Facebook',    'Name' => 'Social_Facebook',  'Value' => $x9, 'Required' => 1, 'Icon' => 'bi bi-facebook']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Instagram',   'Name' => 'Social_Instagram', 'Value' => $x10,'Required' => 1, 'Icon' => 'bi bi-instagram']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Linkedin',    'Name' => 'Social_Linkedin',  'Value' => $x11,'Required' => 1, 'Icon' => 'bi bi-linkedin']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario','Value' => $data['UserData']['UserID'],'Required' => 2]);
                                ?>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </form>

                        </div>

                        <div class="tab-pane fade" id="profile-img">
                            <?php
                            if(isset($data['rowData']['Direccion_img'])&&$data['rowData']['Direccion_img']!=''){ ?>
                                <div class="d-flex justify-content-center pt-3">
                                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-xl-6 col-xxl-5">
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
                                        <?php $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Direccion_img','URL' => $BASE.'/perfil/update','ExtraData' => '"idUsuario": '.$data["UserData"]["UserID"].',"Nombre": "'.$data['rowData']['Nombre'].'"']); ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="tab-pane fade profile-edit pt-3" id="settings">

                            <form id="FormOpciones" name="FormOpciones" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">

                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['idMenuPosicion'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formPostData(1, 4, 'exclamation-circle', 0, '<strong>Posición Menu: </strong> permite seleccionar si se va a utilizar el menu lateral o el menu superior');
                                $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Posición Menu', 'Name' => 'idMenuPosicion','Value' => $x1,'Required' => 2,'arrData' => $data['arrPosicion']]);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario','Value' => $data['UserData']['UserID'],'Required' => 2]);
                                ?>


                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </form>

                        </div>

                        <div class="tab-pane fade profile-edit pt-3" id="profile-change-password">

                            <form id="FormPassword" name="FormPassword" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">

                                <?php
                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formInput(['FormType' => 3,'Placeholder' => 'Contraseña Actual',        'Name' => 'oldPassword', 'Required' => 2,'Icon' => 'bi bi-key']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 3,'Placeholder' => 'Nueva Contraseña',         'Name' => 'password',    'Required' => 2,'Icon' => 'bi bi-key']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 3,'Placeholder' => 'Repetir Nueva Contraseña', 'Name' => 'rePassword',  'Required' => 2,'Icon' => 'bi bi-key']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',    'Value' => $data['UserData']['UserID'],  'Required' => 2]);
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'mainPassword', 'Value' => $data['rowData']['password'], 'Required' => 2]);
                                ?>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Cambiar Contraseña</button>
                                </div>
                            </form>

                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormData").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/perfil/update'; ?>';
            let Informacion = $("#FormData").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#profile-resumen', fromData:'<?php echo $BASE.'/perfil/UpdateData'; ?>'},
                    {Div:'#profile-card', fromData:'<?php echo $BASE.'/perfil/UpdateCard'; ?>'}
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#profile-resumen"]',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

    /******************************************/
    $("#FormPassword").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/perfil/update'; ?>';
            let Informacion = $("#FormPassword").serialize();
            const Options     = {
                Destino:'<?php echo $BASE.'/perfil'; ?>',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

    /******************************************/
    $("#FormOpciones").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/perfil/update'; ?>';
            let Informacion = $("#FormOpciones").serialize();
            const Options     = {
                Destino:'<?php echo $BASE.'/perfil'; ?>',
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
            text: "Esta a punto de borrar la imagen de su perfil, ¿Desea continuar?",
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
                let Direccion   = '<?php echo $BASE.'/perfil/delFiles'; ?>';
                let Informacion = {
                    "idUsuario": <?php echo $data["UserData"]["UserID"]; ?>,
                    "Direccion_img": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/perfil'; ?>',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
</script>