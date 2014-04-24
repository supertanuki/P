<?
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

if(!$user) HeaderRedirect('/');

$message = '';

if($_POST[update_profil])
{	
	$_POST[pwd] = trim($_POST[pwd]);
	
	if(strlen($_POST[pwd])>0 && strlen($_POST[pwd]) < 8)
	{
		$message = "Veuillez choisir un mot de passe d'au moins 8 caractères !";
		
	} else if(!preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]_?])*@[[:alnum:]]([-.]?[[:alnum:]])+\.([a-z]{2,6})$`', $_POST[email]))
	{
		$message = "Veuillez saisir un email valide !";
		
	} else {
	
		// vérification email
		$SQL = "SELECT `id_user`
				FROM `pp_user`
				WHERE `email`='".$db->escapeSimple($_POST[email])."' AND `id_user`!='".$user->id_user."'";
		$result = $db->query($SQL);
		if(DB::isError($result))
		{
			die ("<li>ERROR : ".$result->getMessage()."<li>$SQL");
			
		} else {
			if($result->numRows())
			{
				$message = "Il y a d&eacute;j&agrave; un utilisateur utilisant cet email !";
			} else {
				// insertion
				$SQL = "UPDATE `pp_user` SET
							".(strlen($_POST[pwd])>0 ? "`pwd`='".$db->escapeSimple($_POST[pwd])."'," : "")."
							`email`='".$db->escapeSimple($_POST[email])."',
							`timezone`='".$db->escapeSimple($_POST[fuseau])."',
							`no_mail`='".$db->escapeSimple($_POST[no_mail]?0:1)."',
							`no_mail_end_matches`='".$db->escapeSimple($_POST[no_mail_end_matches]?0:1)."',
							`date_update`=NOW()					
						WHERE `id_user`='".$user->id_user."'";
				$result = $db->query($SQL);
				if(DB::isError($result)) die ($result->getMessage());
				
				if(strlen($_POST[pwd])>0)
				{
					$user = setUser($user->login, $_POST[pwd]);
				} else {
					$user = user_authentificate();
				}
				
				$message = "Votre profil a été enregistré !";
			}
		}
	}
}

pageheader("Profil");
?>

<div id="content_fullscreen">
<?
// affichage des onglets
echo getOnglets('mon_profil');
?>


	<div id="content">
	<h2 class="title_grey">Modifier mon profil</h2>

<? if($message) { ?>
<p id="message_ok" class="message_error"><?=$message?></p>	
<script type="text/javascript">
<!--
new Effect.Highlight('message_ok', {startcolor:'#ff5555', duration:1});
-->
</script>
<? } ?>

	<form method="post" action="/profil.php">
	<input type="hidden" name="update_profil" value="1" />
	<table width="100%">
	<tr><td colspan="2"></td></tr>
	
	<tr><td>Mot de passe</td>	
	<td><input type="password" maxlength="100" name="pwd" class="inputText"></td></tr>
	<tr><td>&nbsp;</td><td>Laissez le champ vide si vous ne souhaitez pas modifier votre mot de passe</td></tr>
	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	
	<tr><td>Email</td>
	<td><input type="text" maxlength="150" size="40" name="email" value="<?=$user->email?>" class="inputText"></td></tr>	
	
	<tr><td nowrap="nowrap">Fuseau horaire</td>
	<td><select name="fuseau" class="inputText">
	<option value="-12" <?=$user->timezone==-12 ? 'selected="selected"' : ''; ?>>(GMT -12h) Eniwetok, Kwajalein</option>
	<option value="-11" <?=$user->timezone==-11 ? 'selected="selected"' : ''; ?>>(GMT -11h) Iles Midway, Samoa</option>
	<option value="-10" <?=$user->timezone==-10 ? 'selected="selected"' : ''; ?>>(GMT -10h) hawaii</option>
	<option value="-9" <?=$user->timezone==-9 ? 'selected="selected"' : ''; ?>>(GMT -9h) Alaska</option>
	<option value="-8" <?=$user->timezone==-8 ? 'selected="selected"' : ''; ?>>(GMT -8h) Pacifique (USA &amp; Canada), Tijuana</option>
	<option value="-7" <?=$user->timezone==-7 ? 'selected="selected"' : ''; ?>>(GMT -7h) Montagnes (USA &amp; Canada), Arizona</option>
	<option value="-6" <?=$user->timezone==-6 ? 'selected="selected"' : ''; ?>>(GMT -6h) Central (USA &amp; Canada), Mexico City</option>
	<option value="-5" <?=$user->timezone==-5 ? 'selected="selected"' : ''; ?>>(GMT -5h) Est (USA &amp; Canada), Bogota, Lima, Quito</option>
	<option value="-4" <?=$user->timezone==-4 ? 'selected="selected"' : ''; ?>>(GMT -4h) heure Atlantique (Canada), Caracas, Antilles</option>
	<option value="-3.5" <?=$user->timezone==-3.5 ? 'selected="selected"' : ''; ?>>(GMT -3:30h) Terre-Neuve</option>
	<option value="-3" <?=$user->timezone==-3 ? 'selected="selected"' : ''; ?>>(GMT -3h) Brasilia, Buenos Aires, Georgetown, Falkland Is</option>
	<option value="-2" <?=$user->timezone==-2 ? 'selected="selected"' : ''; ?>>(GMT -2h) Centre-Atlantique, Ascension Is., St. helena</option>
	<option value="-1" <?=$user->timezone==-1 ? 'selected="selected"' : ''; ?>>(GMT -1h) Les Açores, Iles du Cap Vert</option>
	<option value="0" <?=$user->timezone==0 ? 'selected="selected"' : ''; ?>>(GMT) Casablanca, Dublin, Edinburgh, Londres, Lisbonne, Monrovia</option>
	<option value="1" <?=$user->timezone==1 ? 'selected="selected"' : ''; ?>>(GMT +1h) Amsterdam, Berlin, Bruxelles, Madrid, Paris, Rome</option>
	<option value="2" <?=$user->timezone==2 ? 'selected="selected"' : ''; ?>>(GMT +2h) Le Caire, helsinki, Kaliningrad, Afrique du Sud</option>
	<option value="3" <?=$user->timezone==3 ? 'selected="selected"' : ''; ?>>(GMT +3h) Bagdad, Riyah, Moscow, Nairobi</option>
	<option value="3.5" <?=$user->timezone==3.5 ? 'selected="selected"' : ''; ?>>(GMT +3:30h) Téhéran</option>
	<option value="4" <?=$user->timezone==4 ? 'selected="selected"' : ''; ?>>(GMT +4h) Abu Dhabi, Baku, Muscat, Tbilisi</option>
	<option value="4.5" <?=$user->timezone==4.5 ? 'selected="selected"' : ''; ?>>(GMT +4:30h) Kaboul</option>
	<option value="5" <?=$user->timezone==5 ? 'selected="selected"' : ''; ?>>(GMT +5h) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
	<option value="5.5" <?=$user->timezone==5.5 ? 'selected="selected"' : ''; ?>>(GMT +5:30h) Bombay, Calcutta, Madras, New Delhi</option>
	<option value="6" <?=$user->timezone==6 ? 'selected="selected"' : ''; ?>>(GMT +6h) Almaty, Colombo, Dhaka, Novosibirsk</option>
	<option value="6.5" <?=$user->timezone==6.5 ? 'selected="selected"' : ''; ?>>(GMT +6:30h) Rangoon</option>
	<option value="7" <?=$user->timezone==7 ? 'selected="selected"' : ''; ?>>(GMT +7h) Bangkok, hanoï, Djakarta</option>
	<option value="8" <?=$user->timezone==8 ? 'selected="selected"' : ''; ?>>(GMT +8h) Pékin, hong Kong, Perth, Singapour, Taïpei</option>
	<option value="9" <?=$user->timezone==9 ? 'selected="selected"' : ''; ?>>(GMT +9h) Osaka, Sapporo, Seoul, Tokyo, Yakutsk</option>
	<option value="9.5" <?=$user->timezone==9.5 ? 'selected="selected"' : ''; ?>>(GMT +9:30h) Adélaïde, Darwin</option>
	<option value="10" <?=$user->timezone==10 ? 'selected="selected"' : ''; ?>>(GMT +10h) Canberra, Guam, Melbourne, Sydney, Vladivostok</option>
	<option value="11" <?=$user->timezone==11 ? 'selected="selected"' : ''; ?>>(GMT +11h) Magadan, New Caledonia, Solomon Islands</option>
	<option value="12" <?=$user->timezone==12 ? 'selected="selected"' : ''; ?>>(GMT +12h) Auckland, Wellington, Fiji, Marshall Island</option>
	</select></td></tr>
	
	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	<tr><td align="right"><input type="checkbox" name="no_mail" id="no_mail" value="1" <?=!$user->no_mail ? 'checked="checked"' : ''; ?> /></td>
	<td><label for="no_mail">Recevoir les emails de Prono+ (rappel pour pronostiquer une grille notamment)</label></td></tr>
	
	<? /*
	<tr><td align="right"><input type="checkbox" name="no_mail_end_matches" id="no_mail_end_matches"  value="1" <?=!$user->no_mail_end_matches ? 'checked="checked"' : ''; ?> /></td>
	<td><label for="no_mail_end_matches">Recevoir un email d'alerte lorsqu'un classement d'une grille est en ligne</label></td></tr>	
	*/
	?>
	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td>
	<td><input type="submit" value="Enregistrer" class="link_button" /></td></tr>
	</table></form>



	<br /><br /><br />
	<h2 class="title_orange">Modifier mon avatar</h2>
	<br />
	<table width="100%">
	<tr>
	<td width="40%" valign="top" align="center">
	Avatar actuel<br /><br />
	<div style="padding:10px; border:1px solid #CCCCCC">
	<?
	if($avatar = getAvatar($user->id_user, $user->avatar_key, $user->avatar_ext, 'normal')) {
	?>
		<img src="/avatars/<?=$avatar?>" height="118" width="118" border="0"/>
	<? } else { ?>
		<img src="/template/default/_profil.png" height="118" width="118" border="0" />
	<? } ?>
	</div>
	</td>
	<td width="60%" valign="top">
	<form method="post" enctype="multipart/form-data" onsubmit="$('formAvatar').hide(); $('formAvatarWait').show();">
		<div id="formAvatarWait" style="display:none;"><img src="/template/default/wait.gif" style="float:left; margin-right:10px;" alt="" /> Veuillez patienter svp...</div>
		<div id="formAvatar">
			<div>Choisissez un fichier image sur votre ordinateur :</div>
			<br />
			<div><input id="image_avatar" type="file" name="image" class="link_button" style="width:50%" /><br /><br /><input type="submit" value="Envoyer" class="link_button" /></div>
			<br /><br /><br />
			<div>Formats d'images acceptés : jpg, gif ou png. 118 x 118 pixels minimum et 2500 x 2500 maximum.</div>
			<br /><br /><br /><br /><br />
		</div>
	</form>
	</td>
	</tr>
	</table
	</div>
</div>

<?
pagefooter();
?>