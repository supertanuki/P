<?
/**
* Project: PRONOPLUS
* Description: Classement
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-08-16
* Version: 1.0
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

if(!$_GET[id]) HeaderRedirect('/');

$nb_players_per_page = 20;

$_GET[id] = $db->escapeSimple($_GET[id]);

// recherche classement
$SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`label`, `pp_class`.`last_id_matches`, `pp_class`.`type`, `pp_class`.`ids_league`
		FROM `pp_class`
		WHERE `pp_class`.`id_class`='".$_GET[id]."'";
$result = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	if(!$pp_class = $result->fetchRow())
	{
		HeaderRedirect('/');
	}
}


// recherche classement du joueur
if($_GET[search_joueur] && $_GET[rech_jpseudo]) {
	$SQL = "SELECT `pp_user`.`id_user`, `pp_class_user`.`class`
			FROM `pp_class_user` INNER JOIN `pp_user` ON `pp_user`.`id_user`=`pp_class_user`.`id_user`
			WHERE `pp_class_user`.`id_class`='".$_GET[id]."' AND `pp_class_user`.`id_matches`='".$pp_class->last_id_matches."' AND `pp_user`.`login`='".$db->escapeSimple(trim($_GET[rech_jpseudo]))."'";
	$result_class = $db->query($SQL);
	if(DB::isError($result_class))
	{
		die ("<li>ERROR : ".$result_class->getMessage());
		
	} else {
		if($pp_class_user = $result_class->fetchRow())
		{
			$page=(ceil($pp_class_user->class/$nb_players_per_page)-1)*$nb_players_per_page;
			header("Location: class.php?id=".$_GET[id]."&sqldep=".$page."&selj=".$pp_class_user->id_user."&rech_jpseudo=".$_GET[rech_jpseudo]."#joueur".$pp_class_user->id_user);
			exit;
		} else $class_introuvable=true;
	} 
}

// filtre amis ?
$friends_ids = '';
if($_GET[idl]*1)
{
	if($_GET[idl]*1 == -1)
	{
		// on recherche les id_user de tous les amis
		$SQL = "SELECT `pp_user_friends`.`id_user_friend`
				FROM `pp_user_friends`
				WHERE `pp_user_friends`.`id_user`='".$user->id_user."'
					AND `pp_user_friends`.`valide`='1'";
		$result_user_friends = $db->query($SQL);		
		if(DB::isError($result_user_friends)) die ("<li>$SQL<li>ERROR : ".$result_user_friends->getMessage());		
		while($pp_user_friends = $result_user_friends->fetchRow())
		{
			$friends_ids .= ($friends_ids != '' ? ',' : '') . $pp_user_friends->id_user_friend;
		}
		// on ajoute l'id de l'user courant
		$friends_ids .= ($friends_ids != '' ? ','.$user->id_user : '');
		
	} else {
		// on vérifie que la liste appartient pas à l'user courant
		$SQL = "SELECT `pp_user_listfriends`.`id_user_listfriends`, `pp_user_listfriends`.`label`
				FROM `pp_user_listfriends`
				WHERE `pp_user_listfriends`.`id_user`='".$user->id_user."'
				AND `pp_user_listfriends`.`id_user_listfriends`='".($_GET[idl]*1)."'";
		$result_user_listfriends = $db->query($SQL);
		if(DB::isError($result_user_listfriends)) die ("<li>ERROR : ".$result_user_listfriends->getMessage());
		if($result_user_listfriends->numRows())
		{
			// on recherche les id_user des amis de cette liste
			$SQL = "SELECT `pp_user_friends`.`id_user_friend`
					FROM `pp_user_friends`
					WHERE `pp_user_friends`.`id_user`='".$user->id_user."'
						AND `pp_user_friends`.`id_user_listfriends`='".($_GET[idl]*1)."'
						AND `pp_user_friends`.`valide`='1'";
			$result_user_friends = $db->query($SQL);		
			if(DB::isError($result_user_friends)) die ("<li>$SQL<li>ERROR : ".$result_user_friends->getMessage());		
			while($pp_user_friends = $result_user_friends->fetchRow())
			{
				$friends_ids .= ($friends_ids != '' ? ',' : '') . $pp_user_friends->id_user_friend;
			}
			// on ajoute l'id de l'user courant
			$friends_ids .= ($friends_ids != '' ? ','.$user->id_user : '');
		}
	}
}



// nombre de joueurs ?
$SQL = "SELECT COUNT(`id_user`) AS NBUSERS
		FROM `pp_class_user`
		WHERE `id_class`='".$_GET[id]."' AND `id_matches`='".$pp_class->last_id_matches."'
			".($friends_ids != '' ?  " AND id_user IN (".$friends_ids.")" : ""); // filtre amis
$result = $db->query($SQL);
if(DB::isError($result))
{
	die ("<li>$SQL<li>ERROR : ".$result->getMessage());
	
} else {
	if($pp_class_user = $result->fetchRow())
	{
		$nb_element = $pp_class_user->NBUSERS;
	}
}

$libelle_classement = formatDbData($pp_class->label);
pageheader($libelle_classement." | Prono+");

?>
<div id="content_fullscreen">
<?
// affichage des onglets
echo getOnglets('classement');
?>


<div id="content">
<h1 class="title_green"><?=$libelle_classement?></h1>
<br />

<?
if(!$pp_class->last_id_matches) {
?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p class="center"><strong>Ce classement n'est pas disponible pour l'instant.</strong></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<? } else { ?>




<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td colspan="2" style="padding:0;"><img src="/template/default/blocgrishaut.gif" border="0" /></td></tr>

<? if($user->id_user) { ?>
<tr>
<td style="background:#eee">
<? /* Filtre amis */
if($user->id_user)
{
	$totalfriends = 0;
	
	$SQL = "SELECT `pp_user_listfriends`.`id_user_listfriends`, `pp_user_listfriends`.`label`
			FROM `pp_user_listfriends`
			WHERE `pp_user_listfriends`.`id_user`='".$user->id_user."'
			ORDER BY `pp_user_listfriends`.`order`, `pp_user_listfriends`.`label`";
	$result_user_listfriends = $db->query($SQL);
	if(DB::isError($result_user_listfriends)) die ("<li>ERROR : ".$result_user_listfriends->getMessage());
	if($result_user_listfriends->numRows())
	{
		$str = '';
		$strlist = array();
		while($pp_user_listfriends = $result_user_listfriends->fetchRow())
		{
			// recherche nombre joueurs de la liste
			$SQL = "SELECT COUNT(`pp_user_friends`.`id_user_friend`) AS `nb_friends`
					FROM `pp_user_friends`
					WHERE `pp_user_friends`.`id_user`='".$user->id_user."'
						AND `pp_user_friends`.`id_user_listfriends`='".$pp_user_listfriends->id_user_listfriends."'
						AND `pp_user_friends`.`valide`='1'";
			$result_user_friends = $db->query($SQL);
			if(DB::isError($result_user_friends)) die ("<li>$SQL<li>ERROR : ".$result_user_friends->getMessage());
			if($pp_user_friends = $result_user_friends->fetchRow())
			{
				if($pp_user_friends->nb_friends)
				{
					$strlist[] = '<option value="'.$pp_user_listfriends->id_user_listfriends.'" '.($_GET[idl] == $pp_user_listfriends->id_user_listfriends ? 'selected="selected"' : '').'>'.htmlspecialchars($pp_user_listfriends->label).' ('.$pp_user_friends->nb_friends.')</option>';
					$totalfriends += $pp_user_friends->nb_friends;
				}
			}
		}

		$str .= '<form id="class_select_liste" method="get" action="/class.php" style="margin:0; padding:0">';
		$str .= '<input type="hidden" name="id" value="'.$_GET[id].'">';
		$str .= '<img src="/template/default/group.png" border="0" align="absmiddle" /> Filtrer le classement ';
		$str .= '<select name="idl">';
		$str .= '<option value="">Tout Prono+</option>';
		if(count($strlist) > 1) $str .= '<option value="-1" '.($_GET[idl] == -1 ? 'selected="selected"' : '').'>Tous mes amis ('.$totalfriends.')</option>';
		$str .= implode('', $strlist);
		$str .= '</select> <input type="submit" class="link_button" value="Ok"></form>';
		if($totalfriends > 0) echo $str;
	}
	
	if(!$totalfriends)
	{
		echo '<img src="/template/default/group.png" border="0" align="absmiddle" /> <b>Filtrer le classement : Tu n\'as aucun ami, tu ne peux pas filtrer le classement !</b><br />Si tu avais des amis, tu aurais pu afficher le classement de tes amis et toi. Pas de panique ! Clique sur le pseudo d\'un joueur pour voir son profil et éventuellement l\'ajouter en tant qu\'ami !';
	}
}
?>
</td>

