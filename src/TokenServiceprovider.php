<?php
namespace IEMaker\Token;

use Illuminate\Support\ServiceProvider;

class TokenServiceProvider extends ServiceProvider {

	public function boot() {
		$this->publishes([
			__DIR__ . '/../config/token.php' => config_path('token.php'),
		]);
	}

	public function register() {
		$this->app->bind('token', function($app){
			return new Token(
				$app['Illuminate\Config\Repository'],
				$app['Illuminate\Session\Store'],
			);
		});
	}

}