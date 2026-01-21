    </div>
</main>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Global function to handle link confirmations with SweetAlert2
    function confirmLinkAction(event, url, title, text) {
        event.preventDefault(); 
        Swal.fire({
            title: title || '¿Estás seguro?',
            text: text || "Esta acción no se puede deshacer.",
            icon: 'warning',
            background: '#1f2937',
            color: '#ffffff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#374151',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>
</body>
</html>
