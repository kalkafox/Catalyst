<?php

namespace Catalyst\Form\Field;

use \Catalyst\Form\Form;
use \Catalyst\API\TransitEncryption;

/**
 * Represents a text field
 */
class PasswordField extends AbstractField {
	use LabelTrait, SupportsAutocompleteAttributeTrait;
	/**
	 * Minimum length for the password
	 * @var int
	 */
	protected $minLength = 8;

	/**
	 * Get the password's minimum length
	 * 
	 * @return int Minimum password length
	 */
	public function getMinLength() : int {
		return $this->minLength;
	}

	/**
	 * Set the password's minimum length
	 * 
	 * @param int $minLength New minimum password length
	 */
	public function setMinLength(int $minLength) : void {
		$this->minLength = $minLength;
	}

	/**
	 * Return the field's HTML input
	 * 
	 * @return string The HTML to display
	 */
	public function getHtml() : string {
		$str = '';
		$str .= '<div class="input-field col s12">';

		$inputClasses = ["form-field"];
		$str .= '<input';
		$str .= ' type="password"';
		$str .= ' autocomplete="'.htmlspecialchars($this->getAutocompleteAttribute()).'"';
		$str .= ' data-field-type="'.htmlspecialchars(self::class).'"';
		$str .= ' minlength="'.$this->getMinLength().'"';
		$str .= ' id="'.htmlspecialchars($this->getId()).'"';

		if ($this->isRequired()) {
			$str .= ' required="required"';
		}

		if ($this->isPrimary()) {
			$str .= ' autofocus="autofocus"';
			$inputClasses[] = "active";
		}
		
		$str .= ' class="'.htmlspecialchars(implode(" ", $inputClasses)).'"';
		$str .= '>';
		
		$str .= $this->getLabelHtml();

		$str .= '</div>';
		return $str;
	}

	/**
	 * Full JS validation code, including if statement and all
	 * 
	 * @return string The JS to validate the field
	 */
	public function getJsValidator() : string {
		$str = '';
		if ($this->isRequired()) {
			$str .= 'if (';
			$str .= '$('.json_encode("#".$this->getId()).').val().length === 0';
			$str .= ') {';
			$str .= 'window.log('.json_encode(basename(__CLASS__)).', '.json_encode($this->getId()." - field is required, but empty").', true);';
			$str .= 'markInputInvalid('.json_encode('#'.$this->getId()).', '.json_encode($this->getErrorMessage($this->getMissingErrorCode())).');';
			$str .= Form::CANCEL_SUBMISSION_JS;
			$str .= '}';
		}
		$str .= 'if (';
		$str .= '$('.json_encode("#".$this->getId()).').val().length !== 0';
		$str .= ') {';

		$str .= 'if (';
		$str .= '$('.json_encode("#".$this->getId()).').val().length < '.json_encode($this->getMinLength());
		$str .= ') {';
		$str .= 'window.log('.json_encode(basename(__CLASS__)).', '.json_encode($this->getId()." - field's length is below minimum (".$this->getMinLength().")").', true);';
		$str .= 'markInputInvalid('.json_encode('#'.$this->getId()).', '.json_encode($this->getErrorMessage($this->getInvalidErrorCode())).');';
		$str .= Form::CANCEL_SUBMISSION_JS;
		$str .= '}';
		
		$str .= '}';

		return $str;
	}

	/**
	 * Return JS code to store the field's value in $formDataName
	 * 
	 * @param string $formDataName The name of the FormData variable
	 * @return string Code to use to store field in $formDataName
	 */
	public function getJsAggregator(string $formDataName) : string {
		return $formDataName.'.append('.json_encode($this->getDistinguisher()).', encryptString(btoa($('.json_encode("#".$this->getId()).').val())));'; // yes, this is half-assing it.  it works, and is better than 99% of sites
	}

	/**
	 * Check the field's forms on the servers side
	 * 
	 * @param array $requestArr Array to find the form data in
	 */
	public function checkServerSide(?array &$requestArr=null) : void {
		if (is_null($requestArr)) {
			if ($this->getForm()->getMethod() == Form::POST) {
				$requestArr = &$_POST;
			} else {
				$requestArr = &$_GET;
			}
		}
		if (!array_key_exists($this->getDistinguisher(), $requestArr)) {
			$this->throwMissingError();
		}
		$requestArr[$this->getDistinguisher()] = TransitEncryption::decryptAes($requestArr[$this->getDistinguisher()]);
		if (empty($requestArr[$this->getDistinguisher()])) {
			if ($this->isRequired()) {
				$this->throwMissingError();
			} else {
				return;
			}
		}
		if (strlen($requestArr[$this->getDistinguisher()]) < $this->getMinLength()) {
			$this->throwInvalidError();
		}
	}

	/**
	 * Get the default autocomplete attribute value
	 *
	 * @return string
	 */
	public static function getDefaultAutocompleteAttribute() : string {
		return AutocompleteValues::CURRENT_PASSWORD;
	}
}
