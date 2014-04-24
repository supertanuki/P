<?
/**
* Project: PRONOPLUS
* Description: Mise � jour Sitemap
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2009-01-03
* Version: 1.0
*/
chdir( dirname(__FILE__) );
chdir( '../' );
$_SERVER['DOCUMENT_ROOT'] = getcwd();

ini_set('include_path', '.:/usr/share/php5');

require_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
if(!require_once('PEAR.php')) die("<li>impossible d'inclure PEAR.php");
if(!require_once('DB.php')) die("<li>impossible d'inclure DB.php");

$dsn = "mysql://$db_user:$db_pass@$db_host/$db_name";
$db = DB::connect($dsn);
if (DB::isError($db))
{
	mail(EMAIL_MASTER, 'BDD serveur est en panne !', $db->getMessage().' ('.date("d/m/Y H:i:s").')');
}
?>