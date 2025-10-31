<div id="listTableData" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center pt-3">
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                        <div class="text-center">
                            <h5 class="search-title"><i class="bi bi-mailbox"></i> Envio de correos</h5>
                        </div>
                        <form id="FormSendData" name="FormSendData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                            <?php
                            //se dibujan los inputs
                            $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',   'Name' => 'Hacia',   'Id' => 'Send_Hacia',   'Value' => '','Required' => 2,'Icon' => 'bx bx-mail-send']);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Asunto',  'Name' => 'Asunto',  'Id' => 'Send_Asunto',  'Value' => '','Required' => 2]);
                            $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Mensaje', 'Name' => 'Mensaje', 'Id' => 'Send_Mensaje', 'Value' => '','Required' => 2]);

                            ?>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <button type="submit" class="btn btn-success"><i class="bi bi-cursor"></i> Enviar Correos</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      FORMULARIO DE BUSQUEDA                       */
    /*********************************************************************/
    /******************************************/
    $("#FormSendData").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/testeos/'.$data['TypeSend']; ?>';
            let Informacion = $("#FormSendData").serialize();
            const Options     = {
                ClearForm:'FormSendData',
                closeObject:'#PDloader',
                showNoti:'Mensaje enviado correctamente',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>

