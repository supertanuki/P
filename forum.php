<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

if(is_array($_GET)) foreach($_GET as $key=>$value) $$key = $value;
if(is_array($_POST)) foreach($_POST as $key=>$value) $$key = $value;

// admin
if($user->id_user==27 && $nmsg_admin) {
	if($bloquer) mysql_query("update forum set bloque=1 where Nmsg=$nmsg_admin");
	if($debloquer) mysql_query("update forum set bloque=0 where Nmsg=$nmsg_admin");
	if($supp) mysql_query("update forum set supp=1 where Nmsg=$nmsg_admin");
	if($change_theme)
	{
		//echo "<li>change theme";
		$SQL = "update forum set id_forum_theme=$id_forum_theme_admin where Nmsg=$nmsg_admin";
		mysql_query($SQL) or die ("<li>".$SQL);
		$id_forum_theme = $id_forum_theme_admin;
		$nmsg = $nmsg_admin;
	}
}


if(($nmsg*1)==0 && ($sqldeb*1)!=0) $beginline = $sqldeb;
	else if(($nmsg*1)==0) { unset($nmsg); unset($sqldeb); }

	
if($nmsg)
{
	$resmsg=mysql_query("select sujet, id_forum_theme from forum where Nmsg='$nmsg'");
	if($lmsg=mysql_fetch_assoc($resmsg))
	{
		$title = $lmsg[sujet]." | Forum football Prono+";
		$title_page = $lmsg[sujet];
		
		$resmsg=mysql_query("SELECT `id_forum_theme`, `label`, `description`, `url` FROM `forum_theme` WHERE id_forum_theme='".$lmsg[id_forum_theme]."'");
		if($theme=mysql_fetch_assoc($resmsg))
		{
			$title_theme = $theme["label"];
			$uri_begin_theme = $theme["url"].'/';
			$id_forum_theme = $theme["id_forum_theme"];
			
		} else $id_forum_theme=0;
		
	} else $nmsg=0;
	
} else if($id_forum_theme)
{
	$resmsg=mysql_query("SELECT `id_forum_theme`, `label`, `description`, `url` FROM `forum_theme` WHERE id_forum_theme='".$id_forum_theme."'");
	if($theme=mysql_fetch_assoc($resmsg))
	{
		$title = $theme[label]." | Forum football Prono+";
		$title_page = $theme[label];
		$uri_begin_theme = $theme["url"];
		if($sqldeb*1 != 0) $beginline = $sqldeb;
		
	} else $id_forum_theme=0;
}

if(!$nmsg && !$id_forum_theme) HeaderRedirect('/forum-football/');



pageheader($title ? $title : "Forum football Prono+", array('meta_description' => 'Les discussions de : '.$title_page));
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
<script language="JavaScript">
<!--
function balise(theform, type) {
	if(type=="image") {
		var libelle="Saisissez l'adresse URL de l'image:";
	} else if(type=="lien") {
		var libelle="Saisissez l'adresse URL du lien:";
	}
	insertion = prompt(libelle,"");
	if ((insertion != null) && (insertion != "")) {
		if(type=="image") {
			theform.message.value += "[img]" + insertion + "[/img]";
		} else if(type=="lien") {
			if(insertion.substring(0,7)!="http://") insertion = "http://" + insertion
			theform.message.value += insertion;
		}
	}
	theform.message.focus();
}

function afficher_charte() {
	popupImage = window.open('/charte.htm','_blank','width=500,height=400,toolbar=0,location=0,directories=0,menuBar=0,scrollbars=1,resizable=0');
	popupImage.focus();
}

