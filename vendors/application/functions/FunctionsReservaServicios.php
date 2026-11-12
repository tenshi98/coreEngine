<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Reglas compartidas del módulo reserva_servicios (ADR-0005).
 *
 * Concentra el cálculo de precio, la cobertura, la disponibilidad, la retención
 * temporal y el alta transaccional que usan tanto el backoffice como el portal
 * público. No lee superglobales ni configuración de una aplicación concreta.
 *
 * @package vendors/application/functions
 */
class FunctionsReservaServicios {

    /*******************************************************************************************************************/
    /*                                                                                                                 */
    /*                                                 Constantes                                                      */
    /*                                                                                                                 */
    /*******************************************************************************************************************/
    /** Minutos que un horario queda retenido mientras el cliente completa la reserva. */
    public const HOLD_MINUTOS = 15;
    /** Tasa de IVA aplicada en Chile. */
    public const IVA = 0.19;
    /** Usuario de sistema usado para las cotizaciones creadas desde el portal. */
    public const USUARIO_SISTEMA_WEB = 1;
    /** Zona horaria de la operación. */
    public const ZONA_HORARIA = 'America/Santiago';
    /** reserva_servicios_promociones_tipos.idTipo: descuento porcentual. */
    public const PROMOCION_PORCENTAJE = 1;
    /** reserva_servicios_promociones_tipos.idTipo: descuento de monto fijo. */
    public const PROMOCION_MONTO = 2;
    /** reserva_servicios_bloqueos_tipos.idTipo: bloqueo manual de agenda. */
    public const BLOQUEO_MANUAL = 1;
    /** reserva_servicios_bloqueos_tipos.idTipo: retención temporal del portal. */
    public const BLOQUEO_RETENCION = 2;
    /** reserva_servicios_origen.idOrigen: reserva creada desde el portal web. */
    public const ORIGEN_WEB = 2;
    /** reserva_servicios_asignacion.idRequiereAsignacion: requiere aprobación. */
    public const ASIGNACION_APROBACION = 1;
    /** reserva_servicios_asignacion.idRequiereAsignacion: confirmación automática. */
    public const ASIGNACION_OK = 2;
    /**
     * Formato del Valor de una promoción por idTipo (prefijo/sufijo). Un tipo
     * nuevo = una entrada del mapa, sin tocar formatearValorPromocion().
     */
    private const FORMATO_VALOR_PROMOCION = [
        self::PROMOCION_PORCENTAJE => ['prefijo' => '',   'sufijo' => ' %'],
        self::PROMOCION_MONTO      => ['prefijo' => '$ ', 'sufijo' => ''],
    ];
    /**
     * Matriz de transiciones de estado de la reserva (functional.md §3).
     * Clave: estado actual. Valor: estados de destino permitidos.
     */
    public const TRANSICIONES = [
        1  => [2, 14],
        2  => [3, 14, 15],
        3  => [4, 14],
        4  => [5, 14],
        5  => [6, 14],
        6  => [7, 14],
        7  => [8, 9, 14],
        8  => [9, 14],
        9  => [10, 15],
        10 => [11, 15],
        11 => [12, 15],
        12 => [13],
        13 => [10, 11, 15],
        14 => [],
        15 => [6, 7, 10, 11, 14],
    ];

    /*******************************************************************************************************************/
    /*                                                                                                                 */
    /*                                                 Instancias                                                      */
    /*                                                                                                                 */
    /*******************************************************************************************************************/
    private $DBConn;
    private $queryBuilder;

    /******************************************************************************/
    /**
     * Instancia el helper con la conexión y el constructor de consultas.
     *
     * Si no se reciben, se crean con la configuración de la base de datos del
     * proyecto (MySQL_1). El helper no guarda estado de negocio entre llamadas.
     *
     * @param mixed $DBConn       Conexión PDO de la base de datos.
     * @param mixed $queryBuilder Instancia de QueryBuilder.
     *
     * @return void
     *
     * @example
     * ```php
     * $helper = new FunctionsReservaServicios($DBConn, new QueryBuilder());
     * ```
     */
    public function __construct($DBConn = null, $queryBuilder = null) {
        $this->DBConn       = $DBConn ?? Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $this->queryBuilder = $queryBuilder ?? new QueryBuilder();
    }

