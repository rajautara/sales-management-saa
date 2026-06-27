<?php

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class CheckPermissions extends Command
{
    protected $signature = 'security:permissions
        {path? : Project root to operate on (defaults to the application base path)}
        {--dry-run : Report problems without changing anything (non-zero exit if any are found)}';

    protected $description = 'Audit and fix folder/file permissions: writable dirs, secret lockdown, and a world-writable scan.';

    /** Directories Laravel must be able to write to (created if missing, set to 0775). */
    private const WRITABLE_DIRS = [
        'storage',
        'storage/app',
        'storage/app/public',
        'storage/app/private',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/cache/data',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
    ];

    /** Directories never recursed into during the world-writable scan. */
    private const SCAN_EXCLUDES = ['vendor', 'node_modules', '.git', 'tools', 'public/build'];

    private const DIR_MODE = 0775;
    private const SECRET_MODE = 0600;
    private const WORLD_WRITABLE = 0002;

    public function handle(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->warn('POSIX file permissions do not apply on Windows — nothing to do.');

            return self::SUCCESS;
        }

        $root = rtrim($this->argument('path') ?? base_path(), DIRECTORY_SEPARATOR);

        if (! is_dir($root)) {
            $this->error("Path is not a directory: {$root}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $issues = 0;

        $issues += $this->ensureWritableDirs($root, $dryRun);
        $issues += $this->lockDownSecrets($root, $dryRun);
        $issues += $this->scanWorldWritable($root, $dryRun);

        if ($issues === 0) {
            $this->info('Permissions OK — no issues found.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("{$issues} issue(s) found. Re-run without --dry-run to fix.");

            return self::FAILURE;
        }

        $this->info("Fixed {$issues} issue(s).");

        return self::SUCCESS;
    }

    /** Ensure writable dirs exist and carry 0775. Returns the number of issues. */
    private function ensureWritableDirs(string $root, bool $dryRun): int
    {
        $issues = 0;

        foreach (self::WRITABLE_DIRS as $relative) {
            $path = $root.DIRECTORY_SEPARATOR.$relative;

            if (! is_dir($path)) {
                $issues++;
                $this->line("  [writable] missing dir: {$relative}".($dryRun ? '' : ' — creating'));
                if (! $dryRun) {
                    @mkdir($path, self::DIR_MODE, true);
                    // mkdir() honours the umask, so set the mode explicitly.
                    @chmod($path, self::DIR_MODE);
                }

                continue;
            }

            if ($this->modeOf($path) !== self::DIR_MODE) {
                $issues++;
                $this->line(sprintf('  [writable] %s is %s%s', $relative, $this->fmtMode($path), $dryRun ? '' : ' — chmod 0775'));
                if (! $dryRun) {
                    @chmod($path, self::DIR_MODE);
                }
            }
        }

        return $issues;
    }

    /** Lock sensitive files down to 0600. Returns the number of issues. */
    private function lockDownSecrets(string $root, bool $dryRun): int
    {
        $issues = 0;

        $targets = ['.env', '.env.backup', '.env.production'];

        // Laravel encryption keys, if any: storage/*.key
        foreach (glob($root.'/storage/*.key') ?: [] as $keyFile) {
            $targets[] = ltrim(str_replace($root, '', $keyFile), DIRECTORY_SEPARATOR);
        }

        // MyInvois signing certificate path (absolute or project-relative).
        $certPath = $this->myInvoisCertPath($root);
        if ($certPath !== null) {
            $targets[] = $certPath;
        }

        foreach ($targets as $relative) {
            $path = $this->isAbsolute($relative) ? $relative : $root.DIRECTORY_SEPARATOR.$relative;

            if (! is_file($path)) {
                continue;
            }

            if ($this->modeOf($path) !== self::SECRET_MODE) {
                $issues++;
                $this->line(sprintf('  [secret] %s is %s%s', $relative, $this->fmtMode($path), $dryRun ? '' : ' — chmod 0600'));
                if (! $dryRun) {
                    @chmod($path, self::SECRET_MODE);
                }
            }
        }

        return $issues;
    }

    /** Recursively clear the others-write bit across the project. Returns the number of issues. */
    private function scanWorldWritable(string $root, bool $dryRun): int
    {
        $issues = 0;
        $excludes = array_map(fn ($e) => $root.DIRECTORY_SEPARATOR.$e, self::SCAN_EXCLUDES);

        $directory = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
        $filter = new RecursiveCallbackFilterIterator($directory, function (SplFileInfo $current) use ($excludes) {
            foreach ($excludes as $excluded) {
                if ($current->getPathname() === $excluded) {
                    return false;
                }
            }

            return true;
        });

        /** @var SplFileInfo $info */
        foreach (new RecursiveIteratorIterator($filter) as $info) {
            $path = $info->getPathname();
            $perms = @fileperms($path);

            if ($perms !== false && ($perms & self::WORLD_WRITABLE)) {
                $issues++;
                $relative = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
                $this->line(sprintf('  [world-writable] %s is %s%s', $relative, $this->fmtMode($path), $dryRun ? '' : ' — chmod o-w'));
                if (! $dryRun) {
                    @chmod($path, $perms & ~self::WORLD_WRITABLE & 0777);
                }
            }
        }

        return $issues;
    }

    /** Resolve MYINVOIS_CERT_PATH for the given root (config first, then the .env file). */
    private function myInvoisCertPath(string $root): ?string
    {
        if ($root === rtrim(base_path(), DIRECTORY_SEPARATOR)) {
            $configured = config('myinvois.cert_path');
            if (is_string($configured) && $configured !== '') {
                return $configured;
            }
        }

        $envFile = $root.DIRECTORY_SEPARATOR.'.env';
        if (is_file($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (str_starts_with(trim($line), 'MYINVOIS_CERT_PATH=')) {
                    $value = trim(substr(trim($line), strlen('MYINVOIS_CERT_PATH=')), " \t\"'");

                    return $value !== '' ? $value : null;
                }
            }
        }

        return null;
    }

    private function modeOf(string $path): int
    {
        return fileperms($path) & 0777;
    }

    private function fmtMode(string $path): string
    {
        return '0'.decoct($this->modeOf($path));
    }

    private function isAbsolute(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR);
    }
}
