<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section dashboard">
    <div class="row">

        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 1,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 2,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 3,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 4,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 5,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 6,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 7,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 8,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 9,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 10,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <?php
            $Options = [
                'BASE'   => $BASE,
                'Type'   => 11,
            ];
            $data['Fnc_WidgetsCommon']->widget_meteo($Options);
            ?>
        </div>


    </div>
</section>
