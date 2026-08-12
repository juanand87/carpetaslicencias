document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('buscador_municipalidad');
    const selector = document.getElementById('municipalidad_id');
    const region = document.getElementById('region_municipalidad');
    const opciones = Array.from(selector?.options || []).slice(1).map((opcion) => ({
        value: opcion.value,
        nombre: opcion.textContent.trim(),
        region: opcion.dataset.region || ''
    }));

    if (!buscador || !selector || !region) return;

    const normalizar = (texto) => texto
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    const mostrarRegion = () => {
        const opcion = selector.selectedOptions[0];
        const nombreRegion = opcion?.dataset.region || '';
        region.querySelector('span').textContent = nombreRegion;
        region.hidden = !nombreRegion;
    };

    const filtrarOpciones = () => {
        const termino = normalizar(buscador.value);
        const seleccionActual = selector.value;

        selector.replaceChildren(new Option('Seleccione una municipalidad...', ''));
        opciones
            .filter((opcion) => normalizar(opcion.nombre).includes(termino))
            .forEach((opcion) => {
                const elemento = new Option(opcion.nombre, opcion.value);
                elemento.dataset.region = opcion.region;
                selector.add(elemento);
            });

        if (Array.from(selector.options).some((opcion) => opcion.value === seleccionActual)) {
            selector.value = seleccionActual;
        }
        mostrarRegion();
    };

    buscador.addEventListener('input', filtrarOpciones);
    selector.addEventListener('change', mostrarRegion);
});
