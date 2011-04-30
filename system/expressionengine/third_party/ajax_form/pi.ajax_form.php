<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*  Secure Ajax Forms Addon for EE.
    Copyright (C) 2011  Matt Gilg

	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>. */
	
$plugin_info = array(
						'pi_name'			=> 'AJAX secure forms',
						'pi_version'		=> '1.0',
						'pi_author'			=> 'Matt Gilg',
						'pi_author_url'		=> 'http://mattgilg.com/',
						'pi_description'	=> 'Create secure AJAX-enabled forms.',
						'pi_usage'			=> Ajax_form::usage()
					);

/**
 * Ajax_actions Class
 *
 * @package         ExpressionEngine
 * @category        Plugin
 * @author          Matt Gilg
 * @copyright       Copyright (c) 2011, VisibleDevelopment, Inc.
 * @link            http://www.mattgilg.com/
 */


class Ajax_form {

	var $return_data = null;	
	
	/**
	 * Constructor
	 *
	 */
	function Ajax_form()
	{
		$this->EE =& get_instance();		
		$class = $this->EE->TMPL->fetch_param('class');
		$method = $this->EE->TMPL->fetch_param('method');
		$validated = $this->_validate_request($class,$method);		
		if ($validated){			
			$xid = $this->EE->functions->add_form_security_hash('{XID_HASH}');
			$url = $this->EE->functions->fetch_site_index(0);
			$action_id = $this->EE->functions->fetch_action_id($class, $method);
			$values = array(
		   			'xid'=>$xid,
		   			'post_url'=>$url,
		   			'action_id'=>$action_id,		   	
		   	);
		   	$values['last'] = $this->EE->functions->fetch_site_index(0);
		   	if( isset($this->EE->session->tracker['1']) )
				$values['last'] .= "/{$this->EE->session->tracker['1']}";		   	
		   	$variables[] = $values;
		   	$this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
		} else {
			$this->EE->output->show_user_error($type = 'general', "You must ".
				"specify both a 'class' and 'action' name when using this tag.",
				"Missing required information.");
		}
	}

	// --------------------------------------------------------------------
	function _validate_request($class,$method){
		if (!$class) $this->EE->output->show_user_error($type = 'general', "Please ".
										"specify a valid class when using {exp:ajax_form}.",
										"Missing {exp:ajax_form} form class.");
		if (!$method) $this->EE->output->show_user_error($type = 'general', "Please ".
										"specify a valid method when using {exp:ajax_form}.",
										"Missing {exp:ajax_form} action.");	
		
		$query = $this->EE->db->query("SELECT COUNT(*) AS count FROM {$this->EE->db->dbprefix}actions WHERE class='".$this->EE->db->escape_str($class)."' AND method='".$this->EE->db->escape_str($method)."'");
		if ($query->row('count')  == 0) $this->EE->output->show_user_error($type = 'general', "Could not ".
										"validate existance of {$class}->{$method}().  This action may not ".
										"be registered correctly with the EE core.",
										"Invalid Request.");
		return true;
	}	
	// --------------------------------------------------------------------
	
	/**
	 * Usage
	 *
	 * Plugin Usage
	 *
	 * @access	public
	 * @return	string
	 */
	function usage()
	{
		ob_start(); 
		?>
			This plugin is used to create secure AJAX forms.

			To use this plugin, wrap your form between these tag pairs:
			{exp:ajax_form class="desired_class" action="desired_action"}
			{/exp:ajax_form}

			Here is an example that allows you to create a secure member
			login form via AJAX:
			
			{exp:ajax_form class="Member" method="member_login"}
			
			<form id="loginForm" method='post' action='{post_url}'>
					<input type='hidden' name='XID' value='{xid}' />
					<input type='hidden' name='ACT' value='{action_id}' />
					<input type='text' name='username' />
					<input type='password' name='password' />
					<input type='button' name='Login' id='btnLogin'/>
			</form>
			
			<script>

			$("#btnLogin").click(function(){

			$.post('{post_url}',
			$("#loginForm").serializeArray(),
			function(data){
			// handle response here, parse html, or
			// json if using my output class mod.
			window.location = '{last}';			
			});/*end post*/
			
			});/*end click*/
			
			</script>
			{/exp:ajax_form}
			
			/*-------------------------------------
			          Tags
			--------------------------------------*/
			{xid} - The secure hash that is registered internally with the EE secure form system.
			{action_id} - The action id that corresponds with the requested method.
			{last} - The URL that was visited *prior* to the login page. (for redirection handling)
			{post_url} - The request's destination URL.          
			
		<?php
		$buffer = ob_get_contents();
	
		ob_end_clean(); 

		return $buffer;
	}

	// --------------------------------------------------------------------
	
}
// END CLASS

/* End of file pi.ajax_form.php */
/* Location: ./system/expressionengine/third_party/pi.ajax_form.php */