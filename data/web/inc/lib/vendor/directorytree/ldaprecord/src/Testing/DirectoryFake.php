<?php

namespace LdapRecord\Testing;

use LdapRecord\Container;

class DirectoryFake
{
    /**
     * Setup the fake connection.
     *
     * @param string|null $name
     *
     * @return ConnectionFake
     *
     * @throws \LdapRecord\ContainerException
     */
    public static function setup($name = null)
    {
        $connection = Container::getConnection($name);

        $fake = static::makeConnectionFake(
            $connection->getConfiguration()->all()
        );

        // Replace the connection with a fake.
        Container::addConnection($fake, $name);

        return $fake;
    }

    /**
     * Reset the container.
     *
     * @return void
     */
    public static function tearDown()
    {
        Container::reset();
    }

    /**
     * Make a connection fake.
     *
     * @param array $config
     *
     * @return ConnectionFake
     */
    public static function makeConnectionFake(array $config = [])
    {
        return ConnectionFake::make($config)->shouldBeConnected();
    }
}
