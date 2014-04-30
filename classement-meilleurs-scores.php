<?php
/**
* Project: PRONOPLUS
* Description: Classement journée
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2010-02-22
* Version: 1.0
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();


// filtre amis ?
$friends_ids = '';
if($_GET[idl]*1)
{
	if($_GET[idl]*1 == -1)
	{
		// on recherche les id_user de tous les amis
		$SQL = "SELECT `pp_user_friends`.`id_user_friend`
				FROM `pp_user_friends`
				WHERE `pp_user_friends`.`id_user`='".$user->id_user."'
					AND `pp_user_friends`.`valide`='1'";
		$result_user_friends = $db->query($SQL);		
		if(DB::isError($result_user_friends)) die ("<li>$SQL<li>ERROR : ".$result_user_friends->getMessage());		
		while($pp_user_friends = $result_user_friends->fetchRow())
		{
			$friends_ids .= ($friends_ids != '' ? ',' : '') . $pp_user_friends->id_user_friend;
		}
		// on ajoute l'id de l'user courant
		$friends_ids .= ($friends_ids != '' ? ','.$user->id_user : '');
		
	} else {
		// on vérifie que la liste appartient pas à l'user courant
		$SQL = "SELECT `pp_user_listfriends`.`id_user_listfriends`, `pp_user_listfriends`.`label`
				FROM `pp_user_listfriends`
				WHERE `pp_user_listfriends`.`id_user`='".$user->id_user."'
				AND `pp_user_listfriends`.`id_user_listfriends`='".($_GET[idl]*1)."'";
		$result_user_listfriends = $db->query($SQL);
		if(DB::isError($result_user_listfriends)) die ("<li>ERROR : ".$result_user_listfriends->getMessage());
		if($result_user_listfriends->numRows())
		{
			// on recherche les id_user des amis de cette liste
			$SQL = "SELECT `pp_user_friends`.`id_user_friend`
					FROM `pp_user_friends`
					WHERE `pp_user_friends`.`id_user`='".$user->id_user."'
						AND `pp_user_friends`.`id_user_listfriends`='".($_GET[idl]*1)."'
						AND `pp_user_friends`.`valide`='1'";
			$result_user_friends = $db->query($SQL);		
			if(DB::isError($result_user_friends)) die ("<li>$SQL<li>ERROR : ".$result_user_friends->getMessage());		
			while($pp_user_friends = $result_user_friends->fetchRow())
			{
				$friends_ids .= ($friends_ids != '' ? ',' : '') . $pp_user_friends->id_user_friend;
			}
			// on ajoute l'id de l'user courant
			$friends_ids .= ($friends_ids != '' ? ','.$user->id_user : '');
		}
	}
}


/*
// nombre de joueurs dans le classement définitif ?
$SQL = "SELECT COUNT(`id_user`) AS NBUSERS
		FROM `pp_class_user`
		WHERE `id_class`=1 AND `id_matches`='".$pp_matches->id_matches."'
		".($friends_ids != '' ?  " AND `pp_class_user`.`id_user` IN (".$friends_ids.")" : ""); // filtre amis;
$result = $db->query($SQL);
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage());
	
} else {
	if($pp_class_user = $result->fetchRow())
	{
		$nb_element = $pp_class_user->NBUSERS;
		
	} else HeaderRedirect('/');
}
*/


$title_page = "Les 20 meilleurs scores";
pageheader($title_page." | Prono+");

?>
<div id="content_fullscreen">
<?php
// affichage des onglets
echo getOnglets('classement');
?>



<div id="content">

<h1 class="title_green"><?php echo $title_page?></h1>




