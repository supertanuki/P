<?
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$titrepage = 'Programme de diffusion télé des matchs de la Coupe du Monde 2014';

$user = user_authentificate();
pageheader($titrepage);
?>


<div id="content_fullscreen">
	<?
	// affichage des onglets
	echo getOnglets('coupedumonde2014');
	?>
	<div id="content">
	
	
	<?
	echo '<h2 class="title_green">'.$titrepage.'</h2>';
	
	
	// Liste diffusion
	$matchs = array();
	$matchs_info = array();
	$SQL = "SELECT `id_match`, `tour`, `date`, `tv`, equipe1, equipe2,
				YEAR(`date`) AS `date_match_year`,
				MONTH(`date`) AS `date_match_month`,
				DAYOFMONTH(`date`) AS `date_match_day`,
				DAYOFWEEK(`date`) AS `date_match_dayweek`,
				DATE_FORMAT(`date`, '".$txtlang['AFF_TIME_SQL']."') AS `time_match_format`,
				DATE_FORMAT(`date`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`
			FROM `euro2012_diffusion_tele`";
	$result_diffusions = $db->query($SQL);
	while($pp_diffusion = $result_diffusions->fetchRow())
	{
		$matchs[] = $pp_diffusion;
		$match_ids[] = $pp_diffusion->id_match;
	}
	
			
	// Liste matchs
	$matchs_info = array();
	$SQL = "SELECT `pp_match`.`id_match`, `pp_match`.`id_matches`,
				`pp_match`.`id_team_host`, `pp_match`.`id_team_visitor`,
				`pp_match`.`score`,
				`team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`,
				`team_host`.flag AS team_host_flag,
				`team_visitor`.flag AS team_visitor_flag,
				`pp_match`.`date_match`,						
				YEAR(`pp_match`.`date_match`) AS `date_match_year`,
				MONTH(`pp_match`.`date_match`) AS `date_match_month`,
				DAYOFMONTH(`pp_match`.`date_match`) AS `date_match_day`,
				DAYOFWEEK(`pp_match`.`date_match`) AS `date_match_dayweek`,
				DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_TIME_SQL']."') AS `time_match_format`,
				DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`
			FROM `pp_match`
			INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
			INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
			WHERE `pp_match`.`id_match` IN (".implode(',',$match_ids).")
			ORDER BY `pp_match`.`date_match`";
	$result_match = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_match))
	{
		die ("<li>ERROR : ".$result_match->getMessage()."<li>$SQL");
		
	} else {
		while($pp_info_match = $result_match->fetchRow())
		{
			$matchs_info[$pp_info_match->id_match] = $pp_info_match;
		}
	}
	
	echo '<table width="100%" cellpadding="4">';
	echo '<tr>
			<th>Groupe / Tour</th>
			<th></th>
			<th>Score</th>
			<th></th>					
			<th>TV</th>
		</tr>';
	$date_tmp = "";
	$i=0;
	foreach($matchs as $match)
	{
		$pp_info_match = $matchs_info[$match->id_match];
    if(!$pp_info_match->team_host_label) $pp_info_match->team_host_label = $match->equipe1;
    if(!$pp_info_match->team_visitor_label) $pp_info_match->team_visitor_label = $match->equipe2;
    if(!$pp_info_match->date_match) $pp_info_match->date_match = $match->date;
    if(!$pp_info_match->date_match_dayweek) $pp_info_match->date_match_dayweek = $match->date_match_dayweek;
    if(!$pp_info_match->date_match_day) $pp_info_match->date_match_day = $match->date_match_day;
    if(!$pp_info_match->date_match_month) $pp_info_match->date_match_month = $match->date_match_month;
    if(!$pp_info_match->date_match_year) $pp_info_match->date_match_year = $match->date_match_year;
    if(!$pp_info_match->time_match_format) $pp_info_match->time_match_format = $match->time_match_format;
		
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
			echo '<td colspan="5" style="font-weight:bold; padding-top:16px; border-bottom:solid 1px #ccc">';
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
		
		if(1*date('d') == $pp_info_match->date_match_day
			&& 1*date('m') == $pp_info_match->date_match_month
			&& 1*date('Y') == $pp_info_match->date_match_year)
			$class_line = 'ligne_selected';
		
		echo '<tr class="'.$class_line.'" onmouseover="this.className=\'ligne_rollover\'" onmouseout="this.className=\''.$class_line.'\'">';
		
		echo '<td align="center">'.$match->tour.'</td>';
		
		echo '<td align="right">'.formatDbData($tabscore[0]>$tabscore[1] ? '<b>' . $pp_info_match->team_host_label . '</b>' : $pp_info_match->team_host_label).' '.($pp_info_match->team_host_flag && $pp_info_match->team_visitor_flag ? ' <img src="/image/flags/'.$pp_info_match->team_host_flag.'" align="absmiddle" border="0" />' : '').'</td>';
			
		echo '<td id="div_score_match_'.$i.'" align="center">'.($pp_info_match->score ? '<b>'.$pp_info_match->score.'</b>' : '-<br />'.($pp_info_match->id_matches ? '<a href="/pronostiquer.php?id='.$pp_info_match->id_matches.'" class="link_orange">pronostiquer</a>' : '')).'</td>';
		
		echo '<td>'.($pp_info_match->team_host_flag && $pp_info_match->team_visitor_flag ? '<img src="/image/flags/'.$pp_info_match->team_visitor_flag.'" align="absmiddle" border="0" /> ' : '').' '.formatDbData($tabscore[0]<$tabscore[1] ? '<b>' . $pp_info_match->team_visitor_label . '</b>' : $pp_info_match->team_visitor_label).'</td>';
		
		$tvs = explode(',', $match->tv);
		$tvhtml = array();
		if(count($tvs)==0)
		{
			$tvhtml[] = '-';
		} else {
			foreach($tvs as $tv) if($tv)
			{
				if($tv=='beIN SPORT') $tv = 'be-in-sport';
				$tvhtml[] = '<img src="/image/tv/'.$tv.'.png" alt="'.$tv.'" align="absmiddle" />';
			}
		}
		echo '<td align="center">'.implode('&nbsp;et&nbsp;',$tvhtml).'</td>';
		echo '</tr>';
	}
	echo '</table>';
	?>
		
	</div>
</div>



<?
pagefooter();
?>
