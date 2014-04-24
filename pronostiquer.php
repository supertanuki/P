<?
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();


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
			location.href='pronostic_ok.php';
			-->
			</script>
			<?
		}
	}
}
/* traitement pronostics */





if(!$_GET[id]) HeaderRedirect('/');

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
		}
		
		
		$SQL = "SELECT `pp_match`.`id_match`, `pp_match`.`id_info_match`, `pp_info_match`.`report`,
					`pp_match`.`id_team_host`, `pp_match`.`id_team_visitor`,
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
        LEFT JOIN `pp_info_match` ON `pp_info_match`.`id_info_match`=`pp_match`.`id_info_match`
				WHERE `pp_match`.`id_matches`='".$_GET[id]."'
				ORDER BY `pp_match`.`date_match`";
		//echo "<li>$SQL";
		$result_match = $db->query($SQL);
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
	
	//echo "<pre>"; print_r($score); echo "</pre>";
}


$meta_description = array();
if(is_array($matches)) foreach($matches as $match)
{
	$meta_description[] = $match->team_host_label.($match->score != '' ? ' '.$match->score.' ' : ' - ').$match->team_visitor_label;
}
$meta_description = 'Pronostiquer ' . implode(', ', $meta_description);


pageheader("Pronostiquer ".formatDbData($pp_matches->label)." | Prono+", array('meta_description' => $meta_description));
?>



<style type="text/css" media="screen">@import url(template/default/pronostiquer.css?v=1.2);</style>

<div id="content_fullscreen">
<?
// affichage des onglets
echo getOnglets();
?>



<div id="content">


<h1 class="title_green"><?='Pronostics ' . formatDbData($pp_matches->label)?></h1>

<? if($error!="") { ?>
<div id="msg_error" class="message_error" style="padding:10px; margin-bottom:20px; border:solid 1px #ffff00"><?=$error?></div>
<script type="text/javascript">
<!--
new Effect.Highlight('msg_error', {startcolor:'#ffff00', duration:1});
-->
</script>
<? } ?>


<?
echo "<table width=\"100%\" border=\"0\" cellspacing=\"4\" cellpadding=\"0\"><tr>
		<td valign=\"top\" width=\"1%\"><img src=\"template/default/".$pp_matches->image."\" class=\"preview_matches_image\" border=\"0\" /></td>
		<td  valign=\"top\" width=\"99%\">";
echo getMatchesClass($_GET[id]);

echo '<p>Attention : les pronostics portent sur le résultat à la fin du match, y compris les prolongations éventuelles mais sans la séance de tirs au but.</p>';

$NBUSERS = getNbUsersMatches($_GET[id]);

if($NBUSERS) echo "<p><strong>".$NBUSERS."</strong> joueurs ont déjà pronostiqué ces matchs.</p>";
echo '<p>' . facebook_libe_button('pronostiquer.php?id='.$_GET[id], 440) . '</p>';
echo '<p><a href="#" onclick="Effect.toggle(\'comment_pronostiquer\', \'blind\', { duration: 0.4 }); return false" class="link_orange"><img src="/template/default/help.gif" border="0" /> Comment pronostiquer ?</a></p>';
echo "</td></tr></table>";

echo "<div id=\"comment_pronostiquer\" class=\"message_error\" style=\"display:none\">Pronostique cette grille en misant des points sur les matchs. Tu disposes de 10 points pour chaque match c'est &agrave; dire que si la grille comporte 10 matchs, tu as 10 x 10 = 100 points &agrave; r&eacute;partir selon ta stratégie et sur tous les matchs.<br />Si tu as pronostiqué le bon score d'un match, tu gagnes 10 fois la mise. Si c'est le bon résultat, tu gagnes 3 fois la mise sinon tu ne gagnes aucun point.<br /><br />
	<strong>Exemples :<ul>
		<li>FC Barcelone 1-0 Lyon</strong><br />
			Tu as pronostiqu&eacute; 2-0 et tu as mis&eacute; 30 points.<br />
			Tu gagnes : 30 x 3 = 90 points.<br>&nbsp;</li>
		<li><strong>Marseille 2-1 Lens</strong><br />
			Tu as pronostiqu&eacute; 2-1 et tu as mis&eacute; 50 points.<br />
			Tu gagnes : 50 x 10 = 500 points.</li>
		</ul>
	</div>";
