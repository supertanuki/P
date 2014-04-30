<?php
/**
* Project: PRONOPLUS
* Description: Envoi alerte email pour pronostiquer une grille
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-08-14
* Version: 1.0
*/
chdir( dirname(__FILE__) );
chdir( '../' );
$_SERVER['DOCUMENT_ROOT'] = getcwd();

require_once($_SERVER['DOCUMENT_ROOT'] . '/init.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/mainfunctions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/contentfunctions.php');

$url = "http://www.pronoplus.com/";

// recherche grilles
$matches = array();
$content = array();

if($_GET[id_matches])
{
	$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`image`,
				DATE_FORMAT(`pp_matches`.`date_first_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_first_match_format`,
				DATE_FORMAT(`pp_matches`.`date_last_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_last_match_format`,
				TIMEDIFF(NOW(), `pp_matches`.`date_first_match`) AS `diff_date_first_match`,
				TIMEDIFF(NOW(), `pp_matches`.`date_last_match`) AS `diff_date_last_match`
			FROM `pp_matches`
			WHERE `pp_matches`.`is_calcul`!='1'
				AND `pp_matches`.`id_matches`='".$db->escapeSimple($_GET[id_matches])."'";
} else {
	$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`image`,
				DATE_FORMAT(`pp_matches`.`date_first_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_first_match_format`,
				DATE_FORMAT(`pp_matches`.`date_last_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_last_match_format`,
				TIMEDIFF(NOW(), `pp_matches`.`date_first_match`) AS `diff_date_first_match`,
				TIMEDIFF(NOW(), `pp_matches`.`date_last_match`) AS `diff_date_last_match`
			FROM `pp_matches`
			WHERE `pp_matches`.`is_calcul`!='1' AND `pp_matches`.`is_alert`!='1'
				AND DATE_ADD(`pp_matches`.`date_first_match`, INTERVAL -30 HOUR) < NOW()
			ORDER BY `date_first_match`";
}
$result_matches = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_matches))
{
	die ("<li>ERROR : ".$result_matches->getMessage());
	
} else {
	$label_subject = '';
	while($pp_matches = $result_matches->fetchRow())
	{
		$matches[] = $pp_matches->id_matches;
		
		$label_subject = $pp_matches->label;
		
		$content[$pp_matches->id_matches] .= "<div>
												<h3><img src=\"".$url."template/default/".$pp_matches->image."\" style=\"float:left; margin-right:10px; margin-bottom:10px;\" /><a href=\"".$url."pronostiquer.php?id=".$pp_matches->id_matches."\" style=\"font-size:13px; font-family: Verdana, Arial, Helvetica, sans-serif; color:#333\">".formatDbData($pp_matches->label)."</a><br /><span style=\"color:red\">".format_diff_date($pp_matches->diff_date_first_match)."</span></h3>
						<p>A pronostiquer avant le ".$pp_matches->date_first_match_format.".<br />
						Dernier match &agrave; pronostiquer avant le ".$pp_matches->date_last_match_format." (".format_diff_date($pp_matches->diff_date_last_match).")</p>";			

		$content[$pp_matches->id_matches] .= str_replace('<a href="/', '<a href="'.$url, getMatchesClass($pp_matches->id_matches));

		$content[$pp_matches->id_matches] .= "</div><div class=\"clear\"></div><div><table cellpadding=\"2\" cellspacing=\"1\" align=\"center\">";
							
		$SQL = "SELECT `pp_match`.`id_match`, `team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`,
				DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`,
				TIMEDIFF(NOW(), `pp_match`.`date_match`) AS `diff_date_match`
				FROM `pp_match`
				INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
				INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
				WHERE `pp_match`.`id_matches`='".$pp_matches->id_matches."'
				ORDER BY `pp_match`.`date_match`";
		$result_match = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result_match))
		{
			die ("<li>ERROR : ".$result_match->getMessage());
			
		} else {
			while($pp_match = $result_match->fetchRow())
			{
				$content[$pp_matches->id_matches] .= "<tr>
						<td align=\"right\" width=\"49%\" style=\"padding:2px; background-color:#fff; font-size:11px; font-family: Verdana, Arial, Helvetica, sans-serif;\">".formatDbData($pp_match->team_host_label)."</td>
						<td style=\"padding:2px; background-color:#fff; font-size:11px; font-family: Verdana, Arial, Helvetica, sans-serif;\">-</td>
						<td width=\"49%\" style=\"padding:2px; background-color:#fff; font-size:11px; font-family: Verdana, Arial, Helvetica, sans-serif;\">".formatDbData($pp_match->team_visitor_label)."</td>
					</tr>";
			}
		}			
		
		$content[$pp_matches->id_matches] .= "<tr><td colspan=\"3\" align=\"center\"><p>&nbsp;<br /><a href=\"".$url."pronostiquer.php?id=".$pp_matches->id_matches."\" style=\"border:1px solid #999; padding:3px 5px 3px 5px; background-color:#eee; color:#333; text-decoration:none; font-size:11px; display:inline-block;\">Pronostiquer</a><br />&nbsp;</p></td></tr></table>";
			
		$content[$pp_matches->id_matches] .= "</div><div class=\"clear\"></div><hr>";
	}
	

}


// le joueur a déjà joué cette grille ?
$users = array();
if(count($matches)>0)
{
	foreach($matches as $id_matches)
	{
		$SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`email`
				FROM `pp_user` LEFT JOIN `pp_matches_user` ON `pp_user`.`id_user`=`pp_matches_user`.`id_user` AND `pp_matches_user`.`id_matches`=".$id_matches."
				WHERE `pp_user`.`no_mail`!='1' AND `pp_matches_user`.`id_user` IS NULL";
		$result = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result))
		{
			die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
			
		} else {
			while($pp_user = $result->fetchRow())
			{
				$users[$pp_user->id_user][$id_matches] = true;
				$users[$pp_user->id_user]['email'] = $pp_user->email;
			}
		}
	}
}

/* debug */
/*
$users = array();
foreach($matches as $id_matches)
{
	$users[27][$id_matches] = true;
	$users[27]['email'] = 'rhann@ais.gp';
}
*/
/* debug */


if(count($users)>0)
{
	$prebody = "<p>Salut !<br><br>Il ne te reste plus que quelques heures pour pronostiquer ces matchs :</p><br />";
	
	foreach($users as $id_user=>$mm)
	{
	
		$body = '';
		$nbmatches = 0;
		foreach($content as $id_matches=>$cc)
		{
			if($users[$id_user][$id_matches])
			{
				$body .= $cc;
				$nbmatches++;
			}
		}
		
		
		if($nbmatches <= 1 && $label_subject)
		{
			$randnb = rand(1,4);
			
			if($randnb == 1)
				$subject = "Alerte ! Pronostics de la grille ".$label_subject;
			else if($randnb == 2)
				$subject = "N'oublie pas de pronostiquer la grille ".$label_subject;
			else if($randnb == 3)
				$subject = "N'aurais-tu pas oublié de pronostiquer la grille ".$label_subject." ?";
			else
				$subject = $label_subject . " : plus que quelques heures pour pronostiquer !";
				
		} else {
			$randnb = rand(1,4);
			
			if($randnb == 1)
				$subject = "Alerte ! Plus que quelques heures pour pronostiquer !";
			else if($randnb == 2)
				$subject = "N'oublie pas de pronostiquer ces matchs !";
			else if($randnb == 3)
				$subject = "N'aurais-tu pas oublié de pronostiquer ?";
			else
				$subject = "Tu as des pronostics à faire sur Prono+";
		}
		
		sendemail($users[$id_user]['email'], 'Liline de Prono+', 'noreply@pronoplus.com', $subject, $prebody.$body);
		echo "<li> OK for $id_user\n";
		
		
		/*if($users[$id_user]['email'] == 'rhann@ais.gp')
		{
			$users[$id_user]['email'] = 'supertanuki@gmail.com';
			sendemail($users[$id_user]['email'], 'Liline de Prono+', 'noreply@pronoplus.com', $subject, $prebody.$body);
			echo "<li> OK for TEST\n";
		}*/
		
		
		//usleep(10);
	}
	
	$body = '';
	foreach($content as $id_matches=>$cc)
	{
		$body .= $cc;
	}
	sendemail(EMAIL_MASTER, 'Liline de Prono+', 'noreply@pronoplus.com', $subject.' / CONFIRMATION', $prebody.$body);
	echo "<li> OK for CONFIRMATION\n";
}

// flag des matches
foreach($matches as $id_matches)
{
	$SQL = "UPDATE `pp_matches` SET `is_alert`='1', date_alert=NOW() WHERE `id_matches`='".$id_matches."'";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
}
?>