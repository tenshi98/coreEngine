<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<?php
// Verifico si hay datos
if(is_array($data['arrRutas'])&&!empty($data['arrRutas'])){
    // Recorro los datos (una fila por ruta; el controlador es una columna ordenable/buscable)
    foreach ($data['arrRutas'] as $ruta){
        // Variables
        $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $ruta['idRutas']); ?>
        <tr>
            <td><?php echo $ruta['Controller']; ?></td>
            <td><?php echo $ruta['Metodo']; ?></td>
            <td><?php echo '<strong>Ruta: </strong>'.$ruta['RutaWeb'].'<br><strong>Controlador: </strong>'.$ruta['RutaController']; ?></td>
            <td><?php echo $ruta['Descripcion']; ?></td>
            <td><?php echo $ruta['LevelLimit']; ?></td>
            <td>
                <div class="btn-group" role="group">
                    <button type="button" onclick="TDviewBTN('<?php echo $encryptedId['data']; ?>')"          class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                    <button type="button" onclick="TDeditBTN('<?php echo $encryptedId['data']; ?>')"          class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                    <button type="button" onclick="TDdelBTN( '<?php echo $encryptedId['data']; ?>', 'Ruta')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        </tr>
<?php }
} ?>
