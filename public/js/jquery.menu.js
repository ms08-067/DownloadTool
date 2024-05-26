/**
 * A jQuery plugin boilerplate.
 * Author: Jonathan Nicol @f6design
 */
;
(function($) {
    // Change this to your plugin name. 
    var pluginName = 'PieMenu';

    /**
     * Plugin object constructor.
     * Implements the Revealing Module Pattern.
     */
    function Plugin(element, options) {
        // References to DOM and jQuery versions of element.
        var el = element;
        var $el = $(element);

        // Extend default options with those supplied by user.
        options = $.extend({}, $.fn[pluginName].defaults, options);

        /**
         * Initialize plugin.
         */
        function init() {
            // Add any initialization logic here...

            hook('onInit');
        }

        /**
         * Example Public Method
         */
        function fooPublic() {
            // Code goes here...

        }

        /**
         * Get/set a plugin option.
         * Get usage: $('#el').demoplugin('option', 'key');
         * Set usage: $('#el').demoplugin('option', 'key', value);
         */
        function option(key, val) {
            if (val) {
                options[key] = val;
            } else {
                return options[key];
            }
        }

        /**
         * Destroy plugin.
         * Usage: $('#el').demoplugin('destroy');
         */
        function destroy() {
            // Iterate over each matching element.
            $el.each(function() {
                var el = this;
                var $el = $(this);

                // Add code to restore the element to its original state...

                hook('onDestroy');
                // Remove Plugin instance from the element.
                $el.removeData('plugin_' + pluginName);
            });
        }

        /**
         * Callback hooks.
         * Usage: In the defaults object specify a callback function:
         * hookName: function() {}
         * Then somewhere in the plugin trigger the callback:
         * hook('hookName');
         */
        function hook(hookName) {
            if (options[hookName] !== undefined) {
                // Call the user defined function.
                // Scope is set to the jQuery element we are operating on.
                options[hookName].call(el);
            }
        }

        // Initialize the plugin instance.
        init();

        // Expose methods of Plugin we wish to be public.
        return {
            option: option,
            destroy: destroy,
            fooPublic: fooPublic
        };
    }

    /**
     * Plugin definition.
     */
    $.fn[pluginName] = function(options) {

        var angle,
            delay_time,
            ele_angle = [],
            x_pos = [],
            y_pos = [];

        var settings = $.extend({
            'starting_angle': '0',
            'angle_difference': '90',
            'radius': '200',
            'menu_element': this.children('.menu_option').children(),
            'menu_button': this.children('.menu_button'),
        }, options);

        angle = parseInt(settings.angle_difference) / (settings.menu_element.length - 1);
        delay_time = 1 / (settings.menu_element.length - 1);

        function setPosition(val) {
            $(settings.menu_element).each(function(i, ele) {
                $(ele).css({
                    'left': (val == 0) ? 0 : y_pos[i],
                    'top': (val == 0) ? 0 : -x_pos[i],
                });
            });
        }

        $(settings.menu_button).unbind('click', clickHandler); //remove event if exist

        var clickHandler = function() {
            if ($(this).parent().hasClass('active')) {
                setPosition(0);
                $(this).parent().removeClass('active');
                $(this).parent().addClass('inactive');
            } else {
                setPosition(1);
                $(this).parent().addClass('active');
                $(this).parent().removeClass('inactive');
            }
            //$(this).toggleClass("btn-rotate");
        };

        $(settings.menu_button).bind('click', clickHandler);

        $.fn[pluginName].changestate_expand = function() {
            setPosition(1);
        }

        $.fn[pluginName].changestate_contract = function(val) {
            $($('.menu_option').children()).each(function(i, ele) {
                $(ele).css({
                    'left': 0,
                    'top': 0
                });
            });
        }

        $.fn[pluginName].destroy = function() {
            //
            //
        }

        if (settings.menutype == 'attendance_statuses') {
            return settings.menu_element.each(function(i, ele) {
                ele_angle[i] = (parseInt(settings.starting_angle) + angle * (i)) * Math.PI / 180;

                if (i >= 0 && i < 20) {
                    x_pos[i] = 0;
                    y_pos[i] = (15 * settings.menu_element.length) * Math.abs(ele_angle[i]);
                } else if (i >= 20 && i < 39) {
                    x_pos[i] = 50;
                    y_pos[i] = (15 * settings.menu_element.length) * Math.abs(ele_angle[i - 19]);
                } else if (i >= 39 && i < 58) {
                    x_pos[i] = 100;
                    y_pos[i] = (15 * settings.menu_element.length) * Math.abs(ele_angle[i - 38]);
                } else if (i >= 58) {
                    x_pos[i] = 150;
                    y_pos[i] = (15 * settings.menu_element.length) * Math.abs(ele_angle[i - 57]);
                }
            });
        }

        if (settings.menutype == 'attendance_statuses_time_input') {
            return settings.menu_element.each(function(i, ele) {
                if (i == 0) {
                    x_pos[i] = -120;
                    y_pos[i] = -100;
                } else if (i == 1) {
                    x_pos[i] = -120;
                    y_pos[i] = 60;
                } else if (i == 2) {
                    x_pos[i] = -174;
                    y_pos[i] = -100;
                } else if (i == 3) {
                    x_pos[i] = -174;
                    y_pos[i] = 60;
                } else if (i == 4) {
                    x_pos[i] = -50;
                    y_pos[i] = -17;
                }
            });
        }

        if (settings.menutype == 'attendance_statuses_shift_checkbox') {
            return settings.menu_element.each(function(i, ele) {
                if (i == 0) {
                    x_pos[i] = 0;
                    y_pos[i] = 0;
                }
            });
        }

        if (settings.menutype == 'attendance_statuses_notes_textarea') {
            return settings.menu_element.each(function(i, ele) {
                if (i == 0) {
                    x_pos[i] = 0;
                    y_pos[i] = -300;
                }
            });
        }

        if (settings.menutype == 'attendance_statuses_reset_submit_functions') {
            return settings.menu_element.each(function(i, ele) {
                if (i == 0) {
                    x_pos[i] = 0;
                    y_pos[i] = 0;
                }
            });
        }


        // If the first parameter is a string, treat this as a call to
        // a public method.
        if (typeof arguments[0] === 'string') {
            var methodName = arguments[0];
            var args = Array.prototype.slice.call(arguments, 1);
            var returnVal;
            this.each(function() {
                // Check that the element has a plugin instance, and that
                // the requested public method exists.
                if ($.data(this, 'plugin_' + pluginName) && typeof $.data(this, 'plugin_' + pluginName)[methodName] === 'function') {
                    // Call the method of the Plugin instance, and Pass it
                    // the supplied arguments.
                    returnVal = $.data(this, 'plugin_' + pluginName)[methodName].apply(this, args);
                } else {
                    throw new Error('Method ' + methodName + ' does not exist on jQuery.' + pluginName);
                }
            });
            if (returnVal !== undefined) {
                // If the method returned a value, return the value.
                return returnVal;
            } else {
                // Otherwise, returning 'this' preserves chainability.
                return this;
            }
            // If the first parameter is an object (options), or was omitted,
            // instantiate a new instance of the plugin.
        } else if (typeof options === "object" || !options) {
            return this.each(function() {
                // Only allow the plugin to be instantiated once.
                if (!$.data(this, 'plugin_' + pluginName)) {
                    // Pass options to Plugin constructor, and store Plugin
                    // instance in the elements jQuery data object.
                    $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
                }
            });
        }
    };

    // Default plugin options.
    // Options can be overwritten when initializing plugin, by
    // passing an object literal, or after initialization:
    // $('#el').demoplugin('option', 'key', value);
    $.fn[pluginName].defaults = {
        onInit: function() {},
        onDestroy: function() {}
    };

})(jQuery);
