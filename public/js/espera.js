(function () {
    function crearModalEspera() {
        let modal = document.getElementById('modal-espera-global');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'modal-espera-global';
        modal.className = 'loader-overlay';
        modal.setAttribute('role', 'status');
        modal.setAttribute('aria-live', 'polite');
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
            <div class="loader-card">
                <div class="loader-spinner" aria-hidden="true"></div>
                <p id="modal-espera-mensaje">Procesando…</p>
                <small>Por favor, no cierre esta ventana.</small>
            </div>`;
        document.body.appendChild(modal);
        return modal;
    }

    function mostrarEspera(formulario) {
        const modal = crearModalEspera();
        let mensaje = formulario.dataset.esperaMensaje || 'Procesando…';
        const selectorEstado = formulario.querySelector('[name="nuevo_estado"]');
        const estadosConCorreo = ['Cargada', 'Cargada con observaciones'];

        if (selectorEstado && estadosConCorreo.includes(selectorEstado.value) && formulario.dataset.esperaMensajeCorreo) {
            mensaje = formulario.dataset.esperaMensajeCorreo;
        }

        modal.querySelector('#modal-espera-mensaje').textContent = mensaje;
        modal.classList.add('visible');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('enviando-solicitud');

        const boton = formulario.querySelector('button[type="submit"]');
        if (boton) {
            boton.setAttribute('aria-disabled', 'true');
            boton.style.pointerEvents = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-espera-mensaje]').forEach(function (formulario) {
            formulario.addEventListener('submit', function () {
                mostrarEspera(formulario);
            });
        });
    });
})();