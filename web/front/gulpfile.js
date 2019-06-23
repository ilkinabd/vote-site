var gulp = require("gulp"),
    sass = require("gulp-sass"),
    autoprefixer = require("gulp-autoprefixer"),
    plumber = require("gulp-plumber"),
    cache = require("gulp-cache"),
    imagemin = require("gulp-imagemin"),
    pngquant = require("imagemin-pngquant");
var paths =
    {
        scss:
            {
                lib: [
                    __dirname + "/bower_components/foundation-sites/scss"
                ],
                main: "scss/style.scss",
                watch: "scss/**/*.scss"
            },
        js:
            {
                main: "src/js/app.js",
                lib: [
                    __dirname + "/bower_components/jquery/dist/jquery.min.js",
                    __dirname + "/bower_components/foundation-sites/dist/js/foundation.min.js",
                    __dirname + "/bower_components/motion-ui/dist/motion-ui.min.js"
                ],
                watch: "src/js/app.js"
            },
        fonts:
            {
                main: "src/fonts/**/*",
                watch: "src/fonts/**/*"
            },
        images:
            {
                main:"src/images/**/*",
                watch:"src/images/**/*"
            }
    };

gulp.task("scss", function () {
    return gulp.src(paths.scss.main)
        .pipe(plumber())
        .pipe(sass({outputStyle: 'compressed'/*, includePaths: paths.scss.lib*/}))
        .pipe(autoprefixer(['last 2 versions', '> 1%', 'ie 8', 'ie 7', 'iOS >= 8', 'Safari >= 8']))
        .pipe(gulp.dest('css'))
});

gulp.task("fonts", function () {
    return gulp.src(paths.fonts.main)
        .pipe(gulp.dest("fonts"))
});

gulp.task("images", function () {
    return gulp.src(paths.images.main)
        .pipe(cache(
            imagemin({
                interlaced: true,
                progressive: true,
                svgoPlugins: [{removeViewBox: false}],
                use: [pngquant]
            })
        ))
        .pipe(gulp.dest("images"));
});

gulp.task("watch", [/*"images", "fonts",*/"scss"], function () {
    gulp.watch(paths.scss.watch, ["scss"]);
/*    gulp.watch(paths.fonts.watch, ["fonts"]);
    gulp.watch(paths.images.watch, ["images"]);*/
});