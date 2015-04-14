<?php
namespace Phalcon
{

    use \Phalcon\Di\Service;
    use \Phalcon\Di\Exception;
    use Phalcon\Di\InjectionAwareInterface;
    use Phalcon\Events\EventsAwareInterface;

    /**
     * Phalcon\Di
     *
     * Phalcon\Di is a component that implements Dependency Injection/Service Location
     * of services and it"s itself a container for them.
     *
     * Since Phalcon is highly decoupled, Phalcon\Di is essential to integrate the different
     * components of the framework. The developer can also use this component to inject dependencies
     * and manage global instances of the different classes used in the application.
     *
     * Basically, this component implements the `Inversion of Control` pattern. Applying this,
     * the objects do not receive their dependencies using setters or constructors, but requesting
     * a service dependency injector. This reduces the overall complexity, since there is only one
     * way to get the required dependencies within a component.
     *
     * Additionally, this pattern increases testability in the code, thus making it less prone to errors.
     *
     * <code>
     * $di = new \Phalcon\Di();
     *
     * //Using a string definition
     * $di->set("request", "Phalcon\Http\Request", true);
     *
     * //Using an anonymous function
     * $di->set("request", function(){
     * return new \Phalcon\Http\Request();
     * }, true);
     *
     * $request = $di->getRequest();
     *
     * </code>
     */
    class Di implements \Phalcon\DiInterface, \Phalcon\Events\EventsAwareInterface
    {

        /**
         *
         * @var \Phalcon\Di\Service[]
         */
        protected $_services;

        protected $_sharedInstances;

        /**
         *
         * @var boolean
         */
        protected $_freshInstance = false;

        /**
         *
         * @var \Phalcon\Events\ManagerInterface
         */
        protected $_eventsManager;

        /**
         *
         * @var \Phalcon\DiInterface
         */
        protected static $_default;

        /**
         * \Phalcon\Di constructor
         */
        public function __construct()
        {
            if (! self::$_default) {
                self::$_default = $this;
            }
            $this->_services = array();
            $this->_sharedInstances = array();
        }

        /**
         * Registers a service in the services container
         *
         * @param
         *            string name
         * @param
         *            mixed definition
         * @param
         *            boolean shared
         * @return \Phalcon\Di\ServiceInterface
         */
        public function set($name, $definition, $shared = null)
        {
            $service = new Service($name, $definition, $shared);
            $this->_services[$name] = $service;
            return $service;
        }

        /**
         * Registers an "always shared" service in the services container
         *
         * @param
         *            string name
         * @param
         *            mixed definition
         * @return \Phalcon\Di\ServiceInterface
         */
        public function setShared($name, $definition)
        {
            $service = new Service($name, $definition, true);
            $this->_services[$name] = $service;
            return $service;
        }

        /**
         * Removes a service in the services container
         *
         * @param
         *            string name
         */
        public function remove($name)
        {
            unset($this->_services[$name]);
        }

        /**
         * Attempts to register a service in the services container
         * Only is successful if a service hasn"t been registered previously
         * with the same name
         *
         * @param
         *            string name
         * @param
         *            mixed definition
         * @param
         *            boolean shared
         * @return \Phalcon\Di\ServiceInterface|false
         */
        public function attempt($name, $definition, $shared = null)
        {
            if (! isset($this->_services[$name])) {
                $service = new Service($name, $definition, $shared);
                $this->_services[$name] = $service;
                return $service;
            }
            return false;
        }

        /**
         * Sets a service using a raw \Phalcon\Di\Service definition
         *
         * @param
         *            string name
         * @param
         *            \Phalcon\Di\ServiceInterface rawDefinition
         * @return \Phalcon\Di\ServiceInterface
         */
        public function setRaw($name, \Phalcon\Di\ServiceInterface $rawDefinition)
        {
            $this->_services[$name] = $rawDefinition;
            return $rawDefinition;
        }

        /**
         * Returns a service definition without resolving
         *
         * @param
         *            string name
         * @return mixed
         */
        public function getRaw($name)
        {
            if (isset($this->_services[$name])) {
                return $this->_services[$name]->getDefinition();
            }
            
            throw new Exception('Service \'' . $name . '\' wasn\'t found in the dependency injection container');
        }

        /**
         * Returns a \Phalcon\Di\Service instance
         *
         * @param
         *            string name
         * @return \Phalcon\Di\ServiceInterface
         */
        public function getService($name)
        {
            if (isset($this->_services[$name])) {
                return $this->_services[$name];
            }
            
            throw new Exception('Service \'' . $name . '\' wasn\'t found in the dependency injection container');
        }

