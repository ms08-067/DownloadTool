<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class CustomLog extends Facade
{
    /**
     * Get Facade Accessor for Current Class
     *
     * @return string Accessor
     */
    protected static function getFacadeAccessor()
    {
        return 'customlog';
    }
    
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (! isset($args[0])) {
            return;
        }
        /**dump($args);*/

        $message = $args[0];
        $file = isset($args[1]) ? $args[1] : 'laravel';
        /**dump($file);*/
        // Maximum number of lines in one log file
        $max = 10000;

        // Get Monolog Instance
        $logger = Log::getLogger();
        /**dd($logger);*/

        // Trim log file to a max length
        $path = storage_path('logs'.$file);
        /**dd($path);*/
        if (! file_exists($path)) {
            fopen($path, "w");
        }
        $lines = file($path);
        if (count($lines) >= $max) {
            file_put_contents($path, implode('', array_slice($lines, -$max, $max)));
            exec('sudo chmod 664 '. $path);
            exec('sudo chown www-data:www-data '. $path);
            //chmod($path, 0777);
        }

        // Define custom Monolog handler
        $handler = new StreamHandler($path, Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        // Set defined handler and log the message
        $logger->setHandlers([$handler]);
        $logger->$method($message);
    }
}
