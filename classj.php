<?php
/**
* Project: PRONOPLUS
* Description: Classement journée
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-08-14
* Version: 1.0
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();


if(!$_GET[id]) HeaderRedirect('/');


// recherche journée
$SQL = "SELECT `id_matches`, `pp_matches`.`label`, `pp_matches`.`image`, `pp_matches`.`date_first_match`, `pp_matches`.`date_calcul`
		FROM `pp_matches`
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
		HeaderRedirect('/pronostiquer.php?id=' . $_GET[id]);
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


$class_provisoire = 0;

// nombre de joueurs dans le classement définitif ?
$SQL = "SELECT COUNT(`id_user`) AS NBUSERS
		FROM `pp_class_user`
		WHERE `id_class`=1 AND `id_matches`='".$pp_matches->id_matches."'
		".($friends_ids != '' ?  " AND `pp_class_user`.`id_user` IN (".$friends_ids.")" : ""); // filtre amis;
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
			
			$class_provisoire = -1; // pas de classement provisoire	=> redirection vers la simulation			
			
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
			
			if($class_provisoire == -1) HeaderRedirect('/simulation.php?id='.$pp_matches->id_matches);
		}
		
	} else HeaderRedirect('/pronostiquer.php?id=' . $_GET[id]);
}
	



// recherche classement du joueur
if($_GET[search_joueur] && $_GET[rech_jpseudo]) {
	$SQL = "SELECT `pp_user`.`id_user`, `".$table_class_user."`.`class`
			FROM `".$table_class_user."` INNER JOIN `pp_user` ON `pp_user`.`id_user`=`".$table_class_user."`.`id_user`
			WHERE `".$table_class_user."`.`id_class`=1 AND `".$table_class_user."`.`id_matches`='".$_GET[id]."'
				AND `pp_user`.`login`='".$db->escapeSimple(trim($_GET[rech_jpseudo]))."'";
	$result_class = $db->query($SQL);
	if(DB::isError($result_class))
	{
		die ("<li>ERROR : ".$result_class->getMessage());
		
	} else {
		if($pp_class = $result_class->fetchRow())
		{
			$page=(ceil($pp_class->class/20)-1)*20;
			HeaderRedirect("/classj.php?id=".$_GET[id]."&sqldep=".$page."&selj=".$pp_class->id_user."&rech_jpseudo=".$_GET[rech_jpseudo]."#joueur".$pp_class->id_user);
			
		} else $class_introuvable=true;
	} 
}




// recherche matchs
$matches = array();
$ids_match = "";
$SQL = "SELECT `pp_match`.`id_match`, `pp_match`.`id_info_match`,
		`team_host`.`label` AS `team_host_label`, `team_host`.`flag` AS `team_host_flag`,
		`team_visitor`.`label` AS `team_visitor_label`, `team_visitor`.`flag` AS `team_visitor_flag`,
		`pp_match`.`score`,
		DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`,
		IF(`pp_match`.`date_match` <= NOW(), 1, 0) AS `match_a_commence`
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


$meta_description = array();
if(is_array($matches)) foreach($matches as $match)
{
	$meta_description[] = $match->team_host_label.($match->score != '' ? ' '.$match->score.' ' : ' - ').$match->team_visitor_label;
}
$meta_description = 'Résultats ' . implode(', ', $meta_description);

$title_page = "Classement".($type_calcul=='provisoire' ? ' provisoire' : '')." pronostics de ".formatDbData($pp_matches->label);
pageheader($title_page." | Prono+", array('meta_description' => $meta_description));

?>
<div id="content_fullscreen">
<?php
// affichage des onglets
echo getOnglets('classement');
?>



<div id="content">

<h1 class="title_green"><?php echo $title_page?></h1>


<?php
echo "<table width=\"100%\" border=\"0\" cellspacing=\"4\" cellpadding=\"0\"><tr>
		<td valign=\"top\" width=\"1%\"><img src=\"template/default/".$pp_matches->image."\" class=\"preview_matches_image\" border=\"0\" /></td>
		<td  valign=\"top\" width=\"99%\">";

if(!$user->id_user || $class_provisoire)
{
  // classement ?
  echo getMatchesClass($_GET[id]);
  
} else {

  // classement ?
  $class_datas = array();
  $SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`label`, `pp_class`.`last_id_matches`
      FROM `pp_class`
      INNER JOIN `pp_matches_class` ON `pp_class`.`id_class`=`pp_matches_class`.`id_class`
      WHERE `pp_matches_class`.`id_matches`='".$pp_matches->id_matches."'
      ORDER BY `pp_class`.`order`";
  $result_class = $db->query($SQL);
  //echo "<li>$SQL";
  if(DB::isError($result_class))
  {
    die ("<li>ERROR : ".$result_class->getMessage());
    
  } else {	

    $is_class_current = false;
    while($pp_class = $result_class->fetchRow())
    {
      $class_data = array();
      $class_data['label'] = "<a href=\"/class.php?id=".$pp_class->id_class."\" class=\"link_orange\">".formatDbData(str_replace('Classement', '', $pp_class->label))."</a>";
      
      // classement actuel
      if($pp_class->last_id_matches != $pp_matches->id_matches)
      {
        $SQL = "SELECT `class` FROM `pp_class_user`
                WHERE `id_user`=".$user->id_user." AND `id_class`=".$pp_class->id_class." AND `id_matches`=".$pp_class->last_id_matches."
                LIMIT 1";
        $rclass = $db->query($SQL);
        if(!DB::isError($rclass))
        {
          if($pp_class_user = $rclass->fetchRow())
          {
            $class_data['class_current'] = $pp_class_user->class;
            $is_class_current = true;
          }
        }
      }

      // classement grille actuelle
      $SQL = "SELECT `class` FROM `pp_class_user`
              WHERE `id_user`=".$user->id_user." AND `id_class`=".$pp_class->id_class." AND `id_matches`=".$pp_matches->id_matches."
              LIMIT 1";
      $rclass = $db->query($SQL);
      if(!DB::isError($rclass))
      {
        if($pp_class_user = $rclass->fetchRow())
        {
          $class_data['class_matches'] = $pp_class_user->class;          
        }
      }
      
      // classement avant cette grille
      $SQL = "SELECT `class` FROM `pp_class_user`
              WHERE `id_user`=".$user->id_user." AND `id_class`=".$pp_class->id_class." AND `date_calcul` < '".$pp_matches->date_calcul."'
              AND `id_matches` != ".$pp_matches->id_matches."
              ORDER BY `date_calcul` DESC
              LIMIT 1";
      $rclass = $db->query($SQL);
      // echo "<li>$SQL";
      if(!DB::isError($rclass))
      {
        if($pp_class_user = $rclass->fetchRow())
        {
          $class_data['class_before'] = $pp_class_user->class;
        }
      }
      
      $class_datas[] = $class_data;
    }
    
    if(count($class_datas))
    {
      echo '<table cellpadding="4" style="border:1px solid #ccc"><tr><th>Cette grille compte pour les classements : </th>';
      foreach($class_datas as $data)
      {
        echo '<th>' . $data['label'] . '</th>';
      }
      echo '</tr><tr><td align="right">Mon classement avant cette grille :</td>';
      foreach($class_datas as $data)
      {
        echo '<td align="center">' . ($data['class_before'] ? $data['class_before'] . ($data['class_before']>1 ? 'è' : 'er') : '-') . '</td>';
      }
      echo '</tr><tr class="ligne_grise"><td align="right">Mon classement après cette grille :</td>';
      foreach($class_datas as $data)
      {
        echo '<td align="center">' . ($data['class_matches'] ? $data['class_matches'] . ($data['class_matches']>1 ? 'è' : 'er') . ' ' . ($data['class_matches'] && $data['class_before'] ? evolution_format($data['class_before'] - $data['class_matches']) : '') : '-') . '</td>';
      }
      echo '</tr>';
      
      if($is_class_current)
      {
        echo '<tr><td align="right">Mon classement actuel :</td>';
        foreach($class_datas as $data)
        {
          echo '<td align="center">' . ($data['class_current'] ? $data['class_current'] . ($data['class_current']>1 ? 'è' : 'er') . ' ' . ($data['class_matches'] && $data['class_current'] ? evolution_format($data['class_matches'] - $data['class_current']) : '') : '-') . '</td>';
        }
        echo '</tr>';
      }
      
      echo '</table>';
    }
  }
}

// $NBUSERS = getNbUsersMatches($_GET[id]);
// if($NBUSERS) echo "<p><strong>".$NBUSERS."</strong> joueurs ont pronostiqué ces matchs.</p>";

echo '<p><a href="#comments" class="link_orange"><img src="/template/default/comment.gif" align="absmiddle" border="0" /> commenter</a></p>';


// utilisateur et date de la simulation
if($class_provisoire==1 && $type_calcul=='provisoire')
{
	$SQL = "SELECT id_user,
				DATE_FORMAT(`date_calcul`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_calcul_format`,
				TIMEDIFF(NOW(), `date_calcul`) AS `diff_date_calcul`
			FROM `pp_class_temp`
			WHERE id_matches='".$pp_matches->id_matches."'
			ORDER BY `date_calcul` DESC
			LIMIT 1";
	$result_user_simul = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_user_simul))
	{
		die ("<li>ERROR : ".$result_user_simul->getMessage());
		
	} else {
		if($pp_user_simul = $result_user_simul->fetchRow())
		{
			// recherche du joueur ?
			$user_simul = nom_joueur($pp_user_simul->id_user);
			$avatar = getAvatar($pp_user_simul->id_user, $user_simul->avatar_key, $user_simul->avatar_ext, 'small');
			$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
			
			echo "<p>Classement provisoire calculé <strong>il y a ".format_diff_date($pp_user_simul->diff_date_calcul, false, true)."</strong> par <img src=\"".$avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" vspace=\"2\" />&nbsp;<strong><a href=\"/user.php?q=".urlencode(htmlspecialchars($user_simul->login))."\" class=\"link_orange\">".$user_simul->login."</a></strong><br /><a href=\"/simulation.php?id=".$pp_matches->id_matches."\" class=\"link_button\">Modifier le classement provisoire</a> <a href=\"/pronostiquer.php?id=".$pp_matches->id_matches."\" class=\"link_button\">Pronostiquer ou modifier mes pronostics</a></p>";
		}
	}
}


echo "</td></tr></table>";
?>

<table width="100%"  cellpadding="2" cellspacing="1">
<tr> 
<th width="5%" align="center">Matchs</th>
<th width="20%">&nbsp;</th>
<th width="5%" align="center">Score</th>
<th width="20%">&nbsp;</th>
<th width="10%" nowrap="nowrap">Pts moy</th>
<th width="40%">Tendances des r&eacute;sultats</th>
</tr>
<?php
$anecdotes = array();

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
	
	$url_info_match = "/info_match.php?id=".$match->id_info_match."&idclass=".$_GET[id];
	$title_info_match = "Noter et commenter ".$match->team_host_label.' - '.$match->team_visitor_label;
	
	$tabscore = explode('-', $match->score);
?>
	<tr> 
	<td bgcolor="#eeeeee" align="center"><b>M<?php echo $n ?></b></td>
	<td nowrap="nowrap" align="right"><a href="<?php echo $url_info_match?>" class="link_orange" title="<?php echo htmlspecialchars($title_info_match);?>"><?php echo $tabscore[0]>$tabscore[1] ? '<b>' . $match->team_host_label . '</b>' : $match->team_host_label; ?><?php if($match->team_host_flag && $match->team_visitor_flag) echo '&nbsp;<img src="/image/flags/'.$match->team_host_flag.'" align="absmiddle" border="0" />'; ?></a></td>
	<td bgcolor="#eeeeee" align="center"><a href="<?php echo $url_info_match?>" class="link_orange" title="<?php echo htmlspecialchars($title_info_match);?>">
	<?php if($match->score == "R-R") {
		echo "<font color=\"red\">Annul&eacute;</font>";
	} else {
		echo $match->score;
	} ?>
	</a></td>
	<td nowrap="nowrap"><a href="<?php echo $url_info_match?>" class="link_orange" title="<?php echo htmlspecialchars($title_info_match);?>"><?php if($match->team_host_flag && $match->team_visitor_flag) echo '<img src="/image/flags/'.$match->team_visitor_flag.'" align="absmiddle" border="0"  />&nbsp;'; ?><?php echo $tabscore[0]<$tabscore[1] ? '<b>' . $match->team_visitor_label . '</b>' : $match->team_visitor_label;; ?></a></td>
	<td align="center">
	<?php
	if($match->score != "R-R" && $nb_joueurs_tendance>0) {
		$ptsmoy = round(($tendances[1]->PTSWONAVG * $tendances[1]->NBUSERS + $tendances[2]->PTSWONAVG * $tendances[2]->NBUSERS + $tendances[3]->PTSWONAVG * $tendances[3]->NBUSERS) / $nb_joueurs_tendance);
		echo $ptsmoy;
	} else echo '-'; ?>
	</td>
	<td style="padding:0">
	<?php
	if($match->score != "R-R" && $nb_joueurs_tendance>0) { ?>
	<table width="100%" cellpadding="2">
		<tr>
			<?php
			$totalpercent = 0;
			
			$percent_win = round(100 * $tendances[1]->NBUSERS / $nb_joueurs_tendance);
			if($percent_win<5 && $percent_win>0)
			{
			    $anecdotes['bon_resultats'][$n]['libelle'] = ($tendances[1]->NBUSERS == 1 ? ' Un seul joueur a trouvé le <b>bon score</b> de ' : $tendances[1]->NBUSERS . ' joueurs ont trouvé le <b>bon score</b> de ').'M'.$n.'. '.$match->team_host_label.' - '.$match->team_visitor_label . ' ('.$match->score.')';
			    
			    
		        // recherche de ces joueurs chanceux		
		        $SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`
		        FROM `".$table_match_user."`
		        INNER JOIN `pp_user` ON `pp_user`.`id_user`=`".$table_match_user."`.`id_user`
		        WHERE `".$table_match_user."`.`id_match`=".$match->id_match."
		        AND `".$table_match_user."`.`type_result`=1";
		        $result_users = $db->query($SQL);
		        if(DB::isError($result_users))
		        {
			        die ("<li>ERROR : ".$result_users->getMessage());
			
		        } else {
			        while($pp_user = $result_users->fetchRow())
			        {
				        $anecdotes['bon_resultats'][$n]['joueurs'][] = $pp_user;
			        }
		        }
			}
			
			if($percent_win) { 
			?><td class="result_gagne" width="<?php echo $percent_win?>%"><a href="javascript:" style="color:#fff; text-decoration:none; font-weight:bold;" title="<?php echo $tendances[1]->NBUSERS == 1 ? 'Un seul joueur a trouvé le bon score. Il a misé '.$tendances[1]->PTSAVG.' points et a gagné '.$tendances[1]->PTSWONAVG.' points.' : $tendances[1]->NBUSERS.' joueurs ont trouvé le bon score. Ils ont misé une moyenne de '.$tendances[1]->PTSAVG.' points et ont gagné une moyenne de '.$tendances[1]->PTSWONAVG.' points.' ?> "><?php echo $percent_win?>%</a></td>
			<?php } ?>
			
			<?php
			$percent_draw = round(100 * $tendances[2]->NBUSERS / $nb_joueurs_tendance);
			if($percent_win==0 && $percent_draw < 5 && $percent_draw > 0)
			{
			    $anecdotes['bon_resultats'][$n]['libelle'] = ($tendances[2]->NBUSERS == 1 ? 'Un seul joueur a trouvé le <b>bon résultat</b> de ' : $tendances[2]->NBUSERS . ' joueurs ont trouvé le <b>bon résultat</b> de ').'M'.$n.'. '.$match->team_host_label.' - '.$match->team_visitor_label . ' ('.$match->score.')';
			    
		        // recherche de ces joueurs chanceux		
		        $SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`
		        FROM `".$table_match_user."`
		        INNER JOIN `pp_user` ON `pp_user`.`id_user`=`".$table_match_user."`.`id_user`
		        WHERE `".$table_match_user."`.`id_match`=".$match->id_match."
		        AND `".$table_match_user."`.`type_result`=2";
		        $result_users = $db->query($SQL);
		        if(DB::isError($result_users))
		        {
			        die ("<li>ERROR : ".$result_users->getMessage());
			
		        } else {
			        while($pp_user = $result_users->fetchRow())
			        {
				        $anecdotes['bon_resultats'][$n]['joueurs'][] = $pp_user;
			        }
		        }
			}
			
			if($percent_draw) { ?>
			<td class="result_nul" width="<?php echo $percent_draw?>%"><a href="javascript:" style="color:#fff; text-decoration:none; font-weight:bold;" title="<?php echo $tendances[2]->NBUSERS == 1 ? 'Un seul joueur a trouvé le bon résultat. Il a misé '.$tendances[2]->PTSAVG.' points et a gagné '.$tendances[2]->PTSWONAVG.' points.' : $tendances[2]->NBUSERS.' joueurs ont trouvé le bon résultat. Ils ont misé une moyenne de '.$tendances[2]->PTSAVG.' points et ont gagné une moyenne de '.$tendances[2]->PTSWONAVG.' points.'; ?>"><?php echo $percent_draw?>%</a></td>
			<?php } ?>
			
			<?php
			$percent_lose = round(100 * $tendances[3]->NBUSERS / $nb_joueurs_tendance); 
			if($percent_lose > 95)
			{
			    $anecdotes['surprises'][$n]['libelle'] = 'M'.$n.'. '.$match->team_host_label.' - '.$match->team_visitor_label . ' ('.$match->score.') : '.$percent_lose.'% de mauvais pronostics';
			}
			
			if($percent_lose < 2)
			{
			    $anecdotes['sans_surprise'][$n]['libelle'] = 'M'.$n.'. '.$match->team_host_label.' - '.$match->team_visitor_label . ' ('.$match->score.') : '.(100-$percent_lose).'% de bons pronostics (scores et résultats justes compris)';
			}
			
			if($percent_lose) { ?>
			<td class="result_defaite" width="<?php echo $percent_lose?>%"><a href="javascript:" style="color:#fff; text-decoration:none; font-weight:bold;" title="<?php echo $tendances[3]->NBUSERS == 1 ? 'Un seul joueur a mal joué ce match et a misé' : $tendances[3]->NBUSERS . ' joueurs ont mal joué ce match et ont misé une moyenne de'; ?>  <?php echo $tendances[3]->PTSAVG?> points."><?php echo $percent_lose?>%</a></td>
			<?php } ?>
		</tr>
	</table>
	<?php } else echo '&nbsp;'; ?>
	</td>
	</tr>
<?php
	$n++;
}
?>
</table>

<?php
$anecdotes_index = array(
    'bon_resultats' => 'Coup de chance',
    'surprises' => 'Grosse surprise',
    'sans_surprise' => 'C\'était courru d\'avance',
);
foreach($anecdotes_index as $index=>$libelle)
{
    if(is_array($anecdotes[$index]) && count($anecdotes[$index]))
    {
        echo "<hr /><p><b>".$libelle." :</b><ul>";
        foreach($anecdotes[$index] as $row)
        {
            echo "<li style=\"margin-bottom:1px\">".$row['libelle'];
            
            echo "<!--\n";
            print_r($row['joueurs']);
            echo "\n-->";
            
            $joueurs_en_avant = array();
            if(is_array($row['joueurs']) && count($row['joueurs']))
            {
                foreach($row['joueurs'] as $joueur)
                {
                    if($avatar = getAvatar($joueur->id_user, $joueur->avatar_key, $joueur->avatar_ext, 'small')) {
	                    $joueur_avatar = '<img src="/avatars/'.$avatar.'" height="30" width="30" border="0" align="absmiddle" />';
                    } else {
	                    $joueur_avatar = '<img src="/template/default/_profil.png" height="30" width="30" border="0" align="absmiddle" />';
                    }
                    
                    $joueurs_en_avant[] = '<a href="/classj.php?id='.$_GET['id'].'&search_joueur=1&rech_jpseudo='.urlencode(htmlspecialchars($joueur->login)).'" class="link_button">' . $joueur_avatar . '&nbsp;' . htmlspecialchars($joueur->login) . '</a>';
                }
            }
            
            if(count($joueurs_en_avant))
            {
                echo ' : ' . implode(' ', $joueurs_en_avant);
            }
            
            echo "</li>";
        }
        echo "</ul></p>";
    }
}
?>

<br /><br />

<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td colspan="2" style="padding:0;"><img src="/template/default/blocgrishaut.gif" border="0" /></td></tr>
<tr>
<?php /* Aller à mon classement */ ?>
<td align="left" valign="top" style="background:#eee"><?php if($user->id_user) { ?><a href="/classj.php?id=<?php echo $_GET[id]?>&rech_jpseudo=<?php echo $user->login?>&search_joueur=1" class="link_button"><img src="/template/default/zoom.png" border="0" align="absmiddle" /> Aller à mon classement</a><?php } ?></td>


