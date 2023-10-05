<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class CheckKinstaStatus extends Command
{
	protected $signature = 'kinsta:check {label}';
	protected $description = 'Fetch and display Kinsta sites with a specific label';

	protected $kinstaAPIUrl = 'https://api.kinsta.com/v2';

	public function handle()
	{
		$apiKey = env('KINSTA_API_KEY', 'placeholder');
		$companyId = env('KINSTA_COMPANY_ID', 'placeholder');
		$label = $this->argument('label');

		$this->fetchKinstaSites($apiKey, $companyId, $label);
	}

	protected function fetchKinstaSites($apiKey, $companyId, $labelToFilter)
	{
		$query = http_build_query(['company' => $companyId]);
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_HTTPHEADER => [
				"Authorization: Bearer $apiKey"
			],
			CURLOPT_URL => "{$this->kinstaAPIUrl}/sites?$query",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => "GET",
		]);

		$response = curl_exec($curl);
		$error = curl_error($curl);

		$data = json_decode($response, true)['company']['sites'] ?? [];

		curl_close($curl);

		if ($error) {
			$this->error("cURL Error: " . $error);
			return null;
		}

		// Filter sites by label with case-insensitivity
		$data = array_filter($data, function ($site) use ($labelToFilter) {
			$siteLabels = array_column($site['site_labels'], 'name');
			$siteLabelsLower = array_map('strtolower', $siteLabels);
			return in_array(strtolower($labelToFilter), $siteLabelsLower);
		});

		// Display the filtered sites
		if ($data) {
			foreach ($data as $site) {
				$this->info("Site Name: {$site['name']}");
				$this->info("Site ID: {$site['id']}");
				$this->info("Site Labels: " . implode(', ', array_column($site['site_labels'], 'name')));
				$this->info("----------");
			}
		} else {
			$this->info("No sites found with the label: $labelToFilter");
		}

		return $data ?? null;
	}
}
