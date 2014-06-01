(function ($) {
    function init(plot) {
	var datas = {};
	var plot = plot;

	var updateInterval;
	var totalPoints;
	var pointsRemaining;

	var params = {}
	var ajax_args;

	var processingRequest = false;

	function noData(jqXHR, textStatus, errorThrown) {
	    processingRequest = false;
	}

	function updateData(resp) {
	    processingRequest = false;
	    if (plot == null)
		plot = $.plot(doc, [  ], options);
	    d = resp;
	    
	    for(key in d) {
		if (datas.hasOwnProperty(key)) {
		    datas[key] = datas[key].concat(d[key]);
		} else {
		    datas[key] = d[key];
		}
			
		if (datas[key].length > totalPoints) {
		    datas[key] = datas[key].slice(datas[key].length - totalPoints );
		}
	    }
	    data = [];
	    for (key in datas) {
			data.push({'label' : key,
				    'data' : datas[key]});
	    }
	    
	    plot.setData(data);
	    plot.setupGrid();
	    plot.draw();
	}

	function update(first) {
	    setTimeout(update, updateInterval);	   

	    pointsRemaining++;
	    params.totalPoints = pointsRemaining;

	    if (!processingRequest) {
		try {
		    pointsRemaining = 0
		    processingRequest = true;
		    ajax_args.data = params;

		    $.ajax(ajax_args);

		} catch (err) {
		    processingRequest = false;
		}
	    }
	}


	function processOptions(plot, options) {
	    if (options.updater) {
		updater = options.updater;
		updateInterval = updater.updateInterval;
		totalPoints = updater.totalPoints;

		ajax_args = updater.ajax;
		ajax_args.success = updateData;
		ajax_args.error = noData;
		ajax_args.dataType = 'json';
		if (ajax_args.data) {
		    params = ajax_args.data;
		}
		    

		pointsRemaining = totalPoints;
		params.updateInterval = updateInterval;

		update( 1);
	    }
	}
	plot.hooks.processOptions.push(processOptions);
    }
    
    var options = { updater: 0 };
    
    $.plot.plugins.push({
	    init: init,
		options: options,
		name: "updater",
		version: "0.1"
		});
})(jQuery);