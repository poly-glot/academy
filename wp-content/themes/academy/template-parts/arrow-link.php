<a class="text-link" href="<?php echo esc_url( $args['url'] ?? '' ); ?>">
	<?php echo esc_html( $args['label'] ?? '' ) . academy_hidden_suffix( $args['hidden'] ?? '' ); ?>
	<svg class="icon text-link__icon" aria-hidden="true" focusable="false"><use href="#icon-arrow-right"/></svg>
</a>
