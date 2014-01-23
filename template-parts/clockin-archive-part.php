<?php

wp_enqueue_script("moment", get_stylesheet_directory_uri() . '/moment/min/moment.min.js', array('jquery'));
wp_enqueue_script("flot", get_stylesheet_directory_uri() . '/flot/jquery.flot.js', array('jquery'));
wp_enqueue_script("flot-cat", get_stylesheet_directory_uri() . '/flot/jquery.flot.categories.js', array('jquery', 'flot'));
wp_enqueue_script("flot-time", get_stylesheet_directory_uri() . '/flot/jquery.flot.time.js', array('jquery', 'flot'));
wp_enqueue_script("flot-stacked", get_stylesheet_directory_uri() . '/flot/jquery.flot.stack.js', array('jquery', 'flot'));


get_template_part( "template-parts/charts/daily");
get_template_part( "template-parts/charts/weekly");
get_template_part( "template-parts/charts/monthly");

?>