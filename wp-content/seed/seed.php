<?php

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

kses_remove_filters();

if ( get_stylesheet() !== 'academy' ) {
	switch_theme( 'academy' );
	echo "theme: switched to academy\n";
}

if ( ! function_exists( 'academy_setup' ) ) {
	require_once get_theme_file_path( 'functions.php' );
	academy_setup();
}

$identity = [
	'blogdescription' => 'Practical business education',
	'blogname'        => 'Academy',
	'date_format'     => 'j F Y',
	'start_of_week'   => 1,
	'time_format'     => 'H:i',
	'timezone_string' => 'Europe/London',
];

foreach ( $identity as $name => $value ) {
	update_option( $name, $value );
}

global $wp_rewrite;
$wp_rewrite->set_permalink_structure( '/news/%postname%/' );

echo "settings: identity, timezone and permalinks set\n";

function academy_seed_media_signature(): string {
	return md5( wp_json_encode( [ wp_get_registered_image_subsizes(), wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] ) ] ) );
}

function academy_seed_attachment( string $filename, string $alt ): array {
	$existing = get_posts( [
		'meta_key'    => '_academy_seed_file',
		'meta_value'  => $filename,
		'numberposts' => 1,
		'post_status' => 'any',
		'post_type'   => 'attachment',
	] );

	$signature = academy_seed_media_signature();

	if ( $existing && get_post_meta( $existing[0]->ID, '_academy_seed_signature', true ) === $signature ) {
		update_post_meta( $existing[0]->ID, '_wp_attachment_image_alt', $alt );
		return [ (int) $existing[0]->ID, 0 ];
	}

	if ( $existing ) {
		wp_delete_attachment( $existing[0]->ID, true );
		echo "media REGENERATING {$filename}\n";
	}

	$source = __DIR__ . '/img/' . $filename;

	if ( ! file_exists( $source ) ) {
		echo "media MISSING {$filename}\n";
		return [ 0, 0 ];
	}

	$tmp = wp_tempnam( $filename );
	copy( $source, $tmp );

	$id = media_handle_sideload( [ 'name' => $filename, 'tmp_name' => $tmp ], 0 );

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		echo "media FAILED {$filename}: " . $id->get_error_message() . "\n";
		return [ 0, 0 ];
	}

	update_post_meta( $id, '_academy_seed_file', $filename );
	update_post_meta( $id, '_academy_seed_signature', $signature );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );

	return [ (int) $id, 1 ];
}

function academy_seed_post( string $type, string $slug, array $args, array $meta = [], int $thumb = 0 ): array {
	$found = get_posts( [ 'numberposts' => 1, 'post_name__in' => [ $slug ], 'post_status' => 'any', 'post_type' => $type ] );
	$args  = array_merge( [ 'post_name' => $slug, 'post_status' => 'publish', 'post_type' => $type ], $args );

	if ( $found ) {
		$args['ID'] = $found[0]->ID;
		$id         = wp_update_post( $args );
		$created    = 0;
	} else {
		$id      = wp_insert_post( $args );
		$created = 1;
	}

	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}

	if ( $thumb ) {
		set_post_thumbnail( $id, $thumb );
	}

	return [ (int) $id, $created ];
}

function academy_seed_menu( string $name, array $items ): int {
	$menu     = wp_get_nav_menu_object( $name );
	$id       = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	$existing = wp_get_nav_menu_items( $id ) ?: [];
	$by_title = array_column( $existing, null, 'title' );
	$created  = 0;

	foreach ( array_values( $items ) as $index => $item ) {
		$item   += [ 'menu-item-position' => $index + 1, 'menu-item-status' => 'publish' ];
		$current = $by_title[ $item['menu-item-title'] ] ?? null;

		if ( ! $current ) {
			wp_update_nav_menu_item( $id, 0, $item );
			$created++;
			continue;
		}

		$moved      = (int) $current->menu_order !== $item['menu-item-position'];
		$retargeted = 'custom' === $item['menu-item-type'] && $current->url !== $item['menu-item-url'];

		if ( $moved || $retargeted ) {
			wp_update_nav_menu_item( $id, $current->db_id, $item );
		}
	}

	return $created;
}

$images = [
	'about-film-still.jpg'           => 'A tutor talks through a diagram on a whiteboard with a small group of learners.',
	'hero-about.jpg'                 => 'A woman smiles as she works on a laptop in a bright open-plan office.',
	'hero-certification.jpg'         => 'A man in glasses looks thoughtfully to one side, holding a pen.',
	'hero-courses.jpg'               => 'A woman with a laptop listens during a session in a training room.',
	'hero-home.jpg'                  => 'A professional reads course notes on a laptop at a desk in a bright office.',
	'hero-login.jpg'                 => '',
	'hero-membership.jpg'            => 'A man reads from his laptop at a desk beside a tall window.',
	'hero-news.jpg'                  => 'A canal basin at dusk, with brick warehouses and glass towers reflected in the water.',
	'news-1.jpg'                     => 'Supplier questionnaires and certificate paperwork laid out on a desk beside a pen.',
	'news-2.jpg'                     => 'A laptop on a table shows a video call with three panellists.',
	'news-3.jpg'                     => 'A conference audience faces a speaker on a softly lit stage.',
	'news-4.jpg'                     => 'Hands use a calculator beside a printed fee sheet on a desk.',
	'news-5.jpg'                     => 'A hand holds a phone showing a course screen, with a train window behind.',
	'news-6.jpg'                     => 'Six people talk around a meeting table in a bright room.',
	'portrait-amara-okafor.jpg'      => 'Dr Amara Okafor, Vice Chair of Academy.',
	'portrait-daniel-hughes.jpg'     => 'Daniel Hughes, Head of Certification at Academy.',
	'portrait-fatima-rahman.jpg'     => 'Fatima Rahman, Member Services Manager at Academy.',
	'portrait-hannah-cartwright.jpg' => 'Hannah Cartwright, Standards Lead at Academy.',
	'portrait-jasmine-clarke.jpg'    => 'Jasmine Clarke, Head of Learning at Academy.',
	'portrait-margaret-ellison.jpg'  => 'Margaret Ellison, Chair of Academy.',
	'portrait-owen-griffiths.jpg'    => 'Owen Griffiths, Platform Lead at Academy.',
	'portrait-rahul-mehta.jpg'       => 'Rahul Mehta, Programmes Lead at Academy.',
	'portrait-sophie-brennan.jpg'    => 'Sophie Brennan, Communications Manager at Academy.',
	'portrait-thomas-reilly.jpg'     => 'Thomas Reilly, Treasurer of Academy.',
];

$img           = [];
$created_media = 0;

foreach ( $images as $file => $alt ) {
	[ $img[ $file ], $c ] = academy_seed_attachment( $file, $alt );
	$created_media       += $c;
}

echo "media: {$created_media} created, " . count( array_filter( $img ) ) . ' of ' . count( $images ) . " ready\n";

$content_careers = <<<'HTML'
<p>We have no open roles at the moment. When we do, we list them here with the salary range, the hours and the closing date. We do not use recruitment agencies.</p>
<p><strong>How we work.</strong> Most of the team works from our office in Birmingham two or three days a week and from home the rest. Core hours are 10am to 3pm; the rest is up to you and your manager. Everyone gets 28 days of holiday plus bank holidays, and free access to every Academy course.</p>
<p><strong>Tutors and assessors.</strong> We are always glad to hear from experienced practitioners who would like to teach or mark for us. You will need at least five years of commercial experience and the patience to explain things simply. Most tutors teach two to six days a year alongside their main job. Send a short note about your experience through the enquiry form, choosing "Something else" as the topic.</p>
<p><strong>Fair recruitment.</strong> We shortlist against a published list of criteria, and we offer an interview to any disabled applicant who meets them. Tell us if you need adjustments at any stage.</p>
HTML;

$content_terms = <<<'HTML'
<h2>This is a demonstration website</h2>
<p>Academy, Academy Learning Ltd and the Academy Examinations Board are fictional. They were created to demonstrate a website design. No courses, certificates, memberships or services are on sale, and no payment can be taken. The Academy Examinations Board is not a real awarding body, and Academy certificates are not recognised qualifications.</p>
<h2>Who we are</h2>
<p>In these terms, "we" means Academy Learning Ltd, a fictional company with the placeholder number 00000000 and a fictional registered office at Foundry Court, 41 Lawley Terrace, Birmingham B3 1AA.</p>
<h2>Using this site</h2>
<p>You may browse, print and share pages for personal, non-commercial use. Please do not copy large parts of the site, present it as a real business or use it to mislead anyone.</p>
<h2>Information on this site</h2>
<p>Course descriptions, prices, dates, people and news are invented. They are not advice and should not be relied on for any decision. Where the site mentions laws or practice in general terms, it does so only to make the examples realistic.</p>
<h2>Forms</h2>
<p>The forms on this site do not send or store anything. Please do not enter real personal, financial or confidential information.</p>
<h2>Links</h2>
<p>Social and sharing links are samples and do not lead to real accounts.</p>
<h2>Liability</h2>
<p>The site is provided as it is, for demonstration only. To the extent the law allows, we accept no liability for any loss arising from its use.</p>
<h2>Changes</h2>
<p>We may update these terms at any time. This version is dated 30 September 2026.</p>
<h2>Governing law</h2>
<p>These terms are governed by the law of England and Wales.</p>
HTML;

$content_copyright = <<<'HTML'
<h2>Copyright</h2>
<p>The text, layout, illustrations and photographs on this site are © 2026 Academy Learning Ltd unless stated otherwise. Photographs were created for this demonstration and show fictional people. You may quote short extracts with a link back to the page.</p>
<h2>Names and marks</h2>
<p>"Academy", the stepped-A mark and the certificate names FCCP, CCPP, CCM, CSA and CCR are fictional and used only on this demonstration site. They are not registered trademarks, and nothing here suggests a connection with any real organisation that uses a similar name.</p>
<h2>Third-party names</h2>
<p>Apple and Android are mentioned only to describe where a fictional app would be available. They belong to their owners. YouTube and LinkedIn are named in sample links that do not lead anywhere.</p>
<h2>Fonts</h2>
<p>The site uses the Manrope and IBM Plex Sans typefaces under their open font licences.</p>
<h2>Reporting a concern</h2>
<p>If you think something on this site infringes your rights, email hello@academy.junaid.guru and we will look at it promptly.</p>
HTML;

