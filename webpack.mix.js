const path = require("path");
const fs = require("fs-extra");
const mix = require("laravel-mix");

mix.js("resources/js/app.js", "public/dist/js")
    .sass("resources/sass/app.scss", "public/dist/css")
    .sourceMaps()
    .disableNotifications();

if (mix.inProduction()) {
    require("laravel-mix-versionhash");
    mix.versionHash();
} else {
    mix.sourceMaps();
}

/*mix.options({
    hmrOptions: {
        host: 'radiology.test',  // site's host name
        port: 8080,
    }
});*/

mix.webpackConfig({
    /*devServer: { 
        proxy: {
            host: '127.0.0.1',  // host machine ip 
            port: 8080,
        },
        watchOptions:{
            aggregateTimeout:200,
            poll:5000
        },

    },*/
    plugins: [],
    resolve: {
        extensions: [".js", ".json", ".vue"],
        alias: {
            "~": path.join(__dirname, "./resources/js"),
            Quasar: path.join(
                __dirname,
                "./node_modules/quasar/dist/quasar.umd.min.js"
            ),
            iePolyfill: path.join(
                __dirname,
                "./node_modules/quasar/dist/quasar.ie.polyfills.umd.min.js"
            ),
            i18nLang: path.join(
                __dirname,
                "./node_modules/quasar/dist/lang/es.umd.min.js"
            ),
            QuasarCss: path.join(
                __dirname,
                "./node_modules/quasar/dist/quasar.min.css"
            )
        }
    },
    output: {
        //chunkFilename: 'dist/js/[name].[chunkhash].js',
        chunkFilename: "dist/js/[chunkhash].js",
        path: mix.config.hmr ? "/" : path.resolve(__dirname, "./public/build")
    }
});
mix.then(() => {
    if (!mix.config.hmr) {
        process.nextTick(() => publishAseets());
    }
});

function publishAseets() {
    const publicDir = path.resolve(__dirname, "./public");

    if (mix.inProduction()) {
        fs.removeSync(path.join(publicDir, "dist"));
    }

    fs.copySync(
        path.join(publicDir, "build", "dist"),
        path.join(publicDir, "dist")
    );
    fs.removeSync(path.join(publicDir, "build"));
}
