<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Cache\Exception;

use Exception;

class CacheMissException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $key
     * @param string $reason
     */
    public function __construct(string $key, string $reason)
    {
        parent::__construct(
            sprintf(
                'Cache missing for %s, reason: %s',
                $key,
                $reason
            )
        );
    }
}
