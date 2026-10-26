<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<?php
// Verifico si hay datos
if(is_array($data['arrPosicion'])&&!empty($data['arrPosicion'])){
    // Recorro los datos
    foreach($data['arrPosicion'] as $opcion){ ?>
        <option value="<?php echo $opcion['idPosicion']; ?>"><?php echo $opcion['Codigo'].' | '.$opcion['Zona'].' / '.$opcion['Rack'].' / N'.$opcion['Nivel']; ?></option>
    <?php }
}else{ ?>
    <option value="">No hay posiciones disponibles</option>
<?php } ?>
