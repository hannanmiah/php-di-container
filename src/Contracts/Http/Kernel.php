<?php

namespace Legend\Contracts\Http;

use Legend\Contracts\Foundation\Application;

interface Kernel
{
    public function bootstrap();


    public function handle();


    public function terminate(): void;

    /**
     * Get the Laravel application instance.
     *
     * @return Application
     */
    public function getApplication(): Application;
}