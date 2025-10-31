<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Email</th>
            <th>Nombre</th>
            <th>Ultimo Acceso</th>
            <th>Estado</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrUsuarios'])&&!empty($data['arrUsuarios'])){
            //Recorro
            foreach($data['arrUsuarios'] AS $crud){
                //Variables
                $idEntidad   = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idEntidad']);
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idUsuario']);
                $Entidad     = addslashes($crud['email']); ?>
                <tr>
                    <td><?php echo $crud['email']; ?></td>
                    <td><?php echo $crud['Nombre']; ?></td>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Ultimo_acceso']); ?></td>
                    <td><?php echo '<span class="badge '.$crud['EstadoColor'].'">'.$crud['Estado'].'</span>'; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabUsuariosView('<?php echo $encryptedId; ?>')"                                    class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <button type="button" onclick="tabUsuariosEdit('<?php echo $encryptedId; ?>')"                                    class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <?php
                            //Si existen permisos a las maquinas
                            if(isset($data['MainViewData']['Count_Maquinas'])&&$data['MainViewData']['Count_Maquinas']!=0){
                                echo '<button type="button" onclick="tabUsuariosEditMaq(\''.$idEntidad.'\', \''.$encryptedId.'\')"    class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Permisos Máquinas"><i class="bi bi-gear-fill"></i></button>';
                                if($data['UserData']["maquinasListadoNotificaciones"]==2){
                                    echo '<button type="button" onclick="tabUsuariosEditNoti(\''.$idEntidad.'\', \''.$encryptedId.'\')"    class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Notificaciones"><i class="bi bi-whatsapp"></i></button>';
                                }
                            } ?>
                            <button type="button" onclick="tabUsuariosDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"         class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>