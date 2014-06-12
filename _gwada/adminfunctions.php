<?php
/**
 * Project: PRONOPLUS
 * Description: Fonctions pour l'admin
 * Author: Richard HANNA <supertanuki@gmail.com>
 * Date: 2008-07-07
 * Version: 1.0
 */

function authentificate()
{
    if ($_SESSION[admin]) {
        $admin_user = getAdmin($_SESSION[admin]);
        if ($admin_user != false) return $admin_user;
    }

    header("Location: login.php");
    exit;
}

function getAdmin($strAdmin)
{
    global $db;
    $SQL = "SELECT `id_admin_user`, `is_super_admin`, `lastname`, `firstname` FROM `pp_admin_user`
			WHERE MD5(CONCAT(`login`, 'a', `pwd`))='" . $db->escapeSimple($strAdmin) . "'";
    $result = $db->query($SQL);
    //echo "<li>$SQL";
    if (DB::isError($result)) {
        die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    } else {
        if ($result->numRows()) {
            return $result->fetchRow();
        }
    }
    return false;
}


function adminheader($title = "Admin", $body = "")
{
    $content = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
			<html xmlns=\"http://www.w3.org/1999/xhtml\">
			<head>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
			<title>" . $title . "</title>
			<link href=\"adminstyles.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />
			<script src=\"../lib/scriptaculous-js-1.8.1/prototype.js\"></script>
			<script src=\"../lib/scriptaculous-js-1.8.1/scriptaculous.js\"></script>
			</head>	
			<body" . $body . ">";

    if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
        $content .= '<div style="padding:4px; background:#555; color:#fff; margin-bottom: 10px">Version développement</div>';
    }

    return $content;
}

function adminfooter()
{
    return "</body></html>";
}


function AdminName($admin_user)
{
    return "<fieldset class=\"adminmenu\">
					<legend>Bienvenue " . formatDbData($admin_user->firstname . " " . $admin_user->lastname) . " | <a href=\"logout.php\">Se déconnecter</a></legend>
					<p>Grilles &gt; <a href=\"matches_add.php\">Ajouter</a> | <a href=\"matches_list.php\">Modifier / Calculer</a><br />
					Matchs &gt; <a href=\"info_matches.php\">Gérer</a><br />
					Coupe &gt; <a href=\"cup.php\">Gérer</a><br />
					Clubs / équipes &gt; <a href=\"team.php\">Gérer</a><br />
					Newsletter &gt; <a href=\"newsletter_send.php\">Gérer</a><br />
					Archives &gt; <a href=\"archives.php\">Gérer</a></p>
					</fieldset><br />";
}

