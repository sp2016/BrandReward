var cat_primary = 0;
function openMerSub(obj){
	var stat = $(obj).html();
	if(stat.indexOf(" +")>-1){
		$(obj).html(stat.replace(" +"," -"));
		$(obj).parent().parent().find('div').show();
	}
	if(stat.indexOf(" -")>-1){
		$(obj).html(stat.replace(" -"," +"));
		$(obj).parent().parent().find('div').hide();
		$(obj).parent().parent().find('div').eq(0).show();
	}
}
function showMerCat(obj){
	var check = $(obj).attr('checked');
	var id = $(obj).val();
	var pid = $(obj).attr('pid');
	if(check){
		$("#selMerCat"+id).attr('class','off_cat');
		if($("#Category").length>0) set_cat_str(id,'add');
		if(pid>0){
			if($("#selMerCat"+pid).attr('class')!='off_cat') $("#selMerCat"+pid).attr('class','off_half_cat');
		}
	}else{
		$("#selMerCat"+id).attr('class','on_cat');
		$(obj).parent().find('input').eq(1).attr('checked','');
		$("#selMerCat"+id+" span").attr('class','on_pri');
		if($("#Category").length>0) set_cat_str(id,'del');
		var par = $(obj).parent().parent();
		if(par.find('input').eq(0).attr('checked')){
		}else{

			var pst = 0;
			for(i=1;i<par.find('div').length;i++){
				if(par.find('div').eq(i).find('input[type="checkbox"]').attr('checked')) pst = 1;
			}
			cid = pid;
			if(pid==0) cid = id;
			if(pst>0){
				$("#selMerCat"+cid).attr('class','off_half_cat');
			}else{
				$("#selMerCat"+cid).attr('class','on_cat');
			}
		}
	}
}
function set_cat_str(n,t){
	var str = $("#Category").val();
	if(t=='add'){
		if(str){
			if(str.split(',').length>0){
				str = str + ',' + n;
			}else{
				str = n;
			}
		}else{
			str = n;
		}
	}
	if(t=='del'){
		var arr = str.split(',');
		var str = '';
		for(var i=0;i<arr.length;i++){
			if(arr[i]!=n){
				if(i==0){
					str = arr[i];
				}else{
					str = str + ',' + arr[i];
				}
			}
		}
	}
	$("#Category").val(str);
}
function catPrimary(obj){
	var id = $(obj).val();
		$('.selMerCat span').attr('class','on_pri');
		$('.on_cat span').attr('class','on_pri');
		$('.off_cat span').attr('class','on_pri');
		$('.off_half_cat span').attr('class','on_pri');
		var lobj = $(obj).parent().find('input').eq(0);
		if($(lobj).attr('checked')){
		}else{
			$(lobj).attr('checked','checked');
			showMerCat(lobj);
		}
					
		$('#selMerCat'+id+' span').attr('class','off_pri');
}

