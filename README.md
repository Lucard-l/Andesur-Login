# Andesur · Portal de accesos

Login sencillo con PHP, Bootstrap y PostgreSQL, para probar en XAMPP.

## Instalación en XAMPP

1. Copia el repositorio a `C:\xampp\htdocs\Andesur-Login`.
2. Instala e inicia PostgreSQL: XAMPP incluye MariaDB, pero esta aplicación usa **PostgreSQL**, instalado por separado.
3. En `C:\xampp\php\php.ini`, activa `extension=pdo_pgsql` (quita el `;` inicial). Reinicia Apache desde XAMPP.
4. En pgAdmin crea una base de datos llamada `andesur`. Abre su herramienta de consultas y ejecuta primero `database/01-schema.sql` y después `database/02-seed.sql`. Ejecútalos una sola vez en una base vacía.
5. Copia `config.local.example.php` a `config.local.php`. Escribe el usuario y la contraseña de tu PostgreSQL. Ese archivo queda excluido de Git.
6. Inicia Apache y abre `http://localhost/Andesur-Login/`.

No necesitas Docker, Composer ni Python. Bootstrap se carga por Internet desde `cdn.jsdelivr.net`. PHP 8.0 o posterior, con PDO PostgreSQL habilitado. Los archivos `.htaccess` evitan publicar el SQL y la configuración; conserva la configuración habitual de XAMPP que permite esos archivos (`AllowOverride All`).

## Cuentas de prueba

| Correo | Rol | Contraseña |
| --- | --- | --- |
| ana@andesur.test | Empleado | P@ssw0rd |
| carlos@andesur.test | Gerente | P@ssw0rd |
| sofia@andesur.test | Administrador | P@ssw0rd |

Las contraseñas se guardan como hashes bcrypt. Los datos iniciales incluyen accesos ficticios de hoy y los dos días anteriores.

Cada inicio de sesión correcto suma un acceso. Recargar la página no suma accesos. Todos los usuarios ven el mismo historial; los roles son informativos. El historial está separado por día y muestra cantidad de ingresos, primera y última hora, y el detalle de todas las horas con segundos. Zona horaria: `America/La_Paz`.

## Probar

- Entra con una cuenta y comprueba que aparece en el día actual.
- Cierra sesión y vuelve a entrar: su contador debe aumentar en uno.
- Recarga la página: el contador debe mantenerse.
- Selecciona otro día para consultar sus registros.
- Una contraseña incorrecta debe mostrar un error y no registrar un acceso.

Las cuentas y contraseñas compartidas son únicamente para pruebas locales.
