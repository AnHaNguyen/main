'use strict';

// This directive controls search input
angular.module('core').directive('search', [
	function () {
		return {
			restrict: 'E',
			templateUrl: 'html/search.html',
			scope: {
				select: '=select',
				items: '=items'
			},
			link: function(scope, element, attrs) {
				scope.suggestInput = '';
				scope.input = '';
				scope.focusInput = false;

				// Find the item that is closest to user's input
				var getClosestMatch = function (pattern) {
					// Search through the list of items
					for(var i in scope.items) {
						var item = scope.items[i];
						var code = item.code.toUpperCase(), title = item.title.toUpperCase();

						// Check if module's code and title match user's input
						if ((code.search(pattern) === 0) || (title.search(pattern) === 0)) {
							return item;
						}
					}

					return null;
				};

				// Autocomplete
				scope.updateSuggestInput = function () {
					if (!scope.input) {
						scope.suggestInput = '';
						return;
					}

					var suggestion = getClosestMatch(scope.input.toUpperCase());

					if (suggestion) {
						scope.suggestInput = suggestion.code.toUpperCase() + ' ' + suggestion.title.toUpperCase();
					} else {
						scope.suggestInput = '';
					}
				}

				scope.resetInput = function () {
					scope.input = '';
					scope.suggestInput = '';
				}

				scope.enterInput = function () {
					var selection = getClosestMatch(scope.input.toUpperCase());

					if (selection) {
						scope.select(selection.type, selection.code);
					}

					// Reset input after enter
					scope.resetInput();
				}

				scope.keyup = function (event) {
					switch (event.which) {
						case 13: // Enter 
							scope.enterInput();
							event.preventDefault();
							break;
						case 27: // Escape
							scope.resetInput();
							event.preventDefault();
							break;
						default: // Any key
							scope.updateSuggestInput();
					}
				}
			}
		}
	}
]);
