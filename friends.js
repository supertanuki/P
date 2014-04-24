/**
* Project: PRONOPLUS
* Description: Actions sur la liste des amis
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2010-01-23
* Version: 1.1
*/

// modifier le libellé de la liste
function friend_edit_listfriend(id_user_listfriends)
{
	$('friends_list_'+id_user_listfriends).hide();
	$('friends_edit_list_'+id_user_listfriends).show();
	return false;
}

// demande de suppression de liste, on met où les utilisateurs ?
function friend_del_listfriend(id_user_listfriends)
{
	$('friends_list_'+id_user_listfriends).hide();
	if($('friends_del_list_'+id_user_listfriends)) $('friends_del_list_'+id_user_listfriends).show();	
	return false;
}

// annulation
function friend_cancel_listfriend(id_user_listfriends)
{
	$('friends_edit_list_'+id_user_listfriends).hide();
	if($('friends_del_list_'+id_user_listfriends)) $('friends_del_list_'+id_user_listfriends).hide();
	$('friends_list_'+id_user_listfriends).show();	
	return false;
}