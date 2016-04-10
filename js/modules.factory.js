'use strict';

/**
 *            fetchdata -> init -> resetTable -> reload --> updateAllSelectedModule 
 * 			  removeModule -> removePlannedModule
 * 						   -> updateAllSelectedModule --> saveAllSelectedModule
 * 			  addModule -> addPlannedModule
 * 						-> updateAllSelectedModule --> saveAllSelectedModule
 *   		  changeStateModule -> addPlannedModule, removePlannedModule
 * 			  updateAllSelectedModules -> saveAllSelectedModule
 * 									   --> getType
 * 			  Add Module --> Module is added to ['ALL'] --> getType --> Module is categorized into ['ULR', 'PR', 'UE']
 *  					 --> Module is added to planned modules
 **/

angular.module('core').factory('Modules', ['$http', 'localStorageService', 
		function ($http, localStorageService) {
			var service = {
				plannedModules: [],
				types: {},
				user: {}
			};

			// Module types
			service.types = {
				'ULR': 'University Level Requirements',
				'UE' : 'Unrestricted Electives',
				'PR': 'Programme Requirements',
				'ALL': 'All modules'
			};

			/**
			 **/
			service.saveSelectedModulesToCookies = function () {
				/* Data to be saved is the json string of currently visible modules */
				var data = service.visibleModules['ALL'];

				localStorageService.set('data', data);
			};

			/**
			 * visibleModules is actually selected modules
			 * This function calls saveSelectedModulesToCookies to save data in localStorage
			 **/
			service.updateAllSelectedModules = function () {
				/* Reset both selectedModules and planned Modules to empty */
				service.plannedModules.splice(0, service.plannedModules.length);

				for(var i in service.visibleModules['ALL']) {
					/* For all modules added */
					var module = service.visibleModules['ALL'][i];

					/* Add planned modules */
					if (module.state === 'planned') {
						service.plannedModules.push(module);
					}
				}

				/* get modules' types */
				service.getType();

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
			 *  Find module by module's type and code
			 **/
			function getModule(modCode) {
				for(var i in service.modules) {
					/* For all modules in the list */
					var module = service.modules[i];

					if (module.code === modCode) {
						/* found */
						return module;
					}
				}

				return null;
			};

			/**
			 * Search module by type and mod code and then add to selected modules
			 * This function calls added() to avoid duplicate module
			 * Default state of new module is planned
			 * Origin specifies how this function is called:
			 *	- auto: called automatically when extracting info from localStorage
			 *	- manu: called manually by user
			 * When the function is called automatically, this module must not be added to plan table
			 * Total MCs is updated also
			 * Update all selected modules afterward
			 **/
			service.addModule = function (modType, modCode, origin) {
				var module = getModule(modCode);

				/* Make sure this module has not been added before */
				if (!added(module)) {

					service.visibleModules['ALL'].push(module);

					if ((!origin) || (origin !== 'auto')) {
						service.addPlannedModule(module);
					}
					module.state = 'planned';
				}

				service.updateAllSelectedModules();
			}

			/**
			 * Load data from localStorage
			 **/
			service.reload = function () {
				var data = localStorageService.get('data');
				var plan = localStorageService.get('plan');

				if (data) {

					for(var i in data) {
						var module = data[i];
						var cmd = 'auto';

						/* If these modules are not saved in plan localStorage, then add them to plan table */
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
			};

			/**
			 * This functions resets all arrays to empty
			 * It also calls to resetTable to create empty lists and totalMC variable for each type
			 * Then reload is called to load data from localStorage
			 **/
			service.init = function () {
				// Visible Modules = selected Modules
				service.visibleModules = {};
				service.resetTable();

				service.reload();
			};

			// Check if this module is alread added to visible list
			var added = function (module) {
				for(var i in service.visibleModules['ALL']) {
					if (service.visibleModules['ALL'][i].code === module.code) {
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
				for(var i in service.visibleModules['ALL']) {
					var module = service.visibleModules['ALL'][i];

					if (module.code === modCode) {

						service.visibleModules['ALL'].splice(i, 1);

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
			service.submit = function (url, params, callback) {
				$http({
					method: 'GET',
					url: url,
					params: params
				}).then(function successCallback(res) {

					if (callback) {
						callback(res.data);
					}
				}, function errorCallback(err) { 
					console.log('ERROR: Submitting ' + url + ' ' + err);
				});
			};

			/**
			 *  OUTPUT: List of Module Codes
			 **/
			service.getListOfModules = function () {
				var list = [];
				var modules = service.visibleModules['ALL'];

				for(var i in modules) {
					var module = modules[i];

					list.push(module.code);
				}

				return JSON.stringify(list);
			};

			/**
			 *  Categorize modules into ULR, PR, UE
			 **/
			service.categorizeModule = function () {
				/* reset all module group except for ALL */
				for(var type in service.types) {
					if (type === 'ALL') continue;

					service.visibleModules[type] = [];
					service.totalMCs[type] = 0;
				}

				var modules = service.visibleModules['ALL'];

				for(var i in modules) {
					var module = modules[i];

					/* add module to specific category, and update MC also */
					if ((module.type) && (module.type === 'ULR')) {

						service.visibleModules['ULR'].push(module);
						service.totalMCs['ULR'] += module.mc;
					} else if ((module.type) && (module.type === 'PR')) {

						service.visibleModules['PR'].push(module);
						service.totalMCs['PR'] += module.mc;
					} else if ((module.type) && (module.type === 'UE')) {

						service.visibleModules['UE'].push(module);
						service.totalMCs['UE'] += module.mc;
					} else {
						console.log('WARNING: Cannot identify module\'type');

						service.visibleModules['UE'].push(module);
						service.totalMCs['UE'] += module.mc;
					}
				}
			};

			/**
			 *  Send user's info and list of modules to server to get types of modules
			 *  It also redistributes modules into specific types
			 **/
			service.getType = function () {
				var params = {
					major: 'CS',
					adm_year: '1314',
					focus_area: 'IR',
					mods: service.getListOfModules()
				};

				service.submit('/main/php/get_type.php', params, function (results) {
					var modules = service.visibleModules['ALL'];
					
					for(var i in modules) {
						var module = modules[i];

						var result = results[module.code];

						if (result) {
							module.type = result[0];
							module.subtype = result[1];
						}
					}

					/* Categorize modules into types */
					service.categorizeModule();
				}); 
			};

			return service;
		}
]);