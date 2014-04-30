<?php
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

$team = false;
if($_POST[rechercher] && $_POST[label])
{
	$SQL = "SELECT * FROM `pp_team` WHERE `label` LIKE '".$_POST[label]."%' OR `xlabels` LIKE '".$_POST[label]."%'";
	$result_teams = $db->query($SQL);
	$pp_teams = array();
	while($pp_team = $result_teams->fetchRow())
	{
		$pp_teams[] = $pp_team;
	}
	
	if($result_teams && $result_teams->numRows()==1)
	{
		$team = $pp_teams[0];
	}
	
} elseif($_POST[id_team] && $_POST[label] && $_POST[xlabels])
{
	$SQL = "UPDATE `pp_team`
					SET
						`id_league` = '".$_POST[id_league]."',
						`label` = '".$_POST[label]."',
						`xlabels` = '".$_POST[xlabels]."',
						`flag` = '".$_POST[flag]."',
						`featured` = '".$_POST[featured]."',
						`nb_points_sanction` = '".$_POST[nb_points_sanction]."'
					WHERE `id_team` = '".$_POST[id_team]."'";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	echo "<p class=\"info\">Equipe modifiée !</p>";

} elseif($_POST[label] && $_POST[xlabels])
{
	$SQL = "INSERT INTO `pp_team`(`id_league`, `label`, `xlabels`, `flag`, `featured`, `nb_points_sanction`)
					VALUES('".$_POST[id_league]."', '".$_POST[label]."', '".$_POST[xlabels]."', '".$_POST[flag]."', '".$_POST[featured]."', '".$_POST[nb_points_sanction]."')";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	echo "<p class=\"info\">Equipe ajoutée !</p>";
}

?>

<fieldset>
<legend>Rechercher une équipe</legend>
<form method="post">
<input name="rechercher" type="hidden" value="1" />
Label : <input name="label" type="text" size="30" value="<?php echo $_POST[rechercher] && $_POST[label] ?  htmlspecialchars($_POST[label]) : ''; ?>" /> %<br /><br />
<input type="submit" value="Rechercher" />
</form>
<?php
if($result_teams && $result_teams->numRows())
{
	echo "<p>Résultats trouvés :</p><ul>";
	foreach($pp_teams as $pp_team)
	{
		echo "<li>".$pp_team->label.' / '.$pp_team->xlabels."</li>";
	}
	echo "</ul>";
}
?>

</fieldset><br />

<fieldset>
<legend><?php echo $team ? 'Modifier une équipe' : 'Ajouter une équipe'; ?></legend>
<form method="post">
Championnat : 
<?php
$SQL = "SELECT `label`, `id_league`
		FROM `pp_league`
		ORDER BY `label`";
$result_league = $db->query($SQL);
if(DB::isError($result_match))
{
	die ("<li>ERROR : ".$result_league->getMessage());
	
} else {
	echo "<select name=\"id_league\">";
	echo "<option></option>";
	while($pp_league = $result_league->fetchRow())
	{
		echo "<option value=\"".$pp_league->id_league."\" ".($team && $team->id_league == $pp_league->id_league ? "selected" : "").">".$pp_league->label."</option>";
	}
	echo "</select>";
}
?>
<br /><br />

<input name="id_team" type="hidden" value="<?php echo $team ? htmlspecialchars($team->id_team) : ''; ?>" />

Label : <input name="label" type="text" size="50" value="<?php echo $team ? htmlspecialchars($team->label) : ''; ?>" /><br /><br />

Xlabels : <input name="xlabels" type="text" size="70" value="<?php echo $team ? htmlspecialchars($team->xlabels) : ''; ?>" /><br /><br />

Flag : <input name="flag" type="text" size="70" value="<?php echo $team ? htmlspecialchars($team->flag) : ''; ?>" /><br /><br />

Featured : <input name="featured" type="checkbox" value="1" <?php echo $team && $team->featured ? 'checked' : ''; ?> /><br /><br />

Nb points sanction : <input name="nb_points_sanction" type="text" size="3" value="<?php echo $team ? htmlspecialchars($team->nb_points_sanction) : '0'; ?>" /><br /><br />

<input type="submit" value="Enregistrer" />
</form>
</fieldset>

<?php


echo adminfooter();
?>