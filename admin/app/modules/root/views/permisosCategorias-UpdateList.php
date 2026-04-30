<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th scope="col">Categoria</th>
            <th scope="col">Descripcion</th>
            <th scope="col">Carpeta Contenedora</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrCategoria'])&&!empty($data['arrCategoria'])){
            //Recorro
            foreach($data['arrCategoria'] as $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idPermisosCat']);
                $Entidad     = addslashes($crud['Nombre']); ?>
                <tr>
                    <td>
                        <?php
                        $Icono   = $crud['Icon'];
                        //Si hay colores
                        $Icono  .=  !empty($crud['IconColor'])
                                    ? ' '.$crud['IconColor']
                                    : '';
                        ?>
                        <i class="<?php echo $Icono;?>"></i> <?php echo $crud['Nombre']; ?>
                    </td>
                    <td><?php echo $crud['Descripcion']; ?></td>
                    <td><?php echo $crud['Carpeta']; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="TDviewBTN('<?php echo $encryptedId; ?>')"                             class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <button type="button" onclick="TDeditBTN('<?php echo $encryptedId; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" onclick="TDdelBTN( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>

