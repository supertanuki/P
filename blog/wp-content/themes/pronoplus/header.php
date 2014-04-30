<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; <?php } ?> <?php wp_title(); ?></title>
	<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	
	<script type="text/javascript" src="/lib/scriptaculous-js-1.8.1/prototype.js"></script>
	<script type="text/javascript" src="/lib/scriptaculous-js-1.8.1/scriptaculous.js"></script>
	

	<?php wp_head(); ?>	
<!-- hack flash in IE -->
<script language="javascript" src="<?php bloginfo('url'); ?>/backtothehtml.js"></script>
<script language="javascript">
<!--
if (parent.frames.length > 0) window.top.location.href = location.href;
-->
</script>
<!-- hack transparant PNG -->
<!--[if lte IE 6]>
<script type="text/javascript" src="/supersleight-min.js"></script>
<![endif]-->
</head>

<body>

<?php
$strUser = $_COOKIE[user];
if($strUser)
{
	$footprint = "CONCAT(`id_user`, 'a', MD5(CONCAT(`login`, 'a', MD5(`pwd`))))";
	
	$cond = $footprint . "='".mysql_real_escape_string($strUser)."'";
		
	$SQL = "SELECT `id_user`, `login`, `avatar_key`, `avatar_ext`
			FROM `pp_user`
			WHERE ".$cond;
	$result = mysql_query($SQL);
	//echo "<li>$strUser<li>$SQL";
	if($joueur = mysql_fetch_assoc($result))
	{
		$joueur_avatar = $joueur["avatar_key"] ? '/avatars/'.$joueur["id_user"].'-'.$joueur["avatar_key"].'-30.'.$joueur["avatar_ext"] : '/template/default/_profil.png';
		$joueur_nom = "<li class=\"header_link_green\"><div style=\"display:block; width:150px; overflow:hidden; text-align:left; color:#fff\"><div style=\"float:left;\"><img src=\"".$joueur_avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" style=\"border-right:solid 1px #fff\" /></div><div style=\"float:left; padding:9px 5px 6px 5px; font-weight:bold; width:105px; overflow:hidden;\">".$joueur["login"]."&nbsp;<a href=\"/logout.php\" class=\"logout_link\" title=\"Se d�connecter\" onclick=\"return confirm('Souhaitez-vous vous d�connecter ?');\"><img src=\"/template/default/close.gif\" height=\"12\" width=\"12\" border=\"0\" align=\"absmiddle\" /></a></div><div class=\"clear\"></div></li>";
		$CookieBon = true;
	}
}
?>

<div id="page">

	<div id="header_links">		
		<ul>
			
			<?php
			if($CookieBon == true)
			{
				echo $joueur_nom;
			}
			?>
			<li class="header_link_orange"><a href="/">Pronostics</a></li>
			<li class="header_link_blue"><a href="/forum-football/">Forum</a></li>
		</ul>
		<div style="display:block; width:260px; height:14px; line-height:11px; overflow:hidden; color:#666666; font-size:12px; font-weight:bold; margin:0; padding:2px 5px 10px;">
		<?php if (is_single() && have_posts()) : while (have_posts()) : the_post(); ?>
			<?php the_title(); ?>
		<?php endwhile; else : ?>
		Blog Foot de Prono+, jeu gratuit
		<?php endif; ?>
		</div>
	</div>

    <div class="logo"><a href="/"></a></div>
    <div class="mabulle"><h1>LE BLOG FOOT</h1></div>
    <div style="clear:both"></div>
<?php /* bloginfo('description'); */ ?>
		  
<?php /*if (ls_getinfo("isref") == true) { ?>
<div style="padding:10px;">
<h2>Votre recherche : <a href="<?php echo get_settings('home'); ?>/"><strong><?php ls_getinfo("terms"); ?></strong></a></font></h2>
<p>Vous &ecirc;tes arriv&eacute; ici en cherchant <a href="<?php echo get_settings('home'); ?>/"><i><?php ls_getinfo("terms"); ?></i></a>.<br><b>Les articles suivants pourraient vous int&eacute;resser :</b></p>
<p><?php ls_related(5, 10, "- ", "<br>", "<br>", "", false, false); ?></p> 
</div>
<?php }*/ ?>


