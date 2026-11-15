<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable"
       data-more-url="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes/updateList/'.($data['moreId'] ?? ''); ?>"
       data-has-more="<?php echo !empty($data['hasMore']) ? '1' : '0'; ?>"
       data-next-offset="<?php echo (int) ($data['nextOffset'] ?? 0); ?>"
       data-more-method="GET">
    <thead>
        <tr>
            <th scope="col">Servicio</th>
            <th scope="col" style="width: 100px;">Fecha Ingreso</th>
            <th scope="col">Monto Servicio</th>
            <th scope="col">Estado</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php require(__DIR__.'/tercerosEntidadesListado-Resumen-Planes-UpdateListRows.php'); ?>
    </tbody>
</table>
