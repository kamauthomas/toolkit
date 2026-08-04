<?php
/**
 * First-party enquiries, configurable chatbot content, and website feedback.
 */

function toolkit_support_defaults() {
	return array(
		'enabled'       => 1,
		'greeting'      => 'Hello. I can help with courses, fees, applications, enquiries, or your feedback on the new website.',
		'courses_reply' => 'Explore the current Toolkit courses and choose a practical pathway that matches your goals.',
		'fees_reply'    => 'Fees vary by course and intake. Check the course page, then confirm the current amount with Admissions before payment.',
		'apply_reply'   => 'Use the guided application page to select your course and continue to the application form.',
		'contact_reply' => 'Call +254 709 549 200, WhatsApp +254 711 802 855, or email office@toolkitafrica.ac.ke.',
		'poll_enabled'  => 1,
		'poll_title'    => 'How would you rate the improved Toolkit website?',
		'poll_prompt'   => 'Rate the clarity, design, speed, and ease of finding information.',
	);
}

function toolkit_support_settings() {
	return wp_parse_args( get_option( 'toolkit_support_settings', array() ), toolkit_support_defaults() );
}

add_action( 'init', function() {
	register_post_type( 'toolkit_enquiry', array(
		'labels'       => array( 'name' => 'Enquiries', 'singular_name' => 'Enquiry' ),
		'public'       => false,
		'show_ui'      => false,
		'show_in_rest' => false,
		'supports'     => array( 'title', 'editor' ),
	) );
	register_post_type( 'toolkit_poll', array(
		'labels'       => array( 'name' => 'Poll responses', 'singular_name' => 'Poll response' ),
		'public'       => false,
		'show_ui'      => false,
		'show_in_rest' => false,
		'supports'     => array( 'title', 'editor' ),
	) );
	register_post_type( 'toolkit_speakup', array(
		'labels'       => array( 'name' => 'Speak-up reports', 'singular_name' => 'Speak-up report' ),
		'public'       => false,
		'show_ui'      => false,
		'show_in_rest' => false,
		'supports'     => array( 'title', 'editor' ),
	) );
} );

add_action( 'wp_enqueue_scripts', function() {
	if ( is_admin() || ! eduma_child_redesign_enabled() || ! toolkit_support_settings()['enabled'] ) {
		return;
	}
	$path = get_stylesheet_directory() . '/assets/js/toolkit-support.js';
	wp_enqueue_script( 'toolkit-support', get_stylesheet_directory_uri() . '/assets/js/toolkit-support.js', array(), filemtime( $path ), true );
	wp_localize_script( 'toolkit-support', 'toolkitSupport', array(
		'configEndpoint'  => esc_url_raw( rest_url( 'toolkit/v1/support/config' ) ),
		'enquiryEndpoint' => esc_url_raw( rest_url( 'toolkit/v1/support/enquiry' ) ),
		'pollEndpoint'    => esc_url_raw( rest_url( 'toolkit/v1/support/poll' ) ),
		'config'          => toolkit_support_config()->get_data(),
	) );
}, 1210 );

add_action( 'rest_api_init', function() {
	register_rest_route( 'toolkit/v1', '/support/config', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => 'toolkit_support_config',
	) );
	register_rest_route( 'toolkit/v1', '/support/enquiry', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'toolkit_support_submit_enquiry',
	) );
	register_rest_route( 'toolkit/v1', '/support/poll', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'toolkit_support_submit_poll',
	) );
	register_rest_route( 'toolkit/v1', '/speak-up', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'toolkit_submit_speak_up',
	) );
} );

