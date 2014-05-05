<?php
/**
* Project: PRONOPLUS
* Description: Fonctions d'affichage de contenus
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-07-27
* Version: 1.0
*/


function pageheader($title, $options=false)
{
	global $user;
	@header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
	@header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	@header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
	@header( 'Pragma: no-cache' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $title?></title>

<meta name="description" content="<?php echo $options[meta_description]; ?>" />
<?php if($options[meta_keywords]) { ?><meta name="keywords" content="<?php echo $options[meta_keywords]; ?>"><?php } ?>


<link rel="stylesheet" href="/template/default/styles.css?v=2.5" type="text/css" media="all" />

<?php
// sapin
if( date("m") == 12) { ?>
<style>
.logo {
    background: url(/template/default/sapin.png) no-repeat 350px 0px;
}
</style>
<?php } ?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.1/prototype.js"></script>
<script type="text/javascript" src="/lib/scriptaculous-js-1.8.1/scriptaculous.js"></script>
<script type="text/javascript" src="/mainfunctions.js?v=1.7"></script>
<script type="text/javascript" src="/lib/flash/flash.js"></script>

<script type='text/javascript' src='/lib/prototip2.2.0.2/js/prototip/prototip.js'></script>
<link rel="stylesheet" type="text/css" href="/lib/prototip2.2.0.2/css/prototip.css" />
</head>

<body>

<?php
if($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
{
    echo '<div style="padding:4px; background:#555; color:#fff">Version développement</div>';
}
?>

<div id="all">
	<div id="header_links">		
		<ul>
		<?php if(!$user) { ?>
			<li class="header_link_grey"><a id="login_link" href="javascript:" onclick="SeConnecter(this);">Se connecter</a></li>
			<li class="header_link_green" id="btn_inscrire"><a href="javascript:" onclick="Sinscrire(this);">S'inscrire</a></li>
		<?php } else {
				echo "<li class=\"header_link_green\"><div style=\"display:block; width:150px; overflow:hidden; text-align:left; color:#fff\">";

				if($avatar = getAvatar($user->id_user, $user->avatar_key, $user->avatar_ext, 'small'))
				{
				?><div style="float:left;"><img src="/avatars/<?php echo $avatar?>" height="29" width="29" border="0" align="absmiddle" style="border-right:solid 1px #fff" /></div><?php
				}
				echo "<div style=\"float:left; padding:9px 5px 6px 5px; font-weight:bold; width:105px; overflow:hidden;\"><a href=\"/profil.php\" style=\"display:inline\">".$user->login."</a>&nbsp;<a href=\"/logout.php\" class=\"logout_link\" title=\"Se déconnecter\" onclick=\"return confirm('Souhaitez-vous vous déconnecter ?');\"><img src=\"/template/default/close.gif\" height=\"12\" width=\"12\" border=\"0\" align=\"absmiddle\" /></a></div>";
				echo "<div class=\"clear\"></div>";
				echo "</div></li>";
			} ?>
			<!--<li class="header_link_mobile"><a href="/mobile/">Mobile</a></li>
			<li class="header_link_orange"><a href="/blog/">Blog</a></li>
			<li class="header_link_blue"><a href="/forum-football/">Forum</a></li>-->
		</ul>
		<div style="display:block; width:300px; height:12px; overflow:hidden; color:#666666; font-size:12px; font-weight:bold; margin:0; padding:5px 5px 2px 5px;">
		<?php echo ($title=="Prono+, Pronostic de foot" ? "Jeu gratuit de pronostics de football" : str_replace('| Prono+', '', $title) ); ?>
		</div>
	</div>
	
	<div id="header_window" style="display:none"></div>
	
	<div id="header_space"></div>
	
	<?php if($options['equipe-de-france'] == true) { ?>
	<div class="logo_edf"><a href="/" onfocus="if(this.blur()) this.blur();">PRONO+</a></div>
	<?php } else { ?>
	<div class="logo"><a href="/" onfocus="if(this.blur()) this.blur();">PRONO+</a></div>	
	<?php } ?>
	

	
	<?php
	if(navigator_is_mobile())
	{
		?>
		<p align="center" style="padding: 10px; background: none repeat scroll 0% 0% rgb(255, 255, 170); font-size:24px;"><b>
			Tu es sur ton mobile ?<br /><a href="/mobile/" class="link_orange">Utilise la version mobile de Prono+</a> !
		</b></p>
		<?php
	} else {
		?>
		<noscript><p align="center" style="padding: 10px; background: none repeat scroll 0% 0% rgb(255, 255, 170);"><b>Ce site nécessite JavaScript pour fonctionner intégralement.<br> Merci de l'activer dans votre navigateur.</b></p></noscript>
		<?php
	}
	?>
<?php
}

function listGrillesEnCours()
{
	global $db, $user;
  
  // Grilles de pronostics en cours
  $SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`id_cup_matches`
      FROM `pp_matches`
      WHERE `pp_matches`.`is_calcul`!='1' 
      ORDER BY `date_first_match`";
  $result_matches = $db->query($SQL);
  //echo "<li>$SQL";
  if(DB::isError($result_matches))
  {
    die ("<li>ERROR : ".$result_matches->getMessage());
    
  } else {
    if($result_matches->numRows())
    {
      $html = '';      
      while($pp_matches = $result_matches->fetchRow())
      {
        if($pp_matches->id_cup_matches)
        {
          $SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label`, `pp_cup_matches`.`id_cup_matches`, `pp_cup_matches`.`number_tour`
              FROM `pp_cup_matches`
              INNER JOIN `pp_cup` ON `pp_cup`.`id_cup` = `pp_cup_matches`.`id_cup`
              ORDER BY `pp_cup_matches`.`id_cup` DESC, `pp_cup_matches`.`number_tour` DESC
              LIMIT 1";
          $result_cup_matches = $db->query($SQL);
          //echo "<li>$SQL";
          if(DB::isError($result_cup_matches))
          {
            die ("<li>ERROR : ".$result_cup_matches->getMessage()."<li>$SQL");
            
          } else {
            if($pp_cup_matches = $result_cup_matches->fetchRow())
            {
              //<a href=\"cup.php?id=".$pp_cup->id_cup."\"><img src=\"template/default/coupe.png\" class=\"preview_matches_image\" border=\"0\" />
              $html .= "<li><a href=\"/cup.php?id=".$pp_cup_matches->id_cup."\">".formatDbData($pp_cup_matches->label)." - ".getCupTourLabel($pp_cup_matches->number_tour)."</a></li>";
            }
          }
          
        } else {
          $html .= "<li><a href=\"/classj.php?id=".$pp_matches->id_matches."\">".formatDbData($pp_matches->label)."</a></li>";
        }
      }
      
      if($html)
      {
        $html = '<strong>Grilles de pronostics en cours</strong><ul>' . $html . '</ul>';
      }
    }
  }
  
  return $html;
}


function listDerniersResultats()
{
	global $db, $user;
  
  $html = '';
  
  $SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`
      FROM `pp_matches`
      WHERE `pp_matches`.`is_calcul`='1' AND `pp_matches`.`id_cup_matches`=0
      ORDER BY `date_calcul` DESC
      LIMIT 6";
  $result_matches = $db->query($SQL);
  //echo "<li>$SQL";
  if(DB::isError($result_matches))
  {
    die ("<li>ERROR : ".$result_matches->getMessage());
    
  } else {
    if($result_matches->numRows())
    {
      $html = '';
      while($pp_matches = $result_matches->fetchRow())
      {
        $html .= '<li><a href="/classj.php?id='.$pp_matches->id_matches.'">'.formatDbData($pp_matches->label).'</a></li>';
      }
      if($html) $html =  '<strong>Les derniers résultats</strong><ul>' . $html . '</ul>';
    }
  }
  
  return $html;
}



function listStatsMenu()
{
  global $db;
  $html = '';
  
  // Liste championnats
  $SQL = "SELECT id_league, label, flag
      FROM `pp_league`
      WHERE `afficher_classement`='1'
        AND id_league IN (SELECT DISTINCT id_league FROM `pp_info_matches`)
      ORDER BY `ordre`";
  $result = $db->query($SQL);
  //echo "<li>$SQL";
  if(DB::isError($result))
  {
    die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
    
  } else {
    $html .= '<strong>Résultats et classements des championnats</strong><ul>';
    while($pp_league_info = $result->fetchRow())
    {
      $html .= '<li><a href="/stats-classement.php?id='.$pp_league_info->id_league.'"><img src="/image/flags/'.$pp_league_info->flag.'" border="0" align="absmiddle" />&nbsp;'.$pp_league_info->label.'</a></li>';
    }
    $html .= '</ul><br />';
  }
  
  
  $html .= '<strong>Stats Prono+</strong>
            <ul>
              <li><a href="/historique-resultats.php">Historique des résultats</a></li>
                <li><a href="/classement-evolution.php">Evolution aux classements</a></li>
                <li><a href="/classement-meilleurs-scores.php">Meilleurs scores</a></li>
                <li><a href="/palmares.php">Palmarès Prono+</a></li>
                <li><a href="/palmares-archives.php">Archives palmarès Prono+</a></li>
                <li><a href="/stats-pronostics.php">Statistiques des scores et pronostics</a></li>
            </ul>';
        
  return $html;
}



function pagefooter($options=false)
{
	global $db, $user;
?>
	<!--footer-->
	<div class="clear"></div>
	<div id="whosonline">
	<?php /*if(!$user && ($_SERVER[REQUEST_URI]=='/' || $_SERVER[REQUEST_URI]=='' || $_SERVER[REQUEST_URI]=='/index.php')) { // si on est sur la home ?>
		<div style="float:right; width:230px; border:1px solid #ccc; padding:6px; margin-left:10px;">
			Passé pro dans l'art des pronostics du ballon rond grâce à Pronoplus?  Faites confiance à Betway pour vos prochains <a href="http://betway.com/fr" target="_blank" class="link_orange">paris sportifs</a> ou détendez vous en jouant à un des nombreux <a href="http://betway.com/fr/casino" target="_blank" class="link_orange">jeux de casino</a> spécial foot.
		</div>
	<?php }*/ ?>
    <img src="/template/default/whosonline.png" height="25" width="37" border="0" alt="" align="absmiddle" style="margin-right:6px;" /><strong>Qui est en ligne ?</strong>
    
<?php
$nb_online = 0;
$content = '';
$SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`last_cnx`, `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`
		FROM `pp_user`
		WHERE DATE_ADD(`pp_user`.`last_cnx`, INTERVAL 5 minute) > NOW()
		ORDER BY `pp_user`.`last_cnx` DESC";
$result_pp_user = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result_pp_user))
{
	die ("<li>ERROR : ".$result_pp_user->getMessage()."<li>$SQL");
	
} else {
	$nb_online = $result_pp_user->numRows();
	while($pp_user = $result_pp_user->fetchRow())
	{
		$avatar = getAvatar($pp_user->id_user, $pp_user->avatar_key, $pp_user->avatar_ext, 'small');
		$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
		
		$content .= "<a href=\"/user.php?q=".urlencode(htmlspecialchars($pp_user->login))."\" class=\"link_button\" style=\"margin:0 4px 4px 0\"><img src=\"".$avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" />&nbsp;".str_replace(' ', '&nbsp;', $pp_user->login)."</a>";
	}
}

if($content=='')
	echo "Personne à part vous n'est en ligne, ils doivent certainement tous dormir !!!";
else echo $nb_online . " joueur" .($nb_online > 1 ? "s" : ""). " en ligne<hr>" . $content;
?>
    <div class="clear"></div>
    </div>
    
    
    
    
	<div class="clear"></div>
	<div id="footer_links">
    	<img src="/template/default/logo-footer.png" height="40" width="300" border="0" /><br /><br />
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
        	<td valign="top" width="33%">
            <strong><a href="/">Accueil</a></strong>
            <ul>
                <li><a href="/forum-football/">Forum football</a></li>
                <li><a href="/blog/">Blog foot</a></li>
				
				<li><a href="/cup.php">Coupe Prono+</a></li>
                
                <?php if($user->id_user) { ?>
					<li><a href="/user.php?q=<?php echo htmlspecialchars(urlencode($user->login));?>">Mon profil</a></li>
					<li><a href="/profil.php">Modifier mon profil</a></li>					
				<?php } else if(!$options['forblog']) { ?>
					<li><a href="javascript:" onclick="SeConnecter(this);">Se connecter à Prono+</a></li>
					<li><a href="javascript:" onclick="Sinscrire(this);">S'inscrire gratuitement à Prono+</a></li>
                <?php } ?>
                <li><a href="/friends.php">Mes amis</a></li>
				<?php /* <li><a href="http://recherche.pronoplus.com/">Rechercher</a></li> */ ?>
				<li><a href="http://www.pronoplus.com/mobile/">Version iPhone / Android</a></li>
                <li><a href="/blog/contact">Contacter le webmaster</a></li>
            </ul>
            
            
<?php
$SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`label`
		FROM `pp_class`
		WHERE `pp_class`.`last_id_matches` != 0
		ORDER BY `pp_class`.`order`";
$result = $db->query($SQL);
//echo "<li>$SQL";
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
	
} else {
	echo "<strong><a href=\"/classements.php\">Classements</a></strong>";	
	echo "<ul>";
	echo "<li><a href=\"/classements.php\">Tous les classements</a></li>";
	while($pp_class = $result->fetchRow())
	{
		echo "<li><a href=\"/class.php?id=".$pp_class->id_class."\" class=\"link_orange\">".formatDbData($pp_class->label)."</a></li>";
	}
	echo "</ul>";
}
?>
            </td>
            <td class="footer_separator">&nbsp;</td>
        	<td valign="top" width="33%">
			
			<?php
			// Liste championnats
			$html = '';
      $SQL = "SELECT id_league, label, flag
          FROM `pp_league`
          WHERE `afficher_classement`='1'
            AND id_league IN (SELECT DISTINCT id_league FROM `pp_info_matches`)
          ORDER BY `ordre`";
			$result = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result))
			{
				die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
				
			} else {
				echo '<strong>Résultats et classements des championnats</strong><ul style="list-style-type:none; padding-left:20px;">';
				while($pp_league_info = $result->fetchRow())
				{
					echo '<li><a href="/stats-classement.php?id='.$pp_league_info->id_league.'"><img src="/image/flags/'.$pp_league_info->flag.'" border="0" align="absmiddle" />&nbsp;'.$pp_league_info->label.'</a></li>';
				}
				echo '</ul>';
			}
			
			
			echo listGrillesEnCours();
      
      echo listDerniersResultats();

			?>           
            <strong>Statistiques</strong>
            <ul>
            	<li><a href="/historique-resultats.php">Historique des résultats</a></li>
                <li><a href="/classement-evolution.php">Evolution aux classements</a></li>
				<li><a href="/classement-meilleurs-scores.php">Meilleurs scores</a></li>
                <li><a href="/palmares.php">Palmarès Prono+</a></li>
                <li><a href="/palmares-archives.php">Archives palmarès Prono+</a></li>
                <li><a href="/stats-pronostics.php">Statistiques des scores et pronostics</a></li>
            </ul>
            </td>
            <td class="footer_separator">&nbsp;</td>
            <td valign="top" width="33%">
<?php
	// FORUMS
	$SQL = "SELECT `id_forum_theme`, `label`, `description`, `url` FROM `forum_theme` ORDER BY `order`";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage());
		
	} else {
		echo "<strong><a href=\"/forum-football/\">Forum football</a></strong><ul>";
		while($forum_theme = $result->fetchRow())
		{
			echo "<li><a href=\"/forum-football/".$forum_theme->url."/\">".$forum_theme->label."</a></li>";
		}
		echo "</ul>";
	}
?>




			<strong>Auto-promo</strong>
				<ul>
					<li><a href="http://www.richardhanna.fr/" title="Développeur Web, Web Mobile, PHP/Mysql, Symfony2, Wordpress">Développeur Web, Web Mobile, PHP/Mysql, Symfony2, Wordpress</li>
				</ul>

			<strong>Sites amis</strong>
				<ul>
					<li><a href="http://www.murties.com/">Jeu en ligne</a> gratuit</li>
					<?php /*<li><a href="http://www.wincomparator.com/" title="Paris sportif">Paris sportif</a></li>*/ ?>
					<li><a href="/paris-en-direct/">Présentation des paris sportifs</a></li>
					<?php /*<li><a href="/machine-a-sous/">Machines à sous</a></li>*/ ?>
				</ul>
            </td>
		</tr>
        </table>
    </div>	
</div>

<?php
	if(!$options['forblog']) {
	
		if(!$user) {
			?>
			<script type="text/javascript" language="javascript">
			// <![CDATA[
			document.observe('dom:loaded', function() {
				new Tip('btn_inscrire', '<div style="color:#749028; font-size:14px; text-align:center;"><b>Pas encore inscrit ?<br />Inscrivez-vous gratuitement !</b></div>', {
					style: 'protogrey',
					stem: 'topMiddle',
					hook: { target: 'bottomMiddle', tip: 'topMiddle' },
					offset: { x: 0, y: 0 },
					radius: 5
				});
				$('btn_inscrire').prototip.show();
			});
			// ]]>
			</script>
			<?php
		}
	
		?>
		<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
		var pageTracker = _gat._getTracker("UA-3657249-1");
		pageTracker._initData();
		pageTracker._trackPageview();
		</script>
		
		<!--/footer-->
		</body>
		</html>
		<?php
	}
}


function listClassements()
{
	global $user, $db;
  
  // Les autres classements
  $SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`type`, `pp_class`.`label`
      FROM `pp_class`
      WHERE `pp_class`.`last_id_matches` != 0
      ORDER BY `pp_class`.`type`, `pp_class`.`order`";
  $result = $db->query($SQL);
  if(DB::isError($result))
  {
    die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
    
  } else {
    
    $html = '';
    $type_current = '';
    while($pp_class_autres = $result->fetchRow())
    {
      if($type_current != $pp_class_autres->type)
      {
        $type_current = $pp_class_autres->type;
        
        if($type_current == 'year') $html .= ($html ? "</ul><br />" : "") . "<b>Classements annuels</b><ul>";
          else if($type_current == 'month') $html .= "<ul><li><a href=\"/classements.php\" class=\"link_orange\">Tous mes classements</a></li></ul><br /><b>Classements mensuels</b><ul>";
      }
      if( $html ) $html .= "<li><a href=\"/class.php?id=".$pp_class_autres->id_class."\" class=\"link_orange\">".formatDbData($pp_class_autres->label)."</a></li>";
    }
    if( $html ) $html .= "</ul>";
  }
  
  return $html;
}


