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
 * Déclaration des polices auto-hébergées.
 * Renvoie la liste des fichiers avec leur famille, graisse et plage Unicode.
 *
 * @return array
 */
function abf_font_files() {
	$latin     = 'U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD';
	$latin_ext = 'U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF';

	return array(
		array( 'Grand Hotel', 400, 'grand-hotel-400-latin.woff2', $latin ),
		array( 'Grand Hotel', 400, 'grand-hotel-400-latin-ext.woff2', $latin_ext ),
		array( 'Lato', 400, 'lato-400-latin.woff2', $latin ),
		array( 'Lato', 400, 'lato-400-latin-ext.woff2', $latin_ext ),
		array( 'Lato', 700, 'lato-700-latin.woff2', $latin ),
		array( 'Lato', 700, 'lato-700-latin-ext.woff2', $latin_ext ),
		array( 'Lato', 900, 'lato-900-latin.woff2', $latin ),
		array( 'Lato', 900, 'lato-900-latin-ext.woff2', $latin_ext ),
	);
}

/**
 * Injecte les @font-face EN LIGNE dans le <head>, avec des URL ABSOLUES.
 *
 * Plus robuste que le @font-face dans le fichier CSS : insensible à la
 * minification/combinaison des CSS et au « Remove Unused CSS » de WP Rocket
 * (qui, sur des chemins relatifs ou des feuilles optimisées, casse souvent les
 * polices personnalisées). Précédé du preload des 2 polices critiques (LCP).
 */
function abf_print_font_faces() {
	// Preload des polices visibles d'emblée (titre + texte), crossorigin obligatoire.
	foreach ( array( 'grand-hotel-400-latin.woff2', 'lato-400-latin.woff2' ) as $file ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( ABF_URI . '/assets/fonts/' . $file )
		);
	}

	echo '<style id="abf-fonts">' . "\n";
	foreach ( abf_font_files() as $f ) {
		list( $family, $weight, $file, $range ) = $f;
		printf(
			'@font-face{font-family:"%1$s";font-style:normal;font-weight:%2$d;font-display:swap;src:url("%3$s") format("woff2");unicode-range:%4$s;}' . "\n",
			esc_attr( $family ),
			(int) $weight,
			esc_url( ABF_URI . '/assets/fonts/' . $file ),
			esc_attr( $range )
		);
	}
	echo '</style>' . "\n";

	// Préconnexion aux serveurs de la carte, uniquement sur l'accueil.
	if ( is_front_page() ) {
		echo '<link rel="preconnect" href="https://tile.openstreetmap.org" crossorigin>' . "\n";
		echo '<link rel="preconnect" href="https://unpkg.com" crossorigin>' . "\n";
	}
}
add_action( 'wp_head', 'abf_print_font_faces', 1 );

/**
 * Empêche WP Rocket (Remove Unused CSS) de supprimer les polices personnalisées.
 * Sans effet si WP Rocket est absent.
 *
 * @param array $safelist Liste blanche RUCSS.
 * @return array
 */
function abf_rocket_font_safelist( $safelist ) {
	$safelist[] = 'Grand Hotel';
	$safelist[] = 'Lato';
	$safelist[] = 'abf-fonts';
	return $safelist;
}
add_filter( 'rocket_rucss_safelist', 'abf_rocket_font_safelist' );

/**
 * Exclut la feuille du thème de la suppression de CSS inutilisé (sécurité
 * supplémentaire pour conserver tous les styles de la page unique).
 *
 * @param array $excluded Motifs exclus.
 * @return array
 */
function abf_rocket_exclude_css( $excluded ) {
	$excluded[] = 'aux-belfleurs-child/assets/css/main.css';
	return $excluded;
}
add_filter( 'rocket_rucss_excluded_stylesheets', 'abf_rocket_exclude_css' );

/**
 * Réglages du thème enfant (support des logos, titre, etc.).
 */
function abf_theme_setup() {
	add_theme_support( 'custom-logo' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
}
add_action( 'after_setup_theme', 'abf_theme_setup' );
