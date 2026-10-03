<?php $lead = (string) ( $args['lead'] ?? '' ); ?>
<section class="page-title<?php echo empty( $args['soft'] ) ? '' : ' page-title--soft'; ?>" aria-labelledby="page-title">
	<div class="page-title__inner container">
		<h1 id="page-title"><?php echo esc_html( $args['title'] ?? '' ); ?></h1>
		<?php if ( '' !== $lead ) : ?>
			<p class="page-title__lead"><?php echo academy_linkify( $lead ); ?></p>
		<?php endif; ?>
	</div>
</section>
