<?php
require 'config/db.php'; // Incluye PDO

$id = $_GET['id'] ?? null;

// Validar que el ID sea un número
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    $_SESSION['mensaje'] = "ID de contacto no válido.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: index.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM contactos WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $contacto = $stmt->fetch();

    if (!$contacto) {
        $_SESSION['mensaje'] = "Contacto no encontrado.";
        $_SESSION['tipo_mensaje'] = "warning";
        header("Location: index.php");
        exit;
    }

} catch (\PDOException $e) {
    $_SESSION['mensaje'] = "Error al cargar el contacto: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: index.php");
    exit;
}

require 'templates/header.php';
?>

<div class="card col-md-8 offset-md-2">
    <div class="card-header bg-dark text-white">
        <h2 class="card-title mb-0"><i class="fas fa-user-circle"></i> Detalle del Contacto</h2>
    </div>
    <div class="card-body">
        
        <div class="row mb-3">
            <div class="col-md-4">
                <strong class="text-muted">Nombre Completo:</strong>
            </div>
            <div class="col-md-8 fs-5">
                <?= htmlspecialchars($contacto['nombre']) ?> <?= htmlspecialchars($contacto['apellido']) ?>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <strong class="text-muted">Teléfono:</strong>
            </div>
            <div class="col-md-8">
                <i class="fas fa-phone me-2 text-primary"></i>
                <a href="tel:<?= htmlspecialchars($contacto['telefono']) ?>"><?= htmlspecialchars($contacto['telefono']) ?></a>
            </div>
        </div>

        <?php if (!empty($contacto['email'])): ?>
        <div class="row mb-3">
            <div class="col-md-4">
                <strong class="text-muted">Email:</strong>
            </div>
            <div class="col-md-8">
                <i class="fas fa-envelope me-2 text-primary"></i>
                <a href="mailto:<?= htmlspecialchars($contacto['email']) ?>"><?= htmlspecialchars($contacto['email']) ?></a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($contacto['direccion'])): ?>
        <div class="row mb-3">
            <div class="col-md-4">
                <strong class="text-muted">Dirección:</strong>
            </div>
            <div class="col-md-8">
                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                <?= htmlspecialchars($contacto['direccion']) ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($contacto['notas'])): ?>
        <div class="row mb-3">
            <div class="col-md-4">
                <strong class="text-muted">Notas:</strong>
            </div>
            <div class="col-md-8">
                <p class="border p-2 bg-light rounded" style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($contacto['notas'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

    </div>
    <div class="card-footer text-end">
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
        <a href="editar.php?id=<?= $contacto['id'] ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
    </div>
</div>

<?php require 'templates/footer.php'; ?>