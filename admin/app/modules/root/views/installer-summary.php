<h5 class="card-header text-center"> Sistema de Instalación</h5>
<div class="card-body">
    <div class="text-center">
        <i class="bi bi-database-gear text-color-blue" style="font-size: 5rem;"></i>
    </div>

    <h3 class="text-center text-danger">¡Bienvenido al Asistente de Instalación!</h3>
    <div>
        <p class="text-center text-muted">
            Este asistente te guiará paso a paso en el proceso de instalación del sistema.
            Se configurará la conexión a la base de datos y se ejecutarán los scripts necesarios.
        </p>
    </div>

    <div class="box has-text-left mt-5" style="background-clip: border-box;border: 1px solid rgba(0,0,0,0.175);border-radius: 0.375rem;padding:25px;">
        <h4 class="text-center text-color-blue">
            <i class="bi bi-list-check text-color-blue"></i> El proceso incluye:
        </h4>
        <ul class="list-unstyled">
            <li><i class="bi bi-check text-color-green-dark"></i> Validación de credenciales MySQL</li>
            <li><i class="bi bi-check text-color-green-dark"></i> Verificación de permisos</li>
            <li><i class="bi bi-check text-color-green-dark"></i> Creación de base de datos</li>
            <li><i class="bi bi-check text-color-green-dark"></i> Ejecución de scripts SQL</li>
            <li><i class="bi bi-check text-color-green-dark"></i> Generación de archivo de configuración</li>
        </ul>
    </div>

</div>
<div class="card-footer text-end">
    <a href="<?php echo $BASE.'/install'?>" class="btn btn-danger"><i class="bi bi-arrow-left-circle"></i> Reiniciar</a>
    <?php if($data['ValidInstall'] === true ){ ?>
        <button type="submit" class="btn btn-primary" onclick="return validateSummary();"> Instalar <i class="bi bi-arrow-right-circle"></i></button>
    <?php }else{ ?>
        <a href="#" class="btn btn-primary disabled"> Instalar <i class="bi bi-arrow-right-circle"></i></a>
    <?php } ?>
</div>