<td align="right" style="background:#eee"><a href="/classement-evolution.php?id=<?=$pp_class->id_class?>" class="link_button"><img src="/template/default/evolution_ico.png" height="18" align="absmiddle" border="0" />&nbsp;Evolution au <?=$libelle_classement?></a></td>
</tr>

<tr><td colspan="2" style="padding:2px; background:url(/template/default/separplus.gif) #eee repeat-x" height="3"></td></tr>
<? } ?>

<tr>
<td valign="top" style="background:#eee">
<? /* Recherche classement d'un joueur */ ?>
<form method="get" action="class.php?id=<?=$_GET[id]?>#arcp" style="margin:0; padding:0">
<a name="arcp"></a>
<?
if($class_introuvable) echo "<font color=red><b>Pas de joueur trouvé !</b></font><br>"; ?>
<img src="/template/default/zoom.png" border="0" align="absmiddle" /> Rechercher le classement d'un joueur <input name="rech_jpseudo" type="text" size="12" maxlength="100" value="<?=$rech_jpseudo?htmlspecialchars(stripslashes($rech_jpseudo)):"son pseudo";?>" <?=!$rech_jpseudo?"onfocus=\"this.value=''\"":"";?>>&nbsp;<input type="hidden" name="search_joueur" value="1"><input type="hidden" name="id" value="<?=$_GET[id]?>"><input type="submit" name="search_joueur" value="Ok" class="link_button" />
</form>
</td>

