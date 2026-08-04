<?php
/**
 * Header minimaliste : logo + deux ancres. Collant au scroll.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$abf_infos = abf_infos();
?>
<header class="abf-header" id="abf-header">
	<div class="abf-container abf-header__inner">
		<a class="abf-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( abf_img( 'logo.webp' ) ); ?>" alt="<?php echo esc_attr( $abf_infos['nom'] ); ?>" width="132" height="58">
			<?php endif; ?>
		</a>

		<nav class="abf-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'aux-belfleurs' ); ?>">
			<a href="#carte"><?php esc_html_e( 'Où m\'acheter', 'aux-belfleurs' ); ?></a>
			<a href="#contact"><?php esc_html_e( 'Contact', 'aux-belfleurs' ); ?></a>
		</nav>
	</div>
</header>
