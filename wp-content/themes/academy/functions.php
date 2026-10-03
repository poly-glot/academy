<?php

const ACADEMY_FONTS_URL = 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=Manrope:wght@500;600;700;800&display=swap';

const ACADEMY_STYLESHEETS = array(
	'base/tokens.css',
	'base/base.css',
	'base/layout.css',
	'atoms/button.css',
	'atoms/icon.css',
	'atoms/kicker.css',
	'atoms/logo.css',
	'atoms/meta.css',
	'atoms/prose.css',
	'components/site-header.css',
	'components/site-footer.css',
	'components/hero.css',
	'components/page-hero.css',
	'components/band.css',
	'components/feature-list.css',
	'components/value-grid.css',
	'components/goals.css',
	'components/tabs.css',
	'components/person-card.css',
	'components/level-card.css',
	'components/fee-card.css',
	'components/steps.css',
	'components/course-list.css',
	'components/news-card.css',
	'components/article.css',
	'components/facts.css',
	'components/subnav.css',
	'components/award-strip.css',
	'components/credential.css',
	'components/form.css',
	'components/login.css',
	'components/contact.css',
	'components/community.css',
);

const ACADEMY_MENUS = array(
	'primary'              => 'Primary',
	'footer_about'         => 'Footer: About',
	'footer_membership'    => 'Footer: Membership',
	'footer_certification' => 'Footer: Certification',
	'footer_courses'       => 'Footer: Courses',
	'footer_community'     => 'Footer: Community',
	'legal'                => 'Legal',
);

const ACADEMY_FOOTER_COLUMNS = array(
	array( 'footer_about' ),
	array( 'footer_membership' ),
	array( 'footer_certification', 'footer_courses' ),
	array( 'footer_community' ),
);

const ACADEMY_PERSON_GROUPS = array(
	'founding' => 'Founding members',
	'staff'    => 'Staff',
);

const ACADEMY_CERTIFICATE_FACTS = array(
	'course_code' => 'Course code',
	'level'       => 'Level',
	'category'    => 'Category',
	'duration'    => 'Duration',
	'assessment'  => 'Assessment',
	'price'       => 'Price',
);

const ACADEMY_CERTIFICATE_SECTIONS = array(
	'vision'       => 'Course vision',
	'participants' => 'Who should participate?',
	'description'  => 'Course description',
	'outline'      => 'Course outline',
	'experts'      => 'Course experts',
	'benefits'     => 'Key benefits',
);

const ACADEMY_ENQUIRY_TOPICS = array(
	'Booking a course',
	'Certificates and exams',
	'Membership and renewals',
	'Training for my organisation',
	'Sponsorship',
	'Press and media',
	'Something else',
);

const ACADEMY_NOT_FOUND_LEAD = 'We could not find that page. It may have moved, or the address may be mistyped.';

const ACADEMY_WIDE_SIZES = '(min-width: 62rem) 50vw, 100vw';

function academy_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'caption', 'gallery', 'script', 'search-form', 'style' ) );

	register_nav_menus( ACADEMY_MENUS );

	add_image_size( 'hero', 1920, 1080 );
	add_image_size( 'hero-medium', 1440, 810 );
	add_image_size( 'hero-small', 960, 540 );
	add_image_size( 'card', 800, 600, true );
	add_image_size( 'portrait', 900, 1200, true );

	add_filter( 'image_editor_output_format', 'academy_image_output_format' );
}

add_action( 'after_setup_theme', 'academy_setup' );

function academy_image_output_format( array $formats ): array {
	$formats['image/jpeg'] = 'image/webp';

	return $formats;
}

function academy_enqueue_styles(): void {
	wp_enqueue_style( 'academy-fonts', ACADEMY_FONTS_URL, array(), null );

	$bundle = get_theme_file_path( 'assets/css/main.css' );

	if ( file_exists( $bundle ) ) {
		wp_enqueue_style( 'academy-main', get_theme_file_uri( 'assets/css/main.css' ), array( 'academy-fonts' ), (string) filemtime( $bundle ) );

		return;
	}

	$version  = wp_get_theme()->get( 'Version' );
	$previous = array( 'academy-fonts' );

	foreach ( ACADEMY_STYLESHEETS as $sheet ) {
		$handle = 'academy-' . str_replace( array( '/', '.css' ), array( '-', '' ), $sheet );

		wp_enqueue_style( $handle, get_theme_file_uri( 'assets/css/' . $sheet ), $previous, $version );

		$previous = array( $handle );
	}
}

add_action( 'wp_enqueue_scripts', 'academy_enqueue_styles' );

remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

add_filter( 'script_module_loader_src', 'wp_make_link_relative' );

add_filter( 'run_wptexturize', '__return_false' );

add_filter( 'document_title_separator', fn () => '|' );

add_filter( 'post_type_archive_title', function ( string $title, string $post_type ): string {
	return 'certificate' === $post_type ? ( (string) get_option( 'certification_title' ) ?: $title ) : $title;
}, 10, 2 );

add_action( 'pre_get_posts', function ( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'certificate' ) ) {
		return;
	}

	$query->set( 'order', 'ASC' );
	$query->set( 'orderby', 'menu_order' );
	$query->set( 'posts_per_page', -1 );
} );

