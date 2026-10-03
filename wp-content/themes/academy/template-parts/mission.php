<?php
$page_id    = get_queried_object_id();
$section_id = (string) ( $args['id'] ?? '' );
?>
<section class="section section--navy section--shaped"<?php echo '' === $section_id ? '' : ' id="' . esc_attr( $section_id ) . '"'; ?> aria-labelledby="mission-title">
	<div class="split container">
		<div class="stack">
			<p class="kicker"><?php echo esc_html( (string) academy_meta( 'mission_kicker', $page_id ) ); ?></p>
			<h2 id="mission-title"><?php echo esc_html( (string) academy_meta( 'mission_title', $page_id ) ); ?></h2>
		</div>
		<div class="mission__aside">
			<p class="mission__text"><?php echo esc_html( (string) academy_meta( 'mission_text', $page_id ) ); ?></p>
			<?php
			get_template_part( 'template-parts/arrow-button', null, array(
				'class' => 'button button--on-dark',
				'label' => (string) academy_meta( 'mission_button_label', $page_id ),
				'url'   => (string) academy_meta( 'mission_button_url', $page_id ),
			) );
			?>
		</div>
	</div>
</section>
