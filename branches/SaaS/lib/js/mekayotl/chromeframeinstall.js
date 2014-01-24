window.addEvent('domready', function() {
    $('browserUpdate').set('html', 'Para el correcto funcionamiento se recomienda actualizar tu <a href="http://windows.microsoft.com/es-ES/internet-explorer/downloads/ie-9/worldwide-languages" target="_blank">explorador</a>, o instalar el siguiente <a href="' + $(document.head).getElement('base').href + 'index.html" id="actualiza">plugin</a>.');
    $('actualiza').addEvent('click', function(event) {
        event.stop();
        CFInstall.check({
            mode : "inline",
            destination : $(document.head).getElement('base').href
        });
    });
});
