<?php
/*
if(substr($_SERVER['HTTP_HOST'], 0, 3)!='www' && $_SERVER['HTTP_HOST']!="127.0.0.12")
{
	header('Status: 301 Moved Permanently', false, 301);
	header("Location: http://www.pronoplus.com".$_SERVER['REQUEST_URI']);
	exit;
}
*/
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');


if($_GET[nomobile])
{
	setcookie("pp_no_mobile", "1", 0, '/');
	
} else if(!$_COOKIE[pp_no_mobile])
{
	// d�tection iphone / ipod / android
	if(navigator_is_mobile())
	{ 
		header("location:/mobile/");
		exit;
	}
}

$user = user_authentificate();
/*
echo "<li>user: ";
if(!$user) echo "false"; else echo $user->login;
echo "</li>";
*/
pageheader($debug ? "MAINTENANCE" : "Prono+, Pronostic de foot", array('meta_description' => 'Jeu gratuit de pronostics de football'));

getContentLeft();

getContentRight();

pagefooter();