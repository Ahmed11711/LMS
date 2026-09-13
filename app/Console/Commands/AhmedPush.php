<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AhmedPush extends Command
{
    /**
     * Usage:
     *   php artisan ahmed:push "commit message"
     */
    protected $signature = 'ahmed:push {message : The commit message}';

    protected $description = 'Runs git fetch + status + add + commit + push in one command';

    public function handle(): int
    {
        $message = $this->argument('message');
        $basePath = base_path();

        // 1) Fetch the latest state from the remote without changing anything
        $this->info('== Fetching latest state from GitHub ==');
        $this->runProcess(['git', 'fetch', 'origin'], $basePath);

        // 2) Show current status (ahead/behind origin, etc.)
        $this->info('');
        $this->info('== git status ==');
        $this->runProcess(['git', 'status'], $basePath);

        // Stop if there are no changes to commit
        $clean = $this->runProcess(['git', 'status', '--porcelain'], $basePath, false, true);
        if (trim($clean) === '') {
            $this->warn('No changes to commit.');
            return self::SUCCESS;
        }

        // 3) add + commit + push
        $this->info('');
        $this->info('== Running add + commit + push ==');
        $this->runProcess(['git', 'add', '.'], $basePath);
        $this->runProcess(['git', 'commit', '-m', $message], $basePath);
        $this->runProcess(['git', 'push'], $basePath);

        $this->info('');
        $this->info('Done.');

        return self::SUCCESS;
    }

    /**
     * Runs a shell command and prints its output.
     *
     * @param array<int, string> $command
     * @param string $cwd
     * @param bool $silent If true, output is not printed (still returned as a string)
     * @param bool $quiet If true, suppresses both output and error printing
     */
    protected function runProcess(array $command, string $cwd, bool $silent = false, bool $quiet = false): string
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(120);
        $process->run();

        $output = $process->getOutput() . $process->getErrorOutput();

        if (! $silent && ! $quiet) {
            $this->line(trim($output));
        }

        if (! $process->isSuccessful() && ! $quiet) {
            $this->error('Command failed: ' . implode(' ', $command));
        }

        return $output;
    }
}
