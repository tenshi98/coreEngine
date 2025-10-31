<div id="listTableData" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center pt-3">

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 IA_Chat">
                        <div class="py-2 px-4 border-bottom d-none d-lg-block">
                            <div class="d-flex align-items-center py-1">
                                <div class="position-relative">
                                    <img src="<?php echo $BASE.'/img/profile-img.jpg'; ?>" class="rounded-circle mr-1" alt="ChatBot" width="40" height="40">
                                </div>
                                <div class="flex-grow-1 pl-3">
                                    <strong>ChatBot</strong>
                                    <div style="display: none" id="barra" class="text-muted small"><em>Procesando...</em></div>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative">
                            <div id="chat-container" class="chat-messages p-4">


                            </div>
                        </div>

                        <div class="flex-grow-0 py-3 px-4 border-top">
                            <div class="input-group">
                                <input id="input-question" type="text" class="form-control" placeholder="Realiza una pregunta">
                                <button id="submit-button" class="btn btn-primary">Send</button>
                            </div>
                        </div>

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
    $(document).ready(function() {
        // Handler para enviar la pregunta al hacer Enter en el input
        $('#input-question').keypress(function(event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                var pregunta = $(this).val();
                if (validarCampos(pregunta)) {
                    realizarPregunta(pregunta);
                }
            }
        });

        // Handler para enviar la pregunta al hacer clic en el botón
        $('#submit-button').click(function() {
            var pregunta = $('#input-question').val();
            if (validarCampos(pregunta)) {
                realizarPregunta(pregunta);
            }
        });
    });

    function validarCampos(pregunta) {
        if (pregunta === '') {
            alert('Ingresa una pregunta antes de enviarla.');
            return false;
        }
        return true;
    }

    <?php
    $UserIMG = !empty($data['UserData']['UserIMG'])
        ? $BASE.'/upload/'.$data['UserData']['UserIMG']
        : $BASE.'/img/profile-img.jpg';
    ?>

    function realizarPregunta(pregunta) {
        // Mostrar procesamiento
        $("#barra").show();
        // Obtén una referencia al elemento del div
        var chatContainer = $('#chat-container');
        // Agregar la pregunta al contenedor de chat
        chatContainer.append('<div class="chat-message-right pb-4"><div><img src="<?php echo $UserIMG; ?>" class="rounded-circle mr-1" alt="<?php echo $data['UserData']['UserName']; ?>" width="40" height="40"></div><div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3"><div class="font-weight-bold mb-1">Tu</div>'+pregunta+'</div></div>');
        // Desplazarse al final del contenedor de chat
        chatContainer.scrollTop(chatContainer[0].scrollHeight);
        // Limpiar el input
        $('#input-question').val('');
        // Realizar la solicitud al servidor PHP
        $.ajax({
            type: "POST",
            url:  '<?php echo $BASE.'/Core/testeos/inteligenciaArtificial'; ?>',
            data: {mensaje: pregunta},
            success: function(respuesta) {
                $("#barra").hide();
                // Agregar la respuesta al contenedor de chat
                var jsonData = JSON.parse(respuesta);
                chatContainer.append('<div class="chat-message-left pb-4"><div><img src="<?php echo $BASE.'/img/profile-img.jpg'; ?>" class="rounded-circle mr-1" alt="ChatBot" width="40" height="40"></div><div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3"><div class="font-weight-bold mb-1">ChatBot</div>'+jsonData.message+'</div></div>');
                // Desplazarse al final del contenedor de chat
                chatContainer.scrollTop(chatContainer[0].scrollHeight);
            },
            error: function(data) {
                //se muestra el mensaje
                var jsonData = JSON.parse(respuesta);
                Swal.fire({position: "top-end",timer: 5000,showConfirmButton: false,timerProgressBar: true,icon: 'error',html: jsonData.message});
            },
        });
    }
</script>