<td align="right" valign="top" style="background:#eee">
<? /* Aller à mon classement */ ?>
<? if($user->id_user) { ?><a href="/class.php?id=<?=$_GET[id]?>&rech_jpseudo=<?=$user->login?>&search_joueur=1" class="link_button"><img src="/template/default/last.gif" border="0" align="absmiddle" /> Aller à mon classement</a><? } ?>&nbsp;
</td>
</tr>

<tr><td colspan="2" style="padding:0;"><img src="/template/default/blocgrisbas.gif" border="0" /></td></tr>
</table>
<br />


<a name="class"></a>
<?
if(!$_GET[sqldep]) $_GET[sqldep] = $_POST[sqldep];
if(!$_GET[sqldep]) $sqldep = 0; else $sqldep = $_GET[sqldep];

$extension = "&id=".$_GET[id].($_GET[idl] ? "&idl=".$_GET[idl] : "")."&ordre=".$_GET[ordre]."#class";
$pagego = "class.php";
pagination($pagego, $sqldep, $nb_element, $nb_players_per_page, $extension);
?><br />

<table width="100%" cellpadding="2" cellspacing="1">
  <tr> 
	<? if($friends_ids != '') { ?><th width="5%"><a href="javascript:alert('Mon rang dans le classement avec mes amis')" class="link_orange" title="Mon rang dans le classement avec mes amis">Rang amis</a></th><? } ?>
	<th width="5%"><a href="/class.php?id=<?=$_GET[id]?>&idl=<?=$_GET[idl]?>#class"  class="link_orange" title="Ordonner par nombre de points décroissant">Rang <? if($friends_ids != '') { echo 'réel'; } ?></a></th>
	<th width="19%" colspan="2"><a href="/class.php?id=<?=$_GET[id]?>&idl=<?=$_GET[idl]?>&ordre=login#class" class="link_orange" title="Ordonner par pseudo du joueur">Joueur</a></th>
	<th width="10%"><a href="/class.php?id=<?=$_GET[id]?>&idl=<?=$_GET[idl]?>#class"  class="link_orange" title="Ordonner par nombre de points décroissant">Points</a></th>
	<th width="10%" nowrap="nowrap"><a href="javascript:alert('Différence du nombre de points avec le joueur classé premier')" class="link_orange" title="Différence du nombre de points avec le joueur classé premier">Diff Points 1er</a></th>
	<th width="10%" nowrap="nowrap"><a href="javascript:alert('Différence du nombre de points avec le joueur classé au rang précédent')" class="link_orange" title="Différence du nombre de points avec le joueur classé au rang précédent">Diff Points -1</a></th>
	<th width="10%"><a href="/class.php?id=<?=$_GET[id]?>&idl=<?=$_GET[idl]?>&ordre=jj#class" class="link_orange" title="Ordonner par nombre de matchs joués décroissant">Nb matchs Jou&eacute;s</a></th>
	<th width="5%"><a href="/class.php?id=<?=$_GET[id]?>&idl=<?=$_GET[idl]?>&ordre=sj#class" class="link_orange" title="Ordonner par nombre de scores justes décroissant">Scores justes</a></th>
	<th width="5%"><a href="/class.php?id=<?=$_GET[id]?>&idl=<?=$_GET[idl]?>&ordre=rj#class" class="link_orange" title="Ordonner par nombre de résultats justes décroissant">Résultats justes</a></th>
	<th width="10%"><a href="/class.php?id=<?=$_GET[id]?>&idl=<?=$_GET[idl]?>&ordre=ev#class" class="link_orange" title="Ordonner par évolution décroissante">&Eacute;volution</a></th>
  </tr>
