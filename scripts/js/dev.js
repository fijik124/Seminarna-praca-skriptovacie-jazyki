// Inicializácia všetkých popoverov na stránke
document.addEventListener('DOMContentLoaded', function () {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            // Toto zabezpečí, že popover bude ladiť s tvojím tmavým dizajnom
            container: 'body',
            trigger: 'click', // Zmení sa na okno po kliknutí (vhodné pre mobil)
            sanitize: false   // Povoliť HTML kód (ako <code> alebo <hr>) v obsahu
        })
    });

    // Voliteľné: Zavrieť popover pri kliknutí mimo neho
    document.addEventListener('click', function (e) {
        if (!popoverTriggerList.some(el => el.contains(e.target))) {
            popoverList.forEach(p => p.hide());
        }
    });
});
