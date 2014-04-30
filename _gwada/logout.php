<?php
/**
* Project: PRONOPLUS
* Description: Déconnexion de l'admin
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-07-07
* Version: 1.0
*/
session_start();
require_once('adminfunctions.php');
$_SESSION[admin] = "";

echo adminheader("Login", " onLoad=\"document.getElementById('login').focus()\"");
?>

Vous êtes déconnecté !


<? echo adminfooter(); ?>