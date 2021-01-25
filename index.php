<?php

session_start(['cookie_lifetime' => 86400]);

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;

require_once 'vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);
$twig->addExtension(new IntlExtension());

echo $twig->render('index.html.twig');