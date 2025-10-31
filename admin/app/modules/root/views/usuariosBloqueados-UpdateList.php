<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>DateTime</th>
            <th>Email</th>
            <th>Password</th>
            <th>IP_Client</th>
            <th>Agent_Transp</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrList'])&&!empty($data['arrList'])){
            //Recorro
            foreach($data['arrList'] AS $crud){
                //Variables
                $encryptedEmail = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['Email']);
                $EntidadEmail   = addslashes($crud['Email']);
                //Variables
                $encryptedIP = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['IP_Client']);
                $EntidadIP   = addslashes($crud['IP_Client']); ?>
                <tr>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                    <td><?php echo $crud['Hora']; ?></td>
                    <td><?php echo $crud['DateTime']; ?></td>
                    <td>
                        <?php echo $crud['Email']; ?>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="TDdelBTN_1('<?php echo $encryptedEmail; ?>','<?php echo $EntidadEmail; ?>')"  class="btn btn-danger btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                    <td><?php echo $crud['Password']; ?></td>
                    <td>
                        <?php echo $crud['IP_Client']; ?>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="TDdelBTN_2('<?php echo $encryptedIP; ?>','<?php echo $EntidadIP; ?>')"  class="btn btn-danger btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                    <td><?php echo $crud['Agent_Transp']; ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>