<?php if($user->id_user) { ?>
<table width="100%" cellpadding="4" cellspacing="1">
<tr>
<td colspan="2" style="background:#eee">
<?php /* Filtre amis */
if($user->id_user)
{
	$totalfriends = 0;
	
	$SQL = "SELECT `pp_user_listfriends`.`id_user_listfriends`, `pp_user_listfriends`.`label`
			FROM `pp_user_listfriends`
			WHERE `pp_user_listfriends`.`id_user`='".$user->id_user."'			
			ORDER BY `pp_user_listfriends`.`order`, `pp_user_listfriends`.`label`";
	$result_user_listfriends = $db->query($SQL);
	if(DB::isError($result_user_listfriends)) die ("<li>ERROR : ".$result_user_listfriends->getMessage());
	if($result_user_listfriends->numRows())
	{
		$str = '';
		$strlist = array();
		while($pp_user_listfriends = $result_user_listfriends->fetchRow())
		{
			// recherche nombre joueurs de la liste
			$SQL = "SELECT COUNT(`pp_user_friends`.`id_user_friend`) AS `nb_friends`
					FROM `pp_user_friends`
					WHERE `pp_user_friends`.`id_user`='".$user->id_user."'
						AND `pp_user_friends`.`id_user_listfriends`='".$pp_user_listfriends->id_user_listfriends."'
						AND `pp_user_friends`.`valide`='1'";
			$result_user_friends = $db->query($SQL);
			if(DB::isError($result_user_friends)) die ("<li>$SQL<li>ERROR : ".$result_user_friends->getMessage());
			if($pp_user_friends = $result_user_friends->fetchRow())
			{
				if($pp_user_friends->nb_friends)
				{
					$strlist[] = '<option value="'.$pp_user_listfriends->id_user_listfriends.'" '.($_GET[idl] == $pp_user_listfriends->id_user_listfriends ? 'selected="selected"' : '').'>'.htmlspecialchars($pp_user_listfriends->label).' ('.$pp_user_friends->nb_friends.')</option>';
					$totalfriends += $pp_user_friends->nb_friends;
				}
			}
		}

		$str .= '<a name="friends"></a><form id="class_select_liste" method="get" action="/classement-meilleurs-scores.php#friends">';
		$str .= '<input type="hidden" name="id" value="'.$_GET[id].'">';
		$str .= '<img src="/template/default/group.png" border="0" align="absmiddle" /> Filtrer le classement ';
		$str .= '<select name="idl">';
		$str .= '<option value="">Tout Prono+</option>';
		if(count($strlist) > 1) $str .= '<option value="-1" '.($_GET[idl] == -1 ? 'selected="selected"' : '').'>Tous mes amis ('.$totalfriends.')</option>';
		$str .= implode('', $strlist);
		$str .= '</select> <input type="submit" class="link_button" value="Ok"></form>';
		if($totalfriends > 0) echo $str;
	}
	
	if(!$totalfriends)
	{
		echo '<img src="/template/default/group.png" border="0" align="absmiddle" /> <b>Filtrer le classement : Tu n\'as aucun ami, tu ne peux pas filtrer le classement !</b><br />Si tu avais des amis, tu aurais pu afficher le classement de tes amis et toi. Pas de panique ! Clique sur le pseudo d\'un joueur pour voir son profil et éventuellement l\'ajouter en tant qu\'ami !';
	}
}
?>
</td></tr>
</table><br />
<?php } ?>


<a name="class"></a>
<?php
/*
if(!$_GET[sqldep]) $_GET[sqldep] = $_POST[sqldep];
if(!$_GET[sqldep]) $sqldep = 0; else $sqldep = $_GET[sqldep];	

$extension = "&id=".$_GET[id].($_GET[idl] ? "&idl=".$_GET[idl] : "")."#class";
$pagego = "classj.php";
pagination($pagego, $sqldep, $nb_element, 20, $extension);
*/
?><br />


<?php
$color1_a = "bbbbbb";
$color1_b = "aaaaaa";
$color2_a = "eeeeee";
$color2_b = "ffffff";
$altern = 0;
	

// tableau
$SQL = "SELECT `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`,
			`pp_class_user`.`id_user`, `pp_class_user`.`id_matches`,
			`pp_class_user`.`class`, `pp_class_user`.`nb_score_ok`, `pp_class_user`.`nb_result_ok`,
			`pp_class_user`.`nb_matches`, `pp_class_user`.`nb_points`
		FROM `pp_class_user` INNER JOIN `pp_user` ON `pp_user`.`id_user`=`pp_class_user`.`id_user`
		WHERE `pp_class_user`.`id_class`=1
			".($friends_ids != '' ?  " AND `pp_class_user`.id_user IN (".$friends_ids.")" : "")."
			ORDER BY `pp_class_user`.`nb_points` DESC, `pp_class_user`.`nb_score_ok` DESC, `pp_class_user`.`nb_result_ok` DESC, `pp_class_user`.`id_user`
			LIMIT 20";
