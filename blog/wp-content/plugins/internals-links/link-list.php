<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé
header('Content-type: text/javascript');

require('../../../wp-config.php');

if(!function_exists('is_in_cat'))
{
	// un post $post_id est-il dans la catégorie $cat_id ?
	function is_in_cat($cat_id, $post_id)
	{
		$in_cat = false;
		$cats = get_the_category($post_id);
		foreach ( $cats as $cat ) {
			if ( $cat->term_id == $cat_id ) {
				$in_cat = true;
				break;
			}
		}
		return $in_cat;
	}
}

function internal_links_arbo($id_parent=0, $alinea='')
{
	global $tab;
	$limittext = 40;
	
	$cats = get_categories('type=post&orderby=name&hide_empty=0&hierarchical=0&child_of='.$id_parent);
	//echo "<pre>"; print_r($cats); echo "</pre>";	
	foreach($cats as $cat) if($cat->category_parent == $id_parent)
	{
		$str = '["';		
		$str .= $alinea.(strlen($cat->cat_name)>$limittext ? substr($cat->cat_name, 0, $limittext).'...' : $cat->cat_name);	
		$str .= '", "';		
		$str .= str_replace('http://'.$_SERVER['HTTP_HOST'], '', get_category_link($cat->cat_ID));
		$str .= "\"]\n";
		
		$tab[] = $str;
		
		internal_links_arbo($cat->cat_ID, $alinea."-- ");
		
		$posts = get_posts('category='.$cat->cat_ID.'&orderby=title&order=ASC&post_type=post&numberposts=0');
		//echo "<pre>"; print_r($posts); echo "</pre>";
		foreach($posts as $post) if(is_in_cat($cat->cat_ID, $post->ID))
		{
			$str = '["';		
			$str .= $alinea."-- ".(strlen($post->post_title)>$limittext ? substr($post->post_title, 0, $limittext).'...' : $post->post_title);		
			$str .= '", "';
			$str .= str_replace('http://'.$_SERVER['HTTP_HOST'], '', get_permalink($post->ID));
			$str .= "\"]\n";
			$tab[] = $str;
		}
	}
	
}

$tab = array();
internal_links_arbo(0);

echo 'var tinyMCELinkList = new Array(';
echo implode(',', $tab);
echo ');';
?>