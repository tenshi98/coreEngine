<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

/************************************/
// Matriz de modulos del token (fragmento servido por tokensListadoPermisos::UpdateList)
$arrModulosToken = (isset($data['arrModulos']) && is_array($data['arrModulos'])) ? $data['arrModulos'] : [];
?>
<form id="FormUpdatePermisosToken" name="FormUpdatePermisosToken" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Módulos del token">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <th scope="col">Módulo</th>
                <th scope="col" style="width: 120px;">Permitido</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Verifico si hay datos
            if(!empty($arrModulosToken)){
                // Recorro los modulos
                foreach ($arrModulosToken as $mod){
                    // Variables
                    $idMod   = htmlspecialchars((string)($mod['modulo'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $nombre  = htmlspecialchars((string)($mod['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $descrip = htmlspecialchars((string)($mod['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $checked = ((int)($mod['activo'] ?? 0) === 1) ? 'checked' : '';
                    ?>
                    <tr>
                        <td><?php echo $nombre; ?><?php if($descrip !== ''){ ?> <i class="bi bi-info-circle text-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?php echo $descrip; ?>"></i><?php } ?></td>
                        <td>
                            <div class="col-sm-8 field">
                                <div class="form-check checkbox-success form-switch">
                                    <input                          type="hidden"   value="1" name="switch_<?php echo $idMod; ?>">
                                    <input class="form-check-input" type="checkbox" value="2" name="switch_<?php echo $idMod; ?>" aria-label="Permitir <?php echo $nombre; ?>" <?php echo $checked; ?>>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php
                }
            }else{
                echo '<tr><td colspan="2" class="text-muted">No hay módulos disponibles</td></tr>';
            } ?>
        </tbody>
    </table>

    <?php
    //datos ocultos (id del token cifrado; el helper escapa el valor)
    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idToken','Value' => $data['rowData']['idToken'],'Required' => 2]);
    ?>
    <?php if($data['UserAccess']['LevelAccess']>=2){ ?>
    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
        <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
    </div>
    <?php } ?>
</form>

<script>
    /*********************************************************************/
    /*                      MODULOS DEL TOKEN                            */
    /*********************************************************************/
    /************************************/
    $("#FormUpdatePermisosToken").submit(function(e) {
        //Ejecucion normal
        e.preventDefault();
        // Si ya se está ejecutando, salimos
        if (ejecutandoForm.valor) return;
        //Cambio los valores
        ejecutandoForm.valor = true;
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto (el token CSRF lo añade SendDataForms / $.ajaxSetup)
        let Metodo      = 'POST';
        let Direccion   = '<?php echo htmlspecialchars($BASE.'/'.$data['UserAccess']['RouteAccess'].'/permisos/update', ENT_QUOTES, 'UTF-8'); ?>';
        let Informacion = $("#FormUpdatePermisosToken").serialize();
        const Options     = {
            showNoti:'Permisos Editados Correctamente',
            closeObject:'#PDloader',
            changeValForm: ejecutandoForm,
        };
        //Se envian los datos al formulario
        SendDataForms(Metodo, Direccion, Informacion, Options);
    });

</script>
