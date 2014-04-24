<?php
/*
Plugin Name: Automatic SEO Links
Plugin URI: http://descargas.sinplanes.com/wordpress/automatic-seo-links
Description: Forget to put manually your links, just choose a word and a URL and this plugin will replace all matches in the posts of your blog. You can set the title of the link, target, rel and also you can know every moment how many times a word has been changed.
Author: Emilio
Version: 2.0.1
Author URI: http://www.sinplanes.com
*/


add_filter('the_content', 'automaticSeoLinksChange', 1);
add_action('admin_menu', 'automaticSeoLinksAddOpc'); 
register_activation_hook( __FILE__, 'automaticSeoLinksInstall' );
register_deactivation_hook (__FILE__, 'automaticSeoLinksUnInstall');


$my_table ="automaticSEOlinks";
$my_table_stats ="automaticSEOlinksStats";
$asl_version = "2.0";


/*************************************************
 FUNCIONES DE INSTALACIÓN 
*************************************************/

//Añadimos la opción al menú Opciones

function automaticSeoLinksAddOpc(){   
      if (function_exists('add_options_page')) {
         add_options_page('Automatic SEO Links', 'Automatic SEO Links', 8, basename(__FILE__), 'automaticSeoLinksMenu');
      }
   }

// Función para instalar el plugin, crea la tabla en UTF-8
   
function automaticSeoLinksInstall(){

	automaticSeoLinksInstallMainTable();
	automaticSeoLinksInstallStatsTable();

   }
   
//Función para desinstalar, por defecto deshabilitada, si se quiere eliminar la tabla habrá que hacerlo manualmente
   
function automaticSeoLinksUnInstall(){
   
	//automaticSeoLinksDeleteBD();   
}

//Función para eliminar la tabla del plugin

function automaticSeoLinksDeleteBD(){
	
	global $wpdb;	
	global $my_table;
	global $my_table_stats;
	 
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		
	$table_name= $wpdb->prefix.$my_table;
	$sql = "DROP TABLE $table_name;";
	
	$wpdb->query($sql);
	
	$table_name_stats= $wpdb->prefix.$my_table_stats;
	$sql = "DROP TABLE $table_name_stats;";
	
	$wpdb->query($sql);
}

//Instala la tabla principal del plugin, donde irán los links

function automaticSeoLinksInstallMainTable(){

	global $wpdb,$my_table;		 
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');		
	$table_name= $wpdb->prefix."automaticSEOlinks";	
	
	$sql = " CREATE TABLE $table_name(
		id mediumint( 9 ) NOT NULL AUTO_INCREMENT ,
		idGroup tinytext NULL ,
		asl_text tinytext NOT NULL ,
		asl_url tinytext NOT NULL ,
		asl_title tinytext NOT NULL ,
		asl_rel tinytext NOT NULL ,
		asl_type tinytext NOT NULL ,
		asl_visits tinytext NOT NULL ,
		asl_group tinytext NOT NULL ,
		asl_nopost tinytext NOT NULL ,
		PRIMARY KEY ( `id` )	
	) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
	
	$wpdb->query($sql);

}

function automaticSeoLinksInstallStatsTable(){

	global $wpdb,$my_table;		 
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');		
	$table_name= $wpdb->prefix."automaticSEOlinksStats";	
	
	$sql = " CREATE TABLE $table_name(
		idLink mediumint( 9 ) NOT NULL ,
		idPost mediumint( 9 ) NOT NULL ,	
		visits tinytext NOT NULL 
	) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
	
	$wpdb->query($sql);

}


/*************************************************
 FUNCIONES MIGRACIÓN
*************************************************/

//Función para instalar las nuevas tablas, hacer una transicción  entre versiones anteriores a la 1.5 y posteriores

function checkCompatibilityNewVersions(){
		
	if (automaticSeoLinksNeedMigrate())	{
		echo "<h3>Migrate to version 2</h3>";
		echo "[".date("H:m:s")."] - Let's start the migrate!<br/>";
		echo "[".date("H:m:s")."] - Modifying existing tables<br/>";
		automaticSeoLinksAlterMainTableMigrate();
		echo "[".date("H:m:s")."] - Creating new table for stats<br/>";
		automaticSeoLinksInstallStatsTable();
		echo "[".date("H:m:s")."] - Migrate is finish<br/>";
		echo "<br/><br/> - If you are able to read this, <b>everything is correct!</b><br/>";
	}
}

//Función que forma parte del proceso de migración a la versión 2.0, modifica la tabla principal

function automaticSeoLinksAlterMainTableMigrate(){

	global $wpdb;	 
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');		
	$table_name= $wpdb->prefix."automaticSEOlinks";	
	
	$sql = "RENAME TABLE ".$wpdb->prefix."automatiSEOlinks  TO ".$wpdb->prefix."automaticSEOlinks";
	$wpdb->query($sql);
	echo "[".date("H:m:s")."] - The main table has been rename!<br/>";
	$sql="ALTER TABLE `".$wpdb->prefix."automaticSEOlinks` 
	      ADD `asl_group` INT( 3 ) NOT NULL ,
		  ADD `asl_nopost` TINYTEXT NOT NULL ,
		  ADD `idGroup` INT( 3 ) NOT NULL ;";
	
	$wpdb->query($sql);
	echo "[".date("H:m:s")."] - New fields has been added to main table<br/>";
	
	$sql= "ALTER TABLE `".$wpdb->prefix."automaticSEOlinks` CHANGE `anchortext` `title` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL  ";
	$wpdb->query($sql);
	$sql= "ALTER table `".$wpdb->prefix."automaticSEOlinks` change text asl_text tinytext";
	$wpdb->query($sql);
	$sql= "ALTER table `".$wpdb->prefix."automaticSEOlinks` change url asl_url tinytext";
	$wpdb->query($sql);
	$sql= "ALTER table `".$wpdb->prefix."automaticSEOlinks` change title asl_title tinytext";
	$wpdb->query($sql);
	$sql= "ALTER table `".$wpdb->prefix."automaticSEOlinks` change rel asl_rel tinytext";
	$wpdb->query($sql);
	$sql= "ALTER table `".$wpdb->prefix."automaticSEOlinks` change type asl_type tinytext";
	$wpdb->query($sql);
	$sql= "ALTER table `".$wpdb->prefix."automaticSEOlinks` change visits asl_visits tinytext";
	$wpdb->query($sql);
	echo "[".date("H:m:s")."] - Old fields renamed<br/>";
	
}


