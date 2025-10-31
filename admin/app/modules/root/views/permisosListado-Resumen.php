<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-rutas"><i class="bi bi-file-earmark-text"></i> Rutas</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('permisosListado-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //Se verifican si existen los datos
                                $x1  = $data['rowData']['idPermisosCat'] ?? '';
                                $x2  = $data['rowData']['idEstado'] ?? '';
                                $x3  = $data['rowData']['idTipo'] ?? '';
                                $x4  = $data['rowData']['Nombre'] ?? '';
                                $x5  = $data['rowData']['Descripcion'] ?? '';
                                $x6  = $data['rowData']['idLevelLimit'] ?? '';
                                $x7  = $data['rowData']['RutaWeb'] ?? '';
                                $x8  = $data['rowData']['RutaController'] ?? '';

                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Categoria Permiso',  'Name' => 'idPermisosCat',  'Id' => 'Edit_idPermisosCat',  'Value' => $x1, 'Required' => 2,'arrData' => $data['arrPermisosCat']]);
                                $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Estado',             'Name' => 'idEstado',       'Id' => 'Edit_idEstado',       'Value' => $x2, 'Required' => 2,'arrData' => $data['arrEstados']]);
                                $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Tipo',               'Name' => 'idTipo',         'Id' => 'Edit_idTipo',         'Value' => $x3, 'Required' => 2,'arrData' => $data['arrTipos']]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Nombre',             'Name' => 'Nombre',         'Id' => 'Edit_Nombre',         'Value' => $x4, 'Required' => 2]);
                                $data['Fnc_FormInputs']->formTextarea([              'Placeholder' => 'Descripcion',        'Name' => 'Descripcion',    'Id' => 'Edit_Descripcion',    'Value' => $x5, 'Required' => 1]);
                                $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Nivel Acceso',       'Name' => 'idLevelLimit',   'Id' => 'Edit_idLevelLimit',   'Value' => $x6, 'Required' => 2,'arrData' => $data['arrLevelLimit']]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Ruta Web',           'Name' => 'RutaWeb',        'Id' => 'Edit_RutaWeb',        'Value' => $x7, 'Required' => 2,'Icon' => 'bi bi-puzzle']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Controlador',        'Name' => 'RutaController', 'Id' => 'Edit_RutaController', 'Value' => $x8, 'Required' => 2,'Icon' => 'bi bi-share-fill']);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idPermisos','Value' => $data['rowData']['idPermisos'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-rutas">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Rutas de <?php echo $data['rowData']['Nombre']; ?>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newFormModal"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="X_datatable">
                        <?php require_once('permisosListado-Resumen-Rutas-UpdateList.php'); ?>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="clearfix"></div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="<?php echo $BASE.'/Core/permisos/listado/listAll'; ?>" class="btn btn-danger float-end"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>

<?php require_once('permisosListado-Resumen-Rutas-formNew.php'); ?>


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
            let Direccion   = '<?php echo $BASE.'/Core/permisos/listado/update'; ?>';
            let Informacion = $("#FormEditData").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/Core/permisos/listado/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idPermisos']); ?>'},
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
    function TDviewBTN(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/Core/permisos/listado/rutas/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function TDeditBTN(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/Core/permisos/listado/rutas/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function TDdelBTN(ID, Dato) {
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
                let Direccion   = '<?php echo $BASE.'/Core/permisos/listado/rutas'; ?>';
                let Informacion = {"idRutas": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#X_datatable', fromData:'<?php echo $BASE.'/Core/permisos/listado/rutas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idPermisos']); ?>', refreshTbl:'true'}
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