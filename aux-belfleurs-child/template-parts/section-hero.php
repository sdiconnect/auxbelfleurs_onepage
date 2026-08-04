<?php
/**
 * Hero très court : logo, phrase, sous-phrase, un seul bouton vers la carte.
 * Une seule belle photo en fond.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$abf_count = abf_count_stores();
$abf_hero  = ABF_URI . '/assets/images/hero.webp';
?>
<section class="abf-hero" id="accueil" style="--abf-hero-img:url('<?php echo esc_url( $abf_hero ); ?>');">
	<div class="abf-hero__overlay"></div>
	<div class="abf-container abf-hero__inner">
		<?php if ( has_custom_logo() ) : ?>
			<div class="abf-hero__logo"><?php the_custom_logo(); ?></div>
		<?php endif; ?>

		<h1 class="abf-hero__title">
			<?php esc_html_e( 'Savons et cosmétiques solides bio, fabriqués à la main en Franche-Comté', 'aux-belfleurs' ); ?>
		</h1>

		<p class="abf-hero__subtitle">
			<?php
			if ( $abf_count > 0 ) {
				printf(
					/* translators: %d: nombre de boutiques */
					esc_html( _n( 'Retrouvez mes produits dans %d boutique', 'Retrouvez mes produits dans %d boutiques', $abf_count, 'aux-belfleurs' ) ),
					(int) $abf_count
				);
			} else {
				esc_html_e( 'Retrouvez mes produits près de chez vous', 'aux-belfleurs' );
			}
			?>
		</p>

		<a class="abf-btn abf-btn--primary abf-hero__cta" href="#carte">
			<?php esc_html_e( 'Où m\'acheter', 'aux-belfleurs' ); ?>
		</a>
	</div>
</section>
