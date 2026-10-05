<?php

namespace Snawbar\InvoiceTemplate\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use PharData;
use RuntimeException;
use Snawbar\InvoiceTemplate\InvoiceTemplate;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class InstallCommand extends Command
{
    protected $signature = 'invoice-template:install
                            {--force : Reinstall the engine even if it already exists}
                            {--tag=latest : GitHub release tag to download}
                            {--path= : Install offline from a local engine binary or release archive}
                            {--build : Build the engine from the package source with Go}';

    protected $description = 'Install the PDF engine for this operating system and architecture';

    public function handle()
    {
        $binary = InvoiceTemplate::binaryPath();

        if (File::exists($binary) && ! $this->option('force')) {
            $this->components->warn(sprintf('The PDF engine is already installed at [%s]. Use --force to reinstall it.', $binary));

            return self::SUCCESS;
        }

        try {
            File::ensureDirectoryExists(dirname($binary));

            match (TRUE) {
                $this->option('build') => $this->components->task('Building the PDF engine from source', fn () => $this->build($binary)),
                filled($this->option('path')) => $this->components->task(sprintf('Installing from %s', $this->option('path')), fn () => $this->install($this->option('path'), $binary)),
                default => $this->components->task(sprintf('Downloading %s', $this->assetName()), fn () => $this->download($binary)),
            };

            if (PHP_OS_FAMILY !== 'Windows') {
                chmod($binary, 0755);
            }
        } catch (Throwable $throwable) {
            $this->components->error(sprintf('Installation failed: %s', $throwable->getMessage()));

            return self::FAILURE;
        }

        $process = new Process([$binary, '--version']);
        $process->run();

        $this->components->info(sprintf('PDF engine installed at [%s] (%s).', $binary, mb_trim($process->getOutput())));

        return self::SUCCESS;
    }

    private function download(string $binary): bool
    {
        $archive = sprintf('%s/%s', sys_get_temp_dir(), $this->assetName());

        try {
            Http::timeout(300)->sink($archive)->get($this->downloadUrl($this->assetName()))->throw();

            return $this->install($archive, $binary);
        } finally {
            File::delete($archive);
        }
    }

    private function install(string $source, string $binary): bool
    {
        throw_unless(is_file($source), RuntimeException::class, sprintf('File [%s] does not exist.', $source));

        if (! str_ends_with($source, '.zip') && ! str_ends_with($source, '.tar.gz')) {
            return File::copy($source, $binary);
        }

        $extractDirectory = sprintf('%s/invoice-pdf-%s', sys_get_temp_dir(), bin2hex(random_bytes(4)));

        try {
            $this->extract($source, $extractDirectory);

            return File::move(sprintf('%s/%s', $extractDirectory, $this->binaryName()), $binary);
        } finally {
            File::deleteDirectory($extractDirectory);
        }
    }

    private function build(string $binary): bool
    {
        $process = new Process(
            ['go', 'build', '-trimpath', '-ldflags=-s -w -X main.Version=local', '-o', $binary, '.'],
            dirname(__DIR__, 2) . '/bin',
            ['CGO_ENABLED' => '0'],
        );

        $process->setTimeout(600);
        $process->mustRun();

        return TRUE;
    }

    private function assetName(): string
    {
        $os = match (PHP_OS_FAMILY) {
            'Windows' => 'windows',
            'Darwin' => 'darwin',
            default => 'linux',
        };

        $arch = match (TRUE) {
            str_contains(php_uname('m'), 'arm64'), str_contains(php_uname('m'), 'aarch64') => 'arm64',
            default => 'amd64',
        };

        return sprintf('invoice-pdf_%s_%s.%s', $os, $arch, $os === 'windows' ? 'zip' : 'tar.gz');
    }

    private function binaryName(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'invoice-pdf.exe' : 'invoice-pdf';
    }

    private function downloadUrl(string $asset): string
    {
        $tag = $this->option('tag');

        return match ($tag) {
            'latest' => sprintf('https://github.com/mikailfaruqali/invoice-template/releases/latest/download/%s', $asset),
            default => sprintf('https://github.com/mikailfaruqali/invoice-template/releases/download/%s/%s', $tag, $asset),
        };
    }

    private function extract(string $archive, string $directory): void
    {
        File::ensureDirectoryExists($directory);

        if (str_ends_with($archive, '.zip')) {
            $zipArchive = new ZipArchive;
            $zipArchive->open($archive);
            $zipArchive->extractTo($directory);
            $zipArchive->close();

            return;
        }

        (new PharData($archive))->extractTo($directory, NULL, TRUE);
    }
}
