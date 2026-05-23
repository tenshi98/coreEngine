<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<form id="FormUpdatePermisosBodegas" name="FormUpdatePermisosBodegas" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <th scope="col">Nombre</th>
                <th scope="col" style="width: 120px;">Permitido</th>
            </tr>
        </thead>
        <tbody>
            <?php
            //Verifico si hay datos
            if(is_array($data['arrPermisosBodegas'])&&!empty($data['arrPermisosBodegas'])){
                //Recorro
                foreach ($data['arrPermisosBodegas'] as $perm){
                    //si tiene permiso
                    if(isset($perm['cuentaPerms'])&&$perm['cuentaPerms']!=0){
                        $checked = 'checked';
                    }else{
                        $checked  = '';
                    }
                    ?>
                    <tr>
                        <td><?php echo $perm['Nombre']; ?></td>
                        <td>
                            <div class="col-sm-8 field">
                                <div class="form-check checkbox-success form-switch required=" required>
                                    <input                          type="hidden"   value="1" name="<?php echo 'switch_'.$perm['idBodegas']; ?>">
                                    <input class="form-check-input" type="checkbox" value="2" name="<?php echo 'switch_'.$perm['idBodegas']; ?>" onclick="activar(<?php echo $perm['idBodegas']; ?>)" <?php echo $checked; ?>>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php
                }
            } ?>
        </tbody>
    </table>

    <?php
    //datos ocultos
    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario','Value' => $data['rowData']['idUsuario'],'Required' => 2]);
    ?>
    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
        <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
    </div>
</form>

<script>
    /*********************************************************************/
    /*                      FORMULARIO DE BUSQUEDA                       */
    /*********************************************************************/
    /******************************************/
    $("#FormUpdatePermisosBodegas").submit(function(e) {
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
        let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/bodegas/update'; ?>';
        let Informacion = $("#FormUpdatePermisosBodegas").serialize();
        const Options     = {
            showNoti:'Permisos Editados Correctamente',
            closeObject:'#PDloader',
            changeValForm: ejecutandoForm,
        };
        //Se envian los datos al formulario
        SendDataForms(Metodo, Direccion, Informacion, Options);
    });

</script>

