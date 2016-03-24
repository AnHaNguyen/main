'use strict';

// This directive controls search input
angular.module('core').directive('search', ['Autocomplete',
	function (Autocomplete) {
		return {
			restrict: 'E',
			templateUrl: 'html/search.html',
			scope: {
				select: '=select',
				items: '=items'
			},
			link: function(scope, element, attrs) {
				scope.input = '';
				scope.focusInput = false;
				scope.autocomplete = Autocomplete;

				// Initiate autocomplete with items
				Autocomplete.init(scope.items);

				scope.$watch(function (scope) {
					return scope.items;
				}, function () {
					Autocomplete.init(scope.items);
				}, false);

				scope.updateSuggestInput = function () {
					Autocomplete.update(scope.input);
				}

				scope.resetInput = function () {
					scope.input = '';
					Autocomplete.update(scope.input);
				}

				scope.enterInput = function () {
					var selections = Autocomplete.update(scope.input);

					if (selections && selections.length) {
						var selection = selections[0].value;

						scope.select(selection.type, selection.code);
					}

					// Reset input after enter
					scope.resetInput();
				}

				scope.changeInput = function (newValue) {
					console.log(newValue);
					scope.input = newValue;
					scope.updateSuggestInput();
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
