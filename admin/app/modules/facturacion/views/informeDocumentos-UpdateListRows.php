<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<?php
// Verifico si hay datos
if(is_array($data['arrList'])&&!empty($data['arrList'])){
    // Recorro
    foreach($data['arrList'] as $crud){
        // Se obtiene el ID
        $DataID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idFacturacion']); ?>
        <tr>
            <td><?php echo $crud['TipoMov']; ?></td>
            <td><?php echo $crud['Documento'].' '.$crud['N_Doc']; ?></td>
            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']); ?></td>
            <td>
                <?php
                // Se obtiene el nombre o la razón social
                switch ($crud['idTipoEntidad']) {
                    case 1: $Entidad = $crud['EntidadesApellido'].', '.$crud['EntidadesNombre']; break; //Persona Natural
                    case 2: $Entidad = $crud['EntidadesRazonSocial']; break;                            //Empresas
                    default: $Entidad = '-'; break;                                                         //Tipo no contemplado
                }
                // Imprimir
                echo $Entidad;
                ?>
            </td>
            <td><?php echo '<span class="badge-sp1 badge-sp1-'.$crud['EstadoColor'].'">'.$crud['EstadoPago'].'</span>'; ?></td>
            <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 2); ?></td>
            <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['MontoPagado'], 2); ?></td>
            <td>
                <div class="btn-group" role="group">
                    <?php if($data['UserAccess']['LevelAccess']>=1){ ?>
                        <button type="button" onclick="listTableDataView('<?php echo $DataID['data']; ?>')"    class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                    <?php } ?>
                </div>
            </td>
        </tr>
    <?php } ?>
<?php } ?>
