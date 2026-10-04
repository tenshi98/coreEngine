<?php
/** @var string $BASE */
/** @var array $data */
/** @var \F3 $f3 */

$metodosHTTP = [1 => 'GET', 2 => 'POST', 3 => 'DELETE', 4 => 'PUT'];
$compareFields = [
    'idMetodo'     => 'Método',
    'Descripcion'  => 'Descripción',
    'idLevelLimit' => 'Nivel de Acceso',
    'Controller'   => 'Controlador',
];

$arrInstalador = [];
if (isset($data['arrModules']) && is_array($data['arrModules'])) {
    foreach ($data['arrModules'] as $moduleRoutes) {
        if (!is_array($moduleRoutes)) { continue; }
        foreach ($moduleRoutes as $route) {
            if (isset($route['idMetodo']) && $route['idMetodo'] !== '' && !empty($route['RutaWeb']) && !empty($route['RutaController'])) {
                $key = $route['RutaWeb'] . '||' . $route['RutaController'];
                $arrInstalador[$key] = [
                    'idMetodo'       => (string) $route['idMetodo'],
                    'RutaWeb'        => (string) $route['RutaWeb'],
                    'RutaController' => (string) $route['RutaController'],
                    'Descripcion'    => (string) ($route['Descripcion'] ?? ''),
                    'idLevelLimit'   => (string) ($route['idLevelLimit'] ?? ''),
                    'Controller'     => (string) ($route['Controller'] ?? ''),
                ];
            }
        }
    }
}
$arrBBDD = [];
if (isset($data['arrRutas']) && is_array($data['arrRutas'])) {
    foreach ($data['arrRutas'] as $route) {
        if (isset($route['idMetodo']) && $route['idMetodo'] !== '' && !empty($route['RutaWeb']) && !empty($route['RutaController'])) {
            $key = $route['RutaWeb'] . '||' . $route['RutaController'];
            $arrBBDD[$key] = [
                'idPermisos'     => $route['idPermisos'] ?? null,
                'idMetodo'       => (string) $route['idMetodo'],
                'RutaWeb'        => (string) $route['RutaWeb'],
                'RutaController' => (string) $route['RutaController'],
                'Descripcion'    => (string) ($route['Descripcion'] ?? ''),
                'idLevelLimit'   => (string) ($route['idLevelLimit'] ?? ''),
                'Controller'     => (string) ($route['Controller'] ?? ''),
            ];
        }
    }
}

$allKeys = array_unique(array_merge(array_keys($arrInstalador), array_keys($arrBBDD)));
sort($allKeys);

$countTotal    = count($allKeys);
$countOK       = 0;
$countDiff     = 0;
$countSoloInst = 0;
$countSoloBD   = 0;

$filasComparadas = [];