function automaticSeoLinksNeedMigrate(){

	global $wpdb;
	$table_name= $wpdb->prefix . 'automatiSEOlinks';			
	($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != "") ? $status = true : $status = false;
	return $status;
}

/*************************************************
 FUNCIONES MENÚ
*************************************************/

// Función para gestionar las acciones de los diferentes menús
   
function automaticSeoLinksMenu(){  

	global $wpdb;
	global $asl_version;
	
	//Primero, comprobamos si la versión está actualizada, sino, mostramos aviso	
	$asl_new_version = automaticSeoLinksUpdate();
	if ($asl_new_version) echo '<div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"><b><br/>There is a new version, '.$asl_new_version.'!</b> visit <a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=changeLog">ChangeLog</a> for more details. <br/><br/></div>';

	isset($_GET['acc']) ? $_acc=$_GET['acc']:$_acc="showLinks";
	
	//Vamos a comprobar si va a hacer una migración como tiene que pasarse a la 2	
	$my_table_status = automaticSeoLinksNeedMigrate();
	if (($my_table_status) && ($_acc!="migrate")) {
		
		echo '<div class="wrap">
		<h2>Automatic SEO Links</h2>
		
		<h3>You need to migrate</h3>
		
		<p>You have a previous version of Automatic SEO Links installed ('.$asl_version.'), before using the version 2 you must migrate. Normally this is an
		automatic process but just in case something goes wrong (it should not) I give you the chance to backup your links.</p>
		<p>If everything goes right, you will not loose any data</p>
		
		<p><b>New Features</b><br/><br/>
		
		- HTML valid<br/>
		- Bugs fixed<br/>
		- More stats about Automatic SEO Links<br/>
		- More seo features<br/>
		- Chance to block the change in some post<br/>
		
		</p>
		
		<p><b>So, what can I do now?</b></p>
		
		<p><a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=migrate">Start migrating.</a></p>
		
		</div>';
		
		
		exit();
	}
	
	if($_POST['url']!=""){
		echo "<br>";
		if($_POST['id']!=""){
			if(automaticSeoLinksUpdateLink($_POST['id'],$_POST['url'],$_POST['text'],$_POST['alt'],$_POST['rel'],$_POST['type']))
				echo '<div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"><b><br/>Link correctly updated!</b><br/><br/></div>';
			$_acc="showLinks";
		}
		else{
			if(automaticSeoLinksNewLink($_POST['url'],$_POST['text'],$_POST['alt'],$_POST['rel'],$_POST['type']))
				echo '<div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"><b><br/>Link correctly added!</b><br/><br/></div>';
			else 
				echo '<div id="message" class="error fade" style="background-color: rgb(218, 79, 33);"><br/><b>ERROR! This word is in database!</b><br/><br/></div>';
			$_acc="addLink";
		}
	}else{
		if($_GET['acc']=="del") {
			automaticSeoLinksDeleteLink($_GET['id']); 
			echo '<br><div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"> <br/>Link correctly deleted!<br/><br/></div>';
			$_acc="showLinks";
		}
		else if($_GET['acc']=="delBD"){
			automaticSeoLinksDeleteBD();
			echo '<br><div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"> <br/>BD deleted! now you can deactivated your plugin<br/><br/></div>';
			$_acc="dataBase";
		}
		else if($_GET['acc']=="migrate"){
			$_acc="migrate";
		}
		else if($_GET['acc']=="stats"){
			$_acc="stats";
		}
		
	}  
	
	
	//Mostramos el menú
	echo '<div class="wrap">
			<h2>Automatic SEO Links</h2>';
			
	echo'<ul class="subsubsub">
			<li><a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks">Links</a> |</li>
			<!--<li><a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&opc=group">Groups</a> |</li>-->
			<li><a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=dataBase">Database</a> |</li>
			<!--<li><a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=changeLog">ChangeLog</a> |</li>-->
			<!--<li><a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=contribute">Contribute</a> |</li>-->
			<li><a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=help">Help / F.A.Q.</a> </li>
		</ul>
		<br/><br/>
		
		<script>
		function deleteLink(id){
			var opc = confirm("You are going to delete this link, are you sure?");
			if (opc==true) window.location.href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=del&id="+id;
		}
		</script>
	';  

	//Añadimos un nuevo link
	if($_acc=="addLink"){

	 if (automaticSeoLinksInfoDB()==false) {
			echo "<br/><b>Automatic SEO Links Table deleted</b>! now you can desactivate the plugin in plugins section";
		  }
	 else{
			
			if(isset($_GET['opc'])) $isGroup=true; else $isGroup = false;
			
			
			if($isGroup){
				echo '<h3>New Group</h3>';
				echo 'You can put all the words you want separeting them with commas.<br/>
				If a word is already in the links section it would not be added to the group.<br/><br/>';
			}
			else{

				echo '<h3>New Link</h3>';
			}
			
			echo'

				<fieldset>

				<form method="post" action ="">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<label for="default_post_edit_rows">'; if($isGroup) echo 'Words <small>(separate by comma ",")</small> '; else echo'  Word'; echo'</label>
								</th>
								<td>
									<input type="text" '; if($isGroup) echo 'size="65px"'; else echo'size="10px"';  echo' name="text" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="default_post_edit_rows"> URL</label>
								</th>
								<td>
									<input type="text" name="url" style="width:300px;" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="default_post_edit_rows"> Title</label>
								</th>
								<td>
									<input type="text" name="alt" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="default_post_edit_rows"> Target</label>
								</th>
								<td>
									<select name="type">
										<option value="0"></option>
										<option value="1">_self</option>
										<option value="2">_top</option>
										<option value="3">_blank</option>
										<option value="4">_parent</option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="default_post_edit_rows"> Rel</label>
								</th>
								<td>
									<select name="rel">
										<option value="0"></option>
										<option value="1">external</option>
										<option value="2">nofollow</option>
									</select>
								</td>
							</tr>
						</tbody>
					</table>


					
					<p class="submit"><input class="button-primary" type="submit" name="automaticseolinks" value="'; if($isGroup) echo 'Add Group'; else echo'Add Link';  echo'" /></p>
				</form>
				</fieldset>';
		}
	}
		
	//Editamos un link
	else if($_acc=="edit"){

	 if (automaticSeoLinksInfoDB()==false) {
			echo "<br/><b>Automatic SEO Links Table deleted</b>! now you can desactivate the plugin in plugins section";
		  }
		  else{
		  
		  $_id = $_GET['id'];
		  $_text = base64_decode($_GET['text']);
		  $_url = base64_decode($_GET['url']);
		  $_title = base64_decode($_GET['title']);
		  $_rel = $_GET['rel'];
		  $_type = $_GET['type'];

			echo '<h3>Edit Link</h3>

				<fieldset>

				<form method="post" action ="">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<label for="default_post_edit_rows"> Word</label>
								</th>
								<td>
									<input type="hidden" name="id" value="'.$_id.'" /><input type="text" value="'.$_text.'" name="text" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="default_post_edit_rows"> URL</label>
								</th>
								<td>
									<input type="text" name="url" value="'.$_url.'" style="width:300px;" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="default_post_edit_rows"> Title</label>
								</th>
								<td>
									<input type="text" value="'.$_title.'" name="alt" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="default_post_edit_rows"> Target</label>
								</th>
								<td>
									<select name="type">
										<option value="0" '; if($_type == 0) echo "selected='selected'"; echo'></option>
										<option value="1" '; if($_type == 1) echo "selected='selected'"; echo'>_self</option>
										<option value="2" '; if($_type == 2) echo "selected='selected'"; echo'>_top</option>
										<option value="3" '; if($_type == 3) echo "selected='selected'"; echo'>_blank</option>
										<option value="4" '; if($_type == 4) echo "selected='selected'"; echo'>_parent</option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="default_post_edit_rows"> Rel</label>
								</th>
								<td>
									<select name="rel">
										<option value="0" '; if($_rel == 0) echo "selected='selected'"; echo'></option>
										<option value="1" '; if($_rel == 1) echo "selected='selected'"; echo'>external</option>
										<option value="2" '; if($_rel == 2) echo "selected='selected'"; echo'>nofollow</option>
									</select>
								</td>
							</tr>
						</tbody>
					</table>


					
					<p class="submit"><input class="button-primary" type="submit" name="automaticseolinks" value="Update" /></p>
				</form>
				</fieldset>';
				}
		}
		
	//Cambios en las diferentes versiones
	else if($_acc=="changeLog"){
		
		echo "<h3>ChangeLog</h3>";
		 automaticSeoLinksChangeLog();
		
	}
		
	//Mostramos los links
	else if($_acc=="showLinks"){
		  
		   if (automaticSeoLinksInfoDB()==false) {
			echo "<br/><b>Automatic SEO Links Table deleted</b>! now you can desactivate the plugin in plugins section";
		  }
		 else{
		 
		 if(isset($_GET['opc'])) $isGroup=true; else $isGroup = false;
		  
		 if($isGroup) {
			echo'<h3>Groups </h3>';
			echo' <a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=addLink&opc=group">Add Group</a><br/><br/>';
		 }else {
			echo'<h3>Links </h3>';
			echo ' <a class="button-primary" href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=addLink">Add Link</a><br/><br/>';
			}
		 
		 
		 echo'
		 <table class="widefat">
			<thead>
				<tr>
					<th style="display:none" scope="col">Index</th>
					<th scope="col">Text (<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&orderBy=asl_text&order=asc">+</a>|<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&orderBy=asl_text&order=desc">-</a>) </th>
					<th scope="col">URL (<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&orderBy=asl_url&order=asc">+</a>|<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&orderBy=asl_url&order=desc">-</a>)</th>
					<th scope="col">Title (<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&orderBy=asl_title&order=asc">+</a>|<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&orderBy=asl_title&order=desc">-</a>)</th>
					<th scope="col">Rel (<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&orderBy=asl_rel&order=asc">+</a>|<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&orderBy=asl_rel&order=desc">-</a>)</th>
					<th scope="col">Target (<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&orderBy=asl_type&order=asc">+</a>|<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=showLinks&orderBy=asl_type&order=desc">-</a>)</th>
					<th scope="col">Changes</th>
					<th scope="col">Delete</th>
					<th scope="col">Edit</th>
				</tr>
			</thead>
			<tbody id="the-comment-list" class="list:comment">
				<tr id="comment-1" class="">
					';
					if($_GET['orderBy']=="") $_GET['orderBy'] = "id";
					if($_GET['order']=="") $_GET['order'] = "desc";
					automaticSeoLinksGetLinks($_GET['orderBy'],$_GET['order'],$isGroup);
					echo'
				</tr>
			</tbody>
			<tbody id="the-extra-comment-list" class="list:comment" style="display: none;"> </tbody>
			</table>

		</div>';
		}
	}
	//Mostramos información sobre la tabla y damos la posibilidad de borrarla
	else if($_acc=="dataBase"){
		  
		  if (automaticSeoLinksInfoDB()==false) {
			echo "<br/><b>Automatic SEO Links Table deleted</b>! now you can desactivate the plugin in plugins section";
		  }
		  else{
			  echo '<h3>Database</h3>For preventing you to loose your links when desactivate the plugin, Automatic SEO Links doesn\'t delete the table, 
			  if you want to completely remove this plugin from your blog, just 
			  <b><a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=delBD">click here</a></b> and then desactivate it in plugins section.<br><br>';
			  automaticSeoLinksDataBaseInfo();
		  }
	   }
	//Por si alguien quiere colaborar, hasta ahora no ha dado muchos frutos
	else if($_acc=="contribute"){
		  
		  if (automaticSeoLinksInfoDB()==false) {
			echo "<br/><b>Automatic SEO Links Table deleted</b>! now you can desactivate the plugin in plugins section";
		  }
		  else{
			  echo '<h3>Contribute</h3>
			  I have recieved lots of emails with suggestions, improvements, mistakes and also thanking my job, and I appreciate your contributions but I have to admit that sometimes it took me a lot of time to reply, so that is one of the reasons I have decided to make a forum for Automatic SEO Links.
				<br><br>
						  A place where all the people could know what others think and the most important, as I have no so much time
				to add improvements to the plugin, I would like people help in the developing, 
				so if you want a new feature in this plugin, just do it, and if it is useful and people like it, I would add it to the plugin.
						  <br><br>
						 This plugin is not just for me, is for all of us, if we all work together on this I think we could make a good job.

				You have the source code and <a target="blank" href="http://foro.aesinformatica.com/automatic-seo-links/">the place</a> to share your improvements, the only limit is your imagination.
				<br><br>
				Of course, everyone who collaborate will appear in the plugin as an author.<br><br>
			  
			  <b><a target="blank" href="http://foro.aesinformatica.com/automatic-seo-links/">Automatic SEO Links Forum</a> </b>
			  
			  <h3>Special thanks</h3>
				I want to give thanks to Charles McRobert, Jean-Michel MEYER, Juan Mellido, Tom Gubber, Moolanomy, justaguy and all the people who wrote me thanking my job.
			  ';
		  }
	   }
	//Ayuda F.A.Q.   
	else if($_acc=="help"){
		  
		  if (automaticSeoLinksInfoDB()==false) {
			echo "<br/><b>Automatic SEO Links Table deleted</b>! now you can desactivate the plugin in plugins section";
		  }
		  else{
			  echo '<h3>Help / F.A.Q.</h3>
			  
			  <b>How many words change this plugin?</b><br>
			  It changes one word per post, if you have 1000 post with 5 words each post it would only be replaced one word per post, so 1000 words.
			  
			  <br><br><b>Does it change the post in database?</b><br>
			  No, just change it "on fly" so nothing in database is changed.
			  
			  <br><br><b>It never fails?</b><br>
			  Oh yes, it fails, this plugin has to analyze the code before changing with regular expressions. I am not able to test all
			  the posibilities people can put into their post, so, I have just included more commons regular expressions, if you detect a mistake,
			  please tell us <a target="blank" href="http://foro.aesinformatica.com/automatic-seo-links/">in our forum</a>.
			  
			  <br><br><b>I have found a mistake, where I can go?</b>
			  <br>
			  There is <a target="blank" href="http://foro.aesinformatica.com/automatic-seo-links/">a forum</a> where you can let us know your mistake,
			  if you also share the solution, better :)
			  
			  <br><br><b>I want a new feature in the plugin, what can I do? </b><br>
			  Well, some of you has wrote me asking me for include, as an example, that the plugin replaced all the matches of a word instead of one, 
			  I\'m not going to do that because I hate post where all the words has links, but if you want, you can find more people with the same
			  problem in the forum and maybe you can do it together. Later, if people like, it could be integrated in the plugin.
			  
			  <br><br><b>Is this an Open Source project?</b><br>
			  Yes, something like that, I don\'t care people touch my code even I prefer because I know
			  that when more than one person is working on a project the result is better.
			  
			  

			  ';
		  }
	   }
	    //Migración   
		else if($_acc=="migrate"){
			checkCompatibilityNewVersions();
		}
		//Stats   
		else if($_acc=="stats"){
		
			if(isset($_GET['opc'])) {
				if($_GET['opc']=="block")
					automaticSeoLinksStatsBlockPost($_GET['id'],$_GET['idPost']);
				else if ($_GET['opc']=="unBlock")
					automaticSeoLinksStatsUnBlockPost($_GET['id'],$_GET['idPost']);
			}
			
		
			echo'
			<h3>Stats for '.base64_decode($_GET['text']).'</h3>
		 <table class="widefat">
			<thead>
				<tr>
					<th style="display:none" scope="col">Index</th>
					<th scope="col">ID Post (<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=stats&orderBy=idPost&order=asc&id='.$_GET[id].'">+</a>|<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=stats&orderBy=idPost&order=desc&id='.$_GET[id].'">-</a>) </th>
					<th scope="col">Title (<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=stats&orderBy=post_title&order=asc&id='.$_GET[id].'">+</a>|<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=stats&orderBy=post_title&order=desc&id='.$_GET[id].'">-</a>)</th>
					<th scope="col">Changes (<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=stats&orderBy=visits&order=asc&id='.$_GET[id].'">+</a>|<a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=stats&orderBy=visits&order=desc&id='.$_GET[id].'">-</a>) </th>
					<th scope="col">Block</th>
					<th scope="col">Go</th>
				</tr>
			</thead>
			<tbody id="the-comment-list" class="list:comment">
				<tr id="comment-1" class="">
					';
					if($_GET['orderBy']=="") $_GET['orderBy'] = "id";
					if($_GET['order']=="") $_GET['order'] = "desc";
					automaticSeoLinksGetStats($_GET['orderBy'],$_GET['order'],$_GET['id']);
					echo'
				</tr>
			</tbody>
			<tbody id="the-extra-comment-list" class="list:comment" style="display: none;"> </tbody>
			</table>';
		}
   
}
   
