<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable"
       data-more-url="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/search'; ?>"
       data-has-more="<?php echo !empty($data['hasMore']) ? '1' : '0'; ?>"
       data-next-offset="<?php echo (int) ($data['nextOffset'] ?? 0); ?>">
    <thead>
        <tr>
            <th scope="col">Email</th>
            <th scope="col">Numero</th>
            <th scope="col" style="width: 100px;">Rut</th>
            <th scope="col" style="width: 100px;">Patente</th>
            <th scope="col" style="width: 100px;">Fecha</th>
            <th scope="col" style="width: 100px;">Hora</th>
            <th scope="col">Palabra</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php require(__DIR__.'/crudResumen-UpdateListRows.php'); ?>
    </tbody>
</table>
