<?php
	
	global $created_tabs;
	if(!isset($created_tabs)) $created_tabs = 0;
	else $created_tabs++;
	
	$tabid = "themetab".$created_tabs;
	
?>

<div id="<?php echo $tabid; ?>" class="tabbed">
	<ul class="menu horizontal">
		<li><a href="#<?php echo $tabid; ?>clockin" >View Clockins</a></li>
		<li><a href="#<?php echo $tabid; ?>content" >View Readme</a></li>
	</ul>
	<ul class="items stacked">
		<li id="<?php echo $tabid; ?>clockin" class="selected">
		<?php
			get_template_part( "template-parts/charts/daily");
			get_template_part( "template-parts/charts/weekly");
			get_template_part( "template-parts/charts/monthly");
		?>
		</li>
		<li id="<?php echo $tabid; ?>content" ><?php
			the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentyfourteen' ) );
			wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentyfourteen' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
			) );
		?></li>
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