/*************************************************
 FUNCIONES MOSTRAR LINK
*************************************************/
   
// Función que convierte los valores de la base de datos para target  en HTML

function automaticSeoLinksGetTarget($target){
		switch($target){
			case 0: return "-"; break;
			case 1: return "_self"; break;
			case 2: return "_top"; break;
			case 3: return "_blank"; break;
			case 4: return "_parent"; break;
		}
}

// Función que convierte los valores de la base de datos para rel  en HTML
   
function automaticSeoLinksGetRel($rel){
		switch($rel){
			case 0: return "-"; break;
			case 1: return "external"; break;
			case 2: return "nofollow"; break;
		}
}
   
//Mostramos los enlaces en función del orden y sentido que nos hayan pedido

function automaticSeoLinksGetLinks($orderBy="id",$order="desc",$isGroup){
   
		global $wpdb;
		global $my_table;
		
		
		echo '<tbody id="the-comment-list" class="list:comment">
			';
		
		$table_name= $wpdb->prefix.$my_table;
		
				//Construimos la consulta en función de si es grupo o link
				$query = "select * from $table_name";				
				if($isGroup) $query = $query." where idGroup != NULL"; else $query = $query." where `asl_group` = '0' and `idGroup` = 0";				
				$query = $query." order by ".$orderBy." ".$order;
								
				$links = $wpdb->get_results($query);

				foreach($links as $link){
				
					$_url = base64_encode($link->asl_url);
					$_text = base64_encode($link->asl_text);
					$_title = base64_encode($link->asl_title);
				
					echo '<tr id="comment-1" class="">';
					echo '<td style="display:none">'; echo $link->id; echo'</td>';
					echo '<td>'; echo $link->asl_text; echo'</td>';
					echo '<td>'; echo $link->asl_url; echo'</td>';
					echo '<td>'; echo $link->asl_title; echo'</td>';
					echo '<td>'; echo automaticSeoLinksGetRel($link->asl_rel); echo'</td>';
					echo '<td>'; echo automaticSeoLinksGetTarget($link->asl_type); echo'</td>';
					if($isGroup) { echo '<td>Not available</td>'; }
					else { echo '<td><a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=stats&id='.$link->id.'&text='.$_text.'">'; echo $link->asl_visits; echo'</a></td>'; }
					echo '<td><a href="javascript:deleteLink('.$link->id.');">Delete</a></td>';	
					echo '<td><a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=edit&id='.$link->id.'&text='.$_text.'&url='.$_url.'&title='.$_title.'&rel='.$link->asl_rel.'&type='.$link->asl_type.'">Edit</a></td>';
					echo '</tr>';
				}
				
		echo '</tbody>';
}