$content_privacy = <<<'HTML'
<h2>Who we are</h2>
<p>Academy Learning Ltd is a fictional company created for this demonstration website. In this notice "we" means that fictional company. A real organisation in its position would be the data controller for the personal data described below and would be registered with the Information Commissioner's Office. Academy is not registered, because it does not exist.</p>
<h2>What this site collects</h2>
<p>This site has no accounts, no analytics and no advertising. It sets no cookies. The forms on the contact and login pages do not send or store anything: when you press the button, the page simply shows a message. Please do not type real personal data into them.</p>
<p>The site loads its fonts from Google Fonts. When your browser fetches them, Google receives your IP address and basic browser details, as with any web request. We do not receive or store that information. The site is hosted on a web server that may keep standard access logs (IP address, time, page requested) for a short period for security.</p>
<h2>What a real Academy would collect</h2>
<p>If Academy were real, it would collect:</p>
<ul>
<li>your name, contact details and organisation when you join, book or make an enquiry</li>
<li>your course progress, exam results and learning hours</li>
<li>payment details, handled by a payment provider and never stored in full by us</li>
<li>records of reasonable adjustments you ask for in exams, kept separately and with restricted access</li>
</ul>
<h2>Why, and on what lawful basis</h2>
<p>We would use this data to provide membership, courses and exams (performance of a contract), to keep the accounts we must keep by law (legal obligation), to prevent exam fraud and keep systems secure (legitimate interests) and, only with your consent, to send newsletters. Information about adjustments relating to health would be handled as special category data, with your explicit consent.</p>
<h2>Sharing</h2>
<p>We would share data only with suppliers who help us run the service, such as hosting, email and payment providers, under written contracts. We would confirm a member's certificates to an employer only if the member chose to appear in the public directory or gave permission.</p>
<h2>How long we keep it</h2>
<p>Membership and course records for six years after your membership ends. Certificate records indefinitely, so that we can confirm a result if asked. Enquiries for two years.</p>
<h2>Your rights</h2>
<p>Under UK data protection law you have the right to access your data, to have it corrected or deleted, to restrict or object to its use, to move it to another provider and to withdraw consent at any time. To use any of these rights you would email hello@academy.junaid.guru. You also have the right to complain to the Information Commissioner's Office.</p>
<h2>Changes</h2>
<p>This notice was last updated on 30 September 2026.</p>
HTML;

$content_accessibility = <<<'HTML'
<h2>Our target</h2>
<p>We aim to meet the Web Content Accessibility Guidelines (WCAG) 2.2 at level AA.</p>
<h2>How we tested</h2>
<p>Every page was checked in three ways before publication:</p>
<ul>
<li>automated HTML validation with html-validate</li>
<li>automated accessibility testing with pa11y against the WCAG 2 AA standard</li>
<li>manual keyboard testing: we moved through every page using only the keyboard to check that every link, button and form field can be reached, that the focus outline is always visible and that the order makes sense</li>
</ul>
<p>Automated tools find only some problems, and we have not yet tested with a full range of screen readers or with disabled users. We do not claim full conformance until that testing is done.</p>
<h2>What we have built in</h2>
<ul>
<li>A "Skip to main content" link on every page</li>
<li>Headings in a logical order, with one main heading per page</li>
<li>Text and controls with colour contrast that meets AA</li>
<li>Visible labels on every form field, with hints read out alongside them</li>
<li>Tabs and other interactive parts that work without JavaScript</li>
<li>Animation reduced when your device asks for reduced motion</li>
</ul>
<h2>Known limitations</h2>
<p>Some photographs are still being produced. Until they are ready, a plain coloured panel appears in their place; it carries no information. The video panel on the About page is a still image, because the film has not been made yet.</p>
<h2>Feedback</h2>
<p>If you find a problem, email hello@academy.junaid.guru or call +44 (0)121 496 0142. Tell us the page and what went wrong. We aim to reply within five working days. Because this is a demonstration site, fixes may be made in a later version rather than straight away.</p>
<h2>Date</h2>
<p>This statement was prepared on 30 September 2026 and will be reviewed when the site changes.</p>
HTML;

