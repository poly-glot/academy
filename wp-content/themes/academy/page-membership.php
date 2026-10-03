<?php
get_header();

$page_id = get_queried_object_id();

get_template_part( 'template-parts/hero', null, array( 'variant' => 'bleed' ) );
get_template_part( 'template-parts/intro-band', null, array( 'id' => 'value' ) );
?>
<section class="section section--centred" id="three-ways" aria-labelledby="three-ways-title">
	<div class="container">
		<div class="section-head">
			<div class="section-head__text">
				<h2 id="three-ways-title"><?php echo esc_html( (string) academy_meta( 'membership_ways_title', $page_id ) ); ?></h2>
			</div>
		</div>
		<ul class="value-grid">
			<?php foreach ( academy_rows( 'membership_ways', $page_id ) as $way ) : ?>
				<li class="value-grid__item">
					<div class="value-grid__visual"><span class="icon-badge icon-badge--large icon-badge--tint" aria-hidden="true"><svg class="icon" focusable="false"><use href="#icon-<?php echo esc_attr( $way['icon'] ?? '' ); ?>"/></svg></span></div>
					<h3 class="value-grid__title"><?php echo esc_html( $way['title'] ?? '' ); ?></h3>
					<p class="value-grid__text"><?php echo esc_html( $way['text'] ?? '' ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
		<ul class="value-grid value-grid--short">
			<?php foreach ( academy_rows( 'membership_ways_short', $page_id ) as $way ) : ?>
				<li class="value-grid__item">
					<?php if ( ! empty( $way['icon'] ) ) : ?>
						<div class="value-grid__visual"><span class="icon-badge icon-badge--large" aria-hidden="true"><svg class="icon" focusable="false"><use href="#icon-<?php echo esc_attr( $way['icon'] ); ?>"/></svg></span></div>
					<?php else : ?>
						<div class="value-grid__visual"><p class="value-grid__figure"><?php echo esc_html( $way['figure'] ?? '' ) . academy_hidden_suffix( $way['figure_hidden'] ?? '' ); ?></p></div>
					<?php endif; ?>
					<p class="value-grid__text"><?php echo esc_html( $way['text'] ?? '' ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<section class="section section--brand section--centred" id="join" aria-labelledby="join-title">
	<div class="measure container">
		<div class="stack">
			<h2 id="join-title"><?php echo esc_html( (string) academy_meta( 'membership_join_title', $page_id ) ); ?></h2>
			<p class="lead"><?php echo academy_linkify( (string) academy_meta( 'membership_join_text', $page_id ) ); ?></p>
			<div class="button-row">
				<?php
				get_template_part( 'template-parts/arrow-button', null, array(
					'class' => 'button button--light',
					'label' => (string) academy_meta( 'membership_join_button_label', $page_id ),
					'url'   => (string) academy_meta( 'membership_join_button_url', $page_id ),
				) );
				?>
				<a class="text-link text-link--on-dark" href="<?php echo esc_url( (string) academy_meta( 'membership_join_link_url', $page_id ) ); ?>"><?php echo esc_html( (string) academy_meta( 'membership_join_link_label', $page_id ) ); ?></a>
			</div>
		</div>
	</div>
</section>

<section class="section section--soft" id="levels" aria-labelledby="levels-title">
	<div class="container">
		<div class="section-head">
			<div class="section-head__text">
				<h2 id="levels-title"><?php echo esc_html( (string) academy_meta( 'membership_levels_title', $page_id ) ); ?></h2>
				<p class="section-head__intro"><?php echo esc_html( (string) academy_meta( 'membership_levels_intro', $page_id ) ); ?></p>
			</div>
		</div>
		<ul class="grid grid--3">
			<?php foreach ( academy_rows( 'membership_levels', $page_id ) as $position => $level ) : ?>
				<?php
				$benefits = academy_list( $level['benefits'] ?? null );
				$steps    = academy_list( $level['how_steps'] ?? null );
				$how_text = (string) ( $level['how_text'] ?? '' );
				?>
				<li>
					<article class="level-card">
						<div class="level-card__header">
							<span class="level-card__badge" aria-hidden="true"><?php echo str_repeat( '<span class="level-card__bar"></span>', $position + 1 ); ?></span>
							<h3 class="level-card__title"><?php echo esc_html( $level['title'] ?? '' ); ?></h3>
						</div>
						<p class="level-card__intro"><?php echo esc_html( $level['intro'] ?? '' ); ?></p>
						<h4 class="level-card__heading">Overview</h4>
						<p><?php echo esc_html( $level['overview'] ?? '' ); ?></p>
						<h4 class="level-card__heading">Benefits</h4>
						<ul class="level-card__list">
							<?php foreach ( $benefits as $benefit ) : ?>
								<li><?php echo esc_html( $benefit['text'] ?? '' ); ?></li>
							<?php endforeach; ?>
						</ul>
						<h4 class="level-card__heading">How to become one</h4>
						<?php if ( '' !== $how_text ) : ?>
							<p><?php echo esc_html( $how_text ); ?></p>
						<?php endif; ?>
						<?php if ( $steps ) : ?>
							<ol class="level-card__list">
								<?php foreach ( $steps as $step ) : ?>
									<li><?php echo esc_html( $step['text'] ?? '' ); ?></li>
								<?php endforeach; ?>
							</ol>
						<?php endif; ?>
					</article>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<section class="section section--tint" id="fees" aria-labelledby="fees-title">
	<div class="container">
		<div class="section-head">
			<div class="section-head__text">
				<h2 id="fees-title"><?php echo esc_html( (string) academy_meta( 'membership_fees_title', $page_id ) ); ?></h2>
				<p class="section-head__intro"><?php echo esc_html( (string) academy_meta( 'membership_fees_intro', $page_id ) ); ?></p>
			</div>
		</div>
		<ul class="grid grid--3">
			<?php foreach ( academy_rows( 'membership_fees', $page_id ) as $fee ) : ?>
				<li>
					<article class="fee-card">
						<span class="icon-badge icon-badge--tint" aria-hidden="true"><svg class="icon icon--large" focusable="false"><use href="#icon-<?php echo esc_attr( $fee['icon'] ?? '' ); ?>"/></svg></span>
						<h3 class="fee-card__title"><?php echo esc_html( $fee['title'] ?? '' ); ?></h3>
						<p class="fee-card__price"><?php echo esc_html( $fee['price'] ?? '' ); ?><?php if ( ! empty( $fee['unit'] ) ) : ?> <span class="fee-card__unit"><?php echo esc_html( $fee['unit'] ); ?></span><?php endif; ?></p>
						<?php if ( ! empty( $fee['text'] ) ) : ?>
							<p class="fee-card__text"><?php echo academy_linkify( $fee['text'] ); ?></p>
						<?php endif; ?>
					</article>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<nav class="section section--tight" aria-label="Membership links">
	<div class="container">
		<ul class="link-row">
			<?php foreach ( academy_rows( 'membership_links', $page_id ) as $link ) : ?>
				<li><a class="text-link" href="<?php echo esc_url( $link['url'] ?? '' ); ?>"><?php echo esc_html( $link['label'] ?? '' ) . academy_hidden_suffix( $link['hidden'] ?? '' ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
<?php
get_footer();
