<?php

/**
 * Partial Template - Display the featured slider and the blocks
 */

$data_vars = array(
	'data-animation-speed="'. intval(Bunyad::options()->slider_animation_speed) . '"',
	'data-animation="' . esc_attr(Bunyad::options()->slider_animation) . '"',
	'data-slide-delay="' . esc_attr(Bunyad::options()->slider_slide_delay) . '"',
);

$data_vars = implode(' ', $data_vars);

// get latest featured posts
$args = apply_filters(
	'bunyad_block_query_args', 
	array('meta_key' => '_bunyad_featured_post', 'meta_value' => 1, 'order' => 'date', 'posts_per_page' => 8, 'ignore_sticky_posts' => 1),
	'slider'
);

/*
 * Posts grid generated from a category or a tag?
 */
$limit_cat = Bunyad::options()->featured_right_cat;
$limit_tag = Bunyad::options()->featured_right_tag;

if (!empty($limit_cat)) {
	
	$args['posts_per_page'] = 5;
	$grid_query = new WP_Query(apply_filters('bunyad_block_query_args', array('cat' => $limit_cat, 'posts_per_page' => 3), 'slider_grid'));
}
else if (!empty($limit_tag)) {
	
	$args['posts_per_page'] = 5;
	$grid_query = new WP_Query(apply_filters('bunyad_block_query_args', array('tag' => $limit_tag, 'posts_per_page' => 3), 'slider_grid'));
}

/*
 * Category slider?
 */
if (is_category()) {
	$cat = get_query_var('cat');
	$meta = Bunyad::options()->get('cat_meta_' . $cat);
	
	// slider not enabled? quit!
	if (empty($meta['slider'])) {
		return;
	}
		
	$args['cat'] = $cat;
	
	// latest posts?
	if ($meta['slider'] == 'latest') {
		unset($args['meta_key'], $args['meta_value']);
	}
}

/*
 * Main slider posts query
 */

// use latest posts?
if (Bunyad::posts()->meta('featured_slider') == 'default-latest') {
	unset($args['meta_key'], $args['meta_value']);
}

$query = new WP_Query($args);

if (!$query->have_posts()) {
	return;
}

// Use rest of the 3 posts for grid if not post grid is not using 
// any category or tag. Create reference for to main query.
if (empty($grid_query) && $query->found_posts > 5) {
	$grid_query = &$query;
}


$i = $z = 0; // loop counters

?>
	<div class="main-featured">
		<div class="wrap cf">
			<?php $query->the_post(); ?>
			<div class="container homepage-marquee">
				<div class="row marquee-container">
					<div class="col-8">
						<div class="marquee-img-primary">
							<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full', array('class' => 'img-responsive')); ?></a>
						</div>
						<a href="<?php the_permalink(); ?>">
							<h2><?php the_title(); ?> <span style="margin-left:8px; color:#ffd200;">›</span></h2>
						</a>
					</div><!--end col-8-->
					<?php $query->the_post(); ?>
					<div class="col-4 marquee-container-secondary">
						<div class="col-12" style="border-bottom: solid 3px #ffffff;">
							<div class="marquee-img-secondary">
								<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full', array('class' => 'img-responsive')); ?></a>
							</div>
							<a href="<?php the_permalink(); ?>">
								<h2><?php the_title(); ?> <span style="margin-left:4px; color:#ffd200;">›</span></h2>
							</a>
						</div>
						<?php $query->the_post(); ?>
						<div class="col-12">
							<div class="marquee-img-secondary">
								<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full', array('class' => 'img-responsive')); ?></a>
							</div>
							<a href="<?php the_permalink(); ?>">
								<h2><?php the_title(); ?> <span style="margin-left:4px; color:#ffd200;">›</span></h2>
							</a>
						</div>
					</div><!--end col-4-->
				</div><!--end row-->
			</div><!--end container-->

		<?php wp_reset_query(); ?>

		</div> <!--  .wrap  -->
	</div>
