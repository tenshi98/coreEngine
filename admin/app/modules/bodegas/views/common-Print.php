<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="stock-container">
    <div class="stock-header">
        <div class="row gutters">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                <address>
                    <p>
                        <span class="<?php echo 'badge-sp1 badge-sp1-'.$data['rowData']['ProductoCodigo']; ?>'">Producto: <?php echo $data['rowData']['ProductoNombre']; ?></span>
                        <span class="<?php echo 'badge-sp1 badge-sp1-'.($data['rowData']['StockActual'] < $data['rowData']['StockMinimo'] ? 'stock-bajo' : 'stock-ok'); ?>'">Stock: <?php echo $data['rowData']['StockActual']; ?></span>
                    </p>
                </address>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                <address>
                    <span class="float-end date"><i class="bi bi-truck"></i> <?php echo $data['Fnc_DataDate']->fechaEstandar($data['rowData']['FechaRegistro']); ?></span>
                </address>
            </div>
        </div>
        <div class="row gutters">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <p class="title"><strong><?php echo $data['rowData']['ProductoCodigo']; ?> - Stock Actual</strong></p>
            </div>
        </div>
    </div>
    <div class="stock-body">
        <div class="row gutters">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <p class="text-facture"><strong>Stock Actual:</strong> <?php echo $data['rowData']['StockActual']; ?> unid.</p>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <p class="text-facture"><strong>Stock Mínimo:</strong> <?php echo $data['rowData']['StockMinimo']; ?> unid.</p>
            </div>
        </div>
    </div>
</div>
