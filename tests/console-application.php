<?php declare(strict_types = 1);

(new \Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new \App\Kernel((string) $_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
return new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
