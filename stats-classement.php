<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$pp_league = 0;
if($_GET[id])
{
	// recherche championnat
	$SQL = "SELECT id_league, label
			FROM `pp_league`
			WHERE `afficher_classement`='1' AND id_league='".$db->escapeSimple($_GET[id])."'";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if(!$pp_league = $result->fetchRow())
		{
			HeaderRedirect('/stats-classement.php');
		}
	}
}


if($pp_league->id_league)
{
	// recherche journée
	if(!$_GET[j])
	{
		$SQL = "SELECT `pp_info_matches`.`day_number`
				FROM `pp_info_matches`
				INNER JOIN `pp_info_match` ON `pp_info_matches`.`id_info_matches` = `pp_info_match`.`id_info_matches` AND `pp_info_match`.`score`!=''
				WHERE `pp_info_matches`.`id_league`='".$pp_league->id_league."' AND `pp_info_matches`.`day_number`>0
					AND `pp_info_matches`.date_first_match < NOW()					
				ORDER BY `pp_info_matches`.`day_number` DESC LIMIT 1";
		$result = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result))
		{
			die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
			
		} else {
			if($pp_day_number = $result->fetchRow())
			{
				HeaderRedirect('/stats-classement.php?id='.$pp_league->id_league.'&j='.$pp_day_number->day_number);
			} else {
				HeaderRedirect('/stats-classement.php?id='.$pp_league->id_league.'&j=1');
			}
		}
		
	} else {
		$SQL = "SELECT `id_info_matches`, `day_number`
				FROM `pp_info_matches`
				WHERE `id_league`='".$pp_league->id_league."'
					AND day_number='".$db->escapeSimple($_GET[j])."'
				LIMIT 1";
		$result = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result))
		{
			die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
			
		} else {
			if(!$pp_day_number = $result->fetchRow())
			{
				HeaderRedirect('/stats-classement.php?id='.$pp_league->id_league);
			}
		}
	}
}

$saison_en_cours = getConfig('saison_en_cours');

$titrepage = $pp_league->label.' - '.$pp_day_number->day_number.($pp_day_number->day_number==1 ? 'ère' : 'ème').' journée';

$user = user_authentificate();
pageheader($titrepage);
?>


