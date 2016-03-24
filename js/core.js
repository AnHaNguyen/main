'use strict';

angular.module('core', ['angucomplete-alt']);

angular.module('core').controller('mainController', [ '$scope', 'Modules',
	function($scope, Modules) { 
		$scope.selected = '';
		$scope.initModules = function (admissionYear) {
			Modules.init(admissionYear, function (data) {
				$scope.modules = data;
			});
		};

		$scope.addModule = function () {};

		$scope.initModules();
	}
]);

