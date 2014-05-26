<?php
/**
 * Project: PRONOPLUS
 * Description: Liste grilles de pronostics
 * Author: Richard HANNA <supertanuki@gmail.com>
 * Date: 2008-07-22
 * Version: 1.0
 */

require_once('../init.php');
require_once('adminfunctions.php');
session_start();
$admin_user = authentificate();


// supprimer la grille
if ($_POST[supprimer] && $_POST[id_matches]) {
    // si coupe ?
    $id_cup_matches = 0;
    $SQL = "SELECT `id_cup_matches`
			FROM `pp_matches` WHERE `id_matches`='" . $_POST[id_matches] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
    else {
        if ($pp_matches = $result->fetchRow()) {
            $id_cup_matches = $pp_matches->id_cup_matches;
            $SQL = "UPDATE `pp_cup_matches`
					SET `id_matches`=0
					WHERE `id_cup_matches`='" . $pp_matches->id_cup_matches . "'";
            $result = $db->query($SQL);
            if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
        }
    }

    // supprimer pp_matches_class / id_matches
    $SQL = "DELETE FROM `pp_matches_class` WHERE `id_matches`='" . $_POST[id_matches] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    // supprimer pp_matches_user
    $SQL = "DELETE FROM `pp_matches_user` WHERE `id_matches`='" . $_POST[id_matches] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    // tous les matchs de la grille
    $SQL = "SELECT `id_match`
			FROM `pp_match`
			WHERE `id_matches`='" . $_POST[id_matches] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
    else {
        while ($pp_match = $result->fetchRow()) {
            // supprimer pp_match_user / id_match
            $SQL = "DELETE FROM `pp_match_user` WHERE `id_match`='" . $pp_match->id_match . "'";
            $result_del = $db->query($SQL);
            if (DB::isError($result_del)) die ("<li>ERROR : " . $result_del->getMessage() . "<li>$SQL");

            // pp_match_user_temp / id_match
            $SQL = "DELETE FROM `pp_match_user_temp` WHERE `id_match`='" . $pp_match->id_match . "'";
            $result_del = $db->query($SQL);
            if (DB::isError($result_del)) die ("<li>ERROR : " . $result_del->getMessage() . "<li>$SQL");
        }
    }

    // pp_class_user / id_matches
    $SQL = "DELETE FROM `pp_class_user` WHERE `id_matches`='" . $_POST[id_matches] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    // pp_class_user_temp / id_matches
    $SQL = "DELETE FROM `pp_class_user_temp` WHERE `id_matches`='" . $_POST[id_matches] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    // supprimer pp_match / id_matches
    $SQL = "DELETE FROM `pp_match` WHERE `id_matches`='" . $_POST[id_matches] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    // supprimer pp_matches
    $SQL = "DELETE FROM `pp_matches` WHERE `id_matches`='" . $_POST[id_matches] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    $message = "Grille supprimée !";
}


// sauver et calculer
if (($_POST[save_and_calcule] || $_POST[save_prov]) && $_POST[id_matches] && is_array($_POST[score_host]) && is_array($_POST[score_visitor])) {

    foreach ($_POST[score_host] as $id_match => $ss) {
        $SQL = "UPDATE `pp_match` SET `score`='" . $_POST[score_host][$id_match] . "-" . $_POST[score_visitor][$id_match] . "'
				WHERE `id_match`='" . $id_match . "' AND `id_matches`='" . $_POST[id_matches] . "'";
        $result = $db->query($SQL);
        //echo "<li>$SQL";
        if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
    }

    if ($_POST[save_and_calcule])
        $type = 'classement';
    else
        $type = 'provisoire';

    calcul_class($_POST[id_matches], $type);

    $message = "Calcul termin&eacute; !";
}


// synchroniser matchs
if ($_POST[synchro_match] && $_POST[id_matches]) {
    synchroniser_matches($_POST[id_matches]);
}


