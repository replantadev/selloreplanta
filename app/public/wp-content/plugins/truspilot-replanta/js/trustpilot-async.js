window.fetchTrustpilotReviews = function(containerId, starsImageSuffix, trustpiImageSuffix) {
    fetch(replantaTrustpilot.apiEndpoint + '?stars_image_suffix=' + encodeURIComponent(starsImageSuffix) + '&trustpi_image_suffix=' + encodeURIComponent(trustpiImageSuffix))
        .then(response => response.text()) // Cambia a .text() para manejar HTML
        .then(html => {
            var container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = html;
            } else {
                console.error('Error: El contenedor con ID ' + containerId + ' no existe.');
            }
        })
        .catch(error => {
            console.error('Error al cargar las reseñas de Trustpilot:', error);
            var container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = '<p>No se pudieron cargar las reseñas en este momento.</p>';
            }
        });
};
