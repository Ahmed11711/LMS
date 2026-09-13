<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AhmedPush extends Command
{
    /**
     * الاستخدام:
     *   php artisan ahmed:push "رسالة الكوميت"
     */
    protected $signature = 'ahmed:push {message : رسالة الكوميت}';

    protected $description = 'يعمل git fetch + status + add + commit + push في أمر واحد';

    public function handle(): int
    {
        $message = $this->argument('message');
        $basePath = base_path();

        // 1) جيب آخر تحديثات من الريموت من غير ما يغيّر حاجة
        $this->info('== بنجيب آخر حالة من GitHub ==');
        $this->runProcess(['git', 'fetch', 'origin'], $basePath);

        // 2) اعرض الحالة الحالية (فيه حاجة متأخرة/متقدمة عن origin ولا لأ)
        $this->info('');
        $this->info('== git status ==');
        $status = $this->runProcess(['git', 'status'], $basePath, true);

        // لو مفيش أي تغييرات، وقف من غير ما تعمل كوميت فاضي
        $clean = $this->runProcess(['git', 'status', '--porcelain'], $basePath, false, true);
        if (trim($clean) === '') {
            $this->warn('مفيش أي تعديلات لعمل كوميت ليها.');
            return self::SUCCESS;
        }

        // 3) add + commit + push
        $this->info('');
        $this->info('== بنعمل add + commit + push ==');
        $this->runProcess(['git', 'add', '.'], $basePath);
        $this->runProcess(['git', 'commit', '-m', $message], $basePath);
        $this->runProcess(['git', 'push'], $basePath);

        $this->info('');
        $this->info('تم بنجاح.');

        return self::SUCCESS;
    }

    /**
     * ينفذ أمر شل ويطبع النتيجة على الشاشة.
     *
     * @param array<int, string> $command
     * @param string $cwd
     * @param bool $silent لو true منطبعش النتيجة على الشاشة (لسه بترجع كـ string)
     * @param bool $quiet لو true منطبعش الأمر نفسه على الشاشة
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
            $this->error("فشل الأمر: " . implode(' ', $command));
        }

        return $output;
    }
}
