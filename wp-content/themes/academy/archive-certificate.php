<?php
get_header();

$core       = array();
$specialist = array();

foreach ( $wp_query->posts as $certificate ) {
	if ( 'core' === academy_meta( 'tier', $certificate->ID ) ) {
		$core[] = $certificate;
	} else {
		$specialist[] = $certificate;
	}
}

get_template_part( 'template-parts/diagonal-hero', null, array(
	'image_id' => (int) get_option( 'certification_hero_image' ),
	'lead'     => (string) get_option( 'certification_lead' ),
	'title'    => post_type_archive_title( '', false ),
) );
?>
<section class="section" id="overview" aria-labelledby="overview-title">
	<div class="measure container">
		<div class="prose">
			<h2 id="overview-title"><?php echo esc_html( (string) get_option( 'certification_overview_title' ) ); ?></h2>
			<?php foreach ( academy_paragraphs( (string) get_option( 'certification_overview' ) ) as $paragraph ) : ?>
				<p><?php echo esc_html( $paragraph ); ?></p>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--flush-top" aria-label="Certificates">
	<div class="container">
		<ul class="grid grid--2">
			<?php foreach ( $core as $certificate ) : ?>
				<li>
					<article class="credential">
						<p class="kicker"><?php echo esc_html( (string) academy_meta( 'overview_kicker', $certificate->ID ) ); ?></p>
						<h2 class="title-medium"><?php echo esc_html( get_the_title( $certificate ) ); ?></h2>
						<p class="credential__text"><?php echo esc_html( (string) academy_meta( 'summary', $certificate->ID ) ); ?></p>
						<?php
						get_template_part( 'template-parts/arrow-link', null, array(
							'hidden' => 'about the ' . academy_meta( 'abbreviation', $certificate->ID ),
							'label'  => 'Learn more',
							'url'    => get_permalink( $certificate ),
						) );
						?>
					</article>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<section class="section section--soft" id="specialist" aria-labelledby="specialist-title">
	<div class="container">
		<div class="section-head">
			<div class="section-head__text">
				<h2 id="specialist-title"><?php echo esc_html( (string) get_option( 'certification_specialist_title' ) ); ?></h2>
				<p class="section-head__intro"><?php echo esc_html( (string) get_option( 'certification_specialist_intro' ) ); ?></p>
			</div>
		</div>
		<ul class="link-list">
			<?php foreach ( $specialist as $certificate ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $certificate ) ); ?>"><?php echo esc_html( get_the_title( $certificate ) ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
get_footer();
