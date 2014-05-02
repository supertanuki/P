<?php

/**
 * Project: PRONOPLUS
 * Description: Mise à jour classements des championnats
 * Author: Richard HANNA <supertanuki@gmail.com>
 * Date: 2010-10-13
 * Version: 1.0
 */

chdir(dirname(__FILE__));
chdir('../');
$_SERVER['DOCUMENT_ROOT'] = getcwd();

require_once($_SERVER['DOCUMENT_ROOT'] . '/init.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/mainfunctions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/contentfunctions.php');

set_time_limit(0);

$debug = true;
$email = EMAIL_MASTER;
$saison_en_cours = getConfig('saison_en_cours');

// juste classements
//maj_classements();
//exit;


// DEBUT TRAITEMENT

// CDM 2014
echo "<li><b>CDM 2014</b>";
$idleague = 7;
for ($i = 1; $i <= 8; $i++) {
    $idleq = $i + 5867;
    $url = str_replace('%ID%', $idleq, URL_GROUPE);
    echo "<li>$i";
    extraction_info($idleague, $numero_journee = $i, $url, $debug);
}
/*


// LIGUE 1
echo "<li><b>LIGUE 1</b>";
$idleague = 1;
for($i=1; $i<=38; $i++)
{
	$idleq = $i+45018;
	$url = str_replace('%ID%', $idleq, URL_RESULTAT);
	echo "<li>$i";
	extraction_info($idleague, $numero_journee = $i, $url, $debug);
}


// LIGUE 2
echo "<li><b>LIGUE 2</b>";
$idleague = 3;
for($i=1; $i<=38; $i++)
{
	$idleq = $i+45056;
    $url = str_replace('%ID%', $idleq, URL_RESULTAT);
	echo "<li>$i";
	extraction_info($idleague, $numero_journee = $i, $url);
}

// Angleterre
echo "<li><b>Angleterre</b>";
$idleague = 2;
for($i=1; $i<=38; $i++)
{
	$idleq = $i+45208;
    $url = str_replace('%ID%', $idleq, URL_RESULTAT);
	echo "<li>$i";
	extraction_info($idleague, $numero_journee = $i, $url);
}

// Allemagne
echo "<li><b>Allemagne</b>";
$idleague = 8;
for($i=1; $i<=34; $i++)
{
	$idleq = $i+45389;
    $url = str_replace('%ID%', $idleq, URL_RESULTAT);
	echo "<li>$i";
	extraction_info($idleague, $numero_journee = $i, $url);
}

// Italie
//http://www.lequipe.fr/Football/FootballResultat45867.html
echo "<li><b>Italie</b>";
$idleague = 10;
for($i=1; $i<=38; $i++)
{
    $idleq = $i+45866;
    $url = str_replace('%ID%', $idleq, URL_RESULTAT);
    echo "<li>$i";
    extraction_info($idleague, $numero_journee = $i, $url);
}

// Espagne
echo "<li><b>Espagne</b>";
$idleague = 6;
for($i=1; $i<=38; $i++)
{
	$idleq = $i+45610;
    $url = str_replace('%ID%', $idleq, URL_RESULTAT);
	echo "<li>$i";
	extraction_info($idleague, $numero_journee = $i, $url);
}

// Coupe du monde 2014
echo "<li><b>Coupe du monde 2014</b>";
$idleague = 7;
for($i=1; $i<=9; $i++)
{
	$idleq = $i+4715;
    $url = str_replace('%ID%', $idleq, URL_GROUPE);
	echo "<li>$i";
	extraction_info($idleague, $numero_journee = $i, $url);
}

// Coupe de la Ligue
echo "<li><b>Coupe de la Ligue</b>";
$idleague = 12;
for($i=1; $i<=5; $i++)
{
    $idleq = $i+45523;
    $url = str_replace('%ID%', $idleq, URL_RESULTAT);
    echo "<li>$i";
    extraction_info($idleague, $numero_journee = $i, $url);
}

// Ligue des Champions
echo "<li><b>Ligue des Champions</b>";
$idleague = 4;
for($i=1; $i<=8; $i++)
{
	$idleq = $i+5573;
    $url = str_replace('%ID%', $idleq, URL_GROUPE);
	echo "<li>$i";
	extraction_info($idleague, $numero_journee = $i, $url);
}



// Ligue Europa
// echo "<li><b>Ligue Europa</b>";
// $idleague = 5;
// for($i=1; $i<=12; $i++)
// {
//	 $idleq = $i+5191;
//	 $url = "http://www.lequipe.fr/Football/FootballResultatGroupe".$idleq.".html";
//	 echo "<li>$i";
//	 extraction_info($idleague, $numero_journee = $i, $url);
// }




echo "<li><b>FIN Extract !!!!</b>";

maj_classements();

*/


