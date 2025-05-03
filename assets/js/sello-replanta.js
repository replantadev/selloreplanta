document.addEventListener("DOMContentLoaded", function () {
    var selloContainer = document.getElementById("sello-replanta-container");
    var footer = document.querySelector("footer");
    if (selloContainer && footer) {
        var customBgColor = selloReplantaData.customBgColor;

        if (customBgColor) {
            // Usar el color configurado por el usuario
            selloContainer.style.backgroundColor = customBgColor;
        } else {
            // Detectar el último elemento visible dentro del footer
            var lastElement = Array.from(footer.children).reverse().find(function (el) {
                return window.getComputedStyle(el).display !== "none";
            });

            // Si no hay un último elemento visible, usar el footer como referencia
            if (!lastElement) {
                lastElement = footer;
            }

            // Obtener el color de fondo del último elemento visible
            var computedStyle = window.getComputedStyle(lastElement);
            var backgroundColor = computedStyle.backgroundColor;

            // Si el último elemento no tiene color de fondo explícito, buscar en sus hijos
            if (backgroundColor === "rgba(0, 0, 0, 0)" || backgroundColor === "transparent") {
                var child = lastElement.querySelector("*");
                if (child) {
                    backgroundColor = window.getComputedStyle(child).backgroundColor;
                }
            }

            // Aplicar el color de fondo detectado
            selloContainer.style.backgroundColor = backgroundColor;
        }

        // Insertar el sello como último hijo del footer
        footer.appendChild(selloContainer);
        selloContainer.style.display = "block";
    }
});