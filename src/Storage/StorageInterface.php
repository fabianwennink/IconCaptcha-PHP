<?php

namespace IconCaptcha\Storage;

interface StorageInterface
{
    /**
     * Connect to the storage instance.
     *
     * @return mixed The storage instance.
     */
    public function connect();
}
