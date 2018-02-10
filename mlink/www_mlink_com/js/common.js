
$(function(){
	
	$(document).delegate('.show_filter','click',function(){
		$(".overlay").show();
		$(".filter").show();
//		$(".filter").animate({"width":"85%"},500); 
	});
	
	$(document).delegate('.back,.close,.overlay','click',function(){
                if($(this).attr('data-dismiss') == 'modal'){
                    return;
                }
		$(".overlay").hide();
		$(".filter").hide();
//		$(".filter").animate({"width":"0"},500); 
	});
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
})
