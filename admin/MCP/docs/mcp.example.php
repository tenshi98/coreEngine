<?php
/**
 * Manifiesto MCP de ejemplo (ADR-0011): copiar a admin/app/modules/<modulo>/mcp.php y adaptar.
 *
 * Este ejemplo usa el modulo `bodegas` (tablas y controladores reales de su installer y de controller/).
 * El archivo es opcional: sin el, el modulo se expone en modo automatico (todas las tablas de listTables()
 * salvo las que ocultan las listas de admin/MCP/config/McpConfig.php).
 *
 * Reglas (formato cerrado, ModuleManifest::validate):
 * - Solo devuelve un array con `return`; sin efectos secundarios ni salida (un echo excluye el modulo).
 * - Cualquier clave desconocida (incluida la antigua `permiso`, retirada por ADR-0019: el acceso es por
 *   modulo), tipo distinto o `version` != 1 invalida el manifiesto y EXCLUYE el modulo entero (falla cerrado).
 * - Solo puede RESTRINGIR o PRECISAR: no expone lo que ocultan la lista negra, HIDDEN_TABLES,
 *   HIDDEN_COLUMNS ni las columnas de cuenta/PII, ni amplia el alcance de un token.
 * - Las tablas citadas que no esten en listTables() del installer se ignoran.
 *
 * @see docs/architecture/decisions/0011-manifiesto-mcp-de-modulo.md
 * @see admin/MCP/src/Discovery/ModuleManifest.php
 */
return [
    // Obligatorio: unica version del formato
    'version'     => 1,

    // Opcional (por defecto true): false = el modulo entero no se expone
    'expuesto'    => true,

    // Opcional (por defecto, el nombre de la carpeta del modulo)
    'nombre'      => 'Bodegas',

    // Opcional (por defecto vacia): texto para la IA, aparece en list_modules y describe_module
    'descripcion' => 'Bodegas, sus movimientos de productos y el stock por posicion.',

    // Opcional: ajustes por tabla; la clave es el nombre exacto de la tabla del installer
    'tablas'      => [
        'bodegas_listado' => [
            // Opcional (por defecto true): false = la tabla no se expone
            'expuesta'             => true,

            // Opcional: descripcion de la tabla para la IA
            'descripcion'          => 'Bodegas registradas.',

            // Opcional: columnas que no deben salir (se suman a las que ya oculta McpConfig)
            'columnas_ocultas'     => ['DistribucionFisica'],

            // Opcional: descripcion por columna visible
            'columnas_descripcion' => [
                'Codigo' => 'Codigo interno de la bodega',
                'Nombre' => 'Nombre de la bodega',
            ],
        ],
        'bodegas_movimientos' => [
            // Accesible para cualquier token que tenga asignado el modulo
            'descripcion' => 'Movimientos de entrada y salida de productos.',
        ],
        'bodegas_zonas_tipos' => [
            // Tabla de apoyo que no interesa exponer
            'expuesta' => false,
        ],
    ],
];
