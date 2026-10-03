<?php
get_header();

$page_id  = get_queried_object_id();
$sections = academy_rows( 'community_sections', $page_id );

get_template_part( 'template-parts/page-title', null, array(
	'lead'  => (string) academy_meta( 'hero_lead', $page_id ),
	'title' => implode( ' ', academy_hero_title( $page_id ) ),
) );
?>
<nav class="section section--tight" aria-label="Community sections">
	<div class="container">
		<ul class="subnav__list">
			<?php foreach ( $sections as $section ) : ?>
				<li><a class="subnav__link" href="#<?php echo esc_attr( $section['anchor'] ?? '' ); ?>"><?php echo esc_html( $section['title'] ?? '' ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
<div class="section section--flush-top">
	<div class="container">
		<div class="grid grid--2">
			<?php foreach ( $sections as $section ) : ?>
				<?php $anchor = (string) ( $section['anchor'] ?? '' ); ?>
				<section class="community-item" id="<?php echo esc_attr( $anchor ); ?>" aria-labelledby="<?php echo esc_attr( $anchor ); ?>-title">
					<h2 class="title-medium" id="<?php echo esc_attr( $anchor ); ?>-title"><?php echo esc_html( $section['title'] ?? '' ); ?></h2>
					<p class="community-item__text"><?php echo esc_html( $section['text'] ?? '' ); ?></p>
					<?php
					get_template_part( 'template-parts/arrow-link', null, array(
						'hidden' => (string) ( $section['link_hidden'] ?? '' ),
						'label'  => (string) ( $section['link_label'] ?? '' ),
						'url'    => (string) ( $section['link_url'] ?? '' ),
					) );
					?>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php
get_footer();
