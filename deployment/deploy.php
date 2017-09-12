<?php

/**
 * Usage:
 *
 * Deploying to production machine:
 * ./deployer rgapi:deploy production
 *
 * Deploying to test machine:
 * ./deployer rgapi:deploy production
 */

require 'recipe/laravel.php';

set('keep_releases', 5);
set('repository-for-test-machine', '/opt/hg/dmapi');
set('repository-for-live', 'ssh://user@dev.company.com//opt/hg/dmapi');

server('test2.company.com', 'test.company.com', 22)
    ->user('root')
    ->identityFile()
    ->stage('test')
    ->env('deploy_path', '/var/www/dmapi');

server('dmapi.company.com', '217.168.172.53', 22)
    ->user('pezo')
    ->identityFile()
    ->stage('production')
    ->env('deploy_path', '/var/www/dmapi');



// overwrite the default task because we need mercurial support
task('deploy:update_code', function(){
    if (input()->getArgument('stage') == 'test') {
        $repository = get('repository-for-test-machine');
        env('composer_options', 'install --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction');
    } elseif (input()->getArgument('stage') == 'production') {
        $repository = get('repository-for-live');
    }
    run("hg clone $repository {{release_path}} 2>&1");

    if (input()->getArgument('stage') == 'test') {
        run("cd {{release_path}} && hg checkout develop");
    } else {
        run("cd {{release_path}} && hg checkout master");
    }
})->desc('Update code from mercurial');


task('deploy:test', function(){
    if (input()->getArgument('stage') == 'test') {
        run("php {{deploy_path}}/current/artisan migrate:refresh");
        run("{{deploy_path}}/current/vendor/bin/phpunit -c {{deploy_path}}/current/phpunit.xml");
    }
})->desc('Run PHPUnit tests');

task('reload:apache2', function() {
    run('sudo service apache2 restart');
})->desc('Reloading apache2 server');

set('shared_dirs', [
    'storage/app',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'storage/uploads',
    'bootstrap/cache',
]);

// This is the main task which should be run
task('dmapi:deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:vendors',
    'deploy:shared',
    'deploy:symlink',
    'deploy:writable',
    'deploy:test',
    'cleanup',
])->desc('Deploy DMAPI project');

after('deploy', 'reload:apache2');
after('rollback', 'reload:apache2');
