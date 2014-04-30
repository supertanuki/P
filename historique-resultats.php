<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();
if(!$user) HeaderRedirect('/');
 

$nb_begin = $_GET['start']>0 ? 1 * $_GET['start'] : 0;
$nb_limit = 20;
$nb_step = 10;
$id_user = $user->id_user;

$myStats = array();



$nb_matches = 0;

// nb de grilles
$SQL = "SELECT `pp_matches`.`id_matches`
		FROM `pp_matches`
		INNER JOIN `pp_matches_user` ON `pp_matches_user`.`id_matches`=`pp_matches`.`id_matches` AND `pp_matches_user`.`id_user`='".$id_user."'
		WHERE `pp_matches`.`is_calcul`='1' AND `pp_matches`.`id_cup_matches`=0";
$result_matches = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result_matches->getMessage());
	
} else {
	$nb_matches = $result_matches->numRows();
}


// redirection vers les plus récents
if(!isset($_GET['start']) && $nb_matches > ($nb_begin + $nb_limit))
{
	$paramLimit = "start=".($nb_matches - $nb_limit);
	$paramLimit = ($paramLimit != '' ? '?'.$paramLimit : '');
	HeaderRedirect("/historique-resultats.php".$paramLimit);
}




$content  = '';
$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`image`,
		DATE_FORMAT(`pp_matches_user`.`date_creation`, '".$txtlang['AFF_DATE_SQL']."') AS `date_prono`
		FROM `pp_matches`
		INNER JOIN `pp_matches_user` ON `pp_matches_user`.`id_matches`=`pp_matches`.`id_matches` AND `pp_matches_user`.`id_user`='".$id_user."'
		WHERE `pp_matches`.`is_calcul`='1' AND `pp_matches`.`id_cup_matches`=0
		ORDER BY `pp_matches`.`date_calcul`
		LIMIT $nb_begin, $nb_limit";
$result_matches = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result_matches->getMessage());
	
} else {
	if($result_matches->numRows())
	{
		$content .= '<table border="0" cellspacing="1" cellpadding="4">';
		$content .= '<tr><th width="10%">Id</th>';
		$content .= '<th width="60%" colspan="2">Grilles de matchs</th>';
		$content .= '<th width="10%" nowrap>Classement</th>';
		$content .= '<th width="10%" nowrap>Points</th>';
		$content .= '<th width="10%" nowrap>Points moyens</th></tr>';
		
		
		$i = $nb_begin+1;
		
		while($pp_matches = $result_matches->fetchRow())
		{	
		
			$class_user = array();
			$SQL = "SELECT `class`, `nb_points`
					FROM `pp_class_user`
					WHERE `id_user`='".$user->id_user."' AND `id_class`=1 AND `id_matches`='".$pp_matches->id_matches."'";
			$result_class = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_class))
			{
				die ("<li>ERROR : ".$result_class->getMessage());
				
			} else {	
				while($pp_class = $result_class->fetchRow())
				{
					$class_user['class'] = $pp_class->class;
					$class_user['nb_points'] = $pp_class->nb_points;
				}
			}
			
			$SQL = "SELECT AVG(`nb_points`) AS `sum_nb_points`
					FROM `pp_class_user`
					WHERE `id_class`=1 AND `id_matches`='".$pp_matches->id_matches."'";
			$result_class = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_class))
			{
				die ("<li>ERROR : ".$result_class->getMessage());
				
			} else {	
				while($pp_class = $result_class->fetchRow())
				{
					$class_user['sum_nb_points'] = $pp_class->sum_nb_points;
				}
			}
					
			if($altern) {
				$class_line = 'ligne_grise';
				$altern = 0;
			} else {
				$class_line = 'ligne_blanche';
				$altern = 1;
			}

			$content .= '<tr class="'.$class_line.'">';
			$content .= '<td align="center">'.$i.'</td>';
			$content .= '<td><a href="classj.php?id='.$pp_matches->id_matches.'"><img src="template/default/'.$pp_matches->image.'" style="border:solid 3px #eee;" height="50" width="35" /></a></td>';
			$content .= '<td><h3><a href="classj.php?id='.$pp_matches->id_matches.'" title="Aller au classement">'.formatDbData($pp_matches->label).'</a></h3>';
			$content .= 'Matchs pronostiqués le '.$pp_matches->date_prono;
			$content .= '</td>';
			$content .= '<td align="center">';
			$content .= '<a href="classj.php?id='.$pp_matches->id_matches.'&rech_jpseudo='.urlencode($user->login).'&search_joueur=1" class="link_orange" style="display:block; width:100%" title="Aller à mon classement">'.$class_user['class'].'</a>';
			$content .= '</td>';
			$content .= '<td align="center">';
			$content .= '<a href="classj.php?id='.$pp_matches->id_matches.'&rech_jpseudo='.urlencode($user->login).'&search_joueur=1" class="link_orange" style="display:block; width:100%" title="Aller à mon classement">'.$class_user['nb_points'].'</a>';
			$content .= '</td>';
			$content .= '<td align="center">';
			$content .= round($class_user['sum_nb_points']);
			$content .= '</td>';
			$content .= '</tr>';
			
			
			$tooltip = '';
			$tooltip .= '<table width="100%" border="0" cellspacing="1" cellpadding="4">';
			$tooltip .= '<tr>';
			$tooltip .= '<td><a href="classj.php?id='.$pp_matches->id_matches.'"><img src="template/default/'.$pp_matches->image.'" style="border:solid 3px #eee;" height="50" width="35" /></a></td>';
			$tooltip .= '<td><a href="classj.php?id='.$pp_matches->id_matches.'" style="font-size:13px; font-weight:bold; padding:3px 4px 4px 0px; color:#ED8E00; text-decoration:none;">_matches_label_</a><br />';
			$tooltip .= '_matches_date_prono_';
			$tooltip .= '<br />';
			$tooltip .= 'Classement : '.$class_user['class'].'&nbsp;&nbsp;&nbsp;';
			$tooltip .= 'Points : '.$class_user['nb_points'].'<br />';
			$tooltip .= 'Points moyens tous joueurs : '.round($class_user['sum_nb_points']);
			$tooltip .= '</td></tr>';
			$tooltip .= '</table>';
			
			$tooltip = htmlentities($tooltip);
			$tooltip = str_replace("_matches_label_", formatDbData($pp_matches->label), $tooltip);
			$tooltip = str_replace("_matches_date_prono_", "Matchs pronostiqués le ".$pp_matches->date_prono, $tooltip);			 
			
			$myStats[$i]['nb_points'] = $class_user['nb_points'];
			$myStats[$i]['sum_nb_points'] = $class_user['sum_nb_points'];
			$myStats[$i]['tooltip'] = $tooltip;
			
			$i++;	
		}
		$content .= '</table>';
	}
}	


