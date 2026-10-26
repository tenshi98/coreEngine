<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="pt-2">
    <?php
    // Leyenda de estados
    if(is_array($data['arrEstado'])&&!empty($data['arrEstado'])){ ?>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <?php foreach($data['arrEstado'] as $estado){ ?>
                <span class="badge <?php echo $estado['Color']; ?>"><?php echo $estado['Nombre']; ?></span>
            <?php } ?>
        </div>
    <?php } ?>

    <?php
    // Verifico si hay datos
    if(is_array($data['arrRacks'])&&!empty($data['arrRacks'])){
        // Recorro los racks
        foreach($data['arrRacks'] as $rack){ ?>
            <div class="card mb-2">
                <div class="card-body py-2">
                    <div class="fw-bold mb-2">
                        <i class="bi bi-archive me-1"></i><?php echo $rack['Rack']; ?>
                        <small class="text-muted fw-normal">
                            &middot; Pasillo <?php echo $rack['Pasillo']; ?>
                            &middot; Zona <?php echo $rack['Zona']; ?>
                        </small>
                    </div>
                    <?php
                    // Recorro los niveles
                    foreach($rack['niveles'] as $nivel){ ?>
                        <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                            <small class="text-muted me-1" style="min-width:60px;">Nivel <?php echo $nivel['Nivel']; ?></small>
                            <?php
                            // Recorro las posiciones
                            foreach($nivel['posiciones'] as $posicion){
                                // Variables
                                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $posicion['idPosicion']); ?>
                                <button type="button"
                                        onclick="distPosicionView('<?php echo $encryptedId['data']; ?>')"
                                        class="btn btn-sm py-0 px-2 tooltiplink <?php echo $posicion['Color']; ?>"
                                        data-title="<?php echo $posicion['Codigo'].' - '.$posicion['Estado']; ?>"
                                        style="min-width:34px;">
                                    <?php echo $posicion['Numero']; ?>
                                </button>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php }
    }else{ ?>
        <div class="empty-state text-center py-4">
            <i class="bi bi-grid-3x3-gap" style="font-size: 2.5rem;"></i>
            <h5 class="mt-2">No hay posiciones para mostrar</h5>
            <p class="text-muted mb-0">Cree zonas, pasillos y racks desde la pestaña «Distribución» para generar el mapa.</p>
        </div>
    <?php } ?>
</div>