function automaticSeoLinksStatsUnBlockPost($id,$idPost){

	global $wpdb;
	global $my_table;
	$arr_block_post;
		
	$table_name= $wpdb->prefix.$my_table;
	
	$query = "select `asl_nopost` from $table_name where id = $id";
	$results = $wpdb->get_var($query);

	$posts = explode(",",$results);
	
	foreach($posts as $post){
		if ($post!=$idPost) $arr_block_post[] = $post;
	}
	
	$_nopost = implode(",",$arr_block_post);
	
	$query = "Update $table_name set `asl_nopost` = '$_nopost' where id = $id";
	
	$wpdb->query($query);

}

function automaticSeoLinksStatsBlockPost($id,$idPost){

	
	global $wpdb;
	global $my_table;
		
	$table_name= $wpdb->prefix.$my_table;
	
	$query = "Update $table_name set `asl_nopost` = concat(`asl_nopost`,',$idPost') where id = $id";
	
	$wpdb->query($query);

}

function automaticSeoLinksStatsIsBlock($id,$idPost){

	global $wpdb;
	global $my_table;
		
	$isBlock = false;	
		
	$table_name= $wpdb->prefix.$my_table;
	
	$query = "select `asl_nopost` from $table_name where id = $id";
	$results = $wpdb->get_var($query);

	$results2 = explode(",",$results);
	
	if((isset($results2)))
		$isBlock = (in_array($idPost,$results2))? true : false;
	
	return $isBlock;

}


