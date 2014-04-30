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


echo adminheader("Admin");
echo AdminName($admin_user);


if($_POST[message] && $_POST[subject])
{
	$SQL = "INSERT INTO `pp_newsletter`(`subject`, `message`, `date_newsletter`) VALUES('".$_POST[subject]."', '".$_POST[message]."', NOW())";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	$SQL = "SELECT `id_lang`, `login`, `email`
			FROM `pp_user`
			WHERE `no_mail`!='1'";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		while($pp_user = $result->fetchRow())
		{
			$contenthtml = '<p>Bonjour '.$pp_user->login.' !</p><p>'.$_POST[message].'</p>';
			sendemail($pp_user->email, 'Prono+', 'noreply@pronoplus.com', $pp_user->login.', '.$_POST[subject], $contenthtml);
			echo "<li>".$pp_user->email;
		}
		echo "<li><strong>Terminé !!!</strong>";
	}
}

?>


<form method="post">
Sujet<br />
<input name="subject" type="text" size="50" maxlength="250" /><br /><br />
Message<br />
<textarea name="message" cols="50" rows="10"></textarea><br /><br />
<input type="submit" value="Envoyer" />
</form>

<?php


echo adminfooter();
?>