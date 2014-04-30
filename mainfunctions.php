<?php
/**
* Project: PRONOPLUS
* Description: Fonctions principales
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-07-27
* Version: 1.0
*/
require('encoding.php');


function getConfig($param)
{
	global $db;
	// recherche option souhaitée
	$SQL = "SELECT value FROM pp_config WHERE param='".$param."'";
	$result_pp_config = $db->query($SQL);
	if(DB::isError($result_pp_config))
	{
		die ("<li>ERROR : ".$result_pp_config->getMessage());
		
	} else {
		if($pp_config = $result_pp_config->fetchRow())
		{
			return $pp_config->value;
		}
	}
}

function navigator_is_mobile()
{
	if(stristr($_SERVER['HTTP_USER_AGENT'], "iPhone") || strpos($_SERVER['HTTP_USER_AGENT'], "iPod") || strpos($_SERVER['HTTP_USER_AGENT'], "android"))
		return true;
	else
		return false;
}

function getScoreRandom($type='domicile')
{
  return round(2 * log(rand(1, 5)));
}

function formatdateheure($datem)
{
	$datem = $datem + ($joueur_fuseau*3600)+$correctionheure;
	return date("d", $datem)."/".date("m", $datem)."/".date("Y", $datem)." à ".date("H", $datem)."h".date("i", $datem);
}

function formattexte($le_msg, $noimg=0) {
	$oembed = new OEmbed();
  
  
  $le_msg = str_replace("\n", " <br />", htmlspecialchars($le_msg));
  
  if(!$noimg) $le_msg = $oembed->autoEmbed($le_msg, array('width' => 500));
  
	$le_msg = str_replace(":)" , "<img src=\"/smileys/1.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":furieux:" , "<img src=\"/smileys/2.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":]" , "<img src=\"/smileys/3.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":D" , "<img src=\"/smileys/4.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":?:" , "<img src=\"/smileys/5.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":timide:" , "<img src=\"/smileys/6.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":saoul:" , "<img src=\"/smileys/7.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace("8)" , "<img src=\"/smileys/8.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":p" , "<img src=\"/smileys/9.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":triste:" , "<img src=\"/smileys/10.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":diable:" , "<img src=\"/smileys/11.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":bisou:" , "<img src=\"/smileys/12.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(";)" , "<img src=\"/smileys/13.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":(" , "<img src=\"/smileys/14.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":O" , "<img src=\"/smileys/15.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":mignon:" , "<img src=\"/smileys/16.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":dodo:" , "<img src=\"/smileys/17.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace(":fou:" , "<img src=\"/smileys/18.gif\" border=\"0\">" , $le_msg);
	$le_msg = str_replace("[red]" , "<font color=\"red\">" , $le_msg);
	$le_msg = str_replace("[/red]" , "</font>" , $le_msg);
	
	preg_match_all("/\[img\](.*?)\[\/img\]/i", $le_msg, $regs);
	$resultat=$regs[1];
	$tabimg=array();
	$num_img=0;
	foreach($resultat as $key=>$value) {
		if($noimg) {
			$le_msg=str_replace("[img]".$value."[/img]", "<font color=\"red\">[image non affich&eacute;e: ouvrir le message pour la voir]</font><br />", $le_msg);
		} else {
			$le_msg=str_replace("[img]".$value."[/img]", "|||img".$num_img, $le_msg);
			$tabimg[$num_img]=$value;
			$num_img++;
		}
	}
  
	// $le_msg = eregi_replace("(http|mailto|news|ftp|https)://(([-éa-z0-9\/\.\?_=#@:~])*)", "<a href=\"\\1://\\2\" target=\"_blank\" class=\"link_orange\">\\1://\\2</a>", $le_msg);
	
  //$le_msg = eregi_replace("(http|mailto|news|ftp|https)://(([-éa-z0-9\/\.\?_=#@:~])*)", "<a href=\"\\1://\\2\" target=\"_blank\" class=\"link_orange\">\\1://\\2</a>", $le_msg);
  // '|(https?://[^\s"]+)|im'
  
	//$le_msg = eregi_replace("([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])", "<a href=\"\\1://\\2\\3\" target=\"_blank\">\\1://\\2\\3</a>", $le_msg);
	
	if(!$noimg) {
		foreach($tabimg as $key=>$value) {	
			$le_msg=str_replace("|||img$key", "<img src=\"".$value."\" />", $le_msg);
		}
	}
	return $le_msg;
}

function nom_joueur($id_user)
{
	global $db;
	$SQL = "SELECT *
			FROM `pp_user`
			WHERE id_user = '".$id_user."'";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if($user = $result->fetchRow())
		{
			return $user;
		}
	}
	return false;
}

function log_error($error)
{

}

function formatDbData($str)
{
	return $str;
	//return utf8_encode($str);
}

function HeaderRedirect($page)
{
	header("Location: ".$page);
	exit;
}

function setUser($login, $pwd, $permanent=false)
{
	if($login && $pwd)
	{
		if($user = getUser('', $login, $pwd))
		{
			if($permanent)
			{
				setcookie("user", $user->footprint, time()+100000000, '/');
			} else {
				setcookie("user", $user->footprint, 0, '/');
			}
			return $user;
		}
	}
	return false;
}

function user_authentificate()
{
	$strUser = $_COOKIE[user];
	if($strUser)
	{
		$user = getUser($strUser);
		if($user!=false)
		{
			setLogUser($user->id_user);
			return $user;
		}
	}
	return false;
}

function setLogUser($id_user)
{
	global $db;
	$SQL = "UPDATE `pp_user` SET `last_ip`='".$_SERVER['REMOTE_ADDR']."', `last_cnx`=NOW()
			WHERE `id_user` = '".$id_user."'";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
}

function getUser($strUser, $login=false, $pwd=false)
{
	global $db, $txtlang;
	
	$footprint = "CONCAT(`id_user`, 'a', MD5(CONCAT(`login`, 'a', MD5(`pwd`))))";
	
	if(!$login && !$pwd)
		$cond = $footprint . "='".$db->escapeSimple($strUser)."'";
	else
		$cond = "`login`='".$db->escapeSimple($login)."' AND `pwd`='".$db->escapeSimple($pwd)."'";
		
	$SQL = "SELECT `id_user`, `id_lang`, `login`, `pwd`, `email`, `name`, `no_mail`, `no_mail_end_matches`, `timezone`, `register_date`, `id_usersteam`,
					`avatar_key`, `avatar_ext`,
					".$footprint." AS `footprint`,
					`date_view_wall`, `date_view_friends`,
					DATE_FORMAT(`pp_user`.`register_date`, '".$txtlang['AFF_DATE_SQL']."') AS `register_date_format`	
			FROM `pp_user`
			WHERE ".$cond;
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if($result->numRows())
		{
			return $result->fetchRow();
		}
	}
	return false;
}


