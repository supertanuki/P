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


if($_POST[add] && $_POST[label])
{	
	$_POST[country_selected] = 1;
	
	$id_cup_matches = $_POST[cup_matches] ? $_POST[cup_matches] : 0;
	
	
	$SQL = "INSERT INTO `pp_matches`(`id_info_country`, `id_cup_matches`, `label`, `description`, `image`, `date_creation`)
			VALUES('".$_POST[country_selected]."', '".$id_cup_matches."', '".$db->escapeSimple(trim($_POST[label]))."', '".$db->escapeSimple(trim($_POST[description]))."', '".$db->escapeSimple(trim($_POST[image]))."', NOW())";
	$result = $db->query($SQL);
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		$date_first_match = 0;
		$date_last_match = 0;
		
		if(!$id_matches = $db->insertId())
		{
			die ("<li>ERROR : no id_matches");
		} else {
			
			// mise à jour match coupe ?
			if($id_cup_matches)
			{
				$SQL = "UPDATE `pp_cup_matches` SET `id_matches`='".$id_matches."' WHERE `id_cup_matches`='".$id_cup_matches."'";
				$result = $db->query($SQL);
				if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
			}
		
		
			// insertion des matchs
			if(is_array($_POST[match_selected]))
			{
				foreach($_POST[match_selected] as $id_info_match)
				{
					$SQL = "SELECT `id_team_host`, `id_team_visitor`, `date_match` FROM `pp_info_match` WHERE `id_info_match`='".$id_info_match."'";
					//echo "<li>$SQL";
					$result = $db->query($SQL);					
					if(DB::isError($result))
					{
						die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
						
					} else {
						if(!$pp_info_match = $result->fetchRow())
						{
							die ("<li>ERROR : match non trouvé.<li>$SQL");
							
						} else {
							$SQL = "INSERT INTO `pp_match`(`id_matches`, `id_info_match`, `id_team_host`, `id_team_visitor`, `date_match`)
									VALUES($id_matches, $id_info_match, '".$pp_info_match->id_team_host."', '".$pp_info_match->id_team_visitor."', '".$pp_info_match->date_match."')";
							//echo "<li>$SQL";
							$insert = $db->query($SQL);
							if(DB::isError($result)) die ("<li>ERROR : ".$insert->getMessage());
							
							$date_first_match = $date_first_match==0 || $pp_info_match->date_match < $date_first_match ? $pp_info_match->date_match : $date_first_match;
							$date_last_match = $date_last_match==0 || $pp_info_match->date_match > $date_last_match ? $pp_info_match->date_match : $date_last_match;
						}
					}
				}
				
				$SQL = "UPDATE `pp_matches` SET  date_first_match='".$date_first_match."', date_last_match='".$date_last_match."' WHERE `id_matches`='".$id_matches."'";
				$result = $db->query($SQL);
				if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
			}
			
			// insertion association grille <=> classement
			if(is_array($_POST[class_selected]))
			{
				foreach($_POST[class_selected] as $id_class)
				{
					$SQL = "INSERT INTO `pp_matches_class`(`id_matches`, `id_class`) VALUES($id_matches, $id_class)";
					$result = $db->query($SQL);
					if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
				}
			}
			
			$message = "Journée ajoutée !";
			
		}
	}
}



echo adminheader("Admin");

echo AdminName($admin_user);
?>

<script language="javascript">
<!--
var is_toggling = false;
var nb_selected = 0;

function showTableMatches(id_info_matches)
{
	if($('is_open_'+id_info_matches).value!='1')
	{
		$('is_open_'+id_info_matches).value = '1';
		$('view_'+id_info_matches).src = 'images/zoom_out.png';
		Effect.BlindDown('table_matches_'+id_info_matches, { duration: 0.3 });
	} else {
		$('is_open_'+id_info_matches).value = '0';
		$('view_'+id_info_matches).src = 'images/zoom_in.png';
		Effect.BlindUp('table_matches_'+id_info_matches, { duration: 0.3 });
	}
}

function match_select(list_id, field, type, title)
{
	if(list_id!='')
	{
		var ids = list_id.split(',');
		for(var i=0; i<ids.length; i++)
		{
			$(field+ids[i]).checked = type;
		}
		if(type == true)
		{
			nb_selected += ids.length;
		} else {
			nb_selected -= ids.length;
		}
	}
  
  if($('matches_label').value=='') $('matches_label').value = title;
	
	update_nb_menufixe();
}

function update_match_select(fld)
{
	if(fld.checked)
	{
		nb_selected ++;
	} else {
		nb_selected --;
	}
	
	update_nb_menufixe();
}