?>

<form id="form_pronostiquer" method="post" action="pronostiquer.php" onsubmit="alert('save ?'); return saveprono();">
<input type="hidden" name="save_prono" value="1" />
<input type="hidden" name="id" value="<?=$_GET[id]?>" />


<table width="100%" cellpadding="2" cellspacing="1">
	<tr>
		<th class="grille_equipe_gauche" style="padding:2px;">&nbsp;</th>
		<th class="grille_score" style="padding:2px;">Score</th>
		<th class="grille_equipe_droite" style="padding:2px;">&nbsp;</th>
		<th class="grille_mise_slider" align="center" style="padding:2px;">Mise</th>
		<th class="grille_mise" style="padding:2px;">&nbsp;</th>
	</tr>
<?
$date_tmp = "";
if(is_array($matches)) foreach($matches as $i=>$match)
{
	$i = $i+1;
	
	if($altern) {
		$class_line = 'ligne_grise';
		$altern = 0;
	} else {
		$class_line = 'ligne_blanche';
		$altern = 1;
	}
	
	if($match->date_match != $date_tmp)
	{	
?>
	<tr>
		<td colspan="5" class="popup" style="padding-top:16px; border-bottom:solid 1px #ccc">
		<?
		if(substr($date_tmp, 0, 10) != substr($match->date_match, 0, 10))
		{
			$dayweek = $match->date_match_dayweek;
			if($dayweek==1) $dayweek=8;
			$dayweek = $dayweek-2;		
			echo get_date_complete($dayweek, $match->date_match_day, $match->date_match_month-1, $match->date_match_year);
		}
		?>
		&agrave; <?=$match->time_match_format?>
		</td>
	</tr>

<?
		$date_tmp = $match->date_match;
	}

	if(substr($match->diff_date_match, 0, 1) == '-')
	{
?>
	<input type="hidden" name="id_match[<?=$match->id_match?>]" value="<?=$i?>" />
	<tr id="match_line_<?=$i?>" class="<?=$class_line?>" onmouseover="this.className='ligne_rollover'" onmouseout="this.className='<?=$class_line?>'">
		<td class="grille_equipe_gauche" valign="top"><?=formatDbData($match->team_host_label)?><? if($match->team_host_flag && $match->team_visitor_flag) echo ' <img src="/image/flags/'.$match->team_host_flag.'" align="absmiddle" />'; ?></td>
		
		<td class="grille_score" nowrap="nowrap" valign="top"><div id="div_score_match_<?=$i?>"><select id="score_team_host_<?=$i?>" onchange="setScore(<?=$i?>);">
					<? for($j=0; $j<=9; $j++) echo "<option value=\"$j\" ".(substr($score[$match->id_match]->score, 0, 1)*1 == $j ? "selected=\"selected\"" : "").">$j</option>"; ?>
				</select>&nbsp;-&nbsp;<select id="score_team_visitor_<?=$i?>" onchange="setScore(<?=$i?>);">
					<? for($j=0; $j<=9; $j++) echo "<option value=\"$j\"".(substr($score[$match->id_match]->score, 2, 1)*1 == $j ? "selected=\"selected\"" : "").">$j</option>"; ?>
				</select><input id="score_match_<?=$i?>" name="score_match[<?=$i?>]" type="hidden" value="<?=$score[$match->id_match]->score ? $score[$match->id_match]->score : '0-0'; ?>" /></div>
      <?php if($match->report === '1') echo '<div style="margin:4px 0 0 0; color:red">(reporté)</div>'; ?>
    </td>
		
		<td class="grille_equipe_droite" valign="top"><? if($match->team_host_flag && $match->team_visitor_flag) echo '<img src="/image/flags/'.$match->team_visitor_flag.'" align="absmiddle" /> '; ?><?=formatDbData($match->team_visitor_label)?></td>
		
		<td class="grille_mise_slider" valign="top"><div id="div_slider_<?=$i?>"><div id="track_<?=$i?>" class="grille_track"><div id="handle_<?=$i?>" class="grille_handle"> </div></div></div></td>
		
		<td class="grille_mise" valign="top"><input id="mise_match_<?=$i?>" name="mise_match[<?=$i?>]" type="text" value="<?=$score[$match->id_match]->pts ? $score[$match->id_match]->pts : '10'; ?>" size="2" maxlength="2" class="grille_score_input" onblur="SliderMise_<?=$i?>.setValue(this.value); updateMise();" onfocus="open_match(<?=$i?>); this.select();" /></td>
	</tr>
	
<?
		$tendance = array();
		$SQL = "SELECT COUNT(`id_user`) AS NBUSERS,
				IF(SUBSTRING(`score`, 1, 1)*1 = SUBSTRING(`score`, 3, 1)*1, 2, IF(SUBSTRING(`score`, 1, 1)*1 > SUBSTRING(`score`, 3, 1)*1, 1, 3)) AS `type`,
				ROUND(AVG(`pts`)) AS `AVGPTS`
				FROM `pp_match_user`
				WHERE `id_match`='".$match->id_match."'
				GROUP BY IF(SUBSTRING(`score`, 1, 1)*1 = SUBSTRING(`score`, 3, 1)*1, 2, IF(SUBSTRING(`score`, 1, 1)*1 > SUBSTRING(`score`, 3, 1)*1, 1, 3))";
		$result_match = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result_match))
		{
			die ("<li>ERROR : ".$result_match->getMessage());
			
		} else {
			while($pp_match_user = $result_match->fetchRow())
			{
				$tendance[$pp_match_user->type] = $pp_match_user;
			}
			
			$TOTALUSERS = $tendance[1]->NBUSERS + $tendance[2]->NBUSERS + $tendance[3]->NBUSERS;
			
			if($TOTALUSERS > 0)
				$MISEMOY = round(($tendance[1]->NBUSERS * $tendance[1]->AVGPTS + $tendance[2]->NBUSERS * $tendance[2]->AVGPTS + $tendance[3]->NBUSERS * $tendance[3]->AVGPTS) / $TOTALUSERS);
		}
		
		$tooltip[$i]['content'] = get_apercu_stats_match($match, $tendance, $TOTALUSERS);

	} else {
	?>

	<input type="hidden" name="id_match[<?=$match->id_match?>]" value="<?=$i?>" />
	<tr id="match_line_<?=$i?>" class="<?=$class_line?>" onmouseover="this.className='ligne_rollover'" onmouseout="this.className='<?=$class_line?>'">
		<td class="grille_equipe_gauche"><span id="team_host_<?=$i?>" class="grille_match_off"><?=formatDbData($match->team_host_label)?></span><? if($match->team_host_flag && $match->team_visitor_flag) echo ' <img src="/image/flags/'.$match->team_host_flag.'" align="absmiddle" />'; ?></td>
		
		<td class="grille_score"><div id="div_score_match_<?=$i?>" class="grille_score_off"><input id="score_match_<?=$i?>" type="text" value="<?=$score[$match->id_match]->score ? $score[$match->id_match]->score : '-'; ?>" size="3" maxlength="3" class="grille_info_mise" onfocus="blur();" /></div></td>
		
		<td class="grille_equipe_droite"><? if($match->team_host_flag && $match->team_visitor_flag) echo '<img src="/image/flags/'.$match->team_visitor_flag.'" align="absmiddle" /> '; ?><span id="team_visitor_<?=$i?>" class="grille_match_off"><?=formatDbData($match->team_visitor_label)?></span></td>
		
		<td class="grille_mise_slider"><div id="div_slider_<?=$i?>" class="grille_mise_slider_off">
		<div id="track_<?=$i?>" class="grille_track">
			<div id="handle_<?=$i?>" class="grille_handle_off"> </div>
		</div></div>
		</td>
		
		<td class="grille_mise"><div id="div_mise_<?=$i?>" class="grille_mise_off"><input id="mise_match_<?=$i?>" type="text" value="<?=$score[$match->id_match]->pts ? $score[$match->id_match]->pts : '0'; ?>" size="2" maxlength="2" class="grille_info_mise" onfocus="blur();" /></div></td>
	</tr>
<? } ?>
	

