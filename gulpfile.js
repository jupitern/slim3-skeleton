'use strict';

var assetsBaseDir   = "./resources/assets/",
    vendorsBaseDir  = "./node_modules/",
    outDirectory    = "./public/assets/",
    pathsFixed      = false;

var paths = {
    app: {
        css:    ["app/*.css", "app/*.scss", "app/*.sass"],
        js:     ["app/*.js"],
        images: ["app/images/**"],
        vendor: [
            // jquery
            "jquery/dist/jquery.min.js",
            "jquery-serializeobject/jquery.serializeObject.js",
            "jquery-slimscroll/jquery.slimscroll.min.js",
            //bootstrap
            "bootstrap/dist/js/bootstrap.min.js",
            "bootstrap/dist/css/bootstrap.min.css",
            "bootstrap/dist/fonts/*.{eot,ttf,woff,woff2,svg}",
            // bootstrap sweet alert
            "bootstrap-sweetalert/dist/sweetalert.min.js",
            "bootstrap-sweetalert/dist/sweetalert.css",
            // bootstrap datetime picker
            "bootstrap-datetime-picker/bootstrap-datetimepicker.min.css",
            "bootstrap-datetime-picker/bootstrap-datetimepicker.min.js",
            // font-awesome
            "font-awesome/css/font-awesome.min.css",
            "font-awesome/fonts/*.{eot,ttf,woff,woff2,svg}",
            // select2
            "select2/dist/js/select2.min.js",
            "select2/dist/css/select2.min.css",
            "select2-bootstrap-theme/dist/select2-bootstrap.min.css",
            // metis menu
            "metismenu/dist/metisMenu.min.js",
            "metismenu/dist/metisMenu.min.css",
            // datatables
            "datatables/media/js/jquery.dataTables.min.js",
            "datatables.net-bs/js/dataTables.bootstrap.js",
            "datatables.net-bs/css/dataTables.bootstrap.css",
            // dateformat
            "jquery-dateformat/dist/jquery-dateFormat.min.js",
            // MetisMenu
            "metismenu/src/metisMenu.css",
            "metismenu/src/metisMenu.js"



        ]
    }
};

var gulp = require('gulp')
    ,sass = require('gulp-sass')
    ,concat = require('gulp-concat')
    ,minify = require('gulp-minify')
    ,sourcemaps = require('gulp-sourcemaps')
    ,filter = require('gulp-filter')
    ,clean = require('gulp-clean')
    ,rename = require("gulp-rename")
    ,bowerFixCss = require('gulp-bower-fix-css-path')
    ,runSequence = require("run-sequence")
    ,del = require("del");


function fixPaths() {
    for (var siteName in paths) {
        // add basePath to all paths
        for (var path in paths[siteName]) {
            var pathPrefix = path == 'vendor' ? vendorsBaseDir : assetsBaseDir;

            paths[siteName][path] = paths[siteName][path].map(function (val) {
                return pathPrefix + val;
            });
        }
    }
    pathsFixed = true;
}


gulp.task('assets', function () {

    for (var siteName in paths) {

        if (!pathsFixed) fixPaths();
        var outDir = outDirectory + siteName;
        console.log("compiling asset files for " + siteName);

        // images
        const imgFilter = filter(
            ['**/*.jpg', '**/*.png', '**/*.gif', '**/*.ico', '**/*.svg'],
            {restore: true}
        );

        gulp.src(paths[siteName].images)
            .pipe(imgFilter)
            .pipe(gulp.dest(outDir+'/images/'))
            .pipe(imgFilter.restore);

        // css
        gulp.src(paths[siteName].css)
            .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
            .pipe(sourcemaps.init())
            .pipe(concat('app.css'))
            .pipe(sourcemaps.write())
            .pipe(gulp.dest(outDir));

        // js
        gulp.src(paths[siteName].js)
            .pipe(sourcemaps.init())
            .pipe(concat('app.js'))
            .pipe(minify({
                ext:{
                    src:'-debug.js',
                    min:'.js'
                },
                exclude: ['tasks'],
                ignoreFiles: ['.combo.js', '-min.js', '.min.js']
            }))
            .pipe(sourcemaps.write())
            .pipe(gulp.dest(outDir));
    }
});


gulp.task('vendors', function () {

    for (var siteName in paths) {

        if (!pathsFixed) fixPaths();
        var outDir = outDirectory + siteName;
        console.log("compiling vendor files for " + siteName);

        // vendor css
        const cssFilter = filter('**/*.css');

        gulp.src(paths[siteName].vendor, {base: vendorsBaseDir})
            .pipe(cssFilter)
            .pipe(bowerFixCss({
                "debug":false,
                "absolutePath": "vendor/",
                "types":{
                    "fonts":{
                        extensions: [".eot", ".woff", ".ttf", ".woff2"],
                        prefixPath: "fonts/"
                    },
                    "imgs":{
                        extensions: [".png", ".jpg", ".gif", ".jpeg", ".ico", ".svg"],
                        prefixPath: "images/"
                    }
                }
            }))
            .pipe(concat('vendor.css'))
            .pipe(gulp.dest(outDir+'/'));

        // vendor js
        const jsFilter = filter('**/*.js');

        gulp.src(paths[siteName].vendor, {base: vendorsBaseDir})
            .pipe(jsFilter)
            .pipe(concat('vendor.js'))
            .pipe(gulp.dest(outDir));

        // vendor - images, fonts, other files
        const fontsFilter = filter(['**/*.otf', '**/*.eot', '**/*.svg', '**/*.ttf', '**/*.woff', '**/*.woff2']);
        const imgFilter = filter(['**/*.jpg', '**/*.png', '**/*.gif']);

        gulp.src(paths[siteName].vendor, {base: vendorsBaseDir})
            .pipe(fontsFilter)
            .pipe(rename(function(path){
                var parts = path.dirname.split('\\');
                path.dirname = parts[0] + '\\fonts';
            }))
            .pipe(gulp.dest(outDir+'/vendor/'));

        gulp.src(paths[siteName].vendor, {base: vendorsBaseDir})
            .pipe(imgFilter)
            .pipe(rename(function(path){
                var parts = path.dirname.split('\\');
                path.dirname = parts[0] + '\\images';
            }))
            .pipe(gulp.dest(outDir+'/vendor/'));
    }
});

gulp.task('clean', function () {
    return del(outDirectory);
});


gulp.task('watch', function () {
    gulp.watch(assetsBaseDir + '**/*', ['assets']);
});


gulp.task('run', function(done) {
    return runSequence(
        'vendors', 'assets', 'watch', function () {
            done();
        }
    );
});


gulp.task('default', function() {
    console.log('');
    console.log('gulp clean - delete all files');
    console.log('gulp vendors - compile vendor files');
    console.log('gulp assets - compile application files');
    console.log('gulp run - execute all previous tasks by order');
    console.log('gulp watch - watch for asset changes');
    console.log('');
    return null;
});