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
		<?php while (have_posts()) : the_post(); ?>
			<div class="post" id="post-<?php the_ID(); ?>">
				<h2><a name="post_<?php the_ID(); ?>" href="<?php the_permalink() ?>" rel="bookmark" title="Lien permanent vers <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
				<div class="entry">

<?php /* if($pub < 1) { $pub++; ?>

<script type="text/javascript"><!--
google_ad_client = "pub-4614826582647836";
// Rectangle Prono+ 336x280, date de création 03/01/09
google_ad_slot = "6830984119";
google_ad_width = 336;
google_ad_height = 280;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

<?php } else if($pub < 3) { $pub++; ?>

<script type="text/javascript"><!--
google_ad_client = "pub-4614826582647836";
// LigneProno+ 468x60, date de création 03/01/09
google_ad_slot = "3878222938";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

<?php }*/ ?>
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
					<?php /*<p align="center">Lien direct vers cette page :<br /><input type="text" value="<?php the_permalink() ?>" style="width:100%" onFocus="select();" /></p>*/ ?>
				</div>
				
				
<?php if($pub < 3) { $pub++; ?>
	<script type="text/javascript"><!--
	google_ad_client = "pub-4614826582647836";
	// LigneProno+ 468x60, date de création 03/01/09 
	google_ad_slot = "3878222938";
	google_ad_width = 468;
	google_ad_height = 60;
	//-->
	</script>
	<script type="text/javascript"
	src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
	</script>
<?php } ?>
				
				<p class="postmetadata">Le <?php the_time('j F Y') ?><br /><?php the_tags('Tags: ', ', ', '<br />'); ?> Publié par <strong><?php the_author(); ?></strong> dans <?php the_category(', ') ?> <?php edit_post_link('[Modifier]', '', ''); ?><br />
				<span class="a_comment"><?php comments_popup_link('Soyez le premier à commenter cet article »', '1 commentaire »', '% commentaires »', 'comments-link', 'Les commentaires sont fermés'); ?></span></p>
			</div>
		<?php endwhile; ?>
		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Articles plus anciens') ?>
			
			
<?php
if(!$max_page) $max_page = $wp_query->max_num_pages;
if ( !$paged ) $paged = 1;
$nextpage = intval($paged) + 1;
if ( (! is_single()) && (empty($paged) || $nextpage <= $max_page) )
{
	$my_query = new WP_Query('paged='.$nextpage);
	if (have_posts()) : ?>
<div style="text-align:left; padding:10px; font-size:12px; font-weight:normal">
Vous trouverez parmi les articles plus anciens :
<ul>
<?php while ($my_query->have_posts()) : $my_query->the_post(); ?>
<li><a href="<?php echo the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></li>
<?php endwhile; ?>
</ul>
</div>
<?php endif;
}
?>
			</div>
			<div class="alignright"><?php previous_posts_link('Articles plus récents &raquo;') ?></div>
		</div>
		
	<?php else : ?>
		<h2 class="center">Introuvable</h2>
		<p class="center">Désolé, mais vous cherchez quelque chose qui ne se trouve pas ici .</p>
		<?php include (TEMPLATEPATH . "/searchform.php"); ?>
	<?php endif; ?>
	</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>