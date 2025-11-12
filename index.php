<?php
require 'config/db.php'; // Incluye PDO
require 'templates/header.php';

// --- ESTA ES LA LÓGICA CORREGIDA ---

// 1. Inicializar variables
$termino_busqueda = '';
$params = []; // Un array de parámetros vacío por defecto
$sql = "SELECT id, nombre, apellido, telefono, email FROM contactos ORDER BY nombre ASC";

// 2. Comprobar si hay búsqueda
if (isset($_GET['busqueda']) && !empty(trim($_GET['busqueda']))) {
    $termino_busqueda = trim($_GET['busqueda']);
    
    // 3. Si hay búsqueda, CAMBIAR el SQL y LLENAR los parámetros
    $sql = "SELECT id, nombre, apellido, telefono, email FROM contactos 
            WHERE nombre LIKE :nombre 
            OR apellido LIKE :apellido 
            OR telefono LIKE :telefono
            ORDER BY nombre ASC";
            
    // 4. (LA CLAVE) Añadir comodines '%' para la búsqueda LIKE
    $like_termino = '%' . $termino_busqueda . '%';
    $params = [
        ':nombre' => $like_termino,
        ':apellido' => $like_termino,
        ':telefono' => $like_termino
    ];
}

try {
    $stmt = $pdo->prepare($sql);
    
    // 5. (LA SOLUCIÓN) Ejecutar con el array $params.
    // Si no hay búsqueda, $params estará vacío (execute([])), lo cual es correcto.
    // Si hay búsqueda, $params tendrá [':termino' => '%busqueda%'], lo cual es correcto.
    $stmt->execute($params); 
    
    $contactos = $stmt->fetchAll();

} catch (\PDOException $e) {
    // Manejo de errores
    $_SESSION['mensaje'] = 'Error al cargar los contactos: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
    $contactos = []; // Evita errores si la consulta falla
}
// --- FIN DE LA LÓGICA CORREGIDA ---
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Mis Contactos (<?= count($contactos) ?>)</h2>
    </div>
    <div class="col-md-6">
        <form action="index.php" method="GET" class="d-flex">
            <input class="form-control me-2" type="search" name="busqueda" 
                   placeholder="Buscar por nombre, apellido, tel..." 
                   value="<?= htmlspecialchars($termino_busqueda) // Sanitizar salida ?>">
            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            <?php if (!empty($termino_busqueda)): ?>
                <a href="index.php" class="btn btn-outline-secondary ms-2" title="Limpiar búsqueda"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($contactos) > 0): ?>
                <?php foreach ($contactos as $contacto): ?>
                    <tr>
                        <td><?= htmlspecialchars($contacto['nombre']) ?></td>
                        <td><?= htmlspecialchars($contacto['apellido']) ?></td>
                        <td><?= htmlspecialchars($contacto['telefono']) ?></td>
                        <td><?= htmlspecialchars($contacto['email'] ?? '') // '??' para manejar nulos ?></td>
                        <td>
                            <a href="ver.php?id=<?= $contacto['id'] ?>" class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="editar.php?id=<?= $contacto['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#confirmDeleteModal"
                                    data-id="<?= $contacto['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($contacto['nombre'] . ' ' . $contacto['apellido']) ?>"
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted pt-3 pb-3">
                        <?php if (!empty($termino_busqueda)): ?>
                            No se encontraron contactos para "<?= htmlspecialchars($termino_busqueda) ?>".
                        <?php else: ?>
                            No hay contactos en la agenda. ¡Añade uno!
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas eliminar este contacto?
      </div>
      <div class="modal-footer">
        <form action="eliminar.php" method="POST" id="deleteForm">
            <input type="hidden" name="id" id="deleteId" value="">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require 'templates/footer.php'; ?>