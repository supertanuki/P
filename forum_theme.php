<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');


/*
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
	
} else if($uri[1]!='' && $uri[1]!='/' && substr($uri[1], 0, strlen('index.php')) != 'index.php') {
	header("HTTP/1.0 404 Not Found");
	exit;
}
*/

$user = user_authentificate();

pageheader($title ? $title : "Forum football Prono+");
?>

<div id="content_fullscreen">
<?php
// affichage des onglets
echo getOnglets('forum');
?>
<style type="text/css">
a {
	color:#F07113;
	text-decoration:none;
}

a:hover {
	color:#333;
	text-decoration:none;
}
</style>


	<div id="content">
	<h2 class="title_orange">Les derniers sujets actifs</h2>
	<? getLastPostFromForum(); ?>
	<br /><br />
	
	
	
	<h2 class="title_orange">Les thèmes du forum de discussion</h2>
	<p><a href="/forum_recherche.php"><img src="/template/default/search.png" border="0" align="absmiddle" /> Rechercher sur le forum</a></p>
<?php
	$SQL = "SELECT `id_forum_theme`, `label`, `description`, `url` FROM `forum_theme` ORDER BY `order`";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage());
		
	} else {
		echo "<ul>";
		while($forum_theme = $result->fetchRow())
		{
			echo "<li><a href=\"/forum-football/".$forum_theme->url."/\">".$forum_theme->label."</a><br />".$forum_theme->description."<br><br></li>";
		}
		echo "</ul>";
	}
?>
	</div>
</div>

<?php
pagefooter();
?>