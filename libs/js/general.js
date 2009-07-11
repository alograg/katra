var resizeContent=function(){
	var oContenido=$('contenido');
	var oBodyWidth=oContenido.getParent().getSize();
	oBodyWidth.x-=parseInt(oContenido.getStyle('margin-left'))*2;
	oBodyWidth.x-=parseInt(oContenido.getStyle('border-left-width'))*2;
	oBodyWidth.x-=parseInt(oContenido.getStyle('padding-left'))*2;
	oContenido.setStyle('width',oBodyWidth.x-215);
	/*var cordenadasBarra=$('barra').getCoordinates();//((oBodyWidth.x-215)/4)*3)
	var cordenadasFooter=$('footer').getCoordinates();
	var cordenadasContenido=oContenido.getCoordinates();
	var alturaContanido=Math.max(Math.min(cordenadasFooter.top-cordenadasContenido.top,cordenadasBarra.height),cordenadasContenido.height);
	oContenido.setStyle('height',alturaContanido);*/
}
var once=false;
var columnWidth=function(el){
	try {
		return (max(el.parentNode.offsetWidth,250)+'px');
	}catch(e){
		return '32%';
	};
}
var skin = {};
skin['BORDER_COLOR'] = '#cccccc';
skin['ENDCAP_BG_COLOR'] = '#6d9120';
skin['ENDCAP_TEXT_COLOR'] = '#333333';
skin['ENDCAP_LINK_COLOR'] = '#0000cc';
skin['ALTERNATE_BG_COLOR'] = '#999999';
skin['CONTENT_BG_COLOR'] = '#999999';
skin['CONTENT_LINK_COLOR'] = '#b7df63';
skin['CONTENT_TEXT_COLOR'] = '#FFFFFF';
skin['CONTENT_SECONDARY_LINK_COLOR'] = '#b7df63';
skin['CONTENT_SECONDARY_TEXT_COLOR'] = '#FFFFFF';
skin['CONTENT_HEADLINE_COLOR'] = '#b7df63';
skin['POSITION'] = 'bottom';
skin['DEFAULT_COMMENT_TEXT'] = '- tu comentario aqui -';
skin['HEADER_TEXT'] = 'Comentarios';
window.addEvent('domready', function() {
	$(window).addEvent('resize',resizeContent);
	var cResumenNota=$$('.resumenNota');
	if(cResumenNota){
		var tmpO={maxHeight:0};
		cResumenNota.each(function(item){
				var tmpSize=$(item).getCoordinates();
				this.maxHeight=Math.max(this.maxHeight,tmpSize.height)
			},tmpO);
		cResumenNota.each(function(item){
				$(item).setStyle('height',this.maxHeight);
			},tmpO);
	}
	var tipoDonativo=$$('.donativos option').getRandom();
	if(tipoDonativo)
		tipoDonativo.set('selected', 'selected');
	var boxDiv=$$('.secctionNews').getParent('div');
	if(boxDiv)
		boxDiv.setStyle('overflow-y','scroll')
	resizeContent();
});
