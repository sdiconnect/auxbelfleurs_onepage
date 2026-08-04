<?php
/**
 * Géocodage automatique via Nominatim / OpenStreetMap (gratuit, sans clé).
 *
 * Politique d'usage Nominatim respectée : User-Agent identifiant, 1 requête/s
 * max (une seule requête par enregistrement), pas d'usage massif automatisé.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Géocode un point de vente si nécessaire.
 *
 * - Si l'utilisateur a fourni lat + lng : on ne géocode pas (statut « manual »).
 * - Sinon on interroge Nominatim et on remplit les coordonnées.
 *
 * @param int    $post_id ID du point de vente.
 * @param string $lat_in  Latitude saisie (peut être vide).
 * @param string $lng_in  Longitude saisie (peut être vide).
 */
function abf_maybe_geocode( $post_id, $lat_in, $lng_in ) {
	if ( '' !== $lat_in && '' !== $lng_in && is_numeric( $lat_in ) && is_numeric( $lng_in ) ) {
		update_post_meta( $post_id, '_abf_geo_status', 'manual' );
		return;
	}

	$adresse = get_post_meta( $post_id, '_abf_adresse', true );
	$cp      = get_post_meta( $post_id, '_abf_code_postal', true );
	$ville   = get_post_meta( $post_id, '_abf_ville', true );

	$coords = abf_geocode( $adresse, $cp, $ville );

	if ( $coords ) {
		update_post_meta( $post_id, '_abf_latitude', $coords['lat'] );
		update_post_meta( $post_id, '_abf_longitude', $coords['lng'] );
		update_post_meta( $post_id, '_abf_geo_status', 'ok' );
		delete_post_meta( $post_id, '_abf_geo_notice' );
	} else {
		update_post_meta( $post_id, '_abf_geo_status', 'failed' );
		update_post_meta( $post_id, '_abf_geo_notice', '1' );
	}
}

/**
 * Interroge Nominatim et renvoie les coordonnées, ou false.
 *
 * @param string $adresse Adresse (facultative).
 * @param string $cp      Code postal.
 * @param string $ville   Ville.
 * @return array|false ['lat' => string, 'lng' => string] ou false.
 */
function abf_geocode( $adresse, $cp, $ville ) {
	// Il faut au moins une ville ou un code postal pour tenter le géocodage.
	if ( '' === trim( $cp ) && '' === trim( $ville ) ) {
		return false;
	}

	$query = trim( implode( ', ', array_filter( array( $adresse, trim( $cp . ' ' . $ville ), 'France' ) ) ) );

	$url = 'https://nominatim.openstreetmap.org/search?' . http_build_query(
		array(
			'q'            => $query,
			'format'       => 'jsonv2',
			'limit'        => 1,
			'countrycodes' => 'fr',
		)
	);

	$response = wp_remote_get(
		$url,
		array(
			'timeout'    => 8,
			'user-agent' => 'AuxBelfleurs-WP/1.0 (' . home_url( '/' ) . '; ' . get_option( 'admin_email' ) . ')',
			'headers'    => array( 'Accept' => 'application/json' ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}
	if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $data ) || ! isset( $data[0]['lat'], $data[0]['lon'] ) ) {
		return false;
	}

	return array(
		'lat' => number_format( (float) $data[0]['lat'], 6, '.', '' ),
		'lng' => number_format( (float) $data[0]['lon'], 6, '.', '' ),
	);
}

/**
 * Affiche un avertissement en admin quand un géocodage a échoué.
 */
function abf_geocode_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'abf_pdv' !== $screen->post_type ) {
		return;
	}

	// Sur l'écran d'édition d'un point de vente en échec.
	if ( 'post' === $screen->base && isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$post_id = (int) $_GET['post']; // phpcs:ignore WordPress.Security.NonceVerification
		if ( '1' === get_post_meta( $post_id, '_abf_geo_notice', true ) ) {
			echo '<div class="notice notice-warning"><p><strong>Aux Bêl\'fleurs :</strong> ';
			echo esc_html__( "le géocodage automatique a échoué pour ce point de vente. Vérifiez l'adresse / la ville, ou saisissez la latitude et la longitude à la main (repérez le point sur openstreetmap.org, clic droit « Afficher l'adresse »).", 'aux-belfleurs' );
			echo '</p></div>';
		}
	}

	// Sur la liste : compte global des échecs.
	if ( 'edit' === $screen->base ) {
		$failed = get_posts(
			array(
				'post_type'      => 'abf_pdv',
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_abf_geo_status',
						'value' => 'failed',
					),
				),
			)
		);
		if ( ! empty( $failed ) ) {
			printf(
				'<div class="notice notice-warning"><p><strong>Aux Bêl\'fleurs :</strong> %s</p></div>',
				esc_html( sprintf( _n( '%d point de vente n\'a pas pu être géocodé (marqueur absent de la carte).', '%d points de vente n\'ont pas pu être géocodés (marqueurs absents de la carte).', count( $failed ), 'aux-belfleurs' ), count( $failed ) ) )
			);
		}
	}
}
add_action( 'admin_notices', 'abf_geocode_admin_notice' );