        /**
         * Resolves the service based on its configuration
         *
         * @param
         *            string name
         * @param
         *            array parameters
         * @return mixed
         */
        public function get($name, $parameters = null)
        {
            $eventsManager = $this->getEventsManager();
            $hasEventsManager = is_object($eventsManager);
            if ($hasEventsManager) {
                $eventsManager->fire('di:beforeServiceResolve', $this, array(
                    'name' => $name,
                    'parameters' => $parameters
                ));
            }
            
            if (isset($this->_services[$name])) {
                /**
                 * The service is registered in the DI
                 */
                $service = $this->_services[$name];
                $instance = $service->resolve($parameters, $this);
            } else {
                /**
                 * The DI also acts as builder for any class even if it isn't defined in the DI
                 */
                if (class_exists($name)) {
                    if (is_array($parameters)) {
                        if (count($parameters)) {
                            $instance = new $name($parameters);
                        } else {
                            $instance = new $name();
                        }
                    } else {
                        $instance = new $name();
                    }
                } else {
                    
                    throw new Exception('Service \'' . $name . '\' wasn\'t found in the dependency injection container');
                }
            }
            
            /**
             * Pass the DI itself if the instance implements \Phalcon\Di\InjectionAwareInterface
             */
            if ($instance instanceof InjectionAwareInterface) {
                $instance->setDI($this);
            }
            
            if ($hasEventsManager) {
                /**
                 * Pass the EventsManager if the instance implements \Phalcon\Events\EventsAwareInterface
                 */
                if ($instance instanceof EventsAwareInterface) {
                    $instance->setEventsManager($eventsManager);
                }
                
                $eventsManager->fire('di:afterServiceResolve', $this, array(
                    'name' => $name,
                    'parameters' => $parameters,
                    'instance' => $instance
                ));
            }
            
            return $instance;
        }

        /**
         * Resolves a service, the resolved service is stored in the DI, subsequent requests for this service will return the same instance
         *
         * @param
         *            string name
         * @param
         *            array parameters
         * @return mixed
         */
        public function getShared($name, $parameters = null)
        {
            /**
             * This method provides a first level to shared instances allowing to use non-shared services as shared
             */
            if (isset($this->_sharedInstances[$name])) {
                $instance = $this->_sharedInstances[$name];
                $this->_freshInstance = false;
            } else {
                
                /**
                 * Resolve the instance normally
                 */
                $instance = $this->get($name, $parameters);
                
                /**
                 * Save the instance in the first level shared
                 */
                $this->_sharedInstances[$name] = $instance;
                $this->_freshInstance = true;
            }
            
            return $instance;
        }

        /**
         * Check whether the DI contains a service by a name
         *
         * @param
         *            string name
         * @return boolean
         */
        public function has($name)
        {
            return isset($this->_services[$name]);
        }

        /**
         * Check whether the last service obtained via getShared produced a fresh instance or an existing one
         *
         * @return boolean
         */
        public function wasFreshInstance()
        {
            return $this->_freshInstance;
        }

        /**
         * Return the services registered in the DI
         *
         * @return \Phalcon\Di\Service[]
         */
        public function getServices()
        {
            return $this->_services;
        }

        /**
         * Check if a service is registered using the array syntax
         *
         * @param
         *            string name
         * @return boolean
         */
        public function offsetExists($name)
        {
            return $this->has($name);
        }

        /**
         * Allows to register a shared service using the array syntax
         *
         * <code>
         * $di["request"] = new \Phalcon\Http\Request();
         * </code>
         *
         * @param
         *            string name
         * @param
         *            mixed definition
         * @return boolean
         */
        public function offsetSet($name, $definition)
        {
            $this->setShared($name, $definition);
            return true;
        }

        /**
         * Allows to obtain a shared service using the array syntax
         *
         * <code>
         * var_dump($di["request"]);
         * </code>
         *
         * @param
         *            string name
         * @return mixed
         */
        public function offsetGet($name)
        {
            return $this->getShared($name);
        }

        /**
         * Removes a service from the services container using the array syntax
         *
         * @param
         *            string name
         */
        public function offsetUnset($name)
        {
            return false;
        }

        /**
         * Sets the event manager
         *
         * @param Phalcon\Events\ManagerInterface $eventsManager            
         */
        public function setEventsManager(Phalcon\Events\ManagerInterface $eventsManager)
        {
            $this->_eventsManager = $eventsManager;
        }

        /**
         * Returns the internal event manager
         *
         * @return \Phalcon\Events\ManagerInterface
         */
        public function getEventsManager()
        {
            return $this->_eventsManager;
        }

        /**
         * Magic method to get or set services using setters/getters
         *
         * @param
         *            string method
         * @param
         *            array arguments
         * @return mixed
         */
        public function __call($method, $arguments = null)
        {
            /**
             * If the magic method starts with "get" we try to get a service with that name
             */
            if (\Phalcon\Text::_starts_with($method, 'get')) {
                $possibleService = lcfirst(substr($method, 3));
                if (isset($this->_services[$possibleService])) {
                    if (count($arguments)) {
                        $instance = $this->get($possibleService, $arguments);
                    } else {
                        $instance = $this->get($possibleService);
                    }
                    return $instance;
                }
            }
            
            /**
             * If the magic method starts with "set" we try to set a service using that name
             */
            if (\Phalcon\Text::_starts_with($method, 'set')) {
                if (isset($arguments[0])) {
                    $this->set(lcfirst(substr($method, 3)), $arguments[0]);
                    return null;
                }
            }
            
            /**
             * The method doesn't start with set/get throw an exception
             */
            throw new Exception('Call to undefined method or service \'' . method . '\'');
        }

        /**
         * Set a default dependency injection container to be obtained into static methods
         *
         * @param
         *            \Phalcon\DiInterface dependencyInjector
         */
        public static function setDefault(\Phalcon\DiInterface $dependencyInjector)
        {
            self::$_default = $dependencyInjector;
        }

        /**
         * Return the lastest DI created
         *
         * @return \Phalcon\DiInterface
         */
        public static function getDefault()
        {
            return self::$_default;
        }

        /**
         * Resets the internal default DI
         */
        public static function reset()
        {
            self::$_default = null;
        }
    }
}
