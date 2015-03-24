<?php
namespace Phalcon
{

    use Phalcon\DiInterface;
    use Phalcon\Tag\Select;
    use Phalcon\Tag\Exception;
    use Phalcon\Mvc\UrlInterface;
    use Phalcon\EscaperInterface;

    /**
     * Phalcon\Tag
     *
     * Phalcon\Tag is designed to simplify building of HTML tags.
     * It provides a set of helpers to generate HTML in a dynamic way.
     * This component is an abstract class that you can extend to add more helpers.
     */
    class Tag
    {

        const HTML32 = 1;

        const HTML401_STRICT = 2;

        const HTML401_TRANSITIONAL = 3;

        const HTML401_FRAMESET = 4;

        const HTML5 = 5;

        const XHTML10_STRICT = 6;

        const XHTML10_TRANSITIONAL = 7;

        const XHTML10_FRAMESET = 8;

        const XHTML11 = 9;

        const XHTML20 = 10;

        const XHTML5 = 11;

        protected static $_displayValues = array();

        protected static $_documentTitle = null;

        protected static $_documentTitleSeparator = null;

        protected static $_documentType = self::XHTML5;

        /**
         * Framework Dispatcher
         *
         * @var \Phalcon\DiInterface
         */
        protected static $_dependencyInjector = null;

        protected static $_urlService = null;

        protected static $_dispatcherService = null;

        protected static $_escaperService = null;

        protected static $_autoEscape = true;

        /**
         * Obtains the 'escaper' service if required
         *
         * @param
         *            array params
         * @return \Phalcon\EscaperInterface
         */
        public static function getEscaper($params)
        {
            if (isset($params['escape'])) {
                $autoescape = $params['escape'];
            } else {
                $autoescape = self::$_autoEscape;
            }
            
            if ($autoescape) {
                return self::getEscaperService();
            }
            return null;
        }

        /**
         * Renders parameters keeping order in their HTML attributes
         *
         * @param
         *            string code
         * @param
         *            array attributes
         * @return string
         */
        public static function renderAttributes($code, $attributes)
        {
            $order = array(
                'rel' => null,
                'type' => null,
                'for' => null,
                'src' => null,
                'href' => null,
                'action' => null,
                'id' => null,
                'name' => null,
                'value' => null,
                'class' => null
            );
            
            $attrs = array();
            foreach ($order as $key => $value) {
                if (isset($attributes[$key])) {
                    $attrs[$key] = $attributes[$key];
                }
            }
            
            foreach ($attributes as $key => $value) {
                if (! isset($order[$key])) {
                    $attrs[$key] = $value;
                }
            }
            
            $escaper = self::getEscaper($attributes);
            
            unset($attrs['escape']);
            
            $newCode = $code;
            
            foreach ($attrs as $key => $value) {
                if (is_string($key) && $value !== null) {
                    if ($escaper) {
                        $escaped = $escaper->escapeHtmlAttr($value);
                    } else {
                        $escaped = $value;
                    }
                    $newCode .= ' ' . $key . '="' . $escaped . '"';
                }
            }
            return $newCode;
        }

        /**
         * Sets the dependency injector container.
         *
         * @param
         *            \Phalcon\DiInterface dependencyInjector
         */
        public static function setDI(\Phalcon\DiInterface $dependencyInjector)
        {
            self::$_dependencyInjector = $dependencyInjector;
        }

        /**
         * Internally gets the request dispatcher
         *
         * @return \Phalcon\DiInterface
         */
        public static function getDI()
        {
            if (! is_object(self::$_dependencyInjector)) {
                return \Phalcon\Di::getDefault();
            }
            return self::$_dependencyInjector;
        }

        /**
         * Returns a URL service from the default DI
         *
         * @return \Phalcon\Mvc\UrlInterface
         */
        public static function getUrlService()
        {
            if (! is_object(self::$_urlService)) {
                $dependencyInjector = self::getDI();
                
                if (! is_object($dependencyInjector)) {
                    throw new Exception("A dependency injector container is required to obtain the 'url' service");
                }
                
                self::$_urlService = $dependencyInjector->getShared('url');
            }
            
            return self::$_urlService;
        }

