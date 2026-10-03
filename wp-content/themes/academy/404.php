<?php
get_header();

get_template_part( 'template-parts/page-title', null, array( 'title' => 'Page not found' ) );
?>
<div class="section">
	<div class="container">
		<div class="stack">
			<p class="lead"><?php echo esc_html( ACADEMY_NOT_FOUND_LEAD ); ?></p>
			<ul class="link-list">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Go to the home page</a></li>
				<li><a href="<?php echo esc_url( academy_page_url( 'courses' ) ); ?>">Browse courses</a></li>
				<li><a href="<?php echo esc_url( academy_page_url( 'contact' ) ); ?>">Contact us</a></li>
			</ul>
		</div>
	</div>
</div>
<?php
get_footer();
