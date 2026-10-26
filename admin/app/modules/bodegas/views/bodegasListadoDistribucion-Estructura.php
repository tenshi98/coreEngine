<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="pt-2">
    <?php
    // Verifico si hay datos
    if(is_array($data['arrZonas'])&&!empty($data['arrZonas'])){
        // Recorro los datos
        foreach($data['arrZonas'] as $zona){
            // Variables
            $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $zona['idZona']); ?>
            <div class="card mb-2" id="distZonaCard<?php echo $zona['idZona']; ?>">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge <?php echo $zona['TipoColor']; ?>"><?php echo $zona['Tipo']; ?></span>
                            <strong><?php echo $zona['Nombre']; ?></strong>
                            <span class="badge <?php echo $zona['EstadoColor']; ?>"><?php echo $zona['Estado']; ?></span>
                            <small class="text-muted">
                                <i class="bi bi-signpost me-1"></i><?php echo $zona['Pasillos']; ?>
                                <i class="bi bi-archive ms-2 me-1"></i><?php echo $zona['Racks']; ?>
                                <i class="bi bi-grid-3x3-gap ms-2 me-1"></i><?php echo $zona['Posiciones']; ?>
                            </small>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="distZonaTogglePasillos('<?php echo $encryptedId['data']; ?>', '<?php echo $zona['idZona']; ?>')" class="btn btn-info     btn-sm tooltiplink" data-title="Ver Pasillos"><i class="bi bi-chevron-down"></i></button>
                            <?php if($data['UserAccess']['LevelAccess']>=1){ ?>
                                <button type="button" onclick="distZonaView('<?php echo $encryptedId['data']; ?>')"  class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <?php } ?>
                            <?php if($data['UserAccess']['LevelAccess']>=2){ ?>
                                <button type="button" onclick="distZonaEdit('<?php echo $encryptedId['data']; ?>')"  class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                                <button type="button" onclick="distPasilloNew('<?php echo $encryptedId['data']; ?>')" class="btn btn-success   btn-sm tooltiplink" data-title="Crear Pasillo"><i class="bi bi-plus-lg"></i></button>
                                <button type="button" onclick="distZonaDel('<?php echo $encryptedId['data']; ?>', '<?php echo $zona['idZona']; ?>', '<?php echo addslashes($zona['Nombre']); ?>')" class="btn btn-danger btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if(!empty($zona['Descripcion'])){ ?>
                        <div class="text-muted small mt-1"><?php echo $zona['Descripcion']; ?></div>
                    <?php } ?>
                    <div id="tabPasillosZ<?php echo $zona['idZona']; ?>" class="mt-2" style="display:none;"></div>
                </div>
            </div>
        <?php }
    }else{ ?>
        <div class="empty-state text-center py-4">
            <i class="bi bi-grid" style="font-size: 2.5rem;"></i>
            <h5 class="mt-2">No hay zonas definidas</h5>
            <p class="text-muted mb-0">Use el botón «Crear Zona» para empezar a distribuir la bodega.</p>
        </div>
    <?php } ?>
</div>
