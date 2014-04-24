<?
/**
* Project: PRONOPLUS
* Description: Actions ajax sur le profil
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2010-01-21
* Version: 1.1
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

// Afficher liste libellés pour classer l'ami
if($user->id_user && $_POST[action] == 'user_show_lists' && $_POST[id_user_friend])
{
	$str = '';
	
	$SQL = "SELECT `pp_user_listfriends`.`id_user_listfriends`, `pp_user_listfriends`.`label`
			FROM `pp_user_listfriends`
			WHERE `pp_user_listfriends`.`id_user`='".$user->id_user."'
			ORDER BY `pp_user_listfriends`.`order`, `pp_user_listfriends`.`label`";
	$result_user_listfriends = $db->query($SQL);
	if(DB::isError($result_user_listfriends))
	{
		die ("<li>ERROR : ".$result_user_listfriends->getMessage());
		
	} else {
		if($result_user_listfriends->numRows())
		{
			$str .= 'Le / la classer dans ta liste<br /><select id="user_listfriends_'.$_POST[id_user_friend].'">';
			while($pp_user_listfriends = $result_user_listfriends->fetchRow())
			{
				$str .= '<option value="'.$pp_user_listfriends->id_user_listfriends.'">'.htmlspecialchars($pp_user_listfriends->label).'</option>';
			}
			$str .= '</select>';
			$str .= '&nbsp;<a href="#" onclick="return user_addfriend('.$_POST[id_user_friend].');" class="link_button"><img src="/template/default/group_add.png" height="16" width="16" border="0" align="absmiddle" />&nbsp;Ajouter</a>';
			$str .= '<br /><br />';
		}
	}
	
	$str .= 'Ou bien le / la classer dans ta nouvelle liste :<br /><input id="user_labellist_'.$_POST[id_user_friend].'" type="text" size="25" maxlength="50" value="Nom de la liste" onfocus="this.value = (this.value == \'Nom de la liste\' ? \'\' : this.value)" onblur="this.value = (this.value == \'\' ? \'Nom de la liste\' : this.value)" />';
	$str .= '&nbsp;<a href="#" onclick="$(\'user_labellist_'.$_POST[id_user_friend].'\').value = ($(\'user_labellist_'.$_POST[id_user_friend].'\').value == \'Nom de la liste\' ? \'\' : $(\'user_labellist_'.$_POST[id_user_friend].'\').value); return user_addlistfriend('.$_POST[id_user_friend].');" class="link_button"><img src="/template/default/group_add.png" height="16" width="16" border="0" align="absmiddle" />&nbsp;Ajouter</a>';
	$str .= '<br /><span class="petitgris">Exemple de liste :<br /><a href="#" class="link_orange" onclick="return user_change_label_list('.$_POST[id_user_friend].', \'Amis\')">Amis</a>, <a href="#" class="link_orange" onclick="return user_change_label_list('.$_POST[id_user_friend].', \'Famille\')">Famille</a>, <a href="#" class="link_orange" onclick="return user_change_label_list('.$_POST[id_user_friend].', \'Collègues\')">Collègues</a>, <a href="#" class="link_orange" onclick="return user_change_label_list('.$_POST[id_user_friend].', \'Co-équipiers\')">Co-équipiers</a>...</span>';
	echo $str;
}


// Ajouter la nouvelle liste et ajouter l'ami
if($user->id_user && $_POST[action] == 'user_addlistfriend' && ($_POST[id_user_friend]*1) && trim($_POST[user_labellist]))
{
	// on cherche si l'user id_user_friend existe
	$SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`login`, `pp_user`.`email`, `pp_user`.`no_mail`
			FROM `pp_user`
			WHERE `pp_user`.`id_user`='".$db->escapeSimple($_POST[id_user_friend]*1)."'";
	$result_user = $db->query($SQL);
	if(DB::isError($result_user)) die ("<li>ERROR : ".$result_user->getMessage());
	if(!$pp_user_friend = $result_user->fetchRow()) die ("<li>ERROR : id_user_friend non trouvé");

	// on cherche si la liste n'existe pas déjà
	$SQL = "SELECT `pp_user_listfriends`.id_user_listfriends
			FROM `pp_user_listfriends`
			WHERE `pp_user_listfriends`.`id_user`='".$user->id_user."'
				AND `pp_user_listfriends`.`label`='".$db->escapeSimple(trim($_POST[user_labellist]))."'";
	$result_user_listfriends = $db->query($SQL);
	if(DB::isError($result_user_listfriends))
	{
		die ("<li>$SQL<li>ERROR : ".$result_user_listfriends->getMessage());
		
	} else {
		if($pp_user_listfriends = $result_user_listfriends->fetchRow())
		{
			$id_user_listfriends = $pp_user_listfriends->id_user_listfriends;
			
		} else {
			$SQL = "INSERT INTO `pp_user_listfriends`(`id_user`, `label`, `date_creation`, `date_update`, `order`)
					VALUES('".$user->id_user."', '".$db->escapeSimple(trim($_POST[user_labellist]))."', NOW(), NOW(), 1)";
			$insert_user_listfriends = $db->query($SQL);
			if(DB::isError($insert_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$insert_user_listfriends->getMessage());
			if(!$id_user_listfriends = $db->insertId()) die ("<li>$SQL<li>ERROR : id_user_listfriends non trouvé");
		}
		
		if($id_user_listfriends)
		{
			// est-ce une invitation ?
			$validation_invitation = false;
			// on cherche si ya un lien id_user_friend -> id_user
			$SQL = "UPDATE `pp_user_friends`
					SET `date_validation`=NOW(), `valide`='1'
					WHERE `id_user_friend`='".$user->id_user."' AND `id_user`='".$db->escapeSimple($_POST[id_user_friend]*1)."'";
			$update_user_listfriends = $db->query($SQL);
			if(DB::isError($update_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$update_user_listfriends->getMessage());
			if($db->affectedRows()) $validation_invitation = true;
			
			// on insère le lien id_user -> id_user_friend
			$SQL = "INSERT INTO `pp_user_friends`(`id_user`, `id_user_friend`, `date_creation`, `valide`, `date_validation`, `id_user_listfriends`)
					VALUES('".$user->id_user."', '".$db->escapeSimple($_POST[id_user_friend]*1)."', NOW(),
					'".($validation_invitation ? '1' : '')."', ".($validation_invitation ? "NOW()" : "''").", '".$id_user_listfriends."')";
			$insert_user_listfriends = $db->query($SQL);
			if(DB::isError($insert_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$insert_user_listfriends->getMessage());
			
			if($validation_invitation)
			{
				if($pp_user_friend->no_mail != '1')
				{
					$subject = $user->login.' a accepté ton invitation';
					$body = '<b><a href="http://www.pronoplus.com/user.php?q='.htmlspecialchars($user->login).'">'.$user->login.'</a></b> a accepté ton invitation à être ton ami.';
					sendemail($pp_user_friend->email, 'Liline de Prono+', 'noreply@pronoplus.com', $subject, $body);
				}
				echo "Tu as accepté <b>".htmlspecialchars($pp_user_friend->login)."</b> comme ami(e).";
				
			} else {
				if($pp_user_friend->no_mail != '1')
				{
					$subject = $user->login.' t\'invite à être son ami';
					$body = '<b><a href="http://www.pronoplus.com/user.php?q='.htmlspecialchars($user->login).'">'.$user->login.'</a></b> t\'invite à être son ami.<br />
							Tu peux accepter en allant sur <a href="http://www.pronoplus.com/user.php?q='.htmlspecialchars($user->login).'">son profil</a> ou bien sur <a href="http://www.pronoplus.com/friends.php">ta page amis</a>.';
					sendemail($pp_user_friend->email, 'Liline de Prono+', 'noreply@pronoplus.com', $subject, $body);
				}
				echo "Ton invitation a été envoyée à <b>".htmlspecialchars($pp_user_friend->login)."</b>.";
			}
		}
	}
}


// Ajouter l'ami dans la liste existante
if($user->id_user && $_POST[action] == 'user_addfriend' && ($_POST[id_user_friend]*1) && ($_POST[id_user_listfriends]*1))
{
	// on cherche si l'user id_user_friend existe
	$SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`login`, `pp_user`.`email`, `pp_user`.`no_mail`
			FROM `pp_user`
			WHERE `pp_user`.`id_user`='".$db->escapeSimple($_POST[id_user_friend]*1)."'";
	$result_user = $db->query($SQL);
	if(DB::isError($result_user)) die ("<li>ERROR : ".$result_user->getMessage());
	if(!$pp_user_friend = $result_user->fetchRow()) die ("<li>ERROR : id_user_friend non trouvé");

	// on cherche si la liste existe
	$SQL = "SELECT `pp_user_listfriends`.id_user_listfriends, `pp_user_listfriends`.`label`
			FROM `pp_user_listfriends`
			WHERE `pp_user_listfriends`.`id_user`='".$user->id_user."'
				AND `pp_user_listfriends`.`id_user_listfriends`='".$db->escapeSimple($_POST[id_user_listfriends]*1)."'";
	$result_user_listfriends = $db->query($SQL);
	if(DB::isError($result_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$result_user_listfriends->getMessage());
	
	if(!$pp_user_listfriends = $result_user_listfriends->fetchRow()) die ("<li>id_user_listfriends n'existe pas");
	
	// est-ce une invitation ?
	$validation_invitation = false;
	// on cherche si ya un lien id_user_friend -> id_user
	$SQL = "UPDATE `pp_user_friends`
			SET `date_validation`=NOW(), `valide`='1'
			WHERE `id_user_friend`='".$user->id_user."' AND `id_user`='".$db->escapeSimple($_POST[id_user_friend]*1)."'";
	$update_user_listfriends = $db->query($SQL);
	if(DB::isError($update_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$update_user_listfriends->getMessage());
	if($db->affectedRows()) $validation_invitation = true;
	
	// on insère le lien id_user -> id_user_friend
	$SQL = "INSERT INTO `pp_user_friends`(`id_user`, `id_user_friend`, `date_creation`, `valide`, `date_validation`, `id_user_listfriends`)
			VALUES('".$user->id_user."', '".$db->escapeSimple($_POST[id_user_friend]*1)."', NOW(),
			'".($validation_invitation ? '1' : '')."', ".($validation_invitation ? "NOW()" : "''").", '".$pp_user_listfriends->id_user_listfriends."')";
	$insert_user_listfriends = $db->query($SQL);
	if(DB::isError($insert_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$insert_user_listfriends->getMessage());
	
	if($validation_invitation)
	{
		if($pp_user_friend->no_mail != '1')
		{
			$subject = $user->login.' a accepté ton invitation';
			$body = '<b><a href="http://www.pronoplus.com/user.php?q='.htmlspecialchars($user->login).'">'.$user->login.'</a></b> a accepté ton invitation à être ton ami.';
			sendemail($pp_user_friend->email, 'Liline de Prono+', 'noreply@pronoplus.com', $subject, $body);
		}
		echo "Tu as accepté <b>".htmlspecialchars($pp_user_friend->login)."</b> comme ami(e).";
		
	} else {
		if($pp_user_friend->no_mail != '1')
		{
			$subject = $user->login.' t\'invite à être son ami';
			$body = '<b><a href="http://www.pronoplus.com/user.php?q='.htmlspecialchars($user->login).'">'.$user->login.'</a></b> t\'invite à être son ami.<br />
					Tu peux accepter en allant sur <a href="http://www.pronoplus.com/user.php?q='.htmlspecialchars($user->login).'">son profil</a> ou bien sur <a href="http://www.pronoplus.com/friends.php">ta page amis</a>.';
			sendemail($pp_user_friend->email, 'Liline de Prono+', 'noreply@pronoplus.com', $subject, $body);
		}			
		echo "Ton invitation a été envoyée à <b>".htmlspecialchars($pp_user_friend->login)."</b>.";
	}
}

// Supprimer l'ami
if($user->id_user && $_POST[action] == 'user_friend_delete' && ($_POST[id_user_friend]*1))
{
	$SQL = "DELETE FROM `pp_user_friends` WHERE `id_user_friend`='".$db->escapeSimple($_POST[id_user_friend]*1)."' AND `id_user`='".$user->id_user."'";
	$delete_user_listfriends = $db->query($SQL);
	if(DB::isError($delete_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$delete_user_listfriends->getMessage());
	
	$SQL = "DELETE FROM `pp_user_friends` WHERE `id_user`='".$db->escapeSimple($_POST[id_user_friend]*1)."' AND `id_user_friend`='".$user->id_user."'";
	$delete_user_listfriends = $db->query($SQL);
	if(DB::isError($delete_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$delete_user_listfriends->getMessage());
	
	echo "Tu n'est plus son ami(e) !";
}
?>