<?php
	date_default_timezone_set('UTC');
	$wpdb->query("SET time_zone = '+0:00'");
	add_action('widgets_init', function(){
		include_once dirname(__FILE__)."/rnu_widget.php";
		register_widget('recents_and_updated');
	});

?>