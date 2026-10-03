<?php update_post_thumbnail_cache(); ?>
<section class="section section--flush-top" aria-labelledby="list-title">
	<div class="container">
		<h2 class="visually-hidden" id="list-title">All news and media</h2>
		<ul class="news-grid">
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<li>
					<?php get_template_part( 'template-parts/news-card', null, array( 'post' => get_post() ) ); ?>
				</li>
			<?php endwhile; ?>
		</ul>
		<div class="news-more">
			<a class="button button--outline" href="<?php echo esc_url( get_next_posts_link() ? get_next_posts_page_link() : academy_news_url() ); ?>">More<span class="visually-hidden"> news and media</span></a>
		</div>
	</div>
</section>
