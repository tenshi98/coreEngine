<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class InternalFunctionsTokensOAuthTesting
 *
 * Pruebas del modulo `tokens` - helper `InternalFunctionsTokensOAuth` (reglas de `redirect_uri` del
 * servidor de autorizacion OAuth del MCP: D-9, B-2).
 *
 * Alcance: pruebas NO destructivas y puras. Las tres funciones (`redirectUriAllowed`,
 * `redirectUriMatches`, `redirectUriResolve`) son estaticas, sin BD ni sesion; por eso la clase no
 * extiende `ControllerBase` ni abre conexion (el runner solo exige que la clase se llame como el archivo
 * y que sus metodos publicos terminen en `Test`). No se escribe nada.
 *
 * @see projects/mcp-oauth/specs/technical.md §21.1
 * @see skills/testing/SKILL.md
 */
class InternalFunctionsTokensOAuthTesting {

    /*=========== Constantes de las pruebas ===========*/
    private const CLASE = 'InternalFunctionsTokensOAuth';

    /**
     * redirect_uri que NUNCA deben aceptarse (D-9, B-2).
     */
    private const NEGATIVAS = [
        'barra_invertida_userinfo' => 'http://evil.com\\@127.0.0.1:9999/cb',
        'userinfo_https'           => 'https://h@x/cb',
        'porcentaje_en_ruta'       => 'https://h/c%2Fb',
        'barra_invertida_ruta'     => 'https://h\\cb',
        'host_con_mayusculas'      => 'https://H.example/cb',
        'puerto_0'                 => 'https://h:0/cb',
        'puerto_70000'             => 'https://h:70000/cb',
        'http_ip_privada'          => 'http://10.0.0.1/cb',
        'localhost_con_punto'      => 'http://localhost.:6274/cb',
        'subdominio_localhost'     => 'http://x.localhost:6274/cb',
        'localhost_mayusculas'     => 'http://LOCALHOST:6274/cb',
        'esquema_privado_no_lista' => 'myapp://cb',
        'esquema_inverso_dominio'  => 'com.example.app:/cb',
        'javascript'               => 'javascript:alert(1)',
        'vacia'                    => '',
        'esquema_en_mayusculas'    => 'HTTPS://h.example/cb',
        'con_fragmento'            => 'https://h.example/cb#x',
        'http_no_loopback'         => 'http://example.com/cb',
        'espacio_en_ruta'          => 'https://h.example/c b',
        'puerto_vacio'             => 'http://127.0.0.1:/cb',
    ];

    /**
     * redirect_uri que SI deben aceptarse.
     */
    private const POSITIVAS = [
        'https_claude'      => 'https://claude.ai/api/mcp/auth_callback',
        'cursor'            => 'cursor://anysphere.cursor-retrieval/oauth/cb',
        'vscode'            => 'vscode://ms-vscode.x/cb',
        'localhost_6274'    => 'http://localhost:6274/cb',
        'loopback_ipv4'     => 'http://127.0.0.1:5555/cb',
        'loopback_ipv6'     => 'http://[::1]:5555/cb',
        'vscode_insiders'   => 'vscode-insiders://ms-vscode.x/cb',
        'https_con_puerto'  => 'https://h.example:8443/cb',
    ];

    /******************************************************************************/
    /*                             HELPERS INTERNOS                               */
    /******************************************************************************/
    /*******************************************************************/
    // Registra el resultado de un caso (privado: no se descubre como prueba)
    /*******************************************************************/
    private function addCase(array &$results, string $caso, bool $ok, string $razon = ''){
        $results[$caso] = $ok ? 'OK' : 'FALLO: '.$razon;
    }