        /**
         * Returns an Escaper service from the default DI
         *
         * @return \Phalcon\EscaperInterface
         */
        public static function getEscaperService()
        {
            if (! is_object(self::$_escaperService)) {
                $dependencyInjector = self::getDI();
                
                if (! is_object($dependencyInjector)) {
                    throw new Exception("A dependency injector container is required to obtain the 'escaper' service");
                }
                
                self::$_escaperService = $dependencyInjector->getShared('escaper');
            }
            
            return self::$_escaperService;
        }

        /**
         * Set autoescape mode in generated html
         *
         * @param
         *            boolean autoescape
         */
        public static function setAutoescape($autoescape)
        {
            self::$_autoEscape = $autoescape;
        }

        /**
         * Assigns default values to generated tags by helpers
         *
         * <code>
         * //Assigning "peter" to "name" component
         * \Phalcon\Tag::setDefault("name", "peter");
         *
         * //Later in the view
         * echo \Phalcon\Tag::textField("name"); //Will have the value "peter" by default
         * </code>
         *
         * @param
         *            string id
         * @param
         *            string value
         */
        public static function setDefault($id, $value)
        {
            if (is_array($value) || is_object($value)) {
                throw new Exception("Only scalar values can be assigned to UI components");
            }
            
            self::$_displayValues[$id] = $value;
        }

        /**
         * Assigns default values to generated tags by helpers
         *
         * <code>
         * //Assigning "peter" to "name" component
         * \Phalcon\Tag::setDefaults(array("name" => "peter"));
         *
         * //Later in the view
         * echo \Phalcon\Tag::textField("name"); //Will have the value "peter" by default
         * </code>
         *
         * @param
         *            array values
         * @param
         *            boolean merge
         */
        public static function setDefaults($values, $merge = null)
        {
            if ($merge && is_array(self::$_displayValues)) {
                self::$_displayValues = array_merge(self::$_displayValues, $values);
            } else {
                self::$_displayValues = $values;
            }
        }

        /**
         * Alias of \Phalcon\Tag::setDefault
         *
         * @param
         *            string id
         * @param
         *            string value
         */
        public static function displayTo($id, $value)
        {
            return self::setDefault($id, $value);
        }

        /**
         * Check if a helper has a default value set using \Phalcon\Tag::setDefault or value from _POST
         *
         * @param
         *            string name
         * @return boolean
         */
        public static function hasValue($name)
        {
            return isset(self::$_displayValues[$name]) || isset($_POST[$name]);
        }

        /**
         * Every helper calls this function to check whether a component has a predefined
         * value using \Phalcon\Tag::setDefault or value from _POST
         *
         * @param
         *            string name
         * @param
         *            array params
         * @return mixed
         */
        public static function getValue($name, $params = null)
        {
            if ($params && isset($params['value'])) {
                return $params['value'];
            }
            if (isset(self::$_displayValues[$name])) {
                return self::$_displayValues[$name];
            }
            if (isset($_POST[$name])) {
                return $_POST[$name];
            }
            return null;
        }

        /**
         * Resets the request and internal values to avoid those fields will have any default value
         */
        public static function resetInput()
        {
            self::$_displayValues = array();
            $_POST = array();
        }

