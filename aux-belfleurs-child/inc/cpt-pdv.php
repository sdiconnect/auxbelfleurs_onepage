<?php
/**
 * CPT « Points de vente » (abf_pdv) + métaboxes natives (sans ACF).
 *
 * Champs volontairement réduits : adresse, code postal, ville, téléphone,
 * latitude, longitude, visible. Le titre du post sert de nom de boutique.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Liste des champs méta gérés, avec leur clé de stockage et libellé.
 *
 * @return array
 */
function abf_pdv_fields() {
	return array(
		'adresse'   => __( 'Adresse', 'aux-belfleurs' ),
		'code_postal' => __( 'Code postal', 'aux-belfleurs' ),
		'ville'     => __( 'Ville', 'aux-belfleurs' ),
		'telephone' => __( 'Téléphone', 'aux-belfleurs' ),
		'latitude'  => __( 'Latitude', 'aux-belfleurs' ),
		'longitude' => __( 'Longitude', 'aux-belfleurs' ),
	);
}

/**
 * Enregistre le type de contenu « Points de vente ».
 */
function abf_register_pdv() {
	$labels = array(
		'name'               => __( 'Points de vente', 'aux-belfleurs' ),
		'singular_name'      => __( 'Point de vente', 'aux-belfleurs' ),
		'menu_name'          => __( 'Points de vente', 'aux-belfleurs' ),
		'add_new'            => __( 'Ajouter', 'aux-belfleurs' ),
		'add_new_item'       => __( 'Ajouter un point de vente', 'aux-belfleurs' ),
		'edit_item'          => __( 'Modifier le point de vente', 'aux-belfleurs' ),
		'new_item'           => __( 'Nouveau point de vente', 'aux-belfleurs' ),
		'view_item'          => __( 'Voir le point de vente', 'aux-belfleurs' ),
		'search_items'       => __( 'Rechercher un point de vente', 'aux-belfleurs' ),
		'not_found'          => __( 'Aucun point de vente', 'aux-belfleurs' ),
		'not_found_in_trash' => __( 'Aucun point de vente dans la corbeille', 'aux-belfleurs' ),
		'all_items'          => __( 'Tous les points de vente', 'aux-belfleurs' ),
	);

	register_post_type(
		'abf_pdv',
		array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'menu_icon'           => 'dashicons-store',
			'menu_position'       => 25,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
		)
	);
}
add_action( 'init', 'abf_register_pdv' );

/**
 * Ajoute la métabox unique regroupant tous les champs.
 */
