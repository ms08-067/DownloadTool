var app_permissions = {
    route_permission: { //ROUTE PERMISSION
        profile: {
            init: function() {
                $(window).on('load', function() {
                    app.util.period();
                    app.util.nprogressinit();
                    $('.previous-surround, .next-surround').css('display', 'none');
                    NProgress.configure({ parent: '#progress-bar-parent', showSpinner: false });
                    app_permissions.route_permission.profile.init_next();
                    app.util.fixedheaderviewporthandler();
                });
            },
            init_next: function() {
                var first_doc_refferer = document.referrer;
                var data = [];
                //what this part is doing is checking if more than one browser tab of the same route is trying to be opened at the same time... 
                var browser_tab_already_open_load = JSON.parse(localStorage.getItem('Br24_' + app.env() + '_routepermissionsinfo_oorptoape'));
                if (browser_tab_already_open_load !== null) {
                    //if the key exists it means the tab is open still.
                    if (first_doc_refferer === undefined || first_doc_refferer === null || first_doc_refferer == '') {
                        $('.loader').append('<span style="position: absolute; top: 45%; left: 50%; transform: translateX(-50%) translateY(-50%); font-size: 22px; color: red; text-align:center">Another Route Matrix is open already.<br>for security reasons only one instance is allowed<br><a style="font-size:10px" href="/home">Back Home</a></span>');
                        NProgress.done();
                        $('.navbar-nav').remove();
                        $('.ibox-content').remove();
                        $('.loader').css('background', 'rgb(249,249,249, 0.5');
                        $('.nav-tabs').remove();
                        $('.ibox-title').remove();
                        $('.tab-content').remove();
                        //$('.main').remove();
                    } else {
                        //if (first_doc_refferer != null || first_doc_refferer != undefined) {
                        $('.loader').append('<span style="position: absolute; top: 45%; left: 50%; transform: translateX(-50%) translateY(-50%); font-size: 22px; color: red; text-align:center">Another Route Matrix is open already.<br>for security reasons only one instance is allowed<br><a style="font-size:10px" href="/home">Back Home</a></span>');
                        NProgress.done();
                        $('.loader').css('background', 'rgb(249,249,249, 0.5');
                        $('.ibox-content').remove();
                        $('.nav-tabs').remove();
                        $('.tab-content').remove();
                        $('.navbar-nav').remove();
                        $('.ibox-title').remove();
                        //$('.main').remove();
                        // } else {
                        //     window.close();
                        // }
                    }
                } else {
                    //the variable is not set so you can set it here.. let them carry on and if the user tries to open another tab for the same employee they will inevitably be blocked
                    //unless they copy and paste the URL to a new tab..
                    data['oorptoape'] = { "routepermissions_browsertabstatus": "open", "timestamp": new Date().getTime() };
                    localStorage.setItem('Br24_' + app.env() + '_routepermissionsinfo_oorptoape', JSON.stringify(data['oorptoape']));

                    app_permissions.route_permission.profile.get_rolepositionInfo_tab();
                    //app_permissions.route_permission.profile.get_permissionsInfo_tab();

                    var onlycallonce = 1;
                    $(document).ajaxStop(function() {
                        if (onlycallonce == 1) {
                            //console.log("All AJAX requests completed");
                            app_permissions.route_permission.profile.table();
                            app_permissions.route_permission.profile.add_edit_employeerolepermission_roleposition_permissions_colorbox();
                            onlycallonce = 2;
                        }
                    });

                    //the key to indicate that a tab with that employee detail is already open; will be removed from the localstorage
                    //be careful this also happens on form submit so should not remove the keys that allow this section to work
                    window.onbeforeunload = function() {};
                    window.onunload = function() {
                        localStorage.removeItem('Br24_' + app.env() + '_routepermissionsinfo_oorptoape');
                    };
                    //how to detect when the only tab for the employee that is open is closed?
                    //at that point we want to clear the keys for the other items.
                }
            },
            table: function() {
                $(document).ready(function() {

                    var tabular_load = JSON.parse(localStorage.getItem('Br24_' + app.env() + '_routepermissionsinfo_tab'));
                    if (tabular_load !== null) {
                        if (tabular_load['roleposition_tab'] !== false) {
                            $('#anchor_rolepositionInfo').closest("li").addClass('active');
                            $('#rolepositionInfo').addClass('active');
                            $('#anchor_rolepositionInfo').on("click", function(e) {
                                e.preventDefault();
                            });
                        }
                        if (tabular_load['permissions_tab'] !== false) {
                            $('#anchor_documentationInfo').closest("li").addClass('active');
                            $('#permissionsInfo').addClass('active');
                            $('#anchor_documentationInfo').on("click", function(e) {
                                e.preventDefault();
                            });
                        }
                    } else {
                        $('#anchor_rolepositionInfo').closest("li").addClass('active');
                        $('#rolepositionInfo').addClass('active');
                        $('#anchor_rolepositionInfo').on("click", function(e) {
                            e.preventDefault();
                        });
                        var roleposition_tab = true;
                        var permissions_tab = false;
                        var data = [];
                        data['tabs'] = {
                            "roleposition_tab": roleposition_tab,
                            "permissions_tab": permissions_tab
                        };
                        localStorage.setItem('Br24_' + app.env() + '_routepermissionsinfo_tab', JSON.stringify(data['tabs']));
                        $('.alert-dismissable').css('display', "none");
                    }

                    //variable to remember which tab the user has clicked on to be used to style the tab and do custom actions.
                    var anchorclicked = '';
                    $("a").click(function(event) { anchorclicked = event.target.id; });

                    $('#anchor_rolepositionInfo').click(function() {
                        var anchors_selector = $('#anchor_employeerolepermissionInfo, #anchor_rolepositionInfo, #anchor_permissionsInfo');
                        var tabs_selector = $('#employeerolepermissionInfo, #rolepositionInfo, #permissionsInfo');

                        //console.log('justchangetabs contracts_');
                        //don't have changes nor errors so dont need their confirmation
                        var tabpane_id = anchorclicked.replace('anchor_', '');

                        //unset this tab active to none because you don't know where it is coming from.
                        anchors_selector.parent().removeClass('active');
                        anchors_selector.removeClass('active');
                        tabs_selector.removeClass('active');

                        $('.has-error').removeClass('has-error');
                        $('.help-block').css('display', 'none');

                        //make the clicked tab and the respective pane active
                        $('#' + anchorclicked).parent().addClass('active');
                        $('#' + anchorclicked).addClass('active');
                        $('#' + tabpane_id).addClass('active');

                        $('#' + anchorclicked).on("click", function(e) {
                            e.preventDefault();
                        });

                        //also set the localstorage
                        var roleposition_tab = true;
                        var permissions_tab = false;
                        var data = [];
                        data['tabs'] = {
                            "roleposition_tab": roleposition_tab,
                            "permissions_tab": permissions_tab
                        };
                        localStorage.setItem('Br24_' + app.env() + '_routepermissionsinfo_tab', JSON.stringify(data['tabs']));

                        window.rolepositiontable.fixedHeader.enable();
                        window.rolepositiontable.fixedHeader.adjust();
                        window.permissionstable.fixedHeader.disable();
                    });

                    $('#anchor_permissionsInfo').click(function() {
                        var anchors_selector = $('#anchor_employeerolepermissionInfo, #anchor_rolepositionInfo, #anchor_permissionsInfo');
                        var tabs_selector = $('#employeerolepermissionInfo, #rolepositionInfo, #permissionsInfo');

                        //console.log('justchangetabs contracts_');
                        //don't have changes nor errors so dont need their confirmation
                        var tabpane_id = anchorclicked.replace('anchor_', '');

                        //unset this tab active to none because you don't know where it is coming from.
                        anchors_selector.parent().removeClass('active');
                        anchors_selector.removeClass('active');
                        tabs_selector.removeClass('active');

                        $('.has-error').removeClass('has-error');
                        $('.help-block').css('display', 'none');

                        //make the clicked tab and the respective pane active
                        $('#' + anchorclicked).parent().addClass('active');
                        $('#' + anchorclicked).addClass('active');
                        $('#' + tabpane_id).addClass('active');

                        $('#' + anchorclicked).on("click", function(e) {
                            e.preventDefault();
                        });

                        //also set the localstorage
                        var roleposition_tab = true;
                        var permissions_tab = false;
                        var data = [];
                        data['tabs'] = {
                            "roleposition_tab": roleposition_tab,
                            "permissions_tab": permissions_tab
                        };
                        localStorage.setItem('Br24_' + app.env() + '_routepermissionsinfo_tab', JSON.stringify(data['tabs']));

                        window.rolepositiontable.fixedHeader.disable();
                        window.permissionstable.fixedHeader.enable();
                        window.permissionstable.fixedHeader.adjust();
                    });

                    $('[data-toggle="popover"]').popover({
                        delay: {
                            show: "500",
                            hide: "100"
                        }
                    });
                });

                app.util.nprogressdone();
                app.util.fullscreenloading_end();
            },
            add_edit_employeerolepermission_roleposition_permissions_colorbox: function() {
                app_permissions.route_permission.profile.rolepermissions_table();
                app_permissions.route_permission.profile.permissions_table();
                app_permissions.route_permission.profile.handlefixedheaderpinning();
                //app_permissions.route_permission.profile.routes_fixed_header_scroll_with_scroll_handler();
            },
            rolepermissions_table: function() {
                var document_height = $(document).height() - 400;

                function getData(results) {
                    $.ajax({
                        url: app.data.routepermission_getRoleRositioninfo_db_table,
                        data: { 'getting_columns': true },
                        success: results,
                    });
                }
                $(document).ready(function() {
                    getData(function(data) {
                        var dynamix_columns = [];
                        var columnNames = Object.keys(data.data[0]);
                        //console.log(columnNames);
                        for (var i in columnNames) {
                            var classNameforcheckboxes = null;
                            var be_searchable = false;
                            //need to add this class to the permission checkbox columns className: 'checkbox_col'
                            if (i >= 2) {
                                classNameforcheckboxes = 'checkbox_col';
                            }
                            if (i == 0 || i == 1) {
                                be_searchable = true;
                            }
                            dynamix_columns.push({
                                orderable: false,
                                searchable: be_searchable,
                                data: columnNames[i],
                                className: classNameforcheckboxes
                            });
                        }
                        //console.log(dynamix_columns);
                        window.rolepositiontable = $('#rolepositionTable').DataTable({
                            pageLength: -1,
                            lengthMenu: app.conf.table.lengthMenu,
                            processing: true,
                            serverSide: true,
                            bFilter: true,
                            ordering: true,
                            responsive: true,
                            scrollCollapse: true,
                            bAutoWidth: true,
                            // fixedHeader: {
                            //     header: true,
                            //     footer: true,
                            //     headerOffset: 57,
                            //     footerOffset: 1
                            // },
                            scrollX: true,
                            scrollY: document_height,
                            fixedColumns: {
                                leftColumns: 2
                            },
                            aaSorting: [],
                            stateSave: true,
                            stateDuration: -1,
                            stateSaveCallback: function(settings, data) {
                                var status_all = $("#status_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                                var team_all = $("#team_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                                var position_all = $("#position_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                                var action_all = $("#action_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                                var editor_level_all = $("#editor_level_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                                var sections_all = $("#sections_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                                data['filters'] = { "status": status_all, "team": team_all, "position": position_all, "action": action_all, "editor_level": editor_level_all, "sections": sections_all };
                                sessionStorage.setItem('Br24_' + app.env() + '_rolepositiontable_table_' + settings.sInstance, JSON.stringify(data));
                            },
                            stateLoadCallback: function(settings) {
                                return JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_rolepositiontable_table_' + settings.sInstance));
                                //
                            },
                            oLanguage: {
                                'sProcessing': "<div class='loader_blank'></div><div class='processingblured'>" + eval("app.translations." + app.data.locale + ".please_wait_loading") + "<br><img src='../img/loader.gif'></div><div class='no_blurtext'>" + eval("app.translations." + app.data.locale + ".please_wait_loading") + "<br><img src='../img/loader.gif'></div>",
                                'sZeroRecords': eval("app.translations." + app.data.locale + ".no_roles_added"),
                                'sSearch': eval("app.translations." + app.data.locale + ".section_search")
                            },
                            ajax: {
                                url: app.data.routepermission_getRoleRositioninfo_db_table,
                                dataSrc: 'data',
                            },
                            columnDefs: [
                                { targets: '_all', width: 40 },
                                //
                            ],
                            columns: dynamix_columns,
                            dom: '<".controlsfortable"<"#export_buttonlocation.html5buttons">rf<"#position_filter.dataTables_filter"><"#team_filter.dataTables_filter"><"#editor_level_filter.dataTables_filter"><"#status_filter.dataTables_filter"><"#sections_filter.dataTables_filter"><"#clear_filter.html5buttons"><"clearfix"><".table_block"<".table_float_left"i><".table_float_right">>>t<".controlsfortable">',
                            createdRow: function(row, data, index) {
                                //console.log('data=' + JSON.stringify(data));
                                //console.log('index=' + index);
                                //var str = "How are you doing today?";
                                var res = data.permission.split("data-permission=\"");
                                res = res[1].split("\"");
                                var permission_syntax = res[0];
                                //console.log(permission_syntax);
                                $('td', row).attr('data-permission', permission_syntax).addClass('parent').css('padding', '');
                                //if (JSON.stringify(data.user_id) != 0) { $(row).css('cursor', 'pointer'); }
                                //necessary for the detailed task view per employee
                            },
                            drawCallback: function(settings) {
                                $('#rolepositionTable_filter').css('padding-bottom: 10px');
                                $('.DTFC_LeftBodyWrapper').css('background-color', '#fff');
                                var api = this.api();
                                // $.each(app.conf.table.totalColumn.familymemberinfoIndex, function(idx, val) {
                                //     app.util.totalFormat(idx, api, val);
                                // });
                                $('[data-toggle="popover"]').popover({
                                    delay: {
                                        show: "500",
                                        hide: "100"
                                    }
                                });
                                app.util.nprogressdone();

                                $("input[type=search]").focus();
                                if ($('.ms-has-selections')[0]) {
                                    $('a[name="clearallfilters"]').css('display', "block");
                                } else {
                                    $('a[name="clearallfilters"]').css('display', "none");
                                }

                                function isEmpty(obj) {
                                    for (var key in obj) {
                                        if (obj.hasOwnProperty(key))
                                            return false;
                                    }
                                    return true;
                                }

                                function pad(number, length) {
                                    var str = '' + number;
                                    while (str.length < length) { str = '0' + str; }
                                    return str;
                                }

                                var prefered_dateFormat = null;
                                var patt = null;
                                var prefered_dateFormat_placeholder = null;
                                var number_format_per_locale = null;
                                var delimiter_for_splitting_variable = null;
                                var numberformat_locale = null;
                                if (app.data.locale === 'vi') {
                                    prefered_dateFormat = "dd/mm/yy";
                                    prefered_dateFormat_placeholder = "dd/mm/yyyy";
                                    patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                                    delimiter_for_splitting_variable = '/';
                                } else if (app.data.locale === 'en') {
                                    prefered_dateFormat = "dd/mm/yy";
                                    prefered_dateFormat_placeholder = "dd/mm/yyyy";
                                    patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                                    delimiter_for_splitting_variable = '/';
                                } else if (app.data.locale === 'de') {
                                    prefered_dateFormat = "dd.mm.yy";
                                    prefered_dateFormat_placeholder = "dd.mm.yyyy";
                                    patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                                    delimiter_for_splitting_variable = '.';
                                }

                                if (app.data.currency == 'VND') {
                                    number_format_per_locale = '#,##0.## ₫';
                                    numberformat_locale = 'vi';
                                } else if (app.data.currency == 'USD') {
                                    number_format_per_locale = '$ #,##0.00';
                                    numberformat_locale = 'en';
                                } else if (app.data.currency == 'EUR') {
                                    number_format_per_locale = '€ #,##0.00';
                                    numberformat_locale = 'de';
                                } else {
                                    number_format_per_locale = '#,##0.## ₫';
                                    numberformat_locale = 'vi';
                                }

                                $("input[name*='edit_']").on('click', function(event) {
                                    event.stopImmediatePropagation();
                                    //console.log(this.name);
                                    /**on click we will ask if they want to change the permission for role and section*/
                                    app_permissions.route_permission.profile.handle_route_permission(this.name);
                                });

                                $('.checkbox_col.parent').not("input[name*='edit_']").on('click', function(event) {
                                    event.stopImmediatePropagation();
                                    //console.log('clickingaroundcheckboxonemployeerow');
                                    var idx = window.rolepositiontable.cell(this).index().column;

                                    var title = window.rolepositiontable.column(idx).header();
                                    var rolename = $(title).html().replace(/ /g, "_").toLowerCase();
                                    var permission_Id = $(this).data('permission');
                                    // console.log(permission_Id);
                                    var element_name = "edit_" + rolename + "_" + permission_Id
                                    var checkbox_in_td = $("input[name='edit_" + rolename + "_" + permission_Id + "']");
                                    if (checkbox_in_td.is(':disabled')) {
                                        /** */
                                        /** */
                                    } else {
                                        if (checkbox_in_td.is(':checked')) {
                                            checkbox_in_td.prop("checked", false);
                                        } else {
                                            checkbox_in_td.prop("checked", true);
                                        }
                                        /**on click we will ask if they want to change the permission for role and section*/
                                        app_permissions.route_permission.profile.handle_route_permission(element_name);
                                    }
                                });
                                $('input[type="checkbox"], .checkbox_col').css('cursor', 'pointer');
                            }
                        });

                        var rememberwhichtdbefore = null;
                        $('.dataTable tbody').on('mouseenter', 'td', function() {
                            if ($('#rolepositionTable .dataTables_empty').is(":visible")) {
                                /** do not to anything */
                            } else {
                                var colIdx = window.rolepositiontable.cell(this).index().column;
                                var rowIdx = window.rolepositiontable.cell(this).index().row;
                                //console.log(colIdx);
                                //console.log(rowIdx);
                                //$(window.rolepositiontable.row(rowIdx).nodes()).addClass('highlight');
                                if (0 <= colIdx && 37 >= colIdx) {
                                    $(window.rolepositiontable.cells().nodes()).removeClass('highlight');
                                    $(window.rolepositiontable.column(colIdx).nodes()).addClass('highlight');
                                    $(window.rolepositiontable.columns().header()).removeClass('highlight');
                                    $(window.rolepositiontable.columns().footer()).removeClass('highlight');

                                    $(window.rolepositiontable.column(colIdx).header()).addClass('highlight');
                                    $(window.rolepositiontable.column(colIdx).footer()).addClass('highlight');
                                }
                                if (rememberwhichtdbefore !== rowIdx) {
                                    $(".DTFC_LeftBodyLiner tr[data-dt-row='" + rememberwhichtdbefore + "']").css({
                                        'background-color': '#fff'
                                    });
                                }
                                rememberwhichtdbefore = rowIdx;
                                $(".DTFC_LeftBodyLiner tr[data-dt-row='" + rememberwhichtdbefore + "']").css({
                                    'background-color': '#F5F5F5'
                                });
                            }
                        });
                        $('.dataTable tbody').on('mouseleave', function() {
                            //console.log('leftthetablearea');
                            if ($('#rolepositionTable .dataTables_empty').is(":visible")) {
                                /** do not to anything */
                            } else {
                                $(window.rolepositiontable.cells().nodes()).removeClass('highlight');
                                $(window.rolepositiontable.columns().header()).removeClass('highlight');
                                $(window.rolepositiontable.columns().footer()).removeClass('highlight');
                                $(".DTFC_LeftBodyLiner tr").css({
                                    'background-color': '#fff'
                                });
                            }
                        });
                        app_permissions.route_permission.profile.filter.bySections(window.rolepositiontable);
                        /**app_permissions.route_permission.profile.filter.clearallfilter(window.rolepositiontable);*/
                        $('select#sections_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                        var filter_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_rolepositiontable_table_rolepositionTable'));
                        if (filter_load !== null) {
                            if (filter_load['filters']['sections'] !== "") {
                                var array = filter_load['filters']['sections'].split('|');
                                $("select#sections_filter").val(array);
                                $("select#sections_filter").multiselect('reload');
                            }
                        }
                        $('a[name="clearallfilters"]').on('click', function() {
                            array = [];
                            $("select#sections_filter").val(array);
                            $("select#sections_filter").multiselect('reload');
                            $('.dataTable').DataTable().search('').columns().search('').draw();
                        });
                        $('#role_permission_detail_form').areYouSure();
                    });
                });
            },
            permissions_table: function() {
                window.permissionstable = $('#permissionsTable').DataTable({
                    pageLength: -1,
                    lengthMenu: app.conf.table.lengthMenu,
                    processing: true,
                    serverSide: true,
                    bFilter: true,
                    ordering: true,
                    responsive: true,
                    scrollCollapse: true,
                    bAutoWidth: true,
                    fixedHeader: {
                        header: true,
                        footer: true,
                        headerOffset: 57,
                        footerOffset: 1
                    },
                    aaSorting: [],
                    stateSave: true,
                    stateDuration: -1,
                    stateSaveCallback: function(settings, data) {
                        var status3_all = $("#status_filter3 option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var team3_all = $("#team_filter3 option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var position3_all = $("#position_filter3 option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var action3_all = $("#action_filter3 option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var editor_level3_all = $("#editor_level_filter3 option:selected").map(function() { return $(this).val(); }).get().join('|');
                        data['filters'] = { "status": status3_all, "team": team3_all, "position": position3_all, "action": action3_all, "editor_level": editor_level3_all };
                        sessionStorage.setItem('Br24_' + app.env() + '_permissionstable_table_' + settings.sInstance, JSON.stringify(data));
                    },
                    stateLoadCallback: function(settings) {
                        return JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_permissionstable_table_' + settings.sInstance));
                        //
                    },
                    oLanguage: {
                        'sProcessing': "<div class='loader_blank'></div><div class='processingblured'>" + eval("app.translations." + app.data.locale + ".please_wait_loading") + "<br><img src='../img/loader.gif'></div><div class='no_blurtext'>" + eval("app.translations." + app.data.locale + ".please_wait_loading") + "<br><img src='../img/loader.gif'></div>",
                        'sZeroRecords': eval("app.translations." + app.data.locale + ".no_permissions_added")
                    },
                    ajax: {
                        url: app.data.routepermission_getPermissionsinfo_db_table,
                        dataSrc: 'data',
                    },
                    columnDefs: [
                        //{ targets: [7, 8], width: 88 },
                    ],
                    columns: [
                        { orderable: false, searchable: false, data: 'numbering' },
                        { orderable: false, searchable: false, data: 'name' },
                        { orderable: false, searchable: false, data: 'actions' },
                        { orderable: false, searchable: false, data: 'last_updated_by' },
                        { orderable: false, searchable: false, data: 'last_updated' }
                    ],
                    dom: '<".controlsfortable"<"#export_buttonlocation.html5buttons">rlf<"#position_filter.dataTables_filter"><"#team_filter.dataTables_filter"><"#editor_level_filter.dataTables_filter"><"#status_filter.dataTables_filter"><"#clear_filter.html5buttons"><"clearfix"><".table_block"<".table_float_left"i><".table_float_right"p>>>t<".controlsfortable"p>',
                    drawCallback: function(settings) {
                        var api = this.api();
                        // $.each(app.conf.table.totalColumn.generaldocumentsIndex, function(idx, val) {
                        //     app.util.totalFormat(idx, api, val);
                        // });
                        $('[data-toggle="popover"]').popover({
                            delay: {
                                show: "500",
                                hide: "100"
                            }
                        });
                        app.util.nprogressdone();

                        function isEmpty(obj) {
                            for (var key in obj) {
                                if (obj.hasOwnProperty(key))
                                    return false;
                            }
                            return true;
                        }

                        function pad(number, length) {
                            var str = '' + number;
                            while (str.length < length) { str = '0' + str; }
                            return str;
                        }

                        var prefered_dateFormat = null;
                        var patt = null;
                        var prefered_dateFormat_placeholder = null;
                        var number_format_per_locale = null;
                        var delimiter_for_splitting_variable = null;
                        var numberformat_locale = null;
                        if (app.data.locale === 'vi') {
                            prefered_dateFormat = "dd/mm/yy";
                            prefered_dateFormat_placeholder = "dd/mm/yyyy";
                            patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                            delimiter_for_splitting_variable = '/';
                        } else if (app.data.locale === 'en') {
                            prefered_dateFormat = "dd/mm/yy";
                            prefered_dateFormat_placeholder = "dd/mm/yyyy";
                            patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                            delimiter_for_splitting_variable = '/';
                        } else if (app.data.locale === 'de') {
                            prefered_dateFormat = "dd.mm.yy";
                            prefered_dateFormat_placeholder = "dd.mm.yyyy";
                            patt = new RegExp("^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0]?[1-9]|[1][0-2])[.\/-]([0-9]{4}|[0-9]{2})$");
                            delimiter_for_splitting_variable = '.';
                        }

                        if (app.data.currency == 'VND') {
                            number_format_per_locale = '#,##0.## ₫';
                            numberformat_locale = 'vi';
                        } else if (app.data.currency == 'USD') {
                            number_format_per_locale = '$ #,##0.00';
                            numberformat_locale = 'en';
                        } else if (app.data.currency == 'EUR') {
                            number_format_per_locale = '€ #,##0.00';
                            numberformat_locale = 'de';
                        } else {
                            number_format_per_locale = '#,##0.## ₫';
                            numberformat_locale = 'vi';
                        }

                        $(document).ready(function() {
                            var currentColorBox = ''; /** super important if you want only specific set of colorboxes to resize */
                            var window_focus = true;
                            $(window).focus(function() { window_focus = true; }).blur(function() { window_focus = false; });

                            //define some variables here so can use inside the colorbox onClose Callbacks
                            var checks_if_avatar_colorbox_is_open = null;
                            var success_ajax_then_refresh = null;
                            var been_out = null
                            var refreshIntervalId = null;
                            var timer = function() {
                                refreshIntervalId = setInterval(function() {
                                    //console.log('has focus? ' + window_focus);
                                    //console.log('avatar_colorbox_open=' + checks_if_avatar_colorbox_is_open);
                                }, 100);
                            };
                            var close_colorbox_refreshIntervalId = null;
                            var close_colorbox_timer = function() {
                                //console.log('close_colorbox_100_ms_countdown_timer_started');
                                close_colorbox_refreshIntervalId = setInterval(function() {
                                    $("#cb_add_employeerolepermission.ajax").colorbox.close();
                                    $("#cb_edit_employeerolepermission.ajax").colorbox.close();
                                    $("#cb_add_rolepermission.ajax").colorbox.close();
                                    $("#cb_edit_rolepermission.ajax").colorbox.close();
                                    $("#cb_add_permission.ajax").colorbox.close();
                                    $("#cb_edit_permission.ajax").colorbox.close();
                                    clearInterval(close_colorbox_refreshIntervalId);
                                }, 100);
                            };

                            /** must be at the end */
                            var cboxOptions = { width: '95%', height: '85%', }
                            $(window).resize(function() {
                                var colorboxes_array = ["cb_add_employeerolepermission", "cb_add_rolepermission", "cb_add_permission", "cb_edit_employeerolepermission", "cb_edit_rolepermission", "cb_edit_permission"];
                                if (colorboxes_array.indexOf(currentColorBox) > -1) {
                                    $.colorbox.resize({
                                        width: window.innerWidth > parseInt(cboxOptions.maxWidth) ? cboxOptions.maxWidth : cboxOptions.width,
                                        height: window.innerHeight > parseInt(cboxOptions.maxHeight) ? cboxOptions.maxHeight : cboxOptions.height
                                    });
                                }
                            });


                            $("#cb_add_permission.ajax").colorbox({
                                rel: 'nofollow',
                                width: "95%",
                                height: "85%",
                                escKey: false, //escape key will not close
                                overlayClose: false, //clicking background will not close
                                closeButton: false, // hide the close button
                                onOpen: function() {
                                    //console.log('onOpen: colorbox is about to open');
                                    currentColorBox = 'cb_add_permission';
                                },
                                onLoad: function() {
                                    //console.log('onLoad: colorbox has started to load the targeted content');
                                    //
                                },
                                onComplete: function() {
                                    //timer();
                                    //console.log('timer_started');
                                    //console.log('onComplete: colorbox has displayed the loaded content');
                                    NProgress.configure({ parent: '#progress-bar-parent' });
                                    $('#issue_date').prop('disabled', true).prop('readonly', true);
                                    $('#expiration_date').prop('disabled', true).prop('readonly', true);
                                    $('#doc_number').prop('disabled', true).prop('readonly', true);
                                    $('#issue_place').prop('disabled', true).prop('readonly', true);

                                    var counting_issue = 1;
                                    var $issuedate = $('#issue_date');
                                    $issuedate.datepicker({
                                        dateFormat: prefered_dateFormat,
                                        showMonthAfterYear: true,
                                        numberOfMonths: 1,
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange: "-100:+15",
                                        showOtherMonths: true,
                                        selectOtherMonths: true,
                                        toggleActive: true,
                                        minDate: "-100y",
                                        maxDate: "+15y",
                                        autoclose: true,
                                        onClose: function() {

                                            var addressinput = $(this).val();
                                            /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/

                                            var res = patt.test(addressinput);
                                            if (res == true) {
                                                $('#issue_date').closest('td').removeClass('has-error');
                                                $('#issue_date').nextAll('.help-block').css('display', 'none');

                                                $(this).blur();
                                                counting_issue = 1;
                                            } else {
                                                $("#issue_date").closest("td").addClass("has-error");
                                                if (counting_issue == 1) {
                                                    $("#issue_date").after('<span class="help-block"><strong>' + eval("app.translations." + app.data.locale + ".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations." + app.data.locale + ".or_choose_from_the_calendar") + '</strong></span>');
                                                } else {
                                                    // it exists
                                                }
                                                counting_issue++;
                                                $(this).blur();
                                            }

                                        },
                                        beforeShow: function(input, obj) {
                                            $issuedate.after($issuedate.datepicker('widget'));
                                            setTimeout(function() {
                                                $('#ui-datepicker-div').css('top', '').css('left', '');
                                            }, 0);
                                        }
                                    });

                                    var counting_expire = 1;
                                    var $expiredate = $('#expiration_date');
                                    $expiredate.datepicker({
                                        dateFormat: prefered_dateFormat,
                                        showMonthAfterYear: true,
                                        numberOfMonths: 1,
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange: "-100:+15",
                                        showOtherMonths: true,
                                        selectOtherMonths: true,
                                        toggleActive: true,
                                        minDate: "-100y",
                                        maxDate: "+15y",
                                        autoclose: true,
                                        onClose: function() {
                                            //console.log('closed');
                                            var addressinput = $(this).val();
                                            /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                                            //console.log('addressinput=' + addressinput);

                                            if (addressinput == '') {
                                                /** if the date is blank don't show error because this field is not a requiured field */
                                                $('#expiration_date').closest('td').removeClass('has-error');
                                                $('#expiration_date').nextAll('.help-block').css('display', 'none');
                                                counting_expire = 1;
                                            } else {
                                                var res = patt.test(addressinput);
                                                if (res == true) {
                                                    $('#expiration_date').closest('td').removeClass('has-error');
                                                    $('#expiration_date').nextAll('.help-block').css('display', 'none');

                                                    $(this).blur();
                                                    counting_expire = 1;
                                                } else {

                                                    $("#expire_date").closest("td").addClass("has-error");
                                                    if (counting_expire == 1) {
                                                        $("#expire_date").after('<span class="help-block"><strong>' + eval("app.translations." + app.data.locale + ".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations." + app.data.locale + ".or_choose_from_the_calendar") + '</strong></span>');
                                                    } else {
                                                        // it exists
                                                    }
                                                    counting_expire++;
                                                    $(this).blur();

                                                }
                                            }
                                        },
                                        beforeShow: function(input, obj) {
                                            $expiredate.after($expiredate.datepicker('widget'));
                                            setTimeout(function() {
                                                $('#ui-datepicker-div').css('top', '').css('left', '');
                                            }, 0);
                                        }
                                    });

                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $("#add_document_details_table").data("token") } });
                                    var data = {};
                                    var userId = app.data.userId;
                                    //var todayDate = new Date().toISOString().slice(0, 10);

                                    var dropzone_isvisible_check = null;
                                    var clickedtoadd_doc_type = null;
                                    $(document).ready(function() {
                                        //$('input[type=file]')
                                        $('input[data-ident=add_doc_file]').fileinput({
                                            'language': app.data.locale,
                                            'showUpload': false,
                                            'previewFileType': 'any',
                                            'showClose': false,
                                            'showCaption': true,
                                            'showBrowse': true,
                                            'browseClass': 'btn btn-primary btn-file btn-fileinput-k',
                                            'browseIcon': '<i class="glyphicon glyphicon-folder-open"></i> ',
                                            'showUploadedThumbs': true,
                                            'showPreview': true,
                                            'showRemove': true,
                                            'layoutTemplates': {
                                                'size': ' <samp>({sizeText})</samp>',
                                                'footer': '<div class="file-thumbnail-footer">\n' +
                                                    '<div class="file-footer-caption" title="{caption}">\n' +
                                                    '<div class="file-caption-info">{caption}</div>\n' +
                                                    '<div class="file-size-info">{size}</div>\n' +
                                                    '</div>\n' +
                                                    '{progress}\n{indicator}\n{actions}\n' +
                                                    '</div>',
                                                'actions': '<div class="file-actions">\n' +
                                                    '<div class="file-footer-buttons">\n' +
                                                    '{download} {upload} {delete} {other}' +
                                                    '</div>\n' +
                                                    '</div>\n' +
                                                    '<div class="clearfix"></div>',
                                            },
                                            'preferIconicPreview': false,
                                            /** this will force thumbnails to display icons for following file extensions*/
                                            'previewFileIconSettings': { /** configure your icon file extensions*/
                                                'doc': '<i class="glyphicon glyphicon-file text-primary"></i>',
                                                'xls': '<i class="glyphicon glyphicon-file text-success"></i>',
                                                'ppt': '<i class="glyphicon glyphicon-file text-danger"></i>',
                                                'pdf': '<i class="glyphicon glyphicon-file text-danger"></i>',
                                                'zip': '<i class="glyphicon glyphicon-file text-muted"></i>',
                                                'htm': '<i class="glyphicon glyphicon-file text-info"></i>',
                                                'txt': '<i class="glyphicon glyphicon-file text-info"></i>',
                                                'mov': '<i class="glyphicon glyphicon-file text-warning"></i>',
                                                'mp3': '<i class="glyphicon glyphicon-file text-warning"></i>',
                                            },
                                            'previewFileExtSettings': { /** configure the logic for determining icon file extensions*/
                                                'doc': function(ext) {
                                                    return ext.match(/(doc|docx)$/i);
                                                },
                                                'xls': function(ext) {
                                                    return ext.match(/(xls|xlsx)$/i);
                                                },
                                                'ppt': function(ext) {
                                                    return ext.match(/(ppt|pptx)$/i);
                                                },
                                                'pdf': function(ext) {
                                                    return ext.match(/(pdf)$/i);
                                                },
                                                'zip': function(ext) {
                                                    return ext.match(/(zip|rar|tar|gzip|gz|7z)$/i);
                                                },
                                                'htm': function(ext) {
                                                    return ext.match(/(htm|html)$/i);
                                                },
                                                'txt': function(ext) {
                                                    return ext.match(/(txt|ini|csv|java|php|js|css)$/i);
                                                },
                                                'mov': function(ext) {
                                                    return ext.match(/(avi|mpg|mkv|mov|mp4|3gp|webm|wmv)$/i);
                                                },
                                                'mp3': function(ext) {
                                                    return ext.match(/(mp3|wav)$/i);
                                                }
                                            },
                                        });

                                        //onclick event open dialogue
                                        $('input.file-caption-name').prop('disabled', true).css('cursor', 'pointer');
                                        $('input.file-caption-name').click(function() {
                                            $('input[type=file]').trigger('click');
                                        });
                                        $('input[type=file]').change(function() {
                                            $('input.file-caption-name').val($(this).val());
                                        });
                                        $('div.file-caption').click(function() {
                                            $('input[type=file]').trigger('click');
                                        });
                                        $('div.file-drop-zone').css('cursor', 'pointer');
                                        $('div.file-drop-zone').click(function() {
                                            $('input[type=file]').trigger('click');
                                        });
                                        $('.kv-file-remove').click(function(event) {
                                            event.stopImmediatePropagation();
                                        });
                                        $('.kv-file-download').click(function(event) {
                                            event.stopImmediatePropagation();
                                        });
                                        $('.file-preview-thumbnails').click(function(event) {
                                            event.stopImmediatePropagation();
                                            console.log('clicked kv-file-thumbnails');
                                        });
                                        $('.kv-fileinput-error').click(function(event) {
                                            event.stopImmediatePropagation();
                                            console.log('clicked kv-file-error');
                                        });
                                        var constantlychecking_if_timer = setInterval(function() {
                                            var dropzone_isvisible_check = $('div.file-drop-zone-title').is(":visible");
                                            if (dropzone_isvisible_check == true) {
                                                $('div.file-drop-zone-title').css('cursor', 'pointer');
                                            } else {
                                                $('div.file-preview-frame').css('cursor', 'pointer');
                                                //$('div.file-actions').hide();
                                            }
                                            var popover_isvisible_check = $('.popover').is(":visible");
                                            if (popover_isvisible_check == true) {
                                                $('.popover').css('z-index', '20000000');
                                            }
                                        }, 500);


                                        $('#add_doc_type_button').click(function() {
                                            clickedtoadd_doc_type = 1;
                                        });

                                        var whatarewelookingat = setInterval(function() {
                                            //console.log('ticking');
                                            if (window_focus == true) {
                                                if (been_out !== null) {
                                                    if (clickedtoadd_doc_type == 1) {
                                                        $('select#doc_type_id').css('cursor', 'wait');
                                                        $('select#doc_type_id').prop('disabled', true);
                                                        $('.branch_loader').css('display', 'block');
                                                        app.ajax.json(app.data.urlProfileDocList, data, null, function() {
                                                            documentlist = app.ajax.result.prefix;
                                                            var askingvalue = Object.keys(documentlist).length;
                                                            //console.log('sizeof dropdown='+app.data.howmanydocTypes);
                                                            //console.log('list='+JSON.stringify(documentlist));
                                                            //console.log('sizeof list='+askingvalue);
                                                            if (app.data.howmanydocTypes !== askingvalue) {
                                                                $('select#doc_type_id').empty().html(app.ajax.result.build);
                                                                $('#prefix').val('');
                                                                console.log('updated-list');
                                                                app.data.howmanydocTypes = askingvalue;
                                                            }
                                                            clearInterval(refreshIntervalId);
                                                            //console.log('back here');
                                                            $('.branch_loader').fadeOut('fast');
                                                            $('select#doc_type_id').css('cursor', 'auto');
                                                            $('select#doc_type_id').prop('disabled', false);
                                                        });
                                                        been_out = null;
                                                        //console.log('reset_timer_after_gettinglist_and_now_tracking_if_beenout');
                                                        timer();
                                                        clickedtoadd_doc_type = null;
                                                    }
                                                }
                                                //$('select#doc_type_id').prop('disabled', false);
                                                $('select#doc_type_id').css('cursor', 'auto');
                                            }
                                            if (window_focus == false) {
                                                //console.log('timer-reset');
                                                //$('select#doc_type_id').prop('disabled', true);
                                                $('select#doc_type_id').css('cursor', 'pointer');
                                                been_out = 1;
                                                timer();
                                            };
                                        }, 500);

                                        //pre populate the dropdown list on page ready
                                        var documentlist = [];
                                        if ($('#prefix').val() !== '') {
                                            //console.log('already had value');
                                            $('select#doc_type_id').prop('disabled', true).css('cursor', 'wait');
                                            $('.branch_loader').css('display', 'block');
                                            app.ajax.json(app.data.urlProfileDocList, data, null, function() {
                                                $('select#doc_type_id').prop('disabled', false).css('cursor', 'auto');
                                                $('.branch_loader').fadeOut('fast');
                                                documentlist = app.ajax.result.prefix;
                                            });
                                        } else {
                                            $('select#doc_type_id').css('cursor', 'wait');
                                            $('select#doc_type_id').prop('disabled', true);
                                            $('.branch_loader').css('display', 'block');
                                            app.ajax.json(app.data.urlProfileDocList, data, null, function() {
                                                $('select#doc_type_id').empty().html(app.ajax.result.build);
                                                $('#prefix').val('');
                                                documentlist = app.ajax.result.prefix;
                                                $('.branch_loader').fadeOut('fast');
                                                $('select#doc_type_id').css('cursor', 'auto').prop('disabled', false);
                                            });
                                        }

                                        //on page load if using old form values such as when editing.. then do this
                                        if ($('select#doc_type_id').val() == 0) {
                                            $('#prefix').val('');
                                        } else {
                                            $.each(documentlist, function(index, value) {
                                                var selected = $('select#doc_type_id').val();
                                                if (value.id == selected) {
                                                    //console.log(JSON.stringify(app.data.document_numbering_for_naming));
                                                    var autodoctypenumbering = null;
                                                    // console.log(JSON.stringify(app.data.document_numbering_for_naming[selected]));
                                                    // console.log(typeof app.data.document_numbering_for_naming[selected]);
                                                    if (app.data.document_numbering_for_naming[selected] !== undefined) {
                                                        /**var doc_type_count = app.data.document_numbering_for_naming[selected].doc_type_count;*/
                                                        /**autodoctypenumbering = pad(doc_type_count + 1, 4);*/
                                                        autodoctypenumbering = app.data.document_numbering_for_naming[selected].next_available_counter_for_this_doc_type_and_userid;
                                                    } else {
                                                        autodoctypenumbering = '0001';
                                                    }
                                                    $('#prefix').val(value.prefix + '_' + userId + '_' + autodoctypenumbering);
                                                }
                                            });
                                        }

                                        //when change doc_type_id then do that
                                        $('select#doc_type_id').change(function() {
                                            var selected = $(this).val();
                                            var selected_doc_name = $(this).children("option:selected").text();
                                            //console.log(selected_doc_name);
                                            if (selected == '') {
                                                if ($('select#doc_type_id').val() == 0) {
                                                    $('#prefix').val('');
                                                    $('#upload_doc_reset').prop('disabled', true).prop('readonly', true).hide();
                                                    $('#upload_doc_update').prop('disabled', true).prop('readonly', true).hide();

                                                    $('#issue_date').prop('disabled', true).prop('readonly', true);
                                                    $('#expiration_date').prop('disabled', true).prop('readonly', true);
                                                    $('#doc_number').prop('disabled', true).prop('readonly', true);
                                                    $('#issue_place').prop('disabled', true).prop('readonly', true);
                                                }
                                            } else {
                                                $.each(documentlist, function(index, value) {
                                                    if (value.id == selected) {
                                                        //console.log(JSON.stringify(app.data.document_numbering_for_naming));
                                                        var autodoctypenumbering = null;
                                                        // console.log(JSON.stringify(app.data.document_numbering_for_naming[selected]));
                                                        // console.log(typeof app.data.document_numbering_for_naming[selected]);
                                                        if (app.data.document_numbering_for_naming[selected] !== undefined) {
                                                            /**var doc_type_count = app.data.document_numbering_for_naming[selected].doc_type_count;*/
                                                            /**autodoctypenumbering = pad(doc_type_count + 1, 4);*/
                                                            autodoctypenumbering = app.data.document_numbering_for_naming[selected].next_available_counter_for_this_doc_type_and_userid;
                                                        } else {
                                                            autodoctypenumbering = '0001';
                                                        }
                                                        //console.log(autodoctypenumbering);
                                                        $('#prefix').val(value.prefix + '_' + userId + '_' + autodoctypenumbering);
                                                    }
                                                });
                                                $('#upload_doc_reset').prop('disabled', false).prop('readonly', false).css('display', 'inline-block');
                                                $('#upload_doc_update').prop('disabled', false).prop('readonly', false).css('display', 'inline-block');

                                                $('#issue_date').prop('disabled', false).prop('readonly', false);
                                                $('#expiration_date').prop('disabled', false).prop('readonly', false);
                                                $('#doc_number').prop('disabled', false).prop('readonly', false);
                                                $('#issue_place').prop('disabled', false).prop('readonly', false);
                                            }
                                            // if(selected_doc_name == 'Passport' || selected_doc_name == 'Identity Card' || selected_doc_name == 'People Id CMND'){
                                            //     $('#doc_number').prop('disabled', false).prop('readonly', false);
                                            //     $('#issue_place_issue_place').prop('disabled', false).prop('readonly', false);
                                            // }else{
                                            //     $('#doc_number').prop('disabled', true).prop('readonly', true);
                                            //     $('#issue_place_issue_place').prop('disabled', true).prop('readonly', true);
                                            // }
                                        });
                                    });

                                    //if select is original cannot be is copy..
                                    $('#is_original').click(function() {
                                        var is_original_checked = $('#is_original:checkbox:checked').length > 0;
                                        if (is_original_checked == true) {
                                            $('#is_copy').prop('checked', false);
                                            $('#is_original_hidden').val(1);
                                            $('#is_copy_hidden').val(0);
                                        } else {
                                            $('#is_copy').prop('checked', true);
                                            $('#is_original_hidden').val(0);
                                            $('#is_copy_hidden').val(1);
                                        }
                                    });
                                    $('#is_copy').click(function() {
                                        var is_copy_checked = $('#is_copy:checkbox:checked').length > 0;
                                        if (is_copy_checked == true) {
                                            $('#is_original').prop('checked', false);
                                            $('#is_original_hidden').val(0);
                                            $('#is_copy_hidden').val(1);
                                        } else {
                                            $('#is_original').prop('checked', true);
                                            $('#is_original_hidden').val(1);
                                            $('#is_copy_hidden').val(0);
                                        }
                                    });
                                    $('#is_legalised').click(function() {
                                        var is_legalised_checked = $('#is_legalised:checkbox:checked').length > 0;
                                        if (is_legalised_checked == true) {
                                            $('#is_legalised_hidden').val(1);
                                        } else {
                                            $('#is_legalised_hidden').val(0);
                                        }
                                    });
                                    $('#is_notarised').click(function() {
                                        var is_notarised_checked = $('#is_notarised:checkbox:checked').length > 0;
                                        if (is_notarised_checked == true) {
                                            $('#is_notarised_hidden').val(1);
                                        } else {
                                            $('#is_notarised_hidden').val(0);
                                        }
                                    });

                                    //as soon as the ajax page is loaded then it will save the existing values to a session store
                                    var user = $('#user').val();
                                    var doc_type_id = $('#doc_type_id').val();
                                    var prefix = $('#prefix').val();
                                    var doc_no = $('#doc_no').val();
                                    var file = $('#file').val();

                                    var issue_date = $('#issue_date').val();
                                    var expiration_date = $('#expiration_date').val();
                                    var doc_number = $('#doc_number').val();
                                    var issue_place = $('#issue_place').val();

                                    var is_original = $('#is_original:checkbox:checked').length > 0; //checked = true unchecked = false
                                    var is_copy = $('#is_copy:checkbox:checked').length > 0; //checked = true unchecked = false
                                    var is_legalised = $('#is_legalised:checkbox:checked').length > 0; //checked = true unchecked = false
                                    var is_notarised = $('#is_notarised:checkbox:checked').length > 0; //checked = true unchecked = false

                                    var is_original_hidden = $('#is_original_hidden').val();
                                    var is_copy_hidden = $('#is_copy_hidden').val();
                                    var is_legalised_hidden = $('#is_legalised_hidden').val();
                                    var is_notarised_hidden = $('#is_notarised_hidden').val();
                                    //var limitation = $('#limitation').val();


                                    var url_user_id = app.data.user_id;
                                    var currentdate = new Date();
                                    var datetime = "Last Sync: " + currentdate.getDate() +
                                        "/" + (currentdate.getMonth() + 1) +
                                        "/" + currentdate.getFullYear() +
                                        " @ " + currentdate.getHours() +
                                        ":" + currentdate.getMinutes() +
                                        ":" + currentdate.getSeconds();

                                    var add_document_submitted_data = [];
                                    add_document_submitted_data['add_document_submitted_data'] = {
                                        "whenwasset": datetime,
                                        "user_id": url_user_id,

                                        "add_document_user": user,
                                        "add_document_doc_type_id": doc_type_id,
                                        "add_document_prefix": prefix,
                                        "add_document_doc_no": doc_no,
                                        "add_document_file": file,

                                        "add_document_issue_date": issue_date,
                                        "add_document_expiration_date": expiration_date,
                                        "add_document_doc_number": doc_number,
                                        "add_document_issue_place": issue_place,

                                        "add_document_is_original": is_original,
                                        "add_document_is_copy": is_copy,
                                        "add_document_is_legalised": is_legalised,
                                        "add_document_is_notarised": is_notarised,

                                        "add_document_is_original_hidden": is_original_hidden,
                                        "add_document_is_copy_hidden": is_copy_hidden,
                                        "add_document_is_legalised_hidden": is_legalised_hidden,
                                        "add_document_is_notarised_hidden": is_notarised_hidden
                                    };
                                    sessionStorage.setItem('Br24_' + app.env() + '_' + url_user_id + '_add_uploaddocumentinfo_previous_data', JSON.stringify(add_document_submitted_data['add_document_submitted_data']));

                                    $('#cb_add_document_details_form').areYouSure();
                                    $('#cb_add_document_details_form').on('change', 'select', function() {
                                        $("#upload_doc_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#upload_doc_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of select tags

                                    $('#cb_add_document_details_form').on('change keypress', 'input', function() {
                                        $("#upload_doc_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#upload_doc_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of input: the change event take care of input of type "hidden" also

                                    $("#upload_doc_reset").click(function() {
                                        $('#issue_date').prop('disabled', true).prop('readonly', true);
                                        $('#expiration_date').prop('disabled', true).prop('readonly', true);

                                        var button_selector = $('#upload_doc_reset, #upload_doc_update');
                                        //hide all the buttons
                                        button_selector.prop('disabled', true).css('display', 'none');

                                        var previous_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_' + url_user_id + '_add_uploaddocumentinfo_previous_data'));

                                        $('#user').val(previous_load['add_document_user']);
                                        $('#doc_type_id').val(previous_load['add_document_doc_type_id']);
                                        $('#prefix').val(previous_load['add_document_prefix']);
                                        $('#doc_no').val(previous_load['add_document_doc_no']);

                                        if (previous_load['add_document_file'] == '') {
                                            var dropzone_isvisible_check = null;
                                            $(document).ready(function() {
                                                $('input[data-ident=add_doc_file]').fileinput('clear').fileinput('destroy').fileinput({
                                                    'language': app.data.locale,
                                                    'showUpload': false,
                                                    'previewFileType': 'any',
                                                    'showClose': false,
                                                    'showCaption': true,
                                                    'showBrowse': true,
                                                    'browseClass': 'btn btn-primary btn-file btn-fileinput-k',
                                                    'browseIcon': '<i class="glyphicon glyphicon-folder-open"></i> ',
                                                    'showUploadedThumbs': true,
                                                    'showPreview': true,
                                                    'showRemove': true,
                                                    'layoutTemplates': {
                                                        'size': ' <samp>({sizeText})</samp>',
                                                        'footer': '<div class="file-thumbnail-footer">\n' +
                                                            '<div class="file-footer-caption" title="{caption}">\n' +
                                                            '<div class="file-caption-info">{caption}</div>\n' +
                                                            '<div class="file-size-info">{size}</div>\n' +
                                                            '</div>\n' +
                                                            '{progress}\n{indicator}\n{actions}\n' +
                                                            '</div>',
                                                        'actions': '<div class="file-actions">\n' +
                                                            '<div class="file-footer-buttons">\n' +
                                                            '{download} {upload} {delete} {other}' +
                                                            '</div>\n' +
                                                            '</div>\n' +
                                                            '<div class="clearfix"></div>',
                                                    },
                                                    'preferIconicPreview': false,
                                                    /** this will force thumbnails to display icons for following file extensions*/
                                                    'previewFileIconSettings': { /** configure your icon file extensions*/
                                                        'doc': '<i class="glyphicon glyphicon-file text-primary"></i>',
                                                        'xls': '<i class="glyphicon glyphicon-file text-success"></i>',
                                                        'ppt': '<i class="glyphicon glyphicon-file text-danger"></i>',
                                                        'pdf': '<i class="glyphicon glyphicon-file text-danger"></i>',
                                                        'zip': '<i class="glyphicon glyphicon-file text-muted"></i>',
                                                        'htm': '<i class="glyphicon glyphicon-file text-info"></i>',
                                                        'txt': '<i class="glyphicon glyphicon-file text-info"></i>',
                                                        'mov': '<i class="glyphicon glyphicon-file text-warning"></i>',
                                                        'mp3': '<i class="glyphicon glyphicon-file text-warning"></i>',
                                                    },
                                                    'previewFileExtSettings': { /** configure the logic for determining icon file extensions*/
                                                        'doc': function(ext) {
                                                            return ext.match(/(doc|docx)$/i);
                                                        },
                                                        'xls': function(ext) {
                                                            return ext.match(/(xls|xlsx)$/i);
                                                        },
                                                        'ppt': function(ext) {
                                                            return ext.match(/(ppt|pptx)$/i);
                                                        },
                                                        'pdf': function(ext) {
                                                            return ext.match(/(pdf)$/i);
                                                        },
                                                        'zip': function(ext) {
                                                            return ext.match(/(zip|rar|tar|gzip|gz|7z)$/i);
                                                        },
                                                        'htm': function(ext) {
                                                            return ext.match(/(htm|html)$/i);
                                                        },
                                                        'txt': function(ext) {
                                                            return ext.match(/(txt|ini|csv|java|php|js|css)$/i);
                                                        },
                                                        'mov': function(ext) {
                                                            return ext.match(/(avi|mpg|mkv|mov|mp4|3gp|webm|wmv)$/i);
                                                        },
                                                        'mp3': function(ext) {
                                                            return ext.match(/(mp3|wav)$/i);
                                                        }
                                                    },
                                                });

                                                //onclick event open dialogue
                                                $('input.file-caption-name').prop('disabled', true).css('cursor', 'pointer');
                                                $('input.file-caption-name').click(function() {
                                                    $('input[type=file]').trigger('click');
                                                });
                                                $('input[type=file]').change(function() {
                                                    $('input.file-caption-name').val($(this).val());
                                                });
                                                $('div.file-caption').click(function() {
                                                    $('input[type=file]').trigger('click');
                                                });
                                                $('div.file-drop-zone').css('cursor', 'pointer');
                                                $('div.file-drop-zone').click(function() {
                                                    $('input[type=file]').trigger('click');
                                                });
                                                $('.kv-file-remove').click(function(event) {
                                                    event.stopImmediatePropagation();
                                                });
                                                $('.kv-file-download').click(function(event) {
                                                    event.stopImmediatePropagation();
                                                });
                                                $('.file-preview-thumbnails').click(function(event) {
                                                    event.stopImmediatePropagation();
                                                    console.log('clicked kv-file-thumbnails');
                                                });
                                                $('.kv-fileinput-error').click(function(event) {
                                                    event.stopImmediatePropagation();
                                                    console.log('clicked kv-file-error');
                                                });
                                                var constantlychecking_if_timer = setInterval(function() {
                                                    var dropzone_isvisible_check = $('div.file-drop-zone-title').is(":visible");
                                                    if (dropzone_isvisible_check == true) {
                                                        $('div.file-drop-zone-title').css('cursor', 'pointer');
                                                    } else {
                                                        $('div.file-preview-frame').css('cursor', 'pointer');
                                                        //$('div.file-actions').hide();
                                                    }
                                                    var popover_isvisible_check = $('.popover').is(":visible");
                                                    if (popover_isvisible_check == true) {
                                                        $('.popover').css('z-index', '20000000');
                                                    }
                                                }, 500);
                                            });
                                        }
                                        $('#issue_date').val(previous_load['add_document_issue_date']);
                                        $('#expiration_date').val(previous_load['add_document_expiration_date']);
                                        $('#doc_number').val(previous_load['add_document_doc_number']);
                                        $('#issue_place').val(previous_load['add_document_issue_place']);

                                        $('#is_original:checkbox:checked').val(previous_load['add_document_is_original']);
                                        $('#is_copy:checkbox:checked').val(previous_load['add_document_is_copy']);
                                        $('#is_legalised:checkbox:checked').val(previous_load['add_document_is_legalised']);
                                        $('#is_notarised:checkbox:checked').val(previous_load['add_document_is_notarised']);



                                        if (previous_load['add_document_is_original_hidden'] == true) {
                                            $('#is_original').prop('checked', true);
                                            $('#is_original_hidden').val(1);
                                        } else {
                                            $('#is_original').prop('checked', false);
                                            $('#is_original_hidden').val(0);
                                        }
                                        if (previous_load['add_document_is_copy_hidden'] == true) {
                                            $('#is_copy').prop('checked', true);
                                            $('#is_copy_hidden').val(1);
                                        } else {
                                            $('#is_copy').prop('checked', false);
                                            $('#is_copy_hidden').val(0);
                                        }
                                        if (previous_load['add_document_is_legalised_hidden'] == true) {
                                            $('#is_legalised').prop('checked', true);
                                            $('#is_legalised_hidden').val(1);
                                        } else {
                                            $('#is_legalised').prop('checked', false);
                                            $('#is_legalised_hidden').val(0);
                                        }
                                        if (previous_load['add_document_is_notarised_hidden'] == true) {
                                            $('#is_notarised').prop('checked', true);
                                            $('#is_notarised_hidden').val(1);
                                        } else {
                                            $('#is_notarised').prop('checked', false);
                                            $('#is_notarised_hidden').val(0);
                                        }


                                        $('.has-error').removeClass('has-error');
                                        $('.help-block').css('display', 'none');
                                        $('#cb_add_document_details_form').trigger('reinitialize.areYouSure');
                                    });

                                    $("#upload_doc_update").click(function(e) {
                                        e.preventDefault();
                                        $('.alert_warning').css('display', 'none');
                                        $('.alert_success').css('display', 'none');

                                        var user = $('#user').val();
                                        var doc_type_id = $('#doc_type_id').val();
                                        var prefix = $('#prefix').val();
                                        var doc_no = $('#doc_no').val();
                                        var file = $('input[data-ident=add_doc_file]').val();

                                        var issue_date = $('#issue_date').val();
                                        var expiration_date = $('#expiration_date').val();
                                        var doc_number = $('#doc_number').val();
                                        var issue_place = $('#issue_place').val();

                                        var is_original = $('#is_original:checkbox:checked').length > 0; //checked = true unchecked = false
                                        var is_copy = $('#is_copy:checkbox:checked').length > 0; //checked = true unchecked = false
                                        var is_legalised = $('#is_legalised:checkbox:checked').length > 0; //checked = true unchecked = false
                                        var is_notarised = $('#is_notarised:checkbox:checked').length > 0; //checked = true unchecked = false

                                        var is_original_hidden = $('#is_original_hidden').val();
                                        var is_copy_hidden = $('#is_copy_hidden').val();
                                        var is_legalised_hidden = $('#is_legalised_hidden').val();
                                        var is_notarised_hidden = $('#is_notarised_hidden').val();


                                        var url_user_id = app.data.user_id;
                                        var currentdate = new Date();
                                        var datetime = "Last Sync: " + currentdate.getDate() +
                                            "/" + (currentdate.getMonth() + 1) +
                                            "/" + currentdate.getFullYear() +
                                            " @ " + currentdate.getHours() +
                                            ":" + currentdate.getMinutes() +
                                            ":" + currentdate.getSeconds();

                                        var add_document_submitted_data = [];
                                        add_document_submitted_data['add_document_submitted_data'] = {
                                            "whenwasset": datetime,
                                            "user_id": url_user_id,

                                            "add_document_user": user,
                                            "add_document_doc_type_id": doc_type_id,
                                            "add_document_prefix": prefix,
                                            "add_document_doc_no": doc_no,
                                            "add_document_file": file,

                                            "add_document_issue_date": issue_date,
                                            "add_document_expiration_date": expiration_date,
                                            "add_document_doc_number": doc_number,
                                            "add_document_issue_place": issue_place,

                                            "add_document_is_original": is_original,
                                            "add_document_is_copy": is_copy,
                                            "add_document_is_legalised": is_legalised,
                                            "add_document_is_notarised": is_notarised,

                                            "add_document_is_original_hidden": is_original_hidden,
                                            "add_document_is_copy_hidden": is_copy_hidden,
                                            "add_document_is_legalised_hidden": is_legalised_hidden,
                                            "add_document_is_notarised_hidden": is_notarised_hidden
                                        };
                                        sessionStorage.setItem('Br24_' + app.env() + '_' + url_user_id + '_add_uploaddocumentinfo_submitted_data', JSON.stringify(add_document_submitted_data['add_document_submitted_data']));

                                        var formData = new FormData($('#cb_add_document_details_form')[0]);

                                        NProgress.configure({ parent: '#cboxTitle', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                        NProgress.start();

                                        $('.cb_loader').css('display', 'block').css('cursor', 'wait');
                                        $('#cb_top').addClass('nprogress-busy').css('pointer-events', 'none');
                                        //app.util.fullscreenloading_start();

                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $("#add_document_details_table").data("token") } });
                                        app.ajax.formdata(app.data.employee_add_employee_document, formData, null, function() {
                                            //console.log(app.ajax.resultformdata);
                                            success_ajax_then_refresh = app.ajax.resultformdata.success;

                                            NProgress.done();
                                            $('.cb_loader').css('display', 'none').css('cursor', 'auto');
                                            $('#cb_top').removeClass('nprogress-busy').css('pointer-events', 'auto');

                                            if (app.ajax.resultformdata.success == true) {
                                                $('#cboxLoadedContent').css('background-color', '#4CAF50');
                                                $('.onSuccess_makeGreen').css('background-color', '#4CAF50');
                                                $('.ibox-tool-userid').css('color', 'white');
                                                $('#cb_add_document_details_form').css('display', 'none');
                                                close_colorbox_timer();
                                                $(document.body).css('pointer-events', 'none');
                                                app.util.fullscreenloading_start();
                                            } else {
                                                $('.has-error').removeClass('has-error');
                                                $('.help-block').detach();

                                                $.each(app.ajax.resultformdata.errors, function(idx, val) {
                                                    app_permissions.route_permission.profile.foreach_handle_error_display(idx, val);
                                                });


                                                if (isEmpty(app.ajax.resultformdata.errors)) {
                                                    // Object is empty (Would return true in this example)
                                                    //console.log('object_is_empty');
                                                } else {
                                                    //console.log('object_is_not_empty');
                                                    // Object is NOT empty
                                                    var first_error_offset = $(".has-error:visible:first").offset().top - $(".has-error:visible:first").offsetParent().offset().top;
                                                    var top_padding = 80;
                                                    var where_it_should_scroll_to_on_error = $('#cboxLoadedContent').scrollTop() - Math.abs(first_error_offset) - top_padding;
                                                    $('#cboxLoadedContent').animate({
                                                        scrollTop: where_it_should_scroll_to_on_error
                                                    }, 1000);
                                                }


                                                var indentifyiferrorsonpage = $('.has-error').length > 0;
                                                //change the view to reflect errors have been changed and what errors remain to be fixed
                                                if (indentifyiferrorsonpage == true || indentifyiferrorsonpage == false) {
                                                    $('#user_id').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #user_id');
                                                    });
                                                    $('#doc_type_id').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #doc_type_id');
                                                    });
                                                    $('#prefix').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #prefix');
                                                    });
                                                    $('#doc_no').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #doc_no');
                                                    });
                                                    $('#file').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $('.file-input').parent('.form-group').removeClass('has-error');
                                                        $('.file-caption').find('.help-block').remove();
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #file');
                                                    });
                                                    $('#issue_date').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #issue_date');
                                                    });
                                                    $('#expiration_date').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #expire_date');
                                                    });
                                                    $('#doc_number').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #doc_number');
                                                    });
                                                    $('#issue_place').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #issue_place');
                                                    });
                                                    $('#limitation_limited').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #limitation_limited');
                                                    });
                                                    $('#limitation_unlimited').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #limitation_unlimited');
                                                    });
                                                    $('#user_id').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #user_id');
                                                    });
                                                    $('#is_original_have').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #is_original_have');
                                                    });
                                                    $('#is_copy_have').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #is_copy_have');
                                                    });
                                                    $('#is_legalised_have').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #is_legalised_have');
                                                    });
                                                    $('#is_notarised_have').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #is_notarised_have');
                                                    });
                                                    $('#limitation_have').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #limitation_have');
                                                    });
                                                }
                                            }
                                        });
                                    });

                                    $('#cb_add_document_details_form').on('keyup keypress', function(e) {
                                        var keyCode = e.keyCode || e.which;
                                        if (keyCode === 13) {
                                            e.preventDefault();
                                            return false;
                                        }
                                    });

                                    $('#cboxOverlay').off('click').on('click', function(event) {
                                        //console.log('clickedousideofcolorbox');
                                        var identifychanges = $('#cb_add_document_details_form').hasClass('dirty');
                                        //console.log('here===' + identifychanges);
                                        if (identifychanges == true) {
                                            $.confirm({
                                                title: eval("app.translations." + app.data.locale + ".title_text"),
                                                content: eval("app.translations." + app.data.locale + ".you_have_unsaved_changes") + '\n' + eval("app.translations." + app.data.locale + ".do_you_want_to_discard_those_changes") + '\n',
                                                type: 'red',
                                                draggable: false,
                                                backgroundDismiss: 'cancel',
                                                escapeKey: true,
                                                animateFromElement: false,
                                                onAction: function(btnName) {
                                                    $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                                                },
                                                buttons: {
                                                    ok: {
                                                        btnClass: 'btn-primary text-white',
                                                        keys: ['enter'],
                                                        text: eval("app.translations." + app.data.locale + ".okay_text"),
                                                        action: function() {
                                                            event.stopPropagation();
                                                            $("#cb_add_doc.ajax").colorbox.close();
                                                            $('#cb_add_document_details_form').trigger('reinitialize.areYouSure');
                                                        }
                                                    },
                                                    cancel: {
                                                        text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                        action: function() {
                                                            return false;
                                                        }
                                                    },
                                                }
                                            });
                                        } else {
                                            $("#cb_add_doc.ajax").colorbox.close();
                                            $('#cb_add_document_details_form').trigger('reinitialize.areYouSure');
                                        }
                                    });
                                },
                                onCleanup: function() {
                                    //console.log('onCleanup: colorbox has begun the close process');
                                    if (success_ajax_then_refresh == true) {
                                        app.util.fullscreenloading_start();
                                    }
                                },
                                onClosed: function() {
                                    //console.log('onClosed: colorbox has completely closed');
                                    currentColorBox = '';
                                    $('.modal-backdrop').remove();
                                    $('body').css('cursor', 'default');
                                    if (success_ajax_then_refresh == true) {
                                        //var employeerolepermissiontable = $('#employeerolepermissionTable').DataTable();
                                        //var rolepositiontable = $('#rolepositionTable').DataTable();
                                        //var permissionstable = $('#permissionsTable').DataTable();
                                        window.employeerolepermissiontable.ajax.reload(null, false);
                                        window.rolepositiontable.ajax.reload(null, false);
                                        window.permissionstable.ajax.reload(null, false);
                                        app.util.fullscreenloading_end();
                                        window.employeerolepermissiontable.fixedHeader.adjust();
                                        window.rolepositiontable.fixedHeader.adjust();
                                        window.permissionstable.fixedHeader.adjust();
                                    }

                                    NProgress.configure({ parent: '#progress-bar-parent', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                    clearInterval(refreshIntervalId); //stop the timer called refreshIntervalId
                                    clearInterval(close_colorbox_refreshIntervalId);
                                    //app.util.fullscreenloading_end();
                                },
                            });

                            $("#cb_edit_permission.ajax").colorbox({
                                rel: 'nofollow',
                                width: "95%",
                                height: "85%",
                                escKey: false, //escape key will not close
                                overlayClose: false, //clicking background will not close
                                closeButton: false, // hide the close button
                                onOpen: function() {
                                    //console.log('onOpen: colorbox is about to open');
                                    currentColorBox = 'cb_edit_permission';
                                },
                                onLoad: function() {
                                    //console.log('onLoad: colorbox has started to load the targeted content');
                                    //
                                },
                                onComplete: function() {
                                    //timer();
                                    //console.log('timer_started');
                                    //console.log('onComplete: colorbox has displayed the loaded content');
                                    NProgress.configure({ parent: '#progress-bar-parent' });

                                    var counting_issue = 1;
                                    var $issuedate = $('#issue_date');
                                    $issuedate.datepicker({
                                        dateFormat: prefered_dateFormat,
                                        showMonthAfterYear: true,
                                        numberOfMonths: 1,
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange: "-100:+15",
                                        showOtherMonths: true,
                                        selectOtherMonths: true,
                                        toggleActive: true,
                                        minDate: "-100y",
                                        maxDate: "+15y",
                                        autoclose: true,
                                        onClose: function() {

                                            var addressinput = $(this).val();
                                            /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/

                                            var res = patt.test(addressinput);
                                            if (res == true) {
                                                $('#issue_date').closest('td').removeClass('has-error');
                                                $('#issue_date').nextAll('.help-block').css('display', 'none');

                                                $(this).blur();
                                                counting_issue = 1;
                                            } else {
                                                $("#issue_date").closest("td").addClass("has-error");
                                                if (counting_issue == 1) {
                                                    $("#issue_date").after('<span class="help-block"><strong>' + eval("app.translations." + app.data.locale + ".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations." + app.data.locale + ".or_choose_from_the_calendar") + '</strong></span>');
                                                } else {
                                                    // it exists
                                                }
                                                counting_issue++;
                                                $(this).blur();
                                            }

                                        },
                                        beforeShow: function(input, obj) {
                                            $issuedate.after($issuedate.datepicker('widget'));
                                            setTimeout(function() {
                                                $('#ui-datepicker-div').css('top', '').css('left', '');
                                            }, 0);
                                        }
                                    });

                                    var counting_expire = 1;
                                    var $expiredate = $('#expiration_date');
                                    $expiredate.datepicker({
                                        dateFormat: prefered_dateFormat,
                                        showMonthAfterYear: true,
                                        numberOfMonths: 1,
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange: "-100:+15",
                                        showOtherMonths: true,
                                        selectOtherMonths: true,
                                        toggleActive: true,
                                        minDate: "-100y",
                                        maxDate: "+15y",
                                        autoclose: true,
                                        onClose: function() {
                                            var addressinput = $(this).val();
                                            /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                                            var res = patt.test(addressinput);
                                            if (addressinput == '') {
                                                /** if the date is blank don't show error because this field is not a requiured field */
                                                $('#expiration_date').closest('td').removeClass('has-error');
                                                $('#expiration_date').nextAll('.help-block').css('display', 'none');
                                                counting_expire = 1;
                                            } else {
                                                var res = patt.test(addressinput);
                                                if (res == true) {
                                                    $('#expiration_date').closest('td').removeClass('has-error');
                                                    $('#expiration_date').nextAll('.help-block').css('display', 'none');

                                                    $(this).blur();
                                                    counting_expire = 1;
                                                } else {

                                                    $("#expire_date").closest("td").addClass("has-error");
                                                    if (counting_expire == 1) {
                                                        $("#expire_date").after('<span class="help-block"><strong>' + eval("app.translations." + app.data.locale + ".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations." + app.data.locale + ".or_choose_from_the_calendar") + '</strong></span>');
                                                    } else {
                                                        // it exists
                                                    }
                                                    counting_expire++;
                                                    $(this).blur();

                                                }
                                            }
                                        },
                                        beforeShow: function(input, obj) {
                                            $expiredate.after($expiredate.datepicker('widget'));
                                            setTimeout(function() {
                                                $('#ui-datepicker-div').css('top', '').css('left', '');
                                            }, 0);
                                        }
                                    });

                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $("#edit_document_details_table").data("token") } });
                                    var data = {};
                                    var userId = app.data.userId;
                                    var todayDate = new Date().toISOString().slice(0, 10);

                                    var dropzone_isvisible_check = null;
                                    var clickedtoadd_doc_type = null;
                                    $(document).ready(function() {
                                        // console.log('app.data.dd_route=' + app.data.dd_route);
                                        // console.log('app.data.dd_route_delete=' + app.data.dd_route_delete);
                                        // console.log('app.data.dd_size=' + app.data.dd_size);
                                        // console.log('app.data.dd_prefix=' + app.data.dd_prefix);
                                        // console.log('app.data.dd_type=' + app.data.dd_type);
                                        var url1 = app.data.dd_route;
                                        var url2 = app.data.dd_route_delete;
                                        $('input[data-ident=edit_doc_file]').fileinput({
                                            'language': app.data.locale,
                                            'showUpload': false,
                                            'previewFileType': 'any',
                                            'showClose': false,
                                            'showCaption': true,
                                            'showBrowse': true,
                                            'browseClass': 'btn btn-primary btn-file btn-fileinput-k',
                                            'browseIcon': '<i class="glyphicon glyphicon-folder-open"></i> ',
                                            'showUploadedThumbs': true,
                                            'showPreview': true,
                                            'showRemove': true,
                                            'initialPreview': url1,
                                            'initialPreviewFileType': 'image',
                                            'initialPreviewAsData': true,
                                            'initialPreviewConfig': [
                                                { type: app.data.dd_type, caption: app.data.dd_prefix, downloadUrl: url1, url: url2, size: app.data.dd_size, width: "120px", key: 1 }
                                            ],
                                            'initialPreviewShowDelete': true,
                                            'overwriteInitial': true,
                                            'maxFileSize': 25000,
                                            'initialCaption': app.data.dd_prefix,
                                            'layoutTemplates': {
                                                'size': ' <samp>({sizeText})</samp>',
                                                'footer': '<div class="file-thumbnail-footer">\n' +
                                                    '<div class="file-footer-caption" title="{caption}">\n' +
                                                    '<div class="file-caption-info">{caption}</div>\n' +
                                                    '<div class="file-size-info">{size}</div>\n' +
                                                    '</div>\n' +
                                                    '{progress}\n{indicator}\n{actions}\n' +
                                                    '</div>',
                                                'actions': '<div class="file-actions">\n' +
                                                    '<div class="file-footer-buttons">\n' +
                                                    '{download} {upload} {delete} {other}' +
                                                    '</div>\n' +
                                                    '</div>\n' +
                                                    '<div class="clearfix"></div>',
                                            },
                                            'preferIconicPreview': false,
                                            /** this will force thumbnails to display icons for following file extensions*/
                                            'previewFileIconSettings': { /** configure your icon file extensions*/
                                                'doc': '<i class="glyphicon glyphicon-file text-primary"></i>',
                                                'xls': '<i class="glyphicon glyphicon-file text-success"></i>',
                                                'ppt': '<i class="glyphicon glyphicon-file text-danger"></i>',
                                                'pdf': '<i class="glyphicon glyphicon-file text-danger"></i>',
                                                'zip': '<i class="glyphicon glyphicon-file text-muted"></i>',
                                                'htm': '<i class="glyphicon glyphicon-file text-info"></i>',
                                                'txt': '<i class="glyphicon glyphicon-file text-info"></i>',
                                                'mov': '<i class="glyphicon glyphicon-file text-warning"></i>',
                                                'mp3': '<i class="glyphicon glyphicon-file text-warning"></i>',
                                            },
                                            'previewFileExtSettings': { /** configure the logic for determining icon file extensions*/
                                                'doc': function(ext) {
                                                    return ext.match(/(doc|docx)$/i);
                                                },
                                                'xls': function(ext) {
                                                    return ext.match(/(xls|xlsx)$/i);
                                                },
                                                'ppt': function(ext) {
                                                    return ext.match(/(ppt|pptx)$/i);
                                                },
                                                'pdf': function(ext) {
                                                    return ext.match(/(pdf)$/i);
                                                },
                                                'zip': function(ext) {
                                                    return ext.match(/(zip|rar|tar|gzip|gz|7z)$/i);
                                                },
                                                'htm': function(ext) {
                                                    return ext.match(/(htm|html)$/i);
                                                },
                                                'txt': function(ext) {
                                                    return ext.match(/(txt|ini|csv|java|php|js|css)$/i);
                                                },
                                                'mov': function(ext) {
                                                    return ext.match(/(avi|mpg|mkv|mov|mp4|3gp|webm|wmv)$/i);
                                                },
                                                'mp3': function(ext) {
                                                    return ext.match(/(mp3|wav)$/i);
                                                }
                                            },
                                        }).on('filebeforedelete', function(event, key, data) {
                                            /** This event is triggered on click of the delete button of each initial preview thumbnail file */
                                            //console.log('Key = ' + key);
                                            // console.log('Data = ' + JSON.stringify(data));
                                            return new Promise(function(resolve, reject) {
                                                $.confirm({
                                                    title: eval("app.translations." + app.data.locale + ".title_text"),
                                                    content: eval("app.translations." + app.data.locale + ".are_you_sure_you_want_to_delete_this_file") + '\n' + eval("app.translations." + app.data.locale + ".this_cannot_be_undone") + '\n',
                                                    type: 'red',
                                                    draggable: false,
                                                    backgroundDismiss: 'cancel',
                                                    escapeKey: true,
                                                    animateFromElement: false,
                                                    onAction: function(btnName) {
                                                        $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                                                    },
                                                    buttons: {
                                                        ok: {
                                                            btnClass: 'btn-primary text-white',
                                                            keys: ['enter'],
                                                            text: eval("app.translations." + app.data.locale + ".okay_text"),
                                                            action: function() {
                                                                resolve();
                                                            }
                                                        },
                                                        cancel: {
                                                            text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                            action: function() {
                                                                $.alert(eval("app.translations." + app.data.locale + ".file_deletion_was_aborted"));
                                                            }
                                                        },
                                                    }
                                                });
                                            });
                                        }).on('filepredelete', function(event, key, jqXHR, data) {
                                            /** This event is triggered BEFORE ajax request for deletion is sent to the server of each initial preview thumbnail file */
                                            // console.log('Key = ' + key);
                                            // console.log('Data = ' + JSON.stringify(data));
                                            //console.log('jqXHR =' + JSON.stringify(jqXHR));
                                        }).on('filedeleted', function(event, key, jqXHR, data) {
                                            /** This event is triggered AFTER successful completion of ajax request for deletion of each initial preview thumbnail file */
                                            /** It does not necessarily mean the procedure happen correctly */
                                            //console.log('Key = ' + key);
                                            //console.log('Data = ' + JSON.stringify(data));
                                            //console.log('jqXHR =' + JSON.stringify(jqXHR));
                                            var resultformdata = JSON.stringify(jqXHR.responseJSON);
                                            //console.log(resultformdata);
                                            console.log(jqXHR.responseJSON.success);
                                            console.log(typeof jqXHR.responseJSON.success);
                                            if (jqXHR.responseJSON.success == true) {
                                                $.alert(eval("app.translations." + app.data.locale + ".file_deletion_was_successful"));
                                                /** need to refresh the gendal docs table too */
                                                var generaldocstable = $('#generaldocumentsTable').DataTable();
                                                generaldocstable.ajax.reload(null, false);
                                                generaldocstable.fixedHeader.adjust();
                                            } else {
                                                $.alert(eval("app.translations." + app.data.locale + ".file_deletion_was_unsuccessful"));
                                            }
                                        });

                                        //onclick event open dialogue
                                        $('input.file-caption-name').prop('disabled', true).css('cursor', 'pointer');
                                        $('input.file-caption-name').click(function() {
                                            $('input[type=file]').trigger('click');
                                        });
                                        $('input[type=file]').change(function() {
                                            $('input.file-caption-name').val($(this).val());
                                        });
                                        $('div.file-caption').click(function() {
                                            $('input[type=file]').trigger('click');
                                        });
                                        $('div.file-drop-zone').css('cursor', 'pointer');
                                        $('div.file-drop-zone').click(function() {
                                            $('input[type=file]').trigger('click');
                                        });
                                        $('.kv-file-remove').click(function(event) {
                                            event.stopImmediatePropagation();
                                        });
                                        $('.kv-file-download').click(function(event) {
                                            event.stopImmediatePropagation();
                                        });
                                        $('.file-preview-thumbnails').click(function(event) {
                                            event.stopImmediatePropagation();
                                            console.log('clicked kv-file-thumbnails');
                                        });
                                        $('.kv-fileinput-error').click(function(event) {
                                            event.stopImmediatePropagation();
                                            console.log('clicked kv-file-error');
                                        });
                                        var constantlychecking_if_timer = setInterval(function() {
                                            var dropzone_isvisible_check = $('div.file-drop-zone-title').is(":visible");
                                            if (dropzone_isvisible_check == true) {
                                                $('div.file-drop-zone-title').css('cursor', 'pointer');
                                            } else {
                                                $('div.file-preview-frame').css('cursor', 'pointer');
                                                //$('div.file-actions').hide();
                                            }
                                            var popover_isvisible_check = $('.popover').is(":visible");
                                            if (popover_isvisible_check == true) {
                                                $('.popover').css('z-index', '20000000');
                                            }
                                        }, 500);

                                        var whatarewelookingat = setInterval(function() {
                                            //console.log('ticking');
                                            if (window_focus == true) {
                                                if (been_out !== null) {
                                                    if (clickedtoadd_doc_type == 1) {
                                                        $('select#doc_type_id').css('cursor', 'wait');
                                                        $('select#doc_type_id').prop('disabled', true);
                                                        $('.branch_loader').css('display', 'block');
                                                        app.ajax.json(app.data.urlProfileDocList, data, null, function() {
                                                            documentlist = app.ajax.result.prefix;
                                                            var askingvalue = Object.keys(documentlist).length;
                                                            //console.log('sizeof dropdown='+app.data.howmanydocTypes);
                                                            //console.log('list='+JSON.stringify(documentlist));
                                                            //console.log('sizeof list='+askingvalue);
                                                            if (app.data.howmanydocTypes !== askingvalue) {
                                                                $('select#doc_type_id').empty().html(app.ajax.result.build);
                                                                $('#prefix').val('');
                                                                console.log('updated-list');
                                                                app.data.howmanydocTypes = askingvalue;
                                                            }
                                                            clearInterval(refreshIntervalId);
                                                            //console.log('back here');
                                                            $('.branch_loader').fadeOut('fast');
                                                            $('select#doc_type_id').css('cursor', 'auto');
                                                            $('select#doc_type_id').prop('disabled', false);
                                                        });
                                                        been_out = null;
                                                        //console.log('reset_timer_after_gettinglist_and_now_tracking_if_beenout');
                                                        timer();
                                                        clickedtoadd_doc_type = null;
                                                    }
                                                }
                                                //$('select#doc_type_id').prop('disabled', false);
                                                $('select#doc_type_id').css('cursor', 'auto');
                                            }
                                            if (window_focus == false) {
                                                //console.log('timer-reset');
                                                //$('select#doc_type_id').prop('disabled', true);
                                                $('select#doc_type_id').css('cursor', 'pointer');
                                                been_out = 1;
                                                timer();
                                            };
                                        }, 500);

                                        //pre populate the dropdown list on page ready
                                        var documentlist = [];
                                        if ($('#prefix').val() !== '') {
                                            //console.log('already had value');
                                            $('select#doc_type_id').css('cursor', 'wait').prop('disabled', true);
                                            $('.branch_loader').css('display', 'block');
                                            app.ajax.json(app.data.urlProfileDocList, data, null, function() {
                                                documentlist = app.ajax.result.prefix;
                                                $('.branch_loader').fadeOut('fast');
                                                $('select#doc_type_id').prop('disabled', false).css('cursor', 'auto');
                                            });
                                        } else {
                                            $('select#doc_type_id').prop('disabled', true).css('cursor', 'wait');
                                            $('.branch_loader').css('display', 'block');
                                            app.ajax.json(app.data.urlProfileDocList, data, null, function() {
                                                $('.branch_loader').fadeOut('fast');
                                                $('select#doc_type_id').css('cursor', 'auto').prop('disabled', false);
                                                documentlist = app.ajax.result.prefix;
                                            });
                                        }

                                        //when change doc_type_id then do that
                                        $('select#doc_type_id').change(function() {
                                            var selected = $(this).val();
                                            if (selected == '') {
                                                if ($('select#doc_type_id').val() == 0) {
                                                    $('#prefix').val('');
                                                    $('#upload_doc_reset').prop('disabled', true).prop('readonly', true).hide();
                                                    $('#upload_doc_update').prop('disabled', true).prop('readonly', true).hide();

                                                    $('#issue_date').prop('disabled', true).prop('readonly', true);
                                                    $('#expiration_date').prop('disabled', true).prop('readonly', true);
                                                    $('#doc_number').prop('disabled', true).prop('readonly', true);
                                                    $('#issue_place').prop('disabled', true).prop('readonly', true);
                                                }
                                            } else {
                                                $.each(documentlist, function(index, value) {
                                                    if (value.id == selected) {
                                                        /** here we want to preserve the doc_numbering but if the type changes to change to that prefix */
                                                        var exsiting_prefix = $('#prefix').val();
                                                        //console.log(exsiting_prefix);
                                                        var res = exsiting_prefix.split("_");

                                                        var newfilename = value.prefix + '_' + userId + '_' + res[2];
                                                        $('#prefix').val(newfilename);
                                                        $('.file-caption-name').text(newfilename);
                                                        $('input.file-caption-name').prop('title', newfilename).text(newfilename);
                                                        $('#prefix').closest('td').removeClass('has-error');
                                                        $('#prefix').next('.help-block').css('display', 'none');
                                                    }
                                                });
                                                $('#upload_doc_reset').prop('disabled', false).prop('readonly', false).css('display', 'inline-block');
                                                $('#upload_doc_update').prop('disabled', false).prop('readonly', false).css('display', 'inline-block');

                                                $('#issue_date').prop('disabled', false).prop('readonly', false);
                                                $('#expiration_date').prop('disabled', false).prop('readonly', false);
                                                $('#doc_number').prop('disabled', false).prop('readonly', false);
                                                $('#issue_place').prop('disabled', false).prop('readonly', false);
                                            }
                                        });
                                    });

                                    //if select is original cannot be is copy..
                                    $('#is_original').click(function() {
                                        var is_original_checked = $('#is_original:checkbox:checked').length > 0;
                                        if (is_original_checked == true) {
                                            $('#is_copy').prop('checked', false);
                                            $('#is_original_hidden').val(1);
                                            $('#is_copy_hidden').val(0);
                                        } else {
                                            $('#is_copy').prop('checked', true);
                                            $('#is_original_hidden').val(0);
                                            $('#is_copy_hidden').val(1);
                                        }
                                    });
                                    $('#is_copy').click(function() {
                                        var is_copy_checked = $('#is_copy:checkbox:checked').length > 0;
                                        if (is_copy_checked == true) {
                                            $('#is_original').prop('checked', false);
                                            $('#is_original_hidden').val(0);
                                            $('#is_copy_hidden').val(1);
                                        } else {
                                            $('#is_original').prop('checked', true);
                                            $('#is_original_hidden').val(1);
                                            $('#is_copy_hidden').val(0);
                                        }
                                    });
                                    $('#is_legalised').click(function() {
                                        var is_legalised_checked = $('#is_legalised:checkbox:checked').length > 0;
                                        if (is_legalised_checked == true) {
                                            $('#is_legalised_hidden').val(1);
                                        } else {
                                            $('#is_legalised_hidden').val(0);
                                        }
                                    });
                                    $('#is_notarised').click(function() {
                                        var is_notarised_checked = $('#is_notarised:checkbox:checked').length > 0;
                                        if (is_notarised_checked == true) {
                                            $('#is_notarised_hidden').val(1);
                                        } else {
                                            $('#is_notarised_hidden').val(0);
                                        }
                                    });

                                    //as soon as the ajax page is loaded then it will save the existing values to a session store
                                    var user = $('#user').val();
                                    var doc_type_id = $('#doc_type_id').val();
                                    var prefix = $('#prefix').val();
                                    var doc_no = $('#doc_no').val();
                                    var file = $('input[data-ident=edit_doc_file]').val();

                                    var issue_date = $('#issue_date').val();
                                    var expiration_date = $('#expiration_date').val();
                                    var issue_place = $('#issue_place').val();

                                    var is_original = $('#is_original:checkbox:checked').length > 0; //checked = true unchecked = false
                                    var is_copy = $('#is_copy:checkbox:checked').length > 0; //checked = true unchecked = false
                                    var is_legalised = $('#is_legalised:checkbox:checked').length > 0; //checked = true unchecked = false
                                    var is_notarised = $('#is_notarised:checkbox:checked').length > 0; //checked = true unchecked = false

                                    var is_original_hidden = $('#is_original_hidden').val();
                                    var is_copy_hidden = $('#is_copy_hidden').val();
                                    var is_legalised_hidden = $('#is_legalised_hidden').val();
                                    var is_notarised_hidden = $('#is_notarised_hidden').val();
                                    //var limitation = $('#limitation').val();

                                    var url_user_id = app.data.user_id;
                                    var currentdate = new Date();
                                    var datetime = "Last Sync: " + currentdate.getDate() +
                                        "/" + (currentdate.getMonth() + 1) +
                                        "/" + currentdate.getFullYear() +
                                        " @ " + currentdate.getHours() +
                                        ":" + currentdate.getMinutes() +
                                        ":" + currentdate.getSeconds();

                                    var edit_document_previous_data = [];
                                    edit_document_previous_data['edit_document_previous_data'] = {
                                        "whenwasset": datetime,
                                        "user_id": url_user_id,

                                        "edit_document_user": user,
                                        "edit_document_doc_type_id": doc_type_id,
                                        "edit_document_prefix": prefix,
                                        "edit_document_doc_no": doc_no,
                                        "edit_document_file": file,

                                        "edit_document_issue_date": issue_date,
                                        "edit_document_expiration_date": expiration_date,
                                        "edit_document_issue_place": issue_place,

                                        "edit_document_is_original": is_original,
                                        "edit_document_is_copy": is_copy,
                                        "edit_document_is_legalised": is_legalised,
                                        "edit_document_is_notarised": is_notarised,

                                        "edit_document_is_original_hidden": is_original_hidden,
                                        "edit_document_is_copy_hidden": is_copy_hidden,
                                        "edit_document_is_legalised_hidden": is_legalised_hidden,
                                        "edit_document_is_notarised_hidden": is_notarised_hidden
                                    };
                                    sessionStorage.setItem('Br24_' + app.env() + '_' + url_user_id + '_edit_uploaddocumentinfo_previous_data', JSON.stringify(edit_document_previous_data['edit_document_previous_data']));


                                    if (app.data.fk_doc_type_id == 2 || app.data.fk_doc_type_id == 3 || app.data.fk_doc_type_id == 4 || app.data.fk_doc_type_id == 5 || app.data.fk_doc_type_id == 6 || app.data.fk_doc_type_id == 7) {
                                        $('#issue_date').prop('disabled', true).prop('readonly', true);
                                        $('#expiration_date').prop('disabled', true).prop('readonly', true);
                                    }

                                    $('#cb_edit_document_details_form').areYouSure();
                                    $('#cb_edit_document_details_form').on('change', 'select', function() {
                                        $("#upload_doc_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#upload_doc_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of select tags

                                    $('#cb_edit_document_details_form').on('change keypress', 'input', function() {
                                        $("#upload_doc_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#upload_doc_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of input: the change event take care of input of type "hidden" also

                                    $("#upload_doc_reset").click(function() {
                                        var button_selector = $('#upload_doc_reset, #upload_doc_update');
                                        //hide all the buttons
                                        button_selector.prop('disabled', true).css('display', 'none');

                                        var previous_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_' + url_user_id + '_edit_uploaddocumentinfo_previous_data'));

                                        $('#user').val(previous_load['edit_document_user']);
                                        $('#doc_type_id').val(previous_load['edit_document_doc_type_id']);
                                        $('#prefix').val(previous_load['edit_document_prefix']);
                                        $('#doc_no').val(previous_load['edit_document_doc_no']);

                                        $('#issue_date').val(previous_load['edit_document_issue_date']);
                                        $('#expiration_date').val(previous_load['edit_document_expiration_date']);
                                        $('#issue_place').val(previous_load['edit_document_issue_place']);

                                        $('#is_original:checkbox:checked').val(previous_load['edit_document_is_original']);
                                        $('#is_copy:checkbox:checked').val(previous_load['edit_document_is_copy']);
                                        $('#is_legalised:checkbox:checked').val(previous_load['edit_document_is_legalised']);
                                        $('#is_notarised:checkbox:checked').val(previous_load['edit_document_is_notarised']);


                                        if (previous_load['edit_document_is_original_hidden'] == true) {
                                            $('#is_original').prop('checked', true);
                                            $('#is_original_hidden').val(1);
                                        } else {
                                            $('#is_original').prop('checked', false);
                                            $('#is_original_hidden').val(0);
                                        }
                                        if (previous_load['edit_document_is_copy_hidden'] == true) {
                                            $('#is_copy').prop('checked', true);
                                            $('#is_copy_hidden').val(1);
                                        } else {
                                            $('#is_copy').prop('checked', false);
                                            $('#is_copy_hidden').val(0);
                                        }
                                        if (previous_load['edit_document_is_legalised_hidden'] == true) {
                                            $('#is_legalised').prop('checked', true);
                                            $('#is_legalised_hidden').val(1);
                                        } else {
                                            $('#is_legalised').prop('checked', false);
                                            $('#is_legalised_hidden').val(0);
                                        }
                                        if (previous_load['edit_document_is_notarised_hidden'] == true) {
                                            $('#is_notarised').prop('checked', true);
                                            $('#is_notarised_hidden').val(1);
                                        } else {
                                            $('#is_notarised').prop('checked', false);
                                            $('#is_notarised_hidden').val(0);
                                        }


                                        var dropzone_isvisible_check = null;
                                        var clickedtoadd_doc_type = null;
                                        if (previous_load['edit_document_file'] == '') {
                                            $(document).ready(function() {
                                                // console.log('app.data.dd_route=' + app.data.dd_route);
                                                // console.log('app.data.dd_route_delete=' + app.data.dd_route_delete);
                                                // console.log('app.data.dd_size=' + app.data.dd_size);
                                                // console.log('app.data.dd_prefix=' + app.data.dd_prefix);
                                                // console.log('app.data.dd_type=' + app.data.dd_type);
                                                var url1 = app.data.dd_route;
                                                var url2 = app.data.dd_route_delete;
                                                $('input[data-ident=edit_doc_file]').fileinput('clear').fileinput('destroy').fileinput({
                                                    'language': app.data.locale,
                                                    'showUpload': false,
                                                    'previewFileType': 'any',
                                                    'showClose': false,
                                                    'showCaption': true,
                                                    'showBrowse': true,
                                                    'browseClass': 'btn btn-primary btn-file btn-fileinput-k',
                                                    'browseIcon': '<i class="glyphicon glyphicon-folder-open"></i> ',
                                                    'showUploadedThumbs': true,
                                                    'showPreview': true,
                                                    'showRemove': true,
                                                    'initialPreview': url1,
                                                    'initialPreviewFileType': 'image',
                                                    'initialPreviewAsData': true,
                                                    'initialPreviewConfig': [
                                                        { type: app.data.dd_type, caption: app.data.dd_prefix, downloadUrl: url1, url: url2, size: app.data.dd_size, width: "120px", key: 1 }
                                                    ],
                                                    'initialPreviewShowDelete': true,
                                                    'overwriteInitial': true,
                                                    'maxFileSize': 25000,
                                                    'initialCaption': app.data.dd_prefix,
                                                    'layoutTemplates': {
                                                        'size': ' <samp>({sizeText})</samp>',
                                                        'footer': '<div class="file-thumbnail-footer">\n' +
                                                            '<div class="file-footer-caption" title="{caption}">\n' +
                                                            '<div class="file-caption-info">{caption}</div>\n' +
                                                            '<div class="file-size-info">{size}</div>\n' +
                                                            '</div>\n' +
                                                            '{progress}\n{indicator}\n{actions}\n' +
                                                            '</div>',
                                                        'actions': '<div class="file-actions">\n' +
                                                            '<div class="file-footer-buttons">\n' +
                                                            '{download} {upload} {delete} {other}' +
                                                            '</div>\n' +
                                                            '</div>\n' +
                                                            '<div class="clearfix"></div>',
                                                    },
                                                    'preferIconicPreview': false,
                                                    /** this will force thumbnails to display icons for following file extensions*/
                                                    'previewFileIconSettings': { /** configure your icon file extensions*/
                                                        'doc': '<i class="glyphicon glyphicon-file text-primary"></i>',
                                                        'xls': '<i class="glyphicon glyphicon-file text-success"></i>',
                                                        'ppt': '<i class="glyphicon glyphicon-file text-danger"></i>',
                                                        'pdf': '<i class="glyphicon glyphicon-file text-danger"></i>',
                                                        'zip': '<i class="glyphicon glyphicon-file text-muted"></i>',
                                                        'htm': '<i class="glyphicon glyphicon-file text-info"></i>',
                                                        'txt': '<i class="glyphicon glyphicon-file text-info"></i>',
                                                        'mov': '<i class="glyphicon glyphicon-file text-warning"></i>',
                                                        'mp3': '<i class="glyphicon glyphicon-file text-warning"></i>',
                                                    },
                                                    'previewFileExtSettings': { /** configure the logic for determining icon file extensions*/
                                                        'doc': function(ext) {
                                                            return ext.match(/(doc|docx)$/i);
                                                        },
                                                        'xls': function(ext) {
                                                            return ext.match(/(xls|xlsx)$/i);
                                                        },
                                                        'ppt': function(ext) {
                                                            return ext.match(/(ppt|pptx)$/i);
                                                        },
                                                        'pdf': function(ext) {
                                                            return ext.match(/(pdf)$/i);
                                                        },
                                                        'zip': function(ext) {
                                                            return ext.match(/(zip|rar|tar|gzip|gz|7z)$/i);
                                                        },
                                                        'htm': function(ext) {
                                                            return ext.match(/(htm|html)$/i);
                                                        },
                                                        'txt': function(ext) {
                                                            return ext.match(/(txt|ini|csv|java|php|js|css)$/i);
                                                        },
                                                        'mov': function(ext) {
                                                            return ext.match(/(avi|mpg|mkv|mov|mp4|3gp|webm|wmv)$/i);
                                                        },
                                                        'mp3': function(ext) {
                                                            return ext.match(/(mp3|wav)$/i);
                                                        }
                                                    },
                                                }).on('filebeforedelete', function(event, key, data) {
                                                    /** This event is triggered on click of the delete button of each initial preview thumbnail file */
                                                    //console.log('Key = ' + key);
                                                    // console.log('Data = ' + JSON.stringify(data));
                                                    return new Promise(function(resolve, reject) {
                                                        $.confirm({
                                                            title: eval("app.translations." + app.data.locale + ".title_text"),
                                                            content: eval("app.translations." + app.data.locale + ".are_you_sure_you_want_to_delete_this_file") + '\n' + eval("app.translations." + app.data.locale + ".this_cannot_be_undone") + '\n',
                                                            type: 'red',
                                                            draggable: false,
                                                            backgroundDismiss: 'cancel',
                                                            escapeKey: true,
                                                            animateFromElement: false,
                                                            onAction: function(btnName) {
                                                                $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                                                            },
                                                            buttons: {
                                                                ok: {
                                                                    btnClass: 'btn-primary text-white',
                                                                    keys: ['enter'],
                                                                    text: eval("app.translations." + app.data.locale + ".okay_text"),
                                                                    action: function() {
                                                                        resolve();
                                                                    }
                                                                },
                                                                cancel: {
                                                                    text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                                    action: function() {
                                                                        $.alert(eval("app.translations." + app.data.locale + ".file_deletion_was_aborted"));
                                                                    }
                                                                },
                                                            }
                                                        });
                                                    });
                                                }).on('filepredelete', function(event, key, jqXHR, data) {
                                                    /** This event is triggered BEFORE ajax request for deletion is sent to the server of each initial preview thumbnail file */
                                                    // console.log('Key = ' + key);
                                                    // console.log('Data = ' + JSON.stringify(data));
                                                    //console.log('jqXHR =' + JSON.stringify(jqXHR));
                                                }).on('filedeleted', function(event, key, jqXHR, data) {
                                                    /** This event is triggered AFTER successful completion of ajax request for deletion of each initial preview thumbnail file */
                                                    /** It does not necessarily mean the procedure happen correctly */
                                                    //console.log('Key = ' + key);
                                                    //console.log('Data = ' + JSON.stringify(data));
                                                    //console.log('jqXHR =' + JSON.stringify(jqXHR));
                                                    var resultformdata = JSON.stringify(jqXHR.responseJSON);
                                                    //console.log(resultformdata);
                                                    if (jqXHR.responseJSON.success == true) {
                                                        $.alert(eval("app.translations." + app.data.locale + ".file_deletion_was_successful"));
                                                        var generaldocstable = $('#generaldocumentsTable').DataTable();
                                                        generaldocstable.ajax.reload(null, false);
                                                        generaldocstable.fixedHeader.adjust();
                                                    } else {
                                                        $.alert(eval("app.translations." + app.data.locale + ".file_deletion_was_unsuccessful"));
                                                    }
                                                });

                                                //onclick event open dialogue
                                                $('input.file-caption-name').prop('disabled', true).css('cursor', 'pointer');
                                                $('input.file-caption-name').click(function() {
                                                    $('input[type=file]').trigger('click');
                                                });
                                                $('input[type=file]').change(function() {
                                                    $('input.file-caption-name').val($(this).val());
                                                });
                                                $('div.file-caption').click(function() {
                                                    $('input[type=file]').trigger('click');
                                                });
                                                $('div.file-drop-zone').css('cursor', 'pointer');
                                                $('div.file-drop-zone').click(function() {
                                                    $('input[type=file]').trigger('click');
                                                });
                                                $('.kv-file-remove').click(function(event) {
                                                    event.stopImmediatePropagation();
                                                });
                                                $('.kv-file-download').click(function(event) {
                                                    event.stopImmediatePropagation();
                                                });
                                                $('.file-preview-thumbnails').click(function(event) {
                                                    event.stopImmediatePropagation();
                                                    console.log('clicked kv-file-thumbnails');
                                                });
                                                $('.kv-fileinput-error').click(function(event) {
                                                    event.stopImmediatePropagation();
                                                    console.log('clicked kv-file-error');
                                                });
                                                var constantlychecking_if_timer = setInterval(function() {
                                                    var dropzone_isvisible_check = $('div.file-drop-zone-title').is(":visible");
                                                    if (dropzone_isvisible_check == true) {
                                                        $('div.file-drop-zone-title').css('cursor', 'pointer');
                                                    } else {
                                                        $('div.file-preview-frame').css('cursor', 'pointer');
                                                        //$('div.file-actions').hide();
                                                    }
                                                    var popover_isvisible_check = $('.popover').is(":visible");
                                                    if (popover_isvisible_check == true) {
                                                        $('.popover').css('z-index', '20000000');
                                                    }
                                                }, 500);

                                                var whatarewelookingat = setInterval(function() {
                                                    //console.log('ticking');
                                                    if (window_focus == true) {
                                                        if (been_out !== null) {
                                                            if (clickedtoadd_doc_type == 1) {
                                                                $('select#doc_type_id').css('cursor', 'wait');
                                                                $('select#doc_type_id').prop('disabled', true);
                                                                $('.branch_loader').css('display', 'block');
                                                                app.ajax.json(app.data.urlProfileDocList, data, null, function() {
                                                                    documentlist = app.ajax.result.prefix;
                                                                    var askingvalue = Object.keys(documentlist).length;
                                                                    //console.log('sizeof dropdown='+app.data.howmanydocTypes);
                                                                    //console.log('list='+JSON.stringify(documentlist));
                                                                    //console.log('sizeof list='+askingvalue);
                                                                    if (app.data.howmanydocTypes !== askingvalue) {
                                                                        $('select#doc_type_id').empty().html(app.ajax.result.build);
                                                                        $('#prefix').val('');
                                                                        console.log('updated-list');
                                                                        app.data.howmanydocTypes = askingvalue;
                                                                    }
                                                                    clearInterval(refreshIntervalId);
                                                                    //console.log('back here');
                                                                    $('.branch_loader').fadeOut('fast');
                                                                    $('select#doc_type_id').css('cursor', 'auto');
                                                                    $('select#doc_type_id').prop('disabled', false);
                                                                });
                                                                been_out = null;
                                                                //console.log('reset_timer_after_gettinglist_and_now_tracking_if_beenout');
                                                                timer();
                                                                clickedtoadd_doc_type = null;
                                                            }
                                                        }
                                                        //$('select#doc_type_id').prop('disabled', false);
                                                        $('select#doc_type_id').css('cursor', 'auto');
                                                    }
                                                    if (window_focus == false) {
                                                        //console.log('timer-reset');
                                                        //$('select#doc_type_id').prop('disabled', true);
                                                        $('select#doc_type_id').css('cursor', 'pointer');
                                                        been_out = 1;
                                                        timer();
                                                    };
                                                }, 500);

                                                //pre populate the dropdown list on page ready
                                                var documentlist = [];
                                                if ($('#prefix').val() !== '') {
                                                    //console.log('already had value');
                                                    $('select#doc_type_id').prop('disabled', true).css('cursor', 'wait');
                                                    $('.branch_loader').css('display', 'block');
                                                    app.ajax.json(app.data.urlProfileDocList, data, null, function() {
                                                        $('.branch_loader').fadeOut('fast');
                                                        documentlist = app.ajax.result.prefix;
                                                        $('select#doc_type_id').css('cursor', 'auto').prop('disabled', false);
                                                    });
                                                } else {
                                                    $('select#doc_type_id').css('cursor', 'wait').prop('disabled', true);
                                                    $('.branch_loader').css('display', 'block');
                                                    app.ajax.json(app.data.urlProfileDocList, data, null, function() {
                                                        documentlist = app.ajax.result.prefix;
                                                        $('.branch_loader').fadeOut('fast');
                                                        $('select#doc_type_id').prop('disabled', false).css('cursor', 'auto');
                                                    });
                                                }

                                                //when change doc_type_id then do that
                                                $('select#doc_type_id').change(function() {
                                                    var selected = $(this).val();
                                                    if (selected == '') {
                                                        if ($('select#doc_type_id').val() == 0) {
                                                            $('#prefix').val('');
                                                            $('#upload_doc_reset').prop('disabled', true).prop('readonly', true).hide();
                                                            $('#upload_doc_update').prop('disabled', true).prop('readonly', true).hide();

                                                            $('#issue_date').prop('disabled', true).prop('readonly', true);
                                                            $('#expiration_date').prop('disabled', true).prop('readonly', true);
                                                        }
                                                    } else {
                                                        $.each(documentlist, function(index, value) {
                                                            if (value.id == selected) {
                                                                /** here we want to preserve the doc_numbering but if the type changes to change to that prefix */
                                                                var exsiting_prefix = $('#prefix').val();
                                                                //console.log(exsiting_prefix);
                                                                var res = exsiting_prefix.split("_");

                                                                var newfilename = value.prefix + '_' + userId + '_' + res[2];
                                                                $('#prefix').val(newfilename);
                                                                $('.file-caption-name').text(newfilename);
                                                                $('input.file-caption-name').prop('title', newfilename).text(newfilename);
                                                                $('#prefix').closest('td').removeClass('has-error');
                                                                $('#prefix').next('.help-block').css('display', 'none');
                                                            }
                                                        });
                                                        $('#upload_doc_reset').prop('disabled', false).prop('readonly', false).css('display', 'inline-block');
                                                        $('#upload_doc_update').prop('disabled', false).prop('readonly', false).css('display', 'inline-block');

                                                        $('#issue_date').prop('disabled', false).prop('readonly', false);
                                                        $('#expiration_date').prop('disabled', false).prop('readonly', false);
                                                    }
                                                });
                                            });
                                        }

                                        $('.has-error').removeClass('has-error');
                                        $('.help-block').css('display', 'none');
                                        $('#cb_edit_document_details_form').trigger('reinitialize.areYouSure');
                                    });

                                    $("#upload_doc_update").click(function(e) {
                                        e.preventDefault();
                                        $('.alert_warning').css('display', 'none');
                                        $('.alert_success').css('display', 'none');

                                        $('#issue_date').prop('disabled', false).prop('readonly', true);
                                        $('#expiration_date').prop('disabled', false).prop('readonly', true);

                                        var user = $('#user').val();
                                        var doc_type_id = $('#doc_type_id').val();
                                        var prefix = $('#prefix').val();
                                        var doc_no = $('#doc_no').val();
                                        var file = $('input[data-ident=edit_doc_file]').val();

                                        var issue_date = $('#issue_date').val();
                                        var expiration_date = $('#expiration_date').val();
                                        var issue_place = $('#issue_place').val();

                                        var is_original = $('#is_original:checkbox:checked').length > 0; //checked = true unchecked = false
                                        var is_copy = $('#is_copy:checkbox:checked').length > 0; //checked = true unchecked = false
                                        var is_legalised = $('#is_legalised:checkbox:checked').length > 0; //checked = true unchecked = false
                                        var is_notarised = $('#is_notarised:checkbox:checked').length > 0; //checked = true unchecked = false

                                        var is_original_hidden = $('#is_original_hidden').val();
                                        var is_copy_hidden = $('#is_copy_hidden').val();
                                        var is_legalised_hidden = $('#is_legalised_hidden').val();
                                        var is_notarised_hidden = $('#is_notarised_hidden').val();

                                        var url_user_id = app.data.user_id;
                                        var currentdate = new Date();
                                        var datetime = "Last Sync: " + currentdate.getDate() +
                                            "/" + (currentdate.getMonth() + 1) +
                                            "/" + currentdate.getFullYear() +
                                            " @ " + currentdate.getHours() +
                                            ":" + currentdate.getMinutes() +
                                            ":" + currentdate.getSeconds();

                                        var edit_document_submitted_data = [];
                                        edit_document_submitted_data['edit_document_submitted_data'] = {
                                            "whenwasset": datetime,
                                            "user_id": url_user_id,

                                            "edit_document_user": user,
                                            "edit_document_doc_type_id": doc_type_id,
                                            "edit_document_prefix": prefix,
                                            "edit_document_doc_no": doc_no,
                                            "edit_document_file": file,

                                            "edit_document_issue_date": issue_date,
                                            "edit_document_expiration_date": expiration_date,
                                            "edit_document_issue_place": issue_place,

                                            "edit_document_is_original": is_original,
                                            "edit_document_is_copy": is_copy,
                                            "edit_document_is_legalised": is_legalised,
                                            "edit_document_is_notarised": is_notarised,

                                            "edit_document_is_original_hidden": is_original_hidden,
                                            "edit_document_is_copy_hidden": is_copy_hidden,
                                            "edit_document_is_legalised_hidden": is_legalised_hidden,
                                            "edit_document_is_notarised_hidden": is_notarised_hidden
                                        };
                                        sessionStorage.setItem('Br24_' + app.env() + '_' + url_user_id + '_edit_uploaddocumentinfo_submitted_data', JSON.stringify(edit_document_submitted_data['edit_document_submitted_data']));

                                        var formData = new FormData($('#cb_edit_document_details_form')[0]);

                                        NProgress.configure({ parent: '#cboxTitle', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                        NProgress.start();

                                        $('.cb_loader').css('display', 'block').css('cursor', 'wait');
                                        $('#cb_top').addClass('nprogress-busy').css('pointer-events', 'none');
                                        //app.util.fullscreenloading_start();

                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $("#edit_document_details_table").data("token") } });
                                        app.ajax.formdata(app.data.employee_edit_employee_document, formData, null, function() {
                                            //console.log(app.ajax.resultformdata);
                                            success_ajax_then_refresh = app.ajax.resultformdata.success;

                                            NProgress.done();
                                            $('.cb_loader').css('display', 'none').css('cursor', 'auto');
                                            $('#cb_top').removeClass('nprogress-busy').css('pointer-events', 'auto');

                                            if (app.ajax.resultformdata.success == true) {
                                                $('#cboxLoadedContent').css('background-color', '#4CAF50');
                                                $('.onSuccess_makeGreen').css('background-color', '#4CAF50');
                                                $('.ibox-tool-userid').css('color', 'white');
                                                $('#cb_edit_document_details_form').css('display', 'none');
                                                close_colorbox_timer();
                                                $(document.body).css('pointer-events', 'none');
                                                app.util.fullscreenloading_start();
                                            } else {
                                                $('.has-error').removeClass('has-error');
                                                $('.help-block').detach();

                                                $.each(app.ajax.resultformdata.errors, function(idx, val) {
                                                    app_permissions.route_permission.profile.foreach_handle_error_display(idx, val);
                                                });

                                                if (isEmpty(app.ajax.resultformdata.errors)) {
                                                    // Object is empty (Would return true in this example)
                                                    //console.log('object_is_empty');
                                                } else {
                                                    //console.log('object_is_not_empty');
                                                    // Object is NOT empty
                                                    var first_error_offset = $(".has-error:visible:first").offset().top - $(".has-error:visible:first").offsetParent().offset().top;
                                                    var top_padding = 80;
                                                    var where_it_should_scroll_to_on_error = $('#cboxLoadedContent').scrollTop() - Math.abs(first_error_offset) - top_padding;
                                                    $('#cboxLoadedContent').animate({
                                                        scrollTop: where_it_should_scroll_to_on_error
                                                    }, 1000);
                                                }


                                                var indentifyiferrorsonpage = $('.has-error').length > 0;
                                                //change the view to reflect errors have been changed and what errors remain to be fixed
                                                if (indentifyiferrorsonpage == true || indentifyiferrorsonpage == false) {
                                                    $('#user_id').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #user_id');
                                                    });
                                                    $('#doc_type_id').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #doc_type_id');
                                                    });
                                                    $('#prefix').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #prefix');
                                                    });
                                                    $('#doc_no').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #doc_no');
                                                    });
                                                    $('#file').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $('.file-input').parent('.form-group').removeClass('has-error');
                                                        $('.file-caption').find('.help-block').remove();
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #file');
                                                    });
                                                    $('#issue_date').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #issue_date');
                                                    });
                                                    $('#expiration_date').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #expiration_date');
                                                    });
                                                    $('#issue_place').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #issue_place');
                                                    });
                                                    $('#limitation_limited').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #limitation_limited');
                                                    });
                                                    $('#limitation_unlimited').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #limitation_unlimited');
                                                    });
                                                    $('#user_id').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #user_id');
                                                    });
                                                    $('#is_original_have').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #is_original_have');
                                                    });
                                                    $('#is_copy_have').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #is_copy_have');
                                                    });
                                                    $('#is_legalised_have').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #is_legalised_have');
                                                    });
                                                    $('#is_notarised_have').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #is_notarised_have');
                                                    });
                                                    $('#limitation_have').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #limitation_have');
                                                    });
                                                }
                                            }
                                        });
                                    });

                                    $('#cb_edit_document_details_form').on('keyup keypress', function(e) {
                                        var keyCode = e.keyCode || e.which;
                                        if (keyCode === 13) {
                                            e.preventDefault();
                                            return false;
                                        }
                                    });

                                    $('#cboxOverlay').off('click').on('click', function(event) {
                                        //console.log('clickedousideofcolorbox');
                                        var identifychanges = $('#cb_edit_document_details_form').hasClass('dirty');
                                        //console.log('here===' + identifychanges);
                                        if (identifychanges == true) {
                                            $.confirm({
                                                title: eval("app.translations." + app.data.locale + ".title_text"),
                                                content: eval("app.translations." + app.data.locale + ".you_have_unsaved_changes") + '\n' + eval("app.translations." + app.data.locale + ".do_you_want_to_discard_those_changes") + '\n',
                                                type: 'red',
                                                draggable: false,
                                                backgroundDismiss: 'cancel',
                                                escapeKey: true,
                                                animateFromElement: false,
                                                onAction: function(btnName) {
                                                    $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                                                },
                                                buttons: {
                                                    ok: {
                                                        btnClass: 'btn-primary text-white',
                                                        keys: ['enter'],
                                                        text: eval("app.translations." + app.data.locale + ".okay_text"),
                                                        action: function() {
                                                            event.stopPropagation();
                                                            $("#cb_edit_doc.ajax").colorbox.close();
                                                            $('#cb_edit_document_details_form').trigger('reinitialize.areYouSure');
                                                        }
                                                    },
                                                    cancel: {
                                                        text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                        action: function() {

                                                            return false;
                                                        }
                                                    },
                                                }
                                            });
                                        } else {
                                            $("#cb_edit_doc.ajax").colorbox.close();
                                            $('#cb_edit_document_details_form').trigger('reinitialize.areYouSure');
                                        }
                                    });
                                },
                                onCleanup: function() {
                                    //console.log('onCleanup: colorbox has begun the close process');
                                    if (success_ajax_then_refresh == true) {
                                        app.util.fullscreenloading_start();
                                    }
                                },
                                onClosed: function() {
                                    //console.log('onClosed: colorbox has completely closed');
                                    currentColorBox = '';
                                    $('.modal-backdrop').remove();
                                    $('body').css('cursor', 'default');
                                    if (success_ajax_then_refresh == true) {
                                        //var employeerolepermissiontable = $('#employeerolepermissionTable').DataTable();
                                        //var rolepositiontable = $('#rolepositionTable').DataTable();
                                        //var permissionstable = $('#permissionsTable').DataTable();
                                        window.employeerolepermissiontable.ajax.reload(null, false);
                                        window.rolepositiontable.ajax.reload(null, false);
                                        window.permissionstable.ajax.reload(null, false);
                                        app.util.fullscreenloading_end();
                                        window.employeerolepermissiontable.fixedHeader.adjust();
                                        window.rolepositiontable.fixedHeader.adjust();
                                        window.permissionstable.fixedHeader.adjust();
                                    }

                                    NProgress.configure({ parent: '#progress-bar-parent', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                    clearInterval(refreshIntervalId); //stop the timer called refreshIntervalId
                                    clearInterval(close_colorbox_refreshIntervalId);
                                    //app.util.fullscreenloading_end();
                                },
                            });

                            $("a[name*='delete_permission_']").on('click', function(event) {
                                event.preventDefault();
                                var clicked_href = $(this).attr('href');

                                $.confirm({
                                    title: eval("app.translations." + app.data.locale + ".title_text"),
                                    content: eval("app.translations." + app.data.locale + ".are_you_sure_you_want_to_delete_this_record") + "\n" + eval("app.translations." + app.data.locale + ".this_cannot_be_undone") + "?\n",
                                    type: 'red',
                                    draggable: false,
                                    backgroundDismiss: 'cancel',
                                    escapeKey: true,
                                    animateFromElement: false,
                                    onAction: function(btnName) {
                                        $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                                    },
                                    buttons: {
                                        ok: {
                                            btnClass: 'btn-primary text-white',
                                            keys: ['enter'],
                                            text: eval("app.translations." + app.data.locale + ".okay_text"),
                                            action: function() {
                                                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                                app.util.nprogressinit();
                                                var ajaxdata = {};
                                                app.ajax.jsonGET(clicked_href, ajaxdata, null, function() {
                                                    //console.log(app.ajax.result);
                                                    if (app.ajax.result.success == true) {
                                                        window.permissionstable.ajax.reload(null, false);
                                                        app.util.fullscreenloading_end();
                                                        window.permissionstable.fixedHeader.adjust();
                                                        app.util.nprogressdone();
                                                    } else {
                                                        app.util.nprogressdone();
                                                    }
                                                });
                                            }
                                        },
                                        cancel: {
                                            text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                            action: function() {
                                                $.alert(eval("app.translations." + app.data.locale + ".permission_record_deletion_was_aborted"));
                                            }
                                        },
                                    }
                                });
                            });
                        });
                    }
                });
            },
            handlefixedheaderpinning: function() {
                var scrollTimer;
                var resizeTimer;
                var remembering_mainbody_padding = '';
                $(window).scroll(function() {
                    clearTimeout(scrollTimer);
                    scrollTimer = setTimeout(function() {
                        var mainbody_padding = $('.visitor').outerHeight(true);
                        if (remembering_mainbody_padding != mainbody_padding) {
                            window.rolepositiontable.fixedHeader.headerOffset(mainbody_padding);
                            window.rolepositiontable.fixedHeader.adjust();
                            //window.permissionstable.fixedHeader.headerOffset(mainbody_padding);
                            //window.permissionstable.fixedHeader.adjust();
                            remembering_mainbody_padding = mainbody_padding;
                        }
                    }, 250);
                });
                $(window).resize(function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        var mainbody_padding = $('.visitor').outerHeight(true);
                        if (remembering_mainbody_padding != mainbody_padding) {
                            window.rolepositiontable.fixedHeader.headerOffset(mainbody_padding);
                            window.rolepositiontable.fixedHeader.adjust();
                            //window.permissionstable.fixedHeader.headerOffset(mainbody_padding);
                            //window.permissionstable.fixedHeader.adjust();
                            remembering_mainbody_padding = mainbody_padding;
                        }
                    }, 250);
                });
            },
            routes_fixed_header_scroll_with_scroll_handler: function() {
                var numbertoremember = 0;
                var fixed_header_shift = 35;
                var horizontal = null;
                var vertical = null;
                var whatisthis = null;
                var whatisthis2 = null;
                var numbertouse = null;

                $("#rolepositionInfo .table-responsive").on("scroll", function(e) {
                    //console.log('rolepositionInfo table-responsive is scrolling');
                    horizontal = e.currentTarget.scrollLeft;
                    vertical = e.currentTarget.scrollTop;
                    numbertoremember = horizontal * -1;
                    whatisthis = $("#rolepositionInfo .table-responsive").scrollLeft();
                    whatisthis2 = $("#rolepositionInfo .table-responsive").width();
                    numbertouse = whatisthis2 + whatisthis - 1;
                    //console.log(numbertouse.toFixed(2));
                    $('.controlsfortable').css({
                        'padding-left': horizontal,
                        'width': numbertouse.toFixed(2)
                    });
                    if (window.location.href.indexOf("route_permissions") > -1) {
                        fixed_header_shift = 25;
                    }
                    $(".fixedHeader-floating[aria-describedby='rolepositionTable_info']").css({
                        'left': numbertoremember + fixed_header_shift
                    });
                    window.rolepositiontable.fixedHeader.adjust();
                });

                $(window).resize(function() {
                    // whatisthis = $(".DTFC_ScrollWrapper").scrollLeft();
                    // whatisthis2 = $(".DTFC_ScrollWrapper").width();
                    // numbertouse = whatisthis2 + whatisthis - 1;
                    // $('.controlsfortable').css({
                    //     'padding-left': whatisthis,
                    //     'width': numbertouse.toFixed(2)
                    // });
                });

                // $("#permissionsInfo .table-responsive").on("scroll", function(e) {
                //     //console.log('permissionsInfo table-responsive is scrolling');
                //     horizontal = e.currentTarget.scrollLeft;
                //     vertical = e.currentTarget.scrollTop;
                //     numbertoremember = horizontal * -1;
                //     whatisthis = $("#permissionsInfo .table-responsive").scrollLeft();
                //     whatisthis2 = $("#permissionsInfo .table-responsive").width();
                //     numbertouse = whatisthis2 + whatisthis - 1;
                //     //console.log(numbertouse.toFixed(2));
                //     $('.controlsfortable').css({
                //         'padding-left': horizontal,
                //         'width': numbertouse.toFixed(2)
                //     });
                //     if (window.location.href.indexOf("route_permissions") > -1) {
                //         fixed_header_shift = 25;
                //     }
                //     $(".fixedHeader-floating[aria-describedby='permissionsTable_info']").css({
                //         'left': numbertoremember + fixed_header_shift
                //     });
                //     window.permissionstable.fixedHeader.adjust();
                // });
            },
            get_rolepositionInfo_tab: function() {
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                var ajaxdata = {};

                app.ajax.html(app.data.tabHTML_RoleRositionInfo, ajaxdata, null, function() {
                    $('#rolepositionInfo').html(app.ajax.result);
                    //console.log('#contractInfo_DONE');
                });
            },
            get_permissionsInfo_tab: function() {
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                var ajaxdata = {};

                app.ajax.html(app.data.tabHTML_PermissionsInfo, ajaxdata, null, function() {
                    $('#permissionsInfo').html(app.ajax.result);
                    //console.log('#civilstatusInfo_DONE');
                });
            },
            foreach_handle_error_display: function(idx, val) {
                // console.log('inside foreach_handle_error_display function');
                // console.log(idx);
                // console.log(val);
                // console.log('running');
                if (val.indexOf(' may only contain letters, numbers and spaces.') >= 0) {
                    var splitString = val.split(" may only contain letters, numbers and spaces.");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    //console.log(splitString);
                    //console.log(selector_id);
                    if (selector_id.indexOf('fk_') >= 0) {
                        selector_id = selector_id.replace('fk_', '');
                        val = val.replace('fk ', '');
                    }
                    if (selector_id == 'file') {
                        $('#' + selector_id).closest('td').addClass('has-error');
                        $('#' + selector_id).after('<span class="help-block"><strong></strong></span>');
                        $('.file-caption-name').after('<span style="padding-top:13px; margin-left: -13px;" class="help-block"><strong>' + val + '</strong></span>');
                        $('.file-caption-main').css('padding-bottom', '20px');
                    } else {
                        $('#' + selector_id).closest('td').addClass('has-error');
                        $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                    }
                }
                if (val.indexOf('The file must be a file of type:') >= 0) {
                    var splitString = val.split(" must be a file of type:");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    //console.log(splitString);
                    //console.log(selector_id);
                    if (selector_id.indexOf('fk_') >= 0) {
                        selector_id = selector_id.replace('fk_', '');
                        val = val.replace('fk ', '');
                    }
                    if (selector_id == 'file') {
                        $('#' + selector_id).closest('td').addClass('has-error');
                        $('#' + selector_id).after('<span class="help-block"><strong></strong></span>');
                        $('.file-caption-name').after('<span style="padding-top:13px; margin-left: -13px;" class="help-block"><strong>' + val + '</strong></span>');
                        $('.file-caption-main').css('padding-bottom', '20px');
                    } else {
                        $('#' + selector_id).closest('td').addClass('has-error');
                        $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                    }
                }
                if (val.indexOf(' failed to upload.') >= 0) {
                    var splitString = val.split(" failed to upload.");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    //console.log(splitString);
                    //console.log(selector_id);
                    if (selector_id.indexOf('fk_') >= 0) {
                        selector_id = selector_id.replace('fk_', '');
                        val = val.replace('fk ', '');
                    }
                    if (selector_id == 'file') {
                        $('#' + selector_id).closest('td').addClass('has-error');
                        $('#' + selector_id).after('<span class="help-block"><strong></strong></span>');
                        $('.file-caption-name').after('<span style="padding-top:13px; margin-left: -13px;" class="help-block"><strong>' + val + '</strong></span>');
                        $('.file-caption-main').css('padding-bottom', '20px');
                    } else {
                        $('#' + selector_id).closest('td').addClass('has-error');
                        $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                    }
                }

                if (val.indexOf(' number can only contain numbers.') >= 0) {
                    var splitString = val.split(" number can only contain numbers.");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    if (selector_id.indexOf('fk_') >= 0) {
                        selector_id = selector_id.replace('fk_', '');
                        val = val.replace('fk ', '');
                    }
                    //console.log(splitString);
                    $('#' + selector_id).closest('td').addClass('has-error');
                    $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                }
                if (val.indexOf(' may not be greater than ') >= 0) {
                    var splitString = val.split(" may not be greater than ");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    if (selector_id.indexOf('fk_') >= 0) {
                        selector_id = selector_id.replace('fk_', '');
                        val = val.replace('fk ', '');
                    }
                    //console.log(splitString);
                    $('#' + selector_id).closest('td').addClass('has-error');
                    $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                }

                if (val.indexOf(' number can only contain numbers.') >= 0) {
                    var splitString = val.split(" number can only contain numbers.");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    if (selector_id.indexOf('fk_') >= 0) {
                        selector_id = selector_id.replace('fk_', '');
                        val = val.replace('fk ', '');
                    }
                    //console.log(splitString);
                    $('#' + selector_id).closest('td').addClass('has-error');
                    $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                }
                if (val.indexOf('must be a number.') >= 0) {
                    var splitString = val.split(" must be a number.");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    if (selector_id.indexOf('fk_') >= 0) {
                        selector_id = selector_id.replace('fk_', '');
                        val = val.replace('fk ', '');
                    }
                    //console.log(splitString);
                    $('#' + selector_id).closest('td').addClass('has-error');
                    $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                }
                if (val.indexOf(' must only contain numbers') >= 0) {
                    var splitString = val.split(" must only contain numbers");
                    // splitString = splitString[0].split("The ");
                    splitString = splitString[0];
                    var selector_id = splitString.replace(/ /g, "_");
                    if (selector_id.indexOf('fk_') >= 0) {
                        selector_id = selector_id.replace('fk_', '');
                        val = val.replace('fk ', '');
                    }
                    if (selector_id.indexOf('contact_phone') >= 0) {
                        selector_id = 'work_' + selector_id;
                    }
                    //console.log(splitString);
                    $('#' + selector_id).closest('td').addClass('has-error');
                    $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                }
                if (val.indexOf('is not a valid date.') >= 0) {
                    var splitString = val.split(" is not a valid date.");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    if (selector_id.indexOf('fk_') >= 0) {
                        selector_id = selector_id.replace('fk_', '');
                        val = val.replace('fk ', '');
                    }
                    //console.log(splitString);
                    $('#' + selector_id).closest('td').addClass('has-error');
                    $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                }
                if (val.indexOf(' field is required.') >= 0) {
                    var splitString = val.split(" field");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    //console.log(splitString);
                    //console.log(selector_id);
                    if (selector_id.indexOf('fk_') >= 0) {
                        selector_id = selector_id.replace('fk_', '');
                        val = val.replace('fk ', '');
                    }
                    if (selector_id == 'file') {
                        $('#' + selector_id).closest('td').addClass('has-error');
                        $('#' + selector_id).after('<span class="help-block"><strong></strong></span>');
                        $('.file-caption-name').after('<span style="padding-top:13px; margin-left: -13px;" class="help-block"><strong>' + val + '</strong></span>');
                        $('.file-caption-main').css('padding-bottom', '20px');
                    } else {
                        $('#' + selector_id).closest('td').addClass('has-error');
                        $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                    }
                }
                if (val.indexOf(' must be at least ') >= 0) {
                    var splitString = val.split(" must be at least");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    $('#' + selector_id).closest('td').addClass('has-error');
                    $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                }
                if (val.indexOf(' format is invalid.') >= 0) {
                    var splitString = val.split(" format is invalid.");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    $('#' + selector_id).closest('td').addClass('has-error');
                    $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                }
                if (val.indexOf(' field is required when ') >= 0) {
                    var splitString = val.split(" field is required when ");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    val = val.replace('fk ', '');
                    $('#' + selector_id).closest('td').addClass('has-error');
                    $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                }
                if (val.indexOf(' must be between ') >= 0) {
                    var splitString = val.split(" must be between ");
                    splitString = splitString[0].split("The ");
                    splitString = splitString[1];
                    var selector_id = splitString.replace(/ /g, "_");
                    val = val.replace('fk ', '');
                    $('#' + selector_id).closest('td').addClass('has-error');
                    $('#' + selector_id).after('<span class="help-block"><strong>' + val + '</strong></span>');
                }
                if (val.indexOf('Not Permitted') >= 0) {
                    /** the ajax returned a 404 and so it needs to be refreshed and warn the app */
                }
            },
            filter: {
                // Add html & event for team filter
                byTeam: function(table) {
                    var teams = app.util.build.team();
                    var teamFilter = $("div#team_filter");
                    teamFilter.html(teams);
                    // Custom filter event
                    $("select[id='team_filter']").on('change', function() {
                        var options_all = $("#team_filter option:selected").map(function() {
                            return $(this).text();
                        }).get().join('|');
                        if ((options_all.indexOf("|") >= 0) || (options_all == '')) {
                            table.column(app.conf.table.filterColumn.routepermissions.team).search(options_all, true, false).draw();
                        } else {
                            table.column(app.conf.table.filterColumn.routepermissions.team).search('^' + options_all + '$', true, false).draw();
                        }
                    });
                },
                // Add html & event for team position
                byPosition: function(table) {
                    var positions = app.util.build.position();
                    var positionFilter = $("div#position_filter");
                    positionFilter.html(positions);
                    // Custom filter event
                    $("select[id='position_filter']").on('change', function() {
                        var options_all = $("#position_filter option:selected").map(function() {
                            return $(this).text();
                        }).get().join('|');
                        if ((options_all.indexOf("|") >= 0) || (options_all == '')) {
                            table.column(app.conf.table.filterColumn.routepermissions.position).search(options_all, true, false).draw();
                        } else {
                            table.column(app.conf.table.filterColumn.routepermissions.position).search('^' + options_all + '$', true, false).draw();
                        }
                    });
                },
                byStatus: function(table) {
                    var status = app.util.build.status();
                    var statusFilter = $("div#status_filter");
                    statusFilter.html(status);
                    // Custom filter event
                    $("select[id='status_filter']").on('change', function() {
                        var options_all = $("#status_filter option:selected").map(function() {
                            return $(this).val();
                        }).get().join('|');
                        table.column(app.conf.table.filterColumn.routepermissions.status + 1).search(options_all, true, false).draw();
                    });
                },
                bySections: function(table) {
                    var sections = app.util.build.sections();
                    var sectionsFilter = $("div#sections_filter");
                    sectionsFilter.html(sections);
                    // Custom filter event
                    $("select[id='sections_filter']").on('change', function() {
                        var options_all = $("#sections_filter option:selected").map(function() {
                            return $(this).text();
                        }).get().join('|');
                        table.column(app.conf.table.filterColumn.routepermissions.sections).search(options_all, true, false).draw();
                    });
                },
                exportExcel: function(table) {
                    var a = app.util.build.exportbutton();
                    var position = a.search("data-action=") + 13;
                    var b = '/employeeinfo_export';
                    var exportbuttonmake = [a.slice(0, position), b, a.slice(position)].join('');
                    var exportbuttonlocation = $("div#export_buttonlocation");
                    exportbuttonlocation.html(exportbuttonmake);
                },
                clearallfilter: function(table) {
                    var clearfilter = app.util.build.clearallfiltersbutton();
                    var clearFilterloc = $("div#clear_filter");
                    clearFilterloc.html(clearfilter);
                }
            },
            handle_route_permission: function(element_name) {
                function toTitleCase(str) {
                    return str.replace(
                        /\w\S*/g,
                        function(txt) {
                            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                        }
                    );
                }

                function search(things) {
                    return things.item === 'chips';
                }
                //console.log('inside handler =' + element_name);

                var checkbox_in_td = $("input[name='" + element_name + "']");
                var activate_or_deativate_text = null;
                var activate_or_deativate = null;
                var kind_of_confirm_box = null;
                if (checkbox_in_td.is(':checked')) {
                    /** means want to add a route */
                    activate_or_deativate_text = '<b style="color: Green; font-weight: 900;">ACTIVATE</b>';
                    activate_or_deativate = 1;
                    kind_of_confirm_box = 'green';
                } else {
                    /** means want to remove a route */
                    activate_or_deativate_text = '<b style="color: Red; font-weight: 900;">DEACTIVATE</b>';
                    activate_or_deativate = 0;
                    kind_of_confirm_box = 'red';
                }
                var splitString = element_name.split("edit_");
                var isWritePermission = splitString[1].indexOf("_write_"); //>= 0 if finds //-1 if it does not find
                var isReadPermission = splitString[1].indexOf("_read_"); //>= 0 if finds //-1 if it does not find
                var role_permission_string = null;
                var display_role_to_change = null;
                var display_permission_to_change = null;
                var checkagainst_permissions_type = null;
                var checkagainst_permission_string = null;
                var checkagainst_role_string = null;
                //var get the array of roles and permissions in here to grab their ids dpending on the string.
                var role_to_change_id = null;
                var permission_to_change_id = null;
                var resultcheck = null;
                var first_word_length = null;

                if (isWritePermission >= 0) {
                    role_permission_string = splitString[1].split("_write_");
                    checkagainst_permissions_type = 'WRITE_';
                    checkagainst_permission_string = checkagainst_permissions_type + role_permission_string[1];
                    checkagainst_permission_string = checkagainst_permission_string.replace(/_/g, ' ');
                    checkagainst_role_string = role_permission_string[0].replace(/_/g, ' ');
                    resultcheck = checkagainst_role_string.split(" ");
                    first_word_length = resultcheck[0].length;
                    if (first_word_length <= 2) {
                        checkagainst_role_string = resultcheck[0].toUpperCase() + ' ' + toTitleCase(resultcheck[1]);
                    } else {
                        checkagainst_role_string = toTitleCase(checkagainst_role_string);
                    }
                } else if (isReadPermission >= 0) {
                    role_permission_string = splitString[1].split("_read_");
                    checkagainst_permissions_type = 'READ_';
                    checkagainst_permission_string = checkagainst_permissions_type + role_permission_string[1];
                    checkagainst_permission_string = checkagainst_permission_string.replace(/_/g, ' ');
                    checkagainst_role_string = role_permission_string[0].replace(/_/g, ' ');
                    resultcheck = checkagainst_role_string.split(" ");
                    first_word_length = resultcheck[0].length;
                    if (first_word_length <= 2) {
                        checkagainst_role_string = resultcheck[0].toUpperCase() + ' ' + toTitleCase(resultcheck[1]);
                    } else {
                        checkagainst_role_string = toTitleCase(checkagainst_role_string);
                    }
                }

                /** for permission string need to remove _ and title Case */
                //console.log(checkagainst_permission_string);
                //console.log(checkagainst_role_string);

                $.each(app.data.company_positions, function(idx, val) {
                    if (val.name == checkagainst_role_string) {
                        role_to_change_id = idx;
                        return false;
                    }
                });
                $.each(app.data.company_permissions, function(idx, val) {
                    if (val.name == checkagainst_permission_string) {
                        permission_to_change_id = idx;
                        return false;
                    }
                });


                if (isWritePermission >= 0) {
                    display_permission_to_change = '<b style="color: orange">' + checkagainst_permission_string + '</b>';
                    display_role_to_change = '<b style="color: blue">' + checkagainst_role_string + '\'s </b>';
                }
                if (isReadPermission >= 0) {
                    display_permission_to_change = '<b style="color: orange">' + checkagainst_permission_string + '</b>';
                    display_role_to_change = '<b style="color: blue">' + checkagainst_role_string + '\'s </b>';
                }

                //console.log(app.data.auth_user_permissions);
                var canWRITEpermissions = app.data.auth_user_permissions.indexOf("WRITE permissions"); //>= 0 if finds //-1 if it does not find
                if (canWRITEpermissions >= 0) {
                    $.confirm({
                        title: eval("app.translations." + app.data.locale + ".title_text"),
                        content: activate_or_deativate_text + ' the ' + display_role_to_change + display_permission_to_change + ' permission?',
                        type: kind_of_confirm_box,
                        draggable: true,
                        dragWindowGap: 0,
                        backgroundDismiss: 'cancel',
                        escapeKey: true,
                        animateFromElement: false,
                        onAction: function(btnName) {
                            $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                        },
                        buttons: {
                            ok: {
                                btnClass: 'btn-primary text-white',
                                keys: ['enter'],
                                text: eval("app.translations." + app.data.locale + ".okay_text"),
                                action: function() {
                                    app.util.nprogressinit();

                                    var data = { 'fk_role_id': role_to_change_id, 'fk_permission_id': permission_to_change_id, '_token': $('meta[name="csrf-token"]').attr('content'), 'activate_or_deativate': activate_or_deativate };
                                    app.ajax.json(app.data.urlpostRoutePermissionSingleRecordChange, data, null, function() {
                                        //console.log(app.ajax.result);
                                        success_ajax_then_refresh = app.ajax.result.success;
                                        if (app.ajax.result.success == true) {
                                            /** */
                                            /** */
                                            app.util.nprogressdone();
                                        } else {
                                            /** */
                                            /** */
                                            if (checkbox_in_td.is(':checked')) {
                                                checkbox_in_td.prop("checked", false);
                                            } else {
                                                checkbox_in_td.prop("checked", true);
                                            }
                                            app.util.nprogressdone();
                                        }
                                    });
                                }
                            },
                            cancel: {
                                text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                action: function() {
                                    //$.alert(eval("app.translations."+app.data.locale+".role_permission_assignment_change_was_reset"));
                                    if (checkbox_in_td.is(':checked')) {
                                        checkbox_in_td.prop("checked", false);
                                    } else {
                                        checkbox_in_td.prop("checked", true);
                                    }
                                    // close_colorbox_timer();
                                }
                            },
                        }
                    });
                }
            },
        },
    },
};
