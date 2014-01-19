<?php
class recents_and_updated extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'rnu_custom', // Base ID
			__('Recent Custom', 'text_domain'), // Name
			array( 'description' => __( 'Gets not only Recent posts but updated as well as any searchable post type', 'text_domain' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		// outputs the content of the widget
		$posts = get_posts(array("post_type"=>"any","orderby"=>"modified","order"=>"DESC","posts_per_page"=>5));
		?>
		<aside class="widget">
		<h1 class="widget-title">Recent Posts</h1>
		<ul>
		<?php foreach ( $posts as $post ) { ?>
			<li><a href="<?php echo get_permalink($post->ID);?>"><?php echo $post->post_title; ?></a></li>
		<?php } ?>
		</ul>
		</aside>

		<?php
	}

	public function form( $instance ) {
		// outputs the options form on admin
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
}
?>