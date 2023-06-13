let mix = require('laravel-mix');
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
mix.setPublicPath('dist');
mix.options({
  resourceRoot: '/themes/hello-elementor-child/dist'
})
mix.autoload({
  jquery: ['$', 'window.jQuery', 'jQuery'],
});
mix.js('assets/js/main.js', 'dist/js').setPublicPath('dist').extract();
mix.sass('assets/scss/style.scss', 'dist/css/style.css').setPublicPath('dist').options({
    processCssUrls: false
}).extract();
mix.browserSync({
    proxy:'sensiness.local'
 })
 mix.copyDirectory('assets/fonts', 'dist/css/fonts')