function calcul_class($id_matches, $type_calcul)
{
    global $db;
    set_time_limit(0);

    if ($type_calcul == 'provisoire') {
        $table_class_user = 'pp_class_user_temp';
        $table_match_user = 'pp_match_user_temp';

    } else if ($type_calcul == 'classement') {
        $table_class_user = 'pp_class_user';
        $table_match_user = 'pp_match_user';

    } else return false;

    // on récupère les matchs
    $matches = array();
    $SQL = "SELECT `id_match`, `score` FROM `pp_match`
			WHERE `id_matches`='" . $id_matches . "'";
    $result = $db->query($SQL);
    //echo "<li>$SQL";
    if (DB::isError($result)) {
        die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    } else {
        while ($pp_match = $result->fetchRow()) {
            if (strlen($pp_match->score) != 3) {
                $matches[$pp_match->id_match]['type'] = 0;

            } else {

                $matches[$pp_match->id_match]['score'] = $pp_match->score;

                $score_h = substr($pp_match->score, 0, 1);
                $score_v = substr($pp_match->score, 2, 1);

                if ($score_h == 'R')
                    $type = 4;
                else if ($score_h > $score_v)
                    $type = 1;
                else if ($score_h < $score_v)
                    $type = 2;
                else if ($score_h == $score_v)
                    $type = 3;

                $matches[$pp_match->id_match]['type'] = $type;
            }
        }
    }

    // on récupère les pronostics
    $match_user = array();
    foreach ($matches as $id_match => $mm) {
        $SQL = "SELECT `id_user`, `score`, `pts`, `date_creation`
		        FROM `pp_match_user`
				WHERE `id_match`='" . $id_match . "'";
        $result = $db->query($SQL);
//		echo "<li>$SQL";
        if (DB::isError($result)) {
            die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

        } else {
            while ($pp_match_user = $result->fetchRow()) {
                $match_user[$pp_match_user->id_user][$id_match]['score'] = $pp_match_user->score;
                $match_user[$pp_match_user->id_user][$id_match]['pts'] = $pp_match_user->pts;
                $match_user[$pp_match_user->id_user][$id_match]['date'] = $pp_match_user->date_creation;
            }
        }
    }

//    echo '<pre>'; print_r($match_user); echo '</pre>';
//    exit;

    $class = array();
    $cup = false;

    if ($type_calcul == 'classement') {
        // on récupère les classements concernés
        $SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`last_id_matches`
				FROM `pp_matches_class` INNER JOIN `pp_class` ON `pp_class`.`id_class`=`pp_matches_class`.`id_class`
				WHERE `pp_matches_class`.`id_matches`='" . $id_matches . "'";
        $result = $db->query($SQL);
        //echo "<li>$SQL";
        if (DB::isError($result)) {
            die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

        } else {
            while ($pp_class = $result->fetchRow()) {
                $class[$pp_class->id_class] = $pp_class->last_id_matches;
            }
        }

        // ou est-ce une coupe ?
        $SQL = "SELECT `id_cup_matches`, `id_cup`, `number_tour` FROM `pp_cup_matches`
				WHERE `id_matches`='" . $id_matches . "'";
        $result = $db->query($SQL);
        if (DB::isError($result)) {
            die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

        } else {
            if ($pp_cup = $result->fetchRow()) {
                $cup = $pp_cup;
            }
        }
    }

    // si c'est une coupe => on affecte nb_points = -1 à tous les joueurs
    if ($cup) {
        $SQL = "UPDATE `pp_cup_match_opponents`
				SET `host_nb_points`=-1, `visitor_nb_points`=-1
				WHERE `id_cup_matches`='" . $cup->id_cup_matches . "'";
        $result = $db->query($SQL);
        if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
    }

    // on parcourt tous les joueurs
    foreach ($match_user as $id_user => $match_score) {
        $nb_points = 0;
        $nb_score_ok = 0;
        $nb_result_ok = 0;
        $date_last_pronostic = '0000-00-00 00:00:00';

        // on parcourt les matchs
        foreach ($match_score as $id_match => $score) {
            if ($score['date'] > $date_last_pronostic) $date_last_pronostic = $score['date'];

            if ($matches[$id_match]['type'] > 0) {
                // match reporté
                if ($matches[$id_match]['type'] == 4) {
                    $type_result = 4;
                    $pts_won = $score['pts'];
                } // score identique
                elseif ($matches[$id_match]['score'] == $score['score']) {
                    $type_result = 1;
                    $pts_won = 10 * $score['pts'];
                    $nb_score_ok++;

                    // autres résultats
                } else {

                    $score_h = substr($score['score'], 0, 1);
                    $score_v = substr($score['score'], 2, 1);

                    if ($score_h > $score_v)
                        $type = 1;
                    else if ($score_h < $score_v)
                        $type = 2;
                    else if ($score_h == $score_v)
                        $type = 3;

                    // bon résultat
                    if ($matches[$id_match]['type'] == $type) {
                        $type_result = 2;
                        $pts_won = 3 * $score['pts'];
                        $nb_result_ok++;

                        // mauvais résultat
                    } else {
                        $type_result = 3;
                        $pts_won = 0;
                    }
                }

            } else {
                $type_result = 0;
                $pts_won = 0;
            }

            $SQL = "UPDATE `" . $table_match_user . "` SET `type_result`=" . $type_result . ", `pts_won`=" . $pts_won . ",
						`score`='" . $score['score'] . "', `pts`='" . $score['pts'] . "', `date_update`=NOW()
					WHERE `id_user`='" . $id_user . "' AND `id_match`='" . $id_match . "'";
            $result = $db->query($SQL);
            //echo "<li>$SQL";
            if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

            if ($type_calcul == 'provisoire') {
                if (!$db->affectedRows()) {
                    $SQL = "INSERT INTO `" . $table_match_user . "`(`id_user`, `id_match`, `score`, `pts`, `type_result`, `pts_won`, `date_creation`, `date_update`)
							VALUES('" . $id_user . "', '" . $id_match . "', '" . $score['score'] . "', '" . $score['pts'] . "', '" . $type_result . "', '" . $pts_won . "', NOW(), NOW())";
                    $result = $db->query($SQL);
                    //echo "<li>$SQL";
                    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                }
            }

            $nb_points += $pts_won;
        }

        $nb_matches = count($match_score);

        // on ajoute les scores au classement journée
        $SQL = "UPDATE `" . $table_class_user . "`
				SET `nb_score_ok`=" . $nb_score_ok . ", `nb_result_ok`=" . $nb_result_ok . ", `nb_matches`=" . $nb_matches . ", `nb_points`=" . $nb_points . ", `date_last_pronostic`='" . $date_last_pronostic . "', `date_calcul`=NOW()
				WHERE `id_user`='" . $id_user . "' AND `id_matches`='" . $id_matches . "' AND `id_class`=1"; // classement journée
        $result = $db->query($SQL);
        //echo "<li>$SQL";
        if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
        if (!$db->affectedRows()) {
            $SQL = "INSERT INTO `" . $table_class_user . "`(`id_user`, `id_matches`, `id_class`, `nb_score_ok`, `nb_result_ok`, `nb_matches`, `nb_points`, `date_last_pronostic`, `date_calcul`)
					VALUES(
					'" . $id_user . "',
					'" . $id_matches . "',
					1,
					" . $nb_score_ok . ",
					" . $nb_result_ok . ",
					" . $nb_matches . ",
					" . $nb_points . ",
					'" . $date_last_pronostic . "',
					NOW())";
            $result = $db->query($SQL);
            if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
        }

        if ($type_calcul == 'classement') {
            // on ajoute les scores aux autres classements
            if (count($class)) foreach ($class as $id_class => $last_id_matches) {
                $pp_class_user = NULL;

                // on récupère le classement de l'utilisateur
                $SQL = "SELECT `nb_score_ok`, `nb_result_ok`, `nb_matches`, `nb_points`, `class`, `date_last_pronostic`
						FROM `pp_class_user`
						WHERE `id_class`='" . $id_class . "' AND `id_matches`!='" . $id_matches . "' AND `id_user`='" . $id_user . "'
						ORDER BY `date_calcul` DESC
						LIMIT 1";
                $result_class_user = $db->query($SQL);
                //echo "<li>$SQL";
                if (DB::isError($result)) {
                    die ("<li>ERROR : " . $result_class_user->getMessage() . "<li>$SQL");

                } else {
                    $pp_class_user = $result_class_user->fetchRow();
                    $aclass[$id_class][$id_user] = $pp_class_user->class;
                }

                $SQL = "UPDATE `pp_class_user`
						SET `nb_score_ok`=" . ($nb_score_ok + $pp_class_user->nb_score_ok) . ",
							`nb_result_ok`=" . ($nb_result_ok + $pp_class_user->nb_result_ok) . ",
							`nb_matches`=" . ($nb_matches + $pp_class_user->nb_matches) . ",
							`nb_points`=" . ($nb_points + $pp_class_user->nb_points) . ",
							`date_last_pronostic`='" . ($date_last_pronostic > $pp_class_user->date_last_pronostic ? $date_last_pronostic : $pp_class_user->date_last_pronostic) . "',
							`date_calcul`=NOW()
						WHERE `id_user`='" . $id_user . "' AND `id_matches`='" . $id_matches . "' AND `id_class`='" . $id_class . "'";
                $result = $db->query($SQL);
                //echo "<li>$SQL";
                if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                if (!$db->affectedRows()) {
                    $SQL = "INSERT INTO `pp_class_user`(`id_user`, `id_matches`, `id_class`, `nb_score_ok`, `nb_result_ok`, `nb_matches`, `nb_points`, `date_last_pronostic`, `date_calcul`)
							VALUES('" . $id_user . "', '" . $id_matches . "', '" . $id_class . "', " . ($nb_score_ok + $pp_class_user->nb_score_ok) . ", " . ($nb_result_ok + $pp_class_user->nb_result_ok) . ", " . ($nb_matches + $pp_class_user->nb_matches) . ", " . ($nb_points + $pp_class_user->nb_points) . ", '" . ($date_last_pronostic) . "', NOW())";
                    $result = $db->query($SQL);
                    //echo "<li>$SQL";
                    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                }
            }

            // si c'est une coupe => on affecte le résultat au bon joueur
            if ($cup) {
                $SQL = "UPDATE `pp_cup_match_opponents`
						SET `host_nb_score_ok`=" . $nb_score_ok . ",
							`host_nb_result_ok`=" . $nb_result_ok . ",
							`host_nb_points`=" . $nb_points . "
						WHERE `id_user_host`='" . $id_user . "' AND `id_cup_matches`='" . $cup->id_cup_matches . "'";
                $result = $db->query($SQL);
                if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                if (!$db->affectedRows()) {
                    $SQL = "UPDATE `pp_cup_match_opponents`
							SET `visitor_score_ok`=" . $nb_score_ok . ",
								`visitor_nb_result_ok`=" . $nb_result_ok . ",
								`visitor_nb_points`=" . $nb_points . "
							WHERE `id_user_visitor`='" . $id_user . "' AND `id_cup_matches`='" . $cup->id_cup_matches . "'";
                    $result = $db->query($SQL);
                    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                }
            }
        }
    }

    // classement journée => affecter le classement
    $id_class = 1;
    $SQL = "SELECT `id_user`
			FROM `" . $table_class_user . "`
			WHERE `id_class`='" . $id_class . "' AND `id_matches`='" . $id_matches . "'
			ORDER BY `nb_points` DESC, `nb_score_ok` DESC, `nb_result_ok` DESC, `date_last_pronostic` ASC";
    $result_class_user = $db->query($SQL);
    //echo "<li>$SQL";
    if (DB::isError($result)) {
        die ("<li>ERROR : " . $result_class_user->getMessage() . "<li>$SQL");

    } else {
        $rang = 1;
        while ($pp_class_user = $result_class_user->fetchRow()) {
            $SQL = "UPDATE `" . $table_class_user . "`
					SET `class`=" . $rang . "
					WHERE `id_user`='" . $pp_class_user->id_user . "'
					AND `id_matches`='" . $id_matches . "' AND `id_class`='" . $id_class . "'";
            $result = $db->query($SQL);
            if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
            $rang++;
        }
    }

    if ($type_calcul == 'classement') {
        // calculer les rangs des classements
        if (count($class)) foreach ($class as $id_class => $type) {
            // classements => insérer les joueurs qui n'ont pas joué
            $SQL = "SELECT `p1`.`id_user`,
					MAX(`p1`.`nb_score_ok`) AS `nb_score_ok`,
					MAX(`p1`.`nb_result_ok`) AS `nb_result_ok`,
					MAX(`p1`.`nb_matches`) AS `nb_matches`,
					MAX(`p1`.`nb_points`) AS `nb_points`,
					MAX(`p1`.`date_last_pronostic`) AS `date_last_pronostic`
					FROM `pp_class_user` AS `p1`
					LEFT JOIN `pp_class_user` AS `p2`
					ON `p1`.`id_user`=`p2`.`id_user`
						AND `p2`.`id_class`='" . $id_class . "'
						AND `p2`.`id_matches`='" . $id_matches . "'
					WHERE `p1`.`id_class`='" . $id_class . "' AND `p1`.`id_matches`!='" . $id_matches . "'
					AND `p2`.`id_user` IS NULL
					GROUP BY `p1`.`id_user`";
            $result_class_user = $db->query($SQL);
            //echo "<li>$SQL";
            if (DB::isError($result)) {
                die ("<li>ERROR : " . $result_class_user->getMessage() . "<li>$SQL");

            } else {
                while ($pp_class_user = $result_class_user->fetchRow()) {
                    $SQL = "INSERT INTO `pp_class_user`(`id_user`, `id_matches`, `id_class`, `nb_score_ok`, `nb_result_ok`, `nb_matches`, `nb_points`, `date_last_pronostic`, `date_calcul`)
							VALUES('" . $pp_class_user->id_user . "', '" . $id_matches . "', '" . $id_class . "', " . $pp_class_user->nb_score_ok . ", " . $pp_class_user->nb_result_ok . ", " . $pp_class_user->nb_matches . ", " . $pp_class_user->nb_points . ", '" . $pp_class_user->date_last_pronostic . "', NOW())";
                    $result = $db->query($SQL);
                    //echo "<li>$SQL";
                    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                }
            }

            // classements => affecter le classement
            $SQL = "SELECT `id_user`
					FROM `pp_class_user`
					WHERE `id_class`='" . $id_class . "' AND `id_matches`='" . $id_matches . "'
					ORDER BY `nb_points` DESC, `nb_score_ok` DESC, `nb_result_ok` DESC, `date_last_pronostic` ASC";
            $result_class_user = $db->query($SQL);
            //echo "<li>$SQL";
            if (DB::isError($result)) {
                die ("<li>ERROR : " . $result_class_user->getMessage() . "<li>$SQL");

            } else {
                $rang = 1;
                while ($pp_class_user = $result_class_user->fetchRow()) {
                    $evolution = $aclass[$id_class][$pp_class_user->id_user] ? $aclass[$id_class][$pp_class_user->id_user] * 1 - $rang : 0;
                    $SQL = "UPDATE `pp_class_user`
							SET `class`=" . $rang . ", `evolution`=" . $evolution . "
							WHERE `id_user`='" . $pp_class_user->id_user . "'
							AND `id_matches`='" . $id_matches . "' AND `id_class`='" . $id_class . "'";
                    $result = $db->query($SQL);
                    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                    $rang++;
                }
            }

            // mise à jour classement
            $SQL = "UPDATE `pp_class`
					SET `last_id_matches`=" . $id_matches . ", `date_update`=NOW()
					WHERE `id_class`='" . $id_class . "'";
            $result = $db->query($SQL);
            if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

            // supprimer classement provisoire
            $SQL = "DELETE FROM `pp_class_user_temp`
					WHERE `id_matches`=" . $id_matches . "";
            $result = $db->query($SQL);
            if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

            // supprimer résultats match provisoires
            foreach ($matches as $id_match => $match) {
                $SQL = "DELETE FROM `pp_match_user_temp`
						WHERE `id_match`=" . $id_match . "";
                $result = $db->query($SQL);
                if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
            }
        }

        // si c'est une coupe
        if ($cup) {
            //  => on détermine les gagnants
            $SQL = "UPDATE `pp_cup_match_opponents`
					SET `id_user_won` =
						IF(`host_nb_points` > `visitor_nb_points`, `id_user_host`,
							IF(`host_nb_points` < `visitor_nb_points`, `id_user_visitor`,
								IF(`host_nb_score_ok` > `visitor_score_ok`, `id_user_host`,
									IF(`host_nb_score_ok` < `visitor_score_ok`, `id_user_visitor`,
										IF(`host_nb_result_ok` > `visitor_nb_result_ok`, `id_user_host`,
											IF(`host_nb_result_ok` < `visitor_nb_result_ok`, `id_user_visitor`,
												IF(`host_class` < `visitor_class`, `id_user_host`, `id_user_visitor`)
						))))))
					WHERE `id_cup_matches`='" . $cup->id_cup_matches . "'";
            $result = $db->query($SQL);
            //echo "<li>$SQL";
            if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");


            // créé t-on les matchs du tour suivant ?
            if ($cup->number_tour != 4) {
                $new_number_tour = $cup->number_tour + 1;

                // on créé le nouveau pp_cup_matches
                $SQL = "INSERT INTO `pp_cup_matches`(`id_cup`, `number_tour`)
						VALUES('" . $cup->id_cup . "', '" . $new_number_tour . "')";
                $result = $db->query($SQL);
                if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                if ($id_cup_matches = $db->insertId()) {
                    // on récupère les gagnants
                    $match_cup = array();
                    $SQL = "SELECT `id_user_host`, `cup_sub`, `num_match`, `id_user_won`, `host_class`,
								IF(`id_user_won`=`id_user_host`, `host_class`, `visitor_class`) AS `user_class`
							FROM `pp_cup_match_opponents`
							WHERE `id_cup_matches`='" . $cup->id_cup_matches . "'
							ORDER BY `cup_sub`, `num_match`, `host_class`";
                    $result_cup_user = $db->query($SQL);
                    //echo "<li>$SQL";
                    if (DB::isError($result_cup_user)) {
                        die ("<li>ERROR : " . $result_cup_user->getMessage() . "<li>$SQL");

                    } else {
                        while ($cup_user = $result_cup_user->fetchRow()) {
                            $match_cup[$cup_user->cup_sub][] = $cup_user;
                        }
                    }

                    foreach ($match_cup as $cup_sub => $value) {
                        $opposition = array();

                        if ($new_number_tour == 2) {
                            $tableau = array(1, 8, 5, 4, 3, 6, 7, 2);
                            for ($i = 0; $i < 8; $i = $i + 2) {
                                //echo "<li>$i";
                                $m_opp = array();
                                $m_opp[id_user_host] = $match_cup[$cup_sub][$tableau[$i] - 1]->id_user_won;
                                $m_opp[host_class] = $match_cup[$cup_sub][$tableau[$i] - 1]->user_class;
                                $m_opp[id_user_visitor] = $match_cup[$cup_sub][$tableau[$i + 1] - 1]->id_user_won;
                                $m_opp[visitor_class] = $match_cup[$cup_sub][$tableau[$i + 1] - 1]->user_class;

                                //echo "<li>$cup_sub<pre>";  print_r($m_opp); echo "</pre>";

                                $opposition[] = $m_opp;
                            }

                        } else {
                            for ($i = 0; $i < count($match_cup[$cup_sub]); $i = $i + 2) {
                                $m_opp = array();
                                $m_opp[id_user_host] = $match_cup[$cup_sub][$i]->id_user_won;
                                $m_opp[host_class] = $match_cup[$cup_sub][$i]->user_class;
                                $m_opp[id_user_visitor] = $match_cup[$cup_sub][$i + 1]->id_user_won;
                                $m_opp[visitor_class] = $match_cup[$cup_sub][$i + 1]->user_class;

                                $opposition[] = $m_opp;
                            }

                        }

                        $num_match = 1;


                        foreach ($opposition as $m_opp) {
                            // créer les oppositions
                            $SQL = "INSERT INTO `pp_cup_match_opponents`(`id_cup`, `number_tour`, `cup_sub`, `id_cup_matches`, `num_match`,
											`id_user_host`, `host_class`, `id_user_visitor`, `visitor_class`)
									VALUES('" . $cup->id_cup . "', '" . $new_number_tour . "', '" . $cup_sub . "', '" . $id_cup_matches . "', '" . $num_match . "',
									'" . $m_opp[id_user_host] . "', '" . $m_opp[host_class] . "', '" . $m_opp[id_user_visitor] . "', '" . $m_opp[visitor_class] . "')";
                            $result = $db->query($SQL);
                            if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                            $num_match++;
                        }

                    }
                }
            }
        }


        $SQL = "UPDATE `pp_matches` SET `is_calcul`='1', `date_calcul`=NOW()
				WHERE `id_matches`='" . $id_matches . "'";
        $result = $db->query($SQL);
        if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    }
}


