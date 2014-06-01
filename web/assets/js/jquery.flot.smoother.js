
/*
 * Written by Madison Williams
 * madisonwilliams.net
 */

(function($) {
	var bar,
		handle,
		handle_distance,
		h_offset,
		l_boundary,
		r_boundary,
		handle_width;
	function init(plot) {
		var placeholder = plot.getPlaceholder(),
			phwidth = $(placeholder).width();
		$("#smoother_"+placeholder[0].id).remove()
		$(placeholder).after("<div id='smoother_"+placeholder[0].id+"' class='smoother'><div class='smoother_handle'></div></div>");
		bar = $(".smoother");
		handle = $(".smoother_handle");
		handle_distance = $(bar).offset().left;
		handle_width = Math.floor(phwidth / 20);
		
		l_boundary = 0;
		r_boundary = (phwidth - handle_width);
		
		$(bar).css({
			width : phwidth+"px",
			height: "30px",
			border : "2px #999 solid",
			backgroundColor : "#fefefe"
		})
		
		$(handle).css({
			width : handle_width+"px",
			height: "28px",
			top : "0px",
			position  : "relative",
			"-moz-border-radius" : "5px",
			border : "1px #999 solid",
			left : "1px",
			backgroundColor : "#CCC"
		})
		function bind() {
			var min=handle_distance + (handle_width/2),
				max=handle_distance + (phwidth),
				scale = max-min,
				firstrun = true,
				percentage,
				divval,
				orig_points,
				max_smooth,
				oldObj=[]
				smoothObj=[],
				newData=[];
				
			function smoothit(intensity) {
				var oLeft,
					oRight,
					oArr,
					oData,
					diff;
				divval = scale / intensity;
				percentage = (Math.floor(100 / divval)) / 100;
				$(oldObj).each(function(ind) {
					
					newData[ind] = {
						data : [], 
						label : oldObj[ind].label,
						yaxis : oldObj[ind].yaxis
					};
					
					oData = oldObj[ind].data;
					$(oData).each(function(index) {
						newData[ind].data[index] = [
							oldObj[ind].data[index][0] + (smoothObj[ind].data[index][0] - oldObj[ind].data[index][0]) * percentage,
							oldObj[ind].data[index][1] + (smoothObj[ind].data[index][1] - oldObj[ind].data[index][1]) * percentage
						]
					})
				})
				plot.setData(newData);
				plot.draw()
				
			}
			function draggable() {
				var oLeft,
					boxPos;
				$(document).mousemove(function(e) {
					boxPos = (e.pageX - handle_distance) - handle_width / 2;
					if (boxPos > l_boundary && boxPos < r_boundary) {
						$(handle).css({
							left : boxPos
						})
						smoothit(e.pageX)
					}
					
					
				})
			}
			
			function draggableoff() {
				$(document).unbind("mousemove");
			}
			
			$(bar).mousedown(function(e) {
				e.preventDefault()
				if (firstrun) {
					//create the "perfectly straight" input object
					$(plot.getData()).each(function(index) {
						oldObj.push({data : this.data, label : this.label, yaxis : this.yaxis.n}) //old input object
						var that = this;
						max_smooth = (function() {
							var arr=[],
								points = that.data,
								oLeft = points[0][0],
								oRight= points[0][1],
								p_len = points.length,
								f_left = points[0][0],
								f_right = points[0][1],
								l_left =  points[p_len-1][0],
								l_right = points[p_len-1][1],
								left_inc = (l_left - f_left) / p_len,
								right_inc = (l_right - f_right) / p_len;
							$(points).each(function(index) {
								arr[index] = [oLeft,oRight]
								oLeft+=left_inc;
								oRight+=right_inc;
							})
							return arr;
						}());
						smoothObj[index] = {data : max_smooth, label : this.label, yaxis : this.yaxis.n}
					})
				firstrun=false;
				}
				draggable();
			})
			
			$(document).mouseup(function(e) {
				draggableoff();
			})
		}
		bind()
	}
	$.plot.plugins.push({
		init : init,
		options: {},
		name : "smoother",
		version : "0.1"
	})
}(jQuery))
