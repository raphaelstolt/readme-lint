<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class CurrentCodeBlockRule implements RuleInterface
{
    public function check(string $content): ?LintIssue
    {
        $tempDirectory = '/tmp/rl';
        if (!\is_dir($tempDirectory)) {
            \mkdir($tempDirectory, 0777, true);
        }

        \preg_match_all('/```php(.*?)```/s', $content, $matches);

        if (\count($matches) === 0) {
            return null;
        }

        foreach ($matches[1] as $i => $code) {
            $onlyTheCode = \trim(\str_replace(['```php', '``` php', '<?php'], ' ', $code));
            $snippetPath = $tempDirectory . "/snippet_$i.php";
            $temporaryCodeContent = "<?php " . PHP_EOL . $onlyTheCode;

            file_put_contents($snippetPath, $temporaryCodeContent);

            $result = $this->lintCodeBlock($snippetPath);

            if ($result['exitCode'] !== 0) {
                echo "\033[33m⚠️  Potential issue in snippet #$i:\033[0m\n";
                echo $result['output'] . "\n";

                \unlink($snippetPath);
                \rmdir($tempDirectory);

                return new LintIssue($result['output'], LintIssue::SEVERITY_ERROR, 'Fix code snippet.');
            }
        }

        return null;
    }

    private function lintCodeBlock(string $file): array
    {
        $cmd = \escapeshellcmd("php -f " . $file);

        \exec($cmd . " 2>&1", $output, $exitCode);

        return [
            'output' => $output,
            'exitCode' => $exitCode,
        ];
    }
}