// modifier un match
if ($_POST[update_match] && $_POST[id_matches] && $_POST[id_match]) {
    $date_match = $_POST[date_annee] . "-" . $_POST[date_mois] . "-" . $_POST[date_jour] . " " . $_POST[date_heure] . ":" . $_POST[date_minute] . ":00";
    $SQL = "UPDATE `pp_match` SET id_team_host='" . $_POST[id_team_host] . "', id_team_visitor='" . $_POST[id_team_visitor] . "',
			date_match='" . $date_match . "'
			WHERE `id_match`='" . $_POST[id_match] . "' AND `id_matches`='" . $_POST[id_matches] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    $SQL = "UPDATE `pp_matches` SET date_first_match='" . $date_match . "'
			WHERE `id_matches`='" . $_POST[id_matches] . "' AND '" . $date_match . "' < date_first_match";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    $SQL = "UPDATE `pp_matches` SET date_last_match='" . $date_match . "'
			WHERE `id_matches`='" . $_POST[id_matches] . "' AND '" . $date_match . "' > date_last_match";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
}


echo adminheader("Admin");

echo AdminName($admin_user);
?>

    <script language="javascript">
        <!--
        var is_toggling = false;

        function showTableMatches(id_info_matches) {
            if ($('is_open_' + id_info_matches) == undefined) return false;

            if ($('is_open_' + id_info_matches).value != '1') {
                $('is_open_' + id_info_matches).value = '1';
                $('view_' + id_info_matches).src = 'images/zoom_out.png';
                Effect.BlindDown('table_matches_' + id_info_matches, { duration: 0.3 });
            } else {
                $('is_open_' + id_info_matches).value = '0';
                $('view_' + id_info_matches).src = 'images/zoom_in.png';
                Effect.BlindUp('table_matches_' + id_info_matches, { duration: 0.3 });
            }
        }

        function show_update_match(id_matches, id_match) {
            $('update_match_' + id_matches).update('<p><strong>Chargement...</strong></p>');
            new Ajax.Updater('update_match_' + id_matches, 'action_admin.php', {
                method: 'post',
                parameters: 'action=show_update_match&id_matches=' + id_matches + '&id_match=' + id_match
            });
        }
        -->
    </script>

<?php if ($message) echo "<p class=\"info\">$message</p>"; ?>

<?php
liste_matches_calcul('Grille de pronostics', $is_calcul = '', $limit = 0, $order = 'date_last_match', $ordertype = 'ASC');
echo '<br />';
liste_matches_calcul('Grilles calculées récemment (au cas où erreur de score)', $is_calcul = '1', $limit = 4, $order = 'date_calcul', $ordertype = 'DESC');

