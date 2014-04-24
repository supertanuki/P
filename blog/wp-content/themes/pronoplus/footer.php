<?
/*
<hr />
<div id="footer">
	<p>
		<a href="feed:<?php bloginfo('rss2_url'); ?>">Articles (RSS)</a> et <a href="feed:<?php bloginfo('comments_rss2_url'); ?>">Commentaires (RSS)</a>.
	</p>
</div>

<?php wp_footer(); ?>
*/ ?>

<?
echo file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/footer-blog.php');
?>

</div>



<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-3657249-1");
pageTracker._initData();
pageTracker._trackPageview();
</script>
	</body>
</html>
