<?php
class MY_Form_validation extends CI_Form_validation
{
	public function __construct()
	{
		parent::__construct();		
	}
	/**
	 * Allows using CI Models, Libraries and Helpers externally for validation rules
	 * 
	 * Use: external_callback[type.name.function] or external_callback[type.name.function[params]]
	 * Type = library, model, or helper!
	 * Name = name of library/model/helper
	 * Function = function/method of rule
	 * Params = (optional) parameters set in square brackets to pass to your callback validation function
	 * 
	 * Note 1: When defining external callbacks, the error message must be "external_callback" and not the name of the individual function.
	 * Note 2: Do not use the pipe (|) or dots(.) in extra data transmitted within brackets ([]) in your own callback functions.
	 *         CI uses pipes to separate rules and this external function uses dots, so using either will break rules.
	 * 
	 * @param String $postdata Same as param1 sent to callback functions
	 * @param String $extra    Same as param2 sent to callback functions (within square brackets)
	 */
	
	public function external_callback($postdata, $extra)
	{   
		//Separate out the parts we need from $extra
		list($external_type, $name, $function) = explode('.', $extra);
		$external_type = strtolower($external_type);
		
		$CI =& get_instance();
		
		//Try loading the resource!
		$CI->load->$external_type($name);
		
		// Strip the parameter (if exists) from the rule
		// Rules can contain a parameter: max_length[5]
		$param = FALSE;
		if (preg_match("/(.*?)\[(.*)\]/", $function, $match))
		{
			$function = $match[1];
			$param	= $match[2];
		}
		
		//If library or model, get the method
		$result = FALSE;
		
		if(($external_type == 'library' || $external_type == 'model') && method_exists($CI->$name, $function))
		{
			$result = ( ! empty($param)) ? $CI->$name->$function($postdata, $param) : $CI->$name->$function($postdata);
		}
		//it was a helper, just use function instead...
		else if(function_exists($function))
		{
			$result = ( ! empty($param)) ? $function($postdata, $param) : $function($postdata);
		}
		
		//return the final result of the external callback back to validation
		return $result;
	}
}