function automaticSeoLinksGetStats($orderBy="s.idPost",$order="desc",$id){
   
		global $wpdb;
		global $my_table_stats;
		
		echo '<tbody id="the-comment-list" class="list:comment">
			';
		
		$table_name= $wpdb->prefix.$my_table_stats;
		$table_name_wordpress = $wpdb->prefix."posts";
		
				//Construimos la consulta en función de si es grupo o link
				if($orderBy == "id") $orderBy = "s.idPost";
				$query = "select s.idPost,s.visits, w.post_title, w.guid from $table_name s, $table_name_wordpress w where idLink = $id and s.idPost = w.ID";								
				$query = $query." order by ".$orderBy." ".$order;
			
				$links = $wpdb->get_results($query);

				foreach($links as $link){
					echo '<tr id="comment-1" class="">';
					echo '<td>'; echo $link->idPost; echo'</td>';
					echo '<td>'; echo $link->post_title; echo'</td>';
					echo '<td>'; echo $link->visits; echo'</td>';
					if(automaticSeoLinksStatsIsBlock($_GET[id],$link->idPost)) 
						echo '<td> <a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=stats&opc=unBlock&id='.$_GET[id].'&idPost='.$link->idPost.'"><font color="red">Unblock</color></a> </td>';
					else
						echo '<td> <a href="'.$PHP_SELF.'?page=automatic-seo-links.php&acc=stats&opc=block&id='.$_GET[id].'&idPost='.$link->idPost.'">Block</a> </td>';
					echo '<td> <a target="_blank" href="'.$link->guid.'">Go</a> </td>';
					echo '</tr>';
				}
				
		echo '</tbody>';
}

//Damos de alta un nuevo link
	
function automaticSeoLinksNewLink($url,$text,$title,$rel,$type,$group="")
	{
		global $wpdb;
		global $my_table;
		
		$table_name= $wpdb->prefix . $my_table;

				$queryprev = "select `asl_url` from $table_name where `asl_text` = '$text'";
				$result = $wpdb->get_results($queryprev);

				if(count($result)>0) return false;
				
				//Comprobamos si vamos a dar de alta un link o un grupo				
				$texts = explode(",",$text);
				
				if(count($texts)>1) {
				
					//primero damos de alta el grupo					
						$queryprev2 = "Select max(idGroup) from $table_name";
						$_idGroup = $wpdb->get_var($queryprev2);
						if ($_idGroup==null) $_idGroup = 1; else ++$_idGroup;
						
					
						$query = "INSERT INTO $table_name ( `asl_url`, `idGroup`, `asl_title`,`asl_rel`,`asl_type`,`asl_visits`, `asl_text` ) VALUES ";
						$query .= " (
							'".mysql_real_escape_string($url)."',
							'".$_idGroup."',
							'".mysql_real_escape_string($title)."',
							'".mysql_real_escape_string($rel)."',
							'".mysql_real_escape_string($type)."',
							'0',
							'".mysql_real_escape_string($text)."'
						),";

						$query = substr($query, 0, strlen($query)-1);
						$wpdb->query($query);
				
					//Ahora damos de alta el resto de palabras relacionadas con el grupo
						for ($i=0;$i<count($texts);$i++){
					
						$query = "INSERT INTO $table_name ( `asl_url`, `asl_text`, `asl_title`,`asl_rel`,`asl_type`,`asl_visits`,`asl_group` ) VALUES ";
						$query .= " (
							'".mysql_real_escape_string($url)."',
							'".mysql_real_escape_string($texts[$i])."',
							'".mysql_real_escape_string($title)."',
							'".mysql_real_escape_string($rel)."',
							'".mysql_real_escape_string($type)."',
							'0',
							'$_idGroup'
						),";

						$query = substr($query, 0, strlen($query)-1);
						$wpdb->query($query);
						
					}
				
				}
				else{
					if(strlen($group)>0){ 
					
						$query = "INSERT INTO $table_name ( `asl_url`, `asl_text`, `asl_title`,`asl_rel`,`asl_type`,`visits`,`asl_group` ) VALUES ";
						$query .= " (
							'".mysql_real_escape_string($url)."',
							'".mysql_real_escape_string($text)."',
							'".mysql_real_escape_string($title)."',
							'".mysql_real_escape_string($rel)."',
							'".mysql_real_escape_string($type)."',
							'0',
							'".mysql_real_escape_string($group)."'
						),";
					
					}
					else{
				
						$query = "INSERt INTO $table_name ( `asl_url`, `asl_text`, `asl_title`,`asl_rel`,`asl_type`,`asl_visits`,`idGroup` ) VALUES ";
						$query .= " (
							'".mysql_real_escape_string($url)."',
							'".mysql_real_escape_string($text)."',
							'".mysql_real_escape_string($title)."',
							'".mysql_real_escape_string($rel)."',
							'".mysql_real_escape_string($type)."',
							'0',
							'0'
						),";
					}
					$query = substr($query, 0, strlen($query)-1);
					$wpdb->query($query);
				}
				return true;
}
	
