<?
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

// recherche du joueur
if(trim($_GET[q]))
{
	$SQL = "SELECT `pp_user`.*, 
			DATE_FORMAT(`pp_user`.`register_date`, '".$txtlang['AFF_DATE_SQL']."') AS `register_date_format`
			FROM `pp_user`
			WHERE `pp_user`.`login`='".$db->escapeSimple(trim($_GET[q]))."'";
	//echo $SQL;
	$result_user = $db->query($SQL);
	if(DB::isError($result_user))
	{
		die ("<li>ERROR : ".$result_user->getMessage());
		
	} else {
		if(!$pp_user = $result_user->fetchRow())
		{
			HeaderRedirect('/');
		}
	} 
}

$annee = 0;
if($_GET[annee])
{
	$SQL = "SELECT saison_annee FROM pp_archives WHERE `saison_annee`='".$db->escapeSimple(trim($_GET[annee]))."' LIMIT 1";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	if(!$pp_archives = $result->fetchRow()) HeaderRedirect('/palmares-archives.php');
	$annee = $db->escapeSimple(trim($_GET[annee]));
}

pageheader("Archives de Prono+");
?>

<div id="content_fullscreen">
<?
// affichage des onglets
echo getOnglets();
?>
	<div id="content">
	
	<h2 class="title_green">Archives de Prono+</h2>

<br />
<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr>
	<td colspan="2" style="padding:0;"><img src="/template/default/blocgrishaut.gif" border="0" /></td>
</tr>
<tr>
	<td style="background:#eee" width="50%" valign="top" nowrap="nowrap">
		<form>
		Afficher le palmarès général de l'année : 
		<select name="annee">
			<?
			$SQL = "SELECT DISTINCT saison_annee FROM pp_archives ORDER BY `saison_annee` DESC";
			$result = $db->query($SQL);
			if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
			while($pp_class = $result->fetchRow())
			{
				?>
				<option value="<? echo $pp_class->saison_annee; ?>" <? echo $annee==$pp_class->saison_annee ? 'selected="selected"' : ''; ?>><? echo $pp_class->saison_annee . ' - ' . ($pp_class->saison_annee+1); ?></option>
				<?
			}
			?>
		</select> <input type="submit" class="link_button" value="Ok" />
		</form>
	</td>
	
	<td style="background:#eee" width="50%" valign="top">
	<?
	if($user && $pp_user->id_user != $user->id_user)
	{
		?>
		ou <a href="/palmares-archives.php?q=<?=urlencode(htmlspecialchars($user->login))?>" class="link_button">Afficher mon palmarès personnel</a>
		<?
	}
	?>
	</td>
</tr>
<tr>
	<td colspan="2" style="padding:0;"><img src="/template/default/blocgrisbas.gif" border="0" /></td>
