<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();

if(!$user) HeaderRedirect('/');

//var_dump(gd_info());


$dir_upload = $_SERVER['DOCUMENT_ROOT'].'/upload/';
$dir_avatar = $_SERVER['DOCUMENT_ROOT'].'/avatars/';
$message_error ='';

function imageresize($image, $widthmax, $heightmax)
{
	if(file_exists($image))
	{
		list($w, $h) = @getimagesize($image);		
		
		if($w<$widthmax || $h<$heightmax) return false;
		
		if($w>=$widthmax) {
			$fact=$widthmax/$w;
			$nw=round($w*$fact);
			$nh=round($h*$fact);
			
		} else if($h>=$heightmax) {
			$fact=$heightmax/$h;
			$nw=round($w*$fact);
			$nh=round($h*$fact);
			
		} else return false;
		
		return array('nw'=>$nw, 'nh'=>$nh, 'w'=>$w, 'h'=>$h);
	
	} else return false;
}

function createThumbImage($filename, $filename_destination, $type, $widthmax, $heightmax, $src_x=0, $src_y=0, $src_w=0, $src_h=0)
{
	// Calcul des nouvelles dimensions
	$size = imageresize($filename, $widthmax, $heightmax);
	if(!$size || !$size['nw'] || !$size['nh'] || !$size['w'] || !$size['h']) return false;	
	
	if(!$thumb = @imagecreatetruecolor($src_w ? $widthmax : $size['nw'], $src_w ? $heightmax : $size['nh'])) return false;
	
	if($type=='image/jpeg' || $type=='image/pjpeg')
	{
		if(!$source = @imagecreatefromjpeg($filename)) return false;
	} else if($type=='image/png' || $type=='image/x-png')
	{
		if(!$source = @imagecreatefrompng($filename)) return false;
	} else if($type=='image/gif')
	{
		if(!$source = @imagecreatefromgif($filename)) return false;
	} else
	{
		return false;
	}
	
	// Redimensionnement
	/*@imagecopyresized(	$thumb, $source, 0, 0,
						$src_x, $src_y,
						$src_w ? $widthmax : $size['nw'],
						$src_w ? $heightmax : $size['nh'],
						$src_w ? $src_w : $size['w'],
						$src_h ? $src_h : $size['h']);*/
						
	if(!@imagecopyresampled  ( $thumb, $source, 0, 0,
						$src_x, $src_y,
						$src_w ? $widthmax : $size['nw'],
						$src_w ? $heightmax : $size['nh'],
						$src_w ? $src_w : $size['w'],
						$src_h ? $src_h : $size['h']  )) return false;
	
	// Save
	if($type=='image/jpeg' || $type=='image/pjpeg')
	{
		if(!@imagejpeg($thumb, $filename_destination, 100)) return false;
	} else if($type=='image/png' || $type=='image/x-png')
	{
		if(!@imagepng($thumb, $filename_destination, 0)) return false;
	} else if($type=='image/gif')
	{
		if(!@imagegif($thumb, $filename_destination)) return false;
	} else
	{
		return false;
	}
	
	return $filename_destination;
}


function uploadfile()
{
	global $dir_upload, $user, $message_error;	
	$image2crop = false;
	if($_FILES['image']['tmp_name'])
	{
		if(is_uploaded_file($_FILES['image']['tmp_name']))
		{			
			if($_FILES['image']['type']!='image/gif' && $_FILES['image']['type']!='image/jpeg' && $_FILES['image']['type']!='image/png' && $_FILES['image']['type']!='image/pjpeg' && $_FILES['image']['type']!='image/x-png')
			{			
				$message_error = "Vous ne pouvez envoyer que des images au format jpg, gif ou png. Essayez une autre image svp.";
				unlink($_FILES['image']['tmp_name']);
				return false;
			
			} else {
			
				//$filename = $_FILES['image']['name'];
				
				if($_FILES['image']['type']=='image/gif')
					$extension = 'gif';
				else if($_FILES['image']['type']=='image/jpeg' || $_FILES['image']['type']=='image/pjpeg')
					$extension = 'jpg';
				else if($_FILES['image']['type']=='image/png' || $_FILES['image']['type']=='image/x-png')
					$extension = 'png';
				
				$filename = $user->id_user.'-crop.'.$extension;
			
				if(move_uploaded_file($_FILES['image']['tmp_name'], $dir_upload.$filename))
				{
					// taille image > 300 x 300
					list($w, $h) = @getimagesize($dir_upload.$filename);
					if($h<118 || $w<118 || $h>2500 || $w>2500)
					{
						$message_error = "L'image doit faire au moins 118 x 118 pixels et au plus 2500 x 2500 pixels.";
						return false;
						
					} else {
						$size = imageresize($dir_upload.$filename, 300, 300);
						if($size['nh']>=118 && $size['nw']>=118)
						{
							if($img = createThumbImage($dir_upload.$filename, $dir_upload.$filename, $_FILES['image']['type'], 300, 300))
							{
								//echo "<p>Redimensionnement OK !<br />Chargement de l'image réussi ! /1</p>";
								$image2crop = $img;
								
							} else {
								$message_error = "Impossible de redimensionner l'image ! Essayez de charger une autre image.";
								return false;
							}
							
						} else {
							// TODO : minimiser à 118 la largeur ou la hauteur
							
							if($img = createThumbImage($dir_upload.$filename, $dir_upload.$filename, $_FILES['image']['type'], $w, $h))
							{
								//echo "<p>Redimensionnement OK !<br />Chargement de l'image réussi ! /2</p>";
								$image2crop = $img;
								
								if($w==118 && $h==118)
								{
									$_POST[image2crop] = basename($image2crop);
									$_POST[x1] = 0;
									$_POST[y1] = 0;
									$_POST[width] = 118;
									$_POST[height] = 118;
									CropImage();
								}
								
							} else {
								$message_error = "Impossible de redimensionner l'image ! Essayez de charger une autre image.";
							}
						}
					}
					
				} else
				{
					$message_error = "Erreur de chargement de l'image !";
				}
			}
			
		} else
		{
			$message_error = "Erreur de chargement de l'image !";
		}
	}
	
	return $image2crop;
}












