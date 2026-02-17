<main>
    <div class="container">
        <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-8 d-flex flex-column align-items-center justify-content-center">

                        <div class="card" id="InstallerContent">

                            <h5 class="wizardTittle text-danger">¡Bienvenido al Asistente de Instalación!</h5>
                            <div class="steps clearfix">
                                <ul>
                                    <li class="current">  <a><div class="title"><span class="number">1</span><span class="title_text">Bienvenida</span></div></a></li>
                                    <li class="disabled"> <a><div class="title"><span class="number">2</span><span class="title_text">Credenciales</span></div></a></li>
                                    <li class="disabled"> <a><div class="title"><span class="number">3</span><span class="title_text">BBDD</span></div></a></li>
                                    <li class="disabled"> <a><div class="title"><span class="number">4</span><span class="title_text">Sumario</span></div></a></li>
                                    <li class="disabled"> <a><div class="title"><span class="number">5</span><span class="title_text">Finalización</span></div></a></li>
                                </ul>
                            </div>

                            <div class="card-body">
                                <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 col-xxl-8 mx-auto">

                                    <div class="text-center">
                                        <i class="bi bi-card-checklist text-color-blue" style="font-size: 5rem;"></i>
                                    </div>

                                    <p class="text-center text-muted">
                                        Este asistente te guiará paso a paso en el proceso de instalación del sistema.
                                        Se configurará la conexión a la base de datos y se ejecutarán los scripts necesarios.
                                    </p>

                                    <?php
                                    if($data['ValidInstall'] === false ){
                                        $data['Fnc_FormInputs']->formPostData(4, 4, 'bi bi-exclamation-triangle', 0, '<h4>Alerta:</h4><p>El sistema ya está instalado. Si deseas reinstalar, elimina el archivo de configuración primero y la base de datos.</p>');
                                    } ?>

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
                            </div>
                            <div class="card-footer text-end">
                                <?php if($data['ValidInstall'] === true ){ ?>
                                    <a href="#" class="btn btn-primary" onclick="return validateWelcome();"> Iniciar Instalación <i class="bi bi-arrow-right-circle"></i></a>
                                <?php }else{ ?>
                                    <a href="#" class="btn btn-primary disabled"> Iniciar Instalación <i class="bi bi-arrow-right-circle"></i></a>
                                <?php } ?>
                            </div>

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
    function validateWelcome() {
        //Ejecuto
        let Div       = '#InstallerContent';
        let URL       = '<?php echo $BASE.'/install/credentials'; ?>';
        const Options = {
            refreshTables:'false',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
</script>

<style>
.wizardTittle {font-size: 20px;color: #222;font-weight: bold;text-transform: uppercase;text-align: center;margin: 0px;margin-top: 30px;margin-bottom: 30px;line-height: 1.6;font-family: 'Roboto Slab';}



.steps {border-top: 1px solid #ebebeb;border-bottom: 1px solid #ebebeb;padding: 12px 20px;}
.steps ul {justify-content: space-around;-moz-justify-content: space-around;-webkit-justify-content: space-around;-o-justify-content: space-around;-ms-justify-content: space-around;display: flex;display: -webkit-flex;list-style-type: none;margin: 0;padding: 0;}
.steps ul li {padding-right: 80px;padding-left: 0px;position: relative;}
.steps ul li:after {position: absolute;width: 1px;height: 30px;content: "";background: #ebebeb;right: 18px;top: 50%;transform: translateY(-50%);-moz-transform: translateY(-50%);-webkit-transform: translateY(-50%);-o-transform: translateY(-50%);-ms-transform: translateY(-50%);}
.steps ul li a {color: #999;text-decoration: none;font-weight: bold;}
.steps ul li:last-child:after {width: 0px;}
.steps ul li .title {align-items: center;-moz-align-items: center;-webkit-align-items: center;-o-align-items: center;-ms-align-items: center;}
.steps ul li .title .number {width: 31px;height: 31px;border-radius: 50%;-moz-border-radius: 50%;-webkit-border-radius: 50%;-o-border-radius: 50%;-ms-border-radius: 50%;align-items: center;-moz-align-items: center;-webkit-align-items: center;-o-align-items: center;-ms-align-items: center;justify-content: center;-moz-justify-content: center;-webkit-justify-content: center;-o-justify-content: center;-ms-justify-content: center;border: 2px solid #999;margin-right: 15px;text-align: center;}
.steps ul li .title,
.steps ul li .title .number {display: flex;display: -webkit-flex;color: #999;}
.steps ul .current a,
.steps ul .current a .title,
.steps ul .current a .title .number {color: #222;}
.steps ul .current a .title .number {border: 2px solid #222;}
.steps ul .done a,
.steps ul .done a .title,
.steps ul .done a .title .number {color: #1abc9c;}
.steps ul .done a .title .number {border: 2px solid #1abc9c;}





</style>