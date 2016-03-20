'use strict';

angular.module('core', []);

angular.module('core').controller('mainController', [ '$scope',
	function($scope) {
		$scope.msg = 'Hello';
	}
]);
