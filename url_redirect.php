<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$uri = $_SERVER[REQUEST_URI];
$uri = explode('/', $uri);
//print_r($uri);
if($uri[1]=='forum-football')
{
	if(count($uri)==4)
	{
		//$sqldeb = $uri[1];
		$SQL = "SELECT `id_forum_theme` FROM `forum_theme` WHERE `url`='".$db->escapeSimple($uri[2])."'";
		$result = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result))
		{
			die ("<li>ERROR : ".$result->getMessage());
			
		} else {
			if($forum_theme = $result->fetchRow())
			{
				$id_forum_theme = $forum_theme->id_forum_theme;
				// recherche page
				$uri = explode('.html', $uri[3], 2);
				if(count($uri)==2)
				{
					$uri = explode('page', $uri[0], 2);
					if(count($uri)==2) $sqldeb = $uri[1];
				}
				include("forum.php");
				exit;
			}
		}

		
	} else {
		$uri = $uri[count($uri)-1];
		$uri = explode('.html', $uri, 2);
		if(count($uri)==2)
		{
			$uri = $uri[0];
			$uri = explode('-', $uri);
			$uri = $uri[count($uri)-1];
			$uri = explode('page', $uri);
			$nmsg = $uri[0];
			$sqldeb = $uri[1];
			if($nmsg)
			{
				include("forum.php");
				exit;
			}
		}
	}
	
	include("forum_theme.php");
	exit;
	
} else if($uri[1]!='' && $uri[1]!='/' && substr($uri[1], 0, strlen('index.php')) != 'index.php') {
	header("HTTP/1.0 404 Not Found");
  header("location:/404.html");
	exit;
}
?>