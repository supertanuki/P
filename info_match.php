<?php
/**
* Project: PRONOPLUS
* Description: Match, vote...
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2009-08-13
* Version: 1.0
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

if(!$_GET[id]) HeaderRedirect('/');

// recherche match
$SQL = "SELECT `pp_info_match`.`id_info_match`, t1.label AS `team_host`, t2.label AS `team_visitor`, pp_info_matches.day_number,
			pp_league.id_league, pp_league.label AS `league`, pp_league.afficher_classement, pp_league.flag,
			DATE_FORMAT(`pp_info_match`.`date_match`, '".$txtlang['AFF_DATE_SQL']."') AS `date_match_format`, score, penalties
		FROM `pp_info_match`
		INNER JOIN `pp_team` AS t1 ON t1.id_team = pp_info_match.id_team_host
		INNER JOIN `pp_team` AS t2 ON t2.id_team = pp_info_match.id_team_visitor
		INNER JOIN pp_info_matches ON pp_info_matches.id_info_matches = pp_info_match.id_info_matches
		INNER JOIN pp_league ON pp_info_matches.id_league = pp_league.id_league	
		WHERE `pp_info_match`.`id_info_match`='".$_GET[id]."'";
$result = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	if(!$match = $result->fetchRow())
	{
		HeaderRedirect('/');
	}
}

if( $match->score && $match->score[0] > $match->score[2] )
{
	$soustitre = $match->team_host." a battu ".$match->team_visitor." sur le score de ".$match->score[0]." but".($match->score[0] > 1 ? 's' : '')." à ".$match->score[2];
	
} else if( $match->score && $match->score[0] < $match->score[2] )
{
	$soustitre = $match->team_host." a été battu par ".$match->team_visitor." sur le score de ".$match->score[2]." but".($match->score[2] > 1 ? 's' : '')." à ".$match->score[0];

} else if( $match->score && $match->score[0] == $match->score[2] )
{
	$soustitre = $match->team_host." et ".$match->team_visitor." ont fait match nul sur le score de ".$match->score[2]." à ".$match->score[0];

} else {
	$soustitre = $match->team_host." rencontre ".$match->team_visitor;
}

$user = user_authentificate();

$title_page = $match->team_host.' '.($match->score ? $match->score : '-').' '.$match->team_visitor.' - '.$match->league.($match->day_number ? ', journée '.$match->day_number : '');

pageheader(formatDbData($title_page), array('meta_description' => $soustitre.', le '.$match->date_match_format));



// recherche journée
$title_retour = '';
$url_retour = '';

$SQL = "SELECT `id_matches`, `pp_matches`.`label`, `pp_matches`.`image`,
		`pp_info_country`.`label` AS `country`
		FROM `pp_matches`
		INNER JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_matches`.`id_info_country`
		WHERE `pp_matches`.`id_matches`='".$_GET[idclass]."'
		AND `date_first_match` < NOW()";
$result = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	if($pp_matches = $result->fetchRow())
	{
		$title_retour = formatDbData($pp_matches->label);
		$url_retour = '/classj.php?id='.$_GET[idclass];
	}
}


?>

<style>
.grille_track
{
	background:url(mise_bg.png) #CCCCCC;
	height:10px;
}
.grille_handle
{
	width:14px;
	height:14px;
	background-color:#FF6600;
	cursor: col-resize;
}
.grille_handle_off
{
	width:14px;
	height:14px;
	background-color:#555;
}
.notedumatch
{
	width:38px;
	text-align:center;
	background-color:#FF6600;
	color:#fff;
	font-weight:bold;
	padding:4px;
}
</style>


<div id="content_fullscreen">
<?php
/*
if($title_retour && $url_retour)
{
	$onglets = array(array('title' => $title_retour, 'url' => $url_retour, 'current' => true));
} else {
	$onglets = array(array('title' => 'Classement', 'url' => 'class.php?id='.$_GET[id], 'current' => true));
}
*/
// affichage des onglets
echo getOnglets();


$libelle_match = formatDbData($match->team_host.' - '.$match->team_visitor);


