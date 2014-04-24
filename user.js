/**
* Project: PRONOPLUS
* Description: Actions sur le profil
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2010-01-21
* Version: 1.1
*/

// requete ajax générique
function user_request(id, param)
{
	$('user_add_friend_'+id).update('<img src="/template/default/wait.gif" align="absmiddle" alt="" /> Un instant...');
	
	new Ajax.Updater('user_add_friend_'+id, '/user_actions.php', {
		method: 'post',
		parameters: param
	});
	
	return false;
}

// affichage la liste des listes d'amis
function user_show_lists(id_user_friend)
{
	var param = 'action=user_show_lists&id_user_friend=' + id_user_friend;
	user_request(id_user_friend, param);
	
	return false;
}

// création d'une liste, modification label via les exemples
function user_change_label_list(id_user_friend, label)
{
	$('user_labellist_'+id_user_friend).value = label;
	new Effect.Highlight('user_labellist_'+id_user_friend);
	return false;
}

// création d'une liste, modification label via les exemples
function user_friend_delete(id_user_friend)
{
	var param = 'action=user_friend_delete&id_user_friend=' + id_user_friend;
	user_request(id_user_friend, param);
	return false;
}

function user_addfriend(id_user_friend)
{
	var param = 'action=user_addfriend&id_user_friend=' + id_user_friend + '&id_user_listfriends=' + $('user_listfriends_'+id_user_friend).value;
	user_request(id_user_friend, param);
	return false;
}

function user_addlistfriend(id_user_friend)
{
	if(trim($('user_labellist_'+id_user_friend).value) != '')
	{	
		var param = 'action=user_addlistfriend&id_user_friend=' + id_user_friend + '&user_labellist=' + $('user_labellist_'+id_user_friend).value;
		user_request(id_user_friend, param);
	} else {
		alert('Veuillez choisir un nom de liste.');
	}
	return false;
}