function get_date_complete($dayweek, $day, $month, $year)
{
	global $txtlang;
	$content = str_replace('%DAYWEEK%', $txtlang['DAY_'.$dayweek], $txtlang['AFF_DATE_COMPLETE']);
	$content = str_replace('%DAY%', $day, $content);
	$content = str_replace('%MONTH%', $txtlang['MONTH_'.$month], $content);
	$content = str_replace('%YEAR%', $year, $content);
	return $content;
}



function format_diff_date($diffdate, $is_signe=true, $afficher_quelquesoit_le_signe=false)
{
    //$signe = '+';
    $str = '';

    if($diffdate[0] == '-')
    {
        $diffdate = substr($diffdate, 1, strlen($diffdate));
        $signe = '-';
    } else {
        $signe = '+';
    }

    $diffdatetab = explode(':', $diffdate);

    $jour = floor($diffdatetab[0] / 24);
    $heure = $diffdatetab[0] - ($jour * 24);

    if($is_signe) $str .= $signe."&nbsp;";
    if(1*$jour != 0) $str .= $jour."j&nbsp;";
    if(1*$heure != 0) $str .= $heure."h&nbsp;";
    if($jour*1==0 && $diffdatetab[1] * 1) $str .= $diffdatetab[1]."&nbsp;min";
    if(!$str) $str .= 'moins d\'une minute';

    $str .= '<!-- jour = '.$jour.' * heure = '.$heure.' * minute = '.$diffdatetab[1].' * diffdate = '.$diffdate.'-->';

    if(!$afficher_quelquesoit_le_signe && substr($diffdate, 0, 1) == '-') return '';

    return $str;
}


function sendemail($to, $fromname, $fromemail, $subject, $contenthtml)
{
	include_once($_SERVER['DOCUMENT_ROOT'].'/lib/PHPMailer/class.phpmailer.php');
	$mail    = new PHPMailer();
	$body    = $mail->getFile($_SERVER['DOCUMENT_ROOT'].'/modele_email.php');
	$body    = str_replace('%%CONTENT%%', $contenthtml, $body);
	$mail->From     = 'noreply@pronoplus.com';
	$mail->FromName = 'Liline de Prono+';
	$mail->Subject = stripslashes($subject);
	$mail->AltBody = strip_tags(stripslashes($body));
	$mail->Body = stripslashes($body);
	$mail->CharSet = 'utf-8';

    if($_SERVER['REMOTE_ADDR'] == '127.0.0.1') // debug
    {
        $mail->AddAddress('supertanuki@gmail.com');
    } else {
        $mail->AddAddress($to);
    }

	return $mail->Send();
}


function user_update_date_now($id_user, $field)
{
	global $db, $user;
	$SQL = "UPDATE `pp_user` SET ".$field." = NOW() WHERE id_user='".$id_user."'";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());
}

function pp_comments_nb_afficher($url, $type, $id_type)
{
	$nb_comments = pp_comments_nb($type, $id_type);
	$nb_comments_new = pp_comments_nb($type, $id_type, $new=true);
	$nb_comments_new_html = '';
	if($nb_comments_new) $nb_comments_new_html = ' <span class="onglet_nb_msg_left"><span class="onglet_nb_msg_right">'.$nb_comments_new.'</span></span>';
	$content = '<a href="'.$url.'" class="link_orange"><img src="/template/default/comment.gif" align="absmiddle" border="0" /> '. ($nb_comments > 0 ? $nb_comments.' commentaire'.($nb_comments > 1 ? 's' : '') : 'réagissez !') . $nb_comments_new_html . '</a>';
	return $content;
}


function pp_comments_get_dateviewed($type, $id_type)
{
	global $db, $user;
	if($user->id_user)
	{
		$SQL = "SELECT `date_viewed`
				FROM `pp_comments_viewed`
				WHERE `type`='".$type."' AND `id_type`='".$id_type."'
				AND `id_user`='".$user->id_user."'";
		$result = $db->query($SQL);
		if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());
		if($pp_comments_viewed = $result->fetchRow())
		{
			return $pp_comments_viewed->date_viewed;
		}
	}
	
	return false;
}


function pp_comments_nb($type, $id_type, $new=false)
{
	global $db, $user;
	
	if($user && $new)
	{
		if($type == 'wall')
		{
			$date_limit = $user->date_view_wall;
			
		} else {
			$date_limit = pp_comments_get_dateviewed($type, $id_type);
		}
	}
	
	$SQL = "SELECT COUNT(`id_comment`) AS NB_COMMENTS
			FROM `pp_comments`
			WHERE `pp_comments`.`type`='".$type."' AND `pp_comments`.`id_type`='".$id_type."'
			AND `deleted`!='1'
			".($date_limit ? "AND `pp_comments`.`date_creation` > '".$date_limit."'" : "");
	$result = $db->query($SQL);
	//echo "<li>$SQL</li>";
	if(DB::isError($result))
	{
		die ("<li>$SQL<li>ERROR : ".$result->getMessage());
		
	} else {
		if($pp_comments = $result->fetchRow())
		{
			return $pp_comments->NB_COMMENTS;
		}
	}
	
	return 0;
}


function get_note_match($id_info_match)
{
	global $db;
	$SQL = "SELECT COUNT(id_user) AS nb_votants, AVG(`note`) AS note_match FROM `pp_info_match_note` WHERE `id_info_match`='".$id_info_match."'";
	$result = $db->query($SQL);
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if($pp_info_match_note = $result->fetchRow())
		{
			return array('note_match' => $pp_info_match_note->note_match, 'nb_votants' => $pp_info_match_note->nb_votants);
		}
	}
	return false;
}

function get_team_class($id_team, $id_league)
{
	global $db;
	$saison_en_cours = getConfig('saison_en_cours');

	$SQL = "SELECT id_team
			FROM pp_team_class
			WHERE pp_team_class.saison='".$saison_en_cours."' AND pp_team_class.id_league='".$id_league."'
			ORDER BY pp_team_class.nb_points DESC, (pp_team_class.nb_goals_for - pp_team_class.nb_goals_against) DESC, pp_team_class.nb_goals_for DESC, pp_team_class.nb_matches";
	//echo "<li>$SQL";
	$result_team = $db->query($SQL);
	if(DB::isError($result_team))
	{
		die ("<li>ERROR : ".$result_team->getMessage()."<li>$SQL");
		
	} else {
		if($result_team->numRows())
		{
			$classement = 0;
			while($pp_team_class = $result_team->fetchRow())
			{
				$classement++;
				if($pp_team_class->id_team == $id_team) return $classement;
			}
		}
	}
	
	return false;
}



function get_league($id_info_match)
{
	global $db;
	
	$SQL = "SELECT `pp_info_matches`.`id_league`
			FROM `pp_info_match`
			INNER JOIN `pp_info_matches` ON `pp_info_match`.`id_info_matches`=`pp_info_matches`.`id_info_matches`
			WHERE `pp_info_match`.`id_info_match`='".$id_info_match."'";
	//echo "<li>$SQL";
	$result_match = $db->query($SQL);	
	if(DB::isError($result_match))
	{
		die ("<li>ERROR : ".$result_match->getMessage());
		
	} else {
		if($pp_info_matches = $result_match->fetchRow())
		{
			return $pp_info_matches->id_league;
		}
	}
	return false;
}


