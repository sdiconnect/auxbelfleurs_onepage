<?php
/**
 * Récupération des points de vente pour l'affichage (front).
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Table des départements gérés (préfixe code postal => nom).
 *
 * @return array
 */
function abf_departements() {
	return array(
		'21' => "Côte-d'Or",
		'25' => 'Doubs',
		'70' => 'Haute-Saône',
		'88' => 'Vosges',
		'90' => 'Territoire de Belfort',
		'39' => 'Jura',
	);
}

/**
 * Nom du département à partir d'un code postal.
 *
 * @param string $cp Code postal.
 * @return string
 */
function abf_departement_from_cp( $cp ) {
	$prefix = substr( preg_replace( '/\D/', '', (string) $cp ), 0, 2 );
	$deps   = abf_departements();
	return isset( $deps[ $prefix ] ) ? $deps[ $prefix ] : __( 'Autres', 'aux-belfleurs' );
}

/**
 * Récupère tous les points de vente visibles, normalisés.
 *
 * @return array Liste de tableaux associatifs.
 */
function abf_get_stores() {
	$posts = get_posts(
		array(
			'post_type'      => 'abf_pdv',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => '_abf_visible',
					'value' => '1',
				),
			),
		)
	);

	$stores = array();
	foreach ( $posts as $post ) {
		$cp   = get_post_meta( $post->ID, '_abf_code_postal', true );
		$lat  = get_post_meta( $post->ID, '_abf_latitude', true );
		$lng  = get_post_meta( $post->ID, '_abf_longitude', true );

		$stores[] = array(
			'id'          => $post->ID,
			'nom'         => get_the_title( $post ),
			'adresse'     => get_post_meta( $post->ID, '_abf_adresse', true ),
			'code_postal' => $cp,
			'ville'       => get_post_meta( $post->ID, '_abf_ville', true ),
			'telephone'   => get_post_meta( $post->ID, '_abf_telephone', true ),
			'lat'         => ( '' !== $lat && is_numeric( $lat ) ) ? (float) $lat : null,
			'lng'         => ( '' !== $lng && is_numeric( $lng ) ) ? (float) $lng : null,
			'departement' => abf_departement_from_cp( $cp ),
		);
	}

	return $stores;
}

/**
 * Points de vente groupés par département, triés (département puis ville).
 *
 * @return array [ 'Haute-Saône' => [ store, ... ], ... ]
 */
function abf_get_stores_grouped() {
	$stores = abf_get_stores();
	$groups = array();

	foreach ( $stores as $store ) {
		$groups[ $store['departement'] ][] = $store;
	}

	// Tri des départements par nom, et des boutiques par ville puis nom.
	ksort( $groups );
	foreach ( $groups as &$list ) {
		usort(
			$list,
			function ( $a, $b ) {
				$c = strcasecmp( $a['ville'], $b['ville'] );
				return 0 !== $c ? $c : strcasecmp( $a['nom'], $b['nom'] );
			}
		);
	}
	unset( $list );

	return $groups;
}

/**
 * Données minimales des points de vente géolocalisés, pour le JS de la carte.
 *
 * @return array
 */
function abf_get_stores_for_map() {
	$out = array();
	foreach ( abf_get_stores() as $s ) {
		if ( null === $s['lat'] || null === $s['lng'] ) {
			continue; // Pas de marqueur sans coordonnées.
		}
		$out[] = array(
			'id'    => $s['id'],
			'nom'   => $s['nom'],
			'adr'   => trim( $s['adresse'] ),
			'cp'    => $s['code_postal'],
			'ville' => $s['ville'],
			'tel'   => $s['telephone'],
			'lat'   => $s['lat'],
			'lng'   => $s['lng'],
			'dep'   => $s['departement'],
		);
	}
	return $out;
}

/**
 * Nombre de points de vente visibles (pour le hero).
 *
 * @return int
 */
function abf_count_stores() {
	return count( abf_get_stores() );
}
