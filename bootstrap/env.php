<?php

/*
  |--------------------------------------------------------------------------
  | Detect The Application Environment
  |--------------------------------------------------------------------------
  |
  | Laravel takes a dead simple approach to your application environments
  | so you can just specify a machine name for the host that matches a
  | given environment, then we will automatically detect it for you.
  |
 */
$env = $app->detectEnvironment(function () {
  	$envPath = __DIR__.'/../.env';
  	if (file_exists($envPath)) {
  		$envValue= trim(file_get_contents($envPath));
  		putenv("APP_ENV=$envValue");
  		$env = getenv('APP_ENV');
  		if ($env && file_exists(__DIR__.'/../.'.$env.'.env')) {
  			$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../', '.'.$env.'.env');
  			$dotenv->load();
        }
    }
});
