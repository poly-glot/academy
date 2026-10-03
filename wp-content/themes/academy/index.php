<?php
get_header();

get_template_part( 'template-parts/page-title', null, array( 'title' => wp_strip_all_tags( get_the_archive_title() ) ) );
get_template_part( 'template-parts/news-list' );

get_footer();