function unselect_refresh()
{
	if(obj = document.getElementById('refresh_forum')) obj.checked = false;
}
-->
</script>
<?php
if($sendmsg && $message) {
	// vrification dernier message post
	$datedelai=strtotime("now")-10;
	$res_msg=mysql_query("select Nmsg from forum where id_user='".$user->id_user."' and datemsg > $datedelai") or die("select Nmsg from forum where id_user=$user->id_user and datemsg > $datedelai");
	$nbmsg=mysql_num_rows($res_msg);
	
	if($user->id_user)
	{
		if(!$nmsg  && !$nbmsg) {
			// insertion d'un sujet
			if($sujet) {
       $sql = "insert into forum(id_user, id_forum_theme, sujet, message, url, datemsg, dateder) values('".$user->id_user."', '".$id_forum_theme."', '".($sujet)."', '".($message)."', '".toUrlRewriting($sujet)."', '".strtotime("now")."', '".strtotime("now")."')";
				mysql_query($sql);
        
			} else {
				$lemsg=$message;
			}
		} elseif($num_msg) {
			// modification d'un message
			$rs_msg=mysql_query("select * from forum where Nmsg=$num_msg");
			$lmsg=mysql_fetch_array($rs_msg);
			$datenow = strtotime("now");
			if((($lmsg["datemsg"]+1800) > $datenow && $lmsg["id_user"]==$user->id_user) || $user->id_user==27) {
				$SQL="update forum set message='".($message)."'".($sujet ? ", sujet='".($sujet)."'" : "")." where Nmsg=$num_msg";
				mysql_query($SQL);
				//echo "<li>$SQL";
			} else {
			?>
			<script language="javascript">
			alert("Vous ne pouvez plus modifier ce message !")
			</script>
			<?php
			}
		} elseif(!$nbmsg) {
			// réponse à un sujet
			mysql_query("insert into forum(Nquest, id_user, message, datemsg) values('$nmsg', '".$user->id_user."', '".($message)."', '".strtotime("now")."')");
			mysql_query("update forum set dateder='".strtotime("now")."' where Nmsg='$nmsg'");
		}
	}
		
	if(!$num_msg && $nbmsg) {
		$lemsg=$message;
		?>
		<script language="javascript">
		alert("Vous devez attendre un délai de quelques secondes avant de poster un nouveau message !")
		</script>
		<?php
	} else {
		$resnbmsg = mysql_query("select Nmsg from forum where Nmsg='$nmsg' or Nquest='$nmsg'");
		$nbmsg = mysql_num_rows($resnbmsg);
		$sqldeb = ceil($nbmsg/10)*10-10;
		if($sqldeb<0) $sqldeb=0;
		
		$resdermsg = mysql_query("select Nmsg from forum where id_user='".$user->id_user."' order by Nmsg desc");
		$lmsg = mysql_fetch_row($resdermsg);
		
		$sujet = mysql_query("select * from forum where Nmsg='$nmsg'");
		$rowsujet = mysql_fetch_assoc($sujet);
		?>
		<script language="javascript">
		location.replace("/forum-football/<?php echo $rowsujet["url"]?>-<?php echo $rowsujet["Nmsg"]?><?php echo $sqldeb>0?"page".$sqldeb:""?>.html#mess<?php echo $lmsg[0]?>");
		</script>
		<?php
	}
}

