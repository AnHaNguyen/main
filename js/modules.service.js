'use strict';

angular.module('core').service('Modules', ['$http', '$cookies',
		function ($http, $cookies) {
			var service = {};

			// Module fields
			service.fields = ['Order', 'Type', 'Code', 'Title'];

			// Module types
			service.types = {
				'ULR': 'University Level Requirements',
				'UE' : 'Unrestricted Electives',
				'PR': 'Programme Requirements',
				'ALL': 'All modules'
			};

			service.saveSelectedModulesToCookies = function () {
				var data = JSON.stringify(service.visibleModules['ALL']);

				// Expire date is ten years from now
				var expireDate = new Date();
				expireDate.setDate(expireDate.getDate() + 10 * 365);

				var cookieOption = {
					expires: expireDate
				}; 

				$cookies.put('data', data, cookieOption);
			};

			// Update visibleModules.all
			service.updateAllSelectedModules = function () {
				service.visibleModules['ALL'] = [];

				for(var type in service.types) {
					if (type === 'ALL') continue;

					for(var i in service.visibleModules[type]) {
						var module = service.visibleModules[type][i];

						service.visibleModules['ALL'].push(module);
					}
				}

				service.saveSelectedModulesToCookies();
			};

			// Add modules
			service.addModule = function (modType, modCode) {
				for(var i in service.modules) {
					var module = service.modules[i];

					if ((module.type === modType) && (module.code === modCode)) {
						if (!added(module)) {
							service.visibleModules[modType].push(module);
						}
					}
				}

				service.updateAllSelectedModules();
			}

			// Load saved data from cookie
			service.reload = function () {
				var data = $cookies.get('data');

				if (data) {
					data = JSON.parse(data);

					for(var i in data) {
						var module = data[i];

						service.addModule(module.type, module.code);
					}
				}
			};

			// Reset table, remove all modules 
			service.resetTable = function () {
				service.visibleModules = {};

				for(var type in service.types) {
					service.visibleModules[type] = [];
				}

				service.selectedType = 'ALL';
			};

			// Init moduletable if module list is changed
			service.init = function () {
				// Visible Modules = selected Modules
				service.visibleModules = {};
				service.resetTable();

				service.reload();
			};

			// Switch type of modules to be displayed
			service.switchType = function (type) {
				service.selectedType = type;
			};

			// Check if this module is alread added to visible list
			var added = function (module) {
				for(var i in service.visibleModules['ALL']) {
					if (service.visibleModules['ALL'][i] === module) {
						return true;
					}
				}

				return false;
			};

			// Remove modules
			service.removeModule = function (modType, modCode) {
				for(var i in service.visibleModules[modType]) {
					var module = service.visibleModules[modType][i];

					if (module.code === modCode) {
						service.visibleModules[modType].splice(i, 1);
					}
				}

				service.updateAllSelectedModules();
			};

			service.fetchData = function (admissionYear, callback) {
				$http({
					method: 'GET',
				url: '/main/php/getmodules.php',
				params: {
					ay: admissionYear
				}
				}).then(function successCallback(res) {

					service.modules = res.data;
					service.init();

					if (callback) {
						callback(res.data);
					}
				}, function errorCallback(err) {
					console.log('ERROR: Getting modules - ' + err);
				});
			};

			return service;
		}
]);
