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
					var selectedModule = $item.originalObject;

					scope.select(selectedModule.type, selectedModule.code);
				}
			}
		}
	}
]);
