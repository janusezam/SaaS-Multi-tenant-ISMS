<?php

use App\Support\EnvFile;

test('env file helper updates an existing key', function () {
    $path = tempnam(sys_get_temp_dir(), 'isms-env-');

    expect($path)->not->toBeFalse();

    file_put_contents($path, "APP_NAME=ISMS\nAPP_VERSION=v1.0.2\n");

    app(EnvFile::class)->setKey((string) $path, 'APP_VERSION', 'v1.0.4');

    $contents = file_get_contents((string) $path);

    expect($contents)->toContain('APP_VERSION=v1.0.4');
    expect(substr_count((string) $contents, 'APP_VERSION='))->toBe(1);

    @unlink((string) $path);
});

test('env file helper inserts a missing key after APP_NAME when possible', function () {
    $path = tempnam(sys_get_temp_dir(), 'isms-env-');

    expect($path)->not->toBeFalse();

    file_put_contents($path, "APP_NAME=ISMS\nAPP_ENV=local\n");

    app(EnvFile::class)->setKey((string) $path, 'APP_VERSION', 'v1.0.0');

    $contents = (string) file_get_contents((string) $path);
    $lines = preg_split('/\r\n|\n|\r/', $contents);

    expect($lines[0] ?? null)->toBe('APP_NAME=ISMS');
    expect($lines[1] ?? null)->toBe('APP_VERSION=v1.0.0');

    @unlink((string) $path);
});
