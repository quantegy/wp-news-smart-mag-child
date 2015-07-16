
	<?php do_action('bunyad_sidebar_start'); ?>		
		
		<aside class="col-4 sidebar">
			<ul>
			<?php echo ucinews_filter_comments_recent('primary-sidebar'); ?>
			<?php //if (!dynamic_sidebar('primary-sidebar')) : ?>
				<?php //_e("<li>Nothing yet.</li>", 'bunyad'); ?>
			<?php //endif; ?>
	
			</ul>
		</aside>
		
	<?php do_action('bunyad_sidebar_end'); ?>