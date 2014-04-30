<?php
require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();


pageheader("Pronostiquer | Prono+");
?>


<div id="content_fullscreen">
<?php
// affichage des onglets
echo getOnglets();
?>
	<div id="content">
		<h2 class="title_grey">Pronostiquer</h2>
		<p id="message_ok" class="center">&nbsp;<br /><strong>Vos pronostics ont bien été enregistrés !</strong><br />&nbsp;</p>
      



<?php
	$content = CurrentMatches(false, array("liste_non_joues" => true));
	if($content != '')
	{
		echo "<h2 class=\"title_green\">Les autres grilles à pronostiquer :</h2><br />".$content;
		
	} else {
		echo "<p class=\"center\">Maintenant, venez donner votre avis sur le forum ! Voici les derniers sujets publiés :</p>";
		echo "<p>";
		ob_start();
		getLastPostFromForum();
		$content = ob_get_contents();
		ob_end_clean();
		echo $content;
		echo "</p>";
	}
?>


	</div>
</div>
<script type="text/javascript">
<!--
new Effect.Highlight('message_ok', {startcolor:'#ffff00', duration:1});
-->
</script>
<?php
pagefooter();
?>