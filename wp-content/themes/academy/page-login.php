<?php
get_header();

$page_id = get_queried_object_id();
?>
<section class="login" aria-labelledby="page-title">
	<?php
	academy_picture( (int) get_post_thumbnail_id( $page_id ), 'hero-medium', 'login__picture', array(
		'alt'     => '',
		'class'   => 'login__image',
		'loading' => false,
		'sizes'   => '100vw',
	) );
	?>
	<p class="login__back">
		<a class="text-link text-link--navy text-link--back" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<svg class="icon text-link__icon" aria-hidden="true" focusable="false"><use href="#icon-arrow-left"/></svg>
			Back<span class="visually-hidden"> to the home page</span>
		</a>
	</p>
	<div class="login-card">
		<div class="stack stack--tight">
			<h1 class="login-card__title" id="page-title"><?php echo esc_html( implode( ' ', academy_hero_title( $page_id ) ) ); ?></h1>
			<p><?php echo academy_linkify( (string) academy_meta( 'hero_lead', $page_id ) ); ?></p>
			<p class="notice"><?php echo esc_html( (string) academy_meta( 'form_note', $page_id ) ); ?></p>
		</div>
		<form class="form form--on-dark" action="<?php echo esc_url( get_permalink( $page_id ) . '#demo' ); ?>" method="get">
			<div class="field">
				<label class="field__label" for="username">Username or email</label>
				<input class="field__input" id="username" name="username" type="text" autocomplete="username">
			</div>
			<div class="field">
				<label class="field__label" for="password">Password</label>
				<p class="field__hint" id="password-hint">Passwords are case-sensitive.</p>
				<input class="field__input" id="password" name="password" type="password" autocomplete="current-password" aria-describedby="password-hint">
			</div>
			<div class="form__actions">
				<a href="<?php echo esc_url( academy_page_url( 'contact' ) . '#help' ); ?>">Forgot password?</a>
				<button class="button" type="submit">Log in</button>
			</div>
		</form>
		<p class="target-message target-message--panel" id="demo" tabindex="-1"><?php echo esc_html( (string) academy_meta( 'thank_you_text', $page_id ) ); ?></p>
		<p>Not a member yet? <a class="text-link text-link--on-dark" href="<?php echo esc_url( (string) get_option( 'header_join_url' ) ); ?>">Join now</a></p>
	</div>
</section>
<?php
get_footer();
