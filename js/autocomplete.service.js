'use strict';

angular.module('core').service('Autocomplete', ['$http',
	function ($http) {
		var service = {};
		service.suggestions = [];
		service.items = [];

		service.unhighlightAll = function () {
			for(var i in service.suggestions) {
				var suggestion = service.suggestions[i];

				suggestion.state = 'normal';
			}
		}

		// highlight this suggestion and unhighlight others
		var highlight = function () {
			service.unhighlightAll();

			this.state = 'highlighted';
		}

		var unhighlight = function () {
			// unhighlight this and highlight the first one instead
			if (service.suggestions && service.suggestions.length) {
				service.suggestions[0].highlight();
			}
		}

		var select = function () {
			console.log(this.value);
		}

		// Find the set of items that match user's input
		var getClosestMatch = function (pattern) {
			var matches = [];

			if (pattern === '') return matches;

			for(var i in service.items) {
				var item = service.items[i];
				var code = item.code.toUpperCase(), title = item.title.toUpperCase();

				// Check if module's code and title match user's input
				if (code.search(pattern) === 0) {
					matches.push({ key: 0, value: item });
				} else if (title.search(pattern) === 0) {
					matches.push({ key: 1, value: item })
				} else if ((code.search(pattern) !== -1) || (title.search(pattern) !== -1)) {
					matches.push({ key: 2, value: item });
				}
			}

			// suggestion with lower key will be put on top
			matches.sort(function (x, y) { return x.key - y.key; });

			return matches;
		};

		service.init = function (items) {
			service.items = items;
		};

		// update suggestions by user's input
		service.update = function(pattern) {
			service.suggestions = [];

			var results = getClosestMatch(pattern.toUpperCase());

			if (results && results.length) {
				for(var i in results) {
					var result = results[i].value;

					service.suggestions.push({
						state: 'normal',
						value: result.code.toUpperCase() + ' ' + result.title.toUpperCase(),
						highlight: highlight,
						unhighlight: unhighlight
					});
				}
			} 

			// Default highlight, highlight the first one
			if (service.suggestions && service.suggestions.length) {
				service.suggestions[0].highlight();
			}

			return results;
		}

		return service;
	}
]);
