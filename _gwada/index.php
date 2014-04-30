<?php
/**
* Project: PRONOPLUS
* Description: Accueil de l'admin
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-07-07
* Version: 1.0
*/

require_once('../init.php');
require_once('adminfunctions.php');
session_start();
$admin_user = authentificate();

echo adminheader("Admin");

echo AdminName($admin_user);

echo adminfooter();

?>