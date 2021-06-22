<?php
/**
 * Sifra
 * @author Joël Phaka
 */

require_once __DIR__.'/../__bootstrap.php';

use Sifra\Http\Routing\Router;

// Initialize app routing
Router::init(function ($error) {
    echo $error->getMessage();
});


