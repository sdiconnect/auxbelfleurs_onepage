<?php
/**
 * Aux Bêl'fleurs — thème enfant Astra.
 *
 * Point d'entrée : constantes, chargement des modules, mise en file des assets.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'ABF_VERSION', '1.0.0' );
define( 'ABF_DIR', get_stylesheet_directory() );
define( 'ABF_URI', get_stylesheet_directory_uri() );

/**
 * Coordonnées de la savonnerie (source unique de vérité).
 * Utilisées par le hero, la section contact et le footer.
 */
function abf_infos() {
	return array(
		'nom'       => "Aux Bêl'fleurs",
		'adresse'   => '7 chemin du champ du bois',
		'cp'        => '70700',
		'ville'     => 'Igny',
		'telephone' => '06 88 17 06 55',
		// Adresse de contact (destinataire du formulaire + affichée sur la page).
		// Modifiable sans éditer le thème via le filtre « abf_contact_email ».
		'email'     => apply_filters( 'abf_contact_email', 'auxbelfleurs@gmail.com' ),
		'instagram' => 'https://www.instagram.com/auxbelfleurs/',
		'facebook'  => 'https://www.facebook.com/auxbelfleurs/',
	);
}

/**
 * URL d'une image du thème (dossier assets/images).
 *
 * @param string $file Nom de fichier.
 * @return string
 */
function abf_img( $file ) {
	return ABF_URI . '/assets/images/' . ltrim( $file, '/' );
}

/**
 * Numéro de téléphone au format tel: (chiffres uniquement, préfixe international).
 *
 * @param string $numero Numéro affiché (ex. « 06 88 17 06 55 »).
 * @return string Ex. « +33688170655 ».
 */
function abf_tel_href( $numero ) {
	$digits = preg_replace( '/\D+/', '', $numero );
	if ( 0 === strpos( $digits, '0' ) ) {
		$digits = '33' . substr( $digits, 1 );
	}
	return '+' . $digits;
}

// --- Modules ---------------------------------------------------------------

require_once ABF_DIR . '/inc/cpt-pdv.php';
require_once ABF_DIR . '/inc/geocoding.php';
require_once ABF_DIR . '/inc/admin-columns.php';
require_once ABF_DIR . '/inc/csv-import.php';
require_once ABF_DIR . '/inc/stores.php';
require_once ABF_DIR . '/inc/contact-form.php';

// --- Assets ----------------------------------------------------------------

/**
 * Met en file les feuilles de style (parent Astra + thème enfant).
 */
function abf_enqueue_styles() {
	wp_enqueue_style(
		'astra-parent',
		get_template_directory_uri() . '/style.css',
		array(),
		ABF_VERSION
	);

	wp_enqueue_style(
		'abf-main',
		ABF_URI . '/assets/css/main.css',
		array( 'astra-parent' ),
		ABF_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'abf_enqueue_styles' );

/**
 * Met en file le JS principal, uniquement sur la page d'accueil.
 * Leaflet n'est PAS chargé ici : il est injecté en différé par le JS
 * quand la section carte approche du viewport (voir assets/js/main.js).
 */
function abf_enqueue_scripts() {
	if ( ! is_front_page() ) {
		return;
	}

	wp_enqueue_script(
		'abf-main',
		ABF_URI . '/assets/js/main.js',
		array(),
		ABF_VERSION,
		true
	);

	wp_localize_script(
		'abf-main',
		'ABF_MAP',
		array(
			'leafletCss' => 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
			'leafletJs'  => 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
			'stores'     => abf_get_stores_for_map(),
			'center'     => array( 47.3, 6.15 ), // Centre approximatif Franche-Comté.
			'zoom'       => 9,
			'i18n'       => array(
				'near'     => __( 'Autour de moi', 'aux-belfleurs' ),
				'locating' => __( 'Localisation…', 'aux-belfleurs' ),
				'denied'   => __( "Géolocalisation refusée ou indisponible.", 'aux-belfleurs' ),
				'route'    => __( 'Itinéraire', 'aux-belfleurs' ),
				'noResult' => __( 'Aucune boutique ne correspond.', 'aux-belfleurs' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'abf_enqueue_scripts' );

/**
 * Préchargements pour améliorer le LCP.
 * Polices critiques sur toutes les pages du thème ; connexions carte sur l'accueil.
 */
function abf_resource_hints() {
	// Polices auto-hébergées (titre + texte) : preload avec crossorigin obligatoire.
	$fonts = array(
		'/assets/fonts/grand-hotel-400-latin.woff2',
		'/assets/fonts/lato-400-latin.woff2',
	);
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( ABF_URI . $font )
		);
	}

	// Sur l'accueil uniquement : préconnexion aux serveurs de la carte.
	if ( is_front_page() ) {
		echo '<link rel="preconnect" href="https://tile.openstreetmap.org" crossorigin>' . "\n";
		echo '<link rel="preconnect" href="https://unpkg.com" crossorigin>' . "\n";
	}
}
add_action( 'wp_head', 'abf_resource_hints', 1 );

/**
 * Réglages du thème enfant (support des logos, titre, etc.).
 */
function abf_theme_setup() {
	add_theme_support( 'custom-logo' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
}
add_action( 'after_setup_theme', 'abf_theme_setup' );
