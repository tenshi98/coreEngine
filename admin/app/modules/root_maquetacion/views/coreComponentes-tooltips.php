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
                    <h5 class="card-title">Ejemplo Tooltips</h5>
                    <p>Pase el cursor sobre los botones a continuación para ver las cuatro direcciones de la información sobre herramientas: superior, derecha, inferior e izquierda.</p>

                    <!-- Tooltips Examples -->
                    <button type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ">
                        Tooltip on top
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="right" title="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ">
                        Tooltip on right
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ">
                        Tooltip on bottom
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="left" title="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ">
                        Tooltip on left
                    </button>
                    <!-- End Tooltips Examples -->

                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Tooltips Alternativo</h5>
                    <p>Version alternativa hecha para las datatables</p>

                    <!-- Tooltips Examples -->
                    <button type="button" class="btn btn-secondary tooltiplink" data-title="Loremipsum dolor sit amet">Tooltip on top</button>
                    <!-- End Tooltips Examples -->

                </div>
            </div>

        </div>

    </div>

</section>
