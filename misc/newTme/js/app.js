define([
    'angular',
    'angular-route',
    'angular-aria',
    'angular-material',
    './controllers/index',
    './directives/index',
    './filters/index',
    './services/index'
], function (angular) {
    'use strict';

    return angular.module('tmeModule', [
        'tmeModule.controllers',
        'tmeModule.directives',
        'tmeModule.filters',
        'tmeModule.services',
        'ui.router',
        'ngAnimate',
        'ngCookies',
        'ngMaterial',
        'angular-loading-bar',
        'ui.sortable',
        'ngSanitize'
    ]);
});
