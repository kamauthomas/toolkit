<?php
get_header();
$videos = is_page( 'tti-media' );
$uploads = 'https://toolkitafrica.ac.ke/wp-content/uploads/';
$items = array(
	'0sjNPAXN8pw' => 'From virtual reality to real-world welding',
	'ROArAgWDOTI' => 'Transforming the skills sector',
	'ZP7NJxi8XnQ' => 'Future trainers and trainees in action',
	'LJGs1t8T6Bc' => 'Virtual reality changing welding skills',
	'v06Qf-nFozw' => 'Cutting-edge welding technology',
	'6uvRqVpfT4E' => 'The next generation of young MIG welders',
);
$images = array(
	array( '2025/08/20250801_154427-scaled.jpg', 'Skills look good on you', 'Workshop energy' ),
	array( '2025/08/20250801_154911-2048x1536.jpg', 'Proof of progress', 'Community wins' ),
	array( '2025/08/20250801_154307-1-scaled.jpg', 'The whole crew showed up', 'People of Toolkit' ),
	array( '2025/08/20250815_130822-scaled.jpg', 'Ideas meet action', 'Inside the hub' ),
	array( '2025/08/20250814_091404-1536x865.jpg', 'Built different. Built together.', 'Collaboration' ),
	array( '2025/08/20250814_091329-scaled.jpg', 'Learning beyond the classroom', 'Field notes' ),
);
?>
<main id="main-content" class="toolkit-page toolkit-media-page <?php echo $videos ? 'toolkit-watch-room' : 'toolkit-photo-zine'; ?>">
	<?php if ( $videos ) : ?>
		<section class="watch-room-hero">
			<div class="watch-room-hero__signal"><i></i> Toolkit TV <span>Now streaming</span></div>
			<div class="watch-room-hero__copy"><p class="toolkit-kicker">Real people. Real skills. No filler.</p><h1>Press play on <em>possibility.</em></h1><p>Six short stories from the people turning technical skills into momentum.</p></div>
			<div class="watch-room-hero__orb" aria-hidden="true"><span>▶</span></div>
		</section>
		<section class="watch-room-grid" aria-label="Toolkit video stories">
			<?php $episode = 1; foreach ( $items as $id => $title ) : ?>
				<article class="watch-card">
					<div class="watch-card__frame"><iframe loading="lazy" src="https://www.youtube-nocookie.com/embed/<?php echo esc_attr( $id ); ?>?rel=0" title="<?php echo esc_attr( $title ); ?>" allow="accelerometer; autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>
					<div class="watch-card__meta"><span>EP <?php echo esc_html( str_pad( (string) $episode, 2, '0', STR_PAD_LEFT ) ); ?></span><p>Toolkit originals</p></div>
					<h2><?php echo esc_html( $title ); ?></h2>
				</article>
			<?php $episode++; endforeach; ?>
		</section>
		<footer class="watch-room-footer"><p>Still scrolling? We have more stories where these came from.</p><a class="watch-room-button" href="https://www.youtube.com/@toolkitafrica" target="_blank" rel="noopener">Enter the full channel <span>↗</span></a></footer>
	<?php else : ?>
		<section class="photo-zine-hero">
			<div class="photo-zine-hero__issue">ISSUE 01 <span>●</span> KIKUYU, KE</div>
			<p class="photo-zine-hero__eyebrow">A visual diary from Toolkit Africa</p>
			<h1>Good energy.<br><em>Great work.</em></h1>
			<p class="photo-zine-hero__intro">The workshops, wins and wonderfully human moments behind the skills.</p>
			<div class="photo-zine-sticker photo-zine-sticker--one" aria-hidden="true">100%<br>HANDS ON</div>
			<div class="photo-zine-sticker photo-zine-sticker--two" aria-hidden="true">SCROLL<br>THE STORY ↓</div>
		</section>
		<section class="photo-zine-grid" aria-label="Toolkit image gallery">
			<?php foreach ( $images as $index => $image ) : ?>
				<figure class="photo-zine-card photo-zine-card--<?php echo esc_attr( $index + 1 ); ?>">
					<button type="button" data-gallery-image="<?php echo esc_url( $uploads . $image[0] ); ?>" data-gallery-alt="<?php echo esc_attr( $image[1] ); ?>">
						<img loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>" src="<?php echo esc_url( $uploads . $image[0] ); ?>" alt="<?php echo esc_attr( $image[1] ); ?>">
						<span class="photo-zine-card__zoom" aria-hidden="true">↗</span>
					</button>
					<figcaption><span><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?> / <?php echo esc_html( $image[2] ); ?></span><strong><?php echo esc_html( $image[1] ); ?></strong></figcaption>
				</figure>
			<?php endforeach; ?>
		</section>
		<div class="photo-zine-ticker" aria-hidden="true"><div>MAKE • LEARN • BUILD • SHARE • MAKE • LEARN • BUILD • SHARE •</div></div>
		<dialog class="photo-zine-lightbox" data-gallery-dialog aria-label="Expanded gallery image"><button type="button" data-gallery-close aria-label="Close image">×</button><img src="" alt=""><p></p></dialog>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
