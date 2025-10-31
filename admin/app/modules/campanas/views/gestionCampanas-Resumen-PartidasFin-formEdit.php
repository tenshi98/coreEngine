<?php
//Se obtiene el nombre o la razón social
$Entidad  = '';
$Entidad .= !empty($data['rowData']['EntidadNick'])
            ? $data['rowData']['EntidadNick'].' | '
            : '';
$Entidad .= !empty($data['rowData']['EntidadNombre'])
            ? $data['rowData']['EntidadApellido'].' '.$data['rowData']['EntidadNombre']
            : $data['rowData']['EntidadRazonSocial'];

?>
<form id="FormEditPartidaFin" name="FormEditPartidaFin" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar Partida '.$Entidad.'
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-pencil-square"></i></div>
                    Editar Partida '.$Entidad.'<br>
                    <small>Permite editar una partida existente</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <?php
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Partida',        'Name' => 'Fecha',              'Id' => 'PartidaFin_Fecha',      'Value' => $data['rowData']['Fecha'], 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Entidad',              'Name' => 'idEntidad',          'Id' => 'PartidaFin_idEntidad',  'Value' => $data['rowData']['idEntidad'],                                               'Required' => 2, 'arrData' => $data['arrEntidades'], 'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Sector',               'Name' => 'SectorFake',         'Id' => 'SectorFake',            'Value' => $data['rowData']['EntidadSector'],                                           'Required' => 3]);
        $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Dirección',            'Name' => 'DireccionFake',      'Id' => 'DireccionFake',         'Value' => $data['rowData']['EntidadDireccion'],                                        'Required' => 3]);
        $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Estado Partida',       'Name' => 'EstadoPartidaFake',  'Id' => 'EstadoPartidaFake',     'Value' => $data['rowData']['EstadoPartida'],                                           'Required' => 3]);
        $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Documento Mercantil',  'Name' => 'idDocumentosFake',   'Id' => 'idDocumentosFake',      'Value' => $data['rowData']['Documento'],                                               'Required' => 3]);
        $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Numero Documento',     'Name' => 'N_DocFake',          'Id' => 'N_DocFake',             'Value' => $data['rowData']['N_Doc'],                                                   'Required' => 3]);
        $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Fecha de Venta',       'Name' => 'Creacion_fechaFake', 'Id' => 'Creacion_fechaFake',    'Value' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Creacion_fecha']),   'Required' => 3]);
        $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Observaciones',        'Name' => 'ObservacionesFake',  'Id' => 'ObservacionesFake',     'Value' => $data['rowData']['Observaciones'],                                           'Required' => 3]);

        ?>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="productos_List">
            <?php
            //Recorro
            foreach ($data['arrPartidasProd'] AS $dataP){
                //Variables
                $dataProd_1 = $dataP['Producto'];
                $dataProd_2 = (!empty($dataP['Cantidad']) && $dataP['Cantidad'] != 0)
                            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($dataP['Cantidad'])
                            : '';
                $dataProd_3 = (!empty($dataP['Beneficios']) && $dataP['Beneficios'] != 0)
                            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($dataP['Beneficios'])
                            : '';
                //Datos ocultos
                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Producto_idProdCamp[]',   'Value' => $dataP['idProdCamp'], 'Required' => 2]);
                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Producto_idProducto[]',   'Value' => $dataP['idProducto'], 'Required' => 2]);
                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_Producto_Cantidad[]', 'Value' => $dataP['Cantidad'],   'Required' => 2]);
                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_Producto_Valor[]',    'Value' => $dataP['Beneficios'], 'Required' => 2]);
                ?>
                <div class="row">
                    <div class="col-6"> <?php $data['Fnc_FormInputs']->formInput([                                 'FormType' => 1, 'Placeholder' => 'Producto',     'Name' => 'ProductoFake',         'Value' => $dataProd_1,'Required' => 3]); ?></div>
                    <div class="col">   <?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 5, 'Placeholder' => 'Cantidad',     'Name' => 'Producto_Cantidad[]',  'Value' => $dataProd_2,'Required' => 2,'Icon' => 'bi bi-sort-numeric-down']); ?></div>
                    <div class="col">   <?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 6, 'Placeholder' => 'Valor Total',  'Name' => 'Producto_Valor[]',     'Value' => $dataProd_3,'Required' => 2,'Icon' => 'bi bi-currency-dollar']); ?></div>
                </div>
            <?php } ?>
        </div>

        <?php
        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idExistencia',   'Value' => $data['rowData']['idExistencia'],    'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCampana',      'Value' => $data['rowData']['idCampana'],       'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idFacturacion',  'Value' => $data['rowData']['idFacturacion'],   'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_Fecha',  'Value' => $data['rowData']['Fecha'],       'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_idEntidad',  'Value' => $data['rowData']['idEntidad'],       'Required' => 2]);
        ?>
    </div>
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
            <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
        </div>
    </div>
</form>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormEditPartidaFin").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidasFinalizadas/update'; ?>';
            let Informacion = $("#FormEditPartidaFin").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPartidasFinDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidasFinalizadas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                ],
                //showNoti:'Datos Editados Correctamente',
                closeModal:'#viewModal_2',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /*********************************************************************/
    //Permite utilizar el select filter en modals dinamicos
    $(document).ready(function() {
        $("#PartidaFin_idEntidad").select2({
            dropdownParent: $("#FormEditPartidaFin"),
            width: '100%'
        });
    });
</script>