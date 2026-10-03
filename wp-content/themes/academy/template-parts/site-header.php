<?php
$account_url = (string) get_option( 'header_account_url' );
$join_url    = (string) get_option( 'header_join_url' );
?>
<header class="site-header">
	<div class="site-header__inner container">
		<?php get_template_part( 'template-parts/logo' ); ?>
		<nav class="site-nav" aria-label="Main">
			<?php
			wp_nav_menu( array(
				'container'      => false,
				'fallback_cb'    => false,
				'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
				'menu_class'     => 'site-nav__list',
				'theme_location' => 'primary',
			) );
			?>
		</nav>
		<div class="site-header__actions">
			<a class="button button--outline button--small" href="<?php echo esc_url( $account_url ); ?>"<?php echo academy_is_current( $account_url ) ? ' aria-current="page"' : ''; ?>>
				<svg class="icon button__icon" aria-hidden="true" focusable="false"><use href="#icon-user"/></svg>
				<?php echo esc_html( (string) get_option( 'header_account_label' ) ); ?>
			</a>
			<a class="button button--small" href="<?php echo esc_url( $join_url ); ?>"><?php echo esc_html( (string) get_option( 'header_join_label' ) ); ?></a>
		</div>
	</div>
</header>
