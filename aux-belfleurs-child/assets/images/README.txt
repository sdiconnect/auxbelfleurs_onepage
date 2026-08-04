Images du thème
===============

Fichiers fournis (déjà optimisés en WebP à partir des visuels du handoff design) :

    hero-savons.webp         Photo de fond du héro + 1re tuile de galerie (~124 Ko, 1920 px)
    shampoings-solides.webp  2e tuile de galerie (~85 Ko, 1200 px)
    logo.webp                Logo floral doré (~18 Ko)

Remplacer une image : gardez le même nom de fichier, ou :
- pour le héro, utilisez le filtre `abf_hero_image` (voir section-hero.php) ;
- pour la galerie, le filtre `abf_gallery_tiles` (voir section-gallery.php).

Le logo a un fond blanc ; il est affiché en `mix-blend-mode: multiply` pour se fondre
dans la crème. Pour un rendu parfait, fournir à terme un PNG/SVG à fond transparent
(réglable aussi via Apparence → Personnaliser → Identité du site → Logo).

Si `hero-savons.webp` venait à manquer, un dégradé sarcelle → or s'affiche
automatiquement dans le héro (le site n'est jamais « cassé »).
