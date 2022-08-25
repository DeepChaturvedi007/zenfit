<?php

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\VarDumper\VarDumper;
use AppBundle\Entity\User;

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $v) {
            VarDumper::dump($v);
        }
        die(1);
    }
}

if (! function_exists('rescue')) {
    /**
     * Catch a potential exception and return a default.
     *
     * @param  callable  $rescuee
     * @param  mixed     $rescuer
     * @return mixed
     */
    function rescue(callable $rescuee, $rescuer = null)
    {
        try {
            return $rescuee();
        } catch (Throwable $e) {
            return is_callable($rescuer) ? $rescuer() : $rescuer;
        }
    }
}

if (! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param  int  $times
     * @param  callable  $callback
     * @param  int  $sleep
     * @return mixed
     *
     * @throws \Exception
     */
    function retry($times, callable $callback, $sleep = 0)
    {
        $times--;
        beginning:
        try {
            return $callback();
        } catch (Exception $e) {
            if (! $times) {
                throw $e;
            }
            $times--;
            if ($sleep) {
                usleep($sleep * 1000);
            }
            goto beginning;
        }
    }
}

if (! function_exists('br2nl')) {
    function br2nl($str) {
        $breaks = ['<br />', '<br>', '<br/>'];
        return str_ireplace($breaks, "\r\n", $str);
    }
}

if (! function_exists('abort')) {
    /**
     * Throw an HttpException with the given data.
     * @param  array<mixed> $headers
     * @return noreturn
     */
    function abort(int $code, string $message = '', array $headers = []): void
    {
        if ($code === 404) {
            throw new NotFoundHttpException($message);
        }

        if ($code === 403) {
            throw new AccessDeniedHttpException($message, null, $code);
        }

        throw new HttpException($code, $message, null, $headers);
    }
}

if (! function_exists('abort_if')) {
    /**
     * Throw an HttpException with the given data if the given condition is true.
     *
     * @param  bool    $boolean
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    function abort_if($boolean, $code, $message = '', array $headers = [])
    {
        if ($boolean) {
            abort($code, $message, $headers);
        }
    }
}

if (! function_exists('abort_unless')) {
    /**
     * Throw an HttpException with the given data unless the given condition is true.
     *
     * @param  bool    $boolean
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    function abort_unless($boolean, $code, $message = '', array $headers = [])
    {
        if (! $boolean) {
            abort($code, $message, $headers);
        }
    }
}

if (! function_exists('is_owner')) {
    /**
     * Throw an HttpException with the given data unless the given condition is true.
     */
    function is_owner(mixed $user, mixed $entity): bool #TODO use interface here
    {
        if (!$user instanceof User || !is_object($entity)) {
            return false;
        }

        if (!method_exists($entity, 'getUser')) {
            throw new \RuntimeException('no getUser method');
        }

        return rescue(function () use ($user, $entity) {
            return in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true) || in_array('ROLE_ADMIN', $user->getRoles(), true) || $user->getId() === $entity->getUser()->getId();
        }, false);
    }
}

if (! function_exists('is_admin')) {
    /**
     * Throw an HttpException with the given data unless the user is admin.
     *
     * @param  \AppBundle\Entity\User   $user
     * @return boolean
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    function is_admin(?User $user = null)
    {
        if (!$user) {
            return false;
        }

        return rescue(function () use ($user) {
            return in_array('ROLE_SUPER_ADMIN', $user->getRoles()) ||in_array('ROLE_ADMIN', $user->getRoles());
        }, false);
    }
}

if (! function_exists('is_user')) {
    /**
     * Throw an HttpException with the given data unless the user is logged in.
     *
     * @param  \AppBundle\Entity\User   $user
     * @return boolean
     *
     */
    function is_user(?User $user = null)
    {
        if ($user instanceof User) {
            return true;
        }
        return false;
    }
}

if (! function_exists('can_delete')) {
    /**
     * Throw an HttpException with the given data unless the given condition is true.
     *
     * @param  \AppBundle\Entity\User   $user
     * @param  *                        $entity
     * @return boolean
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    function can_delete($user, $entity)
    {
        return is_owner($user, $entity);
    }
}
