<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

pageheader($title ? $title : "Palmarès");
?>

<div id="content_fullscreen">
<?php
// affichage des onglets
echo getOnglets();
?>
	<div id="content">
	
<?php
// recherche coupe
$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label`
		FROM `pp_cup`
		ORDER BY `id_cup` DESC";
$result_cup = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_cup))
{
	die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
	
} else {

	$altern = 1;
	echo "<h2 class=\"title_green\">Les Coupes Prono+</h2>";
	if(!$result_cup->numRows())
	{
		echo "<p>Aucune coupe gagnée pour l'instant...</p>";
		
	} else {
		echo "<table width=\"100%\">
				<tr class=\"ligne_blanche\">
					<th width=\"40%\">&nbsp;</th>
					<th width=\"20%\">Or</th>
					<th width=\"20%\">Argent</th>
					<th width=\"20%\">Bronze</th>
				</tr>";
		while($pp_cup = $result_cup->fetchRow())
		{
			if($altern) {
				$class_line = 'ligne_grise';
				$altern = 0;
			} else {
				$class_line = 'ligne_blanche';
				$altern = 1;
			}
			
			$SQL = "SELECT `id_user_won`, `cup_sub`
					FROM `pp_cup_match_opponents`
					WHERE `id_cup`='".$pp_cup->id_cup."'
					AND `number_tour`=4 AND `id_user_won`!=0
					ORDER BY `cup_sub`";
			$result_cup_user = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_cup_user))
			{
				die ("<li>ERROR : ".$result_cup_user->getMessage()."<li>$SQL");
				
			} else {
				if($result_cup_user->numRows())
				{
					echo "<tr class=\"".$class_line."\" onmouseover=\"this.className='ligne_rollover'\" onmouseout=\"this.className='".$class_line."'\">
							<td><a href=\"/cup.php?id=".$pp_cup->id_cup."&division=1\" class=\"link_orange\"><strong>".$pp_cup->label."</strong></a></td>";
					
					while($cup_user = $result_cup_user->fetchRow())
					{
						$joueur = nom_joueur($cup_user->id_user_won);
						$avatar = getAvatar($cup_user->id_user_won, $joueur->avatar_key, $joueur->avatar_ext, 'small');
						$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
						// $cup_user->cup_sub
						// $cup_user->id_user_won;
						echo "<td><a href=\"/user.php?q=".urlencode(htmlspecialchars($joueur->login))."\" class=\"link_orange\"><img src=\"".$avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" />&nbsp;".$joueur->login."</a></td>";
					}
					echo "</tr>";
				}
			}
		}
		echo "</table>";
	}
}



// recherche Classement mensuel
$SQL = "SELECT `id_class`, `label`
		FROM `pp_class`
		WHERE `close`='1' AND type='month'
		ORDER BY `order` ASC";
$result_class = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_class))
{
	die ("<li>ERROR : ".$result_class->getMessage()."<li>$SQL");
	
} else {

	$altern = 0;
	echo "<br /><h2 class=\"title_orange\">Les Classements mensuels</h2>";
	if(!$result_class->numRows())
	{
		echo "<p>Aucun classement pour l'instant...</p>";
		
	} else {
		echo "<table width=\"100%\">";
		while($pp_class = $result_class->fetchRow())
		{
			if($altern) {
				$class_line = 'ligne_grise';
				$altern = 0;
			} else {
				$class_line = 'ligne_blanche';
				$altern = 1;
			}
			
			$SQL = "SELECT `id_user`
					FROM `pp_class_user`
					WHERE `id_class`='".$pp_class->id_class."'
					ORDER BY `nb_points` DESC
					LIMIT 1";
			$result_cup_user = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_cup_user))
			{
				die ("<li>ERROR : ".$result_cup_user->getMessage()."<li>$SQL");
				
			} else {
				if($cup_user = $result_cup_user->fetchRow())
				{
					$joueur = nom_joueur($cup_user->id_user);
					$avatar = getAvatar($cup_user->id_user, $joueur->avatar_key, $joueur->avatar_ext, 'small');
					$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
					
					echo "<tr class=\"".$class_line."\" onmouseover=\"this.className='ligne_rollover'\" onmouseout=\"this.className='".$class_line."'\"><td width=\"50%\"><a href=\"/class.php?id=".$pp_class->id_class."\" class=\"link_orange\"><strong>".$pp_class->label."</strong></a></td><td width=\"50%\"><a href=\"/user.php?q=".urlencode(htmlspecialchars($joueur->login))."\" class=\"link_orange\"><img src=\"".$avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" />&nbsp;".$joueur->login."</a></td></tr>";
				}
			}
		}
		echo "</table>";
	}
}





// recherche Classement journée
$SQL = "SELECT `id_matches`, `label`, `image`, `date_first_match`
		FROM `pp_matches`
		WHERE `id_cup_matches`='' AND `is_calcul`='1'
		ORDER BY `date_first_match` DESC";
$result_pp_matches = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_pp_matches))
{
	die ("<li>ERROR : ".$result_pp_matches->getMessage()."<li>$SQL");
	
} else {

	$current_date = '';
	$altern = 0;
	echo "<br /><h2 class=\"title_blue\">Les Classements journées</h2>";
	
	if(!$result_pp_matches->numRows())
	{
		echo "<p>Aucun classement pour l'instant...</p>";
		
	} else {
		echo "<table width=\"100%\">";
		while($pp_matches = $result_pp_matches->fetchRow())
		{
			if($current_date != substr($pp_matches->date_first_match, 0, 7))
			{
				if($current_date!='') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>";
				$current_date = substr($pp_matches->date_first_match, 0, 7);
				
				echo "<tr><td colspan=\"2\"><h2 class=\"title_grey\">".$txtlang['MONTH_'.(substr($current_date, 5, 2)-1)]." ".substr($current_date, 0, 4)."</h2></td></tr>";
			}
		
			if($altern) {
				$class_line = 'ligne_grise';
				$altern = 0;
			} else {
				$class_line = 'ligne_blanche';
				$altern = 1;
			}
			
			$SQL = "SELECT `id_user`
					FROM `pp_class_user`
					WHERE `id_matches`='".$pp_matches->id_matches."' AND `class`=1
					AND `id_class`=1
					ORDER BY `nb_points` DESC
					LIMIT 1";
			$result_user = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_user))
			{
				die ("<li>ERROR : ".$result_user->getMessage()."<li>$SQL");
				
			} else {
				if($class_user = $result_user->fetchRow())
				{
					$joueur = nom_joueur($class_user->id_user);
					$avatar = getAvatar($class_user->id_user, $joueur->avatar_key, $joueur->avatar_ext, 'small');
					$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
					
					echo "<tr class=\"".$class_line."\" onmouseover=\"this.className='ligne_rollover'\" onmouseout=\"this.className='".$class_line."'\"><td width=\"50%\"><a href=\"/classj.php?id=".$pp_matches->id_matches."\" class=\"link_orange\"><img src=\"/template/default/".$pp_matches->image."\" border=\"0\" align=\"absmiddle\" width=\"28\" height=\"40\" /> <strong>".$pp_matches->label."</strong></td><td width=\"50%\"><a href=\"/user.php?q=".urlencode(htmlspecialchars($joueur->login))."\" class=\"link_orange\"><img src=\"".$avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" />&nbsp;".$joueur->login."</a></td></tr>";
				}
			}
		}
		echo "</table>";
	}
}
?>
	
	
	</div>
</div>

<?php
pagefooter();
?>