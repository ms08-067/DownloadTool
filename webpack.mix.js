const mix = require('laravel-mix');
const env = process.env.NODE_ENV

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */



if (env === 'production') {
    /** production environment only minified */
    mix.styles(['resources/css/br24emp_app.css'], 'public/main/br24emp_app.min.css');
    mix.styles(['resources/css/br24emp_custom.css'], 'public/main/br24emp_custom.min.css');
    mix.styles(['resources/css/br24emp_custom_manual.css'], 'public/main/br24emp_custom_manual.min.css');

    mix.js('resources/js/app_echo.js', 'public/main/app_echo.min.js').vue(); /** vue.js laravel echo integration */

    mix.scripts('resources/js/br24emp_freq.js', 'public/main/section/br24emp_freq.min.js');
    mix.scripts('resources/js/br24emp_home_init.js', 'public/main/section/br24emp_home_init.min.js');
    mix.scripts('resources/js/br24emp_ops_init.js', 'public/main/section/br24emp_ops_init.min.js');
    mix.scripts('resources/js/br24emp_permissions_init.js', 'public/main/section/br24emp_permissions_init.min.js');

} else if (env === 'development') {
    /*mix.browserSync('salary_next.lc');*/
    /** development environment only */
    mix.styles(['resources/css/br24emp_app.css'], 'public/main/br24emp_app.dev.css');
    mix.styles(['resources/css/br24emp_custom.css'], 'public/main/br24emp_custom.dev.css');
    mix.styles(['resources/css/br24emp_custom_manual.css'], 'public/main/br24emp_custom_manual.dev.css');

    mix.js('resources/js/app_echo.js', 'public/main/app_echo.dev.js').vue(); /** vue.js laravel echo integration */

    mix.scripts('resources/js/br24emp_freq.js', 'public/main/section/br24emp_freq.dev.js');
    mix.scripts('resources/js/br24emp_home_init.js', 'public/main/section/br24emp_home_init.dev.js');
    mix.scripts('resources/js/br24emp_ops_init.js', 'public/main/section/br24emp_ops_init.dev.js');
    mix.scripts('resources/js/br24emp_permissions_init.js', 'public/main/section/br24emp_permissions_init.dev.js');
}
