var RemoteGet = function() {
    var self = this;
    var widgetname = 'Удалённый виджет';
	self.perenosPoley = function() {
		$(".card-holder__fields .linked-form__field__label").css({"white-space":"normal", "overflow":"hidden", "height":"fit-content"});
	},	
        this.callbacks = {
            render: function() {	
				self.perenosPoley();
                return true;
            },
            init: function() {		
                return true;
            },
            bind_actions: function() {
                return true;
            },
            settings: function() {
                return true;
            },
            onSave: function() {
                alert('Сохранено!');
                return true;
            },
            destroy: function() {

            },

        };
    return this;
};