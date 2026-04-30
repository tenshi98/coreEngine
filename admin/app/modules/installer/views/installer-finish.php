<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<h5 class="wizardTittle text-danger">Instalación Completada</h5>
<div class="steps clearfix">
    <ul>
        <li class="done">    <a><div class="title"><span class="number"><i class="bi bi-check-lg"></i></span><span class="title_text">Bienvenida</span></div></a></li>
        <li class="done">    <a><div class="title"><span class="number"><i class="bi bi-check-lg"></i></span><span class="title_text">Credenciales</span></div></a></li>
        <li class="done">    <a><div class="title"><span class="number"><i class="bi bi-check-lg"></i></span><span class="title_text">BBDD</span></div></a></li>
        <li class="done">    <a><div class="title"><span class="number"><i class="bi bi-check-lg"></i></span><span class="title_text">Sumario</span></div></a></li>
        <li class="current"> <a><div class="title"><span class="number">5</span><span class="title_text">Finalización</span></div></a></li>
    </ul>
</div>

<div class="card-body">
    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 col-xxl-8 mx-auto">
        <div class="text-center">
            <i class="bi bi-database-gear text-color-blue" style="font-size: 5rem;"></i>
        </div>
        <h4 class="text-center text-danger">¡Instalación Exitosa!</h4>
        <p class="text-center text-muted">El sistema se ha instalado correctamente.</p>

    </div>
</div>
<div class="card-footer text-end">
    <a href="<?php echo $BASE.'/install'?>" class="btn btn-primary"><i class="bi bi-arrow-left-circle"></i> Finalizar</a>
</div>

