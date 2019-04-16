define([
    'require',
    'angular',
    'jquery',
    'jquery-ui',
    'angular-animate',
    'angular-sortable',
    'Highcharts',
    //'Highcharts-data',
    'highcharts_more',
    'solid-guage',
    'angular-aria',
    'angular-material',
    'angular-loader',
    'angular-cookie',
    'datatables.net',
    'datatables.net-bootstrap',
    'app',
    'routes',
    'ngSanitize'
], function (require, ng,ngAnimate) {
    'use strict';
    
    require(['domReady!'], function (document) {
        ng.bootstrap(document, ['tmeModule']);
    });
});
