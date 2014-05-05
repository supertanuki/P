<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

/* draws a calendar */
function draw_calendar($month,$year){

    /* draw table */
    $calendar = '<table cellpadding="2" cellspacing="0" class="calendar">';

    /* table headings */
    $headings = array('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi', 'Dimanche');
    $calendar.= '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';

    /* days and weeks vars now ... */
    $running_day = date('N',mktime(0,0,0,$month,1,$year))-1;
    $days_in_month = date('t',mktime(0,0,0,$month,1,$year));
    $days_in_this_week = 1;
    $day_counter = 0;
    $dates_array = array();

    /* row for week one */
    $calendar.= '<tr class="calendar-row">';

    /* print "blank" days until the first of the current week */
    for($x = 0; $x < $running_day; $x++):
        $calendar.= '<td class="calendar-day-np"> </td>';
        $days_in_this_week++;
    endfor;

    /* keep going with days.... */
    for($list_day = 1; $list_day <= $days_in_month; $list_day++):
        $calendar.= '<td class="calendar-day">';
        /* add in the day number */
        $calendar.= '<div class="day-number"><a href="?date='.sprintf("%02s", $list_day).'-'.sprintf("%02s", $month).'-'.$year.'" class="link_button">'.$list_day.'</a></div>';

        $calendar.= '</td>';
        if($running_day == 6):
            $calendar.= '</tr>';
            if(($day_counter+1) != $days_in_month):
                $calendar.= '<tr class="calendar-row">';
            endif;
            $running_day = -1;
            $days_in_this_week = 0;
        endif;
        $days_in_this_week++; $running_day++; $day_counter++;
    endfor;

    /* finish the rest of the days in the week */
    if($days_in_this_week < 8):
        for($x = 1; $x <= (8 - $days_in_this_week); $x++):
            $calendar.= '<td class="calendar-day-np"> </td>';
        endfor;
    endif;

    /* final row */
    $calendar.= '</tr>';

    /* end the table */
    $calendar.= '</table>';

    /* all done, return result */
    return $calendar;
}

$date_array = '';
if(isset($_GET['date']))
{
    $date_array = explode('-', $_GET['date']);
}

if(!isset($_GET['date']) || !checkdate($date_array[1], $date_array[0], $date_array[2]))
{
    HeaderRedirect('/agenda-football.php?date='.date("d-m-Y"));
}

$now = mktime(0, 0, 0, $date_array[1], $date_array[0], $date_array[2]);
$titrepage = 'Agenda football du '.get_date_complete(date("N", $now)-1, $date_array[0]*1, $date_array[1]*1, $date_array[2]*1);

