<?php
get_header();

while ( have_posts() ) {
	the_post();

	get_template_part( 'template-parts/page-title', null, array(
		'lead'  => (string) academy_meta( 'hero_lead' ),
		'soft'  => true,
		'title' => implode( ' ', academy_hero_title( get_the_ID() ) ),
	) );
	?>
	<div class="section">
		<div class="container">
			<div class="prose">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
	<?php
}

get_footer();
