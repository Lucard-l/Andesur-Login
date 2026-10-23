# Andesur · Portal de accesos

Aplicación sencilla con PHP 8.3, Bootstrap 5 y PostgreSQL 16. Requiere Docker con Compose.

## Ejecutar

Desde la raíz del repositorio:

```sh
docker compose up -d --build
```

El servidor escucha en el puerto **8080**. Bootstrap se carga desde `cdn.jsdelivr.net`.

| Correo | Rol | Contraseña |
| --- | --- | --- |
| ana@andesur.test | Empleado | P@ssw0rd |
| carlos@andesur.test | Gerente | P@ssw0rd |
| sofia@andesur.test | Administrador | P@ssw0rd |

Cada login correcto añade un evento. Recargar la página no añade eventos. Todos los usuarios autenticados pueden consultar el mismo historial; los roles son informativos. Se agrupa por persona y día, con total, primer acceso, último acceso y detalle de todas las horas con segundos. El selector permite consultar cualquier fecha. Zona horaria: `America/La_Paz`.

## Base de datos

- `database/01-schema.sql`: usuarios y eventos, con índices.
- `database/02-seed.sql`: tres usuarios con contraseñas bcrypt y accesos de ejemplo de hoy y los dos días anteriores. Los horarios de ejemplo son ficticios.
- PostgreSQL conserva los datos en un volumen Docker y no publica su puerto al host.
- Los scripts SQL solo se ejecutan al crear una base vacía.

```sh
docker compose stop                 # detener sin borrar datos
docker compose up -d                # volver a iniciar
python tests/smoke.py               # prueba funcional; añade dos accesos de Ana
```

Para borrar todos los datos y volver a cargar el esquema y ejemplos (destructivo): `docker compose down -v`, seguido de `docker compose up -d --build`.

## Configuración

Compose suministra `DB_DSN`, `DB_USER`, `DB_PASSWORD` y `APP_TIMEZONE`. La contraseña de base de datos incluida es únicamente para desarrollo local. PHP usa PDO con consultas preparadas, verificación de contraseñas hash, protección CSRF y regeneración de sesión. El router solo publica la página y sus estilos. Para un despliegue real cambia credenciales y cuentas de demostración y utiliza HTTPS y un servidor PHP de producción; el servidor integrado es para desarrollo.
