<?php
/**
 * Excepcion de protocolo MCP / JSON-RPC.
 *
 * @see projects/mcp-servidor/specs/technical.md §5
 */
class McpException extends Exception
{
    /** @var mixed Datos opcionales del error JSON-RPC */
    private mixed $data;

    /**
     * @param int    $code    Codigo JSON-RPC (p. ej. -32602)
     * @param string $message Mensaje fijo
     * @param mixed  $data    Datos opcionales
     */
    public function __construct(int $code, string $message, mixed $data = null)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    /**
     * @return mixed Datos opcionales del error
     */
    public function getData(): mixed
    {
        return $this->data;
    }
}
