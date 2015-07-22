<?php 

/**
 * Singular Content Template
 */

?>

<?php get_header(); ?>

<main class="main wrap cf">
	<div class="row">
		<div class="col-8 main-content">
		
			<?php while (have_posts()) : the_post(); ?>

				<?php 
					
					$panels = get_post_meta(get_the_ID(), 'panels_data', true);
					
					if (!empty($panels) && !empty($panels['grid'])):
						
						get_template_part('content', 'builder');
					
					else:
					
						get_template_part('content', 'single');
						
					endif; 
				?>

				<!-- <div class="comments">
				<?php // comments_template('', true); ?>
				</div> -->
	
			<?php endwhile; // end of the loop. ?>

		</div>
		
		<?php Bunyad::core()->theme_sidebar(); ?>
		
	</div> <!-- .row -->
</main> <!-- .main -->

<?php get_footer(); ?>