$result = $db->query($SQL);
if(DB::isError($result))
{
	die ("<li>ERROR : ".$result->getMessage());
	
} else {
	$rang = ($sqldep?$sqldep:0);
  
  if(!$result->numRows())
  {
    echo '<p class="message_error">Pas de stat disponible pour l\'instant :(</p>';
    
  } else {
    ?>
    <table width="100%" cellpadding="2" cellspacing="1">
    <tr>
      <th width="5%">Rang</th>
      <th colspan="2" width="25%" align="center">Joueur</th>
      <th width="10%" align="center">Points</th>
      <th width="10%" align="center">Scores justes</th>
      <th width="10%" align="center">Résultats justes</th>
      <th width="40%" align="center">Grilles de matchs</th>
    </tr>
    <?php
    while($pp_class_user = $result->fetchRow())
    {   
      $rang++;
      
      if($altern) {
        $class_line = 'ligne_grise';
        $altern = 0;
      } else {
        $class_line = 'ligne_blanche';
        $altern = 1;
      }
      
      if($_GET[selj] == $pp_class_user->id_user) $class_line = 'ligne_selected';
  ?>
  <tr class='<?php echo $class_line?>' onmouseover="this.className='ligne_rollover'" onmouseout="this.className='<?php echo $class_line?>'">
    <td align="center"><a name="joueur<?php echo $pp_class_user->id_user?>"></a><?php echo $rang; ?></td>
    <td align="center"><a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_class_user->login))?>" class="link_orange">
    <?php
    if($avatar = getAvatar($pp_class_user->id_user, $pp_class_user->avatar_key, $pp_class_user->avatar_ext, 'small')) {
    ?>
      <img src="/avatars/<?php echo $avatar?>" height="30" width="30" border="0" />
    <?php } else { ?>
      <img src="/template/default/_profil.png" height="30" width="30" border="0" />
    <?php } ?>
    </a>
    </td>
    <td><a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_class_user->login))?>" class="link_orange"><?php
    if($pp_class_user->id_user != $user->id_user) {
      echo $pp_class_user->login;
    } else {
      echo "<font color=\"red\"><b>".$pp_class_user->login."</b></font>";
    }
    ?>
    </a></td>
    <td align="center"><strong><?php echo $pp_class_user->nb_points?></strong></td>
    <td align="center"><?php echo $pp_class_user->nb_score_ok?></td>
    <td align="center"><?php echo $pp_class_user->nb_result_ok?></td>
    <?php
    $date_prono ='';
    $SQL = "SELECT DATE_FORMAT(`pp_matches_user`.`date_creation`, '".$txtlang['AFF_DATE_SQL']."') AS `date_prono`
        FROM `pp_matches_user`
        WHERE `pp_matches_user`.`id_matches`='".$pp_class_user->id_matches."'
        AND `pp_matches_user`.`id_user`='".$pp_class_user->id_user."'";
    $result_pp_matches = $db->query($SQL);
    if(DB::isError($result_pp_matches)) die ("<li>$SQL<li>ERROR : ".$result_pp_matches->getMessage());
    if($pp_matches_user = $result_pp_matches->fetchRow()) $date_prono ='<br />pronostiqué le '.$pp_matches_user->date_prono;
    
    $label_matches = '';
    $image_matches = '';
    $SQL = "SELECT `label`, `image` FROM `pp_matches` WHERE `id_matches`='".$pp_class_user->id_matches."'";
    $result_pp_matches = $db->query($SQL);
    if(DB::isError($result_pp_matches))
    {
      die ("<li>$SQL<li>ERROR : ".$result_pp_matches->getMessage());											
    } else {
      if($pp_matches = $result_pp_matches->fetchRow())
      {
        $label_matches = $pp_matches->label;
        $image_matches = $pp_matches->image;
      }
    }
    ?>
    <td><a class="link_orange" href="classj.php?id=<?php echo $pp_class_user->id_matches;?>"><img src="template/default/<?php echo $image_matches?>" style="float:left; border:solid 3px #eee; margin-right:10px;" height="50" width="35" /><?php echo $label_matches?></a><?php echo $date_prono?></td>
  </tr>
  <?php
    }
    
    echo '</table><br />';
  }
} ?>

			
			
<?php
//pagination($pagego, $sqldep, $nb_element, 20, $extension);
?>

<a name="comments"></a>
<h1 class="title_orange">Commentaires</h1>
<?php
echo pp_comments_afficher('stats', date('Y') . '1', array('url_param' => 'classement-meilleurs-scores.php?'));
?>


</div>
</div>

<?php
pagefooter();
?>