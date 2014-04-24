<?
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

$id_class = !$_GET[id] ? 2 : $_GET[id];
$id_user = $user->id_user;
$myStats = array();
$nb_matches = 0;


// Classement
$SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`label`
		FROM `pp_class`
		WHERE `pp_class`.`type`!='day' AND `pp_class`.`id_class`='".$id_class."'";
$result = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	if(!$pp_class_label = $result->fetchRow())
	{
		HeaderRedirect('/');
	}
}


// nb d'éléments
$SQL = "SELECT `pp_class_user`.`id_matches`
		FROM `pp_class_user`
		WHERE `pp_class_user`.`id_user`='".$id_user."' AND `pp_class_user`.`id_class`='".$id_class."'";
$result_matches = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result_matches->getMessage());
	
} else {
	$nb_matches = $result_matches->numRows();
}




$content  = '';
		
$SQL = "SELECT `pp_class_user`.`id_matches`, `pp_class_user`.`class`, `pp_class_user`.`nb_points`, `pp_class_user`.`nb_score_ok`, `pp_class_user`.`nb_result_ok`,
		`pp_matches`.`label`, `pp_matches`.`image`,
		DATE_FORMAT(`pp_matches_user`.`date_creation`, '".$txtlang['AFF_DATE_SQL']."') AS `date_prono`
		FROM `pp_class_user`
		INNER JOIN `pp_matches` ON `pp_class_user`.`id_matches` = `pp_matches`.`id_matches`
		LEFT JOIN `pp_matches_user` ON `pp_matches_user`.`id_matches`=`pp_matches`.`id_matches` AND `pp_matches_user`.`id_user`='".$id_user."'
		WHERE `pp_class_user`.`id_user`='".$id_user."' AND `pp_class_user`.`id_class`='".$id_class."'
		ORDER BY `pp_class_user`.`date_calcul`";
$result_matches = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result_matches->getMessage());
	
} else {
	if($result_matches->numRows())
	{		
		$i = $nb_begin+1;
		
		$table = '';
		
		while($pp_matches = $result_matches->fetchRow())
		{					
			if($altern) {
				$class_line = 'ligne_grise';
				$altern = 0;
			} else {
				$class_line = 'ligne_blanche';
				$altern = 1;
			}

			$content = '<tr class="'.$class_line.'">';
			$content .= '<td align="center">'.$i.'</td>';
			$content .= '<td><a href="classj.php?id='.$pp_matches->id_matches.'"><img src="template/default/'.$pp_matches->image.'" style="border:solid 3px #eee;" height="50" width="35" /></a></td>';
			$content .= '<td><h3><a href="classj.php?id='.$pp_matches->id_matches.'" title="Aller au classement">'.formatDbData($pp_matches->label).'</a></h3>';
			$content .= $pp_matches->date_prono ? 'Matchs pronostiqués le '.$pp_matches->date_prono : 'Matchs non pronostiqués';
			$content .= '</td>';
			$content .= '<td align="center">';
			$content .= $pp_matches->class;
			$content .= '</td>';
			$content .= '<td align="center">';
			$content .= $pp_matches->nb_points;
			$content .= '</td>';
			$content .= '<td align="center">';
			$content .= $pp_matches->nb_score_ok;
			$content .= '</td>';
			$content .= '<td align="center">';
			$content .= $pp_matches->nb_result_ok;
			$content .= '</td>';
			$content .= '</tr>';	

			$table = $content . $table;
			
			$myStats[$i]['class'] = $pp_matches->class;
			$myStats[$i]['evolution'] = $pp_matches->evolution;	
			
			$i++;
		}
		
		
		$table = '<table border="0" cellspacing="1" cellpadding="4">
					<tr><th width="10%">N°</th>
					<th width="60%" colspan="2">Grilles de matchs concernées<br />par le '.$pp_class_label->label.'</th>
					<th width="10%">Classement</th>
					<th width="10%">Points cumulés</th>
					<th width="10%">Scores justes cumulés</th>
					<th width="10%">Résultats justes cumulés</th></tr>'.
					$table
					.'</table>';
	}
}	


pageheader("Evolution au ".$pp_class_label->label." | Prono+");

