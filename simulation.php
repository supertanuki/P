<?
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

if(!$_GET[id]) HeaderRedirect('/');






$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`id_cup_matches`, `pp_matches`.`image`,
		DATE_FORMAT(`pp_matches`.`date_first_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_first_match_format`,
		DATE_FORMAT(`pp_matches`.`date_last_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_last_match_format`,
		TIMEDIFF(NOW(), `pp_matches`.`date_first_match`) AS `diff_date_first_match`,
		TIMEDIFF(NOW(), `pp_matches`.`date_last_match`) AS `diff_date_last_match`,
		`pp_info_country`.`label` AS `country`
		FROM `pp_matches`
		INNER JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_matches`.`id_info_country`
		WHERE `pp_matches`.`id_matches`='".$_GET[id]."' AND `pp_matches`.`id_cup_matches`=0
		AND `pp_matches`.`date_first_match` < NOW()
		AND `is_calcul`!='1'";
$result = $db->query($SQL);
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	if(!$pp_matches = $result->fetchRow())
	{
		HeaderRedirect('/classj.php?id='.$_GET[id]);		
	}
}


// sauver et calculer
if($user && $_POST[save] && is_array($_POST[score_host]) && is_array($_POST[score_visitor]))
{

	$SQL = "SELECT `id_matches` FROM `pp_class_temp`
			WHERE `id_matches`='".$pp_matches->id_matches."' AND NOW() < DATE_ADD(`date_calcul`, INTERVAL 1 minute)";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if(!$pp_simul = $result->fetchRow())
		{
			foreach($_POST[score_host] as $id_match=>$ss)
			{
				$SQL = "UPDATE `pp_match` SET `score`='".$db->escapeSimple($_POST[score_host][$id_match]."-".$_POST[score_visitor][$id_match])."'
						WHERE `id_match`='".$db->escapeSimple($id_match)."' AND `id_matches`='".$db->escapeSimple($pp_matches->id_matches)."'";
				$result = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
			}
		
			require_once('_gwada/adminfunctions.php');
			calcul_class($pp_matches->id_matches, 'provisoire');
			
			$SQL = "INSERT INTO `pp_class_temp`(`id_matches`, `id_user`, `date_calcul`)
					VALUES($pp_matches->id_matches, $user->id_user, NOW())";
			$db->query($SQL);
			
			HeaderRedirect('/classj.php?id='.$_GET[id]);
		}
	}
}





pageheader("Calculer le classement provisoire : ".formatDbData($pp_matches->label)." | Prono+");
?>


<div id="content_fullscreen">
<?
// affichage des onglets
echo getOnglets();
?>



<div id="content">


<h2 class="title_green"><?='Calculer le classement provisoire : ' . formatDbData($pp_matches->label)?></h2>




<?
echo "<table width=\"100%\" border=\"0\" cellspacing=\"4\" cellpadding=\"0\"><tr>
		<td valign=\"top\" width=\"1%\"><img src=\"template/default/".$pp_matches->image."\" class=\"preview_matches_image\" border=\"0\" /></td>
		<td  valign=\"top\" width=\"99%\">";
echo getMatchesClass($_GET[id]);
$NBUSERS = getNbUsersMatches($_GET[id]);
if($NBUSERS) echo "<p><strong>".$NBUSERS."</strong> joueurs ont déjà pronostiqué ces matchs.</p>";


$timerebours = -1;

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
		
		echo "<p>Classement provisoire calculé <strong>il y a ".format_diff_date($pp_user_simul->diff_date_calcul, false)."</strong> par <img src=\"".$avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" />&nbsp;<strong>".$user_simul->login."</strong></p>";
		
		if(substr($pp_user_simul->diff_date_calcul, 0, 5) == '00:00') $timerebours = 60 - substr($pp_user_simul->diff_date_calcul, 6, 2);
	}
}

echo "</td></tr></table>";
?>


<div id="msg_alert" class="message_error" style="text-align:center;padding:10px; margin-bottom:20px; border:solid 1px #ffff00">
<strong>ATTENTION</strong> : le classement provisoire est partagé et affiché pour tout le monde !<br />
Veuillez rentrer les vrais scores et non pas vos pronostics ! Tout abus peut être éventuellement puni.
</div>
<script type="text/javascript">
<!--
new Effect.Highlight('msg_alert', {startcolor:'#ffff00', duration:2});
-->
</script>


<? if(!$user) { ?>
<p>&nbsp;</p>
<div id="msg_error" class="message_error" style="padding:10px; margin-bottom:20px; border:solid 1px #ffff00">Veuillez vous identifier afin de faire la simulation.</div>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<script type="text/javascript">
<!--
new Effect.Highlight('msg_error', {startcolor:'#ffff00', duration:1});
-->
</script>

<? } else { ?>





<?
echo "<form method=\"post\" action=\"\" class=\"niceform\">
		<input type=\"hidden\" name=\"id_matches\" value=\"".$pp_matches->id_matches."\">
		<table border=\"0\" width=\"100%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\" align=\"center\">";
		
$SQL = "SELECT `pp_match`.`id_match`, `pp_match`.`score`, `team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`,
			`pp_match`.`date_match`,
			DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`,
			DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_TIME_SQL']."') AS `time_match_format`,
			TIMEDIFF(NOW(), `pp_match`.`date_match`) AS `diff_date_match`,
			YEAR(`pp_match`.`date_match`) AS `date_match_year`,
			MONTH(`pp_match`.`date_match`) AS `date_match_month`,
			DAYOFMONTH(`pp_match`.`date_match`) AS `date_match_day`,
			DAYOFWEEK(`pp_match`.`date_match`) AS `date_match_dayweek`
		FROM `pp_match`
		INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
		INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
		WHERE `pp_match`.`id_matches`='".$pp_matches->id_matches."' AND `pp_match`.`date_match` <= NOW()
		ORDER BY `pp_match`.`date_match`";
//echo "<li>$SQL";
$result_match = $db->query($SQL);
if(DB::isError($result_match))
{
	die ("<li>ERROR : ".$result_match->getMessage());
	
} else {

	$date_tmp = "";

	while($pp_match = $result_match->fetchRow())
	{
		if($pp_match->date_match != $date_tmp)
		{
            echo '<tr><td colspan="3" class="popup" style="padding-top:16px; border-bottom:solid 1px #ccc">';
			if(substr($date_tmp, 0, 10) != substr($pp_match->date_match, 0, 10))
			{
				$dayweek = $pp_match->date_match_dayweek;
				if($dayweek==1) $dayweek=8;
				$dayweek = $dayweek-2;		
				echo get_date_complete($dayweek, $pp_match->date_match_day, $pp_match->date_match_month-1, $pp_match->date_match_year);
			}
            echo ' &agrave; '.$pp_match->time_match_format.'</td></tr>';
			$date_tmp = $pp_match->date_match;
		}
		echo "<tr>
				<td align=\"right\" width=\"45%\">".formatDbData($pp_match->team_host_label)."</td>
				<td align=\"center\" width=\"10%\" nowrap=\"nowrap\"><select name=\"score_host[".$pp_match->id_match."]\">";
		echo "<option value=\"\">-</option>";
		for($j=0; $j<=9; $j++) echo "<option value=\"".$j."\" ".($_POST[save] ? ($_POST[score_host][$pp_match->id_match]!='' && $_POST[score_host][$pp_match->id_match]!='-' && $_POST[score_host][$pp_match->id_match]==$j ? "selected=\"selected\"" : "" ) : ( $pp_match->score!='' && $pp_match->score!='-' && substr($pp_match->score, 0, 1) == $j+'' ? "selected=\"selected\"" : "" ) ).">".$j."</option>";
		
		echo "<option value=\"R\" ".(substr($pp_match->score, 0, 1) == 'R' ? "selected=\"selected\"" : "").">R</option>";
		
		echo "</select> - <select name=\"score_visitor[".$pp_match->id_match."]\">";
		echo "<option value=\"\">-</option>";		
		for($j=0; $j<=9; $j++) echo "<option value=\"".$j."\" ".($_POST[save] ? ($_POST[score_visitor][$pp_match->id_match]!='' && $_POST[score_visitor][$pp_match->id_match]!='-' && $_POST[score_visitor][$pp_match->id_match]==$j ? "selected=\"selected\"" : "" ) : ( $pp_match->score!='' && $pp_match->score!='-' && substr($pp_match->score, 2, 1) == $j+'' ? "selected=\"selected\"" : "" ) ).">".$j."</option>";		
		echo "<option value=\"R\" ".(substr($pp_match->score, 2, 1) == 'R' ? "selected=\"selected\"" : "").">R</option>";
		echo "</select></td>
				<td width=\"45%\">".formatDbData($pp_match->team_visitor_label)."</td>
			</tr>";
	}
}


	
echo "<tr><td colspan=\"3\" align=\"center\">&nbsp;<br />
		<div id=\"div_info\" ".($timerebours==-1 ? "style=\"display:none\"" : "").">Un classement provisoire a été enregistré il y a quelques secondes.<br />Merci de patienter <strong><span id=\"info_secondes\">XX</span></strong> secondes avant de recalculer ce classement.</div>
		<div id=\"div_submit\" ".($timerebours!=-1 ? "style=\"display:none\"" : "")."><input type=\"submit\" name=\"save\" value=\"Enregistrer\" class=\"link_button\" /></div>
		</td></tr></table></form>";
		
		if($timerebours!=-1) {
?>
<script type="text/javascript">
<!--
var timerebours = <?=$timerebours;?>;

function rebour()
{
	$('info_secondes').update(timerebours);
	timerebours--;
	if(timerebours==0)
	{
		clearInterval(timerrebour);
		$('div_info').hide();
		$('div_submit').show();
	}
}

var timerrebour = setInterval("rebour()", 1000);
-->
</script>

<?
		}
}
?>





</div>
</div>

<?
pagefooter();
?>