if(!$nmsg && $id_forum_theme) { ?>

<h1 class="title_orange"><?php echo $title_page?></h1>
<?php
if(!$beginline) $beginline=0;
$resmsg=mysql_query("SELECT * FROM forum WHERE Nquest=0 AND supp=0 AND id_forum_theme=$id_forum_theme ORDER BY dateder DESC LIMIT ".$beginline.",20");

if(!mysql_num_rows($resmsg)) {
?>
<tr bgcolor="#<?php echo $color?>">
	<td colspan="5"><p class="center"><strong>Aucun sujet n'a été créé dans ce forum pour l'instant.</strong><br />Pourquoi ne pas être le premier à le faire ?</p></td>
</tr>
<?php
} else {
?>
<table width="100%" border="0" cellspacing="1" cellpadding="4">
<tr> 
  <th width="50%">Sujets</th>
  <th width="10%">Auteur</th>
  <th width="20%">Dernier message</th>
  <th width="5%">Lu</th>
  <th width="5%">R&eacute;ponses</th>
</tr>
<? $color="ffffff"; ?>
<tr bgcolor="#<?php echo $color?>">
	<td colspan="5"><a href="#modifier_msg"><img src="/template/default/comment_new.gif" height="16" width="16" alt="Ouvrir au sujet" border="0" align="absmiddle"> Ouvrir un sujet</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/forum_recherche.php?id_forum_theme=<?php echo $id_forum_theme?>"><img src="/template/default/search.png" border="0" align="absmiddle" /> Rechercher sur le forum</a></td>
</tr>
<?php

$color="aaaaaa";
while($lmsg=mysql_fetch_array($resmsg)) {
	$resrep=mysql_query("select Nmsg from forum where Nquest=".$lmsg["Nmsg"]);
	if($color=="eeeeee") {
		$color="ffffff";
	} else {
		$color="eeeeee";
	}
?>


		<tr bgcolor="#<?php echo $color?>">
		  <td><a href="/forum-football/<?php echo $lmsg["url"]?>-<?php echo $lmsg["Nmsg"]?>.html">
			<?php
			echo htmlspecialchars($lmsg["sujet"]);
			if($lmsg["bloque"]) {
			?>&nbsp;<img src="/template/default/cadenas.gif" alt="Sujet bloqu&eacute;" border="0" align="absmiddle"><? } ?>
			</a><br />
			<? //pages
$nbmsg=mysql_query("select Nmsg from forum where Nmsg=".$lmsg["Nmsg"]." or Nquest=".$lmsg["Nmsg"]);
$nbtotalmsg=mysql_num_rows($nbmsg);
$nbaff=10;

$nbpage=ceil($nbtotalmsg/$nbaff);
if($nbtotalmsg > 10) {
	?>
			<font size="1">[ 
			<?php
	for($i=1; $i<=$nbpage; $i++) {
		$ldeb=(($i-1)*$nbaff);
		?>
			<a href="/forum-football/<?php echo $lmsg["url"]?>-<?php echo $lmsg["Nmsg"]?><?php echo $ldeb>0?"page".$ldeb:""?>.html">
			<?php echo $i?>
			</a> 
			<?	if($i!=$nbpage) echo ".";
	} ?>
			] </font>
			<? } ?>
		  </td>
	<td>
	<?php
		$rec_nlogin=$lmsg["id_user"];
		if($rec_nlogin!=$user->id_user) {
			$joueur = nom_joueur($rec_nlogin);
			echo "<a href=\"/user.php?q=".urlencode(htmlspecialchars($joueur->login))."\" class=\"link_orange\">".$joueur->login."</a>";
		} else {
			echo "<font color=\"red\">".$user->login."</font>";
		}
	?>
	</td>
		  <td>
		  <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
	<td><div align="center"><font size="1">
	<?php echo formatdateheure($lmsg["dateder"])?></font>
	</div></td>
	<? // dernier message du topic
	$resdermsg=mysql_query("select Nmsg from forum where Nmsg=".$lmsg["Nmsg"]." or Nquest=".$lmsg["Nmsg"]." order by Nmsg desc");
	$lnmsg=mysql_fetch_row($resdermsg);
	?>
				<td width="23" valign="middle"><a href="/forum-football/<?php echo $lmsg["url"]?>-<?php echo $lmsg["Nmsg"]?><?php echo ($nbpage*10-10)>0?"page".($nbpage*10-10):""?>.html#mess<?php echo $lnmsg[0]?>"><img src="/template/default/last.gif" alt="Aller au dernier message" width="16" height="16" hspace="2" border="0" align="absmiddle"></a></td>
  </tr>
</table>
 
		  </td>
		  <td> <div align="center"> 
			  <?php echo $lmsg["nblu"]?>
			  </div></td>
		  <td> <div align="center">
			  <?php echo mysql_num_rows($resrep)?>
			  </div></td>
		</tr>
		<? } ?>
	  </table>
	  <br>
<?php
$res_nb_sujets = mysql_query("select Nmsg from forum where Nquest=0 and supp=0 AND id_forum_theme='".$id_forum_theme."'");
$NbTotal = mysql_num_rows($res_nb_sujets);

$NBtoShow=20;
$NbPagesShowed=3;
$beginline=!$beginline?0:$beginline;
if($NbTotal>$NBtoShow)
{
	echo "<div align=\"center\">Pages : ";
	$nbpages = ceil($NbTotal/$NBtoShow);
	// affichage first et previous
	if($beginline>0)
	{
		echo "<a href=\"/forum-football/".$uri_begin_theme."/".(($beginline-$NBtoShow)>0?"page".($beginline-$NBtoShow).".html":"")."\">&nbsp;&lt;&nbsp;</a>";
	}
	// affichage page
	if(($diff=(ceil($beginline/$NBtoShow)-$NbPagesShowed+1))>0)
	{
		echo "<a href=\"/forum-football/".$uri_begin_theme."/\">&nbsp;1&nbsp;</a>";
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
				echo "<a href=\"/forum-football/".$uri_begin_theme."/page".$begin.".html\">&nbsp;".$i."&nbsp;</a>";
			if($i!=$nbpages) echo "&nbsp;";
		}
	}
	if(($diff=(ceil($beginline/$NBtoShow)+$NbPagesShowed))<$nbpages)
	{
		if(($diff+1)<$nbpages) echo "&nbsp;...&nbsp;";
		echo "<a href=\"/forum-football/".$uri_begin_theme."/page".(($nbpages-1)*$NBtoShow).".html\">&nbsp;".$nbpages."&nbsp;</a>";
	}
	// affichage last et next
	if(($beginline+$NBtoShow)<$NbTotal)
	{
		echo "&nbsp;&nbsp;<a href=\"/forum-football/".$uri_begin_theme."/page".($beginline+$NBtoShow).".html\">&nbsp;&gt;&nbsp;</a>";
	}
	echo "</div>";
}

} // fin tableau liste des sujets
?>


