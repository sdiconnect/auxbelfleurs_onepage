<?php
/**
 * Import CSV des points de vente (saisie initiale des boutiques).
 *
 * Page d'admin sous le menu « Points de vente ». Colonnes attendues :
 * nom, adresse, code_postal, ville, telephone, latitude, longitude, visible
 *
 * Le géocodage automatique est appliqué aux lignes sans latitude/longitude,
 * avec une pause d'1 s entre chaque requête (politique Nominatim).
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajoute la page d'import sous le menu du CPT.
 */
function abf_csv_import_menu() {
	add_submenu_page(
		'edit.php?post_type=abf_pdv',
		__( 'Importer un CSV', 'aux-belfleurs' ),
		__( 'Importer un CSV', 'aux-belfleurs' ),
		'manage_options',
		'abf-import-csv',
		'abf_csv_import_page'
	);
}
add_action( 'admin_menu', 'abf_csv_import_menu' );

/**
 * Affiche la page d'import et traite l'envoi.
 */
function abf_csv_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = null;
	if ( isset( $_POST['abf_csv_submit'] ) ) {
		check_admin_referer( 'abf_csv_import', 'abf_csv_nonce' );
		$report = abf_process_csv_upload();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Importer des points de vente (CSV)', 'aux-belfleurs' ); ?></h1>

		<?php if ( is_array( $report ) ) : ?>
			<div class="notice notice-<?php echo $report['errors'] ? 'warning' : 'success'; ?>">
				<p>
					<?php
					printf(
						/* translators: 1: nombre créés, 2: nombre géocodés, 3: nombre en échec */
						esc_html__( '%1$d point(s) de vente importé(s), %2$d géocodé(s) automatiquement, %3$d en échec de géocodage.', 'aux-belfleurs' ),
						(int) $report['created'],
						(int) $report['geocoded'],
						(int) $report['geo_failed']
					);
					?>
				</p>
				<?php if ( ! empty( $report['messages'] ) ) : ?>
					<ul style="list-style:disc;margin-left:20px;">
						<?php foreach ( $report['messages'] as $m ) : ?>
							<li><?php echo esc_html( $m ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<p><?php esc_html_e( 'Le fichier CSV doit contenir un en-tête avec ces colonnes (l\'ordre est libre) :', 'aux-belfleurs' ); ?></p>
		<p><code>nom, adresse, code_postal, ville, telephone, latitude, longitude, visible</code></p>
		<p><?php esc_html_e( 'Seul « nom » est obligatoire. Les lignes sans latitude/longitude sont géocodées automatiquement (comptez environ 1 seconde par ligne). « visible » : 1 ou 0.', 'aux-belfleurs' ); ?></p>

		<form method="post" enctype="multipart/form-data" style="margin-top:20px;">
			<?php wp_nonce_field( 'abf_csv_import', 'abf_csv_nonce' ); ?>
			<input type="file" name="abf_csv_file" accept=".csv,text/csv" required>
			<p>
				<label>
					<input type="checkbox" name="abf_csv_geocode" value="1" checked>
					<?php esc_html_e( 'Géocoder automatiquement les lignes sans coordonnées', 'aux-belfleurs' ); ?>
				</label>
			</p>
			<p>
				<button type="submit" name="abf_csv_submit" value="1" class="button button-primary">
					<?php esc_html_e( 'Importer', 'aux-belfleurs' ); ?>
				</button>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Traite le fichier CSV envoyé.
 *
 * @return array|WP_Error Rapport d'import.
 */
function abf_process_csv_upload() {
	$report = array(
		'created'    => 0,
		'geocoded'   => 0,
		'geo_failed' => 0,
		'errors'     => 0,
		'messages'   => array(),
	);

	if ( empty( $_FILES['abf_csv_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['abf_csv_file']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$report['errors']     = 1;
		$report['messages'][] = __( 'Aucun fichier reçu.', 'aux-belfleurs' );
		return $report;
	}

	$do_geocode = ! empty( $_POST['abf_csv_geocode'] );
	$path       = $_FILES['abf_csv_file']['tmp_name']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$handle     = fopen( $path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	if ( ! $handle ) {
		$report['errors']     = 1;
		$report['messages'][] = __( 'Impossible de lire le fichier.', 'aux-belfleurs' );
		return $report;
	}

	// Détection du séparateur sur la première ligne (virgule ou point-virgule).
	$first_line = fgets( $handle );
	rewind( $handle );
	$delimiter = ( substr_count( $first_line, ';' ) > substr_count( $first_line, ',' ) ) ? ';' : ',';

	// En-tête.
	$header = fgetcsv( $handle, 0, $delimiter );
	if ( ! $header ) {
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		$report['errors']     = 1;
		$report['messages'][] = __( 'Fichier vide ou en-tête manquant.', 'aux-belfleurs' );
		return $report;
	}
	// Normalisation des en-têtes (minuscules, sans BOM/espaces/accents simples).
	$header = array_map(
		function ( $h ) {
			$h = strtolower( trim( (string) $h ) );
			$h = str_replace( "\xEF\xBB\xBF", '', $h ); // BOM UTF-8.
			$h = strtr( $h, array( 'é' => 'e', 'è' => 'e' ) );
			return $h;
		},
		$header
	);

	$row_num = 1;
	while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
		++$row_num;
		if ( count( array_filter( $row, 'strlen' ) ) === 0 ) {
			continue; // Ligne vide.
		}
		$data = array_combine( $header, array_pad( array_slice( $row, 0, count( $header ) ), count( $header ), '' ) );

		$nom = isset( $data['nom'] ) ? sanitize_text_field( $data['nom'] ) : '';
		if ( '' === $nom ) {
			$report['messages'][] = sprintf( /* translators: %d: numéro de ligne */ __( 'Ligne %d ignorée : nom manquant.', 'aux-belfleurs' ), $row_num );
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'abf_pdv',
				'post_status' => 'publish',
				'post_title'  => $nom,
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			$report['errors']++;
			$report['messages'][] = sprintf( /* translators: %d: numéro de ligne */ __( 'Ligne %d : erreur de création.', 'aux-belfleurs' ), $row_num );
			continue;
		}

		$adresse = isset( $data['adresse'] ) ? sanitize_text_field( $data['adresse'] ) : '';
		$cp      = isset( $data['code_postal'] ) ? sanitize_text_field( $data['code_postal'] ) : '';
		$ville   = isset( $data['ville'] ) ? sanitize_text_field( $data['ville'] ) : '';
		$tel     = isset( $data['telephone'] ) ? sanitize_text_field( $data['telephone'] ) : '';
		$lat     = isset( $data['latitude'] ) ? str_replace( ',', '.', trim( $data['latitude'] ) ) : '';
		$lng     = isset( $data['longitude'] ) ? str_replace( ',', '.', trim( $data['longitude'] ) ) : '';
		$visible = isset( $data['visible'] ) && in_array( trim( $data['visible'] ), array( '0', 'non', 'false', '' ), true ) ? '0' : '1';

		update_post_meta( $post_id, '_abf_adresse', $adresse );
		update_post_meta( $post_id, '_abf_code_postal', $cp );
		update_post_meta( $post_id, '_abf_ville', $ville );
		update_post_meta( $post_id, '_abf_telephone', $tel );
		update_post_meta( $post_id, '_abf_visible', $visible );

		$report['created']++;

		// Coordonnées fournies ?
		if ( '' !== $lat && '' !== $lng && is_numeric( $lat ) && is_numeric( $lng ) ) {
			update_post_meta( $post_id, '_abf_latitude', $lat );
			update_post_meta( $post_id, '_abf_longitude', $lng );
			update_post_meta( $post_id, '_abf_geo_status', 'manual' );
			continue;
		}

		update_post_meta( $post_id, '_abf_latitude', '' );
		update_post_meta( $post_id, '_abf_longitude', '' );

		if ( $do_geocode ) {
			$coords = abf_geocode( $adresse, $cp, $ville );
			if ( $coords ) {
				update_post_meta( $post_id, '_abf_latitude', $coords['lat'] );
				update_post_meta( $post_id, '_abf_longitude', $coords['lng'] );
				update_post_meta( $post_id, '_abf_geo_status', 'ok' );
				$report['geocoded']++;
			} else {
				update_post_meta( $post_id, '_abf_geo_status', 'failed' );
				update_post_meta( $post_id, '_abf_geo_notice', '1' );
				$report['geo_failed']++;
				$report['messages'][] = sprintf( /* translators: %s: nom de la boutique */ __( 'Géocodage échoué : %s (à corriger à la main).', 'aux-belfleurs' ), $nom );
			}
			// Respect de la politique Nominatim : 1 requête/seconde.
			sleep( 1 );
		}
	}

	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

	return $report;
}
