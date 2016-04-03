'use strict';

angular.module('core', ['angucomplete-alt', 'ngCookies']);

angular.module('core',[]).controller('mainController', [ '$scope', '$cookies', 'Modules',
	function($scope, $cookies, Modules) { 
		$scope.username = '';

		$scope.initModules = function (admissionYear) {
			Modules.init(admissionYear, function (data) {
				$scope.modules = data;
			});
		};

		$scope.addModule = function () {};

		$scope.initModules();
	}
]);

angular.module('core',[]).controller('xyController', [ '$scope',
	function ($scope) {
		$scope.Input = {
			username: $scope.full_name,
			bachelor: $scope.bachelor,
			major: $scope.major,
			focus_area: $scope.focus_area,
			admission_year: $scope.admission_year
		};

		/*"full-name", "bachelor", "major", "focus_area", "admission_year"*/

		$scope.login = function (Input) {
			User.login(Input.username, Input.bachelor, Input.major, Input.focus_area, Input.admission_year);
		};

		$scope.confirm = function() {
			$scope.login($scope.Input);
		}
	}
]);

