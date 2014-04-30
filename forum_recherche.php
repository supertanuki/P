<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

pageheader("Rechercher | Forum football Prono+");
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
	
	
	<h2 class="title_orange">Rechercher</h2>
	
	<form method="get">
	<table cellpadding="4" cellspacing="1">
	<tr>
		<td>Mots clés</td>
		<td><input name="keywords" type="text" value="<?php echo htmlspecialchars(stripslashes($_GET[keywords]))?>" maxlength="200" size="50" /></td>
	</tr>
	<tr>
		<td>Utilisateur</td>
		<td><input name="user" type="text" value="<?php echo htmlspecialchars(stripslashes($_GET[user]))?>" maxlength="200" size="20" /></td>
	</tr>
	<tr>
		<td>Thème</td>
		<td><select name="id_forum_theme">
			<option value="">Tous les forums</option>
			<?php
				$SQL = "SELECT `id_forum_theme`, `label`, `description`, `url` FROM `forum_theme` ORDER BY `order`";
				$result = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result))
				{
					die ("<li>ERROR : ".$result->getMessage());
					
				} else {
					while($forum_theme = $result->fetchRow())
					{
						echo "<option value=\"".$forum_theme->id_forum_theme."\" ".($forum_theme->id_forum_theme == $_GET[id_forum_theme] ? "selected" : "").">".$forum_theme->label."</option>";
					}
				}
			?></select></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Rechercher" class="link_button" /></td>
	</tr>
	</table>
	</form><br />


