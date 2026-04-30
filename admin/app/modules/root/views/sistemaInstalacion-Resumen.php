<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
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
    /******************************************/
    function checkModuleData(Controller) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-xl';
        let URL       = '<?php echo $BASE.'/Core/plataforma/instalacion/checkModuleData/'; ?>'+Controller;
        const Options = {
            showModal : '#viewModal-xl',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function checkModuleBBDD(Controller) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-xl';
        let URL       = '<?php echo $BASE.'/Core/plataforma/instalacion/checkModuleBBDD/'; ?>'+Controller;
        const Options = {
            showModal : '#viewModal-xl',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }

</script>
