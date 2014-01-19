<?php
	
	global $created_tabs;
	if(!isset($created_tabs)) $created_tabs = 0;
	else $created_tabs++;
	
	$tabid = "themetab".$created_tabs;
	
?>

<div id="<?php echo $tabid; ?>" class="tabbed">
	<ul class="menu horizontal">
		<li><a href="#<?php echo $tabid; ?>clockin" >View Clockins</a></li>
		<li><a href="#<?php echo $tabid; ?>content" >View Posts</a></li>
	</ul>
	<ul class="items stacked">
		<li id="<?php echo $tabid; ?>clockin" class="selected">
		<?php
			get_template_part( "template-parts/charts/daily");
			get_template_part( "template-parts/charts/weekly");
			get_template_part( "template-parts/charts/monthly");
		?>
		</li>
		<li id="<?php echo $tabid; ?>content" >
		    <ul>
		<!-- The Loop -->

			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<li>
					<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link: <?php the_title(); ?>">
					<?php the_title(); ?></a>,
					<?php the_time('d M Y'); ?> in <?php the_category('&');?>
				</li>

			<?php endwhile; else: ?>
				<p><?php _e('No posts by this author.'); ?></p>
			<?php endif; ?>
		<!-- End Loop -->
		</ul>
	</li>
	</ul>
	<script type="text/javascript">
		jQuery(function($){
			$("#<?php echo $tabid; ?>>.menu a").click(function(e){
				e.preventDefault();
				$("#<?php echo $tabid; ?>>.menu>li").toggleClass("selected", false);
				$(this).toggleClass("selected", true);
				$("#<?php echo $tabid; ?>>.items>li").toggleClass("selected", false);
				$($(this).attr("href")).toggleClass("selected", true);
			});
		});
	</script>
</div>