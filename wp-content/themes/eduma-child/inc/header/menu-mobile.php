<?php
if ( function_exists( 'eduma_child_redesign_enabled' ) && ! eduma_child_redesign_enabled() ) {
	include get_template_directory() . '/inc/header/menu-mobile.php';
	return;
}
/**
 * Child-theme override for Eduma's mobile menu.
 *
 * Mirrors the desktop menu guard so a stale primary assignment cannot expose
 * the parent demo menu on mobile.
 */

$menu_args = array(
	'container'  => false,
	'items_wrap' => '%3$s',
);

$expected_menu_labels = array(
	'Home',
	'About Us',
	'Our Courses',
	'Impact and Insights',
	'Toolkit Blog',
	'Notice Board',
	'The Toolkit Foundation',
	'Contact Us',
);

$menu_has_expected_labels = static function( $menu ) use ( $expected_menu_labels ) {
	if ( ! $menu || empty( $menu->term_id ) ) {
		return false;
	}

	$items = wp_get_nav_menu_items( $menu->term_id );
	if ( empty( $items ) || ! is_array( $items ) ) {
		return false;
	}

	$item_labels = array();
	foreach ( $items as $item ) {
		$item_labels[] = strtolower( trim( html_entity_decode( wp_strip_all_tags( $item->title ) ) ) );
	}

	if ( in_array( 'instructor profile', $item_labels, true ) ) {
		return false;
	}

	$matches = 0;
	foreach ( $expected_menu_labels as $label ) {
		if ( in_array( strtolower( $label ), $item_labels, true ) ) {
			$matches++;
		}
	}

	return $matches >= 4;
};

$fallback_menu_names = array(
	'Main Menu',
	'Main menu',
	'Primary Menu',
	'Primary menu',
	'menu-main-menu',
	'main-menu',
);

$primary_menu = false;
$locations    = get_nav_menu_locations();

if ( ! empty( $locations['primary'] ) ) {
	$primary_menu = wp_get_nav_menu_object( $locations['primary'] );
}

$menu_to_render = false;

if ( $menu_has_expected_labels( $primary_menu ) ) {
	$menu_to_render = $primary_menu;
} else {
	foreach ( $fallback_menu_names as $menu_name ) {
		$maybe_menu = wp_get_nav_menu_object( $menu_name );

		if ( $menu_has_expected_labels( $maybe_menu ) ) {
			$menu_to_render = $maybe_menu;
			break;
		}
	}
}
?>
<ul class="nav navbar-nav">
	<?php
	if ( $menu_to_render ) {
		wp_nav_menu(
			array_merge(
				$menu_args,
				array(
					'menu' => $menu_to_render->term_id,
				)
			)
		);
	} else {
		$fallback_items = array(
			array( 'label' => __( 'Home', 'eduma-child' ), 'url' => home_url( '/' ) ),
			array( 'label' => __( 'About Us', 'eduma-child' ), 'url' => home_url( '/about-toolkit-africa/' ) ),
			array( 'label' => __( 'Our Courses', 'eduma-child' ), 'url' => home_url( '/our-ventures/' ) ),
			array( 'label' => __( 'Impact and Insights', 'eduma-child' ), 'url' => home_url( '/the-toolkit-foundation-copy/' ) ),
			array( 'label' => __( 'Toolkit Blog', 'eduma-child' ), 'url' => home_url( '/toolkit-blog/' ) ),
			array( 'label' => __( 'Notice Board', 'eduma-child' ), 'url' => home_url( '/notice-board/' ) ),
			array( 'label' => __( 'The Toolkit Foundation', 'eduma-child' ), 'url' => home_url( '/the-toolkit-foundation-copy/' ) ),
			array( 'label' => __( 'Contact Us', 'eduma-child' ), 'url' => home_url( '/contact/' ) ),
		);

		foreach ( $fallback_items as $item ) {
			printf(
				'<li><a href="%s">%s</a></li>',
				esc_url( $item['url'] ),
				esc_html( $item['label'] )
			);
		}
	}
	?>
</ul>