<br>
<? } else if($nmsg) {


mysql_query("update forum set nblu=nblu+1 where Nmsg=$nmsg") or die("");
$resmsg=mysql_query("select * from forum where Nmsg=$nmsg") or die("");
if($lmsg=mysql_fetch_array($resmsg))
{
	$is_bloque=$lmsg["bloque"];
	$uri_begin = $lmsg["url"].'-'.$lmsg["Nmsg"];
	$nbaff=10;
}

	
$resmsg=mysql_query("select Nmsg from forum where Nmsg=$nmsg or Nquest=$nmsg");
$nbtotalmsg=mysql_num_rows($resmsg);

if($_GET[gotolast])
{
	// chercher la derniere page // $sqldeb
	$sqldeb = ceil($nbtotalmsg/$nbaff)*$nbaff-$nbaff;
}

if(!$sqldeb) $sqldeb=0;
if($sqldeb>0) $repaff=1;
$resmsg=mysql_query("select * from forum where Nmsg=$nmsg or Nquest=$nmsg order by datemsg limit $sqldeb,$nbaff");
?>

<h2 class="title_orange"><a href="/forum-football/<?php echo $uri_begin_theme?>"><?php echo $title_theme?></a></h2><br />
<h1 class="title_green"><?php echo $lmsg["sujet"]?> <? if($is_bloque) { ?><img src="/template/default/cadenas.gif" alt="Sujet bloqu&eacute;" border="0" align="absmiddle"><? } ?></h1>

<table width="100%" border="0" cellspacing="0" cellpadding="2">
  		<tr> 
		  <td colspan="2"> 
			<div align="center">
			<div style="float:right;"><a href="#modifier_msg"><img src="/template/default/comment_new.gif" height="16" width="16" alt="Répondre au sujet" border="0" style="float:left; margin-right:6px;">Répondre</a></div>
			
			 Pages : 			  
			  <?php
	 $nbpage=ceil($nbtotalmsg/$nbaff);
	 for($i=1; $i<=$nbpage; $i++) {
		$ldeb=(($i-1)*$nbaff);
		if($ldeb!=$sqldeb) {
	 ?>
			  <a href="/forum-football/<?php echo $uri_begin?><?php echo $ldeb>0?"page".$ldeb:""?>.html"><?php echo $i?></a>
			  <? 	} else { 
 		echo "<strong> $i </strong>";
  	} 
	echo ".";
  } ?>
  
	<? // dernier message du topic
	$resdermsg=mysql_query("select Nmsg from forum where Nmsg=$nmsg or Nquest=$nmsg order by Nmsg desc");
	$lnmsg=mysql_fetch_row($resdermsg);
	?>
	<a href="/forum-football/<?php echo $uri_begin?><?php echo ($nbpage*10-10)>0?"page".($nbpage*10-10):""?>.html#mess<?php echo $lnmsg[0]?>"><img src="/template/default/last.gif" alt="Aller au dernier message" width="16" height="16" hspace="2" border="0" align="absmiddle"></a>
		    </div></td>
  </tr>
<?php
$nbmsguser = array();
$repaff = 0;
$i_msg = 0;

while($lmsg=mysql_fetch_array($resmsg)) {
?>
  <tr> 
    	  <td valign="top" align="center" width="15%" bgcolor="#eeeeee"><a name="mess<?php echo $lmsg["Nmsg"]?>"></a>
			<div style="margin-bottom:10px;">
				<div style="width:120px;overflow:hidden;background-color:#CCCCCC">
    				<h2 class="title_green" style="width:120px;overflow:hidden;">
					<?php
					$joueur = nom_joueur($lmsg["id_user"]);
					echo "<a href=\"/user.php?q=".urlencode(htmlspecialchars($joueur->login))."\" class=\"link_orange\">".$joueur->login."</a>";
					?>
					</h2>
				<?php
				echo "<a href=\"/user.php?q=".urlencode(htmlspecialchars($joueur->login))."\" class=\"link_orange\">";
				if($avatar = getAvatar($lmsg["id_user"], $joueur->avatar_key, $joueur->avatar_ext, 'normal')) {
				?>
					<img src="/avatars/<?php echo $avatar?>" height="118" width="118" style="border:1px solid #666" />
				<? } else { ?>
					<img src="/template/default/_profil.png" height="118" width="118" style="border:1px solid #666" />
				<? }
				echo "</a>";
				?>
				</div>
				<br />
				<? // nombre de message de l'utilisateur
				if(!$nbmsguser[$lmsg["id_user"]])
				{
					$resdermsg=mysql_query("select COUNT(Nmsg) AS NBMSG from forum where id_user='".$lmsg["id_user"]."' AND supp=0");
					$nbmsg=mysql_fetch_assoc($resdermsg);
					$nbmsguser[$lmsg["id_user"]] = $nbmsg["NBMSG"];
				}
				echo "<a href=\"/forum_recherche.php?user=".urlencode($joueur->login)."\">".$nbmsguser[$lmsg["id_user"]]." messages</a>";
				?>
        </div></td>
    <td width="85%" valign="top">
	<hr>
    <div align="right"><?php
	// modifier le message
	$datenow = strtotime("now");
	if((($lmsg["datemsg"]+1800) > $datenow && $lmsg["id_user"]==$user->id_user) || $user->id_user==27) {
	?><a href="/forum-football/<?php echo $uri_begin?>.html?modifier=1&num_msg=<?php echo $lmsg["Nmsg"]?>#modifier_msg"><font color="red" size="1">Modifier</font></a> |
	<?php
	}
	?><font size="1"> Posté le <?php echo formatdateheure($lmsg["datemsg"])?></font></div>
	<br />
	<div style="width:620px;overflow:hidden; margin-bottom:10px;">
	  <? echo formattexte($lmsg["message"]); ?>
	  </div>
	</td>
  </tr>
  		<tr> 
		  <td colspan="2"> 
			<?			
			if(!$repaff) {
				echo '<div align="center">' . facebook_libe_button('forum-football/' . $uri_begin . '.html', 450) . '</div><br /><br />';
			}
			
			// if(mysql_num_rows($resmsg)>1 && $i_msg==0)
			// {
				// $i_msg++;
				// ? >
				// <div style="margin-bottom:10px; margin-top:10px;">
				// <script type="text/javascript"><!--
				// google_ad_client = "pub-4614826582647836";
				// /* Prono+ forum 728x90, date de création 15/07/09 */
				// google_ad_slot = "9156666118";
				// google_ad_width = 728;
				// google_ad_height = 90;
				// -->
				// </script>
				// <script type="text/javascript"
				// src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
				// </script></div>
			// < ?
			// }

			
			if(mysql_num_rows($resmsg)>1 && !$repaff) {
				$repaff=1;
    		?>
			<div align="center"><strong>R&eacute;ponses</strong></div>
      		<? } ?>
		  </td>
  </tr>
<? } ?>

  <tr> 
    	  <td><a href="/forum-football/<?php echo $uri_begin_theme?>"><img src="/template/default/last.gif" width="16" height="16" border="0" align="absmiddle"> retour</a></td>
    	  <td> 
			<div align="right">Pages 
			  : 
			  <?php
 $nbpage=ceil($nbtotalmsg/$nbaff);
 for($i=1; $i<=$nbpage; $i++) {
 	$ldeb=(($i-1)*$nbaff);
	if($ldeb!=$sqldeb) {
 ?>
			  <a href="/forum-football/<?php echo $uri_begin?><?php echo $ldeb>0?"page".$ldeb:""?>.html">
			  <?php echo $i?>
			  </a> 
			  <? 	} else { 
 		echo "<strong> $i </strong>";
  	} 
	echo ".";
  } ?>
		    </div></td>
  </tr>
 <tr> 
    	  <td>&nbsp;</td>
    	  <td><input id="refresh_forum" type="checkbox" value="1" <?php echo $_GET[refresh_forum] ? "checked=\"checked\"" : ""; ?> style="vertical-align:middle" /> <label for="refresh_forum">Rafraichir le sujet toutes les minutes</label></td>
  </tr>
</table>

<script language="javascript">
<!--
var time2refresh = 60000;

function RefreshPage()
{
	if(document.getElementById('refresh_forum').checked)
	{
		document.location.href = '/forum.php?nmsg=<?php echo $nmsg?>&gotolast=1&refresh_forum=1';
	} else {
		setTimeout("RefreshPage()", time2refresh);
	}
}
setTimeout("RefreshPage()", time2refresh);
-->
</script>


<br>
<? } ?>

