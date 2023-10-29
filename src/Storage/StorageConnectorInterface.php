<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Storage;

interface StorageConnectorInterface
{
    /**
     * Connect to the storage instance.
     *
     * @return mixed The storage instance.
     */
    public function connect();
}
