BRConf={'SITE_ID':'','API_R':'http://r.brandreward.com','URL_API':'http://api.brandreward.com','URL_HOST':'http://www.brandreward.com','VER':'1.0','UUID':'','READY':false,};function brCompleted(e){if(document.addEventListener||e.type==="load"||document.readyState==="complete"){brDetach();brInit()}};function brDetach(){if(document.addEventListener){document.removeEventListener("DOMContentLoaded",brCompleted,false);window.removeEventListener("load",brCompleted,false)}else{document.detachEvent("onreadystatechange",brCompleted);window.detachEvent("onload",brCompleted)}};function brInit(){if(inIframeBR()){return}if(BRConf.READY){return}if(!document.body){return setTimeout(brInit)}BRConf.READY=true;BRConf.SITE_ID=window._BRConf.key;if(!checkUrlBR(document.location.href)){return}var url=BRConf.URL_HOST+"/static_cdn/jsaccess.php?key="+BRConf.SITE_ID;var script=document.createElement('script');script.setAttribute('src',url);document.getElementsByTagName('head')[0].appendChild(script)};function linkBR(){var aEle=document.getElementsByTagName('a');var dD=document.domain;for(var i=0;i<aEle.length;i++){var Tgt=aEle[i];if(BRConf.WHITEADVER.length>0){if(Tgt['href']&&accessADVER(Tgt['href'])){Tgt.removeAttribute("onmousedown");Tgt.onclick=function(e){var nUrl=BRConf.API_R+'/?key='+BRConf.SITE_ID+'&id=jsr&url='+encodeURIComponent(this.href);var e=e||event;var jt=false;if(this.target=='_blank')jt=true;var op=(e.shiftKey)||(e.ctrlKey)||(e.metaKey)||(e.button==1)||jt;if(op){window.open(nUrl,'_blank')}else{window.location.href=nUrl}return false};Tgt.oncontextmenu=function(e){var nUrl=BRConf.API_R+'/?key='+BRConf.SITE_ID+'&id=jsr&url='+encodeURIComponent(this.href);var e=e||event;var oUrl=this.href;this.href=nUrl;this.onblur=function(){this.href=oUrl;return true};this.onmouseout=function(){this.href=oUrl;return true};return true}}}else if(Tgt['href']&&!isInnerLink(Tgt['href'])&&checkUrlBR(Tgt['href'])&&ignoreADVER(Tgt['href'])){Tgt.removeAttribute("onmousedown");Tgt.onclick=function(e){var nUrl=BRConf.API_R+'/?key='+BRConf.SITE_ID+'&id=jsr&url='+encodeURIComponent(this.href);var e=e||event;var jt=false;if(this.target=='_blank')jt=true;var op=(e.shiftKey)||(e.ctrlKey)||(e.metaKey)||(e.button==1)||jt;if(op){window.open(nUrl,'_blank')}else{window.location.href=nUrl}return false};Tgt.oncontextmenu=function(e){var nUrl=BRConf.API_R+'/?key='+BRConf.SITE_ID+'&id=jsr&url='+encodeURIComponent(this.href);var e=e||event;var oUrl=this.href;this.href=nUrl;this.onblur=function(){this.href=oUrl;return true};this.onmouseout=function(){this.href=oUrl;return true};return true}}}};function isInnerLink(url){var domainLocal=document.domain;var domainParseLocal=domainParse(domainLocal);var domainParseUrl=domainParse(url);if(domainParseLocal['domainName']==domainParseUrl['domainName']){return true}else{return false}};function ignoreADVER(url){var flag=true;var domainParseUrl=domainParse(url);for(var i=0;i<BRConf.IGNOREADVER.length;i++){var ignoreUrl=BRConf.IGNOREADVER[i];var domainParseIgnoreUrl=domainParse(ignoreUrl);if(domainParseUrl['domainName']==domainParseIgnoreUrl['domainName']){flag=false}}return flag};function accessADVER(url){var flag=false;var domainParseUrl=domainParse(url);for(var i=0;i<BRConf.WHITEADVER.length;i++){var whiteUrl=BRConf.WHITEADVER[i];var domainParseWhiteUrl=domainParse(whiteUrl);if(domainParseUrl['domainName']==domainParseWhiteUrl['domainName']){flag=true}}return flag};function ignoreDomainBR(url){for(var i=0;i<BRConf.IGNOREDOMAIN.length;i++){var v=BRConf.IGNOREDOMAIN[i];if(url.indexOf(v)>=0){return false}}return true};function setUUID(){if(BRConf.UUID!='')return;var d=new Date();var t=d.getTime();var s='';for(var i=0;i<16;i++){var z=Math.random()*16|0;s+=z.toString(16)}var uuid=t+'-'+'js'+BRConf.VER.replace('.','')+'-'+s;BRConf.UUID=uuid};function impressionBR(){var d='?act=publisher.track_js';d+='&key='+BRConf.SITE_ID;d+='&v='+BRConf.VER;d+='&ref='+encodeURIComponent(document.referrer);d+='&page='+encodeURIComponent(document.location.href);d+='&bruid='+BRConf.UUID;var url=BRConf.URL_API+d;callBR(url)};function checkUrlBR(url){if(/.\.(jpe?g|gif|png|bmp)$/i.test(url)){return false}if(url.indexOf("http")!=0){return false}return true};function inIframeBR(){try{return window.self!==window.top}catch(e){return true}};function isHttps(){if(document.location.protocol=='https:'){BRConf.URL_API=BRConf.URL_API.replace('http://','https://');BRConf.URL_HOST=BRConf.URL_HOST.replace('http://','https://')}};function brReady(){isHttps();if(document.readyState==="complete"){setTimeout(brInit)}else if(document.addEventListener){document.addEventListener("DOMContentLoaded",brCompleted,false);window.addEventListener("load",brCompleted,false)}else{document.attachEvent("onreadystatechange",brCompleted);window.attachEvent("onload",brCompleted)}};function callbackAccess(res,ignorelist,whitelist){if(res){BRConf.IGNOREADVER=ignorelist;BRConf.WHITEADVER=whitelist;setUUID();linkBR()}};function callBR(url){img=document.createElement("img");img.src=url};function domainParse(domain){var url=domain;var tmp=domain.match(/^(?:(\w+):\/\/)?([^\/\?&#:]+)/i);domain=tmp[2];var top_domain='biz,com,top,edu,gov,info,int,mil,name,net,org,pro,xxx,aero,cat,coop,jobs,museum,travel,mobi,asia,tel';var sub_domain='ad,ae,af,ag,ai,al,am,an,ao,aq,ar,as,at,au,aw,az,ba,bb,bd,be,bf,bg,bh,bi,bj,bm,bn,bo,br,bs,bt,bv,bw,by,bz,ca,cc,cf,cg,ch,ci,ck,cl,cm,cn,co,cq,cr,cu,cv,cx,cy,cz,de,dj,dk,dm,do,dz,ec,ee,eg,eh,es,et,ev,eu,fi,fj,fk,fm,fo,fr,ga,gb,gd,ge,gf,gh,gi,gl,gm,gn,gp,gr,gt,gu,gw,gy,hk,hm,hn,hr,ht,hu,id,ie,il,in,io,iq,ir,is,it,jm,jo,jp,ke,kg,kh,ki,km,kn,kp,kr,kw,ky,kz,la,lb,lc,li,lk,lr,ls,lt,lu,lv,ly,ma,mc,md,me,mg,mh,ml,mm,mn,mo,mp,mq,mr,ms,mt,mv,mw,mx,my,mz,na,nc,ne,nf,ng,ni,nl,no,np,nr,nt,nu,nz,om,qa,pa,pe,pf,pg,ph,pk,pl,pm,pn,pr,pt,pw,py,re,ro,ru,rw,sa,sb,sc,sd,se,sg,sh,si,sj,sk,sl,sm,sn,so,sr,st,su,sy,sz,tc,td,tf,tg,th,tj,tk,tm,tn,to,tp,tr,tt,tv,tw,tz,ua,ug,uk,us,uy,va,vc,ve,vg,vn,vu,wf,ws,ye,yu,za,zm,zr,zw';var domainZone;var domainName;var domainSub;var domainArr=domain.split('.');if(domainArr.length<3){domainZone=domain}else{var last=domainArr.pop();var last_1=domainArr.pop();var last_2=domainArr.pop();var reg_last=new RegExp("(^|,)"+last+"(,|$)");var reg_last_1=new RegExp("(^|,)"+last_1+"(,|$)");var reg_last_2=new RegExp("(^|,)"+last_2+"(,|$)");if(reg_last.test(top_domain)){domainZone=last_1+'.'+last}else if(reg_last.test(sub_domain)){if(reg_last_1.test(top_domain)){domainZone=last_2+'.'+last_1+'.'+last}else if(reg_last_1.test(sub_domain)){domainZone=last_2+'.'+last_1+'.'+last}else{domainZone=last_1+'.'+last}}else{domainZone=last_1+'.'+last}}domainName=domainZone.split('.')[0];domainSub=domain;return{'url':url,'domainZone':domainZone,'domainName':domainName,'domainSub':domainSub}};brReady();