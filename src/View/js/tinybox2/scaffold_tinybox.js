/** getViewport (public domain) **/
var tinyboxVisible=false;var viewport;var boxWidth=290;var boxHeight=250;var prevBoxWidth=290;var tinyboxSubmitted=false;
function getViewport(){var e=window;var a='inner';if(!('innerWidth' in window)){a='client';e=document.documentElement||document.body;}return{width:e[a+'Width'],height:e[a+'Height']}}
function getBoxSize(){viewport=getViewport();boxWidth = Math.round(viewport['width'] / 1.5);boxHeight = Math.round(viewport['height'] / 1.5);boxWidth = boxWidth > 290 ? boxWidth : 290;boxHeight = boxHeight > 250 ? boxHeight : 250;}

//If the browser window is resized, adjust lightbox size accordingly
var resizeTimer;function tinyboxResize(event){clearTimeout(resizeTimer);resizeTimer = setTimeout(function(){if (tinyboxVisible){getBoxSize();if(prevBoxWidth!=boxWidth){TINY.box.size(boxWidth,boxHeight)}prevBoxWidth=boxWidth;}}, 250);}window.addEventListener('resize',tinyboxResize);
function repaintElement(elem){elem.style.display='none';elem.offsetHeight;elem.style.display='';}

//Allow box to be shown with a simple function call
function showBox(url,onload,callback){getBoxSize();TINY.box.show({url:url,post:'',width:boxWidth,height:boxHeight,fixed:false,maskid:'greymask',maskopacity:40,openjs:function(){tinyboxVisible=true;var tb_window=document.getElementById('tinybox_window');tb_window.scrollTop=0;repaintElement(tb_window);if(onload){onload();}},closejs:function(){tinyboxVisible=false;if(callback){callback();}}});document.getElementsByClassName('tmask')[0].onclick=null;}
function showBlankBox(onload,callback){getBoxSize();TINY.box.show({html:'<div id="tinybox_popup_content"></div>',post:'',width:boxWidth,height:boxHeight,fixed:false,maskid:'greymask',maskopacity:40,openjs:function(){tinyboxVisible=true;var tb_window=document.getElementById('tinybox_window');tb_window.scrollTop=0;repaintElement(tb_window);if(onload){onload();}},closejs:function(){tinyboxVisible=false;if(callback){callback();}}});}
function showIframeBox(url,onload,callback){url=url+'#'+Date.now();getBoxSize();TINY.box.show({iframe:url,boxid:'frameless',width:boxWidth,height:boxHeight,fixed:false,maskid:'greymask',maskopacity:40,closejs:function(){tinyboxVisible=false;if(callback){callback();}}});document.getElementsByClassName('tmask')[0].onclick=null;}