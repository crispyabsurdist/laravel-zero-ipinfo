<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use GuzzleHttp\Client;

class CheckSiteStatus extends Command
{
	protected $signature = 'check:status';
	protected $description = 'Check the status of all sites on Kinsta';

	public function handle()
	{
		$apiKey = 'ce053bf5b76206f1294112c4ece92a4467c22cccad7c7e44a53433dd306517a7';

		$client = new Client(['base_uri' => 'https://mykinsta.com/api/v1/']);

		try {
			$response = $client->request('GET', 'sites', [
				'headers' => [
					'Authorization' => "Bearer $apiKey"
				]
			]);

			$sites = json_decode($response->getBody(), true);

			foreach ($sites['data'] as $site) {
				$this->info("Site: {$site['name']}, Status: {$site['status']}");
			}

		} catch (\Exception $e) {
			$this->error("Error: " . $e->getMessage());
		}
	}
}
