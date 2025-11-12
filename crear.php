<?php
require 'config/db.php'; // Incluye la conexión PDO

// Procesamiento del formulario cuando se envía (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recoger datos (sanitización básica de espacios)
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);
    $notas = trim($_POST['notas']);

    // 2. Validación en Servidor
    $errores = [];
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio.";
    }
    if (empty($apellido)) {
        $errores[] = "El apellido es obligatorio.";
    }
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

    // 3. Si no hay errores, insertar en BD
    if (count($errores) === 0) {
        try {
            // 4. Usar Sentencias Preparadas (Seguridad contra Inyección SQL)
            $sql = "INSERT INTO contactos (nombre, apellido, telefono, email, direccion, notas) 
                    VALUES (:nombre, :apellido, :telefono, :email, :direccion, :notas)";
            
            $stmt = $pdo->prepare($sql);
            
            // 5. Bind de parámetros
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $email, !empty($email) ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(':direccion', $direccion, !empty($direccion) ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(':notas', $notas, !empty($notas) ? PDO::PARAM_STR : PDO::PARAM_NULL);

            $stmt->execute();

            // 6. Mensaje de éxito y redirección
            $_SESSION['mensaje'] = "Contacto '" . htmlspecialchars($nombre) . " " . htmlspecialchars($apellido) . "' creado exitosamente.";
            $_SESSION['tipo_mensaje'] = "success";
            
            header("Location: index.php");
            exit; // Terminar ejecución tras redirigir

        } catch (\PDOException $e) {
            // Manejo de errores de BD
            if ($e->getCode() == 23000) { // Error de duplicidad
                $_SESSION['mensaje'] = "Error: El teléfono '" . htmlspecialchars($telefono) . "' ya existe en la agenda.";
            } else {
                $_SESSION['mensaje'] = "Error al crear el contacto: " . $e->getMessage();
            }
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
    } else {
        // Mostrar errores de validación al usuario
        $_SESSION['mensaje'] = "Por favor, corrige los siguientes errores: <br> - " . implode("<br> - ", $errores);
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

// Mostrar la vista (HTML)
require 'templates/header.php';
?>

<h2><i class="fas fa-plus-circle"></i> Nuevo Contacto</h2>
<hr>

<div class="row">
    <div class="col-md-8 offset-md-2">
        
        <div class="contact-form-card">
            
            <form action="crear.php" method="POST">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required 
                               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="apellido" name="apellido" required
                               value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" required
                           placeholder="Ej: 9611234567"
                           pattern="\d{10}"
                           title="Debe tener exactamente 10 dígitos (sin espacios ni símbolos)."
                           value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email (Opcional)</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección (Opcional)</label>
                    <input type="text" class="form-control" id="direccion" name="direccion"
                           value="<?= htmlspecialchars($_POST['direccion'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="notas" class="form-label">Notas (Opcional)</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3"><?= htmlspecialchars($_POST['notas'] ?? '') ?></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="index.php" class="btn btn-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Contacto</button>
                </div>

            </form>

        </div> </div>
</div>

<?php require 'templates/footer.php'; ?>