
<?php
// Archivo: eliminar.php
// Elimina un reporte de fallas de forma controlada.

session_start();

require_once "conexion.php";

// Solo permitir solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Acceso no permitido.");
}

// Comprobar el token de seguridad
$tokenRecibido = $_POST["csrf_token"] ?? "";
$tokenGuardado = $_SESSION["csrf_token"] ?? "";

if (
    !is_string($tokenRecibido) ||
    !is_string($tokenGuardado) ||
    $tokenGuardado === "" ||
    !hash_equals($tokenGuardado, $tokenRecibido)
) {
    http_response_code(403);
    exit("Solicitud no autorizada.");
}

// Obtener el ID del reporte
$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

// Validar el ID
if (!$id || $id < 1) {
    http_response_code(400);
    exit("El reporte seleccionado no es válido.");
}

try {

    // Obtener la conexión
    $conexion = conectarBD();

    // Preparar una eliminación segura
    $sql = "DELETE FROM reportes WHERE id = :id";

    $sentencia = $conexion->prepare($sql);

    // Ejecutar la eliminación
    $sentencia->execute([
        ":id" => $id
    ]);

    // Comprobar si existía el registro
    if ($sentencia->rowCount() === 0) {
        http_response_code(404);
        exit("El reporte no existe.");
    }

    // Renovar el token después de eliminar
    $_SESSION["csrf_token"] = bin2hex(
        random_bytes(32)
    );

    // Regresar al listado
    header("Location: listar.php?eliminado=1");
    exit;

} catch (Throwable $error) {

    http_response_code(503);

    exit(
        "No fue posible eliminar el reporte. " .
        "Compruebe la conexión a la base de datos."
    );

}
