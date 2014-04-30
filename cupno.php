<?php
/**
* Project: PRONOPLUS
* Description: Coupe Prono+
* Author: Richard HANNA <supertanuki@gmail.com>
* Date: 2008-08-31
* Version: 1.0
*/

require_once('init.php');
require_once('mainfunctions.php');
require_once('contentfunctions.php');

$user = user_authentificate();
pageheader("Coupe | Prono+", array('meta_description' => 'Coupe des meilleurs pronotstiqueurs de foot'));

?>

<div id="content_fullscreen">
<?php
// affichage des onglets
echo getOnglets('coupe');
?>
	<div id="content">
		<h2 class="title_green">Coupe Prono+</h2>
		
        <p>Pas de coupe disponible pour le moment. Elle revient en septembre ;)</p>
	</div>
</div>

<?php
pagefooter();
?>