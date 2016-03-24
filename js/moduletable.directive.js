'use strict';

angular.module('core').directive('moduletable', [
	function () {
		return {
			restrict: 'E',
			templateUrl: 'html/moduletable.html',
			scope: {
				modules: '=list',
				addModule: '=add'
			},
			link: function(scope, element, attrs) {
				// Visible Modules, Modules in the list
				scope.visibleModules = {};

				// Module fields
				scope.fields = ['Order', 'Type', 'Code', 'Title'];

				// Module types
				scope.types = {
					'ULR': 'University Level Requirements',
					'UE' : 'Unrestricted Electives',
					'PR': 'Programme Requirements',
					'ALL': 'All modules'
				};

				// Reset table, remove all modules 
				scope.resetTable = function () {
					for(var type in scope.types) {
						scope.visibleModules[type] = [];
					}

					scope.selectedType = 'ALL';
				};

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

					scope.updateAllModules();
				};

				// Remove modules
				scope.removeModule = function (modType, modCode) {
					for(var i in scope.visibleModules[modType]) {
						var module = scope.visibleModules[modType][i];

						if (module.code === modCode) {
							scope.visibleModules[modType].splice(i, 1);
						}
					}

					scope.updateAllModules();
				};

				// Update visibleModules.all
				scope.updateAllModules = function () {
					scope.visibleModules['ALL'] = [];

					for(var type in scope.types) {
						if (type === 'ALL') continue;

						for(var i in scope.visibleModules[type]) {
							var module = scope.visibleModules[type][i];

							scope.visibleModules['ALL'].push(module);
						}
					}
				};

				scope.resetTable();
			}
		}
	}
]);