if(count($myStats) >= 2) {
?>
<!--[if IE]><script type="text/javascript" src="/lib/flotr/excanvas.js"></script><![endif]-->
<script type="text/javascript" src="/lib/flotr/flotr-0.1.0alpha.js"></script>

<style type="text/css">
.flotr-mouse-value
{
	padding:4px !important;
	background-color:#749028 !important;
	color:#fff !important;
	font-weight:bold !important;
}
</style>
<?
}
?>


<div id="content_fullscreen">
<?
// affichage des onglets
echo getOnglets('classement');
?>
<div id="content">
<h2 class="title_green">Evolution au <?=$pp_class_label->label?></h2>
<p>Graphique de mon évolution au classement. En passant la souris sur les points, le n° de journée et le classement s'affichent.</p>


<?
if(count($myStats) >= 2) {
?>
<div id="container" style="width:98%; height:300px; margin:10px;"></div>

<br />

<? /*<div style="overflow:auto; height:200px; border:1px solid #ccc"> */ ?>
<div>
<? echo $table; ?>
</div>

<p align="center"><a href="/class.php?id=<?=$pp_class_label->id_class?>" class="link_button">Voir le <?=$pp_class_label->label?></a></p>

<?
} else {
?>
<p class="message_error">Il n'y a pour l'instant pas assez de grilles jouées pour afficher le graphique d'évolution pour ce classement !</p>
<?
}
?>

<hr>

<?
// Les autres classements
$SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`type`, `pp_class`.`label`, `pp_info_country`.`label` AS `country`
		FROM `pp_class` LEFT JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_class`.`id_info_country`
		WHERE `pp_class`.`last_id_matches` != 0 AND `pp_class`.`id_class`!='".$_GET[id]."'
		ORDER BY `pp_class`.`type`, `pp_class`.`order`";
$result = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	echo '<h2 class="title_green">Voir l\'évolution d\'un autre classement</h2>';	
	echo "<div>";
	$type_current = '';
  
  if(!$result_matches->numRows())
  {
    ?><p class="message_error">Pas d'autres classements disponibles pour l'instant :(</p><?php
  
  } else {
    echo '<ul>';
    while($pp_class_autres = $result->fetchRow())
    {
      if($type_current != $pp_class_autres->type)
      {
        if($type_current != '') echo '</ul>&nbsp;</li>';
        $type_current = $pp_class_autres->type;
        
        if($type_current == 'year') echo "<li>Classements annuels<ul>";
          else if($type_current == 'month') echo "<li>Classements mensuels<ul>";
      }
      echo "<li><a href=\"classement-evolution.php?id=".$pp_class_autres->id_class."\" class=\"link_orange\">".formatDbData($pp_class_autres->label)."</a></li>";
    }
    echo '</ul></li></ul>';
  }
	echo "</div>";		
}
?>

</div>
</div>


<?
if(count($myStats) >= 2) {
?>
<script type="text/javascript">
document.observe('dom:loaded', function(){
	var d = [];
	var i = 1;
	var maxY = 0;
	var minY = 0;
	
	<? foreach($myStats as $matches) { ?>
	d.push([i,-<?=$matches['class']?>]);
	i++;
	<? } ?>
	
	for(var j=0; j<d.length; j++)
	{
		if(maxY==0 || maxY < d[j][1]) maxY = d[j][1];
		if(minY==0 || minY > d[j][1]) minY = d[j][1];	
	}
		
	Flotr.draw(
		$('container'),
		[{data:d}],
		{
			colors: ['#749028'],
			lines: {
				show: true,
				lineWidth: 2
			},
			points: {
				show: true,
				radius: 3,
				lineWidth: 2,
				fill: true,
				fillColor: '#749028'
			},
			xaxis: {
				tickDecimals: 0,
				min: 0,
				max: d.length + 1
			},
			yaxis: {
				tickDecimals: 0,
				min: minY - 10,
				max: maxY + 1,
				tickFormatter: function(y){ return ''+( -1 * y ); }
			},
			mouse:{
				track: true,
				lineColor: '#749028',
				sensibility: 2,
				trackDecimals: 0,
				trackFormatter: function(obj){ return 'Journée = ' + obj.x +', Classement = ' + (-1 * obj.y); }
			}
		}
	);
});			
</script>
<?
}
?>

<?
pagefooter();
?>