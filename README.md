# Aplicativo de Gestión de Envíos

Aplicación web sencilla en **PHP + MySQL**, preparada para desplegarse en **AlwaysData** y versionarse en **GitHub**.

## Funciones

- Registrar envíos.
- Consultar todos los envíos.
- Buscar por destinatario, dirección o descripción.
- Editar envíos.
- Eliminar envíos.
- Creación automática de la tabla `envios` si no existe.
- Diseño responsive y accesible.
- Botones principales ubicados en la parte inferior para facilitar el acceso.

## Estructura

```text
app-envios/
├── index.php
├── config.php
└── README.md
```

## Base de datos

La aplicación usa:

- Host: `mysql-elindall.alwaysdata.net`
- Base de datos: `elindall_envios`
- Usuario: `elindall`

La tabla se crea automáticamente desde `config.php`.

### Tabla `envios`

| Campo | Tipo | Descripción |
|---|---|---|
| id | INT UNSIGNED | Identificador automático |
| destinatario | VARCHAR(150) | Persona que recibe |
| direccion | VARCHAR(255) | Dirección de entrega |
| descripcion | TEXT | Descripción del envío |
| fecha_creacion | TIMESTAMP | Fecha de registro |
| fecha_actualizacion | TIMESTAMP | Última modificación |

## Instalación en AlwaysData

1. Sube `index.php` y `config.php` a la carpeta web de tu sitio.
2. Verifica que PHP esté habilitado.
3. Verifica que la base de datos MySQL `elindall_envios` exista.
4. Abre la URL de tu sitio.
5. Al cargar `index.php`, la aplicación intentará crear la tabla `envios` automáticamente.

## GitHub

Puedes subir los archivos a un repositorio, por ejemplo:

```bash
git init
git add .
git commit -m "Aplicativo de gestión de envíos"
git branch -M main
git remote add origin URL_DE_TU_REPOSITORIO
git push -u origin main
```

## Seguridad

Para un proyecto académico/práctica, las credenciales están configuradas en `config.php` según las indicaciones proporcionadas.

**Importante:** si el repositorio de GitHub será público, no conviene publicar la contraseña de la base de datos. Lo recomendable es utilizar variables de entorno o un archivo de configuración que no se suba al repositorio.