<?php if($_GET[user] || $_GET[keywords]) { ?>

<h2 class="title_orange">Résultats de la recherche</h2>	

<?php
	$nouser = true;
	if($_GET[user])
	{
		$SQL = "SELECT id_user FROM pp_user WHERE login='".$_GET[user]."'";
		$result_user = mysql_query($SQL) or die($SQL);
		if(!$msg_user = mysql_fetch_assoc($result_user))
		{
			$nouser = false;
			?>
			<p class="message_error">Utilisateur <strong><?php echo htmlspecialchars($_GET[user])?></strong> non trouvé !</p>
			<?php
		}
	}
	
	if($nouser) {
	
if(!$_GET[p]) $_GET[p]=0;

if(trim($_GET[keywords]))
{
	$tabkeywords = explode(' ', trim($_GET[keywords]));
}

$SQL = "SELECT forum.Nmsg, forum.Nquest, forum.datemsg,
			first_msg.sujet, first_msg.url,
			pp_user.id_user, pp_user.login,
			forum_theme.label AS label_theme, forum_theme.url AS url_theme
		FROM forum
			INNER JOIN forum AS first_msg
				ON (first_msg.Nmsg=forum.Nquest OR first_msg.Nmsg=forum.Nmsg) AND first_msg.Nmsg!=forum.Nmsg AND first_msg.supp=0
			INNER JOIN pp_user ON pp_user.id_user=forum.id_user
			INNER JOIN forum_theme ON forum_theme.id_forum_theme=first_msg.id_forum_theme
		WHERE 1 
		".($_GET[user] ? " AND forum.id_user = '".$msg_user[id_user]."'" : "")."
		".($_GET[id_forum_theme] ? " AND first_msg.id_forum_theme = '".$_GET[id_forum_theme]."'" : "");
		
if(trim($_GET[keywords]))
{
	$SQL .= " AND MATCH(forum.sujet, forum.message) AGAINST('".$_GET[keywords]."' IN BOOLEAN MODE) ";
	foreach($tabkeywords as $word)
	{
		$SQL .= " AND (forum.sujet LIKE '%".$word."%' OR forum.message LIKE '%".$word."%') ";
	}
}

$SQL .= "ORDER BY datemsg DESC LIMIT ".$_GET[p].", 20";

//echo "<li>$SQL";

$resmsg = mysql_query($SQL) or die("<li>".utf8_encode(mysql_error())."<li>$SQL");
if(!mysql_num_rows($resmsg))
{
?>
<p class="message_error"><strong>Aucun résultat trouvé</strong></p>
<?php } else { ?>
<a name="results"></a>
<table width="100%" border="0" cellspacing="1" cellpadding="4">
<tr> 
	<th width="20%">Forum</th>
	<th width="40%">Sujets</th>
	<th width="20%">Auteur</th>
	<th width="20%">Date message</th>
</tr>
<?php
while($lmsg=mysql_fetch_assoc($resmsg))
{
	if($class_line=="ligne_grise") {
		$color="ligne_blanche";
	} else {
		$color="ligne_grise";
	}
	
	/*
	// 1er message du sujet
	$SQL = "SELECT forum.Nmsg, forum.sujet, forum.url, forum_theme.label AS label_theme, forum_theme.url AS url_theme
			FROM forum
			INNER JOIN forum_theme ON forum_theme.id_forum_theme=forum.id_forum_theme
			WHERE Nmsg=".($lmsg["Nquest"] ? $lmsg["Nquest"] : $lmsg["Nmsg"]);
	$res_sujet = mysql_query($SQL);
	$quest = mysql_fetch_assoc($res_sujet);
	*/
	
	// rang du message
	if($lmsg["Nquest"])
	{		
		// nb de messages avant ce message ?
		$resdermsg = mysql_query("select COUNT(Nmsg)+1 AS NBMSG from forum where (Nmsg=".$lmsg["Nquest"]." OR Nquest=".$lmsg["Nquest"].") AND datemsg < '".$lmsg["datemsg"]."'");
		$nbmsg = mysql_fetch_assoc($resdermsg);
		$url = $lmsg["url"]."-".$lmsg["Nquest"].(ceil($nbmsg[NBMSG]/10)>1 ? "page".(ceil($nbmsg[NBMSG]/10)*10 - 10) : "").".html#mess".$lmsg["Nmsg"];
		
	} else {
		$url = $lmsg["url"]."-".$lmsg["Nmsg"].".html";
	}
?>	
<tr class="<?php echo $class_line?>">
	<td><a href="/forum-football/<?php echo $lmsg["url_theme"]?>/"><?php echo $lmsg["label_theme"]?></a></td>
	<td><a href="/forum-football/<?php echo $url?>"><?php echo htmlspecialchars($lmsg["sujet"]);?></a></td>
	<td><?php echo htmlspecialchars($lmsg["login"]);?></td>
	<td><?php echo formatdateheure($lmsg["datemsg"])?><a href="/forum-football/<?php echo $url?>"><img src="/template/default/last.gif" alt="Aller au message" hspace="2" border="0" align="absmiddle"></a></td>
	</tr>
<?php } ?>
</table>
<?php

		//if($_GET[user] && !$_GET[keywords])		{
			$SQL = "SELECT COUNT(forum.Nmsg) AS NBPOST
					FROM forum
						INNER JOIN forum AS first_msg
							ON (first_msg.Nmsg=forum.Nquest OR first_msg.Nmsg=forum.Nmsg) AND first_msg.Nmsg!=forum.Nmsg AND first_msg.supp=0
						INNER JOIN pp_user ON pp_user.id_user=forum.id_user
						INNER JOIN forum_theme ON forum_theme.id_forum_theme=first_msg.id_forum_theme
					WHERE 1 
					".($_GET[user] ? " AND forum.id_user = '".$msg_user[id_user]."'" : "")."
					".($_GET[id_forum_theme] ? " AND first_msg.id_forum_theme = '".$_GET[id_forum_theme]."'" : "");
			if(trim($_GET[keywords]))
			{
				$SQL .= " AND MATCH(forum.sujet, forum.message) AGAINST('".$_GET[keywords]."' IN BOOLEAN MODE) ";
				foreach($tabkeywords as $word)
				{
					$SQL .= " AND (forum.sujet LIKE '%".$word."%' OR forum.message LIKE '%".$word."%') ";
				}
			}
			
			$resmsg = mysql_query($SQL) or die("<li>".utf8_encode(mysql_error())."<li>$SQL");
			if($nb = mysql_fetch_assoc($resmsg))
			{
				if($NbTotal = $nb['NBPOST'])
				{			
					/* pagination */
					$url_page = "/forum_recherche.php?keywords=".$_GET[keywords]."&id_forum_theme=".$_GET[id_forum_theme]."&user=".$_GET[user];
					$NBtoShow = 20;
					$NbPagesShowed = 3;
					$beginline = !$_GET[p] ? 0 : $_GET[p];
					
					if($NbTotal>$NBtoShow)
					{
						echo "<div align=\"center\">Pages : ";
						$nbpages = ceil($NbTotal/$NBtoShow);
						// affichage first et previous
						if($beginline>0)
						{
							echo "<a href=\"".$url_page.(($beginline-$NBtoShow)>0? "&p=".($beginline-$NBtoShow) : "")."#results\">&nbsp;&lt;&nbsp;</a>";
						}
						// affichage page
						if(($diff=(ceil($beginline/$NBtoShow)-$NbPagesShowed+1))>0)
						{
							echo "<a href=\"".$url_page."#results\">&nbsp;1&nbsp;</a>";
							if($diff>1) echo "&nbsp;...&nbsp;"; else echo "&nbsp;";
						}
						for($i=1; $i<=$nbpages; $i++)
						{
							$begin=(($i-1)*$NBtoShow);
							if($begin>=($beginline-($NbPagesShowed-1)*$NBtoShow) && $begin<=($beginline+($NbPagesShowed-1)*$NBtoShow))
							{
								if($beginline==$begin)
									echo "&nbsp;<strong>".$i."</strong>&nbsp;";
								else
									echo "<a href=\"".$url_page."&p=".$begin."#results\">&nbsp;".$i."&nbsp;</a>";
								if($i!=$nbpages) echo "&nbsp;";
							}
						}
						if(($diff=(ceil($beginline/$NBtoShow)+$NbPagesShowed))<$nbpages)
						{
							if(($diff+1)<$nbpages) echo "&nbsp;...&nbsp;";
							echo "<a href=\"".$url_page."&p=".(($nbpages-1)*$NBtoShow)."#results\">&nbsp;".$nbpages."&nbsp;</a>";
						}
						// affichage last et next
						if(($beginline+$NBtoShow)<$NbTotal)
						{
							echo "&nbsp;&nbsp;<a href=\"".$url_page."&p=".($beginline+$NBtoShow)."#results\">&nbsp;&gt;&nbsp;</a>";
						}
						echo "</div>";
					}
				}
			//}
		}
	}
  }
}
?>
	
	

	</div>
</div>

<?php
pagefooter();
?>