function get_team_forme($id_team, $id_league, $type='general')
{
	global $db, $txtlang;
	
	$forme = array();
	/*
	$SQL = "SELECT `pp_info_match`.`id_team_host`, `pp_info_match`.`id_team_visitor`, `pp_info_match`.`score`,
					`team_host`.`label` AS `team_host_label`, `team_host`.`flag` AS `team_host_flag`,
					`team_visitor`.`label` AS `team_visitor_label`, `team_visitor`.`flag` AS `team_visitor_flag`,
					DATE_FORMAT(`pp_info_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `time_match_format`
			FROM `pp_info_match`
			INNER JOIN `pp_info_matches` ON `pp_info_match`.`id_info_matches`=`pp_info_matches`.`id_info_matches` AND `pp_info_matches`.`id_league`='".$id_league."'
			INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_info_match`.`id_team_host`
			INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_info_match`.`id_team_visitor`
			WHERE (`pp_info_match`.`id_team_host`='".$id_team."' OR `pp_info_match`.`id_team_visitor`='".$id_team."')
				AND TRIM(`pp_info_match`.`score`) != ''
			ORDER BY `pp_info_match`.`date_match` DESC
			LIMIT 8";
	*/
	if($type == 'general')
		$cond = "(`pp_info_match`.`id_team_host`='".$id_team."' OR `pp_info_match`.`id_team_visitor`='".$id_team."')";
	elseif($type == 'domicile')
		$cond = "`pp_info_match`.`id_team_host`='".$id_team."'";
	elseif($type == 'exterieur')
		$cond = "`pp_info_match`.`id_team_visitor`='".$id_team."'";
		
	$SQL = "SELECT `pp_info_match`.`id_team_host`, `pp_info_match`.`id_team_visitor`, `pp_info_match`.`score`
			FROM `pp_info_match`
			INNER JOIN `pp_info_matches` ON `pp_info_match`.`id_info_matches`=`pp_info_matches`.`id_info_matches` AND `pp_info_matches`.`id_league`='".$id_league."'
			WHERE $cond
				AND TRIM(`pp_info_match`.`score`) != ''
			ORDER BY `pp_info_match`.`date_match` DESC
			LIMIT 8";
	//echo "<li>$SQL";
	$result_match = $db->query($SQL);	
	if(DB::isError($result_match))
	{
		die ("<li>ERROR : ".$result_match->getMessage());
		
	} else {
		
		$bp = 0;
		$bc = 0;
	
		while($pp_info_match = $result_match->fetchRow())
		{
			$buts = explode('-', $pp_info_match->score);
			$buts_host = $buts[0];
			$buts_visitor = $buts[1];
			
			// victoire domicile
			if($buts_host > $buts_visitor)
			{
				if($pp_info_match->id_team_host == $id_team)
				{
					$pp_info_match->forme = 'V';
					$bp += $buts_host;
					$bc += $buts_visitor;
				} else {
					$pp_info_match->forme = 'D';
					$bc += $buts_host;
					$bp += $buts_visitor;
				}
			
			// match nul
			} elseif($buts_host == $buts_visitor)
			{
				$pp_info_match->forme = 'N';
				$bp += $buts_host;
				$bc += $buts_visitor;
				
			// victoire extérieure
			} elseif($buts_host < $buts_visitor)
			{
				if($pp_info_match->id_team_host == $id_team)
				{
					$pp_info_match->forme = 'D';
					$bp += $buts_host;
					$bc += $buts_visitor;
				} else {
					$pp_info_match->forme = 'V';
					$bc += $buts_host;
					$bp += $buts_visitor;
				}
			}
			
			$forme[] = $pp_info_match;
		}
		
		if(count($forme)) $forme['bp'] = round($bp / count($forme), 1);
		if(count($forme)) $forme['bc'] = round($bc / count($forme), 1);
	}
	return $forme;
}


function afficher_forme($forme=array())
{
	$html = '';
	foreach($forme as $pp_info_match)
	{
		// victoire
		if($pp_info_match->forme == 'V')
		{
			$html .= '<span class="bloc_forme_victoire">V</span>';
		
		// match nul
		} elseif($pp_info_match->forme == 'N')
		{
			$html .= '<span class="bloc_forme_nul">N</span>';
			
		// défaite
		} elseif($pp_info_match->forme == 'D')
		{
			$html .= '<span class="bloc_forme_defaite">D</span>';
		}
	}
	return $html;
}






