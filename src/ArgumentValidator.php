<?php
namespace ArgumentValidator;

class ArgumentValidatorException extends \Exception {};

/**
 * Simple class to handle arguments to a function.
 * A function sets this up like:
 * 	 $this->toHTMLConfig = new ArgConfig();
 *   $this->toHTMLConfig->addOpt('auth', 'obj:\SomeNamespace\Authorizer', false);
 *   $this->toHTMLConfig->addOpt('actions', NULL, false, false);
 *
 * Then I can call the function with
 * $class->$func(array('actions'=>true));
 * And this will handle it.  Sort of a shortcut to compensate for lack of
 * keyworded functions in php
 *
 * More complete example in the readme
**/
class ArgumentValidator {
	protected $opts = array();
	protected $validated = false;

	/**
	 * Place holder function
	 **/
	public function config() {
		
	}

	/**
	 * Add a keyworded parameter
	 *
	 * @param string $name Name (keyword) of parameter
	 * @param string type Type to validate the input against. Values can be 'int', 'array', 'bool', 'obj:<class>' 
	 * @param bool $req Weather parameter is required
	 * @param mixed $default Default value if parameter isn't provided
	 * @return void
	 **/
	function addOpt($name, $type = NULL, $req = false, $default = NULL) {
		$this->opts[$name] = array('type' => $type, 'default' => $default, 'req' => $req);
	}

	/**
	 * Validate an array of inputs against this validator
	 *
	 * @param array $config Array of inputs
	 * @param string &$verbose_str Referenced string that debugging messages will be appended to
	 * @return ArgumentValidator Returns self
	 * @throw ArgumentValidatorException
	 **/
	function validate($config, &$verbose_str = NULL) {
		$verbose_str = '';
		if (is_array($config)) {
			foreach ($this->opts as $c=>$v) {
				if (array_key_exists($c, $config)) {
					$val = $config[$c];
					if ($v['type'] == 'int' && !is_int($val)) throw new ConfigException("$c must be an int");
					if ($v['type'] == 'array' && !is_array($val)) throw new ConfigException("$c must be an array");
					if ($v['type'] == 'bool') {
						$this->opts[$c]['val'] = $val ? true:false;
						continue;
					}

					if (substr($v['type'], 0, 4) == 'obj:') {
						$class = substr($v['type'], 4);
						if (!is_null($val) && !($val instanceof $class)) {
							throw new ConfigException("$c must be a $class");
						}
					}
					$this->opts[$c]['val'] = $val;

					// Verbose
					$val_str=$val;
					if (is_array($val_str)) $val_str=join(', ', $val_str);
					if (is_object($val_str)) $val_str=get_class($val_str);
					$verbose_str.="[ArgConfig:validate]: Set $c=$val_str\n";
				} else {
					if ($v['req']) {
						// This ws required
						throw new \Exception("$c is required");
					} elseif ($v['default']) {
						$verbose_str.="[ArgConfig:validate]: Setting default $c=".$v['default']."\n";
						$this->opts[$c]['val'] = $v['default'];
					}
				}
			}
		} elseif ($config instanceof ArgConfig) {
			// Assume validated
		}
		$this->validated=true;
		return $this;
	}

	/**
	 * Retreive the configuration for a parameter
	 *
	 * @param string $param Parameter name
	 * @return array
	 **/
	function getConf($param) {
		if (array_key_exists($param, $this->opts) && array_key_exists('val', $this->opts[$param])) return $this->opts[$param]['val'];
		else return NULL;
	}

	/**
	 * Serialize to string.
	 * Output every parameter and it's type
	 *
	 * @return string
	 **/
	function __toString() {
		if (!$this->validated)
			return "Configurator unvalidataed, unaware of configure options.";

		$str = "Configuration:\n";
		foreach ($this->opts as $opt=>$arr) {
			if (!array_key_exists('val', $arr))
				$val_str = 'NULL';
			else {
				$val = $arr['val'];
				$type = $arr['type'];
				if ($type == 'bool') {
					$val_str = $val?'True':'False';
				} elseif (substr($type, 0, 4) == 'obj:') {
					$val_str = 'Obj:'.get_class($val);
				} // could probably do more here.. maybe later
				else {
					$val_str = $val;
				}
			}

			$str .= "- $opt = $val_str\n";
		}
		return $str;
	}
}