$pages = [
	'about'         => [
		'image' => 'hero-about.jpg',
		'meta'  => [
			'about_film_caption'   => 'Film coming soon',
			'about_film_image'     => $img['about-film-still.jpg'],
			'about_goals'          => [
				[ 'text' => 'Publish a clear, open standard for everyday commercial work.' ],
				[ 'text' => 'Make good training affordable for small firms and the public sector.' ],
				[ 'text' => 'Certify people on what they can do, not on how long they sat in a room.' ],
			],
			'about_goals_kicker'   => 'What we are for',
			'about_goals_title'    => 'Our goals',
			'about_people_intro'   => 'Our founding members set the standard and sit on the board. Our staff run the courses, the exams and the member services you use every day.',
			'about_people_kicker'  => 'Our people',
			'about_people_title'   => 'The people behind Academy',
			'hero_button_label'    => 'Explore certification',
			'hero_button_url'      => '/certification/',
			'hero_kicker'          => 'Who we are',
			'hero_lead'            => 'Independent, member-funded and based in Birmingham since 2014. We write the courses, set the standard and answer your emails.',
			'hero_link_label'      => 'Meet the people',
			'hero_link_url'        => '#people',
			'hero_title'           => 'About Academy',
			'intro_kicker'         => 'Our story',
			'intro_text'           => "Academy was set up in Birmingham in 2014 by five practitioners who were tired of hiring people with good degrees and no idea how to read a supplier contract. They wrote a short course, ran it in a borrowed meeting room and asked the people who took it what was missing.\n\nTwelve years later we have about 2,400 members, five certificates and a course catalogue that covers contracts, suppliers, procurement, commercial risk and working capital. We are independent, member-funded and still small enough that the people who write the courses answer your emails.",
			'intro_title'          => 'We\'re empowering the people who keep commercial work on track',
			'meta_description'     => 'Academy was founded in Birmingham in 2014 by five commercial practitioners. Meet the people who set our standard and run our courses.',
			'mission_button_label' => 'See our certificates',
			'mission_button_url'   => '/certification/',
			'mission_kicker'       => 'Our mission',
			'mission_text'         => 'We believe most commercial losses start with ordinary gaps in skill: an unread clause, an unchecked supplier, a payment term nobody negotiated. Our mission is to close those gaps. We set the standard in the open, teach it in plain English, assess it fairly and keep members current as practice changes.',
			'mission_title'        => 'Make the basics of commercial work reliable',
		],
		'title' => 'About',
	],
	'accessibility' => [
		'content' => $content_accessibility,
		'image'   => '',
		'meta'    => [
			'hero_lead'        => 'We want everyone to be able to use this site. This statement says what we have tested, what we know is not perfect and how to tell us about problems.',
			'hero_title'       => 'Accessibility statement',
			'meta_description' => 'We want everyone to be able to use this site. This statement says what we have tested, what we know is not perfect and how to tell us about problems.',
		],
		'title'   => 'Accessibility',
	],
	'careers'       => [
		'content' => $content_careers,
		'image'   => '',
		'meta'    => [
			'hero_lead'        => 'We are a team of 23 people in Birmingham, plus around 40 freelance tutors and assessors across the country.',
			'hero_title'       => 'Careers',
			'meta_description' => 'We are a team of 23 people in Birmingham, plus around 40 freelance tutors and assessors across the country.',
		],
		'title'   => 'Careers',
	],
	'community'     => [
		'image' => '',
		'meta'  => [
			'community_sections' => [
				[
					'anchor'      => 'events',
					'link_hidden' => ' at Academy',
					'link_label'  => 'See upcoming events',
					'link_url'    => '/news/autumn-conference-registration-opens/',
					'text'        => 'Our autumn conference brings around 300 members to Birmingham for a day of short talks and workshops. Through the year we also run half-day briefings on new legislation and practical topics, such as late-payment rules or supplier insolvency. Members pay a reduced rate, and Senior members attend the conference free.',
					'title'       => 'Events',
				],
				[
					'anchor'      => 'research',
					'link_hidden' => ' findings',
					'link_label'  => 'Read our research',
					'link_url'    => '/news/',
					'text'        => 'Each spring we survey members and their employers on how contracts and suppliers are managed in practice: who owns them, what gets checked and where things go wrong. The anonymised findings feed straight into our courses and the annual review of the Academy standard. Members can read the full results.',
					'title'       => 'Research',
				],
				[
					'anchor'      => 'publications',
					'link_hidden' => ' in the member library',
					'link_label'  => 'Browse publications',
					'link_url'    => '/login/',
					'text'        => 'We publish practice notes, template clauses and short guides written by practitioners and checked by our standards committee. Titles include a plain-language drafting guide and a checklist for onboarding new suppliers. Everything is free to members as a download, and selected guides are open to everyone.',
					'title'       => 'Publications',
				],
				[
					'anchor'      => 'sponsorship',
					'link_hidden' => '',
					'link_label'  => 'Ask about sponsorship',
					'link_url'    => '/contact/#enquiry',
					'text'        => 'Organisations can sponsor a conference session, a regional meet-up or a bursary that pays for a learner\'s course and exam. Sponsors never write or approve course content, and we publish a list of every sponsorship we accept each year. If you would like to discuss an idea, please get in touch.',
					'title'       => 'Sponsorship',
				],
				[
					'anchor'      => 'training',
					'link_hidden' => '',
					'link_label'  => 'Plan training for your team',
					'link_url'    => '/contact/#enquiry',
					'text'        => 'Employers can book any of our courses as a closed group, delivered online or at their own premises. We can add examples from your sector and align the course with your internal procedures, while the assessment stays the same as for everyone else. Groups usually range from eight to 20 people.',
					'title'       => 'Training',
				],
				[
					'anchor'      => 'webinars',
					'link_hidden' => '',
					'link_label'  => 'Watch the latest webinar',
					'link_url'    => '/news/contract-management-webinar-recording/',
					'text'        => 'Once a month a panel of members takes one practical question, such as how to manage a contract change or what to do when a supplier stops replying, and works through it live. Webinars run on weekday evenings for one hour, and every recording goes into the member library afterwards.',
					'title'       => 'Webinars',
				],
				[
					'anchor'      => 'forums',
					'link_hidden' => ' (members only)',
					'link_label'  => 'Go to the forums',
					'link_url'    => '/login/',
					'text'        => 'Members can ask questions and share templates in moderated online forums grouped by topic: contracts, suppliers, procurement, risk and working capital. Our moderators are volunteer Senior members. Posts stay inside the membership, and we remove anything that names a client or breaches a confidentiality agreement.',
					'title'       => 'Forums',
				],
				[
					'anchor'      => 'regional-networks',
					'link_hidden' => '',
					'link_label'  => 'Read about the employers network',
					'link_url'    => '/news/midlands-employers-network-formed/',
					'text'        => 'Member-led networks meet in person every month or two in Birmingham, Coventry, Nottingham, Leicester and Stoke-on-Trent, with an online group for members elsewhere. Meetings are informal: a short talk, a case discussion and time to talk. The new Midlands Employers Network links the organisations that train with us.',
					'title'       => 'Regional networks',
				],
			],
			'hero_lead'          => 'Courses teach the method. The community is where members compare notes, test ideas and find out how other organisations handle the same problems.',
			'hero_title'         => 'Community',
			'meta_description'   => 'Events, research, publications, webinars, forums and regional networks for people who work in contracts, suppliers and commercial risk.',
		],
		'title' => 'Community',
	],
	'contact'       => [
		'image' => '',
		'meta'  => [
			'contact_address_title' => 'Visit or write to us',
			'contact_details_title' => 'Get in touch',
			'contact_faqs'          => [
				[
					'answer'   => 'Log in to My Academy, choose the course and pay by card. If your employer is paying, choose "Invoice" at checkout and add a purchase order number.',
					'question' => 'How do I book a course?',
				],
				[
					'answer'   => 'Results for online exams appear in My Academy within ten working days. Case-study and portfolio results take up to four weeks.',
					'question' => 'When do I get my exam result?',
				],
				[
					'answer'   => 'Yes, free of charge up to seven days before your slot. After that, a £25 change fee applies unless you are ill or have an emergency.',
					'question' => 'Can I change my exam date?',
				],
				[
					'answer'   => 'Use "Forgot password?" on the login page. If the email does not arrive within an hour, contact Member Services.',
					'question' => 'I have forgotten my password.',
				],
			],
			'contact_groups'        => [
				[
					'email'      => 'hello@academy.junaid.guru',
					'label'      => 'General enquiries',
					'note'       => '',
					'phone'      => '',
					'phone_href' => '',
				],
				[
					'email'      => 'members@academy.junaid.guru',
					'label'      => 'Membership and renewals',
					'note'       => '',
					'phone'      => '',
					'phone_href' => '',
				],
				[
					'email'      => 'press@academy.junaid.guru',
					'label'      => 'Press',
					'note'       => '',
					'phone'      => '',
					'phone_href' => '',
				],
				[
					'email'      => '',
					'label'      => 'Phone',
					'note'       => '(Monday to Friday, 9am to 5pm)',
					'phone'      => '+44 (0)121 496 0142',
					'phone_href' => '+441214960142',
				],
			],
			'contact_help_title'    => 'Common questions',
			'contact_map_alt'       => 'Street map of central Birmingham with the Academy office marked near the canal.',
			'contact_map_caption'   => 'Map for illustration only.',
			'form_intro'            => 'Tell us what you need and the right person will reply.',
			'form_note'             => 'This is a demonstration form. Please do not send real data: nothing you type is stored or sent.',
			'form_title'            => 'Send us an enquiry',
			'hero_lead'             => 'Most questions are answered below. If yours is not, email or call us, or use the enquiry form. We reply within two working days.',
			'hero_title'            => 'How may we help?',
			'meta_description'      => 'Contact Academy about courses, certificates, membership or training for your team. Email, phone and an enquiry form.',
			'thank_you_text'        => 'This is a demonstration site, so your message has not been sent anywhere. On a real site we would reply within two working days.',
			'thank_you_title'       => 'Thank you',
		],
		'title' => 'Contact',
	],
	'copyright'     => [
		'content' => $content_copyright,
		'image'   => '',
		'meta'    => [
			'hero_title'       => 'Copyright and trademarks',
			'meta_description' => 'How the text, images, names and marks on this demonstration website may be used, and where to report a concern.',
		],
		'title'   => 'Copyright and trademarks',
	],
	'courses'       => [
		'image' => 'hero-courses.jpg',
		'meta'  => [
			'courses_certificates_links'    => [
				[ 'label' => 'Foundation Certificate in Commercial Practice (FCCP)', 'url' => '/certification/fccp/' ],
				[ 'label' => 'Certified Commercial Practice Professional (CCPP)', 'url' => '/certification/ccpp/' ],
				[ 'label' => 'All five certificates', 'url' => '/certification/' ],
			],
			'courses_certificates_text'     => 'Our courses map to five certificates. Start with the foundation, or go straight to a specialist route.',
			'courses_certificates_title'    => 'Working towards a certificate?',
			'courses_intro_text'            => "Our courses are short on purpose. Most take between six and 15 hours, split into modules of under an hour, and each one ends with a practical task you can use at work the following week. Take them on their own, or as preparation for one of our certificates.\n\nFoundation courses, the 100-series, cover the basics every commercial role needs. Practitioner courses, the 200-series, go deeper into specialist topics and assume some experience. Members pay £45 for a foundation course and £85 for a practitioner course; non-members pay 20 per cent more.",
			'courses_intro_title'           => 'Learn the work, one course at a time',
			'courses_list_title'            => 'Course list',
			'courses_series'                => [
				[
					'groups' => [
						[
							'courses' => [
								[ 'code' => 'CP 101', 'title' => 'Reading a commercial contract' ],
								[ 'code' => 'CP 102', 'title' => 'Quotations, pricing and margins' ],
								[ 'code' => 'CP 103', 'title' => 'Purchase orders and invoices that match' ],
							],
							'title'   => 'Business Needs',
						],
						[
							'courses' => [
								[ 'code' => 'CP 111', 'title' => 'Who can sign: delegated authority' ],
								[ 'code' => 'CP 112', 'title' => 'Records, approvals and audit trails' ],
								[ 'code' => 'CP 113', 'title' => 'Compliance essentials for commercial staff' ],
							],
							'title'   => 'Governance Basics',
						],
						[
							'courses' => [
								[ 'code' => 'CP 121', 'title' => 'Cash flow and payment terms' ],
								[ 'code' => 'CP 122', 'title' => 'Finding and onboarding new suppliers' ],
								[ 'code' => 'CP 123', 'title' => 'Bidding for your first public contract' ],
							],
							'title'   => 'Growing a Business',
						],
					],
					'title'  => 'Foundation courses (100-series)',
				],
				[
					'groups' => [
						[
							'courses' => [
								[ 'code' => 'CP 201', 'title' => 'Contract drafting for non-lawyers' ],
								[ 'code' => 'CP 202', 'title' => 'Change control and variations' ],
								[ 'code' => 'CP 203', 'title' => 'Claims, disputes and settlement' ],
							],
							'title'   => 'Specialist Solutions',
						],
						[
							'courses' => [
								[ 'code' => 'CP 211', 'title' => 'Service levels and performance measures' ],
								[ 'code' => 'CP 212', 'title' => 'Framework agreements and call-off contracts' ],
								[ 'code' => 'CP 213', 'title' => 'Software, data and licence agreements' ],
							],
							'title'   => 'Specialist Product',
						],
						[
							'courses' => [
								[ 'code' => 'CP 221', 'title' => 'Supplier due diligence in depth' ],
								[ 'code' => 'CP 222', 'title' => 'Modern slavery and supply-chain checks' ],
								[ 'code' => 'CP 223', 'title' => 'Anti-bribery controls that work' ],
							],
							'title'   => 'Advanced Governance',
						],
						[
							'courses' => [
								[ 'code' => 'CP 231', 'title' => 'Construction and engineering contracts' ],
								[ 'code' => 'CP 232', 'title' => 'Buying for the public sector' ],
								[ 'code' => 'CP 233', 'title' => 'Supply contracts in retail and food' ],
							],
							'title'   => 'Market Focus electives',
						],
					],
					'title'  => 'Practitioner courses (200-series)',
				],
			],
			'courses_steps'                 => [
				[
					'text'  => 'Register with your name and email. It takes two minutes and costs nothing.',
					'title' => 'Create an account',
				],
				[ 'text' => 'Browse the list below or ask us which course fits your role.', 'title' => 'Choose a course' ],
				[
					'text'  => 'Pay by card or ask your employer for an invoice. Members see member prices.',
					'title' => 'Book and pay',
				],
				[ 'text' => 'Your course appears in My Academy straight away, on any device.', 'title' => 'Start learning' ],
			],
			'courses_steps_primary_label'   => 'Register now',
			'courses_steps_primary_url'     => '/login/',
			'courses_steps_secondary_label' => 'Catalogue',
			'courses_steps_secondary_url'   => '#course-list',
			'courses_steps_title'           => 'Become a student, it\'s easy',
			'hero_lead'                     => 'Short, practical courses you can finish around a full-time job.',
			'hero_title'                    => 'Courses',
			'meta_description'              => 'Short courses in contracts, suppliers, procurement and commercial risk. Foundation 100-series and practitioner 200-series, online and in Birmingham.',
		],
		'title' => 'Courses',
	],
	'home'          => [
		'image' => 'hero-home.jpg',
		'meta'  => [
			'hero_button_label'      => 'Explore certification',
			'hero_button_url'        => '/certification/',
			'hero_kicker'            => 'Practical business education',
			'hero_lead'              => 'Courses and certificates for the people who write contracts, choose suppliers and keep the numbers honest. Built around the work you do on Monday morning, not the theory you forget by Friday.',
			'hero_link_label'        => 'Browse courses',
			'hero_link_url'          => '/courses/',
			'hero_title'             => "The practical standard\nin business education.",
			'home_join_button_label' => 'Join now',
			'home_join_button_url'   => '/membership/#join',
			'home_join_link_label'   => 'See membership levels',
			'home_join_link_url'     => '/membership/#levels',
			'home_join_text'         => 'Membership costs £120 a year and includes course discounts, the member library and a record of your learning hours.',
			'home_join_title'        => 'Join Academy',
			'home_why_items'         => [
				[
					'icon'  => 'clipboard-check',
					'text'  => 'You draft, review and decide, the way you would at your desk.',
					'title' => 'Assessed on real tasks',
				],
				[
					'icon'  => 'people',
					'text'  => 'Tutors manage contracts and suppliers for a living, not just in theory.',
					'title' => 'Taught by practitioners',
				],
				[
					'icon'  => 'calendar',
					'text'  => 'Short modules, evening webinars and a mobile app that remembers where you stopped.',
					'title' => 'Study around your week',
				],
			],
			'home_why_kicker'        => 'Why Academy',
			'home_why_text'          => 'Every course is written and marked by practitioners who still do the job. You learn the method, try it on a realistic case, then sit an assessment that tests whether you can apply it. Employers know what an Academy certificate means because the standard is published and the same for everyone.',
			'home_why_title'         => 'Training that stands up at work',
			'meta_description'       => 'Academy trains and certifies people who manage contracts, suppliers and commercial risk. Short courses, recognised certificates and a membership that keeps your skills current.',
			'mission_button_label'   => 'Read about us',
			'mission_button_url'     => '/about/',
			'mission_kicker'         => 'Our mission',
			'mission_text'           => 'Most commercial mistakes are small and avoidable: a missed renewal date, a supplier nobody checked, a clause nobody read. We exist to make the basics reliable. We set a clear standard, teach it in plain language and certify the people who meet it, so organisations across the Midlands and beyond can trust the work done in their name.',
			'mission_title'          => 'Raise the standard of everyday commercial work',
		],
		'title' => 'Home',
	],
	'login'         => [
		'image' => 'hero-login.jpg',
		'meta'  => [
			'form_note'        => 'Demonstration only: no accounts exist.',
			'hero_lead'        => 'Log in to book courses, see your results and download your learning record.',
			'hero_title'       => 'My Academy',
			'meta_description' => 'Log in to My Academy to book courses, see results and download your learning record. Demonstration only.',
			'thank_you_text'   => 'This is a demonstration. No account was checked and nothing was sent.',
		],
		'title' => 'My Academy',
	],
	'membership'    => [
		'image' => 'hero-membership.jpg',
		'meta'  => [
			'hero_button_label'            => 'Join now',
			'hero_button_url'              => '#join',
			'hero_kicker'                  => 'Join Academy',
			'hero_lead'                    => 'Cheaper courses, a template library and a network of peers who work on the same contracts and suppliers you do.',
			'hero_link_label'              => 'See membership levels',
			'hero_link_url'                => '#levels',
			'hero_title'                   => 'Membership',
			'intro_kicker'                 => 'Why join',
			'intro_link_label'             => 'How membership helps you',
			'intro_link_url'               => '#three-ways',
			'intro_text'                   => 'Members get cheaper courses, a library of templates and recorded webinars, and a group of people who deal with the same contracts and suppliers you do. Your learning hours are logged in one place, ready for your next appraisal.',
			'intro_title'                  => 'Membership value',
			'membership_fees'              => [
				[ 'icon' => 'user', 'price' => '£120', 'text' => '', 'title' => 'Standard rate', 'unit' => 'a year' ],
				[
					'icon'  => 'user',
					'price' => '£60',
					'text'  => 'Students, public-sector staff and retirees.',
					'title' => 'Concession rate',
					'unit'  => 'a year',
				],
				[
					'icon'  => 'people',
					'price' => 'By enquiry',
					'text'  => 'Joining as a team of ten or more? Email members@academy.junaid.guru and we will send you a group price within five working days.',
					'title' => 'Groups of ten or more',
					'unit'  => '',
				],
			],
			'membership_fees_intro'        => 'Fees cover 12 months from the day you join. Course and exam fees are separate.',
			'membership_fees_title'        => 'Annual fees',
			'membership_join_button_label' => 'Join now',
			'membership_join_button_url'   => '/contact/#enquiry',
			'membership_join_link_label'   => 'Log in to My Academy',
			'membership_join_link_url'     => '/login/',
			'membership_join_text'         => 'Pay the annual fee online and we will email your login details within one working day. Sign in to My Academy to update your contact details, book courses and download your receipt. If anything goes wrong, Member Services can help at members@academy.junaid.guru.',
			'membership_join_title'        => 'Join now',
			'membership_levels'            => [
				[
					'benefits'  => [
						[ 'text' => 'Member prices on all courses and exams' ],
						[ 'text' => 'The member library: templates, practice notes and webinar recordings' ],
						[ 'text' => 'A learning record that logs your hours automatically' ],
						[ 'text' => 'Invitations to regional networks and the annual conference' ],
					],
					'how_steps' => [],
					'how_text'  => 'Pay the annual fee. No qualifications or experience needed.',
					'intro'     => 'For anyone who buys, sells or manages contracts at work, whatever their job title.',
					'overview'  => 'Your starting point. You get full access to the member library and member prices, and you can start any foundation course.',
					'title'     => 'Associate',
				],
				[
					'benefits'  => [
						[ 'text' => 'Everything Associates get' ],
						[
							'text' => 'Use of the post-nominal letters for each certificate you hold while your membership is current',
						],
						[ 'text' => 'A place in the member directory, if you choose to be listed' ],
						[ 'text' => 'Eligibility to mentor new learners and join standards working groups' ],
					],
					'how_steps' => [],
					'how_text'  => 'Pass any Academy certificate. You become Certified on the day results are published.',
					'intro'     => 'For members who have shown they can apply the standard in a real task.',
					'overview'  => 'Your public member record shows the certificates you hold and the year you passed them.',
					'title'     => 'Certified',
				],
				[
					'benefits'  => [
						[ 'text' => 'Everything Certified members get' ],
						[ 'text' => 'Paid work as an assessor or tutor when places open' ],
						[ 'text' => 'A seat at the annual standards review' ],
						[ 'text' => 'Free entry to the autumn conference' ],
					],
					'how_steps' => [
						[ 'text' => 'Pass the Certified Commercial Practice Professional (CCPP) certificate, and' ],
						[
							'text' => 'Show at least five years of relevant experience in a 30-minute professional review with two Senior members.',
						],
					],
					'how_text'  => '',
					'intro'     => 'For experienced practitioners who lead others and help shape the standard.',
					'overview'  => 'Senior members review course material, assess candidates and sit on our committees.',
					'title'     => 'Senior',
				],
			],
			'membership_levels_intro'      => 'There are three levels. Everyone starts as an Associate. You move up by passing a certificate, not by paying more: the fee is the same at every level.',
			'membership_levels_title'      => 'Membership levels and benefits',
			'membership_links'             => [
				[ 'hidden' => '', 'label' => 'Join now or renew', 'url' => '/membership/#join' ],
				[ 'hidden' => '', 'label' => 'FAQs', 'url' => '/contact/#help' ],
				[ 'hidden' => ' (members only: log in)', 'label' => 'Member directory', 'url' => '/login/' ],
			],
			'membership_ways'              => [
				[
					'icon'  => 'user',
					'text'  => 'Anyone can join as an Associate. You can then move up to Certified and Senior as you pass our certificates and build experience. Each level appears on your public member record, so an employer can check it in a minute. Members also take part in regional networks and forums run by other members.',
					'title' => 'Membership',
				],
				[
					'icon'  => 'certificate',
					'text'  => 'Our five certificates test whether you can do the work: read a contract, assess a supplier, price a risk. Members pay less for every course and exam, get early booking for workshops and can sit most assessments online from home, with an invigilator on camera.',
					'title' => 'Training and certificates',
				],
				[
					'icon'  => 'research',
					'text'  => 'Each year we publish a short survey of how organisations in the Midlands manage contracts and suppliers, plus practice notes and template clauses. Members can read and download all of it, and can ask to join the working groups that write the next edition.',
					'title' => 'Research and guidance',
				],
			],
			'membership_ways_short'        => [
				[
					'figure'        => '',
					'figure_hidden' => '',
					'icon'          => 'share',
					'text'          => 'Swap notes with other members in moderated forums and at monthly regional meet-ups.',
				],
				[
					'figure'        => '',
					'figure_hidden' => '',
					'icon'          => 'screen',
					'text'          => 'Learn at your desk or on the train, with videos, case files and quizzes that save your place.',
				],
				[
					'figure'        => '24/7',
					'figure_hidden' => ' hours a day, every day',
					'icon'          => '',
					'text'          => 'The course library is open 24/7. Book an online exam slot that suits you, including evenings and weekends.',
				],
			],
			'membership_ways_title'        => 'Membership helps you in three ways',
			'meta_description'             => 'Academy membership costs £120 a year, or £60 at the concession rate. Three levels, course discounts, the member library and a record of your learning.',
		],
		'title' => 'Membership',
	],
	'news'          => [
		'image' => '',
		'meta'  => [
			'hero_lead'        => 'Announcements, event news and recordings from Academy. For press enquiries, email press@academy.junaid.guru.',
			'hero_title'       => 'News & media',
			'meta_description' => 'News from Academy: new certificates, webinar recordings, events, fees and member networks.',
		],
		'title' => 'News & media',
	],
	'privacy'       => [
		'content' => $content_privacy,
		'image'   => '',
		'meta'    => [
			'hero_lead'        => 'This notice explains what personal data this demonstration website collects (almost none), and what a real Academy would collect and why.',
			'hero_title'       => 'Privacy notice',
			'meta_description' => 'This notice explains what personal data this demonstration website collects (almost none), and what a real Academy would collect and why.',
		],
		'title'   => 'Privacy',
	],
	'terms'         => [
		'content' => $content_terms,
		'image'   => '',
		'meta'    => [
			'hero_lead'        => 'These terms explain how this demonstration website may be used. Please read them with the demonstration notice below.',
			'hero_title'       => 'Terms and conditions',
			'meta_description' => 'These terms explain how this demonstration website may be used. Please read them with the demonstration notice below.',
		],
		'title'   => 'Terms and conditions',
	],
];