function listDivisionsCup()
{
	global $user, $db;
  
  $html = '';
	$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label`, `pp_cup_matches`.`id_cup_matches`, `pp_cup_matches`.`number_tour`
			FROM `pp_cup_matches`
			INNER JOIN `pp_cup` ON `pp_cup`.`id_cup` = `pp_cup_matches`.`id_cup`
			ORDER BY `pp_cup_matches`.`id_cup` DESC, `pp_cup_matches`.`number_tour` DESC
			LIMIT 1";
	$result_cup_matches = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_cup_matches))
	{
		die ("<li>ERROR : ".$result_cup_matches->getMessage()."<li>$SQL");
		
	} else {
		if($pp_cup_matches = $result_cup_matches->fetchRow())
		{
      $SQL = "SELECT DISTINCT `cup_sub`
          FROM `pp_cup_match_opponents`
          WHERE `id_cup`='".$pp_cup_matches->id_cup."'
          ORDER BY `cup_sub`";
      $result_division = $db->query($SQL);
      //echo "<li>$SQL";
      if(DB::isError($result_division))
      {
        die ("<li>ERROR : ".$result_division->getMessage()."<li>$SQL");
        
      } else {
        $html .= "<b>".formatDbData($pp_cup_matches->label)."</b>";
        $html .= "<ul>";
        while($cup_division = $result_division->fetchRow())
        {
          $html .= "<li><a href=\"/cup.php?id=".$pp_cup_matches->id_cup."&division=".$cup_division->cup_sub."\">".getCupDivisionLabel($cup_division->cup_sub)."</a></li>";
        }
        $html .= "</ul>";
      }
      
		}
	}
  
  return $html;
}


function getOnglets($onglet_actif = false)
{
	global $user;
	$html = "";
	
	// nb nouveaux messages sur le wall ?
	$nb_msg_wall = pp_comments_nb('wall', $user->id_user, $new=true);
	$alt_msg_wall = '';
	if($nb_msg_wall > 0)
	{
		$html_nb_msg_wall = ' <span class="onglet_nb_msg_left"><span class="onglet_nb_msg_right">'.$nb_msg_wall.'</span></span>';
		$alt_msg_wall = $nb_msg_wall.' nouveau'.($nb_msg_wall > 1 ? 'x' : '').' message'.($nb_msg_wall > 1 ? 's' : '').' sur mon mur';
	}
	
	// nouvelles activités amis
	$nb_msg_friends = pp_comments_nb_friends();
	$alt_msg_friends = '';
	if($nb_msg_friends > 0)
	{
		$html_nb_msg_friends = ' <span class="onglet_nb_msg_left"><span class="onglet_nb_msg_right">'.$nb_msg_friends.'</span></span>';
		$alt_msg_friends = $nb_msg_friends.' nouvelle'.($nb_msg_friends > 1 ? 's' : '').' activité'.($nb_msg_friends > 1 ? 's' : '').' des amis';
	}
  
  
	
	// liste onglets
	$onglets = array();
  
	$onglets[] = array('id' => 'accueil', 'title' => 'Accueil', 'url' => '/', 'richcontent' => listGrillesEnCours() . '<br />'. listDerniersResultats());
  
	//$onglets[] = array('id' => 'euro2012', 'title' => '<i>Euro 2012</i>', 'url' => '/euro2012-diffusion-tele.php', 'alt' => 'Diffusion de l\'Euro 2012 à la télé');
	
	$onglets[] = array('id' => 'classement', 'title' => 'Classements', 'url' => '/classements.php', 'alt' => 'Tous les classements de Prono+', 'richcontent' => listClassements());
  
	$onglets[] = array('id' => 'coupe', 'title' => 'Coupe', 'url' => '/cup.php', 'alt' => 'La Coupe Prono+', 'richcontent' => listDivisionsCup());


	$onglets[] = array('id' => 'stats', 'title' => 'Stats', 'url' => '#', 'alt' => '', 'richcontent' => listStatsMenu());

    $onglets[] = array('id' => 'agenda', 'title' => 'Agenda', 'url' => '/agenda-football.php?date='.date("d-m-Y"), 'alt' => 'Agenda football');
  
	if($user) $onglets[] = array('id' => 'mon_profil', 'title' => 'Mur'.$html_nb_msg_wall, 'url' => '/user.php?q='.htmlspecialchars(urlencode($user->login)), 'alt' => $alt_msg_wall ? $alt_msg_wall : 'Mon profil et mon mur');
  
	$onglets[] = array('id' => 'mes_amis', 'title' => 'Amis'.$html_nb_msg_friends, 'url' => '/friends.php', 'alt' => $alt_msg_friends ? $alt_msg_friends : 'Mes amis et leurs activités');
  
  if($user) $onglets[] = array('id' => 'mes_recompenses', 'title' => 'Etoiles', 'url' => '/recompenses.php?q='.htmlspecialchars(urlencode($user->login)), 'alt' => 'Mes récompenses');
  
  $onglets[] = array('id' => 'blog', 'title' => 'Blog', 'url' => '/blog/');
  
  $onglets[] = array('id' => 'forum', 'title' => 'Forum', 'url' => '/forum-football/');
	
	if($onglet_actif == 'equipe-de-france') $html .= "<div id=\"edf_header_onglet\">";
	
	$html .= "<div id=\"menu_onglet\"><ul>";
	//$html .= "<li ".($_SERVER["PHP_SELF"]=='/' || $_SERVER["PHP_SELF"]=='/index.php'  ? "id=\"current_onglet\"" : "")."><a href=\"/\" onfocus=\"if(this.blur()) this.blur();\">Général</a></li>";
			
	if(is_array($onglets)) foreach($onglets as $onglet)
	{
		$html .= "<li ".($onglet_actif == $onglet['id'] ? "id=\"current_onglet\"" : "")."><a href=\"".$onglet['url']."\">".$onglet['title']."</a>";
    if($onglet['richcontent']!='') $html .= '<div class="submenu_onglet">' . $onglet['richcontent'] . "</div>";
    $html .= "</li>";
	}	
	$html .= "</ul></div>";
	$html .= "<div class=\"clear\"></div>";
	
	if($onglet_actif == 'equipe-de-france') $html .= "</div>";
	
	return $html;
}


function getBlocLeft($title, $color, $content, $idname='', $h1=true)
{
	return "<li ".($idname!='' ? "id=\"".$idname."\"" : "").">
				<div class=\"list_handle\"><".($h1 ? "h1" : "h2")." class=\"title_".$color."\">".$title."</".($h1 ? "h1" : "h2")."></div>
				<div class=\"bloc_content\">".$content."</div>
			</li>";
}


function getContentLeft()
{
	global $db, $user;
?>
	<div id="content_left">		

		<?php
		echo getOnglets('accueil');
		?>
		
		<div id="content">
			<ul id="list_left" class="list_sortable">	
				
				<?php
				$alaune = alaune();
				if(strip_tags($alaune))
				{
				    ?>
				    <li style="padding:0; margin:0; border:0"><?php echo $alaune; ?></li>
				    <?php
			    }
			    
			    
				
				
				if(!$user->id_user)
				{
					$content = '
					<p>Jouer à Prono+ c\'est simple :<br />tu as droit à <b>10 points pour chaque match d\'une grille</b> que tu réparties en mises sur tous les matchs de la grille (de 5 à 50 points par match), tu pronostiques les scores et tu gagnes :<br />					
					- <b>10 fois ta mise</b> si le score du match est le bon<br />
					- <b>3 fois ta mise</b> si le résultat du match est le bon (gagné, perdu, nul)<br />					
					- et tu perds ta mise si le résultat du match n\'est pas le bon<br />
					</p><br />
					<p>Exemple :</p>
					<style type="text/css" media="screen">@import url(template/default/pronostiquer.css?v=1.2);</style>
					<table width="100%" cellpadding="2" cellspacing="1">
					<tr>
		<th width="30%">&nbsp;</th>
		<th width="10%" align="center" id="score_example">Score</th>
		<th width="30%">&nbsp;</th>
		<th width="30%" colspan="2" align="center" id="mise_example">Mise</th>
	</tr>
	<tr>
		<td align="right">Manchester United <img src="/image/flags/manchesterunited.png" align="absmiddle"></td>		
		<td nowrap="nowrap">
		            <select disabled>
					    <option selected="selected">2</option>
					</select>
					-
		            <select disabled>
					    <option selected="selected">1</option>
					</select>
          </td>
		
		<td><img src="/image/flags/chelsea.png" align="absmiddle"> Chelsea</td>
		
		<td><div id="div_slider_example1"><div id="grille_track_example1" class="grille_track"><div id="grille_handle_example1" class="grille_handle"></div></div></div></td>
		
		<td class="grille_mise"><input type="text" value="15" size="2" maxlength="2" class="grille_score_input" disabled></td>
	</tr>
	
	<tr class="ligne_grise">
		<td align="right">Real Madrid <img src="/image/flags/realmadrid.png" align="absmiddle"></td>		
		<td nowrap="nowrap">
		            <select disabled>
					    <option selected="selected">1</option>
					</select>
					-
		            <select disabled>
					    <option selected="selected">1</option>
					</select>
          </td>
		
		<td><img src="/image/flags/barcelone.png" align="absmiddle"> FC Barcelone</td>
		
		<td><div id="div_slider_example2"><div id="grille_track_example2" class="grille_track"><div id="grille_handle_example2" class="grille_handle"></div></div></div></td>
		
		<td class="grille_mise"><input type="text" value="5" size="2" maxlength="2" class="grille_score_input" disabled></td>
	</tr>
	</table>
	
	<hr />
	<p>
	    <b>"Je m\'inscris en cours de saison mais je ne pourrai jamais rattraper les premiers au classement général !"</b><br />
	    Déjà : 1- Sois pas défaitiste, tout peut arriver au foot, c\'est Jean-Michel Larqué qui le dit.<br />
	    De 2- tu peux jouer les classements mensuels et comme son nom l\'indique permet de repartir à 0 tous les mois !	    
	</p>
	
	<p>
	    <b>"J\'aime pas le foot"</b><br />
	    C\'est ton droit le plus absolu. Tu peux passer ton chemin.
	</p>
	
	
    <script type="text/javascript" language="javascript">
	document.observe(\'dom:loaded\', function() {
			
        sliderExample1 = new Control.Slider(\'grille_handle_example1\',\'grille_track_example1\',
		        {
			        range:$R(5, 50)
		        });
        sliderExample1.setValue(15);
        sliderExample1.setDisabled();

        sliderExample2 = new Control.Slider(\'grille_handle_example2\',\'grille_track_example2\',
		        {
			        range:$R(5, 50)
		        });
        sliderExample2.setValue(5);
        sliderExample2.setDisabled();

        new Tip(\'score_example\', \'<div style="font-size:12px; text-align:center;">Pronostique le score des matchs</div>\', {
	        style: \'protogrey\',
	        stem: \'bottomMiddle\',
	        hook: { target: \'topMiddle\', tip: \'bottomMiddle\' },
	        offset: { x: 0, y: 0 },
	        radius: 5,
	        width: 200
        });
        
        $(\'score_example\').prototip.show();
        
        new Tip(\'mise_example\', \'<div style="font-size:12px; text-align:center;">Mise + ou - de points sur les matchs</div>\', {
	        style: \'protogrey\',
	        stem: \'bottomMiddle\',
	        hook: { target: \'topMiddle\', tip: \'bottomMiddle\' },
	        offset: { x: 0, y: 0 },
	        radius: 5,
	        width: 240
        });
        
        $(\'mise_example\').prototip.show();
    
    });
    </script>
	';
					
					/*
					$content .= "<p align=\"center\"><a onclick=\"Sinscrire(this); return false\" href=\"#\"><img src=\"/image/jouer-a-pronoplus.gif\" border=\"0\" alt=\"Jouer à Prono+\" /></a></p>
<p>La règle du jeu ?<br />Tu pronostiques une  grille de matchs en misant des points sur ces matchs. Tu disposes de 10 points pour chaque match, c'est &agrave; dire que si la grille  comporte 8 matchs, tu auras 80 points &agrave; r&eacute;partir sur les 8 matchs.<br />Si le r&eacute;sultat du match que tu as pronostiqué est exact, tu gagne 3 fois la somme de ta mise. Mieux, tu gagnes 10 fois ta mise si le score que tu as pronostiqué est le bon !<br /><br />
<strong>Exemples :<br /><br />
Lyon 1-0 FC Barcelone</strong><br />
J'ai pronostiqu&eacute; 2-0 et j'ai mis&eacute; 30 points.<br />
Je gagne : 30 x 3 = 90 points.<br /><br />
<strong>PSG 2-1 Marseille</strong><br />
J'ai pronostiqu&eacute; 2-1 et j'ai mis&eacute; 50 points.<br />
Je gagne : 50 x 10 = 500 points.
</p>";
*/
					echo getBlocLeft("Comment jouer à Prono+ ?", 'orange', $content);
				}
				
				
				
				
				
				if($user->id_user)
				{
					echo PostIt();
				}
				
				
				//echo CurrentCup();
				
				echo LiveScore();
				
				echo CurrentMatches();

				
				if($user->id_user) echo ClassMatches();
				
				/*
				$content = "<p>Les classements seront bient&ocirc;t disponibles.</p>";
				echo getBlocLeft('Classements', 'grey', $content);
				*/
				ob_start();
				getLastPostFromForum();
				$content = ob_get_contents();
				ob_end_clean();
				echo getBlocLeft('Les derniers messages sur le forum', 'grey', $content, '', false);
				?>
				
				<li>
				    <div class="list_handle"><h2 class="title_blueking">Tu n'es pas fan de Prono+ sur Facebook ? Non mais allô quoi !</h2></div>
				    <div class="bloc_content">
				    <iframe src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fpronoplus&amp;width=676&amp;height=558&amp;colorscheme=light&amp;show_faces=true&amp;header=false&amp;stream=true&amp;show_border=false&amp;appId=302697239001" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:676px; height:558px;" allowTransparency="true"></iframe>
				    </div>
				</li>
				
				
				
			</ul>
		</div>
	</div>
	<?php /*
	<script language="javascript">
	<!--
	Sortable.create('list_left', { handle:'list_handle', scroll: window });
	-->
	</script>
	*/ ?>
<?php
}


function alaune()
{
	global $db, $user, $txtlang;
	$content = "";

	$tab_alaune = array();
	
	// à la une ?
	$SQL = "SELECT type, message
			FROM pp_alaune
			WHERE active='1'
			ORDER BY ordre";
	$result_alaune = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_alaune))
	{
		die ("<li>ERROR : ".$result_alaune->getMessage());
		
	} else {
		while($pp_alaune = $result_alaune->fetchRow())
		{
			/* affichage d'un message */
			if($pp_alaune->type == 'message')
			{
				$tab_alaune[] = $pp_alaune->message;
				
			}
			
			/* affichage des derniers articles du blog */
			else if($pp_alaune->type == 'blog')
			{
				$pp_alaune->message = $pp_alaune->message*1;
				$posts = PreviewBlog( array('type' => 'alaune', 'days_expire' => 7, 'nbelements' => $pp_alaune->message ? $pp_alaune->message : 3) );
				if(is_array($posts)) foreach( $posts as $post) $tab_alaune[] = $post;
			}
			
			/* affichage des derniers matchs */
			else if($pp_alaune->type == 'matches' && !$user->id_user)
			{
				// matchs mis en avant ?
				$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`id_cup_matches`,
							`pp_matches`.`image`, `pp_matches`.`date_first_match`, `pp_matches`.`date_last_match`,
							if(`pp_matches`.`date_first_match` < NOW(), 1, 0) AS `show_class_prov`,
							DATE_FORMAT(`pp_matches`.`date_first_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_first_match_format`,
							DATE_FORMAT(`pp_matches`.`date_last_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_last_match_format`,
							TIMEDIFF(NOW(), `pp_matches`.`date_first_match`) AS `diff_date_first_match`,
							TIMEDIFF(NOW(), `pp_matches`.`date_last_match`) AS `diff_date_last_match`,

							DATE_FORMAT(`pp_matches`.`date_first_match`, '".$txtlang['AFF_TIME_SQL']."') AS `date_first_time_match_format`,
							YEAR(`pp_matches`.`date_first_match`) AS `date_first_match_year`,
							MONTH(`pp_matches`.`date_first_match`) AS `date_first_match_month`,
							DAYOFMONTH(`pp_matches`.`date_first_match`) AS `date_first_match_day`,
							DAYOFWEEK(`pp_matches`.`date_first_match`) AS `date_first_match_dayweek`,
							
							DATE_FORMAT(`pp_matches`.`date_last_match`, '".$txtlang['AFF_TIME_SQL']."') AS `date_last_time_match_format`,
							YEAR(`pp_matches`.`date_last_match`) AS `date_last_match_year`,
							MONTH(`pp_matches`.`date_last_match`) AS `date_last_match_month`,
							DAYOFMONTH(`pp_matches`.`date_last_match`) AS `date_last_match_day`,
							DAYOFWEEK(`pp_matches`.`date_last_match`) AS `date_last_match_dayweek`			
						FROM `pp_matches`
						WHERE `pp_matches`.`is_calcul` != '1' AND `pp_matches`.`image` != 'ligue2.png'
						LIMIT ".($pp_alaune->message*1 ? $pp_alaune->message*1 : 3);
				$result_matches = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result_matches))
				{
					die ("<li>ERROR : ".$result_matches->getMessage());
					
				} else {
					while($pp_matches = $result_matches->fetchRow())
					{
						$content = '';
						
						
						if($pp_matches->id_cup_matches)
						{
							$pp_matches->image = 'coupe.png';
							$SQL = "SELECT `id_cup` FROM `pp_cup_matches` WHERE `id_cup_matches` = '".$pp_matches->id_cup_matches."'";
							$result_cup = $db->query($SQL);
							if(!DB::isError($result_cup)) while($pp_cup_matches = $result_cup->fetchRow()) $pp_matches->id_cup = $pp_cup_matches->id_cup;
						}
						if($pp_matches->id_cup)
						{
							$link_matches = "/cup.php?id=".$pp_matches->id_cup;
						} else {
							$link_matches = "/pronostiquer.php?id=".$pp_matches->id_matches;
						}
						
						// le joueur a déjà joué cette grille ?
						$pp_matches->save_yet = false;
						
						$dayweek = $pp_matches->date_first_match_dayweek;
						if($dayweek==1) $dayweek=8;
						$dayweek = $dayweek-2;
						$pp_matches->date_first_match = get_date_complete($dayweek, $pp_matches->date_first_match_day, $pp_matches->date_first_match_month-1, $pp_matches->date_first_match_year)." &agrave; ".$pp_matches->date_first_time_match_format;
						
						if($user)
						{
							$SQL = "SELECT `id_matches` FROM `pp_matches_user`
									WHERE `id_user`='".$user->id_user."' AND `id_matches`='".$pp_matches->id_matches."'";
							$result = $db->query($SQL);
							//echo "<li>$SQL";
							if(DB::isError($result))
							{
								die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
								
							} else {
								if($result->numRows()) $pp_matches->save_yet = true;
							}
						}
						
						$tabb = explode(':', $pp_matches->diff_date_first_match);
						$pp_matches->diff_date_first_match = $tabb[0] *1;
						
						// Lien pronostiquer
						if(!$pp_matches->save_yet && substr($pp_matches->diff_date_last_match, 0, 1) == '-')
						{
							$content .= "<a href=\"".$link_matches."\" class=\"".($pp_matches->diff_date_first_match < -24 ? "link_button" : "link_button_urgent")."\" style=\"float:right;\">Pronostiquer</a>";
						}						
						elseif(substr($pp_matches->diff_date_last_match, 0, 1) == '-')
						{
							// Lien modifier pronostics
							$content .= "<a href=\"".$link_matches."\" class=\"link_button\" style=\"float:right;\">Modifier mes pronostics</a>";
						}
						
						if($pp_matches->id_cup || substr($pp_matches->diff_date_last_match, 0, 1) == '-')
						{
							$link = "<a href=\"".$link_matches."\">";
						} else {
							$link = "<a href=\"/classj.php?id=".$pp_matches->id_matches."\">";
						}
						$content .= $link."<img src=\"/template/default/".$pp_matches->image."\" class=\"preview_matches_image\" border=\"0\" style=\"border:solid 3px #eee;\" alt=\"".htmlspecialchars(formatDbData($pp_matches->label))."\" /></a>";
						$content .= "<h2>".$link.formatDbData($pp_matches->label)."</a></h2>";
						$content .= "<span class=\"red\">".format_diff_date($pp_matches->diff_date_first_match)."</span></strong>";
						
						//$content .= "<p><strong>1<sup>er</sup> match : ".$pp_matches->date_first_match.".</strong></p>";

						// Lien classement provisoire
						if(!$pp_matches->id_cup_matches && $pp_matches->show_class_prov)
						{
							$content .= "<p align=\"center\"><a href=\"/classj.php?id=".$pp_matches->id_matches."\" class=\"link_button\">Classement provisoire</a></p>";					
						}			
						
						// les matchs			
						$SQL = "SELECT `pp_match`.`id_match`, `team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`,
								`team_host`.`featured`+`team_visitor`.`featured` AS `featured_weight`
								FROM `pp_match`
								INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
								INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
								WHERE `pp_match`.`id_matches`='".$pp_matches->id_matches."'
								ORDER BY `featured_weight` DESC
								LIMIT 3";
						$result_match = $db->query($SQL);
						//echo "<li>$SQL";
						if(DB::isError($result_match))
						{
							die ("<li>ERROR : ".$result_match->getMessage());
							
						} else {
							$content .= "<table class=\"table_match\" align=\"center\">";
							while($pp_match = $result_match->fetchRow())
							{
								$content .= "<tr>
										<td align=\"right\" ".($pp_match->diff_date_match>0 ? "class=\"strike\"" : "")." width=\"49%\">".formatDbData($pp_match->team_host_label)."</td>
										<td>-</td>
										<td ".($pp_match->diff_date_match>0 ? "class=\"strike\"" : "")."  width=\"49%\">".formatDbData($pp_match->team_visitor_label)."</td>
									</tr>";
							}
							$content .= "</table>";
						}
						
						$tab_alaune[] = $content;
					}
				}
			}
		}
	}
	
	
	if(count($tab_alaune) > 0)
	{
		$content = '<div style="margin-bottom:4px;">';	
			foreach($tab_alaune as $index=>$alaune)
			{
				$content .= '<div id="alaune'.($index+1).'" class="alaune"'.($index>0 ? ' style="display:none;"' : '').'>'.$alaune.'</div>'."\n";
			}
			
			if(count($tab_alaune) > 1)
			{
				$content .= '<div class="alaune_numeros"><ul>';
				foreach($tab_alaune as $index=>$alaune)
				{
					$content .= '<li id="alaune'.($index+1).'_numero"'.($index==0 ? ' class="on"' : '').'><a href="#" onclick="return alaune_show('.($index+1).', 1);" onfocus="if(this.blur()) this.blur();">'.($index+1).'</a></li>';
				}
				$content .= '</ul></div><div style="clear:both"></div>';
			}
		$content .= '</div>';
	}

	if(count($tab_alaune) > 1)
	{
		$content .= "<script>
			var tempo = 4000;
			var id_alaune = 1;
			function alaune_show(id, stop)
			{
				if(stop == 1)
				{
					clearTimeout(alaune_timer);
					alaune_timer = setTimeout(\"alaune_change()\", tempo+6000);				
				}
				if(id_alaune == id) return;
				$('alaune'+id_alaune+'_numero').className = '';
				$('alaune'+id).show();
				//new Effect.Appear('alaune'+id, {duration:0.4});
				new Effect.Highlight('alaune'+id, { startcolor: '#ffffff', endcolor: '#222222', duration: 0.4 });
				$('alaune'+id_alaune).hide();
				$('alaune'+id+'_numero').className = 'on';
				id_alaune = id;
				return false;
			}
			
			function alaune_change()
			{
				var id_new = id_alaune + 1;
				if(!$('alaune'+id_new)) id_new = 1;
				alaune_show(id_new);
				alaune_timer = setTimeout(\"alaune_change()\", tempo);
			}
			var alaune_timer = setTimeout(\"alaune_change()\", tempo);		
			</script>";
	}
	
	return '<!-- debut a la une -->' . $content . '<!-- fin a la une -->';
}


function getRightBulle($pp_user=0)
{
	global $user, $db;
	if($pp_user->login)
	{
		$userprofil = $pp_user;
	} else {
		$userprofil = $user;
	}
?>
<div class="bulle">
	<div class="bulle_top"></div>
	<div class="bulle_bloc">
		<div class="bulle_content"><div class="center">		
			<?php
			$message_bulle = '';
			
			if($userprofil->id_user == $user->id_user)
			{
				$form = '<div id="bulle_edit_text" style="display:none"><form method="post" action="/user.php?q='.urlencode(htmlspecialchars($userprofil->login)).'">
						<input type="hidden" name="pp_comments_type" value="wall" />
						<input type="hidden" name="pp_comments_id_type" value="'.$userprofil->id_user.'" />
						<input type="hidden" name="pp_comments_parent" value="" />
						<input type="hidden" name="pp_comments_key" value="'.md5('wall_pronoplus_'.$userprofil->id_user.'_pronoplus_').'" />
						<textarea id="envoyer_msg_wall_'.$userprofil->id_user.'_" name="message" rows="4" style="width:99%" ></textarea>
						<button onclick="this.hide()" name="envoyer" type="submit" class="link_button"  value="" />Shooter</button>
						ou <a href="#" onclick="$(\'bulle_static_text\').show(); $(\'bulle_edit_text\').hide(); return false;" style="display:inline; font-weight:normal; text-decoration:underline">J\'ai changé d\'avis</a>
						</form></div>';
			}
			
			if($userprofil->id_user)
			{
				// recherche dernier message posté sur le mur par l'utilisateur
				$SQL = "SELECT `message`
						FROM `pp_comments`
						WHERE `type`='wall' AND `id_type`='".$userprofil->id_user."' AND `id_user`='".$userprofil->id_user."' AND `deleted`!='1'
							AND parent_id_comment=0 AND love='' AND hate=''
						ORDER BY `date_creation` DESC
						LIMIT 1";
				$result = $db->query($SQL);
				if(DB::isError($result))
				{
					die ("<li>$SQL<li>ERROR : ".$result->getMessage());
					
				} else {
					if($pp_comments = $result->fetchRow())
					{
						$message_bulle = formattexte($pp_comments->message);
						
					} else {
						$message_bulle = "Je pense donc je m'exprime";
					}
					
					if($userprofil->id_user == $user->id_user)
						echo '<div id="bulle_static_text">' . $message_bulle . '&nbsp;<a href="#" title="Modifier mon message" onclick="$(\'bulle_static_text\').hide(); $(\'bulle_edit_text\').show(); $(\'envoyer_msg_wall_'.$userprofil->id_user.'_\').focus(); return false;" style="display:inline; float:right;"><img src="/template/default/page_edit.png" border="0" align="absmiddle" /></a></div>' . $form;
					else
						echo $message_bulle;
				}
			}
			
			if($message_bulle == '')
			{
				$rnd = rand(1,18);
				if($rnd == 1) {
				?>
					&laquo;&nbsp;Chaque minute en Amazonie, on déboise l'équivalent de 60 terrains de football. C'est un peu idiot. Il n'y aura jamais assez de joueurs&nbsp;&raquo;
					<br /><em>Le chat de Philippe Geluck</em>
				<?php } else if($rnd == 2) { ?>
					&laquo;&nbsp;Il ne faut pas brûler la peau de l'ours avant de l'avoir vendue&nbsp;&raquo;
					<br /><em>Abdeslam Ouaddou</em>
				<?php } else if($rnd == 3) { ?>
					&laquo;&nbsp;En première mi-temps ça sentait le pâté... et maintenant, ça sent le boudin&nbsp;&raquo;
					<br /><em>Thierry Roland</em>
				<?php } else if($rnd == 4) { ?>
					&laquo;&nbsp;Après cela on peut mourir tranquille, mais le plus tard sera le mieux !&nbsp;&raquo;
					<br /><em>Thierry Roland, finale France - Brésil en 1998</em>
				<?php } else if($rnd == 5) { ?>
					&laquo;&nbsp;Allez, mon petit bonhomme !&nbsp;&raquo;
					<br /><em>Thierry Roland</em>
				<?php } else if($rnd == 6) { ?>
					&laquo;&nbsp;A gauche, à gauche, à gauche !&nbsp;&raquo;
					<br /><em>Jean-Michel Larqué</em>
				<?php } else if($rnd == 7) { ?>
					&laquo;&nbsp;Pas de faute ! Pas de faute ! Pas de faute !&nbsp;&raquo;
					<br /><em>Jean-Michel Larqué</em>
				<?php } else if($rnd == 8) { ?>
					&laquo;&nbsp;Le ballon, c'est comme une femme, il aime les caresses&nbsp;&raquo;
					<br /><em>Eric Cantona</em>
				<?php } else if($rnd == 9) { ?>
					&laquo;&nbsp;Va falloir mettre nos couilles sur le terrain&nbsp;&raquo;
					<br /><em>Jérôme Rothen, alors au PSG</em>
				<?php } else if($rnd == 10) { ?>
					&laquo;&nbsp;Je suis parti pour rester à vie à Arsenal&nbsp;&raquo;
					<br /><em>Thierry Henry</em>
				<?php } else if($rnd == 11) { ?>
					&laquo;&nbsp;Je ne suis pas venu à Munich pour que le Bayern soit un intermède&nbsp;&raquo;
					<br /><em>Franck Ribéry</em>
				<?php } else if($rnd == 12) { ?>
					&laquo;&nbsp;Tu peux changer de femme, tu peux changer de religion mais tu ne peux pas changer d'équipe de foot&nbsp;&raquo;
					<br /><em>Extrait du film Looking for Eric</em>
				<?php } else if($rnd == 13) { ?>
					&laquo;&nbsp;Je ne suis pas un homme, je suis Cantona&nbsp;&raquo;
					<br /><em>Eric Cantona</em>
				<?php } else if($rnd == 14) { ?>
					&laquo;&nbsp;C'est seulement dans un stade qu'on peut voir des Anglais s'embrasser&nbsp;&raquo;
					<br /><em>Eric Cantona</em>
				<?php } else if($rnd == 15) { ?>
					&laquo;&nbsp;Il faut prendre les matchs les uns après les autres&nbsp;&raquo;
					<br /><em>La majorité des footballeurs</em>
				<?php } else if($rnd == 16) { ?>
					&laquo;&nbsp;Je crois que bon...&nbsp;&raquo;
					<br /><em>Laurent Blanc</em>
				<?php } else if($rnd == 17) { ?>
					&laquo;&nbsp;C'était un match difficile mais on a pris les 3 points, c'est l'essentiel&nbsp;&raquo;
					<br /><em>Franck Ribéry, après un match de Coupe de France</em>
				<?php } else { ?>
					&laquo;&nbsp;Au football seul le ballon n'est pas payé, c'est pourtant lui qui se prend le plus de coups&nbsp;&raquo;
					<br /><em>Vincent Roca</em>
				<?php }
			}
			?>
			
		<?php /*if($user->id_user) { ?>
		<?php } else { ?>
		Bienvenue sur Prono+ !
		<?php } */ ?>
		</div></div>
	</div>
	<div class="bulle_bottom"></div>
</div>
<?php
}

function getRightProfil($pp_user=0)
{
	global $user, $db;
	if($pp_user->login)
	{
		$userprofil = $pp_user;
	} else {
		$userprofil = $user;
	}
	
	/* maj url user */
	/*$SQL = "SELECT * FROM `pp_user`";
	$result_user = $db->query($SQL);	
	while($pp_user = $result_user->fetchRow())
	{
		$SQL = "UPDATE pp_user SET urlprofil='".toUrlRewriting($pp_user->login)."' WHERE id_user='".$pp_user->id_user."'";
		$db->query($SQL);
	}*/
	/* maj url user */

    if($userprofil->id_user)
    {
        // on récupère les étoiles déjà obtenues
        $SQL = "SELECT id_recompense, nb
                    FROM  `pp_user_recompenses`
                    WHERE  `id_user` = " . $userprofil->id_user;
        $result_recompenses = $db->query($SQL);
        $nb_recompenses = $result_recompenses->numRows();
    }
	
	$avatar = getAvatar($userprofil->id_user, $userprofil->avatar_key, $userprofil->avatar_ext, 'normal');
?>
		<div class="profil">
		
			<?php if(!$avatar && $userprofil->id_user && $user->id_user == $userprofil->id_user) { ?><div class="message_error"><?php } ?>
			
			<div class="profil_img">
				<div class="profil_img_top"></div>
				<div class="profil_img_left">
					<div class="profil_img_right">
						<?php
						if($user->id_user == $userprofil->id_user) echo '<a href="/avatar.php">';
							if($avatar) {
							?>
								<img src="/avatars/<?php echo $avatar?>" height="118" width="118" border="0" />
							<?php } else { ?>
								<img src="/template/default/_profil.png" height="118" width="118" border="0" />
							<?php }
						if($user->id_user == $userprofil->id_user) echo '</a>';
						?>
					</div>
				</div>				
				<div class="profil_img_bottom"></div>
			</div>
			
			<?php if(!$avatar && $userprofil->id_user && $user->id_user == $userprofil->id_user) { ?><br /><a href="/avatar.php">Choisis maintenant un avatar qui te représente !</a></div><?php } ?>
			
			<div class="pseudo_joueur">
				<?php if($userprofil) { ?>
					<?php echo formatDbData($userprofil->login)?> <?php /*<img src="template/default/status_online.png" alt="En ligne" title="En ligne" height="16" width="16" />*/ ?>
                    <br /><p class="<?php echo $nb_recompenses != 0 ? 'recompense-star' : 'recompense-star-disabled'; ?>"><a href="/recompenses.php?q=<?php echo urlencode($userprofil->login); ?>"><?php echo $nb_recompenses; ?> <?php echo $nb_recompenses > 1 ? 'étoiles' : 'étoile'; ?></a></p>
                    <br /><small>Inscrit(e) depuis le <?php echo $userprofil->register_date_format; ?></small>


					
				<?php } else if(!$user) { ?>
					<a href="javascript:" onclick="SeConnecter(this);" class="link_button_urgent">M'inscrire</a>
				<?php } ?>
			</div>

            <?php if($user->id_user && $userprofil->id_user == $user->id_user) { ?>
			<div class="btn_ajouter_contenu" align="center">
			    <a href="/profil.php"><img src="template/default/user_edit.png" align="absmiddle" alt="Modifier mon profil" height="16" width="16" border="0" /> Modifier mon profil</a>
			</div>
            <?php } ?>
			
			
			
			<?php
			if($user->id_user && $userprofil->id_user == $user->id_user)
			{
				/* recherche d'invitation amis */
				$SQL = "SELECT COUNT(`pp_user_friends`.`id_user_friend`) AS nb_invitations			
						FROM `pp_user_friends`
						WHERE `pp_user_friends`.`id_user_friend`='".$user->id_user."' AND `pp_user_friends`.`valide`=''";
				$result_user_friends = $db->query($SQL);
				if(DB::isError($result_user_friends)) die ("<li>ERROR : ".$result_user_friends->getMessage());
				if($pp_user_friends = $result_user_friends->fetchRow()) if($pp_user_friends->nb_invitations)
				{
					?>
					<br /><div align="center">
						<a class="link_button_urgent" href="/friends.php"><img src="/template/default/group_add.png" height="16" width="16" border="0" align="absmiddle" /> <?php echo $pp_user_friends->nb_invitations.' invitation'.($pp_user_friends->nb_invitations > 1 ? 's' : '').' d\'ami(e)'.($pp_user_friends->nb_invitations > 1 ? 's' : ''); ?></a>
					</div>
					<?php
				}
			}
			?>
			
			<?php /*
			<div class="btn_ajouter_contenu">
				<?php if($user) { ?>
					<img src="template/default/add.png" align="absmiddle" alt="Ajouter contenu" height="16" width="16" border="0" /> <strike>Ajouter du contenu &agrave; ma page</strike><br />
                    Cette fonction n'est pas encore disponible !
				<?php } else { ?>
					<a href="javascript:" onclick="SeConnecter(this);"><img src="template/default/add.png" align="absmiddle" alt="Ajouter contenu" height="16" width="16" border="0" /> Ajouter du contenu &agrave; ma page</a>
				<?php } ?>
			</strike></div>
			*/ ?>
			
			<?php /*if($user) { ?><div><a href="/logout.php"><img src="/template/default/close.gif" height="12" width="12" hspace="2" border="0" align="absmiddle" /> D&eacute;connecter</a></div><?php }*/ ?>
			
		</div>

		<?php
		$class_user = array();
		$SQL = "SELECT `pp_class_user`.`id_class`, `pp_class_user`.`id_user`, `pp_class_user`.`class`, `pp_class_user`.`evolution`
				FROM `pp_class`
				INNER JOIN `pp_class_user` ON `pp_class`.`last_id_matches`=`pp_class_user`.`id_matches` AND `pp_class`.`id_class`=`pp_class_user`.`id_class`
				WHERE `pp_class_user`.`id_user`='".$userprofil->id_user."' AND `pp_class`.`id_class`=2";
		$result_class = $db->query($SQL);
		//echo "<li>$SQL";
		if(DB::isError($result_class))
		{
			die ("<li>ERROR : ".$result_class->getMessage());
			
		} else {	
			while($pp_class = $result_class->fetchRow())
			{
				$class_user[$pp_class->id_class]['class'] = $pp_class->class;
				$class_user[$pp_class->id_class]['evolution'] = $pp_class->evolution;
			}
		}
		

		?>
		<div class="profil_palmares">
			<?php
			if($user->id_user || $userprofil)
			{
			?>
			<div class="profil_palmares_border">
				<table cellspacing="0" cellpadding="2" align="center">
			    <tr>
			      <td align="right" width="70%"><a href="class.php?id=2&rech_jpseudo=<?php echo urlencode($userprofil->login)?>&search_joueur=1">Classement G&eacute;n&eacute;ral</a> : </td>
			      <td width="30%" nowrap>
				  <?php
				  if($class_user[2]['class'])
				  {
				  ?>
				  <a href="class.php?id=2&rech_jpseudo=<?php echo urlencode($userprofil->login)?>&search_joueur=1"><?php echo $class_user[2]['class']?>
					<?php
					if($class_user[2]['evolution']<0) {
						echo "<font color=\"red\" style=\"font-family:Arial; font-size:8px;\">(".$class_user[2]['evolution'].")</font>";
					} elseif($class_user[2]['evolution']>0) {
						echo "<font color=\"green\">(+".$class_user[2]['evolution'].")</font>";
					}
					?>
					</a>
				  <?php
				  } else echo '-';
				  ?>
				  </td>
		        </tr>
				<?php
				// recherche Classement mensuel
				$SQL = "SELECT `id_class`, `label`
						FROM `pp_class`
						WHERE type='month' AND `last_id_matches`>0
						ORDER BY `order` ASC
						LIMIT 1";
				$result_class = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result_class))
				{
					die ("<li>ERROR : ".$result_class->getMessage()."<li>$SQL");
					
				} else {
					if($pp_class = $result_class->fetchRow())
					{
						$class_user = array();
						$SQL = "SELECT `pp_class_user`.`id_class`, `pp_class_user`.`id_user`, `pp_class_user`.`class`, `pp_class_user`.`evolution`
								FROM `pp_class`
								INNER JOIN `pp_class_user` ON `pp_class`.`last_id_matches`=`pp_class_user`.`id_matches` AND `pp_class`.`id_class`=`pp_class_user`.`id_class`
								WHERE `pp_class_user`.`id_user`='".$userprofil->id_user."' AND `pp_class`.`id_class`='".$pp_class->id_class."'";
						$result_class = $db->query($SQL);
						//echo "<li>$SQL";
						if(DB::isError($result_class))
						{
							die ("<li>ERROR : ".$result_class->getMessage());
							
						} else {	
							while($pp_class_user = $result_class->fetchRow())
							{
								$class_user[$pp_class_user->id_class]['class'] = $pp_class_user->class;
								$class_user[$pp_class_user->id_class]['evolution'] = $pp_class_user->evolution;
							}
						}
				?>
                <tr>
                  <td align="right"><a href="class.php?id=<?php echo $pp_class->id_class?>&rech_jpseudo=<?php echo urlencode($userprofil->login)?>&search_joueur=1"><?php echo $pp_class->label?></a> : </td>
                  <td nowrap>
				  <?php
				  if($class_user[$pp_class->id_class]['class'])
				  {
				  ?>
				  <a href="class.php?id=<?php echo $pp_class->id_class?>&rech_jpseudo=<?php echo urlencode($userprofil->login)?>&search_joueur=1"><?php echo $class_user[$pp_class->id_class]['class']?>
					<?php
					if($class_user[$pp_class->id_class]['evolution']<0) {
						echo "<font color=\"red\" style=\"font-family:Arial; font-size:8px;\">(".$class_user[$pp_class->id_class]['evolution'].")</font>";
					} elseif($class_user[$pp_class->id_class]['evolution']>0) {
						echo "<font color=\"green\">(+".$class_user[$pp_class->id_class]['evolution'].")</font>";
					}
					?>
					</a>
				  <?php
				  } else echo '-';
				  ?>
				  </td>
                </tr>
				<?php
					}
				}
				?>
				</table>
			  
				<?php
				if($userprofil->id_user == $user->id_user)
				{
					$linkclass = "/classements.php";
				} else {
					$linkclass = "/classements.php?q=".urlencode(htmlspecialchars($userprofil->login));
				}
				?>
				<ul>
					<li><a href="<?php echo $linkclass; ?>">+ de stats</a></li>
					<li><a href="/recompenses.php?q=<?php echo urlencode(htmlspecialchars($userprofil->login)); ?>">Récompenses</a></li>
					<li><a href="/palmares-archives.php?q=<?php echo urlencode(htmlspecialchars($userprofil->login)); ?>">Archives palmarès</a></li>
				</ul>
			  
		    </div>
			<?php
			}
			?>
		</div>
<?php
}


function getRandomFriends($pp_user)
{
	global $db;
	// Les amis de l'utilisateur
	$html = '';
	$SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`,
				`pp_user_friends`.`id_user_listfriends`
			FROM `pp_user_friends`
			INNER JOIN `pp_user` ON `pp_user`.`id_user` = `pp_user_friends`.`id_user_friend`
			WHERE `pp_user_friends`.`id_user`='".$pp_user->id_user."' AND `pp_user_friends`.`valide`='1'
			ORDER BY RAND()
			LIMIT 6";
	$result_user_friends = $db->query($SQL);
	if(DB::isError($result_user_friends)) die ("<li>ERROR : ".$result_user_friends->getMessage());
	if($nb_friends = $result_user_friends->numRows())
	{
		while($pp_user_friends = $result_user_friends->fetchRow())
		{
			$avatar = getAvatar($pp_user_friends->id_user, $pp_user_friends->avatar_key, $pp_user_friends->avatar_ext, 'small');
			$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';					
			$html .= '<a href="/user.php?q='.urlencode(htmlspecialchars($pp_user_friends->login)).'" class="link_button" style="margin:0 4px 4px 0"><img src="'.$avatar.'" height="29" width="29" border="0" align="absmiddle" />&nbsp;'.str_replace(' ', '&nbsp;', $pp_user_friends->login).'</a>';
		}

	} else {
		$html = '<p>Pas encore d\'amis !</p>';
	}
	
	$html .= '<div style="clear:both;"></div>';
	
	return $html;
}


