<?php
defined('MCP_ROOT') || exit;
/**
 * Utilidades criptograficas del servidor de autorizacion OAuth.
 *
 * Aleatoriedad con random_bytes y huella SHA-256 de codigos, refresh tokens y secretos de cliente.
 *
 * @see projects/mcp-oauth/specs/technical.md §4.2
 */
class OAuthCrypto
{
    /**
     * Cadena hexadecimal aleatoria criptograficamente segura.
     *
     * @param int $bytes Numero de bytes aleatorios (la cadena tiene el doble de caracteres)
     * @return string Hexadecimal en minusculas de 2 * $bytes caracteres
     */
    public static function randomHex(int $bytes): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Huella SHA-256 de un valor.
     *
     * @param string $valor Valor a resumir
     * @return string 64 caracteres hexadecimales en minusculas
     */
    public static function hash(string $valor): string
    {
        return hash('sha256', $valor);
    }
}
