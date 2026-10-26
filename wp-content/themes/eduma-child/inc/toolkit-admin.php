<?php
/** Shared presentation only; operational permissions remain with each screen. */
function toolkit_admin_header( $title, $description ) {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You do not have permission to view Toolkit controls.' );
	$page = isset( $_GET['page'] ) && is_string( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'toolkit-control';
	$links = array(
		'toolkit-control' => 'Overview', 'toolkit-applications' => 'Applications',
		'toolkit-calling-letters' => 'Calling letters', 'toolkit-enquiries' => 'Enquiries',
		'toolkit-analytics' => 'Analytics', 'toolkit-graduands' => 'Graduands & QR',
		'toolkit-speak-up' => 'Speak-up', 'toolkit-poll' => 'Poll',
		'toolkit-admissions-settings' => 'Admissions settings', 'toolkit-chatbot' => 'Assistant settings',
	);
	echo '<header class="toolkit-admin__hero toolkit-admin__workspace-header"><div class="toolkit-admin__identity"><img src="' . esc_url( get_stylesheet_directory_uri() . '/assets/images/toolkit-logo.png' ) . '" alt="Toolkit" width="76" height="76"><div><p>Toolkit Control <span aria-hidden="true">/</span> Website workspace</p><h1>' . esc_html( $title ) . '</h1><span>' . esc_html( $description ) . '</span></div></div><div class="toolkit-admin__header-actions"><span class="toolkit-admin__environment">' . esc_html( toolkit_is_demo_environment() ? 'Demo environment' : 'Production' ) . '</span><a href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener noreferrer">Open website <span aria-hidden="true">↗</span></a></div></header>';
	echo '<nav class="toolkit-admin__quick toolkit-admin__workspace-nav" aria-label="Toolkit workspace">';
	foreach ( $links as $slug => $label ) {
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=' . $slug ) ) . '"' . ( $page === $slug ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>';
	}
	echo '</nav>';
}