function liste_matches_calcul($libelle, $is_calcul = '', $limit = 0, $order = 'date_last_match', $ordertype = 'ASC')
{
    global $db, $txtlang
    ?>
    <fieldset>
        <legend><?php echo $libelle ?></legend>

        <?php
        $SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`id_cup_matches`,
				DATE_FORMAT(`pp_matches`.`date_first_match`, '" . $txtlang['AFF_DATE_TIME_SQL'] . "') AS `date_first_match_format`,
				DATE_FORMAT(`pp_matches`.`date_last_match`, '" . $txtlang['AFF_DATE_TIME_SQL'] . "') AS `date_last_match_format`,
				`pp_info_country`.`label` AS `country`
				FROM `pp_matches`
				INNER JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_matches`.`id_info_country`
				WHERE `is_calcul`='" . $is_calcul . "'
				ORDER BY `pp_matches`.`" . $order . "` " . $ordertype . "
				" . ($limit ? "LIMIT " . $limit : "");
        $result = $db->query($SQL);
        //echo "<li>$SQL";
        if (DB::isError($result)) {
            die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

        } else {
            echo "<table class=\"table_journee\">
					<tr>
						<th></th>
						<th>Grille</th>
						<th>Pays</th>
						<th>Date premier match</th>
						<th>Date dernier match</th>
						<th></th>
					</tr>";
            while ($pp_matches = $result->fetchRow()) {
                echo "<tr>
						<td class=\"table_match_line\"><a href=\"javascript:showTableMatches(" . $pp_matches->id_matches . ")\" title=\"Voir les matchs\"><img id=\"view_" . $pp_matches->id_matches . "\" src=\"images/zoom_in.png\" height=\"16\" width=\"16\" border=\"0\"></a><input id=\"is_open_" . $pp_matches->id_matches . "\" type=\"hidden\" value=\"0\"</td>
						<td>" . formatDbData($pp_matches->label) . "</td>
						<td>" . formatDbData($pp_matches->country) . "</td>
						<td>" . $pp_matches->date_first_match_format . "</td>
						<td>" . $pp_matches->date_last_match_format . "</td>
					</tr>";

                echo "<tr>
						<td colspan=\"6\" class=\"table_match_line\"><div id=\"table_matches_" . $pp_matches->id_matches . "\" style=\"display:none\">
						<form method=\"post\" action=\"\" onsubmit=\"return confirm('Sûr ?');\">
						<input type=\"hidden\" name=\"id_matches\" value=\"" . $pp_matches->id_matches . "\">
						<table class=\"table_match\" border=\"0\">";

                $SQL = "SELECT `pp_match`.`id_match`, `pp_match`.`score`, `team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`,
						DATE_FORMAT(`pp_match`.`date_match`, '" . $txtlang['AFF_DATE_TIME_SQL'] . "') AS `date_match_format`
						FROM `pp_match`
						INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
						INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
						WHERE `pp_match`.`id_matches`='" . $pp_matches->id_matches . "'
						ORDER BY `pp_match`.`date_match`";
                $result_match = $db->query($SQL);
                if (DB::isError($result_match)) {
                    die ("<li>ERROR : " . $result_match->getMessage());

                } else {
                    while ($pp_match = $result_match->fetchRow()) {
                        echo "<tr>
								<td align=\"right\">" . formatDbData($pp_match->team_host_label) . "</td>
								<td align=\"center\"><select name=\"score_host[" . $pp_match->id_match . "]\">";
                        echo "<option value=\"\">-</option>";
                        for ($j = 0; $j <= 9; $j++) echo "<option value=\"" . $j . "\" " . ($pp_match->score != '' && $pp_match->score != '-' && substr($pp_match->score, 0, 1) == $j + '' ? "selected=\"selected\"" : "") . ">" . $j . "</option>";
                        echo "<option value=\"R\" " . (substr($pp_match->score, 0, 1) == 'R' ? "selected=\"selected\"" : "") . ">R</option>";
                        echo "</select> - <select name=\"score_visitor[" . $pp_match->id_match . "]\">";
                        echo "<option value=\"\">-</option>";
                        for ($j = 0; $j <= 9; $j++) echo "<option value=\"" . $j . "\" " . ($pp_match->score != '' && $pp_match->score != '-' && substr($pp_match->score, 2, 1) == $j + '' ? "selected=\"selected\"" : "") . ">" . $j . "</option>";
                        echo "<option value=\"R\" " . (substr($pp_match->score, 2, 1) == 'R' ? "selected=\"selected\"" : "") . ">R</option>";
                        echo "</select></td>
								<td>" . formatDbData($pp_match->team_visitor_label) . "</td>
								<td>" . $pp_match->date_match_format . "</td>
								<td><a href=\"#\" onclick=\"show_update_match(" . $pp_matches->id_matches . ", " . $pp_match->id_match . "); return false;\"><img src=\"images/icon_edit.gif\" border=\"0\" /></a></td>
							</tr>";
                    }
                }

                echo "<tr><td colspan=\"5\" align=\"center\"><input type=\"submit\" name=\"synchro_match\" value=\"Synchroniser les matchs\" style=\"background-color:#ccc; padding:6px;\" />";
                echo " <input type=\"submit\" name=\"supprimer\" value=\"Supprimer la grille\" style=\"background-color:#cc5555; padding:6px;\" /> ";
                echo "<input type=\"submit\" name=\"save_and_calcule\" value=\"Enregistrer et calculer\" style=\"background-color:#ff2222; color:#fff; padding:6px;\" /></td></tr>
					</table></form>
					
					<div id=\"update_match_" . $pp_matches->id_matches . "\"></div>
					
					</div></td>
				</tr>";

            }
            echo "</table>";
        }
        ?>
    </fieldset>
<?php
}

?>

<?php if ($_POST[id_matches]) { ?>
    <script language="javascript">
        showTableMatches(<?php echo $_POST[id_matches]?>);
    </script>
<?php } ?>


<?php
echo adminfooter();
?>