pageheader("Historique des résultats | Prono+");
?>
<!--[if IE]><script type="text/javascript" src="/lib/flotr/excanvas.js"></script><![endif]-->
<script type="text/javascript" src="/lib/flotr/flotr-0.1.0alpha.js"></script>

<script type="text/javascript" src="/lib/mktooltip/mktooltip.js"></script> 

<style type="text/css">
.mktipmsg {padding: 5px; background-color: #eeeeee;  border: 1px solid #bbbbbb; width:320px;font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #555555; display:none; position:absolute;left:0px;top:0px; }
</style>
<div id="mktipmsg" class="mktipmsg"></div>


<div id="content_fullscreen">
<?php
// affichage des onglets
echo getOnglets();
?>
<div id="content">
<h1 class="title_green">Historique des résultats</h1>
<p>Grille par grille, comparaison de mes résultats avec les points moyens réalisés par tous les joueurs.</p><br />

<?php
if(!$nb_matches)
{
?>
<p class="message_error">Aucune stat pour l'instant :(</p>
<?php 
} else {
?>


<div id="container" style="width:98%; height:300px; margin:10px;"></div>

<div align="center">
<? if($nb_begin > 0) {
		$param = (($nb_begin - $nb_step) > 0 ? "start=".($nb_begin - $nb_step) : "start=0");
		$param = ($param != '' ? '?'.$param : '');
?>
<a href="/historique-resultats.php?start=0" class="link_button">&lt;&lt; Les plus anciens</a>
<a href="/historique-resultats.php<?php echo $param?>" class="link_button">&lt; <?php echo (($nb_begin - $nb_step) >= 0 ? $nb_step : $nb_step + ($nb_begin - $nb_step)); ?> précédents</a>
<? } ?>

<? if($nb_matches > ($nb_begin + $nb_limit)) {
		$param = "start=".(($nb_begin + $nb_step + $nb_limit) > $nb_matches ? $nb_begin + ($nb_matches - ($nb_begin + $nb_limit)) : ($nb_begin + $nb_step));
		$param = ($param != '' ? '?'.$param : '');
		
		$paramLimit = "start=".($nb_matches - $nb_limit);
		$paramLimit = ($paramLimit != '' ? '?'.$paramLimit : '');
?>
<a href="/historique-resultats.php<?php echo $param?>" class="link_button"><?php echo ($nb_begin + $nb_step + $nb_limit) > $nb_matches ? ($nb_matches - ($nb_begin + $nb_limit)) : $nb_step; ?> suivants &gt;</a>
<a href="/historique-resultats.php<?php echo $paramLimit?>" class="link_button">Les plus récents &gt;&gt;</a>
<? } ?>
</div>

<br />

<div style="overflow:auto; height:200px; border:1px solid #ccc">
<? echo $content; ?>
</div>



<script type="text/javascript">
document.observe('dom:loaded', function(){
	var d1 = [];
	var d2 = [];
	var i = <?php echo $nb_begin+1?>;
	var tooltip = [];
	
	<? foreach($myStats as $matches) { ?>
	d1.push([i,<?php echo $matches['nb_points']?>]);
	d2.push([i,<?php echo $matches['sum_nb_points']?>]);
	tooltip[i] = '<?php echo str_replace("'", "\\\'", $matches['tooltip'])?>';
	i++;
	<? } ?>
    
    for(var j=d1.length; j<<?php echo $nb_limit?>; j++)
    {
    	d1.push([j, 0]);
        d2.push([j, 0]);
    }
	
	Flotr.draw(
		$('container'),
		[{data:d1, label:'Points'}, {data:d2, label:'Points moyens'}],
		{
			colors: ['#00aa00', '#555555', '#cb4b4b', '#4da74d', '#9440ed'],
			bars: {show:true},
			xaxis: {
				noTicks: <?php echo $nb_limit?>,
				tickDecimals: 0,
				tickFormatter: function(nb)
				{
					if(nb > <?php echo $nb_begin+$nb_limit?>) return '';
					return '<a class="link_orange" style="display:block; padding-left:30px;" href="javascript:" onmouseover="showtip(event, \''+tooltip[nb]+'\');" onmouseout="hidetip();">'+nb+'</a>';
				}
			},
			yaxis: {
				noTicks: 10,
				tickDecimals: 0,
				min: 0,
				max: 1000
			},
			legend:{
				show: true,
				noColumns: 1,
				labelBoxBorderColor: '#000000',
				position: 'ne',
				margin: 5,
				backgroundColor: '#dddddd',
				backgroundOpacity: 0.85
			}
		}
	);
});			
</script>

<?php
}
?>
</div>
</div>
<?php

pagefooter();
?>