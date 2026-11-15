<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

$e   = static function ($v) { return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); };
$num = static function ($v) { return number_format((float) ($v ?? 0), 2, ',', '.'); };
// Alertas: 1 ok, 2 bajo el stock limite, 3 bajo la demanda predicha
$alertas = [
    1 => ['bg-success', 'OK'],
    2 => ['bg-warning', 'Bajo límite'],
    3 => ['bg-danger',  'Reponer'],
];
// Ultima ejecucion correcta y sus metricas
$arrRun   = (isset($data['arrRun']) && is_array($data['arrRun'])) ? $data['arrRun'] : [];
$metricas = !empty($arrRun['Metricas']) ? json_decode((string) $arrRun['Metricas'], true) : [];
$metricas = is_array($metricas) ? $metricas : [];
?>
<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $e($data['TableTitle']); ?></h5>
                    <?php if (empty($arrRun)) { ?>
                        <div class="alert alert-info">Sin resultados; pulse Recalcular.</div>
                    <?php } else { ?>
                        <div class="alert alert-light border">
                            <strong>Calculada:</strong> <?php echo $e($data['Fnc_DataDate']->fechaEstandar($arrRun['FechaFin'])); ?>
                            · <strong>Algoritmo:</strong> <?php echo $e($arrRun['Algoritmo']); ?>
                            · <strong>Productos:</strong> <?php echo (int) $arrRun['Muestras']; ?> de <?php echo (int) $arrRun['MuestrasOrigen']; ?>
                            · <strong>Duración:</strong> <?php echo $e($num(((int) $arrRun['DuracionMs']) / 1000)); ?> s
                            <?php if (!empty($metricas)) { ?>
                                <br>
                                <strong>MAE:</strong> <?php echo $e($num($metricas['mae'] ?? 0)); ?> (línea base <?php echo $e($num($metricas['maeBase'] ?? 0)); ?>)
                                · <strong>RMSE:</strong> <?php echo $e($num($metricas['rmse'] ?? 0)); ?> (línea base <?php echo $e($num($metricas['rmseBase'] ?? 0)); ?>)
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-sm btn-success" data-export-url="<?php echo $e($BASE.'/'.$data['UserAccess']['RouteAccess'].'/exportExcel'); ?>" data-export-filename="reposicion_predictiva" onclick="exportExcelServer(this)"><i class="ri-file-excel-2-line"></i> Exportar a Excel</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable"
                               data-more-url="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/search'; ?>"
                               data-has-more="<?php echo !empty($data['hasMore']) ? '1' : '0'; ?>"
                               data-next-offset="<?php echo (int) ($data['nextOffset'] ?? 0); ?>">
                            <thead>
                                <tr>
                                    <th scope="col">Producto</th>
                                    <th scope="col">Unidad</th>
                                    <th scope="col" class="text-end">Demanda media</th>
                                    <th scope="col" class="text-end">Demanda predicha</th>
                                    <th scope="col" class="text-end">Pendiente</th>
                                    <th scope="col" class="text-end">Stock actual</th>
                                    <th scope="col" class="text-end">Stock límite</th>
                                    <th scope="col" class="text-end">Cobertura (sem.)</th>
                                    <th scope="col" class="text-end">Sugerida</th>
                                    <th scope="col">Alerta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php require(__DIR__.'/informeReposicion-UpdateListRows.php'); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="clearfix"></div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger float-end"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>
