<?php
get_header();
$videos = is_page( 'tti-media' );
$uploads = 'https://toolkitafrica.ac.ke/wp-content/uploads/';
$items = toolkit_youtube_videos();
$first_video = $items ? $items[0] : array( 'id' => '0sjNPAXN8pw', 'title' => 'From virtual reality to real-world welding' );
$images = array(
	array( '2025/08/20250801_154427-scaled.jpg', 'Skills look good on you', 'Workshop energy' ),
	array( '2025/08/20250801_154911-2048x1536.jpg', 'Proof of progress', 'Community wins' ),
	array( '2025/08/20250801_154307-1-scaled.jpg', 'The whole crew showed up', 'People of Toolkit' ),
	array( '2025/08/20250815_130822-scaled.jpg', 'Ideas meet action', 'At the Toolkit' ),
	array( '2025/08/20250814_091404-1536x865.jpg', 'Built different. Built together.', 'Collaboration' ),
	array( '2025/08/20250814_091329-scaled.jpg', 'Learning beyond the classroom', 'Field notes' ),
);
?>
<main id="main-content" class="toolkit-page toolkit-media-page <?php echo $videos ? 'toolkit-video-journey' : 'toolkit-memory-wall'; ?>">
	<?php if ( $videos ) : ?>
		<section class="video-journey-hero">
			<div class="video-journey-hero__copy"><p class="toolkit-kicker">Toolkit stories</p><h1>See skills<br><em>come to life.</em></h1><p>Choose a story, press play and meet the people turning practical knowledge into opportunity.</p></div>
		</section>
		<section class="video-watch-desk" aria-label="Toolkit video gallery">
			<div class="video-watch-player">
				<div class="video-watch-player__frame"><iframe data-video-player src="https://www.youtube-nocookie.com/embed/<?php echo esc_attr( $first_video['id'] ); ?>?rel=0&amp;playsinline=1" title="<?php echo esc_attr( $first_video['title'] ); ?>" allow="accelerometer; autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>
				<div class="video-watch-player__details" aria-live="polite"><span data-video-number>Episode 01</span><h2 data-video-title><?php echo esc_html( $first_video['title'] ); ?></h2><p>Toolkit originals <i aria-hidden="true">•</i> Automatically updated from YouTube</p></div>
			</div>
			<div class="video-watch-playlist">
				<header><div><p class="toolkit-kicker">Watch next</p><h2>Latest from Toolkit</h2><small>Synced from YouTube every 6 hours</small></div><span><b data-video-current>1</b> / <?php echo esc_html( count( $items ) ); ?></span></header>
				<div class="video-watch-playlist__items" role="list">
				<?php $episode = 1; foreach ( $items as $item ) : ?>
					<button type="button" role="listitem" class="video-watch-choice <?php echo 1 === $episode ? 'is-active' : ''; ?>" data-video-id="<?php echo esc_attr( $item['id'] ); ?>" data-video-title="<?php echo esc_attr( $item['title'] ); ?>" data-video-episode="<?php echo esc_attr( $episode ); ?>" aria-pressed="<?php echo 1 === $episode ? 'true' : 'false'; ?>">
						<span class="video-watch-choice__thumb"><img loading="lazy" src="https://i.ytimg.com/vi/<?php echo esc_attr( $item['id'] ); ?>/mqdefault.jpg" alt="<?php echo esc_attr( $item['title'] . ' video thumbnail' ); ?>"><i aria-hidden="true">▶</i></span>
						<span class="video-watch-choice__copy"><small><?php echo esc_html( 'Video ' . str_pad( (string) $episode, 2, '0', STR_PAD_LEFT ) ); ?></small><strong><?php echo esc_html( $item['title'] ); ?></strong></span>
					</button>
				<?php $episode++; endforeach; ?>
				</div>
			</div>
		</section>
		<section class="video-watch-note"><div><strong>Accessible viewing</strong><p>Use the YouTube controls for captions, volume, playback speed and full screen.</p></div><a href="https://www.youtube.com/@toolkitafrica" target="_blank" rel="noopener">View the complete channel <span>↗</span></a></section>
	<?php else : ?>
		<section class="memory-wall-hero">
			<div><p class="toolkit-kicker">Our journey in pictures</p><h1>Moments that<br><em>made the journey.</em></h1><p>Follow the thread through workshops, partnerships, learning and celebration at The Toolkit for Skills and Innovation.</p></div>
			<aside><span>FIELD NOTES</span><strong>Kikuyu, Kenya</strong><small>People • Practice • Progress</small></aside>
		</section>
		<section class="memory-wall" aria-label="Toolkit image gallery">
			<header class="memory-wall__guide"><span>START HERE</span><div></div><p>Every pin marks a step forward.</p></header>
			<div class="memory-wall__path" aria-hidden="true"></div>
			<?php foreach ( $images as $index => $image ) : ?>
				<figure class="memory-card memory-card--<?php echo esc_attr( $index + 1 ); ?>">
					<button type="button" data-gallery-image="<?php echo esc_url( $uploads . $image[0] ); ?>" data-gallery-alt="<?php echo esc_attr( $image[1] ); ?>">
						<i class="memory-pin" aria-hidden="true"></i>
						<img loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>" src="<?php echo esc_url( $uploads . $image[0] ); ?>" alt="<?php echo esc_attr( $image[1] ); ?>">
						<span class="memory-card__zoom" aria-hidden="true">View image ↗</span>
					</button>
					<figcaption><span>STOP <?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><strong><?php echo esc_html( $image[1] ); ?></strong><small><?php echo esc_html( $image[2] ); ?></small></figcaption>
				</figure>
			<?php endforeach; ?>
			<footer class="memory-wall__finish"><span>THE JOURNEY CONTINUES</span><p>New skills. New stories. The same purpose.</p></footer>
		</section>
		<dialog class="memory-lightbox" data-gallery-dialog aria-label="Expanded gallery image"><button type="button" data-gallery-close aria-label="Close image">×</button><img src="" alt=""><p></p></dialog>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
