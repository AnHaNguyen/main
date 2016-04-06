'use strict'

angular.module('core').service('User', ['$http',
	function ($http) {
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

		service.login = function (username, password) {
			console.log(username, password);
		};

		return service;
	}
]);