$page_ids      = [];
$created_pages = 0;

foreach ( $pages as $slug => $page ) {
	$args  = [ 'post_content' => $page['content'] ?? '', 'post_title' => $page['title'] ];
	$thumb = $page['image'] ? $img[ $page['image'] ] : 0;

	[ $page_ids[ $slug ], $c ] = academy_seed_post( 'page', $slug, $args, $page['meta'], $thumb );
	$created_pages            += $c;
}

echo "pages: {$created_pages} created, " . count( $page_ids ) . " total\n";

$people = [
	'margaret-ellison'  => [
		'bio'   => 'Margaret spent 25 years running procurement for a regional housing association, where she rewrote its contract templates twice. She chaired the meeting that founded Academy in 2014 and still marks a handful of scripts every exam season to stay close to the work.',
		'group' => 'founding',
		'image' => 'portrait-margaret-ellison.jpg',
		'name'  => 'Margaret Ellison',
		'role'  => 'Chair',
	],
	'amara-okafor'      => [
		'bio'   => 'Amara lectured in commercial law for ten years before moving into practice as an in-house adviser to a manufacturing group. She leads the board\'s work on assessment fairness and wrote the first version of our plain-language drafting guide, now in its fourth edition.',
		'group' => 'founding',
		'image' => 'portrait-amara-okafor.jpg',
		'name'  => 'Dr Amara Okafor',
		'role'  => 'Vice Chair',
	],
	'thomas-reilly'     => [
		'bio'   => 'Thomas is a chartered accountant who spent most of his career as finance director of family-owned engineering businesses in the Black Country. He keeps Academy\'s fees honest, signs off the annual accounts and teaches the working-capital module whenever the timetable allows.',
		'group' => 'founding',
		'image' => 'portrait-thomas-reilly.jpg',
		'name'  => 'Thomas Reilly',
		'role'  => 'Treasurer',
	],
	'hannah-cartwright' => [
		'bio'   => 'Hannah managed supplier performance for a large logistics operator, covering several hundred contracts at a time. She owns the Academy standard, chairs the committee that reviews it each year and insists every learning outcome can be tested with a real task.',
		'group' => 'founding',
		'image' => 'portrait-hannah-cartwright.jpg',
		'name'  => 'Hannah Cartwright',
		'role'  => 'Standards Lead',
	],
	'rahul-mehta'       => [
		'bio'   => 'Rahul ran commercial teams in local government before starting a consultancy that helps councils and charities buy services well. He designs Academy\'s course pathways, recruits our practitioner tutors and still teaches the public-sector procurement elective twice a year.',
		'group' => 'founding',
		'image' => 'portrait-rahul-mehta.jpg',
		'name'  => 'Rahul Mehta',
		'role'  => 'Programmes Lead',
	],
	'jasmine-clarke'    => [
		'bio'   => 'Jasmine trained as a teacher, then spent eight years building staff training for a national retailer. She leads the team that turns practitioner knowledge into short modules, case studies and webinars, and she reads every piece of learner feedback we receive.',
		'group' => 'staff',
		'image' => 'portrait-jasmine-clarke.jpg',
		'name'  => 'Jasmine Clarke',
		'role'  => 'Head of Learning',
	],
	'daniel-hughes'     => [
		'bio'   => 'Daniel ran exams for a professional body in Cardiff before joining Academy in 2019. He manages question writing, online invigilation, marking and appeals for all five certificates, and he publishes a short report on pass rates and changes after every sitting.',
		'group' => 'staff',
		'image' => 'portrait-daniel-hughes.jpg',
		'name'  => 'Daniel Hughes',
		'role'  => 'Head of Certification',
	],
	'fatima-rahman'     => [
		'bio'   => 'Fatima looks after membership, renewals, learning records and the questions that do not fit anywhere else. Before Academy she managed customer service for a Birmingham building society. Her team aims to answer every email within two working days, and usually does.',
		'group' => 'staff',
		'image' => 'portrait-fatima-rahman.jpg',
		'name'  => 'Fatima Rahman',
		'role'  => 'Member Services Manager',
	],
	'owen-griffiths'    => [
		'bio'   => 'Owen builds and runs the systems behind My Academy, the course library and the mobile app. He previously worked as a developer for an online learning company. He cares about pages that load quickly, work with a keyboard and do not lose your progress.',
		'group' => 'staff',
		'image' => 'portrait-owen-griffiths.jpg',
		'name'  => 'Owen Griffiths',
		'role'  => 'Platform Lead',
	],
	'sophie-brennan'    => [
		'bio'   => 'Sophie edits the member newsletter, runs our webinars and answers press enquiries. She spent six years as a business reporter on a Midlands regional paper, which explains her dislike of jargon and her habit of asking tutors what a sentence actually means.',
		'group' => 'staff',
		'image' => 'portrait-sophie-brennan.jpg',
		'name'  => 'Sophie Brennan',
		'role'  => 'Communications Manager',
	],
];

