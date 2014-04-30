<?php
/**
* Project: PRONOPLUS
* Description: Pronostiquer
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2010-04-04
* Version: 1.0
*/

require_once('../init.php');
require_once('functions-iphone.php');
require_once('../mainfunctions.php');
require_once('../contentfunctions.php');

if(!$user = user_authentificate()) HeaderRedirect('login.php?redirect=pronostiquer.php?id='.$_GET[id]);

/* traitement pronostics */
$error = "";
$oksave = false;

if($_POST[save_prono] && $_POST[id] && $user)
{
	$_GET[id] = $_POST[id];
	
	if(!is_array($_POST['id_match']))  die("Pas de matchs ?");
	
	
	$SQL = "SELECT `pp_matches`.`label`, `pp_matches`.`id_cup_matches`
			FROM `pp_matches`
			WHERE `pp_matches`.`id_matches`='".$_POST[id]."' AND `pp_matches`.`date_last_match`>NOW()";
	$result = $db->query($SQL);
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if(!$pp_matches = $result->fetchRow())
		{
			HeaderRedirect('/');
			
		} else {
			
			// si coupe => vérifier que l'utilisateur est qualifié :
			if($pp_matches->id_cup_matches)
			{
				$SQL = "SELECT `pp_cup_matches`.`id_cup_matches`
						FROM `pp_cup_matches`
						INNER JOIN `pp_cup_match_opponents` ON `pp_cup_match_opponents`.`id_cup_matches`=`pp_cup_matches`.`id_cup_matches`
						WHERE `pp_cup_matches`.`id_cup_matches`='".$pp_matches->id_cup_matches."'
						AND (`pp_cup_match_opponents`.`id_user_host`='".$user->id_user."' OR `pp_cup_match_opponents`.`id_user_visitor`='".$user->id_user."')";
				$result_cup = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result_cup))
				{
					die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
					
				} else if(!$result_cup->numRows()) HeaderRedirect('/');
			}
		}
	}

	$matches = array();
	$ids_match = array();
	$nb_match_valide = 0;
	
	$SQL = "SELECT `pp_match`.`id_match`,
			`team_host`.`label` AS `team_host_label`,
			`team_visitor`.`label` AS `team_visitor_label`,
			TIMEDIFF(NOW(), `pp_match`.`date_match`) AS `diff_date_match`
			FROM `pp_match`
			INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
			INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
			WHERE `pp_match`.`id_matches`='".$db->escapeSimple($_POST[id])."'
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
			$ids_match[] = $pp_match->id_match;
			if(substr($pp_match->diff_date_match, 0, 1) == '-') $nb_match_valide++;
		}
	}
	
	// vérification que tous les matchs sont là
	$id_matches = $match->id_matches;
	foreach($_POST['id_match'] as $id_match=>$value)
	{
		if(!$matches[$id_match]->id_match) die("On ne trouve pas tous les matchs !");
	}
	
	if(count($_POST['id_match']) != count($matches)) die("On ne trouve pas tous les matchs !");

	$misetotale = 0;
	
	reset($matches);
	foreach($matches as $match) if(substr($match->diff_date_match, 0, 1) == '-')
	{
		$labelmatch = formatDbData($match->team_host_label." - ".$match->team_visitor_label);
		$score = $_POST['score_match'][$_POST['id_match'][$match->id_match]];
		$mise = 1 * $_POST['mise_match'][$_POST['id_match'][$match->id_match]];
		
		if($score!='' && $mise>0 && substr($match->diff_date_match, 0, 1) != '-')
		{
			$error .= "Le match ".$labelmatch." se joue ou s'est déjà joué !<br />";
		}
		
		if(!ereg('^[0-9]\-[0-9]$', $score))
		{
			$error .= "Le score du match ".$labelmatch." n'est pas correct !<br />";
		}
		
		if($mise < 5 || $mise > 50)
		{
			$error .= "La mise du match ".$labelmatch." n'est pas correcte !<br />";
		}
		
		$misetotale += $mise;
	}
	
	// Récupération des pronostics effectués
	$score = array();
	$SQL = "SELECT `id_match`, `score`, `pts`
			FROM `pp_match_user` WHERE `id_user`='".$user->id_user."' AND `id_match` IN (".implode(',', $ids_match).")";
	$result_match = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_match))
	{
		die ("<li>ERROR : ".$result_match->getMessage());
		
	} else {
		while($pp_match_user = $result_match->fetchRow())
		{
			$score[$pp_match_user->id_match] = $pp_match_user;
		}
		
		if(count($score)) foreach($matches as $match) if(substr($match->diff_date_match, 0, 1) != '-')
		{
			$misetotale += $score[$match->id_match]->pts ? $score[$match->id_match]->pts : 0;
		}
	}
	
	$points_a_miser = $nb_match_valide * 10;
	if(count($score))
	{
		$points_a_miser = count($score) * 10;
	}
	
	$nbmatchs = count($matches);	
	if($misetotale != $points_a_miser)
	{
		$error .= "Le total de vos mises n'est pas correct !<br />";
	}
	
	$match_ok = false;
	
	// pas d'erreur ? on enregistre !
	if($error=="")
	{
		
		reset($matches);
		foreach($matches as $match) if(substr($match->diff_date_match, 0, 1) == '-')
		{
			$score = $_POST['score_match'][$_POST['id_match'][$match->id_match]];
			$mise = 1 * $_POST['mise_match'][$_POST['id_match'][$match->id_match]];
			
			$SQL = "UPDATE `pp_match_user` SET `score`='".$db->escapeSimple($score)."', `pts`='".$db->escapeSimple($mise)."', `date_update`=NOW()
					WHERE `id_user`='".$user->id_user."' AND `id_match`='".$match->id_match."'";
			$result = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result))
			{
				die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
				
			} else {
				if(!$db->affectedRows())
				{
					$SQL = "INSERT INTO `pp_match_user`(`id_user`, `id_match`, `score`, `pts`, `date_creation`)
							VALUES('".$user->id_user."', '".$match->id_match."', '".$db->escapeSimple($score)."', '".$db->escapeSimple($mise)."', NOW())";
					$result = $db->query($SQL);
					//echo "<li>$SQL";
					if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
					if($db->affectedRows()) $match_ok = true;
				
				} else $match_ok = true;
			}
		}
		
		if($match_ok)
		{
			// flag pronostics matches
			$SQL = "UPDATE `pp_matches_user` SET `date_update`=NOW()
					WHERE `id_user`='".$user->id_user."' AND `id_matches`='".$_POST[id]."'";
			$result = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result))
			{
				die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
				
			} else {
				if(!$db->affectedRows())
				{
					$SQL = "INSERT INTO `pp_matches_user`(`id_user`, `id_matches`, `date_creation`)
							VALUES('".$user->id_user."', '".$_POST[id]."', NOW())";
					$result = $db->query($SQL);
					//echo "<li>$SQL";
					if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
				}
			}
			
			$oksave = true;
			?>
			<script language="javascript">
			<!--
			location.href='index.php?msg=p';
			-->
			</script>
			<?php
		}
	}
}
/* traitement pronostics */