function get_apercu_stats_match($match, $tendance=array(), $TOTALUSERS=0)
{
	// classement des deux équipes
	$id_league = get_league($match->id_info_match);
	$class_team_host = '';
	$class_team_visitor = '';
	if($id_league)
	{
		$class_team_host = get_team_class($match->id_team_host, $id_league);
		$class_team_visitor = get_team_class($match->id_team_visitor, $id_league);
	
		if($class_team_host && $class_team_visitor)
		{
			$class_team_host = '<span class="bloc_bleu">' . $class_team_host . ($class_team_host > 1 ? 'è' : 'er') . '</span>';
			$class_team_visitor = '<span class="bloc_rouge">' . $class_team_visitor . ($class_team_visitor > 1 ? 'è' : 'er') . '</span>';
		}
	}
	
	$html = '<table width="100%" cellpadding="2" cellspacing="1">';		
	$html .= '<tr>';
	$html .= '<th align="right" width="45%">'.$match->team_host_label.' '.$class_team_host.'</th>';
	$html .= '<th width="10%" align="center">-</td>';
	$html .= '<th align="left" width="45%">'.$class_team_visitor.' '.$match->team_visitor_label.'</th>';
	$html .= '</tr>';
	
	if($TOTALUSERS>0)
	{
		$html .= '<tr>';
		$html .= '<td colspan="3" align="center">Tendance des pronostics : <span style="color:#990000">victoire hôte</span> / <span style="color:#888">nul</span> / <span style="color:#003399">victoire visiteur</span><table cellpadding="1" cellspacing="1" width="100%" class="tab_tendance">';
		$html .= '<tr>';
		if($tendance[1]->NBUSERS > 0) $html .= '<td width="' . round(100 * $tendance[1]->NBUSERS / $TOTALUSERS) . '%" bgcolor="#990000"><img src="/template/default/pixel.gif" height="2" width="1" /></td>';
		if($tendance[2]->NBUSERS > 0) $html .= '<td width="' . round(100 * $tendance[2]->NBUSERS / $TOTALUSERS) . '%" bgcolor="#888888"><img src="/template/default/pixel.gif" height="2" width="1" /></td>';
		if($tendance[3]->NBUSERS > 0) $html .= '<td width="' . round(100 * $tendance[3]->NBUSERS / $TOTALUSERS) . '%" bgcolor="#003399"><img src="/template/default/pixel.gif" height="2" width="1" /></td>';
		$html .= '</tr>';			
		$html .= '</table></td>';
		$html .= '</tr>';
		
		$html .= '<tr>';
		$html .= '<td colspan="3"><table cellpadding="1" cellspacing="1" width="100%">';
		$html .= '<tr>';
		if($tendance[1]->NBUSERS > 0) $html .= '<td style="color:#990000" align="left" width="' . round(100 * $tendance[1]->NBUSERS / $TOTALUSERS) . '%">' . round(100 * $tendance[1]->NBUSERS / $TOTALUSERS) . '%</td>';
		if($tendance[2]->NBUSERS > 0) $html .= '<td style="color:#888" align="center" width="' . round(100 * $tendance[2]->NBUSERS / $TOTALUSERS) . '%">' . round(100 * $tendance[2]->NBUSERS / $TOTALUSERS) . '%</td>';
		if($tendance[3]->NBUSERS > 0) $html .= '<td style="color:#003399" align="right" width="' . round(100 * $tendance[3]->NBUSERS / $TOTALUSERS) . '%">' . round(100 * $tendance[3]->NBUSERS / $TOTALUSERS) . '%</td>';
		$html .= '</tr>';			
		$html .= '</table></td>';
		$html .= '</tr>';
	}
	
	// forme des deux équipes
	$forme_host = get_team_forme($match->id_team_host, $id_league);
	$forme_visitor = get_team_forme($match->id_team_visitor, $id_league);
	if(count($forme_host) && count($forme_visitor))
	{
		$html .= '<tr>';
		$html .= '<td align="left" style="border-top:1px solid #ccc">Série en cours ' . afficher_forme($forme_host) . '</td>';
		$html .= '<td style="border-top:1px solid #ccc">&nbsp;</td>';
		$html .= '<td align="left" style="border-top:1px solid #ccc">Série en cours' . afficher_forme($forme_visitor) . '</td>';
		$html .= '</tr>';
		
		$html .= '<tr>';
		$html .= '<td align="left">Ratio BP / BC : ' . $forme_host['bp'].' / '.$forme_host['bc'] . '</td>';
		$html .= '<td>&nbsp;</td>';
		$html .= '<td align="left">Ratio BP / BC : ' . $forme_visitor['bp'].' / '.$forme_visitor['bc'] . '</td>';
		$html .= '</tr>';
		
		$forme_host_domicile = get_team_forme($match->id_team_host, $id_league, 'domicile');
		$forme_visitor_exterieur = get_team_forme($match->id_team_visitor, $id_league, 'exterieur');
		if(count($forme_host_domicile) && count($forme_visitor_exterieur))
		{
			$html .= '<tr>';
			$html .= '<td align="left" nowrap="nowrap">Série domicile ' . afficher_forme($forme_host_domicile) . '</td>';
			$html .= '<td>&nbsp;</td>';
			$html .= '<td align="left" nowrap="nowrap">Série extérieure ' . afficher_forme($forme_visitor_exterieur) . '</td>';
			$html .= '</tr>';
			
			$html .= '<tr>';
			$html .= '<td align="left" nowrap="nowrap">Ratio BP / BC domicile : ' . $forme_host_domicile['bp'].' / '.$forme_host_domicile['bc'] . '</td>';
			$html .= '<td>&nbsp;</td>';
			$html .= '<td align="left" nowrap="nowrap">Ratio BP / BC extérieur : ' . $forme_visitor_exterieur['bp'].' / '.$forme_visitor_exterieur['bc'] . '</td>';
			$html .= '</tr>';
		}
	}
	
	$html .= '</table>';
	
	return $html;
}



function get_classement_league($titre, $saison_en_cours, $id_league, $id_team_focus=0)
{
	global $db;
	
	// Classement
	$pp_team_class_arr = array();
	$SQL = "SELECT pp_team.id_team, pp_team.label, pp_team.flag, pp_team.nb_points_sanction,
				pp_team_class.nb_points, pp_team_class.nb_matches,
				pp_team_class.nb_won, pp_team_class.nb_tie, pp_team_class.nb_lost, pp_team_class.nb_goals_for, pp_team_class.nb_goals_against
			FROM pp_team_class
			INNER JOIN pp_team ON pp_team_class.saison='".$saison_en_cours."' AND pp_team_class.id_league='".$id_league."' AND pp_team.id_team = pp_team_class.id_team
			ORDER BY pp_team_class.nb_points DESC, (pp_team_class.nb_goals_for - pp_team_class.nb_goals_against) DESC, pp_team_class.nb_goals_for DESC, pp_team_class.nb_matches";
	$result_team = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_team))
	{
		die ("<li>ERROR : ".$result_team->getMessage()."<li>$SQL");
		
	} else {
		if($result_team->numRows())
		{
			while($pp_team_class = $result_team->fetchRow())
			{
				$pp_team_class_arr[] = $pp_team_class;
			}
		}
	}
	
	if(count($pp_team_class_arr))
	{
		?>
		<br />
		<h2 class="title_green"><?php echo $titre; ?></h2>
		

		<table width="100%" cellpadding="2" cellspacing="1">
			<tr>
				<th width="4%">&nbsp;</th>
				<th width="34%">&nbsp;</th>
				<th width="14%" colspan="2">Points</th>
				<th width="3%">J</th>
				<th width="3%">G</th>
				<th width="3%">N</th>
				<th width="3%">P</th>
				<th width="14%">%G %N %P</th>
				<th width="4%">BP</th>
				<th width="4%">BC</th>
				<th width="4%">Diff</th>
				<th width="5%">Moy.BP</th>
				<th width="5%">Moy.BC</th>
			</tr>
			<?php
			$bg_blue = '#22A9B2';
			$points_max = 0;
			$taille_points = 100;
			foreach($pp_team_class_arr as $index=>$pp_team_class)
			{
				if($altern) {
					$class_line = 'ligne_grise';
					$altern = 0;
				} else {
					$class_line = 'ligne_blanche';
					$altern = 1;
				}
				
				if($id_team_focus == $pp_team_class->id_team) $class_line = 'ligne_selected';
				
				if(!$points_max) $points_max = $pp_team_class->nb_points;
				
				$diff = $pp_team_class->nb_goals_for - $pp_team_class->nb_goals_against;
				?>
				<tr class="<?php echo $class_line?>" onmouseover="this.className='ligne_rollover'" onmouseout="this.className='<?php echo $class_line?>'">
					<td align="center"><b><?php echo ($index+1) ?></b></td>
					<td><a href="/stats-equipe.php?id=<?php echo $pp_team_class->id_team; ?>" class="link_orange"><?php echo ($pp_team_class->flag ? '<img src="/image/flags/'.$pp_team_class->flag.'"  align="absmiddle" border="0" />' : '').' '.$pp_team_class->label.($pp_team_class->nb_points_sanction != 0 ? '*' : '') ?></a></td>
					
					<td align="center"><?php echo $pp_team_class->nb_points ?></td>
					
					<td align="left"><?php echo $points_max > 0 && $pp_team_class->nb_points > 0 ? '<span style="background:'.$bg_blue.'"><img src="/template/default/pixel.gif" width="'.round($pp_team_class->nb_points * $taille_points / $points_max).'" height="5" /></span>' : ''; ?></td>
					
					<td align="center"><?php echo $pp_team_class->nb_matches ?></td>
					<td align="center"><?php echo $pp_team_class->nb_won ?></td>
					<td align="center"><?php echo $pp_team_class->nb_tie ?></td>
					<td align="center"><?php echo $pp_team_class->nb_lost ?></td>
					
					<td align="center" nowrap="nowrap"><?php
						echo $pp_team_class->nb_won > 0 && $pp_team_class->nb_matches > 0 ? '<span class="result_gagne"><img src="/template/default/pixel.gif" width="'.round($pp_team_class->nb_won * $taille_points / $pp_team_class->nb_matches).'" height="5" /></span>' : '';
						echo $pp_team_class->nb_tie > 0 && $pp_team_class->nb_matches > 0 ? '<span class="result_nul"><img src="/template/default/pixel.gif" width="'.round($pp_team_class->nb_tie * $taille_points / $pp_team_class->nb_matches).'" height="5" /></span>' : '';
						echo $pp_team_class->nb_lost > 0 && $pp_team_class->nb_matches > 0 ? '<span class="result_defaite"><img src="/template/default/pixel.gif" width="'.round($pp_team_class->nb_lost * $taille_points / $pp_team_class->nb_matches).'" height="5" /></span>' : '';
					?></td>
					
					<td align="center"><?php echo $pp_team_class->nb_goals_for ?></td>
					<td align="center"><?php echo $pp_team_class->nb_goals_against ?></td>
					<td align="center"><?php echo ($diff >= 0 ? '+' : '') . $diff ?></td>
					
					<td align="center"><?php echo $pp_team_class->nb_matches > 0 ? round($pp_team_class->nb_goals_for / $pp_team_class->nb_matches, 1) : ''; ?></td>
					<td align="center"><?php echo $pp_team_class->nb_matches > 0 ? round($pp_team_class->nb_goals_against / $pp_team_class->nb_matches, 1) : ''; ?></td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php
		
		// sanctions ?
		$sanctions = array();
		foreach($pp_team_class_arr as $pp_team_class)
		{
			if($pp_team_class->nb_points_sanction != 0) $sanctions[] = $pp_team_class->label . ' : '. $pp_team_class->nb_points_sanction . ' pts';
		}
		if(count($sanctions)) echo '<p>* Sanctions : '.implode(', ', $sanctions).'</p>';
	}
}













