<?php
namespace Phalcon
{

    /**
     * Phalcon\Dispatcher
     *
     * This is the base class for Phalcon\Mvc\Dispatcher and Phalcon\CLI\Dispatcher.
     * This class can't be instantiated directly, you can use it to create your own dispatchers
     */
    abstract class Dispatcher implements \Phalcon\DispatcherInterface, \Phalcon\Di\InjectionAwareInterface, \Phalcon\Events\EventsAwareInterface
    {

        const EXCEPTION_NO_DI = 0;

        const EXCEPTION_CYCLIC_ROUTING = 1;

        const EXCEPTION_HANDLER_NOT_FOUND = 2;

        const EXCEPTION_INVALID_HANDLER = 3;

        const EXCEPTION_INVALID_PARAMS = 4;

        const EXCEPTION_ACTION_NOT_FOUND = 5;

        /**
         *
         * @var \Phalcon\DiInterface
         */
        protected $_dependencyInjector;

        /**
         *
         * @var \Phalcon\Events\ManagerInterface
         */
        protected $_eventsManager;

        protected $_activeHandler;

        protected $_finished = false;

        protected $_forwarded = false;

        protected $_moduleName = null;

        protected $_namespaceName = null;

        protected $_handlerName = null;

        protected $_actionName = null;

        protected $_params = null;

        protected $_returnedValue = null;

        protected $_lastHandler = null;

        protected $_defaultNamespace = null;

        protected $_defaultHandler = null;

        protected $_defaultAction = '';

        protected $_handlerSuffix = '';

        protected $_actionSuffix = 'Action';

        protected $_previousHandlerName = null;

        protected $_previousActionName = null;

        /**
         * \Phalcon\Dispatcher constructor
         */
        public function __construct()
        {
            $this->_params = array();
        }

        /**
         * Sets the dependency injector
         *
         * @param
         *            \Phalcon\DiInterface dependencyInjector
         */
        public function setDI(\Phalcon\DiInterface $dependencyInjector)
        {
            $this->_dependencyInjector = $dependencyInjector;
        }

        /**
         * Returns the internal dependency injector
         *
         * @return \Phalcon\DiInterface
         */
        public function getDI()
        {
            return $this->_dependencyInjector;
        }

        /**
         * Sets the events manager
         *
         * @param
         *            \Phalcon\Events\ManagerInterface eventsManager
         */
        public function setEventsManager(\Phalcon\Events\ManagerInterface $eventsManager)
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
         * Sets the default action suffix
         *
         * @param
         *            string actionSuffix
         */
        public function setActionSuffix($actionSuffix)
        {
            $this->_actionSuffix = $actionSuffix;
        }

        /**
         * Sets the module where the controller is (only informative)
         *
         * @param
         *            string moduleName
         */
        public function setModuleName($moduleName)
        {
            $this->_moduleName = $moduleName;
        }

        /**
         * Gets the module where the controller class is
         *
         * @return string
         */
        public function getModuleName()
        {
            return $this->_moduleName;
        }

        /**
         * Sets the namespace where the controller class is
         *
         * @param
         *            string namespaceName
         */
        public function setNamespaceName($namespaceName)
        {
            return $this->_namespaceName = $namespaceName;
        }

        /**
         * Gets a namespace to be prepended to the current handler name
         *
         * @return string
         */
        public function getNamespaceName()
        {
            return $this->_namespaceName;
        }

        /**
         * Sets the default namespace
         *
         * @param
         *            string namespaceName
         */
        public function setDefaultNamespace($namespaceName)
        {
            $this->_defaultNamespace = $namespaceName;
        }

        /**
         * Returns the default namespace
         *
         * @return string
         */
        public function getDefaultNamespace()
        {
            return $this->_defaultNamespace;
        }

        /**
         * Sets the default action name
         *
         * @param
         *            string actionName
         */
        public function setDefaultAction($actionName)
        {
            $this->_defaultAction = $actionName;
        }

        /**
         * Sets the action name to be dispatched
         *
         * @param
         *            string actionName
         */
        public function setActionName($actionName)
        {
            $this->_actionName = $actionName;
        }

