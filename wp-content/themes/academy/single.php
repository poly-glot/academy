<?php
get_header();

while ( have_posts() ) {
	the_post();

	$category   = get_the_category()[0] ?? null;
	$is_media   = $category && 'media' === $category->slug;
	$author_id  = (int) academy_meta( 'author_profile' );
	$standfirst = (string) academy_meta( 'standfirst' );
	?>
	<div class="article-hero">
		<?php
		academy_picture( (int) academy_meta( 'hero_image' ), 'hero-medium', 'article-hero__picture', array(
			'class'   => 'article-hero__image',
			'loading' => false,
			'sizes'   => '100vw',
		) );
		?>
		<div class="article-hero__bar container">
			<p class="tag<?php echo $is_media ? ' tag--media' : ''; ?>"><?php echo esc_html( $category ? $category->name : '' ); ?><span aria-hidden="true">·</span><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
		</div>
	</div>
	<article class="article container" aria-labelledby="page-title">
		<div class="with-aside">
			<div class="with-aside__main">
				<header class="article__header">
					<h1 class="article__title" id="page-title"><?php echo esc_html( get_the_title() ); ?></h1>
					<?php if ( '' !== $standfirst ) : ?>
						<p class="article__standfirst"><?php echo esc_html( $standfirst ); ?></p>
					<?php endif; ?>
					<?php if ( $author_id ) : ?>
						<p class="article__byline">By <?php echo esc_html( get_the_title( $author_id ) ); ?></p>
					<?php endif; ?>
				</header>
				<div class="prose">
					<?php the_content(); ?>
				</div>
				<p class="article__note"><?php echo esc_html( (string) get_option( 'article_note' ) ); ?></p>
				<p class="article__back">
					<a class="text-link text-link--back" href="<?php echo esc_url( academy_news_url() ); ?>">
						<svg class="icon text-link__icon" aria-hidden="true" focusable="false"><use href="#icon-arrow-left"/></svg>
						Back to News &amp; media
					</a>
				</p>
			</div>
			<aside class="with-aside__aside" aria-label="Share and subjects">
				<section class="aside-block" aria-labelledby="share-title">
					<h2 class="aside-block__title" id="share-title">Share</h2>
					<ul class="aside-block__list">
						<li><a href="#"><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-linkedin"/></svg>Share on LinkedIn<span class="visually-hidden"> (sample link)</span></a></li>
						<li><a href="#"><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-mail"/></svg>Share by email<span class="visually-hidden"> (sample link)</span></a></li>
						<li><a href="#"><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-link"/></svg>Copy link<span class="visually-hidden"> (sample link)</span></a></li>
					</ul>
				</section>
				<section class="aside-block" aria-labelledby="subjects-title">
					<h2 class="aside-block__title" id="subjects-title">Subjects</h2>
					<ul class="aside-block__list">
						<?php foreach ( academy_rows( 'subjects' ) as $subject ) : ?>
							<li><?php echo esc_html( $subject['text'] ?? '' ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			</aside>
		</div>
	</article>
	<?php
}

get_footer();
