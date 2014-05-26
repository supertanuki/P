<?php
/**
 * Project: PRONOPLUS
 * Description: Action AJAX
 * Author: Richard HANNA <supertanuki@gmail.com>
 * Date: 2008-11-16
 * Version: 1.0
 */

require_once('../init.php');
require_once('adminfunctions.php');
require_once('../lang/lang.inc.fr.php');
session_start();
$admin_user = authentificate();


//afficher formulaire de modification d'un match
if ($_POST[action] == 'show_update_match' && $_POST[id_matches] && $_POST[id_match]) {
    $html = '';

    // liste des équipes
    $arrTeam = array();
    $SQL = "SELECT `pp_league`.`label` AS `label_league`, `pp_team`.`label` AS `label_team`, `pp_team`.`id_team`
			FROM `pp_team`
			LEFT JOIN `pp_league` ON `pp_league`.`id_league`=`pp_team`.`id_league`
			ORDER BY `pp_league`.`label`, `pp_team`.`label`";
    $result_team = $db->query($SQL);
    //echo "<li>$SQL";

    if (DB::isError($result_match)) {
        die ("<li>ERROR : " . $result_team->getMessage());

    } else {
        while ($pp_team = $result_team->fetchRow()) {
            $arrTeam[] = $pp_team;
        }
    }


    $SQL = "SELECT `pp_match`.`id_match`, `pp_match`.`score`, `pp_match`.`id_team_host`, `pp_match`.`id_team_visitor`,
				`pp_match`.`date_match`
			FROM `pp_match`
			WHERE `pp_match`.`id_matches`='" . $_POST[id_matches] . "' AND `pp_match`.`id_match`='" . $_POST[id_match] . "'";
    $result_match = $db->query($SQL);
    //echo "<li>$SQL";

    if (DB::isError($result_match)) {
        die ("<li>ERROR : " . $result_match->getMessage());

    } else {
        if ($pp_match = $result_match->fetchRow()) {
            $html .= "<fieldset><legend>Modifier le match</legend>
						<form method=\"post\" action=\"\">
						<input type=\"hidden\" name=\"update_match\" value=\"1\">
						<input type=\"hidden\" name=\"id_matches\" value=\"" . $_POST[id_matches] . "\">
						<input type=\"hidden\" name=\"id_match\" value=\"" . $_POST[id_match] . "\">
						<table width=\"100%\" class=\"table_match\"><tr class=\"table_match_line\">";

            $html .= "<td align=\"right\"><select name=\"id_team_host\">";
            reset($arrTeam);
            //echo "<pre>"; print_r($arrTeam); echo "</pre>";
            $optgroup = '____';
            foreach ($arrTeam as $team) {
                if ($optgroup != $team->label_league) {
                    if ($optgroup != '____') $html .= "</optgroup>";
                    $optgroup = $team->label_league;
                    $html .= "<optgroup label=\"" . ($optgroup == '' ? 'Autres' : $optgroup) . "\">";
                }
                $html .= "<option value=\"" . $team->id_team . "\" " . ($team->id_team == $pp_match->id_team_host ? "selected=\"selected\"" : "") . ">" . $team->label_team . "</option>";
            }
            $html .= "</optgroup>";
            $html .= "</select></td>";

            $html .= "<td align=\"center\">-</td>";

            $html .= "<td><select name=\"id_team_visitor\">";
            reset($arrTeam);
            $optgroup = '____';
            foreach ($arrTeam as $team) {
                if ($optgroup != $team->label_league) {
                    if ($optgroup != '____') $html .= "</optgroup>";
                    $optgroup = $team->label_league;
                    $html .= "<optgroup label=\"" . $optgroup . "\">";
                }
                $html .= "<option value=\"" . $team->id_team . "\" " . ($team->id_team == $pp_match->id_team_visitor ? "selected=\"selected\"" : "") . ">" . $team->label_team . "</option>";
            }
            $html .= "</select></td>";

            $html .= "<td align=\"center\">";
            $html .= "<select name=\"date_jour\">";
            for ($i = 1; $i <= 31; $i++) $html .= "<option value=\"" . $i . "\" " . ($i == 1 * substr($pp_match->date_match, 8, 2) ? "selected=\"selected\"" : "") . ">" . $i . "</option>";
            $html .= "</select>";
            $html .= "<select name=\"date_mois\">";
            for ($i = 1; $i <= 12; $i++) $html .= "<option value=\"" . $i . "\" " . ($i == 1 * substr($pp_match->date_match, 5, 2) ? "selected=\"selected\"" : "") . ">" . $txtlang['MONTH_' . ($i - 1)] . "</option>";
            $html .= "</select>";
            $html .= "<select name=\"date_annee\">";
            for ($i = date('Y') - 1; $i <= date('Y') + 1; $i++) $html .= "<option value=\"" . $i . "\" " . ($i == 1 * substr($pp_match->date_match, 0, 4) ? "selected=\"selected\"" : "") . ">" . $i . "</option>";
            $html .= "</select>";

            $html .= " à <select name=\"date_heure\">";
            for ($i = 0; $i <= 23; $i++) $html .= "<option value=\"" . $i . "\" " . ($i == 1 * substr($pp_match->date_match, 11, 2) ? "selected=\"selected\"" : "") . ">" . $i . "</option>";
            $html .= "</select>h<select name=\"date_minute\">";
            for ($i = 0; $i <= 55; $i += 5) $html .= "<option value=\"" . $i . "\" " . ($i == 1 * substr($pp_match->date_match, 14, 2) ? "selected=\"selected\"" : "") . ">" . $i . "</option>";
            $html .= "</select>";

            $html .= "</td></tr></table>
						<div align=\"center\"><input type=\"submit\" value=\"Enregistrer\" style=\"background-color:#ccc; padding:6px;\" /></div>
						</form>
						</fieldset><br />";
        }
    }

    echo $html;
}