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
			<div class="video-journey-hero__copy"><p class="toolkit-kicker">Official video library</p><h1>See practical skills<br><em>come to life.</em></h1><p>These stories introduce the people turning practical knowledge into opportunity. Choose a topic and press play.</p></div>
		</section>
		<section class="video-watch-desk" aria-label="Official video gallery">
			<div class="video-watch-player">
				<div class="video-watch-player__frame"><iframe data-video-player src="https://www.youtube-nocookie.com/embed/<?php echo esc_attr( $first_video['id'] ); ?>?rel=0&amp;playsinline=1" title="<?php echo esc_attr( $first_video['title'] ); ?>" allow="accelerometer; autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>
				<div class="video-watch-player__details" aria-live="polite"><span data-video-number>Episode 01</span><h2 data-video-title><?php echo esc_html( $first_video['title'] ); ?></h2><p>Toolkit originals <i aria-hidden="true">•</i> Automatically updated from YouTube</p></div>
			</div>
			<div class="video-watch-playlist">
				<header><div><p class="toolkit-kicker">Watch next</p><h2>Latest from Toolkit</h2><small>Synced from YouTube every 6 hours</small></div><span><b data-video-current>1</b> / <?php echo esc_html( count( $items ) ); ?></span></header>
				<div class="video-watch-playlist__items" role="list">
				<?php $episode = 1; foreach ( $items as $item ) : ?>
					<button type="button" role="listitem" class="video-watch-choice <?php echo 1 === $episode ? 'is-active' : ''; ?>" data-video-id="<?php echo esc_attr( $item['id'] ); ?>" data-video-title="<?php echo esc_attr( $item['title'] ); ?>" data-video-episode="<?php echo esc_attr( $episode ); ?>" aria-pressed="<?php echo 1 === $episode ? 'true' : 'false'; ?>">
						<span class="video-watch-choice__thumb"><img loading="lazy" src="https://i.ytimg.com/vi/<?php echo esc_attr( $item['id'] ); ?>/mqdefault.jpg" alt="<?php echo esc_attr( $item['title'] . ' thumbnail' ); ?>"><i aria-hidden="true">▶</i></span>
						<span class="video-watch-choice__copy"><small><?php echo esc_html( 'Video ' . str_pad( (string) $episode, 2, '0', STR_PAD_LEFT ) ); ?></small><strong><?php echo esc_html( $item['title'] ); ?></strong></span>
					</button>
				<?php $episode++; endforeach; ?>
				</div>
			</div>
		</section>
		<section class="video-watch-note"><div><strong>Accessible viewing</strong><p>Use the YouTube controls for captions, volume, playback speed and full screen.</p></div><a href="https://www.youtube.com/@toolkitafrica" target="_blank" rel="noopener">View the complete channel <span>↗</span></a></section>
		<section class="toolkit-section toolkit-institutional-intro"><div><p class="toolkit-kicker">Learning in motion</p><h2>What you will find in the collection</h2><p>Toolkit videos document practical training, learner experiences, workshop activity, partnerships and pathways towards work or enterprise. The collection helps prospective learners see how technical and employability skills connect in real settings.</p><p>Start with a topic that matches your interests, then explore the relevant <a href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Toolkit course pathway</a>. Titles and thumbnails come from the official Toolkit YouTube channel, and the playlist refreshes automatically as new public releases become available.</p><p>Each feature provides useful context before a learner contacts admissions: workshop demonstrations show applied practice, interviews explain programme purpose, and partnership stories show how training connects with institutions and employers.</p><p>Captions and playback controls are provided by YouTube. If you need current admissions information after watching, use the <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Toolkit contact page</a> or begin a <a href="<?php echo esc_url( home_url( '/apply/' ) ); ?>">course application</a>.</p></div></section>
	<?php else : ?>
		<section class="memory-wall-hero">
			<div><p class="toolkit-kicker">Our journey in pictures</p><h1>Toolkit training gallery</h1><p>Toolkit training gallery images follow workshops, partnerships, practical learning and celebration at The Toolkit for Skills and Innovation.</p></div>
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
		<section class="toolkit-section toolkit-institutional-intro"><div><p class="toolkit-kicker">People, practice and progress</p><h2>Explore skills in pictures</h2><p>Each image records part of the learning journey: people practising technical skills, trainers guiding workshop activity, partners sharing expertise and learners celebrating progress. Together, the photographs show how practical training becomes confidence, competence and opportunity.</p><p>First, use the image controls to open a larger view and follow the collection in sequence. Descriptions identify the moment shown without making claims about qualifications, outcomes or dates that the photograph cannot verify.</p><p>Behind every photograph is a wider story. Workshop images show the importance of safe, guided practice, while group moments reflect the relationships that support learning. Field and partnership photographs provide context for how Toolkit connects training with institutions, employers and communities.</p><p>These images are selected for relevance and clarity. As new institutional photography becomes available, the collection can expand without relying on unrelated stock imagery or repeating the same graduation photographs across different stories.</p><p>Finally, visit the <a href="<?php echo esc_url( home_url( '/toolkit-blog/' ) ); ?>">Toolkit Blog</a> for complete stories, browse <a href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">current courses</a>, watch the <a href="<?php echo esc_url( home_url( '/tti-media/' ) ); ?>">Toolkit video collection</a>, or explore the <a href="https://www.youtube.com/@toolkitafrica" target="_blank" rel="noopener">official Toolkit YouTube channel</a>.</p></div></section>
		<dialog class="memory-lightbox" data-gallery-dialog aria-label="Expanded gallery image"><button type="button" data-gallery-close aria-label="Close image">×</button><img src="" alt=""><p></p></dialog>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