        /**
         * Builds a HTML A tag using framework conventions
         *
         * <code>
         * echo \Phalcon\Tag::linkTo("signup/register", "Register Here!");
         * echo \Phalcon\Tag::linkTo(array("signup/register", "Register Here!"));
         * echo \Phalcon\Tag::linkTo(array("signup/register", "Register Here!", "class" => "btn-primary"));
         * echo \Phalcon\Tag::linkTo("http://phalconphp.com/", "Phalcon", FALSE);
         * echo \Phalcon\Tag::linkTo(array("http://phalconphp.com/", "Phalcon Home", FALSE));
         * echo \Phalcon\Tag::linkTo(array("http://phalconphp.com/", "Phalcon Home", "local" =>FALSE));
         * </code>
         *
         * @param
         *            array|string parameters
         * @param
         *            string text
         * @param
         *            boolean local
         * @return string
         */
        public static function linkTo($parameters, $text = null, $local = null)
        {
            if (! is_array($parameters)) {
                $parameters = array(
                    $parameters,
                    $text,
                    $local
                );
            }
            
            if (isset($parameters[0])) {
                $action = $parameters[0];
            } else {
                if (isset($parameters['action'])) {
                    $action = $parameters['action'];
                    unset($parameters['action']);
                } else {
                    $action = '';
                }
            }
            
            if (isset($parameters[1])) {
                $text = $parameters[1];
            } else {
                if (isset($parameters['text'])) {
                    $text = $parameters['text'];
                    unset($parameters['text']);
                } else {
                    $text = '';
                }
            }
            
            if (isset($parameters[2])) {
                $local = $parameters[2];
            } else {
                if (isset($parameters['local'])) {
                    $local = $parameters['local'];
                    unset($parameters['local']);
                } else {
                    $local = true;
                }
            }
            
            if (isset($parameters['query'])) {
                $query = $parameters['query'];
                unset($parameters['query']);
            } else {
                $query = null;
            }
            
            $url = self::getUrlService();
            $parameters['href'] = $url->get($action, $query, $local);
            $code = self::renderAttributes('<a', $parameters);
            $code .= '>' . $text . '</a>';
            
            return $code;
        }

        /**
         * Builds generic INPUT tags
         *
         * @param
         *            string type
         * @param
         *            array parameters
         * @param
         *            boolean asValue
         * @return string
         */
        final protected static function _inputField($type, $parameters, $asValue = null)
        {
            if (! is_array($parameters)) {
                $parameters = array(
                    $parameters,
                    $text,
                    $local
                );
            }
            
            if ($asValue == false) {
                if (! isset($parameters[0])) {
                    $parameters[0] = $parameters['id'];
                } else {
                    $id = $parameters[0];
                }
                if (! isset($parameters['name']) || empty($parameters['name'])) {
                    $parameters['name'] = $id;
                }
                
                /**
                 * Automatically assign the id if the name is not an array
                 */
                if (is_string($id) && ! strpos($id, '[') && ! isset($parameters['id'])) {
                    $parameters['id'] = $id;
                }
                
                $parameters['value'] = self::getValue($id, $parameters);
            } else {
                /**
                 * Use the "id" as value if the user hadn't set it
                 */
                if (! isset($parameters['value']) && isset($parameters[0])) {
                    $parameters['value'] = $parameters[0];
                }
            }
            
            $parameters['type'] = $type;
            $code = self::renderAttributes('<input', $parameters);
            
            /**
             * Check if Doctype is XHTML
             */
            if (self::$_documentType > self::HTML5) {
                $code .= ' />';
            } else {
                $code .= '>';
            }
            
            return $code;
        }

        /**
         * Builds INPUT tags that implements the checked attribute
         *
         * @param
         *            string type
         * @param
         *            array parameters
         * @return string
         */
        final protected static function _inputFieldChecked($type, $parameters)
        {
            if (! is_array($parameters)) {
                $parameters = array(
                    $parameters
                );
            }
            if (! isset($parameters[0])) {
                $parameters[0] = $parameters['id'];
            }
            
            $id = $parameters[0];
            if (! isset($parameters['name']) || empty($parameters['name'])) {
                $parameters['name'] = $id;
            }
            
            /**
             * Automatically assign the id if the name is not an array
             */
            if (! strpos($id, '[')) {
                if (! isset($parameters['id'])) {
                    $parameters['id'] = $id;
                }
            }
            
            /**
             * Automatically check inputs
             */
            if (isset($parameters['value'])) {
                $currentValue = $parameters['value'];
                unset($parameters['value']);
                
                $value = self::getValue($id, $parameters);
                
                if ($value && $currentValue == $value) {
                    $parameters['checked'] = 'checked';
                }
                $parameters['value'] = $currentValue;
            } else {
                $value = self::getValue($id, $parameters);
                
                /**
                 * Evaluate the value in POST
                 */
                if ($value) {
                    $parameters['checked'] = 'checked';
                }
                
                /**
                 * Update the value anyways
                 */
                $parameters['value'] = $value;
            }
            
            $parameters['type'] = $type;
            $code = self::renderAttributes('<input', $parameters);
            
            /**
             * Check if Doctype is XHTML
             */
            if (self::$_documentType > self::HTML5) {
                $code .= ' />';
            } else {
                $code .= '>';
            }
            
            return $code;
        }

