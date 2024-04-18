<?php

namespace Luma\AuroraDatabase\Utils;

use Dotenv\Dotenv;
use Luma\AuroraDatabase\DatabaseConnection;
use Luma\AuroraDatabase\Model\Aurora;

class Populator
{
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $lumaConfigPath = sprintf('%s/config', dirname(__DIR__, 5));

        if (file_exists($lumaConfigPath) && is_dir($lumaConfigPath)) {
            $dotenv = Dotenv::createImmutable($lumaConfigPath);
        } else {
            $dotenv = Dotenv::createImmutable(sprintf('%s/tests/data', dirname(__DIR__, 2)));
        }

        $dotenv->load();

        $databaseCredentialsAreSet = isset($_ENV['DATABASE_USER'])
            && isset($_ENV['DATABASE_PASSWORD'])
            && isset($_ENV['DATABASE_HOST'])
            && isset($_ENV['DATABASE_PORT']);

        if (!$databaseCredentialsAreSet) {
            throw new \Exception('Config could not be loaded');
        }

        Aurora::setDatabaseConnection(
            new DatabaseConnection(
                sprintf('mysql:host=%s;port=%s;', $_ENV['DATABASE_HOST'], $_ENV['DATABASE_PORT']),
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD']
            )
        );
    }

    /**
     * @param string $class
     * @param Collection $data
     *
     * @return self
     */
    public function populate(string $class, Collection $data): self
    {
        if (!class_exists($class) || !is_subclass_of($class, Aurora::class)) {
            return $this;
        }

        foreach ($data as $record) {
            $class::create($record)->save();
            $classNameAsArray = explode('\\', $class);

            echo sprintf("\033[0;32m%s Created\033[0m\033[34m %s \033[0m\n", end($classNameAsArray), print_r($record, true));
        }

        echo "\r\n";

        return $this;
    }
}