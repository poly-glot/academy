<?php
get_header();

$page_id = get_queried_object_id();

get_template_part( 'template-parts/diagonal-hero', null, array(
	'image_id' => (int) get_post_thumbnail_id( $page_id ),
	'lead'     => (string) academy_meta( 'hero_lead', $page_id ),
	'title'    => implode( ' ', academy_hero_title( $page_id ) ),
) );
?>
<section class="section" id="intro" aria-labelledby="intro-title">
	<div class="measure container">
		<div class="prose">
			<h2 id="intro-title"><?php echo esc_html( (string) academy_meta( 'courses_intro_title', $page_id ) ); ?></h2>
			<?php foreach ( academy_paragraphs( (string) academy_meta( 'courses_intro_text', $page_id ) ) as $paragraph ) : ?>
				<p><?php echo esc_html( $paragraph ); ?></p>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--brand section--centred" id="register" aria-labelledby="register-title">
	<div class="container">
		<h2 id="register-title"><?php echo esc_html( (string) academy_meta( 'courses_steps_title', $page_id ) ); ?></h2>
		<ol class="steps">
			<?php foreach ( academy_rows( 'courses_steps', $page_id ) as $step ) : ?>
				<li class="steps__item">
					<h3 class="steps__title"><?php echo esc_html( $step['title'] ?? '' ); ?></h3>
					<p class="steps__text"><?php echo esc_html( $step['text'] ?? '' ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
		<div class="button-row">
			<a class="button button--light" href="<?php echo esc_url( (string) academy_meta( 'courses_steps_primary_url', $page_id ) ); ?>"><?php echo esc_html( (string) academy_meta( 'courses_steps_primary_label', $page_id ) ); ?></a>
			<a class="button button--on-dark" href="<?php echo esc_url( (string) academy_meta( 'courses_steps_secondary_url', $page_id ) ); ?>"><?php echo esc_html( (string) academy_meta( 'courses_steps_secondary_label', $page_id ) ); ?></a>
		</div>
	</div>
</section>

<section class="section" id="course-list" aria-labelledby="course-list-title">
	<div class="container">
		<div class="section-head">
			<div class="section-head__text">
				<h2 id="course-list-title"><?php echo esc_html( (string) academy_meta( 'courses_list_title', $page_id ) ); ?></h2>
			</div>
		</div>
		<div class="course-columns">
			<?php foreach ( academy_rows( 'courses_series', $page_id ) as $series ) : ?>
				<div class="course-columns__column">
					<h3 class="course-columns__title"><?php echo esc_html( $series['title'] ?? '' ); ?></h3>
					<?php foreach ( academy_list( $series['groups'] ?? null ) as $group ) : ?>
						<div class="course-group">
							<h4 class="course-group__title"><?php echo esc_html( $group['title'] ?? '' ); ?></h4>
							<ul class="course-group__list">
								<?php foreach ( academy_list( $group['courses'] ?? null ) as $course ) : ?>
									<li class="course-group__item"><span class="course-group__code"><?php echo esc_html( $course['code'] ?? '' ); ?></span> <?php echo esc_html( $course['title'] ?? '' ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--brand section--centred" id="certificates" aria-labelledby="certificates-title">
	<div class="measure container">
		<div class="stack">
			<h2 id="certificates-title"><?php echo esc_html( (string) academy_meta( 'courses_certificates_title', $page_id ) ); ?></h2>
			<p class="lead"><?php echo esc_html( (string) academy_meta( 'courses_certificates_text', $page_id ) ); ?></p>
			<ul class="link-list">
				<?php foreach ( academy_rows( 'courses_certificates_links', $page_id ) as $link ) : ?>
					<li><a href="<?php echo esc_url( $link['url'] ?? '' ); ?>"><?php echo esc_html( $link['label'] ?? '' ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
<?php
get_footer();