function pp_comments_viewed($type, $id_type)
{
	global $db, $user;
	if($user->id_user)
	{
		$SQL = "UPDATE `pp_comments_viewed`
				SET `date_viewed`=NOW()
				WHERE `type`='".$type."' AND `id_type`='".$id_type."'
				AND `id_user`='".$user->id_user."'";
		$result = $db->query($SQL);
		if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());
		if(!$db->affectedRows())
		{
			$SQL = "INSERT INTO `pp_comments_viewed`(`type`, `id_type`, `id_user`, `date_viewed`)
					VALUES('".$type."', '".$id_type."', '".$user->id_user."', NOW())";
			$result = $db->query($SQL);
			if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());			
		}
		return true;
	}	
	return false;
}



function pp_comments_nb_friends()
{
	global $db, $user;
	
	// activité des amis
	// si accès restreint possible sur les listes amis, on cherche la liste de l'user // sauf si user == wall
	$user_listfriends = array();
	$SQL = "SELECT `id_user_listfriends`, `id_user`
			FROM `pp_user_friends`
			WHERE `id_user_friend`='".$user->id_user."'
			AND `valide`='1'";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());
	while($pp_user_friends = $result->fetchRow())
	{
		$user_listfriends[$pp_user_friends->id_user] = $pp_user_friends->id_user_listfriends;
	}
	
	//echo "<pre>"; print_r($user_listfriends); echo "</pre>";
	
	$nb_comments = 0;
	$content = '';									
	$SQL = "SELECT `id_comment`, `id_user_listfriends`, `id_user`, `type`
			FROM `pp_comments`
			WHERE `pp_comments`.`deleted` != '1'
				AND `pp_comments`.`type` = 'wall' AND `pp_comments`.`id_type` != '".$user->id_user."'
				AND (`pp_comments`.`id_user` = '".$user->id_user."'
						OR `pp_comments`.`id_user_listfriends`=0
						".(count($user_listfriends) ? " OR `pp_comments`.`id_user_listfriends` IN (".implode(',', $user_listfriends).") " : "")."
						".(count($user_listfriends) ? " OR `pp_comments`.`id_user_listfriends`=-1 " : "")."
					)
				AND `pp_comments`.`date_creation` > '".$user->date_view_friends."'
			LIMIT 30";
	//echo "<li>$SQL</li>";
	$result = $db->query($SQL);
	if(DB::isError($result))
	{
		die ("<li>$SQL<li>ERROR : ".$result->getMessage());
		
	} else {
		while($pp_comments = $result->fetchRow())
		{
			if($pp_comments->id_user_listfriends == -1 || $pp_comments->id_user_listfriends != -1 && $user_listfriends[$pp_comments->id_user])
				if($pp_comments->type == 'wall' || $pp_comments->type == 'classj')
					$nb_comments++;
		}
	}
	
	return $nb_comments;
}



