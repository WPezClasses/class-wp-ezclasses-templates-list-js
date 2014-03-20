/*
 * Name: 			List.js ezFilters
 * GitHub: 			https://github.com/ezWebDevTools/list.js.ezfilters
 * Description:		Enhanced filter capabilites for List.js (http://Listjs.com)
 * Version:			0.5.0
 * Author:			Mark Simchock
 * Author URI:		http://chiefalchemist.com
 */
var listJSezFilters = function(objList, optsList) {

	var filterDefaults = {
		dataAll : 'all',
		classFilterButtons : 'listjs-filter',
		defaultDelimiter : ',',
		filterDelimiter : {}, // e.g., filter-name : ';',... - You can set the delimiter on a filter by filter basis
		stripTags : {} // e.g., filter-name : false,... - Tags will be stripped by default, if you don't want / need tags stipped USE stripTags for each filter you want unstripped
	};
	// merge the optsList and the defaults
	for(var key in filterDefaults) {
		if (typeof  optsList[key] === "undefined"){
			optsList[key] = filterDefaults[key];
		}
	}
	
	// listen for the filter button
	var filterButtons = document.getElementsByClassName(optsList.classFilterButtons);
	for( var x = 0; x < filterButtons.length; x++) {
		filterButtons[x].addEventListener( 'click', function(){
			// check for the necessary data attrs
			if (this.getAttribute("data-" + 'filtername') === null || this.getAttribute("data-" + this.getAttribute("data-" + 'filtername')) === null){
				return false;
			}
			// we're good. grab the data-
			fName = this.getAttribute("data-" + 'filtername').toLowerCase();
			fVal = this.getAttribute("data-" + fName).toLowerCase();	
			// now filter
			ezFilter(fName, fVal, optsList);
		});
	}
	
	function ezFilter(fName, fVal){
		// if all then "unfilter" (i.e., remove filter and show all)
		if ( fVal == optsList.dataAll){
			objList.filter();
			return true;
		}
		
		// does this filter have a custom filterDelimiter? if not, use the default.
		if (typeof optsList.filterDelimiter === 'undefined' || typeof optsList.filterDelimiter[fName] === 'undefined'){
			strDelimiter = optsList.defaultDelimiter;
		} else {
			strDelimiter = optsList.filterDelimiter[fName];
		}

		// let the filter magic begin
		objList.filter(function(item) {
			
			itemValues = item.values();
			filterItem = itemValues[fName];
			// if nothing then false it now
			if (filterItem == ''){
				return false
			} 
			// put the itemValues into an array
			var arrFilterItem = [];
			arrFilterItem = filterItem.split(strDelimiter);
			// to strip or not to strip?
			if (optsList.stripTags[fName] === false){
				for( var x = 0; x < arrFilterItem.length; x++) {
					arrFilterItem[x] = arrFilterItem[x].toLowerCase().trim();
				}
			} else {
				for( var x = 0; x < arrFilterItem.length; x++) {
					// Thank you: http://www.javascriptsource.com/snippets/remove-html-tags.html
					strFilterItem = arrFilterItem[x].replace(/&(lt|gt);/g, function (strMatch, p1){
						return (p1 == "lt")? "<" : ">";
					});
					var strFilterItemStripped = strFilterItem.replace(/<\/?[^>]+(>|$)/g, "");
					arrFilterItem[x] = strFilterItemStripped.toLowerCase().trim();
				}
			}			
			// is the filter value in the array
			indexOfTheFilter = arrFilterItem.indexOf(fVal);		
			if ( indexOfTheFilter == -1 ) { 
			   return false;
			} 
			return true;
		});
	}
}

document.addEventListener('DOMContentLoaded',function(){

	var optionsListJs = {
		wrapClass : localizeListJSezFilters.wrapClass,
		listClass : localizeListJSezFilters.listClass,
		valueNames : localizeListJSezFilters.valueNames,
		searchClass : localizeListJSezFilters.searchClass,
		sortClass : localizeListJSezFilters.sortClass,
		indexAsync : localizeListJSezFilters.indexAsync,
		page : localizeListJSezFilters.page,
		i : localizeListJSezFilters.i,
		plugins : localizeListJSezFilters.plugins,
		dataAll : localizeListJSezFilters.dataAll,
		classFilterButtons : localizeListJSezFilters.classFilterButtons,
		defaultDelimiter  : localizeListJSezFilters.defaultDelimiter,
		filterDelimiter : localizeListJSezFilters.filterDelimiter,
		stripTags : localizeListJSezFilters.stripTags
	};
	
	var newListJs = new List(optionsListJs.wrapClass, optionsListJs);
	var newListFilter = new listJSezFilters( newListJs, optionsListJs);
});