<?php
if(isset($data['rowData']['Direccion_img'])&&$data['rowData']['Direccion_img']!=''){
    $UserIMG = $BASE.'/upload/'.$data['rowData']['Direccion_img'];
}else{
    $UserIMG = $BASE.'/img/profile-img.jpg';
}
?>
<img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
<h2><?php echo $data['rowData']['Nombre']; ?></h2>
<h3><?php echo $data['rowData']['TipoUsuario']; ?></h3>
<div class="social-links mt-2">
    <?php if($data['rowData']['Social_X']!=''){ ?>          <a target="_blank" rel="noopener noreferrer" href="<?php echo $data['rowData']['Social_X']; ?>"         class="twitter"><i class="bi bi-twitter"></i></a><?php } ?>
    <?php if($data['rowData']['Social_Facebook']!=''){ ?>   <a target="_blank" rel="noopener noreferrer" href="<?php echo $data['rowData']['Social_Facebook']; ?>"  class="facebook"><i class="bi bi-facebook"></i></a><?php } ?>
    <?php if($data['rowData']['Social_Instagram']!=''){ ?>  <a target="_blank" rel="noopener noreferrer" href="<?php echo $data['rowData']['Social_Instagram']; ?>" class="instagram"><i class="bi bi-instagram"></i></a><?php } ?>
    <?php if($data['rowData']['Social_Linkedin']!=''){ ?>   <a target="_blank" rel="noopener noreferrer" href="<?php echo $data['rowData']['Social_Linkedin']; ?>"  class="linkedin"><i class="bi bi-linkedin"></i></a><?php } ?>
</div>

