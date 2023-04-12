const gulp   = require('gulp');
const del    = require('del');
const dest   = require('gulp-dest');
const fs = require( 'fs' );
const rename = require('gulp-rename');
const plumber      = require('gulp-plumber');
const sourcemaps   = require('gulp-sourcemaps');
const minimist     = require('minimist');
const packageJSON  = require( './package.json' );
const runTimestamp = Math.round(Date.now()/1000);

const options = minimist( process.argv.slice( 2 ), {
    string: [ 'suffix' ],
    default: {
        suffix: process.env.SUFFIX || ''
    }
} );
const paths = {
	css: 'assets/css/*.scss',
	js: ['assets/js/*.js', '!**/*.min.js'],
	php: ["*.php", "{includes,templates}/**/*.php"],
	pot: `languages/${packageJSON.name}.pot`,
};
const zipName = `${packageJSON.name}` + (options.suffix ? `-${options.suffix}` : '');

/**
 * Get a formatted date string.
 * @returns {string}
 */
function getDateString() {
	const now = new Date();
	const realMonth = now.getUTCMonth() + 1,
		month = (realMonth < 10 ? '0' : '') + realMonth,
		day = (now.getUTCDate() < 10 ? '0' : '') + now.getUTCDate();
	return `${now.getUTCFullYear()}-${month}-${day} ` + `${now.getUTCHours()}:${now.getUTCMinutes()}:${now.getUTCSeconds()}+00:00`;
}

/**
 * Execute a command and run the done callback when it completes.
 *
 * @param {string[]|string} command Either a single string or an array of strings to join.
 * @param {function} done The callback function when the command is complete.
 * @param {object} options Options to pass to the exec command.
 */
function exec(command, done, options = {}) {
	const { exec } = require('child_process');
	let commandString;
	if (Array.isArray(command)) {
		commandString = command.join(' ');
	} else if (typeof command === 'string') {
		commandString = command;
	} else {
		done(new Error('Invalid parameter passed to exec. Must be a string or array of strings.'));
	}

	console.log('Executing: `' + commandString + '`');

	exec(commandString, options, function(err, stdOut, stdErr) {
		console.log(stdOut);
		console.log(stdErr);
		done(err);
	});
}

function makeSass() {
	const sass = require('gulp-sass');
	const minifyCSS = require('gulp-csso');
	const autoprefixer = require('gulp-autoprefixer');
	return gulp.src(paths.css, { base: "./" })
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer({
			browsers: ['> 0.5%', 'last 2 versions']
		}))
		.pipe(sourcemaps.init())
		.pipe(minifyCSS({
			restructure: false
		}))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('./'));
}

function makeJs() {
	const uglify = require('gulp-uglify');
	return gulp.src('assets/js/*.js')
		.pipe(sourcemaps.init())
		.pipe(uglify())
		.pipe(dest('assets/js/min/', {ext: '.min.js'}))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('./'));
}

function watchFiles() {
	gulp.watch( paths.js, makeJs );
	gulp.watch( paths.css, makeSass );
}

function cleanDist() {
	return del(['dist/**/*']);
}

/**
 * Scan the theme and create a POT file.
 */
function makePot(done) {
	// Clean /languages directory
	del(['languages/**']);

	const headers = {
		'Language': 'en_US',
		'POT-Creation-Date': getDateString()
	};
	const exclude = [
		'dist',
		'.github',
		'tests',
		'gulpfile.js',
	];
	const commandPieces = [
		'vendor/bin/wp',
		'i18n make-pot',
		'./',
		`languages/${packageJSON.name}.pot`,
		`--slug=${packageJSON.name}`,
		`--domain=${packageJSON.name}`,
		`--headers='${JSON.stringify(headers)}'`,
		`--package-name="${packageJSON.title}"`,
		`--exclude="${exclude.join(',')}"`,
	];

	exec(commandPieces, done);
}

function makeIconFont() {
	const iconfont = require('gulp-iconfont');
	return gulp.src(['assets/icons/*.svg'])
		.pipe(iconfont({
			fontName: 'automatewoo-referrals-iconfont',
			prependUnicode: true,
			formats: ['ttf', 'eot', 'woff', 'svg'],
			timestamp: runTimestamp, // recommended to get consistent builds when watching files
		}))
		.on('glyphs', function(glyphs, options) {
			// CSS templating, e.g.
			console.log(glyphs, options);
		})
		.pipe(gulp.dest('assets/fonts/'));
}

function makeZip() {
	const zip = require('gulp-zip');
	const files = [
		'*.{php,txt,gitignore}',
		'wpml-config.xml',
		'{includes,languages,templates,assets}/**/*'
	];
	return gulp.src(files)
		.pipe(rename(f => {
			f.dirname = `${packageJSON.name}/${f.dirname}`;
		}))
		.pipe(gulp.dest('dist/'))
		.pipe(zip(`${zipName}.zip`))
		.pipe(gulp.dest('dist/'));
}

/**
 * Move a generated zip file to the root directory in preparation for the woorelease script.
 *
 * @param {function} done Callback to tell gulp this function has finished.
 */
function copyZip( done ) {
	fs.copyFile( `./dist/${ zipName }.zip`, `./${ packageJSON.name }.zip`, ( err ) => {
		if ( err ) {
			throw err;
		}
		done();
	} );
}

// Available tasks.
exports.icons = makeIconFont;
exports.makePot = makePot;
exports.default = gulp.series(makeSass, makeJs);
exports.zip = gulp.series(gulp.parallel(cleanDist, this.makePot, this.default), makeZip);
exports.watch = watchFiles;
exports.woorelease = gulp.series(this.zip, copyZip);
