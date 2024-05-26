var app_home = {
    home: {
        index: {
            init: function() {
                console.log('home.index.init');
                app.util.period();
                app.util.nprogressinit();
                $('.previous-surround, .next-surround').css('display', 'none');
                $('.button').not('.isDisabled').on('click', function() {
                    if($(this).attr("target") !== "_blank"){
                        app.util.fullscreenloading_start();
                        NProgress.start();
                    }
                });

                $(".button[target='_blank']").not('.isDisabled').on('click', function() {
                    //console.log('clicked to open new tab');
                    NProgress.done();
                });
                window.onload = function() {
                    NProgress.done();
                    app.util.fullscreenloading_end();
                };
                $(document).ready(function() {
                    $('#bd_month_button').on('click', function() {
                        $('#bd_month_button').addClass('active');
                        $('#bd_week_button').removeClass('active');
                        $('#bd_nextmonth_button').removeClass('active');

                        $("#dashboard_birthday_thismonth tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_thismonth tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                        $("#dashboard_birthday_this_week tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_this_week tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                        $("#dashboard_birthday_next_month tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_next_month tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                    });
                    $('#bd_week_button').on('click', function() {
                        $('#bd_week_button').addClass('active');
                        $('#bd_month_button').removeClass('active');
                        $('#bd_nextmonth_button').removeClass('active');
                        $("#dashboard_birthday_thismonth tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_thismonth tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                        $("#dashboard_birthday_this_week tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_this_week tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                        $("#dashboard_birthday_next_month tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_next_month tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                    });
                    $('#bd_nextmonth_button').on('click', function() {
                        $('#bd_nextmonth_button').addClass('active');
                        $('#bd_month_button').removeClass('active');
                        $('#bd_week_button').removeClass('active');
                        $("#dashboard_birthday_thismonth tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_thismonth tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                        $("#dashboard_birthday_this_week tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_this_week tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                        $("#dashboard_birthday_next_month tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_next_month tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                    });

                    $('#ne_month_button').on('click', function() {
                        $('#ne_month_button').addClass('active');
                        $('#ne_lastmonth_button').removeClass('active');
                        $("#dashboard_new_employees_this_month tbody tr").removeClass("expand-row");
                        $("#dashboard_new_employees_this_month tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                        $("#dashboard_new_employees_last_month tbody tr").removeClass("expand-row");
                        $("#dashboard_new_employees_last_month tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                    });
                    $('#ne_lastmonth_button').on('click', function() {
                        $('#ne_lastmonth_button').addClass('active');
                        $('#ne_month_button').removeClass('active');
                        $("#dashboard_new_employees_this_month tbody tr").removeClass("expand-row");
                        $("#dashboard_new_employees_this_month tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                        $("#dashboard_new_employees_last_month tbody tr").removeClass("expand-row");
                        $("#dashboard_new_employees_last_month tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                    });

                    $('#dashboard_birthday_thismonth tbody').on('click', 'tr.parent', function() {
                        $("#dashboard_birthday_thismonth tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_thismonth tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');

                        $(this).addClass('expand-row');
                        $(this).find('img').css('height', '120px').css('width', '120px').css('margin-left', '');
                    });
                    $('#dashboard_birthday_thismonth tbody').on('click', 'tr.parent.expand-row', function() {
                        $(this).removeClass('expand-row');
                        $(this).find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                    });
                    $('#dashboard_birthday_this_week tbody').on('click', 'tr.parent', function() {
                        $("#dashboard_birthday_this_week tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_this_week tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');

                        $(this).addClass('expand-row');
                        $(this).find('img').css('height', '120px').css('width', '120px').css('margin-left', '');
                    });
                    $('#dashboard_birthday_this_week tbody').on('click', 'tr.parent.expand-row', function() {
                        $(this).removeClass('expand-row');
                        $(this).find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                    });
                    $('#dashboard_birthday_next_month tbody').on('click', 'tr.parent', function() {
                        $("#dashboard_birthday_next_month tbody tr").removeClass("expand-row");
                        $("#dashboard_birthday_next_month tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');

                        $(this).addClass('expand-row');
                        $(this).find('img').css('height', '120px').css('width', '120px').css('margin-left', '');
                    });
                    $('#dashboard_birthday_next_month tbody').on('click', 'tr.parent.expand-row', function() {
                        $(this).removeClass('expand-row');
                        $(this).find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                    });

                    $('#dashboard_new_employees_this_month tbody').on('click', 'tr.parent', function() {
                        $("#dashboard_new_employees_this_month tbody tr").removeClass("expand-row");
                        $("#dashboard_new_employees_this_month tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');

                        $(this).addClass('expand-row');
                        $(this).find('img').css('height', '120px').css('width', '120px').css('margin-left', '');
                    });
                    $('#dashboard_new_employees_this_month tbody').on('click', 'tr.parent.expand-row', function() {
                        $(this).removeClass('expand-row');
                        $(this).find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                    });
                    $('#dashboard_new_employees_last_month tbody').on('click', 'tr.parent', function() {
                        $("#dashboard_new_employees_last_month tbody tr").removeClass("expand-row");
                        $("#dashboard_new_employees_last_month tbody tr").find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');

                        $(this).addClass('expand-row');
                        $(this).find('img').css('height', '120px').css('width', '120px').css('margin-left', '');
                    });
                    $('#dashboard_new_employees_last_month tbody').on('click', 'tr.parent.expand-row', function() {
                        $(this).removeClass('expand-row');
                        $(this).find('img').css('height', '50px').css('width', '50px').css('margin-left', '75px');
                    });


                    $('#dashboard_birthday_thismonth tbody').css('cursor', 'pointer');
                    $('#dashboard_birthday_this_week tbody').css('cursor', 'pointer');
                    $('#dashboard_birthday_next_month tbody').css('cursor', 'pointer');
                    $('#dashboard_new_employees_this_month tbody').css('cursor', 'pointer');
                    $('#dashboard_new_employees_last_month tbody').css('cursor', 'pointer');
                });
            },
        }
    },
};
