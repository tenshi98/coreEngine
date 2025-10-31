# coreEngine
Plataforma está diseñada específicamente para pequeñas y medianas empresas (PyMEs) con infraestructura de alojamiento estándar, compatible con entornos LAMP/LEMP (servidores Apache o Nginx, PHP y MySQL)

## Estructura de Archivos

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
## Descripción Estructura de Archivos

- **admin/app/apis/apiSession/**
-> Manejo del login y del logout de la API

- **admin/app/config/ConfigAPP.php**
-> Archivo de configuración con los datos básicos relacionados con la aplicación, tales como su nombre, slogan, email, etc

- **admin/app/config/ConfigData.php**
-> Archivo de configuración de las conexiones a las bases de datos

- **admin/app/config/ConfigMail.php**
-> Archivo de configuración de las cuentas de email a utilizar para las funciones de mensajería (recuperación de contraseña, notificaciones, etc)

- **admin/app/config/ConfigToken.php**
-> Archivo de configuración con los tokens utilizados para la codificación y decodificación de datos

- **admin/app/crones/cronList/**
-> Es la carpeta que contiene todos los controladores de los crones

- **admin/app/helpers/userSession.php**
-> Archivo encargado de las sesiones del usuario, se encarga de cargar los datos básicos, los permisos y las rutas de acceso a las transacciones, asi mismo se encarga de la actualización de los datos de sesión de los usuarios cuando éstos modifiquen sus datos

- **admin/app/helpers/validateSession.php**
-> Archivo encargado de validar si el acceso de un usuario esta respaldado con un inicio de sesión correspondiente, valida token e IP de la maquina que se conecta

- **admin/app/modules/bodegas/**
-> Módulo de gestión de bodega, posee su propia estructura interna y trabaja de forma independiente a los otros módulos, pose un instalador que permite copiar el modulo e instalarlo en otro proyecto, el instalador genera las tablas, los permisos y sus rutas de acceso

- **admin/app/modules/campanas/**
-> Módulo de campañas, posee las mismas características del modulo anterior, la única diferencia es que este modulo posee dependencias, las cuales son listadas en la interfaz de instalación de módulos, depende principalmente del modulo de bodega, de entidades y compra-ventas

- **admin/app/templates/Forms/**
-> Contiene la visualización de los distintos inputs correspondiente al tema utilizado por la plataforma (bootstrap, tailwind, etc)

- **admin/app/templates/Mail/**
-> Contiene las plantillas utilizadas en los correos

- **admin/app/templates/Widgets/**
-> Contiene la visualización de los widgets del sistema

- **admin/app/templates/api-view.php**
-> Archivo con la lógica de encriptación de la API, por defecto JSON

- **admin/app/utils/ApiList.php**
-> Archivo que genera las rutas para las APIS del sistema, estas dependen de los permisos del usuario

- **admin/app/utils/CronList.php**
-> Archivo que contiene las rutas a los crones del sistema

- **admin/app/utils/LoadErrors.php**
-> Archivo con la lógica encargada de redirigir a la pagina de error en caso de un acceso no autorizado o de cierre de sesión por token invalido

- **admin/app/utils/RateLimit.php**
-> Mini sistema encargado de contar cuantas veces una IP se conecta al servidor en un determinado tiempo antes de bloquearlo, por defecto trabaja con una carpeta llamada security dentro de la carpeta public donde guarda los accesos en archivos físicos en formato JSON, pero también se puede configurar para que trabaje con Redis (lo mas optimo)

- **admin/app/utils/UserAdmin.php**
-> Archivo que se encarga de listar todas las rutas y permisos que tienen los administradores

- **admin/app/utils/UserData.php**
-> Archivo que se encarga de listar todas las rutas y permisos que tienen los usuarios normales en base a sus permisos

- **admin/app/utils/UserGuest.php**
-> Archivo que se encarga de listar todas las rutas y permisos que tienen los usuarios no logeados (el index, login, recover password, etc)

- **admin/public/css/**
-> Carpeta con las hojas de estilo de la plataforma

- **admin/public/img/**
-> Carpeta con las imágenes de la plataforma

- **admin/public/js/**
-> Carpeta con los script de la plataforma

- **admin/public/security/**
-> Carpeta dentro de la cual se guardan las conexiones de las IP

- **admin/public/upload/**
-> Carpeta de subida de archivos (logo de la plataforma, imagen del usuario, archivos relacionados a los módulos, etc)

- **admin/public/vendor/**
-> Librerías encargadas de dar la funcionalidad a la plataforma

- **admin/public/index.php**
-> Archivo de acceso a la plataforma, desde aquí se rutean las direcciones ingresadas

- **admin/public/robots.txt**
-> Archivo encargado de evitar el acceso de los robots, las IAs de exploracion, etc. Permite indicarle a una maquina que no se quiere que se lea un x archivo, no se listen las carpetas, etc

- **vendors/application/controller/**
-> Contiene las clases padre desde donde los módulos trabajan

- **vendors/application/functions/**
-> Contiene las clases utilitarias para la plataforma, tales como validaciones de formato de datos, formateo de datos, generación y visualización de archivos, conexiones a otras APIS, etc

- **vendors/application/models/**
-> Contiene las clases padre desde donde los módulos trabajan

- **vendors/application/utils/**
-> Contiene las clases para la conexion a la base de datos, recuperacion de datos de los Request, etc

- **vendors/fatfree/**
-> Libreria Fat Free Framework (F3)

- **vendors/libs/php-jwt/**
-> Libreria JWT para PHP

- **vendors/libs/predis/**
-> Libreria Redis para PHP


## Demo de la plataforma

URL Demo: [democoreengine.digitalcreations.cl](https://democoreengine.digitalcreations.cl/)<br/>
Usuario: demo1@testmail.com<br/>
Contraseña: 1234


