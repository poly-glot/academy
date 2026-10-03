<?php
get_header();

$news_page_id = (int) get_option( 'page_for_posts' );

get_template_part( 'template-parts/page-title', null, array(
	'lead'  => (string) academy_meta( 'hero_lead', $news_page_id ),
	'title' => implode( ' ', academy_hero_title( $news_page_id ) ),
) );
get_template_part( 'template-parts/news-list' );

get_footer();
