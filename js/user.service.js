'use strict'

angular.module('core').service('User', ['$http', '$cookies',
	function ($http, $cookies) {
/*		this.init = function (admissionYear, callback) {
			$http({
				method: 'GET',
				url: '/main/php/getmodules.php',
				params: {
					ay: admissionYear
				}
			}).then(function successCallback(res) {
				if (callback) {
					callback(res.data);
				}
			}, function errorCallback(err) {
				console.log('ERROR: Getting modules - ' + err);
			});
		}; */
		var service = {};

		service.login = function (username, bachelor, major, focus_area, admission_year) {
			console.log(username, bachelor, major, focus_area, admission_year);
			$cookies.username = username;
			$cookies.bachelor = bachelor;
			$cookies.major = major;
			$cookies.focus_area = focus_area;
			$cookies.admission_year = admission_year;
		};

		return service;
	}
]);
