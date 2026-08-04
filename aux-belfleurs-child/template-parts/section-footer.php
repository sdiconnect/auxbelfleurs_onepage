<?php
/**
 * Footer d'une ligne : mentions légales, confidentialité, copyright.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$abf_mentions = get_page_by_path( 'mentions-legales' );
$abf_privacy  = get_privacy_policy_url();
?>
<footer class="abf-footer">
	<div class="abf-container abf-footer__inner">
		<?php if ( $abf_mentions ) : ?>
			<a href="<?php echo esc_url( get_permalink( $abf_mentions ) ); ?>"><?php esc_html_e( 'Mentions légales', 'aux-belfleurs' ); ?></a>
			<span class="abf-footer__sep" aria-hidden="true">·</span>
		<?php endif; ?>
		<?php if ( $abf_privacy ) : ?>
			<a href="<?php echo esc_url( $abf_privacy ); ?>"><?php esc_html_e( 'Politique de confidentialité', 'aux-belfleurs' ); ?></a>
			<span class="abf-footer__sep" aria-hidden="true">·</span>
		<?php endif; ?>
		<p class="abf-footer__copy">
			© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( abf_infos()['nom'] ); ?>
		</p>
	</div>
</footer>
