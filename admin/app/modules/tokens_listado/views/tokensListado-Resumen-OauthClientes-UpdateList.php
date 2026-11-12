<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover">
    <thead>
        <tr>
            <th scope="col">Nombre (declarado)</th>
            <th scope="col">client_id</th>
            <th scope="col">Tipo</th>
            <th scope="col">Estado</th>
            <th scope="col">Accesos</th>
            <th scope="col">Última concesión</th>
            <th scope="col">Redirección</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Verifico si hay datos
        if(isset($data['arrClientes']) && is_array($data['arrClientes']) && !empty($data['arrClientes'])){
            // Recorro los datos
            foreach($data['arrClientes'] as $crud){
                // Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idCliente']);
                $level       = $data['UserAccess']['LevelAccess'];
                $NombreJS    = json_encode((string)($crud['Nombre'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                $ClientId    = (string)($crud['ClientId'] ?? '');
                // Redirecciones registradas (JSON array): se muestran completas como texto
                $uris = json_decode((string)($crud['RedirectUris'] ?? ''), true);
                if(!is_array($uris)){ $uris = [(string)($crud['RedirectUris'] ?? '')]; } ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)($crud['Nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(substr($ClientId, 0, 8), ENT_QUOTES, 'UTF-8'); ?>&hellip;</td>
                    <td><?php echo htmlspecialchars((string)($crud['AuthMethod'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge-sp1 badge-sp1-<?php echo htmlspecialchars((string)($crud['EstadoColor'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string)($crud['Estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td><?php echo (int)($crud['NAccesos'] ?? 0); ?></td>
                    <td><?php echo htmlspecialchars((string)($crud['UltimaConcesion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php foreach($uris as $uri){ ?>
                            <div><?php echo htmlspecialchars((string)(is_scalar($uri) ? $uri : ''), ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 2) {echo '<button type="button" onclick=\'tabClienteRevocar("'.$encryptedId['data'].'", '.$NombreJS.')\' class="btn btn-danger btn-sm tooltiplink" data-title="Revocar accesos"><i class="bi bi-slash-circle"></i> Revocar accesos</button>';}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="8" class="text-center text-muted">Ningún cliente OAuth usa este token</td></tr>
        <?php } ?>
    </tbody>
</table>
