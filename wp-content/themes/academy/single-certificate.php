<?php
get_header();

$certificate_id = get_queried_object_id();
$facts          = array();

foreach ( ACADEMY_CERTIFICATE_FACTS as $key => $label ) {
	$facts[ $label ] = (string) academy_meta( $key, $certificate_id );
}

get_template_part( 'template-parts/diagonal-hero', null, array(
	'facts'    => $facts,
	'image_id' => (int) get_post_thumbnail_id( $certificate_id ),
	'title'    => get_the_title( $certificate_id ),
) );
?>
<section class="section" aria-label="About this certificate">
	<div class="container">
		<div class="with-aside">
			<div class="with-aside__main">
				<div class="prose prose--sections">
					<?php foreach ( ACADEMY_CERTIFICATE_SECTIONS as $key => $heading ) : ?>
						<h2><?php echo esc_html( $heading ); ?></h2>
						<?php if ( 'outline' === $key ) : ?>
							<ul>
								<?php foreach ( academy_rows( 'outline', $certificate_id ) as $topic ) : ?>
									<li><?php echo esc_html( $topic['text'] ?? '' ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p><?php echo esc_html( (string) academy_meta( $key, $certificate_id ) ); ?></p>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="with-aside__aside">
				<nav class="subnav" aria-labelledby="subnav-title">
					<h2 class="subnav__title" id="subnav-title">Other certificates</h2>
					<ul class="subnav__list">
						<?php foreach ( academy_certificates() as $certificate ) : ?>
							<li><a class="subnav__link" href="<?php echo esc_url( get_permalink( $certificate ) ); ?>"<?php echo $certificate->ID === $certificate_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( (string) academy_meta( 'abbreviation', $certificate->ID ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			</div>
		</div>
	</div>
</section>

<section class="section section--flush-top" aria-label="Book this certificate">
	<div class="container">
		<div class="award-strip">
			<div class="award-strip__text">
				<p><?php echo esc_html( (string) get_option( 'certificate_award_text' ) ); ?></p>
				<p class="award-strip__by"><?php echo esc_html( (string) get_option( 'certificate_awarded_by' ) ); ?></p>
			</div>
			<?php
			get_template_part( 'template-parts/arrow-button', null, array(
				'label' => 'Buy this certificate',
				'url'   => academy_page_url( 'contact' ) . '#enquiry',
			) );
			?>
		</div>
	</div>
</section>
<?php
get_footer();
