<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\PHPStan;

use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Exercises the `JsonPathPropertyExtension` end-to-end by running PHPStan
 * (the same phar used by `composer stan`) against a fixture that accesses
 * path-addressable JSON columns on an entity annotated with `#[JsonColumn]`.
 *
 * The `CustomRuleTestCase` from `cakedc/cakephp-phpstan` cannot be used here
 * because this project installs PHPStan as a phar only (no `PHPStan\Testing`
 * classes on the composer autoloader), so we drive the analyser directly.
 */
#[Group('phpstan-extension')]
class JsonPathPropertyExtensionTest extends TestCase
{
    private function pluginRoot(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * @return array<int, array{message: string, line: int}>
     */
    private function analyseFixture(): array
    {
        $root = $this->pluginRoot();
        $config = $root . '/phpstan.neon';
        $fixture = $root . '/tests/PHPStan/fixtures/JsonPathUsage.php';

        $command = sprintf(
            '%s %s analyse -c %s --error-format=json --no-progress %s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($root . '/vendor/phpstan/phpstan/phpstan'),
            escapeshellarg($config),
            escapeshellarg($fixture),
        );

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
        );
        if ($process === false) {
            $this->fail('Unable to start the PHPStan process.');
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        $output = $stdout === '' ? $stderr : $stdout;
        $decoded = json_decode((string)$output, true);
        if (!is_array($decoded)) {
            $this->fail('Unexpected PHPStan output: ' . $output);
        }

        // The phar wrapper emits an envelope keyed by file path; the raw
        // analyser uses `files`. Collect messages from every reported file so
        // we do not depend on the exact path-separator representation.
        $messages = [];
        if (isset($decoded['error_details']) && is_array($decoded['error_details'])) {
            foreach ($decoded['error_details'] as $fileMessages) {
                foreach ((array)$fileMessages as $entry) {
                    $messages[] = is_array($entry) ? ($entry['message'] ?? '') : (string)$entry;
                }
            }

            return $messages;
        }

        if (isset($decoded['files']) && is_array($decoded['files'])) {
            foreach ($decoded['files'] as $fileData) {
                foreach ((array)($fileData['messages'] ?? []) as $entry) {
                    $messages[] = is_array($entry) ? ($entry['message'] ?? '') : (string)$entry;
                }
            }
        }

        return $messages;
    }

    public function testDeclaredJsonColumnsResolveWithoutError(): void
    {
        $messages = $this->analyseFixture();
        $combined = implode("\n", $messages);

        // Declared columns must NOT raise an "undefined property" error.
        $this->assertStringNotContainsString('data->profile.name', $combined);
        $this->assertStringNotContainsString('meta->theme', $combined);

        // The single expected error is the undeclared `secret` column.
        $this->assertStringContainsString('secret->value', $combined);
    }
}
