<?php

// Check PHP version
$minPhpVersion = '8.1';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION
    );

    exit($message);
}

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

// Define ROOTPATH - FIX for Docker structure
define('ROOTPATH', realpath(FCPATH . '..') . DIRECTORY_SEPARATOR);

// Load our paths config file
require ROOTPATH . 'app/Config/Paths.php';

$paths = new Config\Paths();

// Location of the framework bootstrap file
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';

$app = CodeIgniter\Boot::bootWeb($paths);
$app->run();