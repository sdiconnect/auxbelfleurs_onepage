<?php
/**
 * Mini-galerie : deux tuiles avec légende (savons / shampoings solides).
 * Volontairement sobre — deux images, pas un mur d'images.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filtre les tuiles de la galerie.
 */
$abf_tiles = apply_filters(
	'abf_gallery_tiles',
	array(
		array(
			'img'   => abf_img( 'hero-savons.webp' ),
			'alt'   => __( 'Savons solides alignés', 'aux-belfleurs' ),
			'title' => __( 'Savons solides', 'aux-belfleurs' ),
			'desc'  => __( 'Saponifiés à froid, certifiés biologiques', 'aux-belfleurs' ),
		),
		array(
			'img'   => abf_img( 'shampoings-solides.webp' ),
			'alt'   => __( 'Shampoings solides', 'aux-belfleurs' ),
			'title' => __( 'Shampoings solides', 'aux-belfleurs' ),
			'desc'  => __( 'Cheveux normaux, gras, secs — 80 g', 'aux-belfleurs' ),
		),
	)
);

if ( empty( $abf_tiles ) ) {
	return;
}
?>
<section class="abf-gallery" aria-label="<?php esc_attr_e( 'Aperçu des produits', 'aux-belfleurs' ); ?>">
	<div class="abf-container">
		<div class="abf-gallery__grid">
			<?php foreach ( $abf_tiles as $tile ) : ?>
				<div class="abf-gallery__tile">
					<figure>
						<img src="<?php echo esc_url( $tile['img'] ); ?>" alt="<?php echo esc_attr( $tile['alt'] ); ?>" loading="lazy" decoding="async">
						<figcaption class="abf-gallery__cap">
							<p class="abf-gallery__title"><?php echo esc_html( $tile['title'] ); ?></p>
							<p class="abf-gallery__desc"><?php echo esc_html( $tile['desc'] ); ?></p>
						</figcaption>
					</figure>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
