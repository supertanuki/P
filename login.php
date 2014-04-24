<?
require_once('init.php');
require_once('mainfunctions.php');

if($_POST[action] == "connect")
{
	if($user = setUser($_POST[login], $_POST[pwd], $_POST[permanent]))
	{
		echo "{'response':200}";
	} else {
		echo "{'response':400}";
	}


} else if($_POST[action] == "lostid")
{

	$SQL = "SELECT `id_lang`, `login`, `pwd`
			FROM `pp_user`
			WHERE `email`='".$db->escapeSimple($_POST[email])."'";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if($pp_user = $result->fetchRow())
		{
			$contenthtml = "Bonjour !<br /><br />Voici vos identifiants pour vous connecter à Prono+ :<br /><br />Login : ".$pp_user->login."<br />Mot de passe : ".$pp_user->pwd."<br />";
			sendemail($_POST[email], 'Liline de Prono+', 'noreply@pronoplus.com', 'Prono+ : Rappel de vos identifiants', $contenthtml);
			echo "{'response':200}";
			exit;
		}
	}
	echo "{'response':400}";
}
?>