    /*******************************************************************************************************************/
    /*                                                                                                                 */
    /*                                              Metodos Publicos                                                   */
    /*                                                                                                                 */
    /*******************************************************************************************************************/
    /******************************************************************************/
    /**
     * Obtiene la tarifa vigente de un servicio y variante para una fecha.
     *
     * @param int    $idServicio Identificador del servicio.
     * @param string $variante   Variante comercial (por defecto "base").
     * @param string $fecha      Fecha del servicio en formato Y-m-d.
     *
     * @return array{status:bool,data:mixed,message:string,error:string|null}
     *
     * @example
     * ```php
     * $r = $helper->obtenerTarifaVigente(12, 'base', '2026-11-05');
     * ```
     */
    public function obtenerTarifaVigente(int $idServicio, string $variante, string $fecha): array {

        /************************************/
        // Validaciones
        if ($idServicio <= 0 || !$this->esFechaValida($fecha)) {
            return $this->fail('Datos de tarifa inválidos', 'DATOS_INVALIDOS');
        }
        $variante = trim($variante) !== '' ? trim($variante) : 'base';

        /************************************/
        // Consulta
        $query = [
            'data'   => 't.idTarifa, t.idServicio, t.Variante,
                         COALESCE(NULLIF(t.Etiqueta, \'\'), s.Nombre) AS NombreServicio,
                         t.Valor, t.DuracionMin, t.idRequiereInspeccion, t.VigenciaDesde, t.VigenciaHasta',
            'table'  => 'reserva_servicios_tarifas t',
            'join'   => 'INNER JOIN servicios_listado s ON s.idServicio = t.idServicio',
            'where'  => 't.idServicio = ? AND t.Variante = ? AND t.Activo = 1
                         AND t.VigenciaDesde <= ? AND (t.VigenciaHasta IS NULL OR t.VigenciaHasta >= ?)',
            'params' => [$idServicio, $variante, $fecha, $fecha],
            'group'  => '',
            'having' => '',
            'order'  => 't.VigenciaDesde DESC, t.idTarifa DESC',
        ];
        $result = $this->queryBuilder->queryRow($query, $this->DBConn);
        if ($result['status'] !== true) {
            return $this->fail('No hay tarifa vigente para el servicio seleccionado', 'TARIFA_NO_VIGENTE');
        }

        /************************************/
        // Retorno
        return $this->ok($result['data'], 'Tarifa vigente');
    }

    /******************************************************************************/
    /**
     * Obtiene la mejor promoción vigente aplicable a una cantidad de servicios.
     *
     * No son acumulables: se elige la de mayor beneficio sobre el subtotal
     * informado. Si no se informa subtotal, se usa el valor nominal de la
     * promoción como criterio de comparación.
     *
     * @param int    $cantidadLineas Cantidad de líneas de la reserva.
     * @param string $fecha          Fecha del servicio en formato Y-m-d.
     * @param float  $subtotal       Subtotal sobre el que se calcula el beneficio.
     *
     * @return array{status:bool,data:mixed,message:string,error:string|null}
     */
    public function obtenerPromocionAplicable(int $cantidadLineas, string $fecha, float $subtotal = 0.0): array {

        /************************************/
        // Validaciones
        if ($cantidadLineas < 1 || !$this->esFechaValida($fecha)) {
            return $this->fail('Datos de promoción inválidos', 'DATOS_INVALIDOS');
        }

        /************************************/
        // Consulta
        $query = [
            'data'   => 'idPromocion, Nombre, idTipo, Valor, MinimoServicios, VigenciaDesde, VigenciaHasta',
            'table'  => 'reserva_servicios_promociones',
            'join'   => '',
            'where'  => 'Activo = 1 AND MinimoServicios <= ?
                         AND VigenciaDesde <= ? AND (VigenciaHasta IS NULL OR VigenciaHasta >= ?)',
            'params' => [$cantidadLineas, $fecha, $fecha],
            'group'  => '',
            'having' => '',
            'order'  => 'VigenciaDesde DESC, idPromocion DESC',
            'limit'  => 50,
        ];
        $result = $this->queryBuilder->queryArray($query, $this->DBConn);
        if ($result['status'] !== true) {
            return $this->fail('No fue posible consultar las promociones', 'ERROR_PROMOCIONES');
        }
        if (!$result['data']) {
            return $this->ok(null, 'Sin promociones aplicables');
        }

        /************************************/
        // Se elige el mayor beneficio
        $mejor = null;
        $mayor = -1.0;
        foreach ($result['data'] as $promo) {
            $beneficio = $this->calcularBeneficioPromocion($promo, $subtotal);
            if ($beneficio > $mayor) {
                $mayor = $beneficio;
                $mejor = $promo;
            }
        }

        /************************************/
        // Retorno
        if ($mejor === null) {
            return $this->ok(null, 'Sin promociones aplicables');
        }
        $mejor['Beneficio'] = round($mayor, 2);
        return $this->ok($mejor, 'Promoción aplicable');
    }

    /******************************************************************************/
    /**
     * Valida que una comuna esté cubierta y devuelve su recargo.
     *
     * @param int $idComuna Identificador de la comuna.
     *
     * @return array{status:bool,data:mixed,message:string,error:string|null}
     */
    public function validarCobertura(int $idComuna): array {

        /************************************/
        // Validaciones
        if ($idComuna <= 0) {
            return $this->fail('Comuna inválida', 'COMUNA_INVALIDA');
        }

        /************************************/
        // Consulta
        $query = [
            'data'   => 'co.idComuna, co.Recargo, c.Nombre AS Comuna',
            'table'  => 'reserva_servicios_cobertura co',
            'join'   => 'INNER JOIN core_ubicacion_comunas c ON c.idComuna = co.idComuna',
            'where'  => 'co.idComuna = ? AND co.Activo = 1',
            'params' => [$idComuna],
            'group'  => '',
            'having' => '',
            'order'  => '',
        ];
        $result = $this->queryBuilder->queryRow($query, $this->DBConn);
        if ($result['status'] !== true) {
            return $this->fail('Comuna fuera de cobertura', 'COMUNA_SIN_COBERTURA');
        }

        /************************************/
        // Retorno
        return $this->ok($result['data'], 'Comuna cubierta');
    }

    /******************************************************************************/
    /**
     * Calcula el desglose completo de una reserva.
     *
     * Recibe líneas con idServicio, Variante y Number. Los precios se obtienen
     * siempre de la base de datos; cualquier importe recibido se ignora.
     *
     * @param array  $lineas   Líneas de la reserva.
     * @param int    $idComuna Comuna del servicio.
     * @param string $fecha    Fecha del servicio en formato Y-m-d.
     *
     * @return array{status:bool,data:mixed,message:string,error:string|null}
     *
     * @example
     * ```php
     * $r = $helper->calcularTotales([
     *     ['idServicio' => 3, 'Variante' => 'base', 'Number' => 2],
     * ], 12, '2026-11-05');
     * ```
     */
    public function calcularTotales(array $lineas, int $idComuna, string $fecha): array {

        /************************************/
        // Validaciones
        if (!$lineas || !$this->esFechaValida($fecha)) {
            return $this->fail('La reserva no tiene líneas válidas', 'SIN_LINEAS');
        }
        $cobertura = $this->validarCobertura($idComuna);
        if ($cobertura['status'] !== true) {
            return $cobertura;
        }

        /************************************/
        // Líneas con snapshot
        $detalle       = [];
        $subtotal      = 0.0;
        $duracionTotal = 0;
        foreach (array_values($lineas) as $indice => $linea) {
            $idServicio = (int) ($linea['idServicio'] ?? 0);
            $variante   = (string) ($linea['Variante'] ?? 'base');
            $number     = (float) ($linea['Number'] ?? 0);
            if ($idServicio <= 0 || $number <= 0) {
                return $this->fail('Línea '.($indice + 1).' inválida', 'LINEA_INVALIDA');
            }

            $tarifa = $this->obtenerTarifaVigente($idServicio, $variante, $fecha);
            if ($tarifa['status'] !== true) {
                return $this->fail('Servicio sin tarifa vigente (línea '.($indice + 1).')', 'TARIFA_NO_VIGENTE');
            }
            $t          = $tarifa['data'];
            $valorUnit  = round((float) $t['Valor'], 2);
            $valorTotal = round($valorUnit * $number, 2);
            $duracion   = (int) ceil(((int) $t['DuracionMin']) * $number);

            $detalle[] = [
                'idServicio'     => $idServicio,
                'idTarifa'       => (int) $t['idTarifa'],
                'NombreServicio' => (string) $t['NombreServicio'],
                'Variante'       => (string) $t['Variante'],
                'DuracionMin'    => $duracion,
                'Number'         => $number,
                'ValorUnitario'  => $valorUnit,
                'ValorTotal'     => $valorTotal,
                'idRequiereInspeccion' => (int) $t['idRequiereInspeccion'],
            ];
            $subtotal      += $valorTotal;
            $duracionTotal += $duracion;
        }
        $subtotal = round($subtotal, 2);

        /************************************/
        // Promoción y totales
        $promocion = $this->obtenerPromocionAplicable(count($detalle), $fecha, $subtotal);
        if ($promocion['status'] !== true) {
            return $promocion;
        }
        $idPromocion = null;
        $descuento   = 0.0;
        $nombrePromo = '';
        if (!empty($promocion['data'])) {
            $idPromocion = (int) $promocion['data']['idPromocion'];
            $descuento   = round((float) $promocion['data']['Beneficio'], 2);
            $nombrePromo = (string) $promocion['data']['Nombre'];
        }
        $recargo = round((float) $cobertura['data']['Recargo'], 2);
        $neto    = round($subtotal + $recargo - $descuento, 2);
        if ($neto < 0) {
            return $this->fail('El descuento no puede superar el valor del servicio', 'TOTAL_INVALIDO');
        }
        $iva   = round($neto * self::IVA, 2);
        $total = round($neto + $iva, 2);

        /************************************/
        // Retorno
        return $this->ok([
            'lineas'         => $detalle,
            'subtotal'       => $subtotal,
            'idPromocion'    => $idPromocion,
            'promocion'      => $nombrePromo,
            'descuento'      => $descuento,
            'recargo'        => $recargo,
            'neto'           => $neto,
            'iva'            => $iva,
            'total'          => $total,
            'duracionMin'    => $duracionTotal,
            'comuna'         => (string) $cobertura['data']['Comuna'],
            'requiereInspeccion' => $this->requiereInspeccion($detalle),
        ], 'Totales calculados');
    }

    /******************************************************************************/
    /**
     * Verifica si un trabajador tiene libre un rango horario.
     *
     * No bloquea filas: el llamador debe tomar el lock con
     * ControllerBase::lockRowsForUpdate (o el lock equivalente del helper) antes
     * de escribir. Sin trabajador, el rango no bloquea a nadie.
     *
     * @param int|null $idTrabajador Entidad tipo 3, o null si aún no se asigna.
     * @param string   $fecha        Fecha en formato Y-m-d.
     * @param string   $horaInicio   Hora de inicio en formato H:i.
     * @param int      $duracionMin  Duración en minutos.
     * @param int|null $excluirId    Reserva que se excluye (edición).
     *
     * @return array{status:bool,data:mixed,message:string,error:string|null}
     */
    public function verificarDisponibilidad(?int $idTrabajador, string $fecha, string $horaInicio, int $duracionMin, ?int $excluirId = null): array {

        /************************************/
        // Validaciones
        if (!$this->esFechaValida($fecha) || $duracionMin < 30) {
            return $this->fail('Datos de disponibilidad inválidos', 'DATOS_INVALIDOS');
        }
        $horaInicio = $this->normalizarHora($horaInicio);
        if ($horaInicio === '') {
            return $this->fail('Hora de inicio inválida', 'HORA_INVALIDA');
        }
        $horaTermino = $this->sumarMinutos($horaInicio, $duracionMin);
        if ($horaTermino === '' || $horaTermino <= $horaInicio) {
            return $this->fail('El rango horario no es válido', 'HORA_INVALIDA');
        }
        if ($idTrabajador === null || $idTrabajador <= 0) {
            return $this->ok([
                'Libre'        => true,
                'Hora_Inicio'  => $horaInicio,
                'Hora_Termino' => $horaTermino,
                'Motivo'       => '',
            ], 'Sin trabajador asignado');
        }

        /************************************/
        // Reservas que ocupan agenda
        $conflicto = $this->buscarConflictoReserva($idTrabajador, $fecha, $horaInicio, $horaTermino, $excluirId);
        if ($conflicto['status'] !== true) {
            return $conflicto;
        }
        if ($conflicto['data'] !== null) {
            return $this->ok([
                'Libre'        => false,
                'Hora_Inicio'  => $horaInicio,
                'Hora_Termino' => $horaTermino,
                'Motivo'       => 'El trabajador ya tiene una reserva en ese horario',
                'Conflicto'    => $conflicto['data'],
            ], 'Horario ocupado');
        }

        /************************************/
        // Bloqueos y retenciones
        $bloqueo = $this->buscarConflictoBloqueo($idTrabajador, $fecha, $horaInicio, $horaTermino, $excluirId);
        if ($bloqueo['status'] !== true) {
            return $bloqueo;
        }
        if ($bloqueo['data'] !== null) {
            return $this->ok([
                'Libre'        => false,
                'Hora_Inicio'  => $horaInicio,
                'Hora_Termino' => $horaTermino,
                'Motivo'       => 'El horario está bloqueado o retenido',
                'Conflicto'    => $bloqueo['data'],
            ], 'Horario ocupado');
        }

        /************************************/
        // Retorno
        return $this->ok([
            'Libre'        => true,
            'Hora_Inicio'  => $horaInicio,
            'Hora_Termino' => $horaTermino,
            'Motivo'       => '',
        ], 'Horario disponible');
    }

    /******************************************************************************/
    /**
     * Crea una retención temporal del horario.
     *
     * Ejecuta su propia transacción y toma el lock de la entidad trabajador
     * (equivalente a ControllerBase::lockRowsForUpdate, ADR-0002) antes de
     * comprobar la disponibilidad y escribir.
     *
     * @param int      $idTrabajador Entidad tipo 3.
     * @param string   $fecha        Fecha en formato Y-m-d.
     * @param string   $horaInicio   Hora de inicio en formato H:i.
     * @param int      $duracionMin  Duración en minutos.
     * @param int|null $idReserva    Reserva asociada, si existe.
     *
     * @return array{status:bool,data:mixed,message:string,error:string|null}
     */
    public function crearRetencionTemporal(int $idTrabajador, string $fecha, string $horaInicio, int $duracionMin, ?int $idReserva = null): array {

        /************************************/
        // Validaciones
        if ($idTrabajador <= 0) {
            return $this->fail('Trabajador inválido', 'TRABAJADOR_INVALIDO');
        }
        // Las retenciones vencidas no deben bloquear la agenda
        $this->limpiarRetencionesVencidas();
        $disponible = $this->verificarDisponibilidad($idTrabajador, $fecha, $horaInicio, $duracionMin);
        if ($disponible['status'] !== true) {
            return $disponible;
        }
        if ($disponible['data']['Libre'] !== true) {
            return $this->fail('El horario ya está ocupado', 'HORARIO_OCUPADO');
        }

        /************************************/
        // Transacción
        try {
            $this->DBConn->beginTransaction();
            $lock = $this->lockTrabajador($idTrabajador);
            if ($lock['status'] !== true) {
                $this->DBConn->rollBack();
                return $this->fail('No fue posible reservar el horario', 'LOCK_FALLIDO');
            }
            // Se revalida con el lock tomado
            $disponible = $this->verificarDisponibilidad($idTrabajador, $fecha, $horaInicio, $duracionMin);
            if ($disponible['status'] !== true || $disponible['data']['Libre'] !== true) {
                $this->DBConn->rollBack();
                return $this->fail('El horario ya está ocupado', 'HORARIO_OCUPADO');
            }

            // Se elimina una retención previa de la misma reserva
            if ($idReserva !== null && $idReserva > 0) {
                $this->queryBuilder->queryExecute(
                    'DELETE FROM reserva_servicios_bloqueos WHERE idReservaServicio = ? AND idTipo = ?',
                    $this->DBConn,
                    false,
                    false,
                    [$idReserva, self::BLOQUEO_RETENCION]
                );
            }

            $horaTermino = $this->sumarMinutos($this->normalizarHora($horaInicio), $duracionMin);
            $expira      = (new DateTime('now', new DateTimeZone(self::ZONA_HORARIA)))
                ->modify('+'.self::HOLD_MINUTOS.' minutes')
                ->format('Y-m-d H:i:s');
            $insert = $this->queryBuilder->queryExecute(
                'INSERT INTO reserva_servicios_bloqueos
                    (idEntidadTrabajador, idReservaServicio, idTipo, Fecha, Hora_Inicio, Hora_Termino, Expira, idUsuario, Motivo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                $this->DBConn,
                false,
                false,
                [$idTrabajador, $idReserva, self::BLOQUEO_RETENCION, $fecha, $this->normalizarHora($horaInicio), $horaTermino, $expira, self::USUARIO_SISTEMA_WEB, 'Retención temporal del portal']
            );
            if ($insert['status'] !== true) {
                $this->DBConn->rollBack();
                return $this->fail('No fue posible retener el horario', 'RETENCION_FALLIDA');
            }
            $idBloqueo = (int) $this->DBConn->lastInsertId();
            $this->DBConn->commit();

            /************************************/
            // Retorno
            return $this->ok([
                'idBloqueo'    => $idBloqueo,
                'Expira'       => $expira,
                'Hora_Inicio'  => $this->normalizarHora($horaInicio),
                'Hora_Termino' => $horaTermino,
                'Minutos'      => self::HOLD_MINUTOS,
            ], 'Horario retenido');
        } catch (Exception $e) {
            if ($this->DBConn->inTransaction()) {
                $this->DBConn->rollBack();
            }
            return $this->fail('No fue posible retener el horario', $e->getMessage());
        }
    }

    /******************************************************************************/
    /**
     * Elimina las retenciones temporales vencidas.
     *
     * No requiere transacción propia; un fallo no debe bloquear la operación
     * principal.
     *
     * @return void
     */
    public function limpiarRetencionesVencidas(): void {
        try {
            $this->queryBuilder->queryExecute(
                'DELETE FROM reserva_servicios_bloqueos WHERE idTipo = ? AND Expira IS NOT NULL AND Expira <= NOW()',
                $this->DBConn,
                false,
                false,
                [self::BLOQUEO_RETENCION]
            );
        } catch (Exception $e) {
            // La limpieza es best-effort: no se propaga el error.
        }
    }

    /******************************************************************************/
    /**
     * Genera un código de seguimiento único de 12 caracteres.
     *
     * @return string Código en mayúsculas o cadena vacía si no fue posible.
     */
    public function generarCodigoSeguimiento(): string {

        /************************************/
        // Alfabeto sin caracteres ambiguos
        $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $largo    = strlen($alfabeto) - 1;

        /************************************/
        // Intentos con verificación de unicidad
        for ($intento = 0; $intento < 10; $intento++) {
            $codigo = '';
            for ($i = 0; $i < 12; $i++) {
                $codigo .= $alfabeto[random_int(0, $largo)];
            }
            $query = [
                'data'   => 'idReservaServicio',
                'table'  => 'reserva_servicios_listado',
                'join'   => '',
                'where'  => 'CodigoSeguimiento = ?',
                'params' => [$codigo],
                'group'  => '',
                'having' => '',
                'order'  => '',
            ];
            $existe = $this->queryBuilder->queryRow($query, $this->DBConn);
            if ($existe['status'] === true && empty($existe['data'])) {
                return $codigo;
            }
        }

        /************************************/
        // Retorno
        return '';
    }

    /******************************************************************************/
    /**
     * Crea una cotización con el formato del módulo cotizaciones.
     *
     * @param array $datos idEntidad, idUsuario, lineas, subtotal, descuento,
     *                     recargo, neto, iva, total y observaciones.
     *
     * @return array{status:bool,data:mixed,message:string,error:string|null}
     */
    public function crearCotizacion(array $datos): array {

        /************************************/
        // Validaciones
        $idEntidad = (int) ($datos['idEntidad'] ?? 0);
        $idUsuario = (int) ($datos['idUsuario'] ?? self::USUARIO_SISTEMA_WEB);
        $lineas    = $datos['lineas'] ?? [];
        if ($idEntidad <= 0 || !$lineas) {
            return $this->fail('Datos de cotización inválidos', 'DATOS_INVALIDOS');
        }

        /************************************/
        // Cabecera
        $ahora    = new DateTime('now', new DateTimeZone(self::ZONA_HORARIA));
        $fecha    = $ahora->format('Y-m-d');
        $semana   = (int) $ahora->format('W');
        $mes      = (int) $ahora->format('n');
        $ano      = (int) $ahora->format('Y');
        $hora     = $ahora->format('H:i:s');
        $subtotal = round((float) ($datos['subtotal'] ?? 0), 2);

        $cabecera = $this->queryBuilder->queryExecute(
            'INSERT INTO cotizacion_listado
                (idUsuario, idEntidad, fecha_auto, Creacion_fecha, Creacion_Semana, Creacion_mes,
                 Creacion_ano, Creacion_hora, Observaciones, ValorNeto, IVA, ValorTotal,
                 TotalItems, TotalProductos, TotalServicios, TotalGuias)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            $this->DBConn,
            false,
            false,
            [
                $idUsuario, $idEntidad, $fecha, $fecha, $semana, $mes, $ano, $hora,
                (string) ($datos['observaciones'] ?? 'Cotización generada desde reserva de servicios'),
                round((float) ($datos['neto'] ?? 0), 2),
                round((float) ($datos['iva'] ?? 0), 2),
                round((float) ($datos['total'] ?? 0), 2),
                0, 0, $subtotal, 0,
            ]
        );
        if ($cabecera['status'] !== true) {
            return $this->fail('No fue posible crear la cotización', 'COTIZACION_FALLIDA');
        }
        $idCotizacion = (int) $this->DBConn->lastInsertId();

        /************************************/
        // Detalle de servicios
        foreach ($lineas as $linea) {
            $detalle = $this->queryBuilder->queryExecute(
                'INSERT INTO cotizacion_listado_servicios (idCotizacion, idServicio, Number, ValorTotal)
                 VALUES (?, ?, ?, ?)',
                $this->DBConn,
                false,
                false,
                [
                    $idCotizacion,
                    (int) ($linea['idServicio'] ?? 0),
                    round((float) ($linea['Number'] ?? 0), 2),
                    round((float) ($linea['ValorTotal'] ?? 0), 2),
                ]
            );
            if ($detalle['status'] !== true) {
                return $this->fail('No fue posible guardar el detalle de la cotización', 'COTIZACION_DETALLE_FALLIDO');
            }
        }

        /************************************/
        // Retorno
        return $this->ok(['idCotizacion' => $idCotizacion], 'Cotización creada');
    }

    /******************************************************************************/
    /**
     * Crea una reserva web completa en una sola transacción.
     *
     * Incluye: lock y revalidación de disponibilidad, búsqueda o creación del
     * cliente (entidad tipo 2), reserva, líneas con snapshot, evento, liberación
     * de la retención y cotización.
     *
     * @param array $datos  Datos de la reserva y del cliente. Catálogos por id:
     *                      idRequiereAsignacion (1 aprobación, 2 OK; por defecto 1)
     *                      e idCanal (opcional).
     * @param array $lineas Líneas con idServicio, Variante y Number.
     *
     * @return array{status:bool,data:mixed,message:string,error:string|null}
     */
    public function crearReservaWeb(array $datos, array $lineas): array {

        /************************************/
        // Datos base
        $fecha          = (string) ($datos['Fecha'] ?? '');
        $horaInicio     = $this->normalizarHora((string) ($datos['Hora_Inicio'] ?? ''));
        $idComuna       = (int) ($datos['idComuna'] ?? 0);
        $idUsuario      = (int) ($datos['idUsuario'] ?? self::USUARIO_SISTEMA_WEB);
        $idAsignacion   = (int) ($datos['idRequiereAsignacion'] ?? self::ASIGNACION_APROBACION);
        $idCanal        = (int) ($datos['idCanal'] ?? 0);
        $idTrabajador   = isset($datos['idEntidadTrabajador']) ? (int) $datos['idEntidadTrabajador'] : 0;
        $idRetencion    = isset($datos['idBloqueo']) ? (int) $datos['idBloqueo'] : 0;

        /************************************/
        // Cálculo en servidor
        $calculo = $this->calcularTotales($lineas, $idComuna, $fecha);
        if ($calculo['status'] !== true) {
            return $calculo;
        }
        $c = $calculo['data'];
        if ($horaInicio === '' || (int) $c['duracionMin'] < 30) {
            return $this->fail('Horario o duración inválidos', 'HORARIO_INVALIDO');
        }
        $horaTermino = $this->sumarMinutos($horaInicio, (int) $c['duracionMin']);
        if ($horaTermino === '') {
            return $this->fail('Horario inválido', 'HORARIO_INVALIDO');
        }

        /************************************/
        // Una línea con inspección previa nunca se confirma sola
        if ((int) $c['requiereInspeccion'] === 1 || !in_array($idAsignacion, [self::ASIGNACION_APROBACION, self::ASIGNACION_OK], true)) {
            $idAsignacion = self::ASIGNACION_APROBACION;
        }

        /************************************/
        // Transacción
        try {
            $this->DBConn->beginTransaction();

            /************************************/
            // La retención propia se libera al inicio: si la validación la viera,
            // el propio bloqueo aparecería como conflicto. Al ir dentro de la
            // transacción, un rollback la restaura.
            $this->limpiarRetencionesVencidas();
            if ($idRetencion > 0) {
                $this->queryBuilder->queryExecute(
                    'DELETE FROM reserva_servicios_bloqueos WHERE idBloqueo = ? AND idTipo = ?',
                    $this->DBConn,
                    false,
                    false,
                    [$idRetencion, self::BLOQUEO_RETENCION]
                );
            }

            // Trabajador libre (si no viene asignado, se busca uno)
            $sinTrabajador = false;
            if ($idTrabajador <= 0) {
                $libre = $this->buscarTrabajadorLibre($fecha, $horaInicio, $horaTermino, $idComuna);
                if ($libre['status'] !== true) {
                    // Sin trabajador libre la reserva no se rechaza: queda
                    // solicitada y el backoffice la asigna al confirmar (estado 1).
                    $sinTrabajador = true;
                    $idTrabajador  = 0;
                } else {
                    $idTrabajador = (int) ($libre['data']['idEntidad'] ?? 0);
                }
            }

            // Lock y revalidación
            if ($idTrabajador > 0) {
                $lock = $this->lockTrabajador($idTrabajador);
                if ($lock['status'] !== true) {
                    $this->DBConn->rollBack();
                    return $this->fail('No fue posible reservar el horario', 'LOCK_FALLIDO');
                }
                $disponible = $this->verificarDisponibilidad($idTrabajador, $fecha, $horaInicio, (int) $c['duracionMin']);
                if ($disponible['status'] !== true || $disponible['data']['Libre'] !== true) {
                    $this->DBConn->rollBack();
                    return $this->fail('El horario ya está ocupado', 'HORARIO_OCUPADO');
                }
            }

            // Cliente
            $cliente = $this->resolverCliente($datos);
            if ($cliente['status'] !== true) {
                $this->DBConn->rollBack();
                return $cliente;
            }
            $idEntidad = (int) $cliente['data']['idEntidad'];

            // Código de seguimiento
            $codigo = $this->generarCodigoSeguimiento();
            if ($codigo === '') {
                $this->DBConn->rollBack();
                return $this->fail('No fue posible generar el código de seguimiento', 'CODIGO_FALLIDO');
            }

            // Reserva
            $ahora  = new DateTime('now', new DateTimeZone(self::ZONA_HORARIA));
            $hoy    = $ahora->format('Y-m-d');
            $observ = trim((string) ($datos['Observaciones'] ?? ''));
            $insert = $this->queryBuilder->queryExecute(
                'INSERT INTO reserva_servicios_listado
                    (idEstadoReserva, idCotizacion, idFacturacion, idPromocion, idEntidadCliente,
                     idEntidadTrabajador, idComuna, idUsuarioCreador, idUsuarioResponsable,
                     CodigoSeguimiento, idOrigen, idRequiereAsignacion, idCanal, Direccion, Referencia,
                     Fecha, Hora_Inicio, Hora_Termino, DuracionMin, Observaciones, Recargo, Descuento,
                     ValorNeto, IVA, ValorTotal, UltimoContacto, ProximaAccion, fecha_auto,
                     Creacion_fecha, Creacion_Semana, Creacion_mes, Creacion_ano, Creacion_hora)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                $this->DBConn,
                false,
                false,
                [
                    1, null, null, $c['idPromocion'], $idEntidad,
                    $idTrabajador > 0 ? $idTrabajador : null, $idComuna, $idUsuario,
                    (int) ($datos['idUsuarioResponsable'] ?? 0) ?: null,
                    $codigo, self::ORIGEN_WEB, $idAsignacion, $idCanal > 0 ? $idCanal : null,
                    (string) ($datos['Direccion'] ?? ''), (string) ($datos['Referencia'] ?? ''),
                    $fecha, $horaInicio, $horaTermino, (int) $c['duracionMin'], $observ !== '' ? $observ : null,
                    $c['recargo'], $c['descuento'], $c['neto'], $c['iva'], $c['total'],
                    null, 'Contactar al cliente', $hoy, $hoy,
                    (int) $ahora->format('W'), (int) $ahora->format('n'), (int) $ahora->format('Y'), $ahora->format('H:i:s'),
                ]
            );
            if ($insert['status'] !== true) {
                $this->DBConn->rollBack();
                return $this->fail('No fue posible crear la reserva', 'RESERVA_FALLIDA');
            }
            $idReserva = (int) $this->DBConn->lastInsertId();

            // Líneas con snapshot
            foreach ($c['lineas'] as $linea) {
                $detalle = $this->queryBuilder->queryExecute(
                    'INSERT INTO reserva_servicios_listado_servicios
                        (idReservaServicio, idTarifa, NombreServicio,
                         DuracionMin, Number, ValorUnitario, ValorTotal)
                     VALUES (?, ?, ?, ?, ?, ?, ?)',
                    $this->DBConn,
                    false,
                    false,
                    [
                        $idReserva, $linea['idTarifa'], $linea['NombreServicio'],
                        $linea['DuracionMin'], $linea['Number'],
                        $linea['ValorUnitario'], $linea['ValorTotal'],
                    ]
                );
                if ($detalle['status'] !== true) {
                    $this->DBConn->rollBack();
                    return $this->fail('No fue posible guardar el detalle de la reserva', 'RESERVA_DETALLE_FALLIDO');
                }
            }

            // Evento
            $evento = $this->queryBuilder->queryExecute(
                'INSERT INTO reserva_servicios_listado_eventos
                    (idReservaServicio, idUsuario, idEstadoAnterior, idEstadoNuevo, Evento, Motivo, FechaCreacion, HoraCreacion)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                $this->DBConn,
                false,
                false,
                [$idReserva, $idUsuario, null, 1, 'Solicitud creada', 'Alta desde portal web', $hoy, $ahora->format('H:i:s')]
            );
            if ($evento['status'] !== true) {
                $this->DBConn->rollBack();
                return $this->fail('No fue posible registrar el evento de la reserva', 'EVENTO_FALLIDO');
            }

            // Cotización
            $cotizacion = $this->crearCotizacion([
                'idEntidad'     => $idEntidad,
                'idUsuario'     => $idUsuario,
                'lineas'        => $c['lineas'],
                'subtotal'      => $c['subtotal'],
                'neto'          => $c['neto'],
                'iva'           => $c['iva'],
                'total'         => $c['total'],
                'observaciones' => 'Reserva web '.$codigo,
            ]);
            if ($cotizacion['status'] !== true) {
                $this->DBConn->rollBack();
                return $cotizacion;
            }
            $idCotizacion = (int) $cotizacion['data']['idCotizacion'];
            $this->queryBuilder->queryExecute(
                'UPDATE reserva_servicios_listado SET idCotizacion = ? WHERE idReservaServicio = ?',
                $this->DBConn,
                false,
                false,
                [$idCotizacion, $idReserva]
            );

            $this->DBConn->commit();

            /************************************/
            // Retorno
            return $this->ok([
                'idReservaServicio'   => $idReserva,
                'CodigoSeguimiento'   => $codigo,
                'idCotizacion'        => $idCotizacion,
                'idEntidadCliente'    => $idEntidad,
                'idEntidadTrabajador' => $idTrabajador > 0 ? $idTrabajador : null,
                'Total'               => $c['total'],
                'Estado'              => 1,
                'RequiereAsignacion'  => $sinTrabajador || $idTrabajador <= 0 ? 1 : 0,
            ], 'Reserva creada');
        } catch (Exception $e) {
            if ($this->DBConn->inTransaction()) {
                $this->DBConn->rollBack();
            }
            return $this->fail('No fue posible crear la reserva', $e->getMessage());
        }
    }

    /******************************************************************************/
    /**
     * Valida que un cambio de estado esté permitido por la matriz de transiciones.
     *
     * No consulta la base de datos ni valida motivo, nivel de acceso ni
     * disponibilidad: eso es responsabilidad del llamador (T2).
     *
     * @param int $idEstadoActual Estado actual de la reserva.
     * @param int $idEstadoNuevo  Estado de destino solicitado.
     *
     * @return array{status:bool,data:mixed,message:string,error:string|null}
     *
     * @example
     * ```php
     * $r = $helper->validarTransicion(4, 5); // status true
     * $r = $helper->validarTransicion(14, 5); // status false
     * ```
     */
    public function validarTransicion(int $idEstadoActual, int $idEstadoNuevo): array {
        $permitidos = self::TRANSICIONES[$idEstadoActual] ?? null;
        if ($permitidos === null || !in_array($idEstadoNuevo, $permitidos, true)) {
            return $this->fail('Transición no permitida', 'TRANSICION_NO_PERMITIDA');
        }
        return $this->ok(['Desde' => $idEstadoActual, 'Hacia' => $idEstadoNuevo], 'Transición permitida');
    }

    /******************************************************************************/
    /**
     * Formatea el Valor de una promoción según su tipo (función pura, sin BD).
     *
     * Usa el mapa FORMATO_VALOR_PROMOCION: Porcentaje -> '25,50 %'; Monto ->
     * '$ 25.000,50'; tipo ausente del mapa o no numérico -> solo el número con
     * 2 decimales. Devuelve texto sin escapar (el escape HTML lo hace la vista).
     *
     * @param mixed $idTipo reserva_servicios_promociones_tipos.idTipo.
     * @param mixed $valor  Valor de la promoción (no numérico se trata como 0).
     *
     * @return string
     *
     * @example
     * ```php
     * FunctionsReservaServicios::formatearValorPromocion(1, 25.5);    // '25,50 %'
     * FunctionsReservaServicios::formatearValorPromocion(2, 25000.5); // '$ 25.000,50'
     * FunctionsReservaServicios::formatearValorPromocion(99, 25000.5); // '25.000,50'
     * ```
     */
    public static function formatearValorPromocion($idTipo, $valor): string {
        $formato = is_numeric($idTipo) ? (self::FORMATO_VALOR_PROMOCION[(int) $idTipo] ?? null) : null;
        $numero = number_format(is_numeric($valor) ? (float) $valor : 0.0, 2, ',', '.');
        return ($formato['prefijo'] ?? '') . $numero . ($formato['sufijo'] ?? '');
    }

    /*******************************************************************************************************************/
    /*                                                                                                                 */
    /*                                              Metodos Privados                                                   */
    /*                                                                                                                 */
    /*******************************************************************************************************************/
    /******************************************************************************/
    /**
     * Busca la primera entidad trabajadora activa de la comuna sin conflicto en el rango.
     *
     * Solo considera a los trabajadores asignados a la cobertura activa de la
     * comuna (reserva_servicios_cobertura_trabajadores).
     *
     * @param string $fecha       Fecha Y-m-d.
     * @param string $horaInicio  Hora H:i:s.
     * @param string $horaTermino Hora H:i:s.
     * @param int    $idComuna    Comuna del servicio.
     *
     * @return array
     */
    private function buscarTrabajadorLibre(string $fecha, string $horaInicio, string $horaTermino, int $idComuna): array {
        $sql = 'SELECT e.idEntidad
                FROM entidades_listado e
                WHERE e.idTipo = 3 AND e.idEstado = 1
                  AND EXISTS (
                      SELECT 1 FROM reserva_servicios_cobertura_trabajadores ct
                      INNER JOIN reserva_servicios_cobertura co ON co.idCobertura = ct.idCobertura
                      WHERE ct.idTrabajador = e.idEntidad AND co.idComuna = ? AND co.Activo = 1
                  )
                  AND NOT EXISTS (
                      SELECT 1 FROM reserva_servicios_listado rl
                      INNER JOIN reserva_servicios_estados re ON re.idEstadoReserva = rl.idEstadoReserva
                      WHERE rl.idEntidadTrabajador = e.idEntidad AND rl.Fecha = ?
                        AND rl.Hora_Inicio < ? AND rl.Hora_Termino > ? AND re.BloqueaAgenda = 1
                  )
                  AND NOT EXISTS (
                      SELECT 1 FROM reserva_servicios_bloqueos b
                      WHERE b.idEntidadTrabajador = e.idEntidad AND b.Fecha = ?
                        AND b.Hora_Inicio < ? AND b.Hora_Termino > ?
                        AND (b.idTipo = ? OR (b.idTipo = ? AND b.Expira > NOW()))
                  )
                ORDER BY e.idEntidad ASC
                LIMIT 1';
        $result = $this->queryBuilder->queryExecute(
            $sql,
            $this->DBConn,
            false,
            true,
            [$idComuna, $fecha, $horaTermino, $horaInicio, $fecha, $horaTermino, $horaInicio, self::BLOQUEO_MANUAL, self::BLOQUEO_RETENCION]
        );
        if ($result['status'] !== true) {
            return $this->fail('No fue posible consultar trabajadores disponibles', 'TRABAJADOR_CONSULTA');
        }
        if (empty($result['data'])) {
            return $this->fail('No hay trabajadores disponibles para ese horario', 'SIN_TRABAJADOR');
        }
        return $this->ok($result['data'], 'Trabajador disponible');
    }

    /******************************************************************************/
    /**
     * Busca un conflicto de reserva para un trabajador.
     *
     * @return array
     */
    private function buscarConflictoReserva(int $idTrabajador, string $fecha, string $horaInicio, string $horaTermino, ?int $excluirId): array {
        $sql = 'SELECT rl.idReservaServicio, rl.Hora_Inicio, rl.Hora_Termino
                FROM reserva_servicios_listado rl
                INNER JOIN reserva_servicios_estados re ON re.idEstadoReserva = rl.idEstadoReserva
                WHERE rl.idEntidadTrabajador = ? AND rl.Fecha = ?
                  AND rl.Hora_Inicio < ? AND rl.Hora_Termino > ?
                  AND re.BloqueaAgenda = 1';
        $params = [$idTrabajador, $fecha, $horaTermino, $horaInicio];
        if ($excluirId !== null && $excluirId > 0) {
            $sql .= ' AND rl.idReservaServicio <> ?';
            $params[] = $excluirId;
        }
        $sql .= ' LIMIT 1';
        $result = $this->queryBuilder->queryExecute($sql, $this->DBConn, false, true, $params);
        if ($result['status'] !== true) {
            return $this->fail('No fue posible validar la disponibilidad', 'DISPONIBILIDAD_CONSULTA');
        }
        return $this->ok($result['data'] ?: null, 'Consulta de reservas');
    }

    /******************************************************************************/
    /**
     * Busca un bloqueo o retención activa para un trabajador.
     *
     * @return array
     */
    private function buscarConflictoBloqueo(int $idTrabajador, string $fecha, string $horaInicio, string $horaTermino, ?int $excluirId): array {
        $sql = 'SELECT b.idBloqueo, b.idTipo, b.Hora_Inicio, b.Hora_Termino, b.Expira
                FROM reserva_servicios_bloqueos b
                WHERE b.idEntidadTrabajador = ? AND b.Fecha = ?
                  AND b.Hora_Inicio < ? AND b.Hora_Termino > ?
                  AND (b.idTipo = ? OR (b.idTipo = ? AND b.Expira > NOW()))';
        $params = [$idTrabajador, $fecha, $horaTermino, $horaInicio, self::BLOQUEO_MANUAL, self::BLOQUEO_RETENCION];
        if ($excluirId !== null && $excluirId > 0) {
            $sql .= ' AND (b.idReservaServicio IS NULL OR b.idReservaServicio <> ?)';
            $params[] = $excluirId;
        }
        $sql .= ' LIMIT 1';
        $result = $this->queryBuilder->queryExecute($sql, $this->DBConn, false, true, $params);
        if ($result['status'] !== true) {
            return $this->fail('No fue posible validar los bloqueos', 'BLOQUEO_CONSULTA');
        }
        return $this->ok($result['data'] ?: null, 'Consulta de bloqueos');
    }

    /******************************************************************************/
    /**
     * Bloquea la fila del trabajador dentro de la transacción activa.
     *
     * Equivale a ControllerBase::lockRowsForUpdate('entidades_listado',
     * 'idEntidad', [$id]) (ADR-0002), adaptado a una clase que no hereda del
     * controlador.
     *
     * @param int $idTrabajador Identificador de la entidad.
     *
     * @return array
     */
    private function lockTrabajador(int $idTrabajador): array {
        $idTrabajador = (int) $idTrabajador;
        if ($idTrabajador <= 0) {
            return $this->fail('Trabajador inválido', 'TRABAJADOR_INVALIDO');
        }
        $result = $this->queryBuilder->queryExecute(
            'SELECT idEntidad FROM entidades_listado WHERE idEntidad = ? FOR UPDATE',
            $this->DBConn,
            false,
            false,
            [$idTrabajador]
        );
        if ($result['status'] !== true) {
            return $this->fail('No fue posible bloquear al trabajador', 'LOCK_FALLIDO');
        }
        return $this->ok($result['data'], 'Trabajador bloqueado');
    }

    /******************************************************************************/
    /**
     * Busca o crea la entidad cliente (tipo 2) de una reserva web.
     *
     * @param array $datos Datos del cliente.
     *
     * @return array
     */
    private function resolverCliente(array $datos): array {

        /************************************/
        // Cliente ya identificado
        $idEntidad = (int) ($datos['idEntidadCliente'] ?? 0);
        if ($idEntidad > 0) {
            $query = [
                'data'   => 'idEntidad',
                'table'  => 'entidades_listado',
                'join'   => '',
                'where'  => 'idEntidad = ? AND idTipo = 2 AND idEstado = 1',
                'params' => [$idEntidad],
                'group'  => '',
                'having' => '',
                'order'  => '',
            ];
            $existe = $this->queryBuilder->queryRow($query, $this->DBConn);
            if ($existe['status'] !== true || empty($existe['data'])) {
                return $this->fail('Cliente inválido', 'CLIENTE_INVALIDO');
            }
            return $this->ok(['idEntidad' => $idEntidad], 'Cliente existente');
        }

        /************************************/
        // Datos mínimos
        $nombre    = trim((string) ($datos['Nombre'] ?? ''));
        $apellido  = trim((string) ($datos['ApellidoPat'] ?? ''));
        $email     = strtolower(trim((string) ($datos['Email'] ?? '')));
        $telefono  = trim((string) ($datos['Telefono'] ?? ''));
        $direccion = trim((string) ($datos['Direccion'] ?? ''));
        $idComuna  = (int) ($datos['idComuna'] ?? 0);
        if ($nombre === '' || ($email === '' && $telefono === '')) {
            return $this->fail('Faltan datos de contacto del cliente', 'CLIENTE_INCOMPLETO');
        }

        /************************************/
        // Búsqueda por correo o teléfono (cualquiera de los dos identifica)
        $where  = 'idTipo = 2 AND (1 = 0';
        $params = [];
        if ($email !== '') {
            $where   .= ' OR Email = ?';
            $params[] = $email;
        }
        if ($telefono !== '') {
            $where   .= ' OR Fono1 = ?';
            $params[] = $telefono;
        }
        $where .= ')';
        $query = [
            'data'   => 'idEntidad',
            'table'  => 'entidades_listado',
            'join'   => '',
            'where'  => $where,
            'params' => $params,
            'group'  => '',
            'having' => '',
            'order'  => 'idEntidad ASC',
        ];
        $busqueda = $this->queryBuilder->queryRow($query, $this->DBConn);
        if ($busqueda['status'] !== true) {
            return $this->fail('No fue posible buscar al cliente', 'CLIENTE_CONSULTA');
        }

        if (!empty($busqueda['data'])) {
            $idEntidad = (int) $busqueda['data']['idEntidad'];
        } else {
            /************************************/
            // Alta del cliente
            $insert = $this->queryBuilder->queryExecute(
                'INSERT INTO entidades_listado
                    (idEstado, idTipo, idTipoEntidad, password, Nombre, ApellidoPat, ApellidoMat,
                     Email, Fono1, idComuna, Direccion)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                $this->DBConn,
                false,
                false,
                [
                    1, 2, 1, '',
                    $nombre,
                    $apellido !== '' ? $apellido : '-',
                    trim((string) ($datos['ApellidoMat'] ?? '')),
                    $email !== '' ? $email : null,
                    $telefono !== '' ? $telefono : null,
                    $idComuna > 0 ? $idComuna : null,
                    $direccion !== '' ? $direccion : null,
                ]
            );
            if ($insert['status'] !== true) {
                return $this->fail('No fue posible registrar al cliente', 'CLIENTE_ALTA');
            }
            $idEntidad = (int) $this->DBConn->lastInsertId();
        }

        /************************************/
        // Se completan solo campos vacíos
        $this->queryBuilder->queryExecute(
            'UPDATE entidades_listado
             SET Direccion = COALESCE(NULLIF(Direccion, \'\'), ?),
                 idComuna  = COALESCE(idComuna, ?),
                 Email     = COALESCE(NULLIF(Email, \'\'), ?),
                 Fono1     = COALESCE(NULLIF(Fono1, \'\'), ?)
             WHERE idEntidad = ?',
            $this->DBConn,
            false,
            false,
            [
                $direccion !== '' ? $direccion : null,
                $idComuna > 0 ? $idComuna : null,
                $email !== '' ? $email : null,
                $telefono !== '' ? $telefono : null,
                $idEntidad,
            ]
        );

        /************************************/
        // Contacto principal
        $contacto = $this->queryBuilder->queryRow([
            'data'   => 'idContacto',
            'table'  => 'entidades_listado_contactos',
            'join'   => '',
            'where'  => 'idEntidad = ? AND (Email = ? OR Fono1 = ?)',
            'params' => [$idEntidad, $email, $telefono],
            'group'  => '',
            'having' => '',
            'order'  => 'idContacto ASC',
        ], $this->DBConn);
        if ($contacto['status'] !== true) {
            return $this->fail('No fue posible consultar el contacto del cliente', 'CONTACTO_CONSULTA');
        }
        if (empty($contacto['data'])) {
            $this->queryBuilder->queryExecute(
                'INSERT INTO entidades_listado_contactos
                    (idEntidad, Nombre, ApellidoPat, ApellidoMat, Email, Fono1, idComuna, Direccion, idEstado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                $this->DBConn,
                false,
                false,
                [
                    $idEntidad,
                    $nombre,
                    $apellido !== '' ? $apellido : '-',
                    trim((string) ($datos['ApellidoMat'] ?? '')),
                    $email !== '' ? $email : null,
                    $telefono !== '' ? $telefono : null,
                    $idComuna > 0 ? $idComuna : null,
                    $direccion !== '' ? $direccion : null,
                    1,
                ]
            );
        }

        /************************************/
        // Retorno
        return $this->ok(['idEntidad' => $idEntidad], 'Cliente resuelto');
    }

    /******************************************************************************/
    /**
     * Calcula el beneficio de una promoción.
     *
     * @param array $promo    Fila de la promoción.
     * @param float $subtotal Subtotal de la reserva.
     *
     * @return float Beneficio en pesos.
     */
    private function calcularBeneficioPromocion(array $promo, float $subtotal): float {
        $tipo  = (int) ($promo['idTipo'] ?? self::PROMOCION_MONTO);
        $valor = (float) ($promo['Valor'] ?? 0);
        if ($tipo === self::PROMOCION_PORCENTAJE) {
            $porcentaje = min(max($valor, 0), 100);
            if ($subtotal > 0) {
                return round($subtotal * $porcentaje / 100, 2);
            }
            return round($porcentaje, 2);
        }
        return round($subtotal > 0 ? min($valor, $subtotal) : $valor, 2);
    }

    /******************************************************************************/
    /**
     * Indica si alguna línea exige inspección previa.
     *
     * @param array $lineas Líneas con snapshot.
     *
     * @return int 1 si requiere inspección, 0 en caso contrario.
     */
    private function requiereInspeccion(array $lineas): int {
        foreach ($lineas as $linea) {
            if ((int) ($linea['idRequiereInspeccion'] ?? 0) === 1) {
                return 1;
            }
        }
        return 0;
    }

    /******************************************************************************/
    /**
     * Suma minutos a una hora y devuelve el resultado en H:i:s.
     *
     * @param string $hora       Hora en formato H:i o H:i:s.
     * @param int    $minutos    Minutos a sumar.
     *
     * @return string Hora resultante o cadena vacía si la entrada no es válida.
     */
    private function sumarMinutos(string $hora, int $minutos): string {
        $hora = $this->normalizarHora($hora);
        if ($hora === '' || $minutos <= 0) {
            return '';
        }
        $dt = DateTime::createFromFormat('H:i:s', $hora, new DateTimeZone(self::ZONA_HORARIA));
        if (!$dt) {
            return '';
        }
        $dt->modify('+'.$minutos.' minutes');
        return $dt->format('H:i:s');
    }

    /******************************************************************************/
    /**
     * Normaliza una hora a H:i:s.
     *
     * @param string $hora Hora en formato H:i o H:i:s.
     *
     * @return string Hora normalizada o cadena vacía.
     */
    private function normalizarHora(string $hora): string {
        $hora = trim($hora);
        foreach (['H:i:s', 'H:i'] as $formato) {
            $dt = DateTime::createFromFormat($formato, $hora, new DateTimeZone(self::ZONA_HORARIA));
            if ($dt && $dt->format($formato) === $hora) {
                return $dt->format('H:i:s');
            }
        }
        return '';
    }

    /******************************************************************************/
    /**
     * Valida una fecha en formato Y-m-d.
     *
     * @param string $fecha Fecha a validar.
     *
     * @return bool
     */
    private function esFechaValida(string $fecha): bool {
        $dt = DateTime::createFromFormat('Y-m-d', $fecha, new DateTimeZone(self::ZONA_HORARIA));
        return $dt !== false && $dt->format('Y-m-d') === $fecha;
    }

    /******************************************************************************/
    /**
     * Respuesta correcta del helper.
     *
     * @param mixed  $data    Datos de salida.
     * @param string $message Mensaje descriptivo.
     *
     * @return array
     */
    private function ok($data = null, string $message = 'OK'): array {
        return ['status' => true, 'data' => $data, 'message' => $message, 'error' => null];
    }

    /******************************************************************************/
    /**
     * Respuesta fallida del helper.
     *
     * @param string      $message Mensaje para el usuario.
     * @param string|null $error   Código interno del error.
     *
     * @return array
     */
    private function fail(string $message, ?string $error = null): array {
        return ['status' => false, 'data' => null, 'message' => $message, 'error' => $error ?? ''];
    }
}