<?php /* Recherche classement d'un joueur */ ?>
<td align="right" valign="top" style="background:#eee"><a name="arcp"></a>
<form method="get" action="classj.php#arcp">
<?php
if($class_introuvable) echo "<font color=red><b>Pas de joueur trouvé !</b></font><br>"; ?>
<img src="/template/default/zoom.png" border="0" align="absmiddle" /> Rechercher le classement d'un joueur <input name="rech_jpseudo" type="text" size="12" maxlength="100" value="<?php echo $rech_jpseudo?htmlspecialchars(stripslashes($rech_jpseudo)):"son pseudo";?>" <?php echo !$rech_jpseudo?"onfocus=\"this.value=''\"":"";?>>&nbsp;<input type="hidden" name="search_joueur" value="1"><input type="hidden" name="id" value="<?php echo $_GET[id]?>"><input type="submit" name="search_joueur" value="Ok" class="link_button" />
</form>
</td>
</tr>


<?php if($user->id_user) { ?>
<tr><td colspan="2" style="padding:2px; background:url(/template/default/separplus.gif) #eee repeat-x" height="3"></td></tr>
<tr>
<td colspan="2" style="background:#eee">
<?php /* Filtre amis */
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

		$str .= '<a name="friends"></a><form id="class_select_liste" method="get" action="/classj.php#friends">';
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
</td></tr>
<?php } ?>
<tr><td colspan="2" style="padding:0;"><img src="/template/default/blocgrisbas.gif" border="0" /></td></tr>
</table><br />



