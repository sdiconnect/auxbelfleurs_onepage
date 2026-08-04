<?php
/**
 * Colonnes personnalisées dans la liste des points de vente :
 * ville + statut de géocodage, avec tri par ville.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Déclare les colonnes.
 *
 * @param array $columns Colonnes existantes.
 * @return array
 */
function abf_pdv_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['abf_ville']   = __( 'Ville', 'aux-belfleurs' );
			$new['abf_geo']     = __( 'Géocodage', 'aux-belfleurs' );
			$new['abf_visible'] = __( 'Visible', 'aux-belfleurs' );
		}
	}
	return $new;
}
add_filter( 'manage_abf_pdv_posts_columns', 'abf_pdv_columns' );

/**
 * Remplit les colonnes.
 *
 * @param string $column  Clé de colonne.
 * @param int    $post_id ID du post.
 */
function abf_pdv_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'abf_ville':
			$cp    = get_post_meta( $post_id, '_abf_code_postal', true );
			$ville = get_post_meta( $post_id, '_abf_ville', true );
			echo esc_html( trim( $cp . ' ' . $ville ) );
			break;

		case 'abf_geo':
			$status = get_post_meta( $post_id, '_abf_geo_status', true );
			$map    = array(
				'ok'     => array( '#137333', __( '✔ auto', 'aux-belfleurs' ) ),
				'manual' => array( '#8250df', __( '✎ manuel', 'aux-belfleurs' ) ),
				'failed' => array( '#b32d2e', __( '✘ échec', 'aux-belfleurs' ) ),
			);
			if ( isset( $map[ $status ] ) ) {
				printf( '<span style="color:%s;font-weight:600;">%s</span>', esc_attr( $map[ $status ][0] ), esc_html( $map[ $status ][1] ) );
			} else {
				echo '<span style="color:#999;">—</span>';
			}
			break;

		case 'abf_visible':
			$visible = get_post_meta( $post_id, '_abf_visible', true );
			echo '1' === $visible ? '✅' : '<span style="color:#999;">—</span>';
			break;
	}
}
add_action( 'manage_abf_pdv_posts_custom_column', 'abf_pdv_column_content', 10, 2 );

/**
 * Rend la colonne ville triable.
 *
 * @param array $columns Colonnes triables.
 * @return array
 */
function abf_pdv_sortable_columns( $columns ) {
	$columns['abf_ville'] = 'abf_ville';
	return $columns;
}
add_filter( 'manage_edit-abf_pdv_sortable_columns', 'abf_pdv_sortable_columns' );

/**
 * Applique le tri par ville (méta) dans l'admin.
 *
 * @param WP_Query $query Requête.
 */
function abf_pdv_sort( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'abf_pdv' !== $query->get( 'post_type' ) ) {
		return;
	}
	if ( 'abf_ville' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', '_abf_ville' );
		$query->set( 'orderby', 'meta_value' );
	}
}
add_action( 'pre_get_posts', 'abf_pdv_sort' );
