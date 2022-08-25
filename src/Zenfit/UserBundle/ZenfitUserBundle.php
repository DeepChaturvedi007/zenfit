<?php

namespace Zenfit\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ZenfitUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