        /**
         * Builds a HTML input[type="color"] tag
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function colorField($parameters)
        {
            return self::_inputField('color', $parameters);
        }

        /**
         * Builds a HTML input[type="text"] tag
         *
         * <code>
         * echo \Phalcon\Tag::textField(array("name", "size" => 30));
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function textField($parameters)
        {
            return self::_inputField('text', $parameters);
        }

        /**
         * Builds a HTML input[type="number"] tag
         *
         * <code>
         * echo \Phalcon\Tag::numericField(array("price", "min" => "1", "max" => "5"));
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function numericField($parameters)
        {
            return self::_inputField('number', $parameters);
        }

        /**
         * Builds a HTML input[type="range"] tag
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function rangeField($parameters)
        {
            return self::_inputField('range', $parameters);
        }

        /**
         * Builds a HTML input[type="email"] tag
         *
         * <code>
         * echo \Phalcon\Tag::emailField("email");
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function emailField($parameters)
        {
            return self::_inputField('email', $parameters);
        }

        /**
         * Builds a HTML input[type="date"] tag
         *
         * <code>
         * echo \Phalcon\Tag::dateField(array("born", "value" => "14-12-1980"))
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function dateField($parameters)
        {
            return self::_inputField('date', $parameters);
        }

        /**
         * Builds a HTML input[type="datetime"] tag
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function dateTimeField($parameters)
        {
            return self::_inputField('datetime', $parameters);
        }

        /**
         * Builds a HTML input[type="datetime-local"] tag
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function dateTimeLocalField($parameters)
        {
            return self::_inputField('datetime-local', $parameters);
        }

        /**
         * Builds a HTML input[type="month"] tag
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function monthField($parameters)
        {
            return self::_inputField('month', $parameters);
        }

        /**
         * Builds a HTML input[type="time"] tag
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function timeField($parameters)
        {
            return self::_inputField('time', $parameters);
        }

        /**
         * Builds a HTML input[type="week"] tag
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function weekField($parameters)
        {
            return self::_inputField('week', $parameters);
        }

        /**
         * Builds a HTML input[type="password"] tag
         *
         * <code>
         * echo \Phalcon\Tag::passwordField(array("name", "size" => 30));
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function passwordField($parameters)
        {
            return self::_inputField('password', $parameters);
        }

        /**
         * Builds a HTML input[type="hidden"] tag
         *
         * <code>
         * echo \Phalcon\Tag::hiddenField(array("name", "value" => "mike"));
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function hiddenField($parameters)
        {
            return self::_inputField('hidden', $parameters);
        }

        /**
         * Builds a HTML input[type="file"] tag
         *
         * <code>
         * echo \Phalcon\Tag::fileField("file");
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function fileField($parameters)
        {
            return self::_inputField('file', $parameters);
        }

        /**
         * Builds a HTML input[type="search"] tag
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function searchField($parameters)
        {
            return self::_inputField('search', $parameters);
        }

        /**
         * Builds a HTML input[type="tel"] tag
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function telField($parameters)
        {
            return self::_inputField('tel', $parameters);
        }

        /**
         * Builds a HTML input[type="url"] tag
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function urlField($parameters)
        {
            return self::_inputField('url', $parameters);
        }

        /**
         * Builds a HTML input[type="check"] tag
         *
         * <code>
         * echo \Phalcon\Tag::checkField(array("terms", "value" => "Y"));
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function checkField($parameters)
        {
            return self::_inputFieldChecked('checkbox', $parameters);
        }

        /**
         * Builds a HTML input[type="radio"] tag
         *
         * <code>
         * echo \Phalcon\Tag::radioField(array("weather", "value" => "hot"))
         * </code>
         *
         * Volt syntax:
         * <code>
         * {{ radio_field("Save") }}
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function radioField($parameters)
        {
            return self::_inputFieldChecked("radio", $parameters);
        }

        /**
         * Builds a HTML input[type="image"] tag
         *
         * <code>
         * echo \Phalcon\Tag::imageInput(array("src" => "/img/button.png"));
         * </code>
         *
         * Volt syntax:
         * <code>
         * {{ image_input("src": "/img/button.png") }}
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function imageInput($parameters)
        {
            return self::_inputField("image", $parameters, true);
        }

        /**
         * Builds a HTML input[type="submit"] tag
         *
         * <code>
         * echo \Phalcon\Tag::submitButton("Save")
         * </code>
         *
         * Volt syntax:
         * <code>
         * {{ submit_button("Save") }}
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function submitButton($parameters)
        {
            return self::_inputField("submit", $parameters, true);
        }

        /**
         * Builds a HTML SELECT tag using a PHP array for options
         *
         * <code>
         * echo \Phalcon\Tag::selectStatic("status", array("A" => "Active", "I" => "Inactive"))
         * </code>
         *
         * @param
         *            array parameters
         * @param
         *            array data
         * @return string
         */
        public static function selectStatic($parameters, $data = null)
        {
            return Select::selectField($parameters, $data);
        }

