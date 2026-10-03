<?php
get_header();

$page_id = get_queried_object_id();
$note    = (string) academy_meta( 'form_note', $page_id );

get_template_part( 'template-parts/page-title', null, array(
	'lead'  => (string) academy_meta( 'hero_lead', $page_id ),
	'title' => implode( ' ', academy_hero_title( $page_id ) ),
) );
?>
<section class="section" id="help" aria-labelledby="help-title">
	<div class="split split--start container">
		<h2 id="help-title"><?php echo esc_html( (string) academy_meta( 'contact_help_title', $page_id ) ); ?></h2>
		<div class="faq">
			<?php foreach ( academy_rows( 'contact_faqs', $page_id ) as $faq ) : ?>
				<details class="faq__item">
					<summary class="faq__question"><?php echo esc_html( $faq['question'] ?? '' ); ?></summary>
					<p class="faq__answer"><?php echo esc_html( $faq['answer'] ?? '' ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<section class="section section--soft" id="details" aria-labelledby="details-title">
	<div class="split split--start container">
		<div class="stack">
			<h2 id="details-title"><?php echo esc_html( (string) academy_meta( 'contact_details_title', $page_id ) ); ?></h2>
			<dl class="contact-list">
				<?php foreach ( academy_rows( 'contact_groups', $page_id ) as $group ) : ?>
					<?php
					$email = (string) ( $group['email'] ?? '' );
					$phone = (string) ( $group['phone'] ?? '' );
					$value = '' !== $email
						? '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>'
						: '<a href="' . esc_url( 'tel:' . ( $group['phone_href'] ?? '' ) ) . '">' . str_replace( ' ', '&nbsp;', esc_html( $phone ) ) . '</a>';
					$extra = trim( (string) ( $group['note'] ?? '' ) );
					?>
					<div class="contact-list__item"><dt class="contact-list__label"><?php echo esc_html( $group['label'] ?? '' ); ?></dt><dd class="contact-list__value"><?php echo $value . ( '' === $extra ? '' : ' ' . esc_html( $extra ) ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
			<h3><?php echo esc_html( (string) academy_meta( 'contact_address_title', $page_id ) ); ?></h3>
			<address class="contact-list__value"><?php echo implode( '<br>', array_map( 'esc_html', academy_lines( (string) get_option( 'address' ) ) ) ); ?></address>
		</div>
		<figure class="map">
			<img class="map__image" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/map.svg' ) ); ?>" alt="<?php echo esc_attr( (string) academy_meta( 'contact_map_alt', $page_id ) ); ?>" width="800" height="500" loading="lazy">
			<figcaption class="map__caption"><?php echo esc_html( (string) academy_meta( 'contact_map_caption', $page_id ) ); ?></figcaption>
		</figure>
	</div>
	<div class="container">
		<p class="company-note"><?php echo esc_html( (string) get_option( 'company_notice' ) ); ?></p>
	</div>
</section>
<section class="section" id="enquiry" aria-labelledby="enquiry-title">
	<div class="container">
		<div class="enquiry">
			<div class="stack stack--tight">
				<h2 id="enquiry-title"><?php echo esc_html( (string) academy_meta( 'form_title', $page_id ) ); ?></h2>
				<p><?php echo esc_html( (string) academy_meta( 'form_intro', $page_id ) ); ?></p>
				<p class="notice" id="demo-note"><?php echo esc_html( $note ); ?></p>
			</div>
			<form class="form" action="<?php echo esc_url( get_permalink( $page_id ) . '#thanks' ); ?>" method="get">
				<div class="form__row">
					<div class="field">
						<label class="field__label" for="name">Your name<span class="visually-hidden"> (required)</span></label>
						<p class="field__hint" id="name-hint">So we know what to call you.</p>
						<input class="field__input" id="name" name="your_name" type="text" autocomplete="name" required aria-describedby="name-hint">
					</div>
					<div class="field">
						<label class="field__label" for="email">Email address<span class="visually-hidden"> (required)</span></label>
						<p class="field__hint" id="email-hint">We only use this to reply.</p>
						<input class="field__input" id="email" name="email" type="email" autocomplete="email" required aria-describedby="email-hint">
					</div>
				</div>
				<div class="form__row">
					<div class="field">
						<label class="field__label" for="organisation">Organisation <span class="field__optional">(optional)</span></label>
						<p class="field__hint" id="organisation-hint">Leave blank if you are asking for yourself.</p>
						<input class="field__input" id="organisation" name="organisation" type="text" autocomplete="organization" aria-describedby="organisation-hint">
					</div>
					<div class="field">
						<label class="field__label" for="topic">What is it about?</label>
						<select class="field__select" id="topic" name="topic">
							<option value="">Choose a topic</option>
							<?php foreach ( ACADEMY_ENQUIRY_TOPICS as $topic ) : ?>
								<option><?php echo esc_html( $topic ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="field">
					<label class="field__label" for="message">Your message<span class="visually-hidden"> (required)</span></label>
					<p class="field__hint" id="message-hint">A few sentences is plenty. Please do not include personal or financial details.</p>
					<textarea class="field__textarea" id="message" name="message" rows="6" required aria-describedby="message-hint demo-note"></textarea>
				</div>
				<div class="form__actions">
					<p class="field__hint"><?php echo esc_html( $note ); ?></p>
					<button class="button" type="submit">Send enquiry</button>
				</div>
			</form>
			<div class="target-message target-message--panel" id="thanks" tabindex="-1">
				<h3><?php echo esc_html( (string) academy_meta( 'thank_you_title', $page_id ) ); ?></h3>
				<p><?php echo esc_html( (string) academy_meta( 'thank_you_text', $page_id ) ); ?></p>
				<a class="text-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to the home page</a>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
