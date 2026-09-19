<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\PHPStan;

use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Exercises the `JsonFieldAssociationPropertyVisitor` end-to-end by running
 * PHPStan (the same phar used by `composer stan`) against a fixture whose
 * entity declares associations via `#[JsonReference]` / `#[JsonEmbed]`.
 *
 * The visitor injects `@property` phpdoc so that `$entity->author` (single)
 * and `$entity->tags` (list) resolve to the target entity type instead of the
 * `__get` mixed type. We assert the properties are typed by verifying that
 * passing them to a string-typed parameter raises `argument.type` errors.
 *
 * `CustomRuleTestCase` from `cakedc/cakephp-phpstan` cannot be used here
 * because this project installs PHPStan as a phar only (no `PHPStan\Testing`
 * classes on the composer autoloader), so we drive the analyser directly.
 */
#[Group('phpstan-extension')]
class JsonFieldAssociationPropertyVisitorTest extends TestCase
{
    private function pluginRoot(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * @return array<int, string>
     */
    private function analyseFixture(): array
    {
        $root = $this->pluginRoot();
        $config = $root . '/phpstan.neon';
        $fixture = $root . '/tests/PHPStan/fixtures/JsonFieldAssociationUsage.php';

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

    public function testAssociationPropertiesAreTyped(): void
    {
        $messages = $this->analyseFixture();
        $combined = implode("\n", $messages);

        // Single `#[JsonReference]` resolves to the target entity.
        $this->assertStringContainsString('JfApp\Model\Entity\User given', $combined);

        // Many `#[JsonReference]` resolves to a list of the target entity.
        $this->assertStringContainsString('array<JfApp\Model\Entity\Tag> given', $combined);

        // Single `#[JsonEmbed]` resolves to the document entity.
        $this->assertStringContainsString('JfApp\Model\Entity\JsonFieldProfile given', $combined);

        // Many `#[JsonEmbed]` resolves to a list of the document entity.
        $this->assertStringContainsString('array<JfApp\Model\Entity\JsonFieldProfile> given', $combined);

        // None of the association properties should fall back to "undefined
        // property" (i.e. the mixed `__get` type) once typed.
        $this->assertStringNotContainsString('undefined property', $combined);
    }
}