// les amis en commun entre $user1 et $user2
function getSameFriends($user1, $user2)
{
	global $db;
	
	// Les amis de l'utilisateur
	$html = '';
	$SQL = "SELECT `pp_user_friends`.`id_user_friend`
			FROM `pp_user_friends`
			WHERE `pp_user_friends`.`id_user`='".$user2->id_user."' AND `pp_user_friends`.`valide`='1'";
	$result_user_friends = $db->query($SQL);
	if(DB::isError($result_user_friends)) die ("<li>ERROR : ".$result_user_friends->getMessage());
	if($nb_friends = $result_user_friends->numRows())
	{
		$list_ids_friends = '';
		while($pp_user_friends = $result_user_friends->fetchRow())
		{
			$list_ids_friends .= ($list_ids_friends!='' ? ',' : '') . $pp_user_friends->id_user_friend;
		}
		
		$SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`,
					`pp_user_friends`.`id_user_listfriends`
				FROM `pp_user_friends`
				INNER JOIN `pp_user` ON `pp_user`.`id_user` = `pp_user_friends`.`id_user_friend`
				WHERE `pp_user_friends`.`id_user`='".$user1->id_user."' AND `pp_user_friends`.`valide`='1'
				AND `pp_user_friends`.`id_user_friend` IN (".$list_ids_friends.")
				ORDER BY `pp_user`.`login`";
		$result_user_friends = $db->query($SQL);
		if(DB::isError($result_user_friends)) die ("<li>ERROR : ".$result_user_friends->getMessage());
		if($nb_friends = $result_user_friends->numRows())
		{
			while($pp_user_friends = $result_user_friends->fetchRow())
			{
				$avatar = getAvatar($pp_user_friends->id_user, $pp_user_friends->avatar_key, $pp_user_friends->avatar_ext, 'small');
				$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';					
				$html .= '<a href="/user.php?q='.urlencode(htmlspecialchars($pp_user_friends->login)).'" class="link_button" style="margin:0 4px 4px 0"><img src="'.$avatar.'" height="29" width="29" border="0" align="absmiddle" />&nbsp;'.str_replace(' ', '&nbsp;', $pp_user_friends->login).'</a>';
			}

		} else {
			$html = '<p>Aucun ami en commun !</p>';
		}

	} else {
		$html = '<p>Aucun ami en commun !</p>';
	}

	$html .= '<div style="clear:both;"></div>';
	
	return $html;
}


function getContentRight()
{
	global $db, $txtlang, $user;
?>
	<div id="content_right">
		
	<?php
	getRightBulle();
	getRightProfil();
	?>	
		
		<ul class="list_sortable">
		
			
		<?php
		if($user->id_user)
		{
			?>
			<li>
				<h2 class="title_blue">Amis au hasard</h2>
				<div class="bloc_content">
				<?php echo getRandomFriends($user); ?>
				<hr /><div style="text-align:center"><a href="/friends.php" class="link_orange">Activités de mes amis</a></div>
				</div>
			</li>
		
			<li>
				<h2 class="title_green">Coup de chapeau !</h2>
				<div class="bloc_content"><?php echo CoupDeChapeau(); ?></div>
			</li>
			<?php
		}
		?>
		
		
		
			<li>
				<h2 class="title_grey">Nouveaux joueurs</h2>
				<div class="bloc_content">
				<?php
					$html = '';
					$SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`,
								DATE_FORMAT(`pp_user`.`register_date`, '".$txtlang['AFF_DATE_SQL']."') AS `register_date_format`
							FROM `pp_user` 
							WHERE `last_ip` != ''
							ORDER BY `register_date` DESC
							LIMIT 6";
					$result_newusers = $db->query($SQL);
					if(DB::isError($result_newusers)) die ("<li>ERROR : ".$result_newusers->getMessage());
					while($pp_newuser = $result_newusers->fetchRow())
					{
						$avatar = getAvatar($pp_newuser->id_user, $pp_newuser->avatar_key, $pp_newuser->avatar_ext, 'small');
						$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';					
						//$html .= '<div style="float:left; margin:4px; padding:4px; border:1px solid #ccc; background:#eee;"><a href="/user.php?q='.urlencode(htmlspecialchars($pp_newuser->login)).'" class="link_orange"><img src="'.$avatar.'" height="29" width="29" border="0" align="absmiddle" />&nbsp;'.str_replace(' ', '&nbsp;', $pp_newuser->login).'</a><br /><span class="petitgris">Le&nbsp;'.$pp_newuser->register_date_format.'</span></div>';
						$html .= '<a href="/user.php?q='.urlencode(htmlspecialchars($pp_newuser->login)).'" class="link_button" style="margin:0 4px 4px 0"><img src="'.$avatar.'" height="29" width="29" border="0" align="absmiddle" />&nbsp;'.str_replace(' ', '&nbsp;', $pp_newuser->login).'<br /><span class="petitgris">Le&nbsp;'.$pp_newuser->register_date_format.'</span></a>';
					}					
					$html .= '<div style="clear:both;"></div>';
					echo $html;
				?>
				</div>
			</li>
			
			
			
			
			
			<?php
			/*
			<li>
				<h2 class="title_blueking">L'équipe de France</h2>
				<div class="bloc_content"><a href="/equipe-de-france-de-football.php" class="link_orange"><img src="/template/default/pronoplus-equipedefrance_preview.png" height="81" width="230" border="0" style="margin-bottom:10px;" /><br />Le <strong>calendrier</strong>, les <strong>résultats</strong> et l'<strong>actualités</strong> des <strong>Bleus</strong> sur Prono+</a></div>
			</li>
			*/
			?>
			
			
			
			
			<?php
			// Liste championnats
			$html = '';
      $SQL = "SELECT id_league, label, flag
          FROM `pp_league`
          WHERE `afficher_classement`='1'
            AND id_league IN (SELECT DISTINCT id_league FROM `pp_info_matches`)
          ORDER BY `ordre`";
			$result = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result))
			{
				die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
				
			} else {
				while($pp_league_info = $result->fetchRow())
				{
					$html .= '<a href="/stats-classement.php?id='.$pp_league_info->id_league.'" class="link_button" style="margin-bottom:4px;"><img src="/image/flags/'.$pp_league_info->flag.'" border="0" align="absmiddle" />&nbsp;'.$pp_league_info->label.'</a>&nbsp;';
				}
			}
			?>
			<li>
				<h2 class="title_orange">Résultats des championnats</h2>
				<div class="bloc_content">
				<?php echo $html; $html = ''; ?>
				
				<?php      
			        $SQL = "SELECT `n`.`id_info_match`,
			                    AVG(`n`.`note`) AS `note_avg`,
		                        `team_host`.`label` AS `team_host_label`,
		                        `team_visitor`.`label` AS `team_visitor_label`,
		                        `m`.`score`
					        FROM `pp_info_match_note` AS `n`
					            INNER JOIN `pp_info_match` AS `m` ON `m`.`id_info_match` = `n`.`id_info_match`
	                            INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`m`.`id_team_host`
	                            INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`m`.`id_team_visitor`
					        GROUP BY `n`.`id_info_match`
					        ORDER BY DATE_FORMAT(`n`.`date_update`, '%Y-%m-%d') DESC, `note_avg` DESC
					        LIMIT 6";
			        $result_notes = $db->query($SQL);
			        if(DB::isError($result_notes)) die ("<li>ERROR : ".$result_notes->getMessage());
			
			        if($result_notes->numRows())
			        {					    
			            $i=0;
			            
			            echo '<hr /><strong>Dernières notes</strong>';
			            
			            while($pp_match_note = $result_notes->fetchRow())
			            {                            
                            
                            echo '<div class="'. ($i%2 == 1 ? 'ligne_blanche' : 'ligne_grise') .'" style="padding:2px 0">';
			                
			                echo '<a href="/info_match.php?id='.$pp_match_note->id_info_match.'" class="link_orange">';
			                
			                echo '<span style="display:block; float:left; width:28%; text-align:right">';
			                echo htmlspecialchars($pp_match_note->team_host_label);  
			                echo '</span>';
			                
			                echo '<span style="display:block; float:left; width:14%; text-align:center">';
			                echo $pp_match_note->score ? $pp_match_note->score : '-';
			                echo '</span>';
			                
			                echo '<span style="display:block; float:left; width:28%">';
			                echo htmlspecialchars($pp_match_note->team_visitor_label);
			                echo '</span>';
			                
			                echo '<span style="display:block; float:left; width:17%; text-align:right">';
			                echo '<strong>'.round($pp_match_note->note_avg, 1) .'</strong>/20';
			                echo '</span>';
			                
			                echo '<span style="display:block; float:left; width:13%; text-align:right">';
			                echo '<img src="/template/default/comment.gif" align="absmiddle" border="0" />';
			                echo pp_comments_nb('info_match', $pp_match_note->id_info_match);
			                echo '</span>';
			                			                
			                echo '</a>';
			                echo '<div class="clear"></div>';
			                
                            echo '</div>';
                            $i++;
                            
			            }		    
		            }
		        ?>
				</div>
			</li>
			
			
			<?php
      $posts = PreviewBlog( array('days_expire' => 10, 'nbelements' => 5) );
      if(trim($posts))
      {
        ?>
        <li>
          <h2 class="title_orange">Les derniers articles du blog</h2>
          <div class="bloc_content"><?php echo $posts; ?></div>
        </li>
        <?php
      }
      ?>
			

			

			
			
			

			<?php
      if(!$user->id_user)
      {
				// matchs mis en avant ?
				$content = array();
				$SQL = "SELECT `pp_match`.`id_matches`, `team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`,
						`team_host`.`featured`+`team_visitor`.`featured` AS `featured_weight`
						FROM `pp_match`
						INNER JOIN `pp_matches` ON `pp_matches`.`id_matches` = `pp_match`.`id_matches`
						INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
						INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
						WHERE `pp_matches`.`is_calcul` != '1' AND `pp_matches`.`image` != 'ligue2.png'
							AND `pp_matches`.`id_cup_matches` = 0
							AND `pp_match`.`date_match` > NOW()
						ORDER BY `featured_weight` DESC
						LIMIT 6";
				$result_match = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result_match))
				{
					die ("<li>ERROR : ".$result_match->getMessage());
					
				} else {
					while($pp_match = $result_match->fetchRow())
					{
						$content[] = '<a href="/pronostiquer.php?id='.$pp_match->id_matches.'" class="link_orange">Pronostic '.formatDbData($pp_match->team_host_label).' - '.formatDbData($pp_match->team_visitor_label).'</a>';
					}
				}
				if(count($content))
				{
					?>
					<li>
						<h2 class="title_green">Matchs à ne pas manquer</h2>
						<div class="bloc_content">
							<?php echo implode(', ', $content); ?>
						</div>
					</li>
					<?php
				}
      }
			?>
				
			
			
			<?php
			/*
			<li>
				<?php <h2 class="title_blueking">Prono+ sur Facebook</h2> ?>
				<div class="bloc_content" style="padding:0">
					<?php if($_SERVER['HTTP_HOST'] == 'www.pronoplus.com') { ?>
					<!--
					<script type="text/javascript" src="http://static.ak.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/fr_FR"></script>
					<script type="text/javascript">FB.init("5742344b18e179b243f7dc3fb692f59f");</script>
					<fb:fan profile_id="134326005820" stream="false" connections="4" width="238"></fb:fan>-->
					<iframe src="http://www.facebook.com/plugins/likebox.php?id=134326005820&amp;width=238&amp;connections=0&amp;stream=false&amp;header=false&amp;height=62" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:238px; height:62px;" allowTransparency="true"></iframe>
					<?php } ?>
				</div>
			</li>
			*/
			?>

            <li>
                <h2 class="title_blue">Aider Prono+</h2>
                <div class="bloc_content">
                    <p>Prono+ existe depuis 2000 et ne rapporte pas grand chose à son webmaster. Ce site est un investissement important en temps et en frais d'hébergement (environ 230€/an serveur dédié). Si vous souhaitez aider Prono+ à sa maintenance, son hébergement et son évolution, donnez !</p>
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                        <input type="hidden" name="cmd" value="_s-xclick">
                        <input type="hidden" name="hosted_button_id" value="XJRNLT6J8HP2L">
                        <input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
                        <img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
                    </form>
                </div>
            </li>
			
			
			<li><div class="bloc_content">
				<a href="/blog/contact" class="link_orange"><img src="/template/default/icon_email.gif" align="absmiddle" border="0" /> <b>Contacter le webmaster</b><br />Faire un feedback, proposer une idée ou signaler un problème ?</a>
			</div></li>
			
		</ul>
	</div>
