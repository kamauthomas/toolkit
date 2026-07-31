<?php
/**
 * Cached YouTube channel-feed integration for the public video gallery.
 */

function toolkit_youtube_channel_id() {
	return defined( 'TOOLKIT_YOUTUBE_CHANNEL_ID' )
		? sanitize_text_field( TOOLKIT_YOUTUBE_CHANNEL_ID )
		: 'UC67SsKsWKQtVrYRsRJHF_SQ';
}

function toolkit_youtube_curated_videos() {
	return array(
		array( 'id' => '0sjNPAXN8pw', 'title' => 'From virtual reality to real-world welding' ),
		array( 'id' => 'ROArAgWDOTI', 'title' => 'Transforming the skills sector' ),
		array( 'id' => 'ZP7NJxi8XnQ', 'title' => 'Future trainers and trainees in action' ),
		array( 'id' => 'LJGs1t8T6Bc', 'title' => 'Virtual reality changing welding skills' ),
		array( 'id' => 'v06Qf-nFozw', 'title' => 'Cutting-edge welding technology' ),
		array( 'id' => '6uvRqVpfT4E', 'title' => 'The next generation of young MIG welders' ),
	);
}

function toolkit_youtube_parse_feed( $xml_body ) {
	if ( ! class_exists( 'DOMDocument' ) || ! is_string( $xml_body ) || '' === trim( $xml_body ) ) {
		return array();
	}
	$document = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$loaded   = $document->loadXML( $xml_body, LIBXML_NONET | LIBXML_NOBLANKS );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );
	if ( ! $loaded ) {
		return array();
	}

	$xpath = new DOMXPath( $document );
	$xpath->registerNamespace( 'atom', 'http://www.w3.org/2005/Atom' );
	$xpath->registerNamespace( 'yt', 'http://www.youtube.com/xml/schemas/2015' );
	$videos = array();
	foreach ( $xpath->query( '//atom:entry' ) as $entry ) {
		$id_node    = $xpath->query( './yt:videoId', $entry )->item( 0 );
		$title_node = $xpath->query( './atom:title', $entry )->item( 0 );
		$date_node  = $xpath->query( './atom:published', $entry )->item( 0 );
		$id         = $id_node ? sanitize_text_field( $id_node->textContent ) : '';
		$title      = $title_node ? sanitize_text_field( $title_node->textContent ) : '';
		if ( ! preg_match( '/^[A-Za-z0-9_-]{11}$/', $id ) || ! $title ) {
			continue;
		}
		$videos[] = array(
			'id'        => $id,
			'title'     => $title,
			'published' => $date_node ? sanitize_text_field( $date_node->textContent ) : '',
		);
	}
	usort(
		$videos,
		static function ( $left, $right ) {
			$left_time  = isset( $left['published'] ) ? strtotime( $left['published'] ) : 0;
			$right_time = isset( $right['published'] ) ? strtotime( $right['published'] ) : 0;
			return $right_time <=> $left_time;
		}
	);
	return $videos;
}

function toolkit_youtube_videos( $limit = 12 ) {
	$limit     = max( 1, min( 15, absint( $limit ) ) );
	$cache_key = 'toolkit_youtube_uploads_v2';
	$feed      = get_transient( $cache_key );

	if ( false === $feed ) {
		$url      = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . rawurlencode( toolkit_youtube_channel_id() );
		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'    => 8,
				'user-agent' => 'ToolkitAfrica-VideoGallery/1.0',
			)
		);
		$feed     = array();
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$feed = toolkit_youtube_parse_feed( wp_remote_retrieve_body( $response ) );
		}
		if ( $feed ) {
			set_transient( $cache_key, $feed, 6 * HOUR_IN_SECONDS );
			update_option( 'toolkit_youtube_uploads_stale', $feed, false );
		} else {
			$feed = get_option( 'toolkit_youtube_uploads_stale', array() );
			set_transient( $cache_key, $feed, 30 * MINUTE_IN_SECONDS );
		}
	}

	$videos = array();
	foreach ( array_merge( is_array( $feed ) ? $feed : array(), toolkit_youtube_curated_videos() ) as $video ) {
		if ( empty( $video['id'] ) || isset( $videos[ $video['id'] ] ) ) {
			continue;
		}
		$videos[ $video['id'] ] = $video;
	}
	return array_slice( array_values( $videos ), 0, $limit );
}
