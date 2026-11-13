# Sistema de Gestión de Fallas en Laboratorios Universitarios

Proyecto desarrollado para la actividad 3 de la asignatura Desarrollo de Aplicaciones en la Web.

La aplicación permite registrar y administrar las fallas que presentan los computadores de los laboratorios universitarios.

## Funciones principales

- Registrar nuevos reportes.
- Consultar y buscar reportes existentes.
- Modificar la información registrada.
- Eliminar reportes.
- Validar los datos ingresados en los formularios.

## Tecnologías utilizadas

HTML, CSS, Bootstrap, JavaScript, PHP y MariaDB.

Para ejecutar el proyecto se utilizó Docker en GitHub Codespaces. La base de datos se puede consultar mediante phpMyAdmin.

## Ejecución

Desde la terminal del proyecto:

`docker compose up -d`

La aplicación funciona en el puerto 8000 y phpMyAdmin en el puerto 8080.

Es necesario configurar previamente las credenciales de la base de datos en el archivo `.env`.

## Repositorio

https://github.com/joan101000/taller-3
