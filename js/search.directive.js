'use strict';

// This directive controls search input
angular.module('core').directive('search', [
	function () {
		return {
			restrict: 'EA',
			templateUrl: 'html/search.html',
			scope: {
				id: '@id',
				select: '=select',
				items: '=items',
				clearinput: '@clearinput',
				initvalue: '=init',
				titlefield: '@titlefield',
				minlength: '@minlength'
			},
			link: function(scope, element, attrs) {

				/* User did not provide clearinput, default is empty string */
				if (!scope.clearinput) {
					scope.clearinput= '';
				}

				/**
				 *  Autocomplete search
				 **/
				scope.search = function (pattern) {
					var limit = 20;
					var ans = [];

					/* Both pattern and text are converted to uppercase */
					pattern = pattern.toUpperCase();

					for(var i in scope.items) {
						var item = scope.items[i];

						var match = false;

						/* Limit the number of results as it may go up to 5000 */
						if (ans.length > limit) break;

						/* Check all fields in item */
						for(var field in item) {
							var value = item[field];

							/* Only search defined, string value */
							if (value && ((typeof(value)) === 'string')) {

								/* Both pattern and text are converted to uppercase */
								value = value.toUpperCase();

								if ((!pattern) || (value.search(pattern) != -1)) {
									match = true;
								}
							}

							/* item matched, no need to continue */
							if (match) break;
						}

						/* Item found, add to ans */
						if (match) {
							ans.push(item);
						}
					}

					return ans;
				};

				/**
				 *  This handle event when user submits input from search
				 */
				scope.enterInput = function ($item) {
					if ($item) {
						var selectedModule = $item.originalObject;

						if (selectedModule) {
							scope.select(selectedModule);
						}
					}
				};

				scope.log = function () {
					console.log('-->', scope.items, scope.minlength, typeof(scope.minlength));
				};
			}
		}
	}
]);