function pp_comments_afficher($type, $id_type, $options=array())
{
	global $db, $user, $txtlang;
	
	/*
	array(	'admin' => $pp_user->id_user,
			'id_comment' => '',
			'show_context' => true,
			'url_param' => '',
			'parent' => $pp_comments->id_comment,
			'order' => 'ASC',
			'possible_private' => true,
			'possible_list' => true,
			'show' => 5,
			'no_form' => false,
			'hide_form' => true,
			'is_message_prive' => false,
			'date_viewed' => date,
			'herite' => true)
	*/
	if(!$options[order]) $options[order] = 'ASC';
	
	//md5($type.'_pronoplus_'.$id_type.'_pronoplus_'.$pp_comments->id_comment);
	//pp_comments_del='.$pp_comments_key.'&pp_comments_id='.$type.'a'.$id_type.'a'.$pp_comments_id
	
	// suppression commentaire
	//echo "<li>id_user=".$user->id_user." && pp_comments_del=".$_POST[pp_comments_del];
	if($user->id_user && $_GET[pp_comments_del])
	{
		//echo "<li><h1>del 1</h1></li>";
		$pp_comments_post = explode('-', $_GET[pp_comments_id]);		
		if($pp_comments_post[0] == $type && $pp_comments_post[1] == $id_type)
		{
			//echo "<li><h1>del 2</li>";
			if(md5($pp_comments_post[0].'_pronoplus_'.$pp_comments_post[1].'_pronoplus_'.$pp_comments_post[2]) == $_GET[pp_comments_del])
			{
				//echo "<li><h1>del 3</li>";
				$SQL = "UPDATE `pp_comments` SET `deleted`='1'
						WHERE `id_comment`='".$pp_comments_post[2]."'"
						.($options[admin] == $user->id_user ? "" : " AND `id_user`='".$user->id_user."'");
				$result = $db->query($SQL);
				if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());
				//echo "<li>$SQL</li>";
				if($db->affectedRows())
				{
					$SQL = "UPDATE `pp_comments` SET `deleted`='1'
							WHERE `parent_id_comment`='".$pp_comments_post[2]."'";
					$result = $db->query($SQL);
					if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());
					//echo "<li>$SQL</li>";
				}
			}
		}
	}
	
	// insertion commentaire
	if($user->id_user && $_POST[pp_comments_type]==$type && $_POST[pp_comments_id_type]==$id_type && $_POST[pp_comments_key] && trim($_POST[message]) && ($_POST['pp_comments_parent'] && $options['parent'] && $_POST['pp_comments_parent'] == $options['parent'] || !$_POST['pp_comments_parent'] && !$options['parent']))
	{
		if(md5($type.'_pronoplus_'.$id_type.'_pronoplus_'.$_POST['pp_comments_parent']) == $_POST[pp_comments_key])
		{
			// on vérifie que le message n'est pas un doublon
			$SQL = "SELECT `id_comment` FROM `pp_comments`
					WHERE `type`='".$type."' AND `id_type`='".$id_type."' AND `id_user`='".$user->id_user."' AND `footprint`='".$db->escapeSimple(md5(trim($type . $id_type . $_POST[message])))."'
					AND DATE_ADD(`date_creation`, INTERVAL 10 minute) > NOW()";
			$result = $db->query($SQL);
			if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());
			if(!$result->numRows())
			{
				$SQL = "INSERT INTO `pp_comments`(`parent_id_comment`, `type`, `id_type`, `id_user`, `id_user_listfriends`, `message`, `footprint`, `date_creation`)
						VALUES('".($_POST['pp_comments_parent'] * 1)."', '".$db->escapeSimple($type)."', '".$db->escapeSimple($id_type)."', '".$db->escapeSimple($user->id_user)."', '".$db->escapeSimple(trim($_POST[idl]))."','".$db->escapeSimple(trim($_POST[message]))."', '".$db->escapeSimple(md5(trim($type . $id_type . $_POST[message])))."', NOW())";
				$result = $db->query($SQL);
				if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());

                // prévenir le proprio du wall
                if($type == 'wall')
                {
                    if($user_wall = nom_joueur($id_type))
                    {
                        if($user->id_user != $id_type && !$user->no_mail)
                        {
                            sendemail($user_wall->email, null, null,
                                'Un nouveau message sur ton mur Prono+ !',
                                "Un nouveau message a été posté par <b>".htmlspecialchars($user->login)."</b> sur ton mur.<br />Consulte le ici : <a href=\"http://www.pronoplus.com/user.php?q=".urlencode($user_wall->login)."\">http://www.pronoplus.com/user.php?q=".urlencode($user_wall->login)."</a>"
                            );
                        }

                        if($_POST['pp_comments_parent'] * 1)
                        {
                            $SQL = "SELECT DISTINCT `id_user`
                                    FROM `pp_comments`
                                    WHERE
                                        (
                                            `id_comment` = '".$db->escapeSimple($_POST['pp_comments_parent'])."'
                                            OR `parent_id_comment` = '".$db->escapeSimple($_POST['pp_comments_parent'])."'
                                        )
                                        AND id_user != '".$user->id_user."'
                                        AND id_user != '".$user_wall->id_user."'
                                        ";
                            $result_comment = $db->query($SQL);
                            if(DB::isError($result_comment)) die ("<li>$SQL<li>ERROR : ".$result_comment->getMessage());
                            if($pp_comment = $result_comment->fetchRow())
                            {
                                if($user_to_notify = nom_joueur($pp_comment->id_user))
                                {
                                    if(!$user_to_notify->no_mail)
                                    {
                                        sendemail($user_to_notify->email, null, null,
                                            'Un nouvelle réponse à ton message sur Prono+ sur le mur de '.$user_wall->login.' !',
                                            $user->id_user == $user_wall->id_user ?
                                                "Une nouvelle réponse a été postée par <b>".htmlspecialchars($user->login)."</b> à ton message sur son mur.<br />Consulte le ici : <a href=\"http://www.pronoplus.com/user.php?q=".urlencode($user_wall->login)."\">http://www.pronoplus.com/user.php?q=".urlencode($user_wall->login)."</a>"
                                                : "Une nouvelle réponse a été postée par <b>".htmlspecialchars($user->login)."</b> à ton message sur le mur de <b>".$user_wall->login."</b>.<br />Consulte le ici : <a href=\"http://www.pronoplus.com/user.php?q=".urlencode($user_wall->login)."\">http://www.pronoplus.com/user.php?q=".urlencode($user_wall->login)."</a>"
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
			}
		}
	}
	
	$pp_comment_id = 'pp_comment_'.$type.'_'.$id_type.'_'.$options['parent'];
	
	if($options['no_form'] != true)
	{
		
		$formhtml = '';
		
		if($options['hide_form'] == true) $formhtml .= '<button id="btn_form_label_'.$type.'_'.$id_type.'_'.$options['parent'].'" onclick="$(\'btn_form_label_'.$type.'_'.$id_type.'_'.$options['parent'].'\').hide(); $(\''.$pp_comment_id.'\').show();" name="envoyer" type="submit" class="link_button" value="" /><img src="/template/default/comment_new.gif" align="absmiddle" /> '.($options['hide_form_label'] ? $options['hide_form_label'] : 'Commenter').'</button>';
		
		$formhtml .= '<div id="'.$pp_comment_id.'" '.($options['hide_form'] == true ? 'style="display:none;"' : '').'>';
    
        $formhtml .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
        $formhtml .= '<tr><td width="1%" class="comment-user">';

        if(!$user->id_user)
        {
          $formhtml .= '<img src="/template/default/_profil.png" height="30" width="30" border="0" />';

        } else {
            $formhtml .= '<a href="/user.php?q='.urlencode(htmlspecialchars($user->login)).'" class="link_orange">';
            if($avatar = getAvatar($user->id_user, $user->avatar_key, $user->avatar_ext, 'small'))
            {
              $formhtml .= '<img src="/avatars/'.$avatar.'" height="30" width="30" border="0" />';
            } else {
              $formhtml .= '<img src="/template/default/_profil.png" height="30" width="30" border="0" />';
            }
            $formhtml .= '<br />'.htmlspecialchars($user->login).'</a>';
        }

        $formhtml .= '</td><td width="99%" class="parent_show_on_hover comment-new"><div class="b">';
		
		// affichage du formulaire
		if(!$user->id_user)
		{
			$disabled_forum = true;
			$formhtml .= '<p align="center"><br /><strong>Vous devez vous enregistrer pour écrire un commentaire.<br />Vous pouvez <a href="#" onclick="SeConnecter(this); return false;">vous inscrire en quelques secondes</a>.</strong><br /><br /></p>';
		}
		
		if($user->id_user)
		{
			$formhtml .= '<form name="envoyer_msg_'.$type.'_'.$id_type.'_'.$options['parent'].'" method="post" action="#pp_comments_'.$type.'_'.$id_type.'_'.$options['parent'].'">';
			$formhtml .= '<input type="hidden" name="pp_comments_type" value="'.$type.'" />';
			$formhtml .= '<input type="hidden" name="pp_comments_id_type" value="'.$id_type.'" />';
			$formhtml .= '<input type="hidden" name="pp_comments_parent" value="'.$options['parent'].'" />';
			$formhtml .= '<input type="hidden" name="pp_comments_key" value="'.md5($type.'_pronoplus_'.$id_type.'_pronoplus_'.$options['parent']).'" />';
		}
		
		$message_fld = 'envoyer_msg_'.$type.'_'.$id_type.'_'.$options['parent'];

		$formhtml .= '<table width="100%" border="0" cellspacing="1" cellpadding="2">';
		/*
		$formhtml .= '<tr>';
		$formhtml .= '<td style="border:none;"><img onclick="smiley(\':)\', '.$message_fld.')" src="/smileys/1.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':furieux:\', '.$message_fld.')" src="/smileys/2.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':]\', '.$message_fld.')" src="/smileys/3.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':D\', '.$message_fld.')" src="/smileys/4.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':?:\', '.$message_fld.')" src="/smileys/5.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':timide:\', '.$message_fld.')" src="/smileys/6.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':saoul:\', '.$message_fld.')" src="/smileys/7.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\'8)\', '.$message_fld.')" src="/smileys/8.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':p\', '.$message_fld.')" src="/smileys/9.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':triste:\', '.$message_fld.')" src="/smileys/10.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':diable:\', '.$message_fld.')" src="/smileys/11.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':bisou:\', '.$message_fld.')" src="/smileys/12.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\';)\', '.$message_fld.')" src="/smileys/13.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':(\', '.$message_fld.')" src="/smileys/14.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':O\', '.$message_fld.')" src="/smileys/15.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':mignon:\', '.$message_fld.')" src="/smileys/16.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':dodo:\', '.$message_fld.')" src="/smileys/17.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '<img onclick="smiley(\':fou:\', '.$message_fld.')" src="/smileys/18.gif" border="0" onmouseover="this.style.cursor=\'hand\';"> ';
		$formhtml .= '</td>';
		$formhtml .= '</tr>';
		$formhtml .= '<tr>';
		*/

		$formhtml .= '<tr><td style="border:none;"><textarea id="'.$message_fld.'" name="message" cols="40" rows="6" style="width:99%" '.($disabled_forum ? "disabled=\"disabled\"" : "").'></textarea></td>';
		$formhtml .= '</tr>';	
		
		// affichage du menu public / privé
		if($options[possible_private] && $user->id_user != $options[admin])
		{
			$str .= '<tr><td style="border:none;"><img src="/template/default/cadenas.gif" align="absmiddle" /> <select name="idl">';
			$str .= '<option value="">Message public</option>';
			$str .= '<option value="-2">Message privé</option>';
			$str .= '</select></td></tr>';
			$formhtml .= $str;
		}

				
		// si affichage visibilité par liste amis
		if($options[possible_list] && $user->id_user == $options[admin])
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
							$strlist[] = '<option value="'.$pp_user_listfriends->id_user_listfriends.'">par '.htmlspecialchars($pp_user_listfriends->label).' ('.$pp_user_friends->nb_friends.')</option>';
							$totalfriends += $pp_user_friends->nb_friends;
						}
					}
				}

				$str .= '<tr><td style="border:none;"><img src="/template/default/cadenas.gif" align="absmiddle" /> <select name="idl">';
				$str .= '<option value="">Visible par tout Prono+</option>';
				if(count($strlist) > 1) $str .= '<option value="-1">par tous mes amis ('.$totalfriends.')</option>';
				$str .= implode('', $strlist);
				$str .= '</select></td></tr>';
				if($totalfriends > 0) $formhtml .= $str;
			}		
		}
		
		
		$formhtml .= '<tr><td style="border:none;"><button onclick="this.hide()" name="envoyer" type="submit" class="link_button" '.($disabled_forum ? "disabled=\"disabled\"" : "").' value="" /><img src="/template/default/comment_new.gif" align="absmiddle" /> '.($options['submit_label'] ? $options['submit_label'] : 'Shooter mon commentaire').'</button></td></tr>';
		

		$formhtml .= '</table></div></td></tr></table>';
    
		if($user->id_user)
		{
			$formhtml .= '<p><small>Merci :<br />
							- d\'être clair dans votre propos (1 ou 2 mots ne suffisent pas toujours !),<br />
							- de ne pas insulter, ou proférer des messages haineux ou à caractères racistes,<br />
							- d\'écrire en français (et non en abréviation ou SMS),<br />
							- de répondre en rapport avec le sujet de discussion,<br />
							- et de respecter les membres qui vont vous lire !<br />
							En résumé, tout message ne respectant pas la <a href="http://fr.wikipedia.org/wiki/N%C3%A9tiquette" target="_blank">nétiquette</a> seront purement et simplement supprimés.</small></p>';
			$formhtml .= '</form>';
		}
    
		$formhtml .= '</div>';
	}
	
	// affichage commentaires
	$html = '';
	
	if($options[order] != 'ASC') $html .= $formhtml;
	
	// si accès restreint possible sur les listes amis, on cherche la liste de l'user // sauf si user == wall
	$user_listfriends = 0;
	if($options[possible_list] && $user->id_user != $id_type)
	{
		$SQL = "SELECT `id_user_listfriends`
				FROM `pp_user_friends`
				WHERE `id_user`='".$id_type."' AND `id_user_friend`='".$user->id_user."'
				AND `valide`='1'";
		$result = $db->query($SQL);
		if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());
		if($pp_user_friends = $result->fetchRow())
		{
			$user_listfriends = $pp_user_friends->id_user_listfriends;
		}
	}
	
	$SQL = "SELECT `pp_comments`.`id_comment`, `pp_comments`.`id_user_listfriends`, `pp_comments`.`date_creation`,
				`pp_user`.`login`, `pp_user`.`id_user`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`,
				`pp_comments`.`message`,
				DATE_FORMAT(`pp_comments`.`date_creation`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_creation_format`
			FROM `pp_comments`
			INNER JOIN `pp_user` ON `pp_comments`.`id_user` = `pp_user`.`id_user`
			WHERE `pp_comments`.`type`='".$type."' AND `pp_comments`.`id_type`='".$id_type."'
				".($options[id_comment] ? " AND `pp_comments`.`id_comment`='".$options[id_comment]."' " : "")."
				AND `deleted`!='1' AND parent_id_comment='".($options[parent] ? $options[parent] : 0)."'
				AND (
						`pp_comments`.`id_user_listfriends`=-2 AND `pp_comments`.`id_user`='".$user->id_user."'
						OR `pp_comments`.`id_type`='".$user->id_user."'
						OR `pp_comments`.`id_user_listfriends`=0
						".($options[possible_list] && $user->id_user != $id_type && $user_listfriends ? " OR `pp_comments`.`id_user_listfriends`='".$user_listfriends."' " : "")."
						".($options[possible_list] && $user->id_user != $id_type && $user_listfriends ? " OR `pp_comments`.`id_user_listfriends`=-1 " : "")."
					)
			ORDER BY `date_creation` ".($options[order] ? $options[order] : 'DESC')."
			".($options[show] ? 'LIMIT '.($options[show]*1) : '');
	$result = $db->query($SQL);
	//echo "<li>$SQL</li>";
	if(DB::isError($result))
	{
		die ("<li>$SQL<li>ERROR : ".$result->getMessage());
		
	} else {
		if($nbcomments = $result->numRows())
		{
			if($options['show_nb_messages'] !== false) $html .= '<p><b><a name="pp_comments_'.$type.'_'.$id_type.'_'.$options['parent'].'"></a> <img src="/template/default/comment.gif" align="absmiddle" /> '.$nbcomments.' commentaire'.($nbcomments>1 ? 's': '').' :</b></p>';
			
			$html .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">';

			// $class = 'ligne_blanche';
			
			while($pp_comments = $result->fetchRow())
			{
				$class = '';
        //$class = ($class != 'ligne_blanche' ? 'ligne_blanche' : 'ligne_grise');				
				if(!$options[show_context] && $pp_comments->id_user_listfriends == -2 || $options['is_message_prive']) $class = 'ligne_bleu';
				
				$html .= '<tr class="'.$class.'">
						<td width="1%" class="comment-user">
						<a href="/user.php?q='.urlencode(htmlspecialchars($pp_comments->login)).'" class="link_orange">';

				if($avatar = getAvatar($pp_comments->id_user, $pp_comments->avatar_key, $pp_comments->avatar_ext, 'small'))
				{
					$html .= '<img src="/avatars/'.$avatar.'" height="30" width="30" border="0" />';
				} else {
					$html .= '<img src="/template/default/_profil.png" height="30" width="30" border="0" />';
				}
				
				$html_del = '';
				$html_del_js = '';
				
				// si l'utilisateur est l'auteur du message ou si l'utilisateur est admin (c'est son mur par exemple)
				if($pp_comments->id_user == $user->id_user || $options[admin] == $user->id_user)
				{
					$pp_comments_key = md5($type.'_pronoplus_'.$id_type.'_pronoplus_'.$pp_comments->id_comment);
					$html_del = '<span class="show_on_hover"><a href="'.$options[url_param].'&pp_comments_del='.$pp_comments_key.'&pp_comments_id='.$type.'-'.$id_type.'-'. $pp_comments->id_comment.'#pp_comments_'.$type.'_'.$id_type.'" onclick="return confirm(\'Es-tu sûr de vouloir supprimer ce message ?\')" style="font-size:10px; color:#888">Supprimer ce message</a></span>';
					$html_del_js = 'onmouseover="$(\'pp_comments_'.$pp_comments->id_comment.'\').show();" onmouseout="$(\'pp_comments_'.$pp_comments->id_comment.'\').hide();"';
				}
				
				$html .= '<br />'.htmlspecialchars($pp_comments->login).'</a>	
							</td>
							<td width="99%" class="parent_show_on_hover '.($options['date_viewed'] && $options['date_viewed'] < $pp_comments->date_creation ? 'comment-new' : 'comment-normal').'"><div class="b">
								'.(!$options[show_context] && $pp_comments->id_user_listfriends == -2 ? '<img src="/template/default/cadenas.gif" align="absmiddle" /> <b>Message privé</b><br /><br />' : '').'
								'.(!$options[show_context] && ($pp_comments->id_user_listfriends == -1 || $pp_comments->id_user_listfriends > 0) ? '<img src="/template/default/cadenas.gif" align="absmiddle" /> <b>Message visible par certains amis</b><br /><br />' : '');
				
				if($options[show_context])
				{
					$pp_user_wall = nom_joueur($id_type);
					
					if($pp_comments->id_user == $id_type)
					{
						$html .= '<i>sur <a href="/user.php?q='.urlencode(htmlspecialchars($pp_user_wall->login)).'" class="link_orange">son mur</a></i> : <br /><br />';
						
					} else {
						if($avatar = getAvatar($id_type, $pp_user_wall->avatar_key, $pp_user_wall->avatar_ext, 'small'))
						{
							$htmlavatar = '<img src="/avatars/'.$avatar.'" height="30" width="30" align="absmiddle" border="0" />';
						} else {
							$htmlavatar = '<img src="/template/default/_profil.png" height="30" width="30" align="absmiddle" border="0" />';
						}
						$html .= '<i>sur <a href="/user.php?q='.urlencode(htmlspecialchars($pp_user_wall->login)).'" class="link_orange">le mur de '.$htmlavatar.' '.htmlspecialchars($pp_user_wall->login).'</a></i> : <br /><br />';
					}
				}
				
				$html .= formattexte($pp_comments->message).'
								<br /><br /><span style="font-size:10px; color:#aaa">Le ' . $pp_comments->date_creation_format . $html_del . '</span>';
				
				if($options['herite'])
				{
					$html .= '<br />' . pp_comments_afficher($type, $id_type, array('admin' => $options[admin], 'url_param' => $options[url_param], 'parent' => $pp_comments->id_comment, 'is_message_prive' => ($pp_comments->id_user_listfriends == -2 ? true : false), 'show_nb_messages' => ($pp_comments->id_user_listfriends == -2 ? false : true),'order' => 'ASC', 'hide_form' => true, 'hide_form_label' => ($pp_comments->id_user_listfriends == -2 ? 'Répondre' : ''), 'show' => 5, 'submit_label' => ($pp_comments->id_user_listfriends == -2 ? 'Shooter ma réponse' : '')));
				}
				
				$html .= '</div></td></tr>';

				/*if($options['herite'])
				{
					$class = ($class != 'ligne_blanche' ? 'ligne_blanche' : 'ligne_grise');					
					$html .= '<tr class="'.$class.'">
							<td>&nbsp;</td>
							<td style="padding-bottom:20px;">';
					$html .= pp_comments_afficher($type, $id_type, array('admin' => $options[admin], 'url_param' => $options[url_param], 'parent' => $pp_comments->id_comment, 'is_message_prive' => ($pp_comments->id_user_listfriends == -2 ? true : false), 'show_nb_messages' => ($pp_comments->id_user_listfriends == -2 ? false : true),'order' => 'ASC', 'hide_form' => true, 'hide_form_label' => ($pp_comments->id_user_listfriends == -2 ? 'Répondre' : ''), 'show' => 5, 'submit_label' => ($pp_comments->id_user_listfriends == -2 ? 'Shooter ma réponse' : '')));
					$html .= '</td></tr>';
				}*/
			}
			$html .= '</table><br />';
		}
	}
	
	if($options[order] == 'ASC') $html .= $formhtml;
	
	return $html;
}