        /**
         * Builds a HTML SELECT tag using a \Phalcon\Mvc\Model resultset as options
         *
         * <code>
         * echo \Phalcon\Tag::select(array(
         * "robotId",
         * Robots::find("type = "mechanical""),
         * "using" => array("id", "name")
         * ));
         * </code>
         *
         * Volt syntax:
         * <code>
         * {{ select("robotId", robots, "using": ["id", "name"]) }}
         * </code>
         *
         * @param
         *            array parameters
         * @param
         *            array data
         * @return string
         */
        public static function select($parameters, $data = null)
        {
            return Select::selectField($parameters, $data);
        }

        /**
         * Builds a HTML TEXTAREA tag
         *
         * <code>
         * echo \Phalcon\Tag::textArea(array("comments", "cols" => 10, "rows" => 4))
         * </code>
         *
         * Volt syntax:
         * <code>
         * {{ text_area("comments", "cols": 10, "rows": 4) }}
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function textArea($parameters)
        {
            if (! is_array($parameters)) {
                $parameters = array(
                    $parameters
                );
            }
            
            if (! isset($parameters[0]) && isset($parameters['id'])) {
                $parameters[0] = $parameters['id'];
            }
            
            $id = $parameters[0];
            if (! isset($parameters['name']) || empty($parameters['name'])) {
                $parameters['name'] = $id;
            }
        }

        /**
         * Builds a HTML FORM tag
         *
         * <code>
         * echo \Phalcon\Tag::form("posts/save");
         * echo \Phalcon\Tag::form(array("posts/save", "method" => "post"));
         * </code>
         *
         * Volt syntax:
         * <code>
         * {{ form("posts/save") }}
         * {{ form("posts/save", "method": "post") }}
         * </code>
         *
         * @param
         *            array parameters
         * @return string
         */
        public static function form($parameters)
        {}

        /**
         * Builds a HTML close FORM tag
         *
         * @return string
         */
        public static function endForm()
        {}

        /**
         * Set the title of view content
         *
         * <code>
         * \Phalcon\Tag::setTitle("Welcome to my Page");
         * </code>
         *
         * @param
         *            string title
         */
        public static function setTitle($title)
        {}

        /**
         * Set the title separator of view content
         *
         * <code>
         * \Phalcon\Tag::setTitleSeparator("-");
         * </code>
         *
         * @param
         *            string titleSeparator
         */
        public static function setTitleSeparator($titleSeparator)
        {}

        /**
         * Appends a text to current document title
         *
         * @param
         *            string title
         */
        public static function appendTitle($title)
        {}

        /**
         * Prepends a text to current document title
         *
         * @param
         *            string title
         */
        public static function prependTitle($title)
        {}

