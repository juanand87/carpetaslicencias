// Validaciones JavaScript básicas

/**
 * Validar formato de RUN chileno
 */
function validarRUN(run) {
    // Limpiar el RUN
    run = run.toUpperCase().replace(/[.-]/g, '');
    
    // Verificar formato
    if (!/^\d{1,2}\d{3}\d{3}[K0-9]$/.test(run)) {
        return false;
    }
    
    // Calcular dígito verificador
    const numRun = run.slice(0, -1);
    const dv = run[run.length - 1];
    
    let s = 0;
    let m = 2;
    
    for (let i = numRun.length - 1; i >= 0; i--) {
        s += parseInt(numRun[i]) * m;
        m++;
        if (m > 7) m = 2;
    }
    
    let dvCorrecto = 11 - (s % 11);
    if (dvCorrecto === 11) dvCorrecto = 0;
    else if (dvCorrecto === 10) dvCorrecto = 'K';
    
    return String(dvCorrecto) === dv;
}

/**
 * Formatear RUN en tiempo real
 */
function formatarRUN(input) {
    let valor = input.value.replace(/[.-]/g, '');
    
    if (valor.length > 8) {
        valor = valor.slice(0, 8) + '-' + valor.slice(8);
    }
    if (valor.length > 5) {
        valor = valor.slice(0, 5) + '.' + valor.slice(5);
    }
    if (valor.length > 2) {
        valor = valor.slice(0, 2) + '.' + valor.slice(2);
    }
    
    input.value = valor;
}

/**
 * Validar formulario de solicitud
 */
function validarFormularioSolicitud(form) {
    const run = form.querySelector('[name="run"]').value;
    const correo = form.querySelector('[name="correo_solicitante"]').value;
    
    // Validar RUN
    if (!validarRUN(run)) {
        alert('El RUN ingresado no es válido. Use formato: 12.345.678-9');
        return false;
    }
    
    // Validar correo
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regexEmail.test(correo)) {
        alert('El correo electrónico no es válido');
        return false;
    }
    
    return true;
}

/**
 * Mostrar mensaje de carga
 */
function mostrarCarga(mostrar = true) {
    let loader = document.getElementById('loader');
    
    if (mostrar) {
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'loader';
            loader.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.3);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            `;
            loader.innerHTML = `
                <div style="
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    text-align: center;
                ">
                    <div style="
                        border: 4px solid #f3f3f3;
                        border-top: 4px solid #667eea;
                        border-radius: 50%;
                        width: 40px;
                        height: 40px;
                        animation: spin 1s linear infinite;
                        margin: 0 auto 15px;
                    "></div>
                    <p>Procesando...</p>
                </div>
            `;
            document.body.appendChild(loader);
        }
        loader.style.display = 'flex';
    } else {
        if (loader) loader.style.display = 'none';
    }
}

/**
 * Animación de spinner
 */
const style = document.createElement('style');
style.innerHTML = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

/**
 * Confirmar acciones peligrosas
 */
function confirmarAccion(mensaje) {
    return confirm(mensaje || '¿Está seguro de esta acción?');
}

/**
 * Copiar al portapapeles
 */
function copiarAlPortapapeles(texto) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(texto).then(() => {
            alert('Copiado al portapapeles');
        });
    }
}

