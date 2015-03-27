<?php
require_once __DIR__.'/vendor/autoload.php';

/* Silex */
$app = new Silex\Application();
$app['debug'] = (getenv('APPLICATION_ENV') == 'development');

/* Twig */
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/views'));

/* Alterar tamanho das imagens e vídeos da Central */
$app['twig']->addFilter('resize', new Twig_Filter_Function(
    function($string, $width, $height = false)
    {
        if (!strstr($string, ".jpg")) { return $string; }
        if ($height) { return str_replace('.jpg', "_{$width}x{$height}.jpg", $string); }
        return str_replace('.jpg', "_{$width}.jpg", $string);
    }
));

/* Limitar letras de uma string */
$app['twig']->addFilter('limitLetters', new Twig_Filter_Function(
    function ($string, $limit, $suffix = '...')
    {
        $string = str_replace(PHP_EOL, ' ', $string);
        return (strlen($string) > $limit)? explode(PHP_EOL,wordwrap($string, $limit, PHP_EOL))[0] . $suffix : $string;
    }
));

$app['twig']->addFilter('var_dump', new Twig_Filter_Function('var_dump'));

/* Cache */
$app['cache'] = $app->share(function() use ($app) {
    if ($app['debug']) {
        return null;
    }
    $mc = new \Memcached();
    $mc->addServer('localhost', 11211);
    return new \Secom\Cache\Memcached($mc);
});

$app['client'] = $app->share(function() use ($app) {
    return new \Secom\Central3\Client('teste', $app['cache']);
});