        /**
         * Gets the latest dispatched action name
         *
         * @return string
         */
        public function getActionName()
        {
            return $this->_actionName;
        }

        /**
         * Sets action params to be dispatched
         *
         * @param
         *            array params
         */
        public function setParams($params)
        {
            if (! is_array($params)) {
                $this->_throwDispatchException('Parameters must be an Array', self::EXCEPTION_INVALID_PARAMS);
                return;
            }
            $this->_params = $params;
        }

        /**
         * Gets action params
         *
         * @return array
         */
        public function getParams()
        {
            return $this->_params;
        }

        /**
         * Set a param by its name or numeric index
         *
         * @param
         *            mixed param
         * @param
         *            mixed value
         */
        public function setParam($param, $value)
        {
            $this->_params[$name] = $value;
        }

        /**
         * Gets a param by its name or numeric index
         *
         * @param
         *            mixed param
         * @param
         *            string|array filters
         * @param
         *            mixed defaultValue
         * @return mixed
         */
        public function getParam($param, $filters = null, $defaultValue = null)
        {
            $params = $this->_params;
            $paramValue = null;
            if (isset($params[$param])) {
                $paramValue = $params[$param];
                if ($filters != null) {
                    $dependencyInjector = $this->_dependencyInjector;
                    if (! is_object($dependencyInjector)) {
                        $this->_throwDispatchException('A dependency injection object is required to access the \'filter\' service', self::EXCEPTION_NO_DI);
                        return null;
                    }
                    $filter = $dependencyInjector->getShared('filter');
                    return $filter->sanitize($paramValue, $filters);
                } else {
                    return $paramValue;
                }
            }
            return $defaultValue;
        }

        /**
         * Returns the current method to be/executed in the dispatcher
         *
         * @return string
         */
        public function getActiveMethod()
        {
            return $this->_actionName . $this->_actionSuffix;
        }

        /**
         * Checks if the dispatch loop is finished or has more pendent controllers/tasks to dispatch
         *
         * @return boolean
         */
        public function isFinished()
        {
            return $this->_finished;
        }

        /**
         * Sets the latest returned value by an action manually
         *
         * @param
         *            mixed value
         */
        public function setReturnedValue($value)
        {
            $this->_returnedValue = $value;
        }

        /**
         * Returns value returned by the lastest dispatched action
         *
         * @return mixed
         */
        public function getReturnedValue()
        {
            return $this->_returnedValue;
        }

