'use strict';

angular.module('core', ['angucomplete-alt', 'ngCookies', 'ui.sortable']);

angular.module('core').controller('mainController', [ '$scope', '$cookies', 'Modules', 'Transport',
	function($scope, $cookies, Modules, Transport) { 
		$scope.selected = '';

		$scope.modulesController = Modules;

		Transport.plannedModules = Modules.plannedModules;

		$scope.initModules = function (admissionYear, major) {
			Modules.fetchData(admissionYear, major, function (data) {
				$scope.modules = data;
				console.log('new >> ' , data);
			});
		};

		// Remove module
		$scope.removeModule = Modules.removeModule;

		// Change state of module from planned to taken and vice versa
		$scope.changeState = Modules.changeState;

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

angular.module('core').controller('planController', [ '$scope', 'Transport', '$cookies',
	function ($scope, Transport, $cookies) {
		// CHeck the cookie first
		var plan = $cookies.get('plan');

		$scope.save = function () {
			// Update by save it to cookies
			var plan = JSON.stringify($scope.modules);

			// Expire date is ten years from now
			var expireDate = new Date();
			expireDate.setDate(expireDate.getDate() + 10 * 365);

			var cookieOption = {
				expires: expireDate
			}; 

			$cookies.put('plan', plan, cookieOption);
		};

		if (plan) {
			var plan = JSON.parse(plan);

			$scope.modules = plan;
		} else {
			$scope.modules = [ [], [], [], [] ];
		}

		$scope.addPlannedModule = function (module) {
			$scope.modules[0].push(module);
			$scope.save();
		};

		$scope.removePlannedModule = function (mod) {
			for(var s in $scope.modules) {
				var modules = $scope.modules[s];

				for(var i in modules) {
					var module = modules[i];

					if (module.code === mod.code) {
						modules.splice(i, 1);
						$scope.save();
						return;
					}
				}
			}
		};

		Transport.removePlannedModule = $scope.removePlannedModule;
		Transport.addPlannedModule = $scope.addPlannedModule;

		$scope.semester = [];
		$scope.semester[0] = $scope.modules[0];
		$scope.semester[1] = $scope.modules[1];
		$scope.semester[2] = $scope.modules[2];
		$scope.semester[3] = $scope.modules[3];

		$scope.sortingLog = [];

		$scope.sortableOptions = {
			placeholder: "modholder",
			connectWith: ".semester",
			tolerance: 'intersect',
			update: function () {
				$scope.save();
			}
		};

		$scope.log = function () {
			console.log($scope.modules);
		};
	}
]);