<?php
}



function PostIt()
{
	global $user, $db, $txtlang;	
	
	$SQL = "SELECT `pp_postit`.`id_postit`, `pp_postit`.`message`,
	            DATE_FORMAT(`pp_postit`.`date_message`, '".$txtlang['AFF_DATE_SQL']."') AS `date_message_format`
			FROM `pp_postit`
			LEFT JOIN `pp_postit_user` ON `pp_postit`.`id_postit` = `pp_postit_user`.`id_postit` AND `pp_postit_user`.`id_user` = '".$user->id_user."'
			WHERE `pp_postit`.`active`='1'
			    AND `pp_postit_user`.`id_postit` IS NULL
			    AND (`pp_postit`.`id_user` = 0 OR `pp_postit`.`id_user` = ".$user->id_user.")
			    ORDER BY `date_message` DESC
			LIMIT 1";
	$result_postit = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_postit))
	{
		die ("<li>ERROR : ".$result_postit->getMessage()."<li>$SQL");
		
	} else {

		if($postit = $result_postit->fetchRow())
		{
			$content = "<p class=\"message_error\">".$postit->message."</p><p align=\"center\"><small>Alerte reçue le ".$postit->date_message_format."</small><br /><a href=\"#\" onclick=\"close_postit(".$postit->id_postit.");\" class=\"link_orange\"><img src=\"/template/default/close.gif\" align=\"absmiddle\" border=\"0\" /> Supprimer cette alerte</a></p>";
			$content .= "<script>
			function close_postit(id_postit)
			{
				Effect.toggle('postit_bloc', 'slide', {duration:0.3});
				new Ajax.Request('actions.php', {
				  method: 'post',
				  parameters:'action=deletepostit&id_postit='+id_postit
				});
			}
			</script>";
			
			return getBlocLeft('Alerte !', 'orange', $content, 'postit_bloc', '', false);
		}
	}	
}




