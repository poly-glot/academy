<?php
$page_id    = get_queried_object_id();
$is_circle  = 'circle' === ( $args['variant'] ?? 'bleed' );
$title_id   = $is_circle ? 'hero-title' : 'page-title';
$title      = academy_hero_title( $page_id );
$kicker     = (string) academy_meta( 'hero_kicker', $page_id );
$lead       = (string) academy_meta( 'hero_lead', $page_id );
$link_label = (string) academy_meta( 'hero_link_label', $page_id );
?>
<section class="hero hero--<?php echo $is_circle ? 'circle' : 'bleed'; ?>" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="hero__inner container">
		<div class="hero__copy">
			<?php if ( '' !== $kicker ) : ?>
				<p class="kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>
			<?php if ( $is_circle ) : ?>
				<h1 id="<?php echo esc_attr( $title_id ); ?>"><?php echo implode( ' ', array_map( fn ( string $line ) => '<span class="hero__title-line">' . esc_html( $line ) . '</span>', $title ) ); ?></h1>
			<?php else : ?>
				<h1 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( implode( ' ', $title ) ); ?></h1>
			<?php endif; ?>
			<?php if ( '' !== $lead ) : ?>
				<p class="lead"><?php echo academy_linkify( $lead ); ?></p>
			<?php endif; ?>
			<div class="button-row">
				<?php
				get_template_part( 'template-parts/arrow-button', null, array(
					'label' => (string) academy_meta( 'hero_button_label', $page_id ),
					'url'   => (string) academy_meta( 'hero_button_url', $page_id ),
				) );
				?>
				<?php if ( '' !== $link_label ) : ?>
					<a class="text-link text-link--navy" href="<?php echo esc_url( (string) academy_meta( 'hero_link_url', $page_id ) ); ?>"><?php echo esc_html( $link_label ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="hero__media">
			<div class="hero__tint" aria-hidden="true"></div>
			<div class="hero__arc" aria-hidden="true"></div>
			<figure class="hero__figure">
				<?php
				academy_picture( (int) get_post_thumbnail_id( $page_id ), 'hero-medium', 'hero__picture', array(
					'class'         => 'hero__image',
					'fetchpriority' => 'high',
					'loading'       => false,
					'sizes'         => ACADEMY_WIDE_SIZES,
				) );
				?>
			</figure>
		</div>
	</div>
</section>
