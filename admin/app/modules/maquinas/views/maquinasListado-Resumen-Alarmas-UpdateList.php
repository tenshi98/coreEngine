<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se verifica si hay datos
if(!is_array($data['arrAlarmas']) || empty($data['arrAlarmas'])){
    echo '<div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> La máquina no tiene alarmas personalizadas registradas.</div>';
    return;
}

// Se prepara el nivel de acceso
$levelAccess = (int)($data['UserAccess']['LevelAccess'] ?? 0);
?>
<div class="accordion" id="alarmaAccordion">
    <?php foreach($data['arrAlarmas'] as $crud){
        // Variables
        $uid         = (string)($crud['uid'] ?? '');
        $uidSeguro   = preg_replace('/[^A-Za-z0-9_-]/', '_', $uid);
        $uidAttr     = htmlspecialchars($uid, ENT_QUOTES, 'UTF-8');
        $collapseId  = 'alarmaCollapse_'.$uidSeguro;
        $headingId   = 'alarmaHeading_'.$uidSeguro;
        $idTipo      = (int)($crud['idTipoAlarma'] ?? 0);
        $idEstado    = (int)($crud['idEstado'] ?? 0);
        $estadoClass = ($idEstado === 1) ? 'bg-success' : 'bg-danger';

        // Se determina si un valor debe mostrarse
        $mostrar = function($valor) {
            return !($valor === null || $valor === '');
        };
    ?>
    <div class="accordion-item mb-2">
        <h2 class="accordion-header d-flex align-items-stretch" id="<?php echo $headingId; ?>">
            <button class="accordion-button collapsed flex-grow-1" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false" aria-controls="<?php echo $collapseId; ?>">
                <div class="d-flex flex-wrap align-items-center gap-2 w-100">
                    <strong><?php echo htmlspecialchars((string)($crud['Nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                    <span class="badge <?php echo $estadoClass; ?>"><?php echo htmlspecialchars((string)($crud['Estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="badge bg-secondary"><?php echo htmlspecialchars((string)($crud['TipoAlarma'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars((string)($crud['Prioridad'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    <small class="text-muted">Errores: <?php echo (int)($crud['ActualErrores'] ?? 0); ?> / <?php echo (int)($crud['MaxErrores'] ?? 0); ?></small>
                </div>
            </button>
            <div class="d-flex align-items-center gap-1 p-2">
                <?php if($levelAccess>=1){ ?>
                    <button type="button" onclick="tabAlarmasView('<?php echo $uidAttr; ?>')"    class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                <?php } ?>
                <?php if($levelAccess>=2){ ?>
                    <button type="button" onclick="tabAlarmasEdit('<?php echo $uidAttr; ?>')"    class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                    <button type="button" onclick="tabAlarmasDel('<?php echo $uidAttr; ?>', '<?php echo addslashes((string)($crud['Nombre'] ?? '')); ?>')" class="btn btn-danger btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                <?php } ?>
            </div>
        </h2>
        <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse" aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#alarmaAccordion">
            <div class="accordion-body">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-6"><strong>Unidad de Medida:</strong> <?php echo htmlspecialchars((string)($crud['UniMed'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php if($idTipo===4 && $mostrar($crud['ValorError'] ?? null)){ ?>
                        <div class="col-12 col-md-6"><strong>Valor de Error:</strong> <?php echo htmlspecialchars((string)$crud['ValorError'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php } ?>
                    <?php if(($idTipo===5 || $idTipo===6) && $mostrar($crud['ValorMinimo'] ?? null)){ ?>
                        <div class="col-12 col-md-6"><strong>Valor Mínimo:</strong> <?php echo htmlspecialchars((string)$crud['ValorMinimo'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php } ?>
                    <?php if(($idTipo===5 || $idTipo===6) && $mostrar($crud['ValorMaximo'] ?? null)){ ?>
                        <div class="col-12 col-md-6"><strong>Valor Máximo:</strong> <?php echo htmlspecialchars((string)$crud['ValorMaximo'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php } ?>
                    <?php if($idTipo===7 && $mostrar($crud['PorcentajeDiferencia'] ?? null)){ ?>
                        <div class="col-12 col-md-6"><strong>Porcentaje de Diferencia:</strong> <?php echo htmlspecialchars((string)$crud['PorcentajeDiferencia'], ENT_QUOTES, 'UTF-8'); ?>%</div>
                    <?php } ?>
                    <?php if($idTipo===8 && $mostrar($crud['HoraInicio'] ?? null)){ ?>
                        <div class="col-12 col-md-6"><strong>Hora Inicio:</strong> <?php echo htmlspecialchars((string)$crud['HoraInicio'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php } ?>
                    <?php if($idTipo===8 && $mostrar($crud['HoraTermino'] ?? null)){ ?>
                        <div class="col-12 col-md-6"><strong>Hora Término:</strong> <?php echo htmlspecialchars((string)$crud['HoraTermino'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php } ?>
                </div>

                <h6><i class="bi bi-diagram-3"></i> Sensores de la alarma</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Sensor</th>
                                <?php if($idTipo===1 || $idTipo===2){ ?>
                                    <th>Rango Inicio</th>
                                    <th>Rango Término</th>
                                <?php } ?>
                                <?php if($idTipo===3){ ?>
                                    <th>Valor Específico</th>
                                <?php } ?>
                                <?php if($idTipo===8){ ?>
                                    <th>Valor Encendido</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $arrSensores = $crud['Sensores'] ?? [];
                            if(is_array($arrSensores) && !empty($arrSensores)){
                                foreach($arrSensores as $sensor){ ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string)($sensor['Sensor'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php if($idTipo===1 || $idTipo===2){ ?>
                                            <td><?php echo htmlspecialchars((string)($sensor['RangoInicio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string)($sensor['RangoTermino'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php } ?>
                                        <?php if($idTipo===3){ ?>
                                            <td><?php echo htmlspecialchars((string)($sensor['ValorEspecifico'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php } ?>
                                        <?php if($idTipo===8){ ?>
                                            <td><?php echo htmlspecialchars((string)($sensor['ValorEncendido'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php } ?>
                                    </tr>
                                <?php }
                            }else{ ?>
                                <tr><td colspan="4" class="text-muted">Sin sensores asociados.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
