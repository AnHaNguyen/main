'use strict';

angular.module('core', ['angucomplete-alt', 'ngCookies']);

angular.module('core').controller('mainController', [ '$scope', '$cookies', 'Modules',
	function($scope, $cookies, Modules) { 
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

angular.module('core').controller('loginController', [ '$scope', 'User',
	function ($scope, User) {
		$scope.Input = {
			username: '',
			password: ''
		};

		$scope.login = function (Input) {
			User.login(Input.username, Input.password);
		};
	}
]);

