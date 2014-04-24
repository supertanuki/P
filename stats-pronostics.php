<?
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

// Total scores
$nbtotal_scores = 0;
$SQL = "SELECT count(`id_match`) AS NB
		FROM `pp_match`
		WHERE score!='' AND score!='-' AND score!='R-R'";
$result_score = $db->query($SQL);
if(DB::isError($result_score))
{
	die ("<li>ERROR : ".$result_score->getMessage());
	
} else {
	if($pp_score = $result_score->fetchRow()) $nbtotal_scores = $pp_score->NB;
}

// scores récurrents
$scores = array();
$SQL = "SELECT `score`, count(`id_match`) AS NB
		FROM `pp_match`
		WHERE score!='' AND score!='-' AND score!='R-R'
		GROUP BY `score`
		ORDER BY NB DESC";
$result_score = $db->query($SQL);
if(DB::isError($result_score))
{
	die ("<li>ERROR : ".$result_score->getMessage());
	
} else {
	while($pp_score = $result_score->fetchRow()) $scores[$pp_score->score]['nb_matchs'] = $pp_score->NB;
}

// Total pronostics
$nbtotal_pronos = 0;
$SQL = "SELECT count( * ) AS NB
		FROM `pp_match_user`";
$result_score = $db->query($SQL);
if(DB::isError($result_score))
{
	die ("<li>ERROR : ".$result_score->getMessage());
	
} else {
	if($pp_score = $result_score->fetchRow()) $nbtotal_pronos = $pp_score->NB;
}

// pronos récurrents
$pronos = array();
$SQL = "SELECT `score` , count( * ) AS NB, AVG( pts ) AS MOYPTS , AVG( pts_won ) AS MOYWON
		FROM `pp_match_user`
		GROUP BY `score`
		ORDER BY NB DESC";
$result_score = $db->query($SQL);
if(DB::isError($result_score))
{
	die ("<li>ERROR : ".$result_score->getMessage());
	
} else {
	while($pp_score = $result_score->fetchRow()) $scores[$pp_score->score]['stats'] = $pp_score;
}

pageheader("Statistiques des scores et pronostics");
?>
<script src="/lib/TSorter/TSorter_1_compressed.js" type="text/javascript"></script>
<script type="text/javascript">
function init_tables_sorter()
{
	if(document.getElementById('table_scores'))
	{
		var table_scores = new TSorter;
		table_scores.init('table_scores', '1');
	}
}	
window.onload = init_tables_sorter;
</script>
<style>
.table_sortable th {padding:4px 12px 4px 2px; cursor: pointer}
.table_sortable th.descend{background: #E4EDFC 95% 50% url('/template/default/up.gif') no-repeat;}
.table_sortable th.ascend{background: #E4EDFC 95% 50% url('/template/default/down.gif') no-repeat;}
</style>



<div id="content_fullscreen">
<?
// affichage des onglets
echo getOnglets();
?>
	<div id="content">
	
	<h2 class="title_green">Statistiques des scores et pronostics</h2>
	<p>Les stats des scores des matchs qui étaient à pronostiquer sur Prono+ et les pronostics de tous les joueurs.</p>
	
	<?php
	if(!$nbtotal_scores)
	{
		echo '<p class="message_error"><b>Aucune stat disponible pour le moment...</b></p>';
	
	} else {
		$altern = 0;
		echo '<table id="table_scores" width="100%" class="table_sortable">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>Score</th>';
		echo '<th abbr="number" class="descend">Nombre<br />matchs</th>';
		echo '<th abbr="number">%</th>';
		echo '<th abbr="number">Nombre pronos</th>';
		echo '<th abbr="number">%</th>';
		echo '<th abbr="number">Moyenne<br />points misés</th>';
		echo '<th abbr="number">Moyenne<br />points gagnés</th>';
		echo '<th abbr="float">Ratio points gagnés<br />/ points misés</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		$nbmax=30;
		$nb=0;
		foreach($scores as $score=>$stats)
		{
			$class_line = 'ligne_blanche';			
			echo "<tr class=\"".$class_line."\" onmouseover=\"this.className='ligne_rollover'\" onmouseout=\"this.className='".$class_line."'\">";
			echo "<td align=\"center\"><strong>".$score."</strong></td>";
			echo "<td align=\"right\">".($stats['nb_matchs'] ? $stats['nb_matchs'] : 0)."</td>";
			echo "<td align=\"center\">".($nbtotal_scores && $stats['nb_matchs'] ? round(100*$stats['nb_matchs']/$nbtotal_scores) : 0)."%</td>";
			echo "<td align=\"right\">".($stats['stats']->NB ? $stats['stats']->NB : 0)."</td>";
			echo "<td align=\"center\">".($nbtotal_pronos && $stats['stats']->NB ? round(100*$stats['stats']->NB/$nbtotal_pronos) : 0)."%</td>";
			echo "<td align=\"center\">".($stats['stats']->MOYPTS ? round($stats['stats']->MOYPTS) : 0)."</td>";
			echo "<td align=\"center\">".($stats['stats']->MOYWON ? round($stats['stats']->MOYWON) : 0)."</td>";
			echo "<td align=\"center\">".number_format(($stats['stats']->MOYWON ? round($stats['stats']->MOYWON / $stats['stats']->MOYPTS, 2) : 0), 2)."</td>";
			echo "</tr>";
			$nb++;
			if($nb==$nbmax) break;
		}
		echo '</tbody>';
		echo "</table><br /><br />";
	}
	?>	
	</div>
</div>



<?
pagefooter();
?>