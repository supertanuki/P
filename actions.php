<?php
/**
* Project: PRONOPLUS
* Description: Actions ajax
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2009-08-26
* Version: 1.1
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

// suppression postit
if($user->id_user && $_POST[action] == 'deletepostit' && $_POST[id_postit])
{
    $SQL = "SELECT * FROM `pp_postit`
            WHERE id_postit = '".$db->escapeSimple($_POST[id_postit])."'
            AND (`pp_postit`.`id_user` = 0 OR `pp_postit`.`id_user` = ".$user->id_user.")";
    $result_postit = $db->query($SQL);
    if(DB::isError($result_postit))
    {
        die ("<li>ERROR : ".$result_postit->getMessage()."<li>$SQL");

    } else {
        if($postit = $result_postit->fetchRow())
        {
            if($postit->id_user)
            {
                $SQL = "UPDATE `pp_postit` SET `active` = '' WHERE `id_postit` = " . $postit->id_postit;
                $db->query($SQL);
            } else {
                $SQL = "INSERT INTO `pp_postit_user`(`id_postit`, `id_user`, `deleted`)
                        VALUES('".$postit->id_postit."', '".$db->escapeSimple($user->id_user)."', '1')";
                $db->query($SQL);
            }
        }
    }
}

// noter un match
if($user->id_user && $_POST[id_info_match] && $_POST[action] == 'noter_match' && $_POST[note]>=0 && $_POST[note]<=20)
{
	if($user->id_user)
	{
		$SQL = "UPDATE `pp_info_match_note`
		        SET `note`='".$db->escapeSimple($_POST[note])."', date_update=NOW()
		        WHERE `id_info_match`='".$db->escapeSimple($_POST[id_info_match])."' AND `id_user`='".$db->escapeSimple($user->id_user)."'";
		$db->query($SQL);
		if(!$db->affectedRows())
		{		
			$SQL = "INSERT INTO `pp_info_match_note`(`id_info_match`, `id_user`, `note`, date_update)
			        VALUES('".$db->escapeSimple($_POST[id_info_match])."', '".$db->escapeSimple($user->id_user)."', '".$db->escapeSimple($_POST[note])."', NOW())";
			$db->query($SQL);
		}
	}
}