function abf_add_pdv_metabox() {
	add_meta_box(
		'abf_pdv_infos',
		__( 'Coordonnées du point de vente', 'aux-belfleurs' ),
		'abf_render_pdv_metabox',
		'abf_pdv',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'abf_add_pdv_metabox' );

/**
 * Affiche la métabox.
 *
 * @param WP_Post $post Post courant.
 */
function abf_render_pdv_metabox( $post ) {
	wp_nonce_field( 'abf_save_pdv', 'abf_pdv_nonce' );

	$values  = array();
	foreach ( abf_pdv_fields() as $key => $label ) {
		$values[ $key ] = get_post_meta( $post->ID, '_abf_' . $key, true );
	}
	$visible = get_post_meta( $post->ID, '_abf_visible', true );
	// Par défaut visible pour un nouveau post (aucune méta enregistrée).
	$is_new  = ( '' === get_post_meta( $post->ID, '_abf_visible', true ) && 'auto-draft' === $post->post_status );
	$checked = ( '1' === $visible || $is_new ) ? 'checked' : '';

	$status  = get_post_meta( $post->ID, '_abf_geo_status', true );
	?>
	<style>
		.abf-metabox label { display:block; font-weight:600; margin:12px 0 4px; }
		.abf-metabox input[type="text"] { width:100%; max-width:520px; }
		.abf-metabox .abf-row { display:flex; gap:20px; flex-wrap:wrap; }
		.abf-metabox .abf-row > div { flex:1; min-width:180px; }
		.abf-hint { color:#666; font-weight:400; font-size:12px; margin-top:2px; }
		.abf-geo-ok   { color:#137333; }
		.abf-geo-fail { color:#b32d2e; }
		.abf-geo-manual { color:#8250df; }
	</style>
	<div class="abf-metabox">
		<label for="abf_adresse"><?php esc_html_e( 'Adresse', 'aux-belfleurs' ); ?></label>
		<input type="text" id="abf_adresse" name="abf_adresse" value="<?php echo esc_attr( $values['adresse'] ); ?>" placeholder="<?php esc_attr_e( 'ex. 12 rue des Fleurs', 'aux-belfleurs' ); ?>">

		<div class="abf-row">
			<div>
				<label for="abf_code_postal"><?php esc_html_e( 'Code postal', 'aux-belfleurs' ); ?></label>
				<input type="text" id="abf_code_postal" name="abf_code_postal" value="<?php echo esc_attr( $values['code_postal'] ); ?>" inputmode="numeric" pattern="[0-9]{5}">
			</div>
			<div>
				<label for="abf_ville"><?php esc_html_e( 'Ville', 'aux-belfleurs' ); ?></label>
				<input type="text" id="abf_ville" name="abf_ville" value="<?php echo esc_attr( $values['ville'] ); ?>">
			</div>
		</div>

		<label for="abf_telephone"><?php esc_html_e( 'Téléphone', 'aux-belfleurs' ); ?></label>
		<input type="text" id="abf_telephone" name="abf_telephone" value="<?php echo esc_attr( $values['telephone'] ); ?>">

		<div class="abf-row">
			<div>
				<label for="abf_latitude"><?php esc_html_e( 'Latitude', 'aux-belfleurs' ); ?></label>
				<input type="text" id="abf_latitude" name="abf_latitude" value="<?php echo esc_attr( $values['latitude'] ); ?>" placeholder="47.4460">
				<p class="abf-hint"><?php esc_html_e( 'Laissez vide pour un géocodage automatique à l\'enregistrement.', 'aux-belfleurs' ); ?></p>
			</div>
			<div>
				<label for="abf_longitude"><?php esc_html_e( 'Longitude', 'aux-belfleurs' ); ?></label>
				<input type="text" id="abf_longitude" name="abf_longitude" value="<?php echo esc_attr( $values['longitude'] ); ?>" placeholder="6.1580">
				<p class="abf-hint"><?php esc_html_e( 'Corrigez ici si le point tombe au mauvais endroit.', 'aux-belfleurs' ); ?></p>
			</div>
		</div>

		<?php if ( $status ) : ?>
			<p class="abf-hint">
				<?php esc_html_e( 'Géocodage :', 'aux-belfleurs' ); ?>
				<?php if ( 'ok' === $status ) : ?>
					<span class="abf-geo-ok"><?php esc_html_e( '✔ automatique réussi', 'aux-belfleurs' ); ?></span>
				<?php elseif ( 'manual' === $status ) : ?>
					<span class="abf-geo-manual"><?php esc_html_e( '✎ coordonnées saisies manuellement', 'aux-belfleurs' ); ?></span>
				<?php else : ?>
					<span class="abf-geo-fail"><?php esc_html_e( '✘ échec — vérifiez l\'adresse ou saisissez les coordonnées à la main', 'aux-belfleurs' ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<p style="margin-top:16px;">
			<label style="display:inline; font-weight:600;">
				<input type="checkbox" name="abf_visible" value="1" <?php echo esc_attr( $checked ); ?>>
				<?php esc_html_e( 'Visible sur le site (afficher ce point de vente sur la carte et la liste)', 'aux-belfleurs' ); ?>
			</label>
		</p>
	</div>
	<?php
}

/**
 * Enregistre les champs de la métabox.
 *
 * @param int $post_id ID du post.
 */
function abf_save_pdv( $post_id ) {
	if ( ! isset( $_POST['abf_pdv_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['abf_pdv_nonce'] ), 'abf_save_pdv' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'abf_pdv' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Champs texte.
	$sanitizers = array(
		'adresse'     => 'sanitize_text_field',
		'code_postal' => 'sanitize_text_field',
		'ville'       => 'sanitize_text_field',
		'telephone'   => 'sanitize_text_field',
	);
	foreach ( $sanitizers as $key => $fn ) {
		$val = isset( $_POST[ 'abf_' . $key ] ) ? call_user_func( $fn, wp_unslash( $_POST[ 'abf_' . $key ] ) ) : '';
		update_post_meta( $post_id, '_abf_' . $key, $val );
	}

	// Coordonnées : on retient ce que l'utilisateur a saisi (avant géocodage auto).
	$lat_in = isset( $_POST['abf_latitude'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['abf_latitude'] ) ) ) : '';
	$lng_in = isset( $_POST['abf_longitude'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['abf_longitude'] ) ) ) : '';
	$lat_in = str_replace( ',', '.', $lat_in );
	$lng_in = str_replace( ',', '.', $lng_in );

	update_post_meta( $post_id, '_abf_latitude', $lat_in );
	update_post_meta( $post_id, '_abf_longitude', $lng_in );

	// Case visible.
	$visible = isset( $_POST['abf_visible'] ) ? '1' : '0';
	update_post_meta( $post_id, '_abf_visible', $visible );

	// Géocodage (voir inc/geocoding.php). Ne géocode que si lat/lng vides.
	abf_maybe_geocode( $post_id, $lat_in, $lng_in );
}
add_action( 'save_post_abf_pdv', 'abf_save_pdv' );
