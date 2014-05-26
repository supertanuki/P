<?php
/**
 * Project: PRONOPLUS
 * Description: Mise à jour Sitemap
 * Author: Richard HANNA <supertanuki@gmail.com>
 * Date: 2009-01-03
 * Version: 1.0
 */
$url_rebuild_sitemap = "http://www.pronoplus.com/blog/?sm_command=build&sm_key=7f75fc785c7dd2cabe9adf5260249a93";

chdir(dirname(__FILE__));
chdir('../');
$_SERVER['DOCUMENT_ROOT'] = getcwd();

require_once($_SERVER['DOCUMENT_ROOT'] . '/init.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/mainfunctions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/contentfunctions.php');


class GoogleSitemapGeneratorPage
{
    var $_url;
    var $_priority;
    var $_changeFreq;
    var $_lastMod;

    function GoogleSitemapGeneratorPage()
    {
    }
}

function GoogleSitemapAddPage($page, $priority = 1)
{
    $var = new GoogleSitemapGeneratorPage();
    $var->_url = $page;
    $var->_priority = $priority;
    $var->_changeFreq = 'daily';
    $var->_lastMod = mktime(date("G"), date("i"), date("s"), date("n"), date("j"), date("Y"));
    return $var;
}

$data = array();
$data[] = GoogleSitemapAddPage("http://www.pronoplus.com/");
$data[] = GoogleSitemapAddPage("http://www.pronoplus.com/equipe-de-france-de-football.php");
$data[] = GoogleSitemapAddPage("http://www.pronoplus.com/palmares.php");


// COUPE
$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label`, `pp_cup_matches`.`id_cup_matches`, `pp_cup_matches`.`number_tour`
	FROM `pp_cup_matches`
	INNER JOIN `pp_cup` ON `pp_cup`.`id_cup` = `pp_cup_matches`.`id_cup`
	ORDER BY `pp_cup_matches`.`id_cup` DESC, `pp_cup_matches`.`number_tour` DESC
	LIMIT 1";
$result_cup_matches = $db->query($SQL);
//echo "<li>$SQL";
if (DB::isError($result_cup_matches)) {
    die ("<li>ERROR : " . $result_cup_matches->getMessage() . "<li>$SQL");

} else {
    if ($pp_cup_matches = $result_cup_matches->fetchRow()) {
        $data[] = GoogleSitemapAddPage("http://www.pronoplus.com/cup.php?id=" . $pp_cup_matches->id_cup);
    }
}


// CLASSEMENTS
$SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`label`, `pp_info_country`.`label` AS `country`
		FROM `pp_class` LEFT JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_class`.`id_info_country`
		WHERE `pp_class`.`type`!='day'
		ORDER BY `pp_class`.`order`";
$result = $db->query($SQL);
//echo "<li>$SQL";
if (DB::isError($result)) {
    die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

} else {
    while ($pp_class = $result->fetchRow()) {
        $data[] = GoogleSitemapAddPage("http://www.pronoplus.com/class.php?id=" . $pp_class->id_class);
    }
}


// PRONOSTICS EN COURS
$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`
        FROM `pp_matches`
        WHERE `pp_matches`.`is_calcul`!='1' AND `pp_matches`.`id_cup_matches`=0
        ORDER BY `date_first_match`";
$result_matches = $db->query($SQL);
//echo "<li>$SQL";
if (DB::isError($result)) {
    die ("<li>ERROR : " . $result_matches->getMessage());

} else {
    while ($pp_matches = $result_matches->fetchRow()) {
        $data[] = GoogleSitemapAddPage("http://www.pronoplus.com/pronostiquer.php?id=" . $pp_matches->id_matches);
    }
}


// RESULTATS
$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`
		FROM `pp_matches`
		WHERE `pp_matches`.`is_calcul`='1' AND `pp_matches`.`id_cup_matches`=0
		ORDER BY `date_calcul` DESC";
$result_matches = $db->query($SQL);
//echo "<li>$SQL";
if (DB::isError($result)) {
    die ("<li>ERROR : " . $result_matches->getMessage());

} else {
    while ($pp_matches = $result_matches->fetchRow()) {
        $data[] = GoogleSitemapAddPage("http://www.pronoplus.com/classj.php?id=" . $pp_matches->id_matches);
    }
}


// FORUM
$data[] = GoogleSitemapAddPage("http://www.pronoplus.com/forum-football/");

// THEMES FORUM
$SQL = "SELECT `id_forum_theme`, `label`, `description`, `url` FROM `forum_theme` ORDER BY `order`";
$result = $db->query($SQL);
//echo "<li>$SQL";
if (DB::isError($result)) {
    die ("<li>ERROR : " . $result->getMessage());

} else {
    while ($forum_theme = $result->fetchRow()) {
        $data[] = GoogleSitemapAddPage("http://www.pronoplus.com/forum-football/" . $forum_theme->url, 0.7);
    }
}

// MESSAGES FORUM
$SQL = "SELECT `url`, `Nmsg` FROM `forum` WHERE Nquest=0 AND supp=0 ORDER BY dateder DESC";
$result = $db->query($SQL);
//echo "<li>$SQL";
if (DB::isError($result)) {
    die ("<li>ERROR : $SQL | " . $result->getMessage());

} else {
    while ($forum_msg = $result->fetchRow()) {
        $data[] = GoogleSitemapAddPage("http://www.pronoplus.com/forum-football/" . $forum_msg->url . "-" . $forum_msg->Nmsg . ".html", 0.5);
    }
}


// mise à jour pages supplémentaires
$SQL = "UPDATE `wp_options` SET `option_value` = '" . $db->escapeSimple(serialize($data)) . "'
		WHERE `option_name`='sm_cpages'";
$result_option = $db->query($SQL);
//echo "<li>$SQL";
if (DB::isError($result_option)) die ("<li>ERROR : " . $result_option->getMessage() . "<li>$SQL");

// rebuild du sitemap
echo "<li>" . file_get_contents($url_rebuild_sitemap);
?>