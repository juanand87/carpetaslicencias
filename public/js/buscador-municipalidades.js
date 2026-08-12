document.addEventListener('DOMContentLoaded', () => {
    const selector = document.getElementById('municipalidad_id');
    const region = document.getElementById('region_municipalidad');

    if (!selector || !region || typeof TomSelect === 'undefined') return;

    const mostrarRegion = (valor) => {
        const opcion = Array.from(selector.options).find((item) => item.value === valor);
        const nombreRegion = opcion?.dataset.region || '';
        region.querySelector('span').textContent = nombreRegion;
        region.hidden = !nombreRegion;
    };

    new TomSelect(selector, {
        plugins: ['dropdown_input'],
        create: false,
        maxItems: 1,
        allowEmptyOption: true,
        placeholder: 'Seleccione una municipalidad...',
        searchField: ['text'],
        sortField: { field: 'text', direction: 'asc' },
        openOnFocus: true,
        selectOnTab: true,
        closeAfterSelect: true,
        render: {
            no_results: () => '<div class="no-results">No se encontraron comunas</div>'
        },
        onDropdownOpen: function () {
            const entradaBusqueda = this.dropdown?.querySelector('.dropdown-input');
            if (entradaBusqueda) {
                entradaBusqueda.placeholder = 'Escriba para buscar una comuna...';
                entradaBusqueda.setAttribute('aria-label', 'Buscar comuna');
            }
        },
        onChange: mostrarRegion
    });
});
