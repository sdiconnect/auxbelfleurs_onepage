<?php
/**
 * Page d'accueil — page unique « points de vente + contact ».
 *
 * Document autonome et minimaliste : on ne charge pas l'en-tête Astra
 * (qui porterait encore les restes WooCommerce), on construit un header
 * réduit au logo + deux ancres.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'abf-onepage' ); ?>>
<?php wp_body_open(); ?>

<a class="abf-skip-link screen-reader-text" href="#carte"><?php esc_html_e( 'Aller à la carte des points de vente', 'aux-belfleurs' ); ?></a>

<?php get_template_part( 'template-parts/header', 'minimal' ); ?>

<main id="abf-content">
	<?php
	get_template_part( 'template-parts/section', 'hero' );
	get_template_part( 'template-parts/section', 'map' );
	get_template_part( 'template-parts/section', 'contact' );
	?>
</main>

<?php get_template_part( 'template-parts/section', 'footer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
