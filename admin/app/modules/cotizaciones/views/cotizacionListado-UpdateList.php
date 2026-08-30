<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th scope="col">Documento</th>
            <th scope="col">Fecha</th>
            <th scope="col">Entidad</th>
            <th scope="col" class="text-end">Valor Total</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrList'])&&!empty($data['arrList'])){
            //Recorro
            foreach($data['arrList'] as $crud){
                $Entidad  = '';
                //Se obtiene el nombre o la razón social
                switch ($crud['idTipoEntidad']) {
                    case 1: $Entidad .= $crud['EntidadesApellido'].', '.$crud['EntidadesNombre']; break; //Persona Natural
                    case 2: $Entidad .= $crud['EntidadesRazonSocial']; break;                            //Empresas
                }
                $Entidad .= !empty($crud['EntidadesNick'])
                            ? ' ('.$crud['EntidadesNick'].')'
                            : '';

                // Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idCotizacion']);
                $level       = $data['UserAccess']['LevelAccess'];
                $route       = $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$encryptedId;
                $Entidad     = addslashes('Cotización N° '.$crud['idCotizacion']); ?>
                <tr>
                    <td><?php echo 'Cotización N° '.$crud['idCotizacion']; ?></td>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']); ?></td>
                    <td><?php echo $Entidad; ?></td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 2); ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 1) {echo '<button type="button" onclick="listTableDataView(\''.$encryptedId.'\')"                  class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                            if ($level >= 2) {echo '<a href="'.$route.'"                                                                     class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></a>';}
                            if ($level >= 4) {echo '<button type="button" onclick="listTableDataDel(\''.$encryptedId.'\', \''.$Entidad.'\')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
