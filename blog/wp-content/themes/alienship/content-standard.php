<?php
/**
 * @package Alien Ship
 * @since Alien Ship 0.1
 */
?>
<?php alienship_post_before(); ?>
<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <?php alienship_post_inside_before(); ?>
  <header class="entry-header">
    <h2 class="entry-title"><a class="entry-title" title="<?php printf( esc_attr__( 'Link to %s', 'alienship' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
  </header><!-- .entry-header -->

  <div class="entry-content">
    <?php alienship_content(); ?>
    <?php wp_link_pages( array( 'before' => '' . __( 'Pages:', 'alienship' ) .'<div class="pagination"><ul>', 'link_before' => '<li>', 'link_after' => '</li>', 'after' => '</ul></div>' ) ); ?>
  </div><!-- .entry-content -->

  <footer class="entry-meta">
  <?php
  if (of_get_option('alienship_published_date',1) ) { alienship_posted_on(); } // show published date?
  if (of_get_option('alienship_post_author',1) ) { alienship_post_author(); } // Show post author?
  if (of_get_option('alienship_post_categories',1) && is_single() || of_get_option('alienship_post_categories_posts_page',1) && !is_single() ) { alienship_post_categories(); } // show post categories?
  if (of_get_option('alienship_post_tags',1) && is_single() || of_get_option('alienship_post_tags_posts_page',1) && !is_single() ) { alienship_post_tags(); } // show post tags?
  if (of_get_option('alienship_post_comments_link',1) ) { // Show comment link? ?>
  <?php if ( comments_open() || ( '0' != get_comments_number() && ! comments_open() ) ) : ?>
  <span class="comments-link"><span class="sep">&nbsp;&nbsp;</span><i class="icon-comment"></i>&nbsp;<?php comments_popup_link( __( 'Leave a comment', 'alienship' ), __( '1 Comment', 'alienship' ), __( '% Comments', 'alienship' ) ); ?>&nbsp;</span>
  <?php endif; ?>
  <?php } // show comment link ?>
  <?php edit_post_link( __( 'Edit', 'alienship' ), '<span class="edit-link">&nbsp;&nbsp;<i class="icon-pencil"></i>&nbsp;', '</span>' ); // display the edit link
  ?>
  </footer><!-- .entry-meta -->
  <?php alienship_post_inside_after(); ?>
</article><!-- #post-<?php the_ID(); ?> -->
<?php alienship_post_after(); ?>