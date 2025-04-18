function agregarFiltro() {
    // Obtener el contenedor de los filtros
    var contenedorFiltros = document.getElementById('contenedor-filtros');
    var filasFiltro = contenedorFiltros.getElementsByClassName('fila-filtro');
    var nuevaFila = filasFiltro[0].cloneNode(true); // Clonar la primera fila

    // Calcular el índice para los nuevos nombres
    var nuevoIndice = filasFiltro.length;

    // Actualizar los nombres de los campos en la nueva fila
    nuevaFila.querySelector('select').name = 'filtros[' + nuevoIndice + '][entidad]';
    nuevaFila.querySelector('input').name = 'filtros[' + nuevoIndice + '][valor]';
    nuevaFila.querySelector('input').value = ''; // Limpiar el valor del input

    // Configurar los botones de la nueva fila
    var botonesDiv = nuevaFila.querySelector('.botones');
    botonesDiv.innerHTML = `
        <button type="button" class="boton-eliminar" onclick="eliminarFiltro(this)">-</button>
        <button type="button" class="boton-agregar" onclick="agregarFiltro()">+</button>
    `;

    // Añadir la nueva fila al contenedor
    contenedorFiltros.appendChild(nuevaFila);

    // Si es la segunda fila, eliminar el botón "+" de la primera fila
    if (filasFiltro.length === 2) {
        var primerBotonAgregar = filasFiltro[0].querySelector('.boton-agregar');
        if (primerBotonAgregar) {
            primerBotonAgregar.remove();
        }
    }
}

function eliminarFiltro(boton) {
    var fila = boton.closest('.fila-filtro'); // Obtener la fila correspondiente
    var contenedorFiltros = document.getElementById('contenedor-filtros');
    var filasFiltro = contenedorFiltros.getElementsByClassName('fila-filtro');

    // Eliminar la fila seleccionada
    fila.remove();

    // Si solo queda una fila, asegurarse de que tenga el botón "+"
    if (filasFiltro.length === 1) {
        var botonesDiv = filasFiltro[0].querySelector('.botones');
        if (!botonesDiv.querySelector('.boton-agregar')) {
            botonesDiv.innerHTML += '<button type="button" class="boton-agregar" onclick="agregarFiltro()">+</button>';
        }
    }
}
