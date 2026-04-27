<?php

declare(strict_types=1);

namespace App\Support;

final class EnvFile
{
    public function setKey(string $path, string $key, string $value): void
    {
        if (! is_file($path)) {
            throw new \RuntimeException("Env file not found at {$path}.");
        }

        $contents = file_get_contents($path);

        if (! is_string($contents)) {
            throw new \RuntimeException("Unable to read env file at {$path}.");
        }

        $eol = str_contains($contents, "\r\n") ? "\r\n" : "\n";
        $normalizedValue = trim($value);
        $replacementLine = $key.'='.$normalizedValue;

        $pattern = '/^'.preg_quote($key, '/').'\s*=\s*.*$/m';

        if (preg_match($pattern, $contents) === 1) {
            $updated = preg_replace($pattern, $replacementLine, $contents, 1);

            if (! is_string($updated)) {
                throw new \RuntimeException("Unable to update {$key} in env file.");
            }

            file_put_contents($path, $updated);

            return;
        }

        $lines = explode($eol, $contents);
        $insertIndex = null;

        foreach ($lines as $index => $line) {
            if (str_starts_with($line, 'APP_NAME=')) {
                $insertIndex = $index + 1;
                break;
            }
        }

        if ($insertIndex === null) {
            $lines[] = $replacementLine;
        } else {
            array_splice($lines, $insertIndex, 0, [$replacementLine]);
        }

        $updated = implode($eol, $lines);

        if (! str_ends_with($updated, $eol)) {
            $updated .= $eol;
        }

        file_put_contents($path, $updated);
    }
}