$person_ids     = [];
$created_people = 0;
$order          = 0;

foreach ( $people as $slug => $person ) {
	$order++;
	$meta = [ 'bio' => $person['bio'], 'group' => $person['group'], 'role' => $person['role'] ];

	[ $person_ids[ $slug ], $c ] = academy_seed_post( 'person', $slug, [ 'menu_order' => $order, 'post_title' => $person['name'] ], $meta, $img[ $person['image'] ] );
	$created_people             += $c;
}

echo "people: {$created_people} created, " . count( $person_ids ) . " total\n";

$certificates = [
	'fccp' => [
		'meta'  => [
			'abbreviation'    => 'FCCP',
			'assessment'      => 'Online exam, 90 minutes, 60 questions',
			'benefits'        => 'You will know what you are agreeing to before you sign, and when to ask for help. Passing the FCCP makes you a Certified member and lets you use the letters FCCP while your membership is current.',
			'category'        => 'Commercial practice',
			'course_code'     => 'CP 190-01',
			'description'     => 'The FCCP is studied online through ten short modules, each with a worked example from a fictional manufacturer, Hollin Brook Components, and a quiz. You can sit the exam whenever you feel ready, at home with an online invigilator or at our Birmingham centre. Questions are scenario-based: you read a short email, clause or invoice and choose what to do next.',
			'duration'        => 'About 40 hours of self-paced study, up to six months to finish',
			'experts'         => 'The course was written by Hannah Cartwright, our Standards Lead, with Gareth Lowe, a procurement manager in the further-education sector, and Priya Nandakumar, a commercial solicitor who advises small businesses. Eleanor Whitfield reviewed the compliance module.',
			'level'           => 'Level 1 (Foundation)',
			'outline'         => [
				[ 'text' => 'How contracts are formed, and when a quote or email becomes binding' ],
				[ 'text' => 'Purchase orders, terms and conditions, and the battle of the forms' ],
				[ 'text' => 'Pricing, quotations and basic cost breakdowns' ],
				[ 'text' => 'Choosing a supplier and the checks to run first' ],
				[ 'text' => 'Payment terms, invoices and their effect on cash flow' ],
				[ 'text' => 'Delegated authority: who may sign what' ],
				[ 'text' => 'Keeping records that survive an audit' ],
				[ 'text' => 'An introduction to compliance duties, including bribery and modern slavery' ],
				[ 'text' => 'Handling a late delivery or a disputed invoice' ],
				[ 'text' => 'Ending a contract properly' ],
			],
			'overview_kicker' => 'Entry level',
			'participants'    => 'People in their first two years of a buying, selling, contracts or finance role. Team leaders in operations who sign purchase orders. Founders and office managers of small firms who handle suppliers themselves. Career changers moving into commercial work. There are no prerequisites.',
			'price'           => '£295 plus £35 booking fee',
			'summary'         => 'The FCCP covers the essentials every commercial role needs: how a contract is formed, what a purchase order commits you to, how to check a supplier and how payment terms affect cash. It suits people in their first two years of the work, or anyone moving into it from another field. Study takes about 40 hours and the online exam lasts 90 minutes.',
			'tier'            => 'core',
			'vision'          => 'To give everyone who touches a contract the same reliable foundations, so that the basics are done right the first time and problems are spotted while they are still cheap to fix.',
		],
		'title' => 'Foundation Certificate in Commercial Practice (FCCP)',
	],
	'ccpp' => [
		'meta'  => [
			'abbreviation'    => 'CCPP',
			'assessment'      => 'Written case study (3,000 words) and a 45-minute recorded interview',
			'benefits'        => 'You will leave with a tested plan you can use at work and the confidence to defend it. Passing the CCPP is the main route to Senior membership and lets you use the letters CCPP.',
			'category'        => 'Commercial practice',
			'course_code'     => 'CP 390-01',
			'description'     => 'The CCPP is taught in small cohorts of up to 16 people. Five live workshop days, spread over ten weeks, alternate with guided reading and peer review in groups of four. The written case study asks you to diagnose the commercial weaknesses of an organisation and propose a 12-month improvement plan. In the recorded interview two assessors test your reasoning and ask how you would respond as circumstances change.',
			'duration'        => 'Five taught days over ten weeks, plus about 60 hours of private study',
			'experts'         => 'The CCPP is led by Dr Amara Okafor and Rahul Mehta. Guest sessions come from Kwame Asante, a former commercial director in rail engineering, and Bethan Morgan, who runs internal audit for a group of further-education colleges.',
			'level'           => 'Level 3 (Advanced)',
			'outline'         => [
				[ 'text' => 'Commercial strategy and how it links to the organisation\'s goals' ],
				[ 'text' => 'Category planning and make-or-buy decisions' ],
				[ 'text' => 'Negotiation planning for high-value contracts' ],
				[ 'text' => 'Portfolio-level supplier and contract risk' ],
				[ 'text' => 'Working capital, payment practice and late-payment exposure' ],
				[ 'text' => 'Governance: reporting, controls and the role of the board' ],
				[ 'text' => 'Leading change in a commercial team' ],
				[ 'text' => 'Ethics, conflicts of interest and speaking up' ],
			],
			'overview_kicker' => 'Advanced',
			'participants'    => 'Contract managers, procurement leads, commercial directors and senior finance staff with at least five years of relevant experience. Holding the FCCP or a specialist certificate helps but is not required. You will need access to a real or anonymised contract portfolio to draw on in the case study.',
			'price'           => '£545 plus £35 booking fee',
			'summary'         => 'The CCPP is for practitioners who already run contracts or supplier relationships and now set the approach for a team. It is assessed by a written case study and a recorded interview, so you show your judgement rather than recall facts. Passing it is the main route to Senior membership.',
			'tier'            => 'core',
			'vision'          => 'To recognise practitioners who can set a commercial approach for a team, weigh competing risks and explain their decisions to a board in plain language.',
		],
		'title' => 'Certified Commercial Practice Professional (CCPP)',
	],
	'ccm'  => [
		'meta'  => [
			'abbreviation' => 'CCM',
			'assessment'   => 'Online exam, two hours, with a contract pack to annotate',
			'benefits'     => 'You will be able to set up a contract properly, keep it on track and act quickly when things change. Passing lets you use the letters CCM and counts towards Certified membership.',
			'category'     => 'Contracts',
			'course_code'  => 'CP 290-01',
			'description'  => 'The CCM follows one service contract from signature to exit. In each of the three workshops you work on the same contract pack: a facilities-management agreement with schedules, a change log and six months of performance reports. Between workshops you complete short exercises, such as drafting a variation or a formal notice, and get written feedback from a tutor. The exam uses a fresh contract pack, and you can bring your own notes.',
			'duration'     => 'Three live online workshops over six weeks, plus about 30 hours of study',
			'experts'      => 'Written by Laurence Tse, a contract manager with 20 years in public-sector outsourcing, with Siobhan Doyle, a construction disputes specialist. Daniel Hughes oversees the assessment.',
			'level'        => 'Level 2 (Specialist)',
			'outline'      => [
				[ 'text' => 'Reading a contract for its obligations, dates and remedies' ],
				[ 'text' => 'Building a contract summary and an obligations register' ],
				[ 'text' => 'Mobilisation and the first 90 days' ],
				[ 'text' => 'Measuring performance against service levels' ],
				[ 'text' => 'Change control, variations and pricing changes' ],
				[ 'text' => 'Notices, deadlines and how rights can be lost by delay' ],
				[ 'text' => 'Claims, disputes and routes to settlement' ],
				[ 'text' => 'Renewal, retendering and a clean exit' ],
			],
			'participants' => 'Contract managers and administrators, project managers who run supplier work, and account managers on the selling side who manage obligations to customers. Some experience of live contracts is assumed; the FCCP or equivalent knowledge is recommended.',
			'price'        => '£395 plus £35 booking fee',
			'tier'         => 'specialist',
			'vision'       => 'To make sure that once a contract is signed, someone owns it, knows what it says and makes it deliver what was paid for.',
		],
		'title' => 'Certificate in Contract Management (CCM)',
	],
	'csa'  => [
		'meta'  => [
			'abbreviation' => 'CSA',
			'assessment'   => 'Supplier review portfolio and a 60-minute online exam',
			'benefits'     => 'You will be able to build an assurance process in proportion to the risk and explain it to auditors and customers. Passing lets you use the letters CSA and counts towards Certified membership.',
			'category'     => 'Suppliers and procurement',
			'course_code'  => 'CP 291-01',
			'description'  => 'The CSA is our newest certificate. It covers the full assurance cycle: checks before onboarding, monitoring during the contract and structured reviews when something goes wrong. You work through the case of a mid-sized food distributor with 40 suppliers and a recent near miss. For the portfolio you review one supplier, real or provided by us, and write a short assurance report with recommendations. The exam then tests how you would respond to new information about that supplier.',
			'duration'     => 'Four live online workshops over eight weeks, plus about 30 hours of study. First cohort starts 2 November 2026',
			'experts'      => 'Led by Hannah Cartwright and Marcus Bell, head of supplier quality at a West Midlands food wholesaler, with a session on financial distress from Thomas Reilly.',
			'level'        => 'Level 2 (Specialist)',
			'outline'      => [
				[ 'text' => 'Mapping suppliers by spend, risk and dependence' ],
				[ 'text' => 'Due diligence: financial health, ownership and insurance' ],
				[ 'text' => 'Labour standards, modern slavery and environmental checks' ],
				[ 'text' => 'Onboarding questionnaires that people actually answer' ],
				[ 'text' => 'Monitoring signs of distress, from late deliveries to staff turnover' ],
				[ 'text' => 'Supplier performance reviews and improvement plans' ],
				[ 'text' => 'Business continuity and second sourcing' ],
				[ 'text' => 'When and how to exit a supplier' ],
			],
			'participants' => 'Buyers, supplier relationship managers, quality and compliance staff, and anyone who approves new suppliers. It also suits people in small firms who sell to larger customers and want to understand the checks they will face. The FCCP or equivalent experience is recommended.',
			'price'        => '£395 plus £35 booking fee',
			'tier'         => 'specialist',
			'vision'       => 'To help organisations know who they are buying from, spot weak suppliers early and fix problems together before they become failures.',
		],
		'title' => 'Certificate in Supplier Assurance (CSA)',
	],
	'ccr'  => [
		'meta'  => [
			'abbreviation' => 'CCR',
			'assessment'   => 'Online exam, two hours, including a risk-register exercise',
			'benefits'     => 'You will be able to explain what could go wrong in a deal, what it might cost and who should carry it. Passing lets you use the letters CCR and counts towards Certified membership.',
			'category'     => 'Commercial risk',
			'course_code'  => 'CP 292-01',
			'description'  => 'The CCR is self-paced, with two optional live clinics where you can bring questions to a tutor. Modules follow the life of a deal: bid, negotiation, delivery and close. Each one ends with a short exercise on a sample software-services contract, building up a single risk register you complete during the course. The exam presents a new deal and asks you to identify the main risks, suggest how to allocate them in the contract and estimate their cost.',
			'duration'     => 'Self-paced, about 45 hours, with two optional live clinics',
			'experts'      => 'Written by Priya Nandakumar and Kwame Asante, with worked financial examples prepared by Thomas Reilly. Eleanor Whitfield, a former internal auditor, reviewed the reporting module.',
			'level'        => 'Level 2 (Specialist)',
			'outline'      => [
				[ 'text' => 'Types of commercial risk and where they hide' ],
				[ 'text' => 'Risk registers that people keep up to date' ],
				[ 'text' => 'Liability caps, indemnities and insurance' ],
				[ 'text' => 'Pricing risk: contingency, fixed price and cost-plus' ],
				[ 'text' => 'Payment terms, credit risk and working capital' ],
				[ 'text' => 'Currency and inflation clauses in plain terms' ],
				[ 'text' => 'Supplier failure and concentration risk' ],
				[ 'text' => 'Reporting risk to decision makers' ],
			],
			'participants' => 'Commercial and bid managers, finance business partners, procurement leads and in-house advisers who review deals before signature. Some experience of contracts and basic financial statements is assumed.',
			'price'        => '£425 plus £35 booking fee',
			'tier'         => 'specialist',
			'vision'       => 'To give commercial staff a practical way to find, price and share out the risks in a deal, so that decisions are made knowingly rather than by accident.',
		],
		'title' => 'Certificate in Commercial Risk (CCR)',
	],
];

