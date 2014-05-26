<?php
/**
 * Project: PRONOPLUS
 * Description: Accueil version iphone
 * Author: Richard HANNA <supertanuki@gmail.com>
 * Date: 2008-07-07
 * Version: 1.0
 */

require_once('../init.php');
require_once('functions-iphone.php');
require_once('../mainfunctions.php');
require_once('../contentfunctions.php');

$user = user_authentificate();


echo pp_iphone_header("Accueil", $is_menu = true, $is_retour = false, $is_list = true);


if ($user->id_user) {
    ?>
    <div id="user">
        <?php
        if ($avatar = getAvatar($user->id_user, $user->avatar_key, $user->avatar_ext, 'small')) {
            ?><img alt="" src="/avatars/<?php echo $avatar; ?>" align="absmiddle" /> <?php
        }
        echo $user->login;
        echo '<div id="rightbutton"><a href="logout.php" onclick="return confirm(\'Souhaitez-vous vous déconnecter ?\');">Déconnecter</a></div>';

        if ($_GET[msg] == 'p') {
            ?>
            <p style="color:red; font-weight:bold; text-align:center; border-top:1px solid #ddd; padding-top:10px;">Vos
                pronostics ont bien été enregistrés !</p>
        <?php
        }
        ?>
    </div>
<?php
}
?>
<ul>
    <li class="title">Grilles de pronostics en cours</li>
<?php
// Grilles de pronostics en cours
$list_matches = CurrentMatches(false, array('return_data' => true));
$line = 0;
foreach ($list_matches as $pp_matches) if (!$pp_matches->save_yet && substr($pp_matches->diff_date_last_match, 0, 1) == '-') {
    $image = $pp_matches->id_cup_matches ? 'coupe.png' : $pp_matches->image;

    // Coupe ?
    if ($pp_matches->id_cup_matches) {
        // le joueur est-il qualifié pour la coupe ?
        $SQL = "SELECT `id_user_won`, `number_tour`
						FROM `pp_cup_match_opponents`
						WHERE `id_cup_matches`='" . $pp_matches->id_cup_matches . "'
						AND (`id_user_host`='" . $user->id_user . "' OR `id_user_visitor`='" . $user->id_user . "')";
        $result_cup_user = $db->query($SQL);
        if (DB::isError($result_cup_user)) {
            die ("<li>ERROR : " . $result_cup_user->getMessage() . "<li>$SQL");
        } else {
            if ($cup_user = $result_cup_user->fetchRow()) {
                pp_iphone_matches($pp_matches->label, 'pronostiquer.php?id=' . $pp_matches->id_matches, '/template/default/' . $image, format_diff_date($pp_matches->diff_date_first_match));
                $line++;
            }
        }
    } else {
        pp_iphone_matches($pp_matches->label, 'pronostiquer.php?id=' . $pp_matches->id_matches, '/template/default/' . $image, format_diff_date($pp_matches->diff_date_first_match));
        $line++;
    }
}
if ($line == 0) {
    ?>
    <li class="textbox">Aucune grille en attente de pronostics</li>
<?php
}
?>

    <li class="title">Grilles de matchs jouées ou terminées</li>
<?php

// Grilles de matchs jouées ou terminées
$line = 0;
foreach ($list_matches as $pp_matches) if ($pp_matches->save_yet || substr($pp_matches->diff_date_last_match, 0, 1) != '-') {
    if ($pp_matches->show_class_prov)
        $url = 'classj.php?id=' . $pp_matches->id_matches;
    else
        $url = 'pronostiquer.php?id=' . $pp_matches->id_matches;

    $image = $pp_matches->id_cup_matches ? 'coupe.png' : $pp_matches->image;

    // Coupe ?
    if ($pp_matches->id_cup_matches) {
        // le joueur est-il qualifié pour la coupe ?
        $SQL = "SELECT `id_user_won`, `number_tour`
						FROM `pp_cup_match_opponents`
						WHERE `id_cup_matches`='" . $pp_matches->id_cup_matches . "'
						AND (`id_user_host`='" . $user->id_user . "' OR `id_user_visitor`='" . $user->id_user . "')";
        $result_cup_user = $db->query($SQL);
        if (DB::isError($result_cup_user)) {
            die ("<li>ERROR : " . $result_cup_user->getMessage() . "<li>$SQL");
        } else {
            if ($cup_user = $result_cup_user->fetchRow()) {
                pp_iphone_matches($pp_matches->label, $url, '/template/default/' . $image, format_diff_date($pp_matches->diff_date_first_match));
                $line++;
            }
        }
    } else {
        pp_iphone_matches($pp_matches->label, $url, '/template/default/' . $image, format_diff_date($pp_matches->diff_date_first_match));
        $line++;
    }
}
if ($line == 0) {
    ?>
    <li class="textbox">Aucune grille jouée ou terminée</li>
<?php
}


$list_matches = ClassMatches(array('return_data' => true));
?>
    <li class="title">Résultats</li>
<?php
if (!is_array($list_matches) || !count($list_matches)) {
    ?>
    <li class="textbox">Aucun résultat pour l'instant...</li>
<?php
} else {
    // Résultats
    $line = 0;
    foreach ($list_matches as $pp_matches) {
        if ($pp_matches->class_user && $pp_matches->class_nb_points) {
            $image = $pp_matches->id_cup_matches ? 'coupe.png' : $pp_matches->image;
            $description = $pp_matches->class_user . '<sup>' . ($pp_matches->class_user > 1 ? 'ème' : 'er') . '</sup>, ' . $pp_matches->class_nb_points . ' pts';


            // Coupe ?
            if ($pp_matches->id_cup_matches) {
                // le joueur est-il qualifié pour la coupe ?
                $SQL = "SELECT `id_user_won`, `number_tour`
                FROM `pp_cup_match_opponents`
                WHERE `id_cup_matches`='" . $pp_matches->id_cup_matches . "'
                AND (`id_user_host`='" . $user->id_user . "' OR `id_user_visitor`='" . $user->id_user . "')";
                $result_cup_user = $db->query($SQL);
                if (DB::isError($result_cup_user)) {
                    die ("<li>ERROR : " . $result_cup_user->getMessage() . "<li>$SQL");
                } else {
                    if ($cup_user = $result_cup_user->fetchRow()) {
                        if ($cup_user->id_user_won == $user->id_user)
                            $description = ($cup_user->number_tour == 4 ? 'Gagné !!! avec ' : 'Qualifié, ') . $pp_matches->class_nb_points . ' pts';
                        else
                            $description = 'Eliminé, ' . $pp_matches->class_nb_points . ' pts';

                        pp_iphone_matches($pp_matches->label, 'classj.php?id=' . $pp_matches->id_matches, '/template/default/' . $image, $description);
                        $line++;
                    }
                }
            } else {
                pp_iphone_matches($pp_matches->label, 'classj.php?id=' . $pp_matches->id_matches, '/template/default/' . $image, $description);
                $line++;
            }
        }
    }
}
?>
</ul><?php

echo pp_iphone_footer();
?>