foreach ($allKeys as $key) {
    $inInst   = isset($arrInstalador[$key]);
    $inBD     = isset($arrBBDD[$key]);
    $dataInst = $inInst ? $arrInstalador[$key] : null;
    $dataBD   = $inBD   ? $arrBBDD[$key]       : null;

    $rutaWeb        = $dataInst['RutaWeb']        ?? $dataBD['RutaWeb'];
    $rutaController = $dataInst['RutaController'] ?? $dataBD['RutaController'];
    $controller     = $dataInst['Controller']     ?? $dataBD['Controller'];

    $diferencias = [];
    $estadoBadge = '';
    $estadoTipo  = '';

    if ($inInst && $inBD) {
        foreach ($compareFields as $campo => $label) {
            $valInst = html_entity_decode($dataInst[$campo] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $valBD   = html_entity_decode($dataBD[$campo]   ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if ($valInst !== $valBD) {
                if ($campo === 'idMetodo') {
                    $txtInst = $metodosHTTP[(int)$valInst] ?? $valInst;
                    $txtBD   = $metodosHTTP[(int)$valBD]   ?? $valBD;
                } else {
                    $txtInst = $valInst !== '' ? $valInst : '(vacío)';
                    $txtBD   = $valBD   !== '' ? $valBD   : '(vacío)';
                }
                $diferencias[] = "<strong>{$label}:</strong> Inst [{$txtInst}] ≠ BD [{$txtBD}]";
            }
        }

        if (empty($diferencias)) {
            $countOK++;
            $estadoTipo  = 'ok';
            $estadoBadge = '<span class="badge-sp1 badge-sp1-bg-success"><i class="bi bi-check-circle"></i> Sincronizada</span>';
        } else {
            $countDiff++;
            $estadoTipo  = 'diff';
            $estadoBadge = '<span class="badge-sp1 badge-sp1-bg-warning"><i class="bi bi-exclamation-triangle"></i> Diferencias</span>';
        }
    } elseif ($inInst && !$inBD) {
        $countSoloInst++;
        $estadoTipo  = 'solo_inst';
        $estadoBadge = '<span class="badge-sp1 badge-sp1-bg-danger"><i class="bi bi-database-slash"></i> Falta en BD</span>';
        $diferencias[] = 'Ruta declarada en el instalador pero no registrada en base de datos';
    } else {
        $countSoloBD++;
        $estadoTipo  = 'solo_bd';
        $estadoBadge = '<span class="badge-sp1 badge-sp1-bg-secondary"><i class="bi bi-box-arrow-in-up"></i> No en Instalador</span>';
        $diferencias[] = 'Ruta existe en base de datos pero no está declarada en el instalador';
    }

    $metodoId  = (int)($dataInst['idMetodo'] ?? $dataBD['idMetodo'] ?? 0);
    $metodoTxt = $metodosHTTP[$metodoId] ?? ('ID: ' . $metodoId);

    $filasComparadas[] = [
        'key'            => $key,
        'RutaWeb'        => $rutaWeb,
        'RutaController' => $rutaController,
        'Controller'     => $controller,
        'Metodo'         => $metodoTxt,
        'inInst'         => $inInst,
        'inBD'           => $inBD,
        'estadoTipo'     => $estadoTipo,
        'estadoBadge'    => $estadoBadge,
        'diferencias'    => $diferencias,
    ];
}
?>

<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

            <!-- Resumen de Metricas -->
            <div class="row mb-3">
                <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
                    <div class="card">
                        <div class="card-body p-3">
                            <h6 class="text-muted mb-1 small">Total Rutas</h6>
                            <h4 class="mb-0 fw-bold"><?php echo $countTotal; ?></h4>
                            <small class="text-muted">Inst: <?php echo count($arrInstalador); ?> | BD: <?php echo count($arrBBDD); ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
                    <div class="card">
                        <div class="card-body p-3">
                            <h6 class="text-muted mb-1 small">Sincronizadas</h6>
                            <h4 class="mb-0 fw-bold text-success"><?php echo $countOK; ?></h4>
                            <small class="text-muted">Coincidencia idéntica</small>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
                    <div class="card">
                        <div class="card-body p-3">
                            <h6 class="text-muted mb-1 small">Con Diferencias</h6>
                            <h4 class="mb-0 fw-bold text-warning"><?php echo $countDiff; ?></h4>
                            <small class="text-muted">Campos discordantes</small>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
                    <div class="card">
                        <div class="card-body p-3">
                            <h6 class="text-muted mb-1 small">Faltantes / Huérfanas</h6>
                            <h4 class="mb-0 fw-bold text-danger"><?php echo ($countSoloInst + $countSoloBD); ?></h4>
                            <small class="text-muted">Falta BD: <?php echo $countSoloInst; ?> | No Inst: <?php echo $countSoloBD; ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Detalle -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between align-items-center">
                            <span><?php echo $data['TableTitle'] ?? 'Comparación Rutas'; ?> (v2)</span>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 45px;">N°</th>
                                    <th scope="col" style="width: 65px;">Método</th>
                                    <th scope="col">Ruta Web</th>
                                    <th scope="col">Ruta Controller</th>
                                    <th scope="col" style="width: 80px; text-align: center;">Instalador</th>
                                    <th scope="col" style="width: 80px; text-align: center;">BBDD</th>
                                    <th scope="col" style="width: 140px;">Estado</th>
                                    <th scope="col">Diferencias / Detalle</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                $n = 1;
                                foreach ($filasComparadas as $fila):
                                    $rowClass = '';
                                    if ($fila['estadoTipo'] === 'solo_inst') {
                                        $rowClass = 'table-danger';
                                    } elseif ($fila['estadoTipo'] === 'solo_bd') {
                                        $rowClass = 'table-secondary';
                                    } elseif ($fila['estadoTipo'] === 'diff') {
                                        $rowClass = 'table-warning';
                                    }
                                ?>
                                    <tr <?php echo $rowClass !== '' ? 'class="' . $rowClass . '"' : ''; ?>>
                                        <td><?php echo $n++; ?></td>
                                        <td><span class="badge bg-dark"><?php echo htmlspecialchars($fila['Metodo'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><code><?php echo htmlspecialchars($fila['RutaWeb'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                        <td>
                                            <strong><?php echo $fila['RutaController']; ?></strong>
                                            <?php if (!empty($fila['Controller'])): ?>
                                                <br><small class="text-muted"><i class="bi bi-cpu"></i> <?php echo htmlspecialchars($fila['Controller'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($fila['inInst']): ?>
                                                <span class="badge-sp1 badge-sp1-bg-success">Sí</span>
                                            <?php else: ?>
                                                <span class="badge-sp1 badge-sp1-bg-danger">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($fila['inBD']): ?>
                                                <span class="badge-sp1 badge-sp1-bg-success">Sí</span>
                                            <?php else: ?>
                                                <span class="badge-sp1 badge-sp1-bg-danger">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $fila['estadoBadge']; ?></td>
                                        <td>
                                            <?php if (!empty($fila['diferencias'])): ?>
                                                <small>
                                                    <ul class="mb-0 ps-3">
                                                        <?php foreach ($fila['diferencias'] as $diff): ?>
                                                            <li><?php echo $diff; ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted"><i class="bi bi-check"></i> Coincide</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