$certificate_ids      = [];
$created_certificates = 0;
$order                = 0;

foreach ( $certificates as $slug => $certificate ) {
	$order++;
	$args = [ 'menu_order' => $order, 'post_title' => $certificate['title'] ];

	[ $certificate_ids[ $slug ], $c ] = academy_seed_post( 'certificate', $slug, $args, $certificate['meta'], $img['hero-certification.jpg'] );
	$created_certificates            += $c;
}

echo "certificates: {$created_certificates} created, " . count( $certificate_ids ) . " total\n";

$categories         = [ 'media' => 'Media', 'news' => 'News' ];
$created_categories = 0;

foreach ( $categories as $slug => $name ) {
	if ( term_exists( $slug, 'category' ) ) {
		continue;
	}

	wp_insert_term( $name, 'category', [ 'slug' => $slug ] );
	$created_categories++;
}

echo "categories: {$created_categories} created, " . count( $categories ) . " total\n";

$article_supplier_assurance_certificate_launches = <<<'HTML'
<p>When we asked members last spring which skills they most wanted to build, supplier checks came top for the second year running. More than half said they had approved a supplier in the past year without seeing its accounts. One in five had dealt with a supplier failure that nobody saw coming.</p>
<p>The Certificate in Supplier Assurance (CSA) is our answer. It is the first new certificate since 2021 and the fifth in our range, alongside the FCCP, CCPP, CCM and CCR.</p>
<h2>What the certificate covers</h2>
<p>The CSA follows the full assurance cycle. It starts with mapping suppliers by spend, risk and dependence, so that effort goes where it matters. It then covers due diligence on finances, ownership and labour standards, onboarding questionnaires, monitoring for early signs of distress, performance reviews and, when needed, a planned exit.</p>
<p>Learners work through the case of a food distributor with 40 suppliers and a recent near miss, when a packaging supplier stopped trading a week before a seasonal peak. "We wanted a case that felt uncomfortable," says Hannah Cartwright, our Standards Lead, who wrote much of the course. "Nobody did anything outrageous. They just stopped looking."</p>
<h2>How it is assessed</h2>
<p>Candidates prepare a short assurance report on one supplier, either from their own organisation, anonymised, or from a case pack we provide. A 60-minute online exam then tests how they would respond to new information about that supplier. Both parts are marked by the Academy Examinations Board against published criteria.</p>
<h2>Dates and prices</h2>
<p>The first cohort starts on 2 November 2026, with four live online workshops over eight weeks. The CSA costs £395 for members plus the £35 booking fee. Passing it counts towards Certified membership and allows holders to use the letters CSA.</p>
<p>Places in the first cohort are limited to 24. To book, log in to My Academy or contact Member Services.</p>
HTML;

$article_contract_management_webinar_recording = <<<'HTML'
<p>Almost every contract changes after it is signed. Volumes go up, a service is added, a deadline moves. The question is whether the change is agreed properly or simply happens.</p>
<p>That was the theme of our September webinar, which drew 412 live viewers, our largest audience so far. The panel was Laurence Tse, who manages outsourced services for a public body, Siobhan Doyle, a construction disputes specialist, and Jasmine Clarke, our Head of Learning, in the chair.</p>
<h2>Three points worth the hour</h2>
<p>First, write down every change, even the small ones. Laurence described a facilities contract where dozens of minor requests, each agreed by phone, added 11 per cent to the annual cost before anyone noticed. "None of them was wrong. All of them were invisible."</p>
<p>Second, check who has authority before you agree anything. Siobhan said that most of the disputes she sees involve a change agreed by someone who was not allowed to agree it, on either side.</p>
<p>Third, price the change before the work starts. Once work is under way, the supplier has every reason to wait and the customer has very little bargaining power left.</p>
<h2>What is in the library</h2>
<p>Members can now watch the full recording, with captions and a transcript, and download the change-control template the panel used. It is a two-page form with a log sheet, written to be filled in during a phone call rather than after it.</p>
<p>The session draws on module five of the Certificate in Contract Management (CCM), which covers change control and variations in more depth.</p>
<p>Our next webinar, on what to do when a supplier stops replying, runs on 15 October 2026 at 6pm.</p>
HTML;

