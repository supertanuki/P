<?php
/**
* Project: PRONOPLUS
* Description: Récompenses
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2010-01-19
* Version: 1.0
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');
require_once('recompenses_ids.php');




if(!$_GET[q]) HeaderRedirect('/');

$user = user_authentificate();

// recherche du joueur
if($_GET[q])
{
	//$_GET[q] = utf8_encode($_GET[q]);
	$SQL = "SELECT `pp_user`.*, DATE_FORMAT(`pp_user`.`register_date`, '".$txtlang['AFF_DATE_SQL']."') AS `register_date_format`
			FROM `pp_user`
			WHERE `pp_user`.`login`='".$db->escapeSimple(trim($_GET[q]))."'";
	//echo $SQL;
	$result_user = $db->query($SQL);
	if(DB::isError($result_user))
	{
		die ("<li>ERROR : ".$result_user->getMessage());
		
	} else {
		if(!$pp_user = $result_user->fetchRow())
		{
			HeaderRedirect('/');
		}
	} 
}

// on récupère les étoiles déjà obtenues
$recompenses_valid = array();
$SQL = "SELECT id_recompense, nb
                FROM  `pp_user_recompenses`
                WHERE  `id_user` = " . $pp_user->id_user;
$result_recompenses = $db->query($SQL);
while($pp_recompenses = $result_recompenses->fetchRow())
{
    $recompenses_valid[$pp_recompenses->id_recompense] = $pp_recompenses->nb;
}

// totaux
$recompenses_total = array();
$recompenses_total_detail = array();
$SQL = "SELECT count(*) AS `TOTAL`, id_recompense
                FROM  `pp_user_recompenses`
                WHERE  `id_user` != " . $pp_user->id_user . "
                GROUP BY id_recompense";
$result_recompenses = $db->query($SQL);
while($pp_recompenses = $result_recompenses->fetchRow())
{
    $recompenses_total[$pp_recompenses->id_recompense] = $pp_recompenses->TOTAL;
    
    /*
    if($pp_recompenses->TOTAL <= 3)
    {
        // recherche joueurs
        $SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`
        FROM `pp_user_recompenses`
        INNER JOIN `pp_user` ON `pp_user`.`id_user`=`pp_user_recompenses`.`id_user` AND `pp_user_recompenses`.`id_user` != " . $pp_user->id_user."
        WHERE `pp_user_recompenses`.`id_recompense`='".$pp_recompenses->id_recompense."'";        
        //echo ($SQL);
        $result_users = $db->query($SQL);
        if(DB::isError($result_users))
        {
	        die ("<li>ERROR : ".$result_users->getMessage());
	
        } else {
	        while($pp_user_star = $result_users->fetchRow())
	        {
		        $recompenses_total_detail[$pp_recompenses->id_recompense][] = $pp_user_star;
	        }
        }
    }
    */
}

pageheader("Récompenses de ".$pp_user->login, array('meta_description' => 'Mur de '.$pp_user->login.', ses messages et les messages de ses amis'));
?>
<script type="text/javascript" src="/user.js?v=1"></script>

<div id="content_left">
	<?php
	echo getOnglets('mes_recompenses');
	?>
	<div id="content">
    <h1 class="title_green">Les étoiles de Prono+, les objectifs à atteindre !</h1>
    
    <p>Voici les objectifs ou/et défis que vous devrez atteindre sur Prono+ et ainsi obtenir les étoiles suivantes en récompenses !</p>
    <p>Les étoiles obtenues ne sont mises à jour que toutes les nuits (forcément... pour des étoiles ! :)).</p>    
    
		<?php
		if($pp_user->id_user != $user->id_user)
		{
			?>
			<p>
            Les objectifs atteints par :
			<?php
			if($avatar = getAvatar($pp_user->id_user, $pp_user->avatar_key, $pp_user->avatar_ext, 'small')) {
			?>
				<a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange"><img src="/avatars/<?php echo $avatar?>" height="30" width="30" border="0" style="vertical-align:middle" /></a>
			<? } else { ?>
				<a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange"><img src="/template/default/_profil.png" height="30" width="30" border="0" style="vertical-align:middle" /></a>
			<? } ?>
			<a href="/user.php?q=<?php echo urlencode(htmlspecialchars($pp_user->login))?>" class="link_orange"><?php echo htmlspecialchars($pp_user->login); ?></a>
			</p>
			<?php
		}

    $nbetoiles = 0;
    foreach($recompenses_ids as $title=>$rr)
    {
        foreach($rr as $r)
        {
            $nbetoiles++;
        }
    }

    echo '<p class="'.
        (count($recompenses_valid) ? 'recompense-star' : 'recompense-star-disabled')
        .'"> TOTAL : '.
        (!count($recompenses_valid) ?
            'Aucune étoile obtenue :(' :
            (count($recompenses_valid)>1 ? count($recompenses_valid).' étoiles obtenues sur '.$nbetoiles.' ! :)' : 'Qu\'une seule étoile obtenue sur '.$nbetoiles.' !')
        ).'</p>';

    foreach($recompenses_ids as $title=>$rr)
    {
      echo '<br /><h2 class="title_grey">'.$title.'</h2>';
      foreach($rr as $r)
      {
        echo '<p class="'.($recompenses_valid[ $r['id'] ] ? 'recompense-star' : 'recompense-star-disabled').'">'.$r['title'].($recompenses_valid[ $r['id'] ]>1 ? ' ('.$recompenses_valid[ $r['id'] ].')' : '');
        
        echo '<span>';
        
        echo $recompenses_total[ $r['id'] ] >=1 ?
            ($recompenses_valid[ $r['id'] ] ? $recompenses_total[ $r['id'] ] . ($recompenses_total[ $r['id'] ] ==1 ? 'autre joueur' : ' autres joueurs') : ($recompenses_total[ $r['id'] ] == 1 ? $recompenses_total[ $r['id'] ].' joueur' : $recompenses_total[ $r['id'] ].' joueurs')) : 
            ($recompenses_valid[ $r['id'] ] ? 'Tu es le seul ! Tu est trop fort !' : 'Aucun !');
            
        /*
        $joueurs_en_avant = array();
        if(is_array($recompenses_total_detail[ $r['id'] ]) && count($recompenses_total_detail[ $r['id'] ]))
        {
            foreach($recompenses_total_detail[ $r['id'] ] as $joueur)
            {
                if($avatar = getAvatar($joueur->id_user, $joueur->avatar_key, $joueur->avatar_ext, 'small')) {
                    $joueur_avatar = '<img src="/avatars/'.$avatar.'" height="30" width="30" border="0" align="absmiddle" />';
                } else {
                    $joueur_avatar = '<img src="/template/default/_profil.png" height="30" width="30" border="0" align="absmiddle" />';
                }
                
                $joueurs_en_avant[] = '<a href="/recompenses.php?q='.urlencode(htmlspecialchars($joueur->login)).'" class="link_button">' . $joueur_avatar . '&nbsp;' . htmlspecialchars($joueur->login) . '</a>';
            }
        }
        
        if(count($joueurs_en_avant))
        {
            echo ' : ' . implode(' ', $joueurs_en_avant);
        }            
            
        echo '</span><div class="clear"></div>';
        */
        
        echo '</p>';
      }
    }
    ?>
	</div>
</div>

<div id="content_right">
	<?php
	getRightBulle($pp_user);
	getRightProfil($pp_user);
	?>
</div>

<?php
pagefooter();
?>
