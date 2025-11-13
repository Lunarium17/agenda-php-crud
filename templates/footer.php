</main> <footer class="bg-light text-center text-muted p-4 mt-5">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> - Mini Proyecto PHP. Desarrollado por:</p>
        <ul style="list-style: none;">
            <li>Victor Armando Gámez Vázquez - 23270666</li>
            <li>Alejandro Salazar Morales - 23270688</li>
            <li>Alejandra Lizeth Ruíz Juárez - 23270669</li>
            <li>Miguel Alvarado Gamboa - 23270673</li>
        </ul>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var confirmDeleteModal = document.getElementById('confirmDeleteModal');
    if (confirmDeleteModal) {
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            // Botón que disparó el modal
            var button = event.relatedTarget;
            
            // Extraer info de los atributos data-*
            var contactId = button.getAttribute('data-id');
            var contactName = button.getAttribute('data-nombre');

            // Actualizar el contenido del modal
            var modalTitle = confirmDeleteModal.querySelector('.modal-title');
            var modalBody = confirmDeleteModal.querySelector('.modal-body');
            var deleteFormInput = confirmDeleteModal.querySelector('#deleteId');

            modalTitle.textContent = 'Confirmar Eliminación';
            modalBody.innerHTML = '¿Estás seguro de que deseas eliminar a <strong>' + 
                                  // Sanitización básica en JS
                                  contactName.replace(/</g, "&lt;").replace(/>/g, "&gt;") + 
                                  '</strong>?';
            deleteFormInput.value = contactId;
        });
    }
});
</script>

</body>
</html>