// MISE A JOUR CLASSEMENTS
function maj_classements()
{
    global $db, $saison_en_cours;

    // recherche championnats concernés
    $SQL = "SELECT id_league FROM pp_league WHERE afficher_classement='1'";
    $result_league = $db->query($SQL);
    //echo "<li>$SQL";
    if (DB::isError($result_league)) {
        die ("<li>ERROR : " . $result_league->getMessage());

    } else {
        while ($pp_league = $result_league->fetchRow()) {

            // Equipes
            $pp_team = array();
            $SQL = "SELECT pp_team.id_team, pp_team.nb_points_sanction
                    FROM pp_team
                    WHERE id_league='" . $pp_league->id_league . "'";
            $result_team = $db->query($SQL);
            //echo "<li>$SQL";
            if (DB::isError($result_team)) {
                die ("<li>ERROR : " . $result_team->getMessage() . "<li>$SQL");

            } else {
                if ($result_team->numRows()) {
                    while ($t = $result_team->fetchRow()) {
                        $pp_team[$t->id_team] = $t;
                    }
                }
            }


            $arr_team = array();
            $SQL = "SELECT pp_info_match.id_team_host, pp_info_match.id_team_visitor, pp_info_match.score
                    FROM pp_info_match
                        INNER JOIN pp_info_matches
                        ON pp_info_matches.id_league='" . $pp_league->id_league . "'
                            AND pp_info_match.id_info_matches = pp_info_matches.id_info_matches
                            AND pp_info_matches.day_number > 0";
            $result_info_matches = $db->query($SQL);
            //echo "<li>$SQL";
            if (DB::isError($result_info_matches)) {
                die ("<li>ERROR : " . $result_info_matches->getMessage());

            } else {
                while ($pp_info_match = $result_info_matches->fetchRow()) {
                    if (strlen($pp_info_match->score) >= 3) {
                        $buts = explode('-', $pp_info_match->score);
                        $buts_host = $buts[0];
                        $buts_visitor = $buts[1];

                        $arr_team[$pp_info_match->id_team_host]['nb_matches']++;
                        $arr_team[$pp_info_match->id_team_host]['nb_goals_for'] += $buts_host;
                        $arr_team[$pp_info_match->id_team_host]['nb_goals_against'] += $buts_visitor;

                        $arr_team[$pp_info_match->id_team_visitor]['nb_matches']++;
                        $arr_team[$pp_info_match->id_team_visitor]['nb_goals_for'] += $buts_visitor;
                        $arr_team[$pp_info_match->id_team_visitor]['nb_goals_against'] += $buts_host;

                        // victoire domicile
                        if ($buts_host > $buts_visitor) {
                            $arr_team[$pp_info_match->id_team_host]['nb_points'] += 3;
                            $arr_team[$pp_info_match->id_team_host]['nb_won']++;
                            $arr_team[$pp_info_match->id_team_visitor]['nb_lost']++;

                            // match nul
                        } elseif ($buts_host == $buts_visitor) {
                            $arr_team[$pp_info_match->id_team_host]['nb_points'] += 1;
                            $arr_team[$pp_info_match->id_team_visitor]['nb_points'] += 1;
                            $arr_team[$pp_info_match->id_team_host]['nb_tie']++;
                            $arr_team[$pp_info_match->id_team_visitor]['nb_tie']++;

                            // victoire extérieure
                        } elseif ($buts_host < $buts_visitor) {
                            $arr_team[$pp_info_match->id_team_visitor]['nb_points'] += 3;
                            $arr_team[$pp_info_match->id_team_visitor]['nb_won']++;
                            $arr_team[$pp_info_match->id_team_host]['nb_lost']++;
                        }
                    }
                }
            }

            // update / insertion classement
            if (count($arr_team)) {
                // delete
                $SQL = "DELETE FROM pp_team_class
                        WHERE id_league = '" . $pp_league->id_league . "'
                            AND saison = '" . $saison_en_cours . "'";
                $result = $db->query($SQL);
                //echo "<li>$SQL";
                if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage());

                foreach ($arr_team as $id_team => $team) {
                    // insert
                    $SQL = "INSERT INTO pp_team_class(id_team, id_league, saison, nb_points, nb_matches, nb_won, nb_tie, nb_lost, nb_goals_for, nb_goals_against, date_update)
                            VALUES('" . $id_team . "', '" . $pp_league->id_league . "', '" . $saison_en_cours . "', '" . ($team['nb_points'] + $pp_team[$id_team]->nb_points_sanction) . "', '" . $team['nb_matches'] . "', '" . $team['nb_won'] . "', '" . $team['nb_tie'] . "', '" . $team['nb_lost'] . "', '" . $team['nb_goals_for'] . "', '" . $team['nb_goals_against'] . "', NOW())";
                    $result = $db->query($SQL);
                    //echo "<li>$SQL";
                    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage());
                }
            }
        }
    }

    echo "<li><b>FIN MAJ !!!!</b>";
}


