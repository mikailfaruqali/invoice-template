<?php

namespace Snawbar\InvoiceTemplate\Console;

use Illuminate\Console\Command;
use Snawbar\InvoiceTemplate\InvoiceTemplate;
use Symfony\Component\Process\Process;

class CheckCommand extends Command
{
    protected $signature = 'invoice-template:check';

    protected $description = 'Verify that the PDF engine and Chrome are installed and working';

    public function handle()
    {
        $binary = InvoiceTemplate::binaryPath();

        if (! is_file($binary)) {
            $this->components->error(sprintf('PDF engine not found at [%s]. Run "php artisan invoice-template:install".', $binary));

            return self::FAILURE;
        }

        $engine = $this->runEngine([$binary, '--version']);
        $chrome = $this->runEngine([$binary, '--detect-chrome', ...$this->chromeOption()]);

        $this->components->twoColumnDetail('PDF engine', $engine['ok'] ? sprintf('%s <fg=gray>%s</>', $engine['output'], $binary) : sprintf('<fg=red>%s</>', $engine['output']));
        $this->components->twoColumnDetail('Chrome', $chrome['ok'] ? $chrome['output'] : sprintf('<fg=red>%s</>', $chrome['output']));

        if ($engine['ok'] && $chrome['ok']) {
            $this->components->info('The PDF engine is ready.');

            return self::SUCCESS;
        }

        $this->components->error('One or more checks failed.');

        return self::FAILURE;
    }

    private function chromeOption(): array
    {
        $chrome = config('snawbar-invoice-template.chrome');

        return filled($chrome) ? ['--chrome', $chrome] : [];
    }

    private function runEngine(array $command): array
    {
        $process = new Process($command);
        $process->run();

        return [
            'ok' => $process->isSuccessful(),
            'output' => mb_trim($process->isSuccessful() ? $process->getOutput() : $process->getErrorOutput()),
        ];
    }
}
