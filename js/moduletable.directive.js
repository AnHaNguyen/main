'use strict';

angular.module('core').directive('moduletable', ['$cookies',
	function ($cookies) {
		return {
			restrict: 'E',
			templateUrl: 'html/moduletable.html',
			scope: {
				modules: '=list',
				addModule: '=add'
			},
			link: function(scope, element, attrs) {

				// Module fields
				scope.fields = ['Order', 'Type', 'Code', 'Title'];

				// Module types
				scope.types = {
					'ULR': 'University Level Requirements',
					'UE' : 'Unrestricted Electives',
					'PR': 'Programme Requirements',
					'ALL': 'All modules'
				};

				scope.saveSelectedModulesToCookies = function () {
					var data = JSON.stringify(scope.visibleModules['ALL']);

					// Expire date is ten years from now
					var expireDate = new Date();
					expireDate.setDate(expireDate.getDate() + 10 * 365);

					var cookieOption = {
						expires: expireDate
					}; 

					$cookies.put('data', data, cookieOption);
				};

				// Update visibleModules.all
				scope.updateAllSelectedModules = function () {
					scope.visibleModules['ALL'] = [];

					for(var type in scope.types) {
						if (type === 'ALL') continue;

						for(var i in scope.visibleModules[type]) {
							var module = scope.visibleModules[type][i];

							scope.visibleModules['ALL'].push(module);
						}
					}

					scope.saveSelectedModulesToCookies();
				};

				// Add modules
				scope.addModule = function (modType, modCode) {
					for(var i in scope.modules) {
						var module = scope.modules[i];

						if ((module.type === modType) && (module.code === modCode)) {
							if (!added(module)) {
								scope.visibleModules[modType].push(module);
							}
						}
					}

					scope.updateAllSelectedModules();
				}

				// Load saved data from cookie
				scope.reload = function () {
					var data = $cookies.get('data');

					if (data) {
						data = JSON.parse(data);

						for(var i in data) {
							var module = data[i];

							scope.addModule(module.type, module.code);
						}
					}
				};

				// Reset table, remove all modules 
				scope.resetTable = function () {
					for(var type in scope.types) {
						scope.visibleModules[type] = [];
					}

					scope.selectedType = 'ALL';
				};

				// Init moduletable if module list is changed
				scope.init = function () {
					// Visible Modules = selected Modules
					scope.visibleModules = {};
					scope.resetTable();

					scope.reload();
				};

				scope.$watch(function () {
					return scope.modules;
				}, function (modules) {
					if (modules && modules.length) {
						// valid set of modules
						scope.init();
					}
				});

				// Switch type of modules to be displayed
				scope.switchType = function (type) {
					scope.selectedType = type;
				};

				// Check if this module is alread added to visible list
				var added = function (module) {
					for(var i in scope.visibleModules['ALL']) {
						if (scope.visibleModules['ALL'][i] === module) {
							return true;
						}
					}

					return false;
				};

				// Remove modules
				scope.removeModule = function (modType, modCode) {
					for(var i in scope.visibleModules[modType]) {
						var module = scope.visibleModules[modType][i];

						if (module.code === modCode) {
							scope.visibleModules[modType].splice(i, 1);
						}
					}

					scope.updateAllSelectedModules();
				};
			}
		}
	}
]);