$notedumatch = 0;
$nb_votants = 0;
if($pp_info_match_note = get_note_match($match->id_info_match))
{
	$notedumatch = $pp_info_match_note[note_match];
	$nb_votants = $pp_info_match_note[nb_votants];
	if($notedumatch)
	{
		$pourcent = round($notedumatch/20 * 100) - 4;
		if($pourcent < 1) $pourcent=1;
	}
}
?>

	<div id="content">
		<h1 class="title_green"><?php echo formatDbData($title_page)?></h1>
		
		<p>
			<? echo ($match->afficher_classement ? '<a href="/stats-classement.php?id='.$match->id_league.'&j='.$match->day_number.'" class="link_orange">' : '')
				. ($match->flag ? '<img src="/image/flags/'.$match->flag.'" border="0" align="absmiddle" /> ' : '')
				. $match->league
				. ($match->day_number ? ' - ' . $match->day_number . ($match->day_number > 1 ? '<sup>ème</sup>' : '<sup>ère</sup>') . ' journée' : '')
				. ($match->afficher_classement ? '</a>' : '')
				. ' : '
				. $soustitre;
			?>
		</p>
		
		<h3 style="color:#555" align="center">La note du match <? echo formatDbData($match->team_host.' - '.$match->team_visitor); ?> <?php echo $match->score ? '('.$match->score.')' : ''; ?> : <? echo $notedumatch ? round($notedumatch, 2) . ' / 20' : ''; ?></h3>		
		
		
		<?php
		if($nb_votants > 0) {
		?>		
			<br />
			<table width="100%" border="0"><tr>	
				<td width="<?php echo $pourcent?>%">&nbsp;</td>
				<td width="<?php echo 100-$pourcent?>%"><div class="notedumatch"><?php echo round($notedumatch, 2);?></div></td>
			</tr></table>
			
			<div id="track_note_du_match" class="grille_track">
				<div id="handle_note_du_match" class="grille_handle_off"> </div>
			</div>		
			
			<table width="100%" border="0"><tr>	
				<td align="left" width="2%">0</td>	
				<td align="center" width="47%">5</td>	
				<td align="center" width="2%">10</td>	
				<td align="center" width="47%">15</td>	
				<td align="right" width="2%">20</td>	
			</tr></table>
			
			<br />
			<p align="center"><?php echo $nb_votants?> joueur<?php echo $nb_votants>1 ? 's ont ' : ' a '; ?> noté le match <b><?php echo $libelle_match?></b>.</p>
			<br /><br />
		<?php
		} else {
		?>
			<p align="center">Aucun joueur n'a noté ce match. Soyez le premier !</p>
			<br /><br />
		<? } ?>
		

		
		<div style="background:#eee; padding:6px 0">
		    <h3 align="center">Ma note du match</h3>
		    <br />
		
		
		    <div id="track_note" class="grille_track">
			    <div id="handle_note" class="grille_handle"> </div>
		    </div>
		
		    <table width="100%" border="0"><tr>
			    <td align="left" width="2%">0</td>	
			    <td align="center" width="47%">5</td>	
			    <td align="center" width="2%">10</td>	
			    <td align="center" width="47%">15</td>	
			    <td align="right" width="2%">20</td>
		    </tr></table>
		</div>
		
		<br />
		
		<?php
		$note_perso_match = 0;
		$SQL = "SELECT `note` FROM `pp_info_match_note` WHERE `id_info_match`='".$_GET[id]."' AND `id_user`='".$user->id_user."'";
		$result = $db->query($SQL);
		if(DB::isError($result))
		{
			die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
			
		} else {
			if($pp_info_match_note = $result->fetchRow())
			{
				$note_perso_match = $pp_info_match_note->note;
				?>
				<div id="note_du_match" class="message_error" align="center">Vous avez donné une note de <?php echo $pp_info_match_note->note?> à ce match.</div>
				<?php
				
			} else {
				?>
				<div id="note_du_match" class="message_error" align="center">Vous n'avez pas encore noter <?php echo $libelle_match?><br />
				Déplacer le curseur orange pour noter ce match.</div>
				<?php
			}
		}
		?>
		
		<a name="comments"></a>
		<h1 class="title_orange">Commenter ce match</h1>
		<?php
		echo pp_comments_afficher('info_match', $_GET[id]);
		?>
		
	</div>
</div>


<script type="text/javascript" language="javascript">
// <![CDATA[
function init_sliders()
{
<?php
if($nb_votants > 0) {
?>	
	var slider_note_du_match = new Control.Slider('handle_note_du_match','track_note_du_match',
		{
			range:$R(0,20),
			sliderValue: <?php echo $notedumatch?>
		});
	slider_note_du_match.setDisabled();
<?php
}
?>	
	new Control.Slider('handle_note','track_note',
		{
			range:$R(0,20),
			values: [<? for($i=0; $i<=19; $i++) echo $i.', '; ?>, 20],
			sliderValue: <?php echo $note_perso_match?>,
        	onChange:function(note) { noter(note); }
		});
}

function noter(note)
{
<? if(!$user->id_user) { ?>
		$('note_du_match').update('<a href="javascript:" onclick="SeConnecter();" class="link_orange">Connectez-vous pour noter ce match</a>');
	
<? } else { ?>
	note = Math.round(note);
	new Ajax.Request('/actions.php', {
		parameters: { action: 'noter_match', note: note, id_info_match: <?php echo $_GET[id]?> },
		onComplete: function(transport)
		{
			if (200 == transport.status)
			{
				$('note_du_match').update("Votre note de " + note +" a été enregistrée.");
				new Effect.Highlight('note_du_match', {startcolor:'#ffff00', duration:1});
			}
		}

	});
<? } ?>
}

window.onload = init_sliders;
// ]]>
</script>

<?php
pagefooter();
?>
