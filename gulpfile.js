const gulp = require("gulp");
const sass = require("gulp-sass")(require("sass"));
const rename = require("gulp-rename");

// Dossier source et destination
const paths = {
	styles: {
		src: "assets/styles/**/*.scss",
		dest: "public/css",
	},
};

// Tâche de compilation Sass
function styles() {
	return gulp
		.src(paths.styles.src)
		.pipe(sass().on("error", sass.logError))
		.pipe(rename({ suffix: ".min" }))
		.pipe(gulp.dest(paths.styles.dest));
}

// Tâche de surveillance
function watch() {
	gulp.watch(paths.styles.src, styles);
}

// Tâches par défaut
const build = gulp.series(styles, watch);

exports.styles = styles;
exports.watch = watch;
exports.default = build;
