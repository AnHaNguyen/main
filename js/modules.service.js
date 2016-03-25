'use strict';

angular.module('core').service('Modules', ['$http',
	function ($http) {
		this.init = function (admissionYear, callback) {
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
				console.log('ERROR: Getting requirements - ' + err);
			});
		};
	}
]);
