<?php

// Archivo: conexion.php
// Conexión de PHP con MariaDB utilizando PDO.

function conectarBD(): PDO
{
    // Obtener los datos de conexión desde Docker.
    $servidor = getenv("DB_HOST");
    $baseDatos = getenv("DB_NAME");
    $usuario = getenv("DB_USER");
    $contrasena = getenv("DB_PASSWORD");

    // Verificar la configuración.
    if (
        !$servidor ||
        !$baseDatos ||
        !$usuario ||
        !$contrasena
    ) {
        throw new RuntimeException(
            "La conexión a la base de datos no está configurada."
        );
    }

    try {
        $conexion = new PDO(
            "mysql:host=$servidor;dbname=$baseDatos;charset=utf8mb4",
            $usuario,
            $contrasena,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        return $conexion;

    } catch (PDOException $error) {
        throw new RuntimeException(
            "No fue posible conectar con la base de datos.",
            0,
            $error
        );
    }
}
