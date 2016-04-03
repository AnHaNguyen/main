'use strict';

angular.module('core', ['angucomplete-alt', 'ngCookies']);

angular.module('core').controller('mainController', [ '$scope', '$cookies', 'Modules',
	function($scope, $cookies, Modules) { 
		$scope.selected = '';

		$scope.modulesController = Modules;

		$scope.initModules = function (admissionYear, major) {
			Modules.fetchData(admissionYear, major, function (data) {
				$scope.modules = data;
				console.log('new >> ' , data);
			});
		};

		// Function to add new module
		$scope.addModule = Modules.addModule;

		// Function to submit list of taken modules
		$scope.submit = function () {
			Modules.submit($scope.admissionYear, $scope.major, $scope.focusArea, function (data) {
				console.log(data);
			});
		};

		$scope.initModules(1415, 'CS');

		$scope.displayMajor = '';
		$scope.displayFocusArea = '';
		$scope.displayAdmissionYear = '';

		$scope.setInfo = function (major, focusArea, admissionYear) {
			$scope.major = major;
			$scope.focusArea = focusArea;
			$scope.admissionYear = admissionYear;

			$scope.displayMajor = $scope.major;
			$scope.displayFocusArea = $scope.focusArea;
			$scope.displayAdmissionYear = $scope.admissionYear;

			$scope.initModules($scope.admissionYear, $scope.major);
		};

		// Find user's info in cookies
		var infoUser = $cookies.get('info');

		if (infoUser) {
			$scope.setInfo(infoUser.major, infoUser.focusArea, infoUser.admissionYear);
		}

		// Update user's info
		$scope.updateInfo = function () {
			$scope.setInfo($scope.major, $scope.focusArea, $scope.admissionYear);
		};
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