function CropImage()
{
	global $db, $dir_upload, $dir_avatar, $user, $message_error;
	if($_POST[image2crop] && isset($_POST[x1]) && isset($_POST[y1]) && isset($_POST[width]) && isset($_POST[height]))
	{
		$type = @getimagesize($dir_upload.$_POST[image2crop]);
		
		if($type[mime]=='image/gif')
			$extension = 'gif';
		else if($type[mime]=='image/jpeg' || $type[mime]=='image/pjpeg')
			$extension = 'jpg';
		else if($type[mime]=='image/png' || $type[mime]=='image/x-png')
			$extension = 'png';
			
		if(!$extension)
		{		
			$message_error = "Impossible de redimensionner l'image ! Essayez de charger une autre image.";
			return false;
		}
		
		$filename_source = $user->id_user.'-crop.'.$extension;
		$md = md5(time());
		$filename_destination = $user->id_user.'-'.$md.'-118.'.$extension;
		$thumb_destination = $user->id_user.'-'.$md.'-59.'.$extension;
		$thumb2_destination = $user->id_user.'-'.$md.'-30.'.$extension;
		
		
		if(createThumbImage($dir_upload.$filename_source, $dir_upload.$filename_destination, $type[mime], 118, 118, $_POST[x1], $_POST[y1], $_POST[width], $_POST[height]))
		{
			if(createThumbImage($dir_upload.$filename_source, $dir_upload.$thumb_destination, $type[mime], 59, 59, $_POST[x1], $_POST[y1], $_POST[width], $_POST[height]))
			{
				if(createThumbImage($dir_upload.$filename_source, $dir_upload.$thumb2_destination, $type[mime], 30, 30, $_POST[x1], $_POST[y1], $_POST[width], $_POST[height]))
				{
					//echo "<p class=\"message_error\">Avatar enregistré !</p>";
					
					// déplacer les fichiers
					if(file_exists($dir_upload.$filename_source)) unlink($dir_upload.$filename_source);
					
					copy($dir_upload.$filename_destination, $dir_avatar.$filename_destination);
					if(file_exists($dir_upload.$filename_destination)) unlink($dir_upload.$filename_destination);
					
					copy($dir_upload.$thumb_destination, $dir_avatar.$thumb_destination);
					if(file_exists($dir_upload.$thumb_destination)) unlink($dir_upload.$thumb_destination);
					
					copy($dir_upload.$thumb2_destination, $dir_avatar.$thumb2_destination);
					if(file_exists($dir_upload.$thumb2_destination)) unlink($dir_upload.$thumb2_destination);
					
					// supprimer l'ancien avatar
					if($user->avatar_key && $user->avatar_ext)
					{
						$avatar = $user->id_user.'-'.$user->avatar_key.'-118.'.$user->avatar_ext;
						if(file_exists($dir_avatar.$avatar)) unlink($dir_avatar.$avatar);
						$avatar = $user->id_user.'-'.$user->avatar_key.'-59.'.$user->avatar_ext;
						if(file_exists($dir_avatar.$avatar)) unlink($dir_avatar.$avatar);
						$avatar = $user->id_user.'-'.$user->avatar_key.'-30.'.$user->avatar_ext;
						if(file_exists($dir_avatar.$avatar)) unlink($dir_avatar.$avatar);
					}
					
					$SQL = "UPDATE `pp_user` SET `avatar_key`='".$md."', `avatar_ext`='".$extension."' WHERE `id_user`='".$user->id_user."'";
					$result_class = $db->query($SQL);
					if(DB::isError($result_class)) die ("<li>ERROR : ".$result_class->getMessage());
					
					HeaderRedirect('/');
					
				} else {			
					$message_error = "Impossible de redimensionner l'image ! Essayez de charger une autre image.";
					return false;
				}
					
			} else {			
				$message_error = "Impossible de redimensionner l'image ! Essayez de charger une autre image.";
				return false;
			}
			
		} else {		
			$message_error = "Impossible de redimensionner l'image ! Essayez de charger une autre image.";
			return false;
		}
	}
	
	return false;	
}


