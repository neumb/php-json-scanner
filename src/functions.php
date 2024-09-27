<?php

declare(strict_types=1);

namespace Neumb\JsonScanner;

if (!function_exists('unimplemented')) {
    function unimplemented(?string $what = null): never
    {
        if ($what !== null) {
            throw new \Exception("not implemented: $what");
        } else {
            throw new \Exception("not implemented");
        }
    }
}