function academy_meta_description(): string {
	if ( is_404() ) {
		return ACADEMY_NOT_FOUND_LEAD;
	}

	if ( is_post_type_archive( 'certificate' ) ) {
		return (string) get_option( 'certification_meta_description' );
	}

	if ( is_singular( 'certificate' ) ) {
		return (string) academy_meta( 'vision' );
	}

	if ( is_singular( 'post' ) ) {
		return (string) get_post_field( 'post_excerpt', get_queried_object_id() );
	}

	if ( is_home() ) {
		return (string) academy_meta( 'meta_description', (int) get_option( 'page_for_posts' ) );
	}

	if ( is_page() ) {
		return (string) academy_meta( 'meta_description' );
	}

	return (string) get_bloginfo( 'description' );
}

add_action( 'wp_head', function (): void {
	$description = academy_meta_description();

	if ( '' !== $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}
}, 1 );

function academy_section_url(): string {
	if ( is_home() || is_singular( 'post' ) ) {
		return academy_page_url( 'about' );
	}

	if ( is_post_type_archive( 'certificate' ) || is_singular( 'certificate' ) ) {
		return (string) get_post_type_archive_link( 'certificate' );
	}

	if ( is_page() && ! is_front_page() ) {
		return (string) get_permalink( get_queried_object_id() );
	}

	return '';
}

function academy_is_current( string $url ): bool {
	static $section = null;

	$section ??= untrailingslashit( academy_section_url() );

	if ( '' === $section ) {
		return false;
	}

	$absolute = str_starts_with( $url, '/' ) ? home_url( $url ) : $url;

	return untrailingslashit( $absolute ) === $section;
}

add_filter( 'nav_menu_css_class', fn () => array() );

add_filter( 'nav_menu_item_id', fn () => '' );

add_filter( 'nav_menu_link_attributes', function ( array $atts, WP_Post $item, stdClass $args ): array {
	unset( $atts['aria-current'] );

	if ( 'primary' !== $args->theme_location ) {
		return $atts;
	}

	$atts['class'] = 'site-nav__link';

	if ( academy_is_current( $item->url ) ) {
		$atts['aria-current'] = 'page';
	}

	return $atts;
}, 10, 3 );

function academy_meta( string $key, ?int $post_id = null ) {
	return get_post_meta( $post_id ?? get_queried_object_id(), $key, true );
}

function academy_list( $value ): array {
	return is_array( $value ) ? array_values( $value ) : array();
}

function academy_rows( string $key, ?int $post_id = null ): array {
	return academy_list( academy_meta( $key, $post_id ) );
}

function academy_option_rows( string $name ): array {
	return academy_list( get_option( $name ) );
}

function academy_paragraphs( string $text ): array {
	return array_map( 'trim', preg_split( '/\n\s*\n/', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
}

function academy_lines( string $text ): array {
	$lines = array_map( 'trim', preg_split( '/\R/', trim( $text ) ) );

	return array_values( array_filter( $lines, fn ( string $line ) => '' !== $line ) );
}

function academy_linkify( string $text ): string {
	return make_clickable( esc_html( $text ) );
}

function academy_hidden_suffix( string $text ): string {
	$text = trim( $text );

	return '' === $text ? '' : '<span class="visually-hidden"> ' . esc_html( $text ) . '</span>';
}

function academy_hero_title( int $post_id ): array {
	$lines = academy_lines( (string) academy_meta( 'hero_title', $post_id ) );

	return $lines ?: array( get_the_title( $post_id ) );
}

function academy_page_url( string $slug ): string {
	$page = get_page_by_path( $slug );

	return $page ? (string) get_permalink( $page ) : home_url( '/' );
}

function academy_news_url(): string {
	$page_id = (int) get_option( 'page_for_posts' );

	return $page_id ? (string) get_permalink( $page_id ) : home_url( '/' );
}

function academy_picture( int $attachment_id, string $size, string $picture_class, array $attributes ): void {
	$image = $attachment_id ? wp_get_attachment_image( $attachment_id, $size, false, $attributes ) : '';

	if ( '' === $image ) {
		return;
	}

	printf( '<picture class="%s">%s</picture>', esc_attr( $picture_class ), $image );
}

function academy_latest_posts( int $count ): WP_Query {
	$query = new WP_Query( array(
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'posts_per_page'      => $count,
	) );

	update_post_thumbnail_cache( $query );

	return $query;
}

function academy_people_by_group(): array {
	$query = new WP_Query( array(
		'no_found_rows'  => true,
		'order'          => 'ASC',
		'orderby'        => 'menu_order',
		'post_type'      => 'person',
		'posts_per_page' => -1,
	) );

	update_post_thumbnail_cache( $query );

	$groups = array_fill_keys( array_keys( ACADEMY_PERSON_GROUPS ), array() );

	foreach ( $query->posts as $person ) {
		$groups[ (string) academy_meta( 'group', $person->ID ) ][] = $person;
	}

	return array_intersect_key( $groups, ACADEMY_PERSON_GROUPS );
}

function academy_certificates(): array {
	return get_posts( array(
		'order'          => 'ASC',
		'orderby'        => 'menu_order',
		'post_type'      => 'certificate',
		'posts_per_page' => -1,
	) );
}
