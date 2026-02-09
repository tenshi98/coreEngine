<main>
    <div class="container">
        <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-6 d-flex flex-column align-items-center justify-content-center">

                        <div class="card" id="InstallerContent">

                            <h5 class="card-header text-center"> Sistema de Instalación</h5>
                            <form id="FormWelcome" name="FormWelcome" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                                <div class="card-body">
                                    <div class="text-center">
                                        <i class="bi bi-database-gear text-color-blue" style="font-size: 5rem;"></i>
                                    </div>

                                    <h3 class="text-center text-danger">¡Bienvenido al Asistente de Instalación!</h3>
                                    <p class="text-center text-muted">
                                        Este asistente te guiará paso a paso en el proceso de instalación del sistema.
                                        Se configurará la conexión a la base de datos y se ejecutarán los scripts necesarios.
                                    </p>

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
                                    <?php if($data['ValidInstall'] === true ){ ?>
                                        <button type="submit" class="btn btn-primary"> Siguiente <i class="bi bi-arrow-right-circle"></i></button>
                                    <?php }else{ ?>
                                        <a href="#" class="btn btn-primary disabled"> Siguiente <i class="bi bi-arrow-right-circle"></i></a>
                                    <?php } ?>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormWelcome").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            e.preventDefault();
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Metodo      = 'POST';
            let Direccion   = '<?php echo $BASE.'/install/credentials'; ?>';
            let Informacion = $("#FormWelcome").serialize();
            const Options     = {
                UpdateDivFrom : 'InstallerContent',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

    function validateCredentials() {
        $("#FormCredentials").submit(function(e) {
            //Se validan los datos de los formularios
            var validatorResult = validator.checkAll(this);
            //verifico el resultado
            if(validatorResult.valid===false){
                return !!validatorResult.valid;
            }else{
                e.preventDefault();
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'POST';
                let Direccion   = '<?php echo $BASE.'/install/database'; ?>';
                let Informacion = $("#FormCredentials").serialize();
                const Options     = {
                    UpdateDivFrom : 'InstallerContent',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    function validateDatabase() {
        $("#FormDatabase").submit(function(e) {
            //Se validan los datos de los formularios
            var validatorResult = validator.checkAll(this);
            //verifico el resultado
            if(validatorResult.valid===false){
                return !!validatorResult.valid;
            }else{
                e.preventDefault();
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'POST';
                let Direccion   = '<?php echo $BASE.'/install/summary'; ?>';
                let Informacion = $("#FormDatabase").serialize();
                const Options     = {
                    UpdateDivFrom : 'InstallerContent',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    function validateSummary() {
        $("#FormDatabase").submit(function(e) {
            //Se validan los datos de los formularios
            var validatorResult = validator.checkAll(this);
            //verifico el resultado
            if(validatorResult.valid===false){
                return !!validatorResult.valid;
            }else{
                e.preventDefault();
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'POST';
                let Direccion   = '<?php echo $BASE.'/install/summary'; ?>';
                let Informacion = $("#FormDatabase").serialize();
                const Options     = {
                    UpdateDivFrom : 'InstallerContent',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

</script>
