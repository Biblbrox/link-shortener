<?php

namespace UrlShortener;

class Render
{
    public static function renderFile($path)
    {
        ob_start();
        include $path;
        $template = ob_get_contents();
        ob_get_clean();
    }
}