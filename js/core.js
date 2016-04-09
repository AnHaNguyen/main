'use strict';

angular.module('core', ['angucomplete-alt', 'ngCookies', 'ui.sortable', 'LocalStorageModule']);

angular.module('core').controller('mainController', [ '$scope', 'Modules', 'User', 
	function($scope, Modules, User) { 

		$scope.user = User;

		$scope.modulesController = Modules;

		$scope.initModules = function (admissionYear, major) {
			Modules.fetchData(admissionYear, major, function (data) {
				$scope.modules = data;
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

		$scope.user.reset = function (major, focusArea, admissionYear) {
			$scope.initModules(admissionYear, major);
		};

		$scope.user.init();
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

angular.module('core').controller('planController', [ '$scope', 'Modules', 'localStorageService',
	function ($scope, Modules, localStorageService) {
		/* Create clone of modules factory */
		$scope.initModules = function (admissionYear, major) {
			Modules.fetchData(admissionYear, major, function (data) {
				$scope.modules = data;
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
		var plan = localStorageService.get('plan');

		$scope.save = function () {
			// Update by save it to localStorage
			var plan = $scope.semester;

			localStorageService.set('plan', plan);
		};

		$scope.plannedMC = [];

		/** 
		 *  MC Counter in plan table
		 *  Recompute mc after a module is added, removed or moved
		 **/

		$scope.computePlannedMC = function () {
			for(var i in $scope.semester) {
				$scope.plannedMC[i] = 0;

				var sem = $scope.semester[i];

				for(var j in sem) {
					var mod = sem[j];

					$scope.plannedMC[i] += mod.mc;
				}
			}
		};

		if (plan) {
		/* BRANCH: stored plan found */
			$scope.semester = plan;

			$scope.computePlannedMC();
		} else {
		/* BRANCH: stored plan not found */
			$scope.semester = [ [], [], [], [] ];

			$scope.computePlannedMC();
		}

		$scope.addPlannedModule = function (module) {
			var clone = {
				code: module.code,
				title: module.title,
				mc: module.mc
			};
			$scope.semester[0].push(clone);
			$scope.save();

			$scope.computePlannedMC();
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

			$scope.computePlannedMC();
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

				$scope.computePlannedMC();
			}
		};

		$scope.log = function () {
			console.log($scope.semester);
		};
	}
]);