<?

// ordres
if($_GET[ordre]=="login") $sql_ordre="`pp_user`.`login`";
else if($_GET[ordre]=="jj") $sql_ordre="`pp_class_user`.`nb_matches` DESC, `pp_class_user`.`class`";
else if($_GET[ordre]=="sj") $sql_ordre="`pp_class_user`.`nb_score_ok` DESC, `pp_class_user`.`class`";
else if($_GET[ordre]=="rj") $sql_ordre="`pp_class_user`.`nb_result_ok` DESC, `pp_class_user`.`class`";
else if($_GET[ordre]=="ev") $sql_ordre="`pp_class_user`.`evolution` DESC, `pp_class_user`.`class`";
else $sql_ordre="`pp_class_user`.`class`";


$color1_a = "ffffff";
$color1_b = "eeeeee";
$color2_a = "eeeeee";
$color2_b = "ffffff";
$altern = 0;

// points du premier
$SQL = "SELECT `nb_points`
		FROM `pp_class_user`
		WHERE `id_class`='".$_GET[id]."' AND `id_matches`='".$pp_class->last_id_matches."' AND `class`=1";
$result = $db->query($SQL);
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage());
	
} else {
	if($pp_class_user = $result->fetchRow())
	{
		$ptsfirst = $pp_class_user->nb_points;
	}
}


