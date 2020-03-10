( function( $ ){
	ko.bindingHandlers.select2 = {
	    init: function (element, valueAccessor, allBindingsAccessor) {
		    console.log(allBindingsAccessor());
	        var obj = valueAccessor(),
	            allBindings = allBindingsAccessor(),
	            lookupKey = allBindings.lookupKey;
	        var select2 = $(element).select2(obj);
	        // Bind events
	        if ("on" in obj) {
	            for (var event in obj.on) {
	                select2.on(event, obj.on[event]);
	            }
	        }
	
	        if(allBindings.value){  // FIX no initial values
	            allBindings.value.subscribe(function (v) {
	                $(element).trigger('change');
	            });
	        }
	
	        if (lookupKey) {
	            var value = ko.utils.unwrapObservable(allBindings.value);
	            $(element).select2('data', ko.utils.arrayFirst(obj.data.results, function (item) {
	                return item[lookupKey] === value;
	            }));
	        }
	
	        // The selected binding is similar to the value binding, but it is in array format and preserves the objects
	        var selected = obj.selected;
	        if (selected) {
	            $(element).select2('data', ko.utils.unwrapObservable(selected));
	
	            select2.on('change', function (e) {
	                if (ko.isObservable(selected))
	                    selected($(element).select2('data'));
	            });
	        }
	
	        ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
	            $(element).select2('destroy');
	        });
	    },
	    update: function (element, valueAccessor, allBindingsAccessor) {
	        var allBindings = allBindingsAccessor();
	
	        // If we have a selected binding, update the select2 to reflect any changes (creates a dependency on the observable)
	        var selected = ko.utils.unwrapObservable(valueAccessor().selected);
	        if (selected) {
	            $(element).select2('data', selected);
	        }
	
	        $(element).trigger('change');
	    }
	};	
} )( jQuery );