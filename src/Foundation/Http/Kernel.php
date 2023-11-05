<?php

namespace Legend\Foundation\Http;

use Legend\Contracts\Foundation\Application;
use Legend\Contracts\Http\Kernel as KernelContract;
use Legend\Http\Response;

class Kernel implements KernelContract
{

    public function bootstrap()
    {
        // TODO: Implement bootstrap() method.
    }

    public function terminate(): void
    {
        // TODO: Implement terminate() method.
    }

    public function getApplication(): Application
    {

    }

    public function handle()
    {

    }

    protected function sendRequestThroughRouter()
    {
        return Response::toJson([
            'message' => 'Hello World',
        ]);
    }
}