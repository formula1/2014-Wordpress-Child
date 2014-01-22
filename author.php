<?php
/**
 * The default template for displaying a clockin_project
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen-st-child
 */
wp_enqueue_script("flot", get_stylesheet_directory_uri() . '/flot/jquery.flot.js', array('jquery'));
wp_enqueue_script("flot-cat", get_stylesheet_directory_uri() . '/flot/jquery.flot.categories.js', array('jquery', 'flot'));
wp_enqueue_script("flot-time", get_stylesheet_directory_uri() . '/flot/jquery.flot.time.js', array('jquery', 'flot'));
 
?>

<?php get_header(); ?>
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
<article <?php post_class(); ?>>
    <?php
    $curauth = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
    ?>
	<header class="entry-header">

			<h1><?php echo $curauth->nickname; ?></h1>
		</header>
		<div class="entry-content">
			<?php get_template_part( 'template-parts/tabbed', 'author' ); ?>
		</div>
</article>
		</div>
	</div>
<?php
get_sidebar();
get_footer();
?>