        /**
         * Dispatches a handle action taking into account the routing parameters
         *
         * @return object
         */
        public function dispatch()
        {
            $dependencyInjector = $this->_dependencyInjector;
            if (! is_object($dependencyInjector)) {
                $this->_throwDispatchException('A dependency injection container is required to access related dispatching services', self::EXCEPTION_NO_DI);
                return false;
            }
            
            // Calling beforeDispatchLoop
            $eventsManager = $this->_eventsManager;
            $hasEventsManager = is_object($eventsManager);
            if ($hasEventsManager) {
                if ($eventsManager->fire('dispatch:beforeDispatchLoop', $this) === false) {
                    return false;
                }
            }
            
            $numberDispatches = 0;
            $handlerSuffix = $this->_handlerSuffix;
            $actionSuffix = $this->_actionSuffix;
            
            $this->_finished = false;
            
            while (! $this->_finished) {
                ++ $numberDispatches;
                
                // Throw an exception after 256 consecutive forwards
                if ($numberDispatches == 256) {
                    $this->_throwDispatchException('Dispatcher has detected a cyclic routing causing stability problems', self::EXCEPTION_CYCLIC_ROUTING);
                    break;
                }
                
                $this->_finished = true;
                
                // If the current namespace is null we used the set in this->_defaultNamespace
                $namespaceName = $this->_namespaceName;
                if (! $namespaceName) {
                    $this->_namespaceName = $namespaceName = $this->_defaultNamespace;
                }
                
                // If the handler is null we use the set in this->_defaultHandler
                $handlerName = $this->_handlerName;
                if (! $handlerName) {
                    $this->_handlerName = $handlerName = $this->_defaultHandler;
                }
                
                // If the action is null we use the set in this->_defaultAction
                $actionName = $this->_actionName;
                if (! $actionName) {
                    $this->_actionName = $actionName = $this->_defaultAction;
                }
                
                // Calling beforeDispatch
                if ($hasEventsManager) {
                    if ($eventsManager->fire('dispatch:beforeDispatch', $this) === false) {
                        continue;
                    }
                    
                    // Check if the user made a forward in the listener
                    if (! $this->_finished == false) {
                        continue;
                    }
                }
                
                // We don't camelize the classes if they are in namespaces
                if (strpos($handlerName, '\\') === false) {
                    $camelizedClass = \Phalcon\Text::camelize($handlerName);
                } else {
                    $camelizedClass = $handlerName;
                }
                
                // Create the complete controller class name prepending the namespace
                if ($namespaceName) {
                    if (\Phalcon\Text::_ends_with($namespaceName, '\\')) {
                        $handlerClass = $namespaceName . $camelizedClass . $handlerSuffix;
                    } else {
                        $handlerClass = $namespaceName . '\\' . $camelizedClass . $handlerSuffix;
                    }
                } else {
                    $handlerClass = $camelizedClass . $handlerSuffix;
                }
                
                // Handlers are retrieved as shared instances from the Service Container
                $hasService = $dependencyInjector->has($handlerClass);
                if (! $hasService) {
                    // DI doesn't have a service with that name, try to load it using an autoloader
                    $hasService = class_exists($handlerClass);
                }
                
                // If the service can be loaded we throw an exception
                if (! $hasService) {
                    $status = $this->_throwDispatchException($handlerClass . '  handler class cannot be loaded', self::EXCEPTION_HANDLER_NOT_FOUND);
                    if ($status === false) {
                        // Check if the user made a forward in the listener
                        if ($this->_finished == false) {
                            continue;
                        }
                    }
                    break;
                }
                
                // Handlers must be only objects
                $handler = $dependencyInjector->getShared($handlerClass);
                
                $wasFresh = false;
                // If the object was recently created in the DI we initialize it
                if ($dependencyInjector->wasFreshInstance() === true) {
                    $wasFresh = true;
                }
                
                if (! is_object($handler)) {
                    $status = $this->_throwDispatchException('Invalid handler returned from the services container', self::EXCEPTION_INVALID_HANDLER);
                    if ($status === false) {
                        if ($this->_finished === false) {
                            continue;
                        }
                    }
                    break;
                }
                
                $this->_activeHandler = $handler;
                
                // Check if the params is an array
                $params = $this->_params;
                if (! is_array($params)) {
                    // An invalid parameter variable was passed throw an exception
                    $status = $this->_throwDispatchException('Action parameters must be an Array', self::EXCEPTION_INVALID_PARAMS);
                    if ($status === false) {
                        if ($this->_finished === false) {
                            continue;
                        }
                    }
                    break;
                }
                
                // Check if the method exists in the handler
                $actionMethod = $actionName . $actionSuffix;
                if (! method_exists($handler, $actionMethod)) {
                    
                    // Call beforeNotFoundAction
                    if ($hasEventsManager) {
                        if ($eventsManager->fire('dispatch:beforeNotFoundAction', $this) === false) {
                            continue;
                        }
                        
                        if ($this->_finished === false) {
                            continue;
                        }
                    }
                    
                    // Try to throw an exception when an action isn't defined on the object
                    $status = $this->_throwDispatchException('Action \'' . $actionName . '\' was not found on handler \'' . $handlerName . '\'', self::EXCEPTION_ACTION_NOT_FOUND);
                    if ($status === false) {
                        if ($this->_finished === false) {
                            continue;
                        }
                    }
                    
                    break;
                }
                
                // Calling beforeExecuteRoute
                if ($hasEventsManager) {
                    if ($eventsManager->fire('dispatch:beforeExecuteRoute', $this) === false) {
                        continue;
                    }
                    
                    // Check if the user made a forward in the listener
                    if ($this->_finished === false) {
                        continue;
                    }
                }
                
                // Calling beforeExecuteRoute as callback and event
                if (method_exists($handler, 'beforeExecuteRoute')) {
                    if ($handler->beforeExecuteRoute($this) === false) {
                        continue;
                    }
                    
                    // Check if the user made a forward in the listener
                    if ($this->_finished === false) {
                        continue;
                    }
                }
                
                /**
                 * Call the 'initialize' method just once per request
                 */
                if ($wasFresh === true) {
                    if (method_exists($handler, 'initialize')) {
                        $handler->initialize();
                    }
                    
                    /**
                     * Calling afterInitialize
                     */
                    if ($hasEventsManager) {
                        if ($eventsManager->fire('dispatch:afterInitialize', $this) === false) {
                            continue;
                        }
                        // Check if the user made a forward in the listener
                        if ($this->_finished === false) {
                            continue;
                        }
                    }
                }
                
                try {
                    // We update the latest value produced by the latest handler
                    $this->_returnedValue = call_user_func_array(array(
                        $handler,
                        $actionMethod
                    ), $params);
                    $this->_lastHandler = $handler;
                } catch (\Exception $e) {
                    if ($this->_handleException($e) === false) {
                        if ($this->_finished === false) {
                            continue;
                        }
                    } else {
                        throw $e;
                    }
                }
                
                // Calling afterExecuteRoute
                if ($hasEventsManager) {
                    if ($eventsManager->fire('dispatch:afterExecuteRoute', $this, $this->_returnedValue) === false) {
                        continue;
                    }
                    
                    if ($this->_finished === false) {
                        continue;
                    }
                    
                    // Call afterDispatch
                    $eventsManager->fire('dispatch:afterDispatch', $this);
                }
                
                // Calling afterExecuteRoute as callback and event
                if (method_exists($handler, 'afterExecuteRoute')) {
                    if ($handler->afterExecuteRoute($this, $this->_returnedValue) === false) {
                        continue;
                    }
                    if ($this->_finished === false) {
                        continue;
                    }
                }
            }
            
            // Call afterDispatchLoop
            if ($hasEventsManager) {
                $eventsManager->fire('dispatch:afterDispatchLoop', $this);
            }
            return $handler;
        }

