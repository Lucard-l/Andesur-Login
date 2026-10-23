# Andesur · Portal de accesos

Aplicación sencilla de PHP, Bootstrap y PostgreSQL para instalar directamente en Ubuntu Server o Windows Server. No requiere XAMPP, Docker, Composer ni Node.js.

## Estructura y requisitos

- PHP 8.3 o posterior con las extensiones PDO y `pdo_pgsql`.
- PostgreSQL 16 o posterior.
- Apache 2.4 en Ubuntu o IIS con PHP FastCGI en Windows.
- **La raíz pública del sitio debe apuntar exclusivamente a `public/`**, nunca a la raíz del repositorio. El SQL y la configuración quedan fuera del sitio web.
- Bootstrap se carga desde `cdn.jsdelivr.net`; el navegador necesita acceso a Internet para cargarlo.

La estructura es `public/` (página y estilos), `database/` (esquema y ejemplos), `config.php` (carga de configuración).

## Ubuntu Server 24.04 LTS · Apache

Instala los componentes:

```sh
sudo apt update
sudo apt install apache2 libapache2-mod-php php-cli php-pgsql postgresql git
sudo systemctl enable --now apache2 postgresql
sudo git clone https://github.com/Lucard-l/Andesur-Login.git /var/www/andesur
```

Crea un usuario propio para la aplicación y una base de datos. `createuser` solicita una contraseña nueva; guárdala para la configuración del sitio:

```sh
sudo -u postgres createuser --pwprompt andesur
sudo -u postgres createdb --owner=andesur andesur
psql -h localhost -U andesur -d andesur -f /var/www/andesur/database/01-schema.sql
```

Para cargar los usuarios y accesos ficticios de demostración, ejecuta también:

```sh
psql -h localhost -U andesur -d andesur -f /var/www/andesur/database/02-seed.sql
```

Estos scripts se ejecutan una sola vez sobre una base vacía. No se vuelven a importar al actualizar la aplicación.

Configura la conexión:

```sh
sudo nano /var/www/andesur/config.local.php
sudo chown root:www-data /var/www/andesur/config.local.php
sudo chmod 640 /var/www/andesur/config.local.php
```

En `config.local.php` usa el ejemplo de conexión de la sección «Configuración» e introduce la contraseña del usuario `andesur`. El usuario de Apache necesita lectura de los archivos y acceso a sus directorios, pero no permisos de escritura en el repositorio. PHP guarda las sesiones en su directorio habitual del sistema.

Crea `/etc/apache2/sites-available/andesur.conf` con este contenido y cambia `ServerName` por tu dominio o nombre del servidor:

```apache
<VirtualHost *:80>
    ServerName andesur.example.com
    DocumentRoot /var/www/andesur/public
    <Directory /var/www/andesur/public>
        Options -Indexes
        AllowOverride None
        Require all granted
        DirectoryIndex index.php
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/andesur-error.log
    CustomLog ${APACHE_LOG_DIR}/andesur-access.log combined
</VirtualHost>
```

Guarda el archivo y habilita el sitio:

```sh
sudo nano /etc/apache2/sites-available/andesur.conf
sudo a2ensite andesur
sudo apache2ctl configtest
sudo systemctl reload apache2
```

Accede mediante el nombre configurado. Si usas un dominio, configura primero su DNS. Si deseas que este sea el sitio predeterminado por IP y no utilizas el sitio de bienvenida, puedes deshabilitarlo con `sudo a2dissite 000-default` y recargar Apache. Si hay firewall activo, permite únicamente los puertos web que uses; PostgreSQL puede quedarse en localhost.

## Windows Server · IIS