function CoupDeChapeau()
{
	global $user, $db;
	// recherche coupe
	$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label`, `pp_cup_match_opponents`.`id_user_won`
			FROM `pp_cup`
			INNER JOIN `pp_cup_match_opponents` ON `pp_cup_match_opponents`.`id_cup` = `pp_cup`.`id_cup`
			WHERE `pp_cup_match_opponents`.`cup_sub`=1 AND `pp_cup_match_opponents`.`number_tour`=4 AND `pp_cup_match_opponents`.`id_user_won`!=0
			ORDER BY `pp_cup`.`id_cup` DESC
			LIMIT 1";
	$result_cup = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_cup))
	{
		die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
		
	} else {

		if($pp_cup = $result_cup->fetchRow())
		{
			$joueur = nom_joueur($pp_cup->id_user_won);
			$avatar = getAvatar($pp_cup->id_user_won, $joueur->avatar_key, $joueur->avatar_ext, 'small');
			$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
			
			echo "<div><strong><a href=\"/cup.php?id=".$pp_cup->id_cup."&division=1\" class=\"link_orange\">".$pp_cup->label."</a></strong><br /><a href=\"/user.php?q=".urlencode(htmlspecialchars($joueur->login))."\" class=\"link_orange\"><img src=\"".$avatar."\" vspace=\"4\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" />&nbsp;".$joueur->login."</a></div><hr>";
		}
	}



	// recherche Classement mensuel
	$SQL = "SELECT `id_class`, `label`
			FROM `pp_class`
			WHERE `close`='1' AND type='month' AND last_id_matches!=0
			ORDER BY `order` ASC
			LIMIT 1";
	$result_class = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_class))
	{
		die ("<li>ERROR : ".$result_class->getMessage()."<li>$SQL");
		
	} else {

		if($pp_class = $result_class->fetchRow())
		{
			$SQL = "SELECT `id_user`
					FROM `pp_class_user`
					WHERE `id_class`='".$pp_class->id_class."'
					ORDER BY `nb_points` DESC
					LIMIT 1";
			$result_cup_user = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_cup_user))
			{
				die ("<li>ERROR : ".$result_cup_user->getMessage()."<li>$SQL");
				
			} else {
				if($cup_user = $result_cup_user->fetchRow())
				{
					$joueur = nom_joueur($cup_user->id_user);
					$avatar = getAvatar($cup_user->id_user, $joueur->avatar_key, $joueur->avatar_ext, 'small');
					$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
					
					echo "<div><strong><a href=\"/class.php?id=".$pp_class->id_class."\" class=\"link_orange\">".$pp_class->label."</a></strong><br /><a href=\"/user.php?q=".urlencode(htmlspecialchars($joueur->login))."\" class=\"link_orange\"><img src=\"".$avatar."\" height=\"29\" width=\"29\" vspace=\"4\" border=\"0\" align=\"absmiddle\" />&nbsp;".$joueur->login."</a></div><hr>";
				}
			}
		}
	}
	
	echo "<div style=\"text-align:center\"><a href=\"/palmares.php\" class=\"link_orange\">Tout le palmarès</a> | <a href=\"/palmares-archives.php\" class=\"link_orange\">Archives </a></div>";
}





function PreviewBlog($options=false)
{
	global $db,$txtlang;
	$content = "";
	
	/*
	$options=
		'category_id' = X
		'nbelements' = 3
		'days_expire' = 7
		'type' => 'alaune'
		'twocolumns' => true | false
	*/

	$SQL = "SELECT wp_posts.ID, DATE_FORMAT(wp_posts.post_date, '%d-%m-%Y') AS urldate,
				wp_posts.post_title, wp_posts.post_name, wp_posts.comment_count,
				DATE_FORMAT(wp_posts.post_date, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `post_date_format`
			FROM wp_posts
			".($options['category_id'] ? "INNER JOIN wp_term_relationships ON wp_posts.ID = wp_term_relationships.object_id" : "")."
			WHERE wp_posts.post_type='post' AND wp_posts.post_status='publish'
			".($options['category_id'] ? "AND wp_term_relationships.term_taxonomy_id = '".$options['category_id']."'" : "")."
			".($options['days_expire']*1 ? "AND DATE_ADD(wp_posts.post_date, INTERVAL ".($options['days_expire'] * 1)." DAY) > NOW()" : "")."
			ORDER BY wp_posts.post_date DESC
			LIMIT ".($options['nbelements'] ? $options['nbelements'] : 5);
	$result_post = $db->query($SQL);
	if(DB::isError($result_post))
	{
		die ("<li>ERROR : ".$result_post->getMessage());
		
	} else {
	
		if($options['twocolumns'])
		{
			$nbresults = $result_post->numRows();
			$nbelements_first_column = floor($nbresults / 2);
		}
		
		$i_element = 1;
	
		while($post = $result_post->fetchRow())
		{
			$thumbnail = false;
			$SQL = "SELECT wp_postmeta.meta_value
					FROM wp_posts
					LEFT JOIN wp_postmeta ON wp_postmeta.post_id=wp_posts.ID
					WHERE wp_postmeta.meta_key='_wp_attachment_metadata'
					AND wp_posts.post_parent='".$post->ID."'
					LIMIT 1";			
			$result_image = $db->query($SQL);
			if(DB::isError($result_post))
			{
				die ("<li>ERROR : ".$result_image->getMessage());
				
			} else {
				if($image = $result_image->fetchRow())
				{
					$meta = unserialize($image->meta_value);
					
					$thumbnail = '';
				
					if($options['type'] == 'alaune' && $meta['file'] && $meta['sizes']['medium']['file'])
						$thumbnail = str_replace('/home/pronoplu/www', '', dirname($meta['file'])).'/'.$meta['sizes']['medium']['file'];

					if(!$thumbnail && $meta['file'] && $meta['sizes']['thumbnail']['file'])
						$thumbnail = str_replace('/home/pronoplu/www', '', dirname($meta['file'])).'/'.$meta['sizes']['thumbnail']['file'];
						
					if($thumbnail && strpos($thumbnail, '/wp-content/uploads/') === false)
					{
						$thumbnail = '/blog/wp-content/uploads/'.$thumbnail;						
					}
					
					if(!file_exists($_SERVER['DOCUMENT_ROOT'].$thumbnail)) $thumbnail = false;
				}
			}
			
			if($options['type'] != 'alaune' && $options['twocolumns'] && $i_element==1)
			{
				$content .= "<div style=\"float:left; width:45%; margin-right:10px;\">";
			}
			
			$content .= $options['type'] == 'alaune' ? "<h2><a href=\"/blog/\">Sur le blog de Prono+ :</a></h2>" : "";
			
			$content .= ($options['type'] == 'alaune' ? "<h2>" : "<div style=\"margin-top:6px;\">")."<a href=\"/blog/".$post->post_name.'-'.$post->urldate.".html\" class=\"link_orange\" style=\"display:block; width:100%\"><img src=\"".($thumbnail ? $thumbnail : "/template/default/blog-foot-ico.gif")."\" border=\"0\" style=\"float:left; padding-right:6px;\" ".($options['type'] != 'alaune' ? "height=\"60\" width=\"60\"" : "height=\"110\"")." /><strong>" . ($options['type'] == 'alaune' ? "<span style=\"font-size:16px;\">" : "") . $post->post_title . ($options['type'] == 'alaune' ? "</span>" : "") . "</strong><br /><br /><img src=\"/blog/wp-content/themes/pronoplus/images/comment.gif\" border=\"0\" align=\"absmiddle\" /> ".($post->comment_count > 0 ? $post->comment_count.($post->comment_count > 1 ? " commentaires" : " commentaire") : "Commenter cet article")."</a>".($options['type'] == 'alaune' ? "</h2>" : "</div><div class=\"clear\"></div>");

			if($options['type'] != 'alaune' && $options['twocolumns'] && $i_element==$nbelements_first_column)
			{
				$content .= "</div>";
				$i_element = 0;
			}
			
			if($options['type'] == 'alaune')
			{
				$arr_content[] = $content;
				$content = '';
			}

			$i_element++;
			
		}
		if($options['type'] != 'alaune' && $content) $content .= "<div class=\"clear\"></div>";
	}
	
	if($options['type'] != 'alaune') return $content;
		else return $arr_content;
}






/*
function PreviewMatches($id_matches)
{
	global $db, $user, $txtlang;
	$content = "";
	
	$SQL = "SELECT `pp_matches`.`label`, `pp_matches`.`image`,
			DATE_FORMAT(`pp_matches`.`date_first_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_first_match_format`,
			DATE_FORMAT(`pp_matches`.`date_last_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_last_match_format`,
			TIMEDIFF(NOW(), `pp_matches`.`date_first_match`) AS `diff_date_first_match`,
			TIMEDIFF(NOW(), `pp_matches`.`date_last_match`) AS `diff_date_last_match`,
			`pp_info_country`.`label` AS `country`
			FROM `pp_matches`
			INNER JOIN `pp_info_country` ON `pp_info_country`.`id_info_country`=`pp_matches`.`id_info_country`
			WHERE `pp_matches`.`id_matches`='".$id_matches."'";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if($pp_matches = $result->fetchRow())
		{
			$content .= "<h3><img src=\"template/default/".$pp_matches->image."\" style=\"margin-right:10px; margin-bottom:10px; float:left; border:solid 3px #eee;\" /><a href=\"pronostiquer.php?id=".$id_matches."\">".formatDbData($pp_matches->label)."</a> <span class=\"red\">".format_diff_date($pp_matches->diff_date_first_match)."</span></h3>
							<p>A pronostiquer avant le ".$pp_matches->date_first_match_format.".<br />
							Dernier match &agrave; pronostiquer avant le ".$pp_matches->date_last_match_format." (".format_diff_date($pp_matches->diff_date_last_match).")</p>
							";			
			
			$content .= getMatchesClass($id_matches);			
			
			$content .= "<table class=\"table_match\" align=\"center\">";
								
			$SQL = "SELECT `pp_match`.`id_match`, `team_host`.`label` AS `team_host_label`, `team_visitor`.`label` AS `team_visitor_label`,
					DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`,
					TIMEDIFF(NOW(), `pp_match`.`date_match`) AS `diff_date_match`
					FROM `pp_match`
					INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
					INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
					WHERE `pp_match`.`id_matches`='".$id_matches."'
					ORDER BY `pp_match`.`date_match`";
			$result_match = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_match))
			{
				die ("<li>ERROR : ".$result_match->getMessage());
				
			} else {
				while($pp_match = $result_match->fetchRow())
				{
					$content .= "<tr>
							<td align=\"right\" ".($pp_match->diff_date_match>0 ? "class=\"strike\"" : "")." width=\"49%\">".formatDbData($pp_match->team_host_label)."</td>
							<td>-</td>
							<td ".($pp_match->diff_date_match>0 ? "class=\"strike\"" : "")."  width=\"49%\">".formatDbData($pp_match->team_visitor_label)."</td>
						</tr>";
				}
			}
			$content .= "<tr><td colspan=\"3\"><p class=\"center\">&nbsp;<br /><a href=\"pronostiquer.php?id=".$id_matches."\" class=\"link_button\">Pronostiquer</a><br />&nbsp;</p></td></tr></table>";
		}
	}
	
	return getBlocLeft('Prochaine grille &agrave; pronostiquer', 'green', $content, '', false);
}
*/



