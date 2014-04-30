<?php
/**
* Project: PRONOPLUS
* Description: Details tour de coupe
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-08-14
* Version: 1.0
*/

if(!$_POST[id_cup] || !$_POST[cup_sub] || !$_POST[number_tour]) exit;



require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

$nbjoueurs = 16;

// recherche coupe
$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label` AS `label_cup`, `pp_class`.`label` AS `label_class`
		FROM `pp_cup`
		INNER JOIN `pp_class` ON `pp_cup`.`id_class_ref`=`pp_class`.`id_class`
		WHERE `id_cup`='".$_POST[id_cup]."'";
$result_cup = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_cup))
{
	die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
	
} else {
	if(!$pp_cup = $result_cup->fetchRow())
	{
		exit;
	
	} else {
		$SQL = "SELECT `pp_cup_matches`.`id_cup_matches`, `pp_cup_matches`.`id_matches`
				FROM `pp_cup_matches`
				WHERE `pp_cup_matches`.`id_cup`='".$pp_cup->id_cup."'
					AND `pp_cup_matches`.`number_tour`='".$_POST[number_tour]."'";
		$result_matches = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result_matches))
		{
			die ("<li>ERROR : ".$result_matches->getMessage()."<li>$SQL");
			
		} else {
			if(!$pp_cup_matches = $result_matches->fetchRow())
			{				
				exit;
			}
		}
	}
}



// matchs
$match_cup = array();
$SQL = "SELECT `pp_cup_match_opponents`.`id_user_host`, `pp_cup_match_opponents`.`id_user_visitor`, `pp_cup_match_opponents`.`number_tour`,
			`pp_cup_match_opponents`.`host_class`, `pp_cup_match_opponents`.`visitor_class`,
			`pp_cup_match_opponents`.`id_user_won`,
      `pp_cup_match_opponents`.`host_nb_points`, `pp_cup_match_opponents`.`host_nb_score_ok`, `pp_cup_match_opponents`.`host_nb_result_ok`,
      `pp_cup_match_opponents`.`visitor_nb_points`, `pp_cup_match_opponents`.`visitor_score_ok`, `pp_cup_match_opponents`.`visitor_nb_result_ok`,      
			`user_host`.`login` AS `login_host`, `user_host`.`avatar_key` AS `avatar_key_host`, `user_host`.`avatar_ext`  AS `avatar_ext_host`,
			`user_visitor`.`login` AS `login_visitor`, `user_visitor`.`avatar_key` AS `avatar_key_visitor`, `user_visitor`.`avatar_ext`  AS `avatar_ext_visitor`
		FROM `pp_cup_match_opponents`
		INNER JOIN `pp_user` AS `user_host` ON `user_host`.`id_user`=`pp_cup_match_opponents`.`id_user_host`
		INNER JOIN `pp_user` AS `user_visitor` ON `user_visitor`.`id_user`=`pp_cup_match_opponents`.`id_user_visitor`
		WHERE `pp_cup_match_opponents`.`cup_sub`='".$_POST[cup_sub]."'
			AND `pp_cup_match_opponents`.`number_tour`='".$_POST[number_tour]."'
			AND `pp_cup_match_opponents`.`id_cup`='".$_POST[id_cup]."'
		ORDER BY `pp_cup_match_opponents`.`num_match`, `pp_cup_match_opponents`.`host_class`";
$result_cup_user = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_cup_user))
{
	die ("<li>ERROR : ".$result_cup_user->getMessage()."<li>$SQL");
	
} else {
	while($cup_user = $result_cup_user->fetchRow())
	{
		$avatar_host = getAvatar($cup_user->id_user_host, $cup_user->avatar_key_host, $cup_user->avatar_ext_host, 'small');
		$avatar_host = $avatar_host ? '/avatars/'.$avatar_host : '/template/default/_profil.png';
		$cup_user->avatar_host = $avatar_host;
		
		$avatar_visitor = getAvatar($cup_user->id_user_visitor, $cup_user->avatar_key_visitor, $cup_user->avatar_ext_visitor, 'small');
		$avatar_visitor = $avatar_visitor ? '/avatars/'.$avatar_visitor : '/template/default/_profil.png';
		$cup_user->avatar_visitor = $avatar_visitor;
		
		$match_cup[] = $cup_user;
	}
}





/* classement journée*/

// recherche journée
$SQL = "SELECT `pp_matches`.`label`,
		`pp_info_country`.`label` AS `country`
		FROM `pp_matches`
		INNER JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_matches`.`id_info_country`
		WHERE `pp_matches`.`id_matches`='".$pp_cup_matches->id_matches."'";
