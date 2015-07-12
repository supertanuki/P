<?php

/**
 * Project: PRONOPLUS
 * Description: Mise à jour classements des championnats
 * Author: Richard HANNA <supertanuki@gmail.com>
 * Date: 2010-10-13
 * Version: 1.0
 */

require_once('acl.php');
checkAccess();


chdir(dirname(__FILE__));
chdir('../');
$_SERVER['DOCUMENT_ROOT'] = getcwd();


require_once($_SERVER['DOCUMENT_ROOT'] . '/init.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/mainfunctions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/contentfunctions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/cron/update_functions.php');

set_time_limit(0);

$debug = false;
$email = EMAIL_MASTER;
$saison_en_cours = getConfig('saison_en_cours');

// DEBUT TRAITEMENT

$SQL = "SELECT `pp_matches_cron`.*, `pp_league`.`label` AS `league_label`
            FROM `pp_matches_cron` INNER JOIN `pp_league` ON `pp_league`.`id_league` = `pp_matches_cron`.`id_league`
            WHERE `pp_matches_cron`.`enabled` = '1'";
$results = $db->query($SQL);
if (DB::isError($results)) {
    die ("<li>ERROR : " . $results->getMessage());

} else {
    while ($pp_matches_cron = $results->fetchRow()) {
        run_matches_cron($pp_matches_cron);
    }
}


echo "<li><b>FIN Extract !!!!</b>";

maj_classements();
