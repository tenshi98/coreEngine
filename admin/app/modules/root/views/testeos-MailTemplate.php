<?php
echo norm_text($data['MailTemplate']);

function norm_text($Text){
    //Datos buscados
    $healthy = array('&lt;', '&gt;', '&quot;', '&amp;nbsp;');
    $yummy   = array('<', '>', '"', '&nbsp;');
    //devolver
    return str_replace($healthy, $yummy, $Text);

}

?>