        /**
         * Gets the current document title
         *
         * <code>
         * echo \Phalcon\Tag::getTitle();
         * </code>
         *
         * <code>
         * {{ get_title() }}
         * </code>
         *
         * @return string
         */
        public static function getTitle($tags = null)
        {}

        /**
         * Gets the current document title separator
         *
         * <code>
         * echo \Phalcon\Tag::getTitleSeparator();
         * </code>
         *
         * <code>
         * {{ get_title_separator() }}
         * </code>
         *
         * @return string
         */
        public static function getTitleSeparator()
        {}

        /**
         * Builds a LINK[rel="stylesheet"] tag
         *
         * <code>
         * echo \Phalcon\Tag::stylesheetLink("http://fonts.googleapis.com/css?family=Rosario", false);
         * echo \Phalcon\Tag::stylesheetLink("css/style.css");
         * </code>
         *
         * Volt Syntax:
         * <code>
         * {{ stylesheet_link("http://fonts.googleapis.com/css?family=Rosario", false) }}
         * {{ stylesheet_link("css/style.css") }}
         * </code>
         *
         * @param
         *            array parameters
         * @param
         *            boolean local
         * @return string
         */
        public static function stylesheetLink($parameters = null, $local = null)
        {}

        /**
         * Builds a SCRIPT[type="javascript"] tag
         *
         * <code>
         * echo \Phalcon\Tag::javascriptInclude("http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js", false);
         * echo \Phalcon\Tag::javascriptInclude("javascript/jquery.js");
         * </code>
         *
         * Volt syntax:
         * <code>
         * {{ javascript_include("http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js", false) }}
         * {{ javascript_include("javascript/jquery.js") }}
         * </code>
         *
         * @param
         *            array parameters
         * @param
         *            boolean local
         * @return string
         */
        public static function javascriptInclude($parameters = null, $local = null)
        {}

        /**
         * Builds HTML IMG tags
         *
         * <code>
         * echo \Phalcon\Tag::image("img/bg.png");
         * echo \Phalcon\Tag::image(array("img/photo.jpg", "alt" => "Some Photo"));
         * </code>
         *
         * Volt Syntax:
         * <code>
         * {{ image("img/bg.png") }}
         * {{ image("img/photo.jpg", "alt": "Some Photo") }}
         * {{ image("http://static.mywebsite.com/img/bg.png", false) }}
         * </code>
         *
         * @param
         *            array parameters
         * @param
         *            boolean local
         * @return string
         */
        public static function image($parameters = null, $local = null)
        {}

        /**
         * Converts texts into URL-friendly titles
         *
         * <code>
         * echo \Phalcon\Tag::friendlyTitle("These are big important news", "-")
         * </code>
         *
         * @param
         *            string text
         * @param
         *            string separator
         * @param
         *            boolean lowercase
         * @param
         *            mixed replace
         * @return text
         */
        public static function friendlyTitle($text, $separator = null, $lowercase = null, $replace = null)
        {}

        /**
         * Set the document type of content
         *
         * @param
         *            integer doctype
         */
        public static function setDocType($doctype)
        {}

        /**
         * Get the document type declaration of content
         *
         * @return string
         */
        public static function getDocType()
        {}

        /**
         * Builds a HTML tag
         *
         * <code>
         * echo \Phalcon\Tag::tagHtml(name, parameters, selfClose, onlyStart, eol);
         * </code>
         *
         * @param
         *            string tagName
         * @param
         *            array parameters
         * @param
         *            boolean selfClose
         * @param
         *            boolean onlyStart
         * @param
         *            boolean useEol
         * @return string
         */
        public static function tagHtml($tagName, $parameters = null, $selfClose = null, $onlyStart = null, $useEol = null)
        {}

        /**
         * Builds a HTML tag closing tag
         *
         * <code>
         * echo \Phalcon\Tag::tagHtmlClose("script", true)
         * </code>
         *
         * @param
         *            string tagName
         * @param
         *            boolean useEol
         * @return string
         */
        public static function tagHtmlClose($tagName, $useEol = null)
        {}
    }
}
