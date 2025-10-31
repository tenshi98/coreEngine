<section class="section">

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4 col-xxl-3" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Default Accordion</h5>
                    <?php
                    $Options = [
                        'type'      => 1,
                        'showOpen'  => 1,
                        'arrData'   => $data['arrData']
                    ];
                    $data['Fnc_WidgetsCommon']->acordeon($Options); ?>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4 col-xxl-3" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Accordion without outline borders</h5>
                    <?php
                    $Options = [
                        'type'      => 2,
                        'showOpen'  => 1,
                        'arrData'   => $data['arrData']
                    ];
                    $data['Fnc_WidgetsCommon']->acordeon($Options); ?>
                </div>
            </div>
        </div>
    </div>

</section>