$article_autumn_conference_registration_opens = <<<'HTML'
<p>Registration is open for the Academy autumn conference, which takes place on Thursday 19 November 2026 at the Foundry Court centre in Birmingham. We expect around 300 members and guests.</p>
<h2>The programme</h2>
<p>The morning opens with a review of what our spring research found about supplier failure. Daniel Hughes and Hannah Cartwright will then explain the changes planned for the Academy standard in 2027 and ask for comments from the floor.</p>
<p>After that the day splits into three tracks:</p>
<ul>
<li><strong>Suppliers</strong>: case studies from two members whose suppliers went into administration, and what they would do differently.</li>
<li><strong>Payment</strong>: a session on late-payment practice, including how to read a supplier's payment history and what small firms can do about slow-paying customers.</li>
<li><strong>Contracts</strong>: a workshop on rewriting a standard clause in plain English, led by Dr Amara Okafor. Bring a clause from your own templates if you can.</li>
</ul>
<p>The afternoon closes with a panel of four employers from the new Midlands Employers Network, who will talk about how they train and promote commercial staff.</p>
<h2>Prices</h2>
<p>Tickets cost £95 for members and £145 for non-members, including lunch. Senior members attend free. Students and members on the concession rate pay £45. We have 20 free places for job seekers, funded by conference sponsors.</p>
<h2>Access</h2>
<p>The venue has step-free access, a hearing loop in the main hall and a quiet room. Live captions will run on all main-hall sessions. If you have other access needs, tell us when you book and we will do our best to meet them.</p>
<p>Book through My Academy before 6 November 2026.</p>
HTML;

$article_membership_fees_held_for_2027 = <<<'HTML'
<p>At its meeting on 20 August the board agreed that membership fees will not rise in 2027. The standard rate stays at £120 a year. The concession rate for students, public-sector staff and retirees stays at £60.</p>
<h2>Why we can hold the fee</h2>
<p>Three things made this possible. Membership grew by around 9 per cent in the past year, to about 2,400. Our move to online invigilation has cut the cost of running each exam sitting by roughly a third. And the new mobile app was built by our own small team rather than an outside agency, so it came in well under the budget we set in 2025.</p>
<p>At the same time our costs have risen. Venue hire, software licences and tutor fees are all higher than two years ago. Holding the fee means the surplus will be smaller in 2027, but we still expect to break even with a modest reserve.</p>
<p>"Most of our members pay for themselves," says Thomas Reilly, Treasurer. "Many work for small firms or in the public sector, where training budgets are the first thing cut. Keeping the fee steady matters more to them than another feature."</p>
<h2>What changes</h2>
<p>Course and certificate prices will rise by between £5 and £15 from 1 January 2027, the first increase since 2024. Members will still pay 20 per cent less than non-members. The £35 booking fee is unchanged.</p>
<p>Group membership for teams of ten or more remains priced by enquiry.</p>
<h2>The accounts</h2>
<p>Our annual accounts for the year to 31 March 2026 are now in the member library, with a one-page summary in plain English. Members can raise questions at the annual meeting on 19 November 2026, held during the autumn conference.</p>
HTML;

$article_learning_app_now_on_mobile = <<<'HTML'
<p>Our learning app is now out of testing and available to every member and student, free of charge, from the usual app stores for Apple and Android phones.</p>
<h2>What it does</h2>
<p>The app gives you every course you have booked, in the same order and with the same quizzes as the website. Your place is saved as you go, so you can start a module on the bus and finish it on your laptop. You can download up to five modules at a time to study without a signal, and your answers sync when you are back online.</p>
<p>You can also watch webinar recordings, read the member library and check your learning hours. Exams are not available in the app, because our online invigilation needs a laptop or desktop computer with a camera.</p>
<h2>Built with members</h2>
<p>More than 200 members tested early versions over the spring. Their feedback changed a lot. We added a text-size control, a dark mode and transcripts for every video. Testers who use screen readers helped us fix the order of controls on quiz screens, which had been confusing.</p>
<p>"The most common request was simply to stop losing progress," says Owen Griffiths, our Platform Lead. "That was the first thing we fixed."</p>
<h2>Getting started</h2>
<p>Search for "Academy Learning" in your app store and sign in with your My Academy username and password. If you have forgotten your password, reset it on the website first.</p>
<p>The app collects only what it needs to save your progress. It does not use advertising trackers. Our privacy notice explains what we hold and why.</p>
<p>Problems or ideas? Email members@academy.junaid.guru with "App" in the subject line.</p>
HTML;

$article_midlands_employers_network_formed = <<<'HTML'
<p>Eighteen employers from across the Midlands have agreed to form the Midlands Employers Network, a group for organisations that train commercial staff with Academy. Members include two councils, a housing association, a hospital trust, several manufacturers and a handful of small firms with fewer than 50 staff.</p>
<h2>Why a network</h2>
<p>The idea came from employers themselves. At last year's conference several said they had the same problems: finding people with commercial skills, keeping them once trained, and knowing what a good career path looks like for someone who manages contracts.</p>
<p>"Everyone was solving it alone," says Rahul Mehta, our Programmes Lead. "A manufacturer in Telford and a council in Coventry turned out to be writing almost the same job description."</p>
<h2>What it will do</h2>
<p>The network has agreed three priorities for its first year:</p>
<ul>
<li>a shared set of job profiles for commercial roles, linked to the Academy standard</li>
<li>a work-placement scheme so that staff can spend a week in another member's commercial team</li>
<li>a short annual report on pay and training for commercial roles across the region</li>
</ul>
<p>The network will meet four times a year, alternating between online and in person. Academy provides the secretariat and meeting space but does not chair it. The first chair, elected by members, runs procurement at a Wolverhampton housing association.</p>
<h2>Joining</h2>
<p>Any organisation that has trained at least three people with Academy in the past two years can join, at no cost. The first meeting takes place on 24 September 2026 at Foundry Court.</p>
<p>Employers interested in joining should contact Rahul Mehta through the enquiry form, choosing "Training for my organisation" as the topic.</p>
HTML;

$posts = [
	'autumn-conference-registration-opens'    => [
		'author'     => 'sophie-brennan',
		'category'   => 'news',
		'content'    => $article_autumn_conference_registration_opens,
		'date'       => '2026-09-09 09:00:00',
		'excerpt'    => 'This year\'s conference runs on 19 November 2026 in Birmingham, with sessions on supplier failure, late payment and plain-language contracts.',
		'image'      => 'news-3.jpg',
		'standfirst' => 'This year\'s conference takes place on 19 November 2026 in Birmingham. Expect short talks, practical workshops and an afternoon on supplier failure, late payment and contracts people can read.',
		'subjects'   => [
			[ 'text' => 'Events' ],
			[ 'text' => 'Conference' ],
			[ 'text' => 'Late payment' ],
			[ 'text' => 'Plain-language contracts' ],
		],
		'title'      => 'Registration opens for the autumn conference',
	],
	'contract-management-webinar-recording'   => [
		'author'     => 'sophie-brennan',
		'category'   => 'media',
		'content'    => $article_contract_management_webinar_recording,
		'date'       => '2026-09-18 09:00:00',
		'excerpt'    => 'The recording of our September webinar is now in the member library, with the change-control template the panel used.',
		'image'      => 'news-2.jpg',
		'standfirst' => 'Our September webinar asked a panel of three contract managers how they handle changes once a contract is live. The recording and the change-control template they used are now in the member library.',
		'subjects'   => [
			[ 'text' => 'Webinars' ],
			[ 'text' => 'Contract management' ],
			[ 'text' => 'Change control' ],
			[ 'text' => 'Member library' ],
		],
		'title'      => 'Watch again: managing contract changes without the drama',
	],
	'learning-app-now-on-mobile'              => [
		'author'     => 'owen-griffiths',
		'category'   => 'media',
		'content'    => $article_learning_app_now_on_mobile,
		'date'       => '2026-08-12 09:00:00',
		'excerpt'    => 'Every Academy course now works in our free mobile app, with offline downloads and progress that follows you between phone and laptop.',
		'image'      => 'news-5.jpg',
		'standfirst' => 'Every Academy course is now available in our mobile app for Apple and Android phones. Progress syncs with My Academy, and you can download modules to study offline.',
		'subjects'   => [
			[ 'text' => 'Learning app' ],
			[ 'text' => 'Online learning' ],
			[ 'text' => 'Accessibility' ],
			[ 'text' => 'My Academy' ],
		],
		'title'      => 'Academy learning app now on mobile',
	],
	'membership-fees-held-for-2027'           => [
		'author'     => 'thomas-reilly',
		'category'   => 'news',
		'content'    => $article_membership_fees_held_for_2027,
		'date'       => '2026-08-27 09:00:00',
		'excerpt'    => 'The standard fee stays at £120 and the concession rate at £60 for all of 2027. Our Treasurer explains the decision.',
		'image'      => 'news-4.jpg',
		'standfirst' => 'The board has agreed to keep the annual membership fee at £120, and the concession rate at £60, for all of 2027. Our Treasurer explains why.',
		'subjects'   => [
			[ 'text' => 'Membership' ],
			[ 'text' => 'Fees' ],
			[ 'text' => 'Annual accounts' ],
			[ 'text' => 'Governance' ],
		],
		'title'      => 'Membership fees held for 2027',
	],
	'midlands-employers-network-formed'       => [
		'author'     => 'rahul-mehta',
		'category'   => 'news',
		'content'    => $article_midlands_employers_network_formed,
		'date'       => '2026-07-29 09:00:00',
		'excerpt'    => 'Eighteen employers that train their staff with Academy have formed a network to share practice on hiring, training and promoting commercial staff.',
		'image'      => 'news-6.jpg',
		'standfirst' => 'Eighteen organisations that train their staff with Academy have formed a network to share practice on hiring, training and promoting commercial staff. It meets for the first time in September.',
		'subjects'   => [
			[ 'text' => 'Regional networks' ],
			[ 'text' => 'Employers' ],
			[ 'text' => 'Careers' ],
			[ 'text' => 'Community' ],
		],
		'title'      => 'Midlands Employers Network formed',
	],
	'supplier-assurance-certificate-launches' => [
		'author'     => 'sophie-brennan',
		'category'   => 'news',
		'content'    => $article_supplier_assurance_certificate_launches,
		'date'       => '2026-09-30 09:00:00',
		'excerpt'    => 'Our fifth certificate covers supplier due diligence, onboarding checks and performance reviews. The first cohort starts on 2 November 2026.',
		'image'      => 'news-1.jpg',
		'standfirst' => 'Our fifth certificate covers the whole supplier assurance cycle, from checks before onboarding to structured reviews when things go wrong. Bookings are open now, and the first cohort starts on 2 November 2026.',
		'subjects'   => [
			[ 'text' => 'Certification' ],
			[ 'text' => 'Supplier assurance' ],
			[ 'text' => 'Procurement' ],
			[ 'text' => 'Due diligence' ],
		],
		'title'      => 'Certificate in Supplier Assurance opens for booking',
	],
];

