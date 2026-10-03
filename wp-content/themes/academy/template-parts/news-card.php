<?php
$news     = $args['post'];
$category = get_the_category( $news->ID )[0] ?? null;
$title    = get_the_title( $news );
$url      = get_permalink( $news );
?>
<article class="news-card">
	<div class="news-card__media">
		<?php
		academy_picture( (int) get_post_thumbnail_id( $news ), 'card', 'news-card__picture', array(
			'class'   => 'news-card__image',
			'loading' => 'lazy',
		) );
		?>
	</div>
	<p class="meta"><?php if ( $category ) : ?><span class="meta__category<?php echo 'media' === $category->slug ? ' meta__category--media' : ''; ?>"><?php echo esc_html( $category->name ); ?></span><?php endif; ?><time class="meta__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $news ) ); ?>"><?php echo esc_html( get_the_date( '', $news ) ); ?></time></p>
	<h3 class="news-card__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></h3>
	<p class="news-card__summary"><?php echo esc_html( $news->post_excerpt ); ?></p>
	<?php
	get_template_part( 'template-parts/arrow-link', null, array(
		'hidden' => 'about ' . $title,
		'label'  => 'Read more',
		'url'    => $url,
	) );
	?>
</article>
