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
                    <h5 class="card-title">Popovers Normales</h5>
                    <p>Haga click en los botones para ver la información: superior, derecha, inferior e izquierda. </p>

                    <button type="button" class="btn btn-secondary" data-popover data-title="Popover top"    data-content="Este aparece arriba"    data-placement="top">Popover Top</button>
                    <button type="button" class="btn btn-secondary" data-popover data-title="Popover bottom" data-content="Este aparece abajo"     data-placement="bottom">Popover Bottom</button>
                    <button type="button" class="btn btn-secondary" data-popover data-title="Popover left"   data-content="Este aparece izquierda" data-placement="left">Popover left</button>
                    <button type="button" class="btn btn-secondary" data-popover data-title="Popover right"  data-content="Este aparece derecha"   data-placement="right">Popover right</button>

                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Popovers con colores</h5>
                    <p>Versiones con colores</p>

                    <button type="button" class="btn btn-primary"   data-popover data-extraclass="popover-primary"   data-title="Popover top"    data-content="Este aparece arriba"    data-placement="top">Popover Top</button>
                    <button type="button" class="btn btn-secondary" data-popover data-extraclass="popover-secondary" data-title="Popover bottom" data-content="Este aparece abajo"     data-placement="bottom">Popover Bottom</button>
                    <button type="button" class="btn btn-success"   data-popover data-extraclass="popover-success"   data-title="Popover left"   data-content="Este aparece izquierda" data-placement="left">Popover left</button>
                    <button type="button" class="btn btn-danger"    data-popover data-extraclass="popover-danger"    data-title="Popover right"  data-content="Este aparece derecha"   data-placement="right">Popover right</button>
                    <button type="button" class="btn btn-warning"   data-popover data-extraclass="popover-warning"   data-title="Popover top"    data-content="Este aparece arriba"    data-placement="top">Popover Top</button>
                    <button type="button" class="btn btn-info"      data-popover data-extraclass="popover-info"      data-title="Popover bottom" data-content="Este aparece abajo"     data-placement="bottom">Popover Bottom</button>
                    <button type="button" class="btn btn-light"     data-popover data-extraclass="popover-light"     data-title="Popover left"   data-content="Este aparece izquierda" data-placement="left">Popover left</button>
                    <button type="button" class="btn btn-dark"      data-popover data-extraclass="popover-dark"      data-title="Popover right"  data-content="Este aparece derecha"   data-placement="right">Popover right</button>
                    <button type="button" class="btn btn-link"      data-popover data-extraclass="popover-link"      data-title="Popover top"    data-content="Este aparece arriba"    data-placement="top">Popover Top</button>



                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Popovers con contenido HTML</h5>
                    <p>Versiones con contenido HTML</p>

                    <button type="button" class="btn btn-secondary" data-popover data-title="Popover HTML top"    data-html="true" data-content="<b>Este aparece arriba</b><br><button type='button' class='btn btn-primary'>Botón</button>"    data-placement="top">Popover HTML Top</button>
                    <button type="button" class="btn btn-secondary" data-popover data-title="Popover HTML bottom" data-html="true" data-content="<b>Este aparece abajo</b><br><button type='button' class='btn btn-primary'>Botón</button>"     data-placement="bottom">Popover HTML Bottom</button>
                    <button type="button" class="btn btn-secondary" data-popover data-title="Popover HTML left"   data-html="true" data-content="<b>Este aparece izquierda</b><br><button type='button' class='btn btn-primary'>Botón</button>" data-placement="left">Popover HTML left</button>
                    <button type="button" class="btn btn-secondary" data-popover data-title="Popover HTML right"  data-html="true" data-content="<b>Este aparece derecha</b><br><button type='button' class='btn btn-primary'>Botón</button>"   data-placement="right">Popover HTML right</button>

                </div>
            </div>

        </div>
    </div>

</section>
