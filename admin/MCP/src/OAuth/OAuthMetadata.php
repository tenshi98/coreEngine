<?php
defined('MCP_ROOT') || exit;
/**
 * Metadatos del servidor de autorizacion (RFC 8414), del recurso protegido (RFC 9728) y comparacion
 * de la audiencia (RFC 8707).
 *
 * Todas las URL salen de McpConfig (MCP_PUBLIC_URL), nunca de la cabecera Host (S-16).
 *
 * @see projects/mcp-oauth/specs/technical.md §6
 */
class OAuthMetadata
{
    /**
     * Metadatos del recurso protegido (RFC 9728).
     *
     * @return array<string,mixed> resource (URI canonica del MCP con barra final), authorization_servers, scopes_supported,
     *                             bearer_methods_supported y resource_name
     */
    public static function protectedResource(): array
    {
        return [
            'resource'                 => McpConfig::resourceUrl(),
            'authorization_servers'    => [McpConfig::issuerUrl()],
            'scopes_supported'         => ['mcp'],
            'bearer_methods_supported' => ['header'],
            'resource_name'            => 'coreEngine MCP',
        ];
    }

    /**
     * Metadatos del servidor de autorizacion (RFC 8414): solo S256 y solo los grants authorization_code y refresh_token.
     *
     * @return array<string,mixed>
     */
    public static function authorizationServer(): array
    {
        $i = McpConfig::issuerUrl();
        return [
            'issuer'                                          => $i,
            'authorization_endpoint'                          => $i . '/authorize',
            'token_endpoint'                                  => $i . '/token',
            'registration_endpoint'                           => $i . '/register',
            'revocation_endpoint'                             => $i . '/revoke',
            'response_types_supported'                        => ['code'],
            'response_modes_supported'                        => ['query'],
            'grant_types_supported'                           => ['authorization_code', 'refresh_token'],
            'code_challenge_methods_supported'                => ['S256'],
            'token_endpoint_auth_methods_supported'           => ['none', 'client_secret_post', 'client_secret_basic'],
            'revocation_endpoint_auth_methods_supported'      => ['none', 'client_secret_post', 'client_secret_basic'],
            'scopes_supported'                                => ['mcp'],
            'authorization_response_iss_parameter_supported'  => true,
        ];
    }

    /**
     * Indica si un valor de `resource` es el MCP canonico (D-11).
     *
     * parse_url; scheme y host en minusculas y una "/" final de la ruta menos, en ambos lados; se compara con
     * resourceUrl() normalizada igual. Un valor con query, fragment o credenciales no coincide.
     *
     * @param string $valor Valor del parametro resource
     * @return bool true si es la URI del MCP
     */
    public static function isMcpResource(string $valor): bool
    {
        $esperado = self::normalizar((string) McpConfig::resourceUrl());
        $recibido = self::normalizar($valor);
        return $esperado !== null && $recibido !== null && $recibido === $esperado;
    }

    /**
     * Forma normalizada scheme://host[:puerto]ruta para comparar URI de recurso.
     *
     * @param string $uri URI a normalizar
     * @return string|null null si no es analizable o lleva query, fragment o credenciales
     */
    private static function normalizar(string $uri): ?string
    {
        $p = parse_url($uri);
        if ($p === false || !isset($p['scheme'], $p['host'])) {
            return null;
        }
        if (isset($p['query']) || isset($p['fragment']) || isset($p['user']) || isset($p['pass'])) {
            return null;
        }
        $path = $p['path'] ?? '';
        if (str_ends_with($path, '/')) {
            $path = substr($path, 0, -1);
        }
        return strtolower($p['scheme']) . '://' . strtolower($p['host']) . (isset($p['port']) ? ':' . $p['port'] : '') . $path;
    }
}
