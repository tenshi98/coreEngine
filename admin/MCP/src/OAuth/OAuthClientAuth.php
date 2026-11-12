<?php
defined('MCP_ROOT') || exit;
/**
 * Autenticacion de clientes OAuth en /token y /revoke (client_secret_basic, client_secret_post y none).
 *
 * @see projects/mcp-oauth/specs/technical.md §8.6
 */
class OAuthClientAuth
{
    /**
     * Lee la cabecera Authorization (mismas fuentes que TokenAuthenticator::extractBearer).
     *
     * @return string|null Valor crudo de la cabecera, o null si no hay
     */
    private static function authorizationHeader(): ?string
    {
        $valor = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $valor = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $valor = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            foreach (apache_request_headers() as $nombre => $v) {
                if (strcasecmp((string) $nombre, 'Authorization') === 0) {
                    $valor = $v;
                    break;
                }
            }
        }
        return is_string($valor) ? trim($valor) : null;
    }

    /**
     * Autentica al cliente de una peticion al endpoint de tokens o de revocacion.
     *
     * Pasos: Basic (RFC 6749 §2.3.1) o cuerpo; coherencia del metodo con el registrado; secreto con SHA-256.
     * Cualquier incoherencia o fallo devuelve client null sin distinguir la causa.
     *
     * @param array<string,string> $p    Parametros del cuerpo (client_id, client_secret)
     * @param OAuthClientRepository $repo Repositorio de clientes
     * @return array{client: ?array, basic: bool} client: fila del cliente autenticado y activo; basic: true si
     *         la peticion trae Authorization Basic
     * @throws RuntimeException Si falla la base de datos
     */
    public static function authenticate(array $p, OAuthClientRepository $repo): array
    {
        $cabecera = self::authorizationHeader();
        $basic    = $cabecera !== null && preg_match('/^Basic\s+([A-Za-z0-9+\/=]+)$/i', $cabecera, $m) === 1;

        if ($basic) {
            $decodificado = base64_decode($m[1], true);
            if ($decodificado === false || !str_contains($decodificado, ':')) {
                return ['client' => null, 'basic' => true];
            }
            [$id, $secret] = explode(':', $decodificado, 2);
            $id     = urldecode($id);
            $secret = urldecode($secret);
            if (isset($p['client_secret']) || (isset($p['client_id']) && $p['client_id'] !== $id)) {
                return ['client' => null, 'basic' => true];
            }
        } else {
            $id     = (string) ($p['client_id'] ?? '');
            $secret = $p['client_secret'] ?? null;
        }

        $c = $repo->findByClientId($id);
        if ($c === null) {
            return ['client' => null, 'basic' => $basic];
        }

        $metodo = (string) $c['AuthMethod'];
        if (($basic && $metodo !== 'client_secret_basic')
            || (!$basic && $secret !== null && $metodo !== 'client_secret_post')
            || ($metodo === 'none' && $secret !== null)) {
            return ['client' => null, 'basic' => $basic];
        }

        if (!$repo->authenticate($c, $secret) || !$repo->isUsable($c)) {
            return ['client' => null, 'basic' => $basic];
        }
        return ['client' => $c, 'basic' => $basic];
    }
}
