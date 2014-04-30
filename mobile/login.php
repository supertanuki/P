<?php
/**
* Project: PRONOPLUS
* Description: Accueil version iphone
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-07-07
* Version: 1.0
*/

require_once('../init.php');
require_once('functions-iphone.php');
require_once('../mainfunctions.php');
require_once('../contentfunctions.php');

$user = user_authentificate();

echo pp_iphone_header("Se connecter", $is_menu=false, $is_retour=true);

	?>
	<ul id="msgbugsafari" class="pageitem">
		<li class="textbox">
			<div>BUG sur Safari Mobile : impossible de se connecter... En attendant que ce bug soit résolu, essayez d'installer Chrome sur votre mobile ;)</div>
		</li>
	</ul>
	
	<div id="login_form_status" style="display:none"><span class="graytitle" id="login_form_status_msg"></span></div>
	<div id="login_form" style="display:none">
	<form method="post" action="" onsubmit="return pp_login();">
		<ul class="pageitem">
			<li class="smallfield"><span class="name">Pseudo</span><input id="connect_login" placeholder="Ton pseudo" type="text" /></li>
			<li class="smallfield"><span class="name">Mot de passe</span><input id="connect_password" placeholder="Ton mot de passe" type="password" /></li>
			<li class="checkbox"><span class="check"><span class="name">Rester connecté</span><input id="connect_permanent" name="remember" type="checkbox" /></span></li>
			<li class="button"><input name="Submit input" type="submit" value="Valider" /></li>
		</ul>
	</form>
	</div>
	
	<script type="text/javascript" language="javascript">
	// <![CDATA[
	var redirect = '<?php echo $_GET[redirect]; ?>';
	$('msgbugsafari').hide();
	$('login_form').show();
	// ]]>
	</script>
	<?php

echo pp_iphone_footer();
?>