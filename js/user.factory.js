'use strict';

/**
 *  Reset function to be added later in mainController
 *  update -> setInfo -> reset
 *  load info from cookie -> setInfo
 **/
angular.module('core').factory('User', ['$http', 'localStorageService',
		function ($http, localStorageService) {
			var object = {};

			object.displayMajor = '';
			object.displayFocusArea = '';
			object.displayAdmissionYear = '';
			object.displayUsername = '';
			object.displayBachelor = '';

			/**
			 *  Save user's data to cookie 
			 **/
			object.save = function () {
				/* JSON Encoding */
				var data = JSON.stringify({
					major: object.major,
					focusArea: object.focusArea,
					admissionYear: object.admissionYear,
					username: object.username,
					bachelor: object.bachelor
				});

				localStorageService.set('user', data);
			};

			/**
			 *  Update user's info and reset the whole website 
			 *  It calls reset after user's info is updated
			 *  It calls save to save info to localStorage
			 **/
			object.setInfo = function (major, focusArea, admissionYear, username, bachelor) {
				object.major = major;
				object.focusArea = focusArea;
				object.admissionYear = admissionYear;
				object.username = username;
				object.bachelor = bachelor;


				object.displayMajor = object.major;
				object.displayFocusArea = object.focusArea;
				object.displayAdmissionYear = object.admissionYear;
				object.displayUsername = object.username;
				object.displayBachelor = object.bachelor;

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
					object.setInfo(info.major, info.focusArea, info.admissionYear, info.username, info.bachelor);
				}
			};

			return object;
		}
]);
