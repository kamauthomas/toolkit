<?php
/**
 * Child-theme override for Eduma's main menu.
 *
 * The parent theme falls back to the first available menu when the primary
 * location is not assigned. That can surface unrelated menus in the header.
 */

$menu_args = array(
	'container'  => false,
	'items_wrap' => '%3$s',
	'fallback_cb' => false,
);

$fallback_menu_names = array(
	'Main Menu',
	'Main menu',
	'Primary Menu',
	'Primary menu',
	'menu-main-menu',
	'main-menu',
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

$primary_menu = false;
$locations    = get_nav_menu_locations();

if ( ! empty( $locations['primary'] ) ) {
	$primary_menu = wp_get_nav_menu_object( $locations['primary'] );
}

$fallback_menu = false;

foreach ( $fallback_menu_names as $menu_name ) {
	$maybe_menu = wp_get_nav_menu_object( $menu_name );
	if ( $menu_has_expected_labels( $maybe_menu ) ) {
		$fallback_menu = $maybe_menu;
		break;
	}
}

$menu_to_render = false;

if ( $menu_has_expected_labels( $primary_menu ) ) {
	$menu_to_render = $primary_menu;
} elseif ( $fallback_menu ) {
	$menu_to_render = $fallback_menu;
}
?>
<ul class="nav navbar-nav menu-main-menu thim-ekits-menu__nav">
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
			array( 'label' => __( 'Home', 'eduma-child' ), 'url' => home_url( '/' ), 'active' => is_front_page() ),
			array( 'label' => __( 'About Us', 'eduma-child' ), 'url' => home_url( '/about-us/' ) ),
			array( 'label' => __( 'Our Courses', 'eduma-child' ), 'url' => home_url( '/our-ventures/' ) ),
			array( 'label' => __( 'Impact and Insights', 'eduma-child' ), 'url' => home_url( '/impact-and-insights/' ) ),
			array( 'label' => __( 'Toolkit Blog', 'eduma-child' ), 'url' => home_url( '/toolkit-blog/' ) ),
			array( 'label' => __( 'Notice Board', 'eduma-child' ), 'url' => home_url( '/notice-board/' ) ),
			array( 'label' => __( 'The Toolkit Foundation', 'eduma-child' ), 'url' => home_url( '/the-toolkit-foundation-copy/' ) ),
			array( 'label' => __( 'Contact Us', 'eduma-child' ), 'url' => home_url( '/contact/' ) ),
		);

		foreach ( $fallback_items as $item ) {
			$item_classes = ! empty( $item['active'] ) ? ' class="current-menu-item"' : '';
			printf(
				'<li%s><a href="%s"><span class="thim-ekits-menu__nav-link">%s</span></a></li>',
				$item_classes,
				esc_url( $item['url'] ),
				esc_html( $item['label'] )
			);
		}
	}

	if ( get_theme_mod( 'thim_header_style', 'header_v1' ) !== 'header_v4' ) {
		?>
		<li class="menu-search">
			<form class="menu-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="header-menu-search"><?php esc_html_e( 'Search', 'eduma-child' ); ?></label>
				<input id="header-menu-search" class="menu-search__input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search', 'eduma-child' ); ?>">
				<button class="menu-search__button" type="submit" aria-label="<?php esc_attr_e( 'Search', 'eduma-child' ); ?>">
					<i class="fa fa-search" aria-hidden="true"></i>
				</button>
			</form>
		</li>
		<?php

		printf(
			'<li class="menu-right menu-right--fallback"><a class="header-apply-btn" href="%s">%s</a></li>',
			esc_url( home_url( '/our-ventures/toolkit-courses-apply-today/' ) ),
			esc_html__( 'Apply Now', 'eduma-child' )
		);
	}
	?>
</ul>