function toolkit_support_config() {
	$settings = toolkit_support_settings();
	$topics   = array(
		'courses' => array( 'label' => 'Courses', 'reply' => $settings['courses_reply'], 'url' => home_url( '/our-ventures/' ), 'linkLabel' => 'Explore courses' ),
		'fees'    => array( 'label' => 'Fees', 'reply' => $settings['fees_reply'], 'url' => home_url( '/our-ventures/' ), 'linkLabel' => 'View course details' ),
		'apply'   => array( 'label' => 'How to apply', 'reply' => $settings['apply_reply'], 'url' => home_url( '/our-ventures/toolkit-courses-apply-today/' ), 'linkLabel' => 'Start application' ),
		'contact' => array( 'label' => 'Contact Toolkit', 'reply' => $settings['contact_reply'], 'url' => home_url( '/contact/' ), 'linkLabel' => 'Contact Toolkit' ),
	);
	if ( function_exists( 'toolkit_speak_up_enabled' ) && toolkit_speak_up_enabled() ) {
		$topics['speak_up'] = array( 'label' => 'Speak up safely', 'reply' => 'If you need to report a concern, use the dedicated speak-up page. Please do not share sensitive details in this chat.', 'url' => home_url( '/speak-up/' ), 'linkLabel' => 'Open speak-up page' );
	}
	return rest_ensure_response( array(
		'enabled'  => (bool) $settings['enabled'],
		'greeting' => $settings['greeting'],
		'topics'   => $topics,
		'poll'     => array(
			'enabled' => (bool) $settings['poll_enabled'],
			'title'   => $settings['poll_title'],
			'prompt'  => $settings['poll_prompt'],
		),
	) );
}

function toolkit_submit_speak_up( WP_REST_Request $request ) {
	$valid = toolkit_support_validate_request( $request, 2 );
	if ( is_wp_error( $valid ) ) return $valid;
	$category = sanitize_text_field( (string) $request->get_param( 'category' ) );
	$report   = sanitize_textarea_field( (string) $request->get_param( 'report' ) );
	$contact  = rest_sanitize_boolean( $request->get_param( 'contact_me' ) );
	$name     = $contact ? sanitize_text_field( (string) $request->get_param( 'name' ) ) : '';
	$email    = $contact ? sanitize_email( (string) $request->get_param( 'email' ) ) : '';
	$phone    = $contact ? sanitize_text_field( (string) $request->get_param( 'phone' ) ) : '';
	$consent  = rest_sanitize_boolean( $request->get_param( 'consent' ) );
	$allowed  = array( 'safety', 'misconduct', 'fraud', 'harassment', 'other' );
	if ( ! in_array( $category, $allowed, true ) || strlen( $report ) < 30 || strlen( $report ) > 5000 || ! $consent ) {
		return new WP_Error( 'invalid_speak_up', 'Choose a concern type, provide at least 30 characters, and confirm the handling notice.', array( 'status' => 400 ) );
	}
	if ( $contact && ! $email && ! $phone ) return new WP_Error( 'missing_contact', 'Add an email or phone number, or leave follow-up switched off.', array( 'status' => 400 ) );
	if ( $email && ! is_email( $email ) ) return new WP_Error( 'invalid_email', 'Enter a valid email address.', array( 'status' => 400 ) );
	$post_id = wp_insert_post( array( 'post_type' => 'toolkit_speakup', 'post_status' => 'private', 'post_title' => 'Speak-up report: ' . $category, 'post_content' => $report ), true );
	if ( is_wp_error( $post_id ) ) return new WP_Error( 'storage_failed', 'The report could not be saved. Please use the direct contact options on this page.', array( 'status' => 500 ) );
	foreach ( array( '_toolkit_category' => $category, '_toolkit_name' => $name, '_toolkit_email' => $email, '_toolkit_phone' => $phone, '_toolkit_contact_requested' => $contact ? 'yes' : 'no', '_toolkit_status' => 'new', '_toolkit_page' => toolkit_support_clean_path( $request->get_param( 'page' ) ) ) as $key => $value ) update_post_meta( $post_id, $key, $value );
	return new WP_REST_Response( array( 'message' => 'Thank you. Your report has been recorded for restricted review. Keep this reference for your records: SU-' . str_pad( (string) $post_id, 6, '0', STR_PAD_LEFT ) ), 201 );
}

