<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable"
       data-more-url="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos/updateList/'.($data['moreId'] ?? ''); ?>"
       data-has-more="<?php echo !empty($data['hasMore']) ? '1' : '0'; ?>"
       data-next-offset="<?php echo (int) ($data['nextOffset'] ?? 0); ?>"
       data-more-method="GET">
    <thead>
        <tr>
            <th scope="col">Nombre</th>
            <th scope="col">Tipo Contacto</th>
            <th scope="col">Email</th>
            <th scope="col">Celular</th>
            <th scope="col">Telefono</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php require(__DIR__.'/entidadesListado-Resumen-Contactos-UpdateListRows.php'); ?>
    </tbody>
</table>
