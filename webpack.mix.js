const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    module: {
        rules: [{
            test: require.resolve('jquery'),
            use: [{
                loader: 'expose-loader',
                options: '$'
            }]
        }]
    }
});

// Configurazione
var config = {
    production: 'public', // Cartella di destinazione
    development: 'resources', // Cartella dei file di personalizzazione
    modules: 'node_modules', // Cartella dei file di personalizzazione
};

// CSS
mix.sass(config.development + '/sass/vendor.scss', config.production + '/css')
    .styles([
        config.development + '/css/*.css'
    ], config.production + '/css/style.css');

// Images
mix.copy(config.development + '/images/**/*.*', config.production + '/images')

// JavaScript
mix.js(config.development + '/js/app.js', config.production + '/js');

mix.autoload({
    jquery: ['$', 'global.jQuery', "jQuery", "global.$", "jquery", "global.jquery"],
    cookieconsent: ['cookieconsent', 'window.cookieconsent'],
    moment: ['moment'],
    parsleyjs: ['parsley'],
    sweetalert2: ['swal'],
});

mix.extract([
    'bootstrap',
    'jquery',
    'jquery.complexify',
    'moment',
    'parsleyjs',
    'sweetalert2',
    'cookieconsent',
]);
