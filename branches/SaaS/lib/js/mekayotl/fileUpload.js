var fileUpload = new Class({
    initialize : function(el, url, invisibleElement) {
        this.el = el;
        this.url = url;
        this.invisibleElement = invisibleElement;
        this.loadingElement = new Element('span', {
            'html' : '<img src="default/style/img/loading.gif" alt="cargando" />Cargando'
        });
        this.el.addEvent('change', this.iframeSender.bind(this));
    },
    iframeSender : function(e) {
        this.el = e.target;
        if(this.invisibleElement)
            this.invisibleElement.set('styles',{display:'none'});
        this.loadingElement.replaces(this.el);
        var iframe = new IFrame({
            id : 'ifrSender',
            src : 'about:blank',
            styles : {
                display : 'none'
            }
        });
        var form = new Element('form', {
            id : 'frmSender',
            method : 'post',
            target : 'ifrSender',
            enctype : 'multipart/form-data',
            encoding : 'multipart/form-data',
            action : this.url
        });
        this.iframe = iframe.inject(this.loadingElement, 'after');
        var iframeBody = $(this.iframe.contentDocument.documentElement);
        if (!iframeBody) {
            var content = '<!DOCTYPE html><head><title>Dynamic iframe</title>'
                    + '<body></body></html>';
            this.iframe.contentWindow.document.open('text/html', 'replace');
            this.iframe.contentWindow.document.write(content);
            this.iframe.contentWindow.document.close();
            iframeBody = $(this.iframe.contentWindow.document
                    .getElementsByTagName('BODY')[0]);
        }
        this.iframeBody = iframeBody;
        this.form = form.inject(this.iframeBody);
        this.el.inject(this.form);
        this.iframe.addEvent('load', this.ifrLoad.bind(this));
        this.form.submit();
    },
    ifrLoad : function(e) {
        this.el = new Element('input', {
            type : 'file',
            id : "filImage",
            accept : "image/*",
            name : "image",
            placeholder : "No se ha seleccionado un archivo nuevo"
        });
        if(this.invisibleElement)
            this.invisibleElement.set('styles',{display:'block'});
        this.el.replaces(this.loadingElement);
        this.el.addEvent('change', this.iframeSender.bind(this));
        this.iframe = e.target;
        var iframeBody = $(this.iframe.contentDocument.documentElement);
        if (!iframeBody) {
            iframeBody = $(this.iframe.contentWindow.document
                    .getElementsByTagName('BODY')[0]);
        }
        this.iframeBody = iframeBody;
        if(this.iframeBody.innerText)
            this.textResult = this.iframeBody.innerText;
        else
            this.textResult = this.iframeBody.getElementsByTagName('BODY')[0]
                .children[0].textContent;
        this.result = JSON.decode(this.textResult);
        this.iframe.destroy();
        this.load(this.result, this.textResult);
    },
    load : function(result, textResult) {
    }
});
/*
 * old: $('filImage').addEvent('change',function(e){ var frame = new IFrame({
 * name:'ifrmImage', id:'ifrmImage', src:'about:blank' }); var form = new
 * Element('form',{id:'imgFrm',method:'post',target:'ifrmImage',enctype:'multipart/form-data',action:'<?php
 * print $baseURL;?>/Member/uploadImage.json'}); this.clone().inject(form);
 * form.inject(frame); frame.inject(this,'after');
 * frame.addEvent('load',loadImage); form.submit(); //alert('asd'); });
 */
