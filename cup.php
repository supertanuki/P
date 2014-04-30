<?php
/**
* Project: PRONOPLUS
* Description: Coupe Prono+
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-08-31
* Version: 1.0
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

if(!$_GET[id])
{
	$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label`, `pp_cup_matches`.`id_cup_matches`, `pp_cup_matches`.`number_tour`
			FROM `pp_cup_matches`
			INNER JOIN `pp_cup` ON `pp_cup`.`id_cup` = `pp_cup_matches`.`id_cup`
			ORDER BY `pp_cup_matches`.`id_cup` DESC, `pp_cup_matches`.`number_tour` DESC
			LIMIT 1";
	$result_cup_matches = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_cup_matches))
	{
		die ("<li>ERROR : ".$result_cup_matches->getMessage()."<li>$SQL");
		
	} else {
		if($pp_cup_matches = $result_cup_matches->fetchRow())
		{
			$_GET[id] = $pp_cup_matches->id_cup;
		}
	}
}
if(!$_GET[id]) HeaderRedirect('/cupno.php');

$nbjoueurs = 16;
$content = '<p class="message_error">Pour se qualifier à la Coupe Prono+, il vous faut être un bon pronostiqueur et être bien placé au <a href="/classements.php" class="link_orange">Classement Mensuel</a> précédant la Coupe : de la 1ère à la 16ème place du classement mensuel vous ouvre l\'accès à la Coupe Or ; de la 17ème à la 32ème places, vous jouerez dans la Coupe Argent et de la 33ème à la 48ème places, vous jouerez la Coupe Bronze.</p>';
$cup_sub = $_GET[division] ? $_GET[division] : 1;

// recherche coupe
$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label` AS `label_cup`, `pp_class`.`label` AS `label_class`
		FROM `pp_cup`
		INNER JOIN `pp_class` ON `pp_cup`.`id_class_ref`=`pp_class`.`id_class`
		WHERE `id_cup`='".$_GET[id]."'";
$result_cup = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_cup))
{
	die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
	
} else {
	if(!$pp_cup = $result_cup->fetchRow())
	{
		HeaderRedirect('/');
	
	} else {
		$SQL = "SELECT `pp_cup_matches`.`id_cup_matches`, `pp_cup_matches`.`id_matches`, `pp_cup_matches`.`number_tour`
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
				// le joueur est-il qualifié pour la coupe ?
				$SQL = "SELECT `cup_sub`, `id_user_host`, `id_user_visitor`, `host_class`, `visitor_class`
						FROM `pp_cup_match_opponents`
						WHERE `id_cup_matches`='".$pp_cup_matches->id_cup_matches."'
						AND (`id_user_host`='".$user->id_user."' OR `id_user_visitor`='".$user->id_user."')";
				$result_cup_user = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result_cup_user))
				{
					die ("<li>ERROR : ".$result_cup_user->getMessage()."<li>$SQL");
					
				} else {
					if($cup_user = $result_cup_user->fetchRow())
					{
						if($_GET[division] && $cup_user->cup_sub!=$_GET[division])
						{
							$content .= "<p class=\"center\">Vous n'êtes pas qualifié pour cette coupe.</p>";
							
						} else {
							$cup_sub = $cup_user->cup_sub;
							
							// qui est le joueur ?
							if($user->id_user == $cup_user->id_user_visitor)
							{
								$id_user_opponent = $cup_user->id_user_host;
								$class_current_user = $cup_user->visitor_class;
							} else {
								$id_user_opponent = $cup_user->id_user_visitor;
								$class_current_user = $cup_user->host_class;
							}
							
							// recherche de l'adversaire ?
							$opponent = nom_joueur($id_user_opponent);
							$avatar = getAvatar($id_user_opponent, $opponent->avatar_key, $opponent->avatar_ext, 'small');
							$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
							
							$first_rank = ($cup_user->cup_sub - 1) * $nbjoueurs +1;
							$content .= "<p>Vous êtes qualifié pour la <strong>".getCupDivisionLabel($cup_user->cup_sub)."</strong></p><p>Cette division concerne les joueurs classés de la ".$first_rank.($first_rank>1 ? "<sup>ème</sup>" : "<sup>ère</sup>")." à la ".($cup_user->cup_sub * $nbjoueurs)."<sup>ème</sup> place au ".formatDbData($pp_cup->label_class)." et vous avez été classé ".$class_current_user.($class_current_user>1 ? "<sup>ème</sup>" : "er").".</p>
							<p>Lors de ce tour, vous affrontez :
							<a href=\"/user.php?q=".urlencode(htmlspecialchars($opponent->login))."\" class=\"link_button\"><img src=\"".$avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" />&nbsp;<strong>".$opponent->login."</strong></a></p>
							<p>Faites de meilleurs pronostics que votre adversaire pour passer au prochain tour et pour gagner la coupe !</p><br />";
							
							if($pp_cup_matches->id_matches) $content .= CurrentMatches($pp_cup_matches->id_matches, array('forcup'=>true));
								else $content .= "<p class=\"message_error\"><strong>La grille de matchs n'a pas encore été créée. Un peu de patience, ça arrive...</strong></p>";
						}
					
					} else {
			
						$content .= "<p class=\"center\">Vous n'êtes pas qualifié pour cette coupe.</p>";
						
					}
				}
			}
		}
	}
}

$title_page = $pp_cup->label_cup.' - '.getCupDivisionLabel($cup_sub).' - '.getCupTourLabel($pp_cup_matches->number_tour);

pageheader($title_page." | Prono+", array('meta_description' => 'Coupe des meilleurs pronotstiqueurs de foot : ' . $title_page));

?>


<script>
<!--
function showCupDetails(id_cup, cup_sub, number_tour)
{
	$('cup_details').update('<img src="/template/default/wait.gif" align="absmiddle" height="10" width="10" /> Chargement en cours...');

	new Ajax.Updater('cup_details', 'cup_details.php', {
	  method: 'post',
	  parameters:'id_cup='+id_cup+'&cup_sub='+cup_sub+'&number_tour='+number_tour,
	  onSuccess:function() {
	  	window.setTimeout("ScrollTo('cup_details_title')", 100);
	  }
	});
}
-->
</script>



<div id="content_fullscreen">
<?php
// affichage des onglets
echo getOnglets('coupe');
?>
	<div id="content">
		<h2 class="title_green"><?php echo $title_page?></h2>
		
		<div style="float:left; width:58%; margin-right:10px;"><?php echo $content; ?></div>
		<div style="float:left; width:36%;">
		<div style="padding:4px 10px 10px 10px; margin-top:10px; border:1px solid #CCCCCC">
		<h3>Les autres divisions</h3>
		<form method="get" action="cup.php">
		<input type="hidden" name="id" value="<?php echo $_GET[id]?>" />
		<select name="division">
<?php
$SQL = "SELECT DISTINCT `cup_sub`
		FROM `pp_cup_match_opponents`
		WHERE `id_cup`='".$_GET[id]."'
		ORDER BY `cup_sub`";
$result_division = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_division))
{
	die ("<li>ERROR : ".$result_division->getMessage()."<li>$SQL");
	
} else {
	while($cup_division = $result_division->fetchRow())
	{
?>
			<option value="<?php echo $cup_division->cup_sub?>" <?php echo $cup_division->cup_sub==$cup_sub ? 'selected="selected"' : ''?>><?php echo getCupDivisionLabel($cup_division->cup_sub)?></option>
<?php
	}
}
?>
		</select>
		<input type="submit" value="Ok" class="link_button" />
		</form>
		</div>
		

		
<?php
$SQL = "SELECT `id_cup`, `label`
		FROM `pp_cup`
		WHERE `id_cup`!='".$_GET[id]."'
		ORDER BY `id_cup` DESC";
$result_cup = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_cup))
{
	die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
	
} else {
	if($result_cup->numRows())
	{
?>
		<div style="padding:4px 10px 10px 10px; margin-top:10px; border:1px solid #CCCCCC">
		<h3>Les autres coupes</h3>
		<form method="get" action="cup.php">
		<select name="id">
<?php
		while($cup = $result_cup->fetchRow())
		{
?>
			<option value="<?php echo $cup->id_cup?>"><?php echo $cup->label?></option>
<?php
		}
?>
		</select>
		<input type="submit" value="Ok" class="link_button" />
		</form>
		</div>
<?php
	}
}
?>

		</div>
		<div class="clear"></div>
	
		<br />

<?php
// matchs
$match_cup = array();
$SQL = "SELECT `pp_cup_match_opponents`.`id_user_host`, `pp_cup_match_opponents`.`id_user_visitor`, `pp_cup_match_opponents`.`number_tour`,
			`pp_cup_match_opponents`.`host_class`, `pp_cup_match_opponents`.`visitor_class`,
			`pp_cup_match_opponents`.`id_user_won`, `pp_cup_match_opponents`.`visitor_nb_points`, `pp_cup_match_opponents`.`host_nb_points`,
			`user_host`.`login` AS `login_host`, `user_host`.`avatar_key` AS `avatar_key_host`, `user_host`.`avatar_ext`  AS `avatar_ext_host`,
			`user_visitor`.`login` AS `login_visitor`, `user_visitor`.`avatar_key` AS `avatar_key_visitor`, `user_visitor`.`avatar_ext`  AS `avatar_ext_visitor`
		FROM `pp_cup_match_opponents`
		INNER JOIN `pp_user` AS `user_host` ON `user_host`.`id_user`=`pp_cup_match_opponents`.`id_user_host`
		INNER JOIN `pp_user` AS `user_visitor` ON `user_visitor`.`id_user`=`pp_cup_match_opponents`.`id_user_visitor`
		WHERE `pp_cup_match_opponents`.`cup_sub`='".$cup_sub."'
			AND `pp_cup_match_opponents`.`id_cup`='".$_GET[id]."'
		ORDER BY `pp_cup_match_opponents`.`number_tour`, `pp_cup_match_opponents`.`num_match`, `pp_cup_match_opponents`.`host_class`";
$result_cup_user = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_cup_user))
{
	die ("<li>ERROR : ".$result_cup_user->getMessage()."<li>$SQL");
	
} else {
	while($cup_user = $result_cup_user->fetchRow())
	{
		$avatar_host = getAvatar($cup_user->id_user_host, $cup_user->avatar_key_host, $cup_user->avatar_ext_host, 'small');
		$avatar_host = $avatar_host ? '/avatars/'.$avatar_host : '/template/default/_profil.png';
		$cup_user->avatar_host = $avatar_host;
		
		$avatar_visitor = getAvatar($cup_user->id_user_visitor, $cup_user->avatar_key_visitor, $cup_user->avatar_ext_visitor, 'small');
		$avatar_visitor = $avatar_visitor ? '/avatars/'.$avatar_visitor : '/template/default/_profil.png';
		$cup_user->avatar_visitor = $avatar_visitor;
		
		$match_cup[$cup_user->number_tour][] = $cup_user;
	}
}


function showPlayerTableau($id_user, $login, $avatar, $class, $id_user_won, $nb_points, $color, $border=false)
{
	global $user;
	
	if(!$id_user) return '<td '.($border ? 'style="border-bottom:1px solid #999;"' : '').' bgcolor="'.$color.'">&nbsp;</td>';
	
	return '<td '.($user->id_user != $id_user ? 'bgcolor="'.$color.'"' : 'class="ligne_selected"').' '.($border ? 'style="border-bottom:1px solid #999;"' : '').'><a href="/user.php?q='.urlencode(htmlspecialchars($login)).'" class="link_orange"><img src="'.$avatar.'" width="29" height="29" border="0" align="left" hspace="5" />'.($id_user_won==$id_user ? '<strong><u>'.$login.'</u></strong>' : $login).'</a><br /><span style="font-family:Arial; font-size:9px;">'.$class.''.($class>1 ? 'ème' : 'er').($id_user_won ? ', '.$nb_points.' point'.($nb_points>1 ? 's' : '') : '').'</span></td>';
}


$tableau = array(1,8,5,4,3,6,7,2);

$libelles_tour = array(1=>'Huitièmes de finale', 2=>'Quarts de finale', 3=>'Demi-finales', 4=>'Finale');
?>


		
		
		<table cellpadding="4" cellspacing="0" width="100%" border="0">
		<tr>
        	<?php foreach($libelles_tour as $key=>$value)
			{
			?>
                <th width="25%">
                <?php if($match_cup[$key][0]->id_user_won) { ?>
                <a href="#a_cup_details" class="link_orange" onclick="showCupDetails(<?php echo $_GET[id]?>, <?php echo $cup_sub?>, <?php echo $key?>); return false;"><?php echo $value?></a>
                <?php } else { ?>
                <?php echo $value?>
                <?php } ?>
            	</th>
            <?php } ?>
		</tr>
<?php
$line = 1;
$idquart = 0;
$iddemi = 0;

$bgcolor[2] = "#dddddd";
$bgcolor[3] = "#eeeeee";
$bgcolor[4] = "#eeeeee";

foreach($tableau as $idmatch)
{
	$bgcolor[1] = $bgcolor[1]=="#eeeeee" ? "#dddddd" : "#eeeeee";	
?>
		<tr>
			<?php
			// 1/8 ème
			echo showPlayerTableau(	$match_cup[1][$idmatch-1]->id_user_host,
									$match_cup[1][$idmatch-1]->login_host,
									$match_cup[1][$idmatch-1]->avatar_host,
									$match_cup[1][$idmatch-1]->host_class,
									$match_cup[1][$idmatch-1]->id_user_won,
									$match_cup[1][$idmatch-1]->host_nb_points,
									$bgcolor[1]);
			
			// 1/4						
			if($line==3 || $line==7 || $line==11 || $line==15)
			{
				echo showPlayerTableau(	$match_cup[2][$idquart]->id_user_visitor,
										$match_cup[2][$idquart]->login_visitor,
										$match_cup[2][$idquart]->avatar_visitor,
										$match_cup[2][$idquart]->visitor_class,
										$match_cup[2][$idquart]->id_user_won,
										$match_cup[2][$idquart]->visitor_nb_points,
										$bgcolor[2],
										$border=true);
				
				$idquart++;
				$bgcolor[2] = $bgcolor[2]=="#eeeeee" ? "#dddddd" : "#eeeeee";
			
			} else echo '<td></td>';
			
			// 1/2
			if($line==5 || $line==13)
			{
				echo showPlayerTableau(	$match_cup[3][$iddemi]->id_user_visitor,
										$match_cup[3][$iddemi]->login_visitor,
										$match_cup[3][$iddemi]->avatar_visitor,
										$match_cup[3][$iddemi]->visitor_class,
										$match_cup[3][$iddemi]->id_user_won,
										$match_cup[3][$iddemi]->visitor_nb_points,
										$bgcolor[3],
										$border=true);
				
				$iddemi++;
				$bgcolor[3] = $bgcolor[2]=="#eeeeee" ? "#dddddd" : "#eeeeee";
			
			} else echo '<td></td>';
			
			
			
			// Finale
			if($line==9)
			{
				echo showPlayerTableau(	$match_cup[4][0]->id_user_visitor,
										$match_cup[4][0]->login_visitor,
										$match_cup[4][0]->avatar_visitor,
										$match_cup[4][0]->visitor_class,
										$match_cup[4][0]->id_user_won,
										$match_cup[4][0]->visitor_nb_points,
										$bgcolor[4],
										$border=true);
			
			} else echo '<td></td>';
			?>
		</tr>
		
		<?php
		$line++;		
		?>	
	
		<tr>
			<?php
			// 1/8
			echo showPlayerTableau(	$match_cup[1][$idmatch-1]->id_user_visitor,
									$match_cup[1][$idmatch-1]->login_visitor,
									$match_cup[1][$idmatch-1]->avatar_visitor,
									$match_cup[1][$idmatch-1]->visitor_class,
									$match_cup[1][$idmatch-1]->id_user_won,
									$match_cup[1][$idmatch-1]->visitor_nb_points,
									$bgcolor[1],
									$border=true);
			
			// 1/4
			if($line==2 || $line==6 || $line==10 || $line==14)
			{
				echo showPlayerTableau(	$match_cup[2][$idquart]->id_user_host,
										$match_cup[2][$idquart]->login_host,
										$match_cup[2][$idquart]->avatar_host,
										$match_cup[2][$idquart]->host_class,
										$match_cup[2][$idquart]->id_user_won,
										$match_cup[2][$idquart]->host_nb_points,
										$bgcolor[2]);
			
			} else echo '<td></td>';
			
			// 1/2
			if($line==4 || $line==12)
			{
				echo showPlayerTableau(	$match_cup[3][$iddemi]->id_user_host,
										$match_cup[3][$iddemi]->login_host,
										$match_cup[3][$iddemi]->avatar_host,
										$match_cup[3][$iddemi]->host_class,
										$match_cup[3][$iddemi]->id_user_won,
										$match_cup[3][$iddemi]->host_nb_points,
										$bgcolor[3]);
			
			} else echo '<td></td>';
			
			
			// Finale
			if($line==8)
			{
				echo showPlayerTableau(	$match_cup[4][0]->id_user_host,
										$match_cup[4][0]->login_host,
										$match_cup[4][0]->avatar_host,
										$match_cup[4][0]->host_class,
										$match_cup[4][0]->id_user_won,
										$match_cup[4][0]->host_nb_points,
										$bgcolor[4]);
			
			} else echo '<td></td>';			
			?>
		</tr>
<?php
	$line++;
}
?>
		</table>
        
        <br />
        <h2 class="title_orange">Qui gagne en cas d'égalité ?</h2>
        <p>Pour chaque opposition de joueurs, c'est le joueur qui a le plus de points qui gagne. En cas d'égalité, les joueurs sont départagés dans l'ordre par le nombre de score justes, par le nombre de résultats justes sinon par le classement de référence.</p>
        
        <br /><a name="a_cup_details"></a>
        <h2 class="title_green" id="cup_details_title">Détails des résultats</h2>
        <div id="cup_details">
        <p>Pour afficher les détails des résultat, cliquez sur le libellé des colonnes du tableau des oppositions (Huitièmes de finale, Quarts de finale, Demi-finales ou Finale).</p>
        </div>
        
	</div>
</div>

<?php
pagefooter();
?>