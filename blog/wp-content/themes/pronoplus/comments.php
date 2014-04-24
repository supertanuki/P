<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Merci de ne pas lancer cette page directement.');

        if (!empty($post->post_password)) { // if there's a password
            if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
				?>
				<p class="nocomments">Cet article est protégé par un mot de passe. Entrez ce mot de passe pour voir les commentaires.</p>
				<?php
				return;
            }
        }

		/* This variable is for alternating comment background */
		$oddcomment = 'class="alt" ';
?>

<!-- You can start editing here. -->

<?php if ($comments) : ?>
	<div class="comments"><?php comments_number('Aucun commentaire', 'Un commentaire', '% commentaires' );?></div> 
	<ol class="commentlist">
	<?php foreach ($comments as $comment) : ?>
		<li <?php echo $oddcomment; ?>id="comment-<?php comment_ID() ?>">
			<cite>
			<?php
			if($comment->comment_author_email)
			{
				$sql = "SELECT * FROM pp_user WHERE email='".$comment->comment_author_email."'";
				$srcMembre = mysql_query($sql);
				if($joueur = mysql_fetch_assoc($srcMembre))
				{
					$joueur_avatar = $joueur["avatar_key"] ? '/avatars/'.$joueur["id_user"].'-'.$joueur["avatar_key"].'-30.'.$joueur["avatar_ext"] : '/template/default/_profil.png';
					echo '<img src="'.$joueur_avatar.'" height="30" width="30" border="0" alt="" align="absmiddle" />&nbsp;'.$joueur["login"];
				} else {
					comment_author_link();
				}
				
			} else {
				comment_author_link();
			}
			?></cite> a dit :
			<?php if ($comment->comment_approved == '0') : ?>
			<em>Votre commentaire est en attente de modération.</em>
			<?php endif; ?>
			<br />
			<small class="commentmetadata"><a href="#comment-<?php comment_ID() ?>" title=""><?php comment_date('j F Y') ?> à <?php comment_time() ?></a> <?php edit_comment_link('Editer','-- ',''); ?></small>
			<?php comment_text() ?>
		</li>
	  <?php 
		/* Changes every other comment to a different class */ 
		$oddcomment = ( empty( $oddcomment ) ) ? 'class="alt" ' : ''; 
	?>
	<?php endforeach; /* end for each comment */ ?>
	</ol>

 <?php else : // this is displayed if there are no comments so far ?>
  <?php if ('open' == $post->comment_status) : ?> 
		<!-- If comments are open, but there are no comments. -->
	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->
		<p class="nocomments">Les commentaires sont fermés. </p>
	  <?php endif; ?>
<?php endif; ?>

<?php if ('open' == $post->comment_status) : ?>
<div class="comments">Laisser un commentaire </div>
<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p>Vous devez être  <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>">connecté</a> pour publier un commentaire.</p>
<?php else : ?>
<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<?php
$CookieBon = false;
$comment_id_joueur = 0;
$strUser = $_COOKIE[user];
if($strUser)
{
	$footprint = "CONCAT(`id_user`, 'a', MD5(CONCAT(`login`, 'a', MD5(`pwd`))))";	
	$cond = $footprint . "='".mysql_real_escape_string($strUser)."'";		
	$SQL = "SELECT `id_user`, `login`, `avatar_key`, `avatar_ext`, `email`
			FROM `pp_user`
			WHERE ".$cond;
	$result = mysql_query($SQL);
	if($joueur = mysql_fetch_assoc($result))
	{
		$joueur_nom = $joueur["login"];
		$joueur_avatar = $joueur["avatar_key"] ? '/avatars/'.$joueur["id_user"].'-'.$joueur["avatar_key"].'-30.'.$joueur["avatar_ext"] : '/template/default/_profil.png';
		$joueur_email = $joueur["email"];		
		$CookieBon = true;
	}
}

if($CookieBon == true) { ?>

<p><?php echo '<img src="'.$joueur_avatar.'" height="30" width="30" border="0" alt="0" align="absmiddle" /> '.$joueur_nom; ?></p>

<input type="hidden" name="author" id="author" value="<?php echo htmlspecialchars($joueur_nom); ?>" />
<input type="hidden" name="email" id="email" value="<?php echo htmlspecialchars($joueur_email); ?>" />

<?php } else if ( $user_ID ) { ?>

<p>Connecté en tant que <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="Se déconnecter du site.">Se déconnecter  &raquo;</a></p>
 
<?php } else { ?>

<p><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" />
<label for="author"><small>Nom <?php if ($req) echo "(obligatoire)"; ?></small></label>
</p>
<p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" />
<label for="email"><small>Adresse e-mail (ne sera pas publié) <?php if ($req) echo "(obligatoire)"; ?></small></label>
</p>
<p><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" />
<label for="url"><small>Site Web</small></label>
</p>

<?php }  ?>

<p><textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea></p>
<p><input name="submit" type="submit" id="submit" tabindex="5" value="Shooter mon commentaire" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
</p>
<?php do_action('comment_form', $post->ID); ?>
</form>

<?php endif; // If registration required and not logged in ?>
<?php endif; // if you delete this the sky will fall on your head ?>