function extraction_info($idleague, $numero_journee, $url, $debug = false)
{
    global $db, $txtlang, $email;

    //$jours = array('lun', 'mar', 'mer', 'jeu', 'ven', 'sam', 'dim');

    if (!$content = file_get_contents($url)) {
        echo("<li>ERROR : impossible d'ouvrir $url");
        return;
    }

    if ($debug) {
        echo '<li>' . $url . '</li>';
    }

    $info_matches = array();
    $info_match = array();

    // isolation contenu
    $tabcontent = explode('<div class="content resultat">', $content, 2);
    if (count($tabcontent) == 2) {
        $tabcontent = explode('</section>', $tabcontent[1], 2);
        $mycontent = $tabcontent[0];

        for ($loop = 1; $loop <= 100; $loop++) {
            $line = explode('<div class="ligne', $mycontent, 2);

            if (count($line) != 2) break;

            if (count($line) == 2) {
                $line = $line[1];

                $this_match = array();

                // Y a t-il une date ?

                //echo "<li>line = ".htmlspecialchars($line);

                // <h2 class="color date-event">samedi 5 novembre 2011  </h2>
                $date_event = explode('<h2', $line, 2);
                $match_avant = explode('<div class="equipeExt">', $date_event[0]);
                // echo "<li>match_avant = ".count($match_avant);
                if (count($match_avant) <= 2) {
                    $date_event = explode('</h2>', $date_event[1], 2);
                    // echo "<li>date_event= ".htmlspecialchars($date_event[0])."</li>";
                    if (count($date_event) == 2 && preg_match('|>.* ([0-9]+)e?r? (.*) ([0-9]{4})|i', $date_event[0], $matches)) {
                        // echo "<li>Titre // ".utf8_decode($matches[2])."<pre>"; print_r($matches); echo "</pre>";
                        for ($imois = 0; $imois <= 11; $imois++) {
                            if (strtolower($txtlang['MONTH_' . $imois]) == strtolower(trim($matches[2]))) {
                                $matches[2] = $imois * 1 + 1;
                                break;
                            }
                        }

                        $this_info_match['date_match'] = (1 * $matches[3]) . "-" . (1 * $matches[2]) . "-" . (1 * $matches[1]);
                        // echo "<li><h2>".$this_info_match['date_match']."</h2></li>";
                    }
                }


                //echo "<li>line = ".htmlspecialchars($line);

                $match = explode('<div class="ligne', $line, 2);
                if (count($match) == 2) {
                    $match = explode('<div class="pariez">', $match[1], 2);
                    $match = $match[0];

                    // echo "<li>match = ".htmlspecialchars($match);

                    // Y a t-il une heure ?
                    // <div class="score"><a class="disabled">19h00</a></div>
                    $score = explode('<div class="score">', $match, 2);
                    if (count($score) == 2) {
                        $score = explode('</div>', $score[1], 2);
                        $score = strip_tags($score[0]);
                        // echo "<li>score = ".htmlspecialchars($score);
                        if (preg_match('|([0-9]{2})h([0-9]{2})|i', $score, $matches)) {
                            $this_info_match['heure_match'] = $matches[1] . ':' . $matches[2] . ':00';
                            // echo "<li>$numero_journee / heure_match = ".$this_info_match['heure_match']."</li>";
                        }

                        // ou est-ce un score ?
                        if (preg_match('|([0-9]+) ?- ?([0-9]+)|i', $score, $matches)) {
                            $this_match['score'] = $matches[1] . '-' . $matches[2];
                            // echo "<li>$numero_journee / score = ".$this_match['score']."</li>";
                        }
                    }

                    // Y a t-il une autre heure ?
                    $heure = explode('<div class="heure">', $match, 2);
                    if (count($heure) != 2) $heure = explode('<div class="heure ">', $match, 2);

                    if (count($heure) == 2) {
                        $heure = explode('</div>', $heure[1], 2);
                        $heure = strip_tags($heure[0]);
                        if (preg_match('|([0-9]{2})h([0-9]{2})|i', $heure, $matches)) {
                            $this_info_match['heure_match'] = $matches[1] . ':' . $matches[2] . ':00';
                        }
                    }


                    // Y a t-il une autre date ?
                    // <div class="heure"><strong>sam 21/01/2012&agrave; Bata</strong></div>
                    $heure = explode('<div class="heure">', $match, 2);
                    if (count($heure) != 2) $heure = explode('<div class="heure ">', $match, 2);

                    if (count($heure) == 2) {
                        $heure = explode('</div>', $heure[1], 2);
                        $heure = strip_tags($heure[0]);
                        //echo "<li>date = ".htmlspecialchars($heure);
                        if (preg_match('|([0-9]{2})\/([0-9]{2})\/([0-9]{4})|i', $heure, $matches)) {
                            $this_info_match['date_match'] = (1 * $matches[3]) . "-" . (1 * $matches[2]) . "-" . (1 * $matches[1]);
                            // echo "<li>$numero_journee / date_match = ".$this_info_match['date_match']."</li>";
                        }
                    }


                    // Equipe domicile ?
                    //<div class="equipeDom"><img src="http://medias.lequipe.fr/logo-football-png/1123/20"><a  href="/Football/FootballFicheClub1123.html" class="gagne">Palerme    &nbsp;<span class="color">(8)</span></a></div>
                    $equipeDom = explode('<div class="equipeDom">', $match, 2);
                    if (count($equipeDom) == 2) {
                        $equipeDom = explode('</div>', $equipeDom[1], 2);
                        $equipeDom = explode('<span class="color">', $equipeDom[0], 2);
                        //if ($debug) echo "<li>" . $equipeDom[0];
                        $equipeDom = trim(strip_tags(str_replace(array('&nbsp;', '"'), array('', ''), $equipeDom[0])));
                        //if ($debug) echo "<li>equipeDom =  #" . htmlspecialchars($equipeDom) . '#';

                        if (strlen($equipeDom) < 2) {
                            $msg = "<li><strong>Problème Equipe domicile</strong> : $equipeDom / $url";
                            if (!$debug) mail($email, 'Alerte Prono+', strip_tags($msg));
                            echo $msg;

                        } else {
                            $SQL = "SELECT `id_team` FROM `pp_team` WHERE `xlabels` LIKE '%" . trim($db->escapeSimple($equipeDom)) . "%'";
                            $result = $db->query($SQL);
                            if (DB::isError($result)) {
                                die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

                            } else {
                                if ($pp_team = $result->fetchRow()) {
                                    $this_match['id_team_host'] = $pp_team->id_team;
                                    $this_match['team_host'] = $equipeDom;
                                } else {
                                    $msg = "<li><strong>Equipe non trouvee</strong> : *" . $equipeDom . "*";
                                    if (!$debug) mail($email, 'Alerte Prono+', strip_tags($msg));
                                    echo $msg;
                                }
                            }
                        }
                    }

                    // Equipe Exterieur ?
                    $equipeExt = explode('<div class="equipeExt">', $match, 2);
                    if (count($equipeExt) == 2) {
                        $equipeExt = explode('</div>', $equipeExt[1], 2);
                        //if ($debug) {
                        //    echo "<li>1. ";
                        //    print_r($equipeExt);
                        //}

                        $equipeExt = explode('<span class="color">', $equipeExt[0], 2);
                        //if ($debug) echo "<li>2. " . $equipeExt[0] . " / " . strip_tags($equipeExt[0]);
                        //if ($debug) echo "<li>2.2. " . strip_tags(str_replace(array('&nbsp;', '"'), array('', ''), $equipeExt[0]));

                        $equipeExt = trim(strip_tags(str_replace(array('&nbsp;', '"'), array('', ''), $equipeExt[0])));
                        //if ($debug) echo "<li>3. $equipeExt";
                        //if ($debug) echo "<li>equipeExt =  #" . htmlspecialchars($equipeExt) . '#';

                        if (strlen($equipeExt) < 2) {
                            $msg = "<li><strong>Problème Equipe exterieur</strong> : $equipeExt / $url";
                            if (!$debug) mail($email, 'Alerte Prono+', strip_tags($msg));
                            echo $msg;

                        } else {
                            $SQL = "SELECT `id_team` FROM `pp_team` WHERE `xlabels` LIKE '%" . trim($db->escapeSimple($equipeExt)) . "%'";
                            $result = $db->query($SQL);
                            if (DB::isError($result)) {
                                die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

                            } else {
                                if ($pp_team = $result->fetchRow()) {
                                    $this_match['id_team_visitor'] = $pp_team->id_team;
                                    $this_match['team_visitor'] = $equipeExt;
                                } else {
                                    $msg = "<li><strong>Equipe non trouvee</strong> : *" . $equipeExt . "*";
                                    if (!$debug) mail($email, 'Alerte Prono+', strip_tags($msg));
                                    echo $msg;
                                }
                            }
                        }
                    }

                    if ($debug) {
                        echo '<li>this_info_match<pre>';
                        print_r($this_info_match);
                        echo '</pre>';
                    }

                    if (!$this_info_match['heure_match']) {
                        $msg = "<li><strong>heure non trouvee</strong> : $equipeDom - $equipeExt";
                        if (!$debug) mail($email, 'Alerte Prono+', strip_tags($msg));
                        echo $msg;

                    } else {
                        $this_match['date_match'] = $this_info_match['date_match'] . ' ' . $this_info_match['heure_match'];
                        $info_match[] = $this_match;

                        // echo "<li>this_match<pre>"; print_r($this_match); echo "</pre>";

                        if ($info_matches['date_first_match'] > $this_match['date_match'] || !$info_matches['date_first_match']) $info_matches['date_first_match'] = $this_match['date_match'];
                        if ($info_matches['date_last_match'] < $this_match['date_match']) $info_matches['date_last_match'] = $this_match['date_match'];
                    }
                }

                $mycontent = $line;
            }
        }
    }

    if ($debug) {
        echo "<li>info_match // $url<pre>";
        print_r($info_match);
        echo "</pre>";
    }


    if (!$debug && $info_matches['date_first_match'] && $info_matches['date_last_match']) {

        // recherche de la journée ? insertion ou update journée
        $id_info_matches = 0;
        $SQL = "SELECT `id_info_matches` FROM `pp_info_matches` WHERE `id_league`='" . $idleague . "' AND `day_number`='" . $numero_journee . "'";
        $result = $db->query($SQL);
        //echo "<li>$SQL";
        if (DB::isError($result)) {
            die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

        } else {
            if ($result->numRows() && $pp_info_matches = $result->fetchRow()) {
                //if(!strstr($pp_info_matches->urls, $url)) $pp_info_matches->urls .= "\n".$url;
                // update de la journée
                $SQL = "UPDATE `pp_info_matches` SET `date_update`=NOW(),
                        `date_first_match`='" . $info_matches['date_first_match'] . "', `date_last_match`='" . $info_matches['date_last_match'] . "'
                        WHERE `id_info_matches`='" . $pp_info_matches->id_info_matches . "'";
                $result = $db->query($SQL);
                //echo "<li>$SQL";
                if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                $id_info_matches = $pp_info_matches->id_info_matches;

            } else {
                // insert de la journée
                $SQL = "INSERT INTO `pp_info_matches`(`id_league`, `day_number`, `date_first_match`, `date_last_match`, `date_creation`, `date_update`)
                        VALUES('" . $idleague . "', '" . $numero_journee . "',
                        '" . $info_matches['date_first_match'] . "', '" . $info_matches['date_last_match'] . "', NOW(), NOW())";
                $result = $db->query($SQL);
                //echo "<li>$SQL";
                if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                $id_info_matches = $db->insertId();
            }
        }
        //$result->free();

        if ($id_info_matches && count($info_match) > 0) {
            for ($i = 0; $i < count($info_match); $i++) {
                if ($info_match[$i]['id_team_visitor'] && $info_match[$i]['id_team_host'] && $info_match[$i]['date_match']) {
                    //update du match ?
                    $SQL = "UPDATE `pp_info_match` SET `date_match`='" . $info_match[$i]['date_match'] . "',
                            `score`='" . $info_match[$i]['score'] . "', `report`='" . $info_match[$i]['report'] . "', `date_update`=NOW()
                            WHERE `id_info_matches`='" . $id_info_matches . "' AND `id_team_host`='" . $info_match[$i]['id_team_host'] . "' AND `id_team_visitor`='" . $info_match[$i]['id_team_visitor'] . "'";
                    $result = $db->query($SQL);
                    //echo "<li>$SQL";
                    if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                    if (!$db->affectedRows()) {
                        // insert du match
                        $SQL = "INSERT IGNORE INTO `pp_info_match`(`id_info_matches`, `id_team_host`, `id_team_visitor`, `date_match`, `date_creation`, `date_update`, `score`, `report`)
                                VALUES('" . $id_info_matches . "', '" . $info_match[$i]['id_team_host'] . "', '" . $info_match[$i]['id_team_visitor'] . "', '" . $info_match[$i]['date_match'] . "',
                  NOW(), NOW(), '" . $info_match[$i]['score'] . "', '" . $info_match[$i]['report'] . "')";
                        $result = $db->query($SQL);
                        //echo "<li>$SQL";
                        if (DB::isError($result)) die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");
                    }
                } else {
                    echo "<li><strong>Il y a une erreur</strong> : id_info_matches = " . $id_info_matches . " // id_team_host = " . $info_match[$i]['id_team_host'] . " // id_team_visitor = " . $info_match[$i]['id_team_visitor'] . " // date_match = " . $info_match[$i]['date_match'];
                }
            }
        }

        echo "<li>$idleague / $numero_journee termine !</li>";

    } else {
        echo "<li>$idleague / $numero_journee echoue : date_first_match ou date_last_match vide !</li>";
    }

    ob_implicit_flush();
    usleep(1000 + rand(0, 1000));
}


