<?php
get_header();

$page_id = get_queried_object_id();

get_template_part( 'template-parts/hero', null, array( 'variant' => 'circle' ) );
?>
<section class="section section--soft" aria-labelledby="why-title">
	<div class="split split--start container">
		<div class="stack">
			<p class="kicker"><?php echo esc_html( (string) academy_meta( 'home_why_kicker', $page_id ) ); ?></p>
			<h2 id="why-title"><?php echo esc_html( (string) academy_meta( 'home_why_title', $page_id ) ); ?></h2>
			<p class="lead"><?php echo esc_html( (string) academy_meta( 'home_why_text', $page_id ) ); ?></p>
		</div>
		<ul class="feature-list">
			<?php foreach ( academy_rows( 'home_why_items', $page_id ) as $item ) : ?>
				<li class="feature-list__item">
					<span class="icon-badge" aria-hidden="true"><svg class="icon icon--large" focusable="false"><use href="#icon-<?php echo esc_attr( $item['icon'] ?? '' ); ?>"/></svg></span>
					<div class="feature-list__body">
						<h3 class="feature-list__title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
						<p class="feature-list__text"><?php echo esc_html( $item['text'] ?? '' ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php get_template_part( 'template-parts/mission' ); ?>

<section class="join-band" aria-labelledby="join-title">
	<div class="join-band__inner container">
		<div class="join-band__text">
			<h2 class="join-band__title" id="join-title"><?php echo esc_html( (string) academy_meta( 'home_join_title', $page_id ) ); ?></h2>
			<p class="join-band__sentence"><?php echo esc_html( (string) academy_meta( 'home_join_text', $page_id ) ); ?></p>
		</div>
		<div class="button-row">
			<a class="text-link text-link--navy" href="<?php echo esc_url( (string) academy_meta( 'home_join_link_url', $page_id ) ); ?>"><?php echo esc_html( (string) academy_meta( 'home_join_link_label', $page_id ) ); ?></a>
			<?php
			get_template_part( 'template-parts/arrow-button', null, array(
				'label' => (string) academy_meta( 'home_join_button_label', $page_id ),
				'url'   => (string) academy_meta( 'home_join_button_url', $page_id ),
			) );
			?>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/news-latest' );

get_footer();