function update_nb_menufixe()
{
	$('menufixe').update(nb_selected + ' matchs sélectionnés');
}

function ValidAddMatches()
{
	if($('matches_label').value=='')
	{
		alert('Tu as oublié de mettre un titre');
		return false;
		
	} else if($('matches_image').value=='')
	{
		alert('Tu as oublié de choisir une image');
		return false;
		
	} else return true;
}
-->
</script>

<? if($message) echo "<p class=\"info\">$message</p>"; ?>

<? /*<fieldset style="width:70%; float:left">*/ ?>
<fieldset>
	<legend>Créer une grille de pronostics</legend>
	<form method="post" action="matches_add.php" onsubmit="return ValidAddMatches();">
	<input type="hidden" name="add" value="1" />
	Titre<br />
	<input id="matches_label" name="label" type="text" value="" size="50" maxlength="250" /> <a href="#" onclick="$('matches_label').value = ''">X</a><br />
  Titre prédéfinis : <a href="#" onclick="$('matches_label').value = 'Championnats européens'">Championnats européens</a>
  <br /><br />
	<?php
  /*
  Description<br />
	<textarea name="description" cols="50"></textarea><br /><br />
  */
  ?>
	Image<br />
	<select id="matches_image" name="image">
    <option value=""></option>
    <option value="ligue1.png">Ligue 1</option>
    <option value="ligue2.png">Ligue 2</option>
    <option value="coupedefrance.png">Coupe de France</option>
    <option value="coupedelaligue.png">Coupe de la Ligue</option>
    <option value="champeurope.png">Championnats européens</option>
    <option value="lchampions.png">Ligue des Champions</option>
    <option value="europaleague.png">Europa League</option>
    <option value="cdm2014.png">CDM 2014</option>
    <option value="diversfoot.png">Divers foot</option>
	</select><br /><br />
	
	<?php
  // tous les matchs dans un délai d'un mois
  $matches = array();
  $SQL = "SELECT `pp_info_match`.`id_info_match`, `pp_info_match`.`id_info_matches`, IF(`pp_match`.`score` != 'R-R', `pp_match`.`id_match`, 0) AS `id_match`,
        `pp_info_match`.`id_team_host`, `pp_info_match`.`id_team_visitor`,
        `team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`,
        DATE_FORMAT(`pp_info_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`
      FROM `pp_info_match`
        INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_info_match`.`id_team_host`
        INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_info_match`.`id_team_visitor`
        LEFT JOIN `pp_match` ON `pp_match`.`id_info_match` = `pp_info_match`.`id_info_match`
      WHERE DATE_ADD(`pp_info_match`.`date_match`, INTERVAL -1 month) < NOW() AND `pp_info_match`.`date_match` > NOW()
      ORDER BY `pp_info_match`.`date_match`";
  $result = $db->query($SQL);
  //echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	} else {
		while($pp_info_match = $result->fetchRow())
		{
      $matches[$pp_info_match->id_info_matches][] = $pp_info_match;
    }
  }
  
  //print_r($matches);
  
  if(count($matches))
  {
    echo "<table class=\"table_journee\">
        <tr>
          <th></th>
          <th>Championnat</th>
          <th>Journée</th>
          <th>Pays</th>
          <th>Date premier match</th>
          <th>Date dernier match</th>
        </tr>";
    foreach($matches as $id_info_matches => $matchs)
    {
      $SQL = "SELECT `pp_info_matches`.`id_info_matches`, `pp_info_matches`.`day_number`, `pp_info_matches`.`number_tour`,
            `pp_info_matches`.`id_league`,
            DATE_FORMAT(`pp_info_matches`.`date_first_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_first_match_format`,
            DATE_FORMAT(`pp_info_matches`.`date_last_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_last_match_format`,
            `pp_league`.`label` AS `league`
          FROM `pp_info_matches`
            LEFT JOIN `pp_league` ON `pp_league`.`id_league`=`pp_info_matches`.`id_league`
          WHERE `pp_info_matches`.`id_info_matches` = '".$id_info_matches."'";
      $result = $db->query($SQL);
      //echo "<li>$SQL";
      if(DB::isError($result))
      {
        die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
        
      } else {

        while($pp_info_matches = $result->fetchRow())
        {
          echo "<tr>
              <td class=\"table_match_line\"><a href=\"javascript:showTableMatches(".$pp_info_matches->id_info_matches.")\" title=\"Voir les matchs\"><img id=\"view_".$pp_info_matches->id_info_matches."\" src=\"images/zoom_in.png\" height=\"16\" width=\"16\" border=\"0\"></a><input id=\"is_open_".$pp_info_matches->id_info_matches."\" type=\"hidden\" value=\"0\"</td>
              <td>".formatDbData($pp_info_matches->league)."</td>
              <td>".($pp_info_matches->day_number ? "Journée ".$pp_info_matches->day_number : formatDbData($pp_info_matches->number_tour))."</td>
              <td>".formatDbData($pp_info_matches->country)."</td>
              <td>".$pp_info_matches->date_first_match_format."</td>
              <td>".$pp_info_matches->date_last_match_format."</td>
            </tr>";
          
          $title_journee = formatDbData($pp_info_matches->league) . ' - ' . ($pp_info_matches->day_number ? $pp_info_matches->day_number . 'è journée' : '');
        
          echo "<tr>
              <td colspan=\"6\" class=\"table_match_line\"><div id=\"table_matches_".$pp_info_matches->id_info_matches."\" style=\"display:none\"><table class=\"table_match\">";
            
          
					// recherche classement de chaque équipe
					foreach($matchs as $key => $pp_info_match)
          {
            $class_host = get_team_class($pp_info_match->id_team_host, $pp_info_matches->id_league);
            if($class_host)
            {
              $class_visitor = get_team_class($pp_info_match->id_team_visitor, $pp_info_matches->id_league);
							
            } else {
              $class_host = '-';
              $class_visitor = '-';
            }
						$matchs[$key]->class_host = $class_host;
						$matchs[$key]->class_visitor = $class_visitor;
						$matchs[$key]->interet_classement = ($class_host<=3 ? $class_host-1 : $class_host) + ($class_visitor<=3 ? $class_visitor-1 : $class_visitor);
					}
					
					// recherche des affiches en se basant sur le classement des deux équipes et s'il y a plus de 2 matchs dans la grille
					if(count($matchs))
					{
						reset($matchs);
						$meilleur_classement = array();
						
						foreach($matchs as $key => $pp_info_match) if($pp_info_match->interet_classement)
						{
							$meilleur_classement[ $pp_info_match->interet_classement ][] = $key;
						}
						
						if(count($meilleur_classement)>2)
						{
							ksort($meilleur_classement);
							$i=1;
							foreach($meilleur_classement as $ms)
							{
								foreach($ms as $key)
								{
									$matchs[$key]->ordre_interet = $i;
									$i++;
								}
							}
						}
					}
						
						
          $list_id_info_match = "";
					reset($matchs);
          foreach($matchs as $pp_info_match)
          {
            echo "<tr>
                <td><input id=\"match_selected_".$pp_info_match->id_info_match."\" name=\"match_selected[]\" type=\"checkbox\" value=\"".$pp_info_match->id_info_match."\" onclick=\"update_match_select(this)\" /> ".$pp_info_match->ordre_interet."</td>
                <td align=\"right\" style=\"".($pp_info_match->id_match ? "text-decoration: line-through;" : "")."\">".formatDbData($pp_info_match->team_host_label)." (".$pp_info_match->class_host.")</td>
                <td>-</td>
                <td style=\"".($pp_info_match->id_match ? "text-decoration: line-through;" : "")."\">".formatDbData($pp_info_match->team_visitor_label)." (".$pp_info_match->class_visitor.")</td>
                <td>".$pp_info_match->date_match_format."</td>
              </tr>";
            $list_id_info_match .= ($list_id_info_match!='' ? ',' : '') . $pp_info_match->id_info_match;
          }
          echo "<tr>
              <td colspan=\"5\"><a href=\"javascript:match_select('".$list_id_info_match."', 'match_selected_', true, '".str_replace("'", "\'", str_replace('"', '&quot;', $title_journee))."')\">Tout sélectionner</a> | <a href=\"javascript:match_select('".$list_id_info_match."', 'match_selected_', false, '')\">Tout dessélectionner</a></td>
            </tr>";	
        }
        echo "</table></div></td>
          </tr>";
          
      }
    }
		echo "</table>";
	}
	?>
	<br /><br />
	
	
	
	<?php
	// Match de coupe ?
	$SQL = "SELECT `pp_cup_matches`.`id_cup_matches`, `pp_cup`.`label`, `pp_cup_matches`.`number_tour`
			FROM `pp_cup_matches`
			INNER JOIN `pp_cup` ON `pp_cup`.`id_cup`=`pp_cup_matches`.`id_cup`
			WHERE `pp_cup_matches`.`id_matches`=0";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {		
		if($result->numRows())
		{
			?>
			Assigner au match de coupe :<br />
			<?php
			if($pp_cup_matches = $result->fetchRow())
			{
				echo "<input name=\"cup_matches\" type=\"checkbox\" id=\"cup_matches\" value=\"".$pp_cup_matches->id_cup_matches."\" />&nbsp;<label for=\"cup_matches\">".formatDbData($pp_cup_matches->label)." - Tour ".$pp_cup_matches->number_tour."</label><br />";
			}
			?>
			<br /><br />
			<?php
		}
	}
	?>
	
	
	
	
	Assigner aux classements :<br /><br />
	<fieldset><legend>Classements Généraux</legend>
	<?php
	// classements généraux ?
	$SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`label`, `pp_info_country`.`label` AS `country`
			FROM `pp_class` LEFT JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_class`.`id_info_country`
			WHERE `pp_class`.`type`='year' AND `pp_class`.`close` != '1'
			ORDER BY `pp_class`.`order`";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {		
		while($pp_class = $result->fetchRow())
		{
			echo "<input id=\"ass_class_".$pp_class->id_class."\" name=\"class_selected[]\" type=\"checkbox\" value=\"".$pp_class->id_class."\" />&nbsp;<label for=\"ass_class_".$pp_class->id_class."\">".formatDbData($pp_class->label." (".($pp_class->country?$pp_class->country:"tous pays").")")."</label><br />";
		}		
	}
	?>
	</fieldset><br />
	
	<fieldset><legend>Classement mensuel</legend>
	<?php
	// classement mensuel ?
	$SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`label`, `pp_info_country`.`label` AS `country`
			FROM `pp_class` LEFT JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_class`.`id_info_country`
			WHERE `pp_class`.`type`='month' AND `pp_class`.`close` != '1'
			ORDER BY `pp_class`.`order` DESC";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		//$checked = true;
		while($pp_class = $result->fetchRow())
		{
			echo "<input id=\"ass_class_".$pp_class->id_class."\" name=\"class_selected[]\" type=\"checkbox\" value=\"".$pp_class->id_class."\" ".($checked ? "checked=\"checked\"" : "")." />&nbsp;<label for=\"ass_class_".$pp_class->id_class."\">".formatDbData($pp_class->label." (".($pp_class->country?$pp_class->country:"tous pays").")")."</label><br />";
			//$checked = false;
		}		
	}
	?>
	</fieldset>
	
	<?php
	/*
	?><br />
	Assigner au Pays :<br />
	<?php
	// classements généraux ?
	$SQL = "SELECT `pp_info_country`.`id_info_country`, `pp_info_country`.`label`
			FROM `pp_info_country`
			WHERE `pp_info_country`.`is_available`='1'";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {		
		while($pp_class = $result->fetchRow())
		{
			echo "<input name=\"country_selected\" type=\"radio\" value=\"".$pp_class->id_info_country."\" checked=\"checked\" disabled=\"disabled\" />&nbsp;".formatDbData($pp_class->label)."<br />";
		}		
	}
	*/
	
	?><br />
	<input type="submit" value="Enregistrer" />
	</form>
