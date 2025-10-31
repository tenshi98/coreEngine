<form id="FormUpdatePermisos" name="FormUpdatePermisos" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <th>Nombre</th>
                <th style="width: 120px;">Permitido</th>
                <th style="width: 280px;">Nivel Acceso</th>
            </tr>
        </thead>
        <tbody>
            <?php
            //Verifico si hay datos
            if(is_array($data['arrPermisos'])&&!empty($data['arrPermisos'])){
                //filtro
                $newData = $data['Fnc_CommonData']->agruparPorClave ($data['arrPermisos'], 'PermisosCat' );
                //Recorro
                foreach ($newData AS $Categoria=>$permisos){
                    //imprimimos la categoría
                    echo ' <tr class="table-secondary"><td colspan="7"><strong>'.$Categoria.'</strong></td></tr>';
                    //se recorren los datos dentro de la categoría
                    foreach ($permisos AS $perm){
                        //Variables
                        $check[1] = '';
                        $check[2] = '';
                        $check[3] = '';
                        $check[4] = '';
                        //si tiene permiso
                        if(isset($perm['level'])&&$perm['level']!=0){
                            $checked = 'checked';
                            $display = '';
                            $check[$perm['level']] = 'checked';
                        }else{
                            $checked  = '';
                            $display  = 'display: none;';
                            $check[1] = 'checked';
                        }
                        ?>
                        <tr>
                            <td><?php echo $perm['Nombre']; ?></td>
                            <td>
                                <div class="col-sm-8 field">
                                    <div class="form-check checkbox-success form-switch required=" required>
                                        <input                          type="hidden"   value="1" name="<?php echo 'switch_'.$perm['idPermisos']; ?>">
                                        <input class="form-check-input" type="checkbox" value="2" name="<?php echo 'switch_'.$perm['idPermisos']; ?>" onclick="activar(<?php echo $perm['idPermisos']; ?>)" <?php echo $checked; ?>>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="dlk-radio btn-group" id="<?php echo 'div_'.$perm['idPermisos']; ?>" style="<?php echo $display; ?>">
                                    <?php
                                    /************************************************************/
                                    if(isset($perm['idLevelLimit'])&&$perm['idLevelLimit']>=1){
                                        echo '
                                        <label class="btn btn-primary btn-sm tooltiplink" data-title="Solo Ver">
                                            <input name="level_'.$perm['idPermisos'].'" class="form-control" type="radio" value="1" '.$check[1].'>
                                            <i class="glyphicon glyphicon-ok"></i> Ver
                                        </label>';
                                    }
                                    /************************************************************/
                                    if(isset($perm['idLevelLimit'])&&$perm['idLevelLimit']>=2){
                                        echo '
                                        <label class="btn btn-secondary btn-sm tooltiplink" data-title="Ver/Editar">
                                            <input name="level_'.$perm['idPermisos'].'" class="form-control" type="radio" value="2" '.$check[2].'>
                                            <i class="glyphicon glyphicon-ok"></i> Editar
                                        </label>';
                                    }
                                    /************************************************************/
                                    if(isset($perm['idLevelLimit'])&&$perm['idLevelLimit']>=3){
                                        echo '
                                        <label class="btn btn-success btn-sm tooltiplink" data-title="Ver/Editar/Crear">
                                            <input name="level_'.$perm['idPermisos'].'" class="form-control" type="radio" value="3" '.$check[3].'>
                                            <i class="glyphicon glyphicon-ok"></i> Crear
                                        </label>';
                                    }
                                    /************************************************************/
                                    if(isset($perm['idLevelLimit'])&&$perm['idLevelLimit']>=4){
                                        echo '
                                        <label class="btn btn-danger btn-sm tooltiplink" data-title="Ver/Editar/Crear/Borrar">
                                            <input name="level_'.$perm['idPermisos'].'" class="form-control" type="radio" value="4" '.$check[4].'>
                                            <i class="glyphicon glyphicon-ok"></i> Borrar
                                        </label>';
                                    } ?>
                                </div>
                            </td>
                        </tr>
                    <?php
                    }
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

    /******************************************/
    <?php
    echo 'const div = [];';
    foreach ($data['arrPermisos'] AS $perm){
        //si tiene permiso
        if(isset($perm['level'])&&$perm['level']!=0){
            echo 'div['.$perm['idPermisos'].'] = 1;';
        }else{
            echo 'div['.$perm['idPermisos'].'] = 0;';
        }
    }
    ?>

    /******************************************/
    function activar(ID) {
        //Mostrar
        if (div[ID]==0) {
            $('#div_'+ID).show();
            div[ID] = 1;
        } else {
            $('#div_'+ID).hide();
            div[ID] = 0;
        }
    }
    /******************************************/
    $("#FormUpdatePermisos").submit(function(e) {
        e.preventDefault();
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Metodo      = 'POST';
        let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/permisos/update'; ?>';
        let Informacion = $("#FormUpdatePermisos").serialize();
        const Options     = {
            showNoti:'Permisos Editados Correctamente',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        SendDataForms(Metodo, Direccion, Informacion, Options);
    });

</script>


<style>
    .dlk-radio input[type="radio"],
    .dlk-radio input[type="checkbox"] {
        margin-left:-99999px;
        display:none;
    }
    .dlk-radio input[type="radio"] + .glyphicon ,
    .dlk-radio input[type="checkbox"] + .glyphicon {
        opacity:0.15
    }
    .dlk-radio input[type="radio"]:checked + .glyphicon,
    .dlk-radio input[type="checkbox"]:checked + .glyphicon{
        opacity:1
    }
</style>