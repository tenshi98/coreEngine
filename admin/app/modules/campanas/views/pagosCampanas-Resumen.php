<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
    <div class="card">
        <div class="card-body pt-3">
            <h5 class="card-title">
                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                    Pagos Realizados <strong><?php echo $data['rowData']['Documento'].' '.($data['rowData']['N_Doc'] ?? 'nRef '.$data['rowData']['idFacturacion']).' ('.$data['Fnc_DataNumbers']->Valores($data['rowData']['ValorTotal'], 0).')';?></strong>
                    <button type="button" class="btn btn-success"  onclick="tabPagoNew('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>')"><i class="bi bi-file-earmark"></i> Ingresar Nuevo</button>
                </div>
            </h5>
            <div class="clearfix"></div>
            <div class="table-responsive" id="tabPagoDataTable">

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
    /*                               LOAD                                */
    /*********************************************************************/
    $(document).ready(function() {
        tabPagoLoadList();
    });
    /*********************************************************************/
    /*                              Pagos                                */
    /*********************************************************************/
    /******************************************/
    function tabPagoLoadList() {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#tabPagoDataTable';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>';
        const Options = {
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPagoNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPagoEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabPagoDel(ID, Dato) {
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos'; ?>';
                let Informacion = {"idPago": ID};
                const Options     = {
                    Function:'tabPagoLoadList',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
</script>
