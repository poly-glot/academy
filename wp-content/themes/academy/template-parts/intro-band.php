<?php
$page_id    = get_queried_object_id();
$section_id = (string) ( $args['id'] ?? '' );
$link_label = (string) academy_meta( 'intro_link_label', $page_id );
?>
<section class="section section--soft" id="<?php echo esc_attr( $section_id ); ?>" aria-labelledby="<?php echo esc_attr( $section_id ); ?>-title">
	<div class="split split--start container">
		<div class="stack">
			<p class="kicker"><?php echo esc_html( (string) academy_meta( 'intro_kicker', $page_id ) ); ?></p>
			<h2 id="<?php echo esc_attr( $section_id ); ?>-title"><?php echo esc_html( (string) academy_meta( 'intro_title', $page_id ) ); ?></h2>
		</div>
		<div class="stack">
			<?php foreach ( academy_paragraphs( (string) academy_meta( 'intro_text', $page_id ) ) as $paragraph ) : ?>
				<p class="lead"><?php echo esc_html( $paragraph ); ?></p>
			<?php endforeach; ?>
			<?php if ( '' !== $link_label ) : ?>
				<a class="text-link" href="<?php echo esc_url( (string) academy_meta( 'intro_link_url', $page_id ) ); ?>"><?php echo esc_html( $link_label ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
