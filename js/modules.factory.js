'use strict';


/**
 *            fetchdata -> init -> resetTable -> reload --> updateAllSelectedModule
 * 			  removeModule -> removePlannedModule
 * 						   -> updateAllSelectedModule --> saveAllSelectedModule
 * 			  addModule -> addPlannedModule
 * 						-> updateAllSelectedModule --> saveAllSelectedModule
 *   		  changeStateModule -> addPlannedModule, removePlannedModule
 **/

angular.module('core').factory('Modules', ['$http', '$cookies', 
		function ($http, $cookies) {
			var service = {};

			service.plannedModules = [];

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

			/**
			 **/
			service.saveSelectedModulesToCookies = function () {
				/* Data to be saved is the json string of currently visible modules */
				var data = JSON.stringify(service.visibleModules['ALL']);

				/* Set cookie's expire date which is ten years from now */
				var expireDate = new Date();
				expireDate.setDate(expireDate.getDate() + 10 * 365);

				$cookies.put('data', data, { expires: expireDate });
			};

			/**
			 * visibleModules is actually selected modules
			 * This function calls saveSelectedModulesToCookies to save data as cookies
			 **/
			service.updateAllSelectedModules = function () {
				/* Reset both selectedModules and planned Modules to empty */
				service.visibleModules['ALL'] = [];
				service.plannedModules.splice(0, service.plannedModules.length);

				for(var type in service.types) {
					if (type === 'ALL') continue;

					for(var i in service.visibleModules[type]) {
						/* For all modules added */
						var module = service.visibleModules[type][i];

						/* Add selected module */
						service.visibleModules['ALL'].push(module);

						/* Add planned modules */
						if (module.state === 'planned') {
							service.plannedModules.push(module);
						}
					}
				}

				service.saveSelectedModulesToCookies();
			};


			/**
			 * These two functions change the state of a selected module from planned to taken
			 * The selected module also needs to be added, removed from plan table
			 **/
			service.addPlannedModule = function (module) {
				module.state = 'planned';
				
				if (service.addPlannedModuleToPlanTable) {
					service.addPlannedModuleToPlanTable(module);
				}
			}

			service.removePlannedModule = function (module) {
				module.state = 'taken';
				
				if (service.removePlannedModuleFromPlanTable) {
					service.removePlannedModuleFromPlanTable(module);
				}
			}

			/**
			 * Search module by type and mod code and then add to selected modules
			 * This function calls added() to avoid duplicate module
			 * Default state of new module is planned
			 * Origin specifies how this function is called:
			 *	- auto: called automatically when extracting info from cookies
			 *	- manu: called manually by user
			 * When the function is called automatically, this module must not be added to plan table
			 * Total MCs is updated also
			 * Update all selected modules afterward
			 **/
			service.addModule = function (modType, modCode, origin) {
				for(var i in service.modules) {
					var module = service.modules[i];

					if ((module.type === modType) && (module.code === modCode)) {
						/* Make sure this module has not been added before */
						if (!added(module)) {

							/* Update total MCs */
							service.totalMCs[modType] += module.mc;
							service.visibleModules[modType].push(module);

							if ((!origin) || (origin !== 'auto')) {
								service.addPlannedModule(module);
							}
							module.state = 'planned';
						}
					}
				}

				service.updateAllSelectedModules();
			}

			/**
			 * Load data from cookies
			 **/
			service.reload = function () {
				var data = $cookies.get('data');
				var plan = $cookies.get('plan');

				if (data) {
					data = JSON.parse(data);

					for(var i in data) {
						var module = data[i];
						var cmd = 'auto';

						/* If these modules are not saved in plan cookies, then add them to plan table */
						if (!plan) cmd = 'manu';

						service.addModule(module.type, module.code, cmd);
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

			/**
			 * This functions resets all arrays to empty
			 * It also calls to resetTable to create empty lists and totalMC variable for each type
			 * Then reload is called to load data from cookies
			 **/
			service.init = function () {
				// Visible Modules = selected Modules
				service.visibleModules = {};
				service.resetTable();

				service.reload();
			};

			/**  not sure if this procedure is still needed
			 * Switch type of modules to be displayed
			 **/
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

			/**
			 * Remove module by modType and modCode
			 * MC is also updated
			 **/
			service.removeModule = function (modType, modCode) {
				for(var i in service.visibleModules[modType]) {
					var module = service.visibleModules[modType][i];

					if (module.code === modCode) {
						service.totalMCs[modType] -= module.mc;
						service.visibleModules[modType].splice(i, 1);

						// Mark this module as unselected
						service.removePlannedModule(module);
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
							service.addPlannedModule(module);
						} else {
							service.removePlannedModule(module);
						}
					}
				}
			};

			/**
			 *  Randomly choosing type for module
			 **/
			var pickType = function (s) {
				if (s.search('CS') != -1) return 'PR';
				else if (s.search('MA') != -1) return 'UE';
				else return 'ULR';
			};

			/**
			 * Reformat input to make sure data from server is usable by front-end
			 **/
			service.preprocess = function (input) {
				var data = [];

				for(var i in input) {
					var module = input[i];
					module.code = i;
					data.push({
						code: i,
						type: pickType(module.code),
						title: module.ModuleTitle,
						mc: module.ModuleCredit,
						semester: module.Semester,
						prerequisites: module.Prerequisites
					});
				}

				return data;
			};

			/**
			 *  The first procedure to be called after website is loaded
			 *  It fetches the list of all modules from server and also initializes values of this factory
			 **/
			service.fetchData = function (admissionYear, major, callback) {
				$http({
					method: 'GET',
					url: '/main/data/simplified.json',
					params: {
						adm_year: admissionYear,
						major: major,
						cmd: 'getreq'
					}
				}).then(function successCallback(res) {

					var data = service.preprocess(res.data);

					service.modules = data;

					service.init();

					if (callback) {
						callback(data);
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
