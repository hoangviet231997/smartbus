const spawn = require('child_process').spawn;
const spawnSync = require('child_process').spawnSync;

console.log("=============== COMPOSER install for web service ===============");
spawnSync('composer', ['install'], { cwd: '.', stdio: [process.stdin, process.stdout, process.stderr] });

console.log("=============== Update NPM packages for web SERVICE ===============");
spawnSync('yarn', ['install'], { cwd: '.', stdio: [process.stdin, process.stdout, process.stderr] });

console.log("=============== Update NPM packages for web CLIENT app ===============");
spawnSync('yarn', ['install'], { cwd: './WebApp', stdio: [process.stdin, process.stdout, process.stderr] });

console.log("=============== Starting ARTISAN SERVE, ANGULAR BUILD WATCH and LARAVEL MIX WATCH ===============");
const ng_process = spawn('ng', ['build', '--watch'], { cwd: './WebApp' });
const artisan_process = spawn('php', ['artisan', 'serve'], { cwd: '.' });
const mix_process = spawn('yarn', ['watch'], { cwd: '.' });

var children = [];

children.push(ng_process);
children.push(artisan_process);
children.push(mix_process);

children.forEach(function(child) {
    child.stdout.pipe(process.stdout);
    child.stderr.pipe(process.stderr);
});

process.on('exit', function() {
  children.forEach(function(child) {
    child.kill();
  });
});

