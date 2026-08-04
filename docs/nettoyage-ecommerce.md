# Nettoyage e-commerce — checklist

Objectif : retirer WooCommerce et ses dépendances **sans perdre de données**,
et rediriger proprement l'ancien trafic vers la nouvelle page unique.

> Ordre important : **on sauvegarde et on exporte AVANT de désactiver quoi que ce soit.**

---

## 1. Sauvegarde & exports (obligatoire, avant tout)

- [ ] **Sauvegarde complète** du site (fichiers + base). Conserver hors du serveur.
- [ ] **Export des commandes** (766) en CSV : WooCommerce → *Extensions/outil d'export*
      ou l'export CSV natif. Vérifier le nombre de lignes.
- [ ] **Export des clients** en CSV.
- [ ] **Export des factures PDF** (extension *PDF Invoices*) hors du site.
      ⚖️ **Conservation légale : 10 ans.** Archiver dans un endroit sûr et sauvegardé.
- [ ] Vérifier que les 3 fichiers (commandes, clients, factures) sont **ouvrables**
      avant de continuer.

## 2. Désactivation des extensions (désactiver, NE PAS supprimer les données)

Dans cet ordre :

- [ ] Stripe Gateway
- [ ] PDF Invoices
- [ ] Plugin de livraison n°1
- [ ] Plugin de livraison n°2
- [ ] **Legacy REST API** (WooCommerce → Réglages → Avancé → Legacy API, décocher)
- [ ] **WooCommerce** en dernier
- [ ] **Spotlight (flux Instagram)** — l'API refuse les comptes personnels.
      Le plus simple : désactiver Spotlight et garder **un simple lien** vers le
      profil Instagram (déjà présent dans la section contact du thème).

> On désactive sans supprimer : les données Woo restent en base si un besoin
> légal ou comptable ressurgit.

## 3. Redirections 301

Toutes les anciennes URL e-commerce doivent renvoyer **301** vers la page unique
ou ses ancres. Deux méthodes possibles.

### Ancres de la page unique

| Ancre        | Cible                          |
|--------------|--------------------------------|
| `#carte`     | Section « Où m'acheter »       |
| `#contact`   | Section contact                |

### Table des redirections

| Ancienne URL (Woo)              | Redirection 301 vers      |
|---------------------------------|---------------------------|
| `/boutique/` (shop)             | `/#carte`                 |
| `/produit/*` (fiches produits)  | `/#carte`                 |
| `/categorie-produit/*`          | `/#carte`                 |
| `/panier/`                      | `/#carte`                 |
| `/commande/` (checkout)         | `/#carte`                 |
| `/mon-compte/*`                 | `/` (accueil)             |
| `/cgv/` ou `/conditions-*`      | `/mentions-legales/`      |
| `/points-de-vente/`             | `/#carte` *(voir note)*   |

> **`/points-de-vente/`** a de l'historique SEO local. Deux choix :
> soit on **réutilise ce slug comme URL de la page d'accueil**, soit on le
> **redirige en 301 vers `/#carte`**. La 1re option préserve mieux le référencement.

### Méthode A — extension *Redirection* (recommandé, sans toucher au serveur)

Installer l'extension **Redirection**, puis créer les règles ci-dessus.
Pour les motifs (`/produit/*`), utiliser le mode « Regex » :

```
Source : ^/produit/.*        →  /#carte   (301)
Source : ^/categorie-produit/.* → /#carte (301)
Source : ^/mon-compte.*      →  /         (301)
```

### Méthode B — `.htaccess` (Apache)

À placer **avant** le bloc `# BEGIN WordPress` :

```apache
# --- Redirections post-WooCommerce (301) ---
RedirectMatch 301 ^/boutique/?$            /#carte
RedirectMatch 301 ^/produit/.*             /#carte
RedirectMatch 301 ^/categorie-produit/.*   /#carte
RedirectMatch 301 ^/panier/?$              /#carte
RedirectMatch 301 ^/commande/?$            /#carte
RedirectMatch 301 ^/mon-compte.*           /
RedirectMatch 301 ^/cgv/?$                 /mentions-legales/
RedirectMatch 301 ^/points-de-vente/?$     /#carte
```

> ⚠️ Les navigateurs ne renvoient pas la partie `#carte` au serveur : la 301
> arrive sur `/`, et le `#carte` est ajouté à l'arrivée. C'est le comportement
> attendu et accepté ici. Adapter les slugs (`/boutique/`, `/commande/`…) aux
> slugs réels du site (ils peuvent être en anglais : `/shop/`, `/checkout/`).

## 4. Nettoyage du header et des pages légales

- [ ] Retirer du header : **recherche produits, panier, « Mon compte », bandeau
      frais de port**. *(Le thème enfant fournit déjà un header minimal sur la page
      d'accueil ; vérifier les autres gabarits Astra / widgets.)*
- [ ] **Mentions légales** : retirer paiement, livraison, comptes clients.
- [ ] **Politique de confidentialité** : retirer les traitements liés au paiement,
      à la livraison et aux comptes.
- [ ] Vérifier que le footer pointe vers les bonnes pages (mentions + confidentialité).

## 5. Caches, SEO, suivi

- [ ] **Purger WP Rocket** (cache complet).
- [ ] **Régénérer le sitemap Yoast** et le resoumettre à Google Search Console.
- [ ] **Surveiller les 404 pendant 1 mois** (extension Redirection → onglet 404,
      ou Search Console) et ajouter les redirections manquantes.
- [ ] Vérifier la config **WP Mail SMTP** (3 échecs d'envoi sur les 30 derniers
      jours) : tester l'envoi, corriger l'expéditeur/API si besoin.

## 6. Extensions à conserver

WP Rocket, Imagify, Yoast SEO, WP Mail SMTP, Antispam Bee.

## 7. Vérifications finales

- [ ] Page d'accueil = page unique, carte visible, 21 boutiques présentes.
- [ ] Formulaire de contact : envoi OK **et** message enregistré dans « Messages reçus ».
- [ ] Test mobile (LCP < 2 s), contrastes, navigation clavier.
- [ ] Anciennes URL Woo → 301 OK (tester quelques produits, le panier, mon-compte).
- [ ] Plus aucun élément e-commerce visible (panier, prix, « ajouter au panier »).
