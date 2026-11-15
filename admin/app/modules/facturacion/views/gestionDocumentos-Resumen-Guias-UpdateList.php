<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable"
       data-more-url="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/guias/updateList/'.($data['DataID'] ?? ''); ?>"
       data-more-method="GET"
       data-has-more="<?php echo !empty($data['hasMore']) ? '1' : '0'; ?>"
       data-next-offset="<?php echo (int) ($data['nextOffset'] ?? 0); ?>">
    <thead>
        <tr>
            <th scope="col" style="width: 100px;">Fecha</th>
            <th scope="col">Documento</th>
            <th scope="col" class="text-end">Valor</th>
            <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){ ?>
                <th scope="col" style="width: 10px;">Acciones</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php require(__DIR__.'/gestionDocumentos-Resumen-Guias-UpdateListRows.php'); ?>
    </tbody>
</table>
