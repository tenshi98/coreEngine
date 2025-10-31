<?php
//Variables
$xData    = array();
$xFechas  = '';
//Obtengo las fechas
$arrFechas = array_unique(array_column($data['arrDataVentas'], 'VentaFecha'));
$arrProdID = array_unique(array_column($data['arrDataVentas'], 'idProducto'));
//Se crean datos vacios
foreach ($arrProdID as $prodID) {
    $xData[$prodID]['Producto'] = '';
    $xData[$prodID]['Unimed'] = '';
    $xData[$prodID]['Cantidad'] = '';
    $xData[$prodID]['Total']    = 0;
    $xData[$prodID]['Valor']    = 0;
    $xFechas  = '';
    foreach ($arrFechas as $fechas) {
        $xData[$prodID][$fechas]['Cantidad'] = '0';
        $xFechas                            .= '"'.$fechas.'",';
    }
}
//Recorro los datos
foreach ($data['arrDataVentas'] as $crud) {
    //se arman los datos
    $xData[$crud['idProducto']]['Producto']                      = $crud['Producto'];
    $xData[$crud['idProducto']]['Unimed']                        = $crud['Unimed'];
    $xData[$crud['idProducto']][$crud['VentaFecha']]['Cantidad'] = $data['Fnc_DataNumbers']->Cantidades($crud['Total'], 0);
    $xData[$crud['idProducto']]['Total']                         = $xData[$crud['idProducto']]['Total'] + $crud['Total'];
    $xData[$crud['idProducto']]['Valor']                         = $xData[$crud['idProducto']]['Valor'] + $crud['Valor'];
}
//Se recorren los datos
foreach ($arrProdID as $prodID) {
    foreach ($arrFechas as $fechas) {
        $xData[$prodID]['Cantidad'] .= $xData[$prodID][$fechas]['Cantidad'].',';
    }
}

?>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2 text-center mb-4">
        <canvas id="pieChart" style="max-height: 200px;"></canvas>
        <h5 class="card-title">Estado Partidas</h5>
        <script>
            $(document).ready(function() {
                /*********************************/
                new Chart(document.querySelector('#pieChart'), {
                    type: 'pie',
                    data: {
                        labels: [<?php foreach ($data['arrEstadisticas'] as $crud) {echo "'".$crud['EstadoPartida']."',";} ?>],
                        datasets: [{
                            label: 'N° Mensajes',
                            data: [<?php foreach ($data['arrEstadisticas'] as $crud) {echo $crud['Cuenta'].",";}?>],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: false,
                            }
                        }
                    }
                });
            });
        </script>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-7 mb-4">
        <?php
        //Verifico el monto
        $isNegative = isset($data['rowData']['Margen']) && $data['rowData']['Margen'] < 0;
        $Icon       = $isNegative ? '<i class="ri-arrow-down-circle-line"></i>' : '<i class="ri-arrow-up-circle-line"></i>';
        $Color      = $isNegative ? 'text-danger' : 'text-success';
        //arrays
        $arrData1 = [
            ['Icon' => '','Titulo' => 'Fecha de Creacion',   'Texto' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Fecha'])],
            ['Icon' => '','Titulo' => 'Nombre',              'Texto' => $data['rowData']['Nombre']],
            ['Icon' => '','Titulo' => 'Bodega',              'Texto' => $data['rowData']['Bodega']],
            ['Icon' => '','Titulo' => 'Estado',              'Texto' => '<span class="badge '.$data['rowData']['EstadoColor'].'">'.$data['rowData']['EstadoNombre'].'</span>'],
            ['Icon' => '','Titulo' => 'Usuario Creacion',    'Texto' => $data['rowData']['Creador']],
            ['Icon' => '','Titulo' => 'Observaciones',       'Texto' => $data['rowData']['Observaciones']],
        ];
        $arrData2 = [
            ['Icon' => '','Titulo' => 'Costos',       'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['Costos'], 2)],
            ['Icon' => '','Titulo' => 'Beneficios',   'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['Beneficios'], 2)],
            ['Icon' => '','Titulo' => 'Perdidas',     'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['Perdidas'], 2)],
            ['Icon' => '','Titulo' => 'Margen',       'Texto' => '<span class="'.$Color.'">'.$Icon.' '.$data['Fnc_DataNumbers']->Valores($data['rowData']['Margen'], 2).'</span>'],
        ];

        echo '<h5 class="card-title">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData1, 8);
        echo '
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                <h5 class="card-title">Montos y cantidades</h5>';
                $data['Fnc_WidgetsCommon']->responsiveTable($arrData2, 8);
            echo '
            </div>
        </div>';
        ?>

    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p class="text-center"><strong><?php echo 'Ventas diarias de la campaña '.$data['rowData']['Nombre']?></strong></p>

                        <div id="reportsChart_4"></div>

                        <script>
                            $(document).ready(function() {
                                new ApexCharts(document.querySelector("#reportsChart_4"), {
                                    series: [
                                        <?php
                                        //Se recorre
                                        foreach ($xData as $crud) {
                                            echo '
                                            {
                                                name: "'.$crud['Producto'].'",
                                                data: ['.$crud['Cantidad'].'],
                                            },';
                                        } ?>
                                    ],
                                    chart: {
                                        height: 200,
                                        type: 'area',
                                        toolbar: {
                                            show: false
                                        },
                                    },
                                    markers: {
                                        size: 4
                                    },
                                    colors: ['#4154f1', '#2eca6a', '#ff771d'],
                                    fill: {
                                        type: "gradient",
                                        gradient: {
                                            shadeIntensity: 1,
                                            opacityFrom: 0.3,
                                            opacityTo: 0.4,
                                            stops: [0, 90, 100]
                                        }
                                    },
                                    dataLabels: {
                                        enabled: false
                                    },
                                    stroke: {
                                        curve: 'smooth',
                                        width: 2
                                    },
                                    xaxis: {
                                        type: 'datetime',
                                        categories: [<?php echo $xFechas; ?>]
                                    },
                                    tooltip: {
                                        x: {
                                            format: 'dd/MM/yyyy'
                                        },
                                    },
                                    legend: {
                                        show: false,
                                    }
                                }).render();
                            });
                        </script>

                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <?php
                    //Se recorre
                    foreach ($xData as $crud) {
                        echo '
                        <div class="col-md-3 col-6">
                            <div class="text-center border-end">
                                <span class="text-success"><i class="bi bi-collection"></i> '.$crud['Total'].' '.$crud['Unimed'].'</span>
                                <h5 class="fw-bold mb-0">'.$data['Fnc_DataNumbers']->Valores($crud['Valor'], 0).'</h5>
                                <span class="text-uppercase">'.$crud['Producto'].'</span>
                            </div>
                        </div>';
                    } ?>


                </div>
            </div>
        </div>

    </div>
</div>
