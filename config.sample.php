<?
/**
* Project: PRONOPLUS
* Description: Accueil de l'admin
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-07-07
* Version: 1.0
*/



if($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
{

  // inclusion de PEAR et de DB en local
  if(!ini_set('include_path', ini_get('include_path').';'.$_SERVER['DOCUMENT_ROOT'].'/lib/pear')) die("Error ini_set !");
  error_reporting(E_ERROR | E_WARNING | E_PARSE);
  ini_set("display_errors", 1);


  $db_user = 'root';
  $db_pass = 'root';
  $db_host = 'localhost';
  $db_name = 'pronoplus';

} else {

  $debug = false;
  $mon_ip = 'x.x.x.x';
  
  // en production  
  if(!ini_set('include_path', $_SERVER['DOCUMENT_ROOT'].':'.$_SERVER['DOCUMENT_ROOT'].'/lib/pear')) die("Error ini_set !");

  $db_user = 'root';
  $db_pass = 'root';
  $db_host = 'localhost';
  $db_name = 'pronoplus';
  
  if($debug)
  {
    if($_SERVER['REMOTE_ADDR'] != $mon_ip) 
    {
      $db_pass = 'nimporte_quoi_pour_bloquer_le_site';
    } else {
      // echo '<h1>Site en maintenance</h1>';
    }
  }
}