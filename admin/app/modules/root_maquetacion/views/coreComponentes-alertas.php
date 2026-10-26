<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Default</h5>
                    <?php
                    $data['Fnc_WidgetsCommon']->printAlertData(1, 1, '', 1, 'A simple primary alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(2, 1, '', 1, 'A simple primary alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(3, 1, '', 1, 'A simple primary alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(4, 1, '', 1, 'A simple primary alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(5, 1, '', 1, 'A simple primary alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(6, 1, '', 1, 'A simple primary alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(7, 1, '', 1, 'A simple primary alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(8, 1, '', 1, 'A simple primary alert—check it out!');
                    ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Default With Icon</h5>
                    <?php
                    $data['Fnc_WidgetsCommon']->printAlertData(1, 2, 'bi bi-star me-1', 1, 'A simple primary alert with icon—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(2, 2, 'bi bi-star me-1', 1, 'A simple primary alert with icon—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(3, 2, 'bi bi-star me-1', 1, 'A simple primary alert with icon—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(4, 2, 'bi bi-star me-1', 1, 'A simple primary alert with icon—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(5, 2, 'bi bi-star me-1', 1, 'A simple primary alert with icon—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(6, 2, 'bi bi-star me-1', 1, 'A simple primary alert with icon—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(7, 2, 'bi bi-star me-1', 1, 'A simple primary alert with icon—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(8, 2, 'bi bi-star me-1', 1, 'A simple primary alert with icon—check it out!');
                    ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Outlined</h5>
                    <?php
                    $data['Fnc_WidgetsCommon']->printAlertData(1, 3, '', 1, 'A simple primary outlined alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(2, 3, '', 1, 'A simple primary outlined alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(3, 3, '', 1, 'A simple primary outlined alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(4, 3, '', 1, 'A simple primary outlined alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(5, 3, '', 1, 'A simple primary outlined alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(6, 3, '', 1, 'A simple primary outlined alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(7, 3, '', 1, 'A simple primary outlined alert—check it out!');
                    $data['Fnc_WidgetsCommon']->printAlertData(8, 3, '', 1, 'A simple primary outlined alert—check it out!');
                    ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Outlined With Icon</h5>
                    <?php
                    $data['Fnc_WidgetsCommon']->printAlertData(1, 4, 'bi bi-star', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(2, 4, 'bi bi-star', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(3, 4, 'bi bi-star', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(4, 4, 'bi bi-star', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(5, 4, 'bi bi-star', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(6, 4, 'bi bi-star', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(7, 4, 'bi bi-star', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(8, 4, 'bi bi-star', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Outlined info</h5>
                    <?php
                    $data['Fnc_WidgetsCommon']->printAlertData(1, 5, '', 1, '<h4>A simple primary outlined alert—check it out!</h4><p>consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>');
                    $data['Fnc_WidgetsCommon']->printAlertData(2, 5, '', 1, '<h4>A simple primary outlined alert—check it out!</h4><p>consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>');
                    $data['Fnc_WidgetsCommon']->printAlertData(3, 5, '', 1, '<h4>A simple primary outlined alert—check it out!</h4><p>consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>');
                    $data['Fnc_WidgetsCommon']->printAlertData(4, 5, '', 1, '<h4>A simple primary outlined alert—check it out!</h4><p>consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>');
                    $data['Fnc_WidgetsCommon']->printAlertData(5, 5, '', 1, '<h4>A simple primary outlined alert—check it out!</h4><p>consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>');
                    $data['Fnc_WidgetsCommon']->printAlertData(6, 5, '', 1, '<h4>A simple primary outlined alert—check it out!</h4><p>consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>');
                    $data['Fnc_WidgetsCommon']->printAlertData(7, 5, '', 1, '<h4>A simple primary outlined alert—check it out!</h4><p>consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>');
                    $data['Fnc_WidgetsCommon']->printAlertData(8, 5, '', 1, '<h4>A simple primary outlined alert—check it out!</h4><p>consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>');
                    ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Default Solid Color</h5>
                    <?php
                    $data['Fnc_WidgetsCommon']->printAlertData(1, 6, '', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(2, 6, '', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(3, 6, '', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(4, 6, '', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(5, 6, '', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(6, 6, '', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(7, 6, '', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    $data['Fnc_WidgetsCommon']->printAlertData(8, 6, '', 1, 'A simple primary alert with icon—check it out!<br>A simple primary');
                    ?>
                </div>
            </div>

        </div>
    </div>
</section>
