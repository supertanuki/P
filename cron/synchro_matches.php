<?php
/**
 * Project: PRONOPLUS
 * Description: Mise � jour horaires matches
 * Author: Richard HANNA <supertanuki@gmail.com>
 * Date: 2011-09-12
 * Version: 1.0
 */
chdir(dirname(__FILE__));
chdir('../');
$_SERVER['DOCUMENT_ROOT'] = getcwd();
require_once($_SERVER['DOCUMENT_ROOT'] . '/init.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_gwada/adminfunctions.php');

$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`
		FROM `pp_matches`
		WHERE `is_calcul`=''";
$result = $db->query($SQL);
if (DB::isError($result)) {
    die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

} else {
    $str = '';
    while ($pp_matches = $result->fetchRow()) {
        echo "<li>synchroniser_matches ".$pp_matches->id_matches;
        synchroniser_matches($pp_matches->id_matches);
        $str .= $pp_matches->label . "\n";
    }
    if ($str) mail(EMAIL_MASTER, 'Synchro Prono+', $str);
}