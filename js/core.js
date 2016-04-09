'use strict';

angular.module('core', ['angucomplete-alt', 'ngCookies', 'ui.sortable']);

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

angular.module('core').controller('planController', [ '$scope', 'Modules', '$cookies',
	function ($scope, Modules, $cookies) {
		/* Create clone of modules factory */
		$scope.initModules = function (admissionYear, major) {
			Modules.fetchData(admissionYear, major, function (data) {
				$scope.modules = data;
				console.log('plan controller >> ' , data);
			});
		};

		// Remove module
		$scope.removeModule = Modules.removeModule;

		// Change state of module from planned to taken and vice versa
		$scope.changeState = Modules.changeState;

		// Function to add new module
		$scope.addModule = Modules.addModule;

		$scope.initModules(1, 1);

		/* End */

		// CHeck the cookie first
		var plan = $cookies.get('plan');

		$scope.save = function () {
			// Update by save it to cookies
			var plan = JSON.stringify($scope.semester);

			// Expire date is ten years from now
			var expireDate = new Date();
			expireDate.setDate(expireDate.getDate() + 10 * 365);

			var cookieOption = {
				expires: expireDate
			}; 

			$cookies.put('plan', plan, cookieOption);
		};

		if (plan) {
		/* BRANCH: Plan cookies found */
			var plan = JSON.parse(plan);

			$scope.semester = plan;
		} else {
		/* BRANCH: Plan cookies not found */
			$scope.semester = [ [], [], [], [] ];
		}

		$scope.addPlannedModule = function (module) {
			$scope.semester[0].push(module);
			$scope.save();
		};

		$scope.removePlannedModule = function (mod) {
			for(var s in $scope.semester) {
				var semester = $scope.semester[s];

				for(var i in semester) {
					var module = semester[i];

					if (module.code === mod.code) {
						semester.splice(i, 1);
						$scope.save();
						return;
					}
				}
			}
		};

		Modules.removePlannedModuleFromPlanTable = $scope.removePlannedModule;
		Modules.addPlannedModuleToPlanTable = $scope.addPlannedModule;

		/* Configuration for sortable angularjs */
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
			console.log($scope.semester);
		};
	}
]);

