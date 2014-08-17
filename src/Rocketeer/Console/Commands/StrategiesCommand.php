<?php
namespace Rocketeer\Console\Commands;

use Rocketeer\Abstracts\AbstractCommand;
use Symfony\Component\Console\Helper\Table;

class StrategiesCommand extends AbstractCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:strategies';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Lists the available options for each strategy';

	/**
	 * Run the tasks
	 *
	 * @return void
	 */
	public function fire()
	{
		$strategies = array(
			'deploy'       => ['Clone', 'Copy', 'Sync'],
			'test'         => ['Phpunit'],
			'migrate'      => ['Artisan'],
			'dependencies' => ['Composer', 'Bundler', 'Npm', 'Bower', 'Polyglot'],
		);

		$table = new Table($this->getOutput());
		$table->setHeaders(['Strategy', 'Implementation', 'Description']);
		foreach ($strategies as $strategy => $implementations) {
			foreach ($implementations as $implementation) {
				$instance = $this->laravel['rocketeer.builder']->buildStrategy($strategy, $implementation);
				$table->addRow([$strategy, $implementation, $instance->getDescription()]);
			}
		}

		$table->render();
	}
}