</fieldset>

<br /><br /><br />
<div id="menufixe" class="menufixe">
</div>

<?php
/*
<fieldset style="width:25%; float:left">
	<legend>N'afficher que les matchs de...</legend>
	<form method="post" action="matches_add.php">
	<?php
	$SQL = "SELECT `pp_league`.`id_league`, `pp_league`.`label` AS `league`, `pp_info_country`.`label` AS `country`
			FROM `pp_league` INNER JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_league`.`id_info_country`
			ORDER BY `country`, `league`";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		$list_id_league = '';
		while($pp_league = $result->fetchRow())
		{
			echo "<input id=\"league_to_select_".$pp_league->id_league."\" name=\"league_to_select\" type=\"checkbox\" value=\"".$pp_league->id_league."\" checked=\"checked\" />&nbsp;".$pp_league->league."&nbsp;(".formatDbData($pp_league->country).")<br />";
			$list_id_league .= ($list_id_league!='' ? ',' : '') . $pp_league->id_league;
		}
		echo "<br /><a href=\"javascript:match_select('".$list_id_league."', 'league_to_select_', true)\">Tout sélectionner</a> | <a href=\"javascript:match_select('".$list_id_league."', 'league_to_select_', false)\">Tout dessélectionner</a>";
	}
	?>
	<br /><br /><input type="submit" value="Valider" disabled="disabled" />
	</form>
</fieldset>
<?php
*/



echo adminfooter();
?>