/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

// ajout d'une fonctionnalité pour afficher le loader lors de la recherche d'un artiste et message de chargement

document.addEventListener('DOMContentLoaded', function () {
    const submitButton = document.getElementById('artist_form_submit');
    const loadingMessage = document.getElementById('loading-message');
    const loadingText = document.getElementById('loading-text');

    if (submitButton) {
        submitButton.addEventListener('click', function () {
            const buttonText = [
                "Résultats en cours de chargement...",
                "En cours d'analyse...",
                "Encore un peu de patience...",
            ];
            let index = 0;

            const updateButtonText = () => {
                loadingText.textContent = buttonText[index % buttonText.length];
                index++;
            };

            updateButtonText();
            loadingMessage.style.display = 'inline-block'; // Affiche la div de chargement
            const intervalId = setInterval(updateButtonText, 2000); // Change le texte toutes les 2 secondes

            // Arrête de mettre à jour le texte lorsque les résultats sont chargés
            document.body.addEventListener(
                'htmx:afterSwap',
                function () {
                    clearInterval(intervalId);
                    loadingMessage.style.display = 'none'; // Cache la div de chargement
                },
                { once: true }
            );
        });
    }
});