// ANCIEN SCAN
//echo "<pre>"; print_r($lignes); echo "</pre>";
/*
$info_matches = array();
$info_match = array();

for($i=1; $i<count($lignes); $i++)
{
    // ligne titre
    if(strstr($lignes[$i], "<th"))
    {
        //echo "<li>Titre<pre>"; print_r($colonnes); echo "</pre>";
        //if(preg_match('|>([0-9]+).* journ&eacute;e, le ([0-9]{2})/([0-9]{2})/([0-9]{4}) &agrave; ([0-9]{2})h([0-9]{2})<|i', $lignes[$i], $matches))
        //<th colspan="7">samedi 28 août 2010</th>
        if(preg_match('|>.* ([0-9]+)e?r? (.*) ([0-9]{4})<|i', $lignes[$i], $matches))
        {
            //echo "<li>Titre // ".utf8_decode($matches[2])."<pre>"; print_r($matches); echo "</pre>";

            for( $imois=0; $imois<=11; $imois++ )
            {
                if( strtolower($txtlang['MONTH_' . $imois]) == strtolower(trim($matches[2])) ) { $matches[2] = $imois * 1 + 1; break; }
            }

            $this_info_match['date_match'] = (1*$matches[3])."-".(1*$matches[2])."-".(1*$matches[1]);
            //echo "<li>$numero_journee / date_match = ".$this_info_match['date_match']."</li>";

        }
/*			else {
            echo "<li>$numero_journee / extraction de $url echoue : date match non trouve !</li><li>".htmlspecialchars($lignes[$i])."</li>";
        }*

    // ligne match
    } else {

        $this_match = array();
        $colonnes = explode("<td", $lignes[$i]);
        for($j=1; $j<count($colonnes); $j++)
        {
            // date match ?
            //<td class="date"><span>mar 14/09/2010</span></td>
            if(strstr($colonnes[$j], 'class="date"') && preg_match('|>.*([0-9]{2})/([0-9]{2})/([0-9]{4})<|i', $lignes[$i], $matches))
            {
                $this_info_match['date_match'] = $matches[3]."-".$matches[2]."-".$matches[1];
            }

            // heure de match ?
            //<td class="heure">&nbsp;19h00</td>
            //if(preg_match('|<a href="[^"]+">([^<]+)</a>|i', $colonnes[$j], $matches))
            if(preg_match('|([0-9]{2})h([0-9]{2})|i', $colonnes[$j], $matches))
            {
                $this_info_match['heure_match'] = $matches[1].':'.$matches[2].':00';
                //echo "<li>$numero_journee / heure_match = ".$this_info_match['heure_match']."</li>";
            }



            // nom d'équipe
            //<td class="visiteur"  style="background-image: url(/Football/logos/FootballLogo12_s20.gif);">            <a class="lien" href="/Football/FootballFicheClub12.html">        <b>   Toulouse
            if(strstr($colonnes[$j], 'domicile') || strstr($colonnes[$j], 'visiteur') || strstr($colonnes[$j], 'exterieur'))
            {
                $team = strip_tags("<td" . $colonnes[$j]);
                $team = explode("(", $team);
                $team = trim( str_replace( '&nbsp;', '', $team[0] ) );
                //echo "<li>team // $team";
                //$SQL = "SELECT `id_team` FROM `pp_team` WHERE `id_league`=".$idleague." AND `xlabels` LIKE '%".trim($db->escapeSimple($team))."%'";
                $SQL = "SELECT `id_team` FROM `pp_team` WHERE `xlabels` LIKE '%".trim($db->escapeSimple($team))."%'";
                $result = $db->query($SQL);
                if(DB::isError($result))
                {
                    die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");

                } else {
                    if($pp_team = $result->fetchRow())
                    {
                        if(!$this_match['id_team_host'])
                        {
                            $this_match['id_team_host'] = $pp_team->id_team;
                            $this_match['team_host'] = $team;
                        }
                        else
                        {
                            $this_match['id_team_visitor'] = $pp_team->id_team;
                            $this_match['team_visitor'] = $team;
                        }
                    } else {
                        echo "<li><strong>Equipe non trouvee</strong> : *".$team."*";
                    }
                }
            }

    // Reporté
    if(preg_match('|score">.*Reporté|i', $colonnes[$j]))
    {
      $this_match['report'] = true;
      echo "<li>match reporté ".strip_tags($lignes[$i]);
    }

            // score
            if(preg_match('|score">.*([0-9]+)-([0-9]+)|i', $colonnes[$j], $matches))
            {
                $this_match['score'] = $matches[1].'-'.$matches[2];
            }
        }
        $this_match['date_match'] = $this_info_match['date_match'].' '.($this_info_match['heure_match'] ? $this_info_match['heure_match'] : '20:45:00');
        $info_match[] = $this_match;
    }

    if($info_matches['date_first_match'] > $this_match['date_match'] || !$info_matches['date_first_match']) $info_matches['date_first_match'] = $this_match['date_match'];
    if($info_matches['date_last_match'] < $this_match['date_match']) $info_matches['date_last_match'] = $this_match['date_match'];

    //echo "<pre>"; print_r($this_match); echo "</pre>";
}
//echo "<pre>$numero_journee // info_matches // "; print_r($info_matches); echo "</pre>";*/