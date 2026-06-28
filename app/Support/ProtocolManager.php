<?php

namespace App\Support;

use Illuminate\Contracts\Container\Container;

class ProtocolManager
{
    /**
     * @var Container LaravelContainer Instance
     */
    protected $container;

    /**
     * @var array Cached Protocol Class List
     */
    protected $protocolClasses = [];

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Discover and register all protocol classes
     *
     * @return self
     */
    public function registerAllProtocols()
    {
        if (empty($this->protocolClasses)) {
            $files = glob(app_path('Protocols') . '/*.php');

            foreach ($files as $file) {
                $className = 'App\\Protocols\\' . basename($file, '.php');

                if (class_exists($className) && is_subclass_of($className, AbstractProtocol::class)) {
                    $this->protocolClasses[] = $className;
                }
            }
        }

        return $this;
    }

    /**
     * Get all registered protocol classes
     *
     * @return array
     */
    public function getProtocolClasses()
    {
        if (empty($this->protocolClasses)) {
            $this->registerAllProtocols();
        }

        return $this->protocolClasses;
    }

    /**
     * Get all protocol identifiers
     *
     * @return array
     */
    public function getAllFlags()
    {
        return collect($this->getProtocolClasses())
            ->map(function ($class) {
                try {
                    $reflection = new \ReflectionClass($class);
                    if (!$reflection->isInstantiable()) {
                        return [];
                    }
                    // 'flags' is a public property with a default value in AbstractProtocol
                    $instanceForFlags = $reflection->newInstanceWithoutConstructor();
                    return $instanceForFlags->flags;
                } catch (\ReflectionException $e) {
                    // Log or handle error if a class is problematic
                    report($e);
                    return [];
                }
            })
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Match the appropriate protocol handler class name based on the identifier
     *
     * @param string $flag Request Identifier
     * @return string|null Protocol Class Name ornull
     */
    public function matchProtocolClassName(string $flag): ?string
    {
// In reverse order, give higher priority to the latest defined protocols
        foreach (array_reverse($this->getProtocolClasses()) as $protocolClassString) {
            try {
                $reflection = new \ReflectionClass($protocolClassString);

                if (!$reflection->isInstantiable() || !$reflection->isSubclassOf(AbstractProtocol::class)) {
                    continue;
                }

                // 'flags' is a public property in AbstractProtocol
                $instanceForFlags = $reflection->newInstanceWithoutConstructor();
                $flags = $instanceForFlags->flags;

                if (collect($flags)->contains(fn($f) => stripos($flag, (string) $f) !== false)) {
                    return $protocolClassString; // Return class name string
                }
            } catch (\ReflectionException $e) {
                report($e); // Consider logging this error
                continue;
            }
        }
        return null;
    }

    /**
     * Match the appropriate protocol handler instance based on the identifier (Original logic，If needed)
     *
     * @param string $flag Request Identifier
     * @param array $user User Information
     * @param array $servers Server List
     * @param array $clientInfo Client Information
     * @return AbstractProtocol|null
     */
    public function matchProtocol($flag, $user, $servers, $clientInfo = [])
    {
        $protocolClassName = $this->matchProtocolClassName($flag);
        if ($protocolClassName) {
            return $this->makeProtocolInstance($protocolClassName, [
                'user' => $user,
                'servers' => $servers,
                'clientName' => $clientInfo['name'] ?? null,
                'clientVersion' => $clientInfo['version'] ?? null
            ]);
        }
        return null;
    }

    /**
     * General method to create a protocol instance，Compatible with different versions ofLaravelContainer
     * 
     * @param string $class Class Name
     * @param array $parameters Constructor Parameters
     * @return object Instance
     */
    protected function makeProtocolInstance($class, array $parameters)
    {
        // Laravel's make method can accept an array of parameters as its second argument.
        // These will be used when resolving the class's dependencies.
        return $this->container->make($class, $parameters);
    }
}