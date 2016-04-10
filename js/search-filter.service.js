'use strict'

angular.module('core').service('SearchFilter', ['$http',
	function ($http) {
		var object = {
			filter: {},
			pass: function() {}
		};

		object.filter = {};

		object.pass = function (item) {

			for(var field in object.filter) {
				if ((!item[field]) || (item[field] !== object.filter[field])) {
					return false;
				}
			}

			return true;
		};

		object.set = function (newField, newValue) {
			object.filter[newField] = newValue;
		};

		object.remove = function (removedField) {
			var newFilterSet = {};

			for(var field in object.filter) {

				if (field !== removedField) {
					newFilterSet[field] = object.filter[field];
				}
			}

			object.filter = newFilterSet;
		};

		object.resetFilter = function () {
			object.filter = {};
		};

		return object;
	}
]);
