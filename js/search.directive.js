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

				scope.enterInput = function ($item) {

					if ($item) {
						var selectedModule = $item.originalObject;

						if (selectedModule) {
							scope.select(selectedModule.type, selectedModule.code);
						}
					}
				}
			}
		}
	}
]);