        /**
         * Forwards the execution flow to another controller/action
         * Dispatchers are unique per module.
         * Forwarding between modules is not allowed
         *
         * <code>
         * $this->dispatcher->forward(array("controller" => "posts", "action" => "index"));
         * </code>
         *
         * @param
         *            array forward
         */
        public function forward($forward)
        {
            if (! is_array($forward)) {
                $this->_throwDispatchException('Forward parameter must be an Array');
                return null;
            }
            
            // Check if we need to forward to another namespace
            if (isset($forward['namespace'])) {
                $this->_namespaceName = $forward['namespace'];
            }
            
            // Check if we need to forward to another controller
            if (isset($forward['controller'])) {
                $this->_previousHandlerName = $this->_handlerName;
                $this->_handlerName = $forward['controller'];
            } elseif (isset($forward['task'])) {
                $this->_previousHandlerName = $this->_handlerName;
                $this->_handlerName = $forward['task'];
            }
            
            // Check if we need to forward to another action
            if (isset($forward['action'])) {
                $this->_previousActionName = $this->_actionName;
                $this->_actionName = $forward['action'];
            }
            
            // Check if we need to forward changing the current parameters
            if (isset($forward['params'])) {
                $this->_params = $forward['params'];
            }
            
            $this->_finished = false;
            $this->_forwarded = true;
        }

        /**
         * Check if the current executed action was forwarded by another one
         *
         * @return boolean
         */
        public function wasForwarded()
        {
            return $this->_forwarded;
        }

        /**
         * Throws an internal exception
         *
         * @param string $message            
         * @param int $exceptionCode            
         * @return boolean
         */
        protected abstract function _throwDispatchException($message, $exceptionCode = null);

        /**
         * Handles a user exception
         *
         * @param \Exception $exception            
         * @return boolean
         */
        protected abstract function _handleException(\Exception $exception);
    }
}
