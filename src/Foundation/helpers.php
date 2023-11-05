<?php

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

if (!function_exists('dd')) {
    #[NoReturn] function dd(mixed $variable): void
    {
        $cloner = new VarCloner();

        $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();
        $dumper->dump($cloner->cloneVar($variable));

        die(1);
    }
}

if (!function_exists('dump')) {
    function dump(mixed $variable): void
    {
        $cloner = new VarCloner();

        $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();
        $dumper->dump($cloner->cloneVar($variable));
    }
}