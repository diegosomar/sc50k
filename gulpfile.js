const { watch, src, dest, parallel, series } = require('gulp');
const sass = require('gulp-sass');
const rename = require("gulp-rename");
const uglify = require('gulp-uglify');
const babel = require('gulp-babel');
const del = require('del');
const include = require('gulp-include');

// fetch command line arguments
const arg = (argList => {
  let arg = {}, a, opt, thisOpt, curOpt;
  for (a = 0; a < argList.length; a++) {

    thisOpt = argList[a].trim();
    opt = thisOpt.replace(/^\-+/, '');

    if (opt === thisOpt) {
      // argument value
      if (curOpt) arg[curOpt] = opt;
      curOpt = null;
    }
    else {
      // argument name
      curOpt = opt;
      arg[curOpt] = true;
    }
  }

  return arg;
})(process.argv);

// Compila, minifica e salva os CSS na pasta dist/
function css() {

	let sourcemaps = true;

	if ( typeof arg.env != 'undefined' && arg.env == 'prod' ){
		sourcemaps = false;
    $out = src('assets/styles/*.scss', { sourcemaps: sourcemaps })
           .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
           .pipe(rename({ suffix: '.min' }))
           .pipe(dest('dist/css', { sourcemaps: sourcemaps }))
	}

  else {
    $out = src('assets/styles/*.scss', { sourcemaps: sourcemaps })
           .pipe(sass().on('error', sass.logError))
           .pipe(rename({ suffix: '.dev' }))
           .pipe(dest('dist/css', { sourcemaps: sourcemaps }))
  }

  return $out;
}

// Minifica e salva os JS na pasta dist/
function js() {

	let sourcemaps = true;

	if ( typeof arg.env != 'undefined' && arg.env == 'prod' ){
		sourcemaps = false;
    var $out = src('assets/scripts/**/*.js', { sourcemaps: sourcemaps })
               .pipe(include())
               .pipe(rename({ suffix: '.min' }))
               .pipe(babel({
                 presets: ['@babel/env']
               }))
               .pipe(uglify())
               .pipe(dest('dist/js', { sourcemaps: sourcemaps }))
  }
  else {
    var $out = src('assets/scripts/**/*.js', { sourcemaps: sourcemaps })
               .pipe(include())
               .pipe(rename({ suffix: '.dev' }))
               .pipe(babel({
                 presets: ['@babel/env']
               }))
               .pipe(dest('dist/js', { sourcemaps: sourcemaps }))
  }

  return $out;
}

function fonts() {
	return src('assets/fonts/**/*')
	.pipe(dest('dist/fonts'))
}

function images() {	
	return src('assets/images/**/*')
	.pipe(dest('dist/images'))
}

function clean_js(){
	if ( typeof arg.env != 'undefined' && arg.env == 'prod' ){
    return del('dist/js/**/*.min.js', {force:true});
  }
  else {
    return del('dist/js/**/*.dev.js', {force:true});
  }
}

function clean_css(){
	if ( typeof arg.env != 'undefined' && arg.env == 'prod' ){
    return del('dist/css/**/*.min.css', {force:true});
  } else {
    return del('dist/css/**/*.dev.css', {force:true});
  }
}

function clean_fonts(){return del('dist/fonts/**', {force:true});}
function clean_images(){return del('dist/images/**', {force:true});}

exports.js = series(clean_js, js);
exports.css = series(clean_css, css);
exports.fonts = series(clean_fonts, fonts);
exports.images = series(clean_images, images);
exports.default = series(clean_css, css, clean_js, js, clean_fonts, fonts, clean_images, images);

exports.watch = function() {
  watch('assets/styles/**/*.scss', series(clean_css, css));
  watch('assets/scripts/**/*.js', series(clean_js, js));
  watch('assets/fonts/**/*', series(clean_fonts, fonts));
  watch('assets/images/**/*', series(clean_images, images));
};
