function resetAff(){
	$("#affiliatetype").val("");
	$("#affiliatename").val("");
}


$().ready(function() {
	function log(event, data, formatted) {
		$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
	}
	
	function formatItem(row) {
		return row[1] + "(" + row[0] + ")" + (row[2]? " - " +row[2]:"");
	}
	function formatResult(row) {
		return row[1];
	}
	
	if(typeof addfun != "function"){
		function addfun(){}
	}
	
	//program_list
	$("#program_search").autocomplete('../front/program_search.php?ajaxTag=searchProgram', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true,
		//extraParams : new Array(getAffType),
		myParams: new Array("affiliatetype"),
		cacheLength: 0,
		//addfun: sel_program
	});
	
	/*function getAffType(){
		return $("#affiliatetype").val()
	}*/
	
	$("#affiliatename").autocomplete('../front/program_search.php?ajaxTag=searchAffiliate', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true,
		addfun: sel_aff
	});
	
	function sel_aff(){
		$.ajax({type: "GET",
			url: "../front/program_search.php?ajaxTag=getAffiliateByName&q="+encodeURIComponent($("#affiliatename").val()),			
			success: function(msg){			
				$("#affiliatetype").val(msg);				
			}
		});
		
		if(typeof addfun == "function"){
			addfun();
		}
	}
	
	$("#merchantname").autocomplete('../front/program_search.php?ajaxTag=searchMerName', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatResult,
		autoFill: true,
		myParams: new Array("site"),
		cacheLength: 0
		//addfun: sel_mer
	});
	
	/*function sel_mer(){
		$.ajax({type: "GET",
			url: "/front/program_search.php?site="+escape($("#site").val())+"&ajaxTag=getMerchantIdByName&q="+encodeURIComponent($("#merchantname").val()),			
			success: function(msg){				
				$("#merchantid").val(msg);
				//$("#merchant_id_span").html("MID:" + msg);
				//$("form:first").submit();
			}					   
		});	
	}*/
	
	//task_program_partnership
	$("#program").autocomplete('../front/program_search.php?ajaxTag=searchProgram', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatPrgmResult,
		autoFill: true,		
		myParams: new Array("affiliatetype"),
		cacheLength: 0,
		extraReceiveData : new Array("programid"),
		addfun: addfun
		//addfun: sel_prgm
	});
	
	$("#domain_search").autocomplete('../front/program_search.php?ajaxTag=searchDomain', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatPrgmResult,
		autoFill: true,		
		//myParams: new Array("affiliatetype"),
		cacheLength: 0,
		extraReceiveData : new Array("domainid"),
		addfun: addfun
		//addfun: sel_prgm
	});
	
	
	$("#store").autocomplete('../front/program_search.php?ajaxTag=searchStore', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatStoreResult,
		autoFill: true,		
		//myParams: new Array("affiliatetype"),
		cacheLength: 0,
		extraReceiveData : new Array("storeid"),
		addfun: addfun
		//addfun: sel_prgm
	});
	
	$("#searchstorebymername").autocomplete('../front/program_search.php?ajaxTag=searchStoreByMer', {
		scrollHeight: 320,
		max: 3000,
		formatItem: formatItem,
		formatResult: formatStoreByMerResult,
		autoFill: true,
		//myParams: new Array("site"),
		cacheLength: 0,
		extraReceiveData : new Array("storeid"),
	});
		
	function formatPrgmResult(row) {		
		return row[1] + "|||" + row[2];
	}
	
	function formatStoreResult(row){
		return row[1] + "|||" + row[0] + "|||" + row[2];
	}
	
	function formatStoreByMerResult(row){
		return row[1] + "|||" + row[3];
	}
});

