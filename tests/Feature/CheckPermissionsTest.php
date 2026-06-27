<?php

use Illuminate\Support\Facades\Artisan;

/**
 * The security:permissions command operates on a real directory tree, so each
 * test builds a throwaway project root under the system temp dir and points the
 * command at it via the optional {path} argument — the real project is untouched.
 */
beforeEach(function () {
    $this->root = sys_get_temp_dir().'/perm-test-'.bin2hex(random_bytes(6));
    mkdir($this->root, 0755, true);
});

afterEach(function () {
    exec('rm -rf '.escapeshellarg($this->root));
});

function modeOf(string $path): int
{
    clearstatcache();

    return fileperms($path) & 0777;
}

test('it fixes missing writable dirs, loose secrets, and world-writable files', function () {
    if (PHP_OS_FAMILY === 'Windows') {
        $this->markTestSkipped('POSIX permissions do not apply on Windows.');
    }

    // A loose .env (world/group readable) and a world-writable stray file.
    file_put_contents($this->root.'/.env', "APP_KEY=base64:test\n");
    chmod($this->root.'/.env', 0644);

    file_put_contents($this->root.'/stray.txt', 'data');
    chmod($this->root.'/stray.txt', 0777);

    // storage/ and bootstrap/cache do not exist yet — the command must create them.
    expect(is_dir($this->root.'/storage/framework/views'))->toBeFalse();

    $exit = Artisan::call('security:permissions', ['path' => $this->root]);

    expect($exit)->toBe(0)
        ->and(modeOf($this->root.'/.env'))->toBe(0600)
        ->and(modeOf($this->root.'/stray.txt') & 0002)->toBe(0)   // others-write cleared
        ->and(is_dir($this->root.'/storage/framework/views'))->toBeTrue()
        ->and(modeOf($this->root.'/storage/logs'))->toBe(0775)
        ->and(modeOf($this->root.'/bootstrap/cache'))->toBe(0775);

    // A second run is a clean no-op.
    expect(Artisan::call('security:permissions', ['path' => $this->root]))->toBe(0);
    expect(Artisan::output())->toContain('no issues found');
});

test('dry-run reports issues without changing anything and exits non-zero', function () {
    if (PHP_OS_FAMILY === 'Windows') {
        $this->markTestSkipped('POSIX permissions do not apply on Windows.');
    }

    file_put_contents($this->root.'/.env', "APP_KEY=base64:test\n");
    chmod($this->root.'/.env', 0644);

    $exit = Artisan::call('security:permissions', ['path' => $this->root, '--dry-run' => true]);

    expect($exit)->toBe(1)                                  // non-zero so CI can fail
        ->and(modeOf($this->root.'/.env'))->toBe(0644)     // unchanged
        ->and(is_dir($this->root.'/storage'))->toBeFalse(); // nothing created
});
