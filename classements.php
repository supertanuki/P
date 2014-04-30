<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();
$pp_user = $user;

pageheader("Tous les classements | Prono+");

// recherche du joueur
if($_GET[q])
{
	//$_GET[q] = utf8_encode($_GET[q]);
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

?>


<div id="content_fullscreen">
	<?php
	// affichage des onglets
	echo getOnglets('classement');
	?>
	<div id="content">
		<?php
		if($_GET[q])
		{
			?>
			<p>
			<?php
			if($avatar = getAvatar($pp_user->id_user, $pp_user->avatar_key, $pp_user->avatar_ext, 'small')) {
			?>
				<a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange"><img src="/avatars/<?php echo $avatar?>" height="30" width="30" border="0" align="absmiddle" style="float:left; margin-right:6px;" /></a>
			<? } else { ?>
				<a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange"><img src="/template/default/_profil.png" height="30" width="30" border="0" align="absmiddle" style="float:left; margin-right:6px;" /></a>
			<? } ?>
			Les classements de : <br/><a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange"><?php echo htmlspecialchars($pp_user->login); ?></a> | <a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange">Voir le profil</a>
			</p>
			<?php
		} else {
			echo '<h1 class="title_orange">Mes classements</h1><br />';
		}
		?>
		
		<?php
		// Affichage du tableau des classements
		echo classements_user($pp_user);
		?>
		
		
		<?php
		if(!$_GET[q])
		{
			?>
			<br />
			<h2 class="title_orange">D'autres statistiques</h2>
            <ul>
            	<li><b><a href="/historique-resultats.php" class="link_orange">Historique des résultats</a></b><br />
						Grille par grille, comparaison de mes résultats avec les points moyens réalisés par tous les joueurs.<br />&nbsp;</li>
						
                <li><b><a href="/classement-evolution.php" class="link_orange">Evolution aux classements</a></b><br />
						Graphique de mon évolution aux classements.<br />&nbsp;</li>
						
				<li><b><a href="/classement-meilleurs-scores.php" class="link_orange">Meilleurs scores</a></b><br />
						Le TOP score des grilles, la crème de la crème, les meilleurs pronostiqueurs ou bien... le coup de chance parfois !<br />&nbsp;</li>
				
                <li><b><a href="/palmares.php" class="link_orange">Palmarès Prono+</a></b><br />
						Les petits et les grands vainqueurs, tous ceux qui sont arrivés 1er dans un classement de Prono+<br />&nbsp;</li>
						
                <li><b><a href="/palmares-archives.php" class="link_orange">Archives palmarès Prono+</a></b><br />
						Les petits et les grands vainqueurs, tous ceux qui sont arrivés 1er dans un classement de Prono+, dans le passé !<br />
						+ mes résultats les saisons précédentes.<br />&nbsp;</li>
            
                <li><b><a href="/stats-pronostics.php" class="link_orange">Statistiques des scores et pronostics</a></b><br />
						Les stats des scores des matchs qui étaient à pronostiquer sur Prono+ et les pronostics de tous les joueurs.<br />&nbsp;</li>
            </ul>
			<?php
		}
		?>
	</div>
</div>



<?php
pagefooter();
?>