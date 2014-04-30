<?php
/**
* Project: PRONOPLUS
* Description: Gestion de la coupe
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-08-31
* Version: 1.0
*/

require_once('../init.php');
require_once('adminfunctions.php');
session_start();
$admin_user = authentificate();


if($_POST[cup_add] && $_POST[label] && $_POST[id_class])
{	
	// créer une coupe
	$SQL = "INSERT INTO `pp_cup`(`id_info_country`, `id_class_ref`, `label`)
			VALUES(1, '".$_POST[id_class]."', '".$db->escapeSimple(trim($_POST[label]))."')";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
	// créer le premier match
	if($id_cup = $db->insertId())
	{
	
		$SQL = "INSERT INTO `pp_cup_matches`(`id_cup`, `number_tour`)
				VALUES('".$id_cup."', 1)";
		$result = $db->query($SQL);
		if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
		if($id_cup_matches = $db->insertId())
		{
			$cup_user = array();
			$nbjoueurs = 16;
			$ijoueur = 1;
			$cup_sub = 1;
			
			// chercher les joueurs
			$SQL = "SELECT `pp_class_user`.`id_user`, `pp_class_user`.`class`
					FROM `pp_class` INNER JOIN `pp_class_user` ON `pp_class_user`.`id_class`=`pp_class`.`id_class`
						AND `pp_class`.`last_id_matches`=`pp_class_user`.`id_matches`
					WHERE `pp_class`.`id_class`='".$_POST[id_class]."'
					ORDER BY `pp_class_user`.`class`";			
			$result = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result))
			{
				die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
				
			} else {				
				while($class_user = $result->fetchRow())
				{
					// ne créer que 3 divisions
					if($cup_sub>3) break;
					
					$cup_user[$cup_sub][$ijoueur] = array('id_user'=>$class_user->id_user, 'class'=>$class_user->class);
					echo "<li>cup_sub = $cup_sub ; id_user = ".$class_user->id_user;
					if($ijoueur == $nbjoueurs)
					{
						$cup_sub++;
						$ijoueur = 0;
					}
					$ijoueur++;
				}
			}
			
			foreach($cup_user as $cup_sub=>$sub_user)
			{
				// y a t-il assez de joueurs pour créer une subdivision de coupe ?
				if(count($sub_user) == $nbjoueurs)
				{
					// créer les matchs
					$match_opponents = array();					
					$i_end = $nbjoueurs;
					
					// 1 vs 16 -> 2 vs 15 -> 3 vs 14 ...
					for($i_begin=1; $i_begin <= $nbjoueurs/2; $i_begin++)
					{
						$match_opponents[] = array(	'id_user_host'=>$sub_user[$i_begin]['id_user'],
													'host_class'=>$sub_user[$i_begin]['class'],
													'id_user_visitor'=>$sub_user[$i_end]['id_user'],
													'visitor_class'=>$sub_user[$i_end]['class']);
						$i_end--;
					}
					
					foreach($match_opponents as $m_opp)
					{
						// créer les oppositions
						$SQL = "INSERT INTO `pp_cup_match_opponents`(`id_cup`, `number_tour`, `cup_sub`, `id_cup_matches`, `id_user_host`, `host_class`, `id_user_visitor`, `visitor_class`)
								VALUES('".$id_cup."', 1, '".$cup_sub."', '".$id_cup_matches."',
								'".$m_opp[id_user_host]."', '".$m_opp[host_class]."', '".$m_opp[id_user_visitor]."', '".$m_opp[visitor_class]."')";
						$result = $db->query($SQL);
						if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
					}
				}
			}
			
			$message = "Coupe créée !";
		}
	}
}



echo adminheader("Admin");

echo AdminName($admin_user);
?>



<?php if($message) echo "<p class=\"info\">$message</p>"; ?>



<?php
// classements mensuels ?
$class_month = array();
$SQL = "SELECT id_class, label FROM pp_class WHERE type='month' ORDER BY `order` ASC";
$result = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	while($pp_class = $result->fetchRow())
	{
		$class_month[$pp_class->id_class] = $pp_class->label;
	}
}
?>

<fieldset>
	<legend>Créer une coupe</legend>
	<form method="post" action="cup.php">
	<input name="cup_add" type="hidden" value="1" />
	
	Titre<br />
	<input name="label" type="text" value="" size="50" maxlength="250" /><br /><br />
	
	Classement mensuel concerné<br />
	<select name="id_class">
	<?php foreach($class_month as $id_class=>$label) { ?>
		<option value="<?php echo $id_class?>"><?php echo $label?></option>
	<?php } ?>
	</select><br /><br />
	
	<input type="submit" value="Valider" />
	</form>
</fieldset><br />


<fieldset>
	<legend>Coupe en cours</legend>
<?php
// Coupe en cours
$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label`
		FROM `pp_cup`
		ORDER BY `pp_cup`.`id_cup` DESC
		LIMIT 1";
$result_cup = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_cup))
{
	die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
	
} else {
	if($pp_cup = $result_cup->fetchRow())
	{
		$SQL = "SELECT `pp_cup_matches`.`id_cup_matches`, `pp_cup_matches`.`number_tour`, `pp_cup_matches`.`id_matches`
				FROM `pp_cup_matches`
				WHERE `pp_cup_matches`.`id_cup`='".$pp_cup->id_cup."'
				ORDER BY `pp_cup_matches`.`number_tour` DESC
				LIMIT 1";
		$result_matches = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result_matches))
		{
			die ("<li>ERROR : ".$result_matches->getMessage()."<li>$SQL");
			
		} else {
			if($pp_cup_matches = $result_matches->fetchRow())
			{
				echo "<p><strong>".formatDbData($pp_cup->label)." - Tour ".$pp_cup_matches->number_tour."</strong></p>";
				
				if(!$pp_cup_matches->id_matches)
				{
					echo "<p>/!\ Matchs non attribués /!\</p>";
						
				}
			}
		}		
	}
}
?>
</fieldset>

<?	
echo adminfooter();
?>