$imagecropped = CropImage();
$image2crop = uploadfile();

pageheader("Avatar | Prono+");
?>


<div id="content_fullscreen">
<?php
// affichage des onglets
echo getOnglets('mon_profil');
?>
<div id="content">



<style type="text/css">		
	#testWrap {
		float:left;	
		border:solid 1px #000;		
	}
	
	#preview_results {
		clear: both;
	}
	
	#preview_bloc 
	{
		margin-left:20px;
		float:left;
	}
	#preview_bloc h3
	{
		margin:0px; padding:4px;
	}
	#previewArea
	{
		border:solid 1px #000;
	}
</style>

<?php
//charger l'image
if(!$image2crop)
{
?>
	<h2 class="title_orange">Choisir votre avatar</h2>
	<?php if($message_error) echo '<p class="message_error">'.$message_error.'</p>'; ?>
	<br />
	<table width="100%">
	<tr>
	<td width="40%" valign="top" align="center">
	Avatar actuel<br /><br />
	<div style="padding:10px; border:1px solid #CCCCCC">
	<?php
	if($avatar = getAvatar($user->id_user, $user->avatar_key, $user->avatar_ext, 'normal')) {
	?>
		<img src="/avatars/<?php echo $avatar?>" height="118" width="118" border="0"/>
	<?php } else { ?>
		<img src="/template/default/_profil.png" height="118" width="118" border="0" />
	<?php } ?>
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

<?php } else {

// recadrer l'image
list($w, $h) = getimagesize($image2crop);
?>

<form method="post">
	<input type="hidden" name="image2crop" value="<?php echo basename($image2crop)?>" />
	
	<h2 class="title_orange">Recadrer votre avatar</h2>
	<?php if($message_error) echo '<p class="message_error">'.$message_error.'</p>'; ?>
	
	<br />Recadrer ou redimensionner l'image pour créer votre avatar :<br /><br />
	
	<div id="testWrap">
		<img src="upload/<?php echo basename($image2crop)?>?a=<?php echo time()?>" alt="" id="testImage" width="<?php echo $w?>" height="<?php echo $h?>" />
	</div>
	<div id="preview_bloc">
	  <h3>Aperçu</h3>
	  <div id="previewArea"></div>
	</div>
	
	<input type="hidden" name="x1" id="x1" />
	<input type="hidden" name="y1" id="y1" />
	<input type="hidden" name="x2" id="x2" />
	<input type="hidden" name="y2" id="y2" />
	<input type="hidden" name="width" id="width" />
	<input type="hidden" name="height" id="height" />
	
	<div id="preview_results"><br /><br /><input type="submit" value="Enregistrer" class="link_button" /></div>
</form>

<script src="/lib/cropper/cropper.js" type="text/javascript"></script>	
<script type="text/javascript">		
	function onEndCrop( coords, dimensions ) {
		$( 'x1' ).value = coords.x1;
		$( 'y1' ).value = coords.y1;
		$( 'x2' ).value = coords.x2;
		$( 'y2' ).value = coords.y2;
		$( 'width' ).value = dimensions.width;
		$( 'height' ).value = dimensions.height;
	}
	
	Event.observe( 
		window, 
		'load', 
		function() { 
			new Cropper.ImgWithPreview( 
				'testImage',
				{ 
					minWidth: 118, 
					minHeight: 118,
					ratioDim: { x: 1, y: 1 },
					displayOnInit: true, 
					onEndCrop: onEndCrop,
					previewWrap: 'previewArea'
				} 
			) 
		} 
	);		
</script>

<?php
}

/*
elseif(!$image2crop && $imagecropped) {

list($w, $h) = getimagesize($imagecropped);
?>
<img src="upload/<?php echo basename($imagecropped)?>" alt="" width="<?php echo $w?>" height="<?php echo $h?>" />
*/
?>
	</div>
</div>
<?php
pagefooter();
?>