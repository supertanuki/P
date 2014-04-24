<?php
	/*
	plugin main options page
    */

	// Prevent loading this file directly - Busted!
	if ( ! class_exists( 'WP' ) )
	{
		header( 'Status: 403 Forbidden' );
		header( 'HTTP/1.1 403 Forbidden' );
		exit;
	}


	// ***************************************
	// start social settings *****************
	// ***************************************

?>
    
    <!-- social stuff -->
    <div class="postbox">
             
            <h3 style="cursor:default;">Let's Get Social!</h3>
            <div class="inside" style="padding:0px 6px 0px 6px;">
               
               
               <!-- start of insideer -->
                    
                    <div style="float:left; margin-left:10px; margin-right:60px;">
					<?php if (wpar_admin_links()) {echo wpar_admin_links;} // Extra admin links ?>
                    </div><!-- end of links box -->
                    
                    <div style="float:left; margin-right:60px; margin-top:20px;">
                    <!-- Place this tag where you want the +1 button to render -->
					<div class="g-plusone" data-size="tall" data-annotation="inline" data-width="250" data-href="http://authorhreview.com/"></div>
    				
                    <div class="clear"><br></div>
                    
                    <!-- Twitter follow us button -->
            		<div style="margin: auto;">
						<a href="https://twitter.com/authorhreview"
            			class="twitter-follow-button"
            			data-show-count="true"
       		    	 	data-lang="en"
            			data-size="normal">Follow @AuthorhReview</a>
						<script>!function(d,s,id)
							{var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id))
								{js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}
								(document,"script","twitter-wjs");
						</script>
					</div>   
                    <!-- end of Twitter follow us button -->  
                    </div><!-- end of second social box -->
                    
                    <div style="float:left; margin-top:20px;">
                    <!-- Facebook like and send buttons -->
                    <div id="fb_warper">
                    	<div class="fb-like" ref="wp-dashboard" data-href="http://authorhreview.com/" data-send="true" data-width="250" data-show-faces="true" data-font="arial"></div>
					</div>
                    <!-- Facebook like and send buttons -->
                    </div><!-- end of third social box -->

                  
				
                
            </div>
            <!-- end of insideer -->
            
			<div style="clear:both;"></div>
    
    	</div>
        <!-- social stuff -->


	
        <!-- for facebook like -->
        <div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '301654169919795', // App ID
      channelUrl : 'http://authorhreview.com/channel.html', // Channel File
      status     : true, // check login status
      cookie     : true, // enable cookies to allow the server to access the session
      xfbml      : true  // parse XFBML
    });

    // Additional initialization code here
  };

  // Load the SDK Asynchronously
  (function(d){
     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement('script'); js.id = id; js.async = true;
     js.src = "//connect.facebook.net/en_US/all.js";
     ref.parentNode.insertBefore(js, ref);
   }(document));
</script>

	<!-- G+ render -->
<script type="text/javascript">
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>