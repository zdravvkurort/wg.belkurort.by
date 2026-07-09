var doubleGet = function() {
    const self = this;
    self.system = self.system();

    this.callbacks = {
        render: function() {

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