<?php
spl_autoload_register(function ($class) {

    $dirSep = DIRECTORY_SEPARATOR;

    $namespace = substr($class, 0, strrpos($class, '\\'));
    $className = empty($namespace) ? $class : substr($class, strrpos($class, '\\') + 1);

    $dir = str_replace('\\', $dirSep, $namespace);
    $dir = is_dir($dir) ? $dir : str_replace('\\', $dirSep, lcfirst($namespace));

    $dir = str_ireplace('Sifra', 'src', $dir);


    $filename = __DIR__ . $dirSep . $dir . $dirSep . $className . '.php';

    if (file_exists($filename)) {
        require $filename;
    } else {
        // For debug purposes only
        die("<br>Class{ {$class} } not found in path: {$filename}");
    }
});