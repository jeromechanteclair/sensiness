/**
 * Webpack configuration.
 */


const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const {
    CleanWebpackPlugin
} = require('clean-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');


// const RemovePlugin = require('remove-files-webpack-plugin');
// JS Directory path.
const JS_DIR = path.resolve(__dirname, 'assets/src/js');
const SCSS_DIR = path.resolve(__dirname, 'assets/src/sass');


const webpack = require("webpack");
const entry = {

    'main': JS_DIR + '/main.js',
    'adminwp': JS_DIR + '/adminwp.js',
    'frontend': SCSS_DIR + '/frontend.scss',
    'admin': SCSS_DIR + '/admin.scss',


};

const output = {

    filename: `[name].js`,
    path: path.resolve(__dirname, 'dist')
};

/**
 * Note: argv.mode will return 'development' or 'production'.
 */
const plugins = (argv) => [

    new MiniCssExtractPlugin({
        filename: `[name].min.css`
    }),

    new BrowserSyncPlugin({
        host: 'localhost',
        port: 3000,
        proxy: 'http://sensiness.local/'
    }),
    new CleanWebpackPlugin({
        verbose: true,
        cleanStaleWebpackAssets: ('production' === argv.mode) // Automatically remove all unused webpack assets on redist, when set to true in production. ( https://www.npmjs.com/package/clean-webpack-plugin#options-and-defaults-optional )
    }),

    new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery',
    }),
    // new RemovePlugin({

    //     after: {
    //         include: [
    //             './dist/frontend.js',
    //             './dist/admin.js'

    //         ]

    //     }
    // })



];

const rules = [

    {
        test: /\.(sa|sc|c)ss$/,

        exclude: /node_modules/,
        use: [

            // Mark content for extraction into seperate CSS file
            MiniCssExtractPlugin.loader,

            // Translates CSS into CommonJS
            'css-loader',

            // Compiles Sass to CSS
            'sass-loader'
        ]
    }, {
        test: /\.css$/i,
        use: ["style-loader", "css-loader"],
    },
    {

        test: /\.svg$/,
        loader: 'svg-inline-loader'

    },


];

/**
 * Since you may have to disambiguate in your webpack.config.js between development and production dists,
 * you can export a function from your webpack configuration instead of exporting an object
 *
 * @param {string} env environment ( See the environment options CLI documentation for syntax examples. https://webpack.js.org/api/cli/#environment-options )
 * @param argv options map ( This describes the options passed to webpack, with keys such as output-filename and optimize-minimize )
 * @return {{output: *, devtool: string, entry: *, optimization: {minimizer: [*, *]}, plugins: *, module: {rules: *}, externals: {jquery: string}}}
 *
 * @see https://webpack.js.org/configuration/configuration-types/#exporting-a-function
 */
module.exports = (env, argv) => ({

    entry: entry,

    output: output,

    /**
     * A full SourceMap is emitted as a separate file ( e.g.  main.js.map )
     * It adds a reference comment to the bundle so development tools know where to find it.
     * set this to false if you don't need it
     */
    devtool: 'source-map',

    module: {
        rules: rules,
    },

    plugins: plugins(argv),
    optimization: {
        minimize: true,
        minimizer: [
            new UglifyJsPlugin({
                cache: false,
                parallel: true,
                sourceMap: false
            })
        ]
    },
    resolve: {

        extensions: ['.js', '.jsx', '.scss']
    },

    externals: {
        jquery: 'jQuery'
    },
    target: ["web", 'es5'],

});