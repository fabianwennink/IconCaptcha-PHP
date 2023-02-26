<?php

namespace IconCaptcha\Session;

class SessionData
{
    /**
     * @var array The positions of the icon on the generated image.
     */
    public array $icons = [];

    /**
     * @var int The icon ID of the correct answer/icon.
     */
    public int $correctId = 0;

    /**
     * @var string The name of the theme used by the captcha instance.
     */
    public string $mode = 'light';

    /**
     * @var bool If the captcha image has been requested yet.
     */
    public bool $requested = false;

    /**
     * @var bool If the captcha was completed (correct icon selected) or not.
     */
    public bool $completed = false;

    /**
     * @var int The number of times an incorrect answer was given.
     */
    public int $attempts = 0;

    /**
     * @var int The (unix) timestamp, at which the timeout for entering too many incorrect guesses expires.
     */
    public int $attemptsTimeout = 0;

    /**
     * @var int The (unix) timestamp, after which the captcha's session should be considered expired.
     */
    public int $expiresAt = 0;

    public function fromArray(array $data): void
    {
        $this->icons = $data['icons'];
        $this->correctId = $data['correctId'];
        $this->mode = $data['mode'];
        $this->requested = $data['requested'];
        $this->completed = $data['completed'];
        $this->attempts = $data['attempts'];
        $this->attemptsTimeout = $data['attemptsTimeout'];
        $this->expiresAt = $data['expiresAt'];
    }

    public function toArray(): array
    {
        return [
            'icons' => $this->icons,
            'correctId' => $this->correctId,
            'mode' => $this->mode,
            'requested' => $this->requested,
            'completed' => $this->completed,
            'attempts' => $this->attempts,
            'attemptsTimeout' => $this->attemptsTimeout,
            'expiresAt' => $this->expiresAt,
        ];
    }

    public function fromJson(string $json): void
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->fromArray($data);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
