/**
 * theme.js — source unique de verite pour les couleurs de l'appli.
 *
 * Toutes les pages "maison" (theme.css) ET la page qui utilise Bootstrap
 * (operateur/autre) piochent leurs couleurs ici. Comme ca, changer une
 * seule valeur ci-dessous suffit a harmoniser toute l'application.
 */
(function () {
    "use strict";

    var MM_COLORS = {
        primary:     "#2563eb",
        primaryDark: "#1d4ed8",
        dark:        "#1e293b",
        success:     "#16a34a",
        danger:      "#dc2626",
        muted:       "#64748b"
    };

    // 1) Applique la palette aux variables CSS (:root) utilisees par theme.css
    var root = document.documentElement;
    root.style.setProperty("--mm-primary", MM_COLORS.primary);
    root.style.setProperty("--mm-primary-dark", MM_COLORS.primaryDark);
    root.style.setProperty("--mm-dark", MM_COLORS.dark);
    root.style.setProperty("--mm-success", MM_COLORS.success);
    root.style.setProperty("--mm-danger", MM_COLORS.danger);
    root.style.setProperty("--mm-muted", MM_COLORS.muted);

    /**
     * 2) Retinte les classes utilitaires Bootstrap (CDN) avec la meme
     *    palette, pour que la page "Gestion des autres operateurs"
     *    (qui charge Bootstrap) ne jure plus avec le reste du site.
     */
    function paint(selector, prop, color) {
        document.querySelectorAll(selector).forEach(function (el) {
            el.style[prop] = color;
        });
    }

    function repaintBootstrapUtilities() {
        paint(".bg-primary", "backgroundColor", MM_COLORS.primary);
        paint(".text-primary", "color", MM_COLORS.primary);
        paint(".btn-primary", "backgroundColor", MM_COLORS.primary);
        paint(".btn-primary", "borderColor", MM_COLORS.primary);

        paint(".bg-success", "backgroundColor", MM_COLORS.success);
        paint(".text-success", "color", MM_COLORS.success);

        paint(".bg-danger", "backgroundColor", MM_COLORS.danger);
        paint(".text-danger", "color", MM_COLORS.danger);

        paint(".bg-dark", "backgroundColor", MM_COLORS.dark);
        paint(".bg-secondary", "backgroundColor", MM_COLORS.muted);
    }

    /**
     * 3) Colore un solde en vert/rouge a partir d'un attribut data-balance,
     *    au lieu de coder la couleur en dur cote PHP (cf. operateur_view.php).
     */
    function paintBalances() {
        document.querySelectorAll("[data-balance]").forEach(function (el) {
            var val = parseFloat(el.getAttribute("data-balance"));
            var color = val >= 0 ? MM_COLORS.success : MM_COLORS.danger;
            el.classList.add(val >= 0 ? "mm-positive" : "mm-negative");
            el.style.color = color;
        });
    }

    // 4) Met en surbrillance le lien de navigation actif
    function highlightActiveNavLink() {
        document.querySelectorAll("nav.mm-nav a").forEach(function (a) {
            if (a.pathname === window.location.pathname) {
                a.classList.add("active");
            }
        });
    }

    // 5) Ferme automatiquement les messages flash apres quelques secondes
    function autoDismissFlashMessages() {
        var selector = ".mm-flash-success, .mm-flash-error, .alert-success, .alert-error, .alert-danger";
        document.querySelectorAll(selector).forEach(function (el) {
            setTimeout(function () {
                el.style.opacity = "0";
                setTimeout(function () { el.remove(); }, 400);
            }, 5000);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        repaintBootstrapUtilities();
        paintBalances();
        highlightActiveNavLink();
        autoDismissFlashMessages();
    });
})();
