<?php get_header(); ?>
<?php $pub = 0; ?>
	<div id="content" class="narrowcolumn">
	
	<?php
	require_once($_SERVER[DOCUMENT_ROOT].'/init.php');
	require_once($_SERVER[DOCUMENT_ROOT].'/mainfunctions.php');
	require_once($_SERVER[DOCUMENT_ROOT].'/contentfunctions.php');
	echo '<div style="padding:10px; background:#ffffff">'.alaune().'</div>';
	?>

		<?php if (have_posts()) : ?>
		<?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
		<?php /* If this is a category archive */ if (is_category()) { ?>
			<h2 class="pagetitle">Les articles &#8216;<?php single_cat_title(); ?>&#8217;</h2>
		<?php /* If this is a tag archive */ } elseif( is_tag() ) { ?> 
			<h2 class="pagetitle">Les articles taggés avec &#8216;<?php single_tag_title(); ?>&#8217;</h2> 
		<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
			<h2 class="pagetitle">Les articles pour <?php the_time('j F Y'); ?></h2>
		<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
			<h2 class="pagetitle">Les articles pour <?php the_time('F Y'); ?></h2>
		<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
			<h2 class="pagetitle">Les articles pour <?php the_time('Y'); ?></h2>
		<?php /* If this is an author archive */ } elseif (is_author()) { ?>
			<h2 class="pagetitle">Les articles par auteur </h2>
		<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
			<h2 class="pagetitle">Les articles du blog de Prono+</h2>
		<?php } ?>

		<?php while (have_posts()) : the_post(); ?>
		<div class="post">
			<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="Lien permanent vers <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
			<div class="entry">
				<?php $is_sexy = get_post_meta($post->ID, 'sexy', true);
				if($is_sexy=='1')
				{
					?>
					<div align="center">
					<div class="sexy"><a href="<?php the_permalink() ?>"></a></div><br />
					<div><a href="<?php the_permalink() ?>">Contenu pour adultes. Cliquez ici pour afficher l'article.</a></div>
					</div>		
					<?php
				} else {
				?>
				<?php the_content('Lire le reste de cet article &raquo;'); ?>
				<?php
				}
				?>
				<?php /*<p align="center">Lien direct vers cette page :<br /><input type="text" value="<?php the_permalink() ?>" style="width:100%" onFocus="select();" /></p> */ ?>
			</div>
			<p class="postmetadata">Le <?php the_time('l j F Y') ?><br /><?php the_tags('Tags: ', ', ', '<br />'); ?> Publié par <strong><?php the_author(); ?></strong> dans <?php the_category(', ') ?> <?php edit_post_link('[Modifier]', '', ' | '); ?><br />
			<span class="a_comment"><?php comments_popup_link('Soyez le premier à commenter cet article »', '1 commentaire »', '% commentaires »', 'comments-link', 'Les commentaires sont fermés'); ?></span></p>
		</div>
		<?php endwhile; ?>
		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Articles plus anciens') ?></div>
			<div class="alignright"><?php previous_posts_link('Articles plus récents &raquo;') ?></div>
		</div>
	<?php else : ?>
		<h2 class="center">Introuvable</h2>
		<?php include (TEMPLATEPATH . '/searchform.php'); ?>
	<?php endif; ?>
	</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
