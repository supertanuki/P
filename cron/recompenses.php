<?php
/**
* Project: PRONOPLUS
* Description: Calcul des etoiles
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2013-07-31
* Version: 1.0
*/
chdir( dirname(__FILE__) );
chdir( '../' );
$_SERVER['DOCUMENT_ROOT'] = getcwd();

require_once($_SERVER['DOCUMENT_ROOT'] . '/init.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/mainfunctions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/contentfunctions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/recompenses_ids.php');

$url = "http://www.pronoplus.com/";

// ne pas créer de postit ni d'email
$debug = false;



// recherche des membres actifs
$SQL = "SELECT * FROM  `pp_user` WHERE  `last_ip` !=  ''";

// debug
//$SQL .= " AND login='Weby'";

$results_user = $db->query($SQL);

$mails_spool = array();

if(DB::isError($results_user))
{
	die ("<li>ERROR : ".$results_user->getMessage());
	
} else {
	while($pp_user = $results_user->fetchRow())
	{
        $recompenses_valid = array();

        // les messages du forum
        $SQL = "SELECT COUNT(Nmsg) AS NB
                FROM `forum`
                WHERE `id_user`='".$db->escapeSimple($pp_user->id_user)."'";
        $result = $db->query($SQL);
        if($row = $result->fetchRow())
        {
            if($row->NB) $recompenses_valid['poster_message'] = true;
            if($row->NB >= 100) $recompenses_valid['poster_100_messages'] = $row->NB;
            if($row->NB >= 500) $recompenses_valid['poster_500_messages'] = $row->NB;
            if($row->NB >= 1000) $recompenses_valid['poster_1000_messages'] = $row->NB;
        }

        // les sujets du forum
        $SQL = "SELECT COUNT(Nmsg) AS NB
                FROM `forum`
                WHERE `id_user`='".$db->escapeSimple($pp_user->id_user)."' AND Nquest=0";
        $result = $db->query($SQL);
        if($row = $result->fetchRow())
        {
            if($row->NB) $recompenses_valid['poster_sujet'] = true;
            if($row->NB >= 10) $recompenses_valid['poster_10_sujets'] = $row->NB;
        }

        // les coupes
        $SQL = "SELECT cup_sub, count(`id_user_won`) AS NB
                FROM `pp_cup_match_opponents`
                WHERE `number_tour`=4 AND `id_user_won`='".$db->escapeSimple($pp_user->id_user)."'
                GROUP BY cup_sub";
        $result_cup_user = $db->query($SQL);
        while($cup_user = $result_cup_user->fetchRow())
        {
            if($cup_user->cup_sub == 1) $recompenses_valid['coupe_or'] = $cup_user->NB;
            elseif($cup_user->cup_sub == 2) $recompenses_valid['coupe_argent'] = $cup_user->NB;
            elseif($cup_user->cup_sub == 3) $recompenses_valid['coupe_bronze'] = $cup_user->NB;
        }

        // nb de matchs joués
        $SQL = "SELECT COUNT(id_user) AS NB
                FROM `pp_match_user`
                WHERE `id_user`='".$db->escapeSimple($pp_user->id_user)."'";
        $result = $db->query($SQL);
        if($row = $result->fetchRow())
        {
            if($row->NB >= 100) $recompenses_valid['jouer_100'] = $row->NB;
            if($row->NB >= 500) $recompenses_valid['jouer_500'] = $row->NB;
            if($row->NB >= 1000) $recompenses_valid['jouer_1000'] = $row->NB;
        }

        // nb de résultats
        $SQL = "SELECT type_result, COUNT(id_user) AS NB
                FROM `pp_match_user`
                WHERE `id_user`='".$db->escapeSimple($pp_user->id_user)."'
                GROUP BY type_result";
        $result = $db->query($SQL);
        while($row = $result->fetchRow())
        {
            if($row->type_result == 1 && $row->NB >= 10) $recompenses_valid['scores_10'] = $row->NB;
            if($row->type_result == 1 && $row->NB >= 100) $recompenses_valid['scores_100'] = $row->NB;
            if($row->type_result == 2 && $row->NB >= 10) $recompenses_valid['resultats_10'] = $row->NB;
            if($row->type_result == 2 && $row->NB >= 100) $recompenses_valid['resultats_100'] = $row->NB;
            if($row->type_result == 2 && $row->NB >= 500) $recompenses_valid['resultats_500'] = $row->NB;
        }

        // points gagnés
        $SQL = "SELECT COUNT(`id_user`) AS NB, SUM(IF(`nb_points`>=600,1,0)) AS NB600
                FROM `pp_class_user`
                WHERE `id_class`=1 AND `id_user`='".$db->escapeSimple($pp_user->id_user)."' AND `nb_points`>=500";
        $result = $db->query($SQL);
        if($row = $result->fetchRow())
        {
            if($row->NB >= 1) $recompenses_valid['points_500'] = $row->NB;
            if($row->NB600 >= 1) $recompenses_valid['points_600'] = $row->NB600;
        }

        // journées gagnés
        $SQL = "SELECT COUNT(`id_user`) AS NB
                FROM `pp_class_user` INNER JOIN `pp_matches` ON `pp_matches`.`id_matches`=`pp_class_user`.`id_matches` AND `pp_matches`.`id_cup_matches`=0
                WHERE `id_class`=1 AND `class`=1 AND `id_user`='".$db->escapeSimple($pp_user->id_user)."'";
        $result = $db->query($SQL);
        if($row = $result->fetchRow())
        {
            if($row->NB >= 1) $recompenses_valid['journee'] = $row->NB;
            if($row->NB >= 5) $recompenses_valid['journee_5'] = $row->NB;
        }

        // classement mensuel
        $SQL = "SELECT COUNT(`pp_class_user`.`id_user`) AS NB
                FROM `pp_class` INNER JOIN `pp_class_user` ON `pp_class_user`.`id_class`=`pp_class`.`id_class` AND `pp_class_user`.`id_matches`=`pp_class`.`last_id_matches`
                WHERE `pp_class`.`close`='1' AND `pp_class`.`type`='month'
                AND `pp_class_user`.`class`=1 AND `pp_class_user`.`id_user`='".$db->escapeSimple($pp_user->id_user)."'";
        $result = $db->query($SQL);
        if($row = $result->fetchRow())
        {
            if($row->NB >= 1) $recompenses_valid['mensuel'] = $row->NB;
        }


//        echo "<pre>"; print_r($recompenses_valid); echo '</pre>';


        // on récupère les étoiles déjà obtenues
        $SQL = "SELECT id_recompense, nb
                FROM  `pp_user_recompenses`
                WHERE  `id_user` = " . $pp_user->id_user;
        $result_recompenses = $db->query($SQL);
        $recompenses = array();
        while($pp_recompenses = $result_recompenses->fetchRow())
        {
            $recompenses[$pp_recompenses->id_recompense] = $pp_recompenses->nb;
        }

//        echo "<pre>"; print_r($recompenses); echo '</pre>';

        $recompenses_alerte = array();
        foreach($recompenses_valid as $id_recompenses => $nb)
        {
            if(!isset($recompenses[$id_recompenses]))
            {
                // Nouvelle étoile
                $recompenses_alerte[$id_recompenses] = $nb;

                $SQL = "INSERT INTO  `pp_user_recompenses` (
                    `id_user` ,
                    `id_recompense` ,
                    `nb`
                )
                VALUES (
                    ".$pp_user->id_user.",  '".$db->escapeSimple($id_recompenses)."',  $nb
                )";
//                echo "<li>$SQL</li>";
                $results = $db->query($SQL);
                if(DB::isError($results)) die ("<li>ERROR : ".$results->getMessage());
            }
        }


        if(count($recompenses_alerte))
        {
            $body = array();
            $body[] = "<p>Salut !</p><p>On a la joie de t'annoncer que tu viens d'obtenir "
                .(
                    count($recompenses_alerte) > 1 ?
                    count($recompenses_alerte) . ' nouvelles étoiles' :
                    ' une nouvelle étoile'
                )
                ." sur Prono+&nbsp;!</p>";

            $body_email = array();
            $body_postit = array();
            foreach($recompenses_ids as $title => $rr)
            {
                $body_li_email = array();
                $body_li_postit = array();
                foreach($rr as $r)
                {
                    if(isset($recompenses_alerte[ $r['id'] ]))
                    {
                        $body_li_email[] = '<li>'.$r['title'].'</li>';
                        $body_li_postit[] = '- '.$r['title'] . '<br />';
                    }
                }
                if(count($body_li_email))
                {
                    $body_email[] = '<b>' . $title . '</b><ul>' . implode("\n", $body_li_email) . '</ul>';
                    $body_postit[] = implode("\n", $body_li_postit);
                }
            }

            $body[] = implode("\n", $body_email);
            $body[] = '<p><b>Total :</b> '.(count($recompenses_valid)>1 ? count($recompenses_valid).' étoiles obtenues ! :)' : 'Qu\'une seule étoile obtenue !').'</p>';
            $body[] = '<p><a href="'. $url .'recompenses.php?q='.urlencode($pp_user->login).'">Voir le détail des étoiles obtenues</a></p>';

            $body = implode("\n", $body);

            // Nouvelle étoile => Ajout de post-it + envoi Email
            if(!$debug || $pp_user->login == 'Weby')
            {
                $postit = '<a href="/recompenses.php?q='.urlencode($pp_user->login).'" class="link_orange">'
                    . (
                        count($recompenses_alerte) > 1 ?
                        count($recompenses_alerte) . ' nouvelles étoiles obtenues !' :
                        'Une nouvelle étoile obtenue !'
                    ) . '</a><br />' . implode("\n", $body_postit);

                $SQL = "INSERT INTO `pp_postit` (
                    `id_user` ,
                    `message` ,
                    `date_message` ,
                    `active`
                    )
                    VALUES (
                        ".$pp_user->id_user." , '".$db->escapeSimple($postit)."', NOW() ,  '1'
                    )";
                $results = $db->query($SQL);
                if(DB::isError($results)) die ("<li>ERROR : ".$results->getMessage());

                if(!$pp_user->no_mail)
                {
                    $mails_spool[] = array(
                        'to' => $pp_user->email,
                        'subject' => 'Bravo ! ' . (
                        count($recompenses_alerte) > 1 ?
                            count($recompenses_alerte) . ' nouvelles étoiles obtenues sur Prono+ !' :
                            ' Une nouvelle étoile obtenue sur Prono+ !'
                        ),
                        'body' =>  $body
                    );
                }
            }
        }
	}
}

//echo "<pre>"; print_r($mails_spool); echo '</pre>';



foreach($mails_spool as $mail)
{
    sendemail($mail['to'], 'Liline de Prono+', 'noreply@pronoplus.com', $mail['subject'], $mail['body']);
}

echo '<p>Envoi de '.count($mails_spool). ' mail(s)</p>';