$created_posts = 0;

foreach ( $posts as $slug => $post ) {
	$args = [
		'post_content' => $post['content'],
		'post_date'    => $post['date'],
		'post_excerpt' => $post['excerpt'],
		'post_title'   => $post['title'],
	];

	$meta = [
		'author_profile' => $person_ids[ $post['author'] ],
		'hero_image'     => $img['hero-news.jpg'],
		'standfirst'     => $post['standfirst'],
		'subjects'       => $post['subjects'],
	];

	[ $id, $c ] = academy_seed_post( 'post', $slug, $args, $meta, $img[ $post['image'] ] );
	wp_set_object_terms( $id, $post['category'], 'category' );
	$created_posts += $c;
}

echo "posts: {$created_posts} created, " . count( $posts ) . " total\n";

$options = [
	'address'                        => "Academy Learning Ltd\nFoundry Court, 41 Lawley Terrace\nBirmingham B3 1AA",
	'article_note'                   => 'Academy and everyone named in this article are fictional, created for this demonstration website.',
	'certificate_award_text'         => 'Pass the assessment to receive your certificate and add its letters after your name.',
	'certificate_awarded_by'         => 'Awarded by Academy Examinations Board',
	'certification_hero_image'       => $img['hero-certification.jpg'],
	'certification_lead'             => 'Five certificates that test what you can do with a contract, a supplier or a risk register.',
	'certification_meta_description' => 'Five Academy certificates in commercial practice, from the entry-level FCCP to the advanced CCPP. Assessed online, priced from £295.',
	'certification_overview'         => "Academy certificates are built on one published standard for commercial practice. Each one tests a defined set of skills with a timed assessment, marked against the same criteria for every candidate. You can study with our courses, on your own or with your employer.\n\nStart with the Foundation Certificate in Commercial Practice if you are new to the work. Take a specialist certificate in contracts, supplier assurance or commercial risk when you need depth in one area. The Certified Commercial Practice Professional certificate is for experienced people who manage others or make the final call.\n\nPrices run from £295 to £545, plus a £35 booking fee. Members pay the listed price; non-members add 20 per cent. Every certificate is awarded by the Academy Examinations Board, our own independent panel.",
	'certification_overview_title'   => 'Overview',
	'certification_specialist_intro' => 'Three shorter certificates for people who need depth in one area.',
	'certification_specialist_title' => 'Specialist certificates',
	'certification_title'            => 'Certification',
	'company_notice'                 => 'Academy Learning Ltd is a fictional company created for this demonstration. Company number 00000000. Registered office: Foundry Court, 41 Lawley Terrace, Birmingham B3 1AA.',
	'copyright_line'                 => '© 2026 Academy Learning Ltd. Registered in England and Wales, company number 00000000. Registered office: Foundry Court, 41 Lawley Terrace, Birmingham B3 1AA.',
	'footer_description'             => 'Academy trains and certifies people in commercial practice: contracts, suppliers, procurement and commercial risk. Based in Birmingham, open to learners everywhere.',
	'footer_notice'                  => 'Academy is a fictional business created for this demonstration website. Courses, prices, people and organisations shown are not real. Please do not send personal data through any form.',
	'header_account_label'           => 'My Academy',
	'header_account_url'             => '/login/',
	'header_join_label'              => 'Join now',
	'header_join_url'                => '/membership/#join',
	'news_block_kicker'              => 'Latest news & media',
	'news_block_link_label'          => 'View all news',
	'news_block_title'               => 'What we have been working on',
	'social'                         => [
		[ 'hidden' => ' (sample link)', 'icon' => 'youtube', 'label' => 'YouTube', 'url' => '#' ],
		[ 'hidden' => ' (sample link)', 'icon' => 'linkedin', 'label' => 'LinkedIn', 'url' => '#' ],
	],
];

foreach ( $options as $name => $value ) {
	update_option( $name, $value );
}

echo 'options: ' . count( $options ) . " set\n";

update_option( 'page_for_posts', $page_ids['news'] );
update_option( 'page_on_front', $page_ids['home'] );
update_option( 'show_on_front', 'page' );
update_option( 'wp_page_for_privacy_policy', $page_ids['privacy'] );

echo "reading: front page and posts page set\n";

$menus = [
	'Primary'       => [
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['about'], 'menu-item-title' => 'About', 'menu-item-type' => 'post_type' ],
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['membership'], 'menu-item-title' => 'Membership', 'menu-item-type' => 'post_type' ],
		[ 'menu-item-object' => 'certificate', 'menu-item-title' => 'Certification', 'menu-item-type' => 'post_type_archive' ],
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['courses'], 'menu-item-title' => 'Courses', 'menu-item-type' => 'post_type' ],
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['community'], 'menu-item-title' => 'Community', 'menu-item-type' => 'post_type' ],
	],
	'About'         => [
		[ 'menu-item-title' => 'Who we are', 'menu-item-type' => 'custom', 'menu-item-url' => '/about/#who' ],
		[ 'menu-item-title' => 'Our mission', 'menu-item-type' => 'custom', 'menu-item-url' => '/about/#mission' ],
		[ 'menu-item-title' => 'Our people', 'menu-item-type' => 'custom', 'menu-item-url' => '/about/#people' ],
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['news'], 'menu-item-title' => 'News', 'menu-item-type' => 'post_type' ],
	],
	'Membership'    => [
		[ 'menu-item-title' => 'Benefits', 'menu-item-type' => 'custom', 'menu-item-url' => '/membership/#value' ],
		[ 'menu-item-title' => 'Levels', 'menu-item-type' => 'custom', 'menu-item-url' => '/membership/#levels' ],
		[ 'menu-item-title' => 'Fees', 'menu-item-type' => 'custom', 'menu-item-url' => '/membership/#fees' ],
		[ 'menu-item-title' => 'Join', 'menu-item-type' => 'custom', 'menu-item-url' => '/membership/#join' ],
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['login'], 'menu-item-title' => 'Manage your credits', 'menu-item-type' => 'post_type' ],
	],
	'Certification' => [
		[ 'menu-item-object' => 'certificate', 'menu-item-title' => 'Certificates', 'menu-item-type' => 'post_type_archive' ],
		[ 'menu-item-object' => 'certificate', 'menu-item-object-id' => $certificate_ids['fccp'], 'menu-item-title' => 'FCCP<span class="visually-hidden">: Foundation Certificate in Commercial Practice</span>', 'menu-item-type' => 'post_type' ],
		[ 'menu-item-object' => 'certificate', 'menu-item-object-id' => $certificate_ids['ccpp'], 'menu-item-title' => 'CCPP<span class="visually-hidden">: Certified Commercial Practice Professional</span>', 'menu-item-type' => 'post_type' ],
	],
	'Courses'       => [
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['courses'], 'menu-item-title' => 'All courses', 'menu-item-type' => 'post_type' ],
	],
	'Community'     => [
		[ 'menu-item-title' => 'Events', 'menu-item-type' => 'custom', 'menu-item-url' => '/community/#events' ],
		[ 'menu-item-title' => 'Research', 'menu-item-type' => 'custom', 'menu-item-url' => '/community/#research' ],
		[ 'menu-item-title' => 'Publications', 'menu-item-type' => 'custom', 'menu-item-url' => '/community/#publications' ],
		[ 'menu-item-title' => 'Sponsorship', 'menu-item-type' => 'custom', 'menu-item-url' => '/community/#sponsorship' ],
		[ 'menu-item-title' => 'Training', 'menu-item-type' => 'custom', 'menu-item-url' => '/community/#training' ],
		[ 'menu-item-title' => 'Webinars', 'menu-item-type' => 'custom', 'menu-item-url' => '/community/#webinars' ],
		[ 'menu-item-title' => 'Forums', 'menu-item-type' => 'custom', 'menu-item-url' => '/community/#forums' ],
		[ 'menu-item-title' => 'Regional networks', 'menu-item-type' => 'custom', 'menu-item-url' => '/community/#regional-networks' ],
	],
	'Legal'         => [
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['careers'], 'menu-item-title' => 'Careers', 'menu-item-type' => 'post_type' ],
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['terms'], 'menu-item-title' => 'Terms and conditions', 'menu-item-type' => 'post_type' ],
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['copyright'], 'menu-item-title' => 'Copyright and trademarks', 'menu-item-type' => 'post_type' ],
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['privacy'], 'menu-item-title' => 'Privacy', 'menu-item-type' => 'post_type' ],
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['accessibility'], 'menu-item-title' => 'Accessibility', 'menu-item-type' => 'post_type' ],
		[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['contact'], 'menu-item-title' => 'Contact', 'menu-item-type' => 'post_type' ],
	],
];

$menu_locations = [
	'footer_about'         => 'About',
	'footer_certification' => 'Certification',
	'footer_community'     => 'Community',
	'footer_courses'       => 'Courses',
	'footer_membership'    => 'Membership',
	'legal'                => 'Legal',
	'primary'              => 'Primary',
];

$created_menu_items = 0;

foreach ( $menus as $name => $items ) {
	$created_menu_items += academy_seed_menu( $name, $items );
}

$locations = get_theme_mod( 'nav_menu_locations', [] );

foreach ( $menu_locations as $location => $name ) {
	$locations[ $location ] = wp_get_nav_menu_object( $name )->term_id;
}

set_theme_mod( 'nav_menu_locations', $locations );

echo "menus: {$created_menu_items} created, " . count( $menus ) . " menus assigned to locations\n";

flush_rewrite_rules( false );

echo "rewrite: rules flushed\n";

$defaults = [ [ 'post', 'hello-world' ], [ 'page', 'sample-page' ], [ 'page', 'privacy-policy' ] ];

foreach ( $defaults as [ $default_type, $default_slug ] ) {
	foreach ( get_posts( [ 'numberposts' => 1, 'post_name__in' => [ $default_slug ], 'post_status' => 'any', 'post_type' => $default_type ] ) as $default_post ) {
		wp_delete_post( $default_post->ID, true );
	}
}

echo "defaults: removed\n";
echo "SEED COMPLETE\n";