<?
}
?>
	<tr>
		<td colspan="3" rowspan="3"><p class="center"><a href="#" onclick="return pronostiquer_pour_moi()" class="link_orange" style="text-decoration:underline">J'ai la flemme</a> </p></td>
		<td class="grille_libelle_mise">Points misés</td>
		<td class="grille_libelle_mise"><input id="points_mises" type="text" value="0" size="2" class="grille_info_mise" onfocus="blur();" /></td>
	</tr>
	<tr id="line_points_trop">
		<td class="grille_libelle_mise">Points en trop</td>
		<td class="grille_libelle_mise"><input id="points_trop" type="text" value="0" size="2" class="grille_info_mise" onfocus="blur();" style="color:red" /></td>
	</tr>
	<tr id="line_points_restants">
		<td class="grille_libelle_mise">Points restants</td>
		<td class="grille_libelle_mise"><input id="points_restants" type="text" value="0" size="2" class="grille_info_mise" onfocus="blur();" style="color:green" /></td>
	</tr>
</table>
<p id="msg_points_restants" class="message_error" style="display:none">N'oubliez pas de miser tous vos points !</p>
<p id="msg_points_trop" class="message_error" style="display:none">Vous avez misé trop de points ! C'est pas bien !</p>
<p class="center" style="padding:20px;background:#f5f5f5"><input type="button" class="link_button" value="Valider" onclick="saveprono()" style="font-size:16px; padding:10px 30px" /></p>
</form>


