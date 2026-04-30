<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section dashboard">
    <div class="row">

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="row">
                <div class="col-sm-4 col-xl-4">
                    <?php $data['Fnc_WidgetsCommon']->widget_mejs_radio_player($BASE); ?>
                </div>
                <div class="col-sm-4 col-xl-4">
                    <?php $data['Fnc_WidgetsCommon']->widget_radio_player($BASE, 2); ?>
                </div>
            </div>
        </div>

    </div>
</section>

