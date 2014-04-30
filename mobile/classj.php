<?php
/**
* Project: PRONOPLUS
* Description: Classement journée
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2010-04-04
* Version: 1.0
*/

require_once('../init.php');
require_once('functions-iphone.php');
require_once('../mainfunctions.php');
require_once('../contentfunctions.php');

$user = user_authentificate();

if(!$_GET[id]) HeaderRedirect('index.php');


// recherche journée
$SQL = "SELECT `id_matches`, `pp_matches`.`label`, `pp_matches`.`image`, `pp_matches`.`date_first_match`, `id_cup_matches`, `is_calcul`,
			TIMEDIFF(NOW(), `pp_matches`.`date_last_match`) AS `diff_date_last_match`,
			`pp_info_country`.`label` AS `country`
		FROM `pp_matches`
		INNER JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_matches`.`id_info_country`
		WHERE `pp_matches`.`id_matches`='".$_GET[id]."'
		AND `date_first_match` < NOW()";
$result = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	if(!$pp_matches = $result->fetchRow())
	{
		HeaderRedirect('index.php');
	}
}

// nombre de joueurs dans le classement définitif ?
$class_provisoire = 0;

$msg_not_provisoire = false;

$table_class_user = 'pp_class_user';
$table_match_user = 'pp_match_user';
$type_calcul = 'classement';
						
$SQL = "SELECT COUNT(`id_user`) AS NBUSERS
		FROM `pp_class_user`
		WHERE `id_class`=1 AND `id_matches`='".$pp_matches->id_matches."'";
$result = $db->query($SQL);
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage());
	
} else {
	if($pp_class_user = $result->fetchRow())
	{
		$nb_element = $pp_class_user->NBUSERS;
		if($nb_element > 0)
		{
			$table_class_user = 'pp_class_user';
			$table_match_user = 'pp_match_user';
			$type_calcul = 'classement';
			
		} else {
			
			$class_provisoire = -1; // pas de classement provisoire
			
			// pas de classement définitif ? nombre de joueurs dans le classement temporaire ?
			$SQL = "SELECT COUNT(`id_user`) AS NBUSERS
					FROM `pp_class_user_temp`
					WHERE `id_class`=1 AND `id_matches`='".$pp_matches->id_matches."'
					".($friends_ids != '' ?  " AND `pp_class_user_temp`.`id_user` IN (".$friends_ids.")" : ""); // filtre amis;
			$result = $db->query($SQL);
			if(DB::isError($result))
			{
				die ("<li>ERROR : ".$result->getMessage());
				
			} else {
				if($pp_class_user = $result->fetchRow())
				{
					$nb_element = $pp_class_user->NBUSERS;
					if($nb_element > 0)
					{
						$table_class_user = 'pp_class_user_temp';
						$table_match_user = 'pp_match_user_temp';
						$type_calcul = 'provisoire';
						$class_provisoire = 1;	 // afficher le classement provisoire					
					}
				}
			}
			
			if($class_provisoire == -1) $msg_not_provisoire = true;
		}
		
	} else HeaderRedirect('index.php');
}


// recherche matchs
$matches = array();
$ids_match = "";
$SQL = "SELECT `pp_match`.`id_match`, `pp_match`.`id_info_match`, `team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`, `pp_match`.`score`,
		DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`
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
		$matches[$pp_match->id_match] = $pp_match;
		$ids_match .= ($ids_match!="" ? "," : "") . $pp_match->id_match;
	}
}

// recherche pronos de l'utilisateur
if($user->id_user && $ids_match!='')
{
	$match_user = array();
	$SQL = "SELECT `id_user`, `id_match`, `score`, `pts`, `type_result`, `pts_won`
	FROM `".$table_match_user."`
	WHERE `id_user`='".$user->id_user."' AND `id_match` IN (".$ids_match.")";
	$result_score = $db->query($SQL);
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result_score->getMessage());
		
	} else {
		while($pp_match_user = $result_score->fetchRow())
		{
			$match_user[$pp_match_user->id_match] = $pp_match_user;
		}
	}
}