function toolkit_support_validate_request( WP_REST_Request $request, $limit = 8 ) {
	$source = $request->get_header( 'origin' );
	if ( ! $source ) {
		$source = $request->get_header( 'referer' );
	}
	if ( ! $source || wp_parse_url( $source, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		return new WP_Error( 'invalid_origin', 'This request could not be verified.', array( 'status' => 403 ) );
	}
	if ( trim( (string) $request->get_param( 'website' ) ) !== '' ) {
		return new WP_Error( 'invalid_submission', 'This submission could not be accepted.', array( 'status' => 400 ) );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	$key = 'toolkit_support_' . substr( wp_hash( $ip ), 0, 20 );
	$use = (int) get_transient( $key );
	if ( $use >= $limit ) {
		return new WP_Error( 'rate_limited', 'Please wait before sending another response.', array( 'status' => 429 ) );
	}
	set_transient( $key, $use + 1, HOUR_IN_SECONDS );
	return true;
}

function toolkit_support_clean_path( $path ) {
	$path = '/' . ltrim( sanitize_text_field( (string) $path ), '/' );
	$path = strtok( $path, '?' );
	return preg_match( '#^/[A-Za-z0-9/_-]{0,180}$#', $path ) ? $path : '/';
}

function toolkit_support_submit_enquiry( WP_REST_Request $request ) {
	$valid = toolkit_support_validate_request( $request, 5 );
	if ( is_wp_error( $valid ) ) {
		return $valid;
	}
	$name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email   = sanitize_email( (string) $request->get_param( 'email' ) );
	$phone   = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$subject = sanitize_text_field( (string) $request->get_param( 'subject' ) );
	$message = sanitize_textarea_field( (string) $request->get_param( 'message' ) );
	$consent = rest_sanitize_boolean( $request->get_param( 'consent' ) );
	if ( strlen( $name ) < 2 || strlen( $message ) < 10 || strlen( $message ) > 2000 || ( ! $email && ! $phone ) || ! $consent ) {
		return new WP_Error( 'invalid_enquiry', 'Provide your name, enquiry, consent, and either an email address or phone number.', array( 'status' => 400 ) );
	}
	if ( $email && ! is_email( $email ) ) {
		return new WP_Error( 'invalid_email', 'Enter a valid email address.', array( 'status' => 400 ) );
	}
	$post_id = wp_insert_post( array(
		'post_type'    => 'toolkit_enquiry',
		'post_status'  => 'private',
		'post_title'   => $subject ? $subject : 'Website enquiry from ' . $name,
		'post_content' => $message,
	), true );
	if ( is_wp_error( $post_id ) ) {
		return new WP_Error( 'storage_failed', 'The enquiry could not be saved. Please contact Toolkit directly.', array( 'status' => 500 ) );
	}
	$metadata = array(
		'_toolkit_name'   => $name,
		'_toolkit_email'  => $email,
		'_toolkit_phone'  => $phone,
		'_toolkit_page'   => toolkit_support_clean_path( $request->get_param( 'page' ) ),
		'_toolkit_status' => 'new',
	);
	foreach ( $metadata as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}
	wp_schedule_single_event( time(), 'toolkit_support_notify_enquiry', array( $post_id ) );
	return new WP_REST_Response( array( 'message' => 'Thank you. Your enquiry has been received and the Toolkit team can follow up using the details provided.' ), 201 );
}

add_action( 'toolkit_support_notify_enquiry', function( $post_id ) {
	if ( 'toolkit_enquiry' !== get_post_type( $post_id ) ) {
		return;
	}
	$name    = get_post_meta( $post_id, '_toolkit_name', true );
	$email   = get_post_meta( $post_id, '_toolkit_email', true );
	$phone   = get_post_meta( $post_id, '_toolkit_phone', true );
	$message = get_post_field( 'post_content', $post_id );
	wp_mail(
		get_option( 'admin_email' ),
		'New Toolkit website enquiry: ' . get_the_title( $post_id ),
		"Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n\n{$message}\n\nReview: " . admin_url( 'admin.php?page=toolkit-enquiries' )
	);
} );

function toolkit_support_submit_poll( WP_REST_Request $request ) {
	$settings = toolkit_support_settings();
	if ( ! $settings['poll_enabled'] ) {
		return new WP_Error( 'poll_closed', 'This poll is not currently accepting responses.', array( 'status' => 403 ) );
	}
	$valid = toolkit_support_validate_request( $request, 8 );
	if ( is_wp_error( $valid ) ) {
		return $valid;
	}
	$rating  = absint( $request->get_param( 'rating' ) );
	$comment = sanitize_textarea_field( (string) $request->get_param( 'comment' ) );
	$aspects = array_intersect(
		array_map( 'sanitize_key', (array) $request->get_param( 'aspects' ) ),
		array( 'design', 'navigation', 'content', 'speed', 'mobile' )
	);
	if ( $rating < 1 || $rating > 5 || strlen( $comment ) > 1000 ) {
		return new WP_Error( 'invalid_poll', 'Select a rating from 1 to 5.', array( 'status' => 400 ) );
	}
	$post_id = wp_insert_post( array(
		'post_type'    => 'toolkit_poll',
		'post_status'  => 'private',
		'post_title'   => 'Website rating: ' . $rating . '/5',
		'post_content' => $comment,
	), true );
	if ( is_wp_error( $post_id ) ) {
		return new WP_Error( 'storage_failed', 'The response could not be saved.', array( 'status' => 500 ) );
	}
	update_post_meta( $post_id, '_toolkit_rating', $rating );
	update_post_meta( $post_id, '_toolkit_aspects', array_values( $aspects ) );
	update_post_meta( $post_id, '_toolkit_page', toolkit_support_clean_path( $request->get_param( 'page' ) ) );
	return new WP_REST_Response( array( 'message' => 'Thank you. Your rating will help Toolkit improve the website experience.' ), 201 );
}

add_action( 'admin_menu', function() {
	add_submenu_page( 'toolkit-control', 'Enquiries', 'Enquiries', 'manage_options', 'toolkit-enquiries', 'toolkit_support_render_enquiries' );
	add_submenu_page( 'toolkit-control', 'Website poll', 'Website poll', 'manage_options', 'toolkit-poll', 'toolkit_support_render_poll' );
	add_submenu_page( 'toolkit-control', 'Chatbot settings', 'Chatbot settings', 'manage_options', 'toolkit-chatbot', 'toolkit_support_render_settings' );
	add_submenu_page( 'toolkit-control', 'Speak-up reports', 'Speak-up reports', 'manage_options', 'toolkit-speak-up', 'toolkit_support_render_speak_up' );
}, 20 );

function toolkit_support_render_speak_up() {
	toolkit_support_admin_header( 'Speak-up reports', 'Restricted review queue. Do not forward reports outside the authorised safeguarding process.' );
	$items = get_posts( array( 'post_type' => 'toolkit_speakup', 'post_status' => 'private', 'numberposts' => 100, 'orderby' => 'date', 'order' => 'DESC' ) );
	echo '<table class="widefat striped" style="margin-top:18px"><thead><tr><th>Date</th><th>Type</th><th>Report</th><th>Follow-up</th><th>Status</th></tr></thead><tbody>';
	foreach ( $items as $item ) { printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', esc_html( get_the_date( 'Y-m-d H:i', $item ) ), esc_html( get_post_meta( $item->ID, '_toolkit_category', true ) ), esc_html( wp_trim_words( $item->post_content, 24 ) ), esc_html( get_post_meta( $item->ID, '_toolkit_contact_requested', true ) === 'yes' ? 'Requested' : 'Anonymous' ), esc_html( get_post_meta( $item->ID, '_toolkit_status', true ) ?: 'new' ) ); }
	if ( ! $items ) echo '<tr><td colspan="5">No reports have been submitted.</td></tr>';
	echo '</tbody></table></div>';
}

add_action( 'admin_post_toolkit_support_settings', function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to update support settings.' );
	}
	check_admin_referer( 'toolkit_support_settings' );
	$settings = toolkit_support_defaults();
	$settings['enabled']      = isset( $_POST['enabled'] ) ? 1 : 0;
	$settings['poll_enabled'] = isset( $_POST['poll_enabled'] ) ? 1 : 0;
	foreach ( array( 'greeting', 'courses_reply', 'fees_reply', 'apply_reply', 'contact_reply', 'poll_title', 'poll_prompt' ) as $field ) {
		$settings[ $field ] = sanitize_textarea_field( wp_unslash( $_POST[ $field ] ?? '' ) );
	}
	update_option( 'toolkit_support_settings', $settings, false );
	wp_safe_redirect( admin_url( 'admin.php?page=toolkit-chatbot&updated=1' ) );
	exit;
} );

add_action( 'admin_post_toolkit_enquiry_status', function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to update enquiries.' );
	}
	$post_id = absint( $_GET['enquiry'] ?? 0 );
	check_admin_referer( 'toolkit_enquiry_status_' . $post_id );
	if ( 'toolkit_enquiry' === get_post_type( $post_id ) ) {
		$status = sanitize_key( $_GET['status'] ?? '' );
		if ( in_array( $status, array( 'new', 'in_progress', 'resolved' ), true ) ) {
			update_post_meta( $post_id, '_toolkit_status', $status );
		}
	}
	wp_safe_redirect( admin_url( 'admin.php?page=toolkit-enquiries' ) );
	exit;
} );

