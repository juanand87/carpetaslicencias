document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.getElementById('selector_comuna');
    const boton = document.getElementById('selector_comuna_boton');
    const textoBoton = document.getElementById('selector_comuna_texto');
    const panel = document.getElementById('selector_comuna_panel');
    const buscador = document.getElementById('buscador_municipalidad');
    const lista = document.getElementById('lista_municipalidades');
    const selector = document.getElementById('municipalidad_id');
    const region = document.getElementById('region_municipalidad');
    const formulario = document.getElementById('form-solicitud');

    if (!contenedor || !boton || !textoBoton || !panel || !buscador || !lista || !selector || !region) return;

    const opciones = Array.from(selector.options).slice(1).map((opcion) => ({
        value: opcion.value,
        nombre: opcion.textContent.trim(),
        region: opcion.dataset.region || ''
    }));

    const normalizar = (texto) => texto
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    const cerrar = () => {
        panel.hidden = true;
        boton.setAttribute('aria-expanded', 'false');
        contenedor.classList.remove('abierto');
    };

    const seleccionar = (opcion) => {
        selector.value = opcion.value;
        textoBoton.textContent = opcion.nombre;
        region.querySelector('span').textContent = opcion.region;
        region.hidden = !opcion.region;
        boton.classList.add('seleccionado');
        boton.classList.remove('invalido');
        selector.dispatchEvent(new Event('change', { bubbles: true }));
        cerrar();
        boton.focus();
    };

    const dibujar = (termino = '') => {
        const filtro = normalizar(termino);
        const coincidencias = opciones.filter((opcion) => normalizar(opcion.nombre).includes(filtro));
        lista.replaceChildren();

        if (!coincidencias.length) {
            const vacio = document.createElement('li');
            vacio.className = 'selector-comuna-vacio';
            vacio.textContent = 'No se encontraron comunas';
            lista.append(vacio);
            return;
        }

        coincidencias.forEach((opcion) => {
            const item = document.createElement('li');
            item.className = 'selector-comuna-opcion';
            item.textContent = opcion.nombre;
            item.role = 'option';
            item.tabIndex = 0;
            item.setAttribute('aria-selected', String(selector.value === opcion.value));
            item.addEventListener('click', () => seleccionar(opcion));
            item.addEventListener('keydown', (evento) => {
                if (evento.key === 'Enter' || evento.key === ' ') {
                    evento.preventDefault();
                    seleccionar(opcion);
                }
            });
            lista.append(item);
        });
    };

    const abrir = () => {
        dibujar();
        panel.hidden = false;
        boton.setAttribute('aria-expanded', 'true');
        contenedor.classList.add('abierto');
        buscador.value = '';
        requestAnimationFrame(() => buscador.focus());
    };

    boton.addEventListener('click', () => panel.hidden ? abrir() : cerrar());
    buscador.addEventListener('input', () => dibujar(buscador.value));
    buscador.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            cerrar();
            boton.focus();
        } else if (evento.key === 'ArrowDown') {
            evento.preventDefault();
            lista.querySelector('.selector-comuna-opcion')?.focus();
        }
    });
    document.addEventListener('click', (evento) => {
        if (!contenedor.contains(evento.target)) cerrar();
    });
    formulario?.addEventListener('submit', (evento) => {
        if (selector.value) return;
        evento.preventDefault();
        evento.stopImmediatePropagation();
        boton.classList.add('invalido');
        abrir();
    });
});