function LiveScore()
{
	global $db, $user, $txtlang;
	
	$nb_matchs = 4;
	
	// recherche matchs
	$matches = array();
	$SQLr = "SELECT `pp_match`.`id_match`, `pp_match`.`id_info_match`, `pp_match`.`id_matches`,
				`team_host`.`label` AS `team_host_label`, `team_host`.`flag` AS `team_host_flag`,
				`team_visitor`.`label` AS `team_visitor_label`, `team_visitor`.`flag` AS `team_visitor_flag`,
				`pp_match`.`score`,
				`team_host`.`featured`+`team_visitor`.`featured` AS `featured_weight`,
				 TIMEDIFF( NOW(), `pp_match`.`date_match` ) AS `date_match_diff`,
				DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_match_format`,
				DATE_FORMAT(`pp_match`.`date_match`, '".$txtlang['AFF_TIME_SQL']."') AS `heure_match_format`,
				`pp_match_user`.`score` AS `score_user`
			FROM `pp_match`
			INNER JOIN `pp_team` AS `team_host` ON `team_host`.`id_team`=`pp_match`.`id_team_host`
			INNER JOIN `pp_team` AS `team_visitor` ON `team_visitor`.`id_team`=`pp_match`.`id_team_visitor`
			INNER JOIN `pp_matches` ON `pp_matches`.`id_matches` = `pp_match`.`id_matches` AND `pp_matches`.`id_cup_matches`=0
			LEFT JOIN `pp_match_user` ON `pp_match_user`.`id_match` = `pp_match`.`id_match` AND `pp_match_user`.`id_user` = '".$user->id_user."'
			";
	
	
  // En cours
  $matches_en_cours = array();
	$SQL = $SQLr . " WHERE DATE_ADD(`pp_match`.`date_match`, INTERVAL -1 MINUTE) < NOW()
        AND DATE_ADD(`pp_match`.`date_match`, INTERVAL 110 MINUTE) > NOW()
        AND `pp_match`.score != 'R-R'
        ORDER BY `pp_match`.`date_match` ASC, `featured_weight` DESC";
	$result_match = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_match))
	{
		die ("<li>ERROR : ".$result_match->getMessage());
		
	} else {
		while($pp_match = $result_match->fetchRow())
		{
			$matches_en_cours[$pp_match->id_match] = $pp_match;
		}
	}
  
  // Terminé
  $matches_termine = array();
	$SQL = $SQLr . " WHERE DATE_ADD(`pp_match`.`date_match`, INTERVAL 111 MINUTE) < NOW()
      AND `pp_match`.score != 'R-R'
			ORDER BY `pp_match`.`date_match` DESC, `featured_weight` DESC
			LIMIT $nb_matchs";
	$result_match = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_match))
	{
		die ("<li>ERROR : ".$result_match->getMessage());
		
	} else {
		while($pp_match = $result_match->fetchRow())
		{
			$matches_termine[$pp_match->id_match] = $pp_match;
		}
	}
	
	// à venir 
	$matches_a_venir = array();
	$SQL = $SQLr . " WHERE DATE_ADD(`pp_match`.`date_match`, INTERVAL -1 MINUTE) >= NOW()
      AND `pp_match`.score != 'R-R'
			ORDER BY `pp_match`.`date_match` ASC, `featured_weight` DESC
			LIMIT $nb_matchs";
	$result_match = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_match))
	{
		die ("<li>ERROR : ".$result_match->getMessage());
		
	} else {
		while($pp_match = $result_match->fetchRow())
		{
			$matches_a_venir[$pp_match->id_match] = $pp_match;
		}
	}



	$content = '';
	$content .= '<div style="overflow:hidden"><table width="100%" cellpadding="4" cellspacing="1">';	
	if(count($matches_en_cours)) $content .= LiveScoreTable('En cours', $matches_en_cours);
	if(count($matches_termine)) $content .= LiveScoreTable('Terminé', $matches_termine);
	if(count($matches_a_venir)) $content .= LiveScoreTable('A venir', $matches_a_venir);
	$content .= "</table></div>";

	if(count($matches_en_cours) || count($matches_termine) || count($matches_a_venir)) $content = getBlocLeft('<span style="float:right;">'.date($txtlang[AFF_DATE_TIME_PHP]).'</span>En direct', 'blue', $content, '', false);
	
	return $content;
}


function LiveScoreTable($title, $matches)
{
	$content = '';
	$content .= '<tr><th colspan="3" align="left">' .$title. '</th><th width="1%"></th><th align="right"><a href="/agenda-football.php" class="link_orange" style="font-weight:normal">Tout l\'agenda football</a></th></tr>';
	
	$nmatch=0;
	
	$date_match_diff_current = '';
	$date_match_format_current = '';
	
	foreach($matches as $id_match=>$match) if($match->score != "R-R")
	{
		$nmatch++;
	
		$url_info_match = "/classj.php?id=".$match->id_matches;

		$content .= '<tr>';
		$content .= '<td align="right"><a href="'.$url_info_match.'" class="link_orange">'
					. $match->team_host_label
					. ($match->team_host_flag && $match->team_visitor_flag ? '&nbsp;<img src="/image/flags/'.$match->team_host_flag.'" align="absmiddle" border="0" />': '' )
					. '</a></td>';
		$content .= '<td bgcolor="#eeeeee" align="center" nowrap><a href="' . $url_info_match . '" class="link_orange">';
		
		if($match->score == "R-R") {
			$content .= '<font color=\"red\">Annul&eacute;</font>';
		} else {
			$content .= $match->score ? $match->score : '-';
		}
		
		$content .= '</a></td>';
		$content .= '<td><a href="' . $url_info_match . '" class="link_orange">'
            . ($match->team_host_flag && $match->team_visitor_flag ? '<img src="/image/flags/'.$match->team_visitor_flag.'" align="absmiddle" border="0"  />&nbsp;' : '') . $match->team_visitor_label
            . '</a></td>';

        if($match->score_user)
        {
            $class_match = '';
            if($match->date_match_diff[0] == '-' || $match->score_user == 'R-R')
            {
                $class_match = 'result_neutre';

            } elseif($match->score_user == $match->score)
            {
                $class_match = 'result_gagne';

            } elseif(
                ($match->score_user[0] > $match->score_user[2] && $match->score[0] > $match->score[2])
                || ($match->score_user[0] < $match->score_user[2] && $match->score[0] < $match->score[2])
                || ($match->score_user[0] == $match->score_user[2] && $match->score[0] == $match->score[2])
            ) {
                $class_match = 'result_nul';

            } else {
                $class_match = 'result_defaite';
            }

            $content .= '<td class="'.$class_match.'" style="text-align:center" nowrap>
                <a href="'.$url_info_match.'" style="color:#fff; text-decoration:none; font-weight:bold;">'
                . $match->score_user
                . '</a></td>';

        } elseif($match->date_match_diff[0] == '-') {
            $content .= '<td style="text-align:center"><a href="/pronostiquer.php?id='.$match->id_matches.'" class="link_button">P+</a></td>';
        } else {
            $content .= '<td style="text-align:center">-</td>';
        }
		
		// $content .= '<td class="result_gagne">0-0</td>';
		
		$content .= '<td align="right" nowrap="nowrap" style="font-size:10px;">';
		
		$is_de_meme = false;
		
		$tabd = explode(':', $match->date_match_diff);
		if( !(substr($tabd[0],0,1) == '-' || $tabd[0]*1 > 1 || $tabd[0]*1 == 1 && $tabd[1]*1 >= 49) )
		{
            $minutes = '';
            $minutes_txt = '';

            if($tabd[0]*1 == 0 && $tabd[1]*1 >= 46 || $tabd[0]*1 == 1 && $tabd[1]*1 <= 1)
            {
                // c'est la mi temps
                //if($match->score != "R-R") $content .= 'mi-temps';
                $minutes_txt = 'mi-temps';
                $minutes = 45;

            } else {
                // affichage des minutes durant le match
                $diffdatetab = explode(':', $match->date_match_diff);
                $minutes = $diffdatetab[0]*60;
                if($diffdatetab[1] * 1) $minutes += $diffdatetab[1]*1;
                //$content .= format_diff_date($match->date_match_diff, $is_signe=true);
                if($minutes == 46) $minutes = 45;
                if($minutes > 46) $minutes = $minutes - 17;
                //$content .= $minutes.'&quot;';
                if($match->score != "R-R") $minutes_txt = $minutes.'&quot;';
            }



            //$content .= '<span style="float:left;"><a class="link_button_urgent" href="' . $url_info_match . '">';
			if($match->score != "R-R") $content .= '<div style="float:left; width:150px; background:#f5f5f5">
			    <a class="link_button_urgent" href="' . $url_info_match . '" style="float:left; width:'.round(($minutes*150/90) - 16).'px;">';

            $content .= $minutes_txt;

			//$content .= '</a></span>';
			if($match->score != "R-R") $content .= '</a></div>';
			
		} else if( substr($tabd[0],0,1) == '-' )
		{
			if($match->date_match_diff != $date_match_diff_current)
			{
				$content .= '<span class="red">' . format_diff_date($match->date_match_diff, $is_signe=true) . '</span>, ';
				$date_match_diff_current = $match->date_match_diff;
			} else {
				$content .= '&quot;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$is_de_meme = true;
			}
		}
		
		if($date_match_format_current != $match->date_match_format)
		{
			$date_match_format_current = $match->date_match_format;
			$diffdatetab = explode(':', $match->date_match_diff);
			if($diffdatetab[0]*1 <= 0 && $diffdatetab[0]*1 >= -24)
			{
				$content .= 'à '.$match->heure_match_format;
			} else {
				$content .= $match->date_match_format;
			}
			
		} elseif(!$is_de_meme) {
			$content .= '&quot;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}

		$content .= '</td></tr>';
	}
	
	return $content;
}



function CurrentMatches($id_matches=false, $options=false)
{
	global $db, $user, $txtlang;
	$content = "";
	
	$list_matches = array();
	
	$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`id_cup_matches`,
			`pp_matches`.`image`, `pp_matches`.`date_first_match`, `pp_matches`.`date_last_match`,
			if(`pp_matches`.`date_first_match` < NOW(), 1, 0) AS `show_class_prov`,
			DATE_FORMAT(`pp_matches`.`date_first_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_first_match_format`,
			DATE_FORMAT(`pp_matches`.`date_last_match`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_last_match_format`,
			TIMEDIFF(NOW(), `pp_matches`.`date_first_match`) AS `diff_date_first_match`,
			TIMEDIFF(NOW(), `pp_matches`.`date_last_match`) AS `diff_date_last_match`,

			DATE_FORMAT(`pp_matches`.`date_first_match`, '".$txtlang['AFF_TIME_SQL']."') AS `date_first_time_match_format`,
			YEAR(`pp_matches`.`date_first_match`) AS `date_first_match_year`,
			MONTH(`pp_matches`.`date_first_match`) AS `date_first_match_month`,
			DAYOFMONTH(`pp_matches`.`date_first_match`) AS `date_first_match_day`,
			DAYOFWEEK(`pp_matches`.`date_first_match`) AS `date_first_match_dayweek`,
			
			DATE_FORMAT(`pp_matches`.`date_last_match`, '".$txtlang['AFF_TIME_SQL']."') AS `date_last_time_match_format`,
			YEAR(`pp_matches`.`date_last_match`) AS `date_last_match_year`,
			MONTH(`pp_matches`.`date_last_match`) AS `date_last_match_month`,
			DAYOFMONTH(`pp_matches`.`date_last_match`) AS `date_last_match_day`,
			DAYOFWEEK(`pp_matches`.`date_last_match`) AS `date_last_match_dayweek`
			FROM `pp_matches`
			WHERE
			".($id_matches ? "`pp_matches`.`id_matches`='".$id_matches."'" : "
			`pp_matches`.`is_calcul`!='1'
			ORDER BY `date_first_match`");
			// AND DATE_ADD(`pp_matches`.`date_first_match`, INTERVAL -31 DAY) < NOW()

	$result_matches = $db->query($SQL);
	//echo "<!-- $SQL -->";
	if(DB::isError($result_matches))
	{
		die ("<li>ERROR : ".$result_matches->getMessage());
		
	} else {
		while($pp_matches = $result_matches->fetchRow())
		{
			$list_matches[$pp_matches->id_matches] = $pp_matches;
			
			// le joueur a déjà joué cette grille ?
			$list_matches[$pp_matches->id_matches]->save_yet = false;
			
			$dayweek = $pp_matches->date_first_match_dayweek;
			if($dayweek==1) $dayweek=8;
			$dayweek = $dayweek-2;
			$list_matches[$pp_matches->id_matches]->date_first_match_libelle = get_date_complete($dayweek, $pp_matches->date_first_match_day, $pp_matches->date_first_match_month-1, $pp_matches->date_first_match_year)." &agrave; ".$pp_matches->date_first_time_match_format;
			
			if($user)
			{
				$SQL = "SELECT `id_matches` FROM `pp_matches_user`
						WHERE `id_user`='".$user->id_user."' AND `id_matches`='".$pp_matches->id_matches."'";
				$result = $db->query($SQL);
				//echo "<li>$SQL";
				if(DB::isError($result))
				{
					die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
					
				} else {
					if($result->numRows()) $list_matches[$pp_matches->id_matches]->save_yet = true;
				}
			}
			
			// Classement provisoire ?
			$list_matches[$pp_matches->id_matches]->show_class_prov = $pp_matches->show_class_prov;
		}
		
		// retourner juste les infos
		if($options['return_data']) return $list_matches;
	}
	
	// liste des matchs non joués et non terminés
	$nbmatchestotal = 0;
	foreach($list_matches as $pp_matches) if(!$pp_matches->save_yet && substr($pp_matches->diff_date_last_match, 0, 1) == '-')
	{
		$nbmatchestotal++;
	}
	$nbmatches = 0;
	$nbmatches2show = 4;
    $altern = 0;
	foreach($list_matches as $pp_matches) if(!$pp_matches->save_yet && substr($pp_matches->diff_date_last_match, 0, 1) == '-')
	{
		$nbmatches++;
		
		if($altern) {
			$class_line = 'ligne_grise';
			$altern = 0;
		} else {
			$class_line = 'ligne_blanche';
			$altern = 1;
		}
		
		if($options['forcup']) $class_line = 'ligne_grise';
		
		if($pp_matches->id_cup_matches && !$options['forcup'])
		{
			$currentCup = CurrentCup($options);
			if($currentCup != '')
			{
				$content .= "<div class=\"".$class_line."\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\"><tr class=\"parent_show_on_hover\">".$currentCup."</tr></table></div>";		
				$content .= "<div class=\"clear\"></div>";
			}
		
		} else {
		
			$content .= "<div id=\"current_matches_".$nbmatches."\" class=\"".$class_line."\" ".($nbmatches > $nbmatches2show ? 'style="display:none;"' : '')."><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\"><tr>
				<td valign=\"top\" width=\"1%\"><a href=\"pronostiquer.php?id=".$pp_matches->id_matches."\"><img src=\"http://www.pronoplus.com/template/default/".$pp_matches->image."\" class=\"preview_matches_image\" border=\"0\" style=\"border:solid 3px #eee;\" alt=\"".htmlspecialchars(formatDbData($pp_matches->label))."\" /></a></td>
				<td  valign=\"top\" width=\"99%\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td valign=\"top\"><h3><a href=\"pronostiquer.php?id=".$pp_matches->id_matches."\">".formatDbData($pp_matches->label)."</a><br /><span class=\"red\">".format_diff_date($pp_matches->diff_date_first_match)."</span></h3></td>
				<td align=\"right\">";
        
      $tabb = explode(':', $pp_matches->diff_date_first_match);
      $content .= "<a href=\"pronostiquer.php?id=".$pp_matches->id_matches."\" class=\"".($tabb[0] < -24 ? "link_button" : "link_button_urgent")."\">Pronostiquer</a>";		
				
			$content .= "</td></tr></table>";
			

			
			$content .= "<br />";
			

      // commentaires PP ?
      $pp_comments_id_type = substr($pp_matches->date_first_match, 0, 4) . $pp_matches->id_matches;
      $content .= pp_comments_nb_afficher('/pronostiquer.php?id='.$pp_matches->id_matches.'#comments', 'classj', $pp_comments_id_type);
      
      
			if(!$options['forcup'])
			{
        $content .= "<div>";
				$content .= "Compte pour : ".getMatchesClass($pp_matches->id_matches, true);				

        $NBUSERS = getNbUsersMatches($pp_matches->id_matches);
        if($NBUSERS) $content .= "<br /><strong>".$NBUSERS."</strong> joueur".($NBUSERS > 1 ? 's' : '');
        
        $content .= "</div>";
      }

      // $content .= '' . facebook_libe_button('pronostiquer.php?id='.$pp_matches->id_matches, 100, 'button_count');

			
			$content .= "</td></tr></table></div><div class=\"clear\"></div>";
		}
		
		if($nbmatches == $nbmatches2show && $nbmatchestotal > $nbmatches2show)
		{
			$content .= '<div id="plus_de_matches" style="padding:4px;"><div class="div_link_plusde"><a href="#" onclick="return OpenOrCloseMatches(\'matches\', true, '.$nbmatches2show.');" class="link_plusde">+ de grilles ('.($nbmatchestotal - $nbmatches2show).')</a></div></div>';
		}
	}
	if($nbmatchestotal > $nbmatches2show) $content .= '<div id="moins_de_matches" style="display:none; padding:4px;"><div class="div_link_plusde"><a href="#" onclick="return OpenOrCloseMatches(\'matches\', false, '.$nbmatches2show.');" class="link_moinsde">- de grilles</a></div></div>';
	
	
	// liste des matchs joués ou terminés
	if(!$options["liste_non_joues"])
	{
		$content_list = '';
		$nbmatchestotal = 0;
		foreach($list_matches as $pp_matches) if($pp_matches->save_yet || substr($pp_matches->diff_date_last_match, 0, 1) != '-')
		{
			$nbmatchestotal++;
		}
		$nbmatches = 0;
		$nbmatches2show = 4;
		foreach($list_matches as $pp_matches) if($pp_matches->save_yet || substr($pp_matches->diff_date_last_match, 0, 1) != '-')
		{
			$nbmatches++;
			
			if($altern) {
				$class_line = 'ligne_grise';
				$altern = 0;
			} else {
				$class_line = 'ligne_blanche';
				$altern = 1;
			}
			
			if($options['forcup']) $class_line = 'ligne_grise';
			
			if($pp_matches->id_cup_matches && !$options['forcup'])
			{
				$options = array('miniature' => true);
				$content_list .= "<tr class=\"".$class_line." parent_show_on_hover\">".CurrentCup($options)."</tr>";
			
			} else {
			
				if($pp_matches->show_class_prov)
					$url = '/classj.php?id='.$pp_matches->id_matches;
				else
					$url = '/pronostiquer.php?id='.$pp_matches->id_matches;
		
				$content_list .= '<tr class="'.$class_line.' parent_show_on_hover" id="current_matcheslist_'.$nbmatches.'" '.($nbmatches > $nbmatches2show ? 'style="display:none;"' : '').'>';
				$content_list .= '<td valign="top" width="10%"><a href="'.$url.'"><img src="http://www.pronoplus.com/template/default/'.$pp_matches->image.'" style="border:solid 3px #eee;" height="50" width="35" /></a></td>';
				$content_list .= '<td valign="top" width="50%">';
				$content_list .= '<h3><a href="'.$url.'">'.formatDbData($pp_matches->label).'</a></h3>';
        
				if(!$pp_matches->id_cup_matches)
				{
					$pp_comments_id_type = substr($pp_matches->date_first_match, 0, 4) . $pp_matches->id_matches;
          $content_list .= pp_comments_nb_afficher($url.'#comments', 'classj', $pp_comments_id_type);
				}
        
        $content_list .= '<br /><span class="red">'.format_diff_date($pp_matches->diff_date_first_match).'</span>';
        
				if(!$pp_matches->id_cup_matches)
				{
          $content_list .= '<div class="show_on_hover">Compte pour : '.getMatchesClass($pp_matches->id_matches, true);
					$content_list .= '<br /><strong>'.getNbUsersMatches($pp_matches->id_matches).'</strong> joueurs</div>';
				}
				
				$content_list .= '</td>';
				
				$content_list .= '<td width="40%" align="right" valign="top">';
		
				if(substr($pp_matches->diff_date_last_match, 0, 1) == '-')
				{
					$content_list .= "<p><a href=\"pronostiquer.php?id=".$pp_matches->id_matches."\" class=\"link_button\">Modifier mes pronostics</a></p>";
				}
				
				if(!$pp_matches->id_cup_matches && $pp_matches->show_class_prov)
				{
					$content_list .= "<p><a href=\"classj.php?id=".$pp_matches->id_matches."\" class=\"link_button\">Classement provisoire</a></p>";
					
				} else {					
					/*
					$urlfacebook = 'http://www.facebook.com/share.php?u=http://www.pronoplus.com/pronostiquer.php?id=' . $_GET[id] . '&title=' . urlencode('Pronostics ' . formatDbData($pp_matches->label));
					$content_list .= '<input type="button" class="buttonfacebook" value="Partager sur Facebook" onclick="window.open(\''.$urlfacebook.'\');" />';
					*/
				}					

				
				$content_list .= '</td>';
				$content_list .= '</tr>';
			}
			if($nbmatches == $nbmatches2show && $nbmatchestotal > $nbmatches2show)
			{
				$content_list .= '<tr id="plus_de_matcheslist"><td colspan="3"><div class="div_link_plusde"><a href="#" onclick="return OpenOrCloseMatches(\'matcheslist\', true, '.$nbmatches2show.');" class="link_plusde">+ de grilles ('.($nbmatchestotal - $nbmatches2show).')</a></div></td></tr>';
			}
		}
		if($nbmatchestotal > $nbmatches2show) $content_list .= '<tr id="moins_de_matcheslist" style="display:none;"><td colspan="3"><div class="div_link_plusde"><a href="#" onclick="return OpenOrCloseMatches(\'matcheslist\', false, '.$nbmatches2show.');" class="link_moinsde">- de grilles</a></div></td></tr>';
		
		if($content_list!='')
		{
			if($content != '') $content .= '<br />';
			$content .= '<table width="100%" border="0" cellspacing="0" cellpadding="2">';
			
			if(!$options['forcup'])
			{
				$content .= '<tr><th colspan="3" style="padding:4px; text-align:left;">Grilles de matchs jouées ou terminées</th></tr>';
			}
			
			$content .= $content_list;
			$content .= '</table>';
		}
	}	
	
	if(!$options['forcup'] && !$options["liste_non_joues"])
	{
		if(!$content) $content = "<p><img src=\"http://www.pronoplus.com/smileys/14.gif\" /> Aucune grille de pronostics en cours...</p>";
		$content = getBlocLeft('Grilles de pronostics en cours', 'green', $content, '', false);
	}
	
	return $content;
}






