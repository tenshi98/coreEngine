<?php
//Variables
$Fecha_Mes   = '';
$Costos      = '';
$Beneficios  = '';
$Perdidas    = '';
$Margenes    = '';
//Se recorren los datos
foreach($data['MainViewData']['Data_Campanas'] AS $crud){
    $Fecha_Mes   .= '"'.$data['Fnc_Convertions']->numero2mes($crud['Fecha_Mes']).'",';
    $Costos      .= '"'.$data['Fnc_DataNumbers']->valoresComparables($crud['Costos'], 0).'",';
    $Beneficios  .= '"'.$data['Fnc_DataNumbers']->valoresComparables($crud['Beneficios'], 0).'",';
    $Perdidas    .= '"'.$data['Fnc_DataNumbers']->valoresComparables($crud['Perdidas'], 0).'",';
    $Margenes    .= '"'.$data['Fnc_DataNumbers']->valoresComparables($crud['Margenes'], 0).'",';
}

?>

<!-- Line Chart -->
<div id="reportsChart" style="max-height: 400px;"></div>

<script>
    function loadAsyncChart() {
        return new Promise((resolve) => {
            new ApexCharts(document.querySelector("#reportsChart"), {
                series: [
                    {name: 'Costos',     data: [<?php echo $Costos; ?>]},
                    {name: 'Beneficios', data: [<?php echo $Beneficios; ?>]},
                    {name: 'Perdidas',   data: [<?php echo $Perdidas; ?>]},
                    {name: 'Margenes',   data: [<?php echo $Margenes; ?>]},
                ],
                chart: {
                    type: 'area',
                    toolbar: {show: false},
                },
                markers: {size: 4},
                colors: ['#4154f1', '#2eca6a', '#ff771d', '#F3C541'],
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
                    type: 'date',
                    categories: [<?php echo $Fecha_Mes; ?>]
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yy'
                    },
                    y: {
                        formatter: function(val) {
                            Valor = parseInt(val);
	                        return "$ " + Valor.toLocaleString('es-CL');
                        }
                    }
                }
            }).render();
        });
    }

    async function asyncCall() {
        const result = await loadAsyncChart();
    }

    asyncCall();

</script>
