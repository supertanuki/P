<?
/**
* Project: PRONOPLUS
* Description: Création grille de pronostics
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-07-22
* Version: 1.0
*/

require_once('../init.php');
require_once('adminfunctions.php');
session_start();
$admin_user = authentificate();


echo adminheader("Admin");
echo AdminName($admin_user);


if($_POST[id_league])
{
	// insert de la journée
	$SQL = "INSERT INTO `pp_info_matches`(`id_league`, `day_number`, `number_tour`, `date_creation`)
			VALUES('".$_POST[id_league]."', '".$_POST[day_number]."', '".$_POST[number_tour]."', NOW())";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	$id_info_matches = $db->insertId();
	
	$date_first_match = 0;
	$date_last_match = 0;
	
	foreach($_POST[id_team_host] as $key=>$value) if($_POST[id_team_host][$key] && $_POST[id_team_visitor][$key])
	{
		// insert du match
		$date_match = $_POST[date_annee][$key]."-".$_POST[date_mois][$key]."-".$_POST[date_jour][$key]." ".$_POST[date_heure][$key].":".$_POST[date_minute][$key].":00";
		
		if($date_first_match > $date_match || !$date_first_match) $date_first_match = $date_match;
		if($date_last_match < $date_match) $date_last_match = $date_match;
		
		$SQL = "INSERT INTO `pp_info_match`(`id_info_matches`, `id_team_host`, `id_team_visitor`, `date_match`, `date_creation`)
				VALUES('".$id_info_matches."', '".$_POST[id_team_host][$key] ."', '".$_POST[id_team_visitor][$key]."', '".$date_match."', NOW())";
		$result = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	}
	
	$SQL = "UPDATE `pp_info_matches` SET `date_first_match`='".$date_first_match."', `date_last_match`='".$date_last_match."'
			WHERE `id_info_matches`='".$id_info_matches."'";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
	echo "<p class=\"info\">Liste de matchs ajoutées !</p>";	
}





// liste des équipes
$arrTeam = array();	
$SQL = "SELECT `pp_league`.`label` AS `label_league`, `pp_team`.`label` AS `label_team`, `pp_team`.`id_team`
		FROM `pp_team`
		LEFT JOIN `pp_league` ON `pp_league`.`id_league`=`pp_team`.`id_league`
		ORDER BY `pp_league`.`label`, `pp_team`.`label`";
$result_team = $db->query($SQL);
if(DB::isError($result_match))
{
	die ("<li>ERROR : ".$result_team->getMessage());
	
} else {
	while($pp_team = $result_team->fetchRow())
	{
		$arrTeam[] = $pp_team;
	}
}


$html = "<table><tr>";
$html .= "<td align=\"right\"><select name=\"id_team_host[]\">";
$html .= "<option></option>";		
reset($arrTeam);
$optgroup = '____';
foreach($arrTeam as $team)
{
	if($optgroup != $team->label_league)
	{
		if($optgroup != '____') $html .= "</optgroup>";
		$optgroup = $team->label_league;
		$html .= "<optgroup label=\"".($optgroup=='' ? 'Autres' : $optgroup)."\">";
	}
	$html .= "<option value=\"".$team->id_team."\">".$team->label_team."</option>";
}	
$html .= "</optgroup>";		
$html .= "</select></td>";

$html .= "<td align=\"center\">-</td>";

$html .= "<td><select name=\"id_team_visitor[]\">";	
$html .= "<option></option>";
reset($arrTeam);
$optgroup = '____';
foreach($arrTeam as $team)
{			
	if($optgroup != $team->label_league)
	{
		if($optgroup != '____') $html .= "</optgroup>";
		$optgroup = $team->label_league;
		$html .= "<optgroup label=\"".$optgroup."\">";
	}		
	$html .= "<option value=\"".$team->id_team."\">".$team->label_team."</option>";
}			
$html .= "</select></td>";

$html .= "<td align=\"center\">";
$html .= "<select name=\"date_jour[]\">";
for($i=1; $i<=31; $i++) $html .= "<option value=\"".$i."\">".$i."</option>";		
$html .= "</select>";
$html .= "<select name=\"date_mois[]\">";
for($i=1; $i<=12; $i++) $html .= "<option value=\"".$i."\" ".($i==date("m")?"selected=\"selected\"":"").">".$txtlang['MONTH_'.($i-1)]."</option>";		
$html .= "</select>";
$html .= "<select name=\"date_annee[]\">";
for($i=date('Y')-1; $i<=date('Y')+1; $i++) $html .= "<option value=\"".$i."\" ".($i==date("Y")?"selected=\"selected\"":"").">".$i."</option>";		
$html .= "</select>";

$html .= " à <select name=\"date_heure[]\">";
for($i=0; $i<=23; $i++) $html .= "<option value=\"".$i."\">".$i."</option>";		
$html .= "</select>h<select name=\"date_minute[]\">";
for($i=0; $i<=55; $i+=5) $html .= "<option value=\"".$i."\">".$i."</option>";		
$html .= "</select>";

$html .= "</td></tr></table>";

echo "<div id=\"line_add_match\" style=\"display:none\">$html</div>";

?>

<fieldset>
<legend>Ajouter une liste de matchs</legend>
<form method="post">
Championnat : 
<?
$SQL = "SELECT `label`, `id_league`
		FROM `pp_league`
		ORDER BY `label`";
$result_league = $db->query($SQL);
if(DB::isError($result_match))
{
	die ("<li>ERROR : ".$result_league->getMessage());
	
} else {
	echo "<select name=\"id_league\">";
	while($pp_league = $result_league->fetchRow())
	{
		echo "<option value=\"".$pp_league->id_league."\">".$pp_league->label."</option>";
	}
	echo "</select>";
}
?>
<br /><br />

Numéro journée : <input type="text" name="day_number" size="2" /><br /><br />

Numéro tour : <input type="text" name="number_tour" size="2" /><br /><br />

<div id="list_matchs">
<? for($i=1; $i<=10; $i++) echo $html; ?>
</div>

<br /><a href="#" onclick="new Insertion.Bottom('list_matchs', $('line_add_match').innerHTML); return false;">Ajouter un match <img src="images/add.png" border="0" /></a><br /><br />

<input type="submit" value="Enregistrer" />
</form>
</fieldset>

<?


echo adminfooter();
?>