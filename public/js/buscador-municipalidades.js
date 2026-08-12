document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('form-solicitud');
    const buscador = document.getElementById('buscador_municipalidad');
    const municipalidadId = document.getElementById('municipalidad_id');
    const opciones = Array.from(document.querySelectorAll('#lista_municipalidades option'));
    const idsPorNombre = new Map(opciones.map((opcion) => [opcion.value, opcion.dataset.id]));

    if (!formulario || !buscador || !municipalidadId) return;

    const sincronizarMunicipalidad = () => {
        const id = idsPorNombre.get(buscador.value.trim()) || '';
        municipalidadId.value = id;
        buscador.setCustomValidity(
            id ? '' : 'Seleccione una municipalidad de la lista.'
        );
    };

    buscador.addEventListener('input', sincronizarMunicipalidad);
    buscador.addEventListener('change', sincronizarMunicipalidad);

    formulario.addEventListener('submit', (evento) => {
        sincronizarMunicipalidad();
        if (!municipalidadId.value) {
            evento.preventDefault();
            buscador.reportValidity();
            buscador.focus();
        }
    });
});
