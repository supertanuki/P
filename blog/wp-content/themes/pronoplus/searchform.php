<form method="get" id="searchform" action="<?php bloginfo('url'); ?>/">
	<div>
		<input type="text" value="<?php the_search_query(); ?>" size="20" name="s" id="s" />
		<input type="submit" id="searchsubmit" value="Chercher" />
	</div>
</form>
