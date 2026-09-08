import { Controller } from "@hotwired/stimulus";
var _Class = class extends Controller {
	constructor(..._args) {
		super(..._args);
		this.inFlight = false;
	}
	connect() {
		if (this.hasButtonTarget) {
			this.buttonTarget.disabled = false;
			this.buttonTarget.textContent = this.maskValue;
		}
		if (this.hasErrorTarget) this.errorTarget.hidden = true;
	}
	async reveal() {
		if (this.inFlight) return;
		this.inFlight = true;
		this.dispatch("start");
		if (this.hasButtonTarget) {
			this.buttonTarget.disabled = true;
			this.buttonTarget.setAttribute("aria-busy", "true");
			this.buttonTarget.textContent = this.loadingLabelValue;
		}
		if (this.hasErrorTarget) this.errorTarget.hidden = true;
		try {
			const response = await fetch(this.urlValue, { headers: { Accept: "application/json" } });
			const data = await response.json().catch(() => ({}));
			if (response.status === 429) {
				this.showError(this.rateLimitedLabelValue);
				this.dispatch("rate-limited", { detail: data });
				return;
			}
			if (!response.ok) {
				this.showError(this.errorLabelValue);
				this.dispatch("error", { detail: data });
				return;
			}
			if (this.hasValueTarget) if (this.renderHtmlValue) this.valueTarget.innerHTML = String(data.html ?? data.value ?? "");
			else this.valueTarget.textContent = String(data.value ?? "");
			if (this.hasContentTarget) this.contentTarget.hidden = false;
			if (this.hasHideButtonTarget) this.hideButtonTarget.hidden = false;
			if (this.hasButtonTarget) {
				this.buttonTarget.disabled = false;
				this.buttonTarget.setAttribute("aria-busy", "false");
				this.buttonTarget.hidden = true;
			}
			this.dispatch("content-loaded", { detail: { value } });
		} catch (error) {
			this.showError(this.errorLabelValue);
			this.dispatch("error", { detail: { error: String(error) } });
		} finally {
			this.inFlight = false;
			if (this.hasButtonTarget && !this.buttonTarget.hidden) {
				this.buttonTarget.disabled = false;
				this.buttonTarget.setAttribute("aria-busy", "false");
				this.buttonTarget.textContent = this.maskValue;
			}
		}
	}
	hide() {
		if (this.hasValueTarget) this.valueTarget.replaceChildren();
		if (this.hasContentTarget) this.contentTarget.hidden = true;
		if (this.hasHideButtonTarget) this.hideButtonTarget.hidden = true;
		if (this.hasErrorTarget) this.errorTarget.hidden = true;
		if (this.hasButtonTarget) {
			this.buttonTarget.hidden = false;
			this.buttonTarget.disabled = false;
			this.buttonTarget.setAttribute("aria-busy", "false");
			this.buttonTarget.textContent = this.maskValue;
		}
		this.dispatch("hidden");
	}
	showError(message) {
		if (this.hasErrorTarget) {
			this.errorTarget.textContent = message;
			this.errorTarget.hidden = false;
		}
	}
};
_Class.targets = [
	"button",
	"content",
	"value",
	"hideButton",
	"error"
];
_Class.values = {
	url: String,
	mask: {
		type: String,
		default: "••••••"
	},
	renderHtml: {
		type: Boolean,
		default: false
	},
	revealLabel: {
		type: String,
		default: "Reveal"
	},
	hideLabel: {
		type: String,
		default: "Hide"
	},
	loadingLabel: {
		type: String,
		default: "Loading"
	},
	errorLabel: {
		type: String,
		default: "Unable to disclose."
	},
	rateLimitedLabel: {
		type: String,
		default: "Rate limit exceeded. Try again later."
	}
};
export { _Class as default };
