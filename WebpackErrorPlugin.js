module.exports = class WebpackErrorPlugin {
    apply(compiler) {
        function doneHook(stats) {
            if (stats.compilation.errors && stats.compilation.errors.length) {
                console.log(stats.compilation.errors);
                process.exit(1);
            }
        }
        compiler.hooks.done.tap("WebpackErrorPlugin", doneHook);
    }
}
