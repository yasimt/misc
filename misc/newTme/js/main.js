if(LOGIN_CITY == 'pune' || LOGIN_CITY == 'Pune') {
    require.config({
        paths: {
            'angular': 'http://172.29.40.237:97/newTme/bower_components/angular/angular',
            'angular-route': 'http://172.29.40.237:97/newTme/bower_components/angular-route/angular-ui-route',
            'angular-animate': 'http://172.29.40.237:97/newTme/bower_components/angular/angular-animate',
            'angular-aria'  :   'http://172.29.40.237:97/newTme/bower_components/angular/angular-aria',
            'angular-material'  :   'http://172.29.40.237:97/newTme/bower_components/angular/angular-material',
            'jquery': 'http://172.29.40.237:97/newTme/vendor/jquery-1.7.1.min',
            'jquery-ui': 'http://172.29.40.237:97/newTme/vendor/jquery-ui-1.10.4.custom.min',
            'angular-sortable': 'http://172.29.40.237:97/newTme/bower_components/angular/angular-sortable',
             'Highcharts':'http://172.29.40.237:97/newTme/vendor/highcharts',
             'highcharts_more':'http://172.29.40.237:97/newTme/vendor/highcharts_more',
           // 'Highcharts':"https://code.highcharts.com/highcharts",
            //'Highcharts-data':'../vendor/data',
            'solid-guage':'http://172.29.40.237:97/newTme/vendor/solid_guage',
            'angular-loader': 'http://172.29.40.237:97/newTme/vendor/loading-bar',
            'angular-cookie': 'http://172.29.40.237:97/newTme/bower_components/angular-cookie',
            'datatables.net': 'http://172.29.40.237:97/newTme/vendor/jquery.dataTables.min',
            'datatables.net-bootstrap': 'http://172.29.40.237:97/newTme/vendor/dataTables.bootstrap.min',
            'domReady': 'http://172.29.40.237:97/newTme/bower_components/requirejs-domready',
            'ngSanitize':'http://172.29.40.237:97/newTme/bower_components/angular/angular-sanitize.min'
        },
        waitSeconds: 0,
        shim: {
            'angular': {
                exports: 'angular'
            },
            'angular-route': {
                deps: ['angular']
            },
            'angular-animate': ['angular'],
            'angular-aria'  :   ['angular','angular-animate'],
            'angular-material'  :   ['angular','angular-animate','angular-aria'],
            'jquery': {
                exports: 'jquery'
            },
            'jquery-ui': ['jquery'],
            'datatables.net' : [ 'jquery' ],
            'datatables.net-bootstrap' : [ 'datatables.net' ],
            'angular-sortable': ['angular','jquery','jquery-ui'],
            'Highcharts':['angular','jquery','jquery-ui'],
            'highcharts_more':['angular','jquery','jquery-ui','Highcharts'],
            'solid-guage': ['angular','jquery','jquery-ui','Highcharts','highcharts_more'],
            'angular-loader' : ['angular'],
            'angular-cookie' : ['angular'],
            'ngSanitize':['angular'],
            //'Highcharts-data':['Highcharts']
        },
        deps: [
            './bootstrap'
        ],
        urlArgs: "ver=56.8" //version change
    });
} else {
    require.config({
        paths: {
            'angular': '../bower_components/angular/angular',
            'angular-route': '../bower_components/angular-route/angular-ui-route',
            'angular-animate': '../bower_components/angular/angular-animate',
            'angular-aria'  :   '../bower_components/angular/angular-aria',
            'angular-material'  :   '../bower_components/angular/angular-material',
            'jquery': '../vendor/jquery-1.7.1.min',
            'jquery-ui': '../vendor/jquery-ui-1.10.4.custom.min',
            'angular-sortable': '../bower_components/angular/angular-sortable',
             'Highcharts':'../vendor/highcharts',
             'highcharts_more':'../vendor/highcharts_more',
           // 'Highcharts':"https://code.highcharts.com/highcharts",
            //'Highcharts-data':'../vendor/data',
            'solid-guage':'../vendor/solid_guage',
            'angular-loader': '../vendor/loading-bar',
            'angular-cookie': '../bower_components/angular-cookie',
            'datatables.net': '../vendor/jquery.dataTables.min',
            'datatables.net-bootstrap': '../vendor/dataTables.bootstrap.min',
            'domReady': '../bower_components/requirejs-domready',
            'ngSanitize':'../bower_components/angular/angular-sanitize.min'
        },
        waitSeconds: 0,
        shim: {
            'angular': {
                exports: 'angular'
            },
            'angular-route': {
                deps: ['angular']
            },
            'angular-animate': ['angular'],
            'angular-aria'  :   ['angular','angular-animate'],
            'angular-material'  :   ['angular','angular-animate','angular-aria'],
            'jquery': {
                exports: 'jquery'
            },
            'jquery-ui': ['jquery'],
            'datatables.net' : [ 'jquery' ],
            'datatables.net-bootstrap' : [ 'datatables.net' ],
            'angular-sortable': ['angular','jquery','jquery-ui'],
            'Highcharts':['angular','jquery','jquery-ui'],
            'highcharts_more':['angular','jquery','jquery-ui','Highcharts'],
            'solid-guage': ['angular','jquery','jquery-ui','Highcharts','highcharts_more'],
            'angular-loader' : ['angular'],
            'angular-cookie' : ['angular'],
            'ngSanitize':['angular'],
            //'Highcharts-data':['Highcharts']
        },
        deps: [
            './bootstrap'
        ],
        urlArgs: "ver=56.8" //version change
    });
}