<?
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
var nb_matchs = <?=$nb_matchs_total; ?>;
var pts_a_miser = <?=$pts_a_miser; ?>;
var id_match_focused = 0;

var SliderMise = new Array();

function init_sliders()
{
<?
for($i=1; $i<=$nb_matchs_total; $i++)
{
	if(substr($matches[$i-1]->diff_date_match, 0, 1) == '-')
	{
?>
SliderMise_<?=$i?> = new Control.Slider('handle_<?=$i?>','track_<?=$i?>',
		{
			range:$R(5,<?=$pts_max?>),
			sliderValue: Math.round($('mise_match_<?=$i?>').value),
			values:[5<? for($j=6; $j<=$pts_max; $j++) echo ", $j"; ?>],
			onSlide:function(v) { $('mise_match_<?=$i?>').value=Math.round(v); updateMise(); },
        	onChange:function(v) { $('mise_match_<?=$i?>').value=Math.round(v); updateMise(); }
		});
		
		<? if($pts_max == 5) { ?>
		SliderMise_<?=$i?>.setDisabled();
		<? } ?>
		
<?
	} else {
?>
SliderMise_<?=$i?> = new Control.Slider('handle_<?=$i?>','track_<?=$i?>',
		{
			range:$R(5,<?=$pts_max_total?>),
			sliderValue: Math.round($('mise_match_<?=$i?>').value),
			values:[5<? for($j=6; $j<=$pts_max_total; $j++) echo ", $j"; ?>],
			onSlide:function(v) { $('mise_match_<?=$i?>').value=Math.round(v); updateMise(); },
        	onChange:function(v) { $('mise_match_<?=$i?>').value=Math.round(v); updateMise(); }
		});
		SliderMise_<?=$i?>.setDisabled();
<?
	}
}
?>
}


function setScore(id_match)
{
	var score = $('score_team_host_'+id_match).value + '-' + $('score_team_visitor_'+id_match).value;
	$('score_match_'+id_match).value = score;
}