function ClassMatches($options = array())
{
	global $db, $user, $txtlang;
	$content = "";
	
	$content .= '<table width="100%" border="0" cellspacing="1" cellpadding="4">';
	$content .= '<tr><th colspan="3">Coupe</th></tr>';
	$content .= "<tr class=\"".$class_line." parent_show_on_hover\">".CurrentCup( array('miniature' => true, 'simple' => true) )."</tr>";
	$content .= '</table><br />';
	
	$content .= '<table width="100%" border="0" cellspacing="1" cellpadding="4">';
	$content .= '<tr><th width="80%" colspan="2">Grilles de matchs</th>';
	$content .= '<th width="10%" nowrap>Classement</th>';
	$content .= '<th width="10%" nowrap>Points</th></tr>';
	
	$SQL = "SELECT `pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`image`, `pp_matches`.`date_first_match`, `pp_matches`.`id_cup_matches`
			FROM `pp_matches`
			WHERE `pp_matches`.`is_calcul`='1' ".(!$options['return_data'] ? "AND `pp_matches`.`id_cup_matches`=0" : "")."
			ORDER BY `date_calcul` DESC
			LIMIT 10";
	$result_matches = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_matches))
	{
		die ("<li>ERROR : ".$result_matches->getMessage());
		
	} else {
		if($result_matches->numRows())
		{
			$id_resultat = 0;
			$list_matches = array();
			while($pp_matches = $result_matches->fetchRow())
			{	
				$id_resultat++;
				
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
						
						$pp_matches->class_user = $pp_class->class;
						$pp_matches->class_nb_points = $pp_class->nb_points;
					}
				}
				
				if(!$options['return_data']) 
				{
					if($altern) {
						$class_line = 'ligne_grise';
						$altern = 0;
					} else {
						$class_line = 'ligne_blanche';
						$altern = 1;
					}

					$content .= '<tr id="resultats_matches_'.$id_resultat.'" class="'.$class_line.' parent_show_on_hover" '.($id_resultat > 3 ? 'style="display:none;"' : '').'>';
					$content .= '<td width="10%" valign="top"><a href="classj.php?id='.$pp_matches->id_matches.'"><img src="http://www.pronoplus.com/template/default/'.$pp_matches->image.'" style="border:solid 3px #eee;" height="50" width="35" /></a></td>';
					$content .= '<td width="70%" valign="top"><h3><a href="classj.php?id='.$pp_matches->id_matches.'">'.formatDbData($pp_matches->label).'</a></h3>';
					// commentaires ?
					$pp_comments_id_type = substr($pp_matches->date_first_match, 0, 4) . $pp_matches->id_matches;
					$content .= pp_comments_nb_afficher('/classj.php?id='.$pp_matches->id_matches.'#comments', 'classj', $pp_comments_id_type);
					
					$content .= '<div class="show_on_hover">Compte pour : '.getMatchesClass($pp_matches->id_matches, true) . '<br />'
								. '<strong>' . getNbUsersMatches($pp_matches->id_matches).'</strong> joueurs.</div>';

					
					$content .= '</td>';
					$content .= '<td align="center">';
					$content .= isset($class_user['class']) ? '<a href="/classj.php?id='.$pp_matches->id_matches.'&rech_jpseudo='.urlencode($user->login).'&search_joueur=1" class="link_orange" style="display:block; width:100%">'.$class_user['class'].'</a>' : '-';
					$content .= '</td>';
					$content .= '<td align="center">';
					$content .= isset($class_user['nb_points']) ? '<a href="classj.php?id='.$pp_matches->id_matches.'&rech_jpseudo='.urlencode($user->login).'&search_joueur=1" class="link_orange" style="display:block; width:100%">'.$class_user['nb_points'].'</a>' : '-';
					$content .= '</td>';
					$content .= '</tr>';

					if($id_resultat == 3)
					{
						$content .= '<tr id="plus_de_resultats"><td colspan="4" style="padding:4px;"><div class="div_link_plusde"><a href="#" onclick="return OpenOrCloseResultats(true);" class="link_plusde">+ de résultats ('.($result_matches->numRows() - 3).')</a></div></td></tr>';
					}
					
				} else {
					$list_matches[] = $pp_matches;
				}
			}
			
			// retourner juste les infos
			if($options['return_data']) return $list_matches;
			
			
			$SQL = "SELECT `pp_matches`.`id_matches`
					FROM `pp_matches`
					INNER JOIN `pp_matches_user` ON `pp_matches_user`.`id_matches`=`pp_matches`.`id_matches` AND `pp_matches_user`.`id_user`='".$user->id_user."'
					WHERE `pp_matches`.`is_calcul`='1' AND `pp_matches`.`id_cup_matches`=0";
			$result_matches = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_matches))
			{
				die ("<li>ERROR : ".$result_matches->getMessage());
				
			} else {
				if($result_matches->numRows())
				{
					if($altern) {
						$class_line = 'ligne_grise';
						$altern = 0;
					} else {
						$class_line = 'ligne_blanche';
						$altern = 1;
					}
		
					$content .= '<tr id="resultats_historique" class="'.$class_line.'" style="display:none;">';
					$content .= '<td width="10%"><a href="historique-resultats.php"><img src="http://www.pronoplus.com/template/default/histo_ico.png" style="border:solid 3px #eee;" height="50" width="35" /></a></td>';
					$content .= '<td width="90%" colspan="3"><h3><a href="historique-resultats.php">Historique des résultats</a></h3></td>';
					$content .= '</tr>';
				}
			}

			if($id_resultat > 3) $content .= '<tr id="moins_de_resultats" style="display:none;"><td colspan="4" style="padding:4px;"><div class="div_link_plusde"><a href="#" onclick="return OpenOrCloseResultats(false);" class="link_moinsde">- de résultats</a></div></td></tr>';	
			
		} else {
			$content .= '<tr><td colspan="3" align="center">Aucun résultat pour l\'instant</td></tr>';
		}
		
		$content .= '</table>';
		//$content .= "<div class=\"clear\"></div>";
	}	
	
	$content = getBlocLeft('Résultats', 'orange', $content, '', false);
	
	return $content;
}



function getNbUsersMatches($id_matches)
{
	global $db;
	$SQL = "SELECT COUNT(`id_user`) AS NBUSERS FROM `pp_matches_user`
			WHERE `id_matches`='".$id_matches."'";
	$result = $db->query($SQL);
	//echo "<br>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		if($pp_matches_user = $result->fetchRow())
		{
			return $pp_matches_user->NBUSERS;
		}
	}
	return false;
}



