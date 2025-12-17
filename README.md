# coreEngine

Plataforma está diseñada específicamente para pequeñas y medianas empresas (PyMEs) con infraestructura de alojamiento estándar, compatible con entornos LAMP/LEMP (servidores Apache o Nginx, PHP y MySQL).

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Módulos](#-módulos)
- [Troubleshooting](#-troubleshooting)
- [Demo](#-demo)

## ✨ Características

- **Caché Redis**: Almacenamiento en caché de dispositivos con fallback automático a MySQL
- **Rate Limiting**: Control de frecuencia de requests configurable
- **Validación Robusta**: Validación completa de datos de entrada
- **Logging Completo**: Logs, requests inválidos, sistema y errores
- **Arquitectura Modular**: Fácil mantenimiento y extensión
- **Abstracción de BD**: Migración simple a otros motores de base de datos
- **Manejo de Errores**: Registro de errores en base de datos y archivos

## 📦 Requisitos

### Técnicos
- **PHP**: 7.0 o superior
- **MySQL**: 5.7 o superior
- **Redis**: 3.0 o superior
- **Extensiones PHP**:
  - `pdo_mysql`
  - `json`
  - `mbstring`

### Servidor Web
- Apache 2.4+ con `mod_rewrite` habilitado
- Nginx 1.10+ (configuración alternativa)

## 🚀 Instalación

### 1. Clonar o Descargar el Proyecto

```bash
git clone https://github.com/tenshi98/coreEngine.git
```

### 2. Instalar Base de Datos

```bash
# Conectar a MySQL
mysql -u root -p

# Ejecutar schema
mysql -u root -p < database/schema.sql

# (Opcional) Cargar datos de prueba
mysql -u root -p < database/testData.sql
```

### 3. Configurar Servidor Web

Los archivos `.htaccess` ya están incluidos. Asegúrate de que `mod_rewrite` esté habilitado:

```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

## ⚙️ Configuración

### Configuración Principal

La carpeta `coreEngine/admin/app/config/` contiene todos los archivos que tienen la configuración del sistema.

### Archivo ConfigAPP.php

| Parámetro | Descripción | Default |
|-----------|-------------|---------|
| `SoftwareName` | Nombre del software | coreEngine |
| `SoftwareSlogan` | Slogan del software | Software Modular |
| `CompanyName` | Nombre de la compañía | coreEngine |
| `CompanyEmail` | Email de la compañía | coreEngine@coreEngine.cl |
| `CompanyCredits` | Créditos | Todos los derechos reservados |
| `URL` | URL base de la plataforma | https://example.cl |
| `N_MaxItems` | N° max de registros sin paginar | 1000 |
| `uploadFolder` | Carpeta de subida de archivos | 'public/upload/' |
| `checkBruteConections` | N° max de intentos de login fallidos antes de banear | 5 |
| `checkBruteMaxConections` | N° max de intentos de login fallidos antes de enviar a la lista negra | 20 |

### Archivo ConfigData.php

| Parámetro | Descripción | Default |
|-----------|-------------|---------|
| `HOSTNAME` | Host de MySQL | localhost |
| `USERNAME` | Usuario de MySQL | userAdmin |
| `PASSWORD` | Password de MySQL | userPassword |
| `DATABASE` | Nombre de la base de datos | dataBase |
| `PORT` | Puerto de MySQL | 3306 |
| `ROUTE` | Host sqlite | '/absolute/path/to/your/database.sqlite' |
| `HOST` | Host Redis | 127.0.0.1:27017 |

### Archivo ConfigMail.php

| Parámetro | Descripción | Default |
|-----------|-------------|---------|
| `SERVERURL` | URL del servidor de correo | smtp.mail.com |
| `SERVERPORT` | Puerto del servidor de correo | 465 |
| `SERVERSECURE` | Codificacion del servidor de correo | SSL |
| `USEREMAIL` | Dirección Email por defecto | joebloggs@gmail.com |
| `USERNAME` | Usuario Email por defecto | joebloggs |
| `PASSWORD` | Password Email por defecto | mypass |
| `SERVERAPI` | Token Api para los servicios externos de envios de correo | Token |

### Archivo ConfigToken.php

| Parámetro | Descripción |
|-----------|-------------|
| `TOKEN_AUTHENTICATION` | enable/disable token authentication |
| `SECRET_KEY` | Secret key for token encryption |
| `TIME_TO_LIVE` | token life time |
| `KEY_1` | Token para uso interno |

## 📁 Estructura del Proyecto

```
.
├── admin/
│   ├── app/
│   │   ├── apis/
│   │   │   └── apiSession/
│   │   ├── config/
│   │   │   ├── ConfigAPP.php
│   │   │   ├── ConfigData.php
│   │   │   ├── ConfigMail.php
│   │   │   └── ConfigToken.php
│   │   ├── crones/
│   │   │   └── cronList/
│   │   ├── helpers/
│   │   │   ├── userSession.php
│   │   │   └── validateSession.php
│   │   ├── modules/
│   │   │   ├── bodegas/
│   │   │   ├── campanas/
│   │   │   ├── cotizaciones/
│   │   │   ├── entidades/
│   │   │   ├── main/
│   │   │   └── .../
│   │   ├── templates/
│   │   │   ├── Forms/
│   │   │   ├── Mail/
│   │   │   ├── Widgets/
│   │   │   ├── api-view.php
│   │   │   ├── guest-footer.php
│   │   │   ├── guest-header.php
│   │   │   ├── pages-error404.php
│   │   │   └── .../
│   │   └── utils/
│   │       ├── ApiList.php
│   │       ├── CronList.php
│   │       ├── LoadErrors.php
│   │       ├── RateLimit.php
│   │       ├── UserAdmin.php
│   │       ├── UserData.php
│   │       └── UserGuest.php
│   └── public/
│       ├── css/
│       ├── img/
│       ├── js/
│       ├── security/
│       ├── upload/
│       ├── vendor/
│       ├── index.php
│       ├── robots.txt
│       └── .htaccess
└── vendors/
    ├── application/
    │   ├── controller/
    │   ├── functions/
    │   ├── models/
    │   └── utils/
    ├── fatfree/
    └── libs/
        ├── php-jwt/
        └── predis/
```

## 🔧 Módulos

### 1. apis

**Ubicación**: `admin/app/apis/`

**Propósito**: Lógica de resolución de las APIS integradas en la plataforma.

### 2. config

**Ubicación**: `admin/app/config/`

**Propósito**: Configuración general de la plataforma.

**Archivos**:
- `ConfigAPP.php`: Archivo de configuración con los datos básicos relacionados con la aplicación, tales como su nombre, slogan, email, etc.
- `ConfigData.php`: Archivo de configuración de las conexiones a las bases de datos.
- `ConfigMail.php`: Archivo de configuración de las cuentas de email a utilizar para las funciones de mensajería (recuperación de contraseña, notificaciones, etc).
- `ConfigToken.php`: Archivo de configuración con los tokens utilizados para la codificación y decodificación de datos.

### 3. crones

**Ubicación**: `admin/app/crones/`

**Propósito**: Manejo de los crones de la plataforma.

### 4. helpers

**Ubicación**: `admin/app/helpers/`

**Propósito**: Manejo de sesiones y validacion de usuarios

**Archivos**:
- `userSession.php`: Archivo encargado de las sesiones del usuario, se encarga de cargar los datos básicos, los permisos y las rutas de acceso a las transacciones, asi mismo se encarga de la actualización de los datos de sesión de los usuarios cuando éstos modifiquen sus datos
- `validateSession.php`: rchivo encargado de validar si el acceso de un usuario esta respaldado con un inicio de sesión correspondiente, valida token e IP de la maquina que se conecta

### 5. templates

**Ubicación**: `admin/app/templates/`

**Propósito**: Manejo de las plantillas de la interfaz de la plataforma, permite hacer cambio fácil entre frameworks css.

**Carpetas**:
- `Forms`: Contiene la visualización de los distintos inputs correspondiente al tema utilizado por la plataforma (bootstrap, tailwind, etc)
- `Mail`: Contiene las plantillas utilizadas en los correos
- `Widgets`: Contiene la visualización de los widgets del sistema

### 6. utils

**Ubicación**: `admin/app/utils/`

**Propósito**: Manejo de las utilidades del sistema

**Archivos**:
- `ApiList.php`: Archivo que genera las rutas para las APIS del sistema, estas dependen de los permisos del usuario
- `CronList.php`: Archivo que contiene las rutas a los crones del sistema
- `LoadErrors.php`: Archivo con la lógica encargada de redirigir a la pagina de error en caso de un acceso no autorizado o de cierre de sesión por token invalido
- `RateLimit.php`: Mini sistema encargado de contar cuantas veces una IP se conecta al servidor en un determinado tiempo antes de bloquearlo, por defecto trabaja con una carpeta llamada security dentro de la carpeta public donde guarda los accesos en archivos físicos en formato JSON, pero también se puede configurar para que trabaje con Redis (lo mas optimo)
- `UserAdmin.php`: Archivo que se encarga de listar todas las rutas y permisos que tienen los administradores
- `UserData.php`: Archivo que se encarga de listar todas las rutas y permisos que tienen los usuarios normales en base a sus permisos
- `UserGuest.php`: Archivo que se encarga de listar todas las rutas y permisos que tienen los usuarios no logeados (el index, login, recover password, etc)

## 🐛 Troubleshooting

### Error: "Error al conectar con MySQL"

**Solución**:
- Verificar que MySQL esté corriendo
- Verificar que la base de datos exista
- Verificar permisos del usuario

```bash
mysql -u root -p -e "SHOW DATABASES;"
mysql -u root -p -e "GRANT ALL ON core_engine.* TO 'tu_usuario'@'localhost';"
```

## 📦 Demo

### Demo de la plataforma

| Dato | Descripción |
|-----------|-------------|
| URL Demo | [democoreengine.digitalcreations.cl](https://democoreengine.digitalcreations.cl/) |
| Usuario | demo1@testmail.com |
| Contraseña | 1234 |

## 📝 Notas Adicionales

### Seguridad

- Todos los queries usan prepared statements
- Validación estricta de entrada
- Headers de seguridad en `.htaccess`
- Rate limiting para prevenir abuso
- Logs de requests sospechosos

### Performance

- Índices optimizados en tablas
- Conexiones persistentes
- TTL configurable para caché

### Mantenimiento

- Logs con rotación automática
- Código documentado
- Arquitectura modular

