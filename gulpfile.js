var fs = require('fs');

var gulp = require('gulp');

var rename = require('gulp-rename');
var rimraf = require('gulp-rimraf');
var insert = require('gulp-insert');
var shell = require('gulp-shell');

gulp.task('clean', function(){
  gulp.src('autogen/*')
    .pipe(rimraf())

  gulp.src('virtfs/etc/hosts')
    .pipe(rimraf())

  return gulp.src('virtfs/etc/named/conf/*.conf')
    .pipe(rimraf())
});

gulp.task('php-tasks', [ 'clean' ], function(){
  return gulp.src('php-tasks/*.php')
    .pipe(shell([
      'php <%= file.path %>'
    ]))
});

gulp.task('hosts', [ 'php-tasks' ], function(){
  header = fs.readFileSync('src/header.hosts');

  return gulp.src('autogen/autogen.hosts')
    .pipe(insert.prepend(header))
    .pipe(rename('hosts'))
    .pipe(gulp.dest('virtfs/etc/'))
});

gulp.task('build', [ 'hosts' ], function(){
  copyright = fs.readFileSync('copyright.txt');

  return gulp.src('autogen/*.conf')
    .pipe(insert.prepend(copyright))
    .pipe(gulp.dest('virtfs/etc/named/conf/'))
});

gulp.task('default', [ 'build' ]);
