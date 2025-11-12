<?php
require 'config/db.php'; // Incluye PDO

// Requisito de Seguridad: Solo aceptar POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id = $_POST['id'] ?? null;

    // Validar ID
    if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
        $_SESSION['mensaje'] = "ID no válido.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: index.php");
        exit;
    }

    try {
        // Usar Sentencia Preparada
        $sql = "DELETE FROM contactos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Verificar si realmente se borró algo
        if ($stmt->rowCount() > 0) {
            $_SESSION['mensaje'] = "Contacto eliminado exitosamente.";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "No se encontró el contacto para eliminar (quizás ya fue borrado).";
            $_SESSION['tipo_mensaje'] = "warning";
        }

    } catch (\PDOException $e) {
        $_SESSION['mensaje'] = "Error al eliminar el contacto: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }

    // Redirigir siempre al index
    header("Location: index.php");
    exit;

} else {
    // Si se accede por GET, rechazar
    $_SESSION['mensaje'] = "Acción no permitida (solo se acepta POST).";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: index.php");
    exit;
}
?>