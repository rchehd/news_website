const gulp = require('gulp');
const autoprefixer = require('gulp-autoprefixer');
const babel = require('gulp-babel');
const clean = require('gulp-clean');
const imagemin = require('gulp-imagemin');
const mode = require('gulp-mode')({
  modes: ['production', 'development'],
  default: 'development',
  verbose: false
});
const rename = require('gulp-rename');
const sass = require('gulp-sass')(require('sass'));
const sassGlob = require('gulp-sass-glob');
const sourcemaps = require('gulp-sourcemaps');
const uglify = require('gulp-uglify');
const spritesmith = require('gulp.spritesmith');
const merge = require('merge-stream');

////////////////////////////////////////////////////////////////////////////////
// Options and variables.
////////////////////////////////////////////////////////////////////////////////

const srcCleanOptions = {
  read: false
};
const cleanOptions = {
  force: true
};
const babelOptions = {
  presets: ['@babel/env']
};
const imageMinSvgoOptions = {
  plugins: [
    { removeUselessDefs: false },
    { cleanupIDs: false},
    { removeViewBox: false}
  ]
};
const sassOptions = {
  outputStyle: (mode.production() ? 'compressed' : 'compact')
};
const uglifyOptions = {
  compress: {
    drop_debugger: !!mode.production()
  }
};

const themesPath = 'web/themes/custom';
const webThemesPath = 'web/themes/custom';
const projectThemePath = themesPath + '/wiw_theme';
const webProjectThemePath = webThemesPath + '/wiw_theme';

const paths = {
  wiw_theme: {
    styles: {
      src: projectThemePath + '/scss/**/*.{scss,sass}',
      dest: projectThemePath + '/css'
    },
    patternsStyles: {
      src: projectThemePath + '/templates/patterns/**/styles/*.{scss,sass}'
    },
    scripts: {
      src: projectThemePath + '/js/**/*.es6.js'
    },
    images: {
      clean: projectThemePath + '/images/optimized/**/*.{png,jpg,gif,svg}',
      src: projectThemePath + '/images/source/**/*.{png,jpg,gif,svg}',
      dest: projectThemePath + '/images/optimized'
    },
  },
};

////////////////////////////////////////////////////////////////////////////////
// Task definitions.
////////////////////////////////////////////////////////////////////////////////

// Base theme.
gulp.task('styles_wiw_theme', function() {
  return themeStyles('wiw_theme');
});
gulp.task('pattern_styles_wiw_theme', function() {
  return themePatternStyles('wiw_theme');
});
gulp.task('es6scripts_wiw_theme', function() {
  return themeES6Scripts('wiw_theme');
});
gulp.task('clean_images_wiw_theme', function () {
  return themeCleanImages('wiw_theme');
});
gulp.task('build_images_wiw_theme', function () {
  return themeBuildImages('wiw_theme');
});




gulp.task('images', gulp.series(
  'clean_images_wiw_theme',
  'build_images_wiw_theme',
));

// Watch.
gulp.task('watch', function (done) {
  if (mode.production()) {
    return done();
  }

  gulp.watch(paths['wiw_theme'].styles.src, gulp.series('styles_wiw_theme'));
  gulp.watch(paths['wiw_theme'].patternsStyles.src, gulp.series('pattern_styles_wiw_theme'));
  gulp.watch(paths['wiw_theme'].scripts.src, gulp.series('es6scripts_wiw_theme'));
  gulp.watch(paths['wiw_theme'].images.src, gulp.series(
    'images',
    'styles_wiw_theme',
    'pattern_styles_wiw_theme'
  ));
});

// Default task.
gulp.task('default', gulp.series(
  'images',
  'styles_wiw_theme',
  'pattern_styles_wiw_theme',
  'es6scripts_wiw_theme',
  'watch'
));

////////////////////////////////////////////////////////////////////////////////
// Functions.
////////////////////////////////////////////////////////////////////////////////

// Sass.
function themeStyles(themePathKey) {
  return gulp.src(paths[themePathKey].styles.src)
    .pipe((mode.development(sourcemaps.init())))
    .pipe(sassGlob())
    .pipe((mode.production(sass(sassOptions))))
    .pipe((mode.development(sass(sassOptions).on('error', sass.logError))))
    .pipe(autoprefixer())
    .pipe((mode.development(sourcemaps.write('.'))))
    .pipe(gulp.dest(paths[themePathKey].styles.dest));
}
function themePatternStyles(themePathKey) {
  return gulp.src(paths[themePathKey].patternsStyles.src)
    .pipe((mode.development(sourcemaps.init())))
    .pipe(sassGlob())
    .pipe((mode.production(sass(sassOptions))))
    .pipe((mode.development(sass(sassOptions).on('error', sass.logError))))
    .pipe(autoprefixer())
    .pipe((mode.development(sourcemaps.write('.'))))
    .pipe(gulp.dest(file => file.base));
}

// ES6.
function themeES6Scripts(themePathKey) {
  return gulp.src(paths[themePathKey].scripts.src)
    .pipe((mode.development(sourcemaps.init())))
    .pipe(babel(babelOptions))
    .pipe(uglify(uglifyOptions))
    .pipe(rename(path => {
      path.basename = path.basename.replace('.es6', '');
    }))
    .pipe((mode.development(sourcemaps.write('.'))))
    .pipe(gulp.dest(file => file.base));
}
function themeES6PatternsScripts(themePathKey) {
  return gulp.src(paths[themePathKey].patternsScripts.src)
    .pipe((mode.development(sourcemaps.init())))
    .pipe(babel(babelOptions))
    .pipe(uglify(uglifyOptions))
    .pipe(rename(path => {
      path.basename = path.basename.replace('.es6', '');
    }))
    .pipe((mode.development(sourcemaps.write('.'))))
    .pipe(gulp.dest(file => file.base));
}

// Images.
function themeCleanImages(themePathKey) {
  return gulp.src(paths[themePathKey].images.clean, srcCleanOptions)
    .pipe(clean(cleanOptions));
}
function themeBuildImages(themePathKey) {
  return gulp.src(paths[themePathKey].images.src)
    .pipe(imagemin([
      imagemin.svgo(imageMinSvgoOptions)
    ]))
    .pipe(gulp.dest(paths[themePathKey].images.dest));
}


