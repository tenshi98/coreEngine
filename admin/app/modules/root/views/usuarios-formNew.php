<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="modal fade" id="newFormModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="FormNewData" name="FormNewData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
                <div class="modal-header">
                    <?php
                    switch ($data['UserData']["sistemaModalSubtitle"]) {
                        case 1:
                            echo '
                            <h5 class="modal-title">
                                <i class="bi bi-file-earmark"></i> Crear Nuevo
                            </h5>';
                            break;
                        case 2:
                            echo '
                            <h5 class="modal-title modal-subtitle">
                                <div class="icon"><i class="bi bi-file-earmark"></i></div>
                                Crear Nuevo<br>
                                <small>Permite crear un nuevo elemento</small>
                            </h5>';
                            break;
                    } ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    //se dibujan los inputs
                    $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Datos Personales', 'Clase' => 'box-title text-color-red-dark']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder'  => 'Email',               'Name'  => 'email',       'Value'  => '',  'Required'  => 2,  'Icon' => 'bx bx-mail-send']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Nombre',              'Name'  => 'Nombre',      'Value'  => '',  'Required'  => 2]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder'  => 'Rut',                 'Name'  => 'Rut',         'Value'  => '',  'Required'  => 1, 'Icon' => 'bi bi-person-circle']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder'  => 'Fecha de Nacimiento', 'Name'  => 'fNacimiento', 'Value'  => '',  'Required'  => 1, 'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder'  => 'Fono',                'Name'  => 'Fono',        'Value'  => '',  'Required'  => 1, 'Icon' => 'bi bi-telephone-fill']);
                    $data['Fnc_FormInputs']->formSelectDepend([           'Placeholder1' => 'Ciudad',              'Name1' => 'idCiudad',    'Value1' => '',  'Required1' => 1, 'arrData1' => $data['arrCiudad'],
                                                                          'Placeholder2' => 'Comuna',              'Name2' => 'idComuna',    'Value2' => '',  'Required2' => 1, 'arrData2' => $data['arrComuna']]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Dirección',           'Name'  => 'Direccion',   'Value'  => '',  'Required'  => 1, 'Icon' => 'bi bi-geo-alt-fill']);

                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'password',       'Value' => '1234', 'Required' => 2]);//password por defecto
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idTipoUsuario',  'Value' => 1,      'Required' => 2]);//Super administrador
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstado',       'Value' => 1,      'Required' => 2]);//Usuario Activo
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idMenuPosicion', 'Value' => 2,      'Required' => 2]);//Superior
                    ?>
                </div>
                <div class="modal-footer">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
                        <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    /******************************************/
    $("#FormNewData").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/administracion/usuarios'; ?>';
            let Informacion = $("#FormNewData").serialize();
            const Options     = {
                DestinoFrom:'<?php echo $BASE.'/Core/administracion/usuarios/resumen/'; ?>',
                ClearForm:'FormNewData',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

</script>