function auto_sel_cat(){
	var obj = $(".catetory_list input[type='checkbox']");
	for(var i=0;i<cat_sel_id.length;i++){
		var csobj = $(".catetory_list input[value='"+cat_sel_id[i]+"']");
		csobj.attr('checked','checked');
		if(csobj.parent().is(":hidden")){
			var fobj = csobj.parent().parent().find('div').eq(0).find('span');
			fobj.html(fobj.html().replace(" +"," -"));
			csobj.parent().parent().find('div').show();
		}
		showMerCat(csobj);
	}
}
function setCss(){
	var str = '<style type="text/css">';
	str = str + '.selMerCat{display:none;}';
	str = str + '.off_cat{height:20px;line-height:20px;font-size:12px}';
	str = str + '.on_cat{display:none;}';
	str = str + '.off_half_cat{height:20px;line-height:20px;font-size:12px;color:#ccc;}';
	str = str + '.selMerCat span{display:none;}';
	str = str + 'span.on_pri{display:none;}';
	str = str + 'span.off_pri{height:20px;line-height:20px;font-size:12px;color:#ff9632}';
	str = str + '.catetory_list{padding:0px;margin:0px;list-style-type:none;border:1px solid #828790;padding:3px;background:#fff;width:270px;height:320px;overflow:auto}';
	str = str + '.catetory_list li{padding:0px;margin:0px}';
	str = str + '</style>';
	$('body').append(str);
}
function auto_sel_mer_cat(){
	auto_sel_cat();
//	if(site_name!='csde'){
		$(".catetory_list input[name='CatPrimary']").attr('checked','');
		$('.selMerCat span').attr('class','on_pri');
		$('.on_cat span').attr('class','on_pri');
		$('.off_cat span').attr('class','on_pri');
		$('.off_half_cat span').attr('class','on_pri');
		if(cat_primary){
			var cpobj = $(".catetory_list input[value='"+cat_primary+"']").parent().find('input').eq(1);
			$(cpobj).attr('checked','checked');
			catPrimary(cpobj);
		}
//	}
}
function auto_sel_store_cat(){
	auto_sel_cat();
}
function markMerCat(){
	setCss();
	$.getJSON("/ajax/category.php", { ajaxaction: "merchant", site:site_name }, function(json){
		var left_str = '<ul class="catetory_list">';
		var right_str = '<ul class="catetory_list">';
		for(var i=0;i<json.length;i++){
			left_str = left_str + '<li>';
			left_str = left_str + '<div>';
			left_str = left_str + '<input name="MerchantCat[]" pid="0" type="checkbox" value="'+json[i]['ParentCate']['ID']+'" onclick="showMerCat(this)" />';
			left_str = left_str + '<span style="cursor:pointer" onclick="openMerSub(this)">'+json[i]['ParentCate']['Name']+' +</span>';
			left_str = left_str + '<input name="CatPrimary" type="radio" value="'+json[i]['ParentCate']['ID']+'" onclick="catPrimary(this)" />Primary';
			left_str = left_str + '</div>';
			right_str = right_str + '<li id="selMerCat'+json[i]['ParentCate']['ID']+'" class="selMerCat" style="padding-left:0px">'+json[i]['ParentCate']['Name'];
			right_str = right_str + '<span>(Primary)</span>';
			right_str = right_str + '</li>	';
			for(var m=0;m<json[i]['ChildCate'].length;m++){
				left_str = left_str + '<div style="display:none">';
				left_str = left_str + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="MerchantSubCat[]" pid="'+json[i]['ParentCate']['ID']+'" type="checkbox" value="'+json[i]['ChildCate'][m]['ID']+'" onclick="showMerCat(this)" /><span>'+json[i]['ChildCate'][m]['Name']+'</span>';
				left_str = left_str + '<input name="CatPrimary" type="radio" value="'+json[i]['ChildCate'][m]['ID']+'" onclick="catPrimary(this)" />Primary';
				left_str = left_str + '</div>';
				right_str = right_str + '<li id="selMerCat'+json[i]['ChildCate'][m]['ID']+'" class="selMerCat" style="padding-left:10px">'+json[i]['ChildCate'][m]['Name'];
				right_str = right_str + '<span>(Primary)</span>';
				right_str = right_str + '</li>';
			}
								 
			left_str = left_str + '</li>';
		}
		left_str =left_str + '</ul>';
		right_str =right_str + '</ul>';
		$('#left_cat').html(left_str);
		$('#right_cat').html(right_str);
		auto_sel_mer_cat();
	});
}
function markStoreCat(){
	setCss();
	$.getJSON("/ajax/category.php", { ajaxaction: "store", site:site_name }, function(json){
		var left_str = '<ul class="catetory_list">';
		var right_str = '<ul class="catetory_list">';
		for(var i=0;i<json.length;i++){
			left_str = left_str + '<li>';
			left_str = left_str + '<div>';
			left_str = left_str + '<input pid="0" type="checkbox" value="'+json[i]['ParentCate']['ID']+'" onclick="showMerCat(this)" />';
			left_str = left_str + '<span style="cursor:pointer" onclick="openMerSub(this)">'+json[i]['ParentCate']['Name']+' +</span>';
			left_str = left_str + '</div>';
			right_str = right_str + '<li id="selMerCat'+json[i]['ParentCate']['ID']+'" class="selMerCat" style="padding-left:0px">'+json[i]['ParentCate']['Name']+'</li>	';				
			for(var m=0;m<json[i]['ChildCate'].length;m++){
				left_str = left_str + '<div style="display:none">';
				left_str = left_str + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input pid="'+json[i]['ParentCate']['ID']+'" type="checkbox" value="'+json[i]['ChildCate'][m]['ID']+'" onclick="showMerCat(this)" /><span>'+json[i]['ChildCate'][m]['Name']+'</span>';
				left_str = left_str + '</div>';
				right_str = right_str + '<li id="selMerCat'+json[i]['ChildCate'][m]['ID']+'" class="selMerCat" style="padding-left:10px">'+json[i]['ChildCate'][m]['Name']+'</li>';
			}
								 
			left_str = left_str + '</li>';
		}
		left_str =left_str + '</ul><input name="Category" type="hidden" value="" id="Category" />';
		right_str =right_str + '</ul>';
		$('#left_cat').html(left_str);
		$('#right_cat').html(right_str);
		auto_sel_store_cat();
	});
}