function getMatchesClass($id_matches, $noparagraph=false)
{
	global $db;
	$label_class = "";
	
	// classement ?
	$SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`label`
			FROM `pp_class`
			INNER JOIN `pp_matches_class` ON `pp_class`.`id_class`=`pp_matches_class`.`id_class`
			WHERE `pp_matches_class`.`id_matches`='".$id_matches."'
			ORDER BY `pp_class`.`order`";
	$result_class = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_class))
	{
		die ("<li>ERROR : ".$result_class->getMessage());
		
	} else {				
		while($pp_class = $result_class->fetchRow())
		{
			$label_class .= ($label_class!="" ? ", " : "") . "<a href=\"/class.php?id=".$pp_class->id_class."\" class=\"link_orange\">".formatDbData(str_replace('Classement', '', $pp_class->label))."</a>";
		}		
	}
	
	// Coupe ?
	$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label`
			FROM `pp_cup`
			INNER JOIN `pp_cup_matches` ON `pp_cup`.`id_cup`=`pp_cup_matches`.`id_cup`
			WHERE `pp_cup_matches`.`id_matches`='".$id_matches."'";
	$result_cup = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_class))
	{
		die ("<li>ERROR : ".$result_cup->getMessage());
		
	} else {				
		while($pp_cup = $result_cup->fetchRow())
		{
			$label_class .= ($label_class!="" ? ", " : "") . "<a href=\"/cup.php?id=".$pp_cup->id_cup."\" class=\"link_orange\">".formatDbData($pp_cup->label)."</a>";
		}		
	}
	
	$justforfun = "Cette grille ne compte pour aucun classement, c'est juste pour s'échauffer ;)";	
	if(!$noparagraph) $label_class = "<p>".($label_class ? "Compte pour : " . $label_class : $justforfun)."</p>";
	if(!$label_class) $label_class = " aucun classement, c'est juste pour s'échauffer ;)";
	
	return $label_class;
}

function pagination($pagego, $sqldep, $nb_element, $nbaff=20, $extension="")
{
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<td width="5%" nowrap="nowrap"> 
	  <?php if(($sqldep-$nbaff)>=0) { // suivant // précédent
$sqlgoto=$sqldep-$nbaff; ?>
	  <a href="/<?php echo $pagego."?sqldep=$sqlgoto".$extension ?>" class="link_button"><img src="/template/default/arrow_left.png" border="0" align="absmiddle" />&nbsp;Vers&nbsp;les&nbsp;premiers</a>
	  <?php } ?>
    </td>

<form method="post" action="<?php echo $pagego."?".$extension; ?>">
	<td width="90%" align="center">
	Rang : 
	<select name="sqldep">
	<?php // pages
	$nbpage=ceil($nb_element/$nbaff);
	for($i=1; $i<=$nbpage; $i++) {
		$ldeb=(($i-1)*$nbaff);
	?>
	
	<option value="<?php echo $ldeb; ?>"<?php if($ldeb==$sqldep) echo " selected=\"selected\" style=\"font-weight: bold; background-color: #eeeeee;\""; ?>><?php echo ($ldeb+1)." - ".($ldeb+$nbaff); ?></option>
	<?php } ?>
	</select>
	<input type="submit" class="link_button" value="Ok">
	 </td>
</form>

	<td align="right width="5%" nowrap="nowrap"> 
	  <?php if(($sqldep+$nbaff) < $nb_element) {
$sqlgoto=$sqldep+$nbaff; ?>
	  <a href="/<?php echo $pagego."?sqldep=$sqlgoto".$extension ?>" class="link_button">Vers&nbsp;les&nbsp;derniers&nbsp;<img src="/template/default/arrow_right.png" border="0" align="absmiddle" /></a>
	  <?php } ?>
    </td>
	
  </tr>
</table>
<?php
}


function getLastPostFromForum()
{
	global $db;
?>
<table width="100%" border="0" cellspacing="1" cellpadding="4">
		<?php
		/*
		<tr>		  
		  <th width="60%">Sujets</th>
		  <th width="20%">Forum</th>
		  <th width="40%">Dernier message</th>
		</tr>
		*/
		?>
<?php
$resmsg=mysql_query("SELECT forum.*, forum_theme.label AS label_theme, forum_theme.url AS url_theme FROM forum INNER JOIN forum_theme ON forum_theme.id_forum_theme=forum.id_forum_theme
					WHERE forum.Nquest=0 AND forum.supp=0 AND forum.bloque=0
					ORDER BY dateder DESC LIMIT 7");
$class = '';
while($lmsg=mysql_fetch_array($resmsg)) {
	$resrep=mysql_query("select Nmsg from forum where Nquest=".$lmsg["Nmsg"]);
	
		// dernier message du topic
		$SQL = "SELECT forum.Nmsg, forum.id_user, forum.message, `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`
			FROM forum
			INNER JOIN `pp_user` ON `pp_user`.`id_user`=`forum`.`id_user`
			WHERE forum.Nmsg='".$lmsg["Nmsg"]."' OR forum.Nquest='".$lmsg["Nmsg"]."'
			ORDER BY forum.Nmsg DESC";	
		$resdermsg=mysql_query($SQL) or die(mysql_error());
		$lnmsg=mysql_fetch_assoc($resdermsg);
		
		//pages
		$nbmsg = mysql_query("select Nmsg from forum where Nmsg=".$lmsg["Nmsg"]." or Nquest=".$lmsg["Nmsg"]);
		$nbtotalmsg = mysql_num_rows($nbmsg);
		$nbaff = 10;
		$nbpage = ceil($nbtotalmsg/$nbaff);
		
		$link = "/forum-football/".$lmsg["url"]."-".$lmsg["Nmsg"].(($nbpage*$nbaff-$nbaff)>0?"page".($nbpage*$nbaff-$nbaff):"").".html#mess".$lnmsg[Nmsg];
		
		$mots = split(' ', $lnmsg["message"]);
		$nbmots = 16;
		$message = '';
		for($i=0; $i<=$nbmots; $i++) $message .= $mots[$i] . ' ';
		$message .= '...';
?>

		<tr class="<?php echo $class = ($class != 'ligne_blanche' ? 'ligne_blanche' : 'ligne_grise'); ?>">
		  <td width="65%">
			<h3><a href="<?php echo $link;?>" class="link_orange">
			<?php echo htmlspecialchars($lmsg["sujet"]); ?>
			<img src="/template/default/last.gif" alt="Aller au dernier message" align="absmiddle" width="16" height="16" border="0" />
			</a></h3>
			
			<?php echo htmlspecialchars($message); ?>

			<?php
			/*			//pages
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
			<?php } */ ?>
			</td>
			<?php /* <td>Dans <a href="/forum-football/<?php echo $lmsg["url_theme"]?>/" class="link_orange"><?php echo $lmsg["label_theme"]?></a></td> */ ?>
		  <td width="35%">
		<a href="/user.php?q=<?php echo urlencode(htmlspecialchars($lnmsg["login"]))?>" class="link_orange">
		<?php
		if($avatar = getAvatar($lnmsg["id_user"], $lnmsg["avatar_key"], $lnmsg["avatar_ext"], 'small')) {
		?>
			<img src="/avatars/<?php echo $avatar?>" height="30" width="30" border="0" style="float:left; margin-right:2px;"  />
		<?php } else { ?>
			<img src="/template/default/_profil.png" height="30" width="30" border="0" style="float:left; margin-right:2px;" />
		<?php } ?>
		<?php echo $lnmsg["login"]; ?></a><br /><span style="font-size:10px;"><?php echo formatdateheure($lmsg["dateder"])?></span>
		
		  </td>
		</tr>
		<?php } ?>
		
		
		<?php
			$forum_themes = array();
			$SQL = "SELECT `id_forum_theme`, `label`, `description`, `url` FROM `forum_theme` ORDER BY `order`";
			$result = $db->query($SQL);
			if(DB::isError($result))
			{
				die ("<li>ERROR : ".$result->getMessage());
				
			} else {
				while($forum_theme = $result->fetchRow())
				{
					$forum_themes[] = $forum_theme;
				}
			}
		?>
		
		<tr>
			<th align="right" colspan="2">
				<span style="float:left"><a href="/forum_recherche.php" class="link_orange"><img src="/template/default/search.png" border="0" align="absmiddle" /> Rechercher</a></span>
			
			<?php
				echo "<select onchange=\"if(this.value != '') document.location.href=this.value\">";
				echo "<option value=\"\">Aller au forum...</option>";
				foreach($forum_themes as $forum_theme)
				{
					echo "<option value=\"/forum-football/".$forum_theme->url."/\">".$forum_theme->label."</option>";
				}
				echo "</select>";
			?>
			</th>
		</tr>
		
		<tr><th colspan="2" align="right"><a class="link_orange" href="#" onclick="$('selection_theme_forum').show(); return false;"><img src="/template/default/comment_new.gif" height="16" width="16" alt="Ouvrir au sujet" border="0" align="absmiddle"> <b>Ouvrir un sujet</b></a>
		<?php
			echo "<select id=\"selection_theme_forum\" style=\"display:none;\" onchange=\"if(this.value != '') document.location.href=this.value\">";
			echo "<option value=\"\">Dans le forum...</option>";
			foreach($forum_themes as $forum_theme)
			{
				echo "<option value=\"/forum-football/".$forum_theme->url."/#modifier_msg\">".$forum_theme->label."</option>";
			}
			echo "</select>";
		?>
		</th></tr>
	  </table>
<?php
}


function getAvatar($id_user, $avatar_key, $avatar_ext, $type)
{
	$dir_avatar = $_SERVER['DOCUMENT_ROOT'].'/avatars/';
	
	if(!$avatar_key || !$avatar_ext) return false;
	
	if($type=='small' && file_exists($dir_avatar.$id_user.'-'.$avatar_key.'-30.'.$avatar_ext))
	{
		return $id_user.'-'.$avatar_key.'-30.'.$avatar_ext;
		
	} else 	if($type=='thumb' && file_exists($dir_avatar.$id_user.'-'.$avatar_key.'-59.'.$avatar_ext))
	{
		return $id_user.'-'.$avatar_key.'-59.'.$avatar_ext;
		
	} else if($type=='normal' && file_exists($dir_avatar.$id_user.'-'.$avatar_key.'-118.'.$avatar_ext))
	{
		return $id_user.'-'.$avatar_key.'-118.'.$avatar_ext;
	}
	
	return false;
}



function CurrentCup($options=false)
{
	global $db, $user, $txtlang;
	
	$content = '';
	$nbjoueurs = 16;
	
	// Coupe en cours
	$SQL = "SELECT `pp_cup`.`id_cup`, `pp_cup`.`label` AS `label_cup`, `pp_class`.`label` AS `label_class`
			FROM `pp_cup`
			INNER JOIN `pp_class` ON `pp_cup`.`id_class_ref`=`pp_class`.`id_class`
			ORDER BY `pp_cup`.`id_cup` DESC
			LIMIT 1";
	$result_cup = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result_cup))
	{
		die ("<li>ERROR : ".$result_cup->getMessage()."<li>$SQL");
		
	} else {
		if($pp_cup = $result_cup->fetchRow())
		{
			$SQL = "SELECT `pp_cup_matches`.`id_cup_matches`, `pp_cup_matches`.`number_tour`
					FROM `pp_cup_matches`
					WHERE `pp_cup_matches`.`id_cup`='".$pp_cup->id_cup."'
					ORDER BY `pp_cup_matches`.`number_tour` DESC
					LIMIT 1";
			$result_matches = $db->query($SQL);
			//echo "<li>$SQL";
			if(DB::isError($result_matches))
			{
				die ("<li>ERROR : ".$result_matches->getMessage()."<li>$SQL");
				
			} else {
				if($pp_cup_matches = $result_matches->fetchRow())
				{
					//<a href=\"cup.php?id=".$pp_cup->id_cup."\"><img src=\"template/default/coupe.png\" class=\"preview_matches_image\" border=\"0\" />
					
					
					// le joueur est-il qualifié pour la coupe ?
					$SQL = "SELECT `cup_sub`, `id_user_host`, `id_user_visitor`, `host_class`, `visitor_class`
							FROM `pp_cup_match_opponents`
							WHERE `id_cup_matches`='".$pp_cup_matches->id_cup_matches."'
							AND (`id_user_host`='".$user->id_user."' OR `id_user_visitor`='".$user->id_user."')";
					$result_cup_user = $db->query($SQL);
					//echo "<li>$SQL";
					if(DB::isError($result_cup_user))
					{
						die ("<li>ERROR : ".$result_cup_user->getMessage()."<li>$SQL");
						
					} else {
						if($cup_user = $result_cup_user->fetchRow())
						{
							// qui est le joueur ?
							if($user->id_user == $cup_user->id_user_visitor)
							{
								$id_user_opponent = $cup_user->id_user_host;
								$class_current_user = $cup_user->visitor_class;
							} else {
								$id_user_opponent = $cup_user->id_user_visitor;
								$class_current_user = $cup_user->host_class;
							}						
              
              
              $content .= "<a href=\"cup.php?id=".$pp_cup->id_cup."\" class=\"link_button\" style=\"float:right;\">Pronostiquer</a>";
              $content .= "<h3><a href=\"cup.php?id=".$pp_cup->id_cup."\">".formatDbData($pp_cup->label_cup)." - ".getCupTourLabel($pp_cup_matches->number_tour)."</a></h3>";

							
							$first_rank = ($cup_user->cup_sub - 1) * $nbjoueurs +1;
							$content .= "<div>Vous êtes qualifié pour la <strong>".getCupDivisionLabel($cup_user->cup_sub)."</strong></div>";
							
							if(!$options["simple"])
							{
								// recherche de l'adversaire ?
								$opponent = nom_joueur($id_user_opponent);
								$avatar = getAvatar($id_user_opponent, $opponent->avatar_key, $opponent->avatar_ext, 'small');
								$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
								
								$content .= "<div>Lors de ce tour, vous affrontez :<br />
								<a href=\"/user.php?q=".urlencode(htmlspecialchars($opponent->login))."\" class=\"link_button\"><img src=\"".$avatar."\" height=\"29\" width=\"29\" border=\"0\" align=\"absmiddle\" />&nbsp;<strong>".$opponent->login."</a></strong></a>";
                
                $content .= "</div>";
                
                //$content .= "<div class=\"show_on_hover\">Cette coupe concerne les joueurs classés de la ".$first_rank.($first_rank>1 ? "<sup>ème</sup>" : "<sup>ère</sup>")." à la ".($cup_user->cup_sub * $nbjoueurs)."<sup>ème</sup> place au ".formatDbData($pp_cup->label_class)." et vous avez été classé ".$class_current_user.($class_current_user>1 ? "<sup>ème</sup>" : "er").".</div>";
							}
              
              
						
						} else {
							
							$content .= "<h3><a href=\"cup.php?id=".$pp_cup->id_cup."\">".formatDbData($pp_cup->label_cup)." - ".getCupTourLabel($pp_cup_matches->number_tour)."</a></h3>";
              
              if($options["liste_non_joues"]) return '';
							$content .= "<p>Vous n'êtes pas qualifié.</p>";
							
						}
					}
				}
			}
			$content = "<td valign=\"top\" width=\"1%\"><a href=\"cup.php?id=".$pp_cup->id_cup."\"><img src=\"http://www.pronoplus.com/template/default/coupe.png\" class=\"preview_matches_image\" border=\"0\" style=\"border:solid 3px #eee;\" ".($options[miniature] ? "height=\"50\" width=\"35\"":"")." /></a></td><td width=\"99%\" colspan=\"2\" valign=\"top\">".$content."</td>";
			
		} else {
			$content = "<td colspan=\"2\" align=\"center\">Un peu de patience ! La coupe revient en septembre !</td>";
		}
	}

	return $content;
}


function getCupDivisionLabel($cup_sub)
{
	if($cup_sub==1)
		return 'Coupe Or';
		
	else if($cup_sub==2)
		return 'Coupe Argent';
		
	else if($cup_sub==3)
		return 'Coupe Bronze';
		
	else if($cup_sub>=4)
		return 'Coupe Chocolat (division '.$cup_sub.')';
}

function getCupTourLabel($number_tour)
{
	if($number_tour==1)
		return 'Huitièmes de finale';
		
	else if($number_tour==2)
		return 'Quarts de finale';
		
	else if($number_tour==3)
		return 'Demi-finales';
		
	else if($number_tour==4)
		return 'Finale';
}



function classements_user($userprofil, $options=array())
{
	global $user, $db;
	$html = '';
	
	// Les classements
	$SQL = "SELECT `pp_class`.`id_class`, `pp_class`.`type`, `pp_class`.`label`, `pp_class`.`last_id_matches`
			FROM `pp_class`
			WHERE `pp_class`.`last_id_matches` != 0
			ORDER BY `pp_class`.`type` DESC, `pp_class`.`order` ASC";
	$result = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
		
	} else {
		$type_current = '';
		
		while($pp_class = $result->fetchRow())
		{
			// infos classement joueur
			$pp_class_user = '';
			$pp_match = '';
			
			if($userprofil->id_user)
			{
				// info joueur
				$SQL = "SELECT `pp_class_user`.`id_user`,
							`pp_class_user`.`class`, `pp_class_user`.`nb_score_ok`, `pp_class_user`.`nb_result_ok`,
							`pp_class_user`.`nb_matches`, `pp_class_user`.`nb_points`, `pp_class_user`.`evolution`
						FROM `pp_class_user` INNER JOIN `pp_user` ON `pp_user`.`id_user`=`pp_class_user`.`id_user`
						WHERE `pp_class_user`.`id_class`='".$pp_class->id_class."'
							AND `pp_class_user`.`id_matches`='".$pp_class->last_id_matches."'
						AND `pp_class_user`.`id_user` = '".$userprofil->id_user."'";
				$result_class_user = $db->query($SQL);
				if(DB::isError($result_class_user))
				{
					die ("<li>ERROR : ".$result_class_user->getMessage()."<li>$SQL");				
				} else {
					$pp_class_user = $result_class_user->fetchRow();
				}
				
				if(!$options[affichage_simple])
				{
					// nb de matchs de ce classement ?
					$SQL = "SELECT COUNT(pp_match.id_match) AS NBMATCH
							FROM pp_matches_class
							INNER JOIN pp_match ON pp_matches_class.id_matches = pp_match.id_matches
							INNER JOIN pp_matches ON pp_matches.id_matches = pp_matches_class.id_matches
							WHERE `pp_matches_class`.`id_class`='".$pp_class->id_class."' AND `pp_matches`.is_calcul='1'";
					$result_class_user = $db->query($SQL);
					if(DB::isError($result_class_user))
					{
						die ("<li>ERROR : ".$result_class_user->getMessage()."<li>$SQL");				
					} else {
						$pp_match = $result_class_user->fetchRow();
					}
				}
			}
		
			// affichage sous titre
			if($type_current != $pp_class->type)
			{
				$class_line = 'ligne_grise';
				
				/*if($type_current != '')
				{
					if(!$options[affichage_simple])
					{
						$html .= '<tr><td colspan="7">&nbsp;</td></tr>';
					} else {
					
					}
				}*/
				
				$type_current = $pp_class->type;			
				if($type_current == 'year') $html .= '<tr class="noborder"><td colspan="7" style="padding:0; margin:0"><h2 class="title_green">Classements annuels</h2></td></tr>';
					else if($type_current == 'month') $html .= '<tr><td colspan="7" style="padding:0; margin:0"><h2 class="title_green">Classements mensuels</h2></td></tr>';
					
				$html .=  '<tr>
							<th>Classement</th>
							<th>Rang</th>
							<th>Dernière évolution</th>
							<th>Points</th>';
				if(!$options[affichage_simple])
				{
					$html .='<th>Nb matchs joués</th>
								<th>Scores justes</th>
								<th>Résultats justes</th>';
				}
				$html .=  '</tr>';
			}
			
			// ligne grise ou blanche
			if($altern) {
				$class_line = 'ligne_grise';
				$altern = 0;
			} else {
				$class_line = 'ligne_blanche';
				$altern = 1;
			}
			
			// évolution
			$evolution = '-';
			if(isset($pp_class_user->evolution))
			{
				$evolution = evolution_format($pp_class_user->evolution);				
				$evolution = '<a href="/classement-evolution.php?id='.$pp_class->id_class.'" class="link_orange" title="Voir l\'évolution au '.htmlspecialchars(formatDbData($pp_class->label)).'">'.$evolution.'</a>';
			}
			
			// affichage ligne
			$html .= '<tr class="'.$class_line.'" onmouseover="this.className=\'ligne_rollover\'" onmouseout="this.className=\''.$class_line.'\'">
				<td><a href="/class.php?id='.$pp_class->id_class.'" class="link_orange" title="Voir le '.htmlspecialchars(formatDbData($pp_class->label)).'">'.formatDbData($pp_class->label).'</a></td>
				<td style="text-align:center">'.($pp_class_user->class ? '<a href="/class.php?id='.$pp_class->id_class.'&rech_jpseudo='.htmlspecialchars($userprofil->login).'&search_joueur=1" class="link_orange" title="Aller à mon classement">'.$pp_class_user->class.'</a>' : '-').'</td>
				<td style="text-align:center">'.$evolution.'</td>
				<td style="text-align:center">'.($pp_class_user->nb_points ? $pp_class_user->nb_points : '-').'</td>';
				
			if(!$options[affichage_simple])
			{
				$html .= '<td style="text-align:center">'.($pp_class_user->nb_matches ? $pp_class_user->nb_matches . ' / ' . $pp_match->NBMATCH . '<br /><span class="petitgris">'.round(100 * $pp_class_user->nb_matches / $pp_match->NBMATCH).'%</span>' : '-').'</td>
					<td style="text-align:center">'.($pp_class_user->nb_score_ok ? $pp_class_user->nb_score_ok . '<br /><span class="petitgris">'.round(100 * $pp_class_user->nb_score_ok / $pp_class_user->nb_matches).'%</span>' : '-').'</td>
					<td style="text-align:center">'.($pp_class_user->nb_result_ok ? $pp_class_user->nb_result_ok . '<br /><span class="petitgris">'.round(100 * $pp_class_user->nb_result_ok / $pp_class_user->nb_matches).'%</span>' : '-').'</td>';
			}
			$html .= '</tr>';
		}	
	}
	
	
	// classements journées
	$SQL = "SELECT `pp_class_user`.`class`, `pp_class_user`.`nb_points`, `pp_class_user`.`nb_score_ok`, `pp_class_user`.`nb_result_ok`, `pp_class_user`.`nb_matches`,
				`pp_matches`.`id_matches`, `pp_matches`.`label`, `pp_matches`.`image`
			FROM `pp_matches`
				INNER JOIN `pp_class_user` ON `pp_class_user`.`id_user`='".$userprofil->id_user."' AND `pp_class_user`.`id_class`=1 AND `pp_matches`.`id_matches`=`pp_class_user`.`id_matches`
			WHERE `pp_matches`.`is_calcul`='1' AND id_cup_matches=0
			ORDER BY `pp_matches`.`date_calcul` DESC
			LIMIT 10";
	$result_matches = $db->query($SQL);
	//echo "<li>$SQL";
	if(DB::isError($result))
	{
		die ("<li>ERROR : ".$result_matches->getMessage());
		
	} else {
		if($result_matches->numRows())
		{
			$html .= '<tr class="noborder"><td colspan="7" style="padding:0; margin:0"><h2 class="title_green">Classements journées (10 dernières)</h2></td></tr>';
			$html .=  '<tr>
						<th colspan="2">Classement</th>
						<th>Rang</th>
						<th>Points</th>';
			if(!$options[affichage_simple])
			{
				$html .='<th>Nb matchs joués</th>
							<th>Scores justes</th>
							<th>Résultats justes</th>';
			}
			$html .=  '</tr>';
			
			$i = $nb_begin+1;
			
			while($pp_matches = $result_matches->fetchRow())
			{			
				if($altern) {
					$class_line = 'ligne_grise';
					$altern = 0;
				} else {
					$class_line = 'ligne_blanche';
					$altern = 1;
				}

				$html .= '<tr class="'.$class_line.'">';
				$html .= '<td colspan="2"><a href="/classj.php?id='.$pp_matches->id_matches.'"title="Aller au classement" class="link_orange"><img src="/template/default/'.$pp_matches->image.'" style="border:solid 3px #eee;" height="30" align="absmiddle" /> '.formatDbData($pp_matches->label).'</a></td>';
				$html .=  '<td style="text-align:center">'.$pp_matches->class.'</td>
							<td style="text-align:center">'.$pp_matches->nb_points.'</td>';
				if(!$options[affichage_simple])
				{
					$html .='<td style="text-align:center">'.$pp_matches->nb_matches.'</td>
								<td style="text-align:center">'.$pp_matches->nb_score_ok.'</td>
								<td style="text-align:center">'.$pp_matches->nb_result_ok.'</td>';
				}
				$html .=  '</tr>';
			}
			
			if($userprofil->id_user == $user->id_user)
			{
				$html .= '<tr class="noborder"><td colspan="7" style="text-align:center">+ de résultats dans l\'<a href="/historique-resultats.php" class="link_orange">historique des résultats</a></td></tr>';
			}
		}
	}
	
	if($html != '')
		$html = '<table width="100%" cellpadding="4" cellspacing="1">' . $html . "</table>";
	else
		$html = '<h2 class="title_green">Classements</h2><p>Aucun classement pour l\'instant... Un peu de patience, la saison ne fait que commencer ! ;)</p>';
	
	return $html;
}




function evolution_format($evolution)
{
	if($evolution < -10)
	{
		$evolution = "<font color=\"red\">".$evolution."</font>";
		
	} elseif($evolution < 0)
	{
		$evolution = "<font color=\"red\">".$evolution."</font>";
		
	} elseif($evolution == 0)
	{
		$evolution = "<font color=\"orange\">=</font>";
		
	} elseif($evolution < 10)
	{
		$evolution = "<font color=\"green\">+".$evolution."</font>";
		
	} else
	{
		$evolution = "<font color=\"green\">+".$evolution."</font>";
	}
	
	return $evolution;
}


function facebook_libe_button($url, $width=400, $layout='standard')
{
	return '<iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.pronoplus.com%2F' . urlencode($url) . '&amp;layout=' . $layout . '&amp;show_faces=true&amp;width=' . $width . '&amp;action=like&amp;font=verdana&amp;colorscheme=light" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:' . $width . 'px; height:25px; display:inline;"></iframe>';
}