if(!$_GET[id]) HeaderRedirect('index.php');

$matches = array();
$ids_match = array();
$nb_matchs = 0;

$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`id_cup_matches`,
			`pp_matches`.`image`, `pp_matches`.`date_first_match`,
			DATE_FORMAT(`pp_matches`.`date_first_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_first_match_format`,
			DATE_FORMAT(`pp_matches`.`date_last_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_last_match_format`,
			TIMEDIFF(NOW(), `pp_matches`.`date_first_match`) AS `diff_date_first_match`,
			TIMEDIFF(NOW(), `pp_matches`.`date_last_match`) AS `diff_date_last_match`
		FROM `pp_matches`
		WHERE `pp_matches`.`id_matches`='".$_GET[id]."' AND `pp_matches`.`date_last_match`>NOW()";
$result = $db->query($SQL);
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	if(!$pp_matches = $result->fetchRow())
	{
		HeaderRedirect('/');
		
	} else {
		
		// si coupe => vérifier que l'utilisateur est qualifié :
		if($pp_matches->id_cup_matches)
		{
			$SQL = "SELECT `pp_cup_matches`.`id_cup_matches`
					FROM `pp_cup_matches`
					INNER JOIN `pp_cup_match_opponents` ON `pp_cup_match_opponents`.`id_cup_matches`=`pp_cup_matches`.`id_cup_matches`
					WHERE `pp_cup_matches`.`id_cup_matches`='".$pp_matches->id_cup_matches."'
					AND (`pp_cup_match_opponents`.`id_user_host`='".$user->id_user."' OR `pp_cup_match_opponents`.`id_user_visitor`='".$user->id_user."')";
			$result_cup = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_cup))
			{
				die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
				
			} else if(!$result_cup->numRows()) HeaderRedirect('/');
			
			// info sur l'aversaire
			$SQL = "SELECT `pp_cup_match_opponents`.`number_tour`,`pp_cup_match_opponents`.`cup_sub`,
						`pp_cup_match_opponents`.`id_user_host`, `pp_cup_match_opponents`.`id_user_visitor`,
						`pp_cup_match_opponents`.`host_class`, `pp_cup_match_opponents`.`visitor_class`,
						`pp_cup_match_opponents`.`id_user_won`, `pp_cup_match_opponents`.`visitor_nb_points`, `pp_cup_match_opponents`.`host_nb_points`,
						`user_host`.`login` AS `login_host`, `user_host`.`avatar_key` AS `avatar_key_host`, `user_host`.`avatar_ext`  AS `avatar_ext_host`,
						`user_visitor`.`login` AS `login_visitor`, `user_visitor`.`avatar_key` AS `avatar_key_visitor`, `user_visitor`.`avatar_ext`  AS `avatar_ext_visitor`
					FROM `pp_cup_match_opponents`
					INNER JOIN `pp_user` AS `user_host` ON `user_host`.`id_user`=`pp_cup_match_opponents`.`id_user_host`
					INNER JOIN `pp_user` AS `user_visitor` ON `user_visitor`.`id_user`=`pp_cup_match_opponents`.`id_user_visitor`
					WHERE `id_cup_matches`='".$pp_matches->id_cup_matches."'
					AND (`id_user_host`='".$user->id_user."' OR `id_user_visitor`='".$user->id_user."')";
			$result_cup_user = $db->query($SQL);
			if(DB::isError($result_cup_user))
			{
				die ("<li>ERROR : ".$result_cup_user->getMessage()."<li>$SQL");						
			} else {
				if(!$cup_user = $result_cup_user->fetchRow()) HeaderRedirect('index.php');
			}
		}
		
		
		$SQL = "SELECT `pp_match`.`id_match`,
				`team_host`.`label` AS `team_host_label`, `team_host`.`flag` AS `team_host_flag`,
				`team_visitor`.`label` AS `team_visitor_label`, `team_visitor`.`flag` AS `team_visitor_flag`,
				`pp_match`.`date_match`, `pp_match`.`score`,
				DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_TIME_SQL']."') AS `time_match_format`,
				TIMEDIFF(NOW(), `pp_match`.`date_match`) AS `diff_date_match`,
				YEAR(`pp_match`.`date_match`) AS `date_match_year`,
				MONTH(`pp_match`.`date_match`) AS `date_match_month`,
				DAYOFMONTH(`pp_match`.`date_match`) AS `date_match_day`,
				DAYOFWEEK(`pp_match`.`date_match`) AS `date_match_dayweek`
				FROM `pp_match`
				INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
				INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
				WHERE `pp_match`.`id_matches`='".$_GET[id]."'
				ORDER BY `pp_match`.`date_match`";
		$result_match = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result_match))
		{
			die ("<li>ERROR : ".$result_match->getMessage());
			
		} else {
			while($pp_match = $result_match->fetchRow())
			{
				$matches[] = $pp_match;
				$ids_match[] = $pp_match->id_match;
				$nb_matchs_total ++;
				$nb_matchs += substr($pp_match->diff_date_match, 0, 1)=='-' ? 1 : 0;
			}
		}
	}
}

