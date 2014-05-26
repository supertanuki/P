function pp_login() {
    var param = 'action=connect&login=' + ($('connect_login').value) + '&pwd=' + ($('connect_password').value) + '&permanent=' + ($('connect_permanent').checked);
    $('login_form_status_msg').update('Vérification en cours, veuillez patienter...');
    $('login_form_status').show();
    $('login_form').hide();

    new Ajax.Request('/login.php', {
        method: 'post',
        parameters: param,
        onComplete: function (data) {
            var retour = eval('(' + data.responseText + ')');
            if (retour.response == 200) {
                document.location.href = redirect ? redirect : 'index.php';

            } else {
                $('login_form_status_msg').update('<strong>Identification incorrecte !</strong>');
                $('connect_login').select();
                $('login_form').show();
            }
        }
    });

    return false;
}


function setScore(id_match) {
    // alert('setScore');
    var score = $('score_team_host_' + id_match)[$('score_team_host_' + id_match).selectedIndex].value + '-' + $('score_team_visitor_' + id_match)[$('score_team_visitor_' + id_match).selectedIndex].value;

    $('score_match_' + id_match).value = score;
    // alert(score);
}

function updateMise() {
    $('msgbugsafari').hide();
    $('pronoform').show();

    // alert('updateMise');
    var points_mises = 0;
    for (var i = 1; i <= nb_matchs; i++) {
        points_mises += 1 * ( $('mise_match_' + i).value != 'undefined' ? $('mise_match_' + i).value : $('mise_match_' + i)[$('mise_match_' + i).selectedIndex].value );
    }

    var points_trop = points_mises - pts_a_miser;
    var points_restants = pts_a_miser - points_mises;

    // alert($('points_mises'));
    $('points_mises').update(points_mises);
    $('points_trop').update(points_trop > 0 ? points_trop : 0);
    $('points_restants').update(points_restants > 0 ? points_restants : 0);
}


function saveprono() {

    if ($('points_trop').value * 1 > 0) {
        $('msg_points_trop').show();
        $('msg_points_restants').hide();

    } else if ($('points_restants').value * 1 > 0) {
        $('msg_points_restants').show();
        $('msg_points_trop').hide();

    } else {
        return true;
    }

    return false;
}