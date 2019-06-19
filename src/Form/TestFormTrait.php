<?php

namespace Catalyst\Form;

use \Catalyst\API\ErrorCodes;
use \Catalyst\Form\Field\{AutocompleteValues,ConfirmField,EmailField,TextField,NumberField,HiddenInputField,MarkdownField,PasswordField,SelectField,CaptchaField};
use \Catalyst\Form\Form;

trait TestFormTrait {
	/**
	 * This form will test many inputs for the browser
	 */
	public static function getTestForm() : Form {
		$form = new Form();

		$form->setDistinguisher(self::getDistinguisherFromFunctionName(__FUNCTION__)); // get-dash-case from camelCase
		$form->setMethod(Form::POST);
		$form->setEndpoint("internal/test/");
		$form->setButtonText("TEST");
		$form->setPrimary(true);

		$textField = new TextField();
		$textField->setDistinguisher("text-field-test-one");
		$textField->setLabel("Required test field with pattern ^([a-g][a-g]?[a-g]?|[h-j]+)$, max length 10, disallowed 'abc','def'");
		$textField->setMaxLength(10);
		$textField->setDisallowed(["abc","def"]);
		$textField->setRequired(true);
		$textField->setPattern('^([a-g][a-g]?[a-g]?|[h-j]+)$');
		$form->addField($textField);

		$textField2 = new TextField();
		$textField2->setDistinguisher("text-field-test-two");
		$textField2->setLabel("Non-required test field with pattern ^([a-g][a-g]?[a-g]?|[h-j]+)$, max length 10, disallowed 'abc','def'");
		$textField2->setMaxLength(10);
		$textField2->setDisallowed(["abc","def"]);
		$textField2->setRequired(false);
		$textField2->setPattern('^([a-g][a-g]?[a-g]?|[h-j]+)$');
		$form->addField($textField2);

		$emailField = new EmailField();
		$emailField->setDistinguisher("test-email-no-req");
		$emailField->setLabel("Email but it's not required");
		$emailField->setRequired(false);
		$emailField->addError(99809, ErrorCodes::ERR_99809);
		$emailField->setMissingErrorCode(99809);
		$emailField->addError(99810, ErrorCodes::ERR_99810);
		$emailField->setInvalidErrorCode(99810);
		$form->addField($emailField);

		$emailField2 = new EmailField();
		$emailField2->setDistinguisher("test-email-req");
		$emailField2->setLabel("Email but it's required");
		$emailField2->setRequired(true);
		$emailField2->addError(99812, ErrorCodes::ERR_99812);
		$emailField2->setMissingErrorCode(99812);
		$emailField2->addError(99813, ErrorCodes::ERR_99813);
		$emailField2->setInvalidErrorCode(99813);
		$form->addField($emailField2);

		$hiddenInput = new HiddenInputField();
		$hiddenInput->setDistinguisher("test-hidden-field");
		$hiddenInput->setHiddenInputId("hidden-field-test");
		$hiddenInput->setRequired(true);
		$hiddenInput->addError(99815, ErrorCodes::ERR_99815);
		$hiddenInput->setMissingErrorCode(99815);
		$form->addField($hiddenInput);

		$selectField = new SelectField();
		$selectField->setDistinguisher("test-select-field-req");
		$selectField->setLabel("Test select, required");
		$selectField->setOptions([
			"key1" => "Fancy value 1",
			"key2" => "Fancy value 2",
			"key3" => "Fancy value 3",
		]);
		$selectField->setRequired(true);
		$selectField->setAutocompleteAttribute(AutocompleteValues::ON);
		$selectField->addError(99818, ErrorCodes::ERR_99818);
		$selectField->setMissingErrorCode(99818);
		$selectField->addError(99819, ErrorCodes::ERR_99819);
		$selectField->setInvalidErrorCode(99819);
		$form->addField($selectField);

		$selectField2 = new SelectField();
		$selectField2->setDistinguisher("test-select-field-no-req");
		$selectField2->setLabel("Test select, not required");
		$selectField2->setOptions([
			"key1" => "Fancy value 1",
			"key2" => "Fancy value 2",
			"key3" => "Fancy value 3",
		]);
		$selectField2->setRequired(false);
		$selectField2->setAutocompleteAttribute(AutocompleteValues::ON);
		$selectField2->addError(99821, ErrorCodes::ERR_99821);
		$selectField2->setMissingErrorCode(99821);
		$selectField2->addError(99822, ErrorCodes::ERR_99822);
		$selectField2->setInvalidErrorCode(99822);
		$form->addField($selectField2);

		$markdownField = new MarkdownField();
		$markdownField->setDistinguisher("test-markdown-field-req");
		$markdownField->setLabel("Test markdown field, required");
		$markdownField->setRequired(true);
		$markdownField->setAutocompleteAttribute(AutocompleteValues::OFF);
		$markdownField->addError(99824, ErrorCodes::ERR_99824);
		$markdownField->setMissingErrorCode(99824);
		$markdownField->addError(99825, ErrorCodes::ERR_99825);
		$markdownField->setInvalidErrorCode(99825);
		$form->addField($markdownField);

		$numberField = new NumberField();
		$numberField->setDistinguisher("test-number-field-req");
		$numberField->setLabel("Test number field, required, min 10, max 20, precision 2");
		$numberField->setRequired(true);
		$numberField->setMin(10);
		$numberField->setMax(20);
		$numberField->setPrecision(2);
		$numberField->setAutocompleteAttribute(AutocompleteValues::OFF);
		$numberField->addError(99827, ErrorCodes::ERR_99827);
		$numberField->setMissingErrorCode(99827);
		$numberField->addError(99828, ErrorCodes::ERR_99828);
		$numberField->setInvalidErrorCode(99828);
		$form->addField($numberField);

		$numberField = new ConfirmField();
		$numberField->setDistinguisher("test-number-field-req");
		$numberField->setPrompt("Are you sure");
		$numberField->addError(99830, ErrorCodes::ERR_99830);
		$numberField->setMissingErrorCode(99830);
		$numberField->addError(99831, ErrorCodes::ERR_99831);
		$numberField->setInvalidErrorCode(99831);
		$form->addField($numberField);

		$captchaField = new CaptchaField();
		$captchaField->setDistinguisher("test-captcha");
		$captchaField->setRequired(true);
		$captchaField->setSiteKey(CaptchaField::DEBUG_CAPTCHA_KEY);
		$captchaField->setSecretKey(CaptchaField::DEBUG_CAPTCHA_SECRET);
		$captchaField->addError(99806, ErrorCodes::ERR_99806);
		$captchaField->setMissingErrorCode(99806);
		$captchaField->addError(99807, ErrorCodes::ERR_99807);
		$captchaField->setInvalidErrorCode(99807);
		$form->addField($captchaField);

		return $form;
	}
}
