<form id="FormNewPartidaStep2" name="FormNewPartidaStep2" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark"></i> Crear Partida
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-file-earmark"></i></div>
                    Crear Partida<br>
                    <small>Permite crear una partida en base a la selección de los clientes</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">

        <div class="table-responsive">
            <table class="table table-sm table-hover datatable">
                <thead>
                    <tr>
                        <th>Tipo Entidad</th>
                        <th>Nombre</th>
                        <th>Sector</th>
                        <th width="10">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //Verifico si hay datos
                    if(is_array($data['arrList'])&&!empty($data['arrList'])){
                        //Recorro
                        foreach($data['arrList'] AS $crud){
                            //Se obtiene el nombre o la razón social
                            $Entidad  = '';
                            $Entidad .= !empty($crud['Nick'])
                                        ? '<strong>'.$crud['Nick'].'</strong> | '
                                        : '';
                            $Entidad .= !empty($crud['Nombre'])
                                        ? $crud['ApellidoPat'].' '.$crud['ApellidoMat'].' '.$crud['Nombre']
                                        : $crud['RazonSocial'];
                            ?>
                            <tr>
                                <td><?php echo $crud['TipoEntidad']; ?></td>
                                <td><?php echo $Entidad; ?></td>
                                <td><?php echo $crud['Sector']; ?></td>
                                <td>
                                    <?php
                                    //datos ocultos
                                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Clients_idEntidad[]', 'Value' => $crud['idEntidad'], 'Required' => 2]);  //ID Cliente
                                    ?>
                                    <div class="btn-group" role="group">
                                        <a class="btn btn-danger btn-sm tooltiplink" data-title="Borrar Información" onclick="delRow(this)"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php }else{
                        echo '<tr><td colspan="4">No se encontraron entradas</td></tr>';
                    } ?>
                </tbody>
            </table>
        </div>

        <?php
        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCampana',       'Value' => $data['rowData']['idCampana'],             'Required' => 2]);  //Campaña
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Fecha',           'Value' => $data['Fnc_ServerServer']->fechaActual(), 'Required' => 2]);  //Fecha de creacion automatica
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstadoPartida', 'Value' => 1,                                         'Required' => 2]);  //Recién Creado
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',       'Value' => $data['UserData']['UserID'],               'Required' => 2]);  //Usuario que lo creo
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'ProdSelec',       'Value' => $data['ProdSelec'],                        'Required' => 2]);  //Productos seleccionados anteriormente

        ?>
    </div>
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
            <button type="button" class="btn btn-success" onclick="submitForm(this)"><i class="bx bx-save"></i> Guardar Cambios</button>
        </div>
    </div>
</form>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormNewPartidaStep2").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas'; ?>';
            let Informacion = $("#FormNewPartidaStep2").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPartidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal_2',
                ClearForm:'FormNewPartidaStep2',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>