//Actualizamos un link

function automaticSeoLinksUpdateLink($id,$url,$text,$title,$rel,$type)
	{
		global $wpdb;
		global $my_table;
		
		$table_name= $wpdb->prefix . $my_table;
		
		$sql = "select idGroup,asl_text FROM $table_name where id = $id and idGroup !=0;";
		$result = $wpdb->get_row($sql);

		if(count($result)>0) {		
	
			//TODO: Comprobar si los enlaces que ha puesto son los que había, en caso de que no, eliminar los que no estén o añadir los nuevos
			$_idGroup =  $result->idGroup;
			
			$sql = "select id FROM $table_name where `asl_group` = $_idGroup;";
			$result2 = $wpdb->get_results($sql);
			
			$texts = explode(",",$text);
						
						//TODO: Comprobar que aunque coincidan son los mismos o no
						/*echo count($result2);
						echo "<br><br>";
						echo count($texts);*/
						
			if ( (count($result2))==(count($texts)) ){ 
		
				$result3 = array_diff_final(explode(",",$result->asl_text),explode(",",$text));
				
				if(count($result3)==0){
				
					$query = "UPDATE $table_name set `asl_url` = '".mysql_real_escape_string($url)."',
						`asl_title` = '".mysql_real_escape_string($title)."',
						`asl_rel` = '".mysql_real_escape_string($rel)."',
						`asl_type` = '".mysql_real_escape_string($type)."' where idGroup ='$_idGroup' or `asl_group` = $_idGroup; ";
				}
				else {
				
					$query = "UPDATE $table_name set `asl_url` = '".mysql_real_escape_string($url)."',
						`asl_title` = '".mysql_real_escape_string($title)."',
						`asl_rel` = '".mysql_real_escape_string($rel)."',
						`asl_type` = '".mysql_real_escape_string($type)."' where idGroup ='$_idGroup' or `asl_group` = $_idGroup; ";
					$wpdb->query($query);
					
					$query = "UPDATE $table_name set `asl_url` = '".mysql_real_escape_string($url)."',
						`text` = '".mysql_real_escape_string($text)."' where idGroup ='$_idGroup'";
					$wpdb->query($query);
					
					automaticSeoLinksUpdateLinkCheckText($result->text,$text,$url,$title,$rel,$type,$_idGroup);	
				}
			}
			else{
					//Hay alguno de más o de menos
					
					$query = "UPDATE $table_name set `asl_url` = '".mysql_real_escape_string($url)."',
						`asl_title` = '".mysql_real_escape_string($title)."',
						`asl_rel` = '".mysql_real_escape_string($rel)."',
						`asl_type` = '".mysql_real_escape_string($type)."' where idGroup ='$_idGroup' or `asl_group` = $_idGroup; ";
					$wpdb->query($query);
					
					$query = "UPDATE $table_name set `asl_url` = '".mysql_real_escape_string($url)."',
						`asl_text` = '".mysql_real_escape_string($text)."' where idGroup ='$_idGroup'";
					$wpdb->query($query);
									
					
					automaticSeoLinksUpdateLinkCheckText($result->text,$text,$url,$title,$rel,$type,$_idGroup);			
			}
		
		}
		else{
				
				$query = "UPDATE $table_name set `asl_url` = '".mysql_real_escape_string($url)."',
         				`asl_text` = '".mysql_real_escape_string($text)."' ,
						`asl_title` = '".mysql_real_escape_string($title)."',
						`asl_rel` = '".mysql_real_escape_string($rel)."',
						`asl_type` = '".mysql_real_escape_string($type)."' where id ='".mysql_real_escape_string($id)."' ";
	
		}
		//echo $query;
		$wpdb->query($query);

		//$_GET['opc'] = "group";
		return true;
}

//Función para comparar arrays, nos devuelve la diferencia

function array_diff_final($arr1,$arr2){

	$result = array();

	foreach ($arr1 as $word1){
		if (!(in_array($word1,$arr2))) $result[]=$word1;
	}
	
	foreach ($arr2 as $word2){
		if (!(in_array($word2,$arr1))) $result[]=$word2;
	}
	
	return $result;

}

//Comprobamos en grupos que las palabras que quiere cambiar son las mismas

function automaticSeoLinksUpdateLinkCheckText($first,$second,$url,$title,$rel,$type,$_idGroup){

	global $wpdb;
	global $my_table;
		
	$table_name= $wpdb->prefix . $my_table;
	
	$_first = explode(",",$first);
	$_second = explode(",",$second);
		
	//$result = (count($_first)>count($_sencod))? array_diff($_first,$_second) : array_diff($_second,$_first) ;
	
	$result = array_diff_final($_first,$_second);
	
	//$result = array_diff($_second,$_first);

	/*print_r($_first);
	echo "<br>";
	print_r($_second);
	echo "<br>";
	print_r($result);
	echo "<br>";*/
		
	foreach ($result as $word){
		if (in_array($word,$_first)){
			//delete
			//echo "<br>Menos ".$word;
			
			$query = "select id from $table_name where `text` = '$word'";
			$_id = $wpdb->get_var($query);
			automaticSeoLinksDeleteLink($_id);
		}
		else {
			//add
			//echo "<br>Mas ".$word;
			automaticSeoLinksNewLink($url,$word,$title,$rel,$type,$_idGroup);
		}
	}

}
	
//Función para eliminar un link
	
function automaticSeoLinksDeleteLink($id){
	 global $wpdb;	 
	 global $my_table;
	 
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		
	$table_name= $wpdb->prefix . $my_table;
	
	//Compruebo antes si tiene idGroup, de ser así, eliminaremos todos
	$sql = "select idGroup FROM $table_name where id = $id;";
	$result = $wpdb->get_var($sql);
	
	
	if($result!=0) {
		$sql = "DELETE FROM $table_name where idGroup = $result[0] or `als_group` = $result[0];";
		$_GET['opc'] = "group";
	}
	else{
		$sql = "DELETE FROM $table_name where id = $id;";
	}
	//echo $sql;
	
	$wpdb->query($sql);

}

