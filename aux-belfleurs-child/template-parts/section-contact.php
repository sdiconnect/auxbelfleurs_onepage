<?php
/**
 * Section contact : coordonnées à gauche, formulaire 4 champs à droite.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$abf_infos    = abf_infos();
$abf_feedback = abf_contact_feedback();
?>
<section class="abf-contact-section" id="contact">
	<div class="abf-container abf-contact-layout">

		<div class="abf-contact-info">
			<h2><?php esc_html_e( 'Contact', 'aux-belfleurs' ); ?></h2>

			<address class="abf-contact-address">
				<strong><?php echo esc_html( $abf_infos['nom'] ); ?></strong><br>
				<?php echo esc_html( $abf_infos['adresse'] ); ?><br>
				<?php echo esc_html( $abf_infos['cp'] . ' ' . $abf_infos['ville'] ); ?>
			</address>

			<p>
				<a href="tel:<?php echo esc_attr( abf_tel_href( $abf_infos['telephone'] ) ); ?>">
					<?php echo esc_html( $abf_infos['telephone'] ); ?>
				</a>
			</p>
			<p>
				<?php
				// antispambot() encode l'e-mail en entités HTML : on l'affiche tel quel
				// (sans esc_attr/esc_html qui ré-encoderaient les entités et casseraient le lien).
				$abf_email_obf = antispambot( $abf_infos['email'] );
				?>
				<a href="mailto:<?php echo $abf_email_obf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
					<?php echo $abf_email_obf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			</p>

			<div class="abf-social">
				<a href="<?php echo esc_url( $abf_infos['instagram'] ); ?>" target="_blank" rel="noopener me">Instagram</a>
				<a href="<?php echo esc_url( $abf_infos['facebook'] ); ?>" target="_blank" rel="noopener me">Facebook</a>
			</div>
		</div>

		<div class="abf-contact-form-wrap">
			<?php if ( $abf_feedback ) : ?>
				<p class="abf-form-feedback abf-form-feedback--<?php echo esc_attr( $abf_feedback['type'] ); ?>" role="status">
					<?php echo esc_html( $abf_feedback['text'] ); ?>
				</p>
			<?php endif; ?>

			<form class="abf-contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="abf_contact">
				<?php wp_nonce_field( 'abf_contact', 'abf_contact_nonce' ); ?>

				<!-- Honeypot anti-spam : ne pas remplir. Masqué aux humains. -->
				<div class="abf-hp" aria-hidden="true">
					<label for="abf_website"><?php esc_html_e( 'Ne pas remplir', 'aux-belfleurs' ); ?></label>
					<input type="text" id="abf_website" name="abf_website" tabindex="-1" autocomplete="off">
				</div>

				<p class="abf-field">
					<label for="abf_nom"><?php esc_html_e( 'Nom', 'aux-belfleurs' ); ?> <span aria-hidden="true">*</span></label>
					<input type="text" id="abf_nom" name="abf_nom" required autocomplete="name">
				</p>

				<p class="abf-field">
					<label for="abf_email"><?php esc_html_e( 'E-mail', 'aux-belfleurs' ); ?> <span aria-hidden="true">*</span></label>
					<input type="email" id="abf_email" name="abf_email" required autocomplete="email">
				</p>

				<p class="abf-field">
					<label for="abf_message"><?php esc_html_e( 'Message', 'aux-belfleurs' ); ?> <span aria-hidden="true">*</span></label>
					<textarea id="abf_message" name="abf_message" rows="5" required></textarea>
				</p>

				<p class="abf-field abf-field--check">
					<label>
						<input type="checkbox" name="abf_rgpd" value="1" required>
						<?php esc_html_e( "J'accepte que mes informations soient utilisées pour me recontacter. Elles ne sont ni revendues ni cédées.", 'aux-belfleurs' ); ?>
					</label>
				</p>

				<p>
					<button type="submit" class="abf-btn abf-btn--primary">
						<?php esc_html_e( 'Envoyer', 'aux-belfleurs' ); ?>
					</button>
				</p>
			</form>
		</div>

	</div>
</section>
