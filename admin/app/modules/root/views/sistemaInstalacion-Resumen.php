<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Instalacion de Modulos</h5>
            <div class="clearfix"></div>
            <div id="DivResumen">
                <?php require_once('sistemaInstalacion-Resumen-Update.php'); ?>
            </div>
        </div>
    </div>
</div>

<script>
    /******************************************/
    function installModule(Controller) {
        Swal.fire({
            title: "Instalar Modulo",
            text: "Esta a punto de instalar este modulo, ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, instalar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'PUT';
                let Direccion   = '<?php echo $BASE.'/Core/plataforma/instalacion/installModule'; ?>';
                let Informacion = {"Controller": Controller };
                const Options     = {
                    UpdateDiv : [
                        {Div:'#DivResumen', fromData:'<?php echo $BASE.'/Core/plataforma/instalacion/resumenUpdate';?>', refreshTbl:'false'}
                    ],
                    showNoti:'Modulo Instalado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /******************************************/
    function uninstallModule(Controller) {
        Swal.fire({
            title: "Instalar Modulo",
            text: "Esta a punto de desinstalar este modulo, ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, desinstalar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'PUT';
                let Direccion   = '<?php echo $BASE.'/Core/plataforma/instalacion/uninstallModule'; ?>';
                let Informacion = {"Controller": Controller };
                const Options     = {
                    UpdateDiv : [
                        {Div:'#DivResumen', fromData:'<?php echo $BASE.'/Core/plataforma/instalacion/resumenUpdate';?>', refreshTbl:'false'}
                    ],
                    showNoti:'Modulo Desinstalado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

</script>