//Si un link ha sido sustituido, aumentamos su contador para las estadísticas

function automaticSeoLinksShowLink($id)
	{
		global $wpdb;
		global $my_table;
		
		$table_name= $wpdb->prefix.$my_table;
				$query = "update $table_name set `asl_visits` = `asl_visits`+1 where id= $id ";	
				$query = substr($query, 0, strlen($query)-1);
				$wpdb->query($query);
}

//Si un link ha sido sustituido, aumentamos su contador para las estadísticas

function automaticSeoLinksChangeLinkStats($idLink, $idPost)
	{
		global $wpdb;
		global $my_table_stats;
		
		$table_name= $wpdb->prefix.$my_table_stats;
				
		//Primero comprobamos si ya existe

		$query = "select idLink from $table_name where `idLink` = $idLink and `idPost` = $idPost";
		$result = $wpdb->get_var($query);
		
		if(!(empty($result))) $query = "update $table_name set `visits` = `visits`+1 where `idLink` = $idLink and `idPost` = $idPost";	
		else $query = "insert into $table_name (`idLink`,`idPost`,`visits`) values ($idLink,$idPost,1)";	
		
		$wpdb->query($query);
}

// Función que reemplaza la palabra por el link 	
//This function is based on the Text-link-ads function, insertInLinkAd

function automaticSeoLinksChange($content)
    {
	
	$text = $content;		
		global $wpdb;
		global $my_table;
		global $notAllowToChange;
		global $post;
		
		
		/*
		echo the_permalink();
		echo "<br>";
		echo the_ID();*/
		
		$table_name= $wpdb->prefix.$my_table;

		$query = "select * from $table_name where idGroup=0";
		$links = $wpdb->get_results($query);		

		foreach($links as $link){
		
			if(!(automaticSeoLinksStatsIsBlock($link->id,$post->ID))){

				$find = $link->asl_text;

                $specialChars = array('/', '*', '+', '?', '^', '$', '[', ']', '(', ')');
                $specialCharsEsc = array('\/', '\*', '\+', '\?', '\^', '\$', '\[', '\]', '\(', '\)');
				
                
                $specialMassage='(\')?(s)?(-)?';                
                $escapedLinkText = str_replace($specialChars, $specialCharsEsc, $find);
                if (strpos($escapedLinkText,' ')!==false){
                    $LinkTexts=explode(' ',$escapedLinkText);
                    $escapedLinkText='';
                    foreach ($LinkTexts as $L){
                        if ($second){
                            $escapedLinkText.=' ';
                        }
                        if (substr($L,-1)=='s'){
                            $L=substr($L,0,-1);
                        }                            
                        $second=true;
                        $escapedLinkText.=$L.$specialMassage;
                        if ($L!=end($LinkTexts)){
                            $escapedLinkText.='(\s)?';
                        }
                    }
                } else {
                    if (substr($escapedLinkText,-1)=='s'){
                        $escapedLinkText=substr($escapedLinkText,0,-1);
                    }
                    $escapedLinkText.=$specialMassage;
                    
                }
                $find = '/\b'.$escapedLinkText.'\b/i';
                $trueMatch = false;

                $matches = array();
                preg_match_all($find, $content, $matches, PREG_OFFSET_CAPTURE);
                $matchData = $matches[0];

                if(count($matchData) > 0){

                    $invalidMatches = array(
                        '/<h[1-6][^>]*>[^<]*'.$escapedLinkText.'[^<]*<\/h[1-6]>/i',
                        '/<a[^>]+>[^<]*'.$escapedLinkText.'[^<]*<\/a>/i',
                        '/href=("|\')[^"\']+'.$escapedLinkText.'[^"\']+("|\')/i',
                        '/src=("|\')[^"\']*'.$escapedLinkText.'[^"\']*("|\')/i',
                        '/alt=("|\')[^"\']*'.$escapedLinkText.'[^"\']*("|\')/i',
                        '/title=("|\')[^"\']*'.$escapedLinkText.'[^"\']*("|\')/i',
                        '/content=("|\')[^"\']*'.$escapedLinkText.'[^"\']*("|\')/i',
                        '/<script[^>]*>[^<]*'.$escapedLinkText.'[^<]*<\/script>/i'
                    );

                    foreach($invalidMatches as $invalidMatch){
                        flagInvalidMatch($matchData, $invalidMatch, $content);
                    }

                    foreach($matchData as $index => $match){
                        if($match[2] != true){
                            $trueMatch = $match;
                            break;
                        }
                    }
                }else{
                    $trueMatch = $matchData[0];
                }

                if(is_array($trueMatch)){
                    $link->asl_type = automaticSeoLinksGetTarget($link->asl_type);
					$link->asl_rel = automaticSeoLinksGetRel($link->asl_rel);
					
					$replacement = '<a href="'.$link->asl_url.'"';
					if ($link->asl_type!="-") $replacement = $replacement.'target="'.$link->asl_type.'" ';
					if ($link->asl_rel!="-") $replacement = $replacement.'rel="'.$link->asl_rel.'" ';
					$replacement =	$replacement.'title="'.$link->asl_title.'" >'.$trueMatch[0].'</a>';
					automaticSeoLinksShowLink($link->id);
					automaticSeoLinksChangeLinkStats($link->id,$post->ID);
					//$content = substr($content, 0, $isFind[1]) . $replacement . substr($content, $isFind[1] + strlen($isFind[0]));
					$content = substr($content, 0, $trueMatch[1]) . $replacement . substr($content, $trueMatch[1] + strlen($trueMatch[0]));
                }
			}
            
		}


        return $content;
    }
	
	function flagInvalidMatch(&$matchData, $pattern, $content)
    {
        $results = array();
        preg_match_all($pattern, $content, $results, PREG_OFFSET_CAPTURE);
        $matches = $results[0];

        if(count($matches) == 0) return;

        foreach($matches as $match){
            $offsetMin = $match[1];
            $offsetMax = $match[1] + strlen($match[0]);
            foreach($matchData as $index => $data){
                if($data[1] >= $offsetMin && $data[1] <= $offsetMax){
                    $matchData[$index][2] = true;
                }
            }
        }

    }
	
