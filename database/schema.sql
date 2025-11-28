
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for core_documentos_mercantiles
-- ----------------------------
DROP TABLE IF EXISTS `core_documentos_mercantiles`;
CREATE TABLE `core_documentos_mercantiles` (
  `idDocumentos` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(30) NOT NULL,
  PRIMARY KEY (`idDocumentos`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_documentos_mercantiles
-- ----------------------------
BEGIN;
INSERT INTO `core_documentos_mercantiles` (`idDocumentos`, `Nombre`) VALUES (1, 'Factura');
INSERT INTO `core_documentos_mercantiles` (`idDocumentos`, `Nombre`) VALUES (2, 'Boleta');
INSERT INTO `core_documentos_mercantiles` (`idDocumentos`, `Nombre`) VALUES (3, 'Guia Despacho');
COMMIT;

-- ----------------------------
-- Table structure for core_documentos_pago
-- ----------------------------
DROP TABLE IF EXISTS `core_documentos_pago`;
CREATE TABLE `core_documentos_pago` (
  `idDocumentoPago` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(30) NOT NULL,
  PRIMARY KEY (`idDocumentoPago`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_documentos_pago
-- ----------------------------
BEGIN;
INSERT INTO `core_documentos_pago` (`idDocumentoPago`, `Nombre`) VALUES (1, 'Efectivo');
INSERT INTO `core_documentos_pago` (`idDocumentoPago`, `Nombre`) VALUES (2, 'Tarjeta de Credito');
INSERT INTO `core_documentos_pago` (`idDocumentoPago`, `Nombre`) VALUES (3, 'Cheque');
INSERT INTO `core_documentos_pago` (`idDocumentoPago`, `Nombre`) VALUES (4, 'Deposito');
INSERT INTO `core_documentos_pago` (`idDocumentoPago`, `Nombre`) VALUES (5, 'Transferencia Bancaria');
INSERT INTO `core_documentos_pago` (`idDocumentoPago`, `Nombre`) VALUES (6, 'Tarjeta de Debito');
INSERT INTO `core_documentos_pago` (`idDocumentoPago`, `Nombre`) VALUES (7, 'Otro');
COMMIT;

-- ----------------------------
-- Table structure for core_estados
-- ----------------------------
DROP TABLE IF EXISTS `core_estados`;
CREATE TABLE `core_estados` (
  `idEstado` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(30) NOT NULL,
  `Color` varchar(255) NOT NULL,
  PRIMARY KEY (`idEstado`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados
-- ----------------------------
BEGIN;
INSERT INTO `core_estados` (`idEstado`, `Nombre`, `Color`) VALUES (1, 'Activo', 'bg-success');
INSERT INTO `core_estados` (`idEstado`, `Nombre`, `Color`) VALUES (2, 'Inactivo', 'bg-danger');
COMMIT;

-- ----------------------------
-- Table structure for core_estados_afirmacion
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_afirmacion`;
CREATE TABLE `core_estados_afirmacion` (
  `idEstadoAfirmacion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoAfirmacion`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_afirmacion
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_afirmacion` (`idEstadoAfirmacion`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Positivo', 'bg-success', NULL);
INSERT INTO `core_estados_afirmacion` (`idEstadoAfirmacion`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Negativo', 'bg-danger', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_analisis
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_analisis`;
CREATE TABLE `core_estados_analisis` (
  `idEstadoAnalisis` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoAnalisis`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_analisis
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_analisis` (`idEstadoAnalisis`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Normal', NULL, NULL);
INSERT INTO `core_estados_analisis` (`idEstadoAnalisis`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Precaucion', NULL, NULL);
INSERT INTO `core_estados_analisis` (`idEstadoAnalisis`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Anormal', NULL, NULL);
INSERT INTO `core_estados_analisis` (`idEstadoAnalisis`, `Nombre`, `Color`, `Icon`) VALUES (4, 'Severo', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_apertura
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_apertura`;
CREATE TABLE `core_estados_apertura` (
  `idEstadoApertura` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoApertura`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_apertura
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_apertura` (`idEstadoApertura`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Abierto', 'bg-danger', NULL);
INSERT INTO `core_estados_apertura` (`idEstadoApertura`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Cerrado', 'bg-success', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_aprobacion
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_aprobacion`;
CREATE TABLE `core_estados_aprobacion` (
  `idEstadoAprobacion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoAprobacion`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_aprobacion
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_aprobacion` (`idEstadoAprobacion`, `Nombre`, `Color`, `Icon`) VALUES (1, 'En espera de aprobacion', NULL, NULL);
INSERT INTO `core_estados_aprobacion` (`idEstadoAprobacion`, `Nombre`, `Color`, `Icon`) VALUES (2, 'No Aprobado', NULL, NULL);
INSERT INTO `core_estados_aprobacion` (`idEstadoAprobacion`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Aprobado', 'bg-success', NULL);
INSERT INTO `core_estados_aprobacion` (`idEstadoAprobacion`, `Nombre`, `Color`, `Icon`) VALUES (4, 'Reintentado', 'bg-warning', NULL);
INSERT INTO `core_estados_aprobacion` (`idEstadoAprobacion`, `Nombre`, `Color`, `Icon`) VALUES (5, 'Rechazado', 'bg-danger', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_asistencia
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_asistencia`;
CREATE TABLE `core_estados_asistencia` (
  `idEstadoAsistencia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoAsistencia`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_asistencia
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_asistencia` (`idEstadoAsistencia`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Ausente', 'bg-danger', NULL);
INSERT INTO `core_estados_asistencia` (`idEstadoAsistencia`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Presente', 'bg-success', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_cierre
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_cierre`;
CREATE TABLE `core_estados_cierre` (
  `idEstadoCierre` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(30) NOT NULL,
  `Color` varchar(255) NOT NULL,
  `Icon` varchar(255) NOT NULL,
  PRIMARY KEY (`idEstadoCierre`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_cierre
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_cierre` (`idEstadoCierre`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Abierta', 'bg-success', '');
INSERT INTO `core_estados_cierre` (`idEstadoCierre`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Cerrada', 'bg-danger', '');
COMMIT;

-- ----------------------------
-- Table structure for core_estados_civil
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_civil`;
CREATE TABLE `core_estados_civil` (
  `idEstadoCivil` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoCivil`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_civil
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_civil` (`idEstadoCivil`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Soltero/a', NULL, NULL);
INSERT INTO `core_estados_civil` (`idEstadoCivil`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Casado/a', NULL, NULL);
INSERT INTO `core_estados_civil` (`idEstadoCivil`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Viudo/a', NULL, NULL);
INSERT INTO `core_estados_civil` (`idEstadoCivil`, `Nombre`, `Color`, `Icon`) VALUES (4, 'Divorciado/a', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_colores
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_colores`;
CREATE TABLE `core_estados_colores` (
  `idColor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idColor`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_colores
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_colores` (`idColor`, `Nombre`) VALUES (1, 'bg-primary');
INSERT INTO `core_estados_colores` (`idColor`, `Nombre`) VALUES (2, 'bg-secondary');
INSERT INTO `core_estados_colores` (`idColor`, `Nombre`) VALUES (3, 'bg-success');
INSERT INTO `core_estados_colores` (`idColor`, `Nombre`) VALUES (4, 'bg-danger');
INSERT INTO `core_estados_colores` (`idColor`, `Nombre`) VALUES (5, 'bg-warning');
INSERT INTO `core_estados_colores` (`idColor`, `Nombre`) VALUES (6, 'bg-info');
INSERT INTO `core_estados_colores` (`idColor`, `Nombre`) VALUES (7, 'bg-light text-dark');
INSERT INTO `core_estados_colores` (`idColor`, `Nombre`) VALUES (8, 'bg-dark');
COMMIT;

-- ----------------------------
-- Table structure for core_estados_contrato
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_contrato`;
CREATE TABLE `core_estados_contrato` (
  `idEstadoContrato` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoContrato`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_contrato
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_contrato` (`idEstadoContrato`, `Nombre`, `Color`, `Icon`) VALUES (1, 'No contratado', NULL, NULL);
INSERT INTO `core_estados_contrato` (`idEstadoContrato`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Contratado', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_devolucion
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_devolucion`;
CREATE TABLE `core_estados_devolucion` (
  `idEstadoDevolucion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoDevolucion`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_devolucion
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_devolucion` (`idEstadoDevolucion`, `Nombre`, `Color`, `Icon`) VALUES (1, 'No devuelto', 'bg-danger', NULL);
INSERT INTO `core_estados_devolucion` (`idEstadoDevolucion`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Devuelto', 'bg-success', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_ejecucion
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_ejecucion`;
CREATE TABLE `core_estados_ejecucion` (
  `idEstadoEjecucion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoEjecucion`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_ejecucion
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_ejecucion` (`idEstadoEjecucion`, `Nombre`, `Color`, `Icon`) VALUES (1, 'No ejecutado', NULL, NULL);
INSERT INTO `core_estados_ejecucion` (`idEstadoEjecucion`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Ejecutado', 'bg-success', NULL);
INSERT INTO `core_estados_ejecucion` (`idEstadoEjecucion`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Cancelado', 'bg-danger', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_encendido
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_encendido`;
CREATE TABLE `core_estados_encendido` (
  `idEstadoEncendido` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoEncendido`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_encendido
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_encendido` (`idEstadoEncendido`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Encendido', 'bg-success', NULL);
INSERT INTO `core_estados_encendido` (`idEstadoEncendido`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Apagado', 'bg-danger', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_estudio
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_estudio`;
CREATE TABLE `core_estados_estudio` (
  `idEstadoEstudio` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoEstudio`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_estudio
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_estudio` (`idEstadoEstudio`, `Nombre`, `Color`, `Icon`) VALUES (1, 'No Terminado', NULL, NULL);
INSERT INTO `core_estados_estudio` (`idEstadoEstudio`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Congelado', NULL, NULL);
INSERT INTO `core_estados_estudio` (`idEstadoEstudio`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Terminado', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_facturacion
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_facturacion`;
CREATE TABLE `core_estados_facturacion` (
  `idFacturable` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(30) NOT NULL,
  PRIMARY KEY (`idFacturable`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_facturacion
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_facturacion` (`idFacturable`, `Nombre`) VALUES (1, 'Facturable');
INSERT INTO `core_estados_facturacion` (`idFacturable`, `Nombre`) VALUES (2, 'No Facturable');
COMMIT;

-- ----------------------------
-- Table structure for core_estados_ingreso
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_ingreso`;
CREATE TABLE `core_estados_ingreso` (
  `idEstadoIngreso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoIngreso`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_ingreso
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_ingreso` (`idEstadoIngreso`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Ingreso', NULL, NULL);
INSERT INTO `core_estados_ingreso` (`idEstadoIngreso`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Egreso', NULL, NULL);
INSERT INTO `core_estados_ingreso` (`idEstadoIngreso`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Traspaso', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_pago
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_pago`;
CREATE TABLE `core_estados_pago` (
  `idEstadoPago` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoPago`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_pago
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_pago` (`idEstadoPago`, `Nombre`, `Color`, `Icon`) VALUES (1, 'No Pagado', 'bg-warning', NULL);
INSERT INTO `core_estados_pago` (`idEstadoPago`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Pagado', 'bg-success', NULL);
INSERT INTO `core_estados_pago` (`idEstadoPago`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Anulado', 'bg-danger', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_partida
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_partida`;
CREATE TABLE `core_estados_partida` (
  `idEstadoPartida` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoPartida`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_partida
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_partida` (`idEstadoPartida`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Partida Creada', NULL, NULL);
INSERT INTO `core_estados_partida` (`idEstadoPartida`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Partida Enviada', NULL, NULL);
INSERT INTO `core_estados_partida` (`idEstadoPartida`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Partida Revisada', 'table-warning', NULL);
INSERT INTO `core_estados_partida` (`idEstadoPartida`, `Nombre`, `Color`, `Icon`) VALUES (4, 'Partida Confirmada', 'table-success', NULL);
INSERT INTO `core_estados_partida` (`idEstadoPartida`, `Nombre`, `Color`, `Icon`) VALUES (5, 'Partida Rechazada', 'table-danger', NULL);
INSERT INTO `core_estados_partida` (`idEstadoPartida`, `Nombre`, `Color`, `Icon`) VALUES (6, 'Partida Entregada', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_paso
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_paso`;
CREATE TABLE `core_estados_paso` (
  `idEstadoPaso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoPaso`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_paso
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_paso` (`idEstadoPaso`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Pasa', 'bg-success', NULL);
INSERT INTO `core_estados_paso` (`idEstadoPaso`, `Nombre`, `Color`, `Icon`) VALUES (2, 'No pasa', 'bg-danger', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_programacion
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_programacion`;
CREATE TABLE `core_estados_programacion` (
  `idEstadoProgramacion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoProgramacion`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_programacion
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_programacion` (`idEstadoProgramacion`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Programada', NULL, NULL);
INSERT INTO `core_estados_programacion` (`idEstadoProgramacion`, `Nombre`, `Color`, `Icon`) VALUES (2, 'En Ejecucion', NULL, NULL);
INSERT INTO `core_estados_programacion` (`idEstadoProgramacion`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Finalizada', NULL, NULL);
INSERT INTO `core_estados_programacion` (`idEstadoProgramacion`, `Nombre`, `Color`, `Icon`) VALUES (4, 'Cancelada', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_solicitud
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_solicitud`;
CREATE TABLE `core_estados_solicitud` (
  `idEstadoSolicitud` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoSolicitud`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_solicitud
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_solicitud` (`idEstadoSolicitud`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Solicitado', NULL, NULL);
INSERT INTO `core_estados_solicitud` (`idEstadoSolicitud`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Ejecutado', 'bg-warning', NULL);
INSERT INTO `core_estados_solicitud` (`idEstadoSolicitud`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Terminado', 'bg-success', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_trabajos
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_trabajos`;
CREATE TABLE `core_estados_trabajos` (
  `idEstadoTrabajo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(255) NOT NULL,
  `Icon` varchar(255) NOT NULL,
  PRIMARY KEY (`idEstadoTrabajo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_trabajos
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_trabajos` (`idEstadoTrabajo`, `Nombre`, `Color`, `Icon`) VALUES (1, 'Abierta', 'bg-primary', 'bi bi-clock');
INSERT INTO `core_estados_trabajos` (`idEstadoTrabajo`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Terminada', 'bg-success', 'bi bi-calendar-check');
INSERT INTO `core_estados_trabajos` (`idEstadoTrabajo`, `Nombre`, `Color`, `Icon`) VALUES (3, 'Cancelada', 'bg-danger', 'bi bi-exclamation-circle');
COMMIT;

-- ----------------------------
-- Table structure for core_estados_uso
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_uso`;
CREATE TABLE `core_estados_uso` (
  `idEstadoUso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoUso`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_uso
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_uso` (`idEstadoUso`, `Nombre`, `Color`, `Icon`) VALUES (1, 'No utilizado', NULL, NULL);
INSERT INTO `core_estados_uso` (`idEstadoUso`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Utilizado', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_utilizable
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_utilizable`;
CREATE TABLE `core_estados_utilizable` (
  `idEstadoUtilizable` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoUtilizable`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_utilizable
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_utilizable` (`idEstadoUtilizable`, `Nombre`, `Color`, `Icon`) VALUES (1, 'No utilizable', 'bg-danger', NULL);
INSERT INTO `core_estados_utilizable` (`idEstadoUtilizable`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Utilizable', 'bg-success', NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estados_vista
-- ----------------------------
DROP TABLE IF EXISTS `core_estados_vista`;
CREATE TABLE `core_estados_vista` (
  `idEstadoVista` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(120) DEFAULT NULL,
  `Icon` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`idEstadoVista`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estados_vista
-- ----------------------------
BEGIN;
INSERT INTO `core_estados_vista` (`idEstadoVista`, `Nombre`, `Color`, `Icon`) VALUES (1, 'No Visto', NULL, NULL);
INSERT INTO `core_estados_vista` (`idEstadoVista`, `Nombre`, `Color`, `Icon`) VALUES (2, 'Visto', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_estudios
-- ----------------------------
DROP TABLE IF EXISTS `core_estudios`;
CREATE TABLE `core_estudios` (
  `idEstudios` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(30) NOT NULL,
  `Color` varchar(255) NOT NULL,
  PRIMARY KEY (`idEstudios`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_estudios
-- ----------------------------
BEGIN;
INSERT INTO `core_estudios` (`idEstudios`, `Nombre`, `Color`) VALUES (1, 'Jardin', 'bg-success');
INSERT INTO `core_estudios` (`idEstudios`, `Nombre`, `Color`) VALUES (2, 'Basica', 'bg-success');
INSERT INTO `core_estudios` (`idEstudios`, `Nombre`, `Color`) VALUES (3, 'Media', 'bg-success');
INSERT INTO `core_estudios` (`idEstudios`, `Nombre`, `Color`) VALUES (4, 'Superior', 'bg-success');
COMMIT;

-- ----------------------------
-- Table structure for core_facturacion_tipo
-- ----------------------------
DROP TABLE IF EXISTS `core_facturacion_tipo`;
CREATE TABLE `core_facturacion_tipo` (
  `idTipo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(30) NOT NULL,
  PRIMARY KEY (`idTipo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_facturacion_tipo
-- ----------------------------
BEGIN;
INSERT INTO `core_facturacion_tipo` (`idTipo`, `Nombre`) VALUES (1, 'Compra');
INSERT INTO `core_facturacion_tipo` (`idTipo`, `Nombre`) VALUES (2, 'Venta');
COMMIT;

-- ----------------------------
-- Table structure for core_iconos_colores
-- ----------------------------
DROP TABLE IF EXISTS `core_iconos_colores`;
CREATE TABLE `core_iconos_colores` (
  `idColor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(30) NOT NULL,
  PRIMARY KEY (`idColor`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_iconos_colores
-- ----------------------------
BEGIN;
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (1, 'color-red');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (2, 'color-red-light');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (3, 'color-red-dark');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (4, 'color-blue');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (5, 'color-blue-light');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (6, 'color-blue-dark');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (7, 'color-green');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (8, 'color-green-light');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (9, 'color-green-dark');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (10, 'color-yellow');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (11, 'color-yellow-light');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (12, 'color-yellow-dark');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (13, 'color-white');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (14, 'color-dark');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (15, 'color-dark-light');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (16, 'color-dark-dark');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (17, 'color-gray');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (18, 'color-gray-light');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (19, 'color-gray-dark');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (20, 'color-mdb-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (21, 'color-red-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (22, 'color-pink-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (23, 'color-purple-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (24, 'color-deep-purple-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (25, 'color-indigo-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (26, 'color-blue-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (27, 'color-light-blue-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (28, 'color-cyan-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (29, 'color-teal-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (30, 'color-green-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (31, 'color-light-green-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (32, 'color-lime-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (33, 'color-yellow-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (34, 'color-amber-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (35, 'color-orange-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (36, 'color-deep-orange-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (37, 'color-brown-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (38, 'color-blue-grey-text');
INSERT INTO `core_iconos_colores` (`idColor`, `Nombre`) VALUES (39, 'color-grey-text');
COMMIT;

-- ----------------------------
-- Table structure for core_opciones
-- ----------------------------
DROP TABLE IF EXISTS `core_opciones`;
CREATE TABLE `core_opciones` (
  `idOpciones` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(255) NOT NULL,
  PRIMARY KEY (`idOpciones`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_opciones
-- ----------------------------
BEGIN;
INSERT INTO `core_opciones` (`idOpciones`, `Nombre`, `Color`) VALUES (1, 'Si', 'bg-success');
INSERT INTO `core_opciones` (`idOpciones`, `Nombre`, `Color`) VALUES (2, 'No', 'bg-danger');
COMMIT;

-- ----------------------------
-- Table structure for core_permisos_categorias
-- ----------------------------
DROP TABLE IF EXISTS `core_permisos_categorias`;
CREATE TABLE `core_permisos_categorias` (
  `idPermisosCat` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(60) NOT NULL,
  `Icon` varchar(120) NOT NULL,
  `IdIconColor` int(10) unsigned NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Carpeta` varchar(255) NOT NULL,
  PRIMARY KEY (`idPermisosCat`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Administrador';

-- ----------------------------
-- Records of core_permisos_categorias
-- ----------------------------
BEGIN;
INSERT INTO `core_permisos_categorias` (`idPermisosCat`, `Nombre`, `Icon`, `IdIconColor`, `Descripcion`, `Carpeta`) VALUES (1, 'Administración', 'bi bi-card-list', 4, 'Categoria para la administracion', 'administracion');
INSERT INTO `core_permisos_categorias` (`idPermisosCat`, `Nombre`, `Icon`, `IdIconColor`, `Descripcion`, `Carpeta`) VALUES (2, 'Gestión Proyectos', 'bi bi-hammer', 9, 'Categoria para los proyectos', 'gestionProyectos');
INSERT INTO `core_permisos_categorias` (`idPermisosCat`, `Nombre`, `Icon`, `IdIconColor`, `Descripcion`, `Carpeta`) VALUES (3, 'Gestión Bodegas y Productos', 'bi bi-bounding-box', 6, 'Categoria para las bodegas', 'gestionBodegas');
INSERT INTO `core_permisos_categorias` (`idPermisosCat`, `Nombre`, `Icon`, `IdIconColor`, `Descripcion`, `Carpeta`) VALUES (4, 'Gestión Documentos Mercantiles', 'bi bi-file-earmark-text', 35, 'Categoria para las boletas y facturas', 'gestionDocumentos');
INSERT INTO `core_permisos_categorias` (`idPermisosCat`, `Nombre`, `Icon`, `IdIconColor`, `Descripcion`, `Carpeta`) VALUES (5, 'Gestión Campañas', 'bi bi-basket', 1, 'Categoria para las campañas', 'gestionCampanas');
INSERT INTO `core_permisos_categorias` (`idPermisosCat`, `Nombre`, `Icon`, `IdIconColor`, `Descripcion`, `Carpeta`) VALUES (6, 'Externalización Servicios', 'bi bi-bezier2', 34, 'Categoria hecha para la externalizacion de los servicios', 'serviciosTerceros');
COMMIT;

-- ----------------------------
-- Table structure for core_permisos_listado
-- ----------------------------
DROP TABLE IF EXISTS `core_permisos_listado`;
CREATE TABLE `core_permisos_listado` (
  `idPermisos` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idPermisosCat` int(10) unsigned NOT NULL,
  `idEstado` int(10) unsigned NOT NULL,
  `idTipo` int(10) unsigned NOT NULL,
  `Nombre` varchar(120) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `idLevelLimit` int(10) unsigned NOT NULL,
  `RutaWeb` varchar(255) NOT NULL,
  `RutaController` varchar(255) NOT NULL,
  PRIMARY KEY (`idPermisos`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Administrador';

-- ----------------------------
-- Records of core_permisos_listado
-- ----------------------------
BEGIN;
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (1, 2, 1, 1, 'Tareas en Curso', 'Listado de tareas gestionadas', 4, 'gestionProyectos/kanban/tareas', 'kanbanTareas');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (2, 2, 1, 3, 'Informe Tareas', 'Informe de las tareas del panel', 1, 'gestionProyectos/kanban/informeTareas', 'informeTareas');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (3, 1, 1, 1, 'Gestion Proyectos - Tableros', 'Listado de tableros kanban', 4, 'administracion/kanban/tableros', 'kanbanTableros');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (4, 1, 1, 1, 'Gestion Proyectos - Tareas', 'Permite crear los trabajos a hacer dentro de las tareas', 4, 'administracion/kanban/trabajos', 'kanbanTrabajos');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (5, 1, 1, 2, 'Usuarios - Listado', 'Permite la administracion de los usuarios al interior de la plataforma', 3, 'administracion/usuarios', 'usuariosListado');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (6, 1, 1, 1, 'Gestion Entidades - Sectores', 'Permite administrar los sectores', 4, 'administracion/entidades/sectores', 'entidadesSectores');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (7, 1, 1, 2, 'Gestion Entidades - Listado', 'Permite administrar las entidades', 4, 'administracion/entidades/listado', 'entidadesListado');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (8, 1, 1, 1, 'Productos - Categorias', 'Permite la administracion de las categorias de los productos', 4, 'administracion/productos/categorias', 'productosCategorias');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (9, 1, 1, 1, 'Productos - Tipos', 'Permite la administracion de los tipos de productos', 4, 'administracion/productos/tipos', 'productosTipos');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (10, 1, 1, 2, 'Productos - Listado', 'Permite la administracion de los productos', 4, 'administracion/productos/listado', 'productosListado');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (11, 1, 1, 2, 'Bodegas - Listado', 'Permite administrar las bodegas', 4, 'administracion/bodegas/listado', 'bodegasListado');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (12, 3, 1, 2, 'Movimientos Bodegas - Ingresos', 'Permite el ingreso de productos a bodega', 4, 'gestionBodegas/ingresos/listado', 'bodegasMovimientoIngreso');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (13, 3, 1, 2, 'Movimientos Bodegas - Egresos', 'Permite el egreso de productos a bodega', 4, 'gestionBodegas/egresos/listado', 'bodegasMovimientoEgreso');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (14, 3, 1, 2, 'Movimientos Bodegas - Traspasos', 'Permite el traspaso de productos entre bodegas', 4, 'gestionBodegas/traspaso/listado', 'bodegasMovimientoTraspaso');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (15, 3, 1, 3, 'Stock Productos', 'Permite ver el stock actual de los productos en las bodegas', 1, 'gestionBodegas/productos/listado', 'informeProductos');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (16, 1, 1, 1, 'Servicios - Categorias', 'Permite la administracion de las categorias de los servicios', 4, 'administracion/servicios/categorias', 'serviciosCategorias');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (17, 1, 1, 2, 'Servicios - Listado', 'Permite la administracion de los servicios', 4, 'administracion/servicios/listado', 'serviciosListado');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (18, 4, 1, 2, 'Compras', 'Permite el ingreso de los documentos de compras', 4, 'gestionDocumentos/compras/listado', 'gestionDocumentosCompras');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (19, 4, 1, 2, 'Ventas', 'Permite el ingreso de los documentos de ventas', 4, 'gestionDocumentos/ventas/listado', 'gestionDocumentosVentas');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (20, 4, 1, 3, 'Buscar Documentos', 'Permite la busqueda de documentos', 1, 'gestionDocumentos/informe/busqueda/listado', 'informeDocumentos');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (21, 1, 1, 2, 'Datos de la Empresa', 'Permite administrar los datos de la empresa', 2, 'administracion/sistema', 'coreSistema');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (22, 5, 1, 2, 'Campañas - Listado', 'Permite la generacion de campañas', 4, 'gestionCampanas/campanas/listado', 'gestionCampanas');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (23, 5, 1, 3, 'Informe Cobranzas', 'Permite filtrar los clientes con pagos atrasados', 1, 'gestionCampanas/cobranzas/listado', 'cobranzaCampanas');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (24, 5, 1, 3, 'Exportar Datos', 'Permite exportar todos los datos', 1, 'gestionCampanas/informe/exportacionDatos', 'exportarCampanas');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (25, 5, 1, 3, 'Informe Pagos', 'Permite filtrar los documentos con falta de pagos para ingresar los pagos', 1, 'gestionCampanas/pagos/listado', 'pagosCampanas');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (26, 6, 1, 2, 'Clientes - Opciones Extras', 'Permite administrar las opciones extras de los clientes', 4, 'serviciosTerceros/entidades/listado', 'tercerosEntidadesListado');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (27, 4, 1, 2, 'Cotizaciones', 'Permite el ingreso de los documentos de ventas', 4, 'cotizacionListado/ventas/listado', 'cotizacionListado');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (28, 4, 1, 3, 'Buscar Cotizaciones', 'Permite la busqueda de cotizaciones', 1, 'cotizacionListado/informe/busqueda/listado', 'informeCotizacion');
INSERT INTO `core_permisos_listado` (`idPermisos`, `idPermisosCat`, `idEstado`, `idTipo`, `Nombre`, `Descripcion`, `idLevelLimit`, `RutaWeb`, `RutaController`) VALUES (29, 1, 1, 2, 'Maquinas - Listado', 'Permite la administracion de las Maquinas', 4, 'administracion/maquinas/listado', 'maquinasListado');
COMMIT;

-- ----------------------------
-- Table structure for core_permisos_listado_level_limit
-- ----------------------------
DROP TABLE IF EXISTS `core_permisos_listado_level_limit`;
CREATE TABLE `core_permisos_listado_level_limit` (
  `idLevelLimit` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `NombreCorto` varchar(120) NOT NULL,
  `Objetivo` varchar(120) NOT NULL,
  PRIMARY KEY (`idLevelLimit`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_permisos_listado_level_limit
-- ----------------------------
BEGIN;
INSERT INTO `core_permisos_listado_level_limit` (`idLevelLimit`, `Nombre`, `NombreCorto`, `Objetivo`) VALUES (1, 'Solo Ver', 'view', 'Ver');
INSERT INTO `core_permisos_listado_level_limit` (`idLevelLimit`, `Nombre`, `NombreCorto`, `Objetivo`) VALUES (2, 'Ver / Editar', 'view / edit', 'Editar');
INSERT INTO `core_permisos_listado_level_limit` (`idLevelLimit`, `Nombre`, `NombreCorto`, `Objetivo`) VALUES (3, 'Ver / Editar / Crear', 'view / edit / create', 'Crear');
INSERT INTO `core_permisos_listado_level_limit` (`idLevelLimit`, `Nombre`, `NombreCorto`, `Objetivo`) VALUES (4, 'Ver / Editar / Crear / Borrar', 'view / edit / create / del', 'Borrar');
COMMIT;

-- ----------------------------
-- Table structure for core_permisos_listado_rutas
-- ----------------------------
DROP TABLE IF EXISTS `core_permisos_listado_rutas`;
CREATE TABLE `core_permisos_listado_rutas` (
  `idRutas` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idPermisos` int(10) unsigned NOT NULL,
  `idMetodo` int(10) unsigned NOT NULL,
  `RutaWeb` varchar(255) NOT NULL,
  `RutaController` varchar(255) NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `idLevelLimit` int(10) unsigned NOT NULL,
  `Controller` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idRutas`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=514 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Administrador';

-- ----------------------------
-- Records of core_permisos_listado_rutas
-- ----------------------------
BEGIN;
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (1, 1, 1, 'gestionProyectos/kanban/tareas/listAll', 'kanbanTareas->listAll', 'Listar Toda la Información', 1, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (2, 1, 2, 'gestionProyectos/kanban/tareas/search', 'kanbanTareas->UpdateTableList', 'Filtrar datos', 1, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (3, 1, 1, 'gestionProyectos/kanban/tareas/updateList', 'kanbanTareas->UpdateList', 'Actualizar Lista', 2, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (4, 1, 1, 'gestionProyectos/kanban/tareas/view/@id', 'kanbanTareas->View', 'Mostrar Detallado', 1, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (5, 1, 1, 'gestionProyectos/kanban/tareas/getID/@id', 'kanbanTareas->GetID', 'Información para el formulario edición', 2, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (6, 1, 2, 'gestionProyectos/kanban/tareas', 'kanbanTareas->Insert', 'Crear Información', 3, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (7, 1, 2, 'gestionProyectos/kanban/tareas/update', 'kanbanTareas->Update', 'Editar por post (modificar y subir archivos)', 2, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (8, 1, 3, 'gestionProyectos/kanban/tareas', 'kanbanTareas->Delete', 'Borrar dato y archivos', 4, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (9, 1, 2, 'gestionProyectos/kanban/tareas/changeStatus', 'kanbanTareas->ChangeStatus', 'Actualiza el estado', 2, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (10, 1, 1, 'gestionProyectos/kanban/tareas/print/@id', 'kanbanTareas->Print', 'Pantalla imprimir', 1, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (11, 1, 1, 'gestionProyectos/kanban/estados/getID/@id', 'kanbanEstados->GetID', 'Información para el formulario edición', 2, 'kanbanEstados');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (12, 1, 2, 'gestionProyectos/kanban/estados', 'kanbanEstados->Insert', 'Crear Información', 3, 'kanbanEstados');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (13, 1, 2, 'gestionProyectos/kanban/estados/update', 'kanbanEstados->Update', 'Editar por post (modificar y subir archivos)', 2, 'kanbanEstados');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (14, 1, 3, 'gestionProyectos/kanban/estados', 'kanbanEstados->Delete', 'Borrar dato y archivos', 4, 'kanbanEstados');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (15, 1, 1, 'gestionProyectos/kanban/tareasTareas/newData/@id', 'kanbanTareasTareas->NewData', 'Formulario Creación', 2, 'kanbanTareasTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (16, 1, 1, 'gestionProyectos/kanban/tareasTareas/getID/@id', 'kanbanTareasTareas->GetID', 'Informacion para el formulario', 2, 'kanbanTareasTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (17, 1, 2, 'gestionProyectos/kanban/tareasTareas', 'kanbanTareasTareas->Insert', 'Crear Información', 2, 'kanbanTareasTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (18, 1, 2, 'gestionProyectos/kanban/tareasTareas/update', 'kanbanTareasTareas->Update', 'Editar por post (modificar y subir archivos)', 2, 'kanbanTareasTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (19, 1, 1, 'gestionProyectos/kanban/tareasParticipantes/newData/@id', 'kanbanTareasParticipantes->NewData', 'Formulario Creación', 2, 'kanbanTareasParticipantes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (20, 1, 2, 'gestionProyectos/kanban/tareasParticipantes', 'kanbanTareasParticipantes->Insert', 'Crear Información', 2, 'kanbanTareasParticipantes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (21, 1, 3, 'gestionProyectos/kanban/tareasParticipantes', 'kanbanTareasParticipantes->Delete', 'Borrar dato y archivos', 2, 'kanbanTareasParticipantes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (22, 1, 1, 'gestionProyectos/kanban/tareas/updateTableList', 'kanbanTareas->UpdateTableList', 'Filtrar datos', 1, 'kanbanTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (23, 2, 1, 'gestionProyectos/kanban/informeTareas/listAll', 'informeTareas->listAll', 'Filtro de búsqueda', 1, 'informeTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (24, 2, 2, 'gestionProyectos/kanban/informeTareas/search', 'informeTareas->UpdateList', 'Filtrar datos', 1, 'informeTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (25, 2, 1, 'gestionProyectos/kanban/informeTareas/view/@id', 'informeTareas->View', 'Mostrar Detallado', 1, 'informeTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (26, 2, 1, 'gestionProyectos/kanban/informeTareas/print/@id', 'informeTareas->Print', 'Pantalla imprimir', 1, 'informeTareas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (27, 3, 1, 'administracion/kanban/tableros/listAll', 'kanbanTableros->listAll', 'Listar Toda la Información', 1, 'kanbanTableros');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (28, 3, 2, 'administracion/kanban/tableros/search', 'kanbanTableros->UpdateList', 'Filtrar datos', 1, 'kanbanTableros');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (29, 3, 1, 'administracion/kanban/tableros/updateList', 'kanbanTableros->UpdateList', 'Actualizar Lista', 2, 'kanbanTableros');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (30, 3, 1, 'administracion/kanban/tableros/view/@id', 'kanbanTableros->View', 'Mostrar Detallado', 1, 'kanbanTableros');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (31, 3, 1, 'administracion/kanban/tableros/getID/@id', 'kanbanTableros->GetID', 'Información para el formulario edición', 2, 'kanbanTableros');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (32, 3, 2, 'administracion/kanban/tableros', 'kanbanTableros->Insert', 'Crear Información', 3, 'kanbanTableros');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (33, 3, 2, 'administracion/kanban/tableros/update', 'kanbanTableros->Update', 'Editar por post (modificar y subir archivos)', 2, 'kanbanTableros');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (34, 3, 3, 'administracion/kanban/tableros', 'kanbanTableros->Delete', 'Borrar dato y archivos', 4, 'kanbanTableros');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (35, 4, 1, 'administracion/kanban/trabajos/listAll', 'kanbanTrabajos->listAll', 'Listar Toda la Información', 1, 'kanbanTrabajos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (36, 4, 2, 'administracion/kanban/trabajos/search', 'kanbanTrabajos->UpdateList', 'Filtrar datos', 1, 'kanbanTrabajos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (37, 4, 1, 'administracion/kanban/trabajos/updateList', 'kanbanTrabajos->UpdateList', 'Actualizar Lista', 2, 'kanbanTrabajos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (38, 4, 1, 'administracion/kanban/trabajos/view/@id', 'kanbanTrabajos->View', 'Mostrar Detallado', 1, 'kanbanTrabajos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (39, 4, 1, 'administracion/kanban/trabajos/getID/@id', 'kanbanTrabajos->GetID', 'Información para el formulario edición', 2, 'kanbanTrabajos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (40, 4, 2, 'administracion/kanban/trabajos', 'kanbanTrabajos->Insert', 'Crear Información', 3, 'kanbanTrabajos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (41, 4, 2, 'administracion/kanban/trabajos/update', 'kanbanTrabajos->Update', 'Editar por post (modificar y subir archivos)', 2, 'kanbanTrabajos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (42, 4, 3, 'administracion/kanban/trabajos', 'kanbanTrabajos->Delete', 'Borrar dato y archivos', 4, 'kanbanTrabajos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (43, 5, 1, 'administracion/usuarios/listAll', 'usuariosListado->listAll', 'Listar Toda la Información', 1, 'usuariosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (44, 5, 2, 'administracion/usuarios/search', 'usuariosListado->UpdateList', 'Filtrar datos', 1, 'usuariosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (45, 5, 1, 'administracion/usuarios/updateList', 'usuariosListado->UpdateList', 'Actualizar Lista', 2, 'usuariosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (46, 5, 1, 'administracion/usuarios/view/@id', 'usuariosListado->View', 'Mostrar Detallado', 1, 'usuariosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (47, 5, 1, 'administracion/usuarios/resumen/@id', 'usuariosListado->Resumen', 'Mostrar Resúmen', 2, 'usuariosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (48, 5, 1, 'administracion/usuarios/resumenUpdate/@id', 'usuariosListado->ResumenUpdate', 'Mostrar información', 2, 'usuariosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (49, 5, 2, 'administracion/usuarios', 'usuariosListado->Insert', 'Crear Información', 3, 'usuariosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (50, 5, 2, 'administracion/usuarios/update', 'usuariosListado->Update', 'Editar por post (modificar y subir archivos)', 2, 'usuariosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (51, 5, 4, 'administracion/usuarios/delFiles', 'usuariosListado->DelFiles', 'Permite eliminar archivos', 2, 'usuariosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (52, 5, 3, 'administracion/usuarios', 'usuariosListado->Delete', 'Borrar dato y archivos', 4, 'usuariosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (53, 5, 1, 'administracion/usuarios/observaciones/new/@id', 'usuariosListadoObs->New', 'Mostrar modal nuevo', 2, 'usuariosListadoObs');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (54, 5, 1, 'administracion/usuarios/observaciones/updateList/@id', 'usuariosListadoObs->UpdateList', 'Actualizar Lista', 2, 'usuariosListadoObs');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (55, 5, 1, 'administracion/usuarios/observaciones/view/@id', 'usuariosListadoObs->View', 'Mostrar Detallado', 2, 'usuariosListadoObs');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (56, 5, 1, 'administracion/usuarios/observaciones/getID/@id', 'usuariosListadoObs->GetID', 'Información para el formulario edición', 2, 'usuariosListadoObs');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (57, 5, 2, 'administracion/usuarios/observaciones', 'usuariosListadoObs->Insert', 'Crear Información', 2, 'usuariosListadoObs');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (58, 5, 2, 'administracion/usuarios/observaciones/update', 'usuariosListadoObs->Update', 'Editar por post (modificar y subir archivos)', 2, 'usuariosListadoObs');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (59, 5, 3, 'administracion/usuarios/observaciones', 'usuariosListadoObs->Delete', 'Borrar dato y archivos', 2, 'usuariosListadoObs');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (60, 5, 2, 'administracion/usuarios/permisos/update', 'usuariosListadoPermisos->Update', 'Modificar los permisos de los usuarios', 2, 'usuariosListadoPermisos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (61, 6, 1, 'administracion/entidades/sectores/listAll', 'entidadesSectores->listAll', 'Listar Toda la Información', 1, 'entidadesSectores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (62, 6, 2, 'administracion/entidades/sectores/search', 'entidadesSectores->UpdateList', 'Filtrar datos', 1, 'entidadesSectores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (63, 6, 1, 'administracion/entidades/sectores/updateList', 'entidadesSectores->UpdateList', 'Actualizar Lista', 2, 'entidadesSectores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (64, 6, 1, 'administracion/entidades/sectores/view/@id', 'entidadesSectores->View', 'Mostrar Detallado', 1, 'entidadesSectores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (65, 6, 1, 'administracion/entidades/sectores/getID/@id', 'entidadesSectores->GetID', 'Información para el formulario edición', 2, 'entidadesSectores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (66, 6, 2, 'administracion/entidades/sectores', 'entidadesSectores->Insert', 'Crear Información', 3, 'entidadesSectores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (67, 6, 2, 'administracion/entidades/sectores/update', 'entidadesSectores->Update', 'Editar por post (modificar y subir archivos)', 2, 'entidadesSectores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (68, 6, 3, 'administracion/entidades/sectores', 'entidadesSectores->Delete', 'Borrar dato y archivos', 4, 'entidadesSectores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (69, 7, 1, 'administracion/entidades/listado/listAll', 'entidadesListado->listAll', 'Listar Toda la Información', 1, 'entidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (70, 7, 2, 'administracion/entidades/listado/search', 'entidadesListado->UpdateList', 'Filtrar datos', 1, 'entidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (71, 7, 1, 'administracion/entidades/listado/updateList', 'entidadesListado->UpdateList', 'Actualizar Lista', 2, 'entidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (72, 7, 1, 'administracion/entidades/listado/view/@id', 'entidadesListado->View', 'Mostrar Detallado', 1, 'entidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (73, 7, 1, 'administracion/entidades/listado/resumen/@id', 'entidadesListado->Resumen', 'Mostrar Resúmen', 2, 'entidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (74, 7, 1, 'administracion/entidades/listado/resumenUpdate/@id', 'entidadesListado->ResumenUpdate', 'Mostrar información', 2, 'entidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (75, 7, 2, 'administracion/entidades/listado', 'entidadesListado->Insert', 'Crear Información', 3, 'entidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (76, 7, 2, 'administracion/entidades/listado/update', 'entidadesListado->Update', 'Editar por post (modificar y subir archivos)', 2, 'entidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (77, 7, 4, 'administracion/entidades/listado/delFiles', 'entidadesListado->DelFiles', 'Permite eliminar archivos', 2, 'entidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (78, 7, 3, 'administracion/entidades/listado', 'entidadesListado->Delete', 'Borrar dato y archivos', 4, 'entidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (79, 7, 1, 'administracion/entidades/listado/observaciones/new/@id', 'entidadesListadoObservaciones->New', 'Mostrar modal nuevo', 2, 'entidadesListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (80, 7, 1, 'administracion/entidades/listado/observaciones/updateList/@id', 'entidadesListadoObservaciones->UpdateList', 'Actualizar Lista', 2, 'entidadesListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (81, 7, 1, 'administracion/entidades/listado/observaciones/view/@id', 'entidadesListadoObservaciones->View', 'Mostrar Detallado', 2, 'entidadesListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (82, 7, 1, 'administracion/entidades/listado/observaciones/getID/@id', 'entidadesListadoObservaciones->GetID', 'Información para el formulario edición', 2, 'entidadesListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (83, 7, 2, 'administracion/entidades/listado/observaciones', 'entidadesListadoObservaciones->Insert', 'Crear Información', 2, 'entidadesListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (84, 7, 2, 'administracion/entidades/listado/observaciones/update', 'entidadesListadoObservaciones->Update', 'Editar por post (modificar y subir archivos)', 2, 'entidadesListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (85, 7, 3, 'administracion/entidades/listado/observaciones', 'entidadesListadoObservaciones->Delete', 'Borrar dato y archivos', 2, 'entidadesListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (86, 7, 1, 'administracion/entidades/listado/cargas/new/@id', 'entidadesListadoCargas->New', 'Mostrar modal nuevo', 2, 'entidadesListadoCargas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (87, 7, 1, 'administracion/entidades/listado/cargas/updateList/@id', 'entidadesListadoCargas->UpdateList', 'Actualizar Lista', 2, 'entidadesListadoCargas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (88, 7, 1, 'administracion/entidades/listado/cargas/view/@id', 'entidadesListadoCargas->View', 'Mostrar Detallado', 2, 'entidadesListadoCargas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (89, 7, 1, 'administracion/entidades/listado/cargas/getID/@id', 'entidadesListadoCargas->GetID', 'Información para el formulario edición', 2, 'entidadesListadoCargas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (90, 7, 2, 'administracion/entidades/listado/cargas', 'entidadesListadoCargas->Insert', 'Crear Información', 2, 'entidadesListadoCargas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (91, 7, 2, 'administracion/entidades/listado/cargas/update', 'entidadesListadoCargas->Update', 'Editar por post (modificar y subir archivos)', 2, 'entidadesListadoCargas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (92, 7, 3, 'administracion/entidades/listado/cargas', 'entidadesListadoCargas->Delete', 'Borrar dato y archivos', 2, 'entidadesListadoCargas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (93, 7, 1, 'administracion/entidades/listado/contactos/new/@id', 'entidadesListadoContactos->New', 'Mostrar modal nuevo', 2, 'entidadesListadoContactos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (94, 7, 1, 'administracion/entidades/listado/contactos/updateList/@id', 'entidadesListadoContactos->UpdateList', 'Actualizar Lista', 2, 'entidadesListadoContactos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (95, 7, 1, 'administracion/entidades/listado/contactos/view/@id', 'entidadesListadoContactos->View', 'Mostrar Detallado', 2, 'entidadesListadoContactos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (96, 7, 1, 'administracion/entidades/listado/contactos/getID/@id', 'entidadesListadoContactos->GetID', 'Información para el formulario edición', 2, 'entidadesListadoContactos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (97, 7, 2, 'administracion/entidades/listado/contactos', 'entidadesListadoContactos->Insert', 'Crear Información', 2, 'entidadesListadoContactos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (98, 7, 2, 'administracion/entidades/listado/contactos/update', 'entidadesListadoContactos->Update', 'Editar por post (modificar y subir archivos)', 2, 'entidadesListadoContactos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (99, 7, 3, 'administracion/entidades/listado/contactos', 'entidadesListadoContactos->Delete', 'Borrar dato y archivos', 2, 'entidadesListadoContactos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (100, 7, 1, 'administracion/entidades/listado/documentos/new/@id', 'entidadesListadoDocumentos->New', 'Mostrar modal nuevo', 2, 'entidadesListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (101, 7, 1, 'administracion/entidades/listado/documentos/updateList/@id', 'entidadesListadoDocumentos->UpdateList', 'Actualizar Lista', 2, 'entidadesListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (102, 7, 1, 'administracion/entidades/listado/documentos/view/@id', 'entidadesListadoDocumentos->View', 'Mostrar Detallado', 2, 'entidadesListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (103, 7, 1, 'administracion/entidades/listado/documentos/getID/@id', 'entidadesListadoDocumentos->GetID', 'Información para el formulario edición', 2, 'entidadesListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (104, 7, 2, 'administracion/entidades/listado/documentos', 'entidadesListadoDocumentos->Insert', 'Crear Información', 2, 'entidadesListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (105, 7, 2, 'administracion/entidades/listado/documentos/update', 'entidadesListadoDocumentos->Update', 'Editar por post (modificar y subir archivos)', 2, 'entidadesListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (106, 7, 3, 'administracion/entidades/listado/documentos', 'entidadesListadoDocumentos->Delete', 'Borrar dato y archivos', 2, 'entidadesListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (107, 8, 1, 'administracion/productos/categorias/listAll', 'productosCategorias->listAll', 'Listar Toda la Información', 1, 'productosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (108, 8, 2, 'administracion/productos/categorias/search', 'productosCategorias->UpdateList', 'Filtrar datos', 1, 'productosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (109, 8, 1, 'administracion/productos/categorias/updateList', 'productosCategorias->UpdateList', 'Actualizar Lista', 2, 'productosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (110, 8, 1, 'administracion/productos/categorias/view/@id', 'productosCategorias->View', 'Mostrar Detallado', 1, 'productosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (111, 8, 1, 'administracion/productos/categorias/getID/@id', 'productosCategorias->GetID', 'Información para el formulario edición', 2, 'productosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (112, 8, 2, 'administracion/productos/categorias', 'productosCategorias->Insert', 'Crear Información', 3, 'productosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (113, 8, 2, 'administracion/productos/categorias/update', 'productosCategorias->Update', 'Editar por post (modificar y subir archivos)', 2, 'productosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (114, 8, 3, 'administracion/productos/categorias', 'productosCategorias->Delete', 'Borrar dato y archivos', 4, 'productosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (115, 9, 1, 'administracion/productos/tipos/listAll', 'productosTipos->listAll', 'Listar Toda la Información', 1, 'productosTipos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (116, 9, 2, 'administracion/productos/tipos/search', 'productosTipos->UpdateList', 'Filtrar datos', 1, 'productosTipos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (117, 9, 1, 'administracion/productos/tipos/updateList', 'productosTipos->UpdateList', 'Actualizar Lista', 2, 'productosTipos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (118, 9, 1, 'administracion/productos/tipos/view/@id', 'productosTipos->View', 'Mostrar Detallado', 1, 'productosTipos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (119, 9, 1, 'administracion/productos/tipos/getID/@id', 'productosTipos->GetID', 'Información para el formulario edición', 2, 'productosTipos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (120, 9, 2, 'administracion/productos/tipos', 'productosTipos->Insert', 'Crear Información', 3, 'productosTipos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (121, 9, 2, 'administracion/productos/tipos/update', 'productosTipos->Update', 'Editar por post (modificar y subir archivos)', 2, 'productosTipos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (122, 9, 3, 'administracion/productos/tipos', 'productosTipos->Delete', 'Borrar dato y archivos', 4, 'productosTipos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (123, 10, 1, 'administracion/productos/listado/listAll', 'productosListado->listAll', 'Listar Toda la Información', 1, 'productosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (124, 10, 2, 'administracion/productos/listado/search', 'productosListado->UpdateList', 'Filtrar datos', 1, 'productosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (125, 10, 1, 'administracion/productos/listado/updateList', 'productosListado->UpdateList', 'Actualizar Lista', 2, 'productosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (126, 10, 1, 'administracion/productos/listado/view/@id', 'productosListado->View', 'Mostrar Detallado', 1, 'productosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (127, 10, 1, 'administracion/productos/listado/resumen/@id', 'productosListado->Resumen', 'Mostrar Resúmen', 2, 'productosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (128, 10, 1, 'administracion/productos/listado/resumenUpdate/@id', 'productosListado->ResumenUpdate', 'Mostrar información', 2, 'productosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (129, 10, 2, 'administracion/productos/listado', 'productosListado->Insert', 'Crear Información', 3, 'productosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (130, 10, 2, 'administracion/productos/listado/update', 'productosListado->Update', 'Editar por post (modificar y subir archivos)', 2, 'productosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (131, 10, 4, 'administracion/productos/listado/delFiles', 'productosListado->DelFiles', 'Permite eliminar archivos', 2, 'productosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (132, 10, 3, 'administracion/productos/listado', 'productosListado->Delete', 'Borrar dato y archivos', 4, 'productosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (133, 10, 1, 'administracion/productos/listado/observaciones/new/@id', 'productosListadoObservaciones->New', 'Mostrar modal nuevo', 2, 'productosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (134, 10, 1, 'administracion/productos/listado/observaciones/updateList/@id', 'productosListadoObservaciones->UpdateList', 'Actualizar Lista', 2, 'productosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (135, 10, 1, 'administracion/productos/listado/observaciones/view/@id', 'productosListadoObservaciones->View', 'Mostrar Detallado', 2, 'productosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (136, 10, 1, 'administracion/productos/listado/observaciones/getID/@id', 'productosListadoObservaciones->GetID', 'Información para el formulario edición', 2, 'productosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (137, 10, 2, 'administracion/productos/listado/observaciones', 'productosListadoObservaciones->Insert', 'Crear Información', 2, 'productosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (138, 10, 2, 'administracion/productos/listado/observaciones/update', 'productosListadoObservaciones->Update', 'Editar por post (modificar y subir archivos)', 2, 'productosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (139, 10, 3, 'administracion/productos/listado/observaciones', 'productosListadoObservaciones->Delete', 'Borrar dato y archivos', 2, 'productosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (140, 10, 1, 'administracion/productos/listado/documentos/new/@id', 'productosListadoDocumentos->New', 'Mostrar modal nuevo', 2, 'productosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (141, 10, 1, 'administracion/productos/listado/documentos/updateList/@id', 'productosListadoDocumentos->UpdateList', 'Actualizar Lista', 2, 'productosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (142, 10, 1, 'administracion/productos/listado/documentos/view/@id', 'productosListadoDocumentos->View', 'Mostrar Detallado', 2, 'productosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (143, 10, 1, 'administracion/productos/listado/documentos/getID/@id', 'productosListadoDocumentos->GetID', 'Información para el formulario edición', 2, 'productosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (144, 10, 2, 'administracion/productos/listado/documentos', 'productosListadoDocumentos->Insert', 'Crear Información', 2, 'productosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (145, 10, 2, 'administracion/productos/listado/documentos/update', 'productosListadoDocumentos->Update', 'Editar por post (modificar y subir archivos)', 2, 'productosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (146, 10, 3, 'administracion/productos/listado/documentos', 'productosListadoDocumentos->Delete', 'Borrar dato y archivos', 2, 'productosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (147, 11, 1, 'administracion/bodegas/listado/listAll', 'bodegasListado->listAll', 'Listar Toda la Información', 1, 'bodegasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (148, 11, 2, 'administracion/bodegas/listado/search', 'bodegasListado->UpdateList', 'Filtrar datos', 1, 'bodegasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (149, 11, 1, 'administracion/bodegas/listado/updateList', 'bodegasListado->UpdateList', 'Actualizar Lista', 2, 'bodegasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (150, 11, 1, 'administracion/bodegas/listado/view/@id', 'bodegasListado->View', 'Mostrar Detallado', 1, 'bodegasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (151, 11, 1, 'administracion/bodegas/listado/resumen/@id', 'bodegasListado->Resumen', 'Mostrar Resúmen', 2, 'bodegasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (152, 11, 1, 'administracion/bodegas/listado/resumenUpdate/@id', 'bodegasListado->ResumenUpdate', 'Mostrar información', 2, 'bodegasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (153, 11, 2, 'administracion/bodegas/listado', 'bodegasListado->Insert', 'Crear Información', 3, 'bodegasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (154, 11, 2, 'administracion/bodegas/listado/update', 'bodegasListado->Update', 'Editar por post (modificar y subir archivos)', 2, 'bodegasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (155, 11, 4, 'administracion/bodegas/listado/delFiles', 'bodegasListado->DelFiles', 'Permite eliminar archivos', 2, 'bodegasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (156, 11, 3, 'administracion/bodegas/listado', 'bodegasListado->Delete', 'Borrar dato y archivos', 4, 'bodegasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (157, 11, 1, 'administracion/bodegas/listado/observaciones/new/@id', 'bodegasListadoObservaciones->New', 'Mostrar modal nuevo', 2, 'bodegasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (158, 11, 1, 'administracion/bodegas/listado/observaciones/updateList/@id', 'bodegasListadoObservaciones->UpdateList', 'Actualizar Lista', 2, 'bodegasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (159, 11, 1, 'administracion/bodegas/listado/observaciones/view/@id', 'bodegasListadoObservaciones->View', 'Mostrar Detallado', 2, 'bodegasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (160, 11, 1, 'administracion/bodegas/listado/observaciones/getID/@id', 'bodegasListadoObservaciones->GetID', 'Información para el formulario edición', 2, 'bodegasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (161, 11, 2, 'administracion/bodegas/listado/observaciones', 'bodegasListadoObservaciones->Insert', 'Crear Información', 2, 'bodegasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (162, 11, 2, 'administracion/bodegas/listado/observaciones/update', 'bodegasListadoObservaciones->Update', 'Editar por post (modificar y subir archivos)', 2, 'bodegasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (163, 11, 3, 'administracion/bodegas/listado/observaciones', 'bodegasListadoObservaciones->Delete', 'Borrar dato y archivos', 2, 'bodegasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (164, 12, 1, 'gestionBodegas/ingresos/listado/listAll', 'bodegasMovimiento->listAll_1', 'Listar Toda la Información', 1, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (165, 12, 2, 'gestionBodegas/ingresos/listado/search', 'bodegasMovimiento->UpdateList_1', 'Filtrar datos', 1, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (166, 12, 1, 'gestionBodegas/ingresos/listado/updateList', 'bodegasMovimiento->UpdateList_1', 'Actualizar Lista', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (167, 12, 1, 'gestionBodegas/ingresos/listado/view/@id', 'bodegasMovimiento->View_1', 'Mostrar Detallado', 1, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (168, 12, 1, 'gestionBodegas/ingresos/listado/resumen/@id', 'bodegasMovimiento->Resumen_1', 'Mostrar Resúmen', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (169, 12, 1, 'gestionBodegas/ingresos/listado/resumenUpdate/@id', 'bodegasMovimiento->ResumenUpdate_1', 'Mostrar información', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (170, 12, 2, 'gestionBodegas/ingresos/listado', 'bodegasMovimiento->Insert', 'Crear Información', 3, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (171, 12, 2, 'gestionBodegas/ingresos/listado/update', 'bodegasMovimiento->Update', 'Editar por post (modificar y subir archivos)', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (172, 12, 4, 'gestionBodegas/ingresos/listado/delFiles', 'bodegasMovimiento->DelFiles', 'Permite eliminar archivos', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (173, 12, 3, 'gestionBodegas/ingresos/listado', 'bodegasMovimiento->Delete', 'Borrar dato y archivos', 4, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (174, 12, 1, 'gestionBodegas/ingresos/listado/productos/new/@id', 'bodegasMovimientoProductos->New_1', 'Mostrar modal nuevo', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (175, 12, 1, 'gestionBodegas/ingresos/listado/productos/updateList/@id', 'bodegasMovimientoProductos->UpdateList_1', 'Actualizar Lista', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (176, 12, 1, 'gestionBodegas/ingresos/listado/productos/getID/@id', 'bodegasMovimientoProductos->GetID_1', 'Información para el formulario edición', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (177, 12, 2, 'gestionBodegas/ingresos/listado/productos', 'bodegasMovimientoProductos->Insert', 'Crear Información', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (178, 12, 2, 'gestionBodegas/ingresos/listado/productos/update', 'bodegasMovimientoProductos->Update', 'Editar por post (modificar y subir archivos)', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (179, 12, 3, 'gestionBodegas/ingresos/listado/productos', 'bodegasMovimientoProductos->Delete', 'Borrar dato y archivos', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (180, 13, 1, 'gestionBodegas/egresos/listado/listAll', 'bodegasMovimiento->listAll_2', 'Listar Toda la Información', 1, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (181, 13, 2, 'gestionBodegas/egresos/listado/search', 'bodegasMovimiento->UpdateList_2', 'Filtrar datos', 1, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (182, 13, 1, 'gestionBodegas/egresos/listado/updateList', 'bodegasMovimiento->UpdateList_2', 'Actualizar Lista', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (183, 13, 1, 'gestionBodegas/egresos/listado/view/@id', 'bodegasMovimiento->View_2', 'Mostrar Detallado', 1, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (184, 13, 1, 'gestionBodegas/egresos/listado/resumen/@id', 'bodegasMovimiento->Resumen_2', 'Mostrar Resúmen', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (185, 13, 1, 'gestionBodegas/egresos/listado/resumenUpdate/@id', 'bodegasMovimiento->ResumenUpdate_2', 'Mostrar información', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (186, 13, 2, 'gestionBodegas/egresos/listado', 'bodegasMovimiento->Insert', 'Crear Información', 3, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (187, 13, 2, 'gestionBodegas/egresos/listado/update', 'bodegasMovimiento->Update', 'Editar por post (modificar y subir archivos)', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (188, 13, 4, 'gestionBodegas/egresos/listado/delFiles', 'bodegasMovimiento->DelFiles', 'Permite eliminar archivos', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (189, 13, 3, 'gestionBodegas/egresos/listado', 'bodegasMovimiento->Delete', 'Borrar dato y archivos', 4, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (190, 13, 1, 'gestionBodegas/egresos/listado/productos/new/@id', 'bodegasMovimientoProductos->New_2', 'Mostrar modal nuevo', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (191, 13, 1, 'gestionBodegas/egresos/listado/productos/updateList/@id', 'bodegasMovimientoProductos->UpdateList_2', 'Actualizar Lista', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (192, 13, 1, 'gestionBodegas/egresos/listado/productos/getID/@id', 'bodegasMovimientoProductos->GetID_2', 'Información para el formulario edición', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (193, 13, 2, 'gestionBodegas/egresos/listado/productos', 'bodegasMovimientoProductos->Insert', 'Crear Información', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (194, 13, 2, 'gestionBodegas/egresos/listado/productos/update', 'bodegasMovimientoProductos->Update', 'Editar por post (modificar y subir archivos)', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (195, 13, 3, 'gestionBodegas/egresos/listado/productos', 'bodegasMovimientoProductos->Delete', 'Borrar dato y archivos', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (196, 14, 1, 'gestionBodegas/traspaso/listado/listAll', 'bodegasMovimiento->listAll_3', 'Listar Toda la Información', 1, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (197, 14, 2, 'gestionBodegas/traspaso/listado/search', 'bodegasMovimiento->UpdateList_3', 'Filtrar datos', 1, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (198, 14, 1, 'gestionBodegas/traspaso/listado/updateList', 'bodegasMovimiento->UpdateList_3', 'Actualizar Lista', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (199, 14, 1, 'gestionBodegas/traspaso/listado/view/@id', 'bodegasMovimiento->View_3', 'Mostrar Detallado', 1, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (200, 14, 1, 'gestionBodegas/traspaso/listado/resumen/@id', 'bodegasMovimiento->Resumen_3', 'Mostrar Resúmen', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (201, 14, 1, 'gestionBodegas/traspaso/listado/resumenUpdate/@id', 'bodegasMovimiento->ResumenUpdate_3', 'Mostrar información', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (202, 14, 2, 'gestionBodegas/traspaso/listado', 'bodegasMovimiento->Insert', 'Crear Información', 3, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (203, 14, 2, 'gestionBodegas/traspaso/listado/update', 'bodegasMovimiento->Update', 'Editar por post (modificar y subir archivos)', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (204, 14, 4, 'gestionBodegas/traspaso/listado/delFiles', 'bodegasMovimiento->DelFiles', 'Permite eliminar archivos', 2, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (205, 14, 3, 'gestionBodegas/traspaso/listado', 'bodegasMovimiento->Delete', 'Borrar dato y archivos', 4, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (206, 14, 1, 'gestionBodegas/traspaso/listado/productos/new/@id', 'bodegasMovimientoProductos->New_3', 'Mostrar modal nuevo', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (207, 14, 1, 'gestionBodegas/traspaso/listado/productos/updateList/@id', 'bodegasMovimientoProductos->UpdateList_3', 'Actualizar Lista', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (208, 14, 1, 'gestionBodegas/traspaso/listado/productos/getID/@id', 'bodegasMovimientoProductos->GetID_3', 'Información para el formulario edición', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (209, 14, 2, 'gestionBodegas/traspaso/listado/productos', 'bodegasMovimientoProductos->Insert', 'Crear Información', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (210, 14, 2, 'gestionBodegas/traspaso/listado/productos/update', 'bodegasMovimientoProductos->Update', 'Editar por post (modificar y subir archivos)', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (211, 14, 3, 'gestionBodegas/traspaso/listado/productos', 'bodegasMovimientoProductos->Delete', 'Borrar dato y archivos', 2, 'bodegasMovimientoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (212, 15, 1, 'gestionBodegas/productos/listado/listAll', 'informeProductos->listAll', 'Filtro de búsqueda', 1, 'informeProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (213, 15, 2, 'gestionBodegas/productos/listado/search', 'informeProductos->UpdateList', 'Filtrar datos', 1, 'informeProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (214, 15, 1, 'gestionBodegas/productos/listado/view/@idProducto/@idBodegas', 'informeProductos->View', 'Mostrar Detallado', 1, 'informeProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (215, 15, 1, 'gestionBodegas/productos/listado/print/@id', 'informeProductos->Print', 'Pantalla imprimir', 1, 'informeProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (216, 16, 1, 'administracion/servicios/categorias/listAll', 'serviciosCategorias->listAll', 'Listar Toda la Información', 1, 'serviciosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (217, 16, 2, 'administracion/servicios/categorias/search', 'serviciosCategorias->UpdateList', 'Filtrar datos', 1, 'serviciosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (218, 16, 1, 'administracion/servicios/categorias/updateList', 'serviciosCategorias->UpdateList', 'Actualizar Lista', 2, 'serviciosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (219, 16, 1, 'administracion/servicios/categorias/view/@id', 'serviciosCategorias->View', 'Mostrar Detallado', 1, 'serviciosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (220, 16, 1, 'administracion/servicios/categorias/getID/@id', 'serviciosCategorias->GetID', 'Información para el formulario edición', 2, 'serviciosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (221, 16, 2, 'administracion/servicios/categorias', 'serviciosCategorias->Insert', 'Crear Información', 3, 'serviciosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (222, 16, 2, 'administracion/servicios/categorias/update', 'serviciosCategorias->Update', 'Editar por post (modificar y subir archivos)', 2, 'serviciosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (223, 16, 3, 'administracion/servicios/categorias', 'serviciosCategorias->Delete', 'Borrar dato y archivos', 4, 'serviciosCategorias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (224, 17, 1, 'administracion/servicios/listado/listAll', 'serviciosListado->listAll', 'Listar Toda la Información', 1, 'serviciosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (225, 17, 2, 'administracion/servicios/listado/search', 'serviciosListado->UpdateList', 'Filtrar datos', 1, 'serviciosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (226, 17, 1, 'administracion/servicios/listado/updateList', 'serviciosListado->UpdateList', 'Actualizar Lista', 2, 'serviciosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (227, 17, 1, 'administracion/servicios/listado/view/@id', 'serviciosListado->View', 'Mostrar Detallado', 1, 'serviciosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (228, 17, 1, 'administracion/servicios/listado/resumen/@id', 'serviciosListado->Resumen', 'Mostrar Resúmen', 2, 'serviciosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (229, 17, 1, 'administracion/servicios/listado/resumenUpdate/@id', 'serviciosListado->ResumenUpdate', 'Mostrar información', 2, 'serviciosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (230, 17, 2, 'administracion/servicios/listado', 'serviciosListado->Insert', 'Crear Información', 3, 'serviciosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (231, 17, 2, 'administracion/servicios/listado/update', 'serviciosListado->Update', 'Editar por post (modificar y subir archivos)', 2, 'serviciosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (232, 17, 4, 'administracion/servicios/listado/delFiles', 'serviciosListado->DelFiles', 'Permite eliminar archivos', 2, 'serviciosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (233, 17, 3, 'administracion/servicios/listado', 'serviciosListado->Delete', 'Borrar dato y archivos', 4, 'serviciosListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (234, 17, 1, 'administracion/servicios/listado/observaciones/new/@id', 'serviciosListadoObservaciones->New', 'Mostrar modal nuevo', 2, 'serviciosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (235, 17, 1, 'administracion/servicios/listado/observaciones/updateList/@id', 'serviciosListadoObservaciones->UpdateList', 'Actualizar Lista', 2, 'serviciosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (236, 17, 1, 'administracion/servicios/listado/observaciones/view/@id', 'serviciosListadoObservaciones->View', 'Mostrar Detallado', 2, 'serviciosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (237, 17, 1, 'administracion/servicios/listado/observaciones/getID/@id', 'serviciosListadoObservaciones->GetID', 'Información para el formulario edición', 2, 'serviciosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (238, 17, 2, 'administracion/servicios/listado/observaciones', 'serviciosListadoObservaciones->Insert', 'Crear Información', 2, 'serviciosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (239, 17, 2, 'administracion/servicios/listado/observaciones/update', 'serviciosListadoObservaciones->Update', 'Editar por post (modificar y subir archivos)', 2, 'serviciosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (240, 17, 3, 'administracion/servicios/listado/observaciones', 'serviciosListadoObservaciones->Delete', 'Borrar dato y archivos', 2, 'serviciosListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (241, 17, 1, 'administracion/servicios/listado/documentos/new/@id', 'serviciosListadoDocumentos->New', 'Mostrar modal nuevo', 2, 'serviciosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (242, 17, 1, 'administracion/servicios/listado/documentos/updateList/@id', 'serviciosListadoDocumentos->UpdateList', 'Actualizar Lista', 2, 'serviciosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (243, 17, 1, 'administracion/servicios/listado/documentos/view/@id', 'serviciosListadoDocumentos->View', 'Mostrar Detallado', 2, 'serviciosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (244, 17, 1, 'administracion/servicios/listado/documentos/getID/@id', 'serviciosListadoDocumentos->GetID', 'Información para el formulario edición', 2, 'serviciosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (245, 17, 2, 'administracion/servicios/listado/documentos', 'serviciosListadoDocumentos->Insert', 'Crear Información', 2, 'serviciosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (246, 17, 2, 'administracion/servicios/listado/documentos/update', 'serviciosListadoDocumentos->Update', 'Editar por post (modificar y subir archivos)', 2, 'serviciosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (247, 17, 3, 'administracion/servicios/listado/documentos', 'serviciosListadoDocumentos->Delete', 'Borrar dato y archivos', 2, 'serviciosListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (248, 18, 1, 'gestionDocumentos/compras/listado/listAll', 'gestionDocumentos->listAll_1', 'Listar Toda la Información', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (249, 18, 2, 'gestionDocumentos/compras/listado/search', 'gestionDocumentos->UpdateList_1', 'Filtrar datos', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (250, 18, 1, 'gestionDocumentos/compras/listado/updateList', 'gestionDocumentos->UpdateList_1', 'Actualizar Lista', 2, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (251, 18, 1, 'gestionDocumentos/compras/listado/view/@id', 'gestionDocumentos->View_1', 'Mostrar Detallado', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (252, 18, 1, 'gestionDocumentos/compras/listado/print/@id', 'gestionDocumentos->Print_1', 'Pantalla imprimir', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (253, 18, 1, 'gestionDocumentos/compras/listado/noPrint/@id', 'gestionDocumentos->noPrint_1', 'Pantalla para visualizar documento', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (254, 18, 1, 'gestionDocumentos/compras/listado/resumen/@id', 'gestionDocumentos->Resumen_1', 'Mostrar Resúmen', 2, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (255, 18, 1, 'gestionDocumentos/compras/listado/resumenUpdate/@id', 'gestionDocumentos->ResumenUpdate_1', 'Mostrar información', 2, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (256, 18, 2, 'gestionDocumentos/compras/listado', 'gestionDocumentos->Insert', 'Crear Información', 3, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (257, 18, 2, 'gestionDocumentos/compras/listado/update', 'gestionDocumentos->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (258, 18, 4, 'gestionDocumentos/compras/listado/delFiles', 'gestionDocumentos->DelFiles', 'Permite eliminar archivos', 2, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (259, 18, 3, 'gestionDocumentos/compras/listado', 'gestionDocumentos->Delete', 'Borrar dato y archivos', 4, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (260, 18, 1, 'gestionDocumentos/compras/listado/items/new/@id', 'gestionDocumentosItems->New_1', 'Mostrar modal nuevo', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (261, 18, 1, 'gestionDocumentos/compras/listado/items/updateList/@id', 'gestionDocumentosItems->UpdateList_1', 'Actualizar Lista', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (262, 18, 1, 'gestionDocumentos/compras/listado/items/getID/@id', 'gestionDocumentosItems->GetID_1', 'Información para el formulario edición', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (263, 18, 2, 'gestionDocumentos/compras/listado/items', 'gestionDocumentosItems->Insert', 'Crear Información', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (264, 18, 2, 'gestionDocumentos/compras/listado/items/update', 'gestionDocumentosItems->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (265, 18, 3, 'gestionDocumentos/compras/listado/items', 'gestionDocumentosItems->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (266, 18, 1, 'gestionDocumentos/compras/listado/productos/new/@id', 'gestionDocumentosProductos->New_1', 'Mostrar modal nuevo', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (267, 18, 1, 'gestionDocumentos/compras/listado/productos/updateList/@id', 'gestionDocumentosProductos->UpdateList_1', 'Actualizar Lista', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (268, 18, 1, 'gestionDocumentos/compras/listado/productos/getID/@id', 'gestionDocumentosProductos->GetID_1', 'Información para el formulario edición', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (269, 18, 2, 'gestionDocumentos/compras/listado/productos', 'gestionDocumentosProductos->Insert', 'Crear Información', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (270, 18, 2, 'gestionDocumentos/compras/listado/productos/update', 'gestionDocumentosProductos->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (271, 18, 3, 'gestionDocumentos/compras/listado/productos', 'gestionDocumentosProductos->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (272, 18, 1, 'gestionDocumentos/compras/listado/servicios/new/@id', 'gestionDocumentosServicios->New_1', 'Mostrar modal nuevo', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (273, 18, 1, 'gestionDocumentos/compras/listado/servicios/updateList/@id', 'gestionDocumentosServicios->UpdateList_1', 'Actualizar Lista', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (274, 18, 1, 'gestionDocumentos/compras/listado/servicios/getID/@id', 'gestionDocumentosServicios->GetID_1', 'Información para el formulario edición', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (275, 18, 2, 'gestionDocumentos/compras/listado/servicios', 'gestionDocumentosServicios->Insert', 'Crear Información', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (276, 18, 2, 'gestionDocumentos/compras/listado/servicios/update', 'gestionDocumentosServicios->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (277, 18, 3, 'gestionDocumentos/compras/listado/servicios', 'gestionDocumentosServicios->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (278, 18, 1, 'gestionDocumentos/compras/listado/guias/new/@id', 'gestionDocumentosGuias->New_1', 'Mostrar modal nuevo', 2, 'gestionDocumentosGuias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (279, 18, 1, 'gestionDocumentos/compras/listado/guias/updateList/@id', 'gestionDocumentosGuias->UpdateList_1', 'Actualizar Lista', 2, 'gestionDocumentosGuias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (280, 18, 2, 'gestionDocumentos/compras/listado/guias', 'gestionDocumentosGuias->Insert', 'Crear Información', 2, 'gestionDocumentosGuias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (281, 18, 3, 'gestionDocumentos/compras/listado/guias', 'gestionDocumentosGuias->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosGuias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (282, 18, 1, 'gestionDocumentos/compras/listado/pagos/new/@id', 'gestionDocumentosPagos->New_1', 'Mostrar modal nuevo', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (283, 18, 1, 'gestionDocumentos/compras/listado/pagos/updateList/@id', 'gestionDocumentosPagos->UpdateList_1', 'Actualizar Lista', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (284, 18, 1, 'gestionDocumentos/compras/listado/pagos/getID/@id', 'gestionDocumentosPagos->GetID_1', 'Información para el formulario edición', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (285, 18, 2, 'gestionDocumentos/compras/listado/pagos', 'gestionDocumentosPagos->Insert', 'Crear Información', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (286, 18, 2, 'gestionDocumentos/compras/listado/pagos/update', 'gestionDocumentosPagos->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (287, 18, 3, 'gestionDocumentos/compras/listado/pagos', 'gestionDocumentosPagos->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (288, 19, 1, 'gestionDocumentos/ventas/listado/listAll', 'gestionDocumentos->listAll_2', 'Listar Toda la Información', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (289, 19, 2, 'gestionDocumentos/ventas/listado/search', 'gestionDocumentos->UpdateList_2', 'Filtrar datos', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (290, 19, 1, 'gestionDocumentos/ventas/listado/updateList', 'gestionDocumentos->UpdateList_2', 'Actualizar Lista', 2, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (291, 19, 1, 'gestionDocumentos/ventas/listado/view/@id', 'gestionDocumentos->View_2', 'Mostrar Detallado', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (292, 19, 1, 'gestionDocumentos/ventas/listado/print/@id', 'gestionDocumentos->Print_2', 'Pantalla imprimir', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (293, 19, 1, 'gestionDocumentos/ventas/listado/noPrint/@id', 'gestionDocumentos->noPrint_2', 'Pantalla para visualizar documento', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (294, 19, 1, 'gestionDocumentos/ventas/listado/resumen/@id', 'gestionDocumentos->Resumen_2', 'Mostrar Resúmen', 2, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (295, 19, 1, 'gestionDocumentos/ventas/listado/resumenUpdate/@id', 'gestionDocumentos->ResumenUpdate_2', 'Mostrar información', 2, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (296, 19, 2, 'gestionDocumentos/ventas/listado', 'gestionDocumentos->Insert', 'Crear Información', 3, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (297, 19, 2, 'gestionDocumentos/ventas/listado/update', 'gestionDocumentos->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (298, 19, 4, 'gestionDocumentos/ventas/listado/delFiles', 'gestionDocumentos->DelFiles', 'Permite eliminar archivos', 2, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (299, 19, 3, 'gestionDocumentos/ventas/listado', 'gestionDocumentos->Delete', 'Borrar dato y archivos', 4, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (300, 19, 1, 'gestionDocumentos/ventas/listado/items/new/@id', 'gestionDocumentosItems->New_2', 'Mostrar modal nuevo', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (301, 19, 1, 'gestionDocumentos/ventas/listado/items/updateList/@id', 'gestionDocumentosItems->UpdateList_2', 'Actualizar Lista', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (302, 19, 1, 'gestionDocumentos/ventas/listado/items/getID/@id', 'gestionDocumentosItems->GetID_2', 'Información para el formulario edición', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (303, 19, 2, 'gestionDocumentos/ventas/listado/items', 'gestionDocumentosItems->Insert', 'Crear Información', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (304, 19, 2, 'gestionDocumentos/ventas/listado/items/update', 'gestionDocumentosItems->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (305, 19, 3, 'gestionDocumentos/ventas/listado/items', 'gestionDocumentosItems->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (306, 19, 1, 'gestionDocumentos/ventas/listado/productos/new/@id', 'gestionDocumentosProductos->New_2', 'Mostrar modal nuevo', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (307, 19, 1, 'gestionDocumentos/ventas/listado/productos/updateList/@id', 'gestionDocumentosProductos->UpdateList_2', 'Actualizar Lista', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (308, 19, 1, 'gestionDocumentos/ventas/listado/productos/getID/@id', 'gestionDocumentosProductos->GetID_2', 'Información para el formulario edición', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (309, 19, 2, 'gestionDocumentos/ventas/listado/productos', 'gestionDocumentosProductos->Insert', 'Crear Información', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (310, 19, 2, 'gestionDocumentos/ventas/listado/productos/update', 'gestionDocumentosProductos->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (311, 19, 3, 'gestionDocumentos/ventas/listado/productos', 'gestionDocumentosProductos->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (312, 19, 1, 'gestionDocumentos/ventas/listado/servicios/new/@id', 'gestionDocumentosServicios->New_2', 'Mostrar modal nuevo', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (313, 19, 1, 'gestionDocumentos/ventas/listado/servicios/updateList/@id', 'gestionDocumentosServicios->UpdateList_2', 'Actualizar Lista', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (314, 19, 1, 'gestionDocumentos/ventas/listado/servicios/getID/@id', 'gestionDocumentosServicios->GetID_2', 'Información para el formulario edición', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (315, 19, 2, 'gestionDocumentos/ventas/listado/servicios', 'gestionDocumentosServicios->Insert', 'Crear Información', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (316, 19, 2, 'gestionDocumentos/ventas/listado/servicios/update', 'gestionDocumentosServicios->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (317, 19, 3, 'gestionDocumentos/ventas/listado/servicios', 'gestionDocumentosServicios->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (318, 19, 1, 'gestionDocumentos/ventas/listado/guias/new/@id', 'gestionDocumentosGuias->New_1', 'Mostrar modal nuevo', 2, 'gestionDocumentosGuias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (319, 19, 1, 'gestionDocumentos/ventas/listado/guias/updateList/@id', 'gestionDocumentosGuias->UpdateList_1', 'Actualizar Lista', 2, 'gestionDocumentosGuias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (320, 19, 2, 'gestionDocumentos/ventas/listado/guias', 'gestionDocumentosGuias->Insert', 'Crear Información', 2, 'gestionDocumentosGuias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (321, 19, 3, 'gestionDocumentos/ventas/listado/guias', 'gestionDocumentosGuias->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosGuias');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (322, 19, 1, 'gestionDocumentos/ventas/listado/pagos/new/@id', 'gestionDocumentosPagos->New_2', 'Mostrar modal nuevo', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (323, 19, 1, 'gestionDocumentos/ventas/listado/pagos/updateList/@id', 'gestionDocumentosPagos->UpdateList_2', 'Actualizar Lista', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (324, 19, 1, 'gestionDocumentos/ventas/listado/pagos/getID/@id', 'gestionDocumentosPagos->GetID_2', 'Información para el formulario edición', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (325, 19, 2, 'gestionDocumentos/ventas/listado/pagos', 'gestionDocumentosPagos->Insert', 'Crear Información', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (326, 19, 2, 'gestionDocumentos/ventas/listado/pagos/update', 'gestionDocumentosPagos->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (327, 19, 3, 'gestionDocumentos/ventas/listado/pagos', 'gestionDocumentosPagos->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (328, 20, 1, 'gestionDocumentos/informe/busqueda/listado/listAll', 'informeDocumentos->listAll', 'Filtro de búsqueda', 1, 'informeDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (329, 20, 2, 'gestionDocumentos/informe/busqueda/listado/search', 'informeDocumentos->UpdateList', 'Filtrar datos', 1, 'informeDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (330, 20, 1, 'gestionDocumentos/informe/busqueda/listado/view/@id', 'gestionDocumentos->View_0', 'Mostrar Detallado', 1, 'informeDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (331, 20, 1, 'gestionDocumentos/informe/busqueda/listado/print/@id', 'gestionDocumentos->Print_0', 'Pantalla imprimir', 1, 'informeDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (332, 21, 1, 'administracion/sistema/listAll', 'coreSistema->Resumen', 'Mostrar Resúmen', 2, 'coreSistema');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (333, 21, 1, 'administracion/sistema/resumenUpdate', 'coreSistema->ResumenUpdate', 'Mostrar información', 2, 'coreSistema');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (334, 21, 2, 'administracion/sistema/update', 'coreSistema->Update', 'Editar por post (modificar y subir archivos)', 2, 'coreSistema');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (335, 21, 4, 'administracion/sistema/delFiles', 'coreSistema->DelFiles', 'Permite eliminar archivos', 2, 'coreSistema');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (336, 22, 1, 'gestionCampanas/campanas/listado/listAll', 'gestionCampanas->listAll', 'Listar Toda la Información', 1, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (337, 22, 2, 'gestionCampanas/campanas/listado/search', 'gestionCampanas->UpdateList', 'Filtrar datos', 1, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (338, 22, 1, 'gestionCampanas/campanas/listado/updateList', 'gestionCampanas->UpdateList', 'Actualizar Lista', 2, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (339, 22, 1, 'gestionCampanas/campanas/listado/view/@id', 'gestionCampanas->View', 'Mostrar Detallado', 1, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (340, 22, 1, 'gestionCampanas/campanas/listado/print/@id', 'gestionCampanas->Print', 'Pantalla imprimir', 1, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (341, 22, 1, 'gestionCampanas/campanas/listado/noPrint/@id', 'gestionCampanas->noPrint', 'Pantalla para visualizar documento', 1, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (342, 22, 1, 'gestionCampanas/campanas/listado/resumen/@id', 'gestionCampanas->Resumen', 'Mostrar Resúmen', 2, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (343, 22, 1, 'gestionCampanas/campanas/listado/resumenUpdate/@id', 'gestionCampanas->ResumenUpdate', 'Mostrar información', 2, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (344, 22, 2, 'gestionCampanas/campanas/listado', 'gestionCampanas->Insert', 'Crear Información', 3, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (345, 22, 2, 'gestionCampanas/campanas/listado/update', 'gestionCampanas->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (346, 22, 4, 'gestionCampanas/campanas/listado/delFiles', 'gestionCampanas->DelFiles', 'Permite eliminar archivos', 2, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (347, 22, 3, 'gestionCampanas/campanas/listado', 'gestionCampanas->Delete', 'Borrar dato y archivos', 4, 'gestionCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (348, 22, 1, 'gestionCampanas/campanas/listado/costos/new/@id', 'gestionCampanasCostos->New', 'Mostrar modal nuevo', 2, 'gestionCampanasCostos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (349, 22, 1, 'gestionCampanas/campanas/listado/costos/newDoc/@id', 'gestionCampanasCostos->NewDoc', 'Mostrar modal nuevo', 2, 'gestionCampanasCostos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (350, 22, 1, 'gestionCampanas/campanas/listado/costos/updateList/@id', 'gestionCampanasCostos->UpdateList', 'Actualizar Lista', 2, 'gestionCampanasCostos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (351, 22, 1, 'gestionCampanas/campanas/listado/costos/getID/@id', 'gestionCampanasCostos->GetID', 'Información para el formulario edición', 2, 'gestionCampanasCostos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (352, 22, 1, 'gestionCampanas/campanas/listado/costos/view/@id', 'gestionDocumentos->View_1', 'Mostrar Detallado', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (353, 22, 2, 'gestionCampanas/campanas/listado/costos', 'gestionCampanasCostos->Insert', 'Crear Información', 2, 'gestionCampanasCostos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (354, 22, 2, 'gestionCampanas/campanas/listado/costosDoc', 'gestionCampanasCostos->InsertDoc', 'Crear Información', 2, 'gestionCampanasCostos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (355, 22, 2, 'gestionCampanas/campanas/listado/costos/update', 'gestionCampanasCostos->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionCampanasCostos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (356, 22, 3, 'gestionCampanas/campanas/listado/costos', 'gestionCampanasCostos->Delete', 'Borrar dato y archivos', 2, 'gestionCampanasCostos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (357, 22, 1, 'gestionCampanas/campanas/listado/partidas/new_step1/@id', 'gestionCampanasPartidas->New_step1', 'Mostrar primera parte modal nuevo', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (358, 22, 2, 'gestionCampanas/campanas/listado/partidas/new_step1', 'gestionCampanasPartidas->New_step2', 'Mostrar Selección', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (359, 22, 1, 'gestionCampanas/campanas/listado/partidas/updateList/@id', 'gestionCampanasPartidas->UpdateList', 'Actualizar Lista', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (360, 22, 1, 'gestionCampanas/campanas/listado/partidas/getID/@id', 'gestionCampanasPartidas->GetID', 'Información para el formulario edición', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (362, 22, 2, 'gestionCampanas/campanas/listado/partidas', 'gestionCampanasPartidas->Insert', 'Crear Información', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (363, 22, 2, 'gestionCampanas/campanas/listado/partidas/update', 'gestionCampanasPartidas->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (364, 22, 3, 'gestionCampanas/campanas/listado/partidas', 'gestionCampanasPartidas->Delete', 'Borrar dato y archivos', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (368, 22, 2, 'gestionCampanas/campanas/listado/partidas/sendCampanaManual', 'gestionCampanasPartidas->Update', 'Envio Manual Whatsapp', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (369, 22, 1, 'gestionCampanas/campanas/listado/partidasFinalizadas/updateList/@id', 'gestionCampanasPartidas->UpdateListFin', 'Actualizar Lista', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (370, 22, 1, 'gestionCampanas/campanas/listado/partidasFinalizadas/view/@id', 'gestionDocumentos->View_2', 'Mostrar Detallado', 1, 'gestionDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (371, 22, 1, 'gestionCampanas/campanas/listado/perdidas/new/@id', 'gestionCampanasPerdidas->New', 'Mostrar primera parte modal nuevo', 2, 'gestionCampanasPerdidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (372, 22, 1, 'gestionCampanas/campanas/listado/perdidas/updateList/@id', 'gestionCampanasPerdidas->UpdateList', 'Actualizar Lista', 2, 'gestionCampanasPerdidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (373, 22, 1, 'gestionCampanas/campanas/listado/perdidas/getID/@id', 'gestionCampanasPerdidas->GetID', 'Información para el formulario edición', 2, 'gestionCampanasPerdidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (374, 22, 2, 'gestionCampanas/campanas/listado/perdidas', 'gestionCampanasPerdidas->Insert', 'Crear Información', 2, 'gestionCampanasPerdidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (375, 22, 2, 'gestionCampanas/campanas/listado/perdidas/update', 'gestionCampanasPerdidas->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionCampanasPerdidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (376, 22, 3, 'gestionCampanas/campanas/listado/perdidas', 'gestionCampanasPerdidas->Delete', 'Borrar dato y archivos', 2, 'gestionCampanasPerdidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (377, 22, 1, 'gestionCampanas/campanas/listado/perdidas/view/@id', 'bodegasMovimiento->View_2', 'Mostrar Detallado', 1, 'bodegasMovimiento');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (378, 23, 1, 'gestionCampanas/cobranzas/listado/listAll', 'cobranzaCampanas->listAll', 'Filtro de búsqueda', 1, 'cobranzaCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (379, 23, 2, 'gestionCampanas/cobranzas/listado/search', 'cobranzaCampanas->UpdateList', 'Filtrar datos', 1, 'cobranzaCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (380, 24, 1, 'gestionCampanas/informe/exportacionDatos/listAll', 'exportarCampanas->listAll', 'Filtro de búsqueda', 1, 'exportarCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (381, 24, 2, 'gestionCampanas/informe/exportacionDatos/search', 'exportarCampanas->UpdateList', 'Filtrar datos', 1, 'exportarCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (382, 24, 1, 'gestionCampanas/informe/exportacionDatos/print/@id', 'exportarCampanas->Print', 'Pantalla imprimir', 1, 'exportarCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (383, 24, 1, 'gestionCampanas/informe/exportacionDatos/exportExcel/@id', 'exportarCampanas->exportExcel', 'Exportar Excel', 1, 'exportarCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (384, 25, 1, 'gestionCampanas/pagos/listado/listAll', 'pagosCampanas->listAll', 'Filtro de búsqueda', 1, 'pagosCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (385, 25, 2, 'gestionCampanas/pagos/listado/search', 'pagosCampanas->UpdateList', 'Filtrar datos', 1, 'pagosCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (386, 25, 1, 'gestionCampanas/pagos/listado/resumen/@id', 'pagosCampanas->Resumen', 'Mostrar Resúmen', 2, 'pagosCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (387, 25, 1, 'gestionCampanas/pagos/listado/pagos/new/@id', 'pagosCampanas->New_2', 'Mostrar modal nuevo', 2, 'pagosCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (388, 25, 1, 'gestionCampanas/pagos/listado/pagos/updateList/@id', 'pagosCampanas->UpdateList_2', 'Actualizar Lista', 2, 'pagosCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (389, 25, 1, 'gestionCampanas/pagos/listado/pagos/getID/@id', 'pagosCampanas->GetID_2', 'Información para el formulario edición', 2, 'pagosCampanas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (390, 25, 2, 'gestionCampanas/pagos/listado/pagos', 'gestionDocumentosPagos->Insert', 'Crear Información', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (391, 25, 2, 'gestionCampanas/pagos/listado/pagos/update', 'gestionDocumentosPagos->Update', 'Editar por post (modificar y subir archivos)', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (392, 25, 3, 'gestionCampanas/pagos/listado/pagos', 'gestionDocumentosPagos->Delete', 'Borrar dato y archivos', 2, 'gestionDocumentosPagos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (393, 22, 1, 'gestionCampanas/campanas/listado/partidasFinalizadas/getID/@id', 'gestionCampanasPartidas->GetIDFinalizadas', 'Información para el formulario edición', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (394, 22, 2, 'gestionCampanas/campanas/listado/partidasFinalizadas/update', 'gestionCampanasPartidas->UpdateFinalizadas', 'Editar por post (modificar y subir archivos)', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (395, 22, 1, 'gestionCampanas/campanas/listado/partidas/new_unique/@id', 'gestionCampanasPartidas->New_unique', 'Mostrar primera parte modal nuevo', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (396, 22, 2, 'gestionCampanas/campanas/listado/partidas/new_unique', 'gestionCampanasPartidas->InsertUnique', 'Crear Información', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (397, 26, 1, 'serviciosTerceros/entidades/listado/listAll', 'tercerosEntidadesListado->listAll', 'Listar Toda la Información', 1, 'tercerosEntidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (398, 26, 2, 'serviciosTerceros/entidades/listado/search', 'tercerosEntidadesListado->UpdateList', 'Filtrar datos', 1, 'tercerosEntidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (399, 26, 1, 'serviciosTerceros/entidades/listado/updateList', 'tercerosEntidadesListado->UpdateList', 'Actualizar Lista', 2, 'tercerosEntidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (400, 26, 1, 'serviciosTerceros/entidades/listado/view/@id', 'tercerosEntidadesListado->View', 'Mostrar Detallado', 1, 'tercerosEntidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (401, 26, 1, 'serviciosTerceros/entidades/listado/resumen/@id', 'tercerosEntidadesListado->Resumen', 'Mostrar Resúmen', 2, 'tercerosEntidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (402, 26, 1, 'serviciosTerceros/entidades/listado/resumenUpdate/@id', 'tercerosEntidadesListado->ResumenUpdate', 'Mostrar información', 2, 'tercerosEntidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (403, 26, 1, 'serviciosTerceros/entidades/listado/planes/new/@id', 'tercerosEntidadesListadoPlanes->New', 'Mostrar modal nuevo', 2, 'tercerosEntidadesListadoPlanes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (404, 26, 1, 'serviciosTerceros/entidades/listado/planes/updateList/@id', 'tercerosEntidadesListadoPlanes->UpdateList', 'Actualizar Lista', 2, 'tercerosEntidadesListadoPlanes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (405, 26, 1, 'serviciosTerceros/entidades/listado/planes/view/@id', 'tercerosEntidadesListadoPlanes->View', 'Mostrar Detallado', 2, 'tercerosEntidadesListadoPlanes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (406, 26, 1, 'serviciosTerceros/entidades/listado/planes/getID/@id', 'tercerosEntidadesListadoPlanes->GetID', 'Información para el formulario edición', 2, 'tercerosEntidadesListadoPlanes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (407, 26, 2, 'serviciosTerceros/entidades/listado/planes', 'tercerosEntidadesListadoPlanes->Insert', 'Crear Información', 2, 'tercerosEntidadesListadoPlanes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (408, 26, 2, 'serviciosTerceros/entidades/listado/planes/update', 'tercerosEntidadesListadoPlanes->Update', 'Editar por post (modificar y subir archivos)', 2, 'tercerosEntidadesListadoPlanes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (409, 26, 3, 'serviciosTerceros/entidades/listado/planes', 'tercerosEntidadesListadoPlanes->Delete', 'Borrar dato y archivos', 2, 'tercerosEntidadesListadoPlanes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (410, 26, 1, 'serviciosTerceros/entidades/listado/usuarios/new/@id', 'tercerosEntidadesListadoUsuarios->New', 'Mostrar modal nuevo', 2, 'tercerosEntidadesListadoUsuarios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (411, 26, 1, 'serviciosTerceros/entidades/listado/usuarios/updateList/@id', 'tercerosEntidadesListadoUsuarios->UpdateList', 'Actualizar Lista', 2, 'tercerosEntidadesListadoUsuarios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (412, 26, 1, 'serviciosTerceros/entidades/listado/usuarios/view/@id', 'tercerosEntidadesListadoUsuarios->View', 'Mostrar Detallado', 2, 'tercerosEntidadesListadoUsuarios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (413, 26, 1, 'serviciosTerceros/entidades/listado/usuarios/getID/@id', 'tercerosEntidadesListadoUsuarios->GetID', 'Información para el formulario edición', 2, 'tercerosEntidadesListadoUsuarios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (414, 26, 2, 'serviciosTerceros/entidades/listado/usuarios', 'tercerosEntidadesListadoUsuarios->Insert', 'Crear Información', 2, 'tercerosEntidadesListadoUsuarios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (415, 26, 2, 'serviciosTerceros/entidades/listado/usuarios/update', 'tercerosEntidadesListadoUsuarios->Update', 'Editar por post (modificar y subir archivos)', 2, 'tercerosEntidadesListadoUsuarios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (416, 26, 3, 'serviciosTerceros/entidades/listado/usuarios', 'tercerosEntidadesListadoUsuarios->Delete', 'Borrar dato y archivos', 2, 'tercerosEntidadesListadoUsuarios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (417, 26, 2, 'serviciosTerceros/entidades/listado/update', 'tercerosEntidadesListado->Update', 'Editar por post (modificar y subir archivos)', 2, 'tercerosEntidadesListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (418, 27, 1, 'cotizacionListado/ventas/listado/listAll', 'cotizacionListado->listAll', 'Listar Toda la Información', 1, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (419, 27, 2, 'cotizacionListado/ventas/listado/search', 'cotizacionListado->UpdateList', 'Filtrar datos', 1, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (420, 27, 1, 'cotizacionListado/ventas/listado/updateList', 'cotizacionListado->UpdateList', 'Actualizar Lista', 2, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (421, 27, 1, 'cotizacionListado/ventas/listado/view/@id', 'cotizacionListado->View', 'Mostrar Detallado', 1, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (422, 27, 1, 'cotizacionListado/ventas/listado/print/@id', 'cotizacionListado->Print', 'Pantalla imprimir', 1, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (423, 27, 1, 'cotizacionListado/ventas/listado/noPrint/@id', 'cotizacionListado->noPrint', 'Pantalla para visualizar documento', 1, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (424, 27, 1, 'cotizacionListado/ventas/listado/resumen/@id', 'cotizacionListado->Resumen', 'Mostrar Resúmen', 2, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (425, 27, 1, 'cotizacionListado/ventas/listado/resumenUpdate/@id', 'cotizacionListado->ResumenUpdate', 'Mostrar información', 2, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (426, 27, 2, 'cotizacionListado/ventas/listado', 'cotizacionListado->Insert', 'Crear Información', 3, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (427, 27, 2, 'cotizacionListado/ventas/listado/update', 'cotizacionListado->Update', 'Editar por post (modificar y subir archivos)', 2, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (428, 27, 4, 'cotizacionListado/ventas/listado/delFiles', 'cotizacionListado->DelFiles', 'Permite eliminar archivos', 2, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (429, 27, 3, 'cotizacionListado/ventas/listado', 'cotizacionListado->Delete', 'Borrar dato y archivos', 4, 'cotizacionListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (430, 27, 1, 'cotizacionListado/ventas/listado/items/new/@id', 'cotizacionListadoItems->New', 'Mostrar modal nuevo', 2, 'cotizacionListadoItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (431, 27, 1, 'cotizacionListado/ventas/listado/items/updateList/@id', 'cotizacionListadoItems->UpdateList', 'Actualizar Lista', 2, 'cotizacionListadoItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (432, 27, 1, 'cotizacionListado/ventas/listado/items/getID/@id', 'cotizacionListadoItems->GetID', 'Información para el formulario edición', 2, 'cotizacionListadoItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (433, 27, 2, 'cotizacionListado/ventas/listado/items', 'cotizacionListadoItems->Insert', 'Crear Información', 2, 'cotizacionListadoItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (434, 27, 2, 'cotizacionListado/ventas/listado/items/update', 'cotizacionListadoItems->Update', 'Editar por post (modificar y subir archivos)', 2, 'cotizacionListadoItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (435, 27, 3, 'cotizacionListado/ventas/listado/items', 'cotizacionListadoItems->Delete', 'Borrar dato y archivos', 2, 'cotizacionListadoItems');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (436, 27, 1, 'cotizacionListado/ventas/listado/productos/new/@id', 'cotizacionListadoProductos->New', 'Mostrar modal nuevo', 2, 'cotizacionListadoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (437, 27, 1, 'cotizacionListado/ventas/listado/productos/updateList/@id', 'cotizacionListadoProductos->UpdateList', 'Actualizar Lista', 2, 'cotizacionListadoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (438, 27, 1, 'cotizacionListado/ventas/listado/productos/getID/@id', 'cotizacionListadoProductos->GetID', 'Información para el formulario edición', 2, 'cotizacionListadoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (439, 27, 2, 'cotizacionListado/ventas/listado/productos', 'cotizacionListadoProductos->Insert', 'Crear Información', 2, 'cotizacionListadoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (440, 27, 2, 'cotizacionListado/ventas/listado/productos/update', 'cotizacionListadoProductos->Update', 'Editar por post (modificar y subir archivos)', 2, 'cotizacionListadoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (441, 27, 3, 'cotizacionListado/ventas/listado/productos', 'cotizacionListadoProductos->Delete', 'Borrar dato y archivos', 2, 'cotizacionListadoProductos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (442, 27, 1, 'cotizacionListado/ventas/listado/servicios/new/@id', 'cotizacionListadoServicios->New', 'Mostrar modal nuevo', 2, 'cotizacionListadoServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (443, 27, 1, 'cotizacionListado/ventas/listado/servicios/updateList/@id', 'cotizacionListadoServicios->UpdateList', 'Actualizar Lista', 2, 'cotizacionListadoServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (444, 27, 1, 'cotizacionListado/ventas/listado/servicios/getID/@id', 'cotizacionListadoServicios->GetID', 'Información para el formulario edición', 2, 'cotizacionListadoServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (445, 27, 2, 'cotizacionListado/ventas/listado/servicios', 'cotizacionListadoServicios->Insert', 'Crear Información', 2, 'cotizacionListadoServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (446, 27, 2, 'cotizacionListado/ventas/listado/servicios/update', 'cotizacionListadoServicios->Update', 'Editar por post (modificar y subir archivos)', 2, 'cotizacionListadoServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (447, 27, 3, 'cotizacionListado/ventas/listado/servicios', 'cotizacionListadoServicios->Delete', 'Borrar dato y archivos', 2, 'cotizacionListadoServicios');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (448, 28, 1, 'cotizacionListado/informe/busqueda/listado/listAll', 'informeCotizacion->listAll', 'Filtro de búsqueda', 1, 'informeCotizacion');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (449, 28, 2, 'cotizacionListado/informe/busqueda/listado/search', 'informeCotizacion->UpdateList', 'Filtrar datos', 1, 'informeCotizacion');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (450, 28, 1, 'cotizacionListado/informe/busqueda/listado/view/@id', 'cotizacionListado->View', 'Mostrar Detallado', 1, 'informeCotizacion');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (451, 28, 1, 'cotizacionListado/informe/busqueda/listado/print/@id', 'cotizacionListado->Print', 'Pantalla imprimir', 1, 'informeCotizacion');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (452, 22, 3, 'gestionCampanas/campanas/listado/partidas/delMassive', 'gestionCampanasPartidas->DeleteMassive', 'Borra datos de forma masiva', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (453, 29, 1, 'administracion/maquinas/listado/listAll', 'maquinasListado->listAll', 'Listar Toda la Información', 1, 'maquinasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (454, 29, 2, 'administracion/maquinas/listado/search', 'maquinasListado->UpdateList', 'Filtrar datos', 1, 'maquinasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (455, 29, 1, 'administracion/maquinas/listado/updateList', 'maquinasListado->UpdateList', 'Actualizar Lista', 2, 'maquinasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (456, 29, 1, 'administracion/maquinas/listado/view/@id', 'maquinasListado->View', 'Mostrar Detallado', 1, 'maquinasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (457, 29, 1, 'administracion/maquinas/listado/resumen/@id', 'maquinasListado->Resumen', 'Mostrar Resúmen', 2, 'maquinasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (458, 29, 1, 'administracion/maquinas/listado/resumenUpdate/@id', 'maquinasListado->ResumenUpdate', 'Mostrar información', 2, 'maquinasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (459, 29, 2, 'administracion/maquinas/listado', 'maquinasListado->Insert', 'Crear Información', 3, 'maquinasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (460, 29, 2, 'administracion/maquinas/listado/update', 'maquinasListado->Update', 'Editar por post (modificar y subir archivos)', 2, 'maquinasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (461, 29, 4, 'administracion/maquinas/listado/delFiles', 'maquinasListado->DelFiles', 'Permite eliminar archivos', 2, 'maquinasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (462, 29, 3, 'administracion/maquinas/listado', 'maquinasListado->Delete', 'Borrar dato y archivos', 4, 'maquinasListado');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (463, 29, 1, 'administracion/maquinas/listado/observaciones/new/@id', 'maquinasListadoObservaciones->New', 'Mostrar modal nuevo', 2, 'maquinasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (464, 29, 1, 'administracion/maquinas/listado/observaciones/updateList/@id', 'maquinasListadoObservaciones->UpdateList', 'Actualizar Lista', 2, 'maquinasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (465, 29, 1, 'administracion/maquinas/listado/observaciones/view/@id', 'maquinasListadoObservaciones->View', 'Mostrar Detallado', 2, 'maquinasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (466, 29, 1, 'administracion/maquinas/listado/observaciones/getID/@id', 'maquinasListadoObservaciones->GetID', 'Información para el formulario edición', 2, 'maquinasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (467, 29, 2, 'administracion/maquinas/listado/observaciones', 'maquinasListadoObservaciones->Insert', 'Crear Información', 2, 'maquinasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (468, 29, 2, 'administracion/maquinas/listado/observaciones/update', 'maquinasListadoObservaciones->Update', 'Editar por post (modificar y subir archivos)', 2, 'maquinasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (469, 29, 3, 'administracion/maquinas/listado/observaciones', 'maquinasListadoObservaciones->Delete', 'Borrar dato y archivos', 2, 'maquinasListadoObservaciones');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (470, 29, 1, 'administracion/maquinas/listado/documentos/new/@id', 'maquinasListadoDocumentos->New', 'Mostrar modal nuevo', 2, 'maquinasListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (471, 29, 1, 'administracion/maquinas/listado/documentos/updateList/@id', 'maquinasListadoDocumentos->UpdateList', 'Actualizar Lista', 2, 'maquinasListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (472, 29, 1, 'administracion/maquinas/listado/documentos/view/@id', 'maquinasListadoDocumentos->View', 'Mostrar Detallado', 2, 'maquinasListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (473, 29, 1, 'administracion/maquinas/listado/documentos/getID/@id', 'maquinasListadoDocumentos->GetID', 'Información para el formulario edición', 2, 'maquinasListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (474, 29, 2, 'administracion/maquinas/listado/documentos', 'maquinasListadoDocumentos->Insert', 'Crear Información', 2, 'maquinasListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (475, 29, 2, 'administracion/maquinas/listado/documentos/update', 'maquinasListadoDocumentos->Update', 'Editar por post (modificar y subir archivos)', 2, 'maquinasListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (476, 29, 3, 'administracion/maquinas/listado/documentos', 'maquinasListadoDocumentos->Delete', 'Borrar dato y archivos', 2, 'maquinasListadoDocumentos');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (477, 22, 1, 'gestionCampanas/campanas/listado/partidas/sendCampanaMassive/@CampanaID/@Fecha/@PartidaID', 'gestionCampanasPartidas->sendCampanaMassiveForm', 'Informacion para el formulario de Envio', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (478, 22, 2, 'gestionCampanas/campanas/listado/partidas/sendCampanaMassive', 'gestionCampanasPartidas->sendCampanaMassive', 'Envio Masivo Whatsapp', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (479, 22, 1, 'gestionCampanas/campanas/listado/partidas/sendCampanaWhatsapp/@ExistenciaID', 'gestionCampanasPartidas->sendCampanaWhatsappForm', 'Informacion para el formulario de Envio', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (480, 22, 2, 'gestionCampanas/campanas/listado/partidas/sendCampanaWhatsapp', 'gestionCampanasPartidas->sendCampanaWhatsapp', 'Envio Unico Whatsapp', 2, 'gestionCampanasPartidas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (481, 26, 1, 'serviciosTerceros/entidades/listado/maquinas/new/@id', 'tercerosEntidadesListadoMaquinas->New', 'Mostrar modal nuevo', 2, 'tercerosEntidadesListadoMaquinas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (482, 26, 1, 'serviciosTerceros/entidades/listado/maquinas/updateList/@id', 'tercerosEntidadesListadoMaquinas->UpdateList', 'Actualizar Lista', 2, 'tercerosEntidadesListadoMaquinas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (483, 26, 1, 'serviciosTerceros/entidades/listado/maquinas/view/@id', 'tercerosEntidadesListadoMaquinas->View', 'Mostrar Detallado', 2, 'tercerosEntidadesListadoMaquinas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (484, 26, 1, 'serviciosTerceros/entidades/listado/maquinas/getID/@id', 'tercerosEntidadesListadoMaquinas->GetID', 'Información para el formulario edición', 2, 'tercerosEntidadesListadoMaquinas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (485, 26, 2, 'serviciosTerceros/entidades/listado/maquinas', 'tercerosEntidadesListadoMaquinas->Insert', 'Crear Información', 2, 'tercerosEntidadesListadoMaquinas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (486, 26, 2, 'serviciosTerceros/entidades/listado/maquinas/update', 'tercerosEntidadesListadoMaquinas->Update', 'Editar por post (modificar y subir archivos)', 2, 'tercerosEntidadesListadoMaquinas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (487, 26, 3, 'serviciosTerceros/entidades/listado/maquinas', 'tercerosEntidadesListadoMaquinas->Delete', 'Borrar dato y archivos', 2, 'tercerosEntidadesListadoMaquinas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (488, 26, 1, 'serviciosTerceros/entidades/listado/usuariosMaq/updateList/@idEntidad/@idUsuario', 'tercerosEntidadesListadoUsuariosMaq->UpdateList', 'Actualizar Lista', 2, 'tercerosEntidadesListadoUsuariosMaq');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (489, 26, 2, 'serviciosTerceros/entidades/listado/usuariosMaq/update', 'tercerosEntidadesListadoUsuariosMaq->Update', 'Editar por post (modificar y subir archivos)', 2, 'tercerosEntidadesListadoUsuariosMaq');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (490, 26, 1, 'serviciosTerceros/entidades/listado/usuariosNoti/updateList/@idEntidad/@idUsuario', 'tercerosEntidadesListadoUsuariosNoti->UpdateList', 'Actualizar Lista', 2, 'tercerosEntidadesListadoUsuariosNoti');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (491, 26, 2, 'serviciosTerceros/entidades/listado/usuariosNoti/update', 'tercerosEntidadesListadoUsuariosNoti->Update', 'Editar por post (modificar y subir archivos)', 2, 'tercerosEntidadesListadoUsuariosNoti');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (492, 29, 1, 'administracion/maquinas/listado/componentes/new/@id', 'maquinasListadoComponentes->New', 'Mostrar modal nuevo', 2, 'maquinasListadoComponentes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (493, 29, 1, 'administracion/maquinas/listado/componentes/updateList/@id', 'maquinasListadoComponentes->UpdateList', 'Actualizar Lista', 2, 'maquinasListadoComponentes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (494, 29, 1, 'administracion/maquinas/listado/componentes/view/@id', 'maquinasListadoComponentes->View', 'Mostrar Detallado', 2, 'maquinasListadoComponentes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (495, 29, 1, 'administracion/maquinas/listado/componentes/getID/@id', 'maquinasListadoComponentes->GetID', 'Información para el formulario edición', 2, 'maquinasListadoComponentes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (496, 29, 2, 'administracion/maquinas/listado/componentes', 'maquinasListadoComponentes->Insert', 'Crear Información', 2, 'maquinasListadoComponentes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (497, 29, 2, 'administracion/maquinas/listado/componentes/update', 'maquinasListadoComponentes->Update', 'Editar por post (modificar y subir archivos)', 2, 'maquinasListadoComponentes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (498, 29, 3, 'administracion/maquinas/listado/componentes', 'maquinasListadoComponentes->Delete', 'Borrar dato y archivos', 2, 'maquinasListadoComponentes');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (499, 29, 1, 'administracion/maquinas/listado/sensores/new/@id', 'maquinasListadoSensores->New', 'Mostrar modal nuevo', 2, 'maquinasListadoSensores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (500, 29, 1, 'administracion/maquinas/listado/sensores/updateList/@id', 'maquinasListadoSensores->UpdateList', 'Actualizar Lista', 2, 'maquinasListadoSensores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (501, 29, 1, 'administracion/maquinas/listado/sensores/view/@id', 'maquinasListadoSensores->View', 'Mostrar Detallado', 2, 'maquinasListadoSensores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (502, 29, 1, 'administracion/maquinas/listado/sensores/getID/@id', 'maquinasListadoSensores->GetID', 'Información para el formulario edición', 2, 'maquinasListadoSensores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (503, 29, 2, 'administracion/maquinas/listado/sensores', 'maquinasListadoSensores->Insert', 'Crear Información', 2, 'maquinasListadoSensores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (504, 29, 2, 'administracion/maquinas/listado/sensores/update', 'maquinasListadoSensores->Update', 'Editar por post (modificar y subir archivos)', 2, 'maquinasListadoSensores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (505, 29, 3, 'administracion/maquinas/listado/sensores', 'maquinasListadoSensores->Delete', 'Borrar dato y archivos', 2, 'maquinasListadoSensores');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (506, 29, 1, 'administracion/maquinas/listado/alarmas/new/@id', 'maquinasListadoAlarmas->New', 'Mostrar modal nuevo', 2, 'maquinasListadoAlarmas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (507, 29, 1, 'administracion/maquinas/listado/alarmas/updateList/@id', 'maquinasListadoAlarmas->UpdateList', 'Actualizar Lista', 2, 'maquinasListadoAlarmas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (508, 29, 1, 'administracion/maquinas/listado/alarmas/view/@id', 'maquinasListadoAlarmas->View', 'Mostrar Detallado', 2, 'maquinasListadoAlarmas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (509, 29, 1, 'administracion/maquinas/listado/alarmas/getID/@id', 'maquinasListadoAlarmas->GetID', 'Información para el formulario edición', 2, 'maquinasListadoAlarmas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (510, 29, 2, 'administracion/maquinas/listado/alarmas', 'maquinasListadoAlarmas->Insert', 'Crear Información', 2, 'maquinasListadoAlarmas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (511, 29, 2, 'administracion/maquinas/listado/alarmas/update', 'maquinasListadoAlarmas->Update', 'Editar por post (modificar y subir archivos)', 2, 'maquinasListadoAlarmas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (512, 29, 3, 'administracion/maquinas/listado/alarmas', 'maquinasListadoAlarmas->Delete', 'Borrar dato y archivos', 2, 'maquinasListadoAlarmas');
INSERT INTO `core_permisos_listado_rutas` (`idRutas`, `idPermisos`, `idMetodo`, `RutaWeb`, `RutaController`, `Descripcion`, `idLevelLimit`, `Controller`) VALUES (513, 7, 1, 'administracion/entidades/listado/exportar', 'entidadesListado->export', 'Listar Todas las entidades para exportarlas', 3, 'entidadesListado');
COMMIT;

-- ----------------------------
-- Table structure for core_permisos_listado_rutas_metodo
-- ----------------------------
DROP TABLE IF EXISTS `core_permisos_listado_rutas_metodo`;
CREATE TABLE `core_permisos_listado_rutas_metodo` (
  `idMetodo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idMetodo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_permisos_listado_rutas_metodo
-- ----------------------------
BEGIN;
INSERT INTO `core_permisos_listado_rutas_metodo` (`idMetodo`, `Nombre`) VALUES (1, 'GET');
INSERT INTO `core_permisos_listado_rutas_metodo` (`idMetodo`, `Nombre`) VALUES (2, 'POST');
INSERT INTO `core_permisos_listado_rutas_metodo` (`idMetodo`, `Nombre`) VALUES (3, 'DELETE');
INSERT INTO `core_permisos_listado_rutas_metodo` (`idMetodo`, `Nombre`) VALUES (4, 'PUT');
COMMIT;

-- ----------------------------
-- Table structure for core_permisos_listado_tipo
-- ----------------------------
DROP TABLE IF EXISTS `core_permisos_listado_tipo`;
CREATE TABLE `core_permisos_listado_tipo` (
  `idTipo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_permisos_listado_tipo
-- ----------------------------
BEGIN;
INSERT INTO `core_permisos_listado_tipo` (`idTipo`, `Nombre`) VALUES (1, 'Crud Normal');
INSERT INTO `core_permisos_listado_tipo` (`idTipo`, `Nombre`) VALUES (2, 'Crud Resumen');
INSERT INTO `core_permisos_listado_tipo` (`idTipo`, `Nombre`) VALUES (3, 'Informe');
INSERT INTO `core_permisos_listado_tipo` (`idTipo`, `Nombre`) VALUES (4, 'Otros');
COMMIT;

-- ----------------------------
-- Table structure for core_posicion_menu
-- ----------------------------
DROP TABLE IF EXISTS `core_posicion_menu`;
CREATE TABLE `core_posicion_menu` (
  `idMenuPosicion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idMenuPosicion`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_posicion_menu
-- ----------------------------
BEGIN;
INSERT INTO `core_posicion_menu` (`idMenuPosicion`, `Nombre`) VALUES (1, 'Lateral');
INSERT INTO `core_posicion_menu` (`idMenuPosicion`, `Nombre`) VALUES (2, 'Superior');
COMMIT;

-- ----------------------------
-- Table structure for core_prioridades
-- ----------------------------
DROP TABLE IF EXISTS `core_prioridades`;
CREATE TABLE `core_prioridades` (
  `idPrioridad` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Color` varchar(255) NOT NULL,
  PRIMARY KEY (`idPrioridad`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_prioridades
-- ----------------------------
BEGIN;
INSERT INTO `core_prioridades` (`idPrioridad`, `Nombre`, `Color`) VALUES (1, 'Baja', 'bg-primary');
INSERT INTO `core_prioridades` (`idPrioridad`, `Nombre`, `Color`) VALUES (2, 'Normal', 'bg-success');
INSERT INTO `core_prioridades` (`idPrioridad`, `Nombre`, `Color`) VALUES (3, 'Media', 'bg-warning');
INSERT INTO `core_prioridades` (`idPrioridad`, `Nombre`, `Color`) VALUES (4, 'Alta', 'bg-warning');
INSERT INTO `core_prioridades` (`idPrioridad`, `Nombre`, `Color`) VALUES (5, 'Urgente', 'bg-danger');
COMMIT;

-- ----------------------------
-- Table structure for core_sexo
-- ----------------------------
DROP TABLE IF EXISTS `core_sexo`;
CREATE TABLE `core_sexo` (
  `idSexo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Mascota` varchar(120) NOT NULL,
  PRIMARY KEY (`idSexo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_sexo
-- ----------------------------
BEGIN;
INSERT INTO `core_sexo` (`idSexo`, `Nombre`, `Mascota`) VALUES (1, 'Masculino', 'Macho');
INSERT INTO `core_sexo` (`idSexo`, `Nombre`, `Mascota`) VALUES (2, 'Femenino', 'Hembra');
COMMIT;

-- ----------------------------
-- Table structure for core_sistemas
-- ----------------------------
DROP TABLE IF EXISTS `core_sistemas`;
CREATE TABLE `core_sistemas` (
  `idSistema` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Sistema_Nombre` varchar(120) NOT NULL,
  `Sistema_Email` varchar(60) DEFAULT NULL,
  `Sistema_Rut` varchar(13) DEFAULT NULL,
  `Sistema_idCiudad` int(10) unsigned DEFAULT NULL,
  `Sistema_idComuna` int(10) unsigned DEFAULT NULL,
  `Sistema_Direccion` varchar(180) DEFAULT NULL,
  `Sistema_IMGLogo` varchar(250) DEFAULT NULL,
  `Sistema_idTema` int(10) unsigned NOT NULL,
  `Sistema_NotiWhatsapp` varchar(15) DEFAULT NULL,
  `Contacto_Nombre` varchar(120) DEFAULT NULL,
  `Contacto_Fono1` varchar(15) DEFAULT NULL,
  `Contacto_Fono2` varchar(15) DEFAULT NULL,
  `Contacto_Fax` varchar(15) DEFAULT NULL,
  `Contacto_Email` varchar(120) DEFAULT NULL,
  `Contacto_Web` varchar(120) DEFAULT NULL,
  `RepresentanteNombre` varchar(120) DEFAULT NULL,
  `RepresentanteRut` varchar(13) DEFAULT NULL,
  `RepresentanteFono` varchar(120) DEFAULT NULL,
  `RepresentanteEmail` varchar(120) DEFAULT NULL,
  `Config_API_GoogleMaps` varchar(255) DEFAULT NULL,
  `Config_WhatsappToken` varchar(255) DEFAULT NULL,
  `Config_WhatsappInstanceId` varchar(255) DEFAULT NULL,
  `KanbanTareasUsoTareas` int(10) unsigned NOT NULL,
  `KanbanTareasAdminTabIndepend` int(10) unsigned NOT NULL,
  `entidadesListadoVerCargas` int(10) unsigned NOT NULL,
  `entidadesListadoVerContactos` int(10) unsigned NOT NULL,
  `entidadesListadoVerDocumentos` int(10) unsigned NOT NULL,
  `productosListadoVerDocumentos` int(10) unsigned NOT NULL,
  `serviciosListadoVerDocumentos` int(10) unsigned NOT NULL,
  `entidadesListadoUsoPassword` int(10) unsigned NOT NULL,
  `gestionDocumentosUsoBodega` int(10) unsigned NOT NULL,
  `entidadesListadoUsoPlanes` int(10) unsigned NOT NULL,
  `entidadesListadoUsoUsuarios` int(10) unsigned NOT NULL,
  `maquinasListadoVerDocumentos` int(10) unsigned NOT NULL,
  `maquinasListadoComponentes` int(10) unsigned NOT NULL,
  `maquinasListadoTelemetria` int(10) unsigned NOT NULL,
  `maquinasListadoBackups` int(10) unsigned NOT NULL,
  `sistemaModalSubtitle` int(10) unsigned NOT NULL,
  `sistemaModalCloseBTN` int(10) unsigned NOT NULL,
  `entidadesListadoUsoMaquinas` int(10) unsigned NOT NULL,
  `maquinasListadoNotificaciones` int(10) unsigned NOT NULL,
  `idOpcionesGen_20` int(10) unsigned NOT NULL,
  `idOpcionesGen_21` int(10) unsigned NOT NULL,
  `idOpcionesGen_22` int(10) unsigned NOT NULL,
  `idOpcionesGen_23` int(10) unsigned NOT NULL,
  `idOpcionesGen_24` int(10) unsigned NOT NULL,
  `idOpcionesGen_25` int(10) unsigned NOT NULL,
  `idOpcionesGen_26` int(10) unsigned NOT NULL,
  `idOpcionesGen_27` int(10) unsigned NOT NULL,
  `idOpcionesGen_28` int(10) unsigned NOT NULL,
  `idOpcionesGen_29` int(10) unsigned NOT NULL,
  `idOpcionesGen_30` int(10) unsigned NOT NULL,
  `idOpcionesGen_31` int(10) unsigned NOT NULL,
  `idOpcionesGen_32` int(10) unsigned NOT NULL,
  `idOpcionesGen_33` int(10) unsigned NOT NULL,
  `idOpcionesGen_34` int(10) unsigned NOT NULL,
  `idOpcionesGen_35` int(10) unsigned NOT NULL,
  `idOpcionesGen_36` int(10) unsigned NOT NULL,
  `idOpcionesGen_37` int(10) unsigned NOT NULL,
  `idOpcionesGen_38` int(10) unsigned NOT NULL,
  `idOpcionesGen_39` int(10) unsigned NOT NULL,
  `idOpcionesGen_40` int(10) unsigned NOT NULL,
  `Social_X` varchar(255) DEFAULT NULL,
  `Social_Facebook` varchar(255) DEFAULT NULL,
  `Social_Instagram` varchar(255) DEFAULT NULL,
  `Social_Linkedin` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idSistema`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Administrador';

-- ----------------------------
-- Records of core_sistemas
-- ----------------------------
BEGIN;
INSERT INTO `core_sistemas` (`idSistema`, `Sistema_Nombre`, `Sistema_Email`, `Sistema_Rut`, `Sistema_idCiudad`, `Sistema_idComuna`, `Sistema_Direccion`, `Sistema_IMGLogo`, `Sistema_idTema`, `Sistema_NotiWhatsapp`, `Contacto_Nombre`, `Contacto_Fono1`, `Contacto_Fono2`, `Contacto_Fax`, `Contacto_Email`, `Contacto_Web`, `RepresentanteNombre`, `RepresentanteRut`, `RepresentanteFono`, `RepresentanteEmail`, `Config_API_GoogleMaps`, `Config_WhatsappToken`, `Config_WhatsappInstanceId`, `KanbanTareasUsoTareas`, `KanbanTareasAdminTabIndepend`, `entidadesListadoVerCargas`, `entidadesListadoVerContactos`, `entidadesListadoVerDocumentos`, `productosListadoVerDocumentos`, `serviciosListadoVerDocumentos`, `entidadesListadoUsoPassword`, `gestionDocumentosUsoBodega`, `entidadesListadoUsoPlanes`, `entidadesListadoUsoUsuarios`, `maquinasListadoVerDocumentos`, `maquinasListadoComponentes`, `maquinasListadoTelemetria`, `maquinasListadoBackups`, `sistemaModalSubtitle`, `sistemaModalCloseBTN`, `entidadesListadoUsoMaquinas`, `maquinasListadoNotificaciones`, `idOpcionesGen_20`, `idOpcionesGen_21`, `idOpcionesGen_22`, `idOpcionesGen_23`, `idOpcionesGen_24`, `idOpcionesGen_25`, `idOpcionesGen_26`, `idOpcionesGen_27`, `idOpcionesGen_28`, `idOpcionesGen_29`, `idOpcionesGen_30`, `idOpcionesGen_31`, `idOpcionesGen_32`, `idOpcionesGen_33`, `idOpcionesGen_34`, `idOpcionesGen_35`, `idOpcionesGen_36`, `idOpcionesGen_37`, `idOpcionesGen_38`, `idOpcionesGen_39`, `idOpcionesGen_40`, `Social_X`, `Social_Facebook`, `Social_Instagram`, `Social_Linkedin`) VALUES (1, 'Compañia', 'company@demo.cl', '1-9', 13, 100, 'Los Lirios 0936', '', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'tokenaaaaa', 'instanceaaaa', 2, 2, 2, 2, 2, 2, 2, 1, 2, 2, 2, 2, 2, 2, 2, 2, 1, 2, 2, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for core_telemetria_tabs
-- ----------------------------
DROP TABLE IF EXISTS `core_telemetria_tabs`;
CREATE TABLE `core_telemetria_tabs` (
  `idTab` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTab`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_telemetria_tabs
-- ----------------------------
BEGIN;
INSERT INTO `core_telemetria_tabs` (`idTab`, `Nombre`) VALUES (1, '°C');
INSERT INTO `core_telemetria_tabs` (`idTab`, `Nombre`) VALUES (2, 'Agro - Checking');
INSERT INTO `core_telemetria_tabs` (`idTab`, `Nombre`) VALUES (3, 'Agro - Weather');
INSERT INTO `core_telemetria_tabs` (`idTab`, `Nombre`) VALUES (4, 'Power - Crane');
INSERT INTO `core_telemetria_tabs` (`idTab`, `Nombre`) VALUES (5, 'Power - Energy');
COMMIT;

-- ----------------------------
-- Table structure for core_telemetria_tipo_noti
-- ----------------------------
DROP TABLE IF EXISTS `core_telemetria_tipo_noti`;
CREATE TABLE `core_telemetria_tipo_noti` (
  `idTipoNoti` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `idEstado` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idTipoNoti`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_telemetria_tipo_noti
-- ----------------------------
BEGIN;
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (1, 'Alerta temprana - Notificacion Correo - Alertas Catastroficas', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (2, 'Alerta temprana - Notificacion Correo - Alertas Normales', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (3, 'Alerta temprana - Notificacion Correo - Equipo Fuera de Geocerca', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (4, 'Alerta temprana - Notificacion Correo - Exceso Velocidad', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (5, 'Alerta temprana - Notificacion Correo - Fuera de Linea', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (6, 'Alerta temprana - Notificacion Whatsapp - Alertas Catastroficas', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (7, 'Alerta temprana - Notificacion Whatsapp - Alertas Normales', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (8, 'Alerta temprana - Notificacion Whatsapp - Equipo Fuera de Geocerca', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (9, 'Alerta temprana - Notificacion Whatsapp - Exceso Velocidad', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (10, 'Alerta temprana - Notificacion Whatsapp - Fuera de Linea', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (11, 'Crones - Reporte Dia - Notificacion Correo - Alertas Catastroficas', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (12, 'Crones - Reporte Dia - Notificacion Correo - Alertas Normales', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (13, 'Crones - Reporte Dia - Notificacion Correo - Equipo Fuera de Geocerca', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (14, 'Crones - Reporte Dia - Notificacion Correo - Exceso Limite Velocidad', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (15, 'Crones - Reporte Dia - Notificacion Correo - Fuera de Linea', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (16, 'Crones - Reporte Dia - Notificacion Correo - Fuera de Linea Actual', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (17, 'Crones - Reporte Dia - Notificacion Whatsapp - Alertas Catastroficas', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (18, 'Crones - Reporte Dia - Notificacion Whatsapp - Alertas Normales', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (19, 'Crones - Reporte Dia - Notificacion Whatsapp - Equipo Fuera de Geocerca', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (20, 'Crones - Reporte Dia - Notificacion Whatsapp - Exceso Limite Velocidad', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (21, 'Crones - Reporte Dia - Notificacion Whatsapp - Fuera de Linea', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (22, 'Crones - Reporte Dia - Notificacion Whatsapp - Fuera de Linea Actual', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (23, 'Crones - Reporte Hora - Notificacion Correo - Alertas Catastroficas', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (24, 'Crones - Reporte Hora - Notificacion Correo - Alertas Normales', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (25, 'Crones - Reporte Hora - Notificacion Correo - Equipo Fuera de Geocerca', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (26, 'Crones - Reporte Hora - Notificacion Correo - Exceso Limite Velocidad', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (27, 'Crones - Reporte Hora - Notificacion Correo - Fuera de Linea', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (28, 'Crones - Reporte Hora - Notificacion Correo - Fuera de Linea Actual', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (29, 'Crones - Reporte Hora - Notificacion Whatsapp - Alertas Catastroficas', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (30, 'Crones - Reporte Hora - Notificacion Whatsapp - Alertas Normales', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (31, 'Crones - Reporte Hora - Notificacion Whatsapp - Equipo Fuera de Geocerca', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (32, 'Crones - Reporte Hora - Notificacion Whatsapp - Exceso Limite Velocidad', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (33, 'Crones - Reporte Hora - Notificacion Whatsapp - Fuera de Linea', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (34, 'Crones - Reporte Hora - Notificacion Whatsapp - Fuera de Linea Actual', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (35, 'Crones - Reporte Media Hora - Notificacion Correo - Alertas Catastroficas', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (36, 'Crones - Reporte Media Hora - Notificacion Correo - Alertas Normales', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (37, 'Crones - Reporte Media Hora - Notificacion Correo - Equipo Fuera de Geocerca', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (38, 'Crones - Reporte Media Hora - Notificacion Correo - Exceso Limite Velocidad', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (39, 'Crones - Reporte Media Hora - Notificacion Correo - Fuera de Linea', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (40, 'Crones - Reporte Media Hora - Notificacion Correo - Fuera de Linea Actual', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (41, 'Crones - Reporte Media Hora - Notificacion Whatsapp - Alertas Catastroficas', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (42, 'Crones - Reporte Media Hora - Notificacion Whatsapp - Alertas Normales', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (43, 'Crones - Reporte Media Hora - Notificacion Whatsapp - Equipo Fuera de Geocerca', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (44, 'Crones - Reporte Media Hora - Notificacion Whatsapp - Exceso Limite Velocidad', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (45, 'Crones - Reporte Media Hora - Notificacion Whatsapp - Fuera de Linea', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (46, 'Crones - Reporte Media Hora - Notificacion Whatsapp - Fuera de Linea Actual', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (47, 'Crones - Reporte Semana - Notificacion Correo - Alertas Catastroficas', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (48, 'Crones - Reporte Semana - Notificacion Correo - Alertas Normales', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (49, 'Crones - Reporte Semana - Notificacion Correo - Equipo Fuera de Geocerca', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (50, 'Crones - Reporte Semana - Notificacion Correo - Exceso Limite Velocidad', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (51, 'Crones - Reporte Semana - Notificacion Correo - Fuera de Linea', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (52, 'Crones - Reporte Semana - Notificacion Correo - Fuera de Linea Actual', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (53, 'Crones - Reporte Semana - Notificacion Whatsapp - Alertas Catastroficas', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (54, 'Crones - Reporte Semana - Notificacion Whatsapp - Alertas Normales', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (55, 'Crones - Reporte Semana - Notificacion Whatsapp - Equipo Fuera de Geocerca', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (56, 'Crones - Reporte Semana - Notificacion Whatsapp - Exceso Limite Velocidad', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (57, 'Crones - Reporte Semana - Notificacion Whatsapp - Fuera de Linea', 1);
INSERT INTO `core_telemetria_tipo_noti` (`idTipoNoti`, `Nombre`, `idEstado`) VALUES (58, 'Crones - Reporte Semana - Notificacion Whatsapp - Fuera de Linea Actual', 1);
COMMIT;

-- ----------------------------
-- Table structure for core_temas
-- ----------------------------
DROP TABLE IF EXISTS `core_temas`;
CREATE TABLE `core_temas` (
  `idTema` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idTema`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_temas
-- ----------------------------
BEGIN;
INSERT INTO `core_temas` (`idTema`, `Nombre`) VALUES (1, 'Por Defecto');
INSERT INTO `core_temas` (`idTema`, `Nombre`) VALUES (2, 'Plus Admin');
INSERT INTO `core_temas` (`idTema`, `Nombre`) VALUES (3, 'Sneat');
COMMIT;

-- ----------------------------
-- Table structure for core_test
-- ----------------------------
DROP TABLE IF EXISTS `core_test`;
CREATE TABLE `core_test` (
  `idTest` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Email` varchar(255) NOT NULL,
  `Numero` int(10) unsigned NOT NULL,
  `Rut` varchar(25) NOT NULL,
  `Patente` varchar(10) NOT NULL,
  `Fecha` date NOT NULL,
  `Hora` time NOT NULL DEFAULT '00:00:00',
  `Palabra` varchar(255) NOT NULL,
  PRIMARY KEY (`idTest`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Limpiar al entregar';

-- ----------------------------
-- Records of core_test
-- ----------------------------
BEGIN;
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (1, 'asd_10511@asd.cl', 30160, '16029464-7', 'au1825', '2025-03-06', '16:28:28', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (2, 'asd_72602@asd.cl', 48230, '16029464-7', 'au1825', '2025-03-13', '19:00:31', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (3, 'asd_86583@asd.cl', 24312, '16029464-7', 'au1825', '2025-03-13', '19:55:44', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (4, 'asd_94035@asd.cl', 5057, '16029464-7', 'au1825', '2025-03-18', '19:25:20', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (5, 'asd_25301@asd.cl', 45823, '16029464-7', 'au1825', '2025-03-18', '19:29:00', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (6, 'asd_28696@asd.cl', 57637, '16029464-7', 'au1825', '2025-03-18', '19:30:29', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (7, 'asd_83979@asd.cl', 36074, '16029464-7', 'au1825', '2025-03-27', '19:31:43', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (8, 'asd_12750@asd.cl', 67654, '16029464-7', 'au1825', '2025-04-07', '12:08:58', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (9, 'asd_88061@asd.cl', 8526, '16029464-7', 'au1825', '2025-04-08', '11:00:50', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (10, 'asd_54998@asd.cl', 61013, '16029464-7', 'au1825', '2025-04-14', '14:11:30', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (11, 'asd_49571@asd.cl', 34608, '16029464-7', 'au1825', '2025-05-02', '13:47:39', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (12, 'asd_35765@asd.cl', 5481, '16029464-7', 'au1825', '2025-10-14', '15:46:56', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (13, 'asd_57166@asd.cl', 47166, '16029464-7', 'au1825', '2025-10-22', '10:45:34', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (14, 'asd_3605@asd.cl', 82546, '16029464-7', 'au1825', '2025-10-22', '11:42:38', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (15, 'asd_7966@asd.cl', 27732, '16029464-7', 'au1825', '2025-10-22', '11:44:19', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (16, 'asd_25448@asd.cl', 61265, '16029464-7', 'au1825', '2025-10-22', '11:46:51', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (17, 'asd_71290@asd.cl', 25279, '16029464-7', 'au1825', '2025-10-22', '11:47:51', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (18, 'asd_9115@asd.cl', 5080, '16029464-7', 'au1825', '2025-10-22', '11:48:23', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (19, 'asd_73030@asd.cl', 27405, '16029464-7', 'au1825', '2025-10-22', '11:49:14', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (20, 'asd_3232@asd.cl', 62371, '16029464-7', 'au1825', '2025-10-22', '11:49:40', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (21, 'asd_49534@asd.cl', 15178, '16029464-7', 'au1825', '2025-10-22', '11:50:50', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (22, 'asd_84761@asd.cl', 93029, '16029464-7', 'au1825', '2025-10-22', '11:51:50', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (23, 'asd_38318@asd.cl', 73033, '16029464-7', 'au1825', '2025-10-22', '11:52:32', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (24, 'asd_19269@asd.cl', 55448, '16029464-7', 'au1825', '2025-10-22', '11:53:46', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (25, 'asd_73272@asd.cl', 69189, '16029464-7', 'au1825', '2025-10-22', '11:54:10', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (26, 'asd_45917@asd.cl', 43409, '16029464-7', 'au1825', '2025-10-22', '11:54:35', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (27, 'asd_68071@asd.cl', 51735, '16029464-7', 'au1825', '2025-10-22', '11:55:57', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (28, 'asd_376@asd.cl', 30652, '16029464-7', 'au1825', '2025-10-22', '11:57:29', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (29, 'asd_90316@asd.cl', 81262, '16029464-7', 'au1825', '2025-10-22', '11:58:09', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (30, 'asd_48293@asd.cl', 81304, '16029464-7', 'au1825', '2025-10-22', '11:58:37', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (31, 'asd_7877@asd.cl', 30385, '16029464-7', 'au1825', '2025-10-22', '11:59:50', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (32, 'asd_87832@asd.cl', 37785, '16029464-7', 'au1825', '2025-10-24', '12:14:44', 'test');
INSERT INTO `core_test` (`idTest`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`) VALUES (33, 'asd_82646@asd.cl', 54240, '16029464-7', 'au1825', '2025-10-25', '22:14:56', 'test');
COMMIT;

-- ----------------------------
-- Table structure for core_test_calendario
-- ----------------------------
DROP TABLE IF EXISTS `core_test_calendario`;
CREATE TABLE `core_test_calendario` (
  `idCalendario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Titulo` varchar(255) NOT NULL,
  `fechaInicio` date NOT NULL,
  `fechaTermino` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`idCalendario`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Limpiar al entregar';

-- ----------------------------
-- Records of core_test_calendario
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for core_test_crud
-- ----------------------------
DROP TABLE IF EXISTS `core_test_crud`;
CREATE TABLE `core_test_crud` (
  `idCrud` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idUsuario` int(10) unsigned NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Numero` int(10) unsigned NOT NULL,
  `Rut` varchar(25) NOT NULL,
  `Patente` varchar(10) NOT NULL,
  `Fecha` date NOT NULL,
  `Hora` time NOT NULL DEFAULT '00:00:00',
  `Palabra` varchar(255) NOT NULL,
  `Direccion_img` varchar(255) DEFAULT NULL,
  `File` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idCrud`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Limpiar al entregar';

-- ----------------------------
-- Records of core_test_crud
-- ----------------------------
BEGIN;
INSERT INTO `core_test_crud` (`idCrud`, `idUsuario`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`, `Direccion_img`, `File`) VALUES (1, 1, 'asd1@asd.cl', 1, '1-9', 'aass12', '2025-04-01', '11:30:00', 'asd', 'ResumenIMG_1744899071.png', '');
INSERT INTO `core_test_crud` (`idCrud`, `idUsuario`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`, `Direccion_img`, `File`) VALUES (3, 1, 'qwe@asd.cl', 1, '16.029.464-7', 'aass23', '2025-04-08', '11:02:00', 'dfg', NULL, NULL);
INSERT INTO `core_test_crud` (`idCrud`, `idUsuario`, `Email`, `Numero`, `Rut`, `Patente`, `Fecha`, `Hora`, `Palabra`, `Direccion_img`, `File`) VALUES (4, 1, 'zxc@asd.cl', 1, '5.457.744-3', 'ggrr22', '2025-04-08', '11:52:00', 'dfdfdf', 'ResumenIMG_1744127583.png', 'ResumenFile_pdf file.pdf');
COMMIT;

-- ----------------------------
-- Table structure for core_test_crud_observaciones
-- ----------------------------
DROP TABLE IF EXISTS `core_test_crud_observaciones`;
CREATE TABLE `core_test_crud_observaciones` (
  `idObservaciones` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idCrud` int(10) unsigned NOT NULL,
  `Observacion` text NOT NULL,
  `FechaCreacion` date NOT NULL,
  PRIMARY KEY (`idObservaciones`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Limpiar al entregar';

-- ----------------------------
-- Records of core_test_crud_observaciones
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for core_tiempo_dias
-- ----------------------------
DROP TABLE IF EXISTS `core_tiempo_dias`;
CREATE TABLE `core_tiempo_dias` (
  `idDia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idDia`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tiempo_dias
-- ----------------------------
BEGIN;
INSERT INTO `core_tiempo_dias` (`idDia`, `Nombre`) VALUES (1, 'Lunes');
INSERT INTO `core_tiempo_dias` (`idDia`, `Nombre`) VALUES (2, 'Martes');
INSERT INTO `core_tiempo_dias` (`idDia`, `Nombre`) VALUES (3, 'Miercoles');
INSERT INTO `core_tiempo_dias` (`idDia`, `Nombre`) VALUES (4, 'Jueves');
INSERT INTO `core_tiempo_dias` (`idDia`, `Nombre`) VALUES (5, 'Viernes');
INSERT INTO `core_tiempo_dias` (`idDia`, `Nombre`) VALUES (6, 'Sabado');
INSERT INTO `core_tiempo_dias` (`idDia`, `Nombre`) VALUES (7, 'Domingo');
COMMIT;

-- ----------------------------
-- Table structure for core_tiempo_frecuencia
-- ----------------------------
DROP TABLE IF EXISTS `core_tiempo_frecuencia`;
CREATE TABLE `core_tiempo_frecuencia` (
  `idFrecuencia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idFrecuencia`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tiempo_frecuencia
-- ----------------------------
BEGIN;
INSERT INTO `core_tiempo_frecuencia` (`idFrecuencia`, `Nombre`) VALUES (1, 'Horas');
INSERT INTO `core_tiempo_frecuencia` (`idFrecuencia`, `Nombre`) VALUES (2, 'Dias');
INSERT INTO `core_tiempo_frecuencia` (`idFrecuencia`, `Nombre`) VALUES (3, 'Semanas');
INSERT INTO `core_tiempo_frecuencia` (`idFrecuencia`, `Nombre`) VALUES (4, 'Meses');
INSERT INTO `core_tiempo_frecuencia` (`idFrecuencia`, `Nombre`) VALUES (5, 'Años');
COMMIT;

-- ----------------------------
-- Table structure for core_tiempo_meses
-- ----------------------------
DROP TABLE IF EXISTS `core_tiempo_meses`;
CREATE TABLE `core_tiempo_meses` (
  `idMes` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idMes`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tiempo_meses
-- ----------------------------
BEGIN;
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (1, 'Enero');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (2, 'Febrero');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (3, 'Marzo');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (4, 'Abril');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (5, 'Mayo');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (6, 'Junio');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (7, 'Julio');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (8, 'Agosto');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (9, 'Septiembre');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (10, 'Octubre');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (11, 'Noviembre');
INSERT INTO `core_tiempo_meses` (`idMes`, `Nombre`) VALUES (12, 'Diciembre');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_contactos
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_contactos`;
CREATE TABLE `core_tipos_contactos` (
  `idTipoContacto` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoContacto`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_contactos
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (4, 'Abogado del propietario');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (5, 'Analista de Inversiones');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (6, 'Analista de proyectos');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (7, 'Arquitecto');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (8, 'Asistente Legal');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (9, 'Director Ejecutivo');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (10, 'Encargada de Gestión y Venta Inmobiliaria');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (11, 'Encargado de Operaciones Habitacionales');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (12, 'Gerente Comercial');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (13, 'Gerente Corporativo de Estrategia');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (14, 'Gerente de Administración y Finanzas');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (15, 'Gerente de Compra de Terrenos');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (16, 'Gerente de desarrollo');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (17, 'Gerente de Nuevos Negocios');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (18, 'Gerente de Operaciones');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (19, 'Gerente de Proyecto');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (20, 'Gerente de Ventas');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (21, 'Gerente Finanzas');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (22, 'Gerente General');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (23, 'Gerente Inmobiliario');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (24, 'Gerente Nuevos Negocios');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (25, 'Gerente Técnico');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (26, 'Ingeniera de Proyecto');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (27, 'Ingeniero de Estudios Inmobiliarios');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (28, 'Jefe de Desarrollo');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (29, 'Jefe de Estudios');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (30, 'Jefe de Gestión Inmobiliaria');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (31, 'Jefe de Negocios Inmobiliarios');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (32, 'Jefe de Proyectos Inmobiliarios');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (33, 'Jefe de Ventas Inmobiliaria VI Región');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (34, 'Jefe Nuevos Negocios');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (35, 'Representante comercial de la empresa');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (36, 'Representante del propietario');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (37, 'Representante legal de la empresa');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (38, 'Representante técnico de la empresa');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (39, 'Secretaria');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (40, 'Sub Gerente Comercial');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (41, 'Sub Gerente de Estudios');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (42, 'Sub Gerente de Gestión Inmobiliaria');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (43, 'Sub Gerente de Negocios Inmobiliarios');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (44, 'Sub Gerente de Proyecto');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (45, 'Sub Gerente Desarrollo Inmobiliario');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (46, 'Sub Gerente Técnico');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (47, 'Asistente Administrativo');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (48, 'Corredor Externo');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (49, 'CEO & Cofounder');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (50, 'Gerente de Estudios y Adquisiciones');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (51, 'Gerente Desarrollos Proyectos Comerciales');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (52, 'Asistente de Gerencia');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (53, 'Constructor Civil');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (54, 'Gerente General Fai Sur');
INSERT INTO `core_tipos_contactos` (`idTipoContacto`, `Nombre`) VALUES (55, 'Secretaria de Gerencia');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_cuentabancaria
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_cuentabancaria`;
CREATE TABLE `core_tipos_cuentabancaria` (
  `idTipoCuenta` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoCuenta`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_cuentabancaria
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_cuentabancaria` (`idTipoCuenta`, `Nombre`) VALUES (1, 'Cuenta Corriente');
INSERT INTO `core_tipos_cuentabancaria` (`idTipoCuenta`, `Nombre`) VALUES (2, 'Cuenta Vista');
INSERT INTO `core_tipos_cuentabancaria` (`idTipoCuenta`, `Nombre`) VALUES (3, 'Cuenta Rut');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_entidad
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_entidad`;
CREATE TABLE `core_tipos_entidad` (
  `idTipo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_entidad
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_entidad` (`idTipo`, `Nombre`) VALUES (1, 'Proveedores');
INSERT INTO `core_tipos_entidad` (`idTipo`, `Nombre`) VALUES (2, 'Clientes');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_entidades
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_entidades`;
CREATE TABLE `core_tipos_entidades` (
  `idTipoEntidad` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoEntidad`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_entidades
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_entidades` (`idTipoEntidad`, `Nombre`) VALUES (1, 'Persona Natural');
INSERT INTO `core_tipos_entidades` (`idTipoEntidad`, `Nombre`) VALUES (2, 'Empresas');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_producto
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_producto`;
CREATE TABLE `core_tipos_producto` (
  `idTipoProducto` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoProducto`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_producto
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_producto` (`idTipoProducto`, `Nombre`) VALUES (1, 'No Aplica');
INSERT INTO `core_tipos_producto` (`idTipoProducto`, `Nombre`) VALUES (2, 'Insumos');
INSERT INTO `core_tipos_producto` (`idTipoProducto`, `Nombre`) VALUES (3, 'Productos');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_queja
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_queja`;
CREATE TABLE `core_tipos_queja` (
  `idTipoQueja` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoQueja`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_queja
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_queja` (`idTipoQueja`, `Nombre`) VALUES (1, 'Servicio no ejecutado');
INSERT INTO `core_tipos_queja` (`idTipoQueja`, `Nombre`) VALUES (2, 'Servicio defectuoso');
INSERT INTO `core_tipos_queja` (`idTipoQueja`, `Nombre`) VALUES (3, 'Servicio mal prestado');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_queja_general
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_queja_general`;
CREATE TABLE `core_tipos_queja_general` (
  `idTipoQueja` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoQueja`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_queja_general
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_queja_general` (`idTipoQueja`, `Nombre`) VALUES (1, 'Trabajo');
INSERT INTO `core_tipos_queja_general` (`idTipoQueja`, `Nombre`) VALUES (2, 'Personal');
INSERT INTO `core_tipos_queja_general` (`idTipoQueja`, `Nombre`) VALUES (3, 'Servicio');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_recepcionpaquete
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_recepcionpaquete`;
CREATE TABLE `core_tipos_recepcionpaquete` (
  `idTipo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_recepcionpaquete
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_recepcionpaquete` (`idTipo`, `Nombre`) VALUES (1, 'Carta');
INSERT INTO `core_tipos_recepcionpaquete` (`idTipo`, `Nombre`) VALUES (2, 'Sobre');
INSERT INTO `core_tipos_recepcionpaquete` (`idTipo`, `Nombre`) VALUES (3, 'Paquete');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_rrhh_bonos
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_rrhh_bonos`;
CREATE TABLE `core_tipos_rrhh_bonos` (
  `idTipo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_rrhh_bonos
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_rrhh_bonos` (`idTipo`, `Nombre`) VALUES (1, 'Afecto a descuentos');
INSERT INTO `core_tipos_rrhh_bonos` (`idTipo`, `Nombre`) VALUES (2, 'No afecto a descuentos');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_rrhh_contrato
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_rrhh_contrato`;
CREATE TABLE `core_tipos_rrhh_contrato` (
  `idTipoContrato` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoContrato`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_rrhh_contrato
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_rrhh_contrato` (`idTipoContrato`, `Nombre`) VALUES (1, 'Contrato a plazo fijo');
INSERT INTO `core_tipos_rrhh_contrato` (`idTipoContrato`, `Nombre`) VALUES (2, 'Contrato a plazo indefinido');
INSERT INTO `core_tipos_rrhh_contrato` (`idTipoContrato`, `Nombre`) VALUES (3, 'Por faena');
INSERT INTO `core_tipos_rrhh_contrato` (`idTipoContrato`, `Nombre`) VALUES (4, 'Honorarios');
INSERT INTO `core_tipos_rrhh_contrato` (`idTipoContrato`, `Nombre`) VALUES (5, 'Part time');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_rrhh_contrato_trabajador
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_rrhh_contrato_trabajador`;
CREATE TABLE `core_tipos_rrhh_contrato_trabajador` (
  `idTipoContratoTrab` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoContratoTrab`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_rrhh_contrato_trabajador
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_rrhh_contrato_trabajador` (`idTipoContratoTrab`, `Nombre`) VALUES (1, 'Trabajador con sueldo mensual');
INSERT INTO `core_tipos_rrhh_contrato_trabajador` (`idTipoContratoTrab`, `Nombre`) VALUES (2, 'Trabajador con sueldo semanal');
INSERT INTO `core_tipos_rrhh_contrato_trabajador` (`idTipoContratoTrab`, `Nombre`) VALUES (3, 'Trabajador con sueldo diario (jornada semanal de 5 días)');
INSERT INTO `core_tipos_rrhh_contrato_trabajador` (`idTipoContratoTrab`, `Nombre`) VALUES (4, 'Trabajador con sueldo diario (jornada semanal de 6 días)');
INSERT INTO `core_tipos_rrhh_contrato_trabajador` (`idTipoContratoTrab`, `Nombre`) VALUES (5, 'Trabajador con sueldo por hora');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_rrhh_licencia_conducir
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_rrhh_licencia_conducir`;
CREATE TABLE `core_tipos_rrhh_licencia_conducir` (
  `idTipoLicencia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoLicencia`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_rrhh_licencia_conducir
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_rrhh_licencia_conducir` (`idTipoLicencia`, `Nombre`) VALUES (1, 'A-1');
INSERT INTO `core_tipos_rrhh_licencia_conducir` (`idTipoLicencia`, `Nombre`) VALUES (2, 'A-2');
INSERT INTO `core_tipos_rrhh_licencia_conducir` (`idTipoLicencia`, `Nombre`) VALUES (3, 'A-3');
INSERT INTO `core_tipos_rrhh_licencia_conducir` (`idTipoLicencia`, `Nombre`) VALUES (4, 'A-4');
INSERT INTO `core_tipos_rrhh_licencia_conducir` (`idTipoLicencia`, `Nombre`) VALUES (5, 'A-5');
INSERT INTO `core_tipos_rrhh_licencia_conducir` (`idTipoLicencia`, `Nombre`) VALUES (6, 'B');
INSERT INTO `core_tipos_rrhh_licencia_conducir` (`idTipoLicencia`, `Nombre`) VALUES (7, 'C');
INSERT INTO `core_tipos_rrhh_licencia_conducir` (`idTipoLicencia`, `Nombre`) VALUES (8, 'D');
INSERT INTO `core_tipos_rrhh_licencia_conducir` (`idTipoLicencia`, `Nombre`) VALUES (9, 'E');
INSERT INTO `core_tipos_rrhh_licencia_conducir` (`idTipoLicencia`, `Nombre`) VALUES (10, 'F');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_rrhh_trabajadores
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_rrhh_trabajadores`;
CREATE TABLE `core_tipos_rrhh_trabajadores` (
  `idTipoTrabajador` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoTrabajador`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_rrhh_trabajadores
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_rrhh_trabajadores` (`idTipoTrabajador`, `Nombre`) VALUES (1, 'Dependiente');
INSERT INTO `core_tipos_rrhh_trabajadores` (`idTipoTrabajador`, `Nombre`) VALUES (2, 'Independiente');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_rrhh_trabajadores_cargas_parentesco
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_rrhh_trabajadores_cargas_parentesco`;
CREATE TABLE `core_tipos_rrhh_trabajadores_cargas_parentesco` (
  `idParentesco` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idParentesco`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_rrhh_trabajadores_cargas_parentesco
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_rrhh_trabajadores_cargas_parentesco` (`idParentesco`, `Nombre`) VALUES (1, 'Padre / Madre');
INSERT INTO `core_tipos_rrhh_trabajadores_cargas_parentesco` (`idParentesco`, `Nombre`) VALUES (2, 'Cónyuge');
INSERT INTO `core_tipos_rrhh_trabajadores_cargas_parentesco` (`idParentesco`, `Nombre`) VALUES (3, 'Hijo/a');
INSERT INTO `core_tipos_rrhh_trabajadores_cargas_parentesco` (`idParentesco`, `Nombre`) VALUES (4, 'Nieto/a');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_rrhh_trabajadores_tipos
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_rrhh_trabajadores_tipos`;
CREATE TABLE `core_tipos_rrhh_trabajadores_tipos` (
  `idTipo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_rrhh_trabajadores_tipos
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_rrhh_trabajadores_tipos` (`idTipo`, `Nombre`) VALUES (1, 'Administrativo');
INSERT INTO `core_tipos_rrhh_trabajadores_tipos` (`idTipo`, `Nombre`) VALUES (2, 'Administrativo Capacitación');
INSERT INTO `core_tipos_rrhh_trabajadores_tipos` (`idTipo`, `Nombre`) VALUES (3, 'Administrativo Docente');
INSERT INTO `core_tipos_rrhh_trabajadores_tipos` (`idTipo`, `Nombre`) VALUES (4, 'Docente');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_rrhh_trabajo
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_rrhh_trabajo`;
CREATE TABLE `core_tipos_rrhh_trabajo` (
  `idTipoTrabajo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoTrabajo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_rrhh_trabajo
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_rrhh_trabajo` (`idTipoTrabajo`, `Nombre`) VALUES (1, 'Normal');
INSERT INTO `core_tipos_rrhh_trabajo` (`idTipoTrabajo`, `Nombre`) VALUES (2, 'Pesado');
COMMIT;

-- ----------------------------
-- Table structure for core_tipos_usuario
-- ----------------------------
DROP TABLE IF EXISTS `core_tipos_usuario`;
CREATE TABLE `core_tipos_usuario` (
  `idTipoUsuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`idTipoUsuario`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_tipos_usuario
-- ----------------------------
BEGIN;
INSERT INTO `core_tipos_usuario` (`idTipoUsuario`, `Nombre`) VALUES (1, 'SuperAdministrador');
INSERT INTO `core_tipos_usuario` (`idTipoUsuario`, `Nombre`) VALUES (2, 'Administrador');
INSERT INTO `core_tipos_usuario` (`idTipoUsuario`, `Nombre`) VALUES (3, 'Operaciones');
COMMIT;

-- ----------------------------
-- Table structure for core_ubicacion_ciudad
-- ----------------------------
DROP TABLE IF EXISTS `core_ubicacion_ciudad`;
CREATE TABLE `core_ubicacion_ciudad` (
  `idCiudad` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Wheater` varchar(255) NOT NULL,
  PRIMARY KEY (`idCiudad`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_ubicacion_ciudad
-- ----------------------------
BEGIN;
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (1, 'Región de Tarapacá', 'https://forecast7.com/en/n20d20n69d29/tarapaca/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (2, 'Región de Antofagasta', 'https://forecast7.com/en/n23d84n69d29/antofagasta-region/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (3, 'Región de Atacama', 'https://forecast7.com/en/n27d57n70d05/atacama-region/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (4, 'Región de Coquimbo', 'https://forecast7.com/en/n30d54n70d81/coquimbo/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (5, 'Región de Valparaiso', 'https://forecast7.com/en/n32d50n71d00/valparaiso-region/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (6, 'Región del Libertador General Bernardo O Higgins', 'https://forecast7.com/en/n34d58n71d00/ohiggins-region/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (7, 'Región del Maule', 'https://forecast7.com/en/n35d52n71d57/maule-region/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (8, 'Región del Bío-Bío', 'https://forecast7.com/en/n36d98n72d33/bio-bio-region/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (9, 'Región de la Araucanía', 'https://forecast7.com/en/n38d95n72d33/araucania/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (10, 'Región de Los Lagos', 'https://forecast7.com/en/n41d92n72d14/los-lagos/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (11, 'Región de Aysén del General Carlos Ibáñez del Campo', 'https://forecast7.com/en/n46d38n72d30/aysen-region/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (12, 'Región de Magallanes y la Antártica Chilena', 'https://forecast7.com/en/n52d21n72d17/magallanes-and-chilean-antarctica/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (13, 'Región Metropolitana', 'https://forecast7.com/en/n33d44n70d65/santiago-metropolitan-region/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (14, 'Región de Los Ríos', 'https://forecast7.com/en/n40d23n72d33/los-rios/');
INSERT INTO `core_ubicacion_ciudad` (`idCiudad`, `Nombre`, `Wheater`) VALUES (15, 'Región de Arica y Parinacota', 'https://forecast7.com/en/n18d59n69d48/arica-y-parinacota-region/');
COMMIT;

-- ----------------------------
-- Table structure for core_ubicacion_comunas
-- ----------------------------
DROP TABLE IF EXISTS `core_ubicacion_comunas`;
CREATE TABLE `core_ubicacion_comunas` (
  `idComuna` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idCiudad` int(10) unsigned NOT NULL,
  `Nombre` varchar(120) NOT NULL,
  `Wheater` varchar(220) NOT NULL,
  PRIMARY KEY (`idComuna`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=354 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_ubicacion_comunas
-- ----------------------------
BEGIN;
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (1, 15, 'Arica', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (2, 1, 'Iquique', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (3, 1, 'Huara', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (4, 1, 'Pica', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (5, 1, 'Pozo almonte', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (6, 2, 'Tocopilla', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (7, 2, 'Antofagasta', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (8, 2, 'Mejillones', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (9, 2, 'Taltal', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (10, 2, 'Calama', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (11, 3, 'ChaÑaral', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (12, 3, 'Diego de almagro', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (13, 3, 'Copiapo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (14, 3, 'Caldera', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (15, 3, 'Tierra amarilla', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (16, 3, 'Vallenar', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (17, 3, 'Freirina', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (18, 3, 'Huasco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (19, 4, 'La serena', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (20, 4, 'La higuera', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (21, 4, 'Coquimbo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (22, 4, 'Andacollo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (23, 4, 'VicuÑa', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (24, 4, 'Paihuano', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (25, 4, 'Ovalle', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (26, 4, 'Monte patria', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (27, 4, 'Punitaqui', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (28, 4, 'Rio hurtado', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (29, 4, 'Combarbala', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (30, 4, 'Illapel', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (31, 4, 'Canela', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (32, 4, 'Salamanca', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (33, 4, 'Los vilos', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (34, 5, 'Valparaiso', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (35, 5, 'Quintero', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (36, 5, 'Puchuncavi', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (37, 5, 'ViÑa del mar', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (38, 5, 'Quilpue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (39, 5, 'Villa alemana', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (40, 5, 'Casablanca', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (41, 5, 'Isla de pascua', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (42, 5, 'San antonio', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (43, 5, 'Santo domingo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (44, 5, 'Algarrobo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (45, 5, 'El quisco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (46, 5, 'Cartagena', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (47, 5, 'El tabo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (48, 5, 'Quillota', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (49, 5, 'La cruz', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (50, 5, 'La calera', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (51, 5, 'Hijuelas', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (52, 5, 'Nogales', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (53, 5, 'Limache', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (54, 5, 'Olmue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (55, 5, 'Petorca', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (56, 5, 'Cabildo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (57, 5, 'Papudo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (58, 5, 'Zapallar', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (59, 5, 'La ligua', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (60, 5, 'San felipe', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (61, 5, 'Putaendo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (62, 5, 'Panquehue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (63, 5, 'Catemu', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (64, 5, 'Santa maria', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (65, 5, 'Llay llay', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (66, 5, 'Los andes', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (67, 5, 'Calle larga', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (68, 5, 'Rinconada', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (69, 5, 'San esteban', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (70, 13, 'Santiago centro', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (71, 13, 'Las condes', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (72, 13, 'Providencia', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (73, 13, 'Santiago oeste', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (75, 13, 'Conchali', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (76, 13, 'Colina', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (77, 13, 'Renca', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (78, 13, 'Lampa', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (79, 13, 'Quilicura', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (80, 13, 'Til-til', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (81, 13, 'Quinta normal', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (82, 13, 'Pudahuel', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (83, 13, 'Curacavi', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (84, 13, 'Santiago sur', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (85, 13, 'PeÑaflor', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (86, 13, 'Talagante', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (87, 13, 'Isla de maipo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (88, 13, 'Melipilla', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (89, 13, 'El monte', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (90, 13, 'Maria pinto', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (91, 13, 'ÑuÑoa', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (92, 13, 'La reina', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (93, 13, 'La florida', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (94, 13, 'Maipu', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (95, 13, 'San miguel', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (96, 13, 'La cisterna', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (97, 13, 'La granja', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (98, 13, 'San bernardo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (99, 13, 'Calera de tango', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (100, 13, 'Puente alto', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (101, 13, 'Pirque', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (102, 13, 'San jose de maipo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (103, 13, 'Buin', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (104, 13, 'Paine', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (105, 6, 'Rancagua', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (106, 6, 'Machali', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (107, 6, 'Graneros', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (108, 13, 'San pedro', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (109, 13, 'Alhue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (110, 6, 'Codegua', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (111, 6, 'San francisco de mostazal', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (112, 6, 'DoÑihue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (113, 6, 'Coltauco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (114, 6, 'Coinco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (115, 6, 'Peumo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (116, 6, 'Las cabras', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (117, 6, 'San vicente', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (118, 6, 'Pichidegua', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (119, 6, 'Requinoa', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (120, 6, 'Olivar', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (121, 6, 'Rengo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (122, 6, 'Malloa', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (123, 6, 'Quinta de tilcoco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (124, 6, 'San fernando', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (125, 6, 'Chimbarongo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (126, 6, 'Nancagua', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (127, 6, 'Placilla', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (128, 6, 'Santa cruz', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (129, 6, 'Lolol', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (130, 6, 'Palmilla', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (131, 6, 'Peralillo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (132, 6, 'Chepica', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (133, 6, 'Paredones', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (134, 6, 'Marchigue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (135, 6, 'Pumanque', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (136, 6, 'Litueche', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (137, 6, 'Pichilemu', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (138, 6, 'Navidad', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (139, 6, 'La estrella', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (140, 7, 'Curico', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (141, 7, 'Romeral', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (142, 7, 'Teno', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (143, 7, 'Rauco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (144, 7, 'HualaÑe', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (145, 7, 'Licanten', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (146, 7, 'Vichuquen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (147, 7, 'Molina', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (148, 7, 'Sagrada familia', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (149, 7, 'Rio claro', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (150, 7, 'Talca', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (151, 7, 'San clemente', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (152, 7, 'Pelarco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (153, 7, 'Pencahue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (154, 7, 'Maule', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (155, 7, 'Curepto', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (156, 7, 'San javier', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (157, 7, 'Constitucion', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (158, 7, 'Empedrado', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (159, 7, 'Linares', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (160, 7, 'Yerbas buenas', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (161, 7, 'Colbun', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (162, 7, 'Longavi', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (163, 7, 'Villa alegre', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (164, 7, 'Parral', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (165, 7, 'Retiro', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (166, 7, 'Cauquenes', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (167, 7, 'Chanco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (168, 8, 'Chillan', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (169, 8, 'Pinto', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (170, 8, 'Coihueco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (171, 8, 'Portezuelo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (172, 8, 'Quirihue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (173, 8, 'Trehuaco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (174, 8, 'Ninhue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (175, 8, 'Cobquecura', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (176, 8, 'San carlos', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (177, 8, 'San gregorio de Ñiquen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (178, 8, 'San fabian', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (179, 8, 'San nicolas', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (180, 8, 'Bulnes', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (181, 8, 'San ignacio', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (182, 8, 'Quillon', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (183, 8, 'Yungay', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (184, 8, 'Pemuco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (185, 8, 'El carmen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (186, 8, 'Coelemu', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (187, 8, 'Ranquil', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (188, 8, 'Concepcion', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (189, 8, 'Talcahuano', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (190, 8, 'Tome', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (191, 8, 'Penco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (192, 8, 'Hualqui', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (193, 8, 'Florida', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (194, 8, 'Coronel', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (195, 8, 'Lota', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (196, 8, 'Santa juana', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (197, 8, 'Curanilahue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (198, 8, 'Arauco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (199, 8, 'Lebu', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (200, 8, 'Los alamos', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (201, 8, 'CaÑete', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (202, 8, 'Contulmo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (203, 8, 'Tirua', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (204, 8, 'Los angeles', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (205, 8, 'Santa barbara', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (206, 8, 'Quilleco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (207, 8, 'Yumbel', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (208, 8, 'Cabrero', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (209, 8, 'Tucapel', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (210, 8, 'Laja', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (211, 8, 'San rosendo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (212, 8, 'Nacimiento', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (213, 8, 'Negrete', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (214, 8, 'Mulchen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (215, 8, 'Quilaco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (216, 9, 'Angol', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (217, 9, 'Puren', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (218, 9, 'Los sauces', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (219, 9, 'Renaico', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (220, 9, 'Collipulli', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (221, 9, 'Ercilla', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (222, 9, 'Traiguen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (223, 9, 'Lumaco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (224, 9, 'Victoria', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (225, 9, 'Curacautin', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (226, 9, 'Lonquimay', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (227, 9, 'Temuco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (228, 9, 'Vilcun', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (229, 9, 'Freire', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (230, 9, 'Cunco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (231, 9, 'Lautaro', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (232, 9, 'Galvarino', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (233, 9, 'Perquenco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (234, 9, 'Nueva imperial', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (235, 9, 'Carahue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (236, 9, 'Puerto saavedra', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (237, 9, 'Pitrufquen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (238, 9, 'Gorbea', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (239, 9, 'Tolten', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (240, 9, 'Loncoche', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (241, 9, 'Villarrica', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (242, 9, 'Pucon', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (243, 14, 'Valdivia', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (244, 14, 'Corral', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (245, 14, 'Mariquina', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (246, 14, 'Mafil', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (247, 14, 'Los lagos', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (248, 14, 'Futrono', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (249, 14, 'Lanco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (250, 14, 'Panguipulli', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (251, 14, 'La union', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (252, 14, 'Paillaco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (253, 14, 'Rio bueno', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (254, 14, 'Lago ranco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (255, 10, 'Osorno', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (256, 10, 'Puyehue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (257, 10, 'San pablo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (258, 10, 'Puerto octay', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (259, 10, 'Rio negro', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (260, 10, 'Purranque', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (261, 10, 'Puerto montt', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (262, 10, 'Cochamo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (263, 10, 'Maullin', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (264, 10, 'Los muermos', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (265, 10, 'Calbuco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (266, 10, 'Puerto varas', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (267, 10, 'Llanquihue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (268, 10, 'Fresia', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (269, 10, 'Frutillar', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (270, 10, 'Castro', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (271, 10, 'Chonchi', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (272, 10, 'Queilen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (273, 10, 'Quellon', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (274, 10, 'Puqueldon', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (275, 10, 'Quinchao', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (276, 10, 'Curaco de velez', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (277, 10, 'Ancud', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (278, 10, 'Quemchi', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (279, 10, 'Dalcahue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (280, 10, 'Chaiten', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (281, 10, 'Futaleufu', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (282, 10, 'Palena', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (284, 11, 'Coyhaique', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (285, 11, 'Aysen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (286, 11, 'Cisnes', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (287, 11, 'Chile chico', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (288, 11, 'Rio ibaÑez', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (289, 11, 'Cochrane', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (290, 12, 'Punta arenas', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (291, 12, 'Puerto natales', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (292, 12, 'Porvenir', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (293, 15, 'General lagos', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (294, 15, 'Putre', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (295, 15, 'Camarones', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (296, 1, 'Camina', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (297, 1, 'Colchane', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (298, 2, 'Maria elena', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (299, 2, 'Sierra gorda', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (300, 2, 'OllagÜe', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (301, 2, 'San pedro de atacama', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (302, 3, 'Alto del carmen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (303, 8, 'Antuco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (304, 9, 'Melipeuco', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (305, 9, 'Curarrehue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (306, 9, 'Teodoro schmidt', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (307, 10, 'San juan de la costa', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (308, 10, 'Hualaihue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (309, 11, 'Guaitecas', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (310, 11, 'O´higgins', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (311, 11, 'Tortel', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (312, 11, 'Lago verde', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (313, 12, 'Torres del paine', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (314, 12, 'Rio verde', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (315, 12, 'San gregorio', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (316, 12, 'Laguna blanca', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (317, 12, 'Primavera', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (318, 12, 'Timaukel', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (319, 12, 'Navarino', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (320, 7, 'Pelluhue', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (321, 5, 'Juan fernandez', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (322, 13, 'PeÑalolen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (323, 13, 'Macul', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (324, 13, 'Cerro navia', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (325, 13, 'Lo prado', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (326, 13, 'San ramon', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (327, 13, 'La pintana', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (328, 13, 'Estacion central', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (329, 13, 'Recoleta', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (330, 13, 'Independencia', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (331, 13, 'Vitacura', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (332, 13, 'Lo barnechea', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (333, 13, 'Cerrillos', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (334, 13, 'Huechuraba', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (335, 13, 'San joaquin', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (336, 13, 'Pedro aguirre cerda', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (337, 13, 'Lo espejo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (338, 13, 'El bosque', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (339, 13, 'Padre hurtado', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (340, 5, 'Concon', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (341, 7, 'San rafael', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (342, 8, 'Chillan viejo', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (343, 8, 'San pedro de la paz', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (344, 8, 'Chiguayante', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (345, 9, 'Padre las casas', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (346, 1, 'Alto hospicio', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (347, 12, 'Antartica', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (348, 6, 'Mostazal', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (349, 8, 'Niquen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (350, 0, 'Sin Información', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (351, 8, 'Hualpen', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (352, 6, 'San Vicente Tagua Tagua', '');
INSERT INTO `core_ubicacion_comunas` (`idComuna`, `idCiudad`, `Nombre`, `Wheater`) VALUES (353, 5, 'Curauma', '');
COMMIT;

-- ----------------------------
-- Table structure for core_unidades_medida
-- ----------------------------
DROP TABLE IF EXISTS `core_unidades_medida`;
CREATE TABLE `core_unidades_medida` (
  `idUniMed` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(120) NOT NULL,
  `Abreviado` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idUniMed`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Fija';

-- ----------------------------
-- Records of core_unidades_medida
-- ----------------------------
BEGIN;
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (1, 'No aplica', '-');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (2, 'Kilos', 'Kg');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (3, 'Litros', 'Lt');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (4, 'Unidades', 'Un');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (5, 'Gramos', 'Gr');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (6, 'Cajas', 'Cj');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (7, 'Bolsas', 'Bl');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (8, 'MiliLitros', 'Ml');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (9, 'Toneladas', 'Tn');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (10, 'Metro Cubico', 'M3');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (11, 'Kilometros', 'Kl');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (12, 'Metros', 'M');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (13, 'Centimetros', 'Cm');
INSERT INTO `core_unidades_medida` (`idUniMed`, `Nombre`, `Abreviado`) VALUES (14, 'Milimetros', 'Ml');
COMMIT;

-- ----------------------------
-- Table structure for usuarios_accesos
-- ----------------------------
DROP TABLE IF EXISTS `usuarios_accesos`;
CREATE TABLE `usuarios_accesos` (
  `idAcceso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idUsuario` int(10) unsigned NOT NULL,
  `Fecha` date NOT NULL,
  `Hora` time NOT NULL DEFAULT '00:00:00',
  `DateTime` datetime NOT NULL,
  `IP_Client` varchar(120) NOT NULL,
  `Agent_Transp` varchar(240) NOT NULL,
  `idSistema` int(10) unsigned NOT NULL,
  `token` text NOT NULL,
  `expiration_date` datetime NOT NULL,
  `idEstado` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idAcceso`) USING BTREE,
  KEY `fk_Usuario` (`idUsuario`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Limpiar al entregar';

-- ----------------------------
-- Table structure for usuarios_apis
-- ----------------------------
DROP TABLE IF EXISTS `usuarios_apis`;
CREATE TABLE `usuarios_apis` (
  `idAcceso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idUsuario` int(10) unsigned NOT NULL,
  `API` text NOT NULL,
  `idEstado` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idAcceso`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Limpiar al entregar';

-- ----------------------------
-- Records of usuarios_apis
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for usuarios_checkbrute
-- ----------------------------
DROP TABLE IF EXISTS `usuarios_checkbrute`;
CREATE TABLE `usuarios_checkbrute` (
  `idAcceso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Fecha` date NOT NULL,
  `Hora` time NOT NULL DEFAULT '00:00:00',
  `DateTime` varchar(30) NOT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `IP_Client` varchar(120) NOT NULL,
  `Agent_Transp` varchar(240) NOT NULL,
  PRIMARY KEY (`idAcceso`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Limpiar al entregar';

-- ----------------------------
-- Table structure for usuarios_listado
-- ----------------------------
DROP TABLE IF EXISTS `usuarios_listado`;
CREATE TABLE `usuarios_listado` (
  `idUsuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `password` text NOT NULL,
  `idTipoUsuario` int(10) unsigned NOT NULL,
  `idEstado` int(10) unsigned NOT NULL,
  `email` varchar(60) NOT NULL,
  `Nombre` varchar(60) NOT NULL,
  `Rut` varchar(13) DEFAULT NULL,
  `fNacimiento` date DEFAULT NULL,
  `Fono` varchar(15) DEFAULT NULL,
  `idCiudad` int(10) unsigned DEFAULT NULL,
  `idComuna` int(10) unsigned DEFAULT NULL,
  `Direccion` varchar(60) DEFAULT NULL,
  `Direccion_img` varchar(120) DEFAULT NULL,
  `Ultimo_acceso` date DEFAULT NULL,
  `Social_X` varchar(255) DEFAULT NULL,
  `Social_Facebook` varchar(255) DEFAULT NULL,
  `Social_Instagram` varchar(255) DEFAULT NULL,
  `Social_Linkedin` varchar(255) DEFAULT NULL,
  `IP_Client` varchar(120) DEFAULT NULL,
  `Agent_Transp` varchar(240) DEFAULT NULL,
  `idMenuPosicion` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idUsuario`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Cuidado';

-- ----------------------------
-- Records of usuarios_listado
-- ----------------------------
BEGIN;
INSERT INTO `usuarios_listado` (`idUsuario`, `password`, `idTipoUsuario`, `idEstado`, `email`, `Nombre`, `Rut`, `fNacimiento`, `Fono`, `idCiudad`, `idComuna`, `Direccion`, `Direccion_img`, `Ultimo_acceso`, `Social_X`, `Social_Facebook`, `Social_Instagram`, `Social_Linkedin`, `IP_Client`, `Agent_Transp`, `idMenuPosicion`) VALUES (1, 'SFRjQTFXSnBsNWUrVmNwUHRsVHhSdz09', 1, 1, 'demo1@testmail.com', 'Usuario Demo 1', '1-9', NULL, NULL, 5, 46, NULL, NULL, '2025-11-13', NULL, NULL, NULL, NULL, '172.18.0.1', 'Mozilla Firefox', 2);
INSERT INTO `usuarios_listado` (`idUsuario`, `password`, `idTipoUsuario`, `idEstado`, `email`, `Nombre`, `Rut`, `fNacimiento`, `Fono`, `idCiudad`, `idComuna`, `Direccion`, `Direccion_img`, `Ultimo_acceso`, `Social_X`, `Social_Facebook`, `Social_Instagram`, `Social_Linkedin`, `IP_Client`, `Agent_Transp`, `idMenuPosicion`) VALUES (2, 'SFRjQTFXSnBsNWUrVmNwUHRsVHhSdz09', 2, 1, 'demo2@testmail.com', 'Usuario Demo 2', '1-9', NULL, NULL, 5, 46, NULL, NULL, '2025-10-29', NULL, NULL, NULL, NULL, '172.18.0.1', 'Mozilla Firefox', 2);
COMMIT;

-- ----------------------------
-- Table structure for usuarios_listado_observaciones
-- ----------------------------
DROP TABLE IF EXISTS `usuarios_listado_observaciones`;
CREATE TABLE `usuarios_listado_observaciones` (
  `idObservaciones` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idUsuario` int(10) unsigned NOT NULL,
  `Observacion` text NOT NULL,
  `FechaCreacion` date NOT NULL,
  PRIMARY KEY (`idObservaciones`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Limpiar al entregar';

-- ----------------------------
-- Records of usuarios_listado_observaciones
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for usuarios_listado_permisos
-- ----------------------------
DROP TABLE IF EXISTS `usuarios_listado_permisos`;
CREATE TABLE `usuarios_listado_permisos` (
  `idPermisoUsuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idUsuario` int(10) unsigned NOT NULL,
  `idPermisos` int(10) unsigned NOT NULL,
  `idLevelLimit` int(10) unsigned NOT NULL,
  `fechaCreacion` date DEFAULT NULL,
  PRIMARY KEY (`idPermisoUsuario`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC COMMENT='Limpiar al entregar';

-- ----------------------------
-- Records of usuarios_listado_permisos
-- ----------------------------
BEGIN;
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (1, 2, 1, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (2, 2, 2, 1, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (3, 2, 3, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (4, 2, 4, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (5, 2, 6, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (6, 2, 7, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (7, 2, 8, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (8, 2, 9, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (9, 2, 10, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (10, 2, 11, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (11, 2, 12, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (12, 2, 13, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (13, 2, 14, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (14, 2, 15, 1, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (15, 2, 16, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (16, 2, 17, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (17, 2, 18, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (18, 2, 19, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (19, 2, 20, 1, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (20, 2, 21, 2, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (21, 2, 22, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (22, 2, 23, 1, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (23, 2, 24, 1, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (24, 2, 25, 1, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (25, 2, 26, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (26, 2, 27, 3, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (27, 2, 28, 1, NULL);
INSERT INTO `usuarios_listado_permisos` (`idPermisoUsuario`, `idUsuario`, `idPermisos`, `idLevelLimit`, `fechaCreacion`) VALUES (28, 2, 29, 3, NULL);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
