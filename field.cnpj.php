<?php defined('BASEPATH') or exit('No direct script access allowed');

class Field_cnpj
{
	public $field_type_slug = "cnpj";	
	public $db_col_type = "bigint(14)";
	public $version = "1.0.0";
	public $author = array(
							'name' => 'Sandro Boçon',
							'github' => 'https://github.com/sandrobocon'
					);
	
	/**
 	 * Output form input
	 *
	 * @access public
	 * @param array
	 * @return string
	 */
	public function form_output($data)
	{
		$options['name']   	= $data['form_slug'];
		$options['id']   	= $data['form_slug'];
		
		if ( ! empty($data['value'])) {
			// Format '00.000.000/0000-00'
			$cnpj = preg_replace( '/[^0-9]/', '', $data['value'] );
			$cnpj = str_pad($cnpj, 14, "0", STR_PAD_LEFT);
			$cnpj = substr($cnpj, 0,2).'.'.substr($cnpj, 2,3).'.'.substr($cnpj, 5,3).'/'.substr($cnpj, 8,4).'-'.substr($cnpj, 12,2);
			$options['value']  	= $cnpj;
		} else {
			$options['value']  	= null;
		}
	
		return form_input($options);
	}
	
	public function pre_save($input, $field, $stream, $row_id)
	{
		if (empty($input)) {
			$input = null;
		} else {
			$input = preg_replace( '/[^0-9]/', '', $input );
		}

		return $input;
	}

	public function pre_output($input, $data)
	{
		// Format '00.000.000/0000-00'
		if ( ! empty($input)) {
			$cnpj = str_pad($input, 14, "0", STR_PAD_LEFT);
			$input = substr($cnpj, 0,2).'.'.substr($cnpj, 2,3).'.'.substr($cnpj, 5,3).'/'.substr($cnpj, 8,4).'-'.substr($cnpj, 12,2);
		}
		return $input;
	}
	
	public function validate( $value, $mode, $field )
	{			
		// Remove everything that is not number 
		$cnpj = preg_replace( '/[^0-9]/', '', $value );
			
		if(strlen($cnpj)> 14) 
			return $this->CI->lang->line('streams:cnpj.too_long');
		
		if( ! is_numeric($cnpj))
			return $this->CI->lang->line('streams:cnpj.invalid_format');
		
		// Turn it to string with exactly 14 digits (insert 0's on left)
		$cnpj = str_pad($cnpj, 14, "0", STR_PAD_LEFT);

		// Validações CNPJ
		$calcular = 0;
		$calcularDois = 0;
	
		for ($i = 0, $x = 5; $i <= 11; $i++, $x--) {
			$x = ($x < 2) ? 9 : $x;
			$number = substr($cnpj, $i, 1);
			$calcular += $number * $x;
		}
	
		for ($i = 0, $x = 6; $i <= 12; $i++, $x--) {
			$x = ($x < 2) ? 9 : $x;
			$numberDois = substr($cnpj, $i, 1);
			$calcularDois += $numberDois * $x;
		}
	
		$digitoUm = (($calcular % 11) < 2) ? 0 : 11 - ($calcular % 11);
		$digitoDois = (($calcularDois % 11) < 2) ? 0 : 11 - ($calcularDois % 11);
 	
		if ($digitoUm <> substr($cnpj, 12, 1) || $digitoDois <> substr($cnpj, 13, 1)) {
			return $this->CI->lang->line('streams:cnpj.invalid');
		}
		return true;
	}
}
