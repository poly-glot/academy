<?php $latest = academy_latest_posts( 3 ); ?>
<section class="section" aria-labelledby="news-title">
	<div class="container">
		<div class="section-head">
			<div class="section-head__text">
				<p class="kicker"><?php echo esc_html( (string) get_option( 'news_block_kicker' ) ); ?></p>
				<h2 id="news-title"><?php echo esc_html( (string) get_option( 'news_block_title' ) ); ?></h2>
			</div>
			<?php
			get_template_part( 'template-parts/arrow-link', null, array(
				'label' => (string) get_option( 'news_block_link_label' ),
				'url'   => academy_news_url(),
			) );
			?>
		</div>
		<ul class="news-grid">
			<?php foreach ( $latest->posts as $news ) : ?>
				<li>
					<?php get_template_part( 'template-parts/news-card', null, array( 'post' => $news ) ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
