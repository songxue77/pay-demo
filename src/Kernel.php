<?php
declare(strict_types=1);

namespace PayDemo;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXISTS = '.{php,yaml,yml}';

    private const ENV_LOCAL = 'dev';
    private const ENV_PHPUNIT = 'phpunit';
    private const ENV_TEST = 'test';
    private const ENV_STAGING = 'staging';
    private const ENV_PROD = 'prod';

    private const PRODUCTION_ENVIRONMENTS = [
        self::ENV_STAGING,
        self::ENV_PROD
    ];
    private const DEVELOPMENT_ENVIRONMENTS = [
        self::ENV_LOCAL,
        self::ENV_PHPUNIT,
        self::ENV_TEST,
    ];

    public function getCacheDir()
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir()
    {
        return $this->getProjectDir() . '/var/log';
    }

    public function registerBundles()
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $conf_dir = $this->getProjectDir() . '/config';

        $loader->load($conf_dir . '/{packages}/*' . self::CONFIG_EXISTS, 'glob');
        $loader->load($conf_dir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXISTS, 'glob');
        $loader->load($conf_dir . '/{services}' . self::CONFIG_EXISTS, 'glob');
        $loader->load($conf_dir . '/{services}_' . $this->environment . self::CONFIG_EXISTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import($this->getProjectDir() . '/src/Controller/', '/', 'annotation');
    }

    /**
     * @return bool
     */
    public static function isProd(): bool
    {
        return self::getEnv() === self::ENV_PROD;
    }

    /**
     * @return bool
     */
    public static function isStaging(): bool
    {
        return self::getEnv() === self::ENV_STAGING;
    }

    /**
     * @return bool
     */
    public static function isTest(): bool
    {
        return self::getEnv() === self::ENV_TEST;
    }

    /**
     * @return bool
     */
    public static function isDev(): bool
    {
        return in_array(self::getEnv(), self::DEVELOPMENT_ENVIRONMENTS, true);
    }

    /**
     * @return bool
     */
    public static function isLocal(): bool
    {
        return in_array(self::getEnv(), [self::ENV_LOCAL, self::ENV_PHPUNIT], true);
    }

    /**
     * @return string
     */
    private static function getEnv(): string
    {
        return getenv('APP_ENV', true);
    }
}