$user = user_authentificate();
pageheader($titrepage);
?>


    <div id="content_fullscreen">
        <?php
        // affichage des onglets
        echo getOnglets();
        ?>
        <div id="content">


            <?php

            echo '<h2 class="title_green">Agenda football</h2>
            <p>';

            $today = mktime(0,0,0);

            echo '<table width="100%"><tr><td width="50%" align="center" valign="top">';
            echo $txtlang['MONTH_'.(1*date('m', $today)-1)] . ' ' . date('Y', $today);
            echo draw_calendar(date('m', $today), date('Y', $today));

            echo '</td><td width="50%" align="center" valign="top">';

            $next_month = date('m', $today)+1;
            $next_month_year = date('Y', $today);
            if($next_month>12)
            {
                $next_month = 1;
                $next_month_year++;
            }
            $next_month = mktime(0,0,0,$next_month,1,$next_month_year);
            echo $txtlang['MONTH_'.(1*date('m', $next_month)-1)] . ' ' . date('Y', $next_month);
            echo draw_calendar(date('m', $next_month), date('Y', $next_month));
            echo '</td></tr></table>';
            echo '</p>';

            echo '<h2 class="title_orange">' . $titrepage . '</h2>';

            /*
            // Liste journées
            $pp_info_matches_arr = array();
            $SQL = "SELECT `day_number`, `id_info_matches`
				FROM `pp_info_matches`
				WHERE `pp_info_matches`.`id_league`='" . $_GET[id] . "' AND `day_number`>0
				ORDER BY `day_number`";
            $result = $db->query($SQL);
            //echo "<li>$SQL";
            if (DB::isError($result)) {
                die ("<li>ERROR : " . $result->getMessage() . "<li>$SQL");

            } else {
                while ($pp_info_matches = $result->fetchRow()) {
                    $pp_info_matches_arr[] = $pp_info_matches;
                }
            }

            echo '<br /><table width="100%"><tr>';
            foreach ($pp_info_matches_arr as $pp_info_matches) {
                echo '<td align="center">';

                if ($pp_info_matches->day_number != $pp_day_number->day_number)
                    echo '<a href="/stats-classement.php?id=' . $pp_league->id_league . '&j=' . $pp_info_matches->day_number . '" class="link_button">' . $pp_info_matches->day_number . '</a>';
                else
                    echo '&nbsp;<b>' . $pp_info_matches->day_number . '</b>&nbsp;';

                echo '</td>';

                if ((count($pp_info_matches_arr) / 2) == $pp_info_matches->day_number) echo '</tr><tr>';
            }
            echo '</tr></table><br />';
            */


            // Liste matchs
            $SQL = "SELECT `pp_info_match`.`id_team_host`, `pp_info_match`.`id_team_visitor`,
                    `pp_info_match`.`id_info_match`, `pp_info_match`.`score`,
                    `team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`,
                    `team_host`.flag AS team_host_flag,
                    `team_visitor`.flag AS team_visitor_flag,
                    `pp_info_match`.`date_match`,
                    YEAR(`pp_info_match`.`date_match`) AS `date_match_year`,
                    MONTH(`pp_info_match`.`date_match`) AS `date_match_month`,
                    DAYOFMONTH(`pp_info_match`.`date_match`) AS `date_match_day`,
                    DAYOFWEEK(`pp_info_match`.`date_match`) AS `date_match_dayweek`,
                    DATE_FORMAT(`pp_info_match`.`date_match`, '" . $txtlang['AFF_TIME_SQL'] . "') AS `time_match_format`,
                    DATE_FORMAT(`pp_info_match`.`date_match`, '" . $txtlang['AFF_DATE_TIME_SQL'] . "') AS `date_match_format`
                FROM `pp_info_match`
                INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_info_match`.`id_team_host`
                INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_info_match`.`id_team_visitor`
                INNER JOIN `pp_info_matches` ON `pp_info_match`.`id_info_matches`=`pp_info_matches`.`id_info_matches`
                    AND `pp_info_matches`.`day_number`='" . $pp_day_number->day_number . "' AND `pp_info_matches`.`id_league`='" . $pp_league->id_league . "'
                ORDER BY `pp_info_match`.`date_match`";
            $result_match = $db->query($SQL);
            //echo "<li>$SQL";
            if (DB::isError($result_match)) {
                die ("<li>ERROR : " . $result_match->getMessage() . "<li>$SQL");

            } else {
                if ($result_match->numRows()) {
                    echo '<table width="100%" cellpadding="4">';
                    echo '<tr>
                        <th width="40%"></th>
                        <th width="10%">Score</th>
                        <th width="40%"></th>
                        <th width="5%">Commentaires</th>
                        <th width="5%">Note</th>
                    </tr>';
                    $date_tmp = "";
                    $i = 0;
                    while ($pp_info_match = $result_match->fetchRow()) {
                        $i++;
                        if (!$pp_info_match->score) $tooltip[$i]['content'] = get_apercu_stats_match($pp_info_match);

                        if ($altern) {
                            $class_line = 'ligne_grise';
                            $altern = 0;
                        } else {
                            $class_line = 'ligne_blanche';
                            $altern = 1;
                        }
                        //echo '<li>'.$pp_info_match->team_host_label.' <b>'.($pp_info_match->score ? $pp_info_match->score : '-').'</b> '.$pp_info_match->team_visitor_label.' / '.$pp_info_match->date_match_format.'</li>';

                        if ($pp_info_match->date_match != $date_tmp) {
                            echo '<tr>';
                            echo '<td colspan="5" style="padding-top:16px; border-bottom:solid 1px #ccc">';
                            if (substr($date_tmp, 0, 10) != substr($pp_info_match->date_match, 0, 10)) {
                                $dayweek = $pp_info_match->date_match_dayweek;
                                if ($dayweek == 1) $dayweek = 8;
                                $dayweek = $dayweek - 2;
                                echo get_date_complete($dayweek, $pp_info_match->date_match_day, $pp_info_match->date_match_month - 1, $pp_info_match->date_match_year);
                            }
                            echo ' &agrave; ' . $pp_info_match->time_match_format;
                            echo '</td>';
                            echo '</tr>';

                            $date_tmp = $pp_info_match->date_match;
                        }


                        $tabscore = explode('-', $pp_info_match->score);

                        echo '<tr id="match_line_' . $i . '" class="' . $class_line . '" onmouseover="this.className=\'ligne_rollover\'" onmouseout="this.className=\'' . $class_line . '\'">';
                        echo '<td align="right"><a href="/stats-equipe.php?id=' . $pp_info_match->id_team_host . '" class="link_orange">' . formatDbData($tabscore[0] > $tabscore[1] ? '<b>' . $pp_info_match->team_host_label . '</b>' : $pp_info_match->team_host_label) . ' ' . ($pp_info_match->team_host_flag && $pp_info_match->team_visitor_flag ? ' <img src="/image/flags/' . $pp_info_match->team_host_flag . '" align="absmiddle" border="0" />' : '') . '</a></td>';

                        echo '<td id="div_score_match_' . $i . '" align="center"><a href="/info_match.php?id=' . $pp_info_match->id_info_match . '" class="link_orange"><b>' . ($pp_info_match->score ? $pp_info_match->score : '-') . '</b></a></td>';

                        echo '<td><a href="/stats-equipe.php?id=' . $pp_info_match->id_team_visitor . '" class="link_orange">' . ($pp_info_match->team_host_flag && $pp_info_match->team_visitor_flag ? '<img src="/image/flags/' . $pp_info_match->team_visitor_flag . '" align="absmiddle" border="0" /> ' : '') . ' ' . formatDbData($tabscore[0] < $tabscore[1] ? '<b>' . $pp_info_match->team_visitor_label . '</b>' : $pp_info_match->team_visitor_label) . '</a></td>';

                        $comments_nb = pp_comments_nb('info_match', $pp_info_match->id_info_match);
                        echo '<td align="center"><a href="/info_match.php?id=' . $pp_info_match->id_info_match . '" class="link_orange"><img src="/template/default/comment.gif" align="absmiddle" border=0"> ' . ($comments_nb > 0 ? $comments_nb : '') . '</a></td>';

                        $pp_info_match_note = get_note_match($pp_info_match->id_info_match);
                        echo '<td align="center"><a href="/info_match.php?id=' . $pp_info_match->id_info_match . '" class="link_orange">' . ($pp_info_match_note[note_match] ? round($pp_info_match_note[note_match], 2) . '/20' : 'Noter&nbsp;!') . '</a></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
            }
            ?>


        </div>
    </div>



<?php
pagefooter();