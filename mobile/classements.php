<?php
/**
 * Project: PRONOPLUS
 * Description: Classements
 * Author: Richard HANNA <supertanuki@gmail.com>
 * Date: 2008-07-07
 * Version: 1.0
 */

require_once('../init.php');
require_once('functions-iphone.php');
require_once('../mainfunctions.php');
require_once('../contentfunctions.php');

$user = user_authentificate();

echo pp_iphone_header("Classements", $is_menu = false, $is_retour = true);

?>
    <ul class="pageitem">
        <li class="textbox">
            <?php echo classements_user($user, array('affichage_simple' => true)); ?>
        </li>
    </ul>
<?php

echo pp_iphone_footer();
?>