'use strict';

angular.module('core').config(function (localStorageServiceProvider) {
	localStorageServiceProvider
		.setNotify(false, false)
		.setStorageType('localStorage')
		.setPrefix('socplan');
});