1. Instala **IIS**, incluyendo la característica **CGI** (habilita FastCGI). Puedes hacerlo desde el Administrador del servidor o con PowerShell como administrador: `Install-WindowsFeature Web-Server, Web-CGI -IncludeManagementTools`.
2. Instala PHP 8.3 o posterior para Windows, compilación **x64 Non Thread Safe**, desde `windows.php.net`. Extráelo en `C:\PHP` e instala el runtime de Microsoft Visual C++ requerido por esa versión. Copia `php.ini-production` a `php.ini`.
3. En `C:\PHP\php.ini` establece `extension_dir = "C:\PHP\ext"`, habilita `extension=pdo_pgsql`, `cgi.force_redirect=0` y `fastcgi.impersonate=1`. Añade `C:\PHP` al PATH del sistema para que FastCGI pueda localizar las bibliotecas de PHP y reinicia IIS después de cambiarlo. Crea `C:\PHP\sessions`, configura `session.save_path = "C:\PHP\sessions"` y permite modificar esa carpeta únicamente a la identidad del pool de la aplicación (además de los administradores). PHP necesita ese permiso para las sesiones.
4. Instala PostgreSQL 16 o posterior con pgAdmin. Crea el usuario de acceso `andesur` con una contraseña propia y una base `andesur` cuyo propietario sea ese usuario. Conéctate a esa base como `andesur` y ejecuta `database/01-schema.sql`; para la demostración ejecuta después `database/02-seed.sql`.
5. Copia el repositorio en `C:\Sites\Andesur-Login`. Crea `config.local.php` con el ejemplo de la sección «Configuración» e introduce las credenciales de PostgreSQL.
6. En IIS crea un pool llamado `Andesur` con **Sin código administrado** y crea un sitio cuya ruta física sea **`C:\Sites\Andesur-Login\public`**. Configura su binding (nombre del servidor y puerto). Concede lectura y ejecución a `IIS AppPool\Andesur` sobre el proyecto y `C:\PHP`, y modificación solo sobre `C:\PHP\sessions`.
7. En **Asignaciones de controlador** del sitio añade una asignación de módulo: ruta `*.php`, módulo `FastCgiModule`, ejecutable `C:\PHP\php-cgi.exe`, nombre `PHP FastCGI`. Acepta crear la aplicación FastCGI. `public/web.config` establece `index.php` como documento predeterminado y deshabilita listar directorios.
8. Reinicia el sitio y accede por el nombre o IP configurados. Comprueba en consola `C:\PHP\php.exe -m` y confirma que aparecen `PDO` y `pdo_pgsql`. Mantén PostgreSQL accesible solo localmente si está en el mismo servidor.

## Configuración y cuentas de demostración

Crea `config.local.php` en la raíz del repositorio con este contenido y sustituye la contraseña:

```php
<?php
return [
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=andesur',
    'user' => 'andesur',
    'password' => 'TU_CONTRASENA_DE_POSTGRESQL',
    'timezone' => 'America/La_Paz',
];
```

`config.local.php` contiene la conexión y la zona horaria; está excluido de Git y fuera de `public/`. Usa un usuario de base de datos propio, no el superusuario `postgres`.

| Correo | Rol | Contraseña |
| --- | --- | --- |
| ana@andesur.test | Empleado | P@ssw0rd |
| carlos@andesur.test | Gerente | P@ssw0rd |
| sofia@andesur.test | Administrador | P@ssw0rd |

Las contraseñas están guardadas como hashes bcrypt. Los datos iniciales incluyen accesos ficticios de hoy y los dos días anteriores. Estas cuentas compartidas son para demostrar la aplicación: antes de exponerla a Internet cambia sus contraseñas o elimina las cuentas y sus accesos de ejemplo. Configura un certificado HTTPS y el binding correspondiente en Apache o IIS antes de usar credenciales reales.

Cada inicio de sesión correcto suma un acceso. Recargar no suma accesos. Todos los usuarios ven el mismo historial; los roles son informativos. El historial está separado por día, con cantidad de ingresos, primera y última hora y detalle con segundos. Zona horaria predeterminada: `America/La_Paz`.

## Comprobación después de instalar

- Abre el sitio: debe mostrar el login con sus estilos.
- Entra con una cuenta: debe aparecer en el día actual.
- Cierra sesión y vuelve a entrar: su contador debe aumentar en uno.
- Recarga: el contador debe mantenerse.
- Selecciona otro día para consultar sus registros.
- Una contraseña incorrecta debe mostrar un error sin registrar un acceso.
- Las rutas `/config.local.php`, `/database/02-seed.sql` y `/.git/config` no deben servir archivos del repositorio.

La aplicación se comprobó con Apache, PHP y PostgreSQL en Linux. Los pasos de Windows Server/IIS requieren validación en el servidor de destino.
