<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<?php
// Verifico si hay datos
if(is_array($data['arrPermisos'])&&!empty($data['arrPermisos'])){
    //Arreglo con los estados
    $estadoBadge = [
        1 => 'bg-success', // Activo
        2 => 'bg-danger',  // Inactivo
    ];
    // Recorro los datos (una fila por permiso; la categoría es una columna ordenable/buscable)
    foreach ($data['arrPermisos'] as $perm){
        // Variables
        $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $perm['idPermisos']);
        $Entidad     = addslashes($perm['Nombre']); ?>
        <tr>
            <td><?php echo $perm['PermisosCat']; ?></td>
            <td><?php echo $perm['Tipo']; ?></td>
            <td><?php echo $perm['Nombre']; ?></td>
            <td><?php echo '<strong>Ruta: </strong>'.$perm['RutaWeb'].'<br><strong>Controlador: </strong>'.$perm['RutaController']; ?></td>
            <td><?php echo $perm['LevelLimit']; ?></td>
            <td><?php echo '<span class="badge-sp1 badge-sp1-'.$estadoBadge[$perm['idEstado']].'">'.$perm['Estado'].'</span>'; ?></td>
            <td>
                <div class="btn-group" role="group">
                    <button type="button" onclick="TDviewBTN('<?php echo $encryptedId['data']; ?>')"                            class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                    <a href="<?php echo $BASE.'/Core/permisos/listado/resumen/'.$encryptedId['data']; ?>"                       class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></a>
                    <button type="button" onclick="TDdelBTN('<?php echo $encryptedId['data']; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        </tr>
    <?php
    }
} ?>
