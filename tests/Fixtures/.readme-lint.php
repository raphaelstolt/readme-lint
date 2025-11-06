<?php declare(strict_types=1);

use Stolt\ReadmeLint\Configuration;
use Stolt\ReadmeLint\Linter;

return (new Configuration(new Linter(\getcwd())))
    ->addRulesToApply(['NoTodoCommentRule', 'LogoPresenceRule']);
