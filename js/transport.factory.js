'use strict';

angular.module('core').factory('Transport', ['$interval', '$http',
		function ($interval, $http) {
			var factory = {};

			factory.requestStack = [];
			factory.isLastRequestReceived = true;

			$interval(function () {
				if (factory.isLastRequestReceived && factory.requestStack.length) {
					// Send only the latest request to server 
					var url = factory.requestStack[factory.requestStack.length - 1];
					factory.requestStack = [];

					// Avoid sending any request at this time
					factory.isLastRequestReceived = false;

					$http({
						url: url,
						method: 'GET'
					}).then(function (result) {
						// Last request is sent, ready to send another request 
						factory.isLastRequestReceived = true;
					}, function (err) {
						console.log('ERROR: Saving modules list' + err);
					});
				}
			}, 10);

			return factory;
		}
]);
