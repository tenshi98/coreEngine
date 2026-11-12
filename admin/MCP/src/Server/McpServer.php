<?php
/**
 * Despacho de los metodos MCP (JSON-RPC 2.0).
 *
 * @see projects/mcp-servidor/specs/technical.md §5
 */
class McpServer
{
    /** @var ToolRegistry Registro de tools */
    private ToolRegistry $tools;

    /** @var ResourceRegistry Registro de resources */
    private ResourceRegistry $resources;

    /**
     * @param ToolRegistry     $tools     Registro de tools
     * @param ResourceRegistry $resources Registro de resources
     */
    public function __construct(ToolRegistry $tools, ResourceRegistry $resources)
    {
        $this->tools     = $tools;
        $this->resources = $resources;
    }

    /**
     * Despacha un mensaje ya validado con JsonRpc::validate.
     *
     * @param array $msg Mensaje JSON-RPC
     * @return array|null Respuesta, o null si es una notificacion
     */
    public function handle(array $msg): ?array
    {
        if (JsonRpc::isNotification($msg)) {
            return null;
        }
        $id     = $msg['id'];
        $params = $msg['params'] ?? [];
        try {
            $result = match ($msg['method']) {
                'initialize'                => $this->initialize($params),
                'ping'                      => $this->ping(),
                'tools/list'                => $this->toolsList(),
                'tools/call'                => $this->toolsCall($params),
                'resources/list'            => $this->resourcesList(),
                'resources/templates/list'  => $this->resourceTemplatesList(),
                'resources/read'            => $this->resourcesRead($params),
                default                     => throw new McpException(-32601, 'Method not found'),
            };
            return JsonRpc::result($id, $result);
        } catch (McpException $e) {
            return JsonRpc::error($id, $e->getCode(), $e->getMessage(), $e->getData());
        } catch (Throwable $e) {
            // El detalle va solo a error_log, nunca al cliente (H-2)
            error_log('MCP error: ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            return JsonRpc::error($id, -32603, 'Internal error');
        }
    }

    /**
     * Negocia la version de protocolo y declara las capacidades.
     *
     * @param array $params Parametros de initialize
     * @return array
     */
    private function initialize(array $params): array
    {
        $pedida  = $params['protocolVersion'] ?? null;
        $version = (is_string($pedida) && in_array($pedida, McpConfig::PROTOCOL_VERSIONS, true))
            ? $pedida
            : McpConfig::PROTOCOL_VERSION;
        return [
            'protocolVersion' => $version,
            'capabilities'    => [
                'tools'     => ['listChanged' => false],
                'resources' => ['subscribe' => false, 'listChanged' => false],
            ],
            'serverInfo'      => McpConfig::SERVER_INFO,
            'instructions'    => McpConfig::INSTRUCTIONS,
        ];
    }

    /**
     * @return object Objeto vacio (serializa como {})
     */
    private function ping(): object
    {
        return (object)[];
    }

    /**
     * @return array tools/list: una sola pagina (cursor ignorado)
     */
    private function toolsList(): array
    {
        return ['tools' => $this->tools->listTools()];
    }

    /**
     * @param array $params Parametros de tools/call
     * @return array Resultado de la tool
     * @throws McpException -32602 si name no es string o arguments no es objeto
     */
    private function toolsCall(array $params): array
    {
        $name = $params['name'] ?? null;
        $args = $params['arguments'] ?? [];
        // Una lista no vacia no es un objeto JSON (PHP 8.0: sin array_is_list)
        if (!is_string($name) || $name === '' || !is_array($args) || ($args !== [] && array_keys($args) === range(0, count($args) - 1))) {
            throw new McpException(-32602, 'Invalid params');
        }
        return $this->tools->call($name, $args);
    }

    /**
     * @return array resources/list: una sola pagina (cursor ignorado)
     */
    private function resourcesList(): array
    {
        return ['resources' => $this->resources->listResources()];
    }

    /**
     * @return array resources/templates/list
     */
    private function resourceTemplatesList(): array
    {
        return ['resourceTemplates' => $this->resources->listTemplates()];
    }

    /**
     * @param array $params Parametros de resources/read
     * @return array Contenido del resource
     * @throws McpException -32602 si uri no es string
     */
    private function resourcesRead(array $params): array
    {
        $uri = $params['uri'] ?? null;
        if (!is_string($uri)) {
            throw new McpException(-32602, 'Invalid params');
        }
        return $this->resources->read($uri);
    }
}
