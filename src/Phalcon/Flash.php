<?php
namespace Phalcon
{
    
    use Phalcon\Flash\Exception;
    use Phalcon\FlashInterface;

    /**
     * Phalcon\Flash
     *
     * Shows HTML notifications related to different circumstances. Classes can be stylized using CSS
     *
     * <code>
     * $flash->success("The record was successfully deleted");
     * $flash->error("Cannot open the file");
     * </code>
     */
    abstract class Flash
    {

        protected $_cssClasses;

        protected $_implicitFlush = true;

        protected $_automaticHtml = true;

        /**
         * \Phalcon\Flash constructor
         *
         * @param
         *            array cssClasses
         */
        public function __construct($cssClasses = null)
        {
            if(!is_array($cssClasses)){
                $cssClasses = array(
                    'error' => 'errorMessage',
                    'notice' => 'noticeMessage',
                    'success' => 'successMessage',
                    'warning' => 'warningMessage'
                );
            }
            $this->_cssClasses = $cssClasses;
        }

        /**
         * Set whether the output must be implictly flushed to the output or returned as string
         *
         * @param
         *            boolean implicitFlush
         * @return \Phalcon\FlashInterface
         */
        public function setImplicitFlush($implicitFlush)
        {
            $this->_implicitFlush = $implicitFlush;
            return $this;
        }

        /**
         * Set if the output must be implictily formatted with HTML
         *
         * @param
         *            boolean automaticHtml
         * @return \Phalcon\FlashInterface
         */
        public function setAutomaticHtml($automaticHtml)
        {
            $this->_automaticHtml = $automaticHtml;
            return $this;
        }

        /**
         * Set an array with CSS classes to format the messages
         *
         * @param
         *            array cssClasses
         * @return \Phalcon\FlashInterface
         */
        public function setCssClasses($cssClasses)
        {
            $this->_cssClasses = $cssClasses;
            return $this;
        }

        /**
         * Shows a HTML error message
         *
         * <code>
         * $flash->error('This is an error');
         * </code>
         *
         * @param
         *            string message
         * @return string
         */
        public function error($message)
        {
            return $this->message('error',$message);
        }

        /**
         * Shows a HTML notice/information message
         *
         * <code>
         * $flash->notice('This is an information');
         * </code>
         *
         * @param
         *            string message
         * @return string
         */
        public function notice($message)
        {
            return $this->message('notice',$message);
        }

        /**
         * Shows a HTML success message
         *
         * <code>
         * $flash->success('The process was finished successfully');
         * </code>
         *
         * @param
         *            string message
         * @return string
         */
        public function success($message)
        {
            return $this->message('success',$message);
        }

        /**
         * Shows a HTML warning message
         *
         * <code>
         * $flash->warning('Hey, this is important');
         * </code>
         *
         * @param
         *            string message
         * @return string
         */
        public function warning($message)
        {
            return $this->message('warning',$message);
        }

        /**
         * Outputs a message formatting it with HTML
         *
         * <code>
         * $flash->outputMessage('error', message);
         * </code>
         *
         * @param
         *            string type
         * @param
         *            string|array message
         */
        public function outputMessage($type, $message)
        {
            if($this->_automaticHtml === true){
                if(isset($this->_cssClasses[$type])){
                    $cssClasses = $this->_cssClasses[$type];
                    if(is_array($cssClasses)){
                        $cssClasses = ' class="'.join(' ', $cssClasses).'"';
                    }else{
                        $cssClasses = 'class="'.$cssClasses.'"';
                    }
                }else{
                    $cssClasses = '';
                }
                $eol = PHP_EOL;
            }
            
            if(is_array($message)){
                /**
                 * We create the message with implicit flush or other
                 */
                if($this->_implicitFlush === false){
                    $content = '';
                }
            }
        }
    }
}
