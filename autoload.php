<?php
spl_autoload_register(function ($class) {
    if (strpos($class, 'TypechoPlugin\\LskyPro\\') === 0) {
        $path = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen('TypechoPlugin\\LskyPro\\'))) . '.php';
        if (file_exists($path)) {
            require $path;
        }
    }
});
