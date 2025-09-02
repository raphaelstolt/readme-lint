<?php

declare(strict_types=1);

namespace Stolt\ReadmeLint\Rules;

use Stolt\ReadmeLint\LintIssue;

final class CurrentCodeBlockRule implements RuleInterface
{

    public function check(string $content): ?LintIssue
    {
        $tempDirectory = '/tmp/rl';
        if (!is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0777, true);
        }

        preg_match_all('/```php(.*?)```/s', $content, $matches);

        foreach ($matches[1] as $i => $code) {
            $snippetPath = $tempDirectory . "/snippet_$i.php";
            file_put_contents($snippetPath, "<?php\n" . trim($code));
            
            $result = $this->runPHPStan($snippetPath);

            if ($result['exitCode'] !== 0) {
                echo "\033[33m⚠️  Potential issue in snippet #$i:\033[0m\n";
                echo $result['output'] . "\n";
            }

            unlink($snippetPath);
        }

        rmdir($tempDirectory);
        return null;
    }

    private function runPHPStan(string $file): array
    {
        $cmd = escapeshellcmd("vendor/bin/phpstan analyse " . $file . " --no-progress --level=5");
        $output = shell_exec($cmd . " 2>&1");
        $exitCode = (str_contains($output, ' [ERROR] ')) ? 1 : 0;
        return [
            'output' => $output,
            'exitCode' => $exitCode,
        ];
    }
}
