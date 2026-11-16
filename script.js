document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (!window.confirm('Voulez-vous vraiment supprimer cet étudiant ?')) {
                event.preventDefault();
            }
        });
    });

    var recherche = document.querySelector('#q');
    var lignes = document.querySelectorAll('[data-student-row]');
    var messageVide = document.querySelector('.no-instant-results');

    if (!recherche || lignes.length === 0) {
        return;
    }

    recherche.addEventListener('input', function () {
        var terme = recherche.value.trim().toLocaleLowerCase();
        var visibles = 0;

        lignes.forEach(function (ligne) {
            var contenu = (ligne.getAttribute('data-search') || '').toLocaleLowerCase();
            var correspond = contenu.indexOf(terme) !== -1;
            ligne.hidden = !correspond;
            if (correspond) {
                visibles += 1;
            }
        });

        if (messageVide) {
            messageVide.hidden = visibles !== 0;
        }
    });
});