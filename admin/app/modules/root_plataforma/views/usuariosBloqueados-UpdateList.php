<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable"
       data-more-url="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/updateList'; ?>"
       data-has-more="<?php echo !empty($data['hasMore']) ? '1' : '0'; ?>"
       data-next-offset="<?php echo (int) ($data['nextOffset'] ?? 0); ?>"
       data-more-method="GET">
    <thead>
        <tr>
            <th scope="col" style="width: 100px;">Fecha</th>
            <th scope="col" style="width: 100px;">Hora</th>
            <th scope="col" style="width: 100px;">DateTime</th>
            <th scope="col">Email</th>
            <th scope="col">Password</th>
            <th scope="col">IP_Client</th>
            <th scope="col">Agent_Transp</th>
        </tr>
    </thead>
    <tbody>
        <?php require(__DIR__.'/usuariosBloqueados-UpdateListRows.php'); ?>
    </tbody>
</table>
