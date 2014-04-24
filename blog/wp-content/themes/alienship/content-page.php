<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package Alien Ship
 * @since Alien Ship 0.1
 */
?>
<?php alienship_post_before(); ?>
<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php alienship_post_inside_before(); ?>
	<header class="entry-header">
		<h1 class="entry-title"><a class="entry-title" title="<?php printf( esc_attr__( 'Link to %s', 'alienship' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
		<div class="clear">&nbsp;</div>
		<?php wp_link_pages( array( 'before' => '' . __( 'Pages:', 'alienship' ) .'<div class="pagination"><ul>', 'link_before' => '<li>', 'link_after' => '</li>', 'after' => '</ul></div>' ) ); ?>
		<?php edit_post_link( __( 'Edit', 'alienship' ), '<span class="edit-link"><i class="icon-pencil"></i>&nbsp;', '</span>' ); ?>
	  <?php alienship_post_inside_after(); ?>
	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->
<?php alienship_post_after(); ?>