function setScoreAndSetSelects(id_match, score1, score2)
{
	$('score_match_'+id_match).value = score1+'-'+score2;
  selectSetValue('score_team_host_'+id_match, score1);
  selectSetValue('score_team_visitor_'+id_match, score2);
}


function updateMise()
{
	var points_mises = 0;
	for(var i=1; i<=nb_matchs; i++)
	{
		points_mises += Math.round($('mise_match_'+i).value);
	}
	
	var points_trop = points_mises - pts_a_miser;
	var points_restants = pts_a_miser - points_mises;
	
	$('points_mises').value = points_mises;
	$('points_trop').value = points_trop > 0 ? points_trop : 0;
	$('points_restants').value = points_restants>0 ? points_restants : 0;
}



function saveprono()
{
	if($('points_trop').value*1 > 0)
	{
		new Effect.Highlight('line_points_trop', {startcolor:'#ffff00', duration:1});
		$('msg_points_trop').show();
		$('msg_points_restants').hide();
		new Effect.Highlight('msg_points_trop', {startcolor:'#ffff00', duration:1});
	
	} else if($('points_restants').value*1 > 0)
	{
		new Effect.Highlight('line_points_restants', {startcolor:'#ffff00', duration:1});
		$('msg_points_restants').show();
		$('msg_points_trop').hide();
		new Effect.Highlight('msg_points_restants', {startcolor:'#ffff00', duration:1});
	
	} else if(<?php echo $user ? 'false' : 'true' ?>)
	{
		alert('Veuillez vous identifier pour enregistrer vos pronostics.');		
		SeConnecter($('login_link'), 'Veuillez vous identifier pour enregistrer vos pronostics.', 'form_pronostiquer_submit()');
	
	} else {
		form_pronostiquer_submit();
		
	}
	
	return false;
}


function form_pronostiquer_submit()
{
	$('form_pronostiquer').submit();
}

function getScoreRandom(type)
{
  return Math.abs(Math.round(2 * Math.round(Math.log(0.1 + Math.random()*4)) * (type=='exterieur' ? 0.5 : 1)));
}

function pronostiquer_pour_moi()
{
  if(confirm("Laisser l'ordinateur pronostiquer pour moi ?\nEt j'accepte le fait qu'il ne connaisse rien en foot."))
  {    
    <?    
    for($i=1; $i<=$nb_matchs_total; $i++)
    {
      if(substr($matches[$i-1]->diff_date_match, 0, 1) == '-')
      {
        ?>
        setScoreAndSetSelects(<? echo $i; ?>, getScoreRandom('domicile'), getScoreRandom('exterieur'));
        <?
      }
    }
    ?>
  }
  return false;
}

document.observe('dom:loaded', function() {
	init_sliders();
	updateMise();
	
<?
for($i=1; $i<=$nb_matchs_total; $i++) if($tooltip[$i])
{
?>
	new Tip('match_line_<? echo $i; ?>', '<? echo str_replace("'", "\'", str_replace("\n", "", $tooltip[$i]['content'])); ?>', {
		style: 'protogrey',
		stem: 'bottomMiddle',
		hook: { target: 'topMiddle', tip: 'bottomMiddle' },
		offset: { x: 0, y: 0 },
		target: 'div_score_match_<? echo $i; ?>',
		radius: 5,
		width: 470,
		hideOthers:true
	});
	
<?
}
?>
});

// ]]>
</script>


<br /><br />
<a name="comments"></a>
<h2 class="title_orange">Réagir...</h2>
<br />
<?
$pp_comments_id_type = substr($pp_matches->date_first_match, 0, 4) . $pp_matches->id_matches;
$date_viewed = pp_comments_get_dateviewed('classj', $pp_comments_id_type);
echo pp_comments_afficher('classj', $pp_comments_id_type, array('url_param' => 'pronostiquer.php?id='.$pp_matches->id_matches, 'date_viewed' => $date_viewed));
$nb_comments = pp_comments_nb('classj', $pp_comments_id_type);
if($nb_comments) pp_comments_viewed('classj', $pp_comments_id_type);
?>


</div>
</div>

<?
pagefooter();
?>
