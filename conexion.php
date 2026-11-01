
<?php

// Archivo: conexion.php
// Conexión a la base de datos del sistema

function conectarBD() {

    // Datos de conexión
    $servidor = "127.0.0.1";
    $baseDatos = "gestion_fallas";

    // Pendientes hasta tener un servidor autorizado
    $usuario = "";
    $contrasena = "";

    // Evitar conexiones sin configurar
    if ($usuario === "") {
        throw new Exception(
            "La conexión está pendiente de configuración."
        );
    }

    try {

        // Crear conexión utilizando PDO
        $conexion = new PDO(
            "mysql:host=$servidor;dbname=$baseDatos;charset=utf8mb4",
            $usuario,
            $contrasena
        );

        // Activar el control de errores
        $conexion->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        return $conexion;

    } catch (PDOException $error) {

        throw new Exception(
            "No fue posible conectar con la base de datos."
        );
    }
}

?>