function synchroniser_matches($id_matches)
{
    global $db;
    set_time_limit(0);

    $SQL = "SELECT `id_match`, `id_info_match` FROM `pp_match` WHERE `id_matches`='" . $id_matches . "'";
    $result = $db->query($SQL);
    if (DB::isError($result)) {
        die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

    } else {
        $date_first_match = '0000-00-00 00:00:00';
        $date_last_match = '0000-00-00 00:00:00';

        while ($pp_match = $result->fetchRow()) {
            $SQL = "SELECT `id_team_host`, `id_team_visitor`, `date_match`, `score`, `penalties`
					FROM `pp_info_match` WHERE `id_info_match`='" . $pp_match->id_info_match . "'";
            $result_info_match = $db->query($SQL);
            if (DB::isError($result_info_match)) die ("<li>ERROR : " . $result_info_match->getMessage() . "<li>$SQL");
            else {
                if ($pp_info_match = $result_info_match->fetchRow()) {
                    $date_first_match = $pp_info_match->date_match < $date_first_match || $date_first_match == '0000-00-00 00:00:00' ? $pp_info_match->date_match : $date_first_match;
                    $date_last_match = $pp_info_match->date_match > $date_last_match ? $pp_info_match->date_match : $date_last_match;

                    $SQL = "UPDATE `pp_match` SET
								`id_team_host`=" . $pp_info_match->id_team_host . ",
								`id_team_visitor`=" . $pp_info_match->id_team_visitor . ",
								`date_match`='" . $pp_info_match->date_match . "',
								".($pp_info_match->score ? "`score`='" . $pp_info_match->score . "'," : '')."
								`penalties`='" . $pp_info_match->penalties . "'
							WHERE `id_match`='" . $pp_match->id_match . "'";
                    $result_update = $db->query($SQL);
                    if (DB::isError($result_update)) die ("<li>ERROR : " . $result_update->getMessage() . "<li>$SQL");
                }
            }
        }

        $SQL = "UPDATE `pp_matches` SET date_first_match='" . $date_first_match . "', date_last_match='" . $date_last_match . "', date_update=NOW() WHERE `id_matches`='" . $id_matches . "'";
        $result = $db->query($SQL);
        if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
    }
}