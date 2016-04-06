'use strict';

// This directive controls search input
angular.module('core').directive('search', [
	function () {
		return {
			restrict: 'E',
			templateUrl: 'html/search.html',
			scope: {
				select: '=select',
				items: '=items'
			},
			link: function(scope, element, attrs) {

				scope.types = ['ULR', 'PR', 'UE', 'ALL'];
				scope.selectedType = 'ALL';
				scope.showSetting = false;
				scope.visibleItems = scope.items;
				scope.input = "";

				scope.search = function (pattern) {
					var limit = 20;
					var ans = [];
					pattern = pattern.toUpperCase();

					for(var i in scope.items) {
						var item = scope.items[i];
						var code = item.code.toUpperCase();
						var title = item.title.toUpperCase();

						if ((code.search(pattern) != -1) || (title.search(pattern) != -1)) {
							ans.push(item);
							if (ans.length > limit) break;
						}
					}

					return ans;
				};

				scope.$watch(function () {
					return scope.items;
				}, function (newItems) {
					scope.visibleItems = newItems;
				}); 

				scope.$watch(function () {
					return scope.input;
				}, function (newInput) {
					console.log(newInput);
				});

				scope.changeVisibleItems = function () {
					scope.visibleItems = [];

					for(var i in scope.items) {
						var item = scope.items[i];

						if ((item.type === scope.selectedType) || (scope.selectedType === 'ALL')) {
							scope.visibleItems.push(item);
						}
					}
				};

				// Change the type of modules to be searched
				scope.changeType = function () {
					for(var i in scope.types) {
						var type = scope.types[i];

						if (type === scope.selectedType) {
							scope.selectedType = scope.types[(parseInt(i) + 1) % scope.types.length];
							break;
						}
					}

					scope.changeVisibleItems();
				};

				scope.enterInput = function ($item) {
					if ($item) {
						var selectedModule = $item.originalObject;

						if (selectedModule) {
							scope.select(selectedModule.type, selectedModule.code);
						}
					}
				};
			}
		}
	}
]);
