<?php
namespace Rocketeer\Traits\BashModules;

use Mockery;
use Rocketeer\TestCases\RocketeerTestCase;

class BinariesTest extends RocketeerTestCase
{
	public function testCanSetCustomPathsForBinaries()
	{
		$binary = __DIR__.'/../../../bin/rocketeer';
		$this->mockConfig(['rocketeer::paths.composer' => $binary]);

		$this->assertEquals($binary, $this->task->which('composer'));
	}

	public function testStoredPathsAreInvalidatedIfIncorrect()
	{
		$this->mock('rocketeer.remote', 'Remote', function ($mock) {
			return $mock
				->shouldReceive('run')->with(['which composer'], Mockery::any())->andReturn(null)
				->shouldReceive('run')->with(['which'], Mockery::any())->andReturn(null)
				->shouldReceive('run')->with(['which foobar'], Mockery::any())->andReturn('foobar not found')
				->shouldReceive('run')->with(['which '.$this->binaries['composer']], Mockery::any())->andReturn($this->binaries['composer'])
				->shouldReceive('runRaw')->andReturn('false');
		}, false);

		$this->localStorage->set('paths.composer', 'foobar');

		$this->assertEquals('composer', $this->task->which('composer'));
		$this->assertNull($this->localStorage->get('paths.composer'));
	}

	public function testCanSetPathToPhpAndArtisan()
	{
		$this->mockConfig(array(
			'rocketeer::paths.php'     => $this->binaries['php'],
			'rocketeer::paths.artisan' => $this->binaries['php'],
		));

		$this->assertEquals($this->binaries['php'].' '.$this->binaries['php'].' migrate', $this->task->artisan()->migrate());
	}

	public function testFetchesBinaryIfNotSpecifiedOrNull()
	{
		$this->mockConfig(array(
			'rocketeer::paths.php' => $this->binaries['php'],
		));

		$this->assertEquals($this->binaries['php'].' artisan migrate', $this->task->artisan()->migrate());
	}

	public function testCanGetBinary()
	{
		$whichGrep = exec('which grep');
		$grep      = $this->task->which('grep');

		$this->assertEquals($whichGrep, $grep);
	}

	public function testCanRunComposer()
	{
		$this->usesComposer(true);
		$this->mock('rocketeer.command', 'Illuminate\Console\Command', function ($mock) {
			return $mock
				->shouldIgnoreMissing()
				->shouldReceive('line')
				->shouldReceive('option')->andReturn([]);
		});

		$this->task('Dependencies')->execute();
		$this->assertCount(2, $this->history->getFlattenedHistory()[0]);
	}

	public function testDoesntRunComposerIfNotNeeded()
	{
		$this->usesComposer(false);
		$this->mock('rocketeer.command', 'Illuminate\Console\Command', function ($mock) {
			return $mock
				->shouldIgnoreMissing()
				->shouldReceive('line')
				->shouldReceive('option')->andReturn([]);
		});

		$this->task('Dependencies')->execute();
		$this->assertEmpty($this->history->getFlattenedHistory());
	}
}
