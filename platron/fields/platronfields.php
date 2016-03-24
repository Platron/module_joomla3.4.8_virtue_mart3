<?php
/**
 * @version $Id: getpaybox.php 6369 2012-08-22 14:33:46Z alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('JPATH_BASE') or die();

jimport('joomla.form.formfield');



class JFormFieldPlatronfields extends JFormField {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $type = 'getPaybox';

	function getInput() {
		$cid = vRequest::getvar('cid', NULL, 'array');
		if (is_Array($cid)) {
			$virtuemart_paymentmethod_id = $cid[0];
		} else {
			$virtuemart_paymentmethod_id = $cid;
		}
		
		$query = "SELECT * FROM `#__virtuemart_paymentmethods` WHERE  virtuemart_paymentmethod_id = '" . $virtuemart_paymentmethod_id . "'";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$payment_params = $db->loadObject()->payment_params;
		$paramsByKey = [];
		if(empty($payment_params)){
			$paramsByKey= [	'testing_mode'=>'TEST',
							'platron_id'=>'',
							'platron_secret'=>'',
							'platron_life_time'=>24,
							'status_success'=>'C',
							'status_canceled'=>'X',
							'status_pending'=>'P'];
		}
		else {
			$params = explode("|", $payment_params);
			foreach ($params as $value) {
				$keyAndValue = explode("=",$value);
				//Добавление параметров в виде ключ - значение 
				if(!empty($keyAndValue[0])){
					if(!empty($keyAndValue[1])){
						$paramsByKey[$keyAndValue[0]] = str_replace('"', '', $keyAndValue[1]);
					}
					else{
						$paramsByKey[$keyAndValue[0]] = '';
					}
				}
			}
		}


		$testChecked = 'checked="checked"';
		$productionChecked = '';
		if($paramsByKey['testing_mode'] == 'PRODUCTION'){
			$testChecked = '';
			$productionChecked = 'checked="checked"';
		}
		if($this->fieldname =='testing_mode'){
			$html = '<fieldset id="params_testing_mode" class="radio" >'
				. '<input type="radio" id="params_testing_mode0" name="params[testing_mode]" '.$testChecked.' value="TEST" />'
				. '<label for="params_testing_mode0" >Да</label>'
				. '<input type="radio" id="params_testing_mode1" name="params[testing_mode]" '.$productionChecked.' value="PRODUCTION" />'
				. '<label for="params_testing_mode1">Нет</label>'
				. '</fieldset>';
		}
		if($this->fieldname == 'platron_id'){
			$html = '<input type="text" name="params[platron_id]" id="params_platron_id" value="'.$paramsByKey['platron_id'].'" size="50">';
		}
		if($this->fieldname == 'platron_secret'){
			$html = '<input type="text" name="params[platron_secret]" id="params_platron_secret" value="'.$paramsByKey['platron_secret'].'" size="50">';
		}
		if($this->fieldname == 'platron_life_time'){
			$html = '<input type="text" name="params[platron_life_time]" id="params_platron_life_time" value="'.$paramsByKey['platron_life_time'].'" size="50">';
		}
		
		if($this->fieldname == 'status_success' || $this->fieldname == 'status_canceled' || $this->fieldname == 'status_pending'){
			$statuses = [
				'P'=>'В ожидании',
				'U'=>'Подтвержден покупателем',
				'C'=>'Подтвержден',
				'X'=>'Отменен',
				'R'=>'Возвращен',
				'S'=>'Доставлен',
				'F'=>'Completed',
				'D'=>'Denied'
			];
					
			$html = '<select id="params_'.$this->fieldname.'" name="params['.$this->fieldname.']">';
			foreach ($statuses as $key => $value) {
				$html .= '<option value="'.$key.'"';
				if(!empty($paramsByKey[$this->fieldname] && $key == $paramsByKey[$this->fieldname])){
					$html .= ' selected="selected"';
				}
				$html .= '>'.$value.'</option>';
			}
			$html .= '</select>';
		}
		
		
		return $html;
	}
}