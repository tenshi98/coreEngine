
<button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-list"></i> Menu</button>
<ul class="dropdown-menu dropdown-menu-large dropdown-menu-large-arrow row">

    <?php
    // Verifico si hay datos
    if(!empty($_SESSION['arrMenu']) && is_array($_SESSION['arrMenu'])){
        // Nro de columnas visuales en desktop (col-md-4 => 3 columnas, Bootstrap apila en movil)
        $numColumnas = 3;
        $colClass    = 'col-xs-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 col-xxl-4';
        // 1. Construyo un bloque HTML por categoria + su altura estimada
        // Altura = 1 titulo + N items + 1 divisor
        $bloques = [];
        foreach ($_SESSION['arrMenu'] as $Categoria=>$permisos){
            if(!is_array($permisos) || empty($permisos)){
                continue;
            }
            $bloqueHtml  = '<ul><li class="dropdown-tittle">'.htmlspecialchars((string)$Categoria, ENT_QUOTES, 'UTF-8').'</li>';
            foreach ($permisos as $perm){
                $iconoBase = isset($perm['PermisosIcon']) ? (string)$perm['PermisosIcon'] : '';
                if(!empty($perm['PermisosIconColor'])){
                    $iconoBase .= ' '.$perm['PermisosIconColor'];
                }
                $icono  = htmlspecialchars($iconoBase, ENT_QUOTES, 'UTF-8');
                $ruta   = htmlspecialchars((string)$perm['RutaWeb'], ENT_QUOTES, 'UTF-8');
                $nombre = htmlspecialchars((string)$perm['Nombre'], ENT_QUOTES, 'UTF-8');
                $bloqueHtml .= '<li><a href="'.$BASE.'/'.$ruta.'/listAll"><i class="'.$icono.'" aria-hidden="true"></i> '.$nombre.'</a></li>';
            }
            $bloqueHtml .= '</ul>';
            $bloques[] = [
                'cat'    => (string)$Categoria,
                'html'   => $bloqueHtml,
                'altura' => count($permisos) + 2,
            ];
        }
        // 2. Separo la categoria fija "Administracion" (con o sin tilde) para que quede primera
        $normalizar = function($texto){
            $texto = mb_strtolower((string)$texto, 'UTF-8');
            $texto = str_replace(['á','é','í','ó','ú','ü','ñ'], ['a','e','i','o','u','u','n'], $texto);
            return $texto;
        };
        $bloqueFijo = [];
        $resto      = [];
        foreach ($bloques as $bloque){
            if($normalizar($bloque['cat']) === 'administracion'){
                $bloqueFijo[] = $bloque;
            } else {
                $resto[] = $bloque;
            }
        }
        // 3. Ordeno el resto por tamaño descendente para compactar (bin-packing greedy)
        usort($resto, function($a, $b){
            return $b['altura'] <=> $a['altura'];
        });
        // 4. Reparto best-fit: el bloque fijo abre la columna 0, luego cada bloque va a la columna mas baja
        $columnas = array_fill(0, $numColumnas, ['bloques' => [], 'altura' => 0]);
        foreach ($bloqueFijo as $bloque){
            $columnas[0]['bloques'][] = $bloque['html'];
            $columnas[0]['altura']   += $bloque['altura'];
        }
        foreach ($resto as $bloque){
            $idxMin = 0;
            for($i = 1; $i < $numColumnas; $i++){
                if($columnas[$i]['altura'] < $columnas[$idxMin]['altura']){
                    $idxMin = $i;
                }
            }
            $columnas[$idxMin]['bloques'][] = $bloque['html'];
            $columnas[$idxMin]['altura']   += $bloque['altura'];
        }
        // 4. Renderizo columnas; divisor solo ENTRE categorias (no al final, evita aire)
        $htmlMenu = '';
        foreach ($columnas as $col){
            if(empty($col['bloques'])){
                continue;
            }
            $htmlMenu .= '<li class="'.$colClass.'">'.implode('<ul><li class="divider"></li></ul>', $col['bloques']).'</li>';
        }
        //Imprimo menu
        echo $htmlMenu;
    }
    ?>

</ul>
