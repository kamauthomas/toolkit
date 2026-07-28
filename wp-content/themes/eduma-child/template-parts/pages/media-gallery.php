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
	'2025/08/20250801_154427-scaled.jpg',
	'2025/08/20250801_154911-2048x1536.jpg',
	'2025/08/20250801_154307-1-scaled.jpg',
	'2025/08/20250815_130822-scaled.jpg',
	'2025/08/20250814_091404-1536x865.jpg',
	'2025/08/20250814_091329-scaled.jpg',
);
?>
<main id="main-content" class="toolkit-page toolkit-media-page">
	<section class="toolkit-media-hero"><div><p class="toolkit-kicker">Impact in focus</p><h1><?php echo $videos ? 'Stories in motion' : 'Learning in action'; ?></h1><p><?php echo $videos ? 'Meet learners, trainers and partners shaping practical skills across Africa.' : 'A closer look at the people, workshops and partnerships behind Toolkit Africa.'; ?></p></div></section>
	<section class="toolkit-media-grid toolkit-section" aria-label="<?php echo $videos ? 'Toolkit videos' : 'Toolkit image gallery'; ?>">
		<?php if ( $videos ) : foreach ( $items as $id => $title ) : ?>
			<article class="toolkit-video-card"><div class="toolkit-video-frame"><iframe loading="lazy" src="https://www.youtube-nocookie.com/embed/<?php echo esc_attr( $id ); ?>" title="<?php echo esc_attr( $title ); ?>" allow="accelerometer; autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div><h2><?php echo esc_html( $title ); ?></h2></article>
		<?php endforeach; else : foreach ( $images as $index => $image ) : ?>
			<figure><a href="<?php echo esc_url( $uploads . $image ); ?>"><img loading="lazy" src="<?php echo esc_url( $uploads . $image ); ?>" alt="<?php echo esc_attr( 'Toolkit Africa practical learning and programme activity ' . ( $index + 1 ) ); ?>"></a></figure>
		<?php endforeach; endif; ?>
	</section>
	<?php if ( $videos ) : ?><p class="toolkit-media-more"><a class="toolkit-btn toolkit-btn-primary" href="https://www.youtube.com/@toolkitafrica">Explore more on YouTube</a></p><?php endif; ?>
</main>
<?php get_footer(); ?>
