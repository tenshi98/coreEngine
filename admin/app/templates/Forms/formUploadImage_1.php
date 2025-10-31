<div class="fileUpload btn btn-primary">
    <span><i class="fa fa-search" aria-hidden="true"></i> Seleccionar Imagen</span>
    <input name="<?php echo $name; ?>" id="<?php echo $name; ?>" type="file" class="upload" />
</div>

<div class="modal" id="<?php echo $name; ?>Modal" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Zona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height: 410px;">
                <div id="<?php echo $name; ?>Preview"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="cortar_image">Cortar y Subir</button>
            </div>
        </div>
    </div>
</div>

<script>
    /******************************************/
    $(document).ready(function(){
        //medidas de la imagen
        $image_crop = $("#<?php echo $name; ?>Preview").croppie({
            enableExif: true,
            viewport: {
                width:200,
                height:200,
                type:"square" //square-circle
            },
            boundary:{
                width:300,
                height:300
            }
        });
        //se abre el modal
        $("#<?php echo $name; ?>").on("change", function(){
            var reader = new FileReader();
            reader.onload = function (event) {
                $image_crop.croppie("bind", {
                    url: event.target.result
                }).then(function(){
                    console.log("jQuery bind complete");
                });
            }
            reader.readAsDataURL(this.files[0]);
            $("#<?php echo $name; ?>Modal").modal("show");
        });
        //se corta la imagen y se envia al formulario
        $("#cortar_image").click(function(event){
            $image_crop.croppie("result", {
                type: "canvas",
                size: "viewport"
            }).then(function(response){
                $.ajax({
                    url:"<?php echo $URL; ?>",
                    type: "POST",
                    data:{
                        "<?php echo $name; ?>": response,
                        <?php echo $ExtraData; ?>,
                    },
                    success:function(data){
                        $("#<?php echo $name; ?>Modal").modal("hide");
                        location.reload();
                    }
                });
            })
        });
    });
</script>