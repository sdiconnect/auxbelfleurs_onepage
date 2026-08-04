# Aux Bêl'fleurs — page unique « points de vente + contact »

Thème enfant Astra qui remplace la boutique WooCommerce par **une seule page**.
Le visiteur trouve où acheter les savons près de chez lui, ou envoie un message.
Aucune fonction e-commerce.

## Contenu du dépôt

```
aux-belfleurs-child/         Le thème enfant à installer
├── style.css                En-tête du thème enfant (déclaration Astra)
├── functions.php            Assets, chargement des modules, infos savonnerie
├── front-page.php           Page unique (document autonome, header minimal)
├── inc/
│   ├── cpt-pdv.php          CPT « Points de vente » + métaboxes natives
│   ├── geocoding.php        Géocodage Nominatim + avertissements admin
│   ├── admin-columns.php    Colonnes ville / géocodage + tri par ville
│   ├── csv-import.php       Import CSV des boutiques
│   ├── stores.php           Récupération des points de vente (groupés par dép.)
│   └── contact-form.php     Traitement du formulaire + enregistrement en base
├── template-parts/          Une partie de template par section
└── assets/
    ├── css/main.css         Feuille de style unique (variables + charte)
    ├── js/main.js           JS unique (carte, recherche, géoloc)
    └── images/              Déposez ici hero.webp (voir le README du dossier)

data/points-de-vente-modele.csv   CSV modèle avec les 21 boutiques
docs/
├── nettoyage-ecommerce.md        Checklist de retrait de WooCommerce + redirections
└── guide-ajout-boutique.md       Mini-guide pour ajouter une boutique (Léa)
```

## Installation du thème

1. **Sauvegarde complète** du site avant toute chose (voir `docs/nettoyage-ecommerce.md`).
2. Compresser le dossier `aux-belfleurs-child/` en `.zip`.
3. WordPress → **Apparence → Thèmes → Ajouter → Téléverser un thème**, choisir le zip.
   *(Astra doit déjà être installé : c'est le thème parent.)*
4. **Activer** « Aux Bêl'fleurs ».
5. **Apparence → Personnaliser → Identité du site → Logo** : déposer le logo floral doré.
6. Déposer la photo de fond du hero dans `assets/images/hero.webp`
   (voir `aux-belfleurs-child/assets/images/README.txt`).
7. Créer/vérifier une page d'accueil : **Réglages → Lecture → La page d'accueil affiche → Une page statique**.
   Le fichier `front-page.php` prend automatiquement le dessus sur cette page.

> Le thème charge **Leaflet en différé** depuis unpkg.com (CDN) uniquement quand
> la carte approche de l'écran, et les **tuiles depuis OpenStreetMap**. Aucune clé
> API, aucun Google Maps. Pour un hébergement 100 % sans CDN, voir la note
> « Auto-hébergement de Leaflet » plus bas.

## Saisie des points de vente

### Option A — Import CSV (recommandé pour la saisie initiale)

1. **Points de vente → Importer un CSV**.
2. Choisir `data/points-de-vente-modele.csv` (ou votre version complétée).
3. Laisser « Géocoder automatiquement » coché → chaque ligne sans coordonnées
   est géocodée via Nominatim (≈ 1 s par ligne, politique d'usage respectée).
4. Vérifier le rapport : les lignes en échec sont signalées et à corriger à la main.

Colonnes du CSV (ordre libre, séparateur `,` ou `;`) :

```
nom, adresse, code_postal, ville, telephone, latitude, longitude, visible
```

Seul `nom` est obligatoire. `visible` = `1` (affiché) ou `0` (masqué).
Si `latitude`/`longitude` sont fournies, elles sont conservées telles quelles.

### Option B — Saisie manuelle

**Points de vente → Ajouter** : titre = nom de la boutique, adresse, publier.
Le géocodage se fait tout seul à l'enregistrement. Voir `docs/guide-ajout-boutique.md`.

> ⚠️ **Adresses postales manquantes.** Le site actuel ne donne que les noms et
> les villes. Sans adresse complète, le marqueur se place **au centre de la commune**.
> Il faut demander à Léa la liste avec les adresses exactes, puis compléter la
> colonne `adresse` du CSV (ou les fiches) et re-géocoder.

## Formulaire de contact

- Sécurité : nonce, honeypot, validation serveur, case RGPD non pré-cochée.
- `Reply-To` = e-mail du visiteur → répondre directement depuis sa boîte mail.
- **Chaque message est aussi enregistré en base** (menu **Messages reçus**), au cas
  où l'envoi SMTP échoue. Antispam Bee et WP Mail SMTP restent en place.

## Points techniques

- **Mobile-first**, contrastes AA, navigation clavier, lien d'évitement.
- La **liste des boutiques est rendue côté serveur**, groupée par département :
  lisible sans JavaScript, bonne pour le SEO local et l'accessibilité.
- La carte (Leaflet) n'est chargée qu'à l'approche de la section → LCP préservé.
- Toute la charte est dans les variables CSS en haut de `assets/css/main.css`.

### Auto-hébergement de Leaflet (optionnel, pour éviter tout CDN)

1. Télécharger Leaflet 1.9.4 (`leaflet.js`, `leaflet.css`, dossier `images/`).
2. Les placer dans `aux-belfleurs-child/assets/vendor/leaflet/`.
3. Dans `functions.php`, remplacer les URL `leafletCss` / `leafletJs` par
   `ABF_URI . '/assets/vendor/leaflet/leaflet.css'` et `.../leaflet.js`.

## Nettoyage e-commerce

**À faire avant/autour de l'activation du thème** : export des commandes,
clients et factures PDF (conservation légale 10 ans), sauvegarde, désactivation
des extensions, **redirections 301**, mise à jour des mentions légales.
Tout est détaillé dans **`docs/nettoyage-ecommerce.md`**.

## Livrables

- ✅ Thème enfant (`aux-belfleurs-child/`)
- ✅ CSV modèle d'import (`data/points-de-vente-modele.csv`)
- ✅ README d'installation (ce fichier)
- ✅ Mini-guide « ajouter une boutique » (`docs/guide-ajout-boutique.md`)
- ✅ Checklist de nettoyage e-commerce + redirections (`docs/nettoyage-ecommerce.md`)
