'use strict';

// This directive controls search input
angular.module('core').directive('search', [
	function () {
		return {
			restrict: 'E',
			templateUrl: 'html/search.html',
			scope: {
			},
			link: function(scope, element, attrs) {
			}
		}
	}
]);
