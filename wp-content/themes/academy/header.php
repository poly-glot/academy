<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( 'assets/favicon.svg' ) ); ?>" type="image/svg+xml">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<a class="skip-link" href="#main">Skip to main content</a>
	<?php get_template_part( 'template-parts/site-header' ); ?>
	<main id="main">