</tr>
</table><br />
	
	

	
	
	
<?
// classement joueur
if($pp_user->id_user)
{
	?>
	<p>
	<a href="/user.php?q=<?=urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange">
	<?
	if($avatar = getAvatar($pp_user->id_user, $pp_user->avatar_key, $pp_user->avatar_ext, 'small')) {
	?>
		<a href="/user.php?q=<?=urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange"><img src="/avatars/<?=$avatar?>" height="30" width="30" border="0" align="absmiddle" style="float:left; margin-right:6px;" /></a>
	<? } else { ?>
		<a href="/user.php?q=<?=urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange"><img src="/template/default/_profil.png" height="30" width="30" border="0" align="absmiddle" style="float:left; margin-right:6px;" /></a>
	<? } ?>
	Palmarès de : <br/><a href="/user.php?q=<?=urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange"><?=htmlspecialchars($pp_user->login); ?></a> | <a href="/user.php?q=<?=urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange">Voir le profil</a>
	</p>
	<?
	
	
	
	// recherche coupe
	$SQL = "SELECT pp_archives.saison_annee, pp_archives.libelle
			FROM pp_archives
			WHERE pp_archives.groupe='coupe'
				AND pp_archives.id_user = '".$pp_user->id_user."'
			ORDER BY pp_archives.saison_annee DESC, pp_archives.ordre";
	$result_cup = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_cup))
	{
		die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
		
	} else {

		$altern = 0;
		echo "<h2 class=\"title_grey\">Les Coupes Prono+ Or</h2>";
		if(!$result_cup->numRows())
		{
			echo "<p>Aucune coupe Prono+ Or gagnée... Peut-être un jour ? ;)</p>";
			
		} else {
			echo "<table width=\"100%\">";
			echo "<tr>";
			echo "<th></th>";
			echo "<th>Année</th>";
			echo "</tr>";
			while($pp_cup = $result_cup->fetchRow())
			{
				if($altern) {
					$class_line = 'ligne_grise';
					$altern = 0;
				} else {
					$class_line = 'ligne_blanche';
					$altern = 1;
				}
				
				echo "<tr class=\"".$class_line."\" onmouseover=\"this.className='ligne_rollover'\" onmouseout=\"this.className='".$class_line."'\">";
				echo "<td><strong>".$pp_cup->libelle."</strong></td>";
				echo "<td align=\"center\">".$pp_cup->saison_annee."</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
	}



	echo "<br /><h2 class=\"title_grey\">Les classements</h2>";


	// recherche ordre classements
	$ppclassorder = array();
	$SQL = "SELECT label FROM pp_class ORDER BY `type` DESC, `order` ASC";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	while($pp_class = $result->fetchRow())
	{
		$ppclassorder[] = $pp_class->label;
	}

	// recherche archives classements
	$ppclass = array();
	$ppannee = array();
	$SQL = "SELECT pp_archives.id_user, pp_archives.saison_annee, pp_archives.libelle, pp_archives.class
			FROM pp_archives
			WHERE pp_archives.groupe='class'
				AND pp_archives.id_user = '".$pp_user->id_user."'
			ORDER BY pp_archives.saison_annee DESC, pp_archives.ordre";
	$result_class = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_class))
	{
		die ("<li>ERROR : ".$result_class->getMessage()."<li>$SQL");
		
	} else {	
		while($pp_class = $result_class->fetchRow())
		{
			$ppclass[$pp_class->libelle][$pp_class->saison_annee] = $pp_class->class;
			$ppannee[$pp_class->saison_annee] = true;
		}
	}
	
	if(count($ppclass))
	{
		$altern = 0;
		echo "<table width=\"100%\"><tr><th>Saisons :</th>";
		foreach($ppannee as $annee => $value)
		{
			echo "<th><strong>".substr($annee, 2, 2).'-'.substr($annee+1, 2, 2)."</strong></th>";
		}
		echo "</tr>";
		foreach($ppclassorder as $libelle)
		{
			$ppclassuser = $ppclass[$libelle];
			
			if($altern) {
				$class_line = 'ligne_grise';
				$altern = 0;
			} else {
				$class_line = 'ligne_blanche';
				$altern = 1;
			}
			
			/*
			$joueur = nom_joueur($ppclassuser['id_user']);
			$avatar = getAvatar($pp_class->id_user, $joueur->avatar_key, $joueur->avatar_ext, 'small');
			$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
			*/
			
			echo "<tr class=\"".$class_line."\" onmouseover=\"this.className='ligne_rollover'\" onmouseout=\"this.className='".$class_line."'\">
					<td nowrap=\"nowrap\"><strong>".$libelle."</strong></td>";

			foreach($ppannee as $annee => $value)
			{
				echo "<td align=\"center\" ".($ppclassuser[$annee]=='1' ? 'style="background-color:#ffff00; font-weight:bold;"' : ($ppclassuser[$annee]=='2' ? 'style="background-color:#ffffaa"' : '')).">".($ppclassuser[$annee] ? $ppclassuser[$annee] : '-')."</td>";
			}
			
			echo "</tr>";				
		}
		echo "</table>";
	} else {
		echo "<p><b>Newbie !!! *</b> Il faudra attendre l'année prochaine pour voir des archives ici !</p><p>(* Newbie = débutant. Synonyme : \"bleu\")</p>";
	}
	
	
	
	
	
	
	
	
	
	
} elseif($annee)
{	
	echo "<h2 class=\"title_orange\">Prono+ saison ".$annee." - ".($annee+1)."</h2><br />";
	
	// recherche coupe
	$SQL = "SELECT pp_archives.saison_annee, pp_archives.id_user, pp_archives.libelle
			FROM pp_archives
			WHERE pp_archives.groupe='coupe'
				AND pp_archives.saison_annee = '".$annee."'
			ORDER BY pp_archives.ordre";
	$result_cup = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_cup))
	{
		die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
		
	} else {

		$altern = 0;
		echo "<h2 class=\"title_grey\">Vainqueurs Coupes Prono+ Or</h2>";
		echo "<table width=\"100%\">";
		while($pp_cup = $result_cup->fetchRow())
		{
			if($altern) {
				$class_line = 'ligne_grise';
				$altern = 0;
			} else {
				$class_line = 'ligne_blanche';
				$altern = 1;
			}
				
			$joueur = nom_joueur($pp_cup->id_user);
			$avatar = getAvatar($pp_cup->id_user, $joueur->avatar_key, $joueur->avatar_ext, 'small');
			$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
			
			echo "<tr class=\"".$class_line."\" onmouseover=\"this.className='ligne_rollover'\" onmouseout=\"this.className='".$class_line."'\">";
			echo "<td width=\"50%\"><strong>".$pp_cup->libelle."</strong></td>";
			echo "<td width=\"50%\"><a href=\"/user.php?q=".urlencode(htmlspecialchars($joueur->login))."\" class=\"link_orange\"><img src=\"".$avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" />&nbsp;".$joueur->login."</a></td>";
			echo "</tr>";
		}
		echo "</table>";
	}



	echo "<br /><h2 class=\"title_grey\">Les classements</h2>";


	// recherche ordre classements
	$ppclassorder = array();
	$SQL = "SELECT label FROM pp_class ORDER BY `type` DESC, `order` ASC";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	while($pp_class = $result->fetchRow())
	{
		$ppclassorder[] = $pp_class->label;
	}

	// recherche archives classements
	$ppclass = array();
	$ppannee = array();
	$SQL = "SELECT id_user, libelle
			FROM pp_archives
			WHERE groupe='class'
				AND class=1
				AND saison_annee = '".$annee."'
			ORDER BY pp_archives.ordre";
	$result_class = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_class))
	{
		die ("<li>ERROR : ".$result_class->getMessage()."<li>$SQL");
		
	} else {	
		while($pp_class = $result_class->fetchRow())
		{
			$ppclass[$pp_class->libelle] = $pp_class->id_user;
		}
	}

	$altern = 0;
	if(count($ppclass))
	{
		echo "<table width=\"100%\">";

		foreach($ppclassorder as $libelle) if($ppclass[$libelle])
		{
			$ppclassuser = $ppclass[$libelle];
			
			if($altern) {
				$class_line = 'ligne_grise';
				$altern = 0;
			} else {
				$class_line = 'ligne_blanche';
				$altern = 1;
			}
			
			
			$joueur = nom_joueur($ppclassuser);
			$avatar = getAvatar($ppclassuser, $joueur->avatar_key, $joueur->avatar_ext, 'small');
			$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
			
			echo "<tr class=\"".$class_line."\" onmouseover=\"this.className='ligne_rollover'\" onmouseout=\"this.className='".$class_line."'\">
					<td width=\"50%\"><strong>".$libelle."</strong></td>";
			echo "<td width=\"50%\"><a href=\"/user.php?q=".urlencode(htmlspecialchars($joueur->login))."\" class=\"link_orange\"><img src=\"".$avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" />&nbsp;".$joueur->login."</a></td>";
			
			echo "</tr>";				
		}
		echo "</table>";		
	}
}
?>
	
	
	</div>
</div>

<?
pagefooter();
?>