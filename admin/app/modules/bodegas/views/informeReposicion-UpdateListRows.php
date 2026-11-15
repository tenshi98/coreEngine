<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<?php
// Utilidades de formato y mapa de alertas (el fragmento de carga incremental no pasa por -UpdateList.php)
$e   = static function ($v) { return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); };
$num = static function ($v) { return number_format((float) ($v ?? 0), 2, ',', '.'); };
// Alertas: 1 ok, 2 bajo el stock limite, 3 bajo la demanda predicha
$alertas = [
    1 => ['bg-success', 'OK'],
    2 => ['bg-warning', 'Bajo límite'],
    3 => ['bg-danger',  'Reponer'],
];
// Verifico si hay datos
if (is_array($data['arrList']) && !empty($data['arrList'])) {
    // Recorro
    foreach ($data['arrList'] as $row) {
        $al = $alertas[(int) $row['Alerta']] ?? ['bg-secondary', '-']; ?>
        <tr>
            <td><?php echo $e($row['Producto']); ?></td>
            <td><?php echo $e($row['UniMed']); ?></td>
            <td class="text-end"><?php echo $num($row['DemandaMedia']); ?></td>
            <td class="text-end"><?php echo $num($row['DemandaPredicha']); ?></td>
            <td class="text-end"><?php echo $e(number_format((float) $row['Pendiente'], 4, ',', '.')); ?></td>
            <td class="text-end"><?php echo $num($row['StockActual']); ?></td>
            <td class="text-end"><?php echo $row['StockLimite'] === null ? '-' : $num($row['StockLimite']); ?></td>
            <td class="text-end"><?php echo $row['Cobertura'] === null ? '-' : $num($row['Cobertura']); ?></td>
            <td class="text-end"><?php echo $num($row['Sugerida']); ?></td>
            <td><span class="badge <?php echo $al[0]; ?>"><?php echo $al[1]; ?></span></td>
        </tr>
    <?php } ?>
<?php } ?>
