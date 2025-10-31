<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <?php if($data['UserData']["entidadesListadoUsoPlanes"]==2){ ?>     <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-planes"    onclick="tabPlanesLoadList()"><i class="bi bi-currency-dollar"></i> Planes</button></li><?php } ?>
                <?php if($data['UserData']["entidadesListadoUsoUsuarios"]==2){ ?>   <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-usuarios"  onclick="tabUsuariosLoadList()"><i class="bi bi-person"></i> Usuarios</button></li><?php } ?>
                <?php if($data['UserData']["entidadesListadoUsoMaquinas"]==2){ ?>   <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-maquinas"  onclick="tabMaquinasLoadList()"><i class="bi bi-gear-fill"></i> Máquinas</button></li><?php } ?>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('tercerosEntidadesListado-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    asd

                </div>

                <?php
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']);
                ?>

                <?php if($data['UserData']["entidadesListadoUsoPlanes"]==2){ ?>
                    <div class="tab-pane fade" id="resumen-planes">
                        <h5 class="card-title">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                Planes de <?php echo $Entidad; ?>
                                <button type="button" class="btn btn-success"  onclick="tabPlanesNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                            </div>
                        </h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabPlanesDataTable">

                        </div>
                    </div>
                <?php } ?>

                <?php if($data['UserData']["entidadesListadoUsoUsuarios"]==2){ ?>
                    <div class="tab-pane fade" id="resumen-usuarios">
                        <h5 class="card-title">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                Usuarios de <?php echo $Entidad; ?>
                                <button type="button" class="btn btn-success"  onclick="tabUsuariosNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                            </div>
                        </h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabUsuariosDataTable">

                        </div>
                    </div>
                <?php } ?>

                <?php if($data['UserData']["entidadesListadoUsoMaquinas"]==2){ ?>
                    <div class="tab-pane fade" id="resumen-maquinas">
                        <h5 class="card-title">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                Máquinas de <?php echo $Entidad; ?>
                                <button type="button" class="btn btn-success"  onclick="tabMaquinasNew('<?php echo $encryptedId; ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                            </div>
                        </h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive" id="tabMaquinasDataTable">

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
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>'},
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

    <?php if($data['UserData']["entidadesListadoUsoPlanes"]==2){ ?>
        /*********************************************************************/
        /*                             PLANES                                */
        /*********************************************************************/
        //Variables
        let PlanesLoad = 0;
        /******************************************/
        function tabPlanesLoadList() {
            //Comparo
            if(PlanesLoad===0){
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#tabPlanesDataTable';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>';
                const Options = {
                    closeObject:'#PDloader',
                    refreshTables:'true',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
                //Indico que esta cargado
                PlanesLoad = 1;
            }
        }
        /******************************************/
        function tabPlanesNew(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes/new/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabPlanesView(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes/view/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabPlanesEdit(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes/getID/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabPlanesDel(ID, Dato) {
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
                    let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes'; ?>';
                    let Informacion = {"idPlan": ID};
                    const Options     = {
                        UpdateDiv : [
                            {Div:'#tabPlanesDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
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

    <?php if($data['UserData']["entidadesListadoUsoUsuarios"]==2){ ?>
        /*********************************************************************/
        /*                              USUARIOS                             */
        /*********************************************************************/
        //Variables
        let UsuariosLoad = 0;
        /******************************************/
        function tabUsuariosLoadList() {
            //Comparo
            if(UsuariosLoad===0){
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#tabUsuariosDataTable';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuarios/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>';
                const Options = {
                    closeObject:'#PDloader',
                    refreshTables:'true',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
                //Indico que esta cargado
                UsuariosLoad = 1;
            }
        }
        /******************************************/
        function tabUsuariosNew(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuarios/new/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabUsuariosView(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent_2';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuarios/view/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal_2',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabUsuariosEdit(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuarios/getID/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabUsuariosDel(ID, Dato) {
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
                    let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuarios'; ?>';
                    let Informacion = {"idUsuario": ID};
                    const Options     = {
                        UpdateDiv : [
                            {Div:'#tabUsuariosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuarios/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
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
        function tabUsuariosEditMaq(idEntidad, idUsuario) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent_2';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuariosMaq/updateList/'; ?>'+idEntidad+'/'+idUsuario;
            const Options = {
                showModal : '#viewModal_2',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabUsuariosEditNoti(idEntidad, idUsuario) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent_2';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuariosNoti/updateList/'; ?>'+idEntidad+'/'+idUsuario;
            const Options = {
                showModal : '#viewModal_2',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
    <?php } ?>

    <?php if($data['UserData']["entidadesListadoUsoMaquinas"]==2){ ?>
        /*********************************************************************/
        /*                            MAQUINAS                               */
        /*********************************************************************/
        //Variables
        let MaquinasLoad = 0;
        /******************************************/
        function tabMaquinasLoadList() {
            //Comparo
            if(MaquinasLoad===0){
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Div       = '#tabMaquinasDataTable';
                let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/maquinas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>';
                const Options = {
                    closeObject:'#PDloader',
                    refreshTables:'true',
                };
                //Se envian los datos al formulario
                UpdateContentId(Div, URL, Options);
                //Indico que esta cargado
                MaquinasLoad = 1;
            }
        }
        /******************************************/
        function tabMaquinasNew(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/maquinas/new/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabMaquinasView(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/maquinas/view/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabMaquinasEdit(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/maquinas/getID/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function tabMaquinasDel(ID, Dato) {
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
                    let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/maquinas'; ?>';
                    let Informacion = {"idMaq": ID};
                    const Options     = {
                        UpdateDiv : [
                            {Div:'#tabMaquinasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/maquinas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
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

</script>