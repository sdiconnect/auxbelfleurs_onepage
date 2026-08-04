<?php
/**
 * Hero court : photo plein cadre, logo sur plaque crème, titre, sous-phrase,
 * un seul bouton vers la carte. Aligné sur la maquette hifi.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$abf_count = abf_count_stores();
/**
 * Filtre l'URL de la photo du hero (permet de la remplacer sans toucher au thème).
 */
$abf_hero_img = apply_filters( 'abf_hero_image', abf_img( 'hero-savons.webp' ) );
?>
<section class="abf-hero" id="accueil">
	<?php if ( $abf_hero_img ) : ?>
		<img class="abf-hero__img" src="<?php echo esc_url( $abf_hero_img ); ?>" alt="" fetchpriority="high" decoding="async">
	<?php endif; ?>
	<div class="abf-hero__overlay"></div>

	<div class="abf-hero__inner">
		<div class="abf-hero__logo">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( abf_img( 'logo.webp' ) ); ?>" alt="<?php echo esc_attr( abf_infos()['nom'] ); ?>" width="168" height="74">
			<?php endif; ?>
		</div>

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
