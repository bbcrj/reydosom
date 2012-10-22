var $j = jQuery.noConflict();
$j(document).ready(
	function (){
		$j("#pikame").PikaChoose({carousel:true});
		if($j('#pikame li.jcarousel-item').length==1)
		$j('.pika-imgnav a').hide()
	});
$j(document).ready(function() {
	$j('.cart_num a').each(function() {
            var hd = jQuery(this).html();
            var index = hd.indexOf(' ');
            if(index == -1) {
                index = hd.length;
            }
            $j(this).html('<em>' + hd.substring(0, index) + '</em>' + hd.substring(index, hd.length));
        });						
		// Tabs Fly-page		
			if ($j('.desc2 > div').hasClass('video')) {
				$j('.tab7').css({display:'block'})
			} else {
				$j('.tab7').css({display:'none'})	
		};

		//accordion begin
	    $j("#accordion dt").eq(0).addClass("active");
	    $j("#accordion dd").eq(0).show();
	    $j("#accordion dt").click(function(){
	        $j(this).next("#accordion dd").slideToggle("slow")
	        .siblings("#accordion dd:visible").slideUp("slow");
	        $j(this).toggleClass("active");
	        $j(this).siblings("#accordion dt").removeClass("active");
	        return false;
	    });
		
		$j(function(){
		 $j("#tabs").tabs({
			fx: { opacity: 'toggle' },			  
	   		cookie: {
            // store cookie for a day, without, it would be a session cookie
            expires: 1
         }
	  })
	});

	});
 $j(window).load(function() {
			$j('.tab_container , .Fly-tabs , .share1 , .share , .checkout-button-top').css('visibility', 'visible');
			$j('.checkout-button-top').css({visibility:'visible',display:'block'});
		});	
