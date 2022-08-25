<?php

namespace AppBundle\Services;

use Symfony\Component\Config\Definition\Exception\Exception;
use Pusher\Pusher;

class PusherService
{
    private Pusher $pusher;

    public function __construct(array $pusherConfig)
    {
        $this->pusher = static::create($pusherConfig);
    }

    /**
     * @return Pusher
     */
    public function client()
    {
        return $this->pusher;
    }

    /**
     * @param array $config
     * @return Pusher
     */
    static public function create(array $config)
    {
        if (!is_array($config['options'])) {
            $config['options'] = [];
        }

        try {
            return new Pusher(
                $config['auth_key'],
                $config['secret'],
                $config['app_id'],
                $config['options']
            );
        } catch (Exception $e) {}
    }


}
