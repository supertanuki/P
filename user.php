<?
/**
* Project: PRONOPLUS
* Description: Profil d'un utilisateur
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2010-01-19
* Version: 1.0
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

if(!$_GET[q]) HeaderRedirect('/');

$user = user_authentificate();

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

pageheader("Profil de ".$pp_user->login, array('meta_description' => 'Mur de '.$pp_user->login.', ses messages et les messages de ses amis'));
?>
<script type="text/javascript" src="/user.js?v=1"></script>

<div id="content_left">
	<?
	if($pp_user->id_user == $user->id_user)
	{
		echo getOnglets('mon_profil');
	} else {
		echo getOnglets();
	}
	?>
	<div id="content">
		<ul id="list_left" class="list_sortable">
			<?
			$content = '';
			
			if($pp_user->id_user == $user->id_user)
			{
				//$content .= '<p>pp_comments_nb = '.pp_comments_nb('wall', $user->id_user, $new=true).'</p>';
				user_update_date_now($user->id_user, 'date_view_wall');
			}
			
			$content .= '<p><b>Ajouter un message au mur :</b></p>';
			$content .= pp_comments_afficher('wall', $pp_user->id_user, array('admin' => $pp_user->id_user, 'url_param' => '/user.php?q='.urlencode(htmlspecialchars($pp_user->login)), 'show_nb_messages' => false, 'submit_label' => 'Shooter mon message', 'possible_private' => true, 'possible_list' => true, 'order' => 'DESC', 'show' => 20, 'herite' => true));
			echo getBlocLeft('Mur de '.htmlspecialchars($pp_user->login), 'orange', $content);
			?>
		</ul>
	</div>
</div>

<div id="content_right">
	<?
	getRightBulle($pp_user);
	getRightProfil($pp_user);
	?>
	<ul class="list_sortable">
	
		<?
		$content = '';
		//$content .= "<p>Plus d'infos sur les profils, bientôt... <img src=\"/smileys/3.gif\" /></p>";
		
		$pp_user_friends = 0;
		if($user && $pp_user->id_user != $user->id_user)
		{
			//$content .= '<fieldset><legend>Amis ?</legend>';
			
			$SQL = "SELECT `pp_user_friends`.*,
					DATE_FORMAT(`pp_user_friends`.`date_validation`, '".$txtlang['AFF_DATE_SQL']."') AS `date_validation_format`				
					FROM `pp_user_friends`
					WHERE `pp_user_friends`.`id_user`='".$user->id_user."' AND `pp_user_friends`.`id_user_friend`='".$pp_user->id_user."'
						OR `pp_user_friends`.`id_user`='".$pp_user->id_user."' AND `pp_user_friends`.`id_user_friend`='".$user->id_user."'";
			$result_user_friends = $db->query($SQL);
			if(DB::isError($result_user_friends))
			{
				die ("<li>ERROR : ".$result_user_friends->getMessage());
				
			} else {
				if($pp_user_friends = $result_user_friends->fetchRow())
				{
					// déjà amis // supprimer ?
					if($pp_user_friends->valide == '1')
					{
						// recherche de la liste de cet utilisateur
						$SQL = "SELECT `pp_user_listfriends`.`label`
								FROM `pp_user_friends`
								INNER JOIN `pp_user_listfriends`
								ON `pp_user_friends`.`id_user_listfriends` = `pp_user_listfriends`.`id_user_listfriends`
								WHERE `pp_user_listfriends`.`id_user`='".$user->id_user."'
									AND `pp_user_friends`.`id_user`='".$user->id_user."'
									AND `pp_user_friends`.`id_user_friend`='".$pp_user->id_user."'";
						$result_user_listfriends = $db->query($SQL);
						if(DB::isError($result_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$result_user_listfriends->getMessage());
						if($pp_user_listfriends = $result_user_listfriends->fetchRow())
						{							
							$content .= '<p id="user_add_friend_'.$pp_user->id_user.'">Tu es ami avec <b>'.htmlspecialchars($pp_user->login).'</b> depuis le '.$pp_user_friends->date_validation_format.'.<br />Il / elle est classé(e) dans la liste &quot;<b>'.htmlspecialchars($pp_user_listfriends->label).'</b>&quot;.<br /><br />';
							$content .= '<a href="#" onclick="return user_friend_delete('.$pp_user->id_user.');" class="link_orange"><img src="/template/default/group_delete.png" height="16" width="16" border="0" align="absmiddle" /> Ne plus être ami ?</a></p>';
						}
					
					// demande déjà en cours ?
					} else if($pp_user_friends->id_user_friend == $pp_user->id_user) {
						$content .= '<p id="user_add_friend_'.$pp_user->id_user.'"><img src="/template/default/group_add.png" height="16" width="16" border="0" align="absmiddle" />&nbsp;Une invitation est déjà en cours avec <b>'.htmlspecialchars($pp_user->login).'</b></p>';
					
					// ce joueur a déjà demandé à devenir ton ami => accepter ou ignorer
					} else {
						$content .= '<p id="user_add_friend_'.$pp_user->id_user.'"><b>'.htmlspecialchars($pp_user->login).'</b> te propose d\'être son ami. <a href="#" onclick="return user_show_lists('.$pp_user->id_user.');" class="link_button"><img src="/template/default/group_add.png" height="16" width="16" border="0" align="absmiddle" />&nbsp;Accepter</a> ou <a href="#" onclick="return user_friend_delete('.$pp_user->id_user.');" class="link_orange">ignorer</a></p>';
					}
				}
			}
			
			
			// aucun lien entre les deux joueurs => bouton ajouter comme ami
			if(!$pp_user_friends && $user && $pp_user->id_user != $user->id_user)
			{
				$content .= '<p id="user_add_friend_'.$pp_user->id_user.'"><a href="#" onclick="return user_show_lists('.$pp_user->id_user.');" class="link_button"><img src="/template/default/group_add.png" height="16" width="16" border="0" style="float:left; margin-right:6px;" />Ajouter <b>'.htmlspecialchars($pp_user->login).'</b> comme ami(e)</a></p>';
			}
			
			//$content .= '</fieldset>';
		}		
		// Affichage du premier bloc
		//$titre = ($pp_user->id_user != $user->id_user ? 'Profil de '.$pp_user->login : 'Ton profil');
		//echo getBlocLeft($titre, 'orange', $content);
		
		if($user && $pp_user->id_user != $user->id_user)
		{
		?>
		<li>
			<h2 class="title_orange">Être ami...</h2>
			<div class="bloc_content">
				<? echo $content; ?>
			</div>
		</li>
		<?
		}
		?>
	
	
		<li>
			<h2 class="title_blue">Amis au hasard</h2>
			<div class="bloc_content">
				<? echo getRandomFriends($pp_user); ?>
			</div>
		</li>
		
		<?
		if($user && $pp_user->id_user != $user->id_user)
		{
			?>
			<li>
				<h2 class="title_grey">Amis en commun</h2>
				<div class="bloc_content">
					<? echo getSameFriends($user, $pp_user); ?>
				</div>
			</li>
			<?
		}
		?>
	</ul>
</div>

<?
pagefooter();
?>