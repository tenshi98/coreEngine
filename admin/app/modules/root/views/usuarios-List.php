<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <div class="btn-group" role="group">
                                <button class="btn btn-secondary tooltiplink" data-title="Filtrar Información" type="button" data-bs-toggle="collapse" data-bs-target="#formSearch" aria-expanded="false" aria-controls="formSearch"><i class="bi bi-search"></i> Filtrar</button>
                                <button class="btn btn-danger tooltiplink"    data-title="Quitar Filtro"       type="button" onclick="deleteFilter()"><i class="ri-filter-off-line"></i></button>
                            </div>
                            <?php echo $data['TableTitle']; ?>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newFormModal"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <?php require_once('usuarios-formSearch.php'); ?>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="X_datatable">
                        <?php require_once('usuarios-UpdateList.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('usuarios-formNew.php'); ?>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="modalContent">

        </div>
    </div>
</div>

<script>
    /******************************************/
    function TDviewBTN(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/Core/administracion/usuarios/view/'; ?>'+ID;
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
                let Direccion   = '<?php echo $BASE.'/Core/administracion/usuarios'; ?>';
                let Informacion = {"idUsuario": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#X_datatable', fromData:'<?php echo $BASE.'/Core/administracion/usuarios/updateList'; ?>', refreshTbl:'true'}
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
    function deleteFilter(collapse=null) {
        //Cargo el loader
        $('#PDloader').show();
        //Opciones
        const Options = {
            refreshTables : 'true',
            closeObject:'#PDloader',
        };
        //refrescar
        UpdateContentId('#X_datatable', '<?php echo $BASE.'/Core/administracion/usuarios/updateList'; ?>', Options);
        //Se muestra el modal
        if(typeof collapse !== 'undefined' && collapse != null && collapse!=''){
            //Se ocultan elementos
            $(collapse).collapse("toggle");
        }
    }
</script>