/*************************************************
 FUNCIONES BASE DE DATOS
*************************************************/
   
// Obtenemos información de la base de datos sobre cuantos links han sido reemplazados y cuantas veces (Estadísticas)

function automaticSeoLinksDataBaseInfo(){
   
		global $wpdb;
		global $my_table;
		
		
		$table_name= $wpdb->prefix.$my_table;
		
				$query = "select count(id) as links ,SUM(asl_visits) as visits from $table_name ";
				$links = $wpdb->get_results($query);
				
				echo 'You have <b>'.$links[0]->links.' links</b> which had been replaced <b>'.$links[0]->visits.' times</b>.';
}
	
//Nos indica si el plugin está instalado o no, más bien si ya existe en la base de datos la tabla principal o si aún no	
	
function automaticSeoLinksInfoDB(){
	global $wpdb;
	global $my_table;
		
		$table_name= $wpdb->prefix . $my_table;
				
		($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != "") ? $back= true : $back= false;
		return $back;

}

//Nos indica si el plugin está instalado o no, más bien si ya existe la base de datos la tabla para las esta´disticas de las palabras o si aún no	
	
function automaticSeoLinksStatsDB(){
	global $wpdb;
	global $my_table;
		
		$table_name= $wpdb->prefix . $my_table;
				
		($wpdb->get_var("SHOW TABLES LIKE '$table_name_stats'") != "") ? $back= true : $back= false;
		return $back;

}
	
/*function automaticSeoLinksChange2($content='')
	{
		$text = $content;		
		global $wpdb;
		global $my_table;
		global $notAllowToChange;
		
		$table_name= $wpdb->prefix.$my_table;

		$query = "select * from $table_name";
		$links = $wpdb->get_results($query);		

		foreach($links as $link){			
				
			$find = '/'.$link->asl_text.'/i';
				$isFind = false;
				
				$matches = array();
				preg_match_all($find, $content, $matches, PREG_OFFSET_CAPTURE);
				$matchData = $matches[0];
				

					$noChanges = array(
						'/<h[1-6][^>]*>[^<]*'.$link->asl_text.'[^<]*<\/h[1-6]>/i',
						'/src=("|\')[^"\']*'.$link->asl_text.'[^"\']*("|\')/i',
						'/alt=("|\')[^"\']*'.$link->asl_text.'[^"\']*("|\')/i',
						'/title=("|\')[^"\']*'.$link->asl_text.'[^"\']*("|\')/i',
						'/content=("|\')[^"\']*'.$link->asl_text.'[^"\']*("|\')/i',
						'/<script[^>]*>[^<]*'.$link->asl_text.'[^<]*<\/script>/i',
						'/<embed[^>]+>[^<]*'.$link->asl_text.'[^<]*<\/embed>/i',
						'/wmode=("|\')[^"\']*'.$link->asl_text.'[^"\']*("|\')/i',
						'/<a[^>]+>[^<]*'.$link->asl_text.'[^<]*<\/a>/i',
						'/href=("|\')[^"\']+'.$link->asl_text.'(.*)[^"\']+("|\')/i'
					);

					foreach($noChanges as $noChange){
						$results = array();
						//if($link->asl_text=="wordpress") echo $noChange."<br>";
						preg_match_all($noChange, $content, $results, PREG_OFFSET_CAPTURE);
						$matches = $results[0];
						//print_r($results);
						
						if($link->asl_text=="wordpress") {
						 //echo "El contenido es: ".strlen($content);
						 //print_r($matches);
						 }

						if(!count($matches) == 0) {
							foreach($matches as $match){
								$start = $match[1];
								$end = $match[1] + strlen($match[0]);
								foreach($matchData as $index => $data){
									if($data[1] >= $start && $data[1] <= $end){
										$matchData[$index][2] = true;
									}
								}
							}
						}		
					}
					
					//print_r($matchData);

					foreach($matchData as $index => $match){
						if($match[2] != true) {
							$isFind = $match;
							break;
						}
					}
					

				if(is_array($isFind)){
				// 	print_r($isFind);
					$link->asl_type = automaticSeoLinksGetTarget($link->asl_type);
					$link->asl_rel = automaticSeoLinksGetRel($link->asl_rel);
					
					$replacement = '<a href="'.$link->asl_url.'"';
					if ($link->asl_type!="-") $replacement = $replacement.'target="'.$link->asl_type.'" ';
					if ($link->asl_rel!="-") $replacement = $replacement.'rel="'.$link->asl_rel.'" ';
					$replacement =	$replacement.'title="'.$link->anchortext.'" >'.$isFind[0].'</a>';
					automaticSeoLinksShowLink($link->id);
					//$content = substr($content, 0, $isFind[1]) . $replacement . substr($content, $isFind[1] + strlen($isFind[0]));
					$content = substr($content, 0, $trueMatch[1]) . $replacement . substr($content, $trueMatch[1] + strlen($trueMatch[0]));
				}


			}


		return $content;
	}
*/

/*************************************************
 FUNCIONES VERSION
*************************************************/	

//Nos dice el listado de las últimas 5 versiones
	
function automaticSeoLinksChangeLog(){
	
	 include_once(ABSPATH . WPINC . '/rss.php');
		 $rss = fetch_rss('http://cvs.aesinformatica.com/Projects/QXV0b21hdGljIFNFTyBMaW5rcw==/rss.xml');
		 $maxitems = 5;
		 $items = array_slice($rss->items, 0, $maxitems);
		?>
		<ul>
		 <?php foreach ( $items as $item ) : ?>
		  <li>
		      <b><a href='http://cvs.aesinformatica.com/download/automatic-seo-links'
		       title='<?php echo $item['title']; ?>'>
		       <?php echo $item['title']; ?>
		      </a></b>
			  <p><?php echo $item['description']; ?></p>
		  </li>
		<?php endforeach; ?>

		</ul>
		<?php
	
}

function automaticSeoLinksUpdate(){

	global $asl_version;
	
	include_once(ABSPATH . WPINC . '/rss.php');
		 $rss = fetch_rss('http://cvs.aesinformatica.com/Projects/QXV0b21hdGljIFNFTyBMaW5rcw==/rss.xml');
		 $item = array_slice($rss->items, 0, 1);
		 //Podía hacer un explode y sacar solo la versión, pero de momento lo dejo así
		 if("Automatic SEO Links-".$asl_version == $item[0]['title']) return false; else return $item[0]['title'];
	

}

?>
