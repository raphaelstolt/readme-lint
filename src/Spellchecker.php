<?php

namespace Stolt\ReadmeLint;

use Symfony\Component\Process\Process;

class Spellchecker
{
    private string $peckPath;

    /**
     * @param string $peckPath
     */
    public function __construct(string $peckPath)
    {
        $this->peckPath = $peckPath;
    }

    public function checkSpellcheckerAvailability(): bool
    {
        return \file_exists($this->peckPath);
    }

    public function runSpellcheckerInit(): Process
    {
        $process = new Process([$this->peckPath, '--init']);
        $process->run();

        return $process;
    }

    public function runSpellchecker(): Process
    {
        $process = new Process([$this->peckPath]);
        $process->run();

        return $process;
    }
}
