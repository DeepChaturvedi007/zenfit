(function ($) {
    $.fn.openChat = function() {
        var $element = this;

        if ($element.children('.chat-widget-wrap').children('.chat-widget-header').length > 0) {
            $element.children('.chat-widget-wrap').children('.chat-widget-header').click();
        }
    };
})(jQuery);