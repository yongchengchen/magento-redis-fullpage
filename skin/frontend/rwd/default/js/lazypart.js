var g_lazy;
if (!String.format) {
  String.format = function(format) {
    var args = Array.prototype.slice.call(arguments, 1);
    return format.replace(/{(\d+)}/g, function(match, number) {
      return typeof args[number] != 'undefined' ? args[number] : match ;
    });
  };
}

(function($) {
	$.LazyPart = function(){
		function isArray(obj) {
			if (typeof obj =="undefined") return false;
			return obj.constructor.toString().indexOf("Array") > -1;
		}
		var app = {};
		app.events = [];
		app.eventtags = [];  //when event is triggerd, store the value
		app.data = [];
		app.jsons =[];

		app.ajaxsubmitform = function(form, fsucc, ferr){
			if (typeof ferr == "undefined"){
				ferr=function(){};
			}
			$.ajax({type:$(form).attr("method"), url:$(form).attr("action"),
				data:new FormData($(form)),dataType: "json",
				success:fsucc, error:ferr});
		}
		
		app.addEvent = function(watchOn, callback){
			if (!isArray(app.events[watchOn])){
				app.events[watchOn] = [] ;
			}
			var val = app.getData(watchOn);
			if (typeof val !="undefined"){
				app.eventtags[watchOn+app.events[watchOn].length.toString()] = val;
				callback(val);
			}
			app.events[watchOn][app.events[watchOn].length] = callback;
		}
		app.trigEvent = function(key){
			if (!isArray(app.events[key])){
				return;
			}
			funcs = app.events[key];
			if (isArray(funcs)){
				var len = funcs.length;
				for(var i=0;i<len;i++){
					var tagKey = key+i.toString();
					if (typeof funcs[i] == 'function' 
						&& app.eventtags[tagKey] != app.getData(key)) { 
						funcs[i](app.getData(key));
						app.eventtags[tagKey] = app.getData(key);
					}
				}
			}
		}
		app.getData = function(key){
			return app.data[key];
		}
		app.setData = function(key, val){
			app.data[key] = val;
			app.trigEvent(key);
			app.dataMapping(key);
		}

		app.dataMapping = function(key){
			$("[data-map='"+ key +"']").each(function(){
				if ("INPUT" == $(this)[0].tagName){
					$(this).val(app.getData(key));
				} else {
					$(this).html(app.getData(key));
				}
			});
		}

		app.atomicInc=0;
		function partloaded(callback){
			if (typeof callback !="undefined"){ callback(app); }
			app.atomicInc--;
			if(app.atomicInc == 0){
				app.initModel();
			}
		}
		app.load = function(url,tgt,callback){
			if (typeof tgt == "undefined"){tgt="body";}
			app.atomicInc++;
			$.get(url,function(data){$(tgt).append(data);partloaded(callback);});
		}
		app.replace = function(url,tgt,callback){
			app.atomicInc++;
			if (url.indexOf("_redis_no_cache")>0){
				app.atomicInc--;
			}
			$.get(url,function(data){$(tgt).replaceWith(data);partloaded(callback);});
		}

		app.pageElementMap=function(){
			$("[mt-controller][mt-mapel]").each(function(){
				var mapel = $(this).attr("mt-mapel");
				var selector ="[mt-controller="+$(this).attr("mt-controller")+"][mt-mapel] [mt-model]";
				$(selector).each(function(){
					var mapobj = $(mapel + $(this).attr("mt-model"));
					var val = mapobj.val();
					if (typeof val !="undefined"){
					} else {
						val = mapobj.text();
					}
					if (typeof val =="undefined"){ val=""};

					if (typeof $(this).attr("mt-model-more")!="undefined"){
					var more = $(mapel + $(this).attr("mt-model-more"));
					if (typeof more !="undefined"){
						if (typeof more.val() !="undefined"){val = val + " " +more.val();}
						else{
							if(typeof more.text() !="undefined"){val = val +" "+ more.text();}
						}
					}
					}
					if (typeof val !="undefined"){
						if ("INPUT" == $(this)[0].tagName){
							$(this).val(val);
						} else {
							$(this).html(val);
						}
					}
				});
                        });
		}

		app.initModel = function(){
			$("[mt-controller][mt-src]").each(function(){
				var depend = $(this).attr("mt-depend");
				var controller = $(this).attr("mt-controller");
				if (typeof depend !="undefined" && depend !=""){
					app.addEvent(depend, function(val){
                                                app.updateController(controller, val);
                                        });
				} else {
					app.updateController(controller);
				}
			});
		}

		app.updateController = function(controller, dependval){
			var selector = "[mt-src][mt-controller="+controller+"] [mt-model]";
			var ctrlSel = "[mt-src][mt-controller="+controller+"]";
			var url = $(ctrlSel).attr("mt-src");
			if (typeof dependval !="undefined"){
				url = String.format(url, dependval);
			}
			function updateElement(data) {
				app.jsons[url] = data;
				if (data){

                                        $(selector).each(function(){
                                                var val = data[$(this).attr("mt-model")];
                                                if (typeof val !="undefined"){
                                                        if ("INPUT" == $(this)[0].tagName){
                                                                $(this).val(val);
                                                        } else {
                                                                $(this).html(val);
                                                        }
                                                }
                                        });
                                }
			}
			if (typeof app.jsons[url] == "undefined"){
				app.jsons[url] = [];
			}

			app.jsons[url][app.jsons[url].length] = selector;
			if (app.jsons[url].length == 1){
				$.getJSON(url,function(data){
					if (data){
						for(var i=0;i<app.jsons[url].length;i++){
							$(app.jsons[url][i]).each(function(){
								var val = data[$(this).attr("mt-model")];
								if (typeof val !="undefined"){
									if ("INPUT" == $(this)[0].tagName){
										$(this).val(val);
									} else {
										$(this).html(val);
									}
								}
							});
						}
					}
				});
			}
                }
		return app;
	};
	g_lazy = new $.LazyPart();
})(jQuery);