$result = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	if(!$pp_matches = $result->fetchRow())
	{
		exit;
	}
}



$table_class_user = 'pp_class_user';
$table_match_user = 'pp_match_user';
$type_calcul = 'classement';
	





// recherche matchs
$matches = array();
$ids_match = "";
$SQL = "SELECT `pp_match`.`id_match`, `team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`, `pp_match`.`score`,
		DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`
		FROM `pp_match`
		INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
		INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
		WHERE `pp_match`.`id_matches`='".$pp_cup_matches->id_matches."'
		ORDER BY `pp_match`.`date_match`";
$result_match = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_match))
{
	die ("<li>ERROR : ".$result_match->getMessage());
	
} else {
	while($pp_match = $result_match->fetchRow())
	{
		$matches[$pp_match->id_match] = $pp_match;
		$ids_match .= ($ids_match!="" ? "," : "") . $pp_match->id_match;
	}
}


$libelles_tour = array(1=>'Huitièmes de finale', 2=>'Quarts de finale', 3=>'Demi-finales', 4=>'Finale');
?>



<div>
<p><strong><?php echo $libelles_tour[$_POST[number_tour]]?></strong></p>
<table width="100%" cellpadding="2" cellspacing="1">
<tr> 
<th width="5%" align="center">Matchs</th>
<th width="20%">&nbsp;</th>
<th width="5%" align="center">Score</th>
<th width="20%">&nbsp;</th>
<th width="10%" nowrap="nowrap">Pts moy</th>
<th width="40%">Tendances des r&eacute;sultats</th>
</tr>
<?php
$n=1;
foreach($matches as $id_match=>$match)
{
	$tendances = array();
	if($match->score != "R-R")
	{
		// calcul des tendances		
		$SQL = "SELECT COUNT(`id_user`) AS NBUSERS, `type_result`, ROUND(AVG(`pts_won`)) AS PTSWONAVG, ROUND(AVG(`pts`)) AS PTSAVG
		FROM `".$table_match_user."`
		WHERE `id_match`=".$id_match."
		GROUP BY `type_result`";
		$result_score = $db->query($SQL);
		if(DB::isError($result))
		{
			die ("<li>ERROR : ".$result_score->getMessage());
			
		} else {
			while($pp_match_user = $result_score->fetchRow())
			{
				$tendances[$pp_match_user->type_result] = $pp_match_user;
			}
		}
	}
	$nb_joueurs_tendance = $tendances[1]->NBUSERS + $tendances[2]->NBUSERS + $tendances[3]->NBUSERS;
?>
	<tr> 
	<td bgcolor="#eeeeee" align="center"><b>M<?php echo $n ?></b></td>
	
	<td align="right"><?php echo $match->team_host_label; ?></td>
	<td bgcolor="#eeeeee" align="center">
	<?php if($match->score == "R-R") {
		echo "<font color=\"red\">Annul&eacute;</font>";
	} else {
		echo $match->score;
	} ?>
	</td>
	<td><?php echo $match->team_visitor_label; ?></td>
	<td align="center">
	<?php
	if($match->score != "R-R" && $nb_joueurs_tendance>0) {
		$ptsmoy = round(($tendances[1]->PTSWONAVG * $tendances[1]->NBUSERS + $tendances[2]->PTSWONAVG * $tendances[2]->NBUSERS + $tendances[3]->PTSWONAVG * $tendances[3]->NBUSERS) / $nb_joueurs_tendance);
		echo $ptsmoy;
	} else echo '-'; ?>
	</td>
	<td style="padding:0">
	<?	
	if($match->score != "R-R" && $nb_joueurs_tendance>0) { ?>
	<table width="100%" cellpadding="2">
		<tr>
			<?php $totalpercent = 0; ?>
			<?php $percent = round(100 * $tendances[1]->NBUSERS / $nb_joueurs_tendance); ?>
			<?php if($percent) { ?><td class="result_gagne" width="<?php echo $percent?>%"><a href="javascript:" style="color:#fff; text-decoration:none; font-weight:bold;" title="<?php echo $percent?>% des joueurs ont trouvé le bon score. Ils ont misé une moyenne de <?php echo $tendances[1]->PTSAVG?> points et ont gagné une moyenne de <?php echo $tendances[1]->PTSWONAVG?> points."><?php echo $percent?>%</a></td><?php } ?>
			<?php $percent = round(100 * $tendances[2]->NBUSERS / $nb_joueurs_tendance); ?>
			<?php if($percent) { ?><td class="result_nul" width="<?php echo $percent?>%"><a href="javascript:" style="color:#fff; text-decoration:none; font-weight:bold;" title="<?php echo $percent?>% des joueurs ont trouvé le bon résultat. Ils ont misé une moyenne de <?php echo $tendances[2]->PTSAVG?> points et ont gagné une moyenne de <?php echo $tendances[2]->PTSWONAVG?> points."><?php echo $percent?>%</a></td><?php } ?>
			<?php $percent = round(100 * $tendances[3]->NBUSERS / $nb_joueurs_tendance); ?>
			<?php if($percent) { ?><td class="result_defaite" width="<?php echo $percent?>%"><a href="javascript:" style="color:#fff; text-decoration:none; font-weight:bold;" title="<?php echo $percent?>% des joueurs ont mal joué ce match et ont misé une moyenne de <?php echo $tendances[3]->PTSAVG?> points."><?php echo $percent?>%</a></td><?php } ?>
		</tr>
	</table>
	<?php } else echo '&nbsp;'; ?>
	</td>
<?php
	$n++;
}
?>
</table><br /><hr>


<table width="100%" cellpadding="2" cellspacing="1" border="0">
<tr>
<th colspan="2" width="20%" align="center">Joueur</th>
<th width="10%" align="center">Classement de référence</th>
<th width="10%" align="center">Points</th>
<th width="10%" align="center">Scores justes</th>
<th width="10%" align="center">Résultats justes</th>
<?php
$i=1;
foreach($matches as $id_match=>$match)
{
?>
	<th width="5%" align="center"><a href="javascript:" style="color:#000; text-decoration:none; font-weight:bold;" title="<?php echo $matches[$id_match]->team_host_label.' '.$matches[$id_match]->score.' '.$matches[$id_match]->team_visitor_label?>">M<?php echo $i?><br />
	<?php if($match->score == "R-R") {
		echo "<span style=\"color:red\">Ann.</span>";
	} else {
		echo $match->score;
	} ?></a>
	</th>
	<?php
	$i++;
}
?>
</tr>
<?php
$color1_a = "bbbbbb";
$color1_b = "aaaaaa";
$color2_a = "eeeeee";
$color2_b = "ffffff";
$altern = 0;
	

// tableau
if($_POST[number_tour]==1)
{
	$tableau = array(0,7,4,3,2,5,6,1);
	
} else if($_POST[number_tour]==2) {
	$tableau = array(0,1,2,3);
	
} else if($_POST[number_tour]==3) {
	$tableau = array(0,1);
	
} else if($_POST[number_tour]==4) {
	$tableau = array(0);
}

foreach($tableau as $key) //liste des matchs_opponents
{   
	$match_cup_detail = $match_cup[$key];
	
	$match_user = array();
	$SQL = "SELECT `id_user`, `id_match`, `score`, `pts`, `type_result`, `pts_won`
	FROM `".$table_match_user."`
	WHERE (`id_user`='".$match_cup_detail->id_user_host."' OR `id_user`='".$match_cup_detail->id_user_visitor."')
		AND `id_match` IN (".$ids_match.")";
	$result_score = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result_score->getMessage());
		
	} else {
		while($pp_match_user = $result_score->fetchRow())
		{
			$match_user[$pp_match_user->id_user][$pp_match_user->id_match] = $pp_match_user;
		}
	}
	
	if($altern) {
		$class_line = 'ligne_grise';
		$altern = 0;
	} else {
		$class_line = 'ligne_blanche';
		$altern = 1;
	}

  
	for($i_opp=0; $i_opp<=1; $i_opp++)
	{
		$souligne_winner=false;
		if($i_opp==0)
		{
			$id_user_line = $match_cup_detail->id_user_host;
			$login_line = $match_cup_detail->login_host;
			$avatar_line = $match_cup_detail->avatar_host;
			$class_line = $match_cup_detail->host_class;
			$won_line = $match_cup_detail->id_user_won;
			$nb_points_line = $match_cup_detail->host_nb_points;
			$nb_score_ok = $match_cup_detail->host_nb_score_ok;
			$nb_result_ok = $match_cup_detail->host_nb_result_ok;
      
      if($match_cup_detail->visitor_nb_points == $match_cup_detail->host_nb_points
          && $match_cup_detail->visitor_score_ok == $match_cup_detail->host_nb_score_ok
          && $match_cup_detail->visitor_nb_result_ok == $match_cup_detail->host_nb_result_ok
          && $match_cup_detail->host_class < $match_cup_detail->visitor_class)
            $souligne_winner=true;
      
		} else {
			$id_user_line = $match_cup_detail->id_user_visitor;
			$login_line = $match_cup_detail->login_visitor;
			$avatar_line = $match_cup_detail->avatar_visitor;
			$class_line = $match_cup_detail->visitor_class;
			$won_line = $match_cup_detail->id_user_won;
			$nb_points_line = $match_cup_detail->visitor_nb_points;
			$nb_score_ok = $match_cup_detail->visitor_score_ok;
			$nb_result_ok = $match_cup_detail->visitor_nb_result_ok;

      if($match_cup_detail->visitor_nb_points == $match_cup_detail->host_nb_points
          && $match_cup_detail->visitor_score_ok == $match_cup_detail->host_nb_score_ok
          && $match_cup_detail->visitor_nb_result_ok == $match_cup_detail->host_nb_result_ok
          && $match_cup_detail->host_class > $match_cup_detail->visitor_class)
            $souligne_winner=true;
		}
    
    
?>

<tr class='<?php echo $class_line?>' onmouseover="this.className='ligne_rollover'" onmouseout="this.className='<?php echo $class_line?>'">
<td <?php echo $i_opp==1 ? 'style="border-bottom:1px solid #999;"':'' ?> align="center" width="5%"><a href="/user.php?q=<?php echo urlencode(htmlspecialchars($login_line))?>" class="link_orange"><img src="<?php echo $avatar_line?>" width="29" height="29" border="0" hspace="5" /></a></td>
<td <?php echo $i_opp==1 ? 'style="border-bottom:1px solid #999;"':'' ?>><a href="/user.php?q=<?php echo urlencode(htmlspecialchars($login_line))?>" class="link_orange">
<?php
if($id_user_line != $user->id_user) {
	echo ($won_line==$id_user_line ? '<strong><u>'.$login_line.'</u></strong>' : $login_line);
} else {
	echo "<font color=\"red\"><b>".($won_line==$id_user_line ? '<strong><u>'.$login_line.'</u></strong>' : $login_line)."</b></font>";
}
?></a></td>
<td <?php echo $i_opp==1 ? 'style="border-bottom:1px solid #999;"':'' ?>  align="center"><?php echo $souligne_winner ? '<b><u>' : ''; ?><?php echo $class_line.($class_line>1 ? 'ème' : 'er')?><?php echo $souligne_winner ? '</u></b>' : ''; ?></td>
<td <?php echo $i_opp==1 ? 'style="border-bottom:1px solid #999;"':'' ?> align="center"><strong><?php echo $nb_points_line?></strong></td>
<td <?php echo $i_opp==1 ? 'style="border-bottom:1px solid #999;"':'' ?> align="center"><?php echo $nb_score_ok?></td>
<td <?php echo $i_opp==1 ? 'style="border-bottom:1px solid #999;"':'' ?> align="center"><?php echo $nb_result_ok?></td>
<?php
foreach($matches as $id_match=>$match)
{
	if(!$match_user[$id_user_line][$id_match]->score)
	{
?>
<td align="center" style="background-color:#ddd"><a href="javascript:" style="color:#000000; text-decoration:none; font-weight:bold;" title="Match non joué">&nbsp;-&nbsp;</a></td>
<?php
	} else {
		if($match_user[$id_user_line][$id_match]->type_result == 1) {
			$color = 'result_gagne';
			$fact = 10;
		
		} elseif($match_user[$id_user_line][$id_match]->type_result == 2) {
			$color = 'result_nul';
			$fact = 3;
		
		} elseif($match_user[$id_user_line][$id_match]->type_result == 3) {
			$color = 'result_defaite';
			$fact = 0;
		
		} elseif($match_user[$id_user_line][$id_match]->type_result == 4) {
			$color="result_neutre";
			$fact = 1;
		
		} else {
			$color="result_gris";
			$fact = 0;
		}
?>
<td align="center" class="<?php echo $color?>">
<?php if($match_user[$id_user_line][$id_match]->type_result > 0) { ?>
	<a href="javascript:" style="color:#fff; text-decoration:none; font-weight:bold;" title="<?php echo $match_user[$id_user_line][$id_match]->team_host_label.' '.$match_user[$id_user_line][$id_match]->score.' '.$match_user[$id_user_line][$id_match]->team_visitor_label.". Points gagnés : ".$match_user[$id_user_line][$id_match]->pts." x ".$fact." = ".$match_user[$id_user_line][$id_match]->pts_won?>"><?php echo $match_user[$id_user_line][$id_match]->score?></a>
<?php
} else {
	echo $match_user[$id_user_line][$id_match]->score;
}
?>
</td>
<?php
	}
}
?>
</tr>
<?php
	}
?>
<tr> 
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td colspan="<?php echo count($matches)?>">&nbsp;</td>
</tr>
<?php
}
?>


</table><br />
</div>