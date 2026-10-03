<?php
get_header();

$page_id = get_queried_object_id();
$groups  = academy_people_by_group();

get_template_part( 'template-parts/hero', null, array( 'variant' => 'bleed' ) );
get_template_part( 'template-parts/intro-band', null, array( 'id' => 'who' ) );
?>
<section class="section" id="goals" aria-labelledby="goals-title">
	<div class="split container">
		<div class="stack">
			<p class="kicker"><?php echo esc_html( (string) academy_meta( 'about_goals_kicker', $page_id ) ); ?></p>
			<h2 id="goals-title"><?php echo esc_html( (string) academy_meta( 'about_goals_title', $page_id ) ); ?></h2>
			<ol class="goals">
				<?php foreach ( academy_rows( 'about_goals', $page_id ) as $goal ) : ?>
					<li class="goals__item"><?php echo esc_html( $goal['text'] ?? '' ); ?></li>
				<?php endforeach; ?>
			</ol>
		</div>
		<figure class="poster">
			<div class="poster__screen">
				<?php
				academy_picture( (int) academy_meta( 'about_film_image', $page_id ), 'hero-small', 'poster__picture', array(
					'class'   => 'poster__image',
					'loading' => 'lazy',
					'sizes'   => ACADEMY_WIDE_SIZES,
				) );
				?>
				<span class="poster__label" aria-hidden="true">
					<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-play"/></svg>
					Play
				</span>
			</div>
			<figcaption class="poster__caption"><?php echo esc_html( (string) academy_meta( 'about_film_caption', $page_id ) ); ?></figcaption>
		</figure>
	</div>
</section>

<?php get_template_part( 'template-parts/mission', null, array( 'id' => 'mission' ) ); ?>

<section class="section section--soft" id="people" aria-labelledby="people-title">
	<div class="container">
		<div class="section-head">
			<div class="section-head__text">
				<p class="kicker kicker--accent"><?php echo esc_html( (string) academy_meta( 'about_people_kicker', $page_id ) ); ?></p>
				<h2 id="people-title"><?php echo esc_html( (string) academy_meta( 'about_people_title', $page_id ) ); ?></h2>
				<p class="section-head__intro"><?php echo esc_html( (string) academy_meta( 'about_people_intro', $page_id ) ); ?></p>
			</div>
		</div>
		<div class="tabs">
			<nav aria-label="Groups of people">
				<ul class="tabs__list">
					<?php foreach ( ACADEMY_PERSON_GROUPS as $group => $label ) : ?>
						<li class="tabs__item"><a class="tabs__tab" href="#<?php echo esc_attr( $group ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<?php foreach ( $groups as $group => $people ) : ?>
				<div class="tabs__panel" id="<?php echo esc_attr( $group ); ?>" tabindex="-1">
					<h3 class="visually-hidden"><?php echo esc_html( ACADEMY_PERSON_GROUPS[ $group ] ); ?></h3>
					<ul class="people-grid">
						<?php foreach ( $people as $person ) : ?>
							<li>
								<article class="person-card">
									<?php
									academy_picture( (int) get_post_thumbnail_id( $person ), 'portrait', 'person-card__picture', array(
										'class'   => 'person-card__photo',
										'loading' => 'lazy',
									) );
									?>
									<h4 class="person-card__name"><?php echo esc_html( get_the_title( $person ) ); ?></h4>
									<p class="person-card__role"><?php echo esc_html( (string) academy_meta( 'role', $person->ID ) ); ?></p>
									<p class="person-card__bio"><?php echo esc_html( (string) academy_meta( 'bio', $person->ID ) ); ?></p>
								</article>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/news-latest' );

get_footer();