function toolkit_support_admin_header( $title, $description ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to view this page.' );
	}
	printf( '<div class="wrap toolkit-support-admin"><h1>%s</h1><p>%s</p>', esc_html( $title ), esc_html( $description ) );
	echo '<nav class="nav-tab-wrapper"><a class="nav-tab" href="' . esc_url( admin_url( 'admin.php?page=toolkit-control' ) ) . '">Overview</a><a class="nav-tab" href="' . esc_url( admin_url( 'admin.php?page=toolkit-enquiries' ) ) . '">Enquiries</a><a class="nav-tab" href="' . esc_url( admin_url( 'admin.php?page=toolkit-poll' ) ) . '">Website poll</a><a class="nav-tab" href="' . esc_url( admin_url( 'admin.php?page=toolkit-chatbot' ) ) . '">Chatbot settings</a></nav>';
}

function toolkit_support_render_enquiries() {
	toolkit_support_admin_header( 'Website enquiries', 'Review and progress enquiries submitted through the Toolkit assistant.' );
	$items = get_posts( array( 'post_type' => 'toolkit_enquiry', 'post_status' => 'private', 'numberposts' => 100, 'orderby' => 'date', 'order' => 'DESC' ) );
	echo '<table class="widefat striped" style="margin-top:18px"><thead><tr><th>Date</th><th>Visitor</th><th>Enquiry</th><th>Page</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
	foreach ( $items as $item ) {
		$name   = get_post_meta( $item->ID, '_toolkit_name', true );
		$email  = get_post_meta( $item->ID, '_toolkit_email', true );
		$phone  = get_post_meta( $item->ID, '_toolkit_phone', true );
		$status = get_post_meta( $item->ID, '_toolkit_status', true ) ?: 'new';
		echo '<tr><td>' . esc_html( get_the_date( 'd M Y H:i', $item ) ) . '</td><td><strong>' . esc_html( $name ) . '</strong><br>' . esc_html( $email ) . '<br>' . esc_html( $phone ) . '</td><td><strong>' . esc_html( get_the_title( $item ) ) . '</strong><br>' . esc_html( wp_trim_words( $item->post_content, 30 ) ) . '</td><td><code>' . esc_html( get_post_meta( $item->ID, '_toolkit_page', true ) ) . '</code></td><td>' . esc_html( ucwords( str_replace( '_', ' ', $status ) ) ) . '</td><td>';
		foreach ( array( 'in_progress' => 'Start', 'resolved' => 'Resolve', 'new' => 'Reopen' ) as $next => $label ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=toolkit_enquiry_status&enquiry=' . $item->ID . '&status=' . $next ), 'toolkit_enquiry_status_' . $item->ID );
			echo '<a class="button button-small" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a> ';
		}
		echo '</td></tr>';
	}
	if ( ! $items ) {
		echo '<tr><td colspan="6">No enquiries have been received.</td></tr>';
	}
	echo '</tbody></table></div>';
}

