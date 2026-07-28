<?php
$videos = isset( $_GET['view'] ) && 'videos' === $_GET['view'];
$uploads = 'https://toolkitafrica.ac.ke/wp-content/uploads/';
$images = array(
	array( '2025/08/20250801_154427-scaled.jpg', 'Skills look good on you', 'Workshop energy' ),
	array( '2025/08/20250801_154911-2048x1536.jpg', 'Proof of progress', 'Community wins' ),
	array( '2025/08/20250801_154307-1-scaled.jpg', 'The whole crew showed up', 'People of Toolkit' ),
	array( '2025/08/20250815_130822-scaled.jpg', 'Ideas meet action', 'Inside the hub' ),
	array( '2025/08/20250814_091404-1536x865.jpg', 'Built different. Built together.', 'Collaboration' ),
	array( '2025/08/20250814_091329-scaled.jpg', 'Learning beyond the classroom', 'Field notes' ),
);
$items = array(
	'0sjNPAXN8pw' => 'From virtual reality to real-world welding',
	'ROArAgWDOTI' => 'Transforming the skills sector',
	'ZP7NJxi8XnQ' => 'Future trainers and trainees in action',
	'LJGs1t8T6Bc' => 'Virtual reality changing welding skills',
	'v06Qf-nFozw' => 'Cutting-edge welding technology',
	'6uvRqVpfT4E' => 'The next generation of young MIG welders',
);
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Toolkit Gallery Local Review</title>
	<link rel="stylesheet" href="/wp-content/themes/eduma-child/brand-tokens.css">
	<link rel="stylesheet" href="/wp-content/themes/eduma-child/page-redesign.css">
	<style>
		*{box-sizing:border-box}body{margin:0}.preview-nav{position:relative;z-index:20;display:flex;justify-content:space-between;align-items:center;min-height:78px;padding:10px max(7vw,24px);background:#fff;border-bottom:1px solid #d8dce2}.preview-brand{display:flex;gap:12px;align-items:center;color:#333;font-weight:800}.preview-mark{display:grid;place-items:center;width:47px;height:47px;color:#fff;background:#ff6600;border-radius:50%;font-size:12px}.preview-tabs{display:flex;gap:8px}.preview-tabs a{padding:10px 16px;color:#333;border:1px solid #d8dce2;border-radius:50px;text-decoration:none;font-weight:700}.preview-tabs a.is-active{color:#fff;background:#969e2a;border-color:#969e2a}.preview-note{padding:10px 20px;color:#fff;background:#333;text-align:center;font-size:12px}.toolkit-kicker{font-weight:800;text-transform:uppercase;letter-spacing:.1em}.toolkit-page{padding-top:0!important}@media(max-width:600px){.preview-nav{align-items:flex-start;flex-direction:column;gap:10px}.preview-tabs{width:100%}.preview-tabs a{flex:1;text-align:center}}
	</style>
</head>
<body>
	<div class="preview-note">LOCAL REVIEW ONLY · nothing on demo or production changes from this page</div>
	<nav class="preview-nav"><div class="preview-brand"><span class="preview-mark">TOOLKIT</span>Gallery concept review</div><div class="preview-tabs"><a class="<?php echo $videos ? '' : 'is-active'; ?>" href="/review/gallery-preview/">Image journey</a><a class="<?php echo $videos ? 'is-active' : ''; ?>" href="/review/gallery-preview/?view=videos">Video stories</a></div></nav>
	<?php if ( $videos ) : ?>
	<main class="toolkit-page toolkit-media-page toolkit-video-journey">
		<section class="video-journey-hero"><div class="video-journey-hero__copy"><p class="toolkit-kicker">Toolkit stories</p><h1>See skills<br><em>come to life.</em></h1><p>Choose a story, press play and meet the people turning practical knowledge into opportunity.</p></div></section>
		<section class="video-watch-desk">
			<div class="video-watch-player"><div class="video-watch-player__frame"><iframe data-video-player src="https://www.youtube-nocookie.com/embed/0sjNPAXN8pw?rel=0&amp;playsinline=1&amp;cc_load_policy=1" title="From virtual reality to real-world welding" allowfullscreen></iframe></div><div class="video-watch-player__details" aria-live="polite"><span data-video-number>Episode 01</span><h2 data-video-title>From virtual reality to real-world welding</h2><p>Toolkit originals <i>•</i> Practical skills in action</p></div></div>
			<div class="video-watch-playlist"><header><div><p class="toolkit-kicker">Watch next</p><h2>Stories from the hub</h2></div><span><b data-video-current>1</b> / <?php echo count( $items ); ?></span></header><div class="video-watch-playlist__items" role="list">
				<?php $episode = 1; foreach ( $items as $id => $title ) : ?><button type="button" role="listitem" class="video-watch-choice <?php echo 1 === $episode ? 'is-active' : ''; ?>" data-video-id="<?php echo htmlspecialchars( $id ); ?>" data-video-title="<?php echo htmlspecialchars( $title ); ?>" data-video-episode="<?php echo $episode; ?>" aria-pressed="<?php echo 1 === $episode ? 'true' : 'false'; ?>"><span class="video-watch-choice__thumb"><img loading="lazy" src="https://i.ytimg.com/vi/<?php echo htmlspecialchars( $id ); ?>/mqdefault.jpg" alt=""><i>▶</i></span><span class="video-watch-choice__copy"><small>Episode <?php echo str_pad( (string) $episode, 2, '0', STR_PAD_LEFT ); ?></small><strong><?php echo htmlspecialchars( $title ); ?></strong></span></button><?php $episode++; endforeach; ?>
			</div></div>
		</section>
		<section class="video-watch-note"><div><strong>Accessible viewing</strong><p>Use the YouTube controls for captions, volume, playback speed and full screen.</p></div><a href="#">View the complete channel <span>↗</span></a></section>
	</main>
	<script src="/wp-content/themes/eduma-child/page-redesign.js"></script>
	<?php else : ?>
	<main class="toolkit-page toolkit-media-page toolkit-memory-wall">
		<section class="memory-wall-hero"><div><p class="toolkit-kicker">Our journey in pictures</p><h1>Moments that<br><em>made the journey.</em></h1><p>Follow the thread through workshops, partnerships, learning and celebration at Toolkit Africa.</p></div><aside><span>FIELD NOTES</span><strong>Kikuyu, Kenya</strong><small>People • Practice • Progress</small></aside></section>
		<section class="memory-wall"><header class="memory-wall__guide"><span>START HERE</span><div></div><p>Every pin marks a step forward.</p></header><div class="memory-wall__path"></div>
			<?php foreach ( $images as $index => $image ) : ?><figure class="memory-card memory-card--<?php echo $index + 1; ?>"><button type="button" data-gallery-image="<?php echo $uploads . $image[0]; ?>" data-gallery-alt="<?php echo htmlspecialchars( $image[1] ); ?>"><i class="memory-pin"></i><img src="<?php echo $uploads . $image[0]; ?>" alt="<?php echo htmlspecialchars( $image[1] ); ?>"><span class="memory-card__zoom">View image ↗</span></button><figcaption><span>STOP <?php echo str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ); ?></span><strong><?php echo htmlspecialchars( $image[1] ); ?></strong><small><?php echo htmlspecialchars( $image[2] ); ?></small></figcaption></figure><?php endforeach; ?>
			<footer class="memory-wall__finish"><span>THE JOURNEY CONTINUES</span><p>New skills. New stories. The same purpose.</p></footer>
		</section>
		<dialog class="memory-lightbox" data-gallery-dialog><button type="button" data-gallery-close>×</button><img src="" alt=""><p></p></dialog>
	</main>
	<script src="/wp-content/themes/eduma-child/page-redesign.js"></script>
	<?php endif; ?>
</body>
</html>
