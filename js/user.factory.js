'use strict';

/**
 *  Reset function to be added later in mainController
 *  update -> setInfo -> reset
 *  load info from cookie -> setInfo
 *  displayField are just strings while Fields, such as majors, focusarea, admissionyear, are objects
 **/
angular.module('core').factory('User', ['$http', 'localStorageService', 'Transport',
		function ($http, localStorageService, Transport) {
			var object = {};

			object.majorsList = [
				{ title: 'Computer Science', code: 'CS' },
				{ title: 'Information System', code: 'IS' },
				{ title: 'Business Analytics', code: 'BZA' },
				{ title: 'Computer Engineering', code: 'CEG' }
			];

			object.focusAreasList = [
				{ title: 'Algorithms & Theory (AT)', code: 'AT' },
				{ title: 'Artificial Intelligence (AI)', code: 'AI' },
				{ title: 'Computer Graphics and Games (CG)', code: 'CG' },
				{ title: 'Computer Security(CS)', code: 'CS' },
				{ title: 'Database Systems (DB)', code: 'DB' },
				{ title: 'Multimedia Information Retrieval (IR)', code: 'IR' },
				{ title: 'Networking and Distributed Systems (NW)', code: 'NW' },
				{ title: 'Parallel Computing (PC)', code: 'PC' },
				{ title: 'Programming Languages (PL)', code: 'PL' },
				{ title: 'Software Engineering (SE)', code: 'SE' },
				{ title: 'Interactive Media (IM)', code: 'IM' },
				{ title: '12. Visual Computing (VC)', code: 'VC' }
			];

			object.admissionYearsList = [
				{ title: '2015/2016', code: '1516' },
				{ title: '2014/2015', code: '1415' },
				{ title: '2013/2014', code: '1314' },
				{ title: '2012/2013', code: '1213' },
				{ title: '2011/2012', code: '1112' },
				{ title: '2010/2011', code: '1011' }
			];

			object.defaultUsername = 'You';
			object.defaultBachelor = 'Bachelor of Computing';

			object.displayMajor = '';
			object.displayFocusArea = '';
			object.displayAdmissionYear = '';
			object.displayUsername = object.defaultUsername;
			object.displayBachelor = object.defaultBachelor;

			/**
			 *  Save user's data to cookie 
			 **/
			object.save = function () {
				var data = {
					major: object.major,
					focusArea: object.focusArea,
					admissionYear: object.admissionYear,
					username: object.username,
					bachelor: object.bachelor
				};

				localStorageService.set('user', data);
			};

			object.findItemByTitle = function (title, items) {
				for(var i in items) {
					var item = items[i];

					if (item.title === title) {
						return item;
					}
				}

				return null;
			}

			object.findItemByCode = function (code, items) {
				for(var i in items) {
					var item = items[i];

					if (item.code === code) {
						return item;
					}
				}

				return null;
			}

			/**
			 *  Update user's info and reset the whole website 
			 *  It calls reset after user's info is updated
			 *  It calls save to save info to localStorage
			 **/
			object.setInfo = function (major, focusArea, admissionYear, username, bachelor, callback) {
				object.major = object.findItemByTitle(major, object.majorsList);
				object.focusArea = object.findItemByTitle(focusArea, object.focusAreasList);
				object.admissionYear = object.findItemByTitle(admissionYear, object.admissionYearsList);
				object.username = ( username ? username : object.defaultUsername );
				object.bachelor = ( bachelor ? bachelor : object.defaultBachelor );


				object.displayMajor = major;
				object.displayFocusArea = focusArea;
				object.displayAdmissionYear = admissionYear;
				object.displayUsername = username;
				object.displayBachelor = bachelor;

				object.reset(object.major, object.focusArea, object.admissionYear, callback);

				object.save();
			};

			/**
			 *  User clicked submit button, update new user's info
			 **/
			object.update = function () {
				object.setInfo(object.displayMajor, object.displayFocusArea, object.displayAdmissionYear, object.displayUsername, object.displayBachelor);
			};

			object.init = function (callback) {
				/* Load user's info from cookie */
				var info = localStorageService.get('user');
				var token = getIVLEToken();

				if (token) {
					initializeUser(token, function (user) {
						var major = object.findItemByCode(getMajor(user), object.majorsList);
						var admissionYear = object.findItemByCode(getAdmissionYear(user), object.admissionYearsList);

						// THis requests for focusArea
						// no need to request for modules later
						getModulesLogin(token, function(semesters, states){
							// default focusarea is software engineer
							var focusArea = 'SE';

							if ((semesters[0]) && (semesters[0].length) && (semesters[0][0] === 'notthefirsttime') && (semesters[7][0])) {
								// focusarea found
								if (object.findItemByCode(semesters[7][0], object.focusAreasList)) {
									focusArea = object.findItemByCode(semesters[7][0], object.focusAreasList).code;
								}
							}

							focusArea = object.findItemByCode(focusArea, object.focusAreasList);

							// Delete cookied data that could be from other users or non-login users
							localStorageService.set('user', '');
							localStorageService.set('data', '');

							// Set matriculation number
							var matric = user.data.UserID;
							object.matric = matric;

							// Load plan table data actually
							Transport.loadCookies();

							// Save modules so that we don't have to request later
							Transport.semesters = semesters;

							object.setInfo(major.title, focusArea.title, admissionYear.title);

							if (callback) {
								callback();
							}
						});
					});
				} else {
					if (info) {
						/* safely extracting info */
						var major = '', focusArea = '', admissionYear = '';

						if (info.major && info.major.title) {
							major = info.major.title;
						}

						if (info.focusArea && info.focusArea.title) {
							focusArea = info.focusArea.title;
						}

						if (info.admissionYear && info.admissionYear.title) {
							admissionYear = info.admissionYear.title;
						}

						object.setInfo(major, focusArea, admissionYear, info.username, info.bachelor);
					}

					if (callback) {
						callback();
					}
				}
			};

			return object;
		}
]);
