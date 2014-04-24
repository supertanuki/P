<?php
// header
function pp_iphone_header($title, $is_menu=false, $is_retour=false, $is_list=false)
{
	global $user;
	
	setcookie("pp_no_mobile", "", 0, '/');
	
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="noindex,follow" name="robots" />
<link href="/mobile/pics/homescreen.gif" rel="apple-touch-icon" />
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
<link href="/mobile/css/style.css" rel="stylesheet" media="screen" type="text/css" />
<title><?php echo $title; ?> | Prono+</title>
<meta  name="description" content="<?php echo $title; ?>" />
<script type="text/javascript" src="/lib/scriptaculous-js-1.8.1/prototype.js"></script>
<script type="text/javascript" src="/mobile/javascript/functions.js"></script>
</head>
<body<?php if($is_list) echo ' class="list"'; ?>>
	<div id="logo"><a href="index.php"><img src="/template/default/logo.png" width="300" alt="Prono+" border="0" /></a></div>
	<div id="topbar">
		<?php
		if($is_retour)
		{
			?>
			<div id="leftnav"><a href="/mobile/index.php"><img alt="home" src="/mobile/images/home.png" /></a></div>
			<?php
		}
		?>
		<div id="title"><?php echo $title; ?></div>
	</div>
	<?php
	if($is_menu)
	{
		?>
		<div id="tributton">
			<div class="links"><a id="pressed" href="#">Accueil</a><?php echo !$user->id_user ? '<a href="/mobile/login.php">Se connecter</a>' : '<a href="/mobile/classements.php">Classements</a>'; ?><a href="/blog/">Blog</a></div>
		</div>
		<?php
	}
	?>
	<div id="content">
	<?php
}


// footer
function pp_iphone_footer()
{
	?>
	</div>
	<div id="footer">
		<a class="noeffect" href="/?nomobile=1">Accéder à la version normale de Prono+</a>
	</div>
</body>

</html><?php
}


// Ligne matches
function pp_iphone_matches($title, $url, $image, $description)
{
	?>
	<li class="withimage">
		<a class="noeffect" href="<?php echo $url; ?>">
			<img alt="<?php echo htmlspecialchars($title); ?>" src="<?php echo $image; ?>" />
			<span class="name"><?php echo $title; ?></span>
			<span class="comment"><?php echo $description; ?></span>
			<span class="arrow"></span>
		</a>
	</li>
	<?php
}
?>