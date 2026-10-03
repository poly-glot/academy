<footer class="site-footer">
	<div class="site-footer__inner container">
		<div class="site-footer__top">
			<div class="site-footer__brand">
				<?php get_template_part( 'template-parts/logo', null, array( 'class' => 'logo logo--on-dark' ) ); ?>
				<p><?php echo esc_html( (string) get_option( 'footer_description' ) ); ?></p>
				<ul class="site-footer__social">
					<?php foreach ( academy_option_rows( 'social' ) as $social ) : ?>
						<li>
							<a class="site-footer__social-link" href="<?php echo esc_url( $social['url'] ?? '' ); ?>">
								<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-<?php echo esc_attr( $social['icon'] ?? '' ); ?>"/></svg>
								<?php echo esc_html( $social['label'] ?? '' ) . academy_hidden_suffix( $social['hidden'] ?? '' ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<nav class="site-footer__columns" aria-label="Footer">
				<?php foreach ( ACADEMY_FOOTER_COLUMNS as $locations ) : ?>
					<div>
						<?php foreach ( $locations as $index => $location ) : ?>
							<h2 class="site-footer__heading<?php echo $index ? ' site-footer__heading--spaced' : ''; ?>"><?php echo esc_html( wp_get_nav_menu_name( $location ) ); ?></h2>
							<?php
							wp_nav_menu( array(
								'container'      => false,
								'fallback_cb'    => false,
								'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
								'menu_class'     => 'site-footer__list',
								'theme_location' => $location,
							) );
							?>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</nav>
		</div>
		<div class="site-footer__bottom">
			<nav aria-label="Legal">
				<?php
				wp_nav_menu( array(
					'container'      => false,
					'fallback_cb'    => false,
					'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
					'menu_class'     => 'site-footer__legal',
					'theme_location' => 'legal',
				) );
				?>
			</nav>
			<p class="site-footer__notice"><?php echo esc_html( (string) get_option( 'footer_notice' ) ); ?></p>
			<p><?php echo esc_html( (string) get_option( 'copyright_line' ) ); ?></p>
		</div>
	</div>
</footer>
