const Encore = require("@symfony/webpack-encore");

if (!Encore.isRuntimeEnvironmentConfigured()) {
	Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore.setOutputPath("public/build/")
	.setPublicPath("/build")
	.addEntry("app", "./assets/app.js")
	.addStyleEntry("styles", "./assets/styles/app.scss")
	.splitEntryChunks()
	.enableSingleRuntimeChunk()
	.cleanupOutputBeforeBuild()
	.enableBuildNotifications()
	.enableSourceMaps(!Encore.isProduction())
	.enableVersioning(Encore.isProduction())
	.configureBabelPresetEnv((config) => {
		config.useBuiltIns = "usage";
		config.corejs = "3.23";
	})
	.enablePostCssLoader()
	.enableSassLoader()
	.addLoader({
		test: /\.css$/,
		use: ["style-loader", "css-loader"],
	});

module.exports = Encore.getWebpackConfig();
