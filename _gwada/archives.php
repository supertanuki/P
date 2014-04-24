<?
/**
* Project: PRONOPLUS
* Description: Archives
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2009-07-14
* Version: 1.0
*/

require_once('../init.php');
require_once('adminfunctions.php');
session_start();
$admin_user = authentificate();

echo adminheader("Admin");
echo AdminName($admin_user);



function ajouter_archive($id_user, $saison_annee, $groupe, $libelle, $ordre, $class=0)
{
	global $db;
	$SQL = "INSERT INTO pp_archives(`id_user`, `saison_annee`, `groupe`, `libelle`, `class`, `ordre`, `date_creation`)
			VALUES($id_user, $saison_annee, '".mysql_real_escape_string(stripslashes($groupe))."', '".mysql_real_escape_string(stripslashes($libelle))."',
			$class, $ordre, NOW())";
	$result = $db->query($SQL);
	if(DB::isError($result)) die("<li>ERROR : ".$result->getMessage()."<li>$SQL");
}


if($_POST[enregistrer_archives] && $_POST[annee])
{
	//suppression déjà enregistré
	$SQL = "DELETE FROM pp_archives WHERE `saison_annee`='".$_POST[annee]."'";
	$result = $db->query($SQL);
	if(DB::isError($result)) die("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
	
	// recherche coupe
	$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label`
			FROM `pp_cup`
			ORDER BY `id_cup`";
	$result_cup = $db->query($SQL);
	if(DB::isError($result_cup))
	{
		die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
		
	} else {
	
		$ordre = 1;
		
		while($pp_cup = $result_cup->fetchRow())
		{
			$SQL = "SELECT `id_user_won`
					FROM `pp_cup_match_opponents`
					WHERE `id_cup`='".$pp_cup->id_cup."'
					AND `cup_sub`=1 AND `number_tour`=4 AND `id_user_won`!=0";
			$result_cup_user = $db->query($SQL);
			if(DB::isError($result_cup_user))
			{
				die ("<li>ERROR : ".$result_cup_user->getMessage()."<li>$SQL");
				
			} else {
				if($cup_user = $result_cup_user->fetchRow())
				{
					ajouter_archive($cup_user->id_user_won, $_POST[annee], 'coupe', $pp_cup->label, $ordre);
					$ordre++;
				}
			}
		}
	}



	// recherche Classements	
	$ordre = 1;
	
	$SQL = "SELECT `id_class`, `label`, `last_id_matches`
			FROM `pp_class`
			ORDER BY `type` DESC, `order` ASC";
	$result_class = $db->query($SQL);
	if(DB::isError($result_class))
	{
		die ("<li>ERROR : ".$result_class->getMessage()."<li>$SQL");
		
	} else {
		while($pp_class = $result_class->fetchRow())
		{
			
			
			if($pp_class->last_id_matches)
			{
				$SQL = "SELECT `id_user`, `class`
						FROM `pp_class_user`
						WHERE `id_class`='".$pp_class->id_class."'
						AND `pp_class_user`.`id_matches`='".$pp_class->last_id_matches."' 
						ORDER BY `class`";
				$result_user = $db->query($SQL);
				if(DB::isError($result_user))
				{
					die ("<li>ERROR : ".$result_user->getMessage()."<li>$SQL");
					
				} else {
					while($row_user = $result_user->fetchRow())
					{					
						ajouter_archive($row_user->id_user, $_POST[annee], 'class', $pp_class->label, $ordre, $row_user->class);						
					}
				}
			}
			
			$ordre++;
		}
	}
	
	echo "<p class=\"info\">Archives enregistrées !</p>";	
}


if($_POST[supprimer_saison])
{
	// mise à jour des classements
	$SQL = "UPDATE pp_class SET close='', last_id_matches=0, nb_matches=0, date_update=NOW()";
	$result = $db->query($SQL);

	// suppression classements temporaires
	$SQL = "TRUNCATE TABLE `pp_class_temp`";
	$result = $db->query($SQL);
	
	$SQL = "TRUNCATE TABLE `pp_class_user_temp`";
	$result = $db->query($SQL);
	
	// suppression classements utilisateurs
	$SQL = "TRUNCATE TABLE `pp_class_user`";
	$result = $db->query($SQL);
	
	// suppression coupes
	$SQL = "TRUNCATE TABLE `pp_cup`";
	$result = $db->query($SQL);
	
	$SQL = "TRUNCATE TABLE `pp_cup_matches`";
	$result = $db->query($SQL);
	
	$SQL = "TRUNCATE TABLE `pp_cup_match_opponents`";
	$result = $db->query($SQL);
	
	// suppression matchs
	$SQL = "TRUNCATE TABLE `pp_info_match`";
	$result = $db->query($SQL);
	
	$SQL = "TRUNCATE TABLE `pp_info_matches`";
	$result = $db->query($SQL);
	
	$SQL = "TRUNCATE TABLE `pp_info_matches`";
	$result = $db->query($SQL);
	
	// suppression matchs à pronostiquer
	$SQL = "TRUNCATE TABLE `pp_match`";
	$result = $db->query($SQL);
	
	$SQL = "TRUNCATE TABLE `pp_matches`";
	$result = $db->query($SQL);
	
	$SQL = "TRUNCATE TABLE `pp_matches_class`";
	$result = $db->query($SQL);
	
	$SQL = "TRUNCATE TABLE `pp_matches_user`";
	$result = $db->query($SQL);
	
	$SQL = "TRUNCATE TABLE `pp_match_user`";
	$result = $db->query($SQL);
	
	$SQL = "TRUNCATE TABLE `pp_match_user_temp`";
	$result = $db->query($SQL);
	
	echo "<p class=\"info\">Données supprimées !</p>";
}

?>
<fieldset>
<legend>Enregistrer les archives</legend>
<form method="post">
Année de la saison : <input type="text" name="annee" value="<?=date("Y")-1;?>" size="4" maxlength="4" /><br /><br />
<input type="submit" name="enregistrer_archives" value="Enregistrer" style="padding: 6px; background-color: rgb(204, 204, 204);" /><br /><br />
NB : Avant l'enregistrement, les données déjà enregistrées en archives pour cette saison seront supprimées.
</form>

<p><b>Extrait des dernières archives enregistrées :</b></p>
<?
$SQL = "SELECT * FROM pp_archives ORDER BY id_archive DESC LIMIT 5";
$result = $db->query($SQL);
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {

	echo "<table border=\"1\" cellspacing=\"0\"><tr><td>id_archive</td><td>id_user</td><td>saison_annee</td><td>groupe</td><td>libelle</td><td>class</td><td>ordre</td><td>date_creation</td></tr>";
	while($archive = $result->fetchRow())
	{
		echo "<tr><td>".$archive->id_archive."</td><td>".$archive->id_user."</td><td>".$archive->saison_annee."</td><td>".$archive->groupe."</td><td>".$archive->libelle."</td><td>".$archive->class."</td><td>".$archive->ordre."</td><td>".$archive->date_creation."</td></tr>";
	}
	echo "</table>";
}
?>

</fieldset>

<br />

<fieldset>
<legend>Supprimer les données de la saison en cours</legend>
<form method="post" onsubmit="return confirm('Êtes-vous certain ?')">
<p>Suppression des coupes, des matchs, des pronostics, des classements des utilisateurs.</p>
<input type="submit" name="supprimer_saison" value="Supprimer la saison" style="padding: 6px; background-color: rgb(255, 34, 34); color: rgb(255, 255, 255);" />
</form>
</fieldset>
<?

echo adminfooter();
?>