function toolkit_support_render_poll() {
	toolkit_support_admin_header( 'Website improvement poll', 'Monitor ratings and the areas visitors say have improved.' );
	$items   = get_posts( array( 'post_type' => 'toolkit_poll', 'post_status' => 'private', 'numberposts' => 500, 'fields' => 'ids' ) );
	$ratings = array_fill( 1, 5, 0 );
	$aspects = array_fill_keys( array( 'design', 'navigation', 'content', 'speed', 'mobile' ), 0 );
	$total   = 0;
	foreach ( $items as $post_id ) {
		$rating = absint( get_post_meta( $post_id, '_toolkit_rating', true ) );
		if ( isset( $ratings[ $rating ] ) ) {
			$ratings[ $rating ]++;
			$total += $rating;
		}
		foreach ( (array) get_post_meta( $post_id, '_toolkit_aspects', true ) as $aspect ) {
			if ( isset( $aspects[ $aspect ] ) ) $aspects[ $aspect ]++;
		}
	}
	printf( '<div style="display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:16px;margin:18px 0"><div class="card"><h2>Responses</h2><p style="font-size:30px">%s</p></div><div class="card"><h2>Average rating</h2><p style="font-size:30px">%s / 5</p></div><div class="card"><h2>Poll status</h2><p style="font-size:22px">%s</p></div></div>', number_format_i18n( count( $items ) ), esc_html( count( $items ) ? number_format_i18n( $total / count( $items ), 1 ) : '0.0' ), toolkit_support_settings()['poll_enabled'] ? 'Open' : 'Closed' );
	echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px"><table class="widefat striped"><thead><tr><th>Rating</th><th>Responses</th></tr></thead><tbody>';
	foreach ( array_reverse( $ratings, true ) as $rating => $count ) printf( '<tr><td>%s / 5</td><td>%s</td></tr>', esc_html( $rating ), number_format_i18n( $count ) );
	echo '</tbody></table><table class="widefat striped"><thead><tr><th>Improved area</th><th>Mentions</th></tr></thead><tbody>';
	foreach ( $aspects as $aspect => $count ) printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html( ucfirst( $aspect ) ), number_format_i18n( $count ) );
	echo '</tbody></table></div></div>';
}