// coupe ?
if($pp_matches->id_cup_matches)
{
	$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label`, `pp_cup_matches`.`number_tour`
			FROM `pp_cup_matches`
			INNER JOIN `pp_cup` ON `pp_cup`.`id_cup` = `pp_cup_matches`.`id_cup`
			WHERE `pp_cup_matches`.`id_cup_matches`='".$pp_matches->id_cup_matches."'";
	$result_cup_matches = $db->query($SQL);
	if(DB::isError($result_cup_matches))
	{
		die ("<li>ERROR : ".$result_cup_matches->getMessage()."<li>$SQL");
		
	} else {
		if(!$pp_cup_matches = $result_cup_matches->fetchRow()) HeaderRedirect('index.php');
		
		// le joueur est-il qualifié pour la coupe ?
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
}

//titre de la page
if(!$pp_matches->id_cup_matches)
	$title_page = "Classement".($type_calcul=='provisoire' ? ' provisoire' : '')." pronostics de ".$pp_matches->label;
else
	$title_page = getCupDivisionLabel($cup_user->cup_sub) . ' - ' . getCupTourLabel($pp_cup_matches->number_tour);


// afficher le bouton modifier mes pronos ?
$modifier_prono = $type_calcul == 'provisoire' ? true : false;
	
$bg_yellow = '#E8AC13';
$bg_red = '#9C1703';
$bg_green = '#87960F';

echo pp_iphone_header(!$pp_matches->id_cup_matches ? 'Classement' : $pp_cup_matches->label, $is_menu=false, $is_retour=true);

	?>
	<?php if($modifier_prono) { ?><form method="post" action="pronostiquer.php?id=<?php echo $pp_matches->id_matches; ?>"><?php } ?>
	<ul class="pageitem">
		<li class="textbox">
			<span class="header"><?php echo $title_page; ?></span>
		</li>
		
		
		
		<li class="textbox">
			<?php if($msg_not_provisoire && !$pp_matches->id_cup_matches) { ?>
				<p style="color:red; font-weight:bold;">Le classement provisoire n'a pas encore été fait. Rendez-vous sur le site version normale pour le faire !</p>		
			<?php } ?>
			<p>
			<table width="100%" cellpadding="2" cellspacing="1">
				<tr>
					<th width="45%" align="right" valign="top"></th>
					<th width="10%" align="center" valign="top">Score</th>
					<th width="45%" valign="top"></th>
				</tr>
			<?php
			$n=1;
			foreach($matches as $id_match=>$match)
			{
				if($match_user[$id_match]->type_result == 1) {
					$color = $bg_green;
					$fact = 10;
				
				} elseif($match_user[$id_match]->type_result == 2) {
					$color = $bg_yellow;
					$fact = 3;
				
				} elseif($match_user[$id_match]->type_result == 3) {
					$color = $bg_red;
					$fact = 0;
				
				} elseif($match_user[$id_match]->type_result == 4) {
					$color="#a5a5a5";
					$fact = 1;
				
				} else {
					$color="#bbbbbb";
					$fact = 0;
				}
				?>
				<tr style="font-weight:bold;">
					<td align="right" valign="top"><? echo $match->team_host_label; ?></td>
					<td align="center" valign="top">
					<?php
					if($match->score == "R-R") {
						echo "<font color=\"red\">Annul&eacute;</font>";
					} else {
						echo $match->score ? $match->score : '-';
					}
					?>
					</td>
					<td valign="top"><? echo $match->team_visitor_label; ?></td>
				</tr>
				<?php
				if($match_user[$id_match]->score) {
					?>
					<tr class="noborder">
						<td align="right"></td>
						<td align="center" style="background:<?php echo $color; ?>; color:#fff; font-weight:bold;">
							<?php
							echo $match_user[$id_match]->score;
							?>
						</td>
						<td align="right">
							<?php
							echo $match->score ? $match_user[$id_match]->pts . ' x '. $fact . ' = ' . $match_user[$id_match]->pts_won : '';
							?>
						</td>
					</tr>
					<?php
				}
				$n++;
			}
			?>
			</table>		
			</p>
		</li>
		
		
		
		
		<?php
		// Coupe ?
		if($pp_matches->id_cup_matches)
		{
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
						<th align="center" colspan="2">Joueur</th>
						<th align="center">Classement</th>
						<th align="center">Points</th>
					</tr>
					<tr<?php echo $cup_user->id_user_host == $cup_user->id_user_won ? ' style="background:yellow"' : ''; ?>>
						<td align="center"><img src="<?php echo $avatar_host; ?>" height="30" width="30" border="0" align="absmiddle" /></td>
						<td><?php echo $cup_user->login_host; ?></td>
						<td align="center"><?php echo $cup_user->host_class . '<sup>' . ($cup_user->host_class > 1 ? 'ème' : 'er') . '</sup>'; ?></td>
						<td align="center"><?php echo $pp_matches->is_calcul ? $cup_user->host_nb_points : '-'; ?></td>
					</tr>
					<tr<?php echo $cup_user->id_user_visitor == $cup_user->id_user_won ? ' style="background:yellow"' : ''; ?>>
						<td align="center"><img src="<?php echo $avatar_visitor; ?>" height="30" width="30" border="0" align="absmiddle" /></td>
						<td><?php echo $cup_user->login_visitor; ?></td>
						<td align="center"><?php echo $cup_user->visitor_class . '<sup>' . ($cup_user->visitor_class > 1 ? 'ème' : 'er') . '</sup>'; ?></td>
						<td align="center"><?php echo $pp_matches->is_calcul ? $cup_user->visitor_nb_points : '-'; ?></td>
					</tr>
				</table>
				</li>
				<?php
			}
			
			
			
		// classement normal	
		} else if(!$msg_not_provisoire)
		{
			?>
			<li class="textbox">
			<table width="100%" cellpadding="2" cellspacing="1">
				<tr>
					<th align="center">Rang</th>
					<th align="center" colspan="2">Joueur</th>
					<th align="center">Points</th>
				</tr>
			<?php
			// tableau
			$SQL = "SELECT `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`,
						`".$table_class_user."`.`id_user`,
						`".$table_class_user."`.`class`, `".$table_class_user."`.`nb_score_ok`, `".$table_class_user."`.`nb_result_ok`,
						`".$table_class_user."`.`nb_matches`, `".$table_class_user."`.`nb_points`
					FROM `".$table_class_user."`
					INNER JOIN `pp_user` ON `pp_user`.`id_user`=`".$table_class_user."`.`id_user`
					WHERE `".$table_class_user."`.`id_class`=1
						AND `".$table_class_user."`.`id_matches`='".$pp_matches->id_matches."'
						AND (`".$table_class_user."`.`class` = 1 OR `".$table_class_user."`.`id_user`='".$user->id_user."')
						ORDER BY `".$table_class_user."`.`class`";
			$result = $db->query($SQL);
			if(DB::isError($result))
			{
				die ("<li>ERROR : ".$result->getMessage());
				
			} else {
				$rang = ($sqldep?$sqldep:0);
				while($pp_class_user = $result->fetchRow())
				{
					?>
						<tr>
							<td align="center"><?php echo $pp_class_user->class?></td>
							<td align="center">
								<?php
								if($avatar = getAvatar($pp_class_user->id_user, $pp_class_user->avatar_key, $pp_class_user->avatar_ext, 'small')) {
								?>
									<img src="/avatars/<?php echo $avatar?>" height="30" width="30" border="0" align="absmiddle" />
								<?php
								} else {
								?>
									<img src="/template/default/_profil.png" height="30" width="30" border="0" align="absmiddle" />
								<?php
								}
								?>
							</td>
							<td>
								<?php echo $pp_class_user->login; ?>
							</td>
							<td align="center"><?php echo $pp_class_user->nb_points?></td>
						</tr>
					<?php
				}
				?>
				<tr><td colspan="4" align="center"><a href="/classj.php?id=<?php echo $pp_matches->id_matches; ?>" class="link_orange">Voir le classement complet sur la version normale de Prono+</a></td></tr>
				<?php
			}
			?>
			</table>
			</li>
			<?php
		}
		?>
		
		<?php if($modifier_prono) { ?><li class="button"><input name="Submit input" type="submit" value="Modifier mes pronostics" /></li><?php } ?>
	</ul>
	
	<?php if($modifier_prono) { ?></form><?php } ?>
	
	
	<?php

echo pp_iphone_footer();
?>