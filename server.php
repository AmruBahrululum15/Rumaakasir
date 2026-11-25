<?php

// Artisan's PHP development server serves existing files before Laravel runs.
// Keep legacy employee photos behind the same access checks as Apache.
$path = str_replace('\\', '/', rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''));
if (preg_match('#(^|/)\.\.(/|$)#', $path)
    || preg_match('#^/storage/(users|profiles|profile)(/|$)#i', $path)) {
    http_response_code(403);
    return;
}

// Propagate false so PHP serves the requested static asset. Swallowing this
// return value produces an empty HTML response instead of CSS/JavaScript.
return require __DIR__.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';
