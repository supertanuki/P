<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

pageheader($title ? $title : "L'Equipe de France de football", array('equipe-de-france' => true, 'meta_description' => 'L\'Equipe de France, le calendrier, les résultats et l\'actualité des Bleus.'));
?>

<style>
.match_gagne { color:#749028; font-weight:bold; }
.match_perdu { color:#c63c3c; font-weight:bold; }
.match_nul { color:#555; font-weight:bold; }
</style>

<div id="content_fullscreen">
<?php

// affichage des onglets
echo getOnglets('equipe-de-france');
?>
<div id="content">
	<ul class="list_sortable">
		<?php
		/*
		<li>
			<h2 class="title_blueking">Pronostic des matchs de l'équipe de France</h2>
			<div class="bloc_content">
				<p class="center"><strong>Aucune grille de pronostics n'est en cours pour l'instant.</strong></p>
			</div>
		</li>
		*/
		?>
		
		<li>
			<h2 class="title_blueking">Calendrier de l'équipe de France 2008 - 2010</h2>
			<div class="bloc_content">
<?php
$calendrier = "Suède;<span class=\"match_gagne\">2-3</span>;<strong>France</strong>;Mercredi 20 août 2008 à 21h00;Match amical
Autriche;<span class=\"match_perdu\">3-1</span>;<strong>France</strong>;Samedi 6 septembre 2008 à 20h30;Eliminatoire Coupe du Monde 2010
<strong>France</strong>;<span class=\"match_gagne\">2-1</span>;Serbie;Mercredi 10 septembre 2008 à 21h00;Eliminatoire Coupe du Monde 2010 
Roumanie;<span class=\"match_nul\">2-2</span>;<strong>France</strong>;Samedi 11 octobre 2008 à 20h30;Eliminatoire Coupe du Monde 2010
<strong>France</strong>;<span class=\"match_gagne\">3-1</span>;Tunisie;Mardi 14 octobre 2008 à 21h00;Match amical
<strong>France</strong>;<span class=\"match_nul\">0-0</span>;Uruguay;Mercredi 19 novembre 2008 à 21h00;Match amical
<strong>France</strong>;<span class=\"match_perdu\">0-2</span>;Argentine;Mercredi 11 février 2009 à 21h00;Match amical
Lituanie;<span class=\"match_gagne\">0-1</span>;<strong>France</strong>;Samedi 28 mars 2009;Eliminatoire Coupe du Monde 2010
<strong>France</strong>;<span class=\"match_gagne\">1-0</span>;Lituanie;Mercredi 1er avril 2009;Eliminatoire Coupe du Monde 2010
<strong>France</strong>;<span class=\"match_perdu\">0-1</span>;Nigéria;Mardi 2 juin 2009 à 21h00;Match amical
<strong>France</strong>;<span class=\"match_gagne\">1-0</span>;Turquie;Vendredi 5 juin 2009 à 21h00;Match amical
Iles Féroé;<span class=\"match_gagne\">0-1</span>;<strong>France</strong>;Mercredi 19 août 2009;Eliminatoire Coupe du Monde 2010
<strong>France</strong>;<span class=\"match_nul\">1-1</span>;Roumanie;Samedi 5 septembre 2009;Eliminatoire Coupe du Monde 2010
Serbie;<span class=\"match_nul\">1-1</span>;<strong>France</strong>;Mercredi 9 septembre 2009;Eliminatoire Coupe du Monde 2010
<strong>France</strong>;<span class=\"match_gagne\">5-0</span>;Iles Féroé;Samedi 10 octobre 2009;Eliminatoire Coupe du Monde 2010
<strong>France</strong>;<span class=\"match_gagne\">3-1</span>;Autriche;Mercredi 14 octobre 2009;Eliminatoire Coupe du Monde 2010
Irlande;<span class=\"match_gagne\">0-1</span>;<strong>France</strong>;Samedi 14 novembre 2009;Barrage Aller Coupe du Monde 2010
<strong>France</strong>;<span class=\"match_nul\">1-1</span>;Irlande;Mercredi 18 novembre 2009;Barrage Retour Coupe du Monde 2010
<strong>France</strong>;<span class=\"match_perdu\">0-2</span>;Espagne;Mercredi 3 mars 2010 à 21h00;Match amical
<strong>France</strong>;<span class=\"match_gagne\">2-1</span>;Costa Rica;Mercredi 26 mai 2010 à 21h00;Match amical
Tunisie;<span class=\"match_nul\">1-1</span>;<strong>France</strong>;Dimanche 30 mai 2010;Match amical
<strong>France</strong>;<span class=\"match_perdu\">0-1</span>;Chine;Vendredi 4 juin 2010;Match amical
Uruguay;<span class=\"match_nul\">0-0</span>;<strong>France</strong>;Vendredi 11 juin 2010;<strong>Coupe du Monde 2010</strong>
<strong>France</strong>;<span class=\"match_perdu\">0-2</span>;Mexique;Jeudi 17 juin 2010;<strong>Coupe du Monde 2010</strong>
<strong>France</strong>;<span class=\"match_perdu\">1-2</span>;Afrique du Sud;Mardi 22 juin 2010;<strong>Coupe du Monde 2010</strong>";
?>
			<table width="100%" cellpadding="4" cellspacing="1" border="0">
			<?php
			$matches = explode("\n", $calendrier);
			foreach($matches as $match)
			{
				$info_match = explode(';', $match);
				if($altern) {
					$class_line = 'ligne_grise';
					$altern = 0;
				} else {
					$class_line = 'ligne_blanche';
					$altern = 1;
				}
			?>
				<tr class="<?php echo $class_line?>" onmouseover="this.className='ligne_rollover'" onmouseout="this.className='<?php echo $class_line?>'">
					<td align="right" width="12%" nowrap="nowrap"><?php echo $info_match[0]?></td>
					<td align="center" width="10%"><?php echo $info_match[1]?></td>
					<td width="12%" nowrap="nowrap"><?php echo $info_match[2]?></td>
					<td width="30%"><?php echo $info_match[3]?></td>
					<td width="36%"><?php echo $info_match[4]?></td>
				</tr>
			<?php
			}
			?>
			</table>
			</div>
		</li>

		
<?php
/*
$classement = "1.  	    SERBIE;  	22; 	10; 	7; 	1; 	2; 	22; 	8; 	+14
2.  	  FRANCE; 	21; 	10; 	6; 	3; 	1; 	18; 	9; 	+9
3.  	  AUTRICHE; 	14; 	10; 	4; 	2; 	4; 	14; 	15; 	-1
4.  	  LITUANIE; 	12; 	10; 	4; 	0;	6; 	10; 	11; 	-1
5.  	  ROUMANIE; 	12; 	10; 	3; 	3; 	4; 	12; 	18;	-6
6.  	  ILES FEROE; 	4; 	10; 	1; 	1; 	8; 	5; 	20; 	-15";
?>
		<li>
			<h2 class="title_blueking">Classement de l'équipe de France dans les éliminatoires pour la Coupe du Monde 2010</h2>
			<div class="bloc_content">
			<p>L'équipe de France se qualifie pour la Coupe du Monde 2010 au dépend de l'Irlande grâce à une victoire à Dublin (1-0) et un match nul après prolongation au Stade de France (1-1). Ce match retour sera marqué à tout jamais par un match médiocre de l'Equipe de France et un contrôle de la main de Thierry Henry amenant l'égalisation et donc la qualification.</p>
			<p><table align="center" cellpadding="8" cellspacing="1" border="0">
				<tr>
					<th>Pays</th>
					<th>Pts</th>
					<th>J</th>
					<th>G</th>
					<th>N</th>
					<th>P</th>
					<th>BP</th>
					<th>BC</th>
					<th>Diff</th>
				</tr>
			<?php
			$equipes = explode("\n", $classement);
			foreach($equipes as $equipe)
			{
				$info_equipe = explode(';', $equipe);
				if($altern) {
					$class_line = 'ligne_grise';
					$altern = 0;
				} else {
					$class_line = 'ligne_blanche';
					$altern = 1;
				}
			?>
				<tr class="<?php echo $class_line?>" onmouseover="this.className='ligne_rollover'" onmouseout="this.className='<?php echo $class_line?>'">
					<td><?php echo $info_equipe[0]?></td>
					<td align="center"><?php echo $info_equipe[1]?></td>
					<td align="center"><?php echo $info_equipe[2]?></td>
					<td align="center"><?php echo $info_equipe[3]?></td>
					<td align="center"><?php echo $info_equipe[4]?></td>
					<td align="center"><?php echo $info_equipe[5]?></td>
					<td align="center"><?php echo $info_equipe[6]?></td>
					<td align="center"><?php echo $info_equipe[7]?></td>
					<td align="center"><?php echo $info_equipe[8]?></td>
				</tr>
			<?php
			}
			?>
			</table></p>
			</div>
		</li>
		<?php
		*/
		?>
		
		
		
	
		<li>
			<h2 class="title_blueking">L'équipe de France sur le blog</h2>
			<div class="bloc_content">
			<?php echo PreviewBlog(array(	'category_id' => 12,
										'twocolumns' => true,
										'nbelements' => 8)); ?>
			</div>
		</li>
	
		<li>
			<h2 class="title_blueking">Les derniers messages du forum Equipe de France</h2>
			<div class="bloc_content">	
	<table width="100%" border="0" cellspacing="1" cellpadding="4">
		<tr>		  
		  <th width="50%">Sujets</th>
		  <th width="20%">Dernier message</th>
		</tr>
<?php
$resmsg=mysql_query("SELECT forum.* FROM forum 
					WHERE Nquest=0 AND forum.supp=0 AND forum.bloque=0 AND forum.id_forum_theme=3
					ORDER BY dateder DESC LIMIT 5");
$color="aaaaaa";
while($lmsg=mysql_fetch_array($resmsg)) {
	$resrep=mysql_query("select Nmsg from forum where Nquest=".$lmsg["Nmsg"]);
	if($color=="eeeeee") {
		$color="ffffff";
	} else {
		$color="eeeeee";
	}
?>

		<tr bgcolor="#<?php echo $color?>">
		  <td><a href="/forum-football/<?php echo $lmsg["url"]?>-<?php echo $lmsg["Nmsg"]?>.html" class="link_orange">
			<?php
			echo htmlspecialchars($lmsg["sujet"]);
			?>
			</a><br />
			<?php //pages
$nbmsg=mysql_query("select Nmsg from forum where Nmsg=".$lmsg["Nmsg"]." or Nquest=".$lmsg["Nmsg"]);
$nbtotalmsg=mysql_num_rows($nbmsg);
$nbaff=10;

$nbpage=ceil($nbtotalmsg/$nbaff);
if($nbtotalmsg > 10) {
	?>
			[ 
			<?php
	for($i=1; $i<=$nbpage; $i++) {
		$ldeb=(($i-1)*$nbaff);
		?>
			<a href="/forum-football/<?php echo $lmsg["url"]?>-<?php echo $lmsg["Nmsg"]?><?php echo $ldeb>0?"page".$ldeb:""?>.html" class="link_orange">
			<?php echo $i?>
			</a> 
			<?	if($i!=$nbpage) echo ".";
	} ?>
			]
			<?php } ?>
			</td>
		  <td>
		  <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
	<td><div align="center"><font size="1">
	<?php echo formatdateheure($lmsg["dateder"])?></font>
	</div></td>
	<?php // dernier message du topic
	$resdermsg=mysql_query("select Nmsg from forum where Nmsg=".$lmsg["Nmsg"]." or Nquest=".$lmsg["Nmsg"]." order by Nmsg desc");
	$lnmsg=mysql_fetch_row($resdermsg);
	?>
				<td width="23" valign="middle"><a href="/forum-football/<?php echo $lmsg["url"]?>-<?php echo $lmsg["Nmsg"]?><?php echo ($nbpage*10-10)>0?"page".($nbpage*10-10):""?>.html#mess<?php echo $lnmsg[0]?>"><img src="/template/default/last.gif" alt="Aller au dernier message" width="16" height="16" hspace="2" border="0" align="absmiddle"></a></td>
  </tr>
</table>
 
		  </td>
		</tr>
		<?php } ?>
	  </table>
	 	 </div>
	  	</li>
	</ul>
	  
	  
	  
	</div>
</div>

<?php
pagefooter();
?>