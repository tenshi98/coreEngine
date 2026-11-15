<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $data['TableTitle']; ?></h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable"
                               data-more-url="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/search'; ?>"
                               data-has-more="<?php echo !empty($data['hasMore']) ? '1' : '0'; ?>"
                               data-next-offset="<?php echo (int) ($data['nextOffset'] ?? 0); ?>">
                            <thead>
                                <tr>
                                    <th scope="col">Tipo Movimiento</th>
                                    <th scope="col">Documento</th>
                                    <th scope="col" style="width: 100px;">Fecha</th>
                                    <th scope="col">Entidad</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Valor Total</th>
                                    <th scope="col">Monto Pagado</th>
                                    <th scope="col" style="width: 10px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php require(__DIR__.'/informeDocumentos-UpdateListRows.php'); ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="clearfix"></div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger float-end"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>