<? if(!$is_bloque || ($is_bloque && $user->id_user==27)) { ?>
	

<a name="modifier_msg"></a>
<?php
if(!$nmsg && !$modifier) {
	$titre_cadre="Ouvrir un sujet dans ".$title_page;
} elseif($num_msg && $modifier) {
	$titre_cadre="Modifier le message";
} else {
	$titre_cadre="Répondre au sujet";
}
echo "<div class=\"popup\"><h2 class=\"title_orange\">$titre_cadre</h2></div>";
		

if(!$user->id_user) {
$disabled_forum=true;
?>
<p align="center"><br /><strong>Vous devez vous enregistrer pour écrire sur le forum de discussion.<br />Vous pouvez <a href="javascript:"; onclick="SeConnecter(this);">vous inscrire en quelques secondes</a>.</strong><br /><br /></p>
<? } else { ?>
	<form name="envoyer_msg" method="post" action="/forum.php">

		<table width="100%" border="0" cellspacing="1" cellpadding="2">
		  <? /*<tr>
            <td>&nbsp;</td>
            <td><a href="javascript:afficher_charte();">Charte d'utilisation du forum</a></td>
	      </tr>*/ ?>
			<?php
			if($num_msg && $modifier)
			{
				$res_msg = mysql_query("select Nquest, id_forum_theme, sujet, message from forum where Nmsg=$num_msg");
				$message = mysql_fetch_object($res_msg);
			}
			?>
			

		  <input name="id_forum_theme" type="hidden" value="<?php echo $id_forum_theme?>">
		  <input name="nmsg" type="hidden" value="<?php echo $nmsg?>">
		  <input name="num_msg" type="hidden" value="<?php echo $num_msg?>">
		  
			<?php
			if(!$nmsg  || $modifier && $num_msg && !$message->Nquest) {
			?>
		  <tr> 
			<td width="20%" align="right">Titre</td>
			<td width="80%"><input name="sujet" type="text" id="sujet" size="50" maxlength="100" value="<?php echo htmlentities(utf8_decode(stripslashes($message->sujet)))?>" <?php echo $disabled_forum?"disabled=\"disabled\"":""?>></td>
		  </tr>
		  <tr> 
			<td></td>
			<td><small>Exemple de bonne formulation de sujet : "Qui gagnera la Coupe du Monde au Brésil ?"<br />ou bien "Comment peut-on s'améliorer aux pronostics ?"<br />Contre-exemple : "Question..." ou bien "Qui gagne ?"</small><br />&nbsp;</td>
		  </tr>
		  <? } ?>
		  <tr> 
			<td></td>
			<td> <img onclick="smiley(':)')" src="/smileys/1.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':furieux:')" src="/smileys/2.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':]')" src="/smileys/3.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':D')" src="/smileys/4.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':?:')" src="/smileys/5.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':timide:')" src="/smileys/6.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':saoul:')" src="/smileys/7.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley('8)')" src="/smileys/8.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':p')" src="/smileys/9.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':triste:')" src="/smileys/10.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':diable:')" src="/smileys/11.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':bisou:')" src="/smileys/12.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(';)')" src="/smileys/13.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':(')" src="/smileys/14.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':O')" src="/smileys/15.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':mignon:')" src="/smileys/16.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':dodo:')" src="/smileys/17.gif" border="0" onmouseover="this.style.cursor='hand';"> 
			  <img onclick="smiley(':fou:')" src="/smileys/18.gif" border="0" onmouseover="this.style.cursor='hand';">
			  <input name="image" type="button" id="image" value="Ins&eacute;rer une image" onclick="balise(this.form, 'image');"  class="link_button" />
			</td>
		  </tr>
		  <tr> 
			<td valign="top"> <div align="right">Message 
				&nbsp;</div></td>
			<td> <textarea name="message" cols="60" rows="10" onfocus="unselect_refresh()" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" <?php echo $disabled_forum?"disabled=\"disabled\"":""?>><?php echo stripslashes($message->message)?></textarea></td>
		  </tr>
		  <tr> 
			<td>&nbsp;</td>
			<td>
				<p>Merci :
				<ul>
					<li>de vérifier que le sujet n'existe pas déjà si vous ouvrez un sujet,</li>
					<li>d'être clair dans votre propos (1 ou 2 mots ne suffisent pas pour ouvrir un sujet),</li>
					<li>de ne pas insulter, ou proférer des messages haineux ou à caractères racistes,</li>
					<li>d'écrire en français (et non en abréviation ou SMS)</li>
					<li>et de respecter les membres qui vont vous lire !</li>
				</ul>
				En résumé, tout message ne respectant pas la <a href="http://fr.wikipedia.org/wiki/N%C3%A9tiquette" target="_blank">nétiquette</a> seront purement et simplement supprimés.</p>
				<p><label><input type="checkbox" /> En cochant cette case, je déclare respecter ces règles simples de bonnes conduites permettant un échange constructif avec les membres de Prono+.</label></p>
			</td>
		  </tr>
		  		
		  <tr> 
			<td>&nbsp;</td>
			<td> <input name="sendmsg" type="hidden" value="1"> <input name="envoyer" type="submit" class="link_button" <?php echo $disabled_forum?"disabled=\"disabled\"":""?> value="Envoyer" />
			</td>
		  </tr>
		</table>
	</form>
<?php } ?>

	<? //outils admin
	if($nmsg && $user->id_user==27) {
	?>
		<br /><form method="post" action="/forum.php">
		<fieldset><legend>Admin</legend><br />
			<input name="nmsg_admin" type="hidden" value="<?php echo $nmsg?>">
			<input name="id_forum_theme" type="hidden" value="<?php echo $id_forum_theme?>">

			<? if(!$is_bloque) { ?>
				<input name="bloquer" type="submit" id="bloquer" value="Bloquer"  class="link_button" />
			<? } else { ?>
				<input name="debloquer" type="submit" id="debloquer" value="D&eacute;bloquer" class="link_button" /> 
			<? } ?>
			
			<input name="supp" type="submit" id="Supp" value="Supprimer"  class="link_button" />
			
			
			<br><br>Changer de thème : <select name="id_forum_theme_admin"><?php
				$SQL = "SELECT `id_forum_theme`, `label`, `description`, `url` FROM `forum_theme` ORDER BY `order`";
				$result = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result))
				{
					die ("<li>ERROR : ".$result->getMessage());
					
				} else {
					while($forum_theme = $result->fetchRow())
					{
						echo "<option value=\"".$forum_theme->id_forum_theme."\" ".($forum_theme->id_forum_theme == $id_forum_theme ? "selected" : "").">".$forum_theme->label."</option>";
					}
				}
			?></select> <input name="change_theme" type="submit" value="Changer"  class="link_button" />
		</fieldset></form>
	<? } ?>
<?php
} //fin is_bloque


?>
</div>
</div>

<?php
pagefooter();
?>