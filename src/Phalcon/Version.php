<?php 

namespace Phalcon {

	/**
	 * Phalcon\Version
	 *
	 * This class allows to get the installed version of the framework
	 */
	
	class Version {

		const VERSION_MAJOR = 0;

		const VERSION_MEDIUM = 1;

		const VERSION_MINOR = 2;

		const VERSION_SPECIAL = 3;

		const VERSION_SPECIAL_NUMBER = 4;

		public $a;

		/**
		 * Area where the version number is set. The format is as follows:
		 * ABBCCDE
		 *
		 * A - Major version
		 * B - Med version (two digits)
		 * C - Min version (two digits)
		 * D - Special release: 1 = Alpha, 2 = Beta, 3 = RC, 4 = Stable
		 * E - Special release version i.e. RC1, Beta2 etc.
		 */
		protected static function _getVersion(){ 
			return array(2,0,0,3,1);
		}


		/**
		 * Translates a number to a special release
		 *
		 * If Special release = 1 this function will return ALPHA
		 *
		 * @return string
		 */
		final protected static function _getSpecial($special){ 
			$suffix = "";
			switch($special){
				case 1:
					$suffix = 'ALPHA';
					break;
				case 2:
					$suffix = 'BETA';
					break;
				case 3:
					$suffix = 'RC';
					break;
			}
			return $suffix;
		}


		/**
		 * Returns the active version (string)
		 *
		 * <code>
		 * echo \Phalcon\Version::get();
		 * </code>
		 *
		 * @return string
		 */
		public static function get(){
			$version = self::_getVersion();
			$major = $version[self::VERSION_MAJOR];
			$medium = $version[self::VERSION_MEDIUM];
			$minor = $version[self::VERSION_MINOR];
			$special = $version[self::VERSION_SPECIAL];
			$specialNumber = $version[self::VERSION_SPECIAL_NUMBER];
			
			$result = $major . '.' . $medium . '.' . $minor . ' ';
			$suffix = self::_getSpecial($special);
			if($special != ""){
				$result .= $suffix . ' ' . $specialNumber;
			}
			
			return trim($result);
		}


		/**
		 * Returns the numeric active version
		 *
		 * <code>
		 * echo \Phalcon\Version::getId();
		 * </code>
		 *
		 * @return string
		 */
		public static function getId(){
			$version = self::_getVersion();
			$major = $version[self::VERSION_MAJOR];
			$medium = $version[self::VERSION_MEDIUM];
			$minor = $version[self::VERSION_MINOR];
			$special = $version[self::VERSION_SPECIAL];
			$specialNumber = $version[self::VERSION_SPECIAL_NUMBER];
			
			return $major . sprintf('%02s', $medium) . sprintf('%02s', $minor) . $special . $specialNumber;
		}


		/**
		 * Returns a specific part of the version. If the wrong parameter is passed
		 * it will return the full version
		 *
		 * <code>
		 * echo \Phalcon\Version::getPart(Phalcon\Version::VERSION_MAJOR);
		 * </code>
		 *
		 * @return string
		 */
		public static function getPart($part){ 
			$version = self::_getVersion();
			$result = '';
			
			switch($part){
				case self::VERSION_MAJOR:
				case self::VERSION_MEDIUM:
				case self::VERSION_MINOR:
				case self::VERSION_SPECIAL_NUMBER:
					$result = $version[$part];
					break;
				case self::VERSION_SPECIAL:
					$result = self::_getSpecial($version[self::VERSION_SPECIAL]);
					break;
				default:
					$result = self::get();
					break;
			}
			
			return $result;
		}

	}
}
