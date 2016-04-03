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
			
			// Module subtypes
			service.subtypes = {
				'ULR': [{
					name: 'GEM A',
					fulfilled: false
				}, {
					name: 'GEM B',
					fulfilled: false
				}, {
					name: 'SSA',
					fulfilled: false
				}],
				'PR': [{
					name: 'CS - FOUNDATION',
					fulfilled: false
				}, {
					name: 'CS - BREADTH & DEPTH',
					fulfilled: false
				}],
				'UE': []
			};
			console.log(service.subtypes);

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

						// Update fulfilled subtypes
						/*for(var type in service.types) {
							for(var subtype in service.types[type]) {

							}
						} */
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
							service.totalMCs[modType] += module.mc;
							service.visibleModules[modType].push(module);

							module.state = 'planned';
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
				service.totalMCs = {};

				for(var type in service.types) {
					service.visibleModules[type] = [];
					service.totalMCs[type] = 0;
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
						service.totalMCs[modType] -= module.mc;
						service.visibleModules[modType].splice(i, 1);

						// Mark this module as unselected
						module.state = 'unselected';
					}
				}

				service.updateAllSelectedModules();
			};

			// Change state between unselected, planned, taken
			service.changeState = function (modType, modCode) {
				for(var i in service.visibleModules['ALL']) {
					var module = service.visibleModules['ALL'][i];

					if ((module.type === modType) && (module.code === modCode)) {
						console.log(modType, modCode);
						if (module.state === 'taken') {
							module.state = 'planned';
						} else {
							module.state = 'taken';
						}
					}
				}
			};

			service.fetchData = function (admissionYear, major, callback) {
				$http({
					method: 'GET',
					url: '/main/php/getmodules.php',
					params: {
						adm_year: admissionYear,
						major: major,
						cmd: 'getreq'
					}
				}).then(function successCallback(res) {

					service.modules = res.data;
					console.log('fetch>>', res.data);

					// Hardcode MC
					for(var i in res.data) {
						var module = res.data[i];

						module.mc = 4;
						module.state = 'unselected';
					}

					service.init();

					if (callback) {
						callback(res.data);
					}
				}, function errorCallback(err) {
					console.log('ERROR: Getting modules - ' + err);
				});
			};

			// Gather all selected modules and send it to back-end
			service.submit = function (admissionYear, major, focusArea, callback) {
				var modules = service.visibleModules['ALL'];
				var selectedModules = [];

				for(var i in modules) {
					var module = modules[i];

					selectedModules.push([module.code, module.type, '4']);
				}

				$http({
					method: 'GET',
					url: '/main/php/getrequirements.php',
					params: {
						adm_year: admissionYear,
						major: major,
						focus_area: focusArea,
						cmd: 'verify'
					}
				}).then(function successCallback(res) {

					if (callback) {
						callback(res.data);
					}
				}, function errorCallback(err) { 
					console.log('ERROR: Sending modules ' + err);
				});
			};

			return service;
		}
]);
