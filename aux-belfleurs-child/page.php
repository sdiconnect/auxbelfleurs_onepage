<?php
/**
 * Gabarit de page classique (mentions légales, politique de confidentialité…).
 *
 * Reprend le même header minimal et le même footer que la page unique, afin que
 * les pages légales restent cohérentes avec le reste du site (au lieu d'hériter
 * du gabarit Astra par défaut).
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
<body <?php body_class( 'abf-onepage abf-subpage' ); ?>>
<?php wp_body_open(); ?>

<a class="abf-skip-link screen-reader-text" href="#abf-content"><?php esc_html_e( 'Aller au contenu', 'aux-belfleurs' ); ?></a>

<?php get_template_part( 'template-parts/header', 'minimal' ); ?>

<main id="abf-content" class="abf-page">
	<div class="abf-container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'abf-page__article' ); ?>>
				<h1 class="abf-page__title"><?php the_title(); ?></h1>
				<div class="abf-page__content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>

<?php get_template_part( 'template-parts/section', 'footer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
