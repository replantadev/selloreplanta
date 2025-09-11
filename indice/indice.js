document.addEventListener("DOMContentLoaded", function() {
  generarIndiceDeContenidos();
});

function generarIndiceDeContenidos() {
  var contenedorPrincipal = document.getElementById('main') || document.body;
  var contenedorIndice = document.getElementById('indice-de-contenidos');
  if (!contenedorIndice) return;

  var titulos = contenedorPrincipal.querySelectorAll('h2, h3, h4, h5, h6');
  if (titulos.length === 0) return; // Si no hay títulos, termina la ejecución.

  var lista = document.createElement('span');
  lista.setAttribute('class', 'lista-indice');

  titulos.forEach(function(titulo, index) {
    var id = 'titulo-' + index;
    titulo.id = id;

    var elemento = document.createElement('p');
    elemento.setAttribute('class', 'elemento-indice elemento-' + titulo.tagName.toLowerCase());

    var enlace = document.createElement('a');
    enlace.href = '#' + id;
    enlace.textContent = titulo.textContent;
    enlace.addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelector(this.getAttribute('href')).scrollIntoView({
        behavior: 'smooth'
      });
    });

    elemento.appendChild(enlace);
    lista.appendChild(elemento);
  });

  contenedorIndice.appendChild(lista);
}
