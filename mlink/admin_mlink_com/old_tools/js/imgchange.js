this.imagePreview = function(){	
		xOffset = 100;
		yOffset = 100;
	$("a.previewImg").hover(function(e){
		this.t = this.title;
		this.title = "";	
		var c = (this.t != "") ? "<br/>" + this.t : "";
		$("body").append("<div id='previewImg'><img src='"+ this.href +"' alt='Image preview' />"+ c +"</div>");
		$("#previewImg")
			.css("position", "absolute");
									
    },
	function(){
		this.title = this.t;	
		$("#previewImg").remove();
    });	
	$("a.previewImg").mousemove(function(e){
		$("#previewImg")
			.css("top",(e.pageY - yOffset) + "px")
			.css("left",(e.pageX + xOffset) + "px");
	});			
};

$(document).ready(function(){
	imagePreview();
});