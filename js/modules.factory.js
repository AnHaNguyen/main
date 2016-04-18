'use strict';

/**
 *            fetchdata -> init -> resetTable -> reload --> updateAllSelectedModule 
 *   		  changeStateModule -> addPlannedModule, removePlannedModule
 * 			  updateAllSelectedModules -> saveAllSelectedModule
 * 									   --> getType
 * 			  Add Module --> Module is added to ['ALL'] --> save to localstorgae --> getType --> Module is categorized into ['ULR', 'PR', 'UE']
 * 																							 --> After getting type, verify modules
 *  					 --> Module is added to planned modules
 *    Module state is waived(waived) when it first added
 **/

angular.module('core').factory('Modules', ['$http', 'localStorageService', 'User', 'Transport', '$interval', 
		function ($http, localStorageService, User, Transport, $interval) {
			var service = {
				plannedModules: [],
				types: {},
				user: {},
				remainingMC: {
					'ULR': 0,
					'UE': 0,
					'PR': 0
				}
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
				var token = getIVLEToken();

				if (token) {
					// save user's info and mods in 8 arrays
					// array #0: tag == 'notthefirsttime' 
					// array #1: planned modules
					// array #2: waived modules
					// array #3: taken modules
					// array #4: ulr modules
					// array #5: pr modules
					// array #6: ue modules
					// array #7: user's info: focus area,
					var mods = [[], [], [], [], [], [], [], []];

					for(var i in data) {
						var mod = data[i];

						if (mod.state === 'planned') {
							mods[1].push(mod.code);
						} else if (mod.state === 'waived') {
							mods[2].push(mod.code);
						} else if (mod.state === 'taken') {
							mods[3].push(mod.code);
						}

						if (mod.isTypeFixed) {
							if (mod.type === 'ULR') {
								mods[4].push(mod.code);
							} else if (mod.type === 'PR') {
								mods[5].push(mod.code);
							} else {
								mods[6].push(mod.code);
							}
						}
					}

					mods[0].push('notthefirsttime');
					mods[7].push(User.focusArea.code);

					var modsStr = JSON.stringify(mods);
                    var url =  "php/authentication/connectdatabase.php?cmd=storeModules&matric="+User.matric+"&modules="+modsStr;

					Transport.requestStack.push(url);
				} else {
					localStorageService.set('data', data);
				}
			};

			service.saveSelectedModulesToDB = function(token){		//to be edited

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
				module.selected = {
					'taken': '',
					'planned': '',
					'waived': '',
					'unselected': ''
				};
				module.selected[module.state] = 'selected-toggle-btn';
				
				if (service.addPlannedModuleToPlanTable) {
					service.addPlannedModuleToPlanTable(module);
				}
			}

			service.removePlannedModule = function (module) {
				module.state = 'taken';
				module.selected = {
					'taken': '',
					'planned': '',
					'waived': '',
					'unselected': ''
				};
				module.selected[module.state] = 'selected-toggle-btn';
				
				if (service.removePlannedModuleFromPlanTable) {
					service.removePlannedModuleFromPlanTable(module);
				}
			}

			service.getSelectedModuleByCode = function (modCode) {
				for(var i in service.visibleModules['ALL']) {
					/* For all modules in the list */
					var module = service.visibleModules['ALL'][i];

					if (module.code === modCode) {
						/* found */
						return module;
					}
				}

				return null;
			};

			/**
			 *  Find module by module's type and code
			 **/
			function getModuleByCode(modCode) {
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
			 *  Find module by module's type and code
			 **/
			service.getModuleByCode = function(modCode) {
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
			service.addModule = function (modCode, modState, origin) {
				var module = getModuleByCode(modCode);

				/* Make sure this module has not been added before */
				if (module) {
					if (!added(module)) {

						service.visibleModules['ALL'].push(module);

						if (modState === 'planned') {
							service.addPlannedModule(module);
						}

						// animations when user add module 
						for(var i in service.visibleModules['ALL']) {
							var module = service.visibleModules['ALL'][i];

							module.new = '';
						}
						module.new = 'new-added-row';
						if ((!origin) || (origin !== 'auto')) {
							Materialize.toast(module.code + ' is added', 3000);
						}

						module.state = (modState ? modState : 'planned');
						module.selected = {
							'taken': '',
							'planned': '',
							'waived': '',
							'unselected': ''
						};
						module.selected[module.state] = 'selected-toggle-btn';
					} else {
						service.changeState(modCode, modState);
					}

					if ((!origin) || (origin !== 'auto')) {
						service.updateAllSelectedModules();
					}
				}
			}

			/**
			 * LOAD DATA HERERERERERER
			 **/
			service.reload = function () {
				/**------------------ IVLE ---------------------------------------------------*/
				var token = getIVLEToken();

				if (token) {
					// No need to request for modules because we already have requested it

					var semesters = Transport.semesters;

					if (semesters[0][0] && (semesters[0][0] === 'notthefirsttime')) {
						for(var s = 1;  s <= 6;  s++) {
							var semester = semesters[s];
							
							for(var i in semester) {
								var modCode = semester[i];
								var modState = 'taken';
								var modType = 'UE';

								if (s % 3 == 1) modState = 'planned', modType = 'ULR';
								else if (s % 3 == 2) modState = 'waived', modType = 'PR';

								if (s <= 3) {
									service.addModule(modCode, modState, 'auto');
								} else {
									service.changeType(modCode, modType, 'auto');
								}
							}
						}
					} else {
						for(var i in semesters) {
							var modules = semesters[i];

							for(var j in modules) {
								var modCode = modules[j];

								if (modCode) {
									service.addModule(modCode, 'taken', 'auto');
								}
							}
						}
					}
				} else {
					var data = localStorageService.get('data');

					if (data) {

						for(var i in data) {
							var module = data[i];

							service.addModule(module.code, module.state, 'auto');

							if (module.isTypeFixed) {
								service.changeType(module.code, module.type, 'auto');
							}
						}
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

				var token = getIVLEToken();

				if (!token) {
					Transport.loadCookies();
				}
				service.reload();

				// Only update one time after reloading
				service.updateAllSelectedModules();

				// Sync plan table with modules table
				if (Transport.sync && token) {
					Transport.sync();
				}
			};

			// Check if this module is alread added to visible list
			User.added = function (module) {
				for(var i in service.visibleModules['ALL']) {
					if (service.visibleModules['ALL'][i].code === module.code) {
						return true;
					}
				}

				return false;
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

			service.added = function(module) {
				for(var i in service.visibleModules['ALL']) {
					if (service.visibleModules['ALL'][i].code === module.code) {
						return true;
					}
				}

				return false;
			};

			/**
			 * Remove module by and modCode
			 * MC is also updated
			 **/
			service.removeModule = function (modCode) {
				for(var i in service.visibleModules['ALL']) {
					var module = service.visibleModules['ALL'][i];

					if (module.code === modCode) {

						// Mark this module as unselected
						service.removePlannedModule(module);

						module.state = 'unselected';
						module.selected = {
							'taken': '',
							'planned': '',
							'waived': '',
							'unselected': ''
						};
						module.selected[module.state] = 'selected-toggle-btn';

						service.visibleModules['ALL'].splice(i, 1);
					}
				}

				service.updateAllSelectedModules();
			};

			// Change state between waived, planned, taken
			service.changeState = function (modCode, newState, origin) {

				var module = getModuleByCode(modCode);

				if (module) {
					if (module.state !== newState) {
						if (module.state === 'planned') {
							service.removePlannedModule(module);
						} else if (newState === 'planned') {
							service.addPlannedModule(module);
						}
					}

					module.state = newState;
					module.selected = {
						'taken': '',
						'planned': '',
						'waived': '',
						'unselected': ''
					};

					module.selected[module.state] = 'selected-toggle-btn';
				}

				if ((!origin) || (origin !== 'auto')) {
					service.updateAllSelectedModules();
				}
			};

			/**
			 *  Purpose: manually change type of a module
			 *  Require: getModulesByCode in Modules factory
			 * 			 updateAllSelectedModules in Modules factory
			 *  Assume: 
			 **/
			service.changeType = function (modCode, newType, origin) {

				var module = getModuleByCode(modCode);

				if (module) {
					if (module.type !== newType) {
						// animations when user change type
						for(var i in service.visibleModules['ALL']) {
							var mod = service.visibleModules['ALL'][i];

							mod.new = '';
						}
						module.new = 'new-added-row';
						if ((!origin) || (origin !== 'auto')) {
							Materialize.toast(module.code + ' is moved to ' + newType, 3000);
						}
					}

					module.type = newType;
					module.isTypeFixed = true;
				}

				if ((!origin) || (origin !== 'auto')) {
					service.updateAllSelectedModules();
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
						mc: parseInt(module.ModuleCredit),
						semester: module.Semester,
						prerequisites: module.Prerequisites,
						isTypeFixed: false
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
					url: '/main/data/newmodules.json',
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
						console.log('WARNING: Cannot identify module\'type', module);

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
				/* Safe copy */
				var major = (User.major && User.major.code ? User.major.code : '');
				var focusArea = (User.focusArea && User.focusArea.code ? User.focusArea.code : '');
				var admissionYear = (User.admissionYear && User.admissionYear.code ? User.admissionYear.code : '');

				var params = {
					major: major,
					focus_area: focusArea,
					adm_year: admissionYear,
					mods: service.getListOfModules()
				};

				service.submit('/main/php/get_type.php', params, function (results) {
					var modules = service.visibleModules['ALL'];
					
					for(var i in modules) {
						var module = modules[i];

						var result = results[module.code];

						if (result) {
							// Only change type if this module's type is not chosen by user
							if (!module.isTypeFixed) {
								module.type = result[0];
							}
							module.subtype = result[1];
						}
					}

					/* Categorize modules into types */
					service.categorizeModule();

					/* After categorizing modules, verify them */
					service.verify();
				}); 
			};

			/**
			 *  Set up parameters and mods to send verify request
			 *  Assume that all modules have been categorized
			 **/
			service.verify = function () {
				/* Safe copy */
				var major = (User.major && User.major.code ? User.major.code : '');
				var focusArea = (User.focusArea && User.focusArea.code ? User.focusArea.code : '');
				var admissionYear = (User.admissionYear && User.admissionYear.code ? User.admissionYear.code : '');

				var modules = [];
				
				for(var i in service.visibleModules['ALL']) {
					var module = service.visibleModules['ALL'][i];

					/* If module is waived then type of module is nil */
					modules.push([
						module.code, (module.state === 'waived' ? 'nil' : module.type), module.mc + ''
					]);
				}

				var params = {
					cmd: 'verify',
					adm_year: admissionYear,
					focus_area: focusArea,
					major: major,
					modules: JSON.stringify(modules)
				};

				service.submit('/main/php/getrequirements.php', params, function (results) {
					for(var type in results) {
						service.remainingMC[type] = Math.max(parseInt(results[type]), 0);
					}
				});
			}

			return service;
		}
]);
