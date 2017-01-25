var ConsentHandler = Class.create();
ConsentHandler.prototype = {

    consentUrl: "/ops/device/",
    initialize: function (url) {
        this.consentUrl = url;
    },
    /**
     * Sends request to DeviceController to toggle the customers consent to the given state, will call the callback
     * with the current consent state
     *
     * @param targetState - state of customer consent given
     * @param callback - callback to call with consent result
     */
    toggleConsent: function (targetState, callback) {
        new Ajax.Request(this.consentUrl + 'toggleConsent', {
            method: 'post',
            parameters: {consent: targetState},
            onSuccess: function (transport) {
                var data = transport.responseText.evalJSON();
                callback(data.consent);
            },
            onFailure: function () {
                callback(null);
            }
        })
    },

    /**
     * Requests current consent state and forwards it to the given callback
     *
     * @param callback - callback to call with consent result
     */
    getConsent: function (callback) {
        new Ajax.Request(this.consentUrl + 'consent', {
            method: 'post',
            onSuccess: function (transport) {
                var data = transport.responseText.evalJSON();
                callback(data.consent);
            },
            onFailure: function () {
                callback(null);
            }
        })
    }

};

