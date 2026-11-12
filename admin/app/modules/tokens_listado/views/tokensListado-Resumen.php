<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se obtiene el ID
$TokenID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idToken']);
?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
    <div class="card">

        <?php
        // Datos
        $Title          = 'Resumen Token';
        $SubTitle       = 'Resumen del Token '.($data['rowData']['Nombre'] ?? '');
        $Text           = 'Información general, permisos y accesos OAuth del Token';
        $IMG            = $BASE.'/img/img-backgrounds/Archivos_1.jpg';
        $Title_color    = 25;
        $SubTitle_color = 17;
        $Text_color     = 40;
        // Imprimo
        echo $data['Fnc_WidgetsViews']->headerProfile($Title, $SubTitle, $Text, $IMG, $Title_color, $SubTitle_color, $Text_color);
        ?>

        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <?php if($data['UserAccess']['LevelAccess']>=2){ ?>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-permisos" onclick="tabPermisosLoad()"><i class="bi bi-shield-check"></i> Permisos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-oauth-clientes" onclick="tabClientesLoad()"><i class="bi bi-hdd-network"></i> Clientes OAuth</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-oauth-accesos" onclick="tabAccesosLoad()"><i class="bi bi-unlock"></i> Accesos OAuth</button></li>
                <?php } ?>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('tokensListado-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Básicos', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Nombre',             'Name' => 'Nombre',         'Id' => 'Edit_Nombre',         'Value' => ($data['rowData']['Nombre'] ?? ''),         'Required' => 2]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 8, 'Placeholder' => 'Fecha de Caducidad', 'Name' => 'FechaCaducidad', 'Id' => 'Edit_FechaCaducidad', 'Value' => ($data['rowData']['FechaCaducidad'] ?? ''), 'Required' => 1, 'Icon' => 'bi bi-calendar3', 'DataInfo' => 'Opcional. Una vez fijada, la fecha solo puede cambiarse, no borrarse']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Administración', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Estado',             'Name' => 'idEstado',       'Id' => 'Edit_idEstado',       'Value' => ($data['rowData']['idEstado'] ?? ''),       'Required' => 2, 'arrData' => $data['arrEstado']]);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idToken', 'Value' => $data['rowData']['idToken'], 'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <?php if($data['UserAccess']['LevelAccess']>=2){ ?>
                <div class="tab-pane fade" id="resumen-permisos">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Permisos de <?php echo htmlspecialchars((string)($data['rowData']['Nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabPermisosList">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-oauth-clientes">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Clientes OAuth que usan este token
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabClientesList">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-oauth-accesos">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Accesos OAuth
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabAccesosList">

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
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$TokenID['data']; ?>'},
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

    <?php if($data['UserAccess']['LevelAccess']>=2){ ?>
    /*********************************************************************/
    /*                             PERMISOS                              */
    /*********************************************************************/
    /************************************/
    function tabPermisosLoad() {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#tabPermisosList';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/permisos/updateList/'.$TokenID['data']; ?>';
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }

    /*********************************************************************/
    /*                         CLIENTES OAUTH                            */
    /*********************************************************************/
    /************************************/
    function tabClientesLoad() {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#tabClientesList';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/oauthClientes/updateList/'.$TokenID['data']; ?>';
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabClienteRevocar(ID, Nombre) {
        Swal.fire({
            title: "Revocar accesos",
            text: "Esta a punto de revocar los accesos del cliente " + Nombre + " con este token, ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, revocar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'POST';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/oauthClientes/revocar'; ?>';
                let Informacion = {"idCliente": ID, "idToken": '<?php echo $TokenID['data']; ?>'};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabClientesList', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/oauthClientes/updateList/'.$TokenID['data']; ?>'},
                        {Div:'#tabAccesosList',  fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/oauthAccesos/updateList/'.$TokenID['data']; ?>'},
                        {Div:'#resumen',         fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$TokenID['data']; ?>'},
                    ],
                    showNoti:'Accesos del Cliente Revocados',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    /*********************************************************************/
    /*                          ACCESOS OAUTH                            */
    /*********************************************************************/
    /************************************/
    function tabAccesosLoad() {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#tabAccesosList';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/oauthAccesos/updateList/'.$TokenID['data']; ?>';
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /************************************/
    function tabAccesoRevocar(ID, Nombre) {
        Swal.fire({
            title: "Revocar acceso",
            text: "Esta a punto de revocar el acceso del cliente " + Nombre + ", ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, revocar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'DELETE';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/oauthAccesos'; ?>';
                let Informacion = {"idToken": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabAccesosList', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/oauthAccesos/updateList/'.$TokenID['data']; ?>'},
                        {Div:'#resumen',        fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$TokenID['data']; ?>'},
                    ],
                    showNoti:'Acceso Revocado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    <?php } ?>

</script>
