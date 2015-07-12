<?php
/**
 * Project: PRONOPLUS
 * Description: Paramétrage des grilles en cron
 * Author: Richard HANNA <supertanuki@gmail.com>
 * Date: 2015-07-12
 * Version: 1.0
 */

require_once('../init.php');
require_once('adminfunctions.php');

session_start();
$admin_user = authentificate();

if ($_GET['id'] && $_GET['action'] == 'delete') {
    $SQL = "DELETE FROM `pp_matches_cron` WHERE `id` = '" . $_GET['id'] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    header("Location: matches_cron.php");
    exit;

} elseif ($_GET['id'] && $_GET['action'] == 'edit') {
    $SQL = "SELECT * FROM `pp_matches_cron` WHERE `id` = '" . $_GET['id'] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
    $pp_matches_cron_to_edit = $result->fetchRow();

    // Update
    if ($_POST['id_league'] && $_POST['url_type'] && $_POST['url_id'] && $_POST['day_number'] && $_POST['increment']) {
        $SQL = "UPDATE `pp_matches_cron` SET
			    `id_league` = '" . $_POST['id_league'] . "',
                `url_type` = '" . $_POST['url_type'] . "',
                `url_id` = '" . $_POST['url_id'] . "',
                `day_number` = '" . $_POST['day_number'] . "',
                `increment` = '" . $_POST['increment'] . "',
                `enabled` = '" . $_POST['enabled'] . "'
                WHERE
                `id` = '" . $_GET['id'] . "'";
        $result = $db->query($SQL);
        if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

        header("Location: matches_cron.php");
        exit;
    }

} elseif ($_POST['id_league'] && $_POST['url_type'] && $_POST['url_id'] && $_POST['day_number'] && $_POST['increment']) {
    $SQL = "INSERT INTO `pp_matches_cron`(`id_league`, `url_type`, `url_id`, `day_number`, `increment`, `enabled`)
			VALUES(
			    '" . $_POST['id_league'] . "',
                '" . $_POST['url_type'] . "',
                '" . $_POST['url_id'] . "',
                '" . $_POST['day_number'] . "',
                '" . $_POST['increment'] . "',
                '" . $_POST['enabled'] . "'
                )";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    header("Location: matches_cron.php");
    exit;
}


echo adminheader("Admin");
echo AdminName($admin_user);

