<?
/**
* Project: PRONOPLUS
* Description: Initialisation du site
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-07-09
* Version: 1.0
*/

// serveur
//$_SERVER['DOCUMENT_ROOT'] = '/home/pplusdev/www';

if($_SERVER['HTTP_HOST'] == 'pronoplus.com')
{
	header('Status: 301 Moved Permanently', false, 301);
	header("Location: http://www.pronoplus.com".$_SERVER['REQUEST_URI']);
	exit;
}

require_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/mainfunctions.php');
if(!require_once('PEAR.php')) die("<li>impossible d'inclure PEAR.php");
if(!require_once('DB.php')) die("<li>impossible d'inclure DB.php");

require_once($_SERVER['DOCUMENT_ROOT'].'/lang/lang.inc.fr.php');// or die("<li>impossible d'inclure lang.inc.fr.php");

$dsn = "mysql://$db_user:$db_pass@$db_host/$db_name";
$db = DB::connect($dsn);
if (DB::isError($db))
{
	include("panne.html");
	exit;
	//die ("<li>ERROR : ".$db->getMessage());
}

$db->setFetchMode(DB_FETCHMODE_OBJECT);

$SQL = "SET NAMES 'utf8'";
$result = $db->query($SQL);
if(DB::isError($result)) die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");

require_once("class_oembed.php");
?>