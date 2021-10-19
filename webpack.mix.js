const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

mix.setResourceRoot('../');
mix.setPublicPath(`./`);
mix.js('admin/js/source/app.js', 'admin/js/build/');
mix.js('public/js/source/app.js', 'public/js/build/');
mix.sass('admin/css/source/app.scss', 'admin/css/build/').sourceMaps();
mix.sass('public/css/source/app.scss', 'public/css/build/').sourceMaps();

mix.version();
