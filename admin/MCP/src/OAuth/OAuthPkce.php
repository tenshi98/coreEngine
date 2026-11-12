<?php
defined('MCP_ROOT') || exit;
/**
 * PKCE con el metodo S256 (RFC 7636), obligatorio para todos los clientes.
 *
 * @see projects/mcp-oauth/specs/technical.md §7.2
 */
class OAuthPkce
{
    /**
     * Valida el formato de un code_challenge S256 (43 caracteres base64url).
     *
     * @param string $c code_challenge recibido
     * @return bool true si cumple ^[A-Za-z0-9_-]{43}$ (modificador D: sin salto de linea final)
     */
    public static function isValidChallenge(string $c): bool
    {
        return preg_match('/^[A-Za-z0-9_-]{43}$/D', $c) === 1;
    }

    /**
     * Valida el formato de un code_verifier (RFC 7636 §4.1).
     *
     * @param string $v code_verifier recibido
     * @return bool true si cumple ^[A-Za-z0-9\-._~]{43,128}$
     */
    public static function isValidVerifier(string $v): bool
    {
        return preg_match('/^[A-Za-z0-9\-._~]{43,128}$/D', $v) === 1;
    }

    /**
     * Comprueba que el verifier corresponde al challenge: BASE64URL(SHA256(verifier)) sin relleno.
     *
     * @param string $verifier  code_verifier del canje
     * @param string $challenge code_challenge guardado con el codigo
     * @return bool true si coinciden (comparacion en tiempo constante)
     */
    public static function verify(string $verifier, string $challenge): bool
    {
        return hash_equals($challenge, rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='));
    }
}
