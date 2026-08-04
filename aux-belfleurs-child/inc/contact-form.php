<?php
/**
 * Formulaire de contact : traitement serveur.
 *
 * Sécurité : nonce, honeypot, validation serveur.
 * Envoi : wp_mail avec Reply-To du visiteur.
 * Robustesse : chaque message est aussi enregistré en base (CPT abf_message),
 * pour ne rien perdre si le SMTP flanche.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre le CPT privé stockant les messages reçus.
 */
function abf_register_message_cpt() {
	register_post_type(
		'abf_message',
		array(
			'labels'          => array(
				'name'          => __( 'Messages', 'aux-belfleurs' ),
				'singular_name' => __( 'Message', 'aux-belfleurs' ),
				'menu_name'     => __( 'Messages reçus', 'aux-belfleurs' ),
				'all_items'     => __( 'Messages reçus', 'aux-belfleurs' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-email',
			'menu_position'   => 26,
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'supports'        => array( 'title', 'editor' ),
			'has_archive'     => false,
		)
	);
}
add_action( 'init', 'abf_register_message_cpt' );

/**
 * Colonnes de la liste des messages (e-mail + date).
 *
 * @param array $columns Colonnes.
 * @return array
 */
function abf_message_columns( $columns ) {
	return array(
		'cb'         => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'      => __( 'Expéditeur', 'aux-belfleurs' ),
		'abf_email'  => __( 'E-mail', 'aux-belfleurs' ),
		'date'       => __( 'Reçu le', 'aux-belfleurs' ),
	);
}
add_filter( 'manage_abf_message_posts_columns', 'abf_message_columns' );

/**
 * Contenu de la colonne e-mail.
 *
 * @param string $column  Clé.
 * @param int    $post_id ID.
 */
function abf_message_column_content( $column, $post_id ) {
	if ( 'abf_email' === $column ) {
		$email = get_post_meta( $post_id, '_abf_email', true );
		if ( $email ) {
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
		}
	}
}
add_action( 'manage_abf_message_posts_custom_column', 'abf_message_column_content', 10, 2 );

/**
 * Traite la soumission du formulaire de contact.
 * Branché sur admin-post (utilisateurs connectés et non connectés).
 */
function abf_handle_contact() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	// Nonce.
	if ( ! isset( $_POST['abf_contact_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['abf_contact_nonce'] ), 'abf_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) . '#contact' );
		exit;
	}

	// Honeypot : champ « site » qui doit rester vide.
	if ( ! empty( $_POST['abf_website'] ) ) {
		// Spam probable : on fait comme si tout allait bien, sans rien envoyer.
		wp_safe_redirect( add_query_arg( 'contact', 'success', $redirect ) . '#contact' );
		exit;
	}

	// RGPD.
	if ( empty( $_POST['abf_rgpd'] ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'rgpd', $redirect ) . '#contact' );
		exit;
	}

	// Champs.
	$nom     = isset( $_POST['abf_nom'] ) ? sanitize_text_field( wp_unslash( $_POST['abf_nom'] ) ) : '';
	$email   = isset( $_POST['abf_email'] ) ? sanitize_email( wp_unslash( $_POST['abf_email'] ) ) : '';
	$message = isset( $_POST['abf_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['abf_message'] ) ) : '';

	// Validation serveur.
	if ( '' === $nom || '' === $message || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'invalid', $redirect ) . '#contact' );
		exit;
	}

	// 1) Enregistrement en base (filet de sécurité si le SMTP échoue).
	$post_id = wp_insert_post(
		array(
			'post_type'    => 'abf_message',
			'post_status'  => 'publish',
			/* translators: %s: nom de l'expéditeur */
			'post_title'   => sprintf( __( 'Message de %s', 'aux-belfleurs' ), $nom ),
			'post_content' => $message,
		),
		true
	);
	if ( ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_abf_email', $email );
		update_post_meta( $post_id, '_abf_nom', $nom );
	}

	// 2) Envoi de l'e-mail.
	$infos   = abf_infos();
	$to      = get_option( 'admin_email' );
	/* translators: %s: nom de l'expéditeur */
	$subject = sprintf( __( '[Site] Nouveau message de %s', 'aux-belfleurs' ), $nom );
	$body    = sprintf(
		"%s : %s\n%s : %s\n\n%s :\n%s\n",
		__( 'Nom', 'aux-belfleurs' ),
		$nom,
		__( 'E-mail', 'aux-belfleurs' ),
		$email,
		__( 'Message', 'aux-belfleurs' ),
		$message
	);
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		sprintf( 'Reply-To: %s <%s>', $nom, $email ),
	);

	$sent = wp_mail( $to, $subject, $body, $headers );

	$status = $sent ? 'success' : 'saved'; // « saved » = enregistré mais e-mail non parti.
	wp_safe_redirect( add_query_arg( 'contact', $status, $redirect ) . '#contact' );
	exit;
}
add_action( 'admin_post_abf_contact', 'abf_handle_contact' );
add_action( 'admin_post_nopriv_abf_contact', 'abf_handle_contact' );

/**
 * Renvoie le message de retour à afficher au-dessus du formulaire.
 *
 * @return array|null [ 'type' => 'success|error', 'text' => '...' ]
 */
function abf_contact_feedback() {
	if ( ! isset( $_GET['contact'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return null;
	}
	$code = sanitize_key( wp_unslash( $_GET['contact'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

	$messages = array(
		'success' => array( 'success', __( 'Message envoyé', 'aux-belfleurs' ), __( 'Merci, je vous réponds sous 48 h.', 'aux-belfleurs' ) ),
		'saved'   => array( 'success', __( 'Message enregistré', 'aux-belfleurs' ), __( 'Merci, je vous réponds sous 48 h.', 'aux-belfleurs' ) ),
		'error'   => array( 'error', __( 'Une erreur est survenue', 'aux-belfleurs' ), __( 'Merci de réessayer dans un instant.', 'aux-belfleurs' ) ),
		'invalid' => array( 'error', __( 'Il manque une information', 'aux-belfleurs' ), __( 'Merci de vérifier votre nom, votre e-mail et votre message.', 'aux-belfleurs' ) ),
		'rgpd'    => array( 'error', __( 'Consentement requis', 'aux-belfleurs' ), __( 'Merci de cocher la case avant d\'envoyer votre message.', 'aux-belfleurs' ) ),
	);

	return isset( $messages[ $code ] )
		? array(
			'type'  => $messages[ $code ][0],
			'title' => $messages[ $code ][1],
			'text'  => $messages[ $code ][2],
		)
		: null;
}
