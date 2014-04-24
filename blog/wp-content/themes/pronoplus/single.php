<?php get_header(); ?>
	<div id="content" class="narrowcolumn">
	
	
<?
require_once($_SERVER[DOCUMENT_ROOT].'/init.php');
require_once($_SERVER[DOCUMENT_ROOT].'/mainfunctions.php');
require_once($_SERVER[DOCUMENT_ROOT].'/contentfunctions.php');
echo '<div style="padding:10px; background:#ffffff">'.alaune().'</div>';
?>
	
	



	
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
  <?php $is_sexy = get_post_meta($post->ID, 'sexy', true);
		/*if($is_sexy=='1')
		{
			?>
  <script language="javascript" src="<?php bloginfo('url'); ?>/sexy.js"></script>
  <?php
  		}*/
	?>
		<div class="post" id="post-<?php the_ID(); ?>">
		
		
		
<?
/*	
<div align="center">
<script language="JavaScript" type="text/javascript" src="/lib/flash/flash.js"></script>
<script language="JavaScript" type="text/javascript">
	AC_FL_RunContent(
		'codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0',
		'width', '452',
		'height', '330',
		'src', '/anim_cadeau_3',
		'quality', 'high',
		'pluginspage', 'http://www.adobe.com/go/getflashplayer_fr',
		'align', 'middle',
		'play', 'true',
		'loop', 'true',
		'scale', 'showall',
		'wmode', 'window',
		'devicefont', 'false',
		'id', 'anim_cadeau_bandeau',
		'bgcolor', '#ffffff',
		'name', 'anim_cadeau_bandeau',
		'menu', 'true',
		'allowFullScreen', 'false',
		'allowScriptAccess','sameDomain',
		'movie', '/anim_cadeau_3',
		'salign', ''
		); //end AC code
</script>
<noscript>
	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="452" height="330" id="anim_cadeau_bandeau" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="movie" value="/anim_cadeau_3.swf" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" />	<embed src="/anim_cadeau_3.swf" quality="high" bgcolor="#ffffff" width="452" height="330" name="anim_cadeau_bandeau" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflashplayer_fr" />
	</object>
</noscript>
</div>
<br />
*/
?>


		
			<h2><a href="<?php echo get_permalink() ?>" rel="bookmark" title="Lien permanent vers <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
			<div class="entry">
<? /*
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
*/ ?>
				<?php the_content('<p class="serif">Lire la suite de l\'article &raquo;</p>'); ?>
				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>				
				<?php the_tags( '<p>Tags: ', ', ', '</p>'); ?> 
			
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

				<p class="postmetadata">
						Cet article  a été publié par <strong><?php the_author(); ?></strong>
						le <?php the_time('l j F Y') ?>
						et est classé dans <?php the_category(', ') ?>.
						Vous pouvez en suivre les commentaires par le biais du flux  
						<?php comments_rss_link('RSS 2.0'); ?>.			
				</p>
			</div>
		</div>
	<div class="navigation"><?php comments_template(); ?></div>
	<br /><br /><br /><br />
	<div class="navigation">
		<div class="alignleft"><?php previous_post_link('&laquo; %link') ?></div>
		<div class="alignright"><?php next_post_link('%link &raquo;') ?></div>
	</div>
	

	
	<?php endwhile; else: ?>
		<p>Désolé, aucun article ne correspond à vos critères.</p>
    <?php endif; ?>
	</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>