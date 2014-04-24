<?php
/** Featured posts grid template
 * @since Alien Ship .59
 */
?>
  <li>
    <div id="innergrid" style="float: none">
    <header>
      <h4 class="grid-header"><a class="entry-title" title="<?php printf( esc_attr__( 'Link to %s', 'alienship' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
    </header>

    <?php if ( has_post_thumbnail() ) { ?>
      <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'alienship' ), the_title_attribute( 'echo=0' ) ); ?>"><?php echo get_the_post_thumbnail( ''. $post->ID .'', array(of_get_option('alienship_featured_posts_image_width'), of_get_option('alienship_featured_posts_image_height')), array('title' => "" )); ?></a>
      <?php } else {
        the_excerpt();
      } ?>
    </div><!-- #innergrid -->
    <div id="grid-footer">
      <p id="grid-footer-meta"><?php if (of_get_option('alienship_published_date',1) ) { alienship_posted_on(); } ?>
      <?php edit_post_link( __( '<span class="edit-link"><i class="icon-pencil pull-right"></i>', 'alienship' ), '', '</span>' ); ?>
      </p>
    </div><!-- #grid-footer -->
  </li>