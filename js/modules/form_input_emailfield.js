class EmailField extends HTMLElement {
	constructor(properties) {
		super();

		// decide if the HTML was created beforehand (e.g. from server) or without attributed (e.g. document.createElement)
		if (properties != undefined) {
			this.properties = properties;
		} else if (this.getAttribute("data-properties") != null) {
			this.properties = JSON.parse(this.getAttribute("data-properties"));
		} else {
			throw new Error("Element created without properties.");
		}

		window.log(this.constructor.name, "Constructing an object to represent "+this.properties.distinguisher);
		
		this.appendChild((() => {
			let className = 'form-field';
			if (this.properties.value != null || this.properties.primary) {
				className += ' active';
			}
			return (() => {
				var $$a = document.createElement('div');
				$$a.setAttribute('class', 'input-field col s12');
				var $$b = this.element = document.createElement('input');
				$$b.id = this.properties.formDistinguisher + '-input-' + this.properties.distinguisher;
				$$b.name = this.properties.distinguisher;
				$$b.type = 'email';
				$$b.setAttribute('autocomplete', this.properties.autocomplete);
				$$b.setAttribute('pattern', this.properties.pattern);
				$$b.setAttribute('maxlength', this.properties.maxlength);
				$$b.value = this.properties.valueIsPrefilled ? this.properties.value : '';
				$$b.required = this.properties.required;
				$$b.autofocus = this.properties.primary;
				$$b.setAttribute('class', className);
				$$a.appendChild($$b);
				var $$c = this.label = new FormLabel(this.properties).children[0];
				$$a.appendChild($$c);
				var $$d = this.helperText = new FormLabelHelperSpan(this.properties).children[0];
				$$a.appendChild($$d);
				return $$a;
			})();
		})());

		this.addEventListener("input", this.verify.bind(this, true), {passive: true});
	}

	/**
	 * @param string errorMessage
	 * @param bool passive
	 */
	markError(errorMessage, passive) {
		window.log(this.properties.distinguisher, "Marking with error message "+errorMessage, true);

		this.element.classList.add("invalid", "marked-invalid");
		this.label.classList.add("active");
		this.helperText.setAttribute("data-error", errorMessage);

		if (!passive) {
			M.escapeToast(errorMessage);
			this.element.focus();
		}
	}

	/**
	 * @return string
	 */
	getValue() {
		return this.element.value;
	}

	/**
	 * The value to actually be sent to the server
	 * @return string
	 */
	getAggregationValue() {
		return this.getValue();
	}

	/**
	 * @param bool passive If the form is actively verifying the content (and thus toasts/etc should show) or
	 *	 false if verify is being called from input
	 * @return bool
	 */
	verify(passive=false) {
		let value = this.getValue();
		window.log(this.properties.distinguisher, "Verifying with value "+JSON.stringify(value));

		if (value.length) {
			if (value.length > this.properties.maxlength) {
				window.log(this.properties.distinguisher, "Value length "+value.length+" exceeds maximum length "+this.properties.maxlength, true);
				this.markError(this.properties.errors.aboveMaxLength, passive);
				return false;
			}
			if (!(new RegExp(this.properties.pattern)).test(value)) {
				window.log(this.properties.distinguisher, "Pattern "+this.properties.pattern+" does not match value", true);
				this.markError(this.properties.errors.patternMismatch, passive);
				return false;
			}
			if (/cat(l.st|alystapp.co)$/.test(value)) {
				window.log(this.properties.distinguisher, "Value ends in catl.st or catalystapp.co", true);
				this.markError(this.properties.errors.internalEmail, passive);
				return false;
			}
		} else {
			if (this.properties.required) {
				window.log(this.properties.distinguisher, "Required but empty value", true);
				this.markError(this.properties.errors.requiredButMissing, passive);
				return false;
			}
		}

		window.log(this.properties.distinguisher, "Verification successful");

		this.element.classList.remove("invalid", "marked-invalid");

		return true;
	}
}