// recherche de $prec_pts
$prec_pts = 0;
if(!$_GET[ordre] && $sqldep > 0)
{
	$SQL = "SELECT `pp_class_user`.`nb_points`
			FROM `pp_class_user`
			WHERE `pp_class_user`.`id_class`='".$_GET[id]."' AND `pp_class_user`.`id_matches`='".$pp_class->last_id_matches."'			
			ORDER BY `pp_class_user`.`class`
			LIMIT ".($sqldep-1).", 1";
	$result = $db->query($SQL);
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage());
		
	} else {
		if($pp_class_user = $result->fetchRow())
		{
			$prec_pts = $pp_class_user->nb_points;
		}
	}
}

       
// tableau
$SQL = "SELECT `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`, `pp_class_user`.`id_user`,
			`pp_class_user`.`class`, `pp_class_user`.`nb_score_ok`, `pp_class_user`.`nb_result_ok`, `pp_class_user`.`nb_matches`, `pp_class_user`.`nb_points`, `pp_class_user`.`evolution`,
			DATE_FORMAT(`".$table_class_user."`.`date_last_pronostic`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_last_pronostic_format`
		FROM `pp_class_user` INNER JOIN `pp_user` ON `pp_user`.`id_user`=`pp_class_user`.`id_user`
		WHERE `pp_class_user`.`id_class`='".$_GET[id]."' AND `pp_class_user`.`id_matches`='".$pp_class->last_id_matches."'
			".($friends_ids != '' ?  " AND `pp_class_user`.`id_user` IN (".$friends_ids.")" : "")."
			ORDER BY ".$sql_ordre."
			LIMIT ".($sqldep?$sqldep:0).", ".$nb_players_per_page;
$result = $db->query($SQL);
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage());
	
} else {
	$rang = ($sqldep?$sqldep:0);
	while($pp_class_user = $result->fetchRow())
	{  
		$rang++;
		$diff_points = $pp_class_user->nb_points - $ptsfirst;
		$diff_points_1 = $pp_class_user->nb_points - $prec_pts;
		$prec_pts = $pp_class_user->nb_points;
		   
		if($altern) {
			$class_line = 'ligne_grise';
			$altern = 0;
		} else {
			$class_line = 'ligne_blanche';
			$altern = 1;
		}
		
		if($_GET[selj] == $pp_class_user->id_user) $class_line = 'ligne_selected';
	?>
	<tr class="<?=$class_line?>" onmouseover="this.className='ligne_rollover'" onmouseout="this.className='<?=$class_line?>'" style="margin-top:20px;"> 
	
		<? if($friends_ids != '') { ?><td align="center"><? echo $rang; ?></td><? } ?>	
		
		<td align="center"><a name="joueur<?=$pp_class_user->id_user?>"></a><?=$pp_class_user->class?></td>
		<td align="center"><a href="/user.php?q=<?=urlencode(htmlspecialchars($pp_class_user->login))?>" class="link_orange">
		<?
		if($avatar = getAvatar($pp_class_user->id_user, $pp_class_user->avatar_key, $pp_class_user->avatar_ext, 'small')) {
		?>
			<img src="/avatars/<?=$avatar?>" height="30" width="30" border="0" />
		<? } else { ?>
			<img src="/template/default/_profil.png" height="30" width="30" border="0" />
		<? } ?>
		</a></td>	
		<td id="tr_line_<?=$pp_class_user->id_user?>"><a href="/user.php?q=<?=urlencode(htmlspecialchars($pp_class_user->login))?>" class="link_orange"><?
		if($pp_class_user->id_user != $user->id_user) {
			echo $pp_class_user->login;
		} else {
			echo "<font color=\"red\"><b>".$pp_class_user->login."</b></font>";
		}
		?></a></td>	
		<td align="center"><strong title="Dernière grille pronostiquée le <?=$pp_class_user->date_last_pronostic_format; ?>" style="cursor:help"><?=$pp_class_user->nb_points; ?></strong></td>
		<td align="center"><?=$pp_class_user->class > 1 ? $diff_points : '-'; ?></td>
		<td align="center"><?=$pp_class_user->class > 2 && !$_GET[ordre] ? $diff_points_1 : '-'; ?></td>		
		<td align="center"><?=$pp_class_user->nb_matches?></td>		
		<td align="center"><?=$pp_class_user->nb_score_ok?></td>		
		<td align="center"><?=$pp_class_user->nb_result_ok?></td>		
		<td align="center"><?=evolution_format($pp_class_user->evolution); ?></td>
	</tr>
	<?
	}
}
?>
</table><br />
<?
pagination($pagego, $sqldep, $nb_element, $nb_players_per_page, $extension);
?>


<? } ?>


<br /><br />


<?php
if($user->id_user)
{
	$content  = '';
	$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`image`,
			DATE_FORMAT(`pp_matches_user`.`date_creation`, '".$txtlang['AFF_DATE_SQL']."') AS `date_prono`
			FROM `pp_matches`
			INNER JOIN `pp_matches_user` ON `pp_matches_user`.`id_matches`=`pp_matches`.`id_matches` AND `pp_matches_user`.`id_user`='".$user->id_user."'
			INNER JOIN `pp_matches_class` ON `pp_matches_class`.`id_matches`=`pp_matches`.`id_matches` AND `pp_matches_class`.`id_class`='".$_GET[id]."'
			WHERE `pp_matches`.`is_calcul`='1'
			ORDER BY `pp_matches`.`date_calcul` DESC";
	$result_matches = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result_matches->getMessage());
		
	} else {
		if($result_matches->numRows())
		{
			$content .= '<table border="0" cellspacing="1" cellpadding="4">';
			$content .= '<tr>';
			$content .= '<th width="60%" colspan="2">Grilles de matchs</th>';
			$content .= '<th width="10%" nowrap>Mon classement</th>';
			$content .= '<th width="10%" nowrap>Mes points</th>';
			$content .= '<th width="10%" nowrap>Points moyens</th></tr>';
			
			
			$i = $nb_begin+1;
			
			while($pp_matches = $result_matches->fetchRow())
			{	
			
				$class_user = array();
				$SQL = "SELECT `class`, `nb_points`
						FROM `pp_class_user`
						WHERE `id_user`='".$user->id_user."' AND `id_class`=1 AND `id_matches`='".$pp_matches->id_matches."'";
				$result_class = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result_class))
				{
					die ("<li>ERROR : ".$result_class->getMessage());
					
				} else {	
					while($pp_class_user = $result_class->fetchRow())
					{
						$class_user['class'] = $pp_class_user->class;
						$class_user['nb_points'] = $pp_class_user->nb_points;
					}
				}
				
				$SQL = "SELECT AVG(`nb_points`) AS `sum_nb_points`
						FROM `pp_class_user`
						WHERE `id_class`=1 AND `id_matches`='".$pp_matches->id_matches."'";
				$result_class = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result_class))
				{
					die ("<li>ERROR : ".$result_class->getMessage());
					
				} else {	
					while($pp_class_user = $result_class->fetchRow())
					{
						$class_user['sum_nb_points'] = $pp_class_user->sum_nb_points;
					}
				}
						
				if($altern) {
					$class_line = 'ligne_grise';
					$altern = 0;
				} else {
					$class_line = 'ligne_blanche';
					$altern = 1;
				}

				$content .= '<tr class="'.$class_line.'">';
				$content .= '<td><a href="classj.php?id='.$pp_matches->id_matches.'"><img src="template/default/'.$pp_matches->image.'" style="border:solid 3px #eee;" height="50" width="35" /></a></td>';
				$content .= '<td><h3><a href="classj.php?id='.$pp_matches->id_matches.'" title="Aller au classement">'.formatDbData($pp_matches->label).'</a></h3>';
				$content .= 'Matchs pronostiqués le '.$pp_matches->date_prono;
				$content .= '</td>';
				$content .= '<td align="center">';
				$content .= '<a href="classj.php?id='.$pp_matches->id_matches.'&rech_jpseudo='.urlencode($user->login).'&search_joueur=1" class="link_orange" style="display:block; width:100%" title="Aller à mon classement">'.$class_user['class'].'</a>';
				$content .= '</td>';
				$content .= '<td align="center">';
				$content .= '<a href="classj.php?id='.$pp_matches->id_matches.'&rech_jpseudo='.urlencode($user->login).'&search_joueur=1" class="link_orange" style="display:block; width:100%" title="Aller à mon classement">'.$class_user['nb_points'].'</a>';
				$content .= '</td>';
				$content .= '<td align="center">';
				$content .= round($class_user['sum_nb_points']);
				$content .= '</td>';
				$content .= '</tr>';
			}
			$content .= '</table>';


            echo "<hr /><p class=\"message_error\">
                    Classement en cas d'égalité de points entre joueurs : le meilleur nombre de scores justes, sinon le meilleur nombre de résultats justes, sinon le premier qui a pronostiqué (date d'enregistrement du pronostic de la dernière grille faisant foi).
            </p>";
			
			echo '<h2 class="title_orange">Classements journées</h2>';
			echo '<div style="overflow:auto; height:200px; border:1px solid #ccc">';
			echo '<p style="margin:4px;">Les grilles de matchs qui ont compté au '.$libelle_classement.'</p>';
			echo $content;
			echo '</div><br />';
			
			echo '<p class="message_error"><b>Info :</b> Pour se qualifier à la <a href="/cup.php" class="link_orange">Coupe Prono+</a>, il vous faut être un bon pronostiqueur et être bien placé au Classement Mensuel précédant la Coupe : de la 1ère à la 16ème place du classement mensuel vous ouvre l\'accès à la Coupe Or ; de la 17ème à la 32ème places, vous jouerez dans la Coupe Argent et de la 33ème à la 48ème places, vous jouerez la Coupe Bronze.</p>';
		}
	}
}
?>







<?
// Les autres classements
$SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`type`, `pp_class`.`label`
		FROM `pp_class`
		WHERE `pp_class`.`last_id_matches` != 0 AND `pp_class`.`id_class`!='".$_GET[id]."'
		ORDER BY `pp_class`.`type`, `pp_class`.`order`";
$result = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	echo '<h2 class="title_orange">Les autres classements</h2>';	
	echo "<div><ul>";
	$type_current = '';
	while($pp_class_autres = $result->fetchRow())
	{
		if($type_current != $pp_class_autres->type)
		{
			if($type_current != '') echo '</ul>&nbsp;</li>';
			$type_current = $pp_class_autres->type;
			
			if($type_current == 'year') echo "<li><b>Classements annuels</b><ul>";
				else if($type_current == 'month') echo "<li><b>Classements mensuels</b><ul>";
		}
		echo "<li><a href=\"class.php?id=".$pp_class_autres->id_class."\" class=\"link_orange\">".formatDbData($pp_class_autres->label)."</a></li>";
	}
	echo "</ul></li>";
	echo "</ul></li>";
}


if($pp_class->ids_league)
{
	// Liste championnats
	$html = '';
	$SQL = "SELECT id_league, label, flag
			FROM `pp_league`
			WHERE `afficher_classement`='1'
			AND id_league IN (".$pp_class->ids_league.")
			ORDER BY `ordre`";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if($pp_league_info = $result->numRows())
		{
			echo "<br /><h2 class=\"title_orange\">Résultats des championnats</h2><ul>";
			while($pp_league_info = $result->fetchRow())
			{
				$html .= '<li><a href="/stats-classement.php?id='.$pp_league_info->id_league.'" class="link_orange" style="margin-bottom:4px;"><img src="/image/flags/'.$pp_league_info->flag.'" border="0" align="absmiddle" />&nbsp;'.$pp_league_info->label.'</a></li>';
			}
			echo $html;
			echo "</ul>";
		}
	}
}	
?>



</div>
</div>

<?
pagefooter();
?>