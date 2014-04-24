<?
require_once('init.php');
require_once('mainfunctions.php');

if($_POST[action] == "signin")
{
	$_POST[login] = trim($_POST[login]);
	$_POST[pwd] = trim($_POST[pwd]);
	
	if(strlen($_POST[login]) < 4)
	{
		echo "{'response':400, 'msg':'Veuillez choisir un pseudo d\'au moins 4 caract&egrave;res !'}";
		exit;
	}
	
	if(strlen($_POST[login]) > 50)
	{
		echo "{'response':400, 'msg':'Veuillez choisir un pseudo d\'au plus 50 caract&egrave;res !'}";
		exit;
	}	
	
	if(!preg_match('`^[0-9a-zA-Z-]+$`', $_POST[login]))
	{
		echo "{'response':400, 'msg':'Veuillez choisir un pseudo en n\'utilisant que les caract&egrave;res a &agrave; z, 0 &agrave; 9 ou un tiret -.'}";
		exit;
	}
	
	if(strlen($_POST[pwd]) < 8)
	{
		echo "{'response':400, 'msg':'Veuillez choisir un mot de passe d\'au moins 8 caract&egrave;res !'}";
		exit;
	}
	
	if(strlen($_POST[pwd]) > 50)
	{
		echo "{'response':400, 'msg':'Veuillez choisir un mot de passe d\'au plus 50 caract&egrave;res !'}";
		exit;
	}
	
	if(!preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]_?])*@[[:alnum:]]([-.]?[[:alnum:]])+\.([a-z]{2,6})$`', $_POST[email]))
	{
		echo "{'response':400, 'msg':'Veuillez saisir un email valide !'}";
		exit;
	}
	
	if(strtolower(trim($_POST[captcha])) != 'quatre' && trim($_POST[captcha]) != '4')
	{
		echo "{'response':400, 'msg':'Quoi !? Vous avez &eacute;chou&eacute; &agrave; la question simple de v&eacute;rification ! Seriez-vous un robot-spammeur ?'}";
		exit;
	}
	
	// vérification email
	$SQL = "SELECT `id_user`
			FROM `pp_user`
			WHERE `email`='".$db->escapeSimple($_POST[email])."'";
	$result = $db->query($SQL);
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if($result->numRows())
		{
			echo "{'response':400, 'msg':'Il y a d&eacute;j&agrave; un utilisateur utilisant cet email !'}";
			exit;
		}
	}
	
	// vérification login
	$SQL = "SELECT `id_user`
			FROM `pp_user`
			WHERE `login`='".$db->escapeSimple($_POST[login])."' OR urlprofil='".$db->escapeSimple($_POST[login])."'";
	$result = $db->query($SQL);
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if($result->numRows())
		{
			echo "{'response':400, 'msg':'Il y a d&eacute;j&agrave; un utilisateur utilisant ce login !'}";
			exit;
		}
	}
	
	// insertion
	$SQL = "INSERT INTO `pp_user`(`id_lang`, `login`, `urlprofil`, `pwd`, `email`, `timezone`, `register_date`)
			VALUES(1, '".$db->escapeSimple($_POST[login])."', '".$db->escapeSimple($_POST[login])."', '".$db->escapeSimple($_POST[pwd])."', '".$db->escapeSimple($_POST[email])."', '".$db->escapeSimple($_POST[fuseau])."', NOW())";
	$result = $db->query($SQL);
	if(DB::isError($result)) die ("{'response':400, 'msg':'".$result->getMessage()."'}");
	
	
	sendemail('supertanuki@gmail.com', 'Liline de Prono+', 'noreply@pronoplus.com', 'Prono+ New user : '.$_POST[login], 'Prono+ New user : '.$_POST[login].' / '.$_POST[email]);
	
	
	if($user = setUser($_POST[login], $_POST[pwd]))
	{
		echo "{'response':200}";
	} else {
		echo "{'response':400, 'msg':'Impossible de se connecter !'}";
	}
}
?>