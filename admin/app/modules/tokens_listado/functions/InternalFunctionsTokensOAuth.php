<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Reglas de redirect_uri del servidor de autorizacion OAuth del MCP (D-9, B-2).
 *
 * Helper de modulo sin herencia ni BD. Lo usan el servidor de autorizacion (admin/MCP/src/OAuth/) y las suites,
 * por eso no lleva el guard defined('MCP_ROOT'). Los metodos son estaticos: se llaman con
 * InternalFunctionsTokensOAuth::redirectUriAllowed() o desde una instancia.
 *
 * @see projects/mcp-oauth/specs/technical.md §7.1
 */
class InternalFunctionsTokensOAuth {

    /*******************************************************************/
    // Constantes
    /*******************************************************************/
    /** Maximo de redirect_uri por cliente. */
    public const MAX_URIS = 5;

    /** Longitud maxima de una redirect_uri. */
    public const MAX_URI_CHARS = 2000;

    /**
     * Unicos hosts http admitidos (loopback); comparacion exacta y en minusculas.
     *
     * parse_url no normaliza el host: LOCALHOST, localhost. y x.localhost quedan fuera.
     * Desviacion consciente (D-9): RFC 8252 §8.3 desaconseja "localhost" (prefiere la IP literal);
     * se admite por interoperabilidad con los clientes MCP.
     */
    public const LOOPBACK = ['localhost', '127.0.0.1', '[::1]'];

    /**
     * Lista blanca de esquemas privados (desviacion documentada de MCP y de RFC 8252 §7.1: no se admiten otros).
     */
    public const ESQUEMAS_PRIVADOS = ['cursor', 'vscode', 'vscode-insiders'];

    /*******************************************************************/
    // Validadores
    /*******************************************************************/
    /**
     * Indica si una redirect_uri es admisible (D-9).
     *
     * Falso si: longitud 0 o mayor que MAX_URI_CHARS; contiene #, \, %, @ o caracteres [\x00-\x20\x7F];
     * parse_url falla o no da scheme; hay user o pass; el puerto no es entero 1-65535 (tambien si parse_url
     * lo descarta: hay ":" tras el host y no hay port). El scheme debe estar en minusculas: https exige host
     * /^[a-z0-9.-]+$/; http exige host en LOOPBACK (exacto); otro scheme debe estar en ESQUEMAS_PRIVADOS.
     *
     * @param string $uri redirect_uri a validar
     * @return bool true si es admisible
     */
    public static function redirectUriAllowed(string $uri): bool {
        $len = strlen($uri);
        if ($len === 0 || $len > self::MAX_URI_CHARS) {
            return false;
        }
        if (preg_match('/[#\\\\%@\x00-\x20\x7F]/', $uri) === 1) {
            return false;
        }
        $p = parse_url($uri);
        if ($p === false || !isset($p['scheme']) || $p['scheme'] === '') {
            return false;
        }
        if (isset($p['user']) || isset($p['pass'])) {
            return false;
        }
        // Puerto: entero 1-65535; si parse_url lo descarto, hay ":" tras el host sin port
        if (isset($p['port'])) {
            if (!is_int($p['port']) || $p['port'] < 1 || $p['port'] > 65535) {
                return false;
            }
        } elseif (($pos = strpos($uri, '://')) !== false) {
            $rest = substr($uri, $pos + 3);
            $end  = strcspn($rest, '/?');
            $auth = substr($rest, 0, $end);
            if (str_starts_with($auth, '[')) {
                $close = strpos($auth, ']');
                $tail  = $close === false ? ':' : substr($auth, $close + 1);
            } else {
                $tail = strstr($auth, ':') ?: '';
            }
            if ($tail !== '') {
                return false;
            }
        }
        $scheme = $p['scheme'];
        if ($scheme !== strtolower($scheme)) {
            return false;
        }
        $host = $p['host'] ?? '';
        if ($scheme === 'https') {
            return $host !== '' && preg_match('/^[a-z0-9.-]+$/', $host) === 1;
        }
        if ($scheme === 'http') {
            return in_array($host, self::LOOPBACK, true);
        }
        return in_array($scheme, self::ESQUEMAS_PRIVADOS, true);
    }

    /**
     * Indica si la redirect_uri pedida corresponde a la registrada (D-9, B-2).
     *
     * Primero aplica redirectUriAllowed a la pedida (falso -> false). Identicas -> true. Si no: ambas http,
     * mismo host en LOOPBACK (identico al registrado: localhost no equivale a 127.0.0.1 ni a [::1]) y, sin
     * el puerto, host, path y query identicos -> true. Resto -> false.
     *
     * @param string $registrada URI registrada del cliente
     * @param string $pedida     URI recibida en /authorize o /token
     * @return bool true si coinciden segun D-9
     */
    public static function redirectUriMatches(string $registrada, string $pedida): bool {
        if (!self::redirectUriAllowed($pedida)) {
            return false;
        }
        if ($registrada === $pedida) {
            return true;
        }
        $r = parse_url($registrada);
        $q = parse_url($pedida);
        if ($r === false || $q === false) {
            return false;
        }
        if (($r['scheme'] ?? '') !== 'http' || ($q['scheme'] ?? '') !== 'http') {
            return false;
        }
        $host = $r['host'] ?? '';
        if (!in_array($host, self::LOOPBACK, true) || ($q['host'] ?? '') !== $host) {
            return false;
        }
        return ($r['path'] ?? '') === ($q['path'] ?? '') && ($r['query'] ?? null) === ($q['query'] ?? null);
    }

    /**
     * Resuelve la URI efectiva desde las registradas (B-2, D-9).
     *
     * Toma la primera registrada que cumple redirectUriMatches y devuelve la URI construida desde la
     * REGISTRADA: ella misma si son identicas o, solo para http loopback, http://<host registrado>:<puerto
     * validado de la pedida><path y query registrados>. Nunca devuelve ni guarda la cadena pedida.
     *
     * @param string[] $registradas URIs registradas del cliente
     * @param string   $pedida      URI recibida
     * @return string|null URI efectiva o null si ninguna coincide
     */
    public static function redirectUriResolve(array $registradas, string $pedida): ?string {
        foreach ($registradas as $r) {
            if (!is_string($r) || !self::redirectUriMatches($r, $pedida)) {
                continue;
            }
            if ($r === $pedida) {
                return $r;
            }
            $rp = parse_url($r);
            $qp = parse_url($pedida);
            $port = isset($qp['port']) ? ':' . (int) $qp['port'] : '';
            $query = isset($rp['query']) ? '?' . $rp['query'] : '';
            return 'http://' . $rp['host'] . $port . ($rp['path'] ?? '') . $query;
        }
        return null;
    }
}
