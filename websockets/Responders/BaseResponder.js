export class BaseResponder {
	respondTo = [];

	canRespond(event) {
		return !!(this.respondTo.find(regex => event.replace("client-", "").match(regex)));
	}
}
