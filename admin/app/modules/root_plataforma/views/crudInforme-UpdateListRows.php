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
                                        $DataID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idCrud']); ?>
                                        <tr>
                                            <td><?php echo $crud['Email']; ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['Numero'], 2); ?></td>
                                            <td><?php echo $crud['Rut']; ?></td>
                                            <td><?php echo $crud['Patente']; ?></td>
                                            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                                            <td><?php echo $crud['Hora']; ?></td>
                                            <td><?php echo $crud['Palabra']; ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if($data['UserAccess']['LevelAccess']>=1){ ?>
                                                        <button type="button" onclick="TDviewBTN('<?php echo $DataID['data']; ?>')"   class="btn btn-primary btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            