if(!$nb_matchs) HeaderRedirect('/');




// Récupération des pronostics effectués
$score = array();
if($user)
{	
	$SQL = "SELECT `id_match`, `score`, `pts`
			FROM `pp_match_user` WHERE `id_user`='".$user->id_user."' AND `id_match` IN (".implode(',', $ids_match).")";
	$result_match = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_match))
	{
		die ("<li>ERROR : ".$result_match->getMessage());
		
	} else {
		while($pp_match_user = $result_match->fetchRow())
		{
			$score[$pp_match_user->id_match] = $pp_match_user;
		}
	}
}


echo pp_iphone_header('Pronostiquer', $is_menu=false, $is_retour=true);

	?>
	<ul id="msgbugsafari" class="pageitem">
		<li class="textbox">
			<div>BUG sur Safari Mobile : impossible de pronostiquer... En attendant que ce bug soit résolu, essayez d'installer Chrome sur votre mobile ;)</div>
		</li>
	</ul>
	
	<form id="pronoform" method="post" action="pronostiquer.php" onsubmit="return saveprono()" style="display:none">
	<input type="hidden" name="save_prono" value="1" />
	<input type="hidden" name="id" value="<?php echo $_GET[id]?>" />
	<ul class="pageitem">
		<li class="textbox">
			<span class="header">Pronostiquer <?php echo $pp_matches->label; ?></span>
		</li>
		
		<?php
		if($cup_user)
		{
			$avatar_host = getAvatar($cup_user->id_user_host, $cup_user->avatar_key_host, $cup_user->avatar_ext_host, 'small');
			$avatar_host = $avatar_host ? '/avatars/'.$avatar_host : '/template/default/_profil.png';
			
			$avatar_visitor = getAvatar($cup_user->id_user_visitor, $cup_user->avatar_key_visitor, $cup_user->avatar_ext_visitor, 'small');
			$avatar_visitor = $avatar_visitor ? '/avatars/'.$avatar_visitor : '/template/default/_profil.png';
			?>
			<li class="textbox">
				<table width="100%" cellpadding="2" cellspacing="1">
					<tr>
						<th align="center" colspan="2">Opposition</th>
						<th align="center">Classement</th>
					</tr>
					<tr<?php echo $cup_user->id_user_host == $cup_user->id_user_won ? ' style="background:yellow"' : ''; ?>>
						<td align="center" width="10%"><img src="<?php echo $avatar_host; ?>" height="30" width="30" border="0" align="absmiddle" /></td>
						<td width="50%"><?php echo $cup_user->login_host; ?></td>
						<td align="center" width="40%"><?php echo $cup_user->host_class . '<sup>' . ($cup_user->host_class > 1 ? 'ème' : 'er') . '</sup>'; ?></td>
					</tr>
					<tr<?php echo $cup_user->id_user_visitor == $cup_user->id_user_won ? ' style="background:yellow"' : ''; ?>>
						<td align="center"><img src="<?php echo $avatar_visitor; ?>" height="30" width="30" border="0" align="absmiddle" /></td>
						<td><?php echo $cup_user->login_visitor; ?></td>
						<td align="center"><?php echo $cup_user->visitor_class . '<sup>' . ($cup_user->visitor_class > 1 ? 'ème' : 'er') . '</sup>'; ?></td>
					</tr>
				</table>
			</li>
			<?php
		}
		?>
		
		<li class="textbox">
			<?php if($error) { ?>
				<p style="color:red; font-weight:bold;"><?php echo $error; ?></p>		
			<?php } ?>
			
			
			
			<table width="100%" border="0" cellpadding="2" cellspacing="1">
				<tr>
					<th>&nbsp;</th>
					<th>Score</th>
					<th>&nbsp;</th>
					<th>Mise</th>
				</tr>
			<?php
			$date_tmp = "";
			if(is_array($matches)) foreach($matches as $i=>$match)
			{
				$i = $i+1;
				
				if($match->date_match != $date_tmp)
				{	
			?>
				<tr>
					<td colspan="4" style="color:#888">
					<?php
					if(substr($date_tmp, 0, 10) != substr($match->date_match, 0, 10))
					{
						$dayweek = $match->date_match_dayweek;
						if($dayweek==1) $dayweek=8;
						$dayweek = $dayweek-2;		
						echo get_date_complete($dayweek, $match->date_match_day, $match->date_match_month-1, $match->date_match_year);
					}
					?>
					&agrave; <?php echo $match->time_match_format?>
					</td>
				</tr>

			<?php
					$date_tmp = $match->date_match;
				}

				if(substr($match->diff_date_match, 0, 1) == '-')
				{
			?>
				<input type="hidden" name="id_match[<?php echo $match->id_match?>]" value="<?php echo $i?>" />
				<tr>
					<td align="right"><span class="overflowed"><?php echo formatDbData($match->team_host_label)?></span></td>
					
					<td align="center" nowrap="nowrap">
						<input id="score_match_<?php echo $i?>" name="score_match[<?php echo $i?>]" type="hidden" value="<?php echo $score[$match->id_match]->score ? $score[$match->id_match]->score : '0-0'; ?>" />
						<select id="score_team_host_<?php echo $i?>" onchange="setScore(<?php echo $i?>)">
							<?php
							for($j=0; $j<=9; $j++)
							{
								?><option value="<?php echo $j; ?>" <?php echo substr($score[$match->id_match]->score, 0, 1) == $j ? 'selected="selected"' : ''; ?>><?php echo $j; ?></option><?php
							}
							?>
						</select>
						<select id="score_team_visitor_<?php echo $i?>" onchange="setScore(<?php echo $i?>)">
							<?php
							for($j=0; $j<=9; $j++)
							{
								?><option value="<?php echo $j; ?>" <?php echo substr($score[$match->id_match]->score, 2, 1) == $j ? 'selected="selected"' : ''; ?>><?php echo $j; ?></option><?php
							}
							?>
						</select>
					</td>
					
					<td><span class="overflowed"><?php echo formatDbData($match->team_visitor_label)?></span></td>
					
					<td align="right">
						<select id="mise_match_<?php echo $i?>" name="mise_match[<?php echo $i?>]" onchange="updateMise();">
							<?php
							for($j=5; $j<=50; $j++)
							{
								?><option value="<?php echo $j; ?>" <?php echo !$score[$match->id_match]->pts && $j == 10 || $score[$match->id_match]->pts == $j ? 'selected="selected"' : ''; ?>><?php echo $j; ?></option><?php
							}
							?>
						</select>
					</td>
				</tr>

			<?php } else { ?>

				<input type="hidden" name="id_match[<?php echo $match->id_match?>]" value="<?php echo $i?>" />
				<input type="hidden" id="mise_match_<?php echo $i?>" name="mise_match[<?php echo $i?>]" value="<?php echo $score[$match->id_match]->pts ? $score[$match->id_match]->pts : '0'; ?>" />
				<tr>
					<td align="right"><?php echo formatDbData($match->team_host_label)?></td>
					<td align="center"><?php echo $score[$match->id_match]->score ? $score[$match->id_match]->score : '-'; ?></td>
					<td><?php echo formatDbData($match->team_visitor_label)?></td>
					<td align="right"><?php echo $score[$match->id_match]->pts ? $score[$match->id_match]->pts : '-'; ?></td>
				</tr>
			<?php } ?>
				

			<?php
			}
			?>
				<tr>
					<td colspan="3" align="right">Points misés</td>
					<td align="right"><span id="points_mises"></span></td>
				</tr>
				<tr class="noborder">
					<td colspan="3" align="right">Points en trop</td>
					<td align="right"><span id="points_trop" style="color:red"></span></td>
				</tr>
				<tr class="noborder">
					<td colspan="3" align="right">Points restants</td>
					<td align="right"><span id="points_restants" style="color:green"></span></td>
				</tr>
			</table>

		</li>		
		<li class="button"><input name="Submit input" type="submit" value="Valider" /></li>
	</ul>
	</form>
	
	<?php
	$points_mises = 0;
	$nb_matchs_joues = 0;
	for($i=1; $i<=$nb_matchs_total; $i++)
	{
		if($score[$matches[$i-1]->id_match]->pts >= 5)
		{
			if(substr($matches[$i-1]->diff_date_match, 0, 1) != '-') $points_mises += $score[$matches[$i-1]->id_match]->pts;
			$nb_matchs_joues++;
		}	
	}

	// si on a déjà joué des matchs
	if($nb_matchs_joues > 0)
	{
		$pts_a_miser = $nb_matchs_joues * 10;
	} else {
		$pts_a_miser = $nb_matchs * 10;
	}

	$pts_max_total = $pts_a_miser - ($nb_matchs_total-1) * 5;
	$pts_max_total = $pts_max_total > 50 ? 50 : $pts_max_total;

	$pts_max = $pts_a_miser - $points_mises - ($nb_matchs-1) * 5;
	$pts_max = $pts_max > 50 ? 50 : $pts_max;
	?>


	<script type="text/javascript" language="javascript">
	// <![CDATA[
	var nb_matchs = <?php echo $nb_matchs_total; ?>;
	var pts_a_miser = <?php echo $pts_a_miser; ?>;
	var id_match_focused = 0;

	window.onload = updateMise;
	// ]]>
	</script>
	
	
	<?php

echo pp_iphone_footer();
?>