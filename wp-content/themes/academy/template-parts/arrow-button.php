<a class="<?php echo esc_attr( $args['class'] ?? 'button' ); ?>" href="<?php echo esc_url( $args['url'] ?? '' ); ?>">
	<?php echo esc_html( $args['label'] ?? '' ); ?>
	<svg class="icon button__icon button__icon--arrow" aria-hidden="true" focusable="false"><use href="#icon-arrow-right"/></svg>
</a>
