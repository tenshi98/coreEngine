<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover">
    <thead>
        <tr>
            <th scope="col">Cliente</th>
            <th scope="col">Emitido</th>
            <th scope="col">Access expira</th>
            <th scope="col">Renovable hasta</th>
            <th scope="col">Estado</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Verifico si hay datos
        if(isset($data['arrAccesos']) && is_array($data['arrAccesos']) && !empty($data['arrAccesos'])){
            // Recorro los datos
            foreach($data['arrAccesos'] as $crud){
                // Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idToken']);
                $level       = $data['UserAccess']['LevelAccess'];
                $ClienteJS   = json_encode((string)($crud['Cliente'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                $Refresh     = (string)($crud['RefreshExpira'] ?? '');
                // Estado del acceso: vigente, expirado renovable o expirado
                if((int)($crud['Expirado'] ?? 0) === 0){
                    $Estado = '<span class="badge bg-success">Vigente</span>';
                }elseif($Refresh !== ''){
                    $Estado = '<span class="badge bg-warning text-dark">Expirado, renovable hasta '.htmlspecialchars($Refresh, ENT_QUOTES, 'UTF-8').'</span>';
                }else{
                    $Estado = '<span class="badge bg-secondary">Expirado</span>';
                } ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)($crud['Cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)($crud['FechaCreacion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)($crud['FechaExpiracion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($Refresh, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $Estado; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 2) {echo '<button type="button" onclick=\'tabAccesoRevocar("'.$encryptedId['data'].'", '.$ClienteJS.')\' class="btn btn-danger btn-sm tooltiplink" data-title="Revocar acceso"><i class="bi bi-slash-circle"></i> Revocar</button>';}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="6" class="text-center text-muted">Sin accesos OAuth emitidos con este token</td></tr>
        <?php } ?>
    </tbody>
</table>
