// @todo optimizar para manejar una sola clase.

/**
 * Transformar una tabla en una gráfica de dona.
 */
var Table2DonutChart = new Class({
    /* implements options */
    Implements : Options,
    /* options */
    options : {
        triggerClass : 'toDonutChart',
        hiddenClass : 'hidden',
        chartSize : '450x150',
        charType : 'pc',
        sizeCheck : /\s?size([^\s]+)/,
        colorCheck : /\s?color([^\s]+)/
    },
    /* constructor of class - initialize */
    initialize : function(options) {
        var chartsrc = 'http://chart.googleapis.com/chart?';
        this.setOptions(options);
        var tables = $$('table.' + this.options.triggerClass);
        for(var i = 0; tables[i]; i++) {
            var t = tables[i];
            var data = [];
            var labels = [];
            var colors = [];
            var title = t.getElement('caption').get('html');
            var size = t.getParent().getSize();
            var chartOptions = {
                chc : 'corp',
                chf : 'bg,s,FFFFFF00',
                cht : 'pc',
                chd : 't:0|',
                chco : null,
                chs : null,
                chdl : null
            };
            var trs = t.getElements('tbody tr');
            for(var j = 0; trs[j]; j++) {
                var tds = trs[j].getElements('td');
                labels.push(tds[0].get('html') + ' (' + tds[1].get('html') + ')');
                data.push(tds[1].get('html'));
                colors.push(trs[j].getStyle('background-color').replace(/\#/, ''));
            }
            chartOptions.chd += data.join(",");
            // chartOptions.chdl = labels.join("|");
            chartOptions.chs = Math.min(size.x, 480);
            chartOptions.chs += 'x' + Math.ceil((Math.min(size.x, 480) / 4) * 3);
            chartOptions.chco = 'FFFFFF,' + colors.join(',');
            t.addClass(this.options.hiddenClass);
            new Element('img', {
                src : chartsrc + Object.toQueryString(chartOptions),
                alt : title,
                title : title,
                'class' : 'donutChart'
            }).inject(t, 'before');
            t.dispose();
        }// end for
    }
});

/**
 * Transformar una tabla en una gráfica de barras.
 */
var Table2BarChart = new Class({
    /* implements options */
    Implements : Options,
    /* options */
    options : {
        triggerClass : 'toBarChart',
        hiddenClass : 'hidden',
        chartSize : '450x150',
        charType : 'bvs',
        sizeCheck : /\s?size([^\s]+)/,
        colorCheck : /\s?color([^\s]+)/
    },
    /* constructor of class - initialize */
    initialize : function(options) {
        var chartsrc = 'http://chart.googleapis.com/chart?';
        this.setOptions(options);
        var tables = $$('table.' + this.options.triggerClass);
        for(var i = 0; tables[i]; i++) {
            var t = tables[i];
            var data = [];
            var title = t.getElement('caption').get('html');
            var size = t.getParent().getSize();
            var maxValue = 100;
            var chartOptions = {
                cht : 'bvs',
                chf : 'bg,s,FFFFFF00',
                chxt : 'x,y,r,t', //Leyendas a desplegar
                chxl : [//Leyendas
                ['leyendas inferiores'], ['leyendas izquierda'], ['leyendas derecha'], ['leyendas superior']],
                chxp : [//Posición de leyendas
                ['posición leyendas inferiores'], ['posición leyendas izquierda'], ['posición leyendas derecha'], ['posición leyendas superior']],
                chbh : 'a,10,10', //Ancho y separación entre barras
                chs : null, //Tamaño
                chco : [], //Color de la serie
                chd : [[]], //Datos
                chg : [0, 0, 0, 0], //Posición de las lineas
                chxr : [[0, 100], [0, 100], [0, 100], [0, 100]],
                chds : []
            };
            var dataRows = t.getElements('tbody tr');
            var row = null;
            var numbRows=0;
            for(var rowPosition = 0; row = dataRows[rowPosition]; rowPosition++) {
                var rowValues = row.getElements('td').get('html');
                data.push(rowValues);
                numbRows=rowPosition+1;
            }
            chartOptions.chco = dataRows.getStyle('background-color').join(',');
            chartOptions.chco = chartOptions.chco.replace(/(\#+)/g, '');
            if(options.maxValue == undefined && options.totalMax != undefined) {
                if( typeof (options.totalMax.value) == 'string') {
                    options.totalMax.value = t.getElements(options.totalMax.value).get('html').invoke('toInt')[0];
                }
                maxValue = Math.ceil(options.totalMax.value / options.totalMax.divisor);
            } else {
                maxValue = dataRows.length * options.maxValue;
            }
            var rigthLine = maxValue;
            chartOptions.chg[1] = maxValue;
            maxValue += Math.ceil(maxValue / dataRows.length);
            for(var i=0;i<numbRows;i++){
                chartOptions.chds.push(0);
                chartOptions.chds.push(maxValue);
            }
            chartOptions.chxt = Object.keys(options.axes).join(',');
            if(options.axes.x != undefined) {
                chartOptions.chxl[0] = '0:|' + t.getElements(options.axes.x).get('html').join('|');
                chartOptions.chxr[0] = null;
                //[0, 0, maxValue].join(',');
                chartOptions.chxp[0] = null;
            } else {
                chartOptions.chxl[0] = null;
            }
            if(options.axes.y != undefined) {
                //var tmp = data.map(arrayEncode, maxValue);
                //chartOptions.chxl[1] = NULL;
                chartOptions.chxr[1] = null;
                chartOptions.chxp[1] = null;
            } else {
                chartOptions.chxr[1] = [1, 0, maxValue].join(',');
                chartOptions.chxl[1] = null;
                chartOptions.chxp[1] = null;
            }
            if(options.axes.r != undefined) {
                chartOptions.chxl[2] = '2:|' + rigthLine + ' ' + t.getElements(options.axes.r)[0].get('html').clean();
                //chartOptions.chxr[2] = [2, 0, rigthLine].join(',');
                chartOptions.chg[1] = Math.ceil((rigthLine / maxValue) * 100);
                chartOptions.chxp[2] = '2,' + chartOptions.chg[1];
            } else {
                chartOptions.chxp[2] = null;
                chartOptions.chxr[2] = null;
                chartOptions.chxl[2] = null;
            }
            if(options.axes.t != undefined) {
                chartOptions.chxl[3] = '3:|' + t.getElements(options.axes.t)[0].get('html').clean();
            } else {
                chartOptions.chxp[3] = null;
                chartOptions.chxr[3] = null;
                chartOptions.chxl[3] = null;
            }
            chartOptions.chxl = chartOptions.chxl.clean().join('|');
            chartOptions.chxr = chartOptions.chxr.clean().join('|');
            chartOptions.chxp = chartOptions.chxp.clean().join('|');
            chartOptions.chs = Math.min(size.x, 480);
            chartOptions.chs += 'x' + Math.ceil((Math.min(size.x, 480) / 4) * 3);//alert(arrayEncode);
            chartOptions.chd = 't:' + data.join('|');
            chartOptions.chg = chartOptions.chg.join(',');
            chartOptions.chds = chartOptions.chds.join(',');
            var src = chartsrc + Object.toQueryString(chartOptions);
            t.addClass(this.options.hiddenClass);
            new Element('img', {
                src : src,
                alt : title,
                title : title,
                'class' : 'barChart'
            }).inject(t, 'before');
            t.dispose();
        }// end for
    }
});
