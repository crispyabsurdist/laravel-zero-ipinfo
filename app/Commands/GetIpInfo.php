<?php

namespace App\Commands;

use function Termwind\{render};
use LaravelZero\Framework\Commands\Command;

class GetIpInfo extends Command
{
	protected $signature = 'getipinfo {url}';
	protected $description = 'Get IP information about a URL';

	public function handle(): void
	{
		$url = $this->argument('url');
		$ip = gethostbyname($url);

		// TODO: Validate URL with active or inactive status
		// $this->runPingCommand($url);
		$this->renderIpInfo($url, $ip);
		$this->scanPorts($ip);
	}

	protected function renderIpInfo($url, $ip)
	{
		render(<<<HTML
		<div class='flex p-2 bg-black w-48'>
			<span class='font-bold w-18'>URL:</span>
			<span class='flex-1'>{$url}</span>
		</div>
		HTML);

		render(<<<HTML
		<div class='flex p-2 bg-black w-48'>
			<span class='font-bold w-18'>IP Address:</span>
			<span class='flex-1'>{$ip}</span>
		</div>
		HTML);
	}

	protected function scanPorts($ip)
	{
		$portsToScan = [80, 443, 22, 21, 25];

		foreach ($portsToScan as $port) {
			$status = $this->isPortOpen($ip, $port) ? 'Open' : 'Closed';
			$statusColor = $status === 'Open' ? 'text-green-500' : 'text-red-500';

			render(<<<HTML
			<div class='flex p-2 bg-slate-800 w-48'>
				<span class='font-bold w-18'>Port {$port}: </span>
				<span class='flex-1 {$statusColor}'>{$status}</span>
			</div>
			HTML);
		}
	}

	protected function isPortOpen($ip, $port)
	{
		$connection = @fsockopen($ip, $port, $errno, $errstr, 2);

		if (is_resource($connection)) {
			fclose($connection);
			return true;
		} else {
			return false;
		}
	}

	protected function runPingCommand($url)
	{
		$output = shell_exec("ping -c 4 " . escapeshellarg($url));

		render(<<<HTML
			<div class='flex p-2 bg-slate-800'>
				<span class='font-bold w-18'>Ping: </span>
				<span class='flex-1 text-green-500'>Site Active {{ $output }}</span>
			</div>
		HTML);
	}
}
