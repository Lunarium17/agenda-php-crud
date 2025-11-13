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

// --- PROCESAMIENTO POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validar que el ID del POST coincida (y esté presente)
    $id_post = $_POST['id'] ?? null;
    if ($id_post != $id) {
        $_SESSION['mensaje'] = "Error de validación de ID.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: index.php");
        exit;
    }
    
    // 1. Recoger datos
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);
    $notas = trim($_POST['notas']);

    // 2. Validación en Servidor
    $errores = [];
    if (empty($nombre)) $errores[] = "El nombre es obligatorio.";
    if (empty($apellido)) $errores[] = "El apellido es obligatorio.";
    
    // VALIDACIÓN DE 10 DÍGITOS
    if (empty($telefono)) {
        $errores[] = "El teléfono es obligatorio.";
    } elseif (!preg_match('/^\d{10}$/', $telefono)) { 
        $errores[] = "El teléfono debe tener exactamente 10 dígitos (sin espacios ni símbolos).";
    }
    // Fin de validación
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del email no es válido.";
    }

    // 3. Si no hay errores, actualizar
    if (count($errores) === 0) {
        try {
            $sql = "UPDATE contactos SET 
                        nombre = :nombre, 
                        apellido = :apellido, 
                        telefono = :telefono, 
                        email = :email, 
                        direccion = :direccion, 
                        notas = :notas
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            
            // 5. Bind de parámetros
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $email, !empty($email) ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(':direccion', $direccion, !empty($direccion) ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(':notas', $notas, !empty($notas) ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // El ID

            $stmt->execute();

            // 6. Mensaje de éxito (UX) y redirección
            $_SESSION['mensaje'] = "Contacto '" . htmlspecialchars($nombre) . "' actualizado exitosamente.";
            $_SESSION['tipo_mensaje'] = "success";
            
            header("Location: index.php");
            exit;

        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['mensaje'] = "Error: El teléfono '" . htmlspecialchars($telefono) . "' ya existe para otro contacto.";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar el contacto: " . $e->getMessage();
            }
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
    } else {
        $_SESSION['mensaje'] = "Por favor, corrige los siguientes errores: <br> - " . implode("<br> - ", $errores);
        $_SESSION['tipo_mensaje'] = "danger";
    }
    

    $contacto = $_POST;
    $contacto['id'] = $id; // Aseguramos que el ID esté presente
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
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
}

// Mostrar el formulario
require 'templates/header.php';
?>

<h2><i class="fas fa-edit"></i> Editar Contacto</h2>
<hr>

<div class="row">
    <div class="col-md-8 offset-md-2">
        
        <div class="contact-form-card">
        
            <form action="editar.php?id=<?= $id ?>" method="POST">
                
                <input type="hidden" name="id" value="<?= $contacto['id'] ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required 
                               value="<?= htmlspecialchars($contacto['nombre']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="apellido" name="apellido" required
                               value="<?= htmlspecialchars($contacto['apellido']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" required
                           placeholder="Ej: 9611234567"
                           pattern="\d{10}"
                           title="Debe tener exactamente 10 dígitos (sin espacios ni símbolos)."
                           value="<?= htmlspecialchars($contacto['telefono']) ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email (Opcional)</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($contacto['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección (Opcional)</label>
                    <input type="text" class="form-control" id="direccion" name="direccion"
                           value="<?= htmlspecialchars($contacto['direccion'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="notas" class="form-label">Notas (Opcional)</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3"><?= htmlspecialchars($contacto['notas'] ?? '') ?></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="index.php" class="btn btn-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
                </div>

            </form>
            
        </div> </div>
</div>

<?php require 'templates/footer.php'; ?>