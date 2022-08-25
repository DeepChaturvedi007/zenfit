const copy = require('copy');
const fs = require('fs');
const path = require('path');
const rimraf = require('rimraf');

const appDirectory = fs.realpathSync(process.cwd());
const resolveApp = function(relativePath) {
  return path.resolve(appDirectory, relativePath);
};

try {
  rimraf.sync(resolveApp('../../web/js/meals'));
} catch(e) {}

copy('build/**/*', resolveApp('../../web/js/meals'), function (err) {
  if (err) throw err;
});
