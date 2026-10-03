<?php
$lead  = (string) ( $args['lead'] ?? '' );
$facts = $args['facts'] ?? array();
?>
<section class="diagonal-hero" aria-labelledby="page-title">
	<div class="diagonal-hero__inner container">
		<div class="diagonal-hero__copy">
			<h1 id="page-title"><?php echo esc_html( $args['title'] ?? '' ); ?></h1>
			<?php if ( '' !== $lead ) : ?>
				<p class="diagonal-hero__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
			<?php if ( $facts ) : ?>
				<h2 class="visually-hidden">Certificate facts</h2>
				<dl class="facts">
					<?php foreach ( $facts as $label => $value ) : ?>
						<div class="facts__row"><dt class="facts__label"><?php echo esc_html( $label ); ?></dt><dd class="facts__value"><?php echo esc_html( $value ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
		</div>
	</div>
	<?php
	academy_picture( (int) ( $args['image_id'] ?? 0 ), 'hero-medium', 'diagonal-hero__picture', array(
		'class'   => 'diagonal-hero__image',
		'loading' => false,
		'sizes'   => ACADEMY_WIDE_SIZES,
	) );
	?>
</section>
