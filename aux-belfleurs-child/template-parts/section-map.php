<?php
/**
 * Section carte — cœur de la page.
 *
 * Carte interactive à gauche, liste des boutiques à droite (desktop) ;
 * empilées sur mobile. Un seul filtre : recherche ville / code postal.
 * La liste est rendue côté serveur, groupée par département (SEO + a11y),
 * et reste lisible sans JavaScript.
 *
 * @package aux-belfleurs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$abf_grouped = abf_get_stores_grouped();
?>
<section class="abf-map-section" id="carte">
	<div class="abf-container">
		<header class="abf-section-head">
			<h2><?php esc_html_e( 'Où m\'acheter', 'aux-belfleurs' ); ?></h2>
			<p><?php esc_html_e( 'Où trouver mes produits en France ?', 'aux-belfleurs' ); ?></p>
		</header>

		<div class="abf-map-tools">
			<label class="abf-search" for="abf-search-input">
				<span class="screen-reader-text"><?php esc_html_e( 'Votre ville ou code postal', 'aux-belfleurs' ); ?></span>
				<input
					type="search"
					id="abf-search-input"
					placeholder="<?php esc_attr_e( 'Votre ville ou code postal', 'aux-belfleurs' ); ?>"
					autocomplete="off"
					inputmode="text">
			</label>
			<button type="button" class="abf-btn abf-btn--ghost" id="abf-near-me" hidden>
				<span class="abf-near__dot" aria-hidden="true"></span>
				<?php esc_html_e( 'Autour de moi', 'aux-belfleurs' ); ?>
			</button>
		</div>

		<div class="abf-map-layout">
			<!-- Carte (chargée en différé par le JS) -->
			<div class="abf-map-wrap">
				<div id="abf-map" class="abf-map" role="application" aria-label="<?php esc_attr_e( 'Carte des points de vente', 'aux-belfleurs' ); ?>">
					<noscript>
						<p class="abf-noscript"><?php esc_html_e( 'Activez JavaScript pour afficher la carte interactive. La liste des boutiques ci-contre reste utilisable.', 'aux-belfleurs' ); ?></p>
					</noscript>
				</div>
			</div>

			<!-- Liste SSR groupée par département -->
			<div class="abf-store-list" id="abf-store-list">
				<?php if ( empty( $abf_grouped ) ) : ?>
					<p><?php esc_html_e( 'La liste des points de vente sera bientôt disponible.', 'aux-belfleurs' ); ?></p>
				<?php else : ?>
					<p class="abf-store-list__empty" id="abf-list-empty" hidden></p>
					<?php foreach ( $abf_grouped as $dep => $stores ) : ?>
						<?php
						$abf_n    = count( $stores );
						$abf_code = array_search( $dep, abf_departements(), true );
						$abf_head = $abf_code ? sprintf( '%s (%s)', $dep, $abf_code ) : $dep;
						?>
						<div class="abf-dep-group" data-dep="<?php echo esc_attr( $dep ); ?>">
							<h3 class="abf-dep-title">
								<span><?php echo esc_html( $abf_head ); ?></span>
								<span class="abf-dep-count">
									<?php
									/* translators: %d: nombre de boutiques */
									printf( esc_html( _n( '%d boutique', '%d boutiques', $abf_n, 'aux-belfleurs' ) ), (int) $abf_n );
									?>
								</span>
							</h3>
							<ul class="abf-dep-stores">
								<?php foreach ( $stores as $s ) : ?>
									<?php
									$abf_has_geo = ( null !== $s['lat'] && null !== $s['lng'] );
									$abf_route   = $abf_has_geo
										? 'https://www.openstreetmap.org/directions?to=' . rawurlencode( $s['lat'] . ',' . $s['lng'] )
										: 'https://www.openstreetmap.org/search?query=' . rawurlencode( trim( $s['adresse'] . ' ' . $s['code_postal'] . ' ' . $s['ville'] ) );
									$abf_haystack = strtolower( $s['ville'] . ' ' . $s['code_postal'] . ' ' . $s['nom'] );
									$abf_loc      = trim( $s['code_postal'] . ' ' . $s['ville'] );
									?>
									<li
										class="abf-store"
										data-id="<?php echo esc_attr( $s['id'] ); ?>"
										data-lat="<?php echo esc_attr( (string) $s['lat'] ); ?>"
										data-lng="<?php echo esc_attr( (string) $s['lng'] ); ?>"
										data-search="<?php echo esc_attr( $abf_haystack ); ?>">
										<div class="abf-store__main">
											<h4 class="abf-store__name"><?php echo esc_html( $s['nom'] ); ?></h4>
											<address class="abf-store__addr">
												<?php if ( $s['adresse'] ) : ?>
													<?php echo esc_html( $s['adresse'] ); ?><br>
												<?php endif; ?>
												<?php echo esc_html( $abf_loc ); ?>
											</address>
											<?php if ( $s['telephone'] ) : ?>
												<a class="abf-store__tel" href="tel:<?php echo esc_attr( abf_tel_href( $s['telephone'] ) ); ?>">
													<?php echo esc_html( $s['telephone'] ); ?>
												</a>
											<?php endif; ?>
										</div>
										<a class="abf-store__route abf-btn abf-btn--small"
											href="<?php echo esc_url( $abf_route ); ?>"
											target="_blank" rel="noopener"
											aria-label="<?php echo esc_attr( sprintf( /* translators: 1: nom, 2: ville */ __( 'Itinéraire vers %1$s, %2$s', 'aux-belfleurs' ), $s['nom'], $s['ville'] ) ); ?>">
											<?php esc_html_e( 'Itinéraire', 'aux-belfleurs' ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