if ($_GET['id'] && ($_GET['action'] == 'preview' || $_GET['action'] == 'run')) {
    $SQL = "SELECT `pp_matches_cron`.*, `pp_league`.`label` AS `league_label`
            FROM `pp_matches_cron`
            INNER JOIN `pp_league` ON `pp_league`.`id_league` = `pp_matches_cron`.`id_league`
            WHERE `id` = '" . $_GET['id'] . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    $pp_matches_cron_to_preview = $result->fetchRow();

    require_once('../cron/update_functions.php');

    run_matches_cron($pp_matches_cron_to_preview, $_GET['action'] == 'preview' ? true : false);
}
?>

    <fieldset>
        <legend><?php echo isset($pp_matches_cron_to_edit) ? 'Modifier' : 'Ajouter un cron'; ?></legend>
        <form method="post">
            Championnat :<br/>

            <?php
            $SQL = "SELECT `label`, `id_league`
		            FROM `pp_league`
		            ORDER BY `label`";
            $result_league = $db->query($SQL);
            if (DB::isError($result_match)) {
                die ("<li>ERROR : " . $result_league->getMessage());

            } else {
                echo "<select name=\"id_league\" required><option></option>";
                while ($pp_league = $result_league->fetchRow()) {
                    echo "<option value=\"" . $pp_league->id_league . "\"
                    ".(isset($pp_matches_cron_to_edit) && $pp_matches_cron_to_edit->id_league == $pp_league->id_league ? 'selected' : '')."
                    >" . $pp_league->label . "</option>";
                }
                echo "</select>";
            }
            ?>
            <br/><br/>

            Type d'URL :<br/>
            <label>
                <input name="url_type" type="radio" value="URL_RESULTAT"
                <?php echo !isset($pp_matches_cron_to_edit) || isset($pp_matches_cron_to_edit) && $pp_matches_cron_to_edit->url_type == 'URL_RESULTAT' ? 'checked' : '' ?>
                >
                Résultat
            </label>
            <br>
            <label>
                <input name="url_type" type="radio" value="URL_GROUPE"
                    <?php echo isset($pp_matches_cron_to_edit) && $pp_matches_cron_to_edit->url_type == 'URL_GROUPE' ? 'checked' : '' ?>
                >
                Groupe
            </label>

            <br/><br/>

            Id :<br/><input type="text" name="url_id" required value="<?php echo isset($pp_matches_cron_to_edit) ? $pp_matches_cron_to_edit->url_id : '' ?>" /><br/><br/>

            Numéro journée 1ère grille :<br/><input type="text" name="day_number" value="<?php echo isset($pp_matches_cron_to_edit) ? $pp_matches_cron_to_edit->day_number : '1' ?>" required /><br/><br/>

            Nombre d'incrément :<br/><input type="text" name="increment" value="<?php echo isset($pp_matches_cron_to_edit) ? $pp_matches_cron_to_edit->increment : '1' ?>" required /><br/><br/>

            <label>
                <input name="enabled" type="checkbox" value="1"
                <?php echo isset($pp_matches_cron_to_edit) && $pp_matches_cron_to_edit->enabled ? 'checked' : '' ?>
                >
                Activé
            </label>

            <br/><br/>

            <input type="submit" value="Enregistrer"/>
        </form>
    </fieldset>


    <?php
    // LIST
    $SQL = "SELECT `pp_matches_cron`.*, `pp_league`.`label` AS `league_label`
            FROM `pp_matches_cron` INNER JOIN `pp_league` ON `pp_league`.`id_league` = `pp_matches_cron`.`id_league`
            ORDER BY `pp_matches_cron`.`enabled` DESC, `pp_league`.`label`, `pp_matches_cron`.`day_number`";
    $results = $db->query($SQL);
    if (DB::isError($results)) {
        die ("<li>ERROR : " . $results->getMessage());

    } else {
        ?>
        <br>
        <a href="/cron/update_classement_championnats.php">Lancer tous les crons</a>
        <br><br>
        <table border="1" cellspacing="0">
            <tr>
                <th>Championnat</th>
                <th>Url</th>
                <th>Numéro journée</th>
                <th>Nombre d'incrément</th>
                <th>Activé</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <?php
        while ($pp_matches_cron = $results->fetchRow()) {
            ?>
            <tr>
                <td>
                    <?php echo $pp_matches_cron->league_label; ?>
                </td>
                <td>
                    <?php echo str_replace('%ID%', $pp_matches_cron->url_id, constant($pp_matches_cron->url_type)); ?>
                </td>
                <td>
                    <?php echo $pp_matches_cron->day_number; ?>
                </td>
                <td>
                    <?php echo $pp_matches_cron->increment; ?>
                </td>
                <td>
                    <?php echo $pp_matches_cron->enabled ? 'Oui' : ''; ?>
                </td>
                <td>
                    <a href="matches_cron.php?action=run&id=<?php echo $pp_matches_cron->id; ?>">Lancer</a>
                </td>
                <td>
                    <a href="matches_cron.php?action=preview&id=<?php echo $pp_matches_cron->id; ?>">Prévisualiser</a>
                </td>
                <td>
                    <a href="matches_cron.php?action=edit&id=<?php echo $pp_matches_cron->id; ?>">Modifier</a>
                </td>
                <td>
                    <a href="matches_cron.php?action=delete&id=<?php echo $pp_matches_cron->id; ?>">Supprimer</a>
                </td>
            </tr>
            <?php
        }
        ?>
        </table>
        <?php
    }
    ?>

<?php
echo adminfooter();