<div id="content_fullscreen">
	<?php
	// affichage des onglets
	echo getOnglets();
	?>
	<div id="content">
	
	
	<?php
	echo '<h2 class="title_orange">Résultats championnats</h2>';	
	
	
	// Liste championnats
	$SQL = "SELECT id_league, label, flag
			FROM `pp_league`
			WHERE `afficher_classement`='1'
        AND id_league IN (SELECT DISTINCT id_league FROM `pp_info_matches`)
			ORDER BY `ordre`";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		echo '<p><table cellpadding="10"><tr>';
		$i = 0;
		while($pp_league_info = $result->fetchRow())
		{
			if($pp_league_info->id_league != $pp_league->id_league)
				echo '<td><a href="/stats-classement.php?id='.$pp_league_info->id_league.'" class="link_button">'.($pp_league_info->flag ? '<img src="/image/flags/'.$pp_league_info->flag.'" border="0" align="absmiddle" />&nbsp;' : '').$pp_league_info->label.'</a></td>';
			else
				echo '<td><b>'.($pp_league_info->flag ? '<img src="/image/flags/'.$pp_league_info->flag.'" border="0" align="absmiddle" />&nbsp;' : '').$pp_league_info->label.'</b></td>';
			
			// $i++;
			// if($i == 4) {
				// $i=0;
				// echo '</tr><tr>';
			// }
		}
		echo '</tr></table></p><br />';
	}
	
	
	
	if($pp_league->id_league)
	{
		// Liste journées
		$pp_info_matches_arr = array();
		$SQL = "SELECT `day_number`, `id_info_matches`
				FROM `pp_info_matches`
				WHERE `pp_info_matches`.`id_league`='".$_GET[id]."' AND `day_number`>0
				ORDER BY `day_number`";
		$result = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result))
		{
			die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
			
		} else {
			while($pp_info_matches = $result->fetchRow())
			{
				$pp_info_matches_arr[] = $pp_info_matches;
			}
		}
		
		echo '<h2 class="title_green">'.$pp_league->label.'</h2>';
		echo '<br /><table width="100%"><tr>';
		foreach($pp_info_matches_arr as $pp_info_matches)
		{
			echo '<td align="center">';
			
			if($pp_info_matches->day_number != $pp_day_number->day_number)
				echo '<a href="/stats-classement.php?id='.$pp_league->id_league.'&j='.$pp_info_matches->day_number.'" class="link_button">'.$pp_info_matches->day_number.'</a>';
			else
				echo '&nbsp;<b>'.$pp_info_matches->day_number.'</b>&nbsp;';
				
			echo '</td>';
			
			if((count($pp_info_matches_arr)/2) == $pp_info_matches->day_number) echo '</tr><tr>';
		}
		echo '</tr></table><br />';
		
		
		
		
		if($pp_info_matches->day_number)
		{
			echo '<h2 class="title_green">'.$titrepage.'</h2>';			
			
			// Liste matchs
			$SQL = "SELECT `pp_info_match`.`id_team_host`, `pp_info_match`.`id_team_visitor`,
						`pp_info_match`.`id_info_match`, `pp_info_match`.`score`,
						`team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`,
						`team_host`.flag AS team_host_flag,
						`team_visitor`.flag AS team_visitor_flag,
						`pp_info_match`.`date_match`,						
						YEAR(`pp_info_match`.`date_match`) AS `date_match_year`,
						MONTH(`pp_info_match`.`date_match`) AS `date_match_month`,
						DAYOFMONTH(`pp_info_match`.`date_match`) AS `date_match_day`,
						DAYOFWEEK(`pp_info_match`.`date_match`) AS `date_match_dayweek`,
						DATE_FORMAT(`pp_info_match`.`date_match`, '".$txtlang['AFF_TIME_SQL']."') AS `time_match_format`,
						DATE_FORMAT(`pp_info_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`
					FROM `pp_info_match`
					INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_info_match`.`id_team_host`
					INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_info_match`.`id_team_visitor`
					INNER JOIN `pp_info_matches` ON `pp_info_match`.`id_info_matches`=`pp_info_matches`.`id_info_matches`
						AND `pp_info_matches`.`day_number`='".$pp_day_number->day_number."' AND `pp_info_matches`.`id_league`='".$pp_league->id_league."'
					ORDER BY `pp_info_match`.`date_match`";
			$result_match = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_match))
			{
				die ("<li>ERROR : ".$result_match->getMessage()."<li>$SQL");
				
			} else {
				if($result_match->numRows())
				{
					echo '<table width="100%" cellpadding="4">';
					echo '<tr>
							<th width="40%"></th>
							<th width="10%">Score</th>
							<th width="40%"></th>
							<th width="5%">Commentaires</th>
							<th width="5%">Note</th>
						</tr>';
					$date_tmp = "";
					$i=0;
					while($pp_info_match = $result_match->fetchRow())
					{
						$i++;
						if(!$pp_info_match->score) $tooltip[$i]['content'] = get_apercu_stats_match($pp_info_match);
						
						if($altern) {
							$class_line = 'ligne_grise';
							$altern = 0;
						} else {
							$class_line = 'ligne_blanche';
							$altern = 1;
						}
						//echo '<li>'.$pp_info_match->team_host_label.' <b>'.($pp_info_match->score ? $pp_info_match->score : '-').'</b> '.$pp_info_match->team_visitor_label.' / '.$pp_info_match->date_match_format.'</li>';
						
						if($pp_info_match->date_match != $date_tmp)
						{
							echo '<tr>';
							echo '<td colspan="5" style="padding-top:16px; border-bottom:solid 1px #ccc">';
							if(substr($date_tmp, 0, 10) != substr($pp_info_match->date_match, 0, 10))
							{
								$dayweek = $pp_info_match->date_match_dayweek;
								if($dayweek==1) $dayweek=8;
								$dayweek = $dayweek-2;		
								echo get_date_complete($dayweek, $pp_info_match->date_match_day, $pp_info_match->date_match_month-1, $pp_info_match->date_match_year);
							}
							echo ' &agrave; '.$pp_info_match->time_match_format;
							echo '</td>';
							echo '</tr>';
							
							$date_tmp = $pp_info_match->date_match;
						}
						
						
						$tabscore = explode('-', $pp_info_match->score);
						
						echo '<tr id="match_line_'.$i.'" class="'.$class_line.'" onmouseover="this.className=\'ligne_rollover\'" onmouseout="this.className=\''.$class_line.'\'">';
						echo '<td align="right"><a href="/stats-equipe.php?id='.$pp_info_match->id_team_host.'" class="link_orange">'.formatDbData($tabscore[0]>$tabscore[1] ? '<b>' . $pp_info_match->team_host_label . '</b>' : $pp_info_match->team_host_label).' '.($pp_info_match->team_host_flag && $pp_info_match->team_visitor_flag ? ' <img src="/image/flags/'.$pp_info_match->team_host_flag.'" align="absmiddle" border="0" />' : '').'</a></td>';
							
						echo '<td id="div_score_match_'.$i.'" align="center"><a href="/info_match.php?id='.$pp_info_match->id_info_match.'" class="link_orange"><b>'.($pp_info_match->score ? $pp_info_match->score : '-').'</b></a></td>';
						
						echo '<td><a href="/stats-equipe.php?id='.$pp_info_match->id_team_visitor.'" class="link_orange">'.($pp_info_match->team_host_flag && $pp_info_match->team_visitor_flag ? '<img src="/image/flags/'.$pp_info_match->team_visitor_flag.'" align="absmiddle" border="0" /> ' : '').' '.formatDbData($tabscore[0]<$tabscore[1] ? '<b>' . $pp_info_match->team_visitor_label . '</b>' : $pp_info_match->team_visitor_label).'</a></td>';
						
						$comments_nb = pp_comments_nb('info_match', $pp_info_match->id_info_match);
						echo '<td align="center"><a href="/info_match.php?id='.$pp_info_match->id_info_match.'" class="link_orange"><img src="/template/default/comment.gif" align="absmiddle" border=0"> '.($comments_nb > 0 ? $comments_nb : '').'</a></td>';
						
						$pp_info_match_note = get_note_match($pp_info_match->id_info_match);
						echo '<td align="center"><a href="/info_match.php?id='.$pp_info_match->id_info_match.'" class="link_orange">'.($pp_info_match_note[note_match] ? round($pp_info_match_note[note_match],2).'/20' : 'Noter&nbsp;!').'</a></td>';
						echo '</tr>';
					}
					echo '</table>';
				}
			}
		}
	}
	?>
	
	
	<?php
	get_classement_league('Classement '.$pp_league->label, $saison_en_cours, $pp_league->id_league);
	?>
  
  
<?php if(is_array($tooltip) && count($tooltip)) { ?>
<script type="text/javascript" language="javascript">
// <![CDATA[
document.observe('dom:loaded', function() {	
<?php
foreach($tooltip as $i=>$var)
{
?>
	new Tip('match_line_<?php echo $i; ?>', '<?php echo str_replace("'", "\'", str_replace("\n", "", $tooltip[$i]['content'])); ?>', {
		style: 'protogrey',
		stem: 'bottomMiddle',
		hook: { target: 'topMiddle', tip: 'bottomMiddle' },
		offset: { x: 0, y: 0 },
		target: 'div_score_match_<?php echo $i; ?>',
		radius: 5,
		width: 470,
		hideOthers:true
	});
	
<?php
}
?>
});

// ]]>
</script>
<?php
}
?>
		
	</div>
</div>



<?php
pagefooter();
?>