<a name="class"></a>
<?php
if(!$_GET[sqldep]) $_GET[sqldep] = $_POST[sqldep];
if(!$_GET[sqldep]) $sqldep = 0; else $sqldep = $_GET[sqldep];	

$extension = "&id=".$_GET[id].($_GET[idl] ? "&idl=".$_GET[idl] : "")."#class";
$pagego = "classj.php";
pagination($pagego, $sqldep, $nb_element, 20, $extension);
?><br />

<table width="100%" cellpadding="2" cellspacing="1">
<tr>
	<?php if($friends_ids != '') { ?><th width="5%">Rang amis</th><?php } ?>
	<th width="5%">Rang <?php if($friends_ids != '') { echo 'réel'; } ?></th>
	<th colspan="2" width="20%" align="center">Joueur</th>
	<th width="10%" align="center">Points</th>
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

	<th width="10%" align="center">Scores justes</th>
	<th width="10%" align="center">Résultats justes</th>
</tr>
<?php
$color1_a = "bbbbbb";
$color1_b = "aaaaaa";
$color2_a = "eeeeee";
$color2_b = "ffffff";
$altern = 0;


	

// tableau
$SQL = "SELECT `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`,
			`".$table_class_user."`.`id_user`,
			`".$table_class_user."`.`class`, `".$table_class_user."`.`nb_score_ok`, `".$table_class_user."`.`nb_result_ok`,
			`".$table_class_user."`.`nb_matches`, `".$table_class_user."`.`nb_points`,
			DATE_FORMAT(`".$table_class_user."`.`date_last_pronostic`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_last_pronostic_format`
		FROM `".$table_class_user."` INNER JOIN `pp_user` ON `pp_user`.`id_user`=`".$table_class_user."`.`id_user`
		WHERE `".$table_class_user."`.`id_class`=1 AND `".$table_class_user."`.`id_matches`='".$_GET[id]."'
			".($friends_ids != '' ?  " AND `".$table_class_user."`.id_user IN (".$friends_ids.")" : "")."
			ORDER BY `".$table_class_user."`.`class`
			LIMIT ".($sqldep?$sqldep:0).", 20";
$result = $db->query($SQL);
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage());
	
} else {
	$rang = ($sqldep?$sqldep:0);
	while($pp_class_user = $result->fetchRow())
	{   
		$rang++;
		$match_user = array();
		$SQL = "SELECT `id_user`, `id_match`, `score`, `pts`, `type_result`, `pts_won`
		FROM `".$table_match_user."`
		WHERE `id_user`='".$pp_class_user->id_user."' AND `id_match` IN (".$ids_match.")";
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
		
		if($altern) {
			$class_line = 'ligne_grise';
			$altern = 0;
		} else {
			$class_line = 'ligne_blanche';
			$altern = 1;
		}
		
		if($_GET[selj] == $pp_class_user->id_user) $class_line = 'ligne_selected';
?>
<tr class='<?php echo $class_line?>' onmouseover="this.className='ligne_rollover'" onmouseout="this.className='<?php echo $class_line?>'">

<?php if($friends_ids != '') { ?><td align="center"><?php echo $rang; ?></td><?php } ?>

<td align="center"><a name="joueur<?php echo $pp_class_user->id_user?>"></a><?php echo $pp_class_user->class?></td>
<td align="center"><a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_class_user->login))?>" class="link_orange">
<?php
if($avatar = getAvatar($pp_class_user->id_user, $pp_class_user->avatar_key, $pp_class_user->avatar_ext, 'small')) {
?>
	<img src="/avatars/<?php echo $avatar?>" height="30" width="30" border="0" />
<?php } else { ?>
	<img src="/template/default/_profil.png" height="30" width="30" border="0" />
<?php } ?>
</a>
</td>
<td><a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_class_user->login))?>" class="link_orange"><?php
if($pp_class_user->id_user != $user->id_user) {
	echo $pp_class_user->login;
} else {
	echo "<font color=\"red\"><b>".$pp_class_user->login."</b></font>";
}
?>
</a></td>
<td align="center"><strong title="Pronostiqué le <?php echo $pp_class_user->date_last_pronostic_format; ?>" style="cursor:help"><?php echo $pp_class_user->nb_points?></strong></td>
<?php
foreach($matches as $id_match=>$match)
{
	if(!$match_user[$id_match]->score)
	{
?>
<td align="center"><a href="javascript:" style="color:#000000; text-decoration:none; font-weight:bold;" title="Match non joué">&nbsp;-&nbsp;</a></td>
<?php
	} else {
	
		$color="#dddddd";
		$fact = 0;
		
		if($matches[$id_match]->match_a_commence)
		{
			if($match_user[$id_match]->type_result == 1) {
				$color = 'result_gagne';
				$fact = 10;
			
			} elseif($match_user[$id_match]->type_result == 2) {
				$color = 'result_nul';
				$fact = 3;
			
			} elseif($match_user[$id_match]->type_result == 3) {
				$color = 'result_defaite';
				$fact = 0;
			
			} elseif($match_user[$id_match]->type_result == 4) {
				$color="result_neutre";
				$fact = 1;
			
			}
		} else {
			$match_user[$id_match]->type_result = 0;
		}
?>
<td align="center" class="<?php echo $color?>">
<?php if($match_user[$id_match]->type_result > 0) { ?>
	<a href="javascript:" style="color:#fff; text-decoration:none; font-weight:bold;" title="<?php echo $matches[$id_match]->team_host_label.' '.$matches[$id_match]->score.' '.$matches[$id_match]->team_visitor_label.". Points gagnés : ".$match_user[$id_match]->pts." x ".$fact." = ".$match_user[$id_match]->pts_won?>"><?php echo $match_user[$id_match]->score?></a>
<?php
} else {
	echo $pp_class_user->id_user == $user->id_user ? '<a href="/pronostiquer.php?id='.$pp_matches->id_matches.'" class="link_orange" title="Modifier mes pronostics">'.$match_user[$id_match]->score.'</a>' : 'X';
}
?>
</td>
<?php
	}
}
?>
<td align="center"><?php echo $pp_class_user->nb_score_ok?></td>
<td align="center"><?php echo $pp_class_user->nb_result_ok?></td>
</tr>
<?php }
} ?>
</table><br />
			
			
<?php
pagination($pagego, $sqldep, $nb_element, 20, $extension);
?>

<hr />
<p class="message_error">
    Classement en cas d'égalité de points entre joueurs : le meilleur nombre de scores justes, sinon le meilleur nombre de résultats justes, sinon le premier qui a pronostiqué (date d'enregistrement du pronostic faisant foi).
</p>
<p align="center"><a href="historique-resultats.php" class="link_orange"><img src="/template/default/histo_ico.png" style="border:solid 3px #eee;" height="30" align="absmiddle" />&nbsp;Historique de tes résultats</a></p>

<a name="comments"></a>
<h2 class="title_orange">Commentaires</h2>
<?php
$pp_comments_id_type = substr($pp_matches->date_first_match, 0, 4) . $pp_matches->id_matches;
$date_viewed = pp_comments_get_dateviewed('classj', $pp_comments_id_type);
echo pp_comments_afficher('classj', $pp_comments_id_type, array('url_param' => 'classj.php?id='.$_GET[id], 'date_viewed' => $date_viewed));
$nb_comments = pp_comments_nb('classj', $pp_comments_id_type);
if($nb_comments) pp_comments_viewed('classj', $pp_comments_id_type);
?>


</div>
</div>

<?php
pagefooter();
?>
