<?php
/**
 * Sifra - Simple Framework
 * @author Joël Phaka
 */

define('BASE_DIR', __DIR__);

if (is_dir(__DIR__.'/app')) {

    define('APP_DIR', __DIR__.'/app');
}

if (function_exists('mb_internal_encoding')) {
    
    mb_internal_encoding('UTF-8');
}


require_once __DIR__.'/__autoload.php';
require_once __DIR__.'/src/helpers.php';
require_once __DIR__.'/src/routes.php';

sessionStart();