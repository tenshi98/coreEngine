<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable"
       data-more-url="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.($data['moreId'] ?? ''); ?>"
       data-has-more="<?php echo !empty($data['hasMore']) ? '1' : '0'; ?>"
       data-next-offset="<?php echo (int) ($data['nextOffset'] ?? 0); ?>"
       data-more-method="GET">
    <thead>
        <tr>
            <th scope="col" style="width: 100px;">Fecha Creacion</th>
            <th scope="col">Observacion</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php require(__DIR__.'/entidadesListado-Resumen-Observaciones-UpdateListRows.php'); ?>
    </tbody>
</table>
