<?
/**
* Project: PRONOPLUS
* Description: authentification à l'admin
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-07-07
* Version: 1.0
*/

require_once('../init.php');
require_once('adminfunctions.php');
session_start();

$login_incorrect = false;

if($_POST[login] && $_POST[pwd])
{
	$strAdmin = md5($_POST[login].'a'.md5($_POST[pwd]));
	if(getAdmin($strAdmin)!=false)
	{
		$_SESSION[admin] = $strAdmin;
		header("Location: index.php");
		exit;
	} else {
		$login_incorrect = true;
	}
}



echo adminheader("Login", " onLoad=\"document.getElementById('login').focus()\"");
?>

<fieldset>
	<legend>Connexion</legend>
	<form method="post" action="login.php">
	<? if($login_incorrect) { ?><strong>Identifiants incorrects</strong><br /><br /><? } ?>
	Login<br />
	<input id="login" type="text" name="login" value="<?=stripslashes($_POST[login])?>" /><br /><br />
	Mot de passe<br />
	<input type="password" name="pwd" value="" /><br /><br />
	<input type="submit" value="Valider" />
	</form>
</fieldset>


<? echo adminfooter(); ?>