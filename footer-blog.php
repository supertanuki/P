<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/init.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/mainfunctions.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/contentfunctions.php');

$user = user_authentificate();

pagefooter(array('forblog'=>true));
?>