    /*******************************************************************/
    // Descripcion legible de un valor para el mensaje de un caso
    /*******************************************************************/
    private function texto($valor): string{
        return var_export($valor, true);
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL HELPER                             */
    /******************************************************************************/
    /**
     * Verifica que el helper se cargue y exponga las tres funciones estaticas publicas y sus constantes.
     *
     * @return array Resultado por caso
     */
    public function estructuraHelperTest(){

        $results = [];

        $existe = class_exists(self::CLASE);
        $this->addCase($results, 'clase_helper_existe', $existe, 'no se carga la clase '.self::CLASE.' (autoload de functions/)');
        if (!$existe) {
            return $results;
        }

        foreach (['redirectUriAllowed' => 1, 'redirectUriMatches' => 2, 'redirectUriResolve' => 2] as $metodo => $parametros) {
            $ok = method_exists(self::CLASE, $metodo);
            $this->addCase($results, 'metodo_'.$metodo, $ok, 'no existe '.self::CLASE.'::'.$metodo.'()');
            if (!$ok) {
                continue;
            }
            $r = new ReflectionMethod(self::CLASE, $metodo);
            $this->addCase($results, 'metodo_'.$metodo.'_publico_estatico', $r->isPublic() && $r->isStatic(),
                $metodo.'() debe ser public static');
            $this->addCase($results, 'metodo_'.$metodo.'_parametros', $r->getNumberOfParameters() === $parametros,
                $metodo.'() declara '.$r->getNumberOfParameters().' parametros (esperado '.$parametros.')');
        }

        $this->addCase($results, 'constante_loopback', constant(self::CLASE.'::LOOPBACK') === ['localhost', '127.0.0.1', '[::1]'],
            'LOOPBACK debe ser exactamente localhost, 127.0.0.1 y [::1] (D-9)');
        $this->addCase($results, 'constante_esquemas_privados', constant(self::CLASE.'::ESQUEMAS_PRIVADOS') === ['cursor', 'vscode', 'vscode-insiders'],
            'ESQUEMAS_PRIVADOS debe ser la lista blanca cursor, vscode y vscode-insiders');
        $this->addCase($results, 'constante_max_uris', constant(self::CLASE.'::MAX_URIS') === 5, 'MAX_URIS debe ser 5');
        $this->addCase($results, 'constante_max_uri_chars', constant(self::CLASE.'::MAX_URI_CHARS') === 2000, 'MAX_URI_CHARS debe ser 2000');

        return $results;

    }

    /******************************************************************************/
    /*                         redirectUriAllowed: NEGATIVOS                       */
    /******************************************************************************/
    /**
     * `redirectUriAllowed` rechaza userinfo, barras invertidas, porcentajes, mayusculas, puertos fuera de
     * rango, http no loopback (incluidas las variantes `localhost.`, `x.localhost` y `LOCALHOST`), esquemas
     * privados fuera de la lista blanca, `javascript:` y las URI vacias o demasiado largas.
     *
     * @return array Resultado por caso
     */
    public function redirectUriAllowedNegativosTest(){

        $results = [];
        if (!class_exists(self::CLASE)) {
            $this->addCase($results, 'clase_helper_existe', false, 'no se carga la clase '.self::CLASE);
            return $results;
        }

        foreach (self::NEGATIVAS as $nombre => $uri) {
            $r = InternalFunctionsTokensOAuth::redirectUriAllowed($uri);
            $this->addCase($results, 'allowed_rechaza_'.$nombre, $r === false,
                'redirectUriAllowed('.$this->texto($uri).') devolvio '.$this->texto($r).' y debia ser false');
        }

        // Longitud: 2000 caracteres es el limite aceptado y 2001 se rechaza
        $base  = 'https://h.example/';
        $en    = $base.str_repeat('a', 2000 - strlen($base));
        $fuera = $base.str_repeat('a', 2001 - strlen($base));
        $this->addCase($results, 'allowed_longitud_2000_aceptada', strlen($en) === 2000 && InternalFunctionsTokensOAuth::redirectUriAllowed($en) === true,
            'una URI de exactamente 2000 caracteres debia aceptarse');
        $this->addCase($results, 'allowed_longitud_2001_rechazada', strlen($fuera) === 2001 && InternalFunctionsTokensOAuth::redirectUriAllowed($fuera) === false,
            'una URI de 2001 caracteres debia rechazarse');

        // Caracteres de control
        foreach (["\x00", "\x1f", "\x7f", "\t", "\n"] as $i => $ctl) {
            $uri = 'https://h.example/c'.$ctl.'b';
            $this->addCase($results, 'allowed_rechaza_control_'.$i, InternalFunctionsTokensOAuth::redirectUriAllowed($uri) === false,
                'una URI con el caracter de control 0x'.bin2hex($ctl).' debia rechazarse');
        }

        return $results;

    }

    /******************************************************************************/
    /*                         redirectUriAllowed: POSITIVOS                       */
    /******************************************************************************/
    /**
     * `redirectUriAllowed` acepta https, los esquemas privados de la lista blanca (`cursor`, `vscode`) y
     * las URI http loopback (`localhost`, `127.0.0.1`, `[::1]`) con puerto valido.
     *
     * @return array Resultado por caso
     */
    public function redirectUriAllowedPositivosTest(){

        $results = [];
        if (!class_exists(self::CLASE)) {
            $this->addCase($results, 'clase_helper_existe', false, 'no se carga la clase '.self::CLASE);
            return $results;
        }

        foreach (self::POSITIVAS as $nombre => $uri) {
            $r = InternalFunctionsTokensOAuth::redirectUriAllowed($uri);
            $this->addCase($results, 'allowed_acepta_'.$nombre, $r === true,
                'redirectUriAllowed('.$this->texto($uri).') devolvio '.$this->texto($r).' y debia ser true');
        }

        // Puertos limite
        $this->addCase($results, 'allowed_acepta_puerto_1', InternalFunctionsTokensOAuth::redirectUriAllowed('http://127.0.0.1:1/cb') === true,
            'el puerto 1 debia aceptarse');
        $this->addCase($results, 'allowed_acepta_puerto_65535', InternalFunctionsTokensOAuth::redirectUriAllowed('http://127.0.0.1:65535/cb') === true,
            'el puerto 65535 debia aceptarse');

        return $results;

    }

    /******************************************************************************/
    /*                              redirectUriMatches                             */
    /******************************************************************************/
    /**
     * `redirectUriMatches` rechaza cualquier pedida no permitida (aunque sea identica a la registrada o
     * su prefijo coincida), acepta la identica y el mismo host loopback con otro puerto, y exige que el
     * host pedido sea el registrado (`localhost` no equivale a `127.0.0.1`).
     *
     * @return array Resultado por caso
     */
    public function redirectUriMatchesTest(){

        $results = [];
        if (!class_exists(self::CLASE)) {
            $this->addCase($results, 'clase_helper_existe', false, 'no se carga la clase '.self::CLASE);
            return $results;
        }
        $m = static function (string $registrada, string $pedida): bool {
            return InternalFunctionsTokensOAuth::redirectUriMatches($registrada, $pedida);
        };

        // Toda pedida no permitida se rechaza, tambien si la registrada es la misma cadena
        foreach (self::NEGATIVAS as $nombre => $uri) {
            $this->addCase($results, 'matches_rechaza_'.$nombre, $m($uri, $uri) === false,
                'redirectUriMatches con registrada y pedida '.$this->texto($uri).' devolvio true y debia ser false');
        }

        // Prefijo coincidente pero pedida no permitida
        $this->addCase($results, 'matches_rechaza_prefijo_con_sufijo_malicioso',
            $m('http://127.0.0.1:5555/cb', 'http://127.0.0.1:5555/cb\\@evil') === false,
            'una pedida con prefijo igual a la registrada pero con "\\@evil" debia rechazarse');
        $this->addCase($results, 'matches_rechaza_pedida_con_userinfo',
            $m('https://h.example/cb', 'https://h.example@evil.example/cb') === false,
            'una pedida con userinfo debia rechazarse');

        // Identicas
        $this->addCase($results, 'matches_identica_https', $m('https://claude.ai/api/mcp/auth_callback', 'https://claude.ai/api/mcp/auth_callback') === true,
            'una pedida identica a la registrada debia aceptarse');
        $this->addCase($results, 'matches_identica_cursor', $m('cursor://anysphere.cursor-retrieval/oauth/cb', 'cursor://anysphere.cursor-retrieval/oauth/cb') === true,
            'una pedida cursor:// identica debia aceptarse');

        // https: solo identica
        $this->addCase($results, 'matches_https_otro_puerto_rechazada', $m('https://h.example/cb', 'https://h.example:8443/cb') === false,
            'en https solo se acepta la URI identica (otro puerto debia rechazarse)');
        $this->addCase($results, 'matches_https_otra_ruta_rechazada', $m('https://h.example/cb', 'https://h.example/otra') === false,
            'en https una ruta distinta debia rechazarse');

        // Loopback: otro puerto, mismo host, path y query
        $this->addCase($results, 'matches_loopback_otro_puerto', $m('http://127.0.0.1:5555/cb', 'http://127.0.0.1:6666/cb') === true,
            'mismo host y ruta loopback con otro puerto debia aceptarse');
        $this->addCase($results, 'matches_loopback_ipv6_otro_puerto', $m('http://[::1]:5555/cb', 'http://[::1]:6666/cb') === true,
            '[::1] con otro puerto debia aceptarse');
        $this->addCase($results, 'matches_loopback_otra_ruta', $m('http://127.0.0.1:5555/cb', 'http://127.0.0.1:6666/otra') === false,
            'loopback con otra ruta debia rechazarse');
        $this->addCase($results, 'matches_loopback_otra_query', $m('http://127.0.0.1:5555/cb?a=1', 'http://127.0.0.1:6666/cb?a=2') === false,
            'loopback con otra query debia rechazarse');
        $this->addCase($results, 'matches_loopback_query_ausente', $m('http://127.0.0.1:5555/cb', 'http://127.0.0.1:6666/cb?x=1') === false,
            'loopback con query anadida debia rechazarse');

        // El host pedido debe ser el registrado (D-9)
        $this->addCase($results, 'matches_localhost_otro_puerto', $m('http://localhost:1/cb', 'http://localhost:2/cb') === true,
            'localhost registrada y localhost con otro puerto debia aceptarse');
        $this->addCase($results, 'matches_localhost_vs_127_rechazada', $m('http://localhost:1/cb', 'http://127.0.0.1:2/cb') === false,
            'localhost registrada y 127.0.0.1 pedida debia rechazarse');
        $this->addCase($results, 'matches_127_vs_localhost_rechazada', $m('http://127.0.0.1:1/cb', 'http://localhost:2/cb') === false,
            '127.0.0.1 registrada y localhost pedida debia rechazarse');
        $this->addCase($results, 'matches_127_vs_ipv6_rechazada', $m('http://127.0.0.1:1/cb', 'http://[::1]:2/cb') === false,
            '127.0.0.1 registrada y [::1] pedida debia rechazarse');

        // Esquema distinto
        $this->addCase($results, 'matches_esquema_distinto', $m('http://localhost:1/cb', 'https://localhost:2/cb') === false,
            'http registrada y https pedida debia rechazarse');

        return $results;

    }

    /******************************************************************************/
    /*                              redirectUriResolve                             */
    /******************************************************************************/
    /**
     * `redirectUriResolve` devuelve la URI construida desde la REGISTRADA (misma cadena si son identicas;
     * en loopback, la registrada con el puerto pedido) y nunca la cadena pedida cuando esta difiere: una
     * pedida con otro host en mayusculas, con path o con query distintos devuelve `null`.
     *
     * @return array Resultado por caso
     */
    public function redirectUriResolveTest(){

        $results = [];
        if (!class_exists(self::CLASE)) {
            $this->addCase($results, 'clase_helper_existe', false, 'no se carga la clase '.self::CLASE);
            return $results;
        }
        $res = static function (array $registradas, string $pedida): ?string {
            return InternalFunctionsTokensOAuth::redirectUriResolve($registradas, $pedida);
        };

        // Identica: devuelve la registrada
        $r = $res(['https://claude.ai/api/mcp/auth_callback'], 'https://claude.ai/api/mcp/auth_callback');
        $this->addCase($results, 'resolve_identica', $r === 'https://claude.ai/api/mcp/auth_callback',
            'identica: esperado la URI registrada, real '.$this->texto($r));

        // localhost con otro puerto
        $r = $res(['http://localhost:1/cb'], 'http://localhost:2/cb');
        $this->addCase($results, 'resolve_localhost_otro_puerto', $r === 'http://localhost:2/cb',
            'localhost con otro puerto: esperado http://localhost:2/cb, real '.$this->texto($r));

        // 127.0.0.1 pedida con localhost registrada: ninguna coincide
        $r = $res(['http://localhost:1/cb'], 'http://127.0.0.1:2/cb');
        $this->addCase($results, 'resolve_localhost_vs_127_null', $r === null,
            'localhost registrada y 127.0.0.1 pedida: esperado null, real '.$this->texto($r));

        // La efectiva conserva path y query de la REGISTRADA
        $r = $res(['http://127.0.0.1:5555/cb?a=b'], 'http://127.0.0.1:6666/cb?a=b');
        $this->addCase($results, 'resolve_conserva_query_registrada', $r === 'http://127.0.0.1:6666/cb?a=b',
            'esperado http://127.0.0.1:6666/cb?a=b (path y query de la registrada con el puerto pedido), real '.$this->texto($r));
        $r = $res(['http://[::1]:5555/cb'], 'http://[::1]:6666/cb');
        $this->addCase($results, 'resolve_ipv6_otro_puerto', $r === 'http://[::1]:6666/cb',
            '[::1] con otro puerto: esperado http://[::1]:6666/cb, real '.$this->texto($r));

        // Nunca la cadena pedida si difiere: mayusculas, query anadida, path distinto, puerto invalido, userinfo
        $diferentes = [
            'mayusculas_host'  => ['http://localhost:1/cb', 'http://LOCALHOST:2/cb'],
            'query_anadida'    => ['http://127.0.0.1:1/cb', 'http://127.0.0.1:2/cb?x=1'],
            'path_distinto'    => ['http://127.0.0.1:1/cb', 'http://127.0.0.1:2/otra'],
            'puerto_70000'     => ['http://127.0.0.1:1/cb', 'http://127.0.0.1:70000/cb'],
            'userinfo'         => ['https://h.example/cb',  'https://h.example@evil.example/cb'],
            'barra_invertida'  => ['http://127.0.0.1:1/cb', 'http://127.0.0.1:2/cb\\@evil'],
            'localhost_punto'  => ['http://localhost:1/cb', 'http://localhost.:2/cb'],
        ];
        foreach ($diferentes as $nombre => [$registrada, $pedida]) {
            $r = $res([$registrada], $pedida);
            $this->addCase($results, 'resolve_nunca_la_pedida_'.$nombre, $r === null && $r !== $pedida,
                'registrada '.$this->texto($registrada).' y pedida '.$this->texto($pedida).': esperado null, real '.$this->texto($r));
        }

        // Varias registradas: toma la primera que coincide y construye desde esa
        $r = $res(['https://otra.example/cb', 'http://127.0.0.1:1/cb'], 'http://127.0.0.1:9/cb');
        $this->addCase($results, 'resolve_primera_coincidente', $r === 'http://127.0.0.1:9/cb',
            'con varias registradas: esperado http://127.0.0.1:9/cb, real '.$this->texto($r));

        // Lista vacia y entradas que no son cadenas
        $this->addCase($results, 'resolve_lista_vacia', $res([], 'https://h.example/cb') === null,
            'sin URIs registradas: esperado null');
        $r = $res([123, null, 'https://h.example/cb'], 'https://h.example/cb');
        $this->addCase($results, 'resolve_ignora_no_cadenas', $r === 'https://h.example/cb',
            'las entradas que no son cadena se ignoran: esperado https://h.example/cb, real '.$this->texto($r));

        return $results;

    }

}
