<?php
namespace Phalcon
{

    use Phalcon\Events\ManagerInterface;
    use Phalcon\Events\EventsAwareInterface;

    /**
     * Phalcon\Loader
     *
     * This component helps to load your project classes automatically based on some conventions
     *
     * <code>
     * //Creates the autoloader
     * $loader = new Loader();
     *
     * //Register some namespaces
     * $loader->registerNamespaces(array(
     * 'Example\Base' => 'vendor/example/base/',
     * 'Example\Adapter' => 'vendor/example/adapter/',
     * 'Example' => 'vendor/example/'
     * ));
     *
     * //register autoloader
     * $loader->register();
     *
     * //Requiring this class will automatically include file vendor/example/adapter/Some.php
     * $adapter = Example\Adapter\Some();
     * </code>
     */
    class Loader implements \Phalcon\Events\EventsAwareInterface
    {

        protected $_eventsManager = null;

        protected $_foundPath = null;

        protected $_checkedPath = null;

        protected $_prefixes = array();

        protected $_classes = array();

        protected $_extensions;

        protected $_namespaces = array();

        protected $_directories = array();

        protected $_registered = false;

        /**
         * \Phalcon\Loader constructor
         */
        public function __construct()
        {
            $this->_extensions = array(
                'php'
            );
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
         * Sets an array of extensions that the loader must try in each attempt to locate the file
         *
         * @param
         *            array extensions
         * @return \Phalcon\Loader
         */
        public function setExtensions($extensions)
        {
            $this->_extensions = $extensions;
            return $this;
        }

        /**
         * Return file extensions registered in the loader
         *
         * @return array
         */
        public function getExtensions()
        {
            return $this->_extensions;
        }

        /**
         * Register namespaces and their related directories
         *
         * @param
         *            array namespaces
         * @param
         *            boolean merge
         * @return \Phalcon\Loader
         */
        public function registerNamespaces($namespaces, $merge = false)
        {
            $this->_namespaces = $merge ? array_merge($this->_namespaces, $namespaces) : $namespaces;
            return $this;
        }

        /**
         * Return current namespaces registered in the autoloader
         *
         * @param
         *            array
         */
        public function getNamespaces()
        {
            return $this->_namespaces;
        }

        /**
         * Register directories on which "not found" classes could be found
         *
         * @param
         *            array prefixes
         * @param
         *            boolean merge
         * @return \Phalcon\Loader
         */
        public function registerPrefixes($prefixes, $merge = null)
        {
            $this->_prefixes = $merge ? array_merge($this->_prefixes, $prefixes) : $prefixes;
            return $this;
        }

        /**
         * Return current prefixes registered in the autoloader
         *
         * @param
         *            array
         */
        public function getPrefixes()
        {
            return $this->_prefixes;
        }

        /**
         * Register directories on which "not found" classes could be found
         *
         * @param
         *            array directories
         * @param
         *            boolean merge
         * @return \Phalcon\Loader
         */
        public function registerDirs($directories, $merge = null)
        {
            $this->_directories = $merge ? array_merge($this->_directories, $directories) : $directories;
            return $this;
        }

        /**
         * Return current directories registered in the autoloader
         *
         * @param
         *            array
         */
        public function getDirs()
        {
            return $this->_directories;
        }

        /**
         * Register classes and their locations
         *
         * @param
         *            array classes
         * @param
         *            boolean merge
         * @return \Phalcon\Loader
         */
        public function registerClasses($classes, $merge = null)
        {
            $this->_classes = $merge ? array_merge($this->_classes, $classes) : $classes;
            return $this;
        }

        /**
         * Return the current class-map registered in the autoloader
         *
         * @param
         *            array
         */
        public function getClasses()
        {
            return $this->_classes;
        }

        /**
         * Register the autoload method
         *
         * @return \Phalcon\Loader
         */
        public function register()
        {
            if ($this->_registered == false) {
                spl_autoload_register(array(
                    $this,
                    'autoLoad'
                ));
                $this->_registered = true;
            }
            return $this;
        }

        /**
         * Unregister the autoload method
         *
         * @return \Phalcon\Loader
         */
        public function unregister()
        {
            if ($this->_registered === true) {
                spl_autoload_unregister(array(
                    $this,
                    'autoLoad'
                ));
                $this->_registered = false;
            }
            return $this;
        }

        /**
         * Makes the work of autoload registered classes
         *
         * @param
         *            string className
         * @return boolean
         */
        public function autoLoad($className)
        {
            $hasEvents = is_object($this->_eventsManager);
            $hasEvents && $this->_eventsManager->fire('loader:beforeCheckClass', $this, $className);
            
            /**
             * First we check for static paths for classes
             */
            if (isset($this->_classes[$className])) {
                $this->_foundPath = $this->_classes[$className];
                $hasEvents && $this->_eventsManager->fire('loader:pathFound', $this, $this->_foundPath);
                require $this->_foundPath;
                return true;
            }
            
            $ds = DIRECTORY_SEPARATOR;
            $namespaceSeparator = '\\';
            /**
             * Checking in namespaces
             */
            foreach ($this->_namespaces as $nsPrefix => $directory) {
                /**
                 * The class name must start with the current namespace
                 */
                if (starts_with($className, $nsPrefix)) {
                    $fileName = str_replace($nsPrefix . $namespaceSeparator, '', $className);
                    $fileName = str_replace($namespaceSeparator, $ds, $fileName);
                    
                    /**
                     * Add a trailing directory separator if the user forgot to do that
                     */
                    $fixedDirectory = rtrim($directory, $ds) . $ds;
                    
                    foreach ($this->_extensions as $extension) {
                        $filePath = $fixedDirectory . $fileName . '.' . $extension;
                        
                        /**
                         * Check if a events manager is available
                         */
                        $this->_checkedPath = $filePath;
                        $hasEvents && $this->_eventsManager->fire('loader:beforeCheckPath', $this);
                        
                        /**
                         * This is probably a good path, let's check if the file does exist
                         */
                        if (is_file($filePath)) {
                            $this->_foundPath = $filePath;
                            $hasEvents && $this->_eventsManager->fire('loader:pathFound', $this, $filePath);
                            
                            require $filePath;
                            return true;
                        }
                    }
                }
            }
            /**
             * Checking in prefixes
             */
            foreach ($this->_prefixes as $prefix => $directory) {
                if (starts_with($className, $prefix)) {
                    /**
                     * Get the possible file path
                     */
                    $fileName = str_replace($prefix . $namespaceSeparator, '', $className);
                    $fileName = str_replace($prefix . '_', '', $fileName);
                    $fileName = str_replace('_', $ds, $fileName);
                    
                    /**
                     * Add a trailing directory separator if the user forgot to do that
                     */
                    $fixedDirectory = rtrim($directory, $ds) . $ds;
                    
                    foreach ($this->_extensions as $extension) {
                        $filePath = $fixedDirectory . $fileName . '.' . $extension;
                        
                        /**
                         * Check if a events manager is available
                         */
                        $this->_checkedPath = $filePath;
                        $hasEvents && $this->_eventsManager->fire('loader:beforeCheckPath', $this);
                        
                        /**
                         * This is probably a good path, let's check if the file does exist
                         */
                        if (is_file($filePath)) {
                            $this->_foundPath = $filePath;
                            $hasEvents && $this->_eventsManager->fire('loader:pathFound', $this, $filePath);
                            
                            require $filePath;
                            return true;
                        }
                    }
                }
            }
            
            /**
             * Change the pseudo-separator by the directory separator in the class name
             */
            $dsClassName = str_replace('_', $ds, $className);
            
            /**
             * And change the namespace separator by directory separator too
             */
            $nsClassName = str_replace($namespaceSeparator, $ds, $dsClassName);
            
            /**
             * Checking in directories
             */
            foreach ($this->_directories as $directory) {
                /**
                 * Add a trailing directory separator if the user forgot to do that
                 */
                $fixedDirectory = rtrim($directory, $ds) . $ds;
                
                foreach ($this->_extensions as $extension) {
                    $filePath = $fixedDirectory . $fileName . '.' . $extension;
                    
                    /**
                     * Check if a events manager is available
                     */
                    $this->_checkedPath = $filePath;
                    $hasEvents && $this->_eventsManager->fire('loader:beforeCheckPath', $this);
                    
                    /**
                     * This is probably a good path, let's check if the file does exist
                     */
                    if (is_file($filePath)) {
                        $this->_foundPath = $filePath;
                        $hasEvents && $this->_eventsManager->fire('loader:pathFound', $this, $filePath);
                        
                        require $filePath;
                        return true;
                    }
                }
            }
            
            $hasEvents && $this->_eventsManager->fire('loader:afterCheckClass', $this, $className);
            
            return false;
        }

        /**
         * Get the path when a class was found
         *
         * @return string
         */
        public function getFoundPath()
        {
            return $this->_foundPath;
        }

        /**
         * Get the path the loader is checking for a path
         *
         * @return string
         */
        public function getCheckedPath()
        {
            return $this->_checkedPath;
        }
    }
}
