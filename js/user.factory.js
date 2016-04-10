'use strict';

/**
 *  Reset function to be added later in mainController
 *  update -> setInfo -> reset
 *  load info from cookie -> setInfo
 *  displayField are just strings while Fields, such as majors, focusarea, admissionyear, are objects
 **/
angular.module('core').factory('User', ['$http', 'localStorageService',
		function ($http, localStorageService) {
			var object = {};

			object.majorsList = [
				{ title: 'Computer Science', code: 'CS' },
				{ title: 'Information System', code: 'IS' }
			];

			object.focusAreasList = [
				{ title: 'Algorith & Theory', code: 'AT' },
				{ title: 'Artificial Intelligence', code: 'AI' },
				{ title: 'Computer Security', code: 'CS' },
				{ title: 'Database Systems', code: 'DS' },
				{ title: 'Multimedia Information Retrieval', code: 'MIR' },
				{ title: 'Network and Distributed Systems', code: 'NDS' },
				{ title: 'Parallel Computing', code: 'PC' },
				{ title: 'Programming Languages', code: 'PL' },
				{ title: 'Software Engineering', code: 'SE' }
			];

			object.admissionYearsList = [
				{ title: '2015/2016', code: '1516' },
				{ title: '2014/2015', code: '1415' },
				{ title: '2013/2014', code: '1314' },
				{ title: '2012/2013', code: '1213' },
				{ title: '2011/2012', code: '1112' },
				{ title: '2010/2011', code: '1011' }
			];

			object.displayMajor = '';
			object.displayFocusArea = '';
			object.displayAdmissionYear = '';
			object.displayUsername = '';
			object.displayBachelor = '';

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

			var findItemByTitle = function (title, items) {
				for(var i in items) {
					var item = items[i];

					if (item.title === title) {
						return item;
					}
				}
			}

			/**
			 *  Update user's info and reset the whole website 
			 *  It calls reset after user's info is updated
			 *  It calls save to save info to localStorage
			 **/
			object.setInfo = function (major, focusArea, admissionYear, username, bachelor) {
				object.major = findItemByTitle(major, object.majorsList);
				object.focusArea = findItemByTitle(focusArea, object.focusAreasList);
				object.admissionYear = findItemByTitle(admissionYear, object.admissionYearsList);
				object.username = username;
				object.bachelor = bachelor;


				object.displayMajor = major;
				object.displayFocusArea = focusArea;
				object.displayAdmissionYear = admissionYear;
				object.displayUsername = username;
				object.displayBachelor = bachelor;

				object.reset(major, focusArea, admissionYear);

				object.save();
			};

			/**
			 *  User clicked submit button, update new user's info
			 **/
			object.update = function () {
				object.setInfo(object.displayMajor, object.displayFocusArea, object.displayAdmissionYear, object.displayUsername, object.displayBachelor);
			};

			object.init = function () {
				/* Load user's info from cookie */
				var info = localStorageService.get('user');

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
			};

			return object;
		}
]);
