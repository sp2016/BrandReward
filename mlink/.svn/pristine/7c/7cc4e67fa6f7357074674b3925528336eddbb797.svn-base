$(document).ready(function(){
	function formatItem(row) {
		//merchantid,merchantname,editor,site
		return row[1] + "(" + row[0] + ")" + " " + row[2] + " " + row[3];
	}
	
	function formatResult(row) {
		return row[1];
	}
	
	//merchant
	$("#merchant_search").live("click", function(){
		var sitename = $("#sitename_sel").val();
		$("#merchant_search").autocomplete(
			'/front/search.php?act=merchantName&sitename='+sitename,
			{
				scrollHeight: 320,
				max: 3000,
				formatItem: formatItem,
				formatResult: formatResult,
				autoFill: true
			});
	});
	
	$("#merchant_search").result(function(event,row,formatted){
		if(!row) return false;
		$("#merchant_search_result_id").val(row[0]);
		$("#merchant_search_result_name").val(row[1]);
		$("#merchant_search_result_editor").val(row[2]);
		$("#merchant_search_result_site").val(row[4]);
		//$("#Contentresult").val(row[0]);
		$("#merchantid").val(row[0]);
		$("#sitename").val(row[4]);
		//$("#nomerchant").val("");
		//it's strang that 'formatted' is merchant id not we expected merchant name??
		assignMerchant();
		$("#assignMerTrue").attr("checked",true);
		try{
			doaftersearch(row);
		}
		catch(err){}
	});
	
	
	$("#merchant_issue").live("click", function(){
		
		var sitename = $("#sitename_sel").val();
		$(".ac_results").remove();
		$("#merchant_issue").autocomplete(
				'/front/search.php?act=merchantissue&sitename='+sitename,
				{
					scrollHeight: 320,
					max: 3000,
					cacheLength:1,
					matchSubset:false,
					formatItem: formatItem,
					formatResult: formatResult,
					autoFill: true
				});
	});
	
	$("#merchant_issue").result(function(event,row,formatted){

		if(!row) return false;
		$("#merchant_search_result_id").val(row[0]);
		$("#merchant_search_result_url").html("<a href='"+ row[4] + "' target='_blank' style='color:#0080C0;'>" + row[4] + "</a>");
		$("#loadListTR").hide();
		loadList();
	});
	
	//init,clear all
	/*
	$("#merchant_search_result_id").val("");
	$("#merchant_search_result_site").val("");
	$("#merchant_search_result_name").val("");
	$("#merchant_search_result_editor").val("");
	$("#merchant_name_auto").html("");
	*/
});//end ready
