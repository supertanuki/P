<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$pp_league = 0;
if($_GET[id])
{
	// recherche championnat
	$SQL = "SELECT id_team, id_league, label, flag
			FROM `pp_team`
			WHERE `id_team`='".$db->escapeSimple($_GET[id])."'";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if(!$pp_team = $result->fetchRow())
		{
			HeaderRedirect('/stats-classement.php');
		}
	}
}


/*
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
*/
$saison_en_cours = getConfig('saison_en_cours');

$titrepage = $pp_team->label.' - résultats et classements';

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
	echo '<h2 class="title_green">' . $titrepage . '</h2>';	
	
	
	if($pp_team->id_league)
	{
		//echo '<h2 class="title_green">'.$titrepage.'</h2>';			
		
		// Liste matchs
		$SQL = "SELECT `pp_info_match`.`id_team_host`, `pp_info_match`.`id_team_visitor`, `pp_info_matches`.`day_number`,
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
					AND `pp_info_matches`.`day_number`>0 AND `pp_info_matches`.`id_league`='".$pp_team->id_league."'
					AND (`team_host`.`id_team`='".$pp_team->id_team."' OR `team_visitor`.`id_team`='".$pp_team->id_team."')
				ORDER BY `pp_info_match`.`date_match`";
		$result_match = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result_match))
		{
			die ("<li>ERROR : ".$result_match->getMessage()."<li>$SQL");
			
		} else {
			if($result_match->numRows())
			{
				echo '<table width="100%" cellpadding="2">';
				echo '<tr>
						<th width="3%">J</th>						
						<th width="15%"></th>
						<th width="5%">Score</th>
						<th width="15%"></th>
						<th width="10%">Commentaires</th>
						<th width="5%">Note</th>
						<th width="27%" colspan="2">Date</th>
					</tr>';
					
				$date_tmp = "";
				$afficher_options = true;
				

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
					
					$bgscore = '';
					if($pp_info_match->score)
					{
						$buts = explode('-', $pp_info_match->score);
						if($buts[0] > $buts[1])
						{
							if($pp_info_match->id_team_host == $pp_team->id_team)
								$bgscore = 'result_gagne';
							else
								$bgscore = 'result_defaite';
							
						} elseif($buts[0] < $buts[1])
						{
							if($pp_info_match->id_team_host == $pp_team->id_team)
								$bgscore = 'result_defaite';
							else
								$bgscore = 'result_gagne';
								
						} else {
							$bgscore = 'result_nul';
						}
					}
					
					echo '<tr id="match_line_'.$i.'" class="'.$class_line.'" onmouseover="this.className=\'ligne_rollover\'" onmouseout="this.className=\''.$class_line.'\'">';
					
					echo '<td align="center">'.$pp_info_match->day_number.'</td>';
					
					echo '<td align="right" nowrap="nowrap">';
					
					if($pp_info_match->id_team_host == $pp_team->id_team)
						echo '<b>'.$pp_info_match->team_host_label.'</b>';
					else
						echo '<a href="/stats-equipe.php?id='.$pp_info_match->id_team_host.'" class="link_orange">' . $pp_info_match->team_host_label .'</a>';
						
					echo ($pp_info_match->team_host_flag && $pp_info_match->team_visitor_flag ? ' <img src="/image/flags/'.$pp_info_match->team_host_flag.'" align="absmiddle" border="0" />' : '').'</a></td>';
						
					echo '<td id="div_score_match_'.$i.'" align="center" class="'.$bgscore.'">'.($pp_info_match->score ? '<a href="/info_match.php?id='.$pp_info_match->id_info_match.'" style="color:#fff;"><b>'.$pp_info_match->score.'</b></a>' : '-').'</td>';
					
					echo '<td nowrap="nowrap">'.($pp_info_match->team_host_flag && $pp_info_match->team_visitor_flag ? '<img src="/image/flags/'.$pp_info_match->team_visitor_flag.'" align="absmiddle" border="0" /> ' : '').' ';

					if($pp_info_match->id_team_visitor == $pp_team->id_team)
						echo '<b>'.$pp_info_match->team_visitor_label.'</b>';
					else
						echo '<a href="/stats-equipe.php?id='.$pp_info_match->id_team_visitor.'" class="link_orange">' . $pp_info_match->team_visitor_label . '</a>';
						
					echo '</td>';
					
					if($afficher_options==true)
					{
						$comments_nb = pp_comments_nb('info_match', $pp_info_match->id_info_match);
						echo '<td align="center"><a href="/info_match.php?id='.$pp_info_match->id_info_match.'" class="link_orange"><img src="/template/default/comment.gif" align="absmiddle" border=0"> '.($comments_nb > 0 ? $comments_nb : '').'</a></td>';
					} else echo '<td align="center"> </td>';
					
					if($afficher_options==true)
					{
						$pp_info_match_note = get_note_match($pp_info_match->id_info_match);
						echo '<td align="center"><a href="/info_match.php?id='.$pp_info_match->id_info_match.'" class="link_orange">'.($pp_info_match_note[note_match] ? round($pp_info_match_note[note_match],2).'/20' : 'Noter&nbsp;!').'</a></td>';
					} else echo '<td align="center"> </td>';
					
					$dayweek = $pp_info_match->date_match_dayweek;
					if($dayweek==1) $dayweek=8;
					$dayweek = $dayweek-2;	
					echo '<td align="right">'.get_date_complete(-1, $pp_info_match->date_match_day, $pp_info_match->date_match_month-1, $pp_info_match->date_match_year).'</td>';
					
					echo '<td>';
					echo $afficher_options==true ? $pp_info_match->time_match_format : ' ';
					echo '</td>';
					
					echo '</tr>';
					
					if($pp_info_match->score == '') $afficher_options=false;
				}
				echo '</table>';
			}
		}
	}
	?>
	
	
	<?php
	// Classement
	get_classement_league('Classement '.$pp_team->label, $saison_en_cours, $pp_team->id_league, $pp_team->id_team);
	?>
	
	<?php
	echo '<br /><h2 class="title_orange">Résultats championnats</h2>';	
	
	// Liste championnats
	$SQL = "SELECT id_league, label, flag
			FROM `pp_league`
			WHERE `afficher_classement`='1'
			ORDER BY `ordre`";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		echo '<p><table cellpadding="10"><tr>';
		while($pp_league_info = $result->fetchRow())
		{
			echo '<td><a href="/stats-classement.php?id='.$pp_league_info->id_league.'" class="link_button"><img src="/image/flags/'.$pp_league_info->flag.'" border="0" align="absmiddle" />&nbsp;'.$pp_league_info->label.'</a></td>';
		}
		echo '</tr></table></p><br />';
	}
	?>
	
<script type="text/javascript" language="javascript">
// <![CDATA[
document.observe('dom:loaded', function() {	
<?php
foreach($tooltip as $i=>$var)
{
?>
	new Tip('match_line_<? echo $i; ?>', '<? echo str_replace("'", "\'", str_replace("\n", "", $tooltip[$i]['content'])); ?>', {
		style: 'protogrey',
		stem: 'bottomMiddle',
		hook: { target: 'topMiddle', tip: 'bottomMiddle' },
		offset: { x: 0, y: 10 },
		target: 'div_score_match_<? echo $i; ?>',
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

	</div>
</div>



<?php
pagefooter();
?>