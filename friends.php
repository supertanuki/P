<?php
/**
* Project: PRONOPLUS
* Description: Profil d'un utilisateur
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2010-01-19
* Version: 1.0
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

if($user->id_user)
{
	/*
	// requete liste sans amis
	if($_GET[del] && $_GET[idl])
	{
		$_POST[friends_action] = 'delete';
		$_POST[id_user_listfriends] = $_GET[idl];
	}
	*/
	
	// suppression liste
	if($_POST[friends_action] == 'delete' && $_POST[id_user_listfriends])
	{
		$del_ok = false;
		
		if($_POST[friends_new_list])
		{
			// on vérifie que la liste $id_user_listfriends existe
			$SQL = "SELECT `id_user_listfriends`
					FROM `pp_user_listfriends`
					WHERE `id_user` = '".$user->id_user."' AND id_user_listfriends='".$db->escapeSimple($_POST[id_user_listfriends])."'";
			$select_user_listfriends = $db->query($SQL);
			if(DB::isError($select_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$select_user_listfriends->getMessage());
			if($select_user_listfriends->numRows())
			{			
				// on vérifie que la liste $friends_new_list existe
				$SQL = "SELECT `id_user_listfriends`
						FROM `pp_user_listfriends`
						WHERE `id_user` = '".$user->id_user."' AND id_user_listfriends='".$db->escapeSimple($_POST[friends_new_list])."'";
				$select_user_listfriends = $db->query($SQL);
				if(DB::isError($select_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$select_user_listfriends->getMessage());
				if($select_user_listfriends->numRows())
				{
					$SQL = "UPDATE `pp_user_friends`
							SET id_user_listfriends='".$db->escapeSimple($_POST[friends_new_list])."'
							WHERE `id_user` = '".$user->id_user."'
								AND id_user_listfriends='".$db->escapeSimple($_POST[id_user_listfriends])."'";
					$update_user_listfriends = $db->query($SQL);
					if(DB::isError($update_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$update_user_listfriends->getMessage());
					
					$del_ok = true;
				}
			}
			
		} else {
			$del_ok = true;
		}
		
		if($del_ok)
		{
			// on vérifie que la liste à supprimer ne contient personne
			$SQL = "SELECT id_user_listfriends
					FROM `pp_user_friends`
					WHERE `id_user` = '".$user->id_user."' AND id_user_listfriends='".$db->escapeSimple($_POST[id_user_listfriends])."'";
			$select_user_listfriends = $db->query($SQL);
			if(DB::isError($select_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$select_user_listfriends->getMessage());
			if(!$select_user_listfriends->numRows())
			{
				$SQL = "DELETE FROM `pp_user_listfriends`
						WHERE `id_user` = '".$user->id_user."' AND id_user_listfriends='".$db->escapeSimple($_POST[id_user_listfriends])."'";
				$del_user_listfriends = $db->query($SQL);
				if(DB::isError($del_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$del_user_listfriends->getMessage());
			}
		}
	}	
	
	// modification libellé de liste
	if($_POST[friends_action] == 'edit' && $_POST[id_user_listfriends] && trim($_POST[friends_labellist]))
	{
		$SQL = "UPDATE `pp_user_listfriends`
				SET `label` = '".$db->escapeSimple(trim($_POST[friends_labellist]))."', `date_update` = NOW()
				WHERE `id_user` = '".$user->id_user."' AND id_user_listfriends='".$db->escapeSimple($_POST[id_user_listfriends])."'";
		$update_user_listfriends = $db->query($SQL);
		if(DB::isError($update_user_listfriends)) die ("<li>$SQL<li>ERROR : ".$update_user_listfriends->getMessage());
	}
}

pageheader("Mes amis");
?>
<script type="text/javascript" src="/user.js?v=1"></script>
<script type="text/javascript" src="/friends.js?v=1"></script>

<div id="content_left">
	<?php
	echo getOnglets('mes_amis');
	?>
	<div id="content">
		<ul id="list_left" class="list_sortable">
			<?php
			if(!$user)
			{
				$content = "<p>Inscris-toi à Prono+. Pronostique les matchs de foot.<br />Invite tes amis et consulte ton classement et ceux de tes amis !</p>
							<p align=\"center\"><a onclick=\"Sinscrire(this); return false\" href=\"#\"><img src=\"/image/jouer-a-pronoplus.gif\" border=\"0\" alt=\"Jouer à Prono+\" /></a></p>";
				echo getBlocLeft('Amis', 'orange', $content);
				
				// Membres de prono+ au hasard
				$content = '';
				$SQL = "SELECT `pp_user`.*		
						FROM `pp_user`
						WHERE `pp_user`.`avatar_key` != ''
						ORDER BY RAND()
						LIMIT 30";
				$result_user = $db->query($SQL);
				if(DB::isError($result_user)) die ("<li>ERROR : ".$result_user->getMessage());
				if($result_user->numRows())
				{
					$content .= '<div>';
					while($pp_user_friends = $result_user->fetchRow())
					{
						$avatar = getAvatar($pp_user_friends->id_user, $pp_user_friends->avatar_key, $pp_user_friends->avatar_ext, 'small');
						$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';					
						$content .= '<div style="float:left; margin:4px; padding:4px; border:1px solid #ccc; background:#eee;"><a href="/user.php?q='.urlencode(htmlspecialchars($pp_user_friends->login)).'" class="link_orange"><img src="'.$avatar.'" height="29" width="29" border="0" align="absmiddle" />&nbsp;'.str_replace(' ', '&nbsp;', $pp_user_friends->login).'</a></div>';					
					}
					$content .= '<div style="float:left; margin:4px; padding:4px; border:1px solid #ccc; background:#eee;">...</div>';		
					$content .= '<div style="clear:both;"></div>';		
					$content .= '</div>';				
				}
				echo getBlocLeft('Les membres de Prono+', 'green', $content);
				
				
			} else {
				// Affichage des invitations
				/* recherche d'invitation amis */
				
				$SQL = "SELECT `pp_user`.*		
						FROM `pp_user_friends`
						INNER JOIN `pp_user` ON `pp_user`.`id_user` = `pp_user_friends`.`id_user`
						WHERE `pp_user_friends`.`id_user_friend`='".$user->id_user."' AND `pp_user_friends`.`valide`=''
						ORDER BY `pp_user_friends`.`date_creation`";
				$result_user_friends = $db->query($SQL);
				if(DB::isError($result_user_friends)) die ("<li>ERROR : ".$result_user_friends->getMessage());
				if($result_user_friends->numRows())
				{
					$invitations = array();
					while($pp_user_friends = $result_user_friends->fetchRow())
					{
						$content = '';
						$avatar = getAvatar($pp_user_friends->id_user, $pp_user_friends->avatar_key, $pp_user_friends->avatar_ext, 'small');
						$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';
						
						$content .= '<p><a href="/user.php?q='.urlencode(htmlspecialchars($pp_user_friends->login)).'" class="link_orange"><img src="'.$avatar.'" height="29" width="29" border="0" align="absmiddle" />&nbsp;'.$pp_user_friends->login.'</a></p>';
							
						$content .= '<p id="user_add_friend_'.$pp_user_friends->id_user.'">Te propose d\'être son ami : <a href="#" onclick="return user_show_lists('.$pp_user_friends->id_user.');" class="link_button"><img src="/template/default/group_add.png" height="16" width="16" border="0" align="absmiddle" />&nbsp;Accepter</a> ou <a href="#" onclick="return user_friend_delete('.$pp_user_friends->id_user.');" class="link_orange">ignorer</a></p>';
						
						$invitations[] = $content;
					}
					
					$content = implode('<hr />', $invitations);
					echo getBlocLeft('Invitations amis', 'green', $content);
				}
				
				$content = '';
				$no_friends = false;
				
				// Les listes de l'utilisateur courant
				$SQL = "SELECT `pp_user_listfriends`.`id_user_listfriends`, `pp_user_listfriends`.`label`
						FROM `pp_user_listfriends`
						WHERE `pp_user_listfriends`.`id_user` = '".$user->id_user."'
						ORDER BY `pp_user_listfriends`.`label`";
				$result_user_listfriends = $db->query($SQL);
				if(DB::isError($result_user_listfriends)) die ("<li>ERROR : ".$result_user_listfriends->getMessage());
				if(!$result_user_listfriends->numRows())
				{
					$no_friends = true;
					
				} else {
					$content = '';
					
					$listfriends = array();
					while($pp_user_listfriends = $result_user_listfriends->fetchRow())
					{
						$listfriends[$pp_user_listfriends->id_user_listfriends] = htmlspecialchars($pp_user_listfriends->label);
					}
					
					// Les amis de l'utilisateur courant
					$friends = array();					
					$SQL = "SELECT `pp_user`.`id_user`, `pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`,
								`pp_user_friends`.`id_user_listfriends`
							FROM `pp_user_friends`
							INNER JOIN `pp_user` ON `pp_user`.`id_user` = `pp_user_friends`.`id_user_friend`
							WHERE `pp_user_friends`.`id_user`='".$user->id_user."' AND `pp_user_friends`.`valide`='1'
							ORDER BY `pp_user_friends`.`id_user_listfriends`, `pp_user`.`login`";
					$result_user_friends = $db->query($SQL);
					if(DB::isError($result_user_friends)) die ("<li>ERROR : ".$result_user_friends->getMessage());
					if($nb_friends = $result_user_friends->numRows())
					{
						
						while($pp_user_friends = $result_user_friends->fetchRow())
						{
							$avatar = getAvatar($pp_user_friends->id_user, $pp_user_friends->avatar_key, $pp_user_friends->avatar_ext, 'small');
							$avatar = $avatar ? '/avatars/'.$avatar : '/template/default/_profil.png';					
							$friends[$pp_user_friends->id_user_listfriends][] = '<div style="float:left; margin:4px; padding:4px; border:1px solid #ccc; background:#eee;"><a href="/user.php?q='.urlencode(htmlspecialchars($pp_user_friends->login)).'" class="link_orange"><img src="'.$avatar.'" height="29" width="29" border="0" align="absmiddle" />&nbsp;'.str_replace(' ', '&nbsp;', $pp_user_friends->login).'</a></div>';
						}
				
					} else {
						$no_friends = true;
					}
					
					
					
					
					
					$content = '';
					$content .= '<p><b>Tu as '.$nb_friends.' ami'.($nb_friends > 1 ? 's' : '').'.</b> <span id="voir_mes_amis"><a href="#" onclick="$(\'voir_mes_amis\').hide(); $(\'cacher_mes_amis\').show(); Effect.toggle(\'mes_amis\', \'blind\', { duration: 0.4 }); return false" class="link_button">Voir et organiser mes listes d\'amis</a></span><span id="cacher_mes_amis" style="display:none"><a href="#" onclick="$(\'voir_mes_amis\').show(); $(\'cacher_mes_amis\').hide(); Effect.toggle(\'mes_amis\', \'blind\', { duration: 0.4 }); return false" class="link_button">Cacher listes des amis</a></span></p><div id="mes_amis" style="display:none">';
					
					// Affichage des listes
					foreach($listfriends as $id_user_listfriends => $listlabel)
					{
						// nb amis de cette liste ?
						$nb_amis_list = 0;
						if(is_array($friends[$id_user_listfriends])) $nb_amis_list = count($friends[$id_user_listfriends]);
						
						$content .= '<br /><fieldset><legend><span id="friends_list_'.$id_user_listfriends.'"><b>'.$listlabel.'</b>&nbsp;<a href="#" onclick="return friend_edit_listfriend('.$id_user_listfriends.');" title="Modifier le libellé de la liste"><img src="/template/default/page_edit.png" align="absmiddle" border="0" /></a> '.(count($listfriends)>1 ? '<a href="#" onclick="return friend_del_listfriend('.$id_user_listfriends.');" title="Supprimer la liste"><img src="/template/default/page_delete.png" align="absmiddle" border="0" /></a>' : '').'</span>';
						/*
						$content .= '<br /><fieldset><legend><span id="friends_list_'.$id_user_listfriends.'"><b>'.$listlabel.'</b>&nbsp;<a href="#" onclick="return friend_edit_listfriend('.$id_user_listfriends.');" title="Modifier le libellé de la liste"><img src="/template/default/page_edit.png" align="absmiddle" border="0" /></a>'.(count($listfriends)>1 ? '&nbsp;'.($nb_amis_list > 0 ? '<a href="#" onclick="return friend_del_listfriend('.$id_user_listfriends.');" title="Supprimer la liste">' : '<a href="/friends.php?del=1&idl='.$id_user_listfriends.'" title="Supprimer la liste">').'<img src="/template/default/page_delete.png" align="absmiddle" border="0" /></a>' : '').'</span>';
						*/
						$content .= '<span id="friends_edit_list_'.$id_user_listfriends.'" style="display:none">
										<form method="post" action="">
										<input type="hidden" name="friends_action" value="edit" />
										<input type="hidden" name="id_user_listfriends" value="'.$id_user_listfriends.'" />
										<input name="friends_labellist" type="text" size="25" maxlength="50" value="'.$listlabel.'" />
										<input type="submit" value="Ok" class="link_button" /> ou <a href="#" class="link_orange" onclick="return friend_cancel_listfriend('.$id_user_listfriends.')">annuler</a>
										</form>
									</span>';
						
						if(count($listfriends)>1)
						{
							$options = '';
							foreach($listfriends as $id => $listlabel) if($id != $id_user_listfriends)
							{
								$options .= '<option value="'.$id.'">'.$listlabel.'</option>';
							}
							$content .= '<span id="friends_del_list_'.$id_user_listfriends.'" style="display:none">
											<form method="post" action="">
											<input type="hidden" name="friends_action" value="delete" />
											<input type="hidden" name="id_user_listfriends" value="'.$id_user_listfriends.'" />
											Suppression de <b>'.$listlabel.'</b><br />
											Déplacer tes amis vers la liste
											<select name="friends_new_list">
												'.$options.'
											</select>
											<input type="submit" value="Ok" class="link_button" /> ou <a href="#" class="link_orange" onclick="return friend_cancel_listfriend('.$id_user_listfriends.')">annuler</a>
											</form>
										</span>';
						}
						
						$content .= '</legend>';
						
						if(is_array($friends[$id_user_listfriends]) && count($friends[$id_user_listfriends])) foreach($friends[$id_user_listfriends] as $friend)
						{
							$content .= $friend;
						} else {
							$content .= "<p>Tu n'as aucun ami dans cette liste !</p>";
						}
						$content .= '<div style="clear:both;"></div>';
						$content .= '</fieldset>';
					}
					$content .= '</div>';
					echo getBlocLeft('Amis', 'green', $content);
					
					
					
					
					
					// activité des amis					
					user_update_date_now($user->id_user, 'date_view_friends');					
					// si accès restreint possible sur les listes amis, on cherche la liste de l'user // sauf si user == wall
					$user_listfriends = array();
					$SQL = "SELECT `id_user_listfriends`, `id_user`
							FROM `pp_user_friends`
							WHERE `id_user_friend`='".$user->id_user."'
							AND `valide`='1'";
					$result = $db->query($SQL);
					if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());
					while($pp_user_friends = $result->fetchRow())
					{
						$user_listfriends[$pp_user_friends->id_user] = $pp_user_friends->id_user_listfriends;
					}
					
					//echo "<pre>"; print_r($user_listfriends); echo "</pre>";
					
					
					
					// recherche des id_comment concernés
					$pp_ids_comment = array();
					$SQL = "SELECT `pp_comments`.`id_comment`, `pp_comments`.`parent_id_comment`
							FROM `pp_comments`
							INNER JOIN `pp_user_friends` ON `pp_user_friends`.`id_user_friend` = `pp_comments`.`id_user` AND `pp_user_friends`.`id_user`='".$user->id_user."'
							WHERE 
								`pp_comments`.`deleted` != '1'
								AND `pp_comments`.`type` = 'wall' AND `pp_comments`.`id_type` != '".$user->id_user."'
								AND (`pp_comments`.`id_user` = '".$user->id_user."'
										OR `pp_comments`.`id_user_listfriends`=0
										".(count($user_listfriends) ? " OR `pp_comments`.`id_user_listfriends` IN (".implode(',', $user_listfriends).") " : "")."
										".(count($user_listfriends) ? " OR `pp_comments`.`id_user_listfriends`=-1 " : "")."
									)
							ORDER BY `pp_comments`.`date_creation` DESC
							LIMIT 50";
					//echo "<li>$SQL</li>";
					$result = $db->query($SQL);
					if(DB::isError($result)) die ("<li>$SQL<li>ERROR : ".$result->getMessage());
					while($pp_comments = $result->fetchRow())
					{
						if(!$pp_comments->parent_id_comment)
							$pp_ids_comment[$pp_comments->id_comment] = $pp_comments->id_comment;
						else
							$pp_ids_comment[$pp_comments->parent_id_comment] = $pp_comments->parent_id_comment;
					}
					
					$pp_matches_arr = array();
					$content = '';									
					$SQL = "SELECT `pp_comments`.`id_comment`, `pp_comments`.`type`, `pp_comments`.`id_type`, `pp_comments`.`id_user`, `pp_comments`.`id_user_listfriends`,
								`pp_user`.`login`, `pp_user`.`avatar_key`, `pp_user`.`avatar_ext`,
								`pp_comments`.`message`,
								DATE_FORMAT(`pp_comments`.`date_creation`, '".$txtlang['AFF_DATE_TIME_SQL']."') AS `date_creation_format`
							FROM `pp_comments`
							INNER JOIN `pp_user` ON `pp_comments`.`id_user` = `pp_user`.`id_user`
							WHERE 
								`pp_comments`.`deleted` != '1'
								AND `pp_comments`.`id_comment` IN (".implode(',', $pp_ids_comment).")
								AND `pp_comments`.`type` = 'wall'
							ORDER BY `date_creation` DESC
							LIMIT 30";
					//echo "<li>$SQL</li>";
					$result = $db->query($SQL);
					if(DB::isError($result))
					{
						die ("<li>$SQL<li>ERROR : ".$result->getMessage());
						
					} else {
						if($nbcomments = $result->numRows())
						{
							while($pp_comments = $result->fetchRow())
							{
								//if($pp_comments->id_user_listfriends == -1 || $pp_comments->id_user_listfriends != -1 && $user_listfriends[$pp_comments->id_user])
								//{
									
									// messages de wall
									if($pp_comments->type == 'wall')
									{
										//$content .= "<p>id_comment=".$pp_comments->id_comment."</p>";
										
										$content .= pp_comments_afficher('wall', $pp_comments->id_type, array('id_comment' => $pp_comments->id_comment, 'show_context' => true, 'no_form' => true, 'url_param' => '/friends.php?', 'show_nb_messages' => false, 'possible_list' => true, 'herite' => true));
									
									
									// messages de classj
									} else if($pp_comments->type == 'classj')
									{
										$id_matches = substr($pp_comments->id_type, 4, strlen($pp_comments->id_type));
										
										if(!$pp_matches_arr[$id_matches])
										{
											$SQL = "SELECT `label` FROM `pp_matches` WHERE `id_matches`='".$id_matches."'";
											$result_pp_matches = $db->query($SQL);
											if(DB::isError($result_pp_matches))
											{
												die ("<li>$SQL<li>ERROR : ".$result_pp_matches->getMessage());											
											} else {
												if($pp_matches = $result_pp_matches->fetchRow()) $pp_matches_arr[$id_matches] = $pp_matches;
											}
										}
										
										if($pp_matches = $pp_matches_arr[$id_matches])
										{
										
											$html = '<table width="100%"  border="0" cellspacing="1" cellpadding="4">';
											$class = 'ligne_blanche';
											$class = ($class != 'ligne_blanche' ? 'ligne_blanche' : 'ligne_grise');
											$html .= '<tr class="'.$class.'">
													<td width="1%" valign="top" nowrap="nowrap" align="center">
													<a href="/user.php?q='.urlencode(htmlspecialchars($pp_comments->login)).'" class="link_orange">';

											if($avatar = getAvatar($pp_comments->id_user, $pp_comments->avatar_key, $pp_comments->avatar_ext, 'small'))
											{
												$html .= '<img src="/avatars/'.$avatar.'" height="30" width="30" border="0" />';
											} else {
												$html .= '<img src="/template/default/_profil.png" height="30" width="30" border="0" />';
											}
											
											$html .= '<br />'.htmlspecialchars($pp_comments->login).'</a>	
														</td>
														<td width="99%">
															<i>a commenté <a href="/classj.php?id='.$id_matches.'#comments" class="link_orange">'.$pp_matches->label.'</a></i> :<br /><br />
															'.formattexte($pp_comments->message).'
															<br /><br /><span style="font-size:10px; color:#aaa">Le ' . $pp_comments->date_creation_format . '</span>			
														</td>
													</tr>';
											$html .= '</table><br />';
											$content .= $html;
										}
									}
								//}
							}
							echo getBlocLeft('Activités de mes amis', 'orange', $content);
						}
					}
					
					
					
					
					
					
									
					
				}
				
				if($no_friends)
				{
					$content .= '<p><img src="/smileys/14.gif" align="absmiddle" /> C\'est trop triste !<br /><br />Tu n\'as malheureusement pas encore d\'amis. Pas de panique ! Clique sur le pseudo d\'un joueur sur le forum ou dans les classements pour voir son profil et éventuellement l\'ajouter en tant qu\'ami !</p>';				
					echo getBlocLeft('Amis', 'green', $content);
				}
			}
			?>
		</ul>
	</div>
</div>

<div id="content_right">
	<?php
	getRightBulle($user);
	getRightProfil($user);
	?>
</div>

<?php
pagefooter();
?>