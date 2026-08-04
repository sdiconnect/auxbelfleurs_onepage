Polices auto-hébergées (RGPD : aucun appel Google Fonts distant)
================================================================

Déposez ici les fichiers .woff2 suivants, aux noms EXACTS :

    grand-hotel-400.woff2     (titres — « Grand Hotel », 400)
    lato-400.woff2            (texte courant — « Lato », 400)
    lato-700.woff2            (« Lato », 700, gras)
    lato-900.woff2            (« Lato », 900, boutons)

Où les trouver (licences SIL Open Font / libres) :
- Grand Hotel : https://fonts.google.com/specimen/Grand+Hotel  (« Download family »)
- Lato        : https://fonts.google.com/specimen/Lato

Convertir les .ttf en .woff2 avec un outil comme
https://www.fontsquirrel.com/tools/webfont-generator (option « Optimal »),
ou `woff2_compress` en ligne de commande.

Les @font-face sont déjà déclarés dans assets/css/main.css et pointent vers
ce dossier. Tant que les .woff2 sont absents, une police de repli s'affiche
automatiquement (cursive pour les titres, sans-serif système pour le texte) —
le site reste lisible, seul le rendu typographique diffère.