function toolkit_support_render_settings() {
	toolkit_support_admin_header( 'Chatbot settings', 'Control the public assistant, common answers, and website poll.' );
	$settings = toolkit_support_settings();
	if ( isset( $_GET['updated'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Chatbot settings updated.</p></div>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="max-width:900px;margin-top:18px">';
	echo '<input type="hidden" name="action" value="toolkit_support_settings">';
	wp_nonce_field( 'toolkit_support_settings' );
	echo '<table class="form-table"><tbody>';
	toolkit_support_setting_checkbox( 'enabled', 'Enable website assistant', $settings['enabled'] );
	toolkit_support_setting_textarea( 'greeting', 'Welcome message', $settings['greeting'] );
	toolkit_support_setting_textarea( 'courses_reply', 'Courses answer', $settings['courses_reply'] );
	toolkit_support_setting_textarea( 'fees_reply', 'Fees answer', $settings['fees_reply'] );
	toolkit_support_setting_textarea( 'apply_reply', 'Application answer', $settings['apply_reply'] );
	toolkit_support_setting_textarea( 'contact_reply', 'Contact answer', $settings['contact_reply'] );
	toolkit_support_setting_checkbox( 'poll_enabled', 'Accept poll responses', $settings['poll_enabled'] );
	toolkit_support_setting_textarea( 'poll_title', 'Poll question', $settings['poll_title'] );
	toolkit_support_setting_textarea( 'poll_prompt', 'Poll guidance', $settings['poll_prompt'] );
	echo '</tbody></table><p><button class="button button-primary" type="submit">Save chatbot settings</button></p></form></div>';
}

function toolkit_support_setting_checkbox( $name, $label, $value ) {
	printf( '<tr><th scope="row">%s</th><td><label><input type="checkbox" name="%s" value="1" %s> Enabled</label></td></tr>', esc_html( $label ), esc_attr( $name ), checked( $value, true, false ) );
}

function toolkit_support_setting_textarea( $name, $label, $value ) {
	printf( '<tr><th scope="row"><label for="%s">%s</label></th><td><textarea class="large-text" rows="3" id="%s" name="%s">%s</textarea></td></tr>', esc_attr( $name ), esc_html( $label ), esc_attr( $name ), esc_attr( $name ), esc_textarea( $value ) );
}
