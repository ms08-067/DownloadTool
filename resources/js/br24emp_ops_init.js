var app_ops = {
    mainmenu: {
        index: {
            init: function() {
                app.util.period();
                app.util.nprogressinit();

                $('.previous-surround, .next-surround').css('display', 'none');

                $('.button').not('.isDisabled').on('click', function() {
                    if($(this).attr("target") !== "_blank"){
                        app.util.nprogressinit();
                        NProgress.start();
                    }
                });

                $(".button[target='_blank']").not('.isDisabled').on('click', function() {
                    //console.log('clicked to open new tab');
                    NProgress.done();
                });

                function syntaxHighlight(json) {
                    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                        var cls = 'number';
                        if (/^"/.test(match)) {
                            if (/:$/.test(match)) {
                                cls = 'key';
                            } else {
                                cls = 'string';
                            }
                        } else if (/true|false/.test(match)) {
                            cls = 'boolean';
                        } else if (/null/.test(match)) {
                            cls = 'null';
                        }
                        return '<span class="' + cls + '">' + match + '</span>';
                    });
                }


                window.onload = function() {
                    NProgress.done();
                    app.util.fullscreenloading_end();

                    window.autoPollrefreshIntervalId_ASAP = null;

                    var delayTime = 5000;
                    var autoPolltimer = function() {
                        window.autoPollrefreshIntervalId_ASAP = setInterval(function() {
                            window.location.reload();
                        }, delayTime);
                    };

                    $('#pause_refresh').on('click', function() {
                        if($(this).hasClass('_paused_')){
                            /**autoPolltimer();*/
                            $(this).removeClass('_paused_');
                            var this_html = $(this).html();
                            $(this).html(this_html.replace("*", ""));
                            window.location.reload();
                        }else{
                            clearInterval(window.autoPollrefreshIntervalId_ASAP);
                            $(this).addClass('_paused_');
                            $(this).html($(this).html()+"*");
                        }
                    });

                    autoPolltimer();


                    $('pre.sf-dump').css('z-index', '1');

                    if($("#task_downloads_files_not_finished_processing").html() == ''){
                       $("#task_downloads_files_not_finished_processing").html(syntaxHighlight(JSON.stringify(app.data.task_downloads_files_not_finished_processing, undefined, 4)));
                    }
                    if($("#upload_files_not_finished_processing").html() == ''){
                       $("#upload_files_not_finished_processing").html(syntaxHighlight(JSON.stringify(app.data.upload_files_not_finished_processing, undefined, 4)));
                    }
                    if($("#task_manual_downloads_files_not_finished_processing").html() == ''){
                       $("#task_manual_downloads_files_not_finished_processing").html(syntaxHighlight(JSON.stringify(app.data.task_manual_downloads_files_not_finished_processing, undefined, 4)));
                    }
                    if($("#worker_jobs_table").html() == ''){
                       $("#worker_jobs_table").html(syntaxHighlight(JSON.stringify(app.data.worker_jobs_table, undefined, 4)));
                    }
                    if($("#worker_failedjobs_table").html() == ''){
                       $("#worker_failedjobs_table").html(syntaxHighlight(JSON.stringify(app.data.worker_failedjobs_table, undefined, 4)));
                    }
                };

            }
        }
    },
    contract_mass_upload_docs: {
        index: {
            init: function() {
                app.util.period();
                $('.previous-surround, .next-surround').css('display', 'none');
                NProgress.configure({ parent: '#progress-bar-parent', showSpinner: false });
                app.util.fixedheaderviewporthandler();
                app_ops.contract_mass_upload_docs.index.table();
            },
            table: function() {
                app.util.nprogressdone();
                $('.button').not('.isDisabled').on('click', function() {
                    if($(this).attr("target") !== "_blank"){
                        app.util.nprogressinit();
                        NProgress.start();
                    }
                });

                $(".button[target='_blank']").not('.isDisabled').on('click', function() {
                    //console.log('clicked to open new tab');
                    NProgress.done();
                });


                /**console.log('ready');*/
                var select_list_employee_list = app.data.selectize_employee_list_formated_json;
                /**console.log('select_list_employee_list=' + JSON.stringify(select_list_employee_list));*/
                var select_selected_list_employee_has_family_member_json = app.data.selectize_selected_employee_has_family_members_in_company_formated_json;
                /**console.log('select_selected_list_employee_has_family_member_json=' + JSON.stringify(select_selected_list_employee_has_family_member_json));*/

                select_selected_list_employee_has_family_member = select_selected_list_employee_has_family_member_json.map(function(item) {
                    return item['fk_is_br24_employee'];
                });
                //console.log('AFTERselect_selected_list_employee_has_family_member=' + select_selected_list_employee_has_family_member);

                var case_id_uploading_to = null;
                var encrypted_case_id_uploading_to = null;

                var $is_br24_employee_select = $('#is_br24_employee').selectize({
                    plugins: ['remove_button', 'optgroup_columns'],
                    persist: false,
                    maxItems: 200,
                    mode: 'single',
                    /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                    placeholder: '-- SELECT CASE ID TO UPLOAD TO --',
                    valueField: ['case_id'],
                    labelField: 'case_id',
                    searchField: ['case_id', 'xml_title_contents', 'xml_jobid_title'],
                    options: select_list_employee_list,
                    /** list of all the viable employees on init */
                    items: select_selected_list_employee_has_family_member,
                    /** list of already selected employees on init */
                    hideSelected: true,
                    openOnFocus: false,
                    closeAfterSelect: true,
                    render: {
                        item: function(item, escape) {
                            return '<div>' +
                                (item.case_id ? '<span class="case_id"><u><b>' + item.case_id + '</b></u></span>' : '') +
                                '<span style="color: #ccc">&nbsp;</span>' +
                                (item.xml_jobid_title ? ' <i class="fa fa-angle-double-right text-danger"></i> ' : '') +
                                (item.xml_jobid_title ? '<span class="xml_jobid_title" style="font-size: 9px; color: #1cd;"><b>' + item.xml_jobid_title + '</b></span>' : '') +
                                '<br>' +
                                (item.xml_title_contents ? '<span class="xml_title_contents" style="font-size: 10px;">' + item.xml_title_contents + '</span>' : '') +
                                //'<br>' +
                                //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                                '</div>';
                        },
                        option: function(item, escape) {
                            var label = item.xml_title_contents || item.email;
                            var caption = item.xml_title_contents ? item.email : null;
                            return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                                '<span class="label label-primary">' + item.case_id + '</span>' +
                                '<span style="color: #ccc">&nbsp;</span>' +

                                (item.xml_jobid_title ? ' <i class="fa fa-angle-double-right text-danger"></i> ' : '') +
                                (item.xml_jobid_title ? '<span class="xml_jobid_title" style="font-size: 9px; color: #1cd;"><b>' + item.xml_jobid_title + '</b></span>' : '') +
                                '<br>' +
                                (item.xml_title_contents ? '<span class="xml_title_contents" style="font-size: 10px;">' + item.xml_title_contents + '</span>' : '') +
                                //(item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                                //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                                '</div>';
                        }
                    },
                    onChange: function(value) {
                        //$('#cb_add_family_members_details_form').addClass('dirty');
                        /** when it changes i want to update the varialbe so that it can be loaded together with the files */
                        /**console.log($('#is_br24_employee').val());*/

                        case_id_uploading_to = $('#is_br24_employee').val()[0];

                        $.each(select_list_employee_list, function(idx, val) {
                            //console.log(idx);
                            if (val.case_id == case_id_uploading_to) {
                                /**console.log(val);*/
                                encrypted_case_id_uploading_to = val.encrypted_case_id;
                                if (val.try >= 1) {
                                    /** if the attempt try is already 1 that means they have already tried to upload to this CASEID */
                                    /** need to warn them that the previous attempt will all be removed */
                                    $.alert({
                                        title: 'Already Uploaded previously to this caseID= ' + case_id_uploading_to,
                                        content: 'the files for caseID= ' + case_id_uploading_to + ' from the previous attempt will all be deleted and replaced with these new files. No backup is being made',
                                        //content: 'các tệp cho caseOD = '+ $ (' # is_br24_employee '). val () [0] +' đã được nhập vào hàng đợi tải lên. Nó sẽ được nén và tải lên s3 trong giây lát.',
                                        type: 'warning'
                                    });
                                }
                                return false;
                            }
                        });


                        if (case_id_uploading_to == undefined) {
                            $('.fileinput-upload-button').css('display', 'none');
                        } else {
                            $('.fileinput-upload-button').css('display', '');
                        }

                        /***/
                        /**console.log(fstack);*/
                        /**console.log(count_files_success);*/
                        if (fstack.length != 0 && count_files_success != 0 && fstack.length == count_files_success) {
                            $('input[data-ident=upload_contract_mass_upload_file]').fileinput('clear');
                            count_files_success = 0;
                            fstack = [];
                        }
                    },
                    onInitialize: function(value) {
                        case_id_uploading_to = $('#is_br24_employee').val()[0];

                        $.each(select_list_employee_list, function(idx, val) {
                            //console.log(idx);
                            if (val.case_id == case_id_uploading_to) {
                                /**console.log(val);*/
                                encrypted_case_id_uploading_to = val.encrypted_case_id;
                                if (val.try >= 1) {
                                    /** if the attempt try is already 1 that means they have already tried to upload to this CASEID */
                                    /** need to warn them that the previous attempt will all be removed */
                                    $.alert({
                                        title: 'Already Uploaded previously to this caseID= ' + case_id_uploading_to,
                                        content: 'the files for caseID= ' + case_id_uploading_to + ' from the previous attempt will all be deleted and replaced with these new files. No backup is being made',
                                        //content: 'các tệp cho caseOD = '+ $ (' # is_br24_employee '). val () [0] +' đã được nhập vào hàng đợi tải lên. Nó sẽ được nén và tải lên s3 trong giây lát.',
                                        type: 'warning'
                                    });
                                }
                                return false;
                            }
                        });

                        if (case_id_uploading_to == undefined) {
                            $('.fileinput-upload-button').css('display', 'none');
                        } else {
                            $('.fileinput-upload-button').css('display', '');
                        }

                        /***/
                        /**console.log(fstack);*/
                        /**console.log(count_files_success);*/
                        // if (fstack.length != 0 && count_files_success != 0 && fstack.length == count_files_success) {
                        //     $('input[data-ident=upload_contract_mass_upload_file]').fileinput('clear');
                        //     count_files_success = 0;
                        //     fstack = [];
                        // }

                    }
                });

                var is_br24_employee_select_selectize = $is_br24_employee_select[0].selectize;
                var is_br24_employee_select_old_options = is_br24_employee_select_selectize.settings;
                var selectize_focus_handler = function(value, $item) {
                    var width_to_be = $('.selectize-control').outerWidth();
                    var height_to_be = 600;
                    $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                };
                is_br24_employee_select_selectize.on('focus', selectize_focus_handler);


                /** filter the shifts from the swapable shifts select */
                var item_remove_handler = function(value, $item) {
                    /**console.log('item_removed');*/
                    // $('#relatives_name').val('');
                    // $('#relatives_date_of_birth').val('');
                    // $('#relatives_tax_number').val('');
                    // $('#relatives_id_number').val('');
                    // $('#relatives_age').val('');
                    case_id_uploading_to = null;
                    encrypted_case_id_uploading_to = null;
                };
                is_br24_employee_select_selectize.on('item_remove', item_remove_handler);

                $(window).resize(function() {
                    var width_to_be = $('.selectize-control').outerWidth();
                    var height_to_be = 600;
                    $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                });


                var count_files_success = 0;
                var fstack = [];

                var dropzone_isvisible_check = null;
                $(document).ready(function() {
                    $.ajaxSetup({ 
                        headers: { 'X-CSRF-TOKEN': $("#manage_mass_upload_contracts_table").data("token") },
                        timeout: 600000
                    });
                    $('input[data-ident=upload_contract_mass_upload_file]').fileinput({
                        'language': app.data.locale,
                        'maxFileCount': 800,
                        'maxFileSize': 4194304,
                        'uploadUrl': app.data.urlPostMassUploadContracts,
                        'uploadAsync': true,
                        'showUpload': true,
                        'previewFileType': 'any',
                        'showClose': false,
                        'showCaption': true,
                        'showBrowse': true,
                        'browseClass': 'btn btn-primary btn-file btn-fileinput-k',
                        'browseIcon': '<i class="glyphicon glyphicon-folder-open"></i> ',
                        'showUploadedThumbs': true,
                        'showPreview': true,
                        'showRemove': true,
                        'showUpload': true,
                        'showUploadStats': true,
                        //'allowedFileExtensions': ["txt", "md", "ini", "text"],
                        //'allowedFileTypes': ["image", "video"],
                        'allowedPreviewTypes': ['image', 'html', 'text', 'video', 'audio', 'flash'],
                        //'allowedPreviewMimeTypes': ['x-world/x-3dmf', 'x-world/x-3dmf', 'application/octet-stream', 'application/x-authorware-bin', 'application/x-authorware-map', 'application/x-authorware-seg', 'text/vnd.abc', 'text/html', 'video/animaflex', 'application/postscript', 'audio/aiff', 'audio/x-aiff', 'audio/aiff', 'audio/x-aiff', 'audio/aiff', 'audio/x-aiff', 'application/x-aim', 'text/x-audiosoft-intra', 'application/x-navi-animation', 'application/x-nokia-9000-communicator-add-on-software', 'application/mime', 'application/octet-stream', 'application/arj', 'application/octet-stream', 'image/x-jg', 'video/x-ms-asf', 'text/x-asm', 'text/asp', 'application/x-mplayer2', 'video/x-ms-asf', 'video/x-ms-asf-plugin', 'audio/basic', 'audio/x-au', 'application/x-troff-msvideo', 'video/avi', 'video/msvideo', 'video/x-msvideo', 'video/avs-video', 'application/x-bcpio', 'application/mac-binary', 'application/macbinary', 'application/octet-stream', 'application/x-binary', 'application/x-macbinary', 'image/bmp', 'image/bmp', 'image/x-windows-bmp', 'application/book', 'application/book', 'application/x-bzip2', 'application/x-bsh', 'application/x-bzip', 'application/x-bzip2', 'text/plain', 'text/x-c', 'text/plain', 'application/vnd.ms-pki.seccat', 'text/plain', 'text/x-c', 'application/clariscad', 'application/x-cocoa', 'application/cdf', 'application/x-cdf', 'application/x-netcdf', 'application/pkix-cert', 'application/x-x509-ca-cert', 'application/x-chat', 'application/x-chat', 'application/java', 'application/java-byte-code', 'application/x-java-class', 'application/octet-stream', 'text/plain', 'text/plain', 'application/x-cpio', 'text/x-c', 'application/mac-compactpro', 'application/x-compactpro', 'application/x-cpt', 'application/pkcs-crl', 'application/pkix-crl', 'application/pkix-cert', 'application/x-x509-ca-cert', 'application/x-x509-user-cert', 'application/x-csh', 'text/x-script.csh', 'application/x-pointplus', 'text/css', 'text/plain', 'application/x-director', 'application/x-deepv', 'text/plain', 'application/x-x509-ca-cert', 'video/x-dv', 'application/x-director', 'video/dl', 'video/x-dl', 'application/msword', 'application/msword', 'application/commonground', 'application/drafting', 'application/octet-stream', 'video/x-dv', 'application/x-dvi', 'drawing/x-dwf (old)', 'model/vnd.dwf', 'application/acad', 'image/vnd.dwg', 'image/x-dwg', 'application/dxf', 'image/vnd.dwg', 'image/x-dwg', 'application/x-director', 'text/x-script.elisp', 'application/x-bytecode.elisp (compiled elisp)', 'application/x-elc', 'application/x-envoy', 'application/postscript', 'application/x-esrehber', 'text/x-setext', 'application/envoy', 'application/x-envoy', 'application/octet-stream', 'text/plain', 'text/x-fortran', 'text/x-fortran', 'text/plain', 'text/x-fortran', 'application/vnd.fdf', 'application/fractals', 'image/fif', 'video/fli', 'video/x-fli', 'image/florian', 'text/vnd.fmi.flexstor', 'video/x-atomic3d-feature', 'text/plain', 'text/x-fortran', 'image/vnd.fpx', 'image/vnd.net-fpx', 'application/freeloader', 'audio/make', 'text/plain', 'image/g3fax', 'image/gif', 'video/gl', 'video/x-gl', 'audio/x-gsm', 'audio/x-gsm', 'application/x-gsp', 'application/x-gss', 'application/x-gtar', 'application/x-compressed', 'application/x-gzip', 'application/x-gzip', 'multipart/x-gzip', 'text/plain', 'text/x-h', 'application/x-hdf', 'application/x-helpfile', 'application/vnd.hp-hpgl', 'text/plain', 'text/x-h', 'text/x-script', 'application/hlp', 'application/x-helpfile', 'application/x-winhelp', 'application/vnd.hp-hpgl', 'application/vnd.hp-hpgl', 'application/binhex', 'application/binhex4', 'application/mac-binhex', 'application/mac-binhex40', 'application/x-binhex40', 'application/x-mac-binhex40', 'application/hta', 'text/x-component', 'text/html', 'text/html', 'text/html', 'text/webviewhtml', 'text/html', 'x-conference/x-cooltalk', 'image/x-icon', 'text/plain', 'image/ief', 'image/ief', 'application/iges', 'model/iges', 'application/iges', 'model/iges', 'application/x-ima', 'application/x-httpd-imap', 'application/inf', 'application/x-internett-signup', 'application/x-ip2', 'video/x-isvideo', 'audio/it', 'application/x-inventor', 'i-world/i-vrml', 'application/x-livescreen', 'audio/x-jam', 'text/plain', 'text/x-java-source', 'text/plain', 'text/x-java-source', 'application/x-java-commerce', 'image/jpeg', 'image/pjpeg', 'image/jpeg', 'image/jpeg', 'image/pjpeg', 'image/jpeg', 'image/pjpeg', 'image/jpeg', 'image/pjpeg', 'image/x-jps', 'application/x-javascript', 'application/javascript', 'application/ecmascript', 'text/javascript', 'text/ecmascript', 'image/jutvision', 'audio/midi', 'music/x-karaoke', 'application/x-ksh', 'text/x-script.ksh', 'audio/nspaudio', 'audio/x-nspaudio', 'audio/x-liveaudio', 'application/x-latex', 'application/lha', 'application/octet-stream', 'application/x-lha', 'application/octet-stream', 'text/plain', 'audio/nspaudio', 'audio/x-nspaudio', 'text/plain', 'application/x-lisp', 'text/x-script.lisp', 'text/plain', 'text/x-la-asf', 'application/x-latex', 'application/octet-stream', 'application/x-lzh', 'application/lzx', 'application/octet-stream', 'application/x-lzx', 'text/plain', 'text/x-m', 'video/mpeg', 'audio/mpeg', 'video/mpeg', 'audio/x-mpequrl', 'application/x-troff-man', 'application/x-navimap', 'text/plain', 'application/mbedlet', 'application/x-magic-cap-package-1.0', 'application/mcad', 'application/x-mathcad', 'image/vasa', 'text/mcf', 'application/netmc', 'application/x-troff-me', 'message/rfc822', 'message/rfc822', 'application/x-midi', 'audio/midi', 'audio/x-mid', 'audio/x-midi', 'music/crescendo', 'x-music/x-midi', 'application/x-midi', 'audio/midi', 'audio/x-mid', 'audio/x-midi', 'music/crescendo', 'x-music/x-midi', 'application/x-frame', 'application/x-mif', 'message/rfc822', 'www/mime', 'audio/x-vnd.audioexplosion.mjuicemediafile', 'video/x-motion-jpeg', 'application/base64', 'application/x-meme', 'application/base64', 'audio/mod', 'audio/x-mod', 'video/quicktime', 'video/quicktime', 'video/x-sgi-movie', 'audio/mpeg', 'audio/x-mpeg', 'video/mpeg', 'video/x-mpeg', 'video/x-mpeq2a', 'audio/mpeg3', 'audio/x-mpeg-3', 'video/mpeg', 'video/x-mpeg', 'audio/mpeg', 'video/mpeg', 'application/x-project', 'video/mpeg', 'video/mpeg', 'audio/mpeg', 'video/mpeg', 'audio/mpeg', 'application/vnd.ms-project', 'application/x-project', 'application/x-project', 'application/x-project', 'application/marc', 'application/x-troff-ms', 'video/x-sgi-movie', 'audio/make', 'application/x-vnd.audioexplosion.mzz', 'image/naplps', 'image/naplps', 'application/x-netcdf', 'application/vnd.nokia.configuration-message', 'image/x-niff', 'image/x-niff', 'application/x-mix-transfer', 'application/x-conference', 'application/x-navidoc', 'application/octet-stream', 'application/oda', 'application/x-omc', 'application/x-omcdatamaker', 'application/x-omcregerator', 'text/x-pascal', 'application/pkcs10', 'application/x-pkcs10', 'application/pkcs-12', 'application/x-pkcs12', 'application/x-pkcs7-signature', 'application/pkcs7-mime', 'application/x-pkcs7-mime', 'application/pkcs7-mime', 'application/x-pkcs7-mime', 'application/x-pkcs7-certreqresp', 'application/pkcs7-signature', 'application/pro_eng', 'text/pascal', 'image/x-portable-bitmap', 'application/vnd.hp-pcl', 'application/x-pcl', 'image/x-pict', 'image/x-pcx', 'chemical/x-pdb', 'application/pdf', 'audio/make', 'audio/make.my.funk', 'image/x-portable-graymap', 'image/x-portable-greymap', 'image/pict', 'image/pict', 'application/x-newton-compatible-pkg', 'application/vnd.ms-pki.pko', 'text/plain', 'text/x-script.perl', 'application/x-pixclscript', 'image/x-xpixmap', 'text/x-script.perl-module', 'application/x-pagemaker', 'application/x-pagemaker', 'image/png', 'application/x-portable-anymap', 'image/x-portable-anymap', 'application/mspowerpoint', 'application/vnd.ms-powerpoint', 'model/x-pov', 'application/vnd.ms-powerpoint', 'image/x-portable-pixmap', 'application/mspowerpoint', 'application/vnd.ms-powerpoint', 'application/mspowerpoint', 'application/powerpoint', 'application/vnd.ms-powerpoint', 'application/x-mspowerpoint', 'application/mspowerpoint', 'application/x-freelance', 'application/pro_eng', 'application/postscript', 'application/octet-stream', 'paleovu/x-pv', 'application/vnd.ms-powerpoint', 'text/x-script.phyton', 'application/x-bytecode.python', 'audio/vnd.qcelp', 'x-world/x-3dmf', 'x-world/x-3dmf', 'image/x-quicktime', 'video/quicktime', 'video/x-qtc', 'image/x-quicktime', 'image/x-quicktime', 'audio/x-pn-realaudio', 'audio/x-pn-realaudio-plugin', 'audio/x-realaudio', 'audio/x-pn-realaudio', 'application/x-cmu-raster', 'image/cmu-raster', 'image/x-cmu-raster', 'image/cmu-raster', 'text/x-script.rexx', 'image/vnd.rn-realflash', 'image/x-rgb', 'application/vnd.rn-realmedia', 'audio/x-pn-realaudio', 'audio/mid', 'audio/x-pn-realaudio', 'audio/x-pn-realaudio', 'audio/x-pn-realaudio-plugin', 'application/ringing-tones', 'application/vnd.nokia.ringing-tone', 'application/vnd.rn-realplayer', 'application/x-troff', 'image/vnd.rn-realpix', 'audio/x-pn-realaudio-plugin', 'text/richtext', 'text/vnd.rn-realtext', 'application/rtf', 'application/x-rtf', 'text/richtext', 'application/rtf', 'text/richtext', 'video/vnd.rn-realvideo', 'text/x-asm', 'audio/s3m', 'application/octet-stream', 'application/x-tbook', 'application/x-lotusscreencam', 'text/x-script.guile', 'text/x-script.scheme', 'video/x-scm', 'text/plain', 'application/sdp', 'application/x-sdp', 'application/sounder', 'application/sea', 'application/x-sea', 'application/set', 'text/sgml', 'text/x-sgml', 'text/sgml', 'text/x-sgml', 'application/x-bsh', 'application/x-sh', 'application/x-shar', 'text/x-script.sh', 'application/x-bsh', 'application/x-shar', 'text/html', 'text/x-server-parsed-html', 'audio/x-psid', 'application/x-sit', 'application/x-stuffit', 'application/x-koan', 'application/x-koan', 'application/x-koan', 'application/x-koan', 'application/x-seelogo', 'application/smil', 'application/smil', 'audio/basic', 'audio/x-adpcm', 'application/solids', 'application/x-pkcs7-certificates', 'text/x-speech', 'application/futuresplash', 'application/x-sprite', 'application/x-sprite', 'application/x-wais-source', 'text/x-server-parsed-html', 'application/streamingmedia', 'application/vnd.ms-pki.certstore', 'application/step', 'application/sla', 'application/vnd.ms-pki.stl', 'application/x-navistyle', 'application/step', 'application/x-sv4cpio', 'application/x-sv4crc', 'image/vnd.dwg', 'image/x-dwg', 'application/x-world', 'x-world/x-svr', 'application/x-shockwave-flash', 'application/x-troff', 'text/x-speech', 'application/x-tar', 'application/toolbook', 'application/x-tbook', 'application/x-tcl', 'text/x-script.tcl', 'text/x-script.tcsh', 'application/x-tex', 'application/x-texinfo', 'application/x-texinfo', 'application/plain', 'text/plain', 'application/gnutar', 'application/x-compressed', 'image/tiff', 'image/x-tiff', 'image/tiff', 'image/x-tiff', 'application/x-troff', 'audio/tsp-audio', 'application/dsptype', 'audio/tsplayer', 'text/tab-separated-values', 'image/florian', 'text/plain', 'text/x-uil', 'text/uri-list', 'text/uri-list', 'application/i-deas', 'text/uri-list', 'text/uri-list', 'application/x-ustar', 'multipart/x-ustar', 'application/octet-stream', 'text/x-uuencode', 'text/x-uuencode', 'application/x-cdlink', 'text/x-vcalendar', 'application/vda', 'video/vdo', 'application/groupwise', 'video/vivo', 'video/vnd.vivo', 'video/vivo', 'video/vnd.vivo', 'application/vocaltec-media-desc', 'application/vocaltec-media-file', 'audio/voc', 'audio/x-voc', 'video/vosaic', 'audio/voxware', 'audio/x-twinvq-plugin', 'audio/x-twinvq', 'audio/x-twinvq-plugin', 'application/x-vrml', 'model/vrml', 'x-world/x-vrml', 'x-world/x-vrt', 'application/x-visio', 'application/x-visio', 'application/x-visio', 'application/wordperfect6.0', 'application/wordperfect6.1', 'application/msword', 'audio/wav', 'audio/x-wav', 'application/x-qpro', 'image/vnd.wap.wbmp', 'application/vnd.xara', 'application/msword', 'application/x-123', 'windows/metafile', 'text/vnd.wap.wml', 'application/vnd.wap.wmlc', 'text/vnd.wap.wmlscript', 'application/vnd.wap.wmlscriptc', 'application/msword', 'application/wordperfect', 'application/wordperfect', 'application/wordperfect6.0', 'application/wordperfect', 'application/wordperfect', 'application/x-wpwin', 'application/x-lotus', 'application/mswrite', 'application/x-wri', 'application/x-world', 'model/vrml', 'x-world/x-vrml', 'model/vrml', 'x-world/x-vrml', 'text/scriplet', 'application/x-wais-source', 'application/x-wintalk', 'image/x-xbitmap', 'image/x-xbm', 'image/xbm', 'video/x-amt-demorun', 'xgl/drawing', 'image/vnd.xiff', 'application/excel', 'application/excel', 'application/x-excel', 'application/x-msexcel', 'application/excel', 'application/vnd.ms-excel', 'application/x-excel', 'application/excel', 'application/vnd.ms-excel', 'application/x-excel', 'application/excel', 'application/x-excel', 'application/excel', 'application/x-excel', 'application/excel', 'application/vnd.ms-excel', 'application/x-excel', 'application/excel', 'application/vnd.ms-excel', 'application/x-excel', 'application/excel', 'application/vnd.ms-excel', 'application/x-excel', 'application/x-msexcel', 'application/excel', 'application/x-excel', 'application/excel', 'application/x-excel', 'application/excel', 'application/vnd.ms-excel', 'application/x-excel', 'application/x-msexcel', 'audio/xm', 'application/xml', 'text/xml', 'xgl/movie', 'application/x-vnd.ls-xpix', 'image/x-xpixmap', 'image/xpm', 'image/png', 'video/x-amt-showrun', 'image/x-xwd', 'image/x-xwindowdump', 'chemical/x-pdb', 'application/x-compress', 'application/x-compressed', 'application/x-compressed', 'application/x-zip-compressed', 'application/zip', 'multipart/x-zip', 'application/octet-stream', 'text/x-script.zsh'],
                        'allowedPreviewMimeTypes': ['text/plain'],
                        'disabledPreviewExtensions': ['dng'],
                        'layoutTemplates': {
                            // 'size': ' <samp>({sizeText})</samp>',
                            // 'footer': '<div class="file-thumbnail-footer">\n' +
                            //     '<div class="file-footer-caption" title="{caption}">\n' +
                            //     '<div class="file-caption-info">{caption}</div>\n' +
                            //     '<div class="file-size-info">{size}</div>\n' +
                            //     '</div>\n' +
                            //     '{progress}\n{indicator}\n{actions}\n' +
                            //     '</div>',
                            'actions': '<div class="file-actions">\n' +
                                '<div class="file-footer-buttons">\n' +
                                //'{download} {upload} {delete} {other}' +
                                '{delete}' +
                                '</div>\n' +
                                '</div>\n' +
                                '<div class="clearfix"></div>',
                            'main1': "" +
                                "<div class=\'input-group {class}\'>\n" +
                                "   <div class=\'input-group-btn\ input-group-prepend'>\n" +
                                "       {browse}\n" +
                                "       {upload}\n" +
                                "       {remove}\n" +
                                "   </div>\n" +
                                "   {caption}\n" +
                                "</div>" +
                                "{preview}\n",
                            'preview': '<div class="file-preview {class}">\n' +
                                '    {close}\n' +
                                '    <div class="close fileinput-remove">×</div>\n' +
                                '    <div class="file-preview-status text-center text-success"></div>\n' +
                                '    <div class="kv-fileinput-error"></div>\n' +
                                '    <div class="{dropClass}">\n' +
                                '    <div class="file-preview-thumbnails">\n' +
                                '    </div>\n' +
                                '    <div class="clearfix"></div>' +
                                '    </div>\n' +
                                '</div>',
                            'progress': '<div class="progress">\n' +
                                '    <div class="progress-bar progress-bar-success progress-bar-striped text-center" role="progressbar" aria-valuenow="{percent}" aria-valuemin="0" aria-valuemax="100" style="width:{percent}%;">\n' +
                                '        {status}\n' +
                                '     </div>\n' +
                                '</div>\n' +
                                '{stats}',
                            'stats': '<div class="text-info file-upload-stats">' +
                                '<span class="pending-time">{pendingTime}</span> ' +
                                '<span class="upload-speed">{uploadSpeed}</span>' +
                                '</div>'
                        },
                        'preferIconicPreview': false,
                        /** this will force thumbnails to display icons for following file extensions*/
                        'previewFileIconSettings': { /** configure your icon file extensions*/
                            'doc': '<i class="glyphicon glyphicon-file text-primary"></i>',
                            'xls': '<i class="glyphicon glyphicon-file text-success"></i>',
                            'ppt': '<i class="glyphicon glyphicon-file text-danger"></i>',
                            'pdf': '<i class="glyphicon glyphicon-file text-danger"></i>',
                            'zip': '<i class="glyphicon glyphicon-file text-muted"></i>',
                            //'htm': '<i class="glyphicon glyphicon-file text-info"></i>',
                            'txt': '<i class="glyphicon glyphicon-file text-info"></i>',
                            'mov': '<i class="glyphicon glyphicon-file text-warning"></i>',
                            'mp3': '<i class="glyphicon glyphicon-file text-warning"></i>',
                            'indd': '<i class="glyphicon glyphicon-file text-primary"></i>',
                            'dng': '<i class="glyphicon glyphicon-file text-primary"></i>',
                        },
                        'hideThumbnailContent': false,
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
                            },
                            'indd': function(ext) {
                                return ext.match(/(indd)$/i);
                            },
                            'dng': function(ext) {
                                return ext.match(/(dng)$/i);
                            }
                        },
                        uploadExtraData: function(previewId, index) {
                            var info = {
                                "case_id": case_id_uploading_to,
                                "encrypted_case_id": encrypted_case_id_uploading_to
                            };
                            return info;
                        },
                    }).on('filebeforeload', function(event, file, index, reader) {
                        // perform your validations based on the 'file' or other parameters
                        // if (file.name === 'UNAPPROVED_FILE.txt') {

                        //     return false; // will abort the file loading for the selected file
                        // }

                        /**console.log('filebeforeload');*/
                        /**console.log(fstack);*/
                        /**console.log(count_files_success);*/

                        if (fstack.length != 0 && count_files_success != 0 && fstack.length == count_files_success) {
                            $('input[data-ident=upload_contract_mass_upload_file]').fileinput('clear');
                            count_files_success = 0;
                            fstack = [];
                        }
                    }).on('fileloaded', function(event, file, previewId, index, reader) {
                        /**console.log("fileloaded");*/

                        /** if there are any uploaded content then when they try to load more files clear the file input */
                        /** per each loaded */
                        /** i want to clear all uploaded */
                    }).on('filebatchselected', function(event, files) {
                        console.log('filebatchselected');

                        var filestack = $('input[data-ident=upload_contract_mass_upload_file]').fileinput('getFileStack');
                        fstack = [];
                        $.each(filestack, function(fileId, fileObj) {
                            if (fileObj !== undefined) {
                                fstack.push(fileObj);
                            }
                        });
                        console.log('Files selected - ' + fstack.length);
                        console.log(fstack);

                        if (case_id_uploading_to == undefined) {
                            $('.fileinput-upload-button').css('display', 'none');
                        }
                    }).on('fileselect', function(event, numFiles, label) {
                        console.log('file selected');
                        var res = label.split(".");
                        var extension = res[res.length - 1];
                        /**console.log(extension);*/

                        if(extension == 'zip' || extension == 'rar'){
                            $.alert({
                                title: 'At this moment uploading .'+extension+' is not supported due to the handling on amazon side',
                                content: 'the files for caseID= ' + case_id_uploading_to + ' will not be uploaded.',
                                type: 'red',
                                draggable: false,
                                backgroundDismiss: 'ok',
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
                                            $('input[data-ident=upload_contract_mass_upload_file]').fileinput('clear');
                                            is_br24_employee_select_selectize.enable();
                                            $('.remove-single').css('display', '');
                                            $('.close.fileinput-remove').css('display', '');
                                            return false;
                                        }
                                    },
                                    // cancel: {
                                    //     text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                    //     action: function() {
                                    //         //$.alert('');
                                    //         return false;
                                    //     }
                                    // },
                                }
                            });
                        }
                        //$('.kv-file-content').css('width', '100%');
                        //$('.kv-preview-data').css('width', '100%');
                        //$('.krajee-default.file-preview-frame .kv-file-content').css('width', '100%');
                        $('.file-drop-zone').css('height', '100%');
                        $('#upload_contract_mass_upload').prop('disabled', false);
                    }).on('filecleared', function(event) {
                        console.log("filecleared");
                        /** using the clear button on the file input */
                        count_files_success = 0;
                        fstack = [];
                    }).on('filereset', function(event) {

                        /** why is this being called? */
                        console.log("filereset");
                    }).on('filepreremove', function(event, id, index) {
                        console.log('id = ' + id + ', index = ' + index);
                        /***/
                    }).on('fileremoved', function(event, id, index) {
                        /**console.log('id = ' + id + ', index = ' + index);*/
                        /** when file is removed update the fstack */
                        console.log('fileremoved');

                        var filestack = $('input[data-ident=upload_contract_mass_upload_file]').fileinput('getFileStack');
                        fstack = [];
                        $.each(filestack, function(fileId, fileObj) {
                            if (fileObj !== undefined) {
                                fstack.push(fileObj);
                            }
                        });
                        console.log('Files selected - ' + fstack.length);
                        console.log(fstack);
                    }).on('fileuploaded', function(event, previewId, index, fileId) {
                        console.log('fileuploaded');
                        /**console.log(previewId.jqXHR.responseText);*/
                        /**console.log(previewId.jqXHR);*/

                        if (previewId.jqXHR.responseJSON.success == true) {
                            count_files_success++;
                            /**console.log('count_files_success = ' + count_files_success);*/
                        }
                    }).on('filebatchuploadsuccess', function(event, data) {
                        console.log('filebatchuploadsuccess');
                        // var form = data.form,
                        //     files = data.files,
                        //     extra = data.extra,
                        //     response = data.response,
                        //     reader = data.reader;
                    }).on('filebatchuploadcomplete', function(event, files, extra) {
                        console.log('filebatchuploadcomplete');


                        console.log(fstack.length);
                        console.log(count_files_success);
                        /** we use this check */
                        if (fstack.length == count_files_success) {
                            /** send the trigger to check the directory and perform the zipping and the schedule task should take care of the rest */
                            is_br24_employee_select_selectize.enable();
                            $('.remove-single').css('display', '');
                            $('.close.fileinput-remove').css('display', '');


                            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

                            var ajaxdata = {
                                'case_id': case_id_uploading_to,
                                'encrypted_case_id': encrypted_case_id_uploading_to,
                                'fstack': JSON.stringify(fstack)
                            };

                            /**console.log(ajaxdata);*/

                            /** by this point we want to trigger an event on server side to start the zipping and sending to s3.. without the need for the client to stick around for that to happen */
                            /** if it reaches here we can safely assume that the file integrity of all the files uploaded are 100% the same as those that can be found on the server */
                            // app.data.oppC_getCheckUploadedFilesOfCaseID
                            app.ajax.jsonGET(app.data.oppC_getTriggerManualUploadStartEventOfCaseID, ajaxdata, null, function() {
                                //console.log(app.ajax.result);
                                if (app.ajax.result.success == true) {

                                    $.alert({
                                        title: 'Files saved to Download Upload Server',
                                        content: 'the files for caseID= ' + case_id_uploading_to + ' have been entered into the upload queue. It will be zipped and uploaded to s3 momentarily.',
                                        //content: 'các tệp cho caseOD = '+ $ (' # is_br24_employee '). val () [0] +' đã được nhập vào hàng đợi tải lên. Nó sẽ được nén và tải lên s3 trong giây lát.',
                                        type: 'green'
                                    });
                                    // window.REPLACEBYRENAMEtable.ajax.reload(null, false);
                                    // app.util.fullscreenloading_end();
                                    // window.REPLACEBYRENAMEtable.fixedHeader.adjust();
                                    // app.util.nprogressdone();
                                    // if (app.ajax.result.process_penalties_accept_reject_table_sync == true) {
                                    //     app_xxx.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                    // }
                                    count_files_success = 0;
                                    fstack = [];
                                } else {
                                    // app.util.nprogressdone();

                                    $.alert({
                                        title: 'Files saved to Download Upload Server but there were errors',
                                        content: 'the files for caseID= ' + case_id_uploading_to + ' have been entered into the upload queue. But there were errors when trying to zip. ' + app.ajax.result.unzip_log,
                                        //content: 'các tệp cho caseOD = '+ $ (' # is_br24_employee '). val () [0] +' đã được nhập vào hàng đợi tải lên. Nó sẽ được nén và tải lên s3 trong giây lát.',
                                        type: 'red'
                                    });

                                    /** they will be tempted to try again ... in which case you need to make sure that the previous files are removed entirely all the way up to s3 */
                                    /** we grab the try attepts value from the selectize element and pass it along with the files and compare */
                                    /** if the numbers are different... we have to also make sure to remove the previous files entirely all the way up to s3 */
                                }
                                app.util.fullscreenloading_end();
                            });

                        } else {

                            is_br24_employee_select_selectize.enable();
                            $('.remove-single').css('display', '');
                            $('.close.fileinput-remove').css('display', '');

                            app.util.fullscreenloading_end();

                            $.alert({
                                title: 'Fail!',
                                content: 'Fail',
                                type: 'red'
                            });

                            /** there were an unequal amount of files successful we will have to notify and someone has to handle it */
                            /** because we obviously have to clear the uploaded files? */
                            /** they may attempt to only upload the ones that didn't work */
                            /** we will notify by that they have to upload everything again..and tell them that nothing has been saved or zipped */
                            /** they need to fix the errors first */
                        }

                    }).on('filepreupload', function(event, data, previewId, index) {
                        //console.log('File pre upload triggered');
                        // var form = data.form;
                        // var files = data.files;
                        // var extra = data.extra;
                        // var response = data.response;
                        // var reader = data.reader;
                    }).on('fileimagesloaded', function(event) {
                        console.log("fileimagesloaded");
                        /** after all are loaded */
                    }).on('fileduplicateerror', function(event, file, fileId, caption, size, id, index) {
                        // console.log(file);
                        // console.log(fileId);
                        // console.log(size);
                        // console.log(id);
                        // console.log(index);
                        // console.log(caption);
                    }).on('fileuploaderror', function(event, data, msg) {
                        var form = data.form,
                            files = data.files,
                            extra = data.extra,
                            response = data.response,
                            reader = data.reader;
                        /**console.log('File upload error');*/
                        console.log('File upload error', data.index, data.fileId, msg);

                        $.alert({
                            title: 'File Fail error!',
                            boxWidth: '700px',
                            useBootstrap: false,
                            content: msg,
                            type: 'red'
                        });

                        $('input[data-ident=upload_contract_mass_upload_file]').fileinput('cancel');

                        is_br24_employee_select_selectize.enable();
                        $('.remove-single').css('display', '');
                        $('.close.fileinput-remove').css('display', '');
                    });


                    $('.fileinput-upload-button').click(function(event) {
                        app.util.fullscreenloading_start();
                        is_br24_employee_select_selectize.disable();
                        $('.remove-single').css('display', 'none');
                        $('.close.fileinput-remove').css('display', 'none');
                    });

                    //onclick event open dialogue
                    $('input.file-caption-name').prop('disabled', true).css('cursor', 'pointer');
                    $('input.file-caption-name').click(function(event) {
                        event.stopPropagation();
                        $('input[type=file]').trigger('click');
                    });
                    $('input[type=file]').change(function() {
                        $('input.file-caption-name').val($(this).val());
                    });
                    $('div.file-caption').click(function(event) {
                        event.stopPropagation();
                        $('input[type=file]').trigger('click');
                    });
                    $('div.file-drop-zone').css('cursor', 'pointer');
                    $('div.file-drop-zone').click(function(event) {
                        event.stopPropagation();
                        $('input[type=file]').trigger('click');
                    });
                    $('.kv-file-remove').click(function(event) {
                        event.stopImmediatePropagation();
                        /**console.log('clicked kv-file-remove');*/
                    });
                    $('.kv-file-download').click(function(event) {
                        event.stopImmediatePropagation();
                        /**console.log('clicked kv-file-download');*/
                    });
                    $('.file-preview-thumbnails').click(function(event) {
                        event.stopImmediatePropagation();
                        /**console.log('clicked kv-file-thumbnails');*/
                    });
                    $('.kv-fileinput-error').click(function(event) {
                        event.stopImmediatePropagation();
                        /**console.log('clicked kv-file-error');*/
                    });
                    $('.fileinput-upload').click(function(event) {
                        /** reset the counter only if we click on the upload button */
                        count_files_success = 0;
                        /** don't reset the filestack */
                        //fstack = [];
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
            },
        },
    },
    manage_downloadlist: { //Manage downloadlist
        profile: {
            init: function() {
                $(window).on('load', function() {
                    app.util.period();
                    //app.util.nprogressinit();
                    $('.previous-surround, .next-surround').css('display', 'none');
                    NProgress.configure({ parent: '#ibox-title', showSpinner: false });
                    app_ops.manage_downloadlist.profile.init_next();
                    app.util.fixedheaderviewporthandler();
                    console.log('manage_downloadlist.index');

                    window.newdataimported = false;
                    /** https://stackoverflow.com/questions/43066633/laravel-echo-does-not-listen-to-channel-and-events?rq=1 */
                    /** must have the . infront of the event Class Name ? */
                    //console.log(window.Echo);
                    // window.Echo.channel('new_autodl_job_data').listen('NewAutoDLJobData', (e) => {
                    //     console.log(e);
                    //     window.downloadlisttable.ajax.reload(null, false);
                    //     /**console.log('reloaded ajax window.downloadlisttable');*/
                    //     // window.location.reload();
                    //     /** if there are are actions currently doing such as with a row in the table */
                    //     /** refresh the auto dl table */
                    //     // // if ($("#colorbox").css("display") == "block") {
                    //     // //     console.log('ColorBox is currently open');
                    //     // // }else{
                    //     // //     console.log('ColorBox is currently closed');
                    //     // // }

                    //     // if ($("#colorbox").css("display") == "block") {
                    //     //     /**$('#cb_id_date_zoom_form').hasClass('dirty')*/
                    //     //     /** have some unsaved info so should not refresh just yet */
                    //     //     /** when they save the data it will refresh anyway */
                    //     //     /** but when they close refresh */
                    //     //     window.newdataimported = true;
                    //     // } else {
                    //     //     $("#cb_id_date_zoom.ajax").colorbox.close();
                    //     //     window.twoweektimesheetshiftplannertable.ajax.reload(null, false);
                    //     //     app.util.fullscreenloading_end();
                    //     //     window.twoweektimesheetshiftplannertable.fixedHeader.adjust();
                    //     // }
                    // });
                });
            },
            init_next: function() {
                var first_doc_refferer = document.referrer;
                var data = [];
                //what this part is doing is checking if more than one browser tab of the same route is trying to be opened at the same time... 
                var browser_tab_already_open_load = JSON.parse(localStorage.getItem('Br24_' + app.env() + '_managedownloadlistinfo_oomrbnoa'));
                if (browser_tab_already_open_load !== null) {
                    //if the key exists it means the tab is open still.
                    if (first_doc_refferer === undefined || first_doc_refferer === null || first_doc_refferer == '') {
                        $('.loader').append('<span style="position: absolute; top: 45%; left: 50%; transform: translateX(-50%) translateY(-50%); font-size: 22px; color: red; text-align:center">Another Manage downloadlist tab is open already.<br>for security reasons only one instance is allowed<br><a style="font-size:10px" href="/downloadlist">Back Home</a></span>');
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
                        $('.loader').append('<span style="position: absolute; top: 45%; left: 50%; transform: translateX(-50%) translateY(-50%); font-size: 22px; color: red; text-align:center">Another Manage downloadlist tab is open already.<br>for security reasons only one instance is allowed<br><a style="font-size:10px" href="/downloadlist">Back Home</a></span>');
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
                    var check_content = $('#br24-navbar').children();
                    if( check_content.length <= 0){
                        /***/
                        console.log("try reloading again but clear cache first");
                        localStorage.removeItem('Br24_' + app.env() + '_managedownloadlistinfo_oomrbnoa');
                        window.location.reload();
                    }
                } else {

                    data['oomrbnoa'] = { "managedownloadlist_browsertabstatus": "open", "timestamp": new Date().getTime() };
                    localStorage.setItem('Br24_' + app.env() + '_managedownloadlistinfo_oomrbnoa', JSON.stringify(data['oomrbnoa']));

                    app_ops.manage_downloadlist.profile.get_downloadlistInfo_tab();

                    var onlycallonce = 1;
                    $(document).ajaxStop(function() {
                        if (onlycallonce == 1) {
                            //console.log("All AJAX requests completed");
                            //app_ops.manage_downloadlist.profile.table();
                            app_ops.manage_downloadlist.profile.add_edit_downloadlist_colorbox();
                            onlycallonce = 2;
                        }
                    });

                    //the key to indicate that a tab with that employee detail is already open; will be removed from the localstorage
                    //be careful this also happens on form submit so should not remove the keys that allow this section to work
                    window.onbeforeunload = function() {};
                    window.onunload = function() {
                        localStorage.removeItem('Br24_' + app.env() + '_managedownloadlistinfo_oomrbnoa');
                        //sessionStorage.removeItem('Br24_' + app.env() + '_managedownloadlistTable_table_downloadlistTable');
                    };
                    //how to detect when the only tab for the employee that is open is closed?
                    //at that point we want to clear the keys for the other items.
                }
            },
            add_edit_downloadlist_colorbox: function() {
                app_ops.manage_downloadlist.profile.downloadlist_table();
                app_ops.manage_downloadlist.profile.handlefixedheaderpinning();
            },
            downloadlist_table: function() {
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

                var global_search_value = '';
                var byStatus_value = '';
                var byCompanyPosition_value = '';
                var byCompanyDepartment_value = '';
                var byEditorLevel_value = '';
                var byEnabledDisabled_value = '';
                var byJobStatus_value = '';
                window.byHideShowColumn_value = '';
                window.byShowColumn_value = '';
                window.byHideColumn_value = '';
                var byAssignee_value = '';
                var byToolClient_value = '';

                /** to make it work with just one datepicker you have to supply the singular date */
                var default_datepicker_date_value = app.data.default_datepicker_date;
                var prev_month_min_date_value = app.data.minDatefordatefromfilter;
                var maxDatefordatefromfilter_prep = app.data.maxDatefordatefromfilter;
                var min_date_value = app.data.timesheet_period.firstdateofMonth;
                var max_date_value = app.data.timesheet_period.lastdateofMonth;
                var prev_min_date_value = app.data.timesheet_period.firstdateofMonth;
                var prev_max_date_value = app.data.timesheet_period.lastdateofMonth;
                var minD_YYYY = '';
                var minD_MM = '';
                var minD_DD = '';
                var maxD_YYYY = '';
                var maxD_MM = '';
                var maxD_DD = '';


                var report_date_value = null;
                var prev_month_from = prev_month_min_date_value.split(delimiter_for_splitting_variable);
                var max_month_from = maxDatefordatefromfilter_prep.split(delimiter_for_splitting_variable);
                var from = min_date_value.split("-"); /** from timesheet_period will always be YYY-MM-DD*/
                var to_date = max_date_value.split("-"); /** from timesheet_period will always be YYY-MM-DD*/
                /** relocate according to locale */
                if (app.data.locale === 'vi') {
                    minD_YYYY = parseInt(prev_month_from[2]);
                    minD_MM = parseInt(prev_month_from[1]) - 1;
                    minD_DD = parseInt(prev_month_from[0]);

                    maxD_YYYY = parseInt(max_month_from[2]);
                    maxD_MM = parseInt(max_month_from[1]) - 1;
                    maxD_DD = parseInt(max_month_from[0]);
                } else if (app.data.locale === 'en') {
                    minD_YYYY = parseInt(prev_month_from[2]);
                    minD_MM = parseInt(prev_month_from[1]) - 1;
                    minD_DD = parseInt(prev_month_from[0]);

                    maxD_YYYY = parseInt(max_month_from[2]);
                    maxD_MM = parseInt(max_month_from[1]) - 1;
                    maxD_DD = parseInt(max_month_from[0]);
                } else if (app.data.locale === 'de') {
                    minD_YYYY = parseInt(prev_month_from[2]);
                    minD_MM = parseInt(prev_month_from[1]) - 1;
                    minD_DD = parseInt(prev_month_from[0]);

                    maxD_YYYY = parseInt(max_month_from[2]);
                    maxD_MM = parseInt(max_month_from[1]) - 1;
                    maxD_DD = parseInt(max_month_from[0]);
                }

                var default_datepicker_date = default_datepicker_date_value.split('-');
                /** if i can somehow use the cache period to set the default date on open */
                var default_minD_YYYY = parseInt(default_datepicker_date[0]);
                var default_minD_MM = parseInt(default_datepicker_date[1]) - 1;
                var default_minD_DD = parseInt(default_datepicker_date[2]);
                var defaultDateVARIABLE = new Date(default_minD_YYYY, default_minD_MM, default_minD_DD)
                // console.log(min_date_value);
                // console.log(max_date_value);
                // console.log(min_date_value);
                // console.log(max_date_value);

                // console.log(minD_YYYY);
                // console.log(minD_MM);
                // console.log(minD_DD);
                // console.log(maxD_YYYY);
                // console.log(maxD_MM);
                // console.log(maxD_DD);


                var editing_configuration = null;
                /** don't refresh the table if clicking directly from one input to another input */
                var keep_track_of_last_clicked_item_to_react_after_reload = null;
                var keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = null;
                var keep_track_of_input_changes_to_assist_screen_load_input_disable = null;

                var keep_track_of_last_clicked_easyautocomplete_to_reopen = null;
                var keep_track_of_last_clicked_item_to_react_after_reload_2 = null;
                var keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2 = null;
                /** in this scenario we are setting the filters up at the begining with the begining of the current month and the current date */

                var only_trigger_once = null;
                var re_open_requested = null;

                var reopen_refreshIntervalId = null;
                var reopen_timer = function() {
                    reopen_refreshIntervalId = setInterval(function() {


                        /**console.log('============================================================================TIMER===INSIDE==================================');*/
                        /**console.log('keep_track_of_last_clicked_item_to_react_after_reload');*/
                        /**console.log(keep_track_of_last_clicked_item_to_react_after_reload);*/
                        /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload');*/
                        /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload);*/
                        /**console.log('keep_track_of_last_clicked_easyautocomplete_to_reopen');*/
                        /**console.log(keep_track_of_last_clicked_easyautocomplete_to_reopen);*/
                        /**console.log('keep_track_of_last_clicked_item_to_react_after_reload_2');*/
                        /**console.log(keep_track_of_last_clicked_item_to_react_after_reload_2);*/
                        /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2');*/
                        /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2);*/
                        /**console.log('re_open_requested');*/
                        /**console.log(re_open_requested);*/
                        /**console.log('============================================================================TIMER===INSIDE==================================');*/




                        $(".dataTable tbody td.parent." + keep_track_of_last_clicked_item_to_react_after_reload_2 + "[data-employee='" + keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2 + "']").trigger('click');
                        clearInterval(reopen_refreshIntervalId); //stop the timer called reopen_refreshIntervalId
                        keep_track_of_last_clicked_easyautocomplete_to_reopen = null;

                        keep_track_of_last_clicked_item_to_react_after_reload_2 = null;
                        keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2 = null;
                    }, 100);
                };


                var filter_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_managedownloadlistTable_table_downloadlistTable'));
                /**console.log("FIRST", filter_load);*/
                /** this grab from the sessions variable is to return the query variables to their set state */
                if (filter_load !== null) {
                    if (filter_load['filters']['global_search'] !== "") {
                        global_search_value = filter_load['filters']['global_search'];
                    }

                    if (filter_load['filters']['byStatus'] !== "") {
                        byStatus_value = filter_load['filters']['byStatus'];
                    }

                    if (filter_load['filters']['byTeam'] !== "") {
                        byCompanyDepartment_value = filter_load['filters']['byTeam'];
                    }

                    if (filter_load['filters']['byPosition'] !== "") {
                        byCompanyPosition_value = filter_load['filters']['byPosition'];
                    }

                    if (filter_load['filters']['byEditor_level'] !== "") {
                        byEditorLevel_value = filter_load['filters']['byEditor_level'];
                    }

                    if (filter_load['filters']['byEnabledDisabled'] !== "") {
                        byEnabledDisabled_value = filter_load['filters']['byEnabledDisabled'];
                    }
                    if (filter_load['filters']['byJobStatus'] !== "") {
                        byJobStatus_value = filter_load['filters']['byJobStatus'];
                    }

                    if (filter_load['filters']['byHideShowColumn_val'] !== "") {
                        window.byHideShowColumn_value = filter_load['filters']['byHideShowColumn_val'];
                    }

                    if (filter_load['filters']['byShowColumn'] !== "") {
                        window.byShowColumn_value = filter_load['filters']['byShowColumn'];
                    }
                    if (filter_load['filters']['byHideColumn'] !== "") {
                        window.byHideColumn_value = filter_load['filters']['byHideColumn'];
                    }

                    if (filter_load['filters']['byAssignee'] !== "") {
                        byAssignee_value = filter_load['filters']['byAssignee'];
                    }
                    if (filter_load['filters']['byToolClient'] !== "") {
                        byToolClient_value = filter_load['filters']['byToolClient'];
                    }
                }

                /** need to provide for the column widths for difference broswers otherwise will look horrible */
                if (app.data.browser_detected == 'Chrome') {
                    var broswer_columnDefs = [
                        { targets: [0], width: '10px' },
                        { targets: [1], width: '10px' },
                        { targets: [2], width: '70px' },
                        { targets: [3], width: '10px' },
                        { targets: [4], width: '10px' },
                        { targets: [5], width: '10px' },
                        { targets: [6], width: '10px' },
                        { targets: [7], width: '10px' },
                        { targets: [8], width: '30px' },
                        { targets: [9], width: '5px' },
                        { targets: [10], width: '200px' },
                        { targets: [12], width: '100px' },
                        { targets: [12], width: '5px' },
                        { targets: [13], width: '30px' },
                        { targets: [14], width: '60px' },
                        { targets: [15, 16, 17], width: '10px' },
                    ];
                    var browser_columns = [
                        //{ orderable: false, searchable: false, data: 'numbering' },
                        { orderable: true, searchable: true, data: 'case_id', className: 'number_of_pictures_col_width' },
                        { orderable: false, searchable: true, data: 'xml_jobid_title', className: 'number_of_pictures_col_width' },
                        { orderable: false, searchable: true, data: 'xml_title_contents', className: 'xml_title_contents_col_width' },
                        { orderable: false, searchable: true, data: 'number_of_pictures', className: 'extra_small_col_width' },
                        { orderable: false, searchable: true, data: 'instructions_col', className: 'extra_small_col_width' },
                        { orderable: false, searchable: true, data: 'preview_col', className: 'extra_small_col_width two_weeks_checkbox_col' },
                        { orderable: false, searchable: true, data: 'output_files_col', className: 'extra_small_col_width' },
                        { orderable: true, searchable: false, data: 'delivery_time', name: 'expected_delivery_date_coalesce_sortorder', className: 'delivery_time_col_width' },
                        { orderable: false, searchable: false, data: 'assignees', className: 'assignee_tags_col_width' },
                        { orderable: false, searchable: false, data: 'custom_row_color_input', className: 'hidden extra_small_col_width'},
                        { orderable: false, searchable: false, data: 'internal_notes', className: 'internal_notes_col_width' },
                        { orderable: false, searchable: false, data: 'job_from', className: 'hidden extra_small_col_width' },
                        { orderable: false, searchable: false, data: 'rating', className: 'hidden assignee_tags_col_width' },
                        { orderable: false, searchable: false, data: 'tags', className: 'assignee_tags_col_width' },
                        { orderable: true, searchable: true, data: 'job_status', name: 'status_grouping_sort_order', className: 'modified_dates_col_with' },
                        { orderable: true, searchable: true, data: 'created_at', name: 'created_at_sortorder', className: 'modified_dates_col_with' },
                        { orderable: false, searchable: false, data: 'last_updated_by', className: 'modified_dates_col_with' },
                        { orderable: true, searchable: false, data: 'last_updated', name: 'last_updated_sortorder', className: 'modified_dates_col_with' },
                        //{ orderable: false, searchable: false, data: 'status_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'expected_delivery_time_custom_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'actions', className: 'hidden' },
                    ];
                } else if (app.data.browser_detected == 'Firefox') {
                    var broswer_columnDefs = [
                        { targets: [0], width: '10px' },
                        { targets: [1], width: '10px' },
                        { targets: [2], width: '10px' },
                        { targets: [3], width: '10px' },
                        { targets: [4], width: '10px' },
                        { targets: [5], width: '10px' },
                        { targets: [6], width: '10px' },
                        { targets: [7], width: '10px' },
                        { targets: [8], width: '10px' },
                        { targets: [9], width: '5px' },
                        { targets: [10], width: '10px' },
                        { targets: [11], width: '10px' },
                        { targets: [12], width: '5px' },
                        { targets: [13], width: '10px' },
                        { targets: [14], width: '10px' },
                        { targets: [15, 16, 17], width: '10px' },
                    ];
                    var browser_columns = [
                        //{ orderable: false, searchable: false, data: 'numbering' },
                        { orderable: true, searchable: true, data: 'case_id', className: 'number_of_pictures_col_width' },
                        { orderable: false, searchable: true, data: 'xml_jobid_title', className: 'number_of_pictures_col_width' },
                        { orderable: false, searchable: true, data: 'xml_title_contents', className: 'xml_title_contents_col_width' },
                        { orderable: false, searchable: true, data: 'number_of_pictures', className: 'extra_small_col_width' },
                        { orderable: false, searchable: true, data: 'instructions_col', className: 'extra_small_col_width' },
                        { orderable: false, searchable: true, data: 'preview_col', className: 'extra_small_col_width two_weeks_checkbox_col' },
                        { orderable: false, searchable: true, data: 'output_files_col', className: 'extra_small_col_width' },
                        { orderable: true, searchable: false, data: 'delivery_time', name: 'expected_delivery_date_coalesce_sortorder', className: 'delivery_time_col_width' },
                        { orderable: false, searchable: false, data: 'assignees', className: 'assignee_tags_col_width' },
                        { orderable: false, searchable: false, data: 'custom_row_color_input', className: 'hidden extra_small_col_width'},
                        { orderable: false, searchable: false, data: 'internal_notes', className: 'internal_notes_col_width' },
                        { orderable: false, searchable: false, data: 'job_from', className: 'hidden extra_small_col_width' },
                        { orderable: false, searchable: false, data: 'rating', className: 'hidden assignee_tags_col_width' },
                        { orderable: false, searchable: false, data: 'tags', className: 'assignee_tags_col_width' },
                        { orderable: true, searchable: true, data: 'job_status', name: 'status_grouping_sort_order', className: 'modified_dates_col_with' },
                        { orderable: true, searchable: true, data: 'created_at', name: 'created_at_sortorder', className: 'modified_dates_col_with' },
                        { orderable: false, searchable: false, data: 'last_updated_by', className: 'modified_dates_col_with' },
                        { orderable: true, searchable: false, data: 'last_updated', name: 'last_updated_sortorder', className: 'modified_dates_col_with' },
                        //{ orderable: false, searchable: false, data: 'status_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'expected_delivery_time_custom_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'actions', className: 'hidden' },
                    ];
                } else {
                    /** use fallback */
                    var broswer_columnDefs = [
                        { targets: [0], width: '10px' },
                        { targets: [1], width: '10px' },
                        { targets: [2], width: '70px' },
                        { targets: [3], width: '10px' },
                        { targets: [4], width: '10px' },
                        { targets: [5], width: '10px' },
                        { targets: [6], width: '10px' },
                        { targets: [7], width: '10px' },
                        { targets: [8], width: '30px' },
                        { targets: [9], width: '5px' },
                        { targets: [10], width: '200px' },
                        { targets: [11], width: '100px' },
                        { targets: [12], width: '5px' },
                        { targets: [13], width: '30px' },
                        { targets: [14], width: '60px' },
                        { targets: [15, 16, 17], width: '10px' },
                    ];
                    var browser_columns = [
                        //{ orderable: false, searchable: false, data: 'numbering' },
                        { orderable: true, searchable: true, data: 'case_id', className: 'number_of_pictures_col_width' },
                        { orderable: false, searchable: true, data: 'xml_jobid_title', className: 'number_of_pictures_col_width' },
                        { orderable: false, searchable: true, data: 'xml_title_contents', className: 'xml_title_contents_col_width' },
                        { orderable: false, searchable: true, data: 'number_of_pictures', className: 'extra_small_col_width' },
                        { orderable: false, searchable: true, data: 'instructions_col', className: 'extra_small_col_width' },
                        { orderable: false, searchable: true, data: 'preview_col', className: 'extra_small_col_width two_weeks_checkbox_col' },
                        { orderable: false, searchable: true, data: 'output_files_col', className: 'extra_small_col_width' },
                        { orderable: true, searchable: false, data: 'delivery_time', name: 'expected_delivery_date_coalesce_sortorder', className: 'delivery_time_col_width' },
                        { orderable: false, searchable: false, data: 'assignees', className: 'assignee_tags_col_width' },
                        { orderable: false, searchable: false, data: 'custom_row_color_input', className: 'hidden extra_small_col_width'},
                        { orderable: false, searchable: false, data: 'internal_notes', className: 'internal_notes_col_width' },
                        { orderable: false, searchable: false, data: 'job_from', className: 'hidden extra_small_col_width' },
                        { orderable: false, searchable: false, data: 'rating', className: 'hidden assignee_tags_col_width' },
                        { orderable: false, searchable: false, data: 'tags', className: 'assignee_tags_col_width' },
                        { orderable: true, searchable: true, data: 'job_status', name: 'status_grouping_sort_order', className: 'modified_dates_col_with' },
                        { orderable: true, searchable: true, data: 'created_at', name: 'created_at_sortorder', className: 'modified_dates_col_with' },
                        { orderable: false, searchable: false, data: 'last_updated_by', className: 'modified_dates_col_with' },
                        { orderable: true, searchable: false, data: 'last_updated', name: 'last_updated_sortorder', className: 'modified_dates_col_with' },
                        //{ orderable: false, searchable: false, data: 'status_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'expected_delivery_time_custom_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'actions', className: 'hidden' },
                    ];
                }

                window.downloadlisttable = $('#downloadlistTable').DataTable({
                    pageLength: app.conf.table.pageLength,
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
                        headerOffset: 0,
                        footerOffset: 0
                    },
                    //fixedColumns: true,
                    // colResize: {
                    //     fixedHeader: {
                    //         bottom: true,
                    //     }
                    // },
                    aaSorting: [],
                    stateSave: true,
                    stateDuration: -1,
                    searching: false,
                    stateSaveCallback: function(settings, data) {
                        /** checkbox variables */
                        var status_all = $("#status_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var team_all = $("#team_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var position_all = $("#position_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var editor_level_all = $("#editor_level_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var job_status_all = $("#jobstatus_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var assignee_all = $("#assignees_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var tool_client_all = $("#tool_client_filter option:selected").map(function() { return $(this).val(); }).get().join('|');

                        /** input variables also query init */
                        var global_search_all = $("#global_search_filter").val();

                        var locale_date_format = prefered_dateFormat;

                        data['filters'] = {
                            /** checkboxes */
                            "status_cb": status_all,
                            "team_cb": team_all,
                            "position_cb": position_all,
                            "editor_level_cb": editor_level_all,
                            "job_status_cb": job_status_all,
                            "assignee_cb": assignee_all,
                            "tool_client_cb": tool_client_all,

                            /** query */
                            "byEnabledDisabled": byEnabledDisabled_value,
                            "byStatus": byStatus_value,
                            "byTeam": byCompanyDepartment_value,
                            "byPosition": byCompanyPosition_value,
                            "byEditor_level": byEditorLevel_value,
                            "byJobStatus": byJobStatus_value,
                            "byAssignee": byAssignee_value,
                            "byToolClient": byToolClient_value,

                            "byHideShowColumn_val": window.byHideShowColumn_value,
                            "byHideColumn": window.byHideColumn_value,
                            "byShowColumn": window.byShowColumn_value,

                            "locale_date_format": locale_date_format,

                            "global_search": global_search_all
                        };
                        /**console.log("setting sessionStorage AAA");*/
                        /**console.log("data", data);*/
                        sessionStorage.setItem('Br24_' + app.env() + '_managedownloadlistTable_table_' + settings.sInstance, JSON.stringify(data));
                    },
                    stateLoadCallback: function(settings) {
                        return JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_managedownloadlistTable_table_' + settings.sInstance));
                        //
                    },
                    oLanguage: {
                        'sProcessing': "<div class='loader_blank'></div><div class='processingblured'>" + eval("app.translations." + app.data.locale + ".please_wait_loading") + "<br><img src='../img/loader.gif'></div><div class='no_blurtext'>" + eval("app.translations." + app.data.locale + ".please_wait_loading") + "<br><img src='../img/loader.gif'></div>",
                        'sZeroRecords': eval("app.translations." + app.data.locale + ".no_downloadlist_added")
                    },
                    ajax: {
                        url: app.data.getdownloadlistinfo_db_table,
                        dataSrc: 'data',
                        type: "POST",
                        beforeSend: function(xhr, type) {
                            if (!type.crossDomain) {
                                xhr.setRequestHeader('X-CSRF-Token', $('meta[name="csrf-token"]').attr('content'));
                            }
                        },
                        data: function(d) {
                            //d.min_birthday = min_birthday_value;
                            //d.max_birthday = max_birthday_value;
                            d.report_date_value = report_date_value;
                            d.locale_date_format = prefered_dateFormat;

                            d.global_search_value = global_search_value;

                            d.byStatus_value = byStatus_value;
                            d.byCompanyPosition_value = byCompanyPosition_value;
                            d.byCompanyDepartment_value = byCompanyDepartment_value;
                            d.byEditorLevel_value = byEditorLevel_value;
                            d.byJobStatus_value = byJobStatus_value;
                            //d.byBirthmonths_value = byBirthmonths_value;
                            d.byEnabledDisabled_value = byEnabledDisabled_value;

                            d.byAssignee_value = byAssignee_value;

                            d.byToolClient_value = byToolClient_value;
                        },
                    },
                    columnDefs: broswer_columnDefs,
                    columns: browser_columns,
                    //order: [[1, "asc" ], [5, "desc"]],
                    order: [
                        [7, "asc"]
                    ],

                    // rowGroup: {
                    //     dataSrc: 'status_grouping',
                    //     //startClassName: 'status_grouping_style unselectable',
                    //     startRender: function(rows, group_name) {
                    //         return $('<tr class="group group-start"><td class="status_grouping_style unselectable" colspan="19"><div style="text-align: left; margin-left: 10px">' + group_name + '</div></td></tr>');
                    //     }
                    // },

                    rowGroup: {
                        dataSrc: 'expected_delivery_time_custom_grouping',
                        //startClassName: 'status_grouping_style unselectable',
                        startRender: function(rows, custom_expected_delivery_time_group) {
                            //return $('<tr style="height: 5px; max-height: 5px;" class="group group-start"><td style="font-size: 5px; height: 5px; max-height: 5px;" class="status_grouping_style unselectable" colspan="17"><div style="text-align: left; margin-left: 10px">' + custom_expected_delivery_time_group + '</div></td></tr>');
                            return $('<tr style="height: 5px; max-height: 5px;" class="group group-start"><td style="font-size: 5px; height: 5px; max-height: 5px;" class="status_grouping_style unselectable" colspan="17"><div style="text-align: left; margin-left: 10px"></div></td></tr>');
                        }
                    },

                    dom: '<".controlsfortable"<"#export_buttonlocation.html5buttons">rl<"#custom_visibility_buttons">f<"#custom_global_filter.dataTables_filter"><"#jobstatus_filter.dataTables_filter"><"#assignees_filter.dataTables_filter"><"#tool_client_buttons.dataTables_filter"><"#editor_level_filter.dataTables_filter"><"#team_filter.dataTables_filter"><"#position_filter.dataTables_filter"><"#status_filter.dataTables_filter"><"#birthdaytodate_filter.dataTables_filter"><"#birthdayfromdate_filter.dataTables_filter"><"#birthmonths_filter.dataTables_filter"><"#clear_filter.html5buttons"><"clearfix"><".table_block"<".table_float_left"i><".table_float_right"p>>>t<".controlsfortable"p>',
                    createdRow: function(row, data, index) {
                        /**console.log('data=' + JSON.stringify(data));*/
                        /**console.log('index=' + index);*/
                        var custom_row_color = '';
                        if (data['custom_color_rgb'] === undefined || data['custom_color_rgb'] === null) {
                            custom_row_color = '';
                        } else {
                            custom_row_color = '#' + data['custom_color_rgb'];
                        }
                        /**console.log(custom_row_color);*/

                        $('td', row).parent().attr('data-rowindex', index).css('background-color', custom_row_color);


                        //if (JSON.stringify(data.user_id) != 0) { $(row).css('cursor', 'pointer'); }
                        //necessary for the detailed task view per employee
                        $('td', row).parent().attr('data-encrypted_case_id', data['encrypted_case_id']);
                        /** there are fewer columns now hiding the checkbox columns due to fewer permissions and we need to adjust the variables */
                        var countervariableH = 0;
                        var countervariableG = 1;
                        var countervariableI = 3;
                        var countervariable3 = 4;
                        var countervariableX = 5;
                        var countervariableP = 6;
                        var countervariable2 = 7;
                        var countervariable = 8;
                        var countervariableA = 9;
                        var countervariableD = 10;
                        var countervariableE = 12;
                        var countervariableJ = 13;
                        var countervariableB = 14;
                        var countervariableF = 16;
                        var countervariableC = 17;

                        // var case_id_string = data['case_id'];
                        // case_id_string = case_id_string.split('<span style="font-weight: 900;" class="case_id_string">');
                        // case_id_string = case_id_string[1].split('</span></a>');
                        // case_id_string = case_id_string[0];
                        // /**console.log(case_id_string);*/

                        /**console.log(app.data.browser_detected);*/
                        if (app.data.browser_detected == 'Chrome') {
                            var local_file_link_prefix = 'file://192.168.1.3/jobs/';
                        } else if (app.data.browser_detected == 'Firefox') {
                            var local_file_link_prefix = 'file://///192.168.1.3/jobs/';
                        } else {
                            /** use fallback */
                            var local_file_link_prefix = 'file://192.168.1.3/jobs/';
                        }

                        $.each(data, function(i, v) {
                            //console.log("Index #" + i + ": " + v);

                            if (i.indexOf('case_id') >= 0) {
                                $('td', row).eq(countervariableH).html('<a class="button keyboardshortcutcombinationtolinktosharedfolder" style="text-decoration: none; font-weight: 900;" data-backup_href="" data-alt_href="' + local_file_link_prefix + data['case_id'] + '/" href="/uploadfiles/' + data['encrypted_case_id'] + '" target="_blank">' + data['case_id_display'] + '</a>');
                            }

                            if (i.indexOf('xml_jobid_title') >= 0) {

                                if (data['xml_jobid_title'] == '') {
                                    var link_to_upload_page_with_case_id = ''
                                } else {
                                    var link_to_upload_page_with_case_id = '<a class="button keyboardshortcutcombinationtolinktosharedfolder" style="text-decoration: none; font-weight: 900;" data-backup_href="" data-alt_href="' + local_file_link_prefix + data['case_id'] + '/" href="/uploadfiles/' + data['encrypted_case_id'] + '" target="_blank">' + data['xml_jobid_title_display'] + '</a>';
                                }
                                $('td', row).eq(countervariableG).html(link_to_upload_page_with_case_id);
                            }

                            if (i.indexOf('internal_notes') >= 0) {
                                $('td', row).eq(countervariableD).css('cursor', 'pointer').css('text-align', 'left').css('vertical-align', 'top').css('word-break', 'break-word').addClass('change_internal_notes').addClass('internal_notes');
                                $('td', row).eq(countervariableD).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            if (i.indexOf('instructions_col') >= 0) {
                                $('td', row).eq(countervariable3).css('cursor', 'pointer').addClass('change_instructions_col').addClass('instructions_col');
                                $('td', row).eq(countervariable3).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');


                                if (data['xml_jobinfoproduction'] == '') {
                                    var instructions_popover = ''
                                } else {
                                    var instructions_popover = '<span class="instructions_popover" data-container="body" data-toggle="popover" data-placement="bottom" data-content="' + data['xml_jobinfoproduction'] + '">'+data['instructions_col']+'</span>';
                                }

                                $('td', row).eq(countervariable3).html(instructions_popover);
                            }

                            if (i.indexOf('preview_col') >= 0) {
                                $('td', row).eq(countervariableX).css('cursor', 'pointer').addClass('change_preview_col').addClass('preview_col');
                                $('td', row).eq(countervariableX).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            if (i.indexOf('number_of_pictures') >= 0) {
                                if (data['number_of_pictures_example'] == null) {
                                    var number_of_pictures_example = '&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else {
                                    var number_of_pictures_example = data['number_of_pictures_example'];
                                }

                                if (data['number_of_pictures_new'] == null) {
                                    var number_of_pictures_new = '&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else {
                                    var number_of_pictures_new = data['number_of_pictures_new'];
                                }


                                $('td', row).eq(countervariableI).html('<div style="width: 100%; position:relative; padding: 4px"><div style="display:inline-block; width: 33.33%;">' + number_of_pictures_example + '</div><div style="display:inline-block; width: 33.33%;">|</div><div style="display:inline-block; width: 33.33%;">' + number_of_pictures_new + '</div></div>');
                            }


                            if (i.indexOf('output_files_col') >= 0) {

                                if (data['output_number_of_pictures_expected'] == null) {
                                    var output_number_of_pictures_expected = '&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else {
                                    var output_number_of_pictures_expected = data['output_number_of_pictures_expected'];
                                }

                                if (data['output_number_of_pictures_real'] == null) {
                                    var output_number_of_pictures_real = '&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else {
                                    var output_number_of_pictures_real = data['output_number_of_pictures_real'];
                                }

                                $('td', row).eq(countervariableP).addClass('change_output_files_col').addClass('output_files_col');
                                $('td', row).eq(countervariableP).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                                $('td', row).eq(countervariableP).html('<div class="manual_change_output_files_col"  style="width: 100%; position:relative; padding: 4px"><div class="output_number_of_pictures_expected" style="display:inline-block; width: 33.33%;">' + output_number_of_pictures_expected + '</div><div style="display:inline-block; width: 33.33%;">|</div><div class="output_number_of_pictures_real" style="display:inline-block; width: 33.33%;">' + output_number_of_pictures_real + '</div></div>');
                            }


                            if (i.indexOf('rating') >= 0) {
                                if (data['custom_job_star_rating_comment'] == null) {
                                    var custom_job_star_rating_comment = '';
                                    var display_edit_custom_job_star_rating_comment = '<i name="edit_custom_star_rating_comment_' + data['idx'] + '" style="display: none; margin-left: 8px; font-size: 17px; cursor: pointer;" class="fa fa-edit"></i>';
                                } else {
                                    var custom_job_star_rating_comment = data['custom_job_star_rating_comment'];
                                    var display_edit_custom_job_star_rating_comment = '<i name="edit_custom_star_rating_comment_' + data['idx'] + '" style="margin-left: 8px; font-size: 17px; cursor: pointer;" class="fa fa-edit"></i>';;
                                }

                                $('td', row).eq(countervariableE).html('<div data-html="true" data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="' + custom_job_star_rating_comment + '"><div name="edit_custom_star_rating_' + data['idx'] + '" class="rateit" data-rateit-value="' + data['rating'] + '" data-rateit-ispreset="true" data-rateit-readonly="false"></div>' + display_edit_custom_job_star_rating_comment + '</div>');
                                $('td', row).eq(countervariableE).addClass('change_rating').addClass('rating');
                                $('td', row).eq(countervariableE).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            if (i.indexOf('tags') >= 0) {
                                $('td', row).eq(countervariableJ).css('cursor', 'pointer').addClass('change_tags').addClass('tags');
                                $('td', row).eq(countervariableJ).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            if (i.indexOf('job_status') >= 0) {
                                $('td', row).eq(countervariableB).css('cursor', 'pointer').addClass('change_job_status').addClass('job_status');
                                $('td', row).eq(countervariableB).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            if (i.indexOf('assignees') >= 0) {
                                $('td', row).eq(countervariable).css('cursor', 'pointer').addClass('change_assignees').addClass('assignees');
                                $('td', row).eq(countervariable).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }
                            if (i.indexOf('delivery_time') >= 0) {
                                $('td', row).eq(countervariable2).html('<span style="' + data['color_it'] + '">' + data['delivery_time'] + '</span>');
                                $('td', row).eq(countervariable2).css('cursor', 'pointer').addClass('change_delivery_time').addClass('delivery_time');
                                $('td', row).eq(countervariable2).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            if (i.indexOf('custom_row_color') >= 0) {
                                $('td', row).eq(countervariableA).html('<div id="colorPicker_'+data['case_id']+'" class="colorPicker"><a class="color"><div class="colorInner" style="background-color: '+data['custom_color_rgb']+';"></div></a><div class="track"></div><ul class="dropdown"><li></li></ul><input type="hidden" class="colorInput" value="#'+data['custom_color']+'"></div>');
                                $('td', row).eq(countervariableA).addClass('change_custom_row_color').addClass('custom_row_color');
                                $('td', row).eq(countervariableA).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '').css('background-color', data['custom_color_rgb_manually_chosen']);
                            }

                            if (i.indexOf('last_updated_by') >= 0) {
                                if (data['last_updated_by'] == null) {
                                    var last_updated_by = '';
                                } else {
                                    var last_updated_by = data['last_updated_by'];
                                }
                                $('td', row).eq(countervariableF).html('<span style="font-size: 8px;">' + last_updated_by + '</span>');
                                $('td', row).eq(countervariableF).addClass('last_updated_by');
                                $('td', row).eq(countervariableF).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            if (i.indexOf('last_updated') >= 0) {
                                $('td', row).eq(countervariableC).addClass('last_updated');
                                $('td', row).eq(countervariableC).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }
                        });

                        var isSectionAccounting = app.data.section.indexOf("accounting"); //>= 0 if finds //-1 if it does not find
                        var canWRITEaccounting = app.data.auth_user_permissions.indexOf("WRITE accounting"); //>= 0 if finds //-1 if it does not find
                        if (isSectionAccounting >= 0 && canWRITEaccounting >= 0) {
                            /***/
                            /***/
                            /**console.log(app.data.section);*/
                            /**console.log(app.data.auth_user_permissions);*/
                        } else {
                            /***/
                            /***/
                        }


                        //console.log(window.downloadlisttable.row(index).node());

                        // console.log(custom_row_color);
                        function get_luminance(color) {
                            var rgb = color.replace('rgb(', '').replace(')', '').split(',');
                            var r = rgb[0];
                            var g = rgb[1];
                            var b = rgb[2];

                            c = [r / 255, g / 255, b / 255];

                            for (var i = 0; i < c.length; ++i) {
                                if (c[i] <= 0.03928) {
                                    c[i] = c[i] / 12.92
                                } else {
                                    c[i] = Math.pow((c[i] + 0.055) / 1.055, 2.4);
                                };
                            };

                            l = 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];

                            return l;
                        };

                        if (custom_row_color == '') {
                            $('td', row).parent().css('color', '#676A6C');
                        } else {
                            var luminance = get_luminance($('td', row).parent().css('background-color'));
                            /**console.log(luminance);*/
                            luminance > 0.433 ? $('td', row).parent().css('color', '#676A6C') : $('td', row).parent().css('color', '#FFFFFF');
                        }


                        /** if the tool reloads want to be able to highlight the new rows that just appeared within the last five minutes after which the style disappears */
                        /** use the download datetime for that */
                        var jobid_created_at_timestamp = parseInt(data['created_at_timestamp']);
                        var current_time_stamp = parseInt(new Date().getTime()/1000);
                        var difference_between_timestamps = current_time_stamp - jobid_created_at_timestamp;
                        var tr = $('td', row).closest('tr');
                        if(difference_between_timestamps <= 7200){
                            /** the job was created new within last 2 hours */
                            /** style the row */
                            /**console.log(tr);*/
                            $(tr).css('border', '2px solid #f8ac59').css('-moz-box-shadow', 'inset 0 0 20px #f8ac59').css('-webkit-box-shadow', 'inset 0 0 20px #f8ac59').css('box-shadow', 'inset 0 0 20px #f8ac59');
                        }else{
                            $(tr).css('border', '').css('-moz-box-shadow', '').css('-webkit-box-shadow', '').css('box-shadow', '');
                        }
                        /** but then we want it to remove these styles but when? */
                    },
                    drawCallback: function(settings) {
                        //app.data.timesheet_period = null;
                        var api = this.api();
                        var json = api.ajax.json();
                        /** JSON stringifying sorts the keys alphabetically */
                        /** ViewComposer adding CURRENCY key .. after this it is removed. what are the implications? */
                        // var extends_app = JSON.stringify(json.extends_app);
                        // extends_app = extends_app.replace(/\//g, "\\\/");
                        // console.log(json.extends_app);
                        ///** since we are trying new method for changing the timesheet period */
                        // var script = document.createElement('script');
                        // script.type = 'text/javascript';
                        // script.id = 'extends_app';
                        // script.text = 'app.ext(' + extends_app + ');';
                        // var element = document.getElementById("extends_app");
                        // document.getElementById("extends_app").parentNode.replaceChild(script, element);

                        // $('#attendance_status_change_period').val(app.data.timesheet_period.when);

                        /** scroll back to where they were positioned */
                        $(document).ready(function() {
                            var previous_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_edit_downloadlist_scroll_position_data'));
                            if (previous_load !== null) {
                                if (previous_load['scroll'] !== "") {
                                    //console.log("scroll = " + previous_load['scroll']);
                                    var scroll = previous_load['scroll'];
                                    $(document).scrollTop(scroll);
                                }
                                sessionStorage.removeItem('Br24_' + app.env() + '_edit_downloadlist_scroll_position_data');
                            }
                        });


                        $("input[type=search]").focus();


                        if ($('.ms-has-selections')[0]) {
                            $('a[name="clearallfilters"]').css('display', "block");
                        } else {
                            $('a[name="clearallfilters"]').css('display', "none");
                        }

                        var api = this.api();
                        // $.each(app.conf.table.totalColumn.managedownloadlistInfo, function(idx, val) {
                        //     app.util.totalFormat(idx, api, val);
                        // });
                        
                        var refreshTimeout = null;
                        $('[data-toggle="popover"]').popover({
                            placement: 'auto bottom',
                            trigger: "manual",
                            html: true,
                            animation: false
                        }).on("mouseenter", function() {
                            var _this = this;
                            var popover_mouseover_function = function(this_elem) {
                                refreshTimeout = setInterval(function() {
                                    $(this_elem).popover("show");
                                }, 300);
                            };
                            popover_mouseover_function(_this);
                            $(this).siblings(".popover").on("mouseleave", function() {
                                $(_this).popover('hide');
                            });
                        }).on("mouseleave", function() {
                            clearInterval(refreshTimeout);
                            var _this = this;
                            var popover_mouseleave_function = function() {
                                setTimeout(function() {
                                    if (!$(".popover:hover").length) {
                                        $(_this).popover("hide")
                                    } else {
                                        popover_mouseleave_function();
                                    }
                                }, 50);
                            };
                            popover_mouseleave_function();
                        });



                        /** format the popover tooltip display */
                        // $('[data-toggle="popover"]').on('inserted.bs.popover', function() {
                        //     $('.popover').css('border', '0px').css('background-color', 'rgba(255,255,255,0.2)');
                        // });
                        // $('[data-toggle="popover"]').on('shown.bs.popover', function() {
                        //     $('.popover').css('border', '0px').css('background-color', 'rgba(255,255,255,0.2)');
                        // });

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

                        $(".currency_number_format").formatNumber({ format: number_format_per_locale, locale: numberformat_locale });





                        $("div[class*='colorPicker']").tinycolorpicker();





                        var new_value = null;
                        var new_value_id = null;
                        //var new_symbol = null;

                        var success_ajax_then_refresh = null;

                        $('.dataTable tbody').off('click', 'td.parent.change_job_status').on('click', 'td.parent.change_job_status', function(event) {
                            /**console.log('td.parent.change_math_operand');*/

                            var target = event.target;
                            event.preventDefault();

                            var tr = $(this).closest('tr');
                            var td = $(this);

                            /**console.log(td);*/

                            var original_html = $(this).html();
                            var row = window.downloadlisttable.row(tr);
                            var thecellvalue = $(this).text();
                            // console.log(thecellvalue);
                            var rowIndex = tr.data('rowindex');
                            var change_status_case_Id = $(this).data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                            keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_status_case_Id;
                            var timestamp_Id = $(this).data('date_ts');
                            // var idx = table.cell(this).index().column;
                            // var date_number = table.column(idx).header();
                            // var clcikedcolumnheader_value = $(date_number).html();

                            if ($(td).hasClass('from_amount_op')) {
                                keep_track_of_last_clicked_item_to_react_after_reload = 'change_math_operand.from_amount_op';
                            }
                            if ($(td).hasClass('to_amount_op')) {
                                keep_track_of_last_clicked_item_to_react_after_reload = 'change_math_operand.to_amount_op';
                            }

                            function triggerFocus() {
                                var e = jQuery.Event("keyup", { keyCode: 65, which: 65 });
                                $("#editrecord_input").focus();
                                //.select();
                                $("#editrecord_input").attr('value', '');
                                $("#editrecord_input").triggerHandler(e);
                                $("#editrecord_input").trigger('change');
                                $("#editrecord_input").css('cursor', 'default');
                            }


                            if ($('#editrecord_input').is(":visible") == true) {
                                /**console.log('input on other cell is visible');*/
                            } else {
                                //Change the cell to a select drop down
                                var input = $('<input id="editrecord_input" style="color: #000; text-align:center; width: 80px; display: block; margin: 0 auto; z-index: 12;" readonly></input>');
                                input.val(thecellvalue);
                                td.html(input);
                                //td.css('padding', '0px');
                                var cell = window.downloadlisttable.cell(this);

                                var options = {
                                    data: app.data.ajax_getAccountingPITMathOperatorSelectOptionList.original,
                                    // url: function(phrase) {
                                    //     return app.data.ajax_getAccountingPITMathOperatorSelectOptionList.original;
                                    // },
                                    getValue: function(element) {
                                        return element.name;
                                        /***/
                                    },
                                    // requestDelay: 0,
                                    // ajaxSettings: {
                                    //     dataType: "json",
                                    //     method: "GET",
                                    //     async: true,
                                    //     data: {
                                    //         dataType: "json"
                                    //     }
                                    // },
                                    preparePostData: function(data) {
                                        //console.log('preparePostData');
                                        data.timestamp_Id = timestamp_Id;
                                        td.css('background', 'url(../img/loader.gif) 50% 50% no-repeat rgba(0, 0, 0, 0)');
                                        $('#editrecord_input').css('opacity', 0);
                                        $('.easy-autocomplete-container').css('opacity', 0);
                                        $(document.body).addClass('nprogress-busy').css('pointer-events', 'none').css('cursor', 'wait');
                                        return data;
                                    },
                                    template: {
                                        type: "custom",
                                        method: function(value, item) {

                                            /** want to hide the option that is currently selected only show others */
                                            if (item.name.toLowerCase() == thecellvalue) {
                                                return;
                                            }

                                            var include_tooltip = ''
                                            // if (item.name == '&nbsp;') {
                                            //     include_tooltip = '';
                                            // } else {
                                            //     include_tooltip = 'data-html="true" data-container="body" data-toggle="popover" data-placement="left" data-trigger="hover" data-content="' + item.tooltip + '"';
                                            // }
                                            return '<div class="key_box_tsr_bridge"><div class="key_box_tsr_penalty_manage" style="color: #000; border: 1px solid rgba(169, 169, 169, 0.5);"' + include_tooltip + '><span class="">' + item.name + '</span></div></div>';
                                        }
                                    },
                                    highlightPhrase: false,
                                    list: {
                                        maxNumberOfElements: 100,
                                        match: {
                                            enabled: false
                                        },
                                        onShowListEvent: function() {
                                            /**console.log('onShowListEvent');*/
                                            td.css('background', '');
                                            $('#editrecord_input').css('opacity', 1);
                                            $('.easy-autocomplete-container').css('opacity', 1);
                                            $(document.body).removeClass('nprogress-busy').css('pointer-events', 'auto').css('cursor', 'default');
                                            var refreshTimeout = null;
                                            $('[data-toggle="popover"]').popover({
                                                placement: 'auto bottom',
                                                trigger: "manual",
                                                html: true,
                                                animation: false
                                            }).on("mouseenter", function() {
                                                var _this = this;
                                                var popover_mouseover_function = function(this_elem) {
                                                    refreshTimeout = setInterval(function() {
                                                        $(this_elem).popover("show");
                                                    }, 300);
                                                };
                                                popover_mouseover_function(_this);
                                                $(this).siblings(".popover").on("mouseleave", function() {
                                                    $(_this).popover('hide');
                                                });
                                            }).on("mouseleave", function() {
                                                clearInterval(refreshTimeout);
                                                var _this = this;
                                                var popover_mouseleave_function = function() {
                                                    setTimeout(function() {
                                                        if (!$(".popover:hover").length) {
                                                            $(_this).popover("hide")
                                                        } else {
                                                            popover_mouseleave_function();
                                                        }
                                                    }, 50);
                                                };
                                                popover_mouseleave_function();
                                            });
                                            $('.easy-autocomplete').css('width', '');
                                        },
                                        onLoadEvent: function() {
                                            /**console.log('onLoadEvent');*/
                                            /**$('.loader').css('display', 'block').css('z-index', '10').css('background', 'url(../img/blank.gif) 50% 50% no-repeat rgb(249, 249, 249, 0.5)');*/
                                            $(".popover").popover('hide');

                                            /**console.log('=========================================================================================== LOAD EVENT ==================================');*/
                                            /**console.log('keep_track_of_last_clicked_item_to_react_after_reload');*/
                                            /**console.log(keep_track_of_last_clicked_item_to_react_after_reload);*/
                                            /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload');*/
                                            /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload);*/
                                            /**console.log('keep_track_of_last_clicked_easyautocomplete_to_reopen');*/
                                            /**console.log(keep_track_of_last_clicked_easyautocomplete_to_reopen);*/
                                            /**console.log('keep_track_of_last_clicked_item_to_react_after_reload_2');*/
                                            /**console.log(keep_track_of_last_clicked_item_to_react_after_reload_2);*/
                                            /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2');*/
                                            /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2);*/
                                            /**console.log('re_open_requested');*/
                                            /**console.log(re_open_requested);*/
                                            /**console.log('=========================================================================================== LOAD EVENT ==================================');*/

                                            clearInterval(reopen_refreshIntervalId); //stop the timer called reopen_refreshIntervalId
                                        },
                                        onClickEvent: function(event) {
                                            /**console.log('onClickEvent');*/
                                            //event.preventDefault();

                                            /** we ask for confirmation */
                                            /** when we selec the new shift we have to perform a function via ajax */

                                            new_value_id = $("#editrecord_input").getSelectedItemData().id;
                                            new_value = $("#editrecord_input").getSelectedItemData().name;
                                            //new_symbol = $("#editrecord_input").getSelectedItemData().symbol;
                                            /**console.log('thecellvalue=' + thecellvalue);*/
                                            /**console.log('new_value_id=' + new_value_id);*/
                                            //console.log('new_value=' + new_value);
                                        },
                                        onChooseEvent: function() {
                                            /**console.log('onChooseEvent');*/
                                            /***/
                                        },
                                        onHideListEvent: function(event) {
                                            /**console.log('onHideListEvent');*/
                                            /**event.preventDefault();*/

                                            /** clicking fills these variables so it will always be not null */

                                            //console.log('success_ajax_then_refresh='+success_ajax_then_refresh);
                                            /**console.log(new_value);*/

                                            $(document.body).removeClass('nprogress-busy').css('pointer-events', 'auto');
                                            $(".popover").popover('hide');

                                            if (new_value == null) {
                                                $("#editrecord_input").remove();
                                                cell.data(original_html);
                                                return;
                                            }


                                            if (new_value != null) {
                                                //$("#editrecord_input").remove();
                                                $(".easy-autocomplete-container").remove();
                                                // cell.data(new_value.toLowerCase());
                                                /** we store the new status to the db */

                                                $.confirm({
                                                    title: eval("app.translations." + app.data.locale + ".title_text"),
                                                    content: 'Change the Job Status from ' + thecellvalue + ' to ' + new_value + ' ?',
                                                    type: 'red',
                                                    draggable: true,
                                                    dragWindowGap: 0,
                                                    backgroundDismiss: 'cancel',
                                                    escapeKey: true,
                                                    animateFromElement: false,
                                                    autoClose: 'ok|50',
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
                                                                var data = {
                                                                    'case_id': change_status_case_Id,
                                                                    'encrypted_case_id': encrypted_case_id_uploading_to,
                                                                    'new_status': new_value.toLowerCase(),
                                                                    'new_status_id': new_value_id
                                                                };

                                                                app.ajax.json(app.data.change_status_for_job, data, null, function() {
                                                                    /**console.log(app.ajax.result);*/
                                                                    success_ajax_then_refresh = app.ajax.result.success;
                                                                    if (app.ajax.result.success == true) {
                                                                        $("#editrecord_input").remove();
                                                                        $(".easy-autocomplete-container").remove();
                                                                        cell.data(new_value);
                                                                        $('.loader').css('display', 'none').css('cursor', 'auto').css('background', 'url(../img/loader.gif) 50% 50% no-repeat rgb(249, 249, 249, 0.5)');

                                                                        // console.log(tr.children().next('.last_updated'));
                                                                        // console.log(app.ajax.result.updated_at);
                                                                        tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                                                        tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                                                                        /** since they want to have the in progress all together wwe kind of need to reload ajax table now */
                                                                        window.downloadlisttable.ajax.reload(null, false);
                                                                        window.downloadlisttable.fixedHeader.adjust();
                                                                    } else {
                                                                        /** we don't change anything put back to what it was and alert  */
                                                                        $.alert({
                                                                            title: 'Alert!',
                                                                            content: 'Job Status not changed',
                                                                        });
                                                                        $("#editrecord_input").remove();
                                                                        cell.data(original_html);
                                                                        $('.loader').css('display', 'none').css('cursor', 'auto').css('background', 'url(../img/loader.gif) 50% 50% no-repeat rgb(249, 249, 249, 0.5)');
                                                                        new_value = null;
                                                                    }
                                                                });
                                                            }
                                                        },
                                                        cancel: {
                                                            text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                            action: function() {
                                                                $("#editrecord_input").remove();
                                                                cell.data(original_html);
                                                                $('.loader').css('display', 'none').css('cursor', 'auto').css('background', 'url(../img/loader.gif) 50% 50% no-repeat rgb(249, 249, 249, 0.5)');
                                                                new_value = null;
                                                            }
                                                        },
                                                    }
                                                });
                                            }
                                        },
                                        showAnimation: {
                                            type: "fade", //normal|slide|fade
                                            time: 1,
                                            callback: function() {
                                                /**console.log('finished showing');*/
                                            }
                                        },
                                        hideAnimation: {
                                            type: "fade", //normal|slide|fade
                                            time: 1,
                                            callback: function() {


                                                if (keep_track_of_last_clicked_item_to_react_after_reload_2 != null && keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2 != null) {
                                                    if (keep_track_of_last_clicked_easyautocomplete_to_reopen == true) {
                                                        /**console.log('$$$$$$$$$$$$$$$$$$$ RE OPEN REQUESTED $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$ RE OPEN REQUESTED $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');*/
                                                        re_open_requested = true;
                                                        reopen_timer();
                                                    }
                                                }

                                                /**console.log('=========================================================================================== CLOSE CALLBACK EVENT ==================================');*/
                                                /**console.log('keep_track_of_last_clicked_item_to_react_after_reload');*/
                                                /**console.log(keep_track_of_last_clicked_item_to_react_after_reload);*/
                                                /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload');*/
                                                /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload);*/
                                                /**console.log('keep_track_of_last_clicked_easyautocomplete_to_reopen');*/
                                                /**console.log(keep_track_of_last_clicked_easyautocomplete_to_reopen);*/
                                                /**console.log('keep_track_of_last_clicked_item_to_react_after_reload_2');*/
                                                /**console.log(keep_track_of_last_clicked_item_to_react_after_reload_2);*/
                                                /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2');*/
                                                /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2);*/
                                                /**console.log('re_open_requested');*/
                                                /**console.log(re_open_requested);*/
                                                /**console.log('=========================================================================================== CLOSE CALLBACK EVENT ==================================');*/
                                            }
                                        }
                                    },
                                };
                                //console.log(JSON.stringify(options));
                                $("#editrecord_input").easyAutocomplete(options).css('z-index', '12');

                                /** to open the select box */
                                triggerFocus();
                            }
                        });

                        $('.dataTable tbody').off('click', 'td.parent.change_assignees').on('click', 'td.parent.change_assignees', function(event) {
                            // console.log('td.parent.change_assignee');
                            if ($('.is_br24_employee').is(":visible") == true) {
                                return false;
                            }

                            keep_track_of_last_clicked_item_to_react_after_reload = 'change_assignee';
                            var target = event.target;
                            event.preventDefault();

                            var tr = $(this).closest('tr');
                            var td = $(this);
                            var change_assignee_original_html = $(this).html();
                            // console.log('change_assignee_original_html');
                            // console.log(change_assignee_original_html);
                            var row = window.downloadlisttable.row(tr);
                            var thecellvalue = $(this).text();
                            // console.log('thecellvalue');
                            // console.log(thecellvalue);
                            //var unformatted_number = $(this).parseNumber({ format: number_format_per_locale, locale: numberformat_locale });
                            /** console.log(unformatted_number); */
                            var rowIndex = tr.data('rowindex');
                            var change_assignee_case_Id = $(this).data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                            /**console.log(encrypted_case_id_uploading_to);*/

                            keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_assignee_case_Id;
                            /**console.log(change_assignee_case_Id);*/
                            var timestamp_Id = $(this).data('date_ts');
                            // var idx = table.cell(this).index().column;
                            // var date_number = table.column(idx).header();
                            // var clcikedcolumnheader_value = $(date_number).html();
                            //var input = $('<input id="edit_assignees_input_' + change_assignee_case_Id + '" type="number" style="text-align:center; width: 100px; display: block; margin: 0 auto; z-index: 12;" min="0" max="2147483647" step="1" pattern="^\\d{1,10}?$"></input>');

                            var input = $('<select style="width: 100%; height: 46px;" class="is_br24_employee form-control" title="" multiple></select>');
                            /** I want this input to be looking like the selectize one */

                            //input.val(unformatted_number);
                            td.html(input);


                            /**console.log('ready');*/
                            var select_list_employee_list = app.data.selectize_employee_list_formated_json;
                            /**console.log('select_list_employee_list=' + JSON.stringify(select_list_employee_list));*/
                            var select_selected_list_employee_has_family_member_json = app.data.selectize_selected_employee_has_family_members_in_company_formated_json;
                            /**console.log('select_selected_list_employee_has_family_member_json=' + JSON.stringify(select_selected_list_employee_has_family_member_json));*/

                            var select_selected_list_employee_has_family_member = select_selected_list_employee_has_family_member_json.map(function(item) {
                                return item['username'];
                            });

                            /**console.log('AFTERselect_selected_list_employee_has_family_member=' + select_selected_list_employee_has_family_member);*/

                            /** if there are any that are any details we can use to populate the selectize */
                            if (change_assignee_original_html != '') {
                                select_selected_list_employee_has_family_member = change_assignee_original_html.split(" ");
                                Object.assign({}, [select_selected_list_employee_has_family_member]);
                            }
                            /**console.log('FROMHTMLselect_selected_list_employee_has_family_member=' + select_selected_list_employee_has_family_member);*/


                            var assignee_ids = null;
                            var encrypted_assignee_ids = null;

                            if (!window.Selectize.prototype.positionDropdownOriginal) {
                                window.Selectize.prototype.positionDropdownOriginal = window.Selectize.prototype.positionDropdown;
                                window.Selectize.prototype.positionDropdown = function() {
                                    if (this.settings.dropdownDirection === 'up') {
                                        let $control = this.$control;
                                        let offset = this.settings.dropdownParent === 'body' ? $control.offset() : $control.position();

                                        var the_td = $control.parent().parent();
                                        var the_td_offset = the_td.offset();
                                        var position_relative_to_viewport = parseInt(the_td_offset.top) - parseInt($(window).scrollTop());

                                        var switch_to_dropdown = false;
                                        if (position_relative_to_viewport <= 300) {
                                            var switch_to_dropdown = true;
                                        }

                                        var height_of_drowdown = 261;
                                        if (switch_to_dropdown) {
                                            var height_of_td = the_td.height() - 12;
                                            height_of_drowdown = -parseInt(height_of_td);
                                        }

                                        this.$dropdown.css({
                                            width: $control.outerWidth(),
                                            top: offset.top - height_of_drowdown,
                                            left: offset.left,
                                        });

                                        this.$dropdown.addClass('direction-' + this.settings.dropdownDirection);
                                        this.$control.addClass('direction-' + this.settings.dropdownDirection);
                                        this.$wrapper.addClass('direction-' + this.settings.dropdownDirection);
                                    } else {
                                        window.Selectize.prototype.positionDropdownOriginal.apply(this, arguments);
                                    }
                                };
                            }

                            var $is_br24_employee_select = $('.is_br24_employee').selectize({
                                plugins: ['remove_button', 'optgroup_columns'],
                                persist: false,
                                maxItems: 200,
                                mode: 'multi',
                                dropdownDirection: 'up',
                                /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                                placeholder: '-- Choose Job Assignee(s) --',
                                valueField: ['username'],
                                labelField: 'username',
                                searchField: ['username', 'fullname', 'fullname_noaccents'],
                                options: select_list_employee_list,
                                /** list of all the viable employees on init */
                                items: select_selected_list_employee_has_family_member,
                                /** list of already selected employees on init */
                                hideSelected: true,
                                openOnFocus: true,
                                closeAfterSelect: true,
                                render: {
                                    item: function(item, escape) {
                                        return '<div>' +
                                            (item.username ? '<span class="username"><u><b>' + item.username + '</b></u></span>' : '') +
                                            //'<span style="color: #ccc">&nbsp;</span>' +
                                            //(item.xml_jobid_title ? ' <i class="fa fa-angle-double-right text-danger"></i> ' : '') +
                                            //(item.xml_jobid_title ? '<span class="xml_jobid_title" style="font-size: 9px; color: #1cd;"><b>' + item.xml_jobid_title + '</b></span>' : '') +
                                            //'<br>' +
                                            //(item.xml_title_contents ? '<span class="xml_title_contents" style="font-size: 10px;">' + item.xml_title_contents + '</span>' : '') +
                                            //'<br>' +
                                            //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                                            '</div>';
                                    },
                                    option: function(item, escape) {
                                        var label = item.xml_title_contents || item.email;
                                        var caption = item.xml_title_contents ? item.email : null;
                                        return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                                            '<span class="label label-primary">' + item.username + '</span>' +
                                            //'<span style="color: #ccc">&nbsp;</span>' +

                                            //(item.fullname ? ' <i class="fa fa-angle-double-right text-danger"></i> ' : '') +
                                            //(item.fullname ? '<span class="fullname" style="font-size: 9px; color: #1cd;"><b>' + item.fullname + '</b></span>' : '') +
                                            //'<br>' +
                                            //(item.xml_title_contents ? '<span class="xml_title_contents" style="font-size: 10px;">' + item.xml_title_contents + '</span>' : '') +
                                            //(item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                                            //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                                            '</div>';
                                    }
                                },
                                onChange: function(value) {
                                    //$('#cb_add_family_members_details_form').addClass('dirty');
                                    /** when it changes i want to update the variable so that it can be loaded together with the files */
                                    /**console.log($('.is_br24_employee').val());*/
                                    assignee_ids = $('.is_br24_employee').val();

                                    /***/
                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                    var data = {
                                        'case_id': change_assignee_case_Id,
                                        'encrypted_case_id': encrypted_case_id_uploading_to,
                                        'assignees': assignee_ids
                                    };

                                    /** use ajax to send data to php */
                                    app.ajax.json(app.data.change_assignees_for_job, data, null, function() {
                                        /**console.log(app.ajax.result);*/
                                        success_ajax_then_refresh = app.ajax.result.success;
                                        if (app.ajax.result.success == true) {


                                            /** what happens if the preview required checkbox is already checkedmarked and assignees are set to blank? */
                                            /** need to update the column in the db for preview required .. */
                                            /** check if the new assignes td is not blank and enable the tr preview required checkbox */
                                            /**.change_preview_col*/
                                            var checkbox_in_td = $("input[name='edit_" + change_assignee_case_Id + "']");
                                            if (checkbox_in_td.is(':checked')) {
                                                status = 2;
                                                /**console.log(status);*/
                                                /** we go in via ajax to amend the status column where user_id and date on accept reject penalty table */
                                                app_ops.manage_downloadlist.profile.sync_preview_required_status(change_assignee_case_Id, encrypted_case_id_uploading_to, status, tr, checkbox_in_td, 'NOT NULL');
                                            }
                                            if (assignee_ids == '') {
                                                checkbox_in_td.prop("disabled", true);
                                                checkbox_in_td.prop("checked", false);
                                            } else {
                                                checkbox_in_td.prop("disabled", false);
                                            }

                                            // console.log(tr.children().next('.last_updated'));
                                            // console.log(app.ajax.result.updated_at);
                                            tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                            tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                                        } else {

                                            // $.each(app.ajax.result.errors, function(idx, val) {
                                            //     $.alert(val);
                                            // });
                                        }
                                    });

                                }
                            });

                            var is_br24_employee_select_selectize = $is_br24_employee_select[0].selectize;
                            var is_br24_employee_select_old_options = is_br24_employee_select_selectize.settings;
                            var selectize_focus_handler = function(value, $item) {
                                var width_to_be = $('.selectize-control').outerWidth();
                                var height_to_be = 600;
                                $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                            };
                            is_br24_employee_select_selectize.on('focus', selectize_focus_handler);


                            var selectize_blur_handler = function(value, $item) {
                                // var width_to_be = $('.selectize-control').outerWidth();
                                // var height_to_be = 600;


                                //$('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                                var this_td_new_content_formated = '';

                                assignee_ids = $('.is_br24_employee').val();
                                /**console.log(assignee_ids);*/
                                /**console.log(change_assignee_original_html);*/
                                if (assignee_ids === undefined || assignee_ids.length == 0) {
                                    td.html('');
                                } else {
                                    /**console.log('blur');*/
                                    $.each(assignee_ids, function(idx, val) {
                                        // console.log(idx);
                                        // console.log(val);
                                        if (this_td_new_content_formated == '') {
                                            this_td_new_content_formated = val;
                                        } else {
                                            this_td_new_content_formated = this_td_new_content_formated + ' ' + val;
                                        }
                                    });
                                    td.html(this_td_new_content_formated);
                                }

                                /** otherwise return it to what it was before */

                                /** want to be able to save the information on the database so that it can be called back when the page reloads */
                                /** also don't want to reload the page want to have the selected options become the td content */
                                /** and if there is content to get the selectize automatically load those ones on click */
                                /** */

                                /** should do it on blur as well or on change to so that as soon as they click its on the database already. */
                            };
                            is_br24_employee_select_selectize.on('blur', selectize_blur_handler);

                            /** filter the shifts from the swapable shifts select */
                            var item_remove_handler = function(value, $item) {
                                /**console.log('item_removed');*/
                                assignee_ids = $('.is_br24_employee').val();
                            };
                            is_br24_employee_select_selectize.on('item_remove', item_remove_handler);

                            $(window).resize(function() {
                                var width_to_be = $('.selectize-control').outerWidth();
                                var height_to_be = 600;
                                $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                            });

                            is_br24_employee_select_selectize.focus();
                        });

                        $('.dataTable tbody').off('click', 'td.parent.change_delivery_time').on('click', 'td.parent.change_delivery_time', function(event) {
                            /**console.log('td.parent.change_delivery_time');*/
                            if ($('.is_deliver_time').is(":visible") == true) {
                                return false;
                            }

                            keep_track_of_last_clicked_item_to_react_after_reload = 'change_delivery_time';
                            var target = event.target;
                            event.preventDefault();

                            var tr = $(this).closest('tr');
                            var td = $(this);
                            var change_assignee_original_html = $(this).html();
                            // console.log('change_assignee_original_html');
                            // console.log(change_assignee_original_html);
                            var row = window.downloadlisttable.row(tr);
                            var thecellvalue = $(this).text();
                            //console.log('thecellvalue');
                            //console.log(thecellvalue);
                            //var unformatted_number = $(this).parseNumber({ format: number_format_per_locale, locale: numberformat_locale });
                            /** console.log(unformatted_number); */
                            var rowIndex = tr.data('rowindex');
                            var change_assignee_case_Id = $(this).data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                            keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_assignee_case_Id;
                            /**console.log(change_assignee_case_Id);*/
                            var timestamp_Id = $(this).data('date_ts');
                            // var idx = table.cell(this).index().column;
                            // var date_number = table.column(idx).header();
                            // var clcikedcolumnheader_value = $(date_number).html();
                            //var input = $('<input id="edit_assignees_input_' + change_assignee_case_Id + '" type="number" style="text-align:center; width: 100px; display: block; margin: 0 auto; z-index: 12;" min="0" max="2147483647" step="1" pattern="^\\d{1,10}?$"></input>');

                            //var input = $('<select style="width: 100%" class="is_br24_employee form-control" title="" multiple></select>');
                            var input = $('<input type="text" style="width: 100%; height:46px; color: #000; text-align: center" class="is_deliver_time" data-field="datetime" value="' + thecellvalue + '" readonly><div id="dtBox"></div>');
                            /** I want this input to be looking like the selectize one */

                            //input.val(unformatted_number);
                            td.html(input);



                            var custom_delivery_time = null;
                            var encrypted_assignee_ids = null;

                            /** we select the time when the set button is clicked */
                            /** if we cancel it goes back to the way it was before */
                            /** we unload the input from the td where possible */
                            /** */



                            var currentText = null;
                            var closeText = null;
                            var amNames = null;
                            var pmNames = null;
                            var timeFormat = null;
                            var timeSuffix = null;
                            var timeOnlyTitle = null;
                            var timeText = null;
                            var hourText = null;
                            var minuteText = null;
                            var secondText = null;
                            var millisecText = null;
                            var microsecText = null;
                            var timezoneText = null;
                            var isRTL = null;

                            if (app.data.locale === 'vi') {
                                currentText = 'Hiện thời';
                                closeText = 'Đóng';
                                amNames = ['SA', 'S'];
                                pmNames = ['CH', 'C'];
                                timeFormat = 'HH:mm';
                                timeSuffix = '';
                                timeOnlyTitle = 'Chọn giờ';
                                timeText = 'Thời gian';
                                hourText = 'Giờ';
                                minuteText = 'Phút';
                                secondText = 'Giây';
                                millisecText = 'Mili giây';
                                microsecText = 'Micrô giây';
                                timezoneText = 'Múi giờ';
                                isRTL = false;
                            } else if (app.data.locale === 'en') {
                                currentText = 'Now';
                                closeText = 'Done';
                                amNames = ['AM', 'A'];
                                pmNames = ['PM', 'P'];
                                timeFormat = 'HH:mm';
                                timeSuffix = '';
                                timeOnlyTitle = 'Choose Time';
                                timeText = 'Time';
                                hourText = 'Hour';
                                minuteText = 'Minute';
                                secondText = 'Second';
                                millisecText = 'Millisecond';
                                microsecText = 'Microsecond';
                                timezoneText = 'Time Zone';
                                isRTL = false;
                            } else if (app.data.locale === 'de') {
                                currentText = 'Jetzt';
                                closeText = 'Fertig';
                                amNames = ['vorm.', 'AM', 'A'];
                                pmNames = ['nachm.', 'PM', 'P'];
                                timeFormat = 'HH:mm';
                                timeSuffix = '';
                                timeOnlyTitle = 'Zeit wählen';
                                timeText = 'Zeit';
                                hourText = 'Stunde';
                                minuteText = 'Minute';
                                secondText = 'Sekunde';
                                millisecText = 'Millisekunde';
                                microsecText = 'Mikrosekunde';
                                timezoneText = 'Zeitzone';
                                isRTL = false;
                            }

                            var has_been_edited = null;
                            $('.is_deliver_time').datetimepicker({
                                currentText: currentText,
                                closeText: closeText,
                                amNames: amNames,
                                pmNames: pmNames,
                                timeFormat: timeFormat,
                                timeSuffix: timeSuffix,
                                timeOnlyTitle: timeOnlyTitle,
                                timeText: timeText,
                                hourText: hourText,
                                minuteText: minuteText,
                                secondText: secondText,
                                millisecText: millisecText,
                                microsecText: microsecText,
                                timezoneText: timezoneText,
                                isRTL: isRTL,
                                dateFormat: prefered_dateFormat,
                                showMonthAfterYear: true,
                                numberOfMonths: 1,
                                showCurrentAtPos: 0,
                                changeMonth: true,
                                changeYear: true,
                                yearRange: "-1:+1",
                                showOtherMonths: false,
                                selectOtherMonths: false,
                                toggleActive: true,
                                todayHighlight: true,
                                showMinute: false,
                                //minDate: new Date(minD_YYYY, minD_MM, minD_DD),
                                //maxDate: new Date(maxD_YYYY, maxD_MM, maxD_DD),
                                autoclose: true,
                                defaultDate: defaultDateVARIABLE,
                                onSelect: function() {
                                    /**console.log('onSelect');*/
                                    has_been_edited = true;
                                },
                                onClose: function() {
                                    /**console.log('onClose');*/

                                    if (has_been_edited == true) {


                                        /** we offer to ask them if they want to save or not */

                                        $.confirm({
                                            title: eval("app.translations." + app.data.locale + ".title_text"),
                                            content: eval("app.translations." + app.data.locale + ".you_have_unsaved_changes") + '\n' + eval("app.translations." + app.data.locale + ".do_you_want_to_save_those_changes") + '\n',
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
                                                        /**you need to format the date before sending */
                                                        /** from locale to expected YYYY-MM-DD */
                                                        var theselecteddatetime = $('.is_deliver_time').val();
                                                        /**console.log(theselecteddatetime);*/
                                                        var datetime_split = theselecteddatetime.split(" ");
                                                        /**console.log(datetime_split);*/

                                                        var from = datetime_split[0].split(delimiter_for_splitting_variable);
                                                        /**console.log(from);*/
                                                        var from_time = datetime_split[1].split(':');
                                                        /**console.log(from_time);*/
                                                        /** because we are dealing with the date and the time we have to take this into consideration before chopping it up and sending it to the db*/

                                                        var datetogoto = null;
                                                        var default_dateYYY = null;
                                                        var default_dateMM = null;
                                                        var default_dateDD = null;
                                                        if (app.data.locale === 'vi') {
                                                            var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]) + ' ' + from_time[0] + ':' + from_time[1];
                                                            // default_dateYYY = parseInt(from[2]);
                                                            // default_dateMM = parseInt(from[1]);
                                                            // default_dateDD = parseInt(from[0]);
                                                        } else if (app.data.locale === 'en') {
                                                            var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]) + ' ' + from_time[0] + ':' + from_time[1];
                                                            // default_dateYYY = parseInt(from[2]);
                                                            // default_dateMM = parseInt(from[1]);
                                                            // default_dateDD = parseInt(from[0]);
                                                        } else if (app.data.locale === 'de') {
                                                            var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]) + ' ' + from_time[0] + ':' + from_time[1];
                                                            // default_dateYYY = parseInt(from[2]);
                                                            // default_dateMM = parseInt(from[1]);
                                                            // default_dateDD = parseInt(from[0]);
                                                        }
                                                        /**console.log(datetogoto);*/

                                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                                        var data = {
                                                            'case_id': change_assignee_case_Id,
                                                            'encrypted_case_id': encrypted_case_id_uploading_to,
                                                            'new_delivery_datetime': datetogoto
                                                        };

                                                        /** use ajax to send data to php */
                                                        app.ajax.json(app.data.change_deliverydate_for_job, data, null, function() {
                                                            /**console.log(app.ajax.result);*/
                                                            success_ajax_then_refresh = app.ajax.result.success;
                                                            if (app.ajax.result.success == true) {
                                                                td.html(theselecteddatetime);

                                                                // console.log(tr.children().next('.last_updated'));
                                                                // console.log(app.ajax.result.updated_at);
                                                                tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                                                tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                                                                /** because this might adjust the position relative to the other job ids */
                                                                window.downloadlisttable.ajax.reload(null, false);
                                                                window.downloadlisttable.fixedHeader.adjust();

                                                            } else {
                                                                td.html(change_assignee_original_html);
                                                            }
                                                        });
                                                    }
                                                },
                                                cancel: {
                                                    text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                    action: function() {
                                                        //$.alert('');
                                                        // console.log('everything stays the same');
                                                        // because probably the user wants to fix the problems on the page and submit again.. 
                                                        td.html(change_assignee_original_html);
                                                        return false;
                                                    }
                                                },
                                            }
                                        });


                                    } else {
                                        /** we revert the td back to the previous values without saving to db */
                                        td.html(change_assignee_original_html);
                                    }
                                }
                            });


                            $(window).resize(function() {
                                $('.is_deliver_time').css('width', '100%').css('height', '100%');
                            });

                            $(".is_deliver_time").focus();
                        });

                        $('.dataTable tbody').off('click', 'td.parent.change_custom_row_color').on('click', 'td.parent.change_custom_row_color', function(event) {
                            /**console.log('td.parent.change_custom_row_color');*/
                            /** need to loose focus from the other changeable tds */
                            $("#editrecord_input").blur();
                            $('.is_br24_employee').selectize().blur();

                            /** it seems to be sending many trips to the db how to prevent that? */


                            var target = event.target;
                            event.preventDefault();

                            /**if another  color picker is open close that one first before opening .... */

                            var tr = $(this).closest('tr');
                            var td = $(this);

                            /**console.log(td);*/

                            var original_html = $(this).html();
                            var row = window.downloadlisttable.row(tr);
                            var thecellvalue = $(this).text();
                            //console.log(thecellvalue);
                            var rowIndex = tr.data('rowindex');
                            var change_custom_colour_case_Id = $(this).data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                            keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_custom_colour_case_Id;
                            var custom_delivery_time = null;
                            var encrypted_assignee_ids = null;

                            var timestamp_Id = $(this).data('date_ts');
                            // var idx = table.cell(this).index().column;
                            // var date_number = table.column(idx).header();
                            // var clcikedcolumnheader_value = $(date_number).html();

                            //var $box = $("#colorPicker_");
                            var $box = $("#colorPicker_" + change_custom_colour_case_Id);
                            $box.tinycolorpicker();
                            var box = $box.data("plugin_tinycolorpicker");
                            $hiddencolorinput = $box.find(".colorInput");
                            /**console.log($hiddencolorinput.val());*/

                            /** only on mouse up  they could be holding the button down */

                            $box.off('change').on("change", function() {
                                //console.log("do something when a new color is set");
                                /** we change the row color and go into the db and save the color hex to the table */
                                /**console.log(box.colorRGB);*/

                                /** go into the db and save the value */

                                /** use ajax to send data to php */
                                /** only do if the orig was not white and white is clicked */
                                /** if orig was white and new is white don't do either */
                                if (box.colorHex == '#FFFFFF' && $hiddencolorinput.val() == '#') {
                                    /**don't do */
                                } else {
                                    /** color the whole row */
                                    // tr.css('background-color', box.colorRGB);
                                    // tr.autotextcolor();
                                    td.css('background-color', box.colorRGB);
                                    td.autotextcolor();
                                }
                            });
                            $box.one().on("mouseup", function() {
                                /**console.log('mouseup');*/
                                /** can we get it do only once on mouse up */
                                if (box.colorHex == '#FFFFFF' && $hiddencolorinput.val() == '#') {
                                    /**don't do */
                                } else {

                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                    var data = {
                                        'case_id': change_custom_colour_case_Id,
                                        'encrypted_case_id': encrypted_case_id_uploading_to,
                                        'new_custom_color': box.colorHex
                                    };

                                    app.ajax.json(app.data.change_custom_color_for_job, data, null, function() {
                                        /**console.log(app.ajax.result);*/
                                        success_ajax_then_refresh = app.ajax.result.success;
                                        if (app.ajax.result.success == true) {
                                            /** color the whole row */
                                            // tr.css('background-color', box.colorRGB);
                                            // tr.autotextcolor();
                                            td.css('background-color', box.colorRGB);
                                            td.autotextcolor();

                                            /** can we get the new updated at time here? and populate the updated_at column with the new value for the current case_id */
                                            // console.log(tr.children().next('.last_updated'));
                                            // console.log(app.ajax.result.updated_at);
                                            tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                            tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                                        } else {
                                            /** we don't change anything */
                                            tr.autotextcolor();
                                        }
                                    });
                                }
                            });
                        });

                        $('.dataTable tbody').off('click', 'td.parent.change_internal_notes').on('click', 'td.parent.change_internal_notes', function(event) {
                            /**console.log('td.parent.change_internal_notes');*/
                            /** need to loose focus from the other changeable tds */
                            $("#editrecord_input").blur();
                            $('.is_br24_employee').selectize().blur();


                            /** it seems to be sending many trips to the db how to prevent that? */
                            if ($('#edit_from_amount_input_' + change_custom_internal_notes_case_Id + '').is(":visible") == true) {
                                return false;
                            }
                            keep_track_of_last_clicked_item_to_react_after_reload = 'change_from_amount';
                            var target = event.target;
                            event.preventDefault();

                            var tr = $(this).closest('tr');
                            var td = $(this);
                            var td_height = td.height();
                            /**console.log(td_height);*/
                            td_height = td_height + 20;

                            var change_internal_note_original_html = $(this).html();
                            /**console.log(change_internal_note_original_html);*/

                            var row = window.downloadlisttable.row(tr);
                            var thecellvalue = $(this).html();
                            /**console.log(thecellvalue);*/
                            thecellvalue = thecellvalue.replace(/(?:<br>)/g, '\r\n');
                            var rowIndex = tr.data('rowindex');
                            var change_custom_internal_notes_case_Id = $(this).data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                            keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_custom_internal_notes_case_Id;
                            /**console.log(change_custom_internal_notes_case_Id);*/
                            var timestamp_Id = $(this).data('date_ts');
                            // var idx = table.cell(this).index().column;
                            // var date_number = table.column(idx).header();
                            // var clcikedcolumnheader_value = $(date_number).html();
                            var input = $('<textarea id="edit_from_amount_input_' + change_custom_internal_notes_case_Id + '" cols="10" rows="5" charswidth="23"  style="white-space: pre-wrap; line-height: 15px; min-height: 30px; width: 100%; height: ' + td_height + 'px; display: block; z-index: 12; resize: vertical; color: black;"></textarea>');
                            input.val(thecellvalue);
                            td.html(input);

                            $(document).on('keydown', '#edit_from_amount_input_' + change_custom_internal_notes_case_Id, function(e) {
                                var input = $(this);
                                var oldVal = input.val();
                                var regex = new RegExp(input.attr('pattern'), 'g');

                                setTimeout(function() {
                                    var newVal = input.val();
                                    if (!regex.test(newVal)) {
                                        input.val(oldVal);
                                    }
                                }, 0);
                                /** if enter key is pressed allow it */
                                if (e.keyCode == 13) {
                                    // input.blur();

                                }
                                /** if esc key is pressed return the thing back to the original ?? */
                                if (e.keyCode == 27) {
                                    td.html(change_internal_note_original_html);
                                }
                            });


                            $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).click(function(event) {
                                event.stopImmediatePropagation();
                                /***/
                            });
                            $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).focus(function() {
                                /** select everything in the input */
                                var save_this = $(this);
                                window.setTimeout(function() {
                                    save_this.select();
                                }, 30);
                            });

                            $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).focus();

                            $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).blur(function() {
                                var edited_number = $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).val();
                                edited_number = edited_number.replace(/(?:\r\n|\r|\n)/g, '<br>');
                                /**console.log(edited_number);*/
                                if (change_internal_note_original_html == edited_number) {
                                    /** console.log('change_from_amount td.html(change_internal_note_original_html)'); */
                                    td.html(change_internal_note_original_html);

                                    /** keep_track_of_last_clicked_item_to_react_after_reload = null; */
                                    /** keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = null; */
                                } else {
                                    /** its different */
                                    /**app.util.nprogressinit();*/
                                    /**app.util.fullscreenloading_start();*/
                                    $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).remove();
                                    // var change_from_amount_replace_html = $("<span class='label label-primary currency_number_format'>" + edited_number + "</span>");
                                    // console.log(change_from_amount_replace_html);
                                    /**console.log('change_from_amount td.html(edited_number)');*/

                                    td.html(edited_number);

                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                    var data = {
                                        'case_id': change_custom_internal_notes_case_Id,
                                        'encrypted_case_id': encrypted_case_id_uploading_to,
                                        'new_custom_internal_note': edited_number
                                    };

                                    /** use ajax to send data to php */
                                    app.ajax.json(app.data.change_custom_internal_note_for_job, data, null, function() {
                                        /**console.log(app.ajax.result);*/
                                        success_ajax_then_refresh = app.ajax.result.success;
                                        if (app.ajax.result.success == true) {

                                            td.html(edited_number);

                                            // console.log(tr.children().next('.last_updated'));
                                            // console.log(app.ajax.result.updated_at);
                                            tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                            tr.children().next('.last_updated').html(app.ajax.result.updated_at);

                                        } else {
                                            td.html(change_internal_note_original_html);
                                        }
                                    });


                                }
                            });
                        });

                        $('.dataTable tbody').off('click', 'td.parent.change_tags').on('click', 'td.parent.change_tags', function(event) {
                            // console.log('td.parent.change_tags');
                            if ($('.is_br24_hashtag').is(":visible") == true) {
                                return false;
                            }

                            keep_track_of_last_clicked_item_to_react_after_reload = 'change_tags';
                            var target = event.target;
                            event.preventDefault();

                            var tr = $(this).closest('tr');
                            var td = $(this);
                            var change_tags_original_html = $(this).html();
                            /**console.log('change_tags_original_html');*/
                            /**console.log(change_tags_original_html);*/

                            /** on click you ened to sanitize it .. */
                            /**<span class='highlight_span'></span>*/
                            var remembering_the_highlighting = change_tags_original_html;
                            var has_highlighted_text = remembering_the_highlighting.includes('<span class="highlight_span">');
                            /**console.log('has_highlighted_text', has_highlighted_text);*/

                            var highlighted_text_in_span_before_word = remembering_the_highlighting.split('<span class="highlight_span">');
                            /**console.log(highlighted_text_in_span_before_word);*/
                            if (highlighted_text_in_span_before_word[1] != undefined || highlighted_text_in_span_before_word[1] != null) {
                                var word_to_highlight = highlighted_text_in_span_before_word[1].split('</span>')[0];
                            }else{
                                var word_to_highlight = '';
                            }
                            /**console.log(word_to_highlight);*/


                            var sanitized_for_plugin = change_tags_original_html.replace("</span>", "").replace('<span class="highlight_span">', "");
                            /**console.log(sanitized_for_plugin);*/

                            var row = window.downloadlisttable.row(tr);
                            var thecellvalue = $(this).text();
                            // console.log('thecellvalue');
                            // console.log(thecellvalue);
                            //var unformatted_number = $(this).parseNumber({ format: number_format_per_locale, locale: numberformat_locale });
                            /** console.log(unformatted_number); */
                            var rowIndex = tr.data('rowindex');
                            var change_tag_case_Id = $(this).data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                            /**console.log(encrypted_case_id_uploading_to);*/

                            keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_tag_case_Id;
                            /**console.log(change_tag_case_Id);*/
                            var timestamp_Id = $(this).data('date_ts');
                            // var idx = table.cell(this).index().column;
                            // var date_number = table.column(idx).header();
                            // var clcikedcolumnheader_value = $(date_number).html();
                            //var input = $('<input id="edit_tags_input_' + change_tag_case_Id + '" type="number" style="text-align:center; width: 100px; display: block; margin: 0 auto; z-index: 12;" min="0" max="2147483647" step="1" pattern="^\\d{1,10}?$"></input>');

                            var input = $('<select style="width: 100%; height: 46px;" class="is_br24_hashtag form-control" title="" multiple></select>');
                            /** I want this input to be looking like the selectize one */

                            //input.val(unformatted_number);
                            td.html(input);
                            

                            /**console.log('ready');*/
                            var select_list_hashtag_list = app.data.selectize_hashtag_list_formated_json;
                            /**console.log('select_list_hashtag_list=' + JSON.stringify(select_list_hashtag_list));*/

                            var select_selected_list_hashtags_json = app.data.selectize_selected_hashtags_formated_json;
                            /**console.log('select_selected_list_hashtags_json=' + JSON.stringify(select_selected_list_hashtags_json));*/

                            var select_selected_list_caseid_has_hastags = select_selected_list_hashtags_json.map(function(item) {
                                return item['name'];
                            });

                            /**console.log('AFTERselect_selected_list_caseid_has_hastags=' + select_selected_list_caseid_has_hastags);*/

                            /** if there are any that are any details we can use to populate the selectize */
                            if (sanitized_for_plugin != '') {
                                select_selected_list_caseid_has_hastags = sanitized_for_plugin.split(" ");
                                /**console.log(select_selected_list_caseid_has_hastags);*/
                                Object.assign({}, [select_selected_list_caseid_has_hastags]);
                            }
                            /**console.log('FROMHTMLselect_selected_list_caseid_has_hastags=' + select_selected_list_caseid_has_hastags);*/


                            var hashtag_ids = null;
                            var encrypted_hashtag_ids = null;

                            if (!window.Selectize.prototype.positionDropdownOriginal) {
                                window.Selectize.prototype.positionDropdownOriginal = window.Selectize.prototype.positionDropdown;
                                window.Selectize.prototype.positionDropdown = function() {
                                    if (this.settings.dropdownDirection === 'up') {
                                        let $control = this.$control;
                                        let offset = this.settings.dropdownParent === 'body' ? $control.offset() : $control.position();

                                        var the_td = $control.parent().parent();
                                        var the_td_offset = the_td.offset();
                                        var position_relative_to_viewport = parseInt(the_td_offset.top) - parseInt($(window).scrollTop());

                                        var switch_to_dropdown = false;
                                        if (position_relative_to_viewport <= 500) {
                                            var switch_to_dropdown = true;
                                        }

                                        var height_of_drowdown = 261;
                                        if (switch_to_dropdown) {
                                            var height_of_td = the_td.height() - 12;
                                            height_of_drowdown = -parseInt(height_of_td);
                                        }

                                        this.$dropdown.css({
                                            width: $control.outerWidth(),
                                            top: offset.top - height_of_drowdown,
                                            left: offset.left,
                                        });

                                        this.$dropdown.addClass('direction-' + this.settings.dropdownDirection);
                                        this.$control.addClass('direction-' + this.settings.dropdownDirection);
                                        this.$wrapper.addClass('direction-' + this.settings.dropdownDirection);
                                    } else {
                                        window.Selectize.prototype.positionDropdownOriginal.apply(this, arguments);
                                    }
                                };
                            }


                            var $is_br24_hashtag_select = $('.is_br24_hashtag').selectize({
                                plugins: ['remove_button', 'optgroup_columns'],
                                persist: true,
                                maxItems: 200,
                                mode: 'multi',
                                dropdownDirection: 'up',
                                create: function(input) {
                                    /**console.log(input);*/
                                    var result = input.replace('#', '');
                                    return {
                                        name: '#' + result.toUpperCase(),
                                    }
                                },
                                /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                                placeholder: '-- Add Tag --',
                                valueField: ['name'],
                                labelField: 'name',
                                searchField: ['name'],
                                options: select_list_hashtag_list,
                                /** list of all the viable employees on init */
                                items: select_selected_list_caseid_has_hastags,
                                /** list of already selected employees on init */
                                hideSelected: false,
                                openOnFocus: true,
                                closeAfterSelect: true,
                                render: {
                                    item: function(item, escape) {
                                        return '<div>' +
                                            (item.name ? '<span class="name"><u><b>' + item.name + '</b></u></span>' : '') +
                                            //'<span style="color: #ccc">&nbsp;</span>' +
                                            //(item.xml_jobid_title ? ' <i class="fa fa-angle-double-right text-danger"></i> ' : '') +
                                            //(item.xml_jobid_title ? '<span class="xml_jobid_title" style="font-size: 9px; color: #1cd;"><b>' + item.xml_jobid_title + '</b></span>' : '') +
                                            //'<br>' +
                                            //(item.xml_title_contents ? '<span class="xml_title_contents" style="font-size: 10px;">' + item.xml_title_contents + '</span>' : '') +
                                            //'<br>' +
                                            //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                                            '</div>';
                                    },
                                    option: function(item, escape) {
                                        var label = item.xml_title_contents || item.email;
                                        var caption = item.xml_title_contents ? item.email : null;

                                        return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                                            '<span class="label label-primary">' + item.name + '</span>' +
                                            '<span style="color: #ccc">&nbsp;</span>' +

                                            //(item.name ? ' <i class="fa fa-angle-double-right text-danger"></i> ' : '') +
                                            //(item.name ? '<span class="name" style="font-size: 9px; color: #1cd;"><b>' + item.name + '</b></span>' : '') +
                                            //'<br>' +

                                            //(item.xml_title_contents ? '<span class="xml_title_contents" style="font-size: 10px;">' + item.xml_title_contents + '</span>' : '') +
                                            //(item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                                            //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                                            '</div>';
                                    }
                                },
                                onChange: function(value) {
                                    /**console.log(value);*/
                                    /**console.log('on change');*/
                                    //$('#cb_add_family_members_details_form').addClass('dirty');
                                    /** when it changes i want to update the variable so that it can be loaded together with the files */
                                    /**console.log($('.is_br24_hashtag').val());*/
                                    hashtag_ids = $('.is_br24_hashtag').val();
                                    /**console.log(hashtag_ids);*/
                                    /**since we only want to store the tage without the hashcharacter */
                                    var result = hashtag_ids.map(function(x) { return x.replace(/#/g, ''); });
                                    /**console.log(result);*/
                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                    var data = {
                                        'case_id': change_tag_case_Id,
                                        'encrypted_case_id': encrypted_case_id_uploading_to,
                                        'hashtag': result
                                    };

                                    /** use ajax to send data to php */
                                    app.ajax.json(app.data.change_tags_for_job, data, null, function() {
                                        /**console.log(app.ajax.result);*/
                                        success_ajax_then_refresh = app.ajax.result.success;
                                        if (app.ajax.result.success == true) {

                                            app.data.selectize_hashtag_list_formated_json = app.ajax.result.selectize_hashtag_list_formated_json;
                                            /**console.log('after');*/
                                            /**console.log(select_list_hashtag_list);*/
                                            // console.log(tr.children().next('.last_updated'));
                                            // console.log(app.ajax.result.updated_at);
                                            tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                            tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                                        } else {

                                            // $.each(app.ajax.result.errors, function(idx, val) {
                                            //     $.alert(val);
                                            // });
                                        }
                                    });

                                }
                            });

                            var is_br24_hashtag_select_selectize = $is_br24_hashtag_select[0].selectize;
                            var is_br24_hashtag_select_old_options = is_br24_hashtag_select_selectize.settings;
                            var selectize_focus_handler = function(value, $item) {
                                var width_to_be = $('.selectize-control').outerWidth();
                                var height_to_be = 300;
                                $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                            };
                            is_br24_hashtag_select_selectize.on('focus', selectize_focus_handler);


                            var selectize_blur_handler = function(value, $item) {
                                // var width_to_be = $('.selectize-control').outerWidth();
                                // var height_to_be = 300;


                                // $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                                var this_td_new_content_formated = '';

                                hashtag_ids = $('.is_br24_hashtag').val();
                                /**console.log(hashtag_ids);*/
                                /**console.log(change_tags_original_html);*/
                                if (hashtag_ids === undefined || hashtag_ids.length == 0) {
                                    td.html('');
                                } else {
                                    /**console.log('blur');*/
                                    $.each(hashtag_ids, function(idx, val) {
                                        // console.log(idx);
                                        // console.log(val);
                                        if (this_td_new_content_formated == '') {
                                            this_td_new_content_formated = val;
                                        } else {
                                            this_td_new_content_formated = this_td_new_content_formated + ' ' + val;
                                        }
                                    });

                                    /**console.log('this_td_new_content_formated', this_td_new_content_formated);*/

                                    /** need to put back the highlighting if the word is still there.. */
                                    /**console.log('word_to_highlight', word_to_highlight);*/


                                    if(word_to_highlight != ""){
                                        var working_on_the_rehighlighting_array = this_td_new_content_formated.split(word_to_highlight);
                                        var new_string_with_highlighting = '';
                                        $.each(working_on_the_rehighlighting_array, function(idx, val) {
                                            // console.log(idx);
                                            // console.log(val);
                                            if(idx == (working_on_the_rehighlighting_array.length - 1)){
                                                new_string_with_highlighting = new_string_with_highlighting.concat(val);
                                            }else{
                                                new_string_with_highlighting = new_string_with_highlighting.concat(val).concat('<span class="highlight_span">'+word_to_highlight+'</span>');
                                            }
                                        });
                                    }else{
                                        new_string_with_highlighting = this_td_new_content_formated;
                                    }

                                    td.html(new_string_with_highlighting);
                                }

                                /** otherwise return it to what it was before */

                                /** want to be able to save the information on the database so that it can be called back when the page reloads */
                                /** also don't want to reload the page want to have the selected options become the td content */
                                /** and if there is content to get the selectize automatically load those ones on click */
                                /** */

                                /** should do it on blur as well or on change to so that as soon as they click its on the database already. */
                            };
                            is_br24_hashtag_select_selectize.on('blur', selectize_blur_handler);

                            /** filter the shifts from the swapable shifts select */
                            var item_remove_handler = function(value, $item) {
                                /**console.log('item_removed');*/
                                hashtag_ids = $('.is_br24_hashtag').val();
                            };
                            is_br24_hashtag_select_selectize.on('item_remove', item_remove_handler);


                            is_br24_hashtag_select_selectize.$control_input.on('keydown', function(e) {
                                var allowedCode = [8, 13, 44, 45, 46, 95];
                                var charCode = (e.charCode) ? e.charCode : ((e.keyCode) ? e.keyCode : ((e.which) ? e.which : 0));
                                /**console.log(charCode);*/

                                if (charCode > 31 && (charCode < 64 || charCode > 90) && (charCode < 97 || charCode > 122) && (charCode < 48 || charCode > 57) && (allowedCode.indexOf(charCode) == -1)) {
                                    return false;
                                } else {
                                    return true;
                                }
                            });



                            $(window).resize(function() {
                                var width_to_be = $('.selectize-control').outerWidth();
                                var height_to_be = 300;
                                $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                            });

                            is_br24_hashtag_select_selectize.focus();
                        });

                        $('.dataTable tbody').off('click', '.output_number_of_pictures_expected').on('click', '.output_number_of_pictures_expected', function(event) {
                            /**console.log('.output_number_of_pictures_expected');*/


                            /** need to loose focus from the other changeable tds */
                            $("#editrecord_input").blur();
                            $('.is_br24_employee').selectize().blur();


                            /** it seems to be sending many trips to the db how to prevent that? */
                            if ($('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id + '').is(":visible") == true) {
                                return false;
                            }
                            keep_track_of_last_clicked_item_to_react_after_reload = 'change_from_amount';
                            var target = event.target;
                            event.preventDefault();

                            var tr = $(this).closest('tr');
                            var td = $(this);
                            /**console.log(td);*/

                            $(this).parent().children('.output_number_of_pictures_real').css('visibility', 'hidden');


                            var td_height = td.parent().height();
                            /**console.log(td_height);*/
                            td_height = td_height + 20;
                            var td_width = td.parent().width();

                            var change_internal_note_original_html = $(this).html();
                            /**console.log(change_internal_note_original_html);*/

                            var row = window.downloadlisttable.row(tr);
                            var thecellvalue = $(this).html();
                            /**console.log(thecellvalue);*/
                            thecellvalue = thecellvalue.replace(/(?:<br>)/g, '\r\n');
                            var rowIndex = tr.data('rowindex');
                            var change_output_number_of_pictures_expected_case_Id = $(this).parent().parent().data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                            keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_output_number_of_pictures_expected_case_Id;
                            /**console.log(change_output_number_of_pictures_expected_case_Id);*/
                            var timestamp_Id = $(this).data('date_ts');
                            // var idx = table.cell(this).index().column;
                            // var date_number = table.column(idx).header();
                            // var clcikedcolumnheader_value = $(date_number).html();
                            var input = $('<input id="edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id + '" type="number" style="text-align:left; width: ' + td_width + 'px; display: block; color: #000; margin: 0 auto; z-index: 12;" min="0" max="999" step="1" pattern="^\\d{1,10}?$"></input>');
                            input.val(thecellvalue);
                            td.html(input);

                            $(document).on('keydown', '#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id, function(e) {
                                var input = $(this);
                                var oldVal = input.val();
                                var regex = new RegExp(input.attr('pattern'), 'g');

                                setTimeout(function() {
                                    var newVal = input.val();
                                    if (!regex.test(newVal)) {
                                        input.val(oldVal);
                                    }
                                }, 0);
                                /** if enter key is pressed allow it */
                                if (e.keyCode == 13) {
                                    // input.blur();

                                }
                                /** if esc key is pressed return the thing back to the original ?? */
                                if (e.keyCode == 27) {
                                    td.html(change_internal_note_original_html);
                                }
                            });


                            $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).click(function(event) {
                                event.stopImmediatePropagation();
                                /***/
                            });
                            $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).focus(function() {
                                /** select everything in the input */
                                var save_this = $(this);
                                window.setTimeout(function() {
                                    save_this.select();
                                }, 30);
                            });

                            $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).focus();

                            $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).blur(function() {
                                var edited_number = $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).val();
                                edited_number = edited_number.replace(/(?:\r\n|\r|\n)/g, '<br>');
                                /**console.log(edited_number);*/
                                if (change_internal_note_original_html == edited_number) {
                                    /** console.log('change_from_amount td.html(change_internal_note_original_html)'); */
                                    td.html(change_internal_note_original_html);
                                    td.parent().children('.output_number_of_pictures_real').css('visibility', 'visible');

                                    /** keep_track_of_last_clicked_item_to_react_after_reload = null; */
                                    /** keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = null; */
                                } else {
                                    /** its different */
                                    /**app.util.nprogressinit();*/
                                    /**app.util.fullscreenloading_start();*/
                                    $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).remove();
                                    // var change_from_amount_replace_html = $("<span class='label label-primary currency_number_format'>" + edited_number + "</span>");
                                    // console.log(change_from_amount_replace_html);
                                    /**console.log('change_from_amount td.html(edited_number)');*/

                                    td.html(edited_number);

                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                    var data = {
                                        'case_id': change_output_number_of_pictures_expected_case_Id,
                                        'encrypted_case_id': encrypted_case_id_uploading_to,
                                        'new_custom_output_expected': edited_number
                                    };

                                    /** use ajax to send data to php */
                                    app.ajax.json(app.data.change_custom_output_expected_for_job, data, null, function() {
                                        /**console.log(app.ajax.result);*/
                                        success_ajax_then_refresh = app.ajax.result.success;
                                        if (app.ajax.result.success == true) {

                                            td.html(edited_number);
                                            td.parent().children('.output_number_of_pictures_real').css('visibility', 'visible');

                                            // console.log(tr.children().next('.last_updated'));
                                            // console.log(app.ajax.result.updated_at);
                                            tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                            tr.children().next('.last_updated').html(app.ajax.result.updated_at);

                                        } else {
                                            td.html(change_internal_note_original_html);
                                            td.parent().children('.output_number_of_pictures_real').css('visibility', 'visible');
                                        }
                                    });


                                }
                            });
                        });


                        /** preview required checkbox */
                        $("input[name*='edit_']").on('click', function(event) {
                            event.stopPropagation();
                            /**console.log('clickingdirectoncheckbox');*/

                            var tr = $(this).closest('tr');
                            var change_assignees_value = tr.find('.change_assignees').html();
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');

                            case_Id = $(this).parent().data('case_id');
                            date_ts_Id = $(this).parent().data('date_ts');
                            var status = null;
                            var checkbox_in_td = $("input[name='edit_" + case_Id + "']");
                            if (checkbox_in_td.is(':checked')) {
                                /**console.log('3was not checked');*/
                                checkbox_in_td.prop("checked", true);
                                status = 1;
                            } else {
                                /**console.log('3was checked');*/
                                checkbox_in_td.prop("checked", false);
                                status = 2;
                            }
                            /**console.log(status);*/
                            /** we go in via ajax to amend the status column where user_id and date on accept reject penalty table */
                            app_ops.manage_downloadlist.profile.sync_preview_required_status(case_Id, encrypted_case_id_uploading_to, status, tr, checkbox_in_td, change_assignees_value);
                        });

                        $('.two_weeks_checkbox_col.parent').not("input[name*='edit_']").on('click', function(event) {
                            event.stopPropagation();
                            /**console.log('clickingaroundcheckboxonemployeerow');*/
                            /** also gets triggered if clicking directy on the checkbox */
                            // if ($('.checkbox_check_uncheck_all').is(':checked')) {
                            //     $('.checkbox_check_uncheck_all').prop("checked", false);
                            // }
                            var tr = $(this).closest('tr');
                            /**console.log(tr.find('.change_assignees').html());*/
                            var change_assignees_value = tr.find('.change_assignees').html();

                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');

                            case_Id = $(this).data('case_id');
                            date_ts_Id = $(this).data('date_ts');
                            var status = null;
                            var checkbox_in_td = $("input[name='edit_" + case_Id + "']");
                            if (checkbox_in_td.is(':checked')) {
                                /**console.log('3was checked');*/
                                checkbox_in_td.prop("checked", false);
                                status = 2;
                            } else {
                                /**console.log('3was not checked');*/
                                checkbox_in_td.prop("checked", true);
                                status = 1;
                            }
                            /**console.log(status);*/
                            /** we go in via ajax to amend the status column where user_id and date on accept reject penalty table */
                            /** normally the checkbox would be disabled if there are no assignees */
                            /** but if they hack it they will still be able to change the check box */
                            /** so as a client side validation check if the assignees have been set or not and disable */
                            /** */

                            app_ops.manage_downloadlist.profile.sync_preview_required_status(case_Id, encrypted_case_id_uploading_to, status, tr, checkbox_in_td, change_assignees_value);
                        });

                        $('input[type="checkbox"], .two_weeks_checkbox_col').css('cursor', 'pointer');


                        $(document).ready(function() {
                            var currentColorBox = ''; /** super important if you want only specific set of colorboxes to resize */
                            var window_focus = true;
                            $(window).focus(function() { window_focus = true; }).blur(function() { window_focus = false; });

                            //define some variables here so can use inside the colorbox onClose Callbacks
                            var checks_if_avatar_colorbox_is_open = null;
                            var success_ajax_then_refresh = null;
                            var been_out = null;
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
                                    $("#cb_add_downloadlist.ajax").colorbox.close();
                                    $("#cb_edit_downloadlist.ajax").colorbox.close();
                                    clearInterval(close_colorbox_refreshIntervalId);
                                }, 100);
                            };

                            /** must be at the end */
                            var cboxOptions = { width: '600px', height: '400px', }
                            $(window).resize(function() {
                                var colorboxes_array = ["cb_add_downloadlist", "cb_edit_downloadlist"];
                                if (colorboxes_array.indexOf(currentColorBox) > -1) {
                                    $.colorbox.resize({
                                        width: window.innerWidth > parseInt(cboxOptions.maxWidth) ? cboxOptions.maxWidth : cboxOptions.width,
                                        height: window.innerHeight > parseInt(cboxOptions.maxHeight) ? cboxOptions.maxHeight : cboxOptions.height
                                    });
                                }
                            });

                            $("#cb_add_downloadlist.ajax").colorbox({
                                rel: 'nofollow',
                                width: "600px",
                                height: "400px",
                                left: '9%',
                                top: '200px',
                                escKey: true, //escape key will not close
                                overlayClose: false, //clicking background will not close
                                closeButton: false, //hide the close button
                                onOpen: function() {
                                    //console.log('onOpen: colorbox is about to open');
                                    currentColorBox = 'cb_add_downloadlist';
                                    //app.util.fullscreenloading_start();
                                },
                                onLoad: function() {
                                    //console.log('onLoad: colorbox has started to load the targeted content');
                                    //
                                },
                                onComplete: function() {
                                    //app.util.fullscreenloading_end();
                                    //timer();
                                    //console.log('timer_started');
                                    //console.log('onComplete: colorbox has displayed the loaded content');

                                    $("#advance_amount").focus(function() {
                                        $(this).parseNumber({ format: number_format_per_locale, locale: numberformat_locale });
                                        $(this).select();
                                    });
                                    $("#advance_amount").blur(function() {
                                        $(this).formatNumber({ format: number_format_per_locale, locale: numberformat_locale });
                                        //
                                    });

                                    var counting_ssd = 1;
                                    var $calendar_date = $('.calendar_date');
                                    $calendar_date.datepicker({
                                        dateFormat: prefered_dateFormat,
                                        showMonthAfterYear: true,
                                        numberOfMonths: 1,
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange: "-10:+1",
                                        showOtherMonths: true,
                                        selectOtherMonths: true,
                                        toggleActive: true,
                                        minDate: "-10y",
                                        maxDate: "+1y",
                                        autoclose: true,
                                        onClose: function() {
                                            var addressinput = $(this).val();
                                            /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                                            var res = patt.test(addressinput);

                                            if (res == true) {
                                                $('#calendar_date').closest('td').removeClass('has-error');
                                                $('#calendar_date').nextAll('.help-block').css('display', 'none');

                                                $(this).blur();
                                                counting_ssd = 1;
                                            } else {
                                                $("#calendar_date").closest("td").addClass("has-error");
                                                if (counting_ssd == 1) {
                                                    $("#calendar_date").after('<span class="help-block"><strong>' + eval("app.translations." + app.data.locale + ".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations." + app.data.locale + ".or_choose_from_the_calendar") + '</strong></span>');
                                                } else {
                                                    // it exists
                                                }
                                                counting_ssd++;
                                                $(this).blur();
                                            }
                                        },
                                        beforeShow: function(input, obj) {
                                            $calendar_date.after($calendar_date.datepicker('widget'));

                                            setTimeout(function() {
                                                $('#ui-datepicker-div').css('top', '').css('left', '');
                                            }, 0);
                                        }
                                    });

                                    $calendar_date.datepicker().datepicker("setDate", new Date());


                                    var select_list_employee_list = app.data.selectize_employee_list_formated_json;
                                    /**console.log('select_list_employee_list=' + JSON.stringify(select_list_employee_list));*/
                                    var select_selected_employee_json = app.data.selectize_selected_employee_formated_json;
                                    //console.log('select_selected_employee_json=' + JSON.stringify(select_selected_employee_json));

                                    select_selected_list_employee_to_receive_messages = select_selected_employee_json.map(function(item) {
                                        return item['fk_is_br24_employee'];
                                    });
                                    //console.log('AFTERselect_selected_list_employee_to_receive_messages=' + select_selected_list_employee_to_receive_messages);

                                    var $recipients_select = $('#recipients').selectize({
                                        plugins: ['remove_button', 'optgroup_columns'],
                                        persist: false,
                                        maxItems: 1,
                                        mode: 'multi',
                                        /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                                        placeholder: '-- ' + eval("app.translations." + app.data.locale + ".select_employee") + ' --',
                                        valueField: 'user_id',
                                        labelField: 'user_id',
                                        searchField: ['user_id', 'fullname', 'fullname_noaccents'],
                                        options: select_list_employee_list,
                                        /** list of all the viable employees on init */
                                        items: select_selected_list_employee_to_receive_messages,
                                        /** list of already selected employees on init */
                                        hideSelected: true,
                                        openOnFocus: true,
                                        closeAfterSelect: true,
                                        render: {
                                            item: function(item, escape) {
                                                return '<div>' +
                                                    (item.user_id ? '<span class="user_id"><b>' + item.user_id + '</b></span>' : '') +
                                                    '<span style="color: #ccc">&nbsp;</span>' +
                                                    (item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                    '<br>' +
                                                    (item.position ? '<span class="position" style="font-size: 9px;">' + item.position + '</span>' : '') +
                                                    //'<br>' +
                                                    //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                                                    '</div>';
                                            },
                                            option: function(item, escape) {
                                                var label = item.fullname || item.email;
                                                var caption = item.fullname ? item.email : null;
                                                return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                                                    '<span class="label label-primary">' + item.user_id + '</span>' +
                                                    '<span style="color: #ccc">&nbsp;</span>' +
                                                    (item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                    '<br>' +
                                                    (item.position ? '<span class="position" style="font-size: 9px; color: #ccc;">' + item.position + '</span>' : '') +
                                                    (item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                                                    //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                                                    '</div>';
                                            }
                                        },
                                        onChange: function(value) {
                                            /** how to get the details populating the fields automatically if an employee is selected from the selectize plugin? provided the information is already on the db at the time */
                                            /** name | dateofbirth | tax code number | ID card number or Passport number */
                                            if (value === undefined || value.length == 0) {
                                                /**array empty or does not exist*/
                                            } else {

                                            }
                                            $('#cb_add_downloadlist_details_form').addClass('dirty');
                                        }
                                    });

                                    var recipients_select_selectize = $recipients_select[0].selectize;
                                    var recipients_select_old_options = recipients_select_selectize.settings;
                                    var selectize_focus_handler = function(value, $item) {
                                        var width_to_be = $('.selectize-control').outerWidth();
                                        $('.selectize-dropdown-content').css('width', width_to_be);
                                    };
                                    recipients_select_selectize.on('focus', selectize_focus_handler);


                                    /** filter the shifts from the swapable shifts select */
                                    var item_remove_handler = function(value, $item) {
                                        $('#cb_add_downloadlist_details_form').addClass('dirty');
                                    };
                                    recipients_select_selectize.on('item_remove', item_remove_handler);

                                    $(window).resize(function() {
                                        var width_to_be = $('.selectize-control').outerWidth();
                                        $('.selectize-dropdown-content').css('width', width_to_be);
                                    });





                                    var calendar_date = $('#calendar_date').val();
                                    var recipients = $('#recipients').val();
                                    var advance_amount = $('#advance_amount').val();
                                    var reason = $('reason').val();
                                    var payment_period_description = $('payment_period_description').val();

                                    var currentdate = new Date();
                                    var datetime = "Last Sync: " + currentdate.getDate() +
                                        "/" + (currentdate.getMonth() + 1) +
                                        "/" + currentdate.getFullYear() +
                                        " @ " + currentdate.getHours() +
                                        ":" + currentdate.getMinutes() +
                                        ":" + currentdate.getSeconds();

                                    var add_downloadlist_previous_data = [];
                                    add_downloadlist_previous_data['add_downloadlist_previous_data'] = {
                                        "whenwasset": datetime,

                                        "add_downloadlist_calendar_date": calendar_date,
                                        "add_downloadlist_recipients": recipients,
                                        "add_downloadlist_advance_amount": advance_amount,
                                        "add_downloadlist_reason": reason,
                                        "add_downloadlist_payment_period_description": payment_period_description
                                    };
                                    sessionStorage.setItem('Br24_' + app.env() + '_add_downloadlistinfo_previous_data', JSON.stringify(add_downloadlist_previous_data['add_downloadlist_previous_data']));



                                    $('#cb_add_downloadlist_details_form').areYouSure();
                                    $('#cb_add_downloadlist_details_form').on('change', 'select', function() {
                                        $("#add_downloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#add_downloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of select tags

                                    $('#cb_add_downloadlist_details_form').on('change keypress', 'input', function() {
                                        $("#add_downloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#add_downloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of input: the change event take care of input of type "hidden" also

                                    $('#cb_add_downloadlist_details_form').on('change keypress', 'textarea', function() {
                                        $("#add_downloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#add_downloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of textareas


                                    $("#add_downloadlist_reset").click(function() {
                                        var button_selector = $('#add_downloadlist_reset, #add_downloadlist_update');
                                        //hide all the buttons
                                        button_selector.prop('disabled', true).css('display', 'none');

                                        var previous_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_add_downloadlistinfo_previous_data'));

                                        /**console.log('clicked');*/
                                        /**console.log(previous_load['add_downloadlist_calendar_date']);*/

                                        $('#calendar_date').val(previous_load['add_downloadlist_calendar_date']);
                                        $('#advance_amount').val(previous_load['add_downloadlist_advance_amount']);
                                        $('#reason').val(previous_load['add_downloadlist_reason']);
                                        $('#payment_period_description').val(previous_load['add_downloadlist_payment_period_description']);

                                        recipients_select_selectize.setValue(previous_load['add_downloadlist_recipients'], false);

                                        $('.has-error').removeClass('has-error');
                                        $('.help-block').css('display', 'none');
                                        $('#cb_add_downloadlist_details_form').trigger('reinitialize.areYouSure');
                                    });

                                    $("#add_downloadlist_update").click(function(e) {
                                        e.preventDefault();
                                        $('.alert_warning').css('display', 'none');
                                        $('.alert_success').css('display', 'none');

                                        var calendar_date = $('#calendar_date').val();
                                        var recipients = $('#recipients').val();
                                        var advance_amount = $('#advance_amount').val();
                                        var reason = $('reason').val();
                                        var payment_period_description = $('payment_period_description').val();


                                        var currentdate = new Date();
                                        var datetime = "Last Sync: " + currentdate.getDate() +
                                            "/" + (currentdate.getMonth() + 1) +
                                            "/" + currentdate.getFullYear() +
                                            " @ " + currentdate.getHours() +
                                            ":" + currentdate.getMinutes() +
                                            ":" + currentdate.getSeconds();

                                        var add_downloadlist_submitted_data = [];
                                        add_downloadlist_submitted_data['add_downloadlist_submitted_data'] = {
                                            "whenwasset": datetime,

                                            "add_downloadlist_calendar_date": calendar_date,
                                            "add_downloadlist_recipients": recipients,
                                            "add_downloadlist_advance_amount": advance_amount,
                                            "add_downloadlist_reason": reason,
                                            "add_downloadlist_payment_period_description": payment_period_description
                                        };
                                        sessionStorage.setItem('Br24_' + app.env() + '_add_downloadlistinfo_submitted_data', JSON.stringify(add_downloadlist_submitted_data['add_downloadlist_submitted_data']));

                                        /** convert the number fields to unformatted number momentarily*/
                                        $("#advance_amount").parseNumber({ format: number_format_per_locale, locale: numberformat_locale });

                                        var formData = new FormData($('#cb_add_downloadlist_details_form')[0]);
                                        formData.append("recipients", recipients);

                                        NProgress.configure({ parent: '#cboxTitle', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                        NProgress.start();

                                        $('.cb_loader').css('display', 'block').css('cursor', 'wait');
                                        $('#cb_top').addClass('nprogress-busy').css('pointer-events', 'none');
                                        //app.util.fullscreenloading_start();

                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $("#add_downloadlist_details_table").data("token") } });
                                        app.ajax.formdata(app.data.manage_add_downloadlist, formData, null, function() {
                                            console.log(app.ajax.resultformdata);
                                            success_ajax_then_refresh = app.ajax.resultformdata.success;

                                            NProgress.done();
                                            $('.cb_loader').css('display', 'none').css('cursor', 'auto');
                                            $('#cb_top').removeClass('nprogress-busy').css('pointer-events', 'auto');

                                            if (app.ajax.resultformdata.success == true) {
                                                $('#cboxLoadedContent').css('background-color', '#4CAF50');
                                                $('.onSuccess_makeGreen').css('background-color', '#4CAF50');
                                                $('.ibox-tool-userid').css('color', 'white');
                                                $('#cb_add_downloadlist_details_form').css('display', 'none');
                                                close_colorbox_timer();
                                                $(document.body).css('pointer-events', 'none');
                                                app.util.fullscreenloading_start();
                                                if (app.ajax.resultformdata.process_penalties_accept_reject_table_sync == true) {
                                                    app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                }
                                            } else {
                                                $("#advance_amount").formatNumber({ format: number_format_per_locale, locale: numberformat_locale });

                                                $('.has-error').removeClass('has-error');
                                                $('.help-block').detach();

                                                $.each(app.ajax.resultformdata.errors, function(idx, val) {
                                                    app_ops.manage_downloadlist.profile.foreach_handle_error_display(idx, val);
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
                                                    $('#calendar_date').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #calendar_date');
                                                    });
                                                    $('#recipients').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #recipients');
                                                    });
                                                    $('#advance_amount').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).nextAll('.help-block').css('display', 'none');
                                                        console.log('something changed in #advance_amount');
                                                    });
                                                    $('#reason').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #reason');
                                                    });
                                                    $('#payment_period_description').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #payment_period_description');
                                                    });
                                                }
                                            }
                                        });
                                    });

                                    $('#cb_add_downloadlist_details_form').on('keyup keypress', function(e) {
                                        var keyCode = e.keyCode || e.which;
                                        if (keyCode === 13) {
                                            e.preventDefault();
                                            return false;
                                        }
                                    });

                                    $('#cboxOverlay').off('click').on('click', function(event) {
                                        //console.log('clickedousideofcolorbox');
                                        var identifychanges = $('#cb_add_downloadlist_details_form').hasClass('dirty');
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
                                                            $("#cb_add_downloadlist.ajax").colorbox.close();
                                                            $('#cb_add_downloadlist_details_form').trigger('reinitialize.areYouSure');
                                                        }
                                                    },
                                                    cancel: {
                                                        text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                        action: function() {
                                                            //$.alert('');
                                                            return false;
                                                        }
                                                    },
                                                }
                                            });
                                        } else {
                                            $("#cb_add_downloadlist.ajax").colorbox.close();
                                            $('#cb_add_downloadlist_details_form').trigger('reinitialize.areYouSure');
                                        }
                                    });
                                },
                                onCleanup: function() {
                                    app.data.selectize_employee_list_formated_json = '';
                                    app.data.selectize_selected_employee_formated_json = '';

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
                                        window.downloadlisttable.ajax.reload(null, false);
                                        app.util.fullscreenloading_end();
                                        window.downloadlisttable.fixedHeader.adjust();
                                    }
                                    NProgress.configure({ parent: '#progress-bar-parent', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                    clearInterval(refreshIntervalId); //stop the timer called refreshIntervalId
                                    clearInterval(close_colorbox_refreshIntervalId);
                                    //app.util.fullscreenloading_end();
                                    sessionStorage.removeItem('Br24_' + app.env() + '_add_downloadlistinfo_previous_data');
                                    sessionStorage.removeItem('Br24_' + app.env() + '_add_downloadlistinfo_submitted_data');
                                },
                            });

                            $("#cb_edit_downloadlist.ajax").colorbox({
                                rel: 'nofollow',
                                width: "600px",
                                height: "400px",
                                left: '60%',
                                top: '200px',
                                escKey: true, //escape key will not close
                                overlayClose: false, //clicking background will not close
                                closeButton: false, //hide the close button
                                onOpen: function() {
                                    //console.log('onOpen: colorbox is about to open');
                                    currentColorBox = 'cb_edit_downloadlist';
                                    //app.util.fullscreenloading_start();
                                },
                                onLoad: function() {
                                    //console.log('onLoad: colorbox has started to load the targeted content');
                                    //
                                },
                                onComplete: function() {
                                    //app.util.fullscreenloading_end();
                                    //timer();
                                    //console.log('timer_started');
                                    //console.log('onComplete: colorbox has displayed the loaded content');


                                    $("#advance_amount").focus(function() {
                                        $(this).parseNumber({ format: number_format_per_locale, locale: numberformat_locale });
                                        $(this).select();
                                    });
                                    $("#advance_amount").blur(function() {
                                        $(this).formatNumber({ format: number_format_per_locale, locale: numberformat_locale });
                                        //
                                    });
                                    $("#advance_amount").formatNumber({ format: number_format_per_locale, locale: numberformat_locale });

                                    var counting_ssd = 1;
                                    var $calendar_date = $('.calendar_date');
                                    $calendar_date.datepicker({
                                        dateFormat: prefered_dateFormat,
                                        showMonthAfterYear: true,
                                        numberOfMonths: 1,
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange: "-10:+1",
                                        showOtherMonths: true,
                                        selectOtherMonths: true,
                                        toggleActive: true,
                                        minDate: "-10y",
                                        maxDate: "+1y",
                                        autoclose: true,
                                        onClose: function() {
                                            var addressinput = $(this).val();
                                            /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                                            var res = patt.test(addressinput);

                                            if (res == true) {
                                                $('#calendar_date').closest('td').removeClass('has-error');
                                                $('#calendar_date').nextAll('.help-block').css('display', 'none');

                                                $(this).blur();
                                                counting_ssd = 1;
                                            } else {
                                                $("#calendar_date").closest("td").addClass("has-error");
                                                if (counting_ssd == 1) {
                                                    $("#calendar_date").after('<span class="help-block"><strong>' + eval("app.translations." + app.data.locale + ".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations." + app.data.locale + ".or_choose_from_the_calendar") + '</strong></span>');
                                                } else {
                                                    // it exists
                                                }
                                                counting_ssd++;
                                                $(this).blur();
                                            }
                                        },
                                        beforeShow: function(input, obj) {
                                            $calendar_date.after($calendar_date.datepicker('widget'));

                                            setTimeout(function() {
                                                $('#ui-datepicker-div').css('top', '').css('left', '');
                                            }, 0);
                                        }
                                    });

                                    //$calendar_date.datepicker().datepicker("setDate", new Date());


                                    var select_list_employee_list = app.data.selectize_employee_list_formated_json;
                                    /**console.log('select_list_employee_list=' + JSON.stringify(select_list_employee_list));*/
                                    var select_selected_employee_json = app.data.selectize_selected_employee_formated_json;
                                    //console.log('select_selected_employee_json=' + JSON.stringify(select_selected_employee_json));

                                    select_selected_list_employee_to_receive_messages = select_selected_employee_json.map(function(item) {
                                        return item['user_id'];
                                    });
                                    /**console.log('AFTERselect_selected_list_employee_to_receive_messages=' + select_selected_list_employee_to_receive_messages);*/

                                    var $recipients_select = $('#recipients').selectize({
                                        plugins: ['remove_button', 'optgroup_columns'],
                                        persist: false,
                                        maxItems: 200,
                                        mode: 'multi',
                                        /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                                        placeholder: '-- ' + eval("app.translations." + app.data.locale + ".select_employee") + ' --',
                                        valueField: 'user_id',
                                        labelField: 'user_id',
                                        searchField: ['user_id', 'fullname', 'fullname_noaccents'],
                                        options: select_list_employee_list,
                                        /** list of all the viable employees on init */
                                        items: select_selected_list_employee_to_receive_messages,
                                        /** list of already selected employees on init */
                                        hideSelected: true,
                                        openOnFocus: true,
                                        closeAfterSelect: true,
                                        render: {
                                            item: function(item, escape) {
                                                return '<div>' +
                                                    (item.user_id ? '<span class="user_id"><b>' + item.user_id + '</b></span>' : '') +
                                                    '<span style="color: #ccc">&nbsp;</span>' +
                                                    (item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                    '<br>' +
                                                    (item.position ? '<span class="position" style="font-size: 9px;">' + item.position + '</span>' : '') +
                                                    //'<br>' +
                                                    //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                                                    '</div>';
                                            },
                                            option: function(item, escape) {
                                                var label = item.fullname || item.email;
                                                var caption = item.fullname ? item.email : null;
                                                return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                                                    '<span class="label label-primary">' + item.user_id + '</span>' +
                                                    '<span style="color: #ccc">&nbsp;</span>' +
                                                    (item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                    '<br>' +
                                                    (item.position ? '<span class="position" style="font-size: 9px; color: #ccc;">' + item.position + '</span>' : '') +
                                                    (item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                                                    //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                                                    '</div>';
                                            }
                                        },
                                        onChange: function(value) {
                                            /** how to get the details populating the fields automatically if an employee is selected from the selectize plugin? provided the information is already on the db at the time */
                                            /** name | dateofbirth | tax code number | ID card number or Passport number */
                                            if (value === undefined || value.length == 0) {
                                                /**array empty or does not exist*/
                                            } else {

                                            }
                                            $('#cb_edit_downloadlist_details_form').addClass('dirty');
                                        }
                                    });

                                    var recipients_select_selectize = $recipients_select[0].selectize;
                                    var recipients_select_old_options = recipients_select_selectize.settings;
                                    var selectize_focus_handler = function(value, $item) {
                                        var width_to_be = $('.selectize-control').outerWidth();
                                        $('.selectize-dropdown-content').css('width', width_to_be);
                                    };
                                    recipients_select_selectize.on('focus', selectize_focus_handler);


                                    /** filter the shifts from the swapable shifts select */
                                    var item_remove_handler = function(value, $item) {
                                        $('#cb_edit_downloadlist_details_form').addClass('dirty');
                                    };
                                    recipients_select_selectize.on('item_remove', item_remove_handler);

                                    $(window).resize(function() {
                                        var width_to_be = $('.selectize-control').outerWidth();
                                        $('.selectize-dropdown-content').css('width', width_to_be);
                                    });






                                    var calendar_date = $('#calendar_date').val();
                                    var recipients = $('#recipients').val();
                                    var advance_amount = $('#advance_amount').val();
                                    var reason = $('#reason').val();
                                    var payment_period_description = $('#payment_period_description').val();

                                    var currentdate = new Date();
                                    var datetime = "Last Sync: " + currentdate.getDate() +
                                        "/" + (currentdate.getMonth() + 1) +
                                        "/" + currentdate.getFullYear() +
                                        " @ " + currentdate.getHours() +
                                        ":" + currentdate.getMinutes() +
                                        ":" + currentdate.getSeconds();

                                    var edit_downloadlist_previous_data = [];
                                    edit_downloadlist_previous_data['edit_downloadlist_previous_data'] = {
                                        "whenwasset": datetime,

                                        "edit_downloadlist_calendar_date": calendar_date,
                                        "edit_downloadlist_recipients": recipients,
                                        "edit_downloadlist_advance_amount": advance_amount,
                                        "edit_downloadlist_reason": reason,
                                        "edit_downloadlist_payment_period_description": payment_period_description
                                    };
                                    sessionStorage.setItem('Br24_' + app.env() + '_edit_downloadlistinfo_previous_data', JSON.stringify(edit_downloadlist_previous_data['edit_downloadlist_previous_data']));


                                    $('#cb_edit_downloadlist_details_form').areYouSure();
                                    $('#cb_edit_downloadlist_details_form').on('change', 'select', function() {
                                        $("#edit_downloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#edit_downloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of select tags

                                    $('#cb_edit_downloadlist_details_form').on('change keypress', 'input', function() {
                                        $("#edit_downloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#edit_downloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of input: the change event take care of input of type "hidden" also

                                    $('#cb_edit_downloadlist_details_form').on('change keypress', 'textarea', function() {
                                        $("#edit_downloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#edit_downloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of textareas                                    


                                    $("#edit_downloadlist_reset").click(function() {
                                        var button_selector = $('#edit_downloadlist_reset, #edit_downloadlist_update');
                                        //hide all the buttons
                                        button_selector.prop('disabled', true).css('display', 'none');

                                        var previous_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_edit_downloadlistinfo_previous_data'));

                                        /**console.log(previous_load['edit_downloadlist_advance_amount']);*/
                                        $('#calendar_date').val(previous_load['edit_downloadlist_calendar_date']);
                                        $('#advance_amount').val(previous_load['edit_downloadlist_advance_amount']);
                                        //$('#advance_amount').formatNumber({ format: number_format_per_locale, locale: numberformat_locale });
                                        $('#reason').val(previous_load['edit_downloadlist_reason']);
                                        $('#payment_period_description').val(previous_load['edit_downloadlist_payment_period_description']);

                                        recipients_select_selectize.setValue(previous_load['edit_downloadlist_recipients'], false);

                                        $('.has-error').removeClass('has-error');
                                        $('.help-block').css('display', 'none');
                                        $('#cb_edit_downloadlist_details_form').trigger('reinitialize.areYouSure');
                                    });

                                    $("#edit_downloadlist_update").click(function(e) {
                                        e.preventDefault();
                                        $('.alert_warning').css('display', 'none');
                                        $('.alert_success').css('display', 'none');

                                        var calendar_date = $('#calendar_date').val();
                                        var recipients = $('#recipients').val();
                                        var advance_amount = $('#advance_amount').val();
                                        var reason = $('#reason').val();
                                        var payment_period_description = $('#payment_period_description').val();

                                        var currentdate = new Date();
                                        var datetime = "Last Sync: " + currentdate.getDate() +
                                            "/" + (currentdate.getMonth() + 1) +
                                            "/" + currentdate.getFullYear() +
                                            " @ " + currentdate.getHours() +
                                            ":" + currentdate.getMinutes() +
                                            ":" + currentdate.getSeconds();

                                        var edit_custom_rc_message_schedule_submitted_data = [];
                                        edit_custom_rc_message_schedule_submitted_data['edit_custom_rc_message_schedule_submitted_data'] = {
                                            "whenwasset": datetime,

                                            "edit_downloadlist_calendar_date": calendar_date,
                                            "edit_downloadlist_recipients": recipients,
                                            "edit_downloadlist_advance_amount": advance_amount,
                                            "edit_downloadlist_reason": reason,
                                            "edit_downloadlist_payment_period_description": payment_period_description

                                        };
                                        sessionStorage.setItem('Br24_' + app.env() + '_edit_downloadlistinfo_submitted_data', JSON.stringify(edit_custom_rc_message_schedule_submitted_data['edit_custom_rc_message_schedule_submitted_data']));

                                        /** convert the number fields to unformatted number momentarily*/
                                        $("#advance_amount").parseNumber({ format: number_format_per_locale, locale: numberformat_locale });

                                        var formData = new FormData($('#cb_edit_downloadlist_details_form')[0]);
                                        formData.append("recipients", recipients);

                                        NProgress.configure({ parent: '#cboxTitle', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                        NProgress.start();

                                        $('.cb_loader').css('display', 'block').css('cursor', 'wait');
                                        $('#cb_top').addClass('nprogress-busy').css('pointer-events', 'none');
                                        //app.util.fullscreenloading_start();

                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $("#edit_downloadlist_details_table").data("token") } });
                                        app.ajax.formdata(app.data.manage_edit_downloadlist, formData, null, function() {
                                            /**console.log(app.ajax.resultformdata);*/
                                            success_ajax_then_refresh = app.ajax.resultformdata.success;

                                            NProgress.done();
                                            $('.cb_loader').css('display', 'none').css('cursor', 'auto');
                                            $('#cb_top').removeClass('nprogress-busy').css('pointer-events', 'auto');

                                            if (app.ajax.resultformdata.success == true) {
                                                $('#cboxLoadedContent').css('background-color', '#4CAF50');
                                                $('.onSuccess_makeGreen').css('background-color', '#4CAF50');
                                                $('.ibox-tool-userid').css('color', 'white');
                                                $('#cb_edit_downloadlist_details_form').css('display', 'none');
                                                close_colorbox_timer();
                                                $(document.body).css('pointer-events', 'none');
                                                app.util.fullscreenloading_start();
                                                if (app.ajax.resultformdata.process_penalties_accept_reject_table_sync == true) {
                                                    app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                }
                                            } else {
                                                $("#advance_amount").formatNumber({ format: number_format_per_locale, locale: numberformat_locale });

                                                $('.has-error').removeClass('has-error');
                                                $('.help-block').detach();

                                                $.each(app.ajax.resultformdata.errors, function(idx, val) {
                                                    app_ops.manage_downloadlist.profile.foreach_handle_error_display(idx, val);
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
                                                    $('#calendar_date').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #calendar_date');
                                                    });
                                                    $('#recipients').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #recipients');
                                                    });
                                                    $('#advance_amount').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).nextAll('.help-block').css('display', 'none');
                                                        console.log('something changed in #advance_amount');
                                                    });
                                                    $('#reason').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #reason');
                                                    });
                                                    $('#payment_period_description').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #payment_period_description');
                                                    });
                                                }
                                            }
                                        });
                                    });

                                    $('#cb_edit_downloadlist_details_form').on('keyup keypress', function(e) {
                                        var keyCode = e.keyCode || e.which;
                                        if (keyCode === 13) {
                                            e.preventDefault();
                                            return false;
                                        }
                                    });

                                    $('#cboxOverlay').off('click').on('click', function(event) {
                                        //console.log('clickedousideofcolorbox');
                                        var identifychanges = $('#cb_edit_downloadlist_details_form').hasClass('dirty');
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
                                                            $("#cb_edit_downloadlist.ajax").colorbox.close();
                                                            $('#cb_edit_downloadlist_details_form').trigger('reinitialize.areYouSure');
                                                        }
                                                    },
                                                    cancel: {
                                                        text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                        action: function() {
                                                            //$.alert('');
                                                            return false;
                                                        }
                                                    },
                                                }
                                            });
                                        } else {
                                            $("#cb_edit_downloadlist.ajax").colorbox.close();
                                            $('#cb_edit_downloadlist_details_form').trigger('reinitialize.areYouSure');
                                        }
                                    });
                                },
                                onCleanup: function() {
                                    // app.data.selectize_employee_list_formated_json = '';
                                    // app.data.selectize_selected_employee_formated_json = '';
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
                                        var currentdate = new Date();
                                        var datetime = "Last Sync: " + currentdate.getDate() +
                                            "/" + (currentdate.getMonth() + 1) +
                                            "/" + currentdate.getFullYear() +
                                            " @ " + currentdate.getHours() +
                                            ":" + currentdate.getMinutes() +
                                            ":" + currentdate.getSeconds();
                                        var scroll = $(document).scrollTop();
                                        var edit_downloadlist_scroll_position_data = [];
                                        edit_downloadlist_scroll_position_data['edit_downloadlist_scroll_position_data'] = {
                                            "whenwasset": datetime,
                                            "scroll": scroll
                                        };
                                        sessionStorage.setItem('Br24_' + app.env() + '_edit_downloadlist_scroll_position_data', JSON.stringify(edit_downloadlist_scroll_position_data['edit_downloadlist_scroll_position_data']));
                                        window.downloadlisttable.ajax.reload(null, false);
                                        app.util.fullscreenloading_end();
                                        window.downloadlisttable.fixedHeader.adjust();
                                    }

                                    NProgress.configure({ parent: '#progress-bar-parent', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                    clearInterval(refreshIntervalId); //stop the timer called refreshIntervalId
                                    clearInterval(close_colorbox_refreshIntervalId);
                                    //app.util.fullscreenloading_end();
                                    sessionStorage.removeItem('Br24_' + app.env() + '_edit_downloadlistinfo_previous_data');
                                    sessionStorage.removeItem('Br24_' + app.env() + '_edit_downloadlistinfo_submitted_data');
                                },
                            });

                            $("a[name*='delete_downloadlist_']").on('click', function(event) {
                                event.preventDefault();
                                var clicked_href = $(this).attr('href');
                                var clicked_href_id_encrypted = $(this).parent().parent().parent().data('workingshiftid');
                                var isReplaceable = $(this).is('[replaceable=true]');
                                var isDeleteable = $(this).is('[deleteable=true]');
                                var isEnableable = $(this).is('[enableable=true]');
                                if (isReplaceable == true) {
                                    $.confirm({
                                        title: eval("app.translations." + app.data.locale + ".title_text"),
                                        content: 'url:' + app.data.URL_replace_shift_with_another + '/' + clicked_href_id_encrypted,
                                        type: 'red',
                                        draggable: false,
                                        backgroundDismiss: 'cancel',
                                        escapeKey: true,
                                        animateFromElement: false,
                                        onAction: function(btnName) {
                                            $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                                        },
                                        onContentReady: function() {
                                            // bind to events

                                            // var jc = this;
                                            // this.$content.find('form').on('submit', function(e) {
                                            //     // if the user submits the form by pressing enter in the field.
                                            //     e.preventDefault();
                                            //     jc.$$formSubmit.trigger('click'); // reference the button and click it
                                            // });

                                            var select_list_working_shift_list = app.data.selectize_working_shifts_formated_json;
                                            /**console.log('select_list_employee_list=' + JSON.stringify(select_list_employee_list));*/

                                            var $select = $('#shifts').selectize({
                                                plugins: ['remove_button', 'optgroup_columns'],
                                                persist: false,
                                                maxItems: 1,
                                                mode: 'multi',
                                                /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                                                placeholder: '-- Select default working shift --',
                                                valueField: 'id',
                                                labelField: 'name',
                                                searchField: ['name'],
                                                options: select_list_working_shift_list,
                                                /** list of all the viable employees on init */
                                                /**items: select_selected_list_employee_default_shifts,*/
                                                /** list of already selected employees on init*/
                                                hideSelected: true,
                                                openOnFocus: true,
                                                closeAfterSelect: true,
                                                render: {
                                                    item: function(item, escape) {
                                                        return '<div>' +
                                                            (item.name ? '<span class="name"><b>' + item.name + '</b></span>' : '') +
                                                            //'<span style="color: #ccc">&nbsp;</span>' +
                                                            //(item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                            //'<br>' +
                                                            //(item.position ? '<span class="position" style="font-size: 9px;">' + item.position + '</span>' : '') +
                                                            //'<br>' +
                                                            //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                                                            '</div>';
                                                    },
                                                    option: function(item, escape) {
                                                        //var label = item.name || item.email;
                                                        //var caption = item.fullname ? item.email : null;
                                                        return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                                                            '<span class="label label-primary">' + item.name + '</span>' +
                                                            //'<span style="color: #ccc">&nbsp;</span>' +
                                                            //(item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                            //'<br>' +
                                                            //(item.position ? '<span class="position" style="font-size: 9px; color: #ccc;">' + item.position + '</span>' : '') +
                                                            //(item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                                                            //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                                                            '</div>';
                                                    }
                                                },
                                                onChange: function(value) {
                                                    /** when change want to remove this option from the other select options */
                                                    //currently_selected_default_start_shift = value;
                                                    // swap_shift_selectize.removeOption(value);
                                                    // swap_shift_selectize.refreshOptions();
                                                },
                                                onItemAdd: function(value) {
                                                    $('#confirmbox_spacer').css('height', '0px');
                                                },
                                                onItemRemove: function(value) {
                                                    $('#confirmbox_spacer').css('height', '200px');
                                                },
                                                onBlur: function() {
                                                    $('#confirmbox_spacer').css('height', '0px');
                                                },
                                                onFocus: function() {
                                                    $('#confirmbox_spacer').css('height', '200px');
                                                }

                                            });
                                            var selectize = $select[0].selectize;
                                            var old_options = selectize.settings;

                                            selectize.focus();
                                        },
                                        buttons: {
                                            disable_replace: {
                                                text: 'Replace & Disable',
                                                btnClass: 'btn-blue',
                                                keys: ['shift'],
                                                action: function() {
                                                    var input = this.$content.find('select#shifts').val();
                                                    /**console.log(input);*/
                                                    if (input === undefined || input.length == 0) {
                                                        $.alert({
                                                            content: "Please select a working shift from the drop down.",
                                                            animateFromElement: false,
                                                            type: 'red'
                                                        });
                                                        return false;
                                                    } else {
                                                        /** have the ability to set the working shift disabled so that it is not visible to use */
                                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                                        app.util.nprogressinit();
                                                        var ajaxdata = {
                                                            'replace_with_working_shift': input
                                                        };

                                                        /** you will show a drop down select to choose which other shift to replace with and when that is done will be disabled */
                                                        app.ajax.jsonGET(clicked_href, ajaxdata, null, function() {
                                                            //console.log(app.ajax.result);
                                                            if (app.ajax.result.success == true) {
                                                                window.downloadlisttable.ajax.reload(null, false);
                                                                app.util.fullscreenloading_end();
                                                                window.downloadlisttable.fixedHeader.adjust();
                                                                app.util.nprogressdone();
                                                                if (app.ajax.result.process_penalties_accept_reject_table_sync == true) {
                                                                    app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                                }
                                                            } else {
                                                                app.util.nprogressdone();
                                                            }
                                                        });

                                                        /** so if the working shift being disabled is being used by a default shift schedule then advise if it should be swaped out with another shift */
                                                        /** then add the disabled flag to the working shift table so that it is no longer used in some views or the select boxes. */
                                                    }
                                                },
                                            },
                                            cancel: {
                                                text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                action: function() {
                                                    /**$.alert(eval("app.translations." + app.data.locale + ".working_shift_deletion_was_aborted"));*/
                                                }
                                            }
                                        },
                                    });
                                } else if (isDeleteable == true) {
                                    $.confirm({
                                        title: eval("app.translations." + app.data.locale + ".title_text"),
                                        content: eval("app.translations." + app.data.locale + ".are_you_sure_you_want_to_delete_this_schedule") + "\n",
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
                                                            window.downloadlisttable.ajax.reload(null, false);
                                                            app.util.fullscreenloading_end();
                                                            window.downloadlisttable.fixedHeader.adjust();
                                                            app.util.nprogressdone();
                                                            if (app.ajax.result.process_penalties_accept_reject_table_sync == true) {
                                                                app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                            }
                                                        } else {
                                                            app.util.nprogressdone();
                                                        }
                                                    });
                                                }
                                            },
                                            cancel: {
                                                text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                action: function() {
                                                    /**$.alert(eval("app.translations." + app.data.locale + ".working_shift_deletion_was_aborted"));*/
                                                }
                                            },
                                        }
                                    });
                                } else if (isEnableable == true) {
                                    $.confirm({
                                        title: eval("app.translations." + app.data.locale + ".title_text"),
                                        content: eval("app.translations." + app.data.locale + ".are_you_sure_you_want_to_enable_this_schedule") + "\n",
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
                                                            window.downloadlisttable.ajax.reload(null, false);
                                                            app.util.fullscreenloading_end();
                                                            window.downloadlisttable.fixedHeader.adjust();
                                                            app.util.nprogressdone();
                                                            if (app.ajax.result.process_penalties_accept_reject_table_sync == true) {
                                                                app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                            }
                                                        } else {
                                                            app.util.nprogressdone();
                                                        }
                                                    });
                                                }
                                            },
                                            cancel: {
                                                text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                action: function() {
                                                    /**$.alert(eval("app.translations." + app.data.locale + ".working_shift_deletion_was_aborted"));*/
                                                }
                                            },
                                        }
                                    });
                                } else {
                                    $.confirm({
                                        title: eval("app.translations." + app.data.locale + ".title_text"),
                                        content: eval("app.translations." + app.data.locale + ".are_you_sure_you_want_to_disable_this_schedule") + "\n",
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
                                                            window.downloadlisttable.ajax.reload(null, false);
                                                            app.util.fullscreenloading_end();
                                                            window.downloadlisttable.fixedHeader.adjust();
                                                            app.util.nprogressdone();
                                                            if (app.ajax.result.process_penalties_accept_reject_table_sync == true) {
                                                                app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                            }
                                                        } else {
                                                            app.util.nprogressdone();
                                                        }
                                                    });
                                                }
                                            },
                                            cancel: {
                                                text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                action: function() {
                                                    /**$.alert(eval("app.translations." + app.data.locale + ".working_shift_deletion_was_aborted"));*/
                                                }
                                            },
                                        }
                                    });
                                }
                            });





                            $("i[name*='edit_custom_star_rating_comment_']").on('click', function(event) {
                                event.preventDefault();
                                /**var original_html = $(this).parent().parent().html();*/
                                /**console.log(original_html);*/
                                var clicked_href_original_star_rating_comment = $(this).parent().attr('data-content');
                                /**console.log($(this).parent());*/
                                /**console.log(clicked_href_original_star_rating_comment);*/
                                var part_to_hide_unhide = $(this).parent();
                                /**console.log(part_to_hide_unhide);*/
                                var edit_custom_star_rating_comment_icon = $(this);
                                /**console.log(edit_custom_star_rating_comment_icon);*/
                                /** when we click on this thing we want to show a text input same like the internal notes etc */
                                /** because we remove the element from the DOM we have to renabled the on click function for it */
                                var change_star_rating_custom_note_case_Id = $(this).parent().parent().data('case_id');

                                /** it seems to be sending many trips to the db how to prevent that? */
                                if ($('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id + '').is(":visible") == true) {
                                    return false;
                                }

                                keep_track_of_last_clicked_item_to_react_after_reload = 'change_from_amount';
                                var target = event.target;
                                event.preventDefault();

                                var tr = $(this).parent().parent().closest('tr');
                                var td = $(this).parent().parent();
                                /**console.log(td);*/

                                /** count how many new lines there are */


                                var change_internal_note_original_html = $(this).html();
                                /**console.log(change_internal_note_original_html);*/

                                var row = window.downloadlisttable.row(tr);
                                var thecellvalue = $(this).parent().attr('data-content');
                                /**console.log(thecellvalue);*/

                                var count_lines_in_comment = thecellvalue.split('<br>').length - 1;
                                /**console.log('count_lines_in_comment =' + count_lines_in_comment);*/
                                var td_height = td.height();
                                /**console.log(td_height);*/
                                td_height = td_height + (count_lines_in_comment * 20);


                                thecellvalue = thecellvalue.replace(/(?:<br>)/g, '\r\n');
                                var rowIndex = tr.data('rowindex');

                                var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                                keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_star_rating_custom_note_case_Id;
                                /**console.log(change_star_rating_custom_note_case_Id);*/
                                var timestamp_Id = $(this).data('date_ts');
                                // var idx = table.cell(this).index().column;
                                // var date_number = table.column(idx).header();
                                // var clcikedcolumnheader_value = $(date_number).html();
                                var input = $('<textarea id="edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id + '" cols="10" rows="5" charswidth="23"  style="white-space: pre-wrap; line-height: 15px; min-height: 30px; width: 100%; height: ' + td_height + 'px; display: block; z-index: 12; resize: vertical; color: black;"></textarea>');
                                input.val(thecellvalue);
                                part_to_hide_unhide.css('display', 'none');
                                td.append(input);


                                $(".popover").popover('hide');

                                $(document).on('keydown', '#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id, function(e) {
                                    var input = $(this);
                                    var oldVal = input.val();
                                    var regex = new RegExp(input.attr('pattern'), 'g');

                                    setTimeout(function() {
                                        var newVal = input.val();
                                        if (!regex.test(newVal)) {
                                            input.val(oldVal);
                                        }
                                    }, 0);
                                    /** if enter key is pressed allow it */
                                    if (e.keyCode == 13) {
                                        // input.blur();

                                    }
                                    /** if esc key is pressed return the thing back to the original ?? */
                                    if (e.keyCode == 27) {
                                        td.html(change_internal_note_original_html);
                                    }
                                });


                                $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).click(function(event) {
                                    event.stopImmediatePropagation();
                                    /***/
                                });
                                $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).focus(function() {
                                    // var save_this = $(this);
                                    // window.setTimeout(function() {
                                    //     save_this.select();
                                    // }, 30);
                                });
                                $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).focus();

                                $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).blur(function() {
                                    var edited_number = $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).val();
                                    edited_number = edited_number.replace(/(?:\r\n|\r|\n)/g, '<br>');
                                    /**console.log(edited_number);*/
                                    if (clicked_href_original_star_rating_comment == edited_number) {
                                        /** console.log('change_from_amount td.html(change_internal_note_original_html)'); */
                                        /**console.log('they were the same');*/
                                        part_to_hide_unhide.css('display', '');
                                        $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).remove();
                                        /** keep_track_of_last_clicked_item_to_react_after_reload = null; */
                                        /** keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = null; */
                                    } else {
                                        /** its different */
                                        /**console.log('its different');*/

                                        /**app.util.nprogressinit();*/
                                        /**app.util.fullscreenloading_start();*/
                                        part_to_hide_unhide.css('display', '');
                                        /** but we replace the contents with the new values */
                                        /** we put back the star rating with the new text */
                                        part_to_hide_unhide.attr('data-content', edited_number);

                                        $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).remove();
                                        // var change_from_amount_replace_html = $("<span class='label label-primary currency_number_format'>" + edited_number + "</span>");
                                        // console.log(change_from_amount_replace_html);
                                        /**console.log('change_from_amount td.html(edited_number)');*/

                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                        var data = {
                                            'case_id': change_star_rating_custom_note_case_Id,
                                            'encrypted_case_id': encrypted_case_id_uploading_to,
                                            'new_star_rating_custom_note': edited_number
                                        };

                                        /** use ajax to send data to php */
                                        app.ajax.json(app.data.change_custom_star_rating_note_for_job, data, null, function() {
                                            /**console.log(app.ajax.result);*/
                                            success_ajax_then_refresh = app.ajax.result.success;
                                            if (app.ajax.result.success == true) {

                                                //td.html(original_html);
                                                part_to_hide_unhide.css('display', '');
                                                /**console.log(part_to_hide_unhide);*/
                                                part_to_hide_unhide.attr('data-content', edited_number);

                                                if (app.ajax.result.star_rating_comment == null) {
                                                    edit_custom_star_rating_comment_icon.css('display', 'none');
                                                    part_to_hide_unhide.attr('data-content', '');
                                                }
                                                // console.log(tr.children().next('.last_updated'));
                                                // console.log(app.ajax.result.updated_at);
                                                tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                                tr.children().next('.last_updated').html(app.ajax.result.updated_at);


                                            } else {
                                                /**td.html(original_html);*/
                                                $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).remove();
                                                part_to_hide_unhide.css('display', '');
                                                part_to_hide_unhide.attr('data-content', clicked_href_original_star_rating_comment);
                                            }
                                        });
                                    }

                                    var refreshTimeout = null;
                                    $('[data-toggle="popover"]').popover({
                                        placement: 'auto bottom',
                                        trigger: "manual",
                                        html: true,
                                        animation: false
                                    }).on("mouseenter", function() {
                                        var _this = this;
                                        var popover_mouseover_function = function(this_elem) {
                                            refreshTimeout = setInterval(function() {
                                                $(this_elem).popover("show");
                                            }, 300);
                                        };
                                        popover_mouseover_function(_this);
                                        $(this).siblings(".popover").on("mouseleave", function() {
                                            $(_this).popover('hide');
                                        });
                                    }).on("mouseleave", function() {
                                        clearInterval(refreshTimeout);
                                        var _this = this;
                                        var popover_mouseleave_function = function() {
                                            setTimeout(function() {
                                                if (!$(".popover:hover").length) {
                                                    $(_this).popover("hide")
                                                } else {
                                                    popover_mouseleave_function();
                                                }
                                            }, 50);
                                        };
                                        popover_mouseleave_function();
                                    });
                                });
                            });
                        });

                        $("div[name*='edit_custom_star_rating_']").rateit({ max: 5, step: .5 });

                        $('.rateit').on('beforerated', function(e, value) {
                            e.preventDefault();

                            var name_attr = $(this).attr('name');
                            var edit_icon_selector = $(this).next('.fa');
                            /**console.log(edit_icon_selector);*/
                            var thehuh = $(this).parent();
                            /**console.log(thehuh);*/

                            var td = $(this).parent().parent();
                            var tr = $(this).parent().parent().closest('tr');
                            /**console.log(td);*/

                            var change_status_case_Id = $(this).parent().parent().data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');

                            // console.log(change_status_case_Id);
                            // console.log(encrypted_case_id_uploading_to);

                            /** prompt if should include a comment */
                            $.confirm({
                                title: 'Setting ' + value + ' Stars',
                                content: '' +
                                    '<form action="" class="formName">' +
                                    '<div class="form-group">' +
                                    '<label>Enter something here</label>' +
                                    '<textarea class="star_rating_comment form-control" required cols="10" rows="5" charswidth="23"  style="white-space: pre-wrap; line-height: 15px; min-height: 30px; max-height: 600px; width: 100%; height: 200px; display: block; z-index: 12; resize: vertical;"/>' +
                                    '</textarea>' +
                                    '</div>' +
                                    '</form>',
                                buttons: {
                                    formSubmit: {
                                        text: 'Submit',
                                        btnClass: 'btn-blue',
                                        action: function() {
                                            var star_rating_comment = this.$content.find('.star_rating_comment').val();

                                            /**console.log(star_rating_comment);*/
                                            /** here we take the contents of the form and send it as the star comments and dont forget the value of stars */
                                            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                            var data = {
                                                'case_id': change_status_case_Id,
                                                'encrypted_case_id': encrypted_case_id_uploading_to,
                                                'new_star_rating_comment': star_rating_comment,
                                                'new_star_rating': value
                                            };

                                            app.ajax.json(app.data.change_star_rating_for_job, data, null, function() {
                                                /**console.log(app.ajax.result);*/
                                                success_ajax_then_refresh = app.ajax.result.success;
                                                if (app.ajax.result.success == true) {
                                                    if (app.ajax.result.star_rating_comment == '' || app.ajax.result.star_rating_comment == null) {
                                                        edit_icon_selector.css('display', 'none');
                                                        $(this).parent().attr('data-content', '----------------------');
                                                        /**thehuh.popover('destroy');*/
                                                    } else {
                                                        /** when you put it in place then you need to make the trigger */
                                                        edit_icon_selector.css('display', '');
                                                        thehuh.attr('data-content', app.ajax.result.star_rating_comment);
                                                    }
                                                    /** if the comment is blank then remove the icon for this job id also */
                                                    /** if the comment is not blank need to show the icon and give it the trigger */
                                                    $("div[name='" + name_attr + "']").rateit('value', app.ajax.result.star_rating);

                                                    tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                                    tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                                                } else {
                                                    /** we don't change anything put back to what it was and alert  */
                                                }
                                            });

                                            var refreshTimeout = null;
                                            $('[data-toggle="popover"]').popover({
                                                placement: 'auto bottom',
                                                trigger: "manual",
                                                html: true,
                                                animation: false
                                            }).on("mouseenter", function() {
                                                var _this = this;
                                                var popover_mouseover_function = function(this_elem) {
                                                    refreshTimeout = setInterval(function() {
                                                        $(this_elem).popover("show");
                                                    }, 300);
                                                };
                                                popover_mouseover_function(_this);
                                                $(this).siblings(".popover").on("mouseleave", function() {
                                                    $(_this).popover('hide');
                                                });
                                            }).on("mouseleave", function() {
                                                clearInterval(refreshTimeout);
                                                var _this = this;
                                                var popover_mouseleave_function = function() {
                                                    setTimeout(function() {
                                                        if (!$(".popover:hover").length) {
                                                            $(_this).popover("hide")
                                                        } else {
                                                            popover_mouseleave_function();
                                                        }
                                                    }, 50);
                                                };
                                                popover_mouseleave_function();
                                            });
                                        }
                                    },
                                    cancel: function() {
                                        //close
                                    },
                                },
                                onContentReady: function() {
                                    // bind to events
                                    var jc = this;
                                    this.$content.find('form').on('submit', function(e) {
                                        // if the user submits the form by pressing enter in the field.
                                        e.preventDefault();
                                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                                    });
                                }
                            });
                        });


                        $('.rateit').on('beforereset', function(e) {
                            e.preventDefault();

                            var name_attr = $(this).attr('name');
                            var edit_icon_selector = $(this).next('.fa');
                            /**console.log(edit_icon_selector);*/
                            var thehuh = $(this).parent();
                            /**console.log(thehuh);*/

                            var td = $(this).parent().parent();
                            var tr = $(this).parent().parent().closest('tr');
                            /**console.log(td);*/

                            var change_status_case_Id = $(this).parent().parent().data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');

                            // console.log(change_status_case_Id);
                            // console.log(encrypted_case_id_uploading_to);

                            $.confirm({
                                title: 'Reset Star Rating?',
                                content: 'This will also clear the comment',
                                type: 'red',
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

                                            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                            var data = {
                                                'case_id': change_status_case_Id,
                                                'encrypted_case_id': encrypted_case_id_uploading_to,
                                                'new_star_rating_comment': null,
                                                'new_star_rating': 0
                                            };

                                            app.ajax.json(app.data.reset_star_rating_for_job, data, null, function() {
                                                /**console.log(app.ajax.result);*/
                                                success_ajax_then_refresh = app.ajax.result.success;
                                                if (app.ajax.result.success == true) {
                                                    /** change the value */
                                                    //app.ajax.result.star_rating_comment

                                                    if (app.ajax.result.star_rating_comment == '' || app.ajax.result.star_rating_comment == null) {
                                                        edit_icon_selector.css('display', 'none');
                                                        thehuh.attr('data-content', '');
                                                        thehuh.popover('destroy');
                                                    } else {
                                                        /** when you put it in place then you need to make the trigger */
                                                        edit_icon_selector.css('display', '');
                                                        /** get the comment and put it in the popover */
                                                        $(this).parent().attr('data-content', app.ajax.result.star_rating_comment);
                                                    }
                                                    /** if the comment is blank then remove the icon for this job id also */
                                                    /** if the comment is not blank need to show the icon and give it the trigger */
                                                    $("div[name='" + name_attr + "']").rateit('value', app.ajax.result.star_rating);

                                                    tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                                    tr.children().next('.last_updated').html(app.ajax.result.updated_at);

                                                } else {
                                                    /** do nothing to change it */
                                                }
                                            });
                                        }
                                    },
                                    cancel: {
                                        text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                        action: function() {
                                            /** cancel out */
                                            /***/
                                        }
                                    },
                                }
                            });
                        });

                        $(".rateit").bind('over', function(event, value) {
                            //$('#hover6').text('Hovering over: ' + value);
                            /**console.log('Hovering over: ' + value);*/
                        });

                        app_ops.manage_downloadlist.profile.handle_show_hide_columns_on_this_table();


                        $(".keyboardshortcutcombinationtolinktosharedfolder").mousedown(function(event) {
                            if (event.ctrlKey == true && event.shiftKey == true && event.altKey == true) {
                                /** if the button is highlighted it affects this fucntionality */
                                if($(event.target).hasClass('highlight_span')){
                                    if ($(event.target).parent()[0].href !== undefined) {
                                        if ($(event.target).parent()[0].href.indexOf("/uploadfiles/") >= 0) {
                                            if ($(event.target).parent()[0].href.indexOf("192.168.1.3") >= 0) {
                                                // console.log('It has already switched url variable down so do nothing');
                                            } else {
                                                var _href = $(event.target).parent()[0].href;
                                                var alt_href = $(event.target).parent().data('alt_href');
                                                /**console.log(_href);*/
                                                /**console.log(alt_href);*/
                                                $(event.target).parent().attr("href", alt_href);
                                                $(event.target).parent().attr("data-backup_href", _href);
                                            }
                                        }
                                    }
                                }else{
                                    //console.log('keyboardshortcutcombinationtolinktosharedfolder link with shift mouse down');
                                    if (event.target.href !== undefined) {
                                        if (event.target.href.indexOf("/uploadfiles/") >= 0) {
                                            if (event.target.href.indexOf("192.168.1.3") >= 0) {
                                                // console.log('It has already switched url variable down so do nothing');
                                            } else {
                                                var _href = event.target.href;
                                                var alt_href = $(this).data('alt_href');
                                                $(this).attr("href", alt_href);
                                                $(this).attr("data-backup_href", _href);
                                            }
                                        }
                                    }
                                }
                            }
                        }).mouseup(function(event) {
                            //console.log('keyboardshortcutcombinationtolinktosharedfolder link with shift mouse up');
                        }).mouseleave(function(event) {
                            /**console.log(event);*/
                            //console.log('keyboardshortcutcombinationtolinktosharedfolder link with shift mouse leave');
                            if($(event.target).hasClass('highlight_span')){
                                if ($(event.target).parent()[0].href !== undefined) {
                                    if ($(event.target).parent()[0].href.indexOf("/uploadfiles/") >= 0) {
                                        if ($(event.target).parent()[0].href.indexOf("192.168.1.3") >= 0) {
                                            var backup_href = $(event.target).parent().data('backup_href');
                                            $(event.target).parent().attr("data-backup_href", "");
                                            $(event.target).parent().attr("href", backup_href);
                                        }
                                    } else {
                                        if ($(event.target).parent()[0].href.indexOf("192.168.1.3") >= 0) {
                                            var backup_href = $(event.target).parent().data('backup_href');
                                            $(event.target).parent().attr("data-backup_href", "");
                                            $(event.target).parent().attr("href", backup_href);
                                        }
                                    }
                                }                                
                            }else{
                                if (event.target.href !== undefined) {
                                    if (event.target.href.indexOf("/uploadfiles/") >= 0) {
                                        if (event.target.href.indexOf("192.168.1.3") >= 0) {
                                            var backup_href = $(this).data('backup_href');
                                            $(this).attr("data-backup_href", "");
                                            $(this).attr("href", backup_href);
                                        }
                                    } else {
                                        if (event.target.href.indexOf("192.168.1.3") >= 0) {
                                            var backup_href = $(this).data('backup_href');
                                            $(this).attr("data-backup_href", "");
                                            $(this).attr("href", backup_href);
                                        }
                                    }
                                }
                            }                            
                        });

                    },
                    initComplete: function(settings, json) {
                        var isSectionAccounting = app.data.section.indexOf("accounting"); //>= 0 if finds //-1 if it does not find
                        var canWRITEaccounting = app.data.auth_user_permissions.indexOf("WRITE accounting"); //>= 0 if finds //-1 if it does not find
                        var mapr_action_column = window.downloadlisttable.column(app.conf.table.filterColumn.managedownloadlistInfo.action);
                        /**console.log(app.data.section);*/
                        /**console.log(isSectionAccounting);*/
                        /**console.log(app.data.auth_user_permissions);*/
                        /**console.log(canWRITEaccounting);*/
                        // if (isSectionAccounting >= 0 && canWRITEaccounting >= 0) {
                        //     mapr_action_column.visible(true);
                        //     $('#downloadlistTable .dataTables_empty').attr('colspan', app.conf.table.filterColumn.managedownloadlistInfo.action + 1);
                        // } else {
                        //     mapr_action_column.visible(false);
                        //     $('#downloadlistTable .dataTables_empty').attr('colspan', app.conf.table.filterColumn.managedownloadlistInfo.action);
                        // }
                    }
                });

                var counting_ctd = 1;
                var $period_datepicker = $('.period_datepicker');
                $period_datepicker.bind('keydown', function(e) {
                    if (e.which == 13) {
                        e.stopImmediatePropagation();
                    }
                    if (e.which == 27) {
                        $(this).blur();
                    }
                }).datepicker({
                    dateFormat: prefered_dateFormat,
                    showMonthAfterYear: true,
                    numberOfMonths: 2,
                    showCurrentAtPos: 1,
                    changeMonth: true,
                    changeYear: true,
                    yearRange: "-2:+2",
                    showOtherMonths: false,
                    selectOtherMonths: false,
                    toggleActive: true,
                    todayHighlight: false,
                    minDate: new Date(minD_YYYY, minD_MM, minD_DD),
                    maxDate: new Date(maxD_YYYY, maxD_MM, maxD_DD),
                    autoclose: true,
                    defaultDate: defaultDateVARIABLE,
                    onSelect: function() {
                        /**you need to format the date before sending */
                        /** from locale to expected YYYY-MM-DD */
                        var theselecteddate = $('.period_datepicker').val();
                        //console.log(theselecteddate);
                        var from = theselecteddate.split(delimiter_for_splitting_variable);
                        //console.log(from);
                        var datetogoto = null;
                        var default_dateYYY = null;
                        var default_dateMM = null;
                        var default_dateDD = null;
                        if (app.data.locale === 'vi') {
                            var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                            default_dateYYY = parseInt(from[2]);
                            default_dateMM = parseInt(from[1]);
                            default_dateDD = parseInt(from[0]);
                        } else if (app.data.locale === 'en') {
                            var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                            default_dateYYY = parseInt(from[2]);
                            default_dateMM = parseInt(from[1]);
                            default_dateDD = parseInt(from[0]);
                        } else if (app.data.locale === 'de') {
                            var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                            default_dateYYY = parseInt(from[2]);
                            default_dateMM = parseInt(from[1]);
                            default_dateDD = parseInt(from[0]);
                        }

                        //console.log(datetogoto);

                        var d = new Date(datetogoto);
                        var year_full = d.getFullYear();
                        var month_short = d.toLocaleString('default', { month: 'short' });
                        $('#attendance_status_change_period').val(month_short + ', ' + year_full);
                        var data = { '_token': $('meta[name="csrf-token"]').attr('content') };
                        app.ajax.jsonGET(app.data.timesheet_period.gotoUrl + '/' + datetogoto, data, null, function() {
                            NProgress.start();
                            app.util.fullscreenloading_start();

                            //console.log(app.ajax.result);
                            success_ajax_then_refresh = app.ajax.result.success;
                            if (app.ajax.result.success == true) {
                                /** you cannot simply repload the table you need to clear the tab twoweektimesheetshiftplannerInfo and then reinit the table will look bad won't it */
                                /** the headers are also in need of */
                                app.data.total_working_days = null;
                                app.data.total_working_time = null;
                                app.data.total_overtime = null;
                                app.data.total_fines = null;

                                $('#total_working_days').html('--');
                                $('#total_working_time').html('--:--:--');
                                $('#total_overtime').html('--:--:--');
                                $('#total_working_time_as_of_today').html('--:--:--');
                                $('#total_fines').html('--');
                                $('#attendance_status_change_period').val(month_short + ', ' + year_full);
                                defaultDateVARIABLE = new Date(default_dateYYY, default_dateMM, default_dateDD);
                                $period_datepicker.datepicker("option", "defaultDate", defaultDateVARIABLE);
                                $(".ui-state-active").removeClass('ui-state-active');

                                report_date_value = theselecteddate;
                                //console.log('redrawing');
                                window.downloadlisttable.settings()[0].jqXHR.abort();
                                window.downloadlisttable.draw();

                            } else {
                                /** */
                                /** */
                                //console.log('NOT working');
                                NProgress.done();
                                app.util.fullscreenloading_end();
                            }
                        });
                    },
                    onClose: function() {
                        var addressinput = $(this).val();
                        /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                        var res = patt.test(addressinput);

                        // if (res == true) {
                        //     $('#contract_date').closest('td').removeClass('has-error');
                        //     $('#contract_date').nextAll('.help-block').css('display', 'none');

                        //     $(this).blur();
                        //     counting_ctd = 1;
                        // } else {
                        //     $("#contract_date").closest("td").addClass("has-error");
                        //     if (counting_ctd == 1) {
                        //         $("#contract_date").after('<span class="help-block"><strong>' + eval("app.translations."+app.data.locale+".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations."+app.data.locale+".or_choose_from_the_calendar") + '</strong></span>');
                        //     } else {
                        //         // it exists
                        //     }
                        //     counting_ctd++;
                        //     $(this).blur();
                        // }
                    },
                    beforeShow: function(input, obj) {
                        // $period_datepicker.after($period_datepicker.datepicker('widget'));
                        var the_input_top = $('.period_datepicker').offset().top;
                        var the_input_left = $('.period_datepicker').offset().left;
                        setTimeout(function() {
                            $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 353).css('z-index', 102);
                            $('#ui-datepicker-div').find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day').find('a').removeClass('ui-state-active');
                        }, 0);
                    },
                    onChangeMonthYear: function() {
                        var the_input_top = $('.period_datepicker').offset().top;
                        var the_input_left = $('.period_datepicker').offset().left;
                        setTimeout(function() {
                            $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 353).css('z-index', 102);
                            $('#ui-datepicker-div').find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day').find('a').removeClass('ui-state-active');
                        }, 0);
                    },
                    beforeShowDay: function(date) {
                        var formated = formatDate(date);
                        var your_date = [];
                        /** the problem is that the array is not completely cleared when it is changed.. on table init clear the app.data.timesheet_period heap */
                        your_dates = Object.keys(app.data.timesheet_period.range_array_days_formated).map(function(key) {
                            return app.data.timesheet_period.range_array_days_formated[key];
                        });
                        // check if date is in your array of dates
                        function formatDate(date) {
                            var d = new Date(date),
                                month = '' + (d.getMonth() + 1),
                                day = '' + d.getDate(),
                                year = d.getFullYear();

                            if (month.length < 2) month = '0' + month;
                            if (day.length < 2) day = '0' + day;

                            return [year, month, day].join('-');
                        }

                        var todaydate = app.data.timesheet_period.today;

                        function formattodayDate(todaydate) {
                            var d = new Date(todaydate),
                                month = '' + (d.getMonth() + 1),
                                day = '' + d.getDate(),
                                year = d.getFullYear();

                            if (month.length < 2) month = '0' + month;
                            if (day.length < 2) day = '0' + day;

                            return [year, month, day].join('-');
                        }


                        var highlight_today = formattodayDate(todaydate);
                        if (formated == highlight_today) {
                            return [true, "ui-state-active shift_planner_datepicker_today", ''];
                        }

                        //console.log('formated='+formated);
                        //console.log('your_dates=' + your_dates);
                        if ($.inArray(formated, your_dates) != -1) {
                            // if it is return the following.
                            return [true, 'ui-state-active', ''];
                        } else {
                            // default
                            return [true, '', ''];
                        }
                    }
                });

                // app_ops.manage_downloadlist.profile.filter.byTeam(window.downloadlisttable);
                // app_ops.manage_downloadlist.profile.filter.byPosition(window.downloadlisttable);
                // app_ops.manage_downloadlist.profile.filter.byStatus(window.downloadlisttable);
                //app_ops.manage_downloadlist.profile.filter.clearallfilter(window.downloadlisttable);
                app_ops.manage_downloadlist.profile.filter.byJobStatus(window.downloadlisttable);
                app_ops.manage_downloadlist.profile.filter.byGlobalSearch(window.downloadlisttable);
                app_ops.manage_downloadlist.profile.filter.byColVis(window.downloadlisttable);
                app_ops.manage_downloadlist.profile.filter.byAssignee(window.downloadlisttable);
                app_ops.manage_downloadlist.profile.filter.byToolClient(window.downloadlisttable);
                //app_ops.manage_downloadlist.profile.filter.byEnabledDisabled(window.downloadlisttable);

                var isSectionAccounting = app.data.section.indexOf("accounting"); //>= 0 if finds //-1 if it does not find
                var canWRITEexport = app.data.auth_user_permissions.indexOf("WRITE export"); //>= 0 if finds //-1 if it does not find
                if (isSectionAccounting >= 0 && canWRITEexport >= 0) {
                    //app_ops.manage_downloadlist.profile.filter.exportExcel(window.downloadlisttable);
                }
                $('select#status_filter').multiselect({ columns: 3, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#team_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#position_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#enabled_disabled_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#jobstatus_filter').multiselect({ columns: 1, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#colVis_show_hide_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'None' } });
                $('select#assignees_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#tool_client_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });

                var filter_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_managedownloadlistTable_table_downloadlistTable'));
                if (filter_load !== null) {
                    // console.log("status selected before = " + filter_load['filters']['status']);
                    // console.log("team selected before = " + filter_load['filters']['team']);
                    // console.log("position selected before = " + filter_load['filters']['position']);
                    if (filter_load['filters']['global_search'] !== "") {
                        $("input[id='global_search_filter']").val(filter_load['filters']['global_search']);
                    }

                    if (filter_load['filters']['status_cb'] !== "") {
                        var array = filter_load['filters']['status_cb'].split('|');
                        $("select#status_filter").val(array);
                        $("select#status_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['team_cb'] !== "") {
                        var array = filter_load['filters']['team_cb'].split('|');
                        $("select#team_filter").val(array);
                        $("select#team_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['position_cb'] !== "") {
                        var array = filter_load['filters']['position_cb'].split('|');
                        $("select#position_filter").val(array);
                        $("select#position_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['editor_level_cb'] !== "") {
                        var array = filter_load['filters']['editor_level_cb'].split('|');
                        $("select#editor_level_filter").val(array);
                        $("select#editor_level_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['job_status_cb'] !== "") {
                        var array = filter_load['filters']['job_status_cb'].split('|');
                        /**console.log("job_status_cb array", array);*/
                        $("select#jobstatus_filter").val(array);
                        $("select#jobstatus_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['byEnabledDisabled'] !== "") {
                        var array = filter_load['filters']['byEnabledDisabled'];
                        $("select#enabled_disabled_filter").val(array);
                        $("select#enabled_disabled_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['byHideShowColumn_val'] !== "") {
                        var array = filter_load['filters']['byHideShowColumn_val'];
                        /**console.log(array);*/
                        $("select#colVis_show_hide_filter").val(array);
                        $("select#colVis_show_hide_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['byShowColumn'] !== "") {
                        var array = filter_load['filters']['byShowColumn'];
                        /**console.log(array);*/
                    }
                    if (filter_load['filters']['byHideShowColumn'] !== "") {
                        var array = filter_load['filters']['byHideShowColumn'];
                        /**console.log(array);*/
                    }

                    if (filter_load['filters']['assignee_cb'] !== "") {
                        var array = filter_load['filters']['assignee_cb'].split('|');
                        $("select#assignees_filter").val(array);
                        $("select#assignees_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['tool_client_cb'] !== "") {
                        var array = filter_load['filters']['tool_client_cb'].split('|');;
                        /**console.log("tool_client_cb array", array);*/
                        $("select#tool_client_filter").val(array);
                        $("select#tool_client_filter").multiselect('reload');
                    }

                } else {
                    /** define the default filtering if there are none available from sessionStorage */
                    window.byHideShowColumn_value = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '14', '15'];
                    $("select#colVis_show_hide_filter").val(window.byHideShowColumn_value);
                    $("select#colVis_show_hide_filter").multiselect('reload');

                    // $("select#enabled_disabled_filter").val(['1']);
                    // $("select#enabled_disabled_filter").multiselect('reload');
                    // byEnabledDisabled_value = ['1'];
                    $("select#jobstatus_filter").val(['3', '4', '5', '6', '7', '8']);
                    $("select#jobstatus_filter").multiselect('reload');
                    byJobStatus_value = "downloaded|pause|check|ready|feedback|in progress";
                    /** must draw to get the values including the variables set */
                    window.downloadlisttable.draw();
                }

                $('a[name="export"]').on('click', function() {
                    var srcEl = $(this);
                    let url = srcEl.data('action');
                    let params = window.downloadlisttable.ajax.params();
                    window.open(url + '?' + $.param(params) + '&filename=downloadlist-Info-' + app.data.timesheet_period.when + '&sheetname=' + app.data.timesheet_period.when);
                });
                $('a[name="clearallfilters"]').on('click', function() {
                    array = [];

                    global_search_value = '';
                    byStatus_value = '';
                    byCompanyDepartment_value = '';
                    byCompanyPosition_value = '';
                    byEnabledDisabled_value = '';
                    byJobStatus_value = '';
                    byAssignee_value = '';
                    byToolClient_value = '';

                    $("input[id='global_search_filter']").val('');
                    $("select#status_filter, select#team_filter, select#position_filter, select#enabled_disabled_filter, select#assignees_filter, select#tool_client_filter").val(array);
                    $("select#status_filter, select#team_filter, select#position_filter, select#enabled_disabled_filter, select#assignees_filter, select#tool_client_filter").multiselect('reload');
                    window.downloadlisttable.settings()[0].jqXHR.abort();
                    window.downloadlisttable.draw();
                });

                /** on page length change if any ajax requests happening then cancel those before */
                $('#downloadlistTable').on('length.dt', function(e, settings, len) {
                    window.downloadlisttable.settings()[0].jqXHR.abort();
                });

                $('#downloadlistTable').on('page.dt', function(e, settings, len) {
                    window.downloadlisttable.settings()[0].jqXHR.abort();
                });

                // Custom filter event for byStatus
                $("select[id='enabled_disabled_filter']").on('change', function() {
                    var options_all = $("#enabled_disabled_filter option:selected").map(function() {
                        return $(this).val();
                    }).get();
                    /** needs to be sent as an array of numbers */
                    byEnabledDisabled_value = options_all;
                    window.downloadlisttable.settings()[0].jqXHR.abort();
                    window.downloadlisttable.draw();
                });

                $("select[id='status_filter']").on('change', function() {
                    var options_all = $("#status_filter option:selected").map(function() {
                        return $(this).val();
                    }).get();
                    /** needs to be sent as an array of numbers */
                    byStatus_value = options_all;
                    window.downloadlisttable.settings()[0].jqXHR.abort();
                    window.downloadlisttable.draw();
                });

                $("select[id='position_filter']").on('change', function() {
                    var options_all = $("#position_filter option:selected").map(function() {
                        return $(this).text();
                    }).get().join('|');
                    //console.log('position=' + options_all);
                    /** needs to be sent as a string pipe delimited */
                    byCompanyPosition_value = options_all;
                    window.downloadlisttable.settings()[0].jqXHR.abort();
                    window.downloadlisttable.draw();
                });

                $("select[id='team_filter']").on('change', function() {
                    var options_all = $("#team_filter option:selected").map(function() {
                        return $(this).text();
                    }).get().join('|');
                    //console.log('department=' + options_all);
                    /** needs to be sent as a string pipe delimited */
                    byCompanyDepartment_value = options_all;
                    window.downloadlisttable.settings()[0].jqXHR.abort();
                    window.downloadlisttable.draw();
                });

                $("select[id='jobstatus_filter']").on('change', function() {
                    var options_all = $("#jobstatus_filter option:selected").map(function() {
                        return $(this).text();
                    }).get().join('|');
                    //console.log('department=' + options_all);
                    /** needs to be sent as a string pipe delimited */
                    byJobStatus_value = options_all;
                    window.downloadlisttable.settings()[0].jqXHR.abort();
                    window.downloadlisttable.draw();
                });

                function delay(callback, ms) {
                    var timer = 0;
                    return function() {
                        var context = this,
                            args = arguments;
                        clearTimeout(timer);
                        timer = setTimeout(function() {
                            callback.apply(context, args);
                        }, ms || 0);
                    };
                }

                $("input[id='global_search_filter']").on("drop search change keyup copy paste cut", delay(function(e) {
                    var keyCodedpressed = app.util.globalSearchkeyCodesPressedAllowed(e.keyCode);
                    if (keyCodedpressed || e.type == 'drop' || e.type == "search" || e.type == "change" || e.keyCode == 13) {
                        global_search_value = $(this).val();
                        if(global_search_value == '!' || global_search_value == '@'){
                            /** its a special seraching command and just by itself and a short delay will error */
                            /** so we don't reload the table unless there is more data to go on */
                        }else{
                            $.xhrPool.abortAll();
                            window.downloadlisttable.settings()[0].jqXHR.abort();
                            window.downloadlisttable.draw();
                        }
                    }
                }, 300));

                $("select[id='colVis_show_hide_filter']").on('change', function(e) {
                    e.preventDefault();
                    app_ops.manage_downloadlist.profile.handle_show_hide_columns_on_this_table();
                });


                $("select[id='assignees_filter']").on('change', function() {
                    var options_all = $("#assignees_filter option:selected").map(function() {
                        return $(this).text();
                    }).get().join('|');
                    console.log('assignees=' + options_all);
                    /** needs to be sent as a string pipe delimited */
                    byAssignee_value = options_all;
                    window.downloadlisttable.settings()[0].jqXHR.abort();
                    window.downloadlisttable.draw();
                });

                $("select[id='tool_client_filter']").on('change', function() {
                    var options_all = $("#tool_client_filter option:selected").map(function() {
                        return $(this).val();
                    }).get();
                    /**console.log('tool_client=' + options_all);*/
                    /** needs to be sent as a string pipe delimited */
                    byToolClient_value = options_all;
                    window.downloadlisttable.settings()[0].jqXHR.abort();
                    window.downloadlisttable.draw();
                });
            },
            handlefixedheaderpinning: function() {
                if (app.data.browser_detected == 'Chrome') {
                    var scrollTimer;
                    var resizeTimer;
                    var remembering_mainbody_padding = '';
                    $(window).scroll(function() {
                        clearTimeout(scrollTimer);
                        scrollTimer = setTimeout(function() {
                            var mainbody_padding = $('.visitor').outerHeight(true);
                            if (remembering_mainbody_padding != mainbody_padding) {
                                window.downloadlisttable.fixedHeader.headerOffset(mainbody_padding);
                                window.downloadlisttable.fixedHeader.adjust();
                                remembering_mainbody_padding = mainbody_padding;
                            }
                        }, 250);
                    });
                    $(window).resize(function() {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(function() {
                            var mainbody_padding = $('.visitor').outerHeight(true);
                            if (remembering_mainbody_padding != mainbody_padding) {
                                window.downloadlisttable.fixedHeader.headerOffset(mainbody_padding);
                                window.downloadlisttable.fixedHeader.adjust();
                                remembering_mainbody_padding = mainbody_padding;
                            }
                        }, 250);
                    });
                } else if (app.data.browser_detected == 'Firefox') {
                    var scrollTimer;
                    var resizeTimer;
                    var remembering_mainbody_padding = '';
                    $(window).scroll(function() {
                        clearTimeout(scrollTimer);
                        scrollTimer = setTimeout(function() {
                            var mainbody_padding = $('.visitor').outerHeight(true);
                            if (remembering_mainbody_padding != mainbody_padding) {
                                window.downloadlisttable.fixedHeader.headerOffset(mainbody_padding);
                                window.downloadlisttable.fixedHeader.adjust();
                                remembering_mainbody_padding = mainbody_padding;
                            }
                        }, 250);
                    });
                    $(window).resize(function() {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(function() {
                            var mainbody_padding = $('.visitor').outerHeight(true);
                            if (remembering_mainbody_padding != mainbody_padding) {
                                window.downloadlisttable.fixedHeader.headerOffset(mainbody_padding);
                                window.downloadlisttable.fixedHeader.adjust();
                                remembering_mainbody_padding = mainbody_padding;
                            }
                        }, 250);
                    });
                } else {}
            },
            get_downloadlistInfo_tab: function() {
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                var ajaxdata = {};

                app.ajax.html(app.data.tabHTML_downloadlistInfo, ajaxdata, null, function() {
                    $('#downloadlistInfo').html(app.ajax.result);
                    /**console.log('#downloadlistInfo_DONE');*/
                });
            },
            foreach_handle_error_display: function(idx, val) {
                // console.log('inside foreach_handle_error_display function');
                // console.log(idx);
                // console.log(val);
                // console.log('running');
                if (val.indexOf('The period modifier field is required when period unit is 2.') >= 0) {

                    var selector_id = 'period_modifier_send';
                    val = 'The period modifier field is required when period unit is Weekly';
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
                if (val.indexOf(' must be a date after ') >= 0) {
                    var splitString = val.split(" must be a date after ");
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

                if (val.indexOf(' is required when ') >= 0) {
                    var splitString = val.split(" is required when ");
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

                if (val.indexOf(' already exisits.') >= 0) {
                    var splitString = val.split(" already exisits.");
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


                if (val.indexOf(' may only contain letters.') >= 0) {
                    var splitString = val.split(" may only contain letters.");
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
            handle_show_hide_columns_on_this_table: function() {
                var all_options = [];
                var all_options_column_groups = [];
                var columns_visible = [];
                var upper_header_columns_visible = [];
                var upper_header_columns_hidden = [];
                var bottom_footer_columns_visible = [];
                var bottom_footer_columns_hidden = [];
                $("select[id='colVis_show_hide_filter'] option").each(function(index) {
                    //console.log($(this).val());
                    /** all avaliable options in the multiple select */
                    all_options[index] = $(this).val();
                    all_options_column_groups[index] = $("select[id='colVis_show_hide_filter'] option[value='" + $(this).val() + "']").attr('data-column');
                });

                $.each($("select[id='colVis_show_hide_filter']").val(), function(index, val) {
                    /**console.log(index);*/
                    //console.log(val);
                    columns_visible[index] = val;
                    upper_header_columns_visible[index] = parseInt(val) + 5;
                    bottom_footer_columns_visible[index] = parseInt(val) - 1;
                    //console.log($("select[id='colVis_show_hide_filter'] option[value='"+val+"']").attr('data-column'));
                    /** each of the values that are present mean they should be visible */
                });

                /**console.log("===================================================");*/
                /**console.log(all_options);*/
                /**console.log(all_options_column_groups);*/
                /**console.log("===================================================");*/
                /**console.log('upper_header_columns_visible');*/
                /**console.log(upper_header_columns_visible);*/
                /**console.log('bottom_footer_columns_visible');*/
                /**console.log(bottom_footer_columns_visible);*/

                /**console.log('columns_visible');*/
                /**console.log(columns_visible);*/
                var columns_hidden = all_options.filter(x => columns_visible.indexOf(x) === -1);
                /**console.log('columns_hidden');*/
                /**console.log(columns_hidden);*/


                $.each(columns_hidden, function(index, val) {
                    upper_header_columns_hidden[index] = parseInt(val) + 5;
                    if (val < 11) {
                        bottom_footer_columns_hidden[index] = parseInt(val) - 1;
                    } else {
                        if (val == 11) {
                            /** dont do it for this item because of the way the footer is made the order differs from the header */
                        } else {
                            bottom_footer_columns_hidden[index] = parseInt(val) - 2;
                        }
                    }
                });

                /**console.log('upper_header_columns_hidden');*/
                /**console.log(upper_header_columns_hidden);*/
                /**console.log('bottom_footer_columns_hidden');*/
                /**console.log(bottom_footer_columns_hidden);*/
                /**console.log("====================== hiding columns by val =============================");*/

                var counter = 0;
                var invisible_array = [];
                $.each(columns_hidden, function(index, val) {
                    /**console.log(index);*/
                    /**console.log(val);*/
                    var _val_index = null;
                    $.each(all_options, function(all_option_index, all_option_val) {
                        if (all_option_val == val) {
                            _val_index = all_option_index;
                        }
                    });

                    var data = all_options_column_groups[_val_index];
                    /**console.log('data - column numbers to hide ');*/
                    data = data.split(',');
                    /**console.log(data);*/

                    $.each(data, function(unimportant_index, column_val) {
                        invisible_array[counter] = parseInt(column_val);
                        counter++;
                    });

                });
                /**console.log(invisible_array);*/
                /**console.log("====================== showing columns by val =============================");*/

                var counter = 0;
                var visible_array = [];
                $.each(columns_visible, function(index, val) {
                    /**console.log(index);*/
                    /**console.log(val);*/
                    var _val_index = null;
                    $.each(all_options, function(all_option_index, all_option_val) {
                        if (all_option_val == val) {
                            _val_index = all_option_index;
                        }
                    });

                    var data = all_options_column_groups[_val_index];
                    /**console.log('data - column numbers to show ');*/
                    data = data.split(',');
                    /**console.log(data);*/

                    $.each(data, function(unimportant_index, column_val) {
                        visible_array[counter] = parseInt(column_val);
                        counter++;
                    });
                });
                /**console.log(visible_array);*/


                window.byHideShowColumn_value = $("select[id='colVis_show_hide_filter']").val();
                /**console.log(byHideShowColumn_value);*/

                window.byShowColumn_value = visible_array;
                window.byHideColumn_value = invisible_array;

                // var make_column_invisible = window.downloadlisttable.columns(invisible_array);
                // make_column_invisible.visible(false);

                // var make_column_visible = window.downloadlisttable.columns(visible_array);
                // make_column_visible.visible(true);
                /**window.downloadlisttable.draw();*/

                /** if you can get the table and go through all the columns checking if it needs to be hidden then remove the class otherwise add the class */

                $.each(window.downloadlisttable.columns().header(), function(key, value) {
                    /**console.log(key, value);*/
                    /**console.log(key);*/
                    if (visible_array.includes(key)) {
                        /**console.log(key + 'included in visible_array');*/
                        /** columns needs to visible change style to display: visible */
                        /** use the value to find the previous th and hide the th that way */
                        /**$(value).css('display', 'visible');*/
                        $(value).removeClass('hidden');
                        /**console.log($(value).parent().prev().children());*/

                        $.each($(value).parent().prev().children(), function(header_visible_key, header_visible_value) {
                            if (upper_header_columns_visible.includes(header_visible_key)) {
                                $(header_visible_value).removeClass('hidden');
                            }
                        });
                    }

                    if (invisible_array.includes(key)) {
                        /**console.log(key + 'included in invisible_array');*/
                        /** columns needs to hidden change style to display: none */
                        /** use the value to find the previous th and show the th that way */
                        /**$(value).css('display', 'none');*/
                        $(value).addClass('hidden');

                        $.each($(value).parent().prev().children(), function(header_invisible_key, header_invisible_value) {
                            if (upper_header_columns_hidden.includes(header_invisible_key)) {
                                $(header_invisible_value).addClass('hidden');
                            }
                        });

                    }
                });

                $.each(window.downloadlisttable.columns().footer(), function(key, value) {
                    /**console.log(key, value);*/
                    /**console.log(key);*/

                    if (visible_array.includes(key)) {
                        /**console.log(key + 'included in visible_array');*/
                        /** columns needs to visible change style to display: visible */
                        /** use the value to find the previous th and hide the th that way */

                        $(value).removeClass('hidden');
                        $.each($(value).parent().next().children(), function(header_visible_key, header_visible_value) {
                            if (bottom_footer_columns_visible.includes(header_visible_key)) {
                                $(header_visible_value).removeClass('hidden');
                            }
                        });
                    }


                    if (invisible_array.includes(key)) {
                        /**console.log(key + 'included in invisible_array');*/
                        /** columns needs to hidden change style to display: none */
                        /** use the value to find the previous th and show the th that way */

                        $(value).addClass('hidden');
                        $.each($(value).parent().next().children(), function(header_invisible_key, header_invisible_value) {
                            /**console.log(header_invisible_key, header_invisible_value);*/
                            if (bottom_footer_columns_hidden.includes(header_invisible_key)) {
                                $(header_invisible_value).addClass('hidden');
                            }
                        });
                    }
                });

                window.downloadlisttable.rows().every(function(rowIdx, tableLoop, rowLoop) {
                    $.each(this.node().children, function(key, value) {
                        /**console.log(key, value);*/
                        if (visible_array.includes(key)) {
                            $(value).removeClass('hidden');
                        }
                        if (invisible_array.includes(key)) {
                            $(value).addClass('hidden');
                        }
                    });
                });

                /**console.log('adjust the fixedheader');*/
                window.downloadlisttable.fixedHeader.adjust();
                /**window.downloadlisttable.draw();*/


                var filter_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_managedownloadlistTable_table_downloadlistTable'));
                /**console.log(filter_load);*/
                if (filter_load !== null) {
                    var data = filter_load;
                    data['filters'] = {
                        /** checkboxes */
                        "status_cb": filter_load['filters']['status_cb'],
                        "team_cb": filter_load['filters']['team_cb'],
                        "position_cb": filter_load['filters']['position_cb'],
                        "editor_level_cb": filter_load['filters']['editor_level_cb'],
                        "job_status_cb": filter_load['filters']['job_status_cb'],
                        "assignee_cb": filter_load['filters']['assignee_cb'],
                        "tool_client_cb": filter_load['filters']['tool_client_cb'],

                        /** query */
                        "byEnabledDisabled": filter_load['filters']['byEnabledDisabled'],
                        "byStatus": filter_load['filters']['byStatus'],
                        "byTeam": filter_load['filters']['byTeam'],
                        "byPosition": filter_load['filters']['byPosition'],
                        "byEditor_level": filter_load['filters']['byEditor_level'],
                        "byJobStatus": filter_load['filters']['byJobStatus'],
                        "byAssignee": filter_load['filters']['byAssignee'],
                        "byToolClient": filter_load['filters']['byToolClient'],

                        "byHideShowColumn_val": window.byHideShowColumn_value,
                        "byHideColumn": window.byHideColumn_value,
                        "byShowColumn": window.byShowColumn_value,

                        "locale_date_format": filter_load['filters']['locale_date_format'],

                        "global_search": filter_load['filters']['global_search']
                    };

                    /**console.log('setting the data BBB');*/
                    /**console.log(data);*/
                    sessionStorage.setItem('Br24_' + app.env() + '_managedownloadlistTable_table_downloadlistTable', JSON.stringify(data));
                }
            },
            sync_preview_required_status: function(case_id, encrypted_case_id_uploading_to, status, tr, checkbox_in_td, change_assignees_value) {
                if (change_assignees_value !== '') {
                    /** we need to protect againt people who change the cell details before clicking the preview contents.. */
                    /** we will just get the assignees from the db in that case */

                    var data = {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        'case_id': case_id,
                        'encrypted_case_id': encrypted_case_id_uploading_to,
                        'status': status,
                        'input_name': checkbox_in_td.attr('name')
                    };

                    /**console.log(app.data.urlSyncReviewRequiredStatus);*/
                    /**console.log(case_id);*/
                    /**console.log(encrypted_case_id_uploading_to);*/
                    /**console.log(status);*/

                    app.ajax.jsonGET(app.data.urlSyncReviewRequiredStatus, data, null, function() {
                        //console.log(app.ajax.result);
                        success_ajax_then_refresh = app.ajax.result.success;
                        if (app.ajax.result.success == true) {
                            app.data.selectize_hashtag_list_formated_json = app.ajax.result.selectize_hashtag_list_formated_json;
                            /**console.log('after');*/
                            /**console.log(select_list_hashtag_list);*/
                            // console.log(tr.children().next('.last_updated'));
                            // console.log(app.ajax.result.updated_at);
                            tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                            tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                        } else {
                            /** */
                            /** */
                            /**console.log('NOT working');*/
                            /** need to reset the check box to the original state */
                            if (checkbox_in_td.is(':checked')) {
                                /**console.log('3was not checked');*/
                                checkbox_in_td.prop("checked", false);
                                /**status = 1;*/
                            } else {
                                /**console.log('3was checked');*/
                                checkbox_in_td.prop("checked", true);
                                /**status = 2;*/
                            }
                            var alert_string = 'Something went wrong';
                            $.each(app.ajax.result.errors, function(idx, val) {
                                //app_attendance.manage_overtimes_requester.profile.foreach_handle_error_display(idx, val);
                                alert_string = alert_string + '<br>' + val;
                            });
                            alert_string = alert_string + '<br>' + app.ajax.result.caseId + ' preview required state not changed';
                            $.alert(alert_string);
                        }
                    });
                } else {
                    if (checkbox_in_td.is(':checked')) {
                        /**console.log('3was not checked');*/
                        checkbox_in_td.prop("checked", false);
                        checkbox_in_td.prop("disabled", true);
                        /**status = 1;*/
                    } else {
                        /**console.log('3was checked');*/
                        checkbox_in_td.prop("checked", true);
                        checkbox_in_td.prop("disabled", false);
                        /**status = 2;*/
                    }
                    var alert_string = case_id + ' preview required state not changed. Reason: Job has no assignees.';
                    $.alert(alert_string);
                }
            },
            filter: {
                // Add html & event for team filter
                byAssignee: function(table) {
                    var assignees = app.util.build.assignees();
                    var assigneesFilter = $("div#assignees_filter");
                    assigneesFilter.html(assignees);
                },
                byColVis: function(table) {
                    var custom_visibility_buttons = app.util.build.custom_visibility_buttons_auto_dl();
                    var colVisButtons = $("div#custom_visibility_buttons");
                    colVisButtons.html(custom_visibility_buttons).css('margin-left', '10px').addClass('dataTables_length');
                },
                byToolClient: function(table){
                    var tool_client_buttons = app.util.build.tool_client_auto_dl();
                    var toolClientButtons = $("div#tool_client_buttons");
                    toolClientButtons.html(tool_client_buttons);
                },
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
                            table.column(app.conf.table.filterColumn.managedownloadlistInfo.team).search(options_all, true, false).draw();
                        } else {
                            table.column(app.conf.table.filterColumn.managedownloadlistInfo.team).search('^' + options_all + '$', true, false).draw();
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
                            table.column(app.conf.table.filterColumn.managedownloadlistInfo.position).search(options_all, true, false).draw();
                        } else {
                            table.column(app.conf.table.filterColumn.managedownloadlistInfo.position).search('^' + options_all + '$', true, false).draw();
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
                        table.column(app.conf.table.filterColumn.managedownloadlistInfo.status + 1).search(options_all, true, false).draw();
                    });
                },
                byEnabledDisabled: function(table) {
                    var enabled_disabled = app.util.build.enabled_disabled();
                    var enabled_disabledFilter = $("div#enabled_disabled_filter");
                    enabled_disabledFilter.html(enabled_disabled);
                    // Custom filter event
                    $("select[id='enabled_disabled_filter']").on('change', function() {
                        var options_all = $("#enabled_disabled_filter option:selected").map(function() {
                            return $(this).val();
                        }).get().join('|');
                        table.column(app.conf.table.filterColumn.managedownloadlistInfo.enabled_disabled).search(options_all, true, false).draw();
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
                        table.column(app.conf.table.filterColumn.managedownloadlistInfo.sections).search(options_all, true, false).draw();
                    });
                },
                byJobStatus: function(table) {
                    var jobstatus = app.util.build.jobstatus();
                    var jobstatusFilter = $("div#jobstatus_filter");
                    jobstatusFilter.html(jobstatus);
                    // Custom filter event
                    $("select[id='jobstatus_filter']").on('change', function() {
                        var options_all = $("#jobstatus_filter option:selected").map(function() {
                            return $(this).text();
                        }).get().join('|');
                        window.downloadlisttable.column(app.conf.table.filterColumn.managedownloadlistInfo.jobstatus).search(options_all, true, false).draw();
                    });
                },
                exportExcel: function(table) {
                    var a = app.util.build.exportbutton();
                    var position = a.search("data-action=") + 13;
                    var b = app.data.URL_getdownloadlistinfoExportExcel;
                    var exportbuttonmake = [a.slice(0, position), b, a.slice(position)].join('');
                    var exportbuttonlocation = $("div#export_buttonlocation");
                    exportbuttonlocation.html(exportbuttonmake);
                },
                clearallfilter: function(table) {
                    var clearfilter = app.util.build.clearallfiltersbutton();
                    var clearFilterloc = $("div#clear_filter");
                    clearFilterloc.html(clearfilter);
                },
                byGlobalSearch: function(table) {
                    var global_search = app.util.build.global_search();
                    var global_searchFilter = $("div#custom_global_filter");
                    global_searchFilter.html(global_search);
                    // Custom filter event
                },
            }
        },
    },
    manage_manualdownloadlist: { //Manage manual downloadlist
        profile: {
            init: function() {
                $(window).on('load', function() {
                    app.util.period();
                    //app.util.nprogressinit();
                    $('.previous-surround, .next-surround').css('display', 'none');
                    NProgress.configure({ parent: '#ibox-title', showSpinner: false });
                    app_ops.manage_manualdownloadlist.profile.init_next();
                    app.util.fixedheaderviewporthandler();
                    console.log('manage_manualdownloadlist.index');

                    window.newdataimported = false;
                    /** https://stackoverflow.com/questions/43066633/laravel-echo-does-not-listen-to-channel-and-events?rq=1 */
                    /** must have the . infront of the event Class Name ? */
                    // console.log(window.Echo);
                    // window.Echo.channel('new_manualdl_job_data').listen('NewManualDLJobData', (e) => {
                    //     console.log(e);
                    //     window.manualdownloadlisttable.ajax.reload(null, false);
                    //     /**console.log('reloaded ajax window.downloadlisttable');*/
                    //     // window.location.reload();
                    //     /** if there are are actions currently doing such as with a row in the table */
                    //     /** refresh the auto dl table */
                    //     // // if ($("#colorbox").css("display") == "block") {
                    //     // //     console.log('ColorBox is currently open');
                    //     // // }else{
                    //     // //     console.log('ColorBox is currently closed');
                    //     // // }

                    //     // if ($("#colorbox").css("display") == "block") {
                    //     //     /**$('#cb_id_date_zoom_form').hasClass('dirty')*/
                    //     //     /** have some unsaved info so should not refresh just yet */
                    //     //     /** when they save the data it will refresh anyway */
                    //     //     /** but when they close refresh */
                    //     //     window.newdataimported = true;
                    //     // } else {
                    //     //     $("#cb_id_date_zoom.ajax").colorbox.close();
                    //     //     window.twoweektimesheetshiftplannertable.ajax.reload(null, false);
                    //     //     app.util.fullscreenloading_end();
                    //     //     window.twoweektimesheetshiftplannertable.fixedHeader.adjust();
                    //     // }
                    // });
                });
            },
            init_next: function() {
                var first_doc_refferer = document.referrer;
                var data = [];
                //what this part is doing is checking if more than one browser tab of the same route is trying to be opened at the same time... 
                var browser_tab_already_open_load = JSON.parse(localStorage.getItem('Br24_' + app.env() + '_managemanualdownloadlistinfo_oommdloa'));
                if (browser_tab_already_open_load !== null) {
                    //if the key exists it means the tab is open still.
                    if (first_doc_refferer === undefined || first_doc_refferer === null || first_doc_refferer == '') {
                        $('.loader').append('<span style="position: absolute; top: 45%; left: 50%; transform: translateX(-50%) translateY(-50%); font-size: 22px; color: red; text-align:center">Another Manage manual downloadlist tab is open already.<br>for security reasons only one instance is allowed<br><a style="font-size:10px" href="/downloadlist">Back Home</a></span>');
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
                        $('.loader').append('<span style="position: absolute; top: 45%; left: 50%; transform: translateX(-50%) translateY(-50%); font-size: 22px; color: red; text-align:center">Another Manage manual downloadlist tab is open already.<br>for security reasons only one instance is allowed<br><a style="font-size:10px" href="/downloadlist">Back Home</a></span>');
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

                    data['oommdloa'] = { "managemanualdownloadlist_browsertabstatus": "open", "timestamp": new Date().getTime() };
                    localStorage.setItem('Br24_' + app.env() + '_managemanualdownloadlistinfo_oommdloa', JSON.stringify(data['oommdloa']));

                    app_ops.manage_manualdownloadlist.profile.get_manualdownloadlistInfo_tab();

                    var onlycallonce = 1;
                    $(document).ajaxStop(function() {
                        if (onlycallonce == 1) {
                            //console.log("All AJAX requests completed");
                            //app_ops.manage_manualdownloadlist.profile.table();
                            app_ops.manage_manualdownloadlist.profile.add_edit_manualdownloadlist_colorbox();
                            onlycallonce = 2;
                        }
                    });

                    //the key to indicate that a tab with that employee detail is already open; will be removed from the localstorage
                    //be careful this also happens on form submit so should not remove the keys that allow this section to work
                    window.onbeforeunload = function() {};
                    window.onunload = function() {
                        localStorage.removeItem('Br24_' + app.env() + '_managemanualdownloadlistinfo_oommdloa');
                        //sessionStorage.removeItem('Br24_' + app.env() + '_managemanualdownloadlistTable_table_manualdownloadlistTable');
                    };
                    //how to detect when the only tab for the employee that is open is closed?
                    //at that point we want to clear the keys for the other items.
                }
            },
            add_edit_manualdownloadlist_colorbox: function() {
                app_ops.manage_manualdownloadlist.profile.manualdownloadlist_table();
                app_ops.manage_manualdownloadlist.profile.handlefixedheaderpinning();
            },
            manualdownloadlist_table: function() {
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

                var global_search_value = '';
                var byStatus_value = '';
                var byCompanyPosition_value = '';
                var byCompanyDepartment_value = '';
                var byEditorLevel_value = '';
                var byEnabledDisabled_value = '';
                var byJobStatus_value = '';
                window.byHideShowColumn_value = '';
                window.byShowColumn_value = '';
                window.byHideColumn_value = '';
                var byAssignee_value = '';

                /** to make it work with just one datepicker you have to supply the singular date */
                var default_datepicker_date_value = app.data.default_datepicker_date;
                var prev_month_min_date_value = app.data.minDatefordatefromfilter;
                var maxDatefordatefromfilter_prep = app.data.maxDatefordatefromfilter;
                var min_date_value = app.data.timesheet_period.firstdateofMonth;
                var max_date_value = app.data.timesheet_period.lastdateofMonth;
                var prev_min_date_value = app.data.timesheet_period.firstdateofMonth;
                var prev_max_date_value = app.data.timesheet_period.lastdateofMonth;
                var minD_YYYY = '';
                var minD_MM = '';
                var minD_DD = '';
                var maxD_YYYY = '';
                var maxD_MM = '';
                var maxD_DD = '';


                var report_date_value = null;
                var prev_month_from = prev_month_min_date_value.split(delimiter_for_splitting_variable);
                var max_month_from = maxDatefordatefromfilter_prep.split(delimiter_for_splitting_variable);
                var from = min_date_value.split("-"); /** from timesheet_period will always be YYY-MM-DD*/
                var to_date = max_date_value.split("-"); /** from timesheet_period will always be YYY-MM-DD*/
                /** relocate according to locale */
                if (app.data.locale === 'vi') {
                    minD_YYYY = parseInt(prev_month_from[2]);
                    minD_MM = parseInt(prev_month_from[1]) - 1;
                    minD_DD = parseInt(prev_month_from[0]);

                    maxD_YYYY = parseInt(max_month_from[2]);
                    maxD_MM = parseInt(max_month_from[1]) - 1;
                    maxD_DD = parseInt(max_month_from[0]);
                } else if (app.data.locale === 'en') {
                    minD_YYYY = parseInt(prev_month_from[2]);
                    minD_MM = parseInt(prev_month_from[1]) - 1;
                    minD_DD = parseInt(prev_month_from[0]);

                    maxD_YYYY = parseInt(max_month_from[2]);
                    maxD_MM = parseInt(max_month_from[1]) - 1;
                    maxD_DD = parseInt(max_month_from[0]);
                } else if (app.data.locale === 'de') {
                    minD_YYYY = parseInt(prev_month_from[2]);
                    minD_MM = parseInt(prev_month_from[1]) - 1;
                    minD_DD = parseInt(prev_month_from[0]);

                    maxD_YYYY = parseInt(max_month_from[2]);
                    maxD_MM = parseInt(max_month_from[1]) - 1;
                    maxD_DD = parseInt(max_month_from[0]);
                }

                var default_datepicker_date = default_datepicker_date_value.split('-');
                /** if i can somehow use the cache period to set the default date on open */
                var default_minD_YYYY = parseInt(default_datepicker_date[0]);
                var default_minD_MM = parseInt(default_datepicker_date[1]) - 1;
                var default_minD_DD = parseInt(default_datepicker_date[2]);
                var defaultDateVARIABLE = new Date(default_minD_YYYY, default_minD_MM, default_minD_DD)
                // console.log(min_date_value);
                // console.log(max_date_value);
                // console.log(min_date_value);
                // console.log(max_date_value);

                // console.log(minD_YYYY);
                // console.log(minD_MM);
                // console.log(minD_DD);
                // console.log(maxD_YYYY);
                // console.log(maxD_MM);
                // console.log(maxD_DD);


                var editing_configuration = null;
                /** don't refresh the table if clicking directly from one input to another input */
                var keep_track_of_last_clicked_item_to_react_after_reload = null;
                var keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = null;
                var keep_track_of_input_changes_to_assist_screen_load_input_disable = null;

                var keep_track_of_last_clicked_easyautocomplete_to_reopen = null;
                var keep_track_of_last_clicked_item_to_react_after_reload_2 = null;
                var keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2 = null;
                /** in this scenario we are setting the filters up at the begining with the begining of the current month and the current date */

                var only_trigger_once = null;
                var re_open_requested = null;

                var reopen_refreshIntervalId = null;
                var reopen_timer = function() {
                    reopen_refreshIntervalId = setInterval(function() {


                        /**console.log('============================================================================TIMER===INSIDE==================================');*/
                        /**console.log('keep_track_of_last_clicked_item_to_react_after_reload');*/
                        /**console.log(keep_track_of_last_clicked_item_to_react_after_reload);*/
                        /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload');*/
                        /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload);*/
                        /**console.log('keep_track_of_last_clicked_easyautocomplete_to_reopen');*/
                        /**console.log(keep_track_of_last_clicked_easyautocomplete_to_reopen);*/
                        /**console.log('keep_track_of_last_clicked_item_to_react_after_reload_2');*/
                        /**console.log(keep_track_of_last_clicked_item_to_react_after_reload_2);*/
                        /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2');*/
                        /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2);*/
                        /**console.log('re_open_requested');*/
                        /**console.log(re_open_requested);*/
                        /**console.log('============================================================================TIMER===INSIDE==================================');*/




                        $(".dataTable tbody td.parent." + keep_track_of_last_clicked_item_to_react_after_reload_2 + "[data-employee='" + keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2 + "']").trigger('click');
                        clearInterval(reopen_refreshIntervalId); //stop the timer called reopen_refreshIntervalId
                        keep_track_of_last_clicked_easyautocomplete_to_reopen = null;

                        keep_track_of_last_clicked_item_to_react_after_reload_2 = null;
                        keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2 = null;
                    }, 100);
                };


                var filter_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_managemanualdownloadlistTable_table_manualdownloadlistTable'));
                /** this grab from the sessions variable is to return the query variables to their set state */
                if (filter_load !== null) {
                    if (filter_load['filters']['global_search'] !== "") {
                        global_search_value = filter_load['filters']['global_search'];
                    }

                    if (filter_load['filters']['byStatus'] !== "") {
                        byStatus_value = filter_load['filters']['byStatus'];
                    }

                    if (filter_load['filters']['byTeam'] !== "") {
                        byCompanyDepartment_value = filter_load['filters']['byTeam'];
                    }

                    if (filter_load['filters']['byPosition'] !== "") {
                        byCompanyPosition_value = filter_load['filters']['byPosition'];
                    }

                    if (filter_load['filters']['byEditor_level'] !== "") {
                        byEditorLevel_value = filter_load['filters']['byEditor_level'];
                    }

                    if (filter_load['filters']['byEnabledDisabled'] !== "") {
                        byEnabledDisabled_value = filter_load['filters']['byEnabledDisabled'];
                    }
                    if (filter_load['filters']['byJobStatus'] !== "") {
                        byJobStatus_value = filter_load['filters']['byJobStatus'];
                    }

                    if (filter_load['filters']['byHideShowColumn_val'] !== "") {
                        window.byHideShowColumn_value = filter_load['filters']['byHideShowColumn_val'];
                    }

                    if (filter_load['filters']['byShowColumn'] !== "") {
                        window.byShowColumn_value = filter_load['filters']['byShowColumn'];
                    }
                    if (filter_load['filters']['byHideColumn'] !== "") {
                        window.byHideColumn_value = filter_load['filters']['byHideColumn'];
                    }

                    if (filter_load['filters']['byAssignee'] !== "") {
                        byAssignee_value = filter_load['filters']['byAssignee'];
                    }

                }

                /** need to provide for the column widths for difference broswers otherwise will look horrible */
                if (app.data.browser_detected == 'Chrome') {
                    var broswer_columnDefs = [
                        // { targets: [0], width: '10px' },
                        // { targets: [1], width: '10px' },
                        // { targets: [2], width: '70px' },
                        // { targets: [3], width: '10px' },
                        // { targets: [4], width: '10px' },
                        // { targets: [5], width: '10px' },
                        // { targets: [6], width: '10px' },
                        // { targets: [7], width: '10px' },
                        // { targets: [8], width: '30px' },
                        // //{ targets: [9], width: '5px' },
                        // { targets: [9], width: '200px' },
                        // { targets: [10], width: '70px' },
                        // { targets: [11], width: '5px' },
                        // { targets: [12], width: '30px' },
                        // { targets: [13], width: '60px' },
                        // { targets: [14, 15, 16], width: '10px' },
                    ];
                    var browser_columns = [
                        //{ orderable: false, searchable: false, data: 'numbering' },
                        { orderable: true, searchable: true, data: 'case_id', className: 'mdl_jobId_col_width' },
                        { orderable: false, searchable: true, data: 'xml_jobid_title', className: 'mdl_jobId_col_width' },
                        { orderable: false, searchable: true, data: 'xml_title_contents', className: 'mdl_xml_title_contents_col_width' },
                        { orderable: false, searchable: true, data: 'number_of_pictures', className: 'mdl_number_of_pictures_col_width' },
                        { orderable: false, searchable: true, data: 'instructions_col', className: 'mdl_extra_small_col_width' },
                        { orderable: false, searchable: true, data: 'preview_col', className: 'mdl_extra_small_col_width two_weeks_checkbox_col' },
                        { orderable: false, searchable: true, data: 'output_files_col', className: 'mdl_extra_small_col_width' },
                        { orderable: true, searchable: false, data: 'delivery_time', name: 'expected_delivery_date_coalesce_sortorder', className: 'mdl_delivery_time_col_width' },
                        { orderable: false, searchable: false, data: 'assignees', className: 'mdl_assignee_tags_col_width' },
                        //{ orderable: false, searchable: false, data: 'custom_row_color_input', className: 'hidden mdl_extra_small_col_width'},
                        { orderable: false, searchable: false, data: 'internal_notes', className: 'mdl_internal_notes_col_width' },
                        { orderable: false, searchable: false, data: 'job_from', className: 'mdl_job_from_col_width' },
                        { orderable: false, searchable: false, data: 'rating', className: 'hidden mdl_assignee_tags_col_width' },
                        { orderable: false, searchable: false, data: 'tags', className: 'mdl_assignee_tags_col_width' },
                        { orderable: true, searchable: true, data: 'job_status', name: 'status_grouping_sort_order', className: 'mdl_modified_dates_col_with' },
                        { orderable: true, searchable: true, data: 'created_at', name: 'created_at_sortorder', className: 'mdl_modified_dates_col_with' },
                        { orderable: false, searchable: false, data: 'last_updated_by', className: 'mdl_modified_dates_col_with' },
                        { orderable: true, searchable: false, data: 'last_updated', name: 'last_updated_sortorder', className: 'mdl_modified_dates_col_with' },
                        //{ orderable: false, searchable: false, data: 'status_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'expected_delivery_time_custom_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'actions', className: 'hidden' },
                    ];
                } else if (app.data.browser_detected == 'Firefox') {
                    var broswer_columnDefs = [
                        // { targets: [0], width: '10px' },
                        // { targets: [1], width: '10px' },
                        // { targets: [2], width: '10px' },
                        // { targets: [3], width: '10px' },
                        // { targets: [4], width: '10px' },
                        // { targets: [5], width: '10px' },
                        // { targets: [6], width: '10px' },
                        // { targets: [7], width: '10px' },
                        // { targets: [8], width: '10px' },
                        // //{ targets: [9], width: '5px' },
                        // { targets: [9], width: '10px' },
                        // { targets: [10], width: '10px' },
                        // { targets: [11], width: '5px' },
                        // { targets: [12], width: '10px' },
                        // { targets: [13], width: '10px' },
                        // { targets: [14, 15, 16], width: '10px' },
                    ];
                    var browser_columns = [
                        //{ orderable: false, searchable: false, data: 'numbering' },
                        { orderable: true, searchable: true, data: 'case_id', className: 'mdl_jobId_col_width' },
                        { orderable: false, searchable: true, data: 'xml_jobid_title', className: 'mdl_jobId_col_width' },
                        { orderable: false, searchable: true, data: 'xml_title_contents', className: 'mdl_xml_title_contents_col_width' },
                        { orderable: false, searchable: true, data: 'number_of_pictures', className: 'mdl_number_of_pictures_col_width' },
                        { orderable: false, searchable: true, data: 'instructions_col', className: 'mdl_extra_small_col_width' },
                        { orderable: false, searchable: true, data: 'preview_col', className: 'mdl_extra_small_col_width two_weeks_checkbox_col' },
                        { orderable: false, searchable: true, data: 'output_files_col', className: 'mdl_extra_small_col_width' },
                        { orderable: true, searchable: false, data: 'delivery_time', name: 'expected_delivery_date_coalesce_sortorder', className: 'mdl_delivery_time_col_width' },
                        { orderable: false, searchable: false, data: 'assignees', className: 'mdl_assignee_tags_col_width' },
                        //{ orderable: false, searchable: false, data: 'custom_row_color_input', className: 'hidden mdl_extra_small_col_width'},
                        { orderable: false, searchable: false, data: 'internal_notes', className: 'mdl_internal_notes_col_width' },
                        { orderable: false, searchable: false, data: 'job_from', className: 'mdl_job_from_col_width' },
                        { orderable: false, searchable: false, data: 'rating', className: 'hidden mdl_assignee_tags_col_width' },
                        { orderable: false, searchable: false, data: 'tags', className: 'mdl_assignee_tags_col_width' },
                        { orderable: true, searchable: true, data: 'job_status', name: 'status_grouping_sort_order', className: 'mdl_modified_dates_col_with' },
                        { orderable: true, searchable: true, data: 'created_at', name: 'created_at_sortorder', className: 'mdl_modified_dates_col_with' },
                        { orderable: false, searchable: false, data: 'last_updated_by', className: 'mdl_modified_dates_col_with' },
                        { orderable: true, searchable: false, data: 'last_updated', name: 'last_updated_sortorder', className: 'mdl_modified_dates_col_with' },
                        //{ orderable: false, searchable: false, data: 'status_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'expected_delivery_time_custom_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'actions', className: 'hidden' },
                    ];
                } else {
                    /** use fallback */
                    var broswer_columnDefs = [
                        // { targets: [0], width: '10px' },
                        // { targets: [1], width: '10px' },
                        // { targets: [2], width: '60px' },
                        // { targets: [3], width: '10px' },
                        // { targets: [4], width: '10px' },
                        // { targets: [5], width: '10px' },
                        // { targets: [6], width: '10px' },
                        // { targets: [7], width: '10px' },
                        // { targets: [8], width: '30px' },
                        // //{ targets: [9], width: '5px' },
                        // { targets: [9], width: '200px' },
                        // { targets: [10], width: '70px' },
                        // { targets: [11], width: '5px' },
                        // { targets: [12], width: '30px' },
                        // { targets: [13], width: '60px' },
                        // { targets: [14, 15, 16], width: '10px' },
                    ];
                    var browser_columns = [
                        //{ orderable: false, searchable: false, data: 'numbering' },
                        { orderable: true, searchable: true, data: 'case_id', className: 'mdl_jobId_col_width' },
                        { orderable: false, searchable: true, data: 'xml_jobid_title', className: 'mdl_jobId_col_width' },
                        { orderable: false, searchable: true, data: 'xml_title_contents', className: 'mdl_xml_title_contents_col_width' },
                        { orderable: false, searchable: true, data: 'number_of_pictures', className: 'mdl_number_of_pictures_col_width' },
                        { orderable: false, searchable: true, data: 'instructions_col', className: 'mdl_extra_small_col_width' },
                        { orderable: false, searchable: true, data: 'preview_col', className: 'mdl_extra_small_col_width two_weeks_checkbox_col' },
                        { orderable: false, searchable: true, data: 'output_files_col', className: 'mdl_extra_small_col_width' },
                        { orderable: true, searchable: false, data: 'delivery_time', name: 'expected_delivery_date_coalesce_sortorder', className: 'mdl_delivery_time_col_width' },
                        { orderable: false, searchable: false, data: 'assignees', className: 'mdl_assignee_tags_col_width' },
                        //{ orderable: false, searchable: false, data: 'custom_row_color_input', className: 'hidden mdl_extra_small_col_width'},
                        { orderable: false, searchable: false, data: 'internal_notes', className: 'mdl_internal_notes_col_width' },
                        { orderable: false, searchable: false, data: 'job_from', className: 'mdl_job_from_col_width' },
                        { orderable: false, searchable: false, data: 'rating', className: 'hidden mdl_assignee_tags_col_width' },
                        { orderable: false, searchable: false, data: 'tags', className: 'mdl_assignee_tags_col_width' },
                        { orderable: true, searchable: true, data: 'job_status', name: 'status_grouping_sort_order', className: 'mdl_modified_dates_col_with' },
                        { orderable: true, searchable: true, data: 'created_at', name: 'created_at_sortorder', className: 'mdl_modified_dates_col_with' },
                        { orderable: false, searchable: false, data: 'last_updated_by', className: 'mdl_modified_dates_col_with' },
                        { orderable: true, searchable: false, data: 'last_updated', name: 'last_updated_sortorder', className: 'mdl_modified_dates_col_with' },
                        //{ orderable: false, searchable: false, data: 'status_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'expected_delivery_time_custom_grouping', className: 'hidden' },
                        //{ orderable: false, searchable: false, data: 'actions', className: 'hidden' },
                    ];
                }

                window.manualdownloadlisttable = $('#manualdownloadlistTable').DataTable({
                    pageLength: app.conf.table.pageLength,
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
                        footerOffset: 0
                    },
                    //fixedColumns: true,
                    // colResize: {
                    //     fixedHeader: {
                    //         bottom: true,
                    //     }
                    // },
                    aaSorting: [],
                    stateSave: true,
                    stateDuration: -1,
                    searching: false,
                    stateSaveCallback: function(settings, data) {
                        /** checkbox variables */
                        var status_all = $("#status_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var team_all = $("#team_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var position_all = $("#position_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var editor_level_all = $("#editor_level_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var job_status_all = $("#jobstatus_filter option:selected").map(function() { return $(this).val(); }).get().join('|');
                        var assignee_all = $("#assignees_filter option:selected").map(function() { return $(this).val(); }).get().join('|');

                        /** input variables also query init */
                        var global_search_all = $("#global_search_filter").val();

                        var locale_date_format = prefered_dateFormat;

                        data['filters'] = {
                            /** checkboxes */
                            "status_cb": status_all,
                            "team_cb": team_all,
                            "position_cb": position_all,
                            "editor_level_cb": editor_level_all,
                            "job_status_cb": job_status_all,
                            "assignee_cb": assignee_all,

                            /** query */
                            "byEnabledDisabled": byEnabledDisabled_value,
                            "byStatus": byStatus_value,
                            "byTeam": byCompanyDepartment_value,
                            "byPosition": byCompanyPosition_value,
                            "byEditor_level": byEditorLevel_value,
                            "byJobStatus": byJobStatus_value,
                            "byAssignee": byAssignee_value,

                            "byHideShowColumn_val": window.byHideShowColumn_value,
                            "byHideColumn": window.byHideColumn_value,
                            "byShowColumn": window.byShowColumn_value,

                            "locale_date_format": locale_date_format,

                            "global_search": global_search_all
                        };
                        sessionStorage.setItem('Br24_' + app.env() + '_managemanualdownloadlistTable_table_' + settings.sInstance, JSON.stringify(data));
                    },
                    stateLoadCallback: function(settings) {
                        return JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_managemanualdownloadlistTable_table_' + settings.sInstance));
                        //
                    },
                    oLanguage: {
                        'sProcessing': "<div class='loader_blank'></div><div class='processingblured'>" + eval("app.translations." + app.data.locale + ".please_wait_loading") + "<br><img src='../img/loader.gif'></div><div class='no_blurtext'>" + eval("app.translations." + app.data.locale + ".please_wait_loading") + "<br><img src='../img/loader.gif'></div>",
                        'sZeroRecords': eval("app.translations." + app.data.locale + ".no_downloadlist_added")
                    },
                    ajax: {
                        url: app.data.getmanualdownloadlistinfo_db_table,
                        dataSrc: 'data',
                        type: "POST",
                        beforeSend: function(xhr, type) {
                            if (!type.crossDomain) {
                                xhr.setRequestHeader('X-CSRF-Token', $('meta[name="csrf-token"]').attr('content'));
                            }
                        },
                        data: function(d) {
                            //d.min_birthday = min_birthday_value;
                            //d.max_birthday = max_birthday_value;
                            d.report_date_value = report_date_value;
                            d.locale_date_format = prefered_dateFormat;

                            d.global_search_value = global_search_value;

                            d.byStatus_value = byStatus_value;
                            d.byCompanyPosition_value = byCompanyPosition_value;
                            d.byCompanyDepartment_value = byCompanyDepartment_value;
                            d.byEditorLevel_value = byEditorLevel_value;
                            d.byJobStatus_value = byJobStatus_value;
                            //d.byBirthmonths_value = byBirthmonths_value;
                            d.byEnabledDisabled_value = byEnabledDisabled_value;

                            d.byAssignee_value = byAssignee_value;
                        },
                    },
                    columnDefs: broswer_columnDefs,
                    columns: browser_columns,
                    //order: [[1, "asc" ], [5, "desc"]],
                    order: [
                        [14, "asc"]
                    ],

                    // rowGroup: {
                    //     dataSrc: 'status_grouping',
                    //     //startClassName: 'status_grouping_style unselectable',
                    //     startRender: function(rows, group_name) {
                    //         return $('<tr class="group group-start"><td class="status_grouping_style unselectable" colspan="19"><div style="text-align: left; margin-left: 10px">' + group_name + '</div></td></tr>');
                    //     }
                    // },

                    rowGroup: {
                        dataSrc: 'expected_delivery_time_custom_grouping',
                        //startClassName: 'status_grouping_style unselectable',
                        startRender: function(rows, custom_expected_delivery_time_group) {
                            //return $('<tr style="height: 5px; max-height: 5px;" class="group group-start"><td style="font-size: 5px; height: 5px; max-height: 5px;" class="status_grouping_style unselectable" colspan="17"><div style="text-align: left; margin-left: 10px">' + custom_expected_delivery_time_group + '</div></td></tr>');
                            return $('<tr style="height: 5px; max-height: 5px;" class="group group-start"><td style="font-size: 5px; height: 5px; max-height: 5px;" class="status_grouping_style unselectable" colspan="17"><div style="text-align: left; margin-left: 10px"></div></td></tr>');
                        }
                    },

                    dom: '<".controlsfortable"<"#export_buttonlocation.html5buttons">rl<"#custom_visibility_buttons">f<"#custom_global_filter.dataTables_filter"><"#jobstatus_filter.dataTables_filter"><"#assignees_filter.dataTables_filter"><"#editor_level_filter.dataTables_filter"><"#team_filter.dataTables_filter"><"#position_filter.dataTables_filter"><"#status_filter.dataTables_filter"><"#birthdaytodate_filter.dataTables_filter"><"#birthdayfromdate_filter.dataTables_filter"><"#birthmonths_filter.dataTables_filter"><"#clear_filter.html5buttons"><"clearfix"><".table_block"<".table_float_left"i><".table_float_right"p>>>t<".controlsfortable"p>',
                    createdRow: function(row, data, index) {
                        /**console.log('data=' + JSON.stringify(data));*/
                        /**console.log('index=' + index);*/
                        if(index == 0){
                            /** stop the polling thing on a reload */
                            clearInterval(window.autoPollrefreshIntervalId);
                            clearInterval(window.autoPollrefreshIntervalId_ASAP);
                            /** to update the app.data. variable to pull the changes immediates to be used */
                            var api = this.api();
                            var json = api.ajax.json();
                            /** JSON stringifying sorts the keys alphabetically */
                            /** ViewComposer adding CURRENCY key .. after this it is removed. what are the implications? */
                            var extends_app = JSON.stringify(json.extends_app);
                            extends_app = extends_app.replace(/\//g, "\\\/");
                            /**console.log(json.extends_app);*/
                            /** since we are trying new method for changing the timesheet period */
                            var script = document.createElement('script');
                            script.type = 'text/javascript';
                            script.id = 'extends_app';
                            script.text = 'app.ext(' + extends_app + ');';
                            var element = document.getElementById("extends_app");
                            document.getElementById("extends_app").parentNode.replaceChild(script, element);
                        }

                        var custom_row_color = '';
                        if (data['custom_color_rgb'] === undefined || data['custom_color_rgb'] === null) {
                            custom_row_color = '';
                        } else {
                            custom_row_color = '#' + data['custom_color_rgb'];
                        }
                        /**console.log(custom_row_color);*/

                        $('td', row).parent().attr('data-rowindex', index).css('background-color', custom_row_color);


                        //if (JSON.stringify(data.user_id) != 0) { $(row).css('cursor', 'pointer'); }
                        //necessary for the detailed task view per employee
                        $('td', row).parent().attr('data-encrypted_case_id', data['encrypted_case_id']);
                        /** there are fewer columns now hiding the checkbox columns due to fewer permissions and we need to adjust the variables */
                        var countervariableH = 0;
                        var countervariableG = 1;
                        var countervariableN = 2;
                        var countervariableI = 3;
                        var countervariable3 = 4;
                        var countervariableX = 5;
                        var countervariableP = 6;
                        var countervariable2 = 7;
                        var countervariable = 8;
                        //var countervariableA = 9;
                        var countervariableD = 9;
                        var countervariableE = 11;
                        var countervariableJ = 12;
                        var countervariableB = 13;
                        var countervariableO = 14;
                        var countervariableF = 15;
                        var countervariableC = 16;

                        // var case_id_string = data['case_id'];
                        // case_id_string = case_id_string.split('<span style="font-weight: 900;" class="case_id_string">');
                        // case_id_string = case_id_string[1].split('</span></a>');
                        // case_id_string = case_id_string[0];
                        // /**console.log(case_id_string);*/

                        /**console.log(app.data.browser_detected);*/
                        if (app.data.browser_detected == 'Chrome') {
                            var local_file_link_prefix = 'file://192.168.1.3/manual/';
                        } else if (app.data.browser_detected == 'Firefox') {
                            var local_file_link_prefix = 'file://///192.168.1.3/manual/';
                        } else {
                            /** use fallback */
                            var local_file_link_prefix = 'file://192.168.1.3/manual/';
                        }

                        $.each(data, function(i, v) {
                            //console.log("Index #" + i + ": " + v);

                            if (i.indexOf('case_id') >= 0) {
                                $('td', row).eq(countervariableH).html('<a class="button keyboardshortcutcombinationtolinktosharedfolder" style="text-decoration: none; font-weight: 900;" data-backup_href="" data-alt_href="/manual_uploadfiles/" href="' + local_file_link_prefix + data['case_id'] + '/" target="_blank">' + data['case_id_display'] + '</a>').css('font-size', '0.7vw');
                            }

                            if (i.indexOf('xml_title_contents') >= 0) {
                                $('td', row).eq(countervariableN).css('font-size', '0.7vw');
                            }

                            if (i.indexOf('xml_jobid_title') >= 0) {

                                if (data['xml_jobid_title'] == '') {
                                    var link_to_upload_page_with_case_id = ''
                                } else {
                                    var link_to_upload_page_with_case_id = '<a class="button keyboardshortcutcombinationtolinktosharedfolder" style="text-decoration: none; font-weight: 900;" data-backup_href="" data-alt_href="/manual_uploadfiles/" href="' + local_file_link_prefix + data['case_id'] + '/" target="_blank">' + data['xml_jobid_title_display'] + '</a>';
                                }
                                $('td', row).eq(countervariableG).html(link_to_upload_page_with_case_id).css('font-size', '0.7vw');
                            }

                            if (i.indexOf('internal_notes') >= 0) {
                                $('td', row).eq(countervariableD).css('cursor', 'pointer').css('text-align', 'left').css('vertical-align', 'top').css('word-break', 'break-word').addClass('change_internal_notes').addClass('internal_notes').css('font-size', '0.8vw');
                                $('td', row).eq(countervariableD).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '').css('font-size', '0.8vw');
                            }

                            if (i.indexOf('instructions_col') >= 0) {
                                $('td', row).eq(countervariable3).css('cursor', 'pointer').addClass('change_instructions_col').addClass('instructions_col');
                                $('td', row).eq(countervariable3).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');


                                if (data['xml_jobinfoproduction'] == '') {
                                    var instructions_popover = ''
                                } else {
                                    var instructions_popover = '<span style="font-size: 0.7vw;" class="instructions_popover" data-html="true" data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-content="' + data['xml_jobinfoproduction'] + '">'+data['instructions_col']+'</span>';
                                }

                                $('td', row).eq(countervariable3).html(instructions_popover).css('font-size', '0.9vw');
                            }

                            if (i.indexOf('preview_col') >= 0) {
                                $('td', row).eq(countervariableX).css('cursor', 'pointer').addClass('change_preview_col').addClass('preview_col');
                                $('td', row).eq(countervariableX).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '').css('font-size', '0.7vw');;
                            }

                            /**if there is a currently downloading job for a particular case .. we flash the number of files column per type updating. */
                            /** they can hover to see details of the download progress.. */
                            /** the trick is how do we make it not clog up the user experience */

                            if (i == 'number_of_pictures') {
                                if (data['number_of_pictures_example'] == null) {
                                    var number_of_pictures_example = '&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else {
                                    var number_of_pictures_example = data['number_of_pictures_example'];
                                }

                                if (data['number_of_pictures_new'] == null) {
                                    var number_of_pictures_new = '&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else {
                                    var number_of_pictures_new = data['number_of_pictures_new'];
                                }

                                if (data['number_of_pictures_ready'] == null) {
                                    var number_of_pictures_ready = '&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else {
                                    var number_of_pictures_ready = data['number_of_pictures_ready'];
                                }


                                /** when the page refreshes we need to have th app.data. refreshed also */

                                var indicate_example_currently_downloading = '';
                                var indicate_new_currently_downloading = '';
                                var indicate_ready_currently_downloading = '';
                                var indicate_example_currently_downloading_prog = '';
                                var indicate_new_currently_downloading_prog = '';
                                var indicate_ready_currently_downloading_prog = '';                                
                                /**.currently_downloading_indicator*/
                                if (app.data.currently_downloading_aria2c[data['case_id']] != null || app.data.currently_downloading_aria2c[data['case_id']] != undefined) {
                                    /**console.log(app.data.currently_downloading_aria2c[data['case_id']]);*/
                                    $.each(app.data.currently_downloading_aria2c[data['case_id']], function(inner_i, inner_v) {
                                        if(inner_i == 'example'){
                                            indicate_example_currently_downloading = 'class="currently_downloading_indicator"';
                                            indicate_example_currently_downloading_prog = 'class="iecd_'+data['case_id']+'"';
                                        }
                                        if(inner_i == 'new'){
                                            indicate_new_currently_downloading = 'class="currently_downloading_indicator"';
                                            indicate_new_currently_downloading_prog = 'class="incd_'+data['case_id']+'"';
                                        }
                                        if(inner_i == 'ready'){
                                            indicate_ready_currently_downloading = 'class="currently_downloading_indicator"';
                                            indicate_ready_currently_downloading_prog = 'class="ircd_'+data['case_id']+'"';
                                        }
                                    });
                                }

                                /** if there is a way to have this already open up if there are downloads with some default */
                                var number_of_pictures_table_parent_first_padding_bottom = null;
                                var number_of_pictures_table_padding_top = null;
                                var number_of_pictures_table_show = null;
                                var number_of_pictures_example_show = null;
                                var number_of_pictures_new_show = null;
                                var number_of_pictures_ready_show = null;
                                if(data['number_of_pictures_table_show'] == false){
                                    number_of_pictures_table_show = 'display: none;';
                                    number_of_pictures_table_parent_first_padding_bottom = 'padding-bottom: 4px;';
                                    number_of_pictures_table_padding_top = 'padding-top: 4px;';
                                }else{
                                    number_of_pictures_table_show = '';
                                    number_of_pictures_table_parent_first_padding_bottom = '';
                                    number_of_pictures_table_padding_top = '';
                                }
                                if(data['number_of_pictures_example_show'] == false){
                                    number_of_pictures_example_show = 'display: none;';
                                }else{
                                    number_of_pictures_example_show = '';
                                }
                                if(data['number_of_pictures_new_show'] == false){
                                    number_of_pictures_new_show = 'display: none;';
                                }else{
                                    number_of_pictures_new_show = '';
                                }
                                if(data['number_of_pictures_ready_show'] == false){
                                    number_of_pictures_ready_show = 'display: none;';
                                }else{
                                    number_of_pictures_ready_show = '';
                                }


                                $('td', row).eq(countervariableI).html('<div style="font-size: 0.7vw; width: 100%; position:relative; padding-top: 4px; padding-left: 4px; padding-right: 4px; '+number_of_pictures_table_parent_first_padding_bottom+'"><span '+indicate_example_currently_downloading+' style="display:inline-block; width: 20%;">' + number_of_pictures_example + '</span><span style="font-size:14px; display:inline-block; width: 20%;">|</span><span '+indicate_new_currently_downloading+' style="display:inline-block; width: 20%;">' + number_of_pictures_new + '</span><span style="font-size:14px; display:inline-block; width: 20%;">|</span><span '+indicate_ready_currently_downloading+' style="display:inline-block; width: 20%;">' + number_of_pictures_ready + '</span></div>').append('<div style="'+number_of_pictures_table_show+' font-size: 0.6vw; width: 100%; position:relative; '+number_of_pictures_table_padding_top+' padding-left: 4px; padding-right: 4px; padding-bottom: 4px;"><div style="'+number_of_pictures_example_show+'"><div style="text-align: left;"><u>emp</u></div><div><span '+indicate_example_currently_downloading_prog+'>........</span></div></div><div style="'+number_of_pictures_new_show+'"><div style="text-align: left;"><u>new</u></div><div><span '+indicate_new_currently_downloading_prog+'>........</span></div></div><div style="'+number_of_pictures_ready_show+'"><div style="text-align: left;"><u>rdy</u></div><div><span '+indicate_ready_currently_downloading_prog+'>........</span></div></div></div>');
                            }


                            if (i.indexOf('output_files_col') >= 0) {

                                if (data['output_number_of_pictures_expected'] == null) {
                                    var output_number_of_pictures_expected = '&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else {
                                    var output_number_of_pictures_expected = data['output_number_of_pictures_expected'];
                                }

                                if (data['output_number_of_pictures_real'] == null) {
                                    var output_number_of_pictures_real = '&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else {
                                    var output_number_of_pictures_real = data['output_number_of_pictures_real'];
                                }

                                $('td', row).eq(countervariableP).addClass('change_output_files_col').addClass('output_files_col');
                                $('td', row).eq(countervariableP).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                                $('td', row).eq(countervariableP).html('<div class="manual_change_output_files_col"  style="width: 100%; position:relative; padding: 4px"><div class="output_number_of_pictures_expected" style="display:inline-block; width: 33.33%;">' + output_number_of_pictures_expected + '</div><div style="display:inline-block; width: 33.33%;">|</div><div class="output_number_of_pictures_real" style="display:inline-block; width: 33.33%;">' + output_number_of_pictures_real + '</div></div>').css('font-size', '0.7vw');;
                            }


                            if (i.indexOf('rating') >= 0) {
                                if (data['custom_job_star_rating_comment'] == null) {
                                    var custom_job_star_rating_comment = '';
                                    var display_edit_custom_job_star_rating_comment = '<i name="edit_custom_star_rating_comment_' + data['idx'] + '" style="display: none; margin-left: 8px; font-size: 17px; cursor: pointer;" class="fa fa-edit"></i>';
                                } else {
                                    var custom_job_star_rating_comment = data['custom_job_star_rating_comment'];
                                    var display_edit_custom_job_star_rating_comment = '<i name="edit_custom_star_rating_comment_' + data['idx'] + '" style="margin-left: 8px; font-size: 17px; cursor: pointer;" class="fa fa-edit"></i>';;
                                }

                                $('td', row).eq(countervariableE).html('<div data-html="true" data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="' + custom_job_star_rating_comment + '"><div name="edit_custom_star_rating_' + data['idx'] + '" class="rateit" data-rateit-value="' + data['rating'] + '" data-rateit-ispreset="true" data-rateit-readonly="false"></div>' + display_edit_custom_job_star_rating_comment + '</div>');
                                $('td', row).eq(countervariableE).addClass('change_rating').addClass('rating');
                                $('td', row).eq(countervariableE).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '').css('font-size', '0.7vw');;
                            }

                            if (i.indexOf('tags') >= 0) {
                                $('td', row).eq(countervariableJ).css('cursor', 'pointer').addClass('change_tags').addClass('tags');
                                $('td', row).eq(countervariableJ).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            if (i.indexOf('job_status') >= 0) {
                                $('td', row).eq(countervariableB).css('cursor', 'default').addClass('change_job_status').addClass('job_status');
                                $('td', row).eq(countervariableB).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '').css('font-size', '0.8vw');
                            }

                            if (i.indexOf('assignees') >= 0) {
                                $('td', row).eq(countervariable).css('cursor', 'pointer').addClass('change_assignees').addClass('assignees');
                                $('td', row).eq(countervariable).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }
                            if (i.indexOf('delivery_time') >= 0) {
                                $('td', row).eq(countervariable2).html('<span style="' + data['color_it'] + '">' + data['delivery_time'] + '</span>');
                                $('td', row).eq(countervariable2).css('cursor', 'pointer').addClass('change_delivery_time').addClass('delivery_time');
                                $('td', row).eq(countervariable2).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            // if (i.indexOf('custom_row_color') >= 0) {
                            //     $('td', row).eq(countervariableA).html('<div id="colorPicker_'+data['case_id']+'" class="colorPicker"><a class="color"><div class="colorInner" style="background-color: '+data['custom_color_rgb']+';"></div></a><div class="track"></div><ul class="dropdown"><li></li></ul><input type="hidden" class="colorInput" value="#'+data['custom_color']+'"></div>');
                            //     $('td', row).eq(countervariableA).addClass('change_custom_row_color').addClass('custom_row_color');
                            //     $('td', row).eq(countervariableA).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            // }
                            
                            if (i.indexOf('created_at') >= 0) {
                                $('td', row).eq(countervariableO).addClass('created_at');
                                $('td', row).eq(countervariableO).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            if (i.indexOf('last_updated_by') >= 0) {
                                if (data['last_updated_by'] == null) {
                                    var last_updated_by = '';
                                } else {
                                    var last_updated_by = data['last_updated_by'];
                                }
                                $('td', row).eq(countervariableF).html('<span style="font-size: 8px;">' + last_updated_by + '</span>');
                                $('td', row).eq(countervariableF).addClass('last_updated_by');
                                $('td', row).eq(countervariableF).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }

                            if (i.indexOf('last_updated') >= 0) {
                                $('td', row).eq(countervariableC).addClass('last_updated');
                                $('td', row).eq(countervariableC).attr('data-case_id', data['case_id']).attr('data-date_ts', data['idx']).addClass('parent').css('padding', '');
                            }
                        });

                        var isSectionAccounting = app.data.section.indexOf("accounting"); //>= 0 if finds //-1 if it does not find
                        var canWRITEaccounting = app.data.auth_user_permissions.indexOf("WRITE accounting"); //>= 0 if finds //-1 if it does not find
                        if (isSectionAccounting >= 0 && canWRITEaccounting >= 0) {
                            /***/
                            /***/
                            /**console.log(app.data.section);*/
                            /**console.log(app.data.auth_user_permissions);*/
                        } else {
                            /***/
                            /***/
                        }


                        //console.log(window.manualdownloadlisttable.row(index).node());

                        // console.log(custom_row_color);
                        function get_luminance(color) {
                            var rgb = color.replace('rgb(', '').replace(')', '').split(',');
                            var r = rgb[0];
                            var g = rgb[1];
                            var b = rgb[2];

                            c = [r / 255, g / 255, b / 255];

                            for (var i = 0; i < c.length; ++i) {
                                if (c[i] <= 0.03928) {
                                    c[i] = c[i] / 12.92
                                } else {
                                    c[i] = Math.pow((c[i] + 0.055) / 1.055, 2.4);
                                };
                            };

                            l = 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];

                            return l;
                        };

                        if (custom_row_color == '') {
                            $('td', row).parent().css('color', '#676A6C');
                        } else {
                            var luminance = get_luminance($('td', row).parent().css('background-color'));
                            /**console.log(luminance);*/
                            luminance > 0.433 ? $('td', row).parent().css('color', '#676A6C') : $('td', row).parent().css('color', '#FFFFFF');
                        }

                        /** if the tool reloads want to be able to highlight the new rows that just appeared within the last five minutes after which the style disappears */
                        /** use the download datetime for that */
                        var jobid_created_at_timestamp = parseInt(data['created_at_timestamp']);
                        var current_time_stamp = parseInt(new Date().getTime()/1000);
                        var difference_between_timestamps = current_time_stamp - jobid_created_at_timestamp;
                        var tr = $('td', row).closest('tr');
                        if(difference_between_timestamps <= 7200){
                            /** the job was created new within last 2 hours */
                            /** style the row */
                            /**console.log(tr);*/
                            $(tr).css('border', '2px solid #f8ac59').css('-moz-box-shadow', 'inset 0 0 20px #f8ac59').css('-webkit-box-shadow', 'inset 0 0 20px #f8ac59').css('box-shadow', 'inset 0 0 20px #f8ac59');
                        }else{
                            $(tr).css('border', '').css('-moz-box-shadow', '').css('-webkit-box-shadow', '').css('box-shadow', '');
                        }
                        /** but then we want it to remove these styles but when? */

                    },
                    drawCallback: function(settings) {
                        //app.data.timesheet_period = null;
                        var api = this.api();
                        var json = api.ajax.json();
                        /** JSON stringifying sorts the keys alphabetically */
                        /** ViewComposer adding CURRENCY key .. after this it is removed. what are the implications? */
                        // var extends_app = JSON.stringify(json.extends_app);
                        // extends_app = extends_app.replace(/\//g, "\\\/");
                        // console.log(json.extends_app);
                        ///** since we are trying new method for changing the timesheet period */
                        // var script = document.createElement('script');
                        // script.type = 'text/javascript';
                        // script.id = 'extends_app';
                        // script.text = 'app.ext(' + extends_app + ');';
                        // var element = document.getElementById("extends_app");
                        // document.getElementById("extends_app").parentNode.replaceChild(script, element);

                        // $('#attendance_status_change_period').val(app.data.timesheet_period.when);

                        /** scroll back to where they were positioned */
                        $(document).ready(function() {
                            var previous_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_edit_manualdownloadlist_scroll_position_data'));
                            if (previous_load !== null) {
                                if (previous_load['scroll'] !== "") {
                                    //console.log("scroll = " + previous_load['scroll']);
                                    var scroll = previous_load['scroll'];
                                    $(document).scrollTop(scroll);
                                }
                                sessionStorage.removeItem('Br24_' + app.env() + '_edit_manualdownloadlist_scroll_position_data');
                            }
                        });


                        $("input[type=search]").focus();


                        if ($('.ms-has-selections')[0]) {
                            $('a[name="clearallfilters"]').css('display', "block");
                        } else {
                            $('a[name="clearallfilters"]').css('display', "none");
                        }

                        var api = this.api();
                        // $.each(app.conf.table.totalColumn.managemanualdownloadlistInfo, function(idx, val) {
                        //     app.util.totalFormat(idx, api, val);
                        // });
                        var refreshTimeout = null;
                        $('[data-toggle="popover"]').popover({
                            placement: 'auto bottom',
                            trigger: "manual",
                            html: true,
                            animation: false
                        }).on("mouseenter", function() {
                            var _this = this;
                            var popover_mouseover_function = function(this_elem) {
                                refreshTimeout = setInterval(function() {
                                    $(this_elem).popover("show");
                                }, 300);
                            };
                            popover_mouseover_function(_this);
                            $(this).siblings(".popover").on("mouseleave", function() {
                                $(_this).popover('hide');
                            });
                        }).on("mouseleave", function() {
                            clearInterval(refreshTimeout);
                            var _this = this;
                            var popover_mouseleave_function = function() {
                                setTimeout(function() {
                                    if (!$(".popover:hover").length) {
                                        $(_this).popover("hide")
                                    } else {
                                        popover_mouseleave_function();
                                    }
                                }, 50);
                            };
                            popover_mouseleave_function();
                        });
                        /** format the popover tooltip display */
                        // $('[data-toggle="popover"]').on('inserted.bs.popover', function() {
                        //     $('.popover').css('border', '0px').css('background-color', 'rgba(255,255,255,0.2)');
                        // });
                        // $('[data-toggle="popover"]').on('shown.bs.popover', function() {
                        //     $('.popover').css('border', '0px').css('background-color', 'rgba(255,255,255,0.2)');
                        // });

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

                        $(".currency_number_format").formatNumber({ format: number_format_per_locale, locale: numberformat_locale });





                        $("div[class*='colorPicker']").tinycolorpicker();





                        var new_value = null;
                        var new_value_id = null;
                        //var new_symbol = null;

                        var success_ajax_then_refresh = null;

                        // $('.dataTable tbody').off('click', 'td.parent.change_job_status').on('click', 'td.parent.change_job_status', function(event) {
                        //     /**console.log('td.parent.change_math_operand');*/

                        //     var target = event.target;
                        //     event.preventDefault();

                        //     var tr = $(this).closest('tr');
                        //     var td = $(this);

                        //     var original_html = $(this).html();
                        //     var row = window.manualdownloadlisttable.row(tr);
                        //     var thecellvalue = $(this).text();

                        //     var rowIndex = tr.data('rowindex');
                        //     var change_status_case_Id = $(this).data('case_id');
                        //     var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                        //     keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_status_case_Id;
                        //     var timestamp_Id = $(this).data('date_ts');

                        //     if ($(td).hasClass('from_amount_op')) {
                        //         keep_track_of_last_clicked_item_to_react_after_reload = 'change_math_operand.from_amount_op';
                        //     }
                        //     if ($(td).hasClass('to_amount_op')) {
                        //         keep_track_of_last_clicked_item_to_react_after_reload = 'change_math_operand.to_amount_op';
                        //     }

                        //     function triggerFocus() {
                        //         var e = jQuery.Event("keyup", { keyCode: 65, which: 65 });
                        //         $("#editrecord_input").focus();
                        //         $("#editrecord_input").attr('value', '');
                        //         $("#editrecord_input").triggerHandler(e);
                        //         $("#editrecord_input").trigger('change');
                        //         $("#editrecord_input").css('cursor', 'default');
                        //     }


                        //     if ($('#editrecord_input').is(":visible") == true) {
                        //         /**console.log('input on other cell is visible');*/
                        //     } else {
                        //         /**Change the cell to a select drop down*/
                        //         var input = $('<input id="editrecord_input" style="color: #000; text-align:center; width: 80px; display: block; margin: 0 auto; z-index: 12;" readonly></input>');
                        //         input.val(thecellvalue);
                        //         td.html(input);
                        //         /**td.css('padding', '0px');*/
                        //         var cell = window.manualdownloadlisttable.cell(this);

                        //         var options = {
                        //             data: app.data.ajax_getAccountingPITMathOperatorSelectOptionList.original,
                        //             /**url: function(phrase) {*/
                        //             /**    return app.data.ajax_getAccountingPITMathOperatorSelectOptionList.original;*/
                        //             /**},*/
                        //             getValue: function(element) {
                        //                 return element.name;
                        //                 /***/
                        //             },
                        //             /**requestDelay: 0,*/
                        //             /**ajaxSettings: {*/
                        //             /**    dataType: "json",*/
                        //             /**    method: "GET",*/
                        //             /**    async: true,*/
                        //             /**    data: {*/
                        //             /**        dataType: "json"*/
                        //             /**    }*/
                        //             /**},*/
                        //             preparePostData: function(data) {
                        //                 /**console.log('preparePostData');*/
                        //                 data.timestamp_Id = timestamp_Id;
                        //                 td.css('background', 'url(../img/loader.gif) 50% 50% no-repeat rgba(0, 0, 0, 0)');
                        //                 $('#editrecord_input').css('opacity', 0);
                        //                 $('.easy-autocomplete-container').css('opacity', 0);
                        //                 $(document.body).addClass('nprogress-busy').css('pointer-events', 'none').css('cursor', 'wait');
                        //                 return data;
                        //             },
                        //             template: {
                        //                 type: "custom",
                        //                 method: function(value, item) {

                        //                     /** want to hide the option that is currently selected only show others */
                        //                     if (item.name.toLowerCase() == thecellvalue) {
                        //                         return;
                        //                     }

                        //                     var include_tooltip = ''
                        //                     /**if (item.name == '&nbsp;') {*/
                        //                     /**    include_tooltip = '';*/
                        //                     /**} else {*/
                        //                     /**    include_tooltip = 'data-html="true" data-container="body" data-toggle="popover" data-placement="left" data-trigger="hover" data-content="' + item.tooltip + '"';*/
                        //                     /**}*/
                        //                     return '<div class="key_box_tsr_bridge"><div class="key_box_tsr_penalty_manage" style="color: #000; border: 1px solid rgba(169, 169, 169, 0.5);"' + include_tooltip + '><span class="">' + item.name + '</span></div></div>';
                        //                 }
                        //             },
                        //             highlightPhrase: false,
                        //             list: {
                        //                 maxNumberOfElements: 100,
                        //                 match: {
                        //                     enabled: false
                        //                 },
                        //                 onShowListEvent: function() {
                        //                     /**console.log('onShowListEvent');*/
                        //                     td.css('background', '');
                        //                     $('#editrecord_input').css('opacity', 1);
                        //                     $('.easy-autocomplete-container').css('opacity', 1);
                        //                     $(document.body).removeClass('nprogress-busy').css('pointer-events', 'auto').css('cursor', 'default');
                        //                     var refreshTimeout = null;
                        //                     $('[data-toggle="popover"]').popover({
                        //                         placement: 'auto bottom',
                        //                         trigger: "manual",
                        //                         html: true,
                        //                         animation: false
                        //                     }).on("mouseenter", function() {
                        //                         var _this = this;
                        //                         var popover_mouseover_function = function(this_elem) {
                        //                             refreshTimeout = setInterval(function() {
                        //                                 $(this_elem).popover("show");
                        //                             }, 300);
                        //                         };
                        //                         popover_mouseover_function(_this);
                        //                         $(this).siblings(".popover").on("mouseleave", function() {
                        //                             $(_this).popover('hide');
                        //                         });
                        //                     }).on("mouseleave", function() {
                        //                         clearInterval(refreshTimeout);
                        //                         var _this = this;
                        //                         var popover_mouseleave_function = function() {
                        //                             setTimeout(function() {
                        //                                 if (!$(".popover:hover").length) {
                        //                                     $(_this).popover("hide")
                        //                                 } else {
                        //                                     popover_mouseleave_function();
                        //                                 }
                        //                             }, 50);
                        //                         };
                        //                         popover_mouseleave_function();
                        //                     });
                        //                     $('.easy-autocomplete').css('width', '');
                        //                 },
                        //                 onLoadEvent: function() {
                        //                     /**console.log('onLoadEvent');*/
                        //                     /**$('.loader').css('display', 'block').css('z-index', '10').css('background', 'url(../img/blank.gif) 50% 50% no-repeat rgb(249, 249, 249, 0.5)');*/
                        //                     $(".popover").popover('hide');

                        //                     /**console.log('=========================================================================================== LOAD EVENT ==================================');*/
                        //                     /**console.log('keep_track_of_last_clicked_item_to_react_after_reload');*/
                        //                     /**console.log(keep_track_of_last_clicked_item_to_react_after_reload);*/
                        //                     /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload');*/
                        //                     /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload);*/
                        //                     /**console.log('keep_track_of_last_clicked_easyautocomplete_to_reopen');*/
                        //                     /**console.log(keep_track_of_last_clicked_easyautocomplete_to_reopen);*/
                        //                     /**console.log('keep_track_of_last_clicked_item_to_react_after_reload_2');*/
                        //                     /**console.log(keep_track_of_last_clicked_item_to_react_after_reload_2);*/
                        //                     /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2');*/
                        //                     /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2);*/
                        //                     /**console.log('re_open_requested');*/
                        //                     /**console.log(re_open_requested);*/
                        //                     /**console.log('=========================================================================================== LOAD EVENT ==================================');*/

                        //                     clearInterval(reopen_refreshIntervalId); //stop the timer called reopen_refreshIntervalId
                        //                 },
                        //                 onClickEvent: function(event) {
                        //                     /**console.log('onClickEvent');*/
                        //                     /**event.preventDefault();*/

                        //                     /** we ask for confirmation */
                        //                     /** when we selec the new shift we have to perform a function via ajax */

                        //                     new_value_id = $("#editrecord_input").getSelectedItemData().id;
                        //                     new_value = $("#editrecord_input").getSelectedItemData().name;
                        //                     /**new_symbol = $("#editrecord_input").getSelectedItemData().symbol;*/
                        //                     /**console.log('thecellvalue=' + thecellvalue);*/
                        //                     /**console.log('new_value_id=' + new_value_id);*/
                        //                     /**console.log('new_value=' + new_value);*/
                        //                 },
                        //                 onChooseEvent: function() {
                        //                     /**console.log('onChooseEvent');*/
                        //                     /***/
                        //                 },
                        //                 onHideListEvent: function(event) {
                        //                     /**console.log('onHideListEvent');*/
                        //                     /**event.preventDefault();*/

                        //                     /**clicking fills these variables so it will always be not null*/

                        //                     /**console.log('success_ajax_then_refresh='+success_ajax_then_refresh);*/
                        //                     /**console.log(new_value);*/

                        //                     $(document.body).removeClass('nprogress-busy').css('pointer-events', 'auto');
                        //                     $(".popover").popover('hide');

                        //                     if (new_value == null) {
                        //                         $("#editrecord_input").remove();
                        //                         cell.data(original_html);
                        //                         return;
                        //                     }


                        //                     if (new_value != null) {
                        //                         /**$("#editrecord_input").remove();*/
                        //                         $(".easy-autocomplete-container").remove();
                        //                         /** cell.data(new_value.toLowerCase());*/
                        //                         /** we store the new status to the db */

                        //                         $.confirm({
                        //                             title: eval("app.translations." + app.data.locale + ".title_text"),
                        //                             content: 'Change the Job Status from ' + thecellvalue + ' to ' + new_value + ' ?',
                        //                             type: 'red',
                        //                             draggable: true,
                        //                             dragWindowGap: 0,
                        //                             backgroundDismiss: 'cancel',
                        //                             escapeKey: true,
                        //                             animateFromElement: false,
                        //                             autoClose: 'ok|50',
                        //                             onAction: function(btnName) {
                        //                                 $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                        //                             },
                        //                             buttons: {
                        //                                 ok: {
                        //                                     btnClass: 'btn-primary text-white',
                        //                                     keys: ['enter'],
                        //                                     text: eval("app.translations." + app.data.locale + ".okay_text"),
                        //                                     action: function() {
                        //                                         $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                        //                                         var data = {
                        //                                             'case_id': change_status_case_Id,
                        //                                             'encrypted_case_id': encrypted_case_id_uploading_to,
                        //                                             'new_status': new_value.toLowerCase(),
                        //                                             'new_status_id': new_value_id
                        //                                         };

                        //                                         app.ajax.json(app.data.change_status_for_job, data, null, function() {
                        //                                             /**console.log(app.ajax.result);*/
                        //                                             success_ajax_then_refresh = app.ajax.result.success;
                        //                                             if (app.ajax.result.success == true) {
                        //                                                 $("#editrecord_input").remove();
                        //                                                 $(".easy-autocomplete-container").remove();
                        //                                                 cell.data(new_value);
                        //                                                 $('.loader').css('display', 'none').css('cursor', 'auto').css('background', 'url(../img/loader.gif) 50% 50% no-repeat rgb(249, 249, 249, 0.5)');

                        //                                                 /** console.log(tr.children().next('.last_updated'));*/
                        //                                                 /** console.log(app.ajax.result.updated_at);*/
                        //                                                 tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                        //                                                 tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                        //                                                 /** since they want to have the in progress all together wwe kind of need to reload ajax table now */
                        //                                                 window.manualdownloadlisttable.ajax.reload(null, false);
                        //                                                 window.manualdownloadlisttable.fixedHeader.adjust();
                        //                                             } else {
                        //                                                 /** we don't change anything put back to what it was and alert  */
                        //                                                 $.alert({
                        //                                                     title: 'Alert!',
                        //                                                     content: 'Job Status not changed',
                        //                                                 });
                        //                                                 $("#editrecord_input").remove();
                        //                                                 cell.data(original_html);
                        //                                                 $('.loader').css('display', 'none').css('cursor', 'auto').css('background', 'url(../img/loader.gif) 50% 50% no-repeat rgb(249, 249, 249, 0.5)');
                        //                                                 new_value = null;
                        //                                             }
                        //                                         });
                        //                                     }
                        //                                 },
                        //                                 cancel: {
                        //                                     text: eval("app.translations." + app.data.locale + ".cancel_text"),
                        //                                     action: function() {
                        //                                         $("#editrecord_input").remove();
                        //                                         cell.data(original_html);
                        //                                         $('.loader').css('display', 'none').css('cursor', 'auto').css('background', 'url(../img/loader.gif) 50% 50% no-repeat rgb(249, 249, 249, 0.5)');
                        //                                         new_value = null;
                        //                                     }
                        //                                 },
                        //                             }
                        //                         });
                        //                     }
                        //                 },
                        //                 showAnimation: {
                        //                     type: "fade", /**normal|slide|fade*/
                        //                     time: 1,
                        //                     callback: function() {
                        //                         /**console.log('finished showing');*/
                        //                     }
                        //                 },
                        //                 hideAnimation: {
                        //                     type: "fade", /**normal|slide|fade*/
                        //                     time: 1,
                        //                     callback: function() {


                        //                         if (keep_track_of_last_clicked_item_to_react_after_reload_2 != null && keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2 != null) {
                        //                             if (keep_track_of_last_clicked_easyautocomplete_to_reopen == true) {
                        //                                 /**console.log('$$$$$$$$$$$$$$$$$$$ RE OPEN REQUESTED $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$ RE OPEN REQUESTED $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');*/
                        //                                 re_open_requested = true;
                        //                                 reopen_timer();
                        //                             }
                        //                         }

                        //                         /**console.log('=========================================================================================== CLOSE CALLBACK EVENT ==================================');*/
                        //                         /**console.log('keep_track_of_last_clicked_item_to_react_after_reload');*/
                        //                         /**console.log(keep_track_of_last_clicked_item_to_react_after_reload);*/
                        //                         /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload');*/
                        //                         /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload);*/
                        //                         /**console.log('keep_track_of_last_clicked_easyautocomplete_to_reopen');*/
                        //                         /**console.log(keep_track_of_last_clicked_easyautocomplete_to_reopen);*/
                        //                         /**console.log('keep_track_of_last_clicked_item_to_react_after_reload_2');*/
                        //                         /**console.log(keep_track_of_last_clicked_item_to_react_after_reload_2);*/
                        //                         /**console.log('keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2');*/
                        //                         /**console.log(keep_track_of_last_clicked_item_data_attribute_to_react_after_reload_2);*/
                        //                         /**console.log('re_open_requested');*/
                        //                         /**console.log(re_open_requested);*/
                        //                         /**console.log('=========================================================================================== CLOSE CALLBACK EVENT ==================================');*/
                        //                     }
                        //                 }
                        //             },
                        //         };
                        //         //console.log(JSON.stringify(options));
                        //         $("#editrecord_input").easyAutocomplete(options).css('z-index', '12');

                        //         /** to open the select box */
                        //         triggerFocus();
                        //     }
                        // });

                        // $('.dataTable tbody').off('click', 'td.parent.change_assignees').on('click', 'td.parent.change_assignees', function(event) {
                        //     // console.log('td.parent.change_assignee');
                        //     if ($('.is_br24_employee').is(":visible") == true) {
                        //         return false;
                        //     }

                        //     keep_track_of_last_clicked_item_to_react_after_reload = 'change_assignee';
                        //     var target = event.target;
                        //     event.preventDefault();

                        //     var tr = $(this).closest('tr');
                        //     var td = $(this);
                        //     var change_assignee_original_html = $(this).html();
                        //     // console.log('change_assignee_original_html');
                        //     // console.log(change_assignee_original_html);
                        //     var row = window.manualdownloadlisttable.row(tr);
                        //     var thecellvalue = $(this).text();
                        //     // console.log('thecellvalue');
                        //     // console.log(thecellvalue);
                        //     //var unformatted_number = $(this).parseNumber({ format: number_format_per_locale, locale: numberformat_locale });
                        //     /** console.log(unformatted_number); */
                        //     var rowIndex = tr.data('rowindex');
                        //     var change_assignee_case_Id = $(this).data('case_id');
                        //     var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                        //     /**console.log(encrypted_case_id_uploading_to);*/

                        //     keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_assignee_case_Id;
                        //     /**console.log(change_assignee_case_Id);*/
                        //     var timestamp_Id = $(this).data('date_ts');
                        //     // var idx = table.cell(this).index().column;
                        //     // var date_number = table.column(idx).header();
                        //     // var clcikedcolumnheader_value = $(date_number).html();
                        //     //var input = $('<input id="edit_assignees_input_' + change_assignee_case_Id + '" type="number" style="text-align:center; width: 100px; display: block; margin: 0 auto; z-index: 12;" min="0" max="2147483647" step="1" pattern="^\\d{1,10}?$"></input>');

                        //     var input = $('<select style="width: 100%; height: 46px;" class="is_br24_employee form-control" title="" multiple></select>');
                        //     /** I want this input to be looking like the selectize one */

                        //     //input.val(unformatted_number);
                        //     td.html(input);


                        //     /**console.log('ready');*/
                        //     var select_list_employee_list = app.data.selectize_employee_list_formated_json;
                        //     /**console.log('select_list_employee_list=' + JSON.stringify(select_list_employee_list));*/
                        //     var select_selected_list_employee_has_family_member_json = app.data.selectize_selected_employee_has_family_members_in_company_formated_json;
                        //     /**console.log('select_selected_list_employee_has_family_member_json=' + JSON.stringify(select_selected_list_employee_has_family_member_json));*/

                        //     var select_selected_list_employee_has_family_member = select_selected_list_employee_has_family_member_json.map(function(item) {
                        //         return item['username'];
                        //     });

                        //     /**console.log('AFTERselect_selected_list_employee_has_family_member=' + select_selected_list_employee_has_family_member);*/

                        //     /** if there are any that are any details we can use to populate the selectize */
                        //     if (change_assignee_original_html != '') {
                        //         select_selected_list_employee_has_family_member = change_assignee_original_html.split(" ");
                        //         Object.assign({}, [select_selected_list_employee_has_family_member]);
                        //     }
                        //     /**console.log('FROMHTMLselect_selected_list_employee_has_family_member=' + select_selected_list_employee_has_family_member);*/


                        //     var assignee_ids = null;
                        //     var encrypted_assignee_ids = null;

                        //     if (!window.Selectize.prototype.positionDropdownOriginal) {
                        //         window.Selectize.prototype.positionDropdownOriginal = window.Selectize.prototype.positionDropdown;
                        //         window.Selectize.prototype.positionDropdown = function() {
                        //             if (this.settings.dropdownDirection === 'up') {
                        //                 let $control = this.$control;
                        //                 let offset = this.settings.dropdownParent === 'body' ? $control.offset() : $control.position();

                        //                 var the_td = $control.parent().parent();
                        //                 var the_td_offset = the_td.offset();
                        //                 var position_relative_to_viewport = parseInt(the_td_offset.top) - parseInt($(window).scrollTop());

                        //                 var switch_to_dropdown = false;
                        //                 if (position_relative_to_viewport <= 300) {
                        //                     var switch_to_dropdown = true;
                        //                 }

                        //                 var height_of_drowdown = 261;
                        //                 if (switch_to_dropdown) {
                        //                     var height_of_td = the_td.height() - 12;
                        //                     height_of_drowdown = -parseInt(height_of_td);
                        //                 }

                        //                 this.$dropdown.css({
                        //                     width: $control.outerWidth(),
                        //                     top: offset.top - height_of_drowdown,
                        //                     left: offset.left,
                        //                 });

                        //                 this.$dropdown.addClass('direction-' + this.settings.dropdownDirection);
                        //                 this.$control.addClass('direction-' + this.settings.dropdownDirection);
                        //                 this.$wrapper.addClass('direction-' + this.settings.dropdownDirection);
                        //             } else {
                        //                 window.Selectize.prototype.positionDropdownOriginal.apply(this, arguments);
                        //             }
                        //         };
                        //     }

                        //     var $is_br24_employee_select = $('.is_br24_employee').selectize({
                        //         plugins: ['remove_button', 'optgroup_columns'],
                        //         persist: false,
                        //         maxItems: 200,
                        //         mode: 'multi',
                        //         dropdownDirection: 'up',
                        //         /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                        //         placeholder: '-- Choose Job Assignee(s) --',
                        //         valueField: ['username'],
                        //         labelField: 'username',
                        //         searchField: ['username', 'fullname', 'fullname_noaccents'],
                        //         options: select_list_employee_list,
                        //         /** list of all the viable employees on init */
                        //         items: select_selected_list_employee_has_family_member,
                        //         /** list of already selected employees on init */
                        //         hideSelected: true,
                        //         openOnFocus: true,
                        //         closeAfterSelect: true,
                        //         render: {
                        //             item: function(item, escape) {
                        //                 return '<div>' +
                        //                     (item.username ? '<span class="username"><u><b>' + item.username + '</b></u></span>' : '') +
                        //                     //'<span style="color: #ccc">&nbsp;</span>' +
                        //                     //(item.xml_jobid_title ? ' <i class="fa fa-angle-double-right text-danger"></i> ' : '') +
                        //                     //(item.xml_jobid_title ? '<span class="xml_jobid_title" style="font-size: 9px; color: #1cd;"><b>' + item.xml_jobid_title + '</b></span>' : '') +
                        //                     //'<br>' +
                        //                     //(item.xml_title_contents ? '<span class="xml_title_contents" style="font-size: 10px;">' + item.xml_title_contents + '</span>' : '') +
                        //                     //'<br>' +
                        //                     //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                        //                     '</div>';
                        //             },
                        //             option: function(item, escape) {
                        //                 var label = item.xml_title_contents || item.email;
                        //                 var caption = item.xml_title_contents ? item.email : null;
                        //                 return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                        //                     '<span class="label label-primary">' + item.username + '</span>' +
                        //                     //'<span style="color: #ccc">&nbsp;</span>' +

                        //                     //(item.fullname ? ' <i class="fa fa-angle-double-right text-danger"></i> ' : '') +
                        //                     //(item.fullname ? '<span class="fullname" style="font-size: 9px; color: #1cd;"><b>' + item.fullname + '</b></span>' : '') +
                        //                     //'<br>' +
                        //                     //(item.xml_title_contents ? '<span class="xml_title_contents" style="font-size: 10px;">' + item.xml_title_contents + '</span>' : '') +
                        //                     //(item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                        //                     //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                        //                     '</div>';
                        //             }
                        //         },
                        //         onChange: function(value) {
                        //             //$('#cb_add_family_members_details_form').addClass('dirty');
                        //             /** when it changes i want to update the variable so that it can be loaded together with the files */
                        //             /**console.log($('.is_br24_employee').val());*/
                        //             assignee_ids = $('.is_br24_employee').val();

                        //             /***/
                        //             $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                        //             var data = {
                        //                 'case_id': change_assignee_case_Id,
                        //                 'encrypted_case_id': encrypted_case_id_uploading_to,
                        //                 'assignees': assignee_ids
                        //             };

                        //             /** use ajax to send data to php */
                        //             app.ajax.json(app.data.change_assignees_for_job, data, null, function() {
                        //                 /**console.log(app.ajax.result);*/
                        //                 success_ajax_then_refresh = app.ajax.result.success;
                        //                 if (app.ajax.result.success == true) {


                        //                     /** what happens if the preview required checkbox is already checkedmarked and assignees are set to blank? */
                        //                     /** need to update the column in the db for preview required .. */
                        //                     /** check if the new assignes td is not blank and enable the tr preview required checkbox */
                        //                     /**.change_preview_col*/
                        //                     var checkbox_in_td = $("input[name='edit_" + change_assignee_case_Id + "']");
                        //                     if (checkbox_in_td.is(':checked')) {
                        //                         status = 2;
                        //                         /**console.log(status);*/
                        //                         /** we go in via ajax to amend the status column where user_id and date on accept reject penalty table */
                        //                         app_ops.manage_manualdownloadlist.profile.sync_preview_required_status(change_assignee_case_Id, encrypted_case_id_uploading_to, status, tr, checkbox_in_td, 'NOT NULL');
                        //                     }
                        //                     if (assignee_ids == '') {
                        //                         checkbox_in_td.prop("disabled", true);
                        //                         checkbox_in_td.prop("checked", false);
                        //                     } else {
                        //                         checkbox_in_td.prop("disabled", false);
                        //                     }

                        //                     // console.log(tr.children().next('.last_updated'));
                        //                     // console.log(app.ajax.result.updated_at);
                        //                     tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                        //                     tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                        //                 } else {

                        //                     // $.each(app.ajax.result.errors, function(idx, val) {
                        //                     //     $.alert(val);
                        //                     // });
                        //                 }
                        //             });

                        //         }
                        //     });

                        //     var is_br24_employee_select_selectize = $is_br24_employee_select[0].selectize;
                        //     var is_br24_employee_select_old_options = is_br24_employee_select_selectize.settings;
                        //     var selectize_focus_handler = function(value, $item) {
                        //         var width_to_be = $('.selectize-control').outerWidth();
                        //         var height_to_be = 600;
                        //         $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                        //     };
                        //     is_br24_employee_select_selectize.on('focus', selectize_focus_handler);


                        //     var selectize_blur_handler = function(value, $item) {
                        //         // var width_to_be = $('.selectize-control').outerWidth();
                        //         // var height_to_be = 600;


                        //         //$('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                        //         var this_td_new_content_formated = '';

                        //         assignee_ids = $('.is_br24_employee').val();
                        //         /**console.log(assignee_ids);*/
                        //         /**console.log(change_assignee_original_html);*/
                        //         if (assignee_ids === undefined || assignee_ids.length == 0) {
                        //             td.html('');
                        //         } else {
                        //             /**console.log('blur');*/
                        //             $.each(assignee_ids, function(idx, val) {
                        //                 // console.log(idx);
                        //                 // console.log(val);
                        //                 if (this_td_new_content_formated == '') {
                        //                     this_td_new_content_formated = val;
                        //                 } else {
                        //                     this_td_new_content_formated = this_td_new_content_formated + ' ' + val;
                        //                 }
                        //             });
                        //             td.html(this_td_new_content_formated);
                        //         }

                        //         /** otherwise return it to what it was before */

                        //         /** want to be able to save the information on the database so that it can be called back when the page reloads */
                        //         /** also don't want to reload the page want to have the selected options become the td content */
                        //         /** and if there is content to get the selectize automatically load those ones on click */
                        //         /** */

                        //         /** should do it on blur as well or on change to so that as soon as they click its on the database already. */
                        //     };
                        //     is_br24_employee_select_selectize.on('blur', selectize_blur_handler);

                        //     /** filter the shifts from the swapable shifts select */
                        //     var item_remove_handler = function(value, $item) {
                        //         /**console.log('item_removed');*/
                        //         assignee_ids = $('.is_br24_employee').val();
                        //     };
                        //     is_br24_employee_select_selectize.on('item_remove', item_remove_handler);

                        //     $(window).resize(function() {
                        //         var width_to_be = $('.selectize-control').outerWidth();
                        //         var height_to_be = 600;
                        //         $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                        //     });

                        //     is_br24_employee_select_selectize.focus();
                        // });

                        // $('.dataTable tbody').off('click', 'td.parent.change_delivery_time').on('click', 'td.parent.change_delivery_time', function(event) {
                        //     /**console.log('td.parent.change_delivery_time');*/
                        //     if ($('.is_deliver_time').is(":visible") == true) {
                        //         return false;
                        //     }

                        //     keep_track_of_last_clicked_item_to_react_after_reload = 'change_delivery_time';
                        //     var target = event.target;
                        //     event.preventDefault();

                        //     var tr = $(this).closest('tr');
                        //     var td = $(this);
                        //     var change_assignee_original_html = $(this).html();
                        //     // console.log('change_assignee_original_html');
                        //     // console.log(change_assignee_original_html);
                        //     var row = window.manualdownloadlisttable.row(tr);
                        //     var thecellvalue = $(this).text();
                        //     //console.log('thecellvalue');
                        //     //console.log(thecellvalue);
                        //     //var unformatted_number = $(this).parseNumber({ format: number_format_per_locale, locale: numberformat_locale });
                        //     /** console.log(unformatted_number); */
                        //     var rowIndex = tr.data('rowindex');
                        //     var change_assignee_case_Id = $(this).data('case_id');
                        //     var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                        //     keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_assignee_case_Id;
                        //     /**console.log(change_assignee_case_Id);*/
                        //     var timestamp_Id = $(this).data('date_ts');
                        //     // var idx = table.cell(this).index().column;
                        //     // var date_number = table.column(idx).header();
                        //     // var clcikedcolumnheader_value = $(date_number).html();
                        //     //var input = $('<input id="edit_assignees_input_' + change_assignee_case_Id + '" type="number" style="text-align:center; width: 100px; display: block; margin: 0 auto; z-index: 12;" min="0" max="2147483647" step="1" pattern="^\\d{1,10}?$"></input>');

                        //     //var input = $('<select style="width: 100%" class="is_br24_employee form-control" title="" multiple></select>');
                        //     var input = $('<input type="text" style="width: 100%; height:46px; color: #000; text-align: center" class="is_deliver_time" data-field="datetime" value="' + thecellvalue + '" readonly><div id="dtBox"></div>');
                        //     /** I want this input to be looking like the selectize one */

                        //     //input.val(unformatted_number);
                        //     td.html(input);



                        //     var custom_delivery_time = null;
                        //     var encrypted_assignee_ids = null;

                        //     /** we select the time when the set button is clicked */
                        //     /** if we cancel it goes back to the way it was before */
                        //     /** we unload the input from the td where possible */
                        //     /** */



                        //     var currentText = null;
                        //     var closeText = null;
                        //     var amNames = null;
                        //     var pmNames = null;
                        //     var timeFormat = null;
                        //     var timeSuffix = null;
                        //     var timeOnlyTitle = null;
                        //     var timeText = null;
                        //     var hourText = null;
                        //     var minuteText = null;
                        //     var secondText = null;
                        //     var millisecText = null;
                        //     var microsecText = null;
                        //     var timezoneText = null;
                        //     var isRTL = null;

                        //     if (app.data.locale === 'vi') {
                        //         currentText = 'Hiện thời';
                        //         closeText = 'Đóng';
                        //         amNames = ['SA', 'S'];
                        //         pmNames = ['CH', 'C'];
                        //         timeFormat = 'HH:mm';
                        //         timeSuffix = '';
                        //         timeOnlyTitle = 'Chọn giờ';
                        //         timeText = 'Thời gian';
                        //         hourText = 'Giờ';
                        //         minuteText = 'Phút';
                        //         secondText = 'Giây';
                        //         millisecText = 'Mili giây';
                        //         microsecText = 'Micrô giây';
                        //         timezoneText = 'Múi giờ';
                        //         isRTL = false;
                        //     } else if (app.data.locale === 'en') {
                        //         currentText = 'Now';
                        //         closeText = 'Done';
                        //         amNames = ['AM', 'A'];
                        //         pmNames = ['PM', 'P'];
                        //         timeFormat = 'HH:mm';
                        //         timeSuffix = '';
                        //         timeOnlyTitle = 'Choose Time';
                        //         timeText = 'Time';
                        //         hourText = 'Hour';
                        //         minuteText = 'Minute';
                        //         secondText = 'Second';
                        //         millisecText = 'Millisecond';
                        //         microsecText = 'Microsecond';
                        //         timezoneText = 'Time Zone';
                        //         isRTL = false;
                        //     } else if (app.data.locale === 'de') {
                        //         currentText = 'Jetzt';
                        //         closeText = 'Fertig';
                        //         amNames = ['vorm.', 'AM', 'A'];
                        //         pmNames = ['nachm.', 'PM', 'P'];
                        //         timeFormat = 'HH:mm';
                        //         timeSuffix = '';
                        //         timeOnlyTitle = 'Zeit wählen';
                        //         timeText = 'Zeit';
                        //         hourText = 'Stunde';
                        //         minuteText = 'Minute';
                        //         secondText = 'Sekunde';
                        //         millisecText = 'Millisekunde';
                        //         microsecText = 'Mikrosekunde';
                        //         timezoneText = 'Zeitzone';
                        //         isRTL = false;
                        //     }

                        //     var has_been_edited = null;
                        //     $('.is_deliver_time').datetimepicker({
                        //         currentText: currentText,
                        //         closeText: closeText,
                        //         amNames: amNames,
                        //         pmNames: pmNames,
                        //         timeFormat: timeFormat,
                        //         timeSuffix: timeSuffix,
                        //         timeOnlyTitle: timeOnlyTitle,
                        //         timeText: timeText,
                        //         hourText: hourText,
                        //         minuteText: minuteText,
                        //         secondText: secondText,
                        //         millisecText: millisecText,
                        //         microsecText: microsecText,
                        //         timezoneText: timezoneText,
                        //         isRTL: isRTL,
                        //         dateFormat: prefered_dateFormat,
                        //         showMonthAfterYear: true,
                        //         numberOfMonths: 1,
                        //         showCurrentAtPos: 0,
                        //         changeMonth: true,
                        //         changeYear: true,
                        //         yearRange: "-1:+1",
                        //         showOtherMonths: false,
                        //         selectOtherMonths: false,
                        //         toggleActive: true,
                        //         todayHighlight: true,
                        //         showMinute: false,
                        //         //minDate: new Date(minD_YYYY, minD_MM, minD_DD),
                        //         //maxDate: new Date(maxD_YYYY, maxD_MM, maxD_DD),
                        //         autoclose: true,
                        //         defaultDate: defaultDateVARIABLE,
                        //         onSelect: function() {
                        //             /**console.log('onSelect');*/
                        //             has_been_edited = true;
                        //         },
                        //         onClose: function() {
                        //             /**console.log('onClose');*/

                        //             if (has_been_edited == true) {


                        //                 /** we offer to ask them if they want to save or not */

                        //                 $.confirm({
                        //                     title: eval("app.translations." + app.data.locale + ".title_text"),
                        //                     content: eval("app.translations." + app.data.locale + ".you_have_unsaved_changes") + '\n' + eval("app.translations." + app.data.locale + ".do_you_want_to_save_those_changes") + '\n',
                        //                     type: 'red',
                        //                     draggable: false,
                        //                     backgroundDismiss: 'cancel',
                        //                     escapeKey: true,
                        //                     animateFromElement: false,
                        //                     onAction: function(btnName) {
                        //                         $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                        //                     },
                        //                     buttons: {
                        //                         ok: {
                        //                             btnClass: 'btn-primary text-white',
                        //                             keys: ['enter'],
                        //                             text: eval("app.translations." + app.data.locale + ".okay_text"),
                        //                             action: function() {
                        //                                 event.stopPropagation();
                        //                                 /**you need to format the date before sending */
                        //                                 /** from locale to expected YYYY-MM-DD */
                        //                                 var theselecteddatetime = $('.is_deliver_time').val();
                        //                                 /**console.log(theselecteddatetime);*/
                        //                                 var datetime_split = theselecteddatetime.split(" ");
                        //                                 /**console.log(datetime_split);*/

                        //                                 var from = datetime_split[0].split(delimiter_for_splitting_variable);
                        //                                 /**console.log(from);*/
                        //                                 var from_time = datetime_split[1].split(':');
                        //                                 /**console.log(from_time);*/
                        //                                 /** because we are dealing with the date and the time we have to take this into consideration before chopping it up and sending it to the db*/

                        //                                 var datetogoto = null;
                        //                                 var default_dateYYY = null;
                        //                                 var default_dateMM = null;
                        //                                 var default_dateDD = null;
                        //                                 if (app.data.locale === 'vi') {
                        //                                     var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]) + ' ' + from_time[0] + ':' + from_time[1];
                        //                                     // default_dateYYY = parseInt(from[2]);
                        //                                     // default_dateMM = parseInt(from[1]);
                        //                                     // default_dateDD = parseInt(from[0]);
                        //                                 } else if (app.data.locale === 'en') {
                        //                                     var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]) + ' ' + from_time[0] + ':' + from_time[1];
                        //                                     // default_dateYYY = parseInt(from[2]);
                        //                                     // default_dateMM = parseInt(from[1]);
                        //                                     // default_dateDD = parseInt(from[0]);
                        //                                 } else if (app.data.locale === 'de') {
                        //                                     var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]) + ' ' + from_time[0] + ':' + from_time[1];
                        //                                     // default_dateYYY = parseInt(from[2]);
                        //                                     // default_dateMM = parseInt(from[1]);
                        //                                     // default_dateDD = parseInt(from[0]);
                        //                                 }
                        //                                 /**console.log(datetogoto);*/

                        //                                 $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                        //                                 var data = {
                        //                                     'case_id': change_assignee_case_Id,
                        //                                     'encrypted_case_id': encrypted_case_id_uploading_to,
                        //                                     'new_delivery_datetime': datetogoto
                        //                                 };

                        //                                 /** use ajax to send data to php */
                        //                                 app.ajax.json(app.data.change_deliverydate_for_job, data, null, function() {
                        //                                     /**console.log(app.ajax.result);*/
                        //                                     success_ajax_then_refresh = app.ajax.result.success;
                        //                                     if (app.ajax.result.success == true) {
                        //                                         td.html(theselecteddatetime);

                        //                                         // console.log(tr.children().next('.last_updated'));
                        //                                         // console.log(app.ajax.result.updated_at);
                        //                                         tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                        //                                         tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                        //                                         /** because this might adjust the position relative to the other job ids */
                        //                                         window.manualdownloadlisttable.ajax.reload(null, false);
                        //                                         window.manualdownloadlisttable.fixedHeader.adjust();

                        //                                     } else {
                        //                                         td.html(change_assignee_original_html);
                        //                                     }
                        //                                 });
                        //                             }
                        //                         },
                        //                         cancel: {
                        //                             text: eval("app.translations." + app.data.locale + ".cancel_text"),
                        //                             action: function() {
                        //                                 //$.alert('');
                        //                                 // console.log('everything stays the same');
                        //                                 // because probably the user wants to fix the problems on the page and submit again.. 
                        //                                 td.html(change_assignee_original_html);
                        //                                 return false;
                        //                             }
                        //                         },
                        //                     }
                        //                 });


                        //             } else {
                        //                 /** we revert the td back to the previous values without saving to db */
                        //                 td.html(change_assignee_original_html);
                        //             }
                        //         }
                        //     });


                        //     $(window).resize(function() {
                        //         $('.is_deliver_time').css('width', '100%').css('height', '100%');
                        //     });

                        //     $(".is_deliver_time").focus();
                        // });

                        // $('.dataTable tbody').off('click', 'td.parent.change_custom_row_color').on('click', 'td.parent.change_custom_row_color', function(event) {
                        //     /**console.log('td.parent.change_custom_row_color');*/
                        //     /** need to loose focus from the other changeable tds */
                        //     $("#editrecord_input").blur();
                        //     $('.is_br24_employee').selectize().blur();

                        //     /** it seems to be sending many trips to the db how to prevent that? */


                        //     var target = event.target;
                        //     event.preventDefault();

                        //     /**if another  color picker is open close that one first before opening .... */

                        //     var tr = $(this).closest('tr');
                        //     var td = $(this);

                        //     /**console.log(td);*/

                        //     var original_html = $(this).html();
                        //     var row = window.manualdownloadlisttable.row(tr);
                        //     var thecellvalue = $(this).text();
                        //     //console.log(thecellvalue);
                        //     var rowIndex = tr.data('rowindex');
                        //     var change_custom_colour_case_Id = $(this).data('case_id');
                        //     var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                        //     keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_custom_colour_case_Id;
                        //     var custom_delivery_time = null;
                        //     var encrypted_assignee_ids = null;

                        //     var timestamp_Id = $(this).data('date_ts');
                        //     // var idx = table.cell(this).index().column;
                        //     // var date_number = table.column(idx).header();
                        //     // var clcikedcolumnheader_value = $(date_number).html();

                        //     //var $box = $("#colorPicker_");
                        //     var $box = $("#colorPicker_" + change_custom_colour_case_Id);
                        //     $box.tinycolorpicker();
                        //     var box = $box.data("plugin_tinycolorpicker");
                        //     $hiddencolorinput = $box.find(".colorInput");
                        //     /**console.log($hiddencolorinput.val());*/

                        //     /** only on mouse up  they could be holding the button down */

                        //     $box.off('change').on("change", function() {
                        //         //console.log("do something when a new color is set");
                        //         /** we change the row color and go into the db and save the color hex to the table */
                        //         /**console.log(box.colorRGB);*/

                        //         /** go into the db and save the value */

                        //         * use ajax to send data to php 
                        //         /** only do if the orig was not white and white is clicked */
                        //         /** if orig was white and new is white don't do either */
                        //         if (box.colorHex == '#FFFFFF' && $hiddencolorinput.val() == '#') {
                        //             /**don't do */
                        //         } else {
                        //             tr.css('background-color', box.colorRGB);
                        //             tr.autotextcolor();
                        //         }
                        //     });
                        //     $box.one().on("mouseup", function() {
                        //         /**console.log('mouseup');*/
                        //         /** can we get it do only once on mouse up */
                        //         if (box.colorHex == '#FFFFFF' && $hiddencolorinput.val() == '#') {
                        //             /**don't do */
                        //         } else {

                        //             $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                        //             var data = {
                        //                 'case_id': change_custom_colour_case_Id,
                        //                 'encrypted_case_id': encrypted_case_id_uploading_to,
                        //                 'new_custom_color': box.colorHex
                        //             };

                        //             app.ajax.json(app.data.change_custom_color_for_job, data, null, function() {
                        //                 /**console.log(app.ajax.result);*/
                        //                 success_ajax_then_refresh = app.ajax.result.success;
                        //                 if (app.ajax.result.success == true) {
                        //                     tr.css('background-color', box.colorRGB);
                        //                     tr.autotextcolor();

                        //                     /** can we get the new updated at time here? and populate the updated_at column with the new value for the current case_id */
                        //                     // console.log(tr.children().next('.last_updated'));
                        //                     // console.log(app.ajax.result.updated_at);
                        //                     tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                        //                     tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                        //                 } else {
                        //                     /** we don't change anything */
                        //                     tr.autotextcolor();
                        //                 }
                        //             });
                        //         }
                        //     });
                        // });

                        $('.dataTable tbody').off('click', 'td.parent.change_internal_notes').on('click', 'td.parent.change_internal_notes', function(event) {
                            /**console.log('td.parent.change_internal_notes');*/
                            /** need to loose focus from the other changeable tds */
                            $("#editrecord_input").blur();
                            $('.is_br24_employee').selectize().blur();


                            /** it seems to be sending many trips to the db how to prevent that? */
                            if ($('#edit_from_amount_input_' + change_custom_internal_notes_case_Id + '').is(":visible") == true) {
                                return false;
                            }
                            keep_track_of_last_clicked_item_to_react_after_reload = 'change_from_amount';
                            var target = event.target;
                            event.preventDefault();

                            var tr = $(this).closest('tr');
                            var td = $(this);
                            var td_height = td.height();
                            /**console.log(td_height);*/
                            td_height = td_height + 20;

                            var change_internal_note_original_html = $(this).html();
                            /**console.log(change_internal_note_original_html);*/

                            var row = window.manualdownloadlisttable.row(tr);
                            var thecellvalue = $(this).html();
                            /**console.log(thecellvalue);*/
                            thecellvalue = thecellvalue.replace(/(?:<br>)/g, '\r\n');
                            var rowIndex = tr.data('rowindex');
                            var change_custom_internal_notes_case_Id = $(this).data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                            keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_custom_internal_notes_case_Id;
                            /**console.log(change_custom_internal_notes_case_Id);*/
                            var timestamp_Id = $(this).data('date_ts');
                            // var idx = table.cell(this).index().column;
                            // var date_number = table.column(idx).header();
                            // var clcikedcolumnheader_value = $(date_number).html();
                            var input = $('<textarea id="edit_from_amount_input_' + change_custom_internal_notes_case_Id + '" cols="10" rows="5" charswidth="23"  style="white-space: pre-wrap; line-height: 15px; min-height: 30px; width: 100%; height: ' + td_height + 'px; display: block; z-index: 12; resize: vertical; color: black;"></textarea>');
                            input.val(thecellvalue);
                            td.html(input);

                            $(document).on('keydown', '#edit_from_amount_input_' + change_custom_internal_notes_case_Id, function(e) {
                                var input = $(this);
                                var oldVal = input.val();
                                var regex = new RegExp(input.attr('pattern'), 'g');

                                setTimeout(function() {
                                    var newVal = input.val();
                                    if (!regex.test(newVal)) {
                                        input.val(oldVal);
                                    }
                                }, 0);
                                /** if enter key is pressed allow it */
                                if (e.keyCode == 13) {
                                    // input.blur();

                                }
                                /** if esc key is pressed return the thing back to the original ?? */
                                if (e.keyCode == 27) {
                                    td.html(change_internal_note_original_html);
                                }
                            });


                            $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).click(function(event) {
                                event.stopImmediatePropagation();
                                /***/
                            });
                            $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).focus(function() {
                                /** select everything in the input */
                                var save_this = $(this);
                                window.setTimeout(function() {
                                    save_this.select();
                                }, 30);
                            });

                            $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).focus();

                            $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).blur(function() {
                                var edited_number = $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).val();
                                edited_number = edited_number.replace(/(?:\r\n|\r|\n)/g, '<br>');
                                /**console.log(edited_number);*/
                                if (change_internal_note_original_html == edited_number) {
                                    /** console.log('change_from_amount td.html(change_internal_note_original_html)'); */
                                    td.html(change_internal_note_original_html);

                                    /** keep_track_of_last_clicked_item_to_react_after_reload = null; */
                                    /** keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = null; */
                                } else {
                                    /** its different */
                                    /**app.util.nprogressinit();*/
                                    /**app.util.fullscreenloading_start();*/
                                    $('#edit_from_amount_input_' + change_custom_internal_notes_case_Id).remove();
                                    // var change_from_amount_replace_html = $("<span class='label label-primary currency_number_format'>" + edited_number + "</span>");
                                    // console.log(change_from_amount_replace_html);
                                    /**console.log('change_from_amount td.html(edited_number)');*/

                                    td.html(edited_number);

                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                    var data = {
                                        'case_id': change_custom_internal_notes_case_Id,
                                        'encrypted_case_id': encrypted_case_id_uploading_to,
                                        'new_custom_internal_note': edited_number
                                    };

                                    /** use ajax to send data to php */
                                    app.ajax.json(app.data.change_custom_internal_note_for_job, data, null, function() {
                                        /**console.log(app.ajax.result);*/
                                        success_ajax_then_refresh = app.ajax.result.success;
                                        if (app.ajax.result.success == true) {

                                            td.html(edited_number);

                                            // console.log(tr.children().next('.last_updated'));
                                            // console.log(app.ajax.result.updated_at);
                                            tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                            tr.children().next('.last_updated').html(app.ajax.result.updated_at);

                                        } else {
                                            td.html(change_internal_note_original_html);
                                        }
                                    });


                                }
                            });
                        });

                        $('.dataTable tbody').off('click', 'td.parent.change_tags').on('click', 'td.parent.change_tags', function(event) {
                            // console.log('td.parent.change_tags');
                            if ($('.is_br24_hashtag').is(":visible") == true) {
                                return false;
                            }

                            keep_track_of_last_clicked_item_to_react_after_reload = 'change_tags';
                            var target = event.target;
                            event.preventDefault();

                            var tr = $(this).closest('tr');
                            var td = $(this);
                            var change_tags_original_html = $(this).html();
                            /**console.log('change_tags_original_html');*/
                            /**console.log(change_tags_original_html);*/

                            /** on click you ened to sanitize it .. */
                            /**<span class='highlight_span'></span>*/
                            var remembering_the_highlighting = change_tags_original_html;
                            var has_highlighted_text = remembering_the_highlighting.includes('<span class="highlight_span">');
                            /**console.log('has_highlighted_text', has_highlighted_text);*/

                            var highlighted_text_in_span_before_word = remembering_the_highlighting.split('<span class="highlight_span">');
                            /**console.log(highlighted_text_in_span_before_word);*/
                            if (highlighted_text_in_span_before_word[1] != undefined || highlighted_text_in_span_before_word[1] != null) {
                                var word_to_highlight = highlighted_text_in_span_before_word[1].split('</span>')[0];
                            }else{
                                var word_to_highlight = '';
                            }
                            /**console.log(word_to_highlight);*/


                            var sanitized_for_plugin = change_tags_original_html.replace("</span>", "").replace('<span class="highlight_span">', "");
                            /**console.log(sanitized_for_plugin);*/

                            var row = window.manualdownloadlisttable.row(tr);
                            var thecellvalue = $(this).text();
                            // console.log('thecellvalue');
                            // console.log(thecellvalue);
                            //var unformatted_number = $(this).parseNumber({ format: number_format_per_locale, locale: numberformat_locale });
                            /** console.log(unformatted_number); */
                            var rowIndex = tr.data('rowindex');
                            var change_tag_case_Id = $(this).data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                            /**console.log(encrypted_case_id_uploading_to);*/

                            keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_tag_case_Id;
                            /**console.log(change_tag_case_Id);*/
                            var timestamp_Id = $(this).data('date_ts');
                            // var idx = table.cell(this).index().column;
                            // var date_number = table.column(idx).header();
                            // var clcikedcolumnheader_value = $(date_number).html();
                            //var input = $('<input id="edit_tags_input_' + change_tag_case_Id + '" type="number" style="text-align:center; width: 100px; display: block; margin: 0 auto; z-index: 12;" min="0" max="2147483647" step="1" pattern="^\\d{1,10}?$"></input>');

                            var input = $('<select style="width: 100%; height: 46px;" class="is_br24_hashtag form-control" title="" multiple></select>');
                            /** I want this input to be looking like the selectize one */

                            //input.val(unformatted_number);
                            td.html(input);
                            

                            /**console.log('ready');*/
                            var select_list_hashtag_list = app.data.selectize_hashtag_list_formated_json;
                            /**console.log('select_list_hashtag_list=' + JSON.stringify(select_list_hashtag_list));*/

                            var select_selected_list_hashtags_json = app.data.selectize_selected_hashtags_formated_json;
                            /**console.log('select_selected_list_hashtags_json=' + JSON.stringify(select_selected_list_hashtags_json));*/

                            var select_selected_list_caseid_has_hastags = select_selected_list_hashtags_json.map(function(item) {
                                return item['name'];
                            });

                            /**console.log('AFTERselect_selected_list_caseid_has_hastags=' + select_selected_list_caseid_has_hastags);*/

                            /** if there are any that are any details we can use to populate the selectize */
                            if (sanitized_for_plugin != '') {
                                select_selected_list_caseid_has_hastags = sanitized_for_plugin.split(" ");
                                /**console.log(select_selected_list_caseid_has_hastags);*/
                                Object.assign({}, [select_selected_list_caseid_has_hastags]);
                            }
                            /**console.log('FROMHTMLselect_selected_list_caseid_has_hastags=' + select_selected_list_caseid_has_hastags);*/


                            var hashtag_ids = null;
                            var encrypted_hashtag_ids = null;

                            if (!window.Selectize.prototype.positionDropdownOriginal) {
                                window.Selectize.prototype.positionDropdownOriginal = window.Selectize.prototype.positionDropdown;
                                window.Selectize.prototype.positionDropdown = function() {
                                    if (this.settings.dropdownDirection === 'up') {
                                        let $control = this.$control;
                                        let offset = this.settings.dropdownParent === 'body' ? $control.offset() : $control.position();

                                        var the_td = $control.parent().parent();
                                        var the_td_offset = the_td.offset();
                                        var position_relative_to_viewport = parseInt(the_td_offset.top) - parseInt($(window).scrollTop());

                                        var switch_to_dropdown = false;
                                        if (position_relative_to_viewport <= 500) {
                                            var switch_to_dropdown = true;
                                        }

                                        var height_of_drowdown = 261;
                                        if (switch_to_dropdown) {
                                            var height_of_td = the_td.height() - 12;
                                            height_of_drowdown = -parseInt(height_of_td);
                                        }

                                        this.$dropdown.css({
                                            width: $control.outerWidth(),
                                            top: offset.top - height_of_drowdown,
                                            left: offset.left,
                                        });

                                        this.$dropdown.addClass('direction-' + this.settings.dropdownDirection);
                                        this.$control.addClass('direction-' + this.settings.dropdownDirection);
                                        this.$wrapper.addClass('direction-' + this.settings.dropdownDirection);
                                    } else {
                                        window.Selectize.prototype.positionDropdownOriginal.apply(this, arguments);
                                    }
                                };
                            }


                            var $is_br24_hashtag_select = $('.is_br24_hashtag').selectize({
                                plugins: ['remove_button', 'optgroup_columns'],
                                persist: true,
                                maxItems: 200,
                                mode: 'multi',
                                dropdownDirection: 'up',
                                create: function(input) {
                                    /**console.log(input);*/
                                    var result = input.replace('#', '');
                                    return {
                                        name: '#' + result.toUpperCase(),
                                    }
                                },
                                /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                                placeholder: '-- Add Tag --',
                                valueField: ['name'],
                                labelField: 'name',
                                searchField: ['name'],
                                options: select_list_hashtag_list,
                                /** list of all the viable employees on init */
                                items: select_selected_list_caseid_has_hastags,
                                /** list of already selected employees on init */
                                hideSelected: false,
                                openOnFocus: true,
                                closeAfterSelect: true,
                                render: {
                                    item: function(item, escape) {
                                        return '<div>' +
                                            (item.name ? '<span class="name"><u><b>' + item.name + '</b></u></span>' : '') +
                                            //'<span style="color: #ccc">&nbsp;</span>' +
                                            //(item.xml_jobid_title ? ' <i class="fa fa-angle-double-right text-danger"></i> ' : '') +
                                            //(item.xml_jobid_title ? '<span class="xml_jobid_title" style="font-size: 9px; color: #1cd;"><b>' + item.xml_jobid_title + '</b></span>' : '') +
                                            //'<br>' +
                                            //(item.xml_title_contents ? '<span class="xml_title_contents" style="font-size: 10px;">' + item.xml_title_contents + '</span>' : '') +
                                            //'<br>' +
                                            //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                                            '</div>';
                                    },
                                    option: function(item, escape) {
                                        var label = item.xml_title_contents || item.email;
                                        var caption = item.xml_title_contents ? item.email : null;

                                        return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                                            '<span class="label label-primary">' + item.name + '</span>' +
                                            '<span style="color: #ccc">&nbsp;</span>' +

                                            //(item.name ? ' <i class="fa fa-angle-double-right text-danger"></i> ' : '') +
                                            //(item.name ? '<span class="name" style="font-size: 9px; color: #1cd;"><b>' + item.name + '</b></span>' : '') +
                                            //'<br>' +

                                            //(item.xml_title_contents ? '<span class="xml_title_contents" style="font-size: 10px;">' + item.xml_title_contents + '</span>' : '') +
                                            //(item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                                            //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                                            '</div>';
                                    }
                                },
                                onChange: function(value) {
                                    /**console.log(value);*/
                                    /**console.log('on change');*/
                                    //$('#cb_add_family_members_details_form').addClass('dirty');
                                    /** when it changes i want to update the variable so that it can be loaded together with the files */
                                    /**console.log($('.is_br24_hashtag').val());*/
                                    hashtag_ids = $('.is_br24_hashtag').val();
                                    /**console.log(hashtag_ids);*/
                                    /**since we only want to store the tage without the hashcharacter */
                                    var result = hashtag_ids.map(function(x) { return x.replace(/#/g, ''); });
                                    /**console.log(result);*/
                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                    var data = {
                                        'case_id': change_tag_case_Id,
                                        'encrypted_case_id': encrypted_case_id_uploading_to,
                                        'hashtag': result
                                    };

                                    /** use ajax to send data to php */
                                    app.ajax.json(app.data.change_tags_for_job, data, null, function() {
                                        /**console.log(app.ajax.result);*/
                                        success_ajax_then_refresh = app.ajax.result.success;
                                        if (app.ajax.result.success == true) {

                                            app.data.selectize_hashtag_list_formated_json = app.ajax.result.selectize_hashtag_list_formated_json;
                                            /**console.log('after');*/
                                            /**console.log(select_list_hashtag_list);*/
                                            // console.log(tr.children().next('.last_updated'));
                                            // console.log(app.ajax.result.updated_at);
                                            tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                            tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                                        } else {

                                            // $.each(app.ajax.result.errors, function(idx, val) {
                                            //     $.alert(val);
                                            // });
                                        }
                                    });

                                }
                            });

                            var is_br24_hashtag_select_selectize = $is_br24_hashtag_select[0].selectize;
                            var is_br24_hashtag_select_old_options = is_br24_hashtag_select_selectize.settings;
                            var selectize_focus_handler = function(value, $item) {
                                var width_to_be = $('.selectize-control').outerWidth();
                                var height_to_be = 300;
                                $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                            };
                            is_br24_hashtag_select_selectize.on('focus', selectize_focus_handler);


                            var selectize_blur_handler = function(value, $item) {
                                // var width_to_be = $('.selectize-control').outerWidth();
                                // var height_to_be = 300;


                                // $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                                var this_td_new_content_formated = '';

                                hashtag_ids = $('.is_br24_hashtag').val();
                                /**console.log(hashtag_ids);*/
                                /**console.log(change_tags_original_html);*/
                                if (hashtag_ids === undefined || hashtag_ids.length == 0) {
                                    td.html('');
                                } else {
                                    /**console.log('blur');*/
                                    $.each(hashtag_ids, function(idx, val) {
                                        // console.log(idx);
                                        // console.log(val);
                                        if (this_td_new_content_formated == '') {
                                            this_td_new_content_formated = val;
                                        } else {
                                            this_td_new_content_formated = this_td_new_content_formated + ' ' + val;
                                        }
                                    });

                                    /**console.log('this_td_new_content_formated', this_td_new_content_formated);*/

                                    /** need to put back the highlighting if the word is still there.. */
                                    /**console.log('word_to_highlight', word_to_highlight);*/


                                    if(word_to_highlight != ""){
                                        var working_on_the_rehighlighting_array = this_td_new_content_formated.split(word_to_highlight);
                                        var new_string_with_highlighting = '';
                                        $.each(working_on_the_rehighlighting_array, function(idx, val) {
                                            // console.log(idx);
                                            // console.log(val);
                                            if(idx == (working_on_the_rehighlighting_array.length - 1)){
                                                new_string_with_highlighting = new_string_with_highlighting.concat(val);
                                            }else{
                                                new_string_with_highlighting = new_string_with_highlighting.concat(val).concat('<span class="highlight_span">'+word_to_highlight+'</span>');
                                            }
                                        });
                                    }else{
                                        new_string_with_highlighting = this_td_new_content_formated;
                                    }

                                    td.html(new_string_with_highlighting);
                                }

                                /** otherwise return it to what it was before */

                                /** want to be able to save the information on the database so that it can be called back when the page reloads */
                                /** also don't want to reload the page want to have the selected options become the td content */
                                /** and if there is content to get the selectize automatically load those ones on click */
                                /** */

                                /** should do it on blur as well or on change to so that as soon as they click its on the database already. */
                            };
                            is_br24_hashtag_select_selectize.on('blur', selectize_blur_handler);

                            /** filter the shifts from the swapable shifts select */
                            var item_remove_handler = function(value, $item) {
                                /**console.log('item_removed');*/
                                hashtag_ids = $('.is_br24_hashtag').val();
                            };
                            is_br24_hashtag_select_selectize.on('item_remove', item_remove_handler);


                            is_br24_hashtag_select_selectize.$control_input.on('keydown', function(e) {
                                var allowedCode = [8, 13, 44, 45, 46, 95];
                                var charCode = (e.charCode) ? e.charCode : ((e.keyCode) ? e.keyCode : ((e.which) ? e.which : 0));
                                /**console.log(charCode);*/

                                if (charCode > 31 && (charCode < 64 || charCode > 90) && (charCode < 97 || charCode > 122) && (charCode < 48 || charCode > 57) && (allowedCode.indexOf(charCode) == -1)) {
                                    return false;
                                } else {
                                    return true;
                                }
                            });



                            $(window).resize(function() {
                                var width_to_be = $('.selectize-control').outerWidth();
                                var height_to_be = 300;
                                $('.selectize-dropdown-content').css('width', width_to_be).css('height', height_to_be);
                            });

                            is_br24_hashtag_select_selectize.focus();
                        });

                        $('.dataTable tbody').off('click', '.output_number_of_pictures_expected').on('click', '.output_number_of_pictures_expected', function(event) {
                            /**console.log('.output_number_of_pictures_expected');*/


                            /** need to loose focus from the other changeable tds */
                            $("#editrecord_input").blur();
                            $('.is_br24_employee').selectize().blur();


                            /** it seems to be sending many trips to the db how to prevent that? */
                            if ($('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id + '').is(":visible") == true) {
                                return false;
                            }
                            keep_track_of_last_clicked_item_to_react_after_reload = 'change_from_amount';
                            var target = event.target;
                            event.preventDefault();

                            var tr = $(this).closest('tr');
                            var td = $(this);
                            /**console.log(td);*/

                            $(this).parent().children('.output_number_of_pictures_real').css('visibility', 'hidden');


                            var td_height = td.parent().height();
                            /**console.log(td_height);*/
                            td_height = td_height + 20;
                            var td_width = td.parent().width();

                            var change_internal_note_original_html = $(this).html();
                            /**console.log(change_internal_note_original_html);*/

                            var row = window.manualdownloadlisttable.row(tr);
                            var thecellvalue = $(this).html();
                            /**console.log(thecellvalue);*/
                            thecellvalue = thecellvalue.replace(/(?:<br>)/g, '\r\n');
                            var rowIndex = tr.data('rowindex');
                            var change_output_number_of_pictures_expected_case_Id = $(this).parent().parent().data('case_id');
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                            keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_output_number_of_pictures_expected_case_Id;
                            /**console.log(change_output_number_of_pictures_expected_case_Id);*/
                            var timestamp_Id = $(this).data('date_ts');
                            // var idx = table.cell(this).index().column;
                            // var date_number = table.column(idx).header();
                            // var clcikedcolumnheader_value = $(date_number).html();
                            var input = $('<input id="edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id + '" type="number" style="text-align:left; width: ' + td_width + 'px; display: block; color: #000; margin: 0 auto; z-index: 12;" min="0" max="999" step="1" pattern="^\\d{1,10}?$"></input>');
                            input.val(thecellvalue);
                            td.html(input);

                            $(document).on('keydown', '#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id, function(e) {
                                var input = $(this);
                                var oldVal = input.val();
                                var regex = new RegExp(input.attr('pattern'), 'g');

                                setTimeout(function() {
                                    var newVal = input.val();
                                    if (!regex.test(newVal)) {
                                        input.val(oldVal);
                                    }
                                }, 0);
                                /** if enter key is pressed allow it */
                                if (e.keyCode == 13) {
                                    // input.blur();

                                }
                                /** if esc key is pressed return the thing back to the original ?? */
                                if (e.keyCode == 27) {
                                    td.html(change_internal_note_original_html);
                                }
                            });


                            $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).click(function(event) {
                                event.stopImmediatePropagation();
                                /***/
                            });
                            $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).focus(function() {
                                /** select everything in the input */
                                var save_this = $(this);
                                window.setTimeout(function() {
                                    save_this.select();
                                }, 30);
                            });

                            $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).focus();

                            $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).blur(function() {
                                var edited_number = $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).val();
                                edited_number = edited_number.replace(/(?:\r\n|\r|\n)/g, '<br>');
                                /**console.log(edited_number);*/
                                if (change_internal_note_original_html == edited_number) {
                                    /** console.log('change_from_amount td.html(change_internal_note_original_html)'); */
                                    td.html(change_internal_note_original_html);
                                    td.parent().children('.output_number_of_pictures_real').css('visibility', 'visible');

                                    /** keep_track_of_last_clicked_item_to_react_after_reload = null; */
                                    /** keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = null; */
                                } else {
                                    /** its different */
                                    /**app.util.nprogressinit();*/
                                    /**app.util.fullscreenloading_start();*/
                                    $('#edit_from_amount_input_' + change_output_number_of_pictures_expected_case_Id).remove();
                                    // var change_from_amount_replace_html = $("<span class='label label-primary currency_number_format'>" + edited_number + "</span>");
                                    // console.log(change_from_amount_replace_html);
                                    /**console.log('change_from_amount td.html(edited_number)');*/

                                    td.html(edited_number);

                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                    var data = {
                                        'case_id': change_output_number_of_pictures_expected_case_Id,
                                        'encrypted_case_id': encrypted_case_id_uploading_to,
                                        'new_custom_output_expected': edited_number
                                    };

                                    /** use ajax to send data to php */
                                    app.ajax.json(app.data.change_custom_output_expected_for_job, data, null, function() {
                                        /**console.log(app.ajax.result);*/
                                        success_ajax_then_refresh = app.ajax.result.success;
                                        if (app.ajax.result.success == true) {

                                            td.html(edited_number);
                                            td.parent().children('.output_number_of_pictures_real').css('visibility', 'visible');

                                            // console.log(tr.children().next('.last_updated'));
                                            // console.log(app.ajax.result.updated_at);
                                            tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                            tr.children().next('.last_updated').html(app.ajax.result.updated_at);

                                        } else {
                                            td.html(change_internal_note_original_html);
                                            td.parent().children('.output_number_of_pictures_real').css('visibility', 'visible');
                                        }
                                    });


                                }
                            });
                        });


                        /** preview required checkbox */
                        $("input[name*='edit_']").on('click', function(event) {
                            event.stopPropagation();
                            /**console.log('clickingdirectoncheckbox');*/

                            var tr = $(this).closest('tr');
                            var change_assignees_value = tr.find('.change_assignees').html();
                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');

                            case_Id = $(this).parent().data('case_id');
                            date_ts_Id = $(this).parent().data('date_ts');
                            var status = null;
                            var checkbox_in_td = $("input[name='edit_" + case_Id + "']");
                            if (checkbox_in_td.is(':checked')) {
                                /**console.log('3was not checked');*/
                                checkbox_in_td.prop("checked", true);
                                status = 1;
                            } else {
                                /**console.log('3was checked');*/
                                checkbox_in_td.prop("checked", false);
                                status = 2;
                            }
                            /**console.log(status);*/
                            /** we go in via ajax to amend the status column where user_id and date on accept reject penalty table */
                            app_ops.manage_manualdownloadlist.profile.sync_preview_required_status(case_Id, encrypted_case_id_uploading_to, status, tr, checkbox_in_td, change_assignees_value);
                        });

                        $('.two_weeks_checkbox_col.parent').not("input[name*='edit_']").on('click', function(event) {
                            event.stopPropagation();
                            /**console.log('clickingaroundcheckboxonemployeerow');*/
                            /** also gets triggered if clicking directy on the checkbox */
                            // if ($('.checkbox_check_uncheck_all').is(':checked')) {
                            //     $('.checkbox_check_uncheck_all').prop("checked", false);
                            // }
                            var tr = $(this).closest('tr');
                            /**console.log(tr.find('.change_assignees').html());*/
                            var change_assignees_value = tr.find('.change_assignees').html();

                            var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');

                            case_Id = $(this).data('case_id');
                            date_ts_Id = $(this).data('date_ts');
                            var status = null;
                            var checkbox_in_td = $("input[name='edit_" + case_Id + "']");
                            if (checkbox_in_td.is(':checked')) {
                                /**console.log('3was checked');*/
                                checkbox_in_td.prop("checked", false);
                                status = 2;
                            } else {
                                /**console.log('3was not checked');*/
                                checkbox_in_td.prop("checked", true);
                                status = 1;
                            }
                            /**console.log(status);*/
                            /** we go in via ajax to amend the status column where user_id and date on accept reject penalty table */
                            /** normally the checkbox would be disabled if there are no assignees */
                            /** but if they hack it they will still be able to change the check box */
                            /** so as a client side validation check if the assignees have been set or not and disable */
                            /** */

                            app_ops.manage_manualdownloadlist.profile.sync_preview_required_status(case_Id, encrypted_case_id_uploading_to, status, tr, checkbox_in_td, change_assignees_value);
                        });

                        $('input[type="checkbox"], .two_weeks_checkbox_col').css('cursor', 'pointer');


                        $(document).ready(function() {
                            var currentColorBox = ''; /** super important if you want only specific set of colorboxes to resize */
                            var window_focus = true;
                            $(window).focus(function() { window_focus = true; }).blur(function() { window_focus = false; });

                            //define some variables here so can use inside the colorbox onClose Callbacks
                            var checks_if_avatar_colorbox_is_open = null;
                            var success_ajax_then_refresh = null;
                            var been_out = null;
                            var refreshIntervalId = null;
                            var timer = function() {
                                refreshIntervalId = setInterval(function() {
                                    /**console.log('has focus? ' + window_focus);*/
                                    //console.log('has focus? ' + window_focus);
                                    //console.log('avatar_colorbox_open=' + checks_if_avatar_colorbox_is_open);
                                }, 100);
                            };
                            var close_colorbox_refreshIntervalId = null;
                            var close_colorbox_timer = function() {
                                //console.log('close_colorbox_100_ms_countdown_timer_started');
                                close_colorbox_refreshIntervalId = setInterval(function() {
                                    $("#cb_add_manualdownloadlist.ajax").colorbox.close();
                                    $("#cb_edit_manualdownloadlist.ajax").colorbox.close();
                                    clearInterval(close_colorbox_refreshIntervalId);
                                }, 100);
                            };

                            function formatBytes(bytes,decimals) {
                               if(bytes == 0) return '0 Bytes';
                               var k = 1024,
                                   dm = decimals || 2,
                                   sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
                                   i = Math.floor(Math.log(bytes) / Math.log(k));
                               return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
                            }
                            var autoCloserefreshIntervalId = null;
                            var autoClosetimer = function() {
                                var countdownstartvalueseconds = 10;
                                autoCloserefreshIntervalId = setInterval(function() {
                                    /**console.log(countdownstartvalueseconds)*/
                                    countdownstartvalueseconds = countdownstartvalueseconds - 1;
                                    if(countdownstartvalueseconds == 0){
                                        $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                                        $('.jconfirm-buttons').children().closest('.btn-primary').trigger('click');
                                        clearInterval(autoCloserefreshIntervalId);
                                        /** for the next time round. */
                                        countdownstartvalueseconds = 0;
                                    }else{
                                        $('.jconfirm-buttons').children().closest('.btn-primary').text('OK (' +countdownstartvalueseconds+ ')');
                                    }
                                }, 1000);
                            };

                            /** must be at the end */
                            var cboxOptions = { width: '600px', height: '400px', }
                            $(window).resize(function() {
                                var colorboxes_array = ["cb_add_manualdownloadlist", "cb_edit_manualdownloadlist"];
                                if (colorboxes_array.indexOf(currentColorBox) > -1) {
                                    $.colorbox.resize({
                                        width: window.innerWidth > parseInt(cboxOptions.maxWidth) ? cboxOptions.maxWidth : cboxOptions.width,
                                        height: window.innerHeight > parseInt(cboxOptions.maxHeight) ? cboxOptions.maxHeight : cboxOptions.height
                                    });
                                }
                            });

                            $("#cb_add_manualdownloadlist.ajax").colorbox({
                                rel: 'nofollow',
                                width: "600px",
                                height: "400px",
                                left: '9%',
                                top: '200px',
                                escKey: true, //escape key will not close
                                overlayClose: false, //clicking background will not close
                                closeButton: false, //hide the close button
                                onOpen: function() {
                                    //console.log('onOpen: colorbox is about to open');
                                    currentColorBox = 'cb_add_manualdownloadlist';
                                    //app.util.fullscreenloading_start();
                                },
                                onLoad: function() {
                                    //console.log('onLoad: colorbox has started to load the targeted content');
                                    //
                                },
                                onComplete: function() {
                                    //app.util.fullscreenloading_end();
                                    //timer();
                                    //console.log('timer_started');
                                    //console.log('onComplete: colorbox has displayed the loaded content');

                                    $("#advance_amount").focus(function() {
                                        $(this).parseNumber({ format: number_format_per_locale, locale: numberformat_locale });
                                        $(this).select();
                                    });
                                    $("#advance_amount").blur(function() {
                                        $(this).formatNumber({ format: number_format_per_locale, locale: numberformat_locale });
                                        //
                                    });

                                    var counting_ssd = 1;
                                    var $calendar_date = $('.calendar_date');
                                    $calendar_date.datepicker({
                                        dateFormat: prefered_dateFormat,
                                        showMonthAfterYear: true,
                                        numberOfMonths: 1,
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange: "-10:+1",
                                        showOtherMonths: true,
                                        selectOtherMonths: true,
                                        toggleActive: true,
                                        minDate: "-10y",
                                        maxDate: "+1y",
                                        autoclose: true,
                                        onClose: function() {
                                            var addressinput = $(this).val();
                                            /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                                            var res = patt.test(addressinput);

                                            if (res == true) {
                                                $('#calendar_date').closest('td').removeClass('has-error');
                                                $('#calendar_date').nextAll('.help-block').css('display', 'none');

                                                $(this).blur();
                                                counting_ssd = 1;
                                            } else {
                                                $("#calendar_date").closest("td").addClass("has-error");
                                                if (counting_ssd == 1) {
                                                    $("#calendar_date").after('<span class="help-block"><strong>' + eval("app.translations." + app.data.locale + ".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations." + app.data.locale + ".or_choose_from_the_calendar") + '</strong></span>');
                                                } else {
                                                    // it exists
                                                }
                                                counting_ssd++;
                                                $(this).blur();
                                            }
                                        },
                                        beforeShow: function(input, obj) {
                                            $calendar_date.after($calendar_date.datepicker('widget'));

                                            setTimeout(function() {
                                                $('#ui-datepicker-div').css('top', '').css('left', '');
                                            }, 0);
                                        }
                                    });

                                    $calendar_date.datepicker().datepicker("setDate", new Date());


                                    var select_list_employee_list = app.data.selectize_employee_list_formated_json;
                                    /**console.log('select_list_employee_list=' + JSON.stringify(select_list_employee_list));*/
                                    var select_selected_employee_json = app.data.selectize_selected_employee_formated_json;
                                    //console.log('select_selected_employee_json=' + JSON.stringify(select_selected_employee_json));

                                    select_selected_list_employee_to_receive_messages = select_selected_employee_json.map(function(item) {
                                        return item['fk_is_br24_employee'];
                                    });
                                    //console.log('AFTERselect_selected_list_employee_to_receive_messages=' + select_selected_list_employee_to_receive_messages);

                                    var $recipients_select = $('#recipients').selectize({
                                        plugins: ['remove_button', 'optgroup_columns'],
                                        persist: false,
                                        maxItems: 1,
                                        mode: 'multi',
                                        /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                                        placeholder: '-- ' + eval("app.translations." + app.data.locale + ".select_employee") + ' --',
                                        valueField: 'user_id',
                                        labelField: 'user_id',
                                        searchField: ['user_id', 'fullname', 'fullname_noaccents'],
                                        options: select_list_employee_list,
                                        /** list of all the viable employees on init */
                                        items: select_selected_list_employee_to_receive_messages,
                                        /** list of already selected employees on init */
                                        hideSelected: true,
                                        openOnFocus: true,
                                        closeAfterSelect: true,
                                        render: {
                                            item: function(item, escape) {
                                                return '<div>' +
                                                    (item.user_id ? '<span class="user_id"><b>' + item.user_id + '</b></span>' : '') +
                                                    '<span style="color: #ccc">&nbsp;</span>' +
                                                    (item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                    '<br>' +
                                                    (item.position ? '<span class="position" style="font-size: 9px;">' + item.position + '</span>' : '') +
                                                    //'<br>' +
                                                    //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                                                    '</div>';
                                            },
                                            option: function(item, escape) {
                                                var label = item.fullname || item.email;
                                                var caption = item.fullname ? item.email : null;
                                                return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                                                    '<span class="label label-primary">' + item.user_id + '</span>' +
                                                    '<span style="color: #ccc">&nbsp;</span>' +
                                                    (item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                    '<br>' +
                                                    (item.position ? '<span class="position" style="font-size: 9px; color: #ccc;">' + item.position + '</span>' : '') +
                                                    (item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                                                    //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                                                    '</div>';
                                            }
                                        },
                                        onChange: function(value) {
                                            /** how to get the details populating the fields automatically if an employee is selected from the selectize plugin? provided the information is already on the db at the time */
                                            /** name | dateofbirth | tax code number | ID card number or Passport number */
                                            if (value === undefined || value.length == 0) {
                                                /**array empty or does not exist*/
                                            } else {

                                            }
                                            $('#cb_add_manualdownloadlist_details_form').addClass('dirty');
                                        }
                                    });

                                    var recipients_select_selectize = $recipients_select[0].selectize;
                                    var recipients_select_old_options = recipients_select_selectize.settings;
                                    var selectize_focus_handler = function(value, $item) {
                                        var width_to_be = $('.selectize-control').outerWidth();
                                        $('.selectize-dropdown-content').css('width', width_to_be);
                                    };
                                    recipients_select_selectize.on('focus', selectize_focus_handler);


                                    /** filter the shifts from the swapable shifts select */
                                    var item_remove_handler = function(value, $item) {
                                        $('#cb_add_manualdownloadlist_details_form').addClass('dirty');
                                    };
                                    recipients_select_selectize.on('item_remove', item_remove_handler);

                                    $(window).resize(function() {
                                        var width_to_be = $('.selectize-control').outerWidth();
                                        $('.selectize-dropdown-content').css('width', width_to_be);
                                    });





                                    var calendar_date = $('#calendar_date').val();
                                    var recipients = $('#recipients').val();
                                    var advance_amount = $('#advance_amount').val();
                                    var reason = $('reason').val();
                                    var payment_period_description = $('payment_period_description').val();

                                    var currentdate = new Date();
                                    var datetime = "Last Sync: " + currentdate.getDate() +
                                        "/" + (currentdate.getMonth() + 1) +
                                        "/" + currentdate.getFullYear() +
                                        " @ " + currentdate.getHours() +
                                        ":" + currentdate.getMinutes() +
                                        ":" + currentdate.getSeconds();

                                    var add_manualdownloadlist_previous_data = [];
                                    add_manualdownloadlist_previous_data['add_manualdownloadlist_previous_data'] = {
                                        "whenwasset": datetime,

                                        "add_manualdownloadlist_calendar_date": calendar_date,
                                        "add_manualdownloadlist_recipients": recipients,
                                        "add_manualdownloadlist_advance_amount": advance_amount,
                                        "add_manualdownloadlist_reason": reason,
                                        "add_manualdownloadlist_payment_period_description": payment_period_description
                                    };
                                    sessionStorage.setItem('Br24_' + app.env() + '_add_manualdownloadlistinfo_previous_data', JSON.stringify(add_manualdownloadlist_previous_data['add_manualdownloadlist_previous_data']));



                                    $('#cb_add_manualdownloadlist_details_form').areYouSure();
                                    $('#cb_add_manualdownloadlist_details_form').on('change', 'select', function() {
                                        $("#add_manualdownloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#add_manualdownloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of select tags

                                    $('#cb_add_manualdownloadlist_details_form').on('change keypress', 'input', function() {
                                        $("#add_manualdownloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#add_manualdownloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of input: the change event take care of input of type "hidden" also

                                    $('#cb_add_manualdownloadlist_details_form').on('change keypress', 'textarea', function() {
                                        $("#add_manualdownloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#add_manualdownloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of textareas


                                    $("#add_manualdownloadlist_reset").click(function() {
                                        var button_selector = $('#add_manualdownloadlist_reset, #add_manualdownloadlist_update');
                                        //hide all the buttons
                                        button_selector.prop('disabled', true).css('display', 'none');

                                        var previous_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_add_manualdownloadlistinfo_previous_data'));

                                        /**console.log('clicked');*/
                                        /**console.log(previous_load['add_manualdownloadlist_calendar_date']);*/

                                        $('#calendar_date').val(previous_load['add_manualdownloadlist_calendar_date']);
                                        $('#advance_amount').val(previous_load['add_manualdownloadlist_advance_amount']);
                                        $('#reason').val(previous_load['add_manualdownloadlist_reason']);
                                        $('#payment_period_description').val(previous_load['add_manualdownloadlist_payment_period_description']);

                                        recipients_select_selectize.setValue(previous_load['add_manualdownloadlist_recipients'], false);

                                        $('.has-error').removeClass('has-error');
                                        $('.help-block').css('display', 'none');
                                        $('#cb_add_manualdownloadlist_details_form').trigger('reinitialize.areYouSure');
                                    });

                                    $("#add_manualdownloadlist_update").click(function(e) {
                                        e.preventDefault();
                                        $('.alert_warning').css('display', 'none');
                                        $('.alert_success').css('display', 'none');

                                        var calendar_date = $('#calendar_date').val();
                                        var recipients = $('#recipients').val();
                                        var advance_amount = $('#advance_amount').val();
                                        var reason = $('reason').val();
                                        var payment_period_description = $('payment_period_description').val();


                                        var currentdate = new Date();
                                        var datetime = "Last Sync: " + currentdate.getDate() +
                                            "/" + (currentdate.getMonth() + 1) +
                                            "/" + currentdate.getFullYear() +
                                            " @ " + currentdate.getHours() +
                                            ":" + currentdate.getMinutes() +
                                            ":" + currentdate.getSeconds();

                                        var add_manualdownloadlist_submitted_data = [];
                                        add_manualdownloadlist_submitted_data['add_manualdownloadlist_submitted_data'] = {
                                            "whenwasset": datetime,

                                            "add_manualdownloadlist_calendar_date": calendar_date,
                                            "add_manualdownloadlist_recipients": recipients,
                                            "add_manualdownloadlist_advance_amount": advance_amount,
                                            "add_manualdownloadlist_reason": reason,
                                            "add_manualdownloadlist_payment_period_description": payment_period_description
                                        };
                                        sessionStorage.setItem('Br24_' + app.env() + '_add_manualdownloadlistinfo_submitted_data', JSON.stringify(add_manualdownloadlist_submitted_data['add_manualdownloadlist_submitted_data']));

                                        /** convert the number fields to unformatted number momentarily*/
                                        $("#advance_amount").parseNumber({ format: number_format_per_locale, locale: numberformat_locale });

                                        var formData = new FormData($('#cb_add_manualdownloadlist_details_form')[0]);
                                        formData.append("recipients", recipients);

                                        NProgress.configure({ parent: '#cboxTitle', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                        NProgress.start();

                                        $('.cb_loader').css('display', 'block').css('cursor', 'wait');
                                        $('#cb_top').addClass('nprogress-busy').css('pointer-events', 'none');
                                        //app.util.fullscreenloading_start();

                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $("#add_manualdownloadlist_details_table").data("token") } });
                                        app.ajax.formdata(app.data.manage_add_manualdownloadlist, formData, null, function() {
                                            console.log(app.ajax.resultformdata);
                                            success_ajax_then_refresh = app.ajax.resultformdata.success;

                                            NProgress.done();
                                            $('.cb_loader').css('display', 'none').css('cursor', 'auto');
                                            $('#cb_top').removeClass('nprogress-busy').css('pointer-events', 'auto');

                                            if (app.ajax.resultformdata.success == true) {
                                                $('#cboxLoadedContent').css('background-color', '#4CAF50');
                                                $('.onSuccess_makeGreen').css('background-color', '#4CAF50');
                                                $('.ibox-tool-userid').css('color', 'white');
                                                $('#cb_add_manualdownloadlist_details_form').css('display', 'none');
                                                close_colorbox_timer();
                                                $(document.body).css('pointer-events', 'none');
                                                app.util.fullscreenloading_start();
                                                if (app.ajax.resultformdata.process_penalties_accept_reject_table_sync == true) {
                                                    app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                }
                                            } else {
                                                $("#advance_amount").formatNumber({ format: number_format_per_locale, locale: numberformat_locale });

                                                $('.has-error').removeClass('has-error');
                                                $('.help-block').detach();

                                                $.each(app.ajax.resultformdata.errors, function(idx, val) {
                                                    app_ops.manage_manualdownloadlist.profile.foreach_handle_error_display(idx, val);
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
                                                    $('#calendar_date').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #calendar_date');
                                                    });
                                                    $('#recipients').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #recipients');
                                                    });
                                                    $('#advance_amount').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).nextAll('.help-block').css('display', 'none');
                                                        console.log('something changed in #advance_amount');
                                                    });
                                                    $('#reason').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #reason');
                                                    });
                                                    $('#payment_period_description').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #payment_period_description');
                                                    });
                                                }
                                            }
                                        });
                                    });

                                    $('#cb_add_manualdownloadlist_details_form').on('keyup keypress', function(e) {
                                        var keyCode = e.keyCode || e.which;
                                        if (keyCode === 13) {
                                            e.preventDefault();
                                            return false;
                                        }
                                    });

                                    $('#cboxOverlay').off('click').on('click', function(event) {
                                        //console.log('clickedousideofcolorbox');
                                        var identifychanges = $('#cb_add_manualdownloadlist_details_form').hasClass('dirty');
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
                                                            $("#cb_add_manualdownloadlist.ajax").colorbox.close();
                                                            $('#cb_add_manualdownloadlist_details_form').trigger('reinitialize.areYouSure');
                                                        }
                                                    },
                                                    cancel: {
                                                        text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                        action: function() {
                                                            //$.alert('');
                                                            return false;
                                                        }
                                                    },
                                                }
                                            });
                                        } else {
                                            $("#cb_add_manualdownloadlist.ajax").colorbox.close();
                                            $('#cb_add_manualdownloadlist_details_form').trigger('reinitialize.areYouSure');
                                        }
                                    });
                                },
                                onCleanup: function() {
                                    app.data.selectize_employee_list_formated_json = '';
                                    app.data.selectize_selected_employee_formated_json = '';

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
                                        window.manualdownloadlisttable.ajax.reload(null, false);
                                        app.util.fullscreenloading_end();
                                        window.manualdownloadlisttable.fixedHeader.adjust();
                                    }
                                    NProgress.configure({ parent: '#progress-bar-parent', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                    clearInterval(refreshIntervalId); //stop the timer called refreshIntervalId
                                    clearInterval(close_colorbox_refreshIntervalId);
                                    //app.util.fullscreenloading_end();
                                    sessionStorage.removeItem('Br24_' + app.env() + '_add_manualdownloadlistinfo_previous_data');
                                    sessionStorage.removeItem('Br24_' + app.env() + '_add_manualdownloadlistinfo_submitted_data');
                                },
                            });

                            $("#cb_edit_manualdownloadlist.ajax").colorbox({
                                rel: 'nofollow',
                                width: "600px",
                                height: "400px",
                                left: '60%',
                                top: '200px',
                                escKey: true, //escape key will not close
                                overlayClose: false, //clicking background will not close
                                closeButton: false, //hide the close button
                                onOpen: function() {
                                    //console.log('onOpen: colorbox is about to open');
                                    currentColorBox = 'cb_edit_manualdownloadlist';
                                    //app.util.fullscreenloading_start();
                                },
                                onLoad: function() {
                                    //console.log('onLoad: colorbox has started to load the targeted content');
                                    //
                                },
                                onComplete: function() {
                                    //app.util.fullscreenloading_end();
                                    //timer();
                                    //console.log('timer_started');
                                    //console.log('onComplete: colorbox has displayed the loaded content');


                                    $("#advance_amount").focus(function() {
                                        $(this).parseNumber({ format: number_format_per_locale, locale: numberformat_locale });
                                        $(this).select();
                                    });
                                    $("#advance_amount").blur(function() {
                                        $(this).formatNumber({ format: number_format_per_locale, locale: numberformat_locale });
                                        //
                                    });
                                    $("#advance_amount").formatNumber({ format: number_format_per_locale, locale: numberformat_locale });

                                    var counting_ssd = 1;
                                    var $calendar_date = $('.calendar_date');
                                    $calendar_date.datepicker({
                                        dateFormat: prefered_dateFormat,
                                        showMonthAfterYear: true,
                                        numberOfMonths: 1,
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange: "-10:+1",
                                        showOtherMonths: true,
                                        selectOtherMonths: true,
                                        toggleActive: true,
                                        minDate: "-10y",
                                        maxDate: "+1y",
                                        autoclose: true,
                                        onClose: function() {
                                            var addressinput = $(this).val();
                                            /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                                            var res = patt.test(addressinput);

                                            if (res == true) {
                                                $('#calendar_date').closest('td').removeClass('has-error');
                                                $('#calendar_date').nextAll('.help-block').css('display', 'none');

                                                $(this).blur();
                                                counting_ssd = 1;
                                            } else {
                                                $("#calendar_date").closest("td").addClass("has-error");
                                                if (counting_ssd == 1) {
                                                    $("#calendar_date").after('<span class="help-block"><strong>' + eval("app.translations." + app.data.locale + ".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations." + app.data.locale + ".or_choose_from_the_calendar") + '</strong></span>');
                                                } else {
                                                    // it exists
                                                }
                                                counting_ssd++;
                                                $(this).blur();
                                            }
                                        },
                                        beforeShow: function(input, obj) {
                                            $calendar_date.after($calendar_date.datepicker('widget'));

                                            setTimeout(function() {
                                                $('#ui-datepicker-div').css('top', '').css('left', '');
                                            }, 0);
                                        }
                                    });

                                    //$calendar_date.datepicker().datepicker("setDate", new Date());


                                    var select_list_employee_list = app.data.selectize_employee_list_formated_json;
                                    /**console.log('select_list_employee_list=' + JSON.stringify(select_list_employee_list));*/
                                    var select_selected_employee_json = app.data.selectize_selected_employee_formated_json;
                                    //console.log('select_selected_employee_json=' + JSON.stringify(select_selected_employee_json));

                                    select_selected_list_employee_to_receive_messages = select_selected_employee_json.map(function(item) {
                                        return item['user_id'];
                                    });
                                    /**console.log('AFTERselect_selected_list_employee_to_receive_messages=' + select_selected_list_employee_to_receive_messages);*/

                                    var $recipients_select = $('#recipients').selectize({
                                        plugins: ['remove_button', 'optgroup_columns'],
                                        persist: false,
                                        maxItems: 200,
                                        mode: 'multi',
                                        /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                                        placeholder: '-- ' + eval("app.translations." + app.data.locale + ".select_employee") + ' --',
                                        valueField: 'user_id',
                                        labelField: 'user_id',
                                        searchField: ['user_id', 'fullname', 'fullname_noaccents'],
                                        options: select_list_employee_list,
                                        /** list of all the viable employees on init */
                                        items: select_selected_list_employee_to_receive_messages,
                                        /** list of already selected employees on init */
                                        hideSelected: true,
                                        openOnFocus: true,
                                        closeAfterSelect: true,
                                        render: {
                                            item: function(item, escape) {
                                                return '<div>' +
                                                    (item.user_id ? '<span class="user_id"><b>' + item.user_id + '</b></span>' : '') +
                                                    '<span style="color: #ccc">&nbsp;</span>' +
                                                    (item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                    '<br>' +
                                                    (item.position ? '<span class="position" style="font-size: 9px;">' + item.position + '</span>' : '') +
                                                    //'<br>' +
                                                    //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                                                    '</div>';
                                            },
                                            option: function(item, escape) {
                                                var label = item.fullname || item.email;
                                                var caption = item.fullname ? item.email : null;
                                                return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                                                    '<span class="label label-primary">' + item.user_id + '</span>' +
                                                    '<span style="color: #ccc">&nbsp;</span>' +
                                                    (item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                    '<br>' +
                                                    (item.position ? '<span class="position" style="font-size: 9px; color: #ccc;">' + item.position + '</span>' : '') +
                                                    (item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                                                    //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                                                    '</div>';
                                            }
                                        },
                                        onChange: function(value) {
                                            /** how to get the details populating the fields automatically if an employee is selected from the selectize plugin? provided the information is already on the db at the time */
                                            /** name | dateofbirth | tax code number | ID card number or Passport number */
                                            if (value === undefined || value.length == 0) {
                                                /**array empty or does not exist*/
                                            } else {

                                            }
                                            $('#cb_edit_manualdownloadlist_details_form').addClass('dirty');
                                        }
                                    });

                                    var recipients_select_selectize = $recipients_select[0].selectize;
                                    var recipients_select_old_options = recipients_select_selectize.settings;
                                    var selectize_focus_handler = function(value, $item) {
                                        var width_to_be = $('.selectize-control').outerWidth();
                                        $('.selectize-dropdown-content').css('width', width_to_be);
                                    };
                                    recipients_select_selectize.on('focus', selectize_focus_handler);


                                    /** filter the shifts from the swapable shifts select */
                                    var item_remove_handler = function(value, $item) {
                                        $('#cb_edit_manualdownloadlist_details_form').addClass('dirty');
                                    };
                                    recipients_select_selectize.on('item_remove', item_remove_handler);

                                    $(window).resize(function() {
                                        var width_to_be = $('.selectize-control').outerWidth();
                                        $('.selectize-dropdown-content').css('width', width_to_be);
                                    });






                                    var calendar_date = $('#calendar_date').val();
                                    var recipients = $('#recipients').val();
                                    var advance_amount = $('#advance_amount').val();
                                    var reason = $('#reason').val();
                                    var payment_period_description = $('#payment_period_description').val();

                                    var currentdate = new Date();
                                    var datetime = "Last Sync: " + currentdate.getDate() +
                                        "/" + (currentdate.getMonth() + 1) +
                                        "/" + currentdate.getFullYear() +
                                        " @ " + currentdate.getHours() +
                                        ":" + currentdate.getMinutes() +
                                        ":" + currentdate.getSeconds();

                                    var edit_manualdownloadlist_previous_data = [];
                                    edit_manualdownloadlist_previous_data['edit_manualdownloadlist_previous_data'] = {
                                        "whenwasset": datetime,

                                        "edit_manualdownloadlist_calendar_date": calendar_date,
                                        "edit_manualdownloadlist_recipients": recipients,
                                        "edit_manualdownloadlist_advance_amount": advance_amount,
                                        "edit_manualdownloadlist_reason": reason,
                                        "edit_manualdownloadlist_payment_period_description": payment_period_description
                                    };
                                    sessionStorage.setItem('Br24_' + app.env() + '_edit_manualdownloadlistinfo_previous_data', JSON.stringify(edit_manualdownloadlist_previous_data['edit_manualdownloadlist_previous_data']));


                                    $('#cb_edit_manualdownloadlist_details_form').areYouSure();
                                    $('#cb_edit_manualdownloadlist_details_form').on('change', 'select', function() {
                                        $("#edit_manualdownloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#edit_manualdownloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of select tags

                                    $('#cb_edit_manualdownloadlist_details_form').on('change keypress', 'input', function() {
                                        $("#edit_manualdownloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#edit_manualdownloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of input: the change event take care of input of type "hidden" also

                                    $('#cb_edit_manualdownloadlist_details_form').on('change keypress', 'textarea', function() {
                                        $("#edit_manualdownloadlist_reset").prop('disabled', false).css('display', 'inline-block');
                                        $("#edit_manualdownloadlist_update").prop('disabled', false).css('display', 'inline-block');
                                    }); // take care of textareas                                    


                                    $("#edit_manualdownloadlist_reset").click(function() {
                                        var button_selector = $('#edit_manualdownloadlist_reset, #edit_manualdownloadlist_update');
                                        //hide all the buttons
                                        button_selector.prop('disabled', true).css('display', 'none');

                                        var previous_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_edit_manualdownloadlistinfo_previous_data'));

                                        /**console.log(previous_load['edit_manualdownloadlist_advance_amount']);*/
                                        $('#calendar_date').val(previous_load['edit_manualdownloadlist_calendar_date']);
                                        $('#advance_amount').val(previous_load['edit_manualdownloadlist_advance_amount']);
                                        //$('#advance_amount').formatNumber({ format: number_format_per_locale, locale: numberformat_locale });
                                        $('#reason').val(previous_load['edit_manualdownloadlist_reason']);
                                        $('#payment_period_description').val(previous_load['edit_manualdownloadlist_payment_period_description']);

                                        recipients_select_selectize.setValue(previous_load['edit_manualdownloadlist_recipients'], false);

                                        $('.has-error').removeClass('has-error');
                                        $('.help-block').css('display', 'none');
                                        $('#cb_edit_manualdownloadlist_details_form').trigger('reinitialize.areYouSure');
                                    });

                                    $("#edit_manualdownloadlist_update").click(function(e) {
                                        e.preventDefault();
                                        $('.alert_warning').css('display', 'none');
                                        $('.alert_success').css('display', 'none');

                                        var calendar_date = $('#calendar_date').val();
                                        var recipients = $('#recipients').val();
                                        var advance_amount = $('#advance_amount').val();
                                        var reason = $('#reason').val();
                                        var payment_period_description = $('#payment_period_description').val();

                                        var currentdate = new Date();
                                        var datetime = "Last Sync: " + currentdate.getDate() +
                                            "/" + (currentdate.getMonth() + 1) +
                                            "/" + currentdate.getFullYear() +
                                            " @ " + currentdate.getHours() +
                                            ":" + currentdate.getMinutes() +
                                            ":" + currentdate.getSeconds();

                                        var edit_custom_rc_message_schedule_submitted_data = [];
                                        edit_custom_rc_message_schedule_submitted_data['edit_custom_rc_message_schedule_submitted_data'] = {
                                            "whenwasset": datetime,

                                            "edit_manualdownloadlist_calendar_date": calendar_date,
                                            "edit_manualdownloadlist_recipients": recipients,
                                            "edit_manualdownloadlist_advance_amount": advance_amount,
                                            "edit_manualdownloadlist_reason": reason,
                                            "edit_manualdownloadlist_payment_period_description": payment_period_description

                                        };
                                        sessionStorage.setItem('Br24_' + app.env() + '_edit_manualdownloadlistinfo_submitted_data', JSON.stringify(edit_custom_rc_message_schedule_submitted_data['edit_custom_rc_message_schedule_submitted_data']));

                                        /** convert the number fields to unformatted number momentarily*/
                                        $("#advance_amount").parseNumber({ format: number_format_per_locale, locale: numberformat_locale });

                                        var formData = new FormData($('#cb_edit_manualdownloadlist_details_form')[0]);
                                        formData.append("recipients", recipients);

                                        NProgress.configure({ parent: '#cboxTitle', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                        NProgress.start();

                                        $('.cb_loader').css('display', 'block').css('cursor', 'wait');
                                        $('#cb_top').addClass('nprogress-busy').css('pointer-events', 'none');
                                        //app.util.fullscreenloading_start();

                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $("#edit_manualdownloadlist_details_table").data("token") } });
                                        app.ajax.formdata(app.data.manage_edit_manualdownloadlist, formData, null, function() {
                                            /**console.log(app.ajax.resultformdata);*/
                                            success_ajax_then_refresh = app.ajax.resultformdata.success;

                                            NProgress.done();
                                            $('.cb_loader').css('display', 'none').css('cursor', 'auto');
                                            $('#cb_top').removeClass('nprogress-busy').css('pointer-events', 'auto');

                                            if (app.ajax.resultformdata.success == true) {
                                                $('#cboxLoadedContent').css('background-color', '#4CAF50');
                                                $('.onSuccess_makeGreen').css('background-color', '#4CAF50');
                                                $('.ibox-tool-userid').css('color', 'white');
                                                $('#cb_edit_manualdownloadlist_details_form').css('display', 'none');
                                                close_colorbox_timer();
                                                $(document.body).css('pointer-events', 'none');
                                                app.util.fullscreenloading_start();
                                                if (app.ajax.resultformdata.process_penalties_accept_reject_table_sync == true) {
                                                    app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                }
                                            } else {
                                                $("#advance_amount").formatNumber({ format: number_format_per_locale, locale: numberformat_locale });

                                                $('.has-error').removeClass('has-error');
                                                $('.help-block').detach();

                                                $.each(app.ajax.resultformdata.errors, function(idx, val) {
                                                    app_ops.manage_manualdownloadlist.profile.foreach_handle_error_display(idx, val);
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
                                                    $('#calendar_date').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #calendar_date');
                                                    });
                                                    $('#recipients').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #recipients');
                                                    });
                                                    $('#advance_amount').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).nextAll('.help-block').css('display', 'none');
                                                        console.log('something changed in #advance_amount');
                                                    });
                                                    $('#reason').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #reason');
                                                    });
                                                    $('#payment_period_description').on('change keypress', function() {
                                                        $(this).parent('.form-group').removeClass('has-error');
                                                        $(this).next('.help-block').css('display', 'none');
                                                        console.log('something changed in #payment_period_description');
                                                    });
                                                }
                                            }
                                        });
                                    });

                                    $('#cb_edit_manualdownloadlist_details_form').on('keyup keypress', function(e) {
                                        var keyCode = e.keyCode || e.which;
                                        if (keyCode === 13) {
                                            e.preventDefault();
                                            return false;
                                        }
                                    });

                                    $('#cboxOverlay').off('click').on('click', function(event) {
                                        //console.log('clickedousideofcolorbox');
                                        var identifychanges = $('#cb_edit_manualdownloadlist_details_form').hasClass('dirty');
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
                                                            $("#cb_edit_manualdownloadlist.ajax").colorbox.close();
                                                            $('#cb_edit_manualdownloadlist_details_form').trigger('reinitialize.areYouSure');
                                                        }
                                                    },
                                                    cancel: {
                                                        text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                        action: function() {
                                                            //$.alert('');
                                                            return false;
                                                        }
                                                    },
                                                }
                                            });
                                        } else {
                                            $("#cb_edit_manualdownloadlist.ajax").colorbox.close();
                                            $('#cb_edit_manualdownloadlist_details_form').trigger('reinitialize.areYouSure');
                                        }
                                    });
                                },
                                onCleanup: function() {
                                    // app.data.selectize_employee_list_formated_json = '';
                                    // app.data.selectize_selected_employee_formated_json = '';
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
                                        var currentdate = new Date();
                                        var datetime = "Last Sync: " + currentdate.getDate() +
                                            "/" + (currentdate.getMonth() + 1) +
                                            "/" + currentdate.getFullYear() +
                                            " @ " + currentdate.getHours() +
                                            ":" + currentdate.getMinutes() +
                                            ":" + currentdate.getSeconds();
                                        var scroll = $(document).scrollTop();
                                        var edit_manualdownloadlist_scroll_position_data = [];
                                        edit_manualdownloadlist_scroll_position_data['edit_manualdownloadlist_scroll_position_data'] = {
                                            "whenwasset": datetime,
                                            "scroll": scroll
                                        };
                                        sessionStorage.setItem('Br24_' + app.env() + '_edit_manualdownloadlist_scroll_position_data', JSON.stringify(edit_manualdownloadlist_scroll_position_data['edit_manualdownloadlist_scroll_position_data']));
                                        window.manualdownloadlisttable.ajax.reload(null, false);
                                        app.util.fullscreenloading_end();
                                        window.manualdownloadlisttable.fixedHeader.adjust();
                                    }

                                    NProgress.configure({ parent: '#progress-bar-parent', showSpinner: false, easing: 'ease', speed: 880, trickleSpeed: 333, trickle: true });
                                    clearInterval(refreshIntervalId); //stop the timer called refreshIntervalId
                                    clearInterval(close_colorbox_refreshIntervalId);
                                    //app.util.fullscreenloading_end();
                                    sessionStorage.removeItem('Br24_' + app.env() + '_edit_manualdownloadlistinfo_previous_data');
                                    sessionStorage.removeItem('Br24_' + app.env() + '_edit_manualdownloadlistinfo_submitted_data');
                                },
                            });

                            $("a[name*='delete_manualdownloadlist_']").on('click', function(event) {
                                event.preventDefault();
                                var clicked_href = $(this).attr('href');
                                var clicked_href_id_encrypted = $(this).parent().parent().parent().data('workingshiftid');
                                var isReplaceable = $(this).is('[replaceable=true]');
                                var isDeleteable = $(this).is('[deleteable=true]');
                                var isEnableable = $(this).is('[enableable=true]');
                                if (isReplaceable == true) {
                                    $.confirm({
                                        title: eval("app.translations." + app.data.locale + ".title_text"),
                                        content: 'url:' + app.data.URL_replace_shift_with_another + '/' + clicked_href_id_encrypted,
                                        type: 'red',
                                        draggable: false,
                                        backgroundDismiss: 'cancel',
                                        escapeKey: true,
                                        animateFromElement: false,
                                        onAction: function(btnName) {
                                            $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                                        },
                                        onContentReady: function() {
                                            // bind to events

                                            // var jc = this;
                                            // this.$content.find('form').on('submit', function(e) {
                                            //     // if the user submits the form by pressing enter in the field.
                                            //     e.preventDefault();
                                            //     jc.$$formSubmit.trigger('click'); // reference the button and click it
                                            // });

                                            var select_list_working_shift_list = app.data.selectize_working_shifts_formated_json;
                                            /**console.log('select_list_employee_list=' + JSON.stringify(select_list_employee_list));*/

                                            var $select = $('#shifts').selectize({
                                                plugins: ['remove_button', 'optgroup_columns'],
                                                persist: false,
                                                maxItems: 1,
                                                mode: 'multi',
                                                /** when maxItems set to 1 multi keeps input behaving in the textinput/tagging way */
                                                placeholder: '-- Select default working shift --',
                                                valueField: 'id',
                                                labelField: 'name',
                                                searchField: ['name'],
                                                options: select_list_working_shift_list,
                                                /** list of all the viable employees on init */
                                                /**items: select_selected_list_employee_default_shifts,*/
                                                /** list of already selected employees on init*/
                                                hideSelected: true,
                                                openOnFocus: true,
                                                closeAfterSelect: true,
                                                render: {
                                                    item: function(item, escape) {
                                                        return '<div>' +
                                                            (item.name ? '<span class="name"><b>' + item.name + '</b></span>' : '') +
                                                            //'<span style="color: #ccc">&nbsp;</span>' +
                                                            //(item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                            //'<br>' +
                                                            //(item.position ? '<span class="position" style="font-size: 9px;">' + item.position + '</span>' : '') +
                                                            //'<br>' +
                                                            //(item.email ? '<span class="email" style="font-size: 9px;">' + escape(item.email) + '</span>' : '') +
                                                            '</div>';
                                                    },
                                                    option: function(item, escape) {
                                                        //var label = item.name || item.email;
                                                        //var caption = item.fullname ? item.email : null;
                                                        return '<div style="margin: 10px; border: 1px solid #000; padding: 10px;">' +
                                                            '<span class="label label-primary">' + item.name + '</span>' +
                                                            //'<span style="color: #ccc">&nbsp;</span>' +
                                                            //(item.fullname ? '<span class="fullname" style="font-size: 10px;">' + item.fullname + '</span>' : '') +
                                                            //'<br>' +
                                                            //(item.position ? '<span class="position" style="font-size: 9px; color: #ccc;">' + item.position + '</span>' : '') +
                                                            //(item.email ? ' - <span class="email" style="font-size: 9px; color: #ccc;">' + item.email + '</span>' : '') +
                                                            //(caption ? '<span class="caption" style="font-size: 10px;">' + escape(caption) + '</span>' : '') +
                                                            '</div>';
                                                    }
                                                },
                                                onChange: function(value) {
                                                    /** when change want to remove this option from the other select options */
                                                    //currently_selected_default_start_shift = value;
                                                    // swap_shift_selectize.removeOption(value);
                                                    // swap_shift_selectize.refreshOptions();
                                                },
                                                onItemAdd: function(value) {
                                                    $('#confirmbox_spacer').css('height', '0px');
                                                },
                                                onItemRemove: function(value) {
                                                    $('#confirmbox_spacer').css('height', '200px');
                                                },
                                                onBlur: function() {
                                                    $('#confirmbox_spacer').css('height', '0px');
                                                },
                                                onFocus: function() {
                                                    $('#confirmbox_spacer').css('height', '200px');
                                                }

                                            });
                                            var selectize = $select[0].selectize;
                                            var old_options = selectize.settings;

                                            selectize.focus();
                                        },
                                        buttons: {
                                            disable_replace: {
                                                text: 'Replace & Disable',
                                                btnClass: 'btn-blue',
                                                keys: ['shift'],
                                                action: function() {
                                                    var input = this.$content.find('select#shifts').val();
                                                    /**console.log(input);*/
                                                    if (input === undefined || input.length == 0) {
                                                        $.alert({
                                                            content: "Please select a working shift from the drop down.",
                                                            animateFromElement: false,
                                                            type: 'red'
                                                        });
                                                        return false;
                                                    } else {
                                                        /** have the ability to set the working shift disabled so that it is not visible to use */
                                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                                        app.util.nprogressinit();
                                                        var ajaxdata = {
                                                            'replace_with_working_shift': input
                                                        };

                                                        /** you will show a drop down select to choose which other shift to replace with and when that is done will be disabled */
                                                        app.ajax.jsonGET(clicked_href, ajaxdata, null, function() {
                                                            //console.log(app.ajax.result);
                                                            if (app.ajax.result.success == true) {
                                                                window.manualdownloadlisttable.ajax.reload(null, false);
                                                                app.util.fullscreenloading_end();
                                                                window.manualdownloadlisttable.fixedHeader.adjust();
                                                                app.util.nprogressdone();
                                                                if (app.ajax.result.process_penalties_accept_reject_table_sync == true) {
                                                                    app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                                }
                                                            } else {
                                                                app.util.nprogressdone();
                                                            }
                                                        });

                                                        /** so if the working shift being disabled is being used by a default shift schedule then advise if it should be swaped out with another shift */
                                                        /** then add the disabled flag to the working shift table so that it is no longer used in some views or the select boxes. */
                                                    }
                                                },
                                            },
                                            cancel: {
                                                text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                action: function() {
                                                    /**$.alert(eval("app.translations." + app.data.locale + ".working_shift_deletion_was_aborted"));*/
                                                }
                                            }
                                        },
                                    });
                                } else if (isDeleteable == true) {
                                    $.confirm({
                                        title: eval("app.translations." + app.data.locale + ".title_text"),
                                        content: eval("app.translations." + app.data.locale + ".are_you_sure_you_want_to_delete_this_schedule") + "\n",
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
                                                            window.manualdownloadlisttable.ajax.reload(null, false);
                                                            app.util.fullscreenloading_end();
                                                            window.manualdownloadlisttable.fixedHeader.adjust();
                                                            app.util.nprogressdone();
                                                            if (app.ajax.result.process_penalties_accept_reject_table_sync == true) {
                                                                app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                            }
                                                        } else {
                                                            app.util.nprogressdone();
                                                        }
                                                    });
                                                }
                                            },
                                            cancel: {
                                                text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                action: function() {
                                                    /**$.alert(eval("app.translations." + app.data.locale + ".working_shift_deletion_was_aborted"));*/
                                                }
                                            },
                                        }
                                    });
                                } else if (isEnableable == true) {
                                    $.confirm({
                                        title: eval("app.translations." + app.data.locale + ".title_text"),
                                        content: eval("app.translations." + app.data.locale + ".are_you_sure_you_want_to_enable_this_schedule") + "\n",
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
                                                            window.manualdownloadlisttable.ajax.reload(null, false);
                                                            app.util.fullscreenloading_end();
                                                            window.manualdownloadlisttable.fixedHeader.adjust();
                                                            app.util.nprogressdone();
                                                            if (app.ajax.result.process_penalties_accept_reject_table_sync == true) {
                                                                app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                            }
                                                        } else {
                                                            app.util.nprogressdone();
                                                        }
                                                    });
                                                }
                                            },
                                            cancel: {
                                                text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                action: function() {
                                                    /**$.alert(eval("app.translations." + app.data.locale + ".working_shift_deletion_was_aborted"));*/
                                                }
                                            },
                                        }
                                    });
                                } else {
                                    $.confirm({
                                        title: eval("app.translations." + app.data.locale + ".title_text"),
                                        content: eval("app.translations." + app.data.locale + ".are_you_sure_you_want_to_disable_this_schedule") + "\n",
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
                                                            window.manualdownloadlisttable.ajax.reload(null, false);
                                                            app.util.fullscreenloading_end();
                                                            window.manualdownloadlisttable.fixedHeader.adjust();
                                                            app.util.nprogressdone();
                                                            if (app.ajax.result.process_penalties_accept_reject_table_sync == true) {
                                                                app_ops.ajaxexample2tb2.profile.sync_penalties_acceptance_rejection_table();
                                                            }
                                                        } else {
                                                            app.util.nprogressdone();
                                                        }
                                                    });
                                                }
                                            },
                                            cancel: {
                                                text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                action: function() {
                                                    /**$.alert(eval("app.translations." + app.data.locale + ".working_shift_deletion_was_aborted"));*/
                                                }
                                            },
                                        }
                                    });
                                }
                            });


                            $("a[name*='redownload_manualdownloadlist_']").off('click').on('click', function(event) {
                                event.preventDefault();
                                var clicked_href = $(this).attr('href');
                                /**console.log(clicked_href);*/
                                /** if you could get the size of the zip to this via data attribute and also have the check whether the file is different using the signature from amazon */
                                var tr = $(this).closest('tr');
                                var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');

                                var redownload_manualdownloadlist_clicked_case_Id = $(this).data('case_id');
                                /** so that the page loads as quick we do this scan before every thing */
                                //$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

                                /** define outside so it can be used elsewhere in this scope */
                                var array_of_job_zips_details = null;

                                var jc_confirm_manualdownloadlisttable_row_redownload_button = $.confirm({
                                    //title: eval("app.translations." + app.data.locale + ".title_text"),
                                    title: "",
                                    content: '<div class="loader search_keynumber_input_loader" style="margin-left: 15px; height:88%; width:92.4%; display: block; margin-top: 7px; border-radius: 4px;"></div>' +
                                        '<span id="search_keynumber_input_loader_text" style="display: inline-block">Checking for Job ID = ' + redownload_manualdownloadlist_clicked_case_Id + '    ...     Please Wait</span>' + '\n' +
                                        '<span id="search_keynumber_input_text" style="display: none;">Download Job = ' + redownload_manualdownloadlist_clicked_case_Id + ' ? The tool found :-</span>\n' +
                                        '<table id="search_keynumber_input_table" cellspacing="0" width="100%" style="display: none; margin-top:12px"><tbody><tr>' +
                                        '<td style="width: 33.3%">' +
                                        '<input type="checkbox" style="margin-bottom: 14px;" class="mdl_checkbox_example form-control" checked>' +
                                        '</td>' +
                                        '<td style="width: 33.3%">' +
                                        '<input type="checkbox" style="margin-bottom: 14px;" class="mdl_checkbox_new form-control" checked>' +
                                        '</td>' +
                                        '<td style="width: 33.3%">' +
                                        '<input type="checkbox" style="margin-bottom: 14px;" class="mdl_checkbox_ready form-control" checked>' +
                                        '</td>' +
                                        '</tr>' +
                                        '<tr>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_example_size"></span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_new_size"></span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_ready_size"></span></td>' +
                                        '</tr>' +
                                        '<tr>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_example_label">Examples</span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_new_label">New</span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_ready_label">Ready</span></td>' +
                                        '</tr>' +
                                        '<tr>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_example_label_extra"></span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_new_label_extra"></span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_ready_label_extra"></span></td>' +
                                        '</tr>' +                                        
                                        '</tbody>' +
                                        '</table>',
                                    type: 'orange',
                                    draggable: true,
                                    dragWindowGap: 0,
                                    backgroundDismiss: false,
                                    escapeKey: 'cancel',
                                    animateFromElement: false,
                                    autoClose: false,
                                    onOpen: function() {

                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

                                        var ajaxdata = {
                                            'case_id': redownload_manualdownloadlist_clicked_case_Id,
                                            'encrypted_case_id': encrypted_case_id_uploading_to
                                        };

                                        app.ajax.jsonGET(app.data.urlGetZipDetailsOfJob + "/" + encrypted_case_id_uploading_to, ajaxdata, null, function() {
                                            /**console.log(app.ajax.result);*/
                                            if (app.ajax.result.success == true) {
                                                /** we setup the form accordingly */
                                                jc_confirm_manualdownloadlisttable_row_redownload_button.buttons.ok.show();
                                                jc_confirm_manualdownloadlisttable_row_redownload_button.buttons.cancel.show();
                                                jc_confirm_manualdownloadlisttable_row_redownload_button.buttons.ok.enable();
                                                jc_confirm_manualdownloadlisttable_row_redownload_button.buttons.cancel.enable();
                                                $('.search_keynumber_input_loader').css('display', 'none');
                                                $('#search_keynumber_input_loader_text').css('display', 'none');
                                                $('#search_keynumber_input_text').css('display', '');
                                                $('#search_keynumber_input_table').css('display', '');

                                                array_of_job_zips_details = app.ajax.result.array_of_job_zips_details;

                                                $.each(app.ajax.result.array_of_job_zips_details, function(idx, val) {
                                                    /** also want to show the user whether there is any difference between the previous downloaded and what is currently on amazon */
                                                    /** if there are no zips.. well then we can alert and then exit out */
                                                    if(idx == 'example'){
                                                        if(val.size == null){
                                                            $('.mdl_checkbox_example').remove();
                                                            $('.mdl_checkbox_example_size').remove();
                                                            $('.mdl_checkbox_example_label').remove();
                                                        }else{
                                                            $('.mdl_checkbox_example_size').html(formatBytes(val.size));
                                                            if(app.ajax.result.all_caseid_zips_local[redownload_manualdownloadlist_clicked_case_Id] !== undefined && app.ajax.result.all_caseid_zips_local[redownload_manualdownloadlist_clicked_case_Id][idx] !== undefined) {
                                                                if(val.lastModified > app.ajax.result.all_caseid_zips_local[redownload_manualdownloadlist_clicked_case_Id][idx].last_modified){
                                                                    /** it is a newer zip probably. */
                                                                    /** let them download it if they choose to */
                                                                }else{
                                                                    /** notify them that it is the same as the one previously downloaded */
                                                                    $('.mdl_checkbox_example').prop('checked', false).prop('disabled', true);
                                                                    $('.mdl_checkbox_example_label_extra').append('(unchanged)');
                                                                }
                                                            }else{
                                                                /** it is undefined so still show them the option to choose it to download */
                                                            }
                                                        }
                                                    }
                                                    if(idx == 'new'){
                                                        if(val.size == null){
                                                            $('.mdl_checkbox_new').remove();
                                                            $('.mdl_checkbox_new_size').remove();
                                                            $('.mdl_checkbox_new_label').remove();
                                                        }else{
                                                            $('.mdl_checkbox_new_size').html(formatBytes(val.size));
                                                            if(app.ajax.result.all_caseid_zips_local[redownload_manualdownloadlist_clicked_case_Id] !== undefined && app.ajax.result.all_caseid_zips_local[redownload_manualdownloadlist_clicked_case_Id][idx] !== undefined) {
                                                                if(val.lastModified > app.ajax.result.all_caseid_zips_local[redownload_manualdownloadlist_clicked_case_Id][idx].last_modified){
                                                                    /** it is a newer zip probably. */
                                                                    /** let them download it if they choose to */
                                                                }else{
                                                                    /** notify them that it is the same as the one previously downloaded */
                                                                    $('.mdl_checkbox_new').prop('checked', false).prop('disabled', true);
                                                                    $('.mdl_checkbox_new_label_extra').append('(unchanged)');
                                                                }
                                                            }else{
                                                                /** it is undefined so still show them the option to choose it to download */
                                                            }
                                                        }
                                                    }
                                                    if(idx == 'ready'){
                                                        if(val.size == null){
                                                            $('.mdl_checkbox_ready').remove();
                                                            $('.mdl_checkbox_ready_size').remove();
                                                            $('.mdl_checkbox_ready_label').remove();
                                                        }else{
                                                            $('.mdl_checkbox_ready_size').html(formatBytes(val.size));
                                                            if(app.ajax.result.all_caseid_zips_local[redownload_manualdownloadlist_clicked_case_Id] !== undefined && app.ajax.result.all_caseid_zips_local[redownload_manualdownloadlist_clicked_case_Id][idx] !== undefined) {
                                                                if(val.lastModified > app.ajax.result.all_caseid_zips_local[redownload_manualdownloadlist_clicked_case_Id][idx].last_modified){
                                                                    /** it is a newer zip probably. */
                                                                    /** let them download it if they choose to */
                                                                }else{
                                                                    /** notify them that it is the same as the one previously downloaded */
                                                                    $('.mdl_checkbox_ready').prop('checked', false).prop('disabled', true);
                                                                    $('.mdl_checkbox_ready_label_extra').append('(unchanged)');
                                                                }
                                                            }else{
                                                                /** it is undefined so still show them the option to choose it to download */
                                                            }
                                                        }
                                                    }
                                                });

                                                /**autoClose the long way .. */
                                                autoClosetimer();
                                                /** if the user clicks on the zip type checkboxes cancel the autocountdowntimer */
                                                $('.mdl_checkbox_example').on('click', function() {
                                                    $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                                                    clearInterval(autoCloserefreshIntervalId);
                                                });
                                                $('.mdl_checkbox_new').on('click', function() {
                                                    $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                                                    clearInterval(autoCloserefreshIntervalId);
                                                });
                                                $('.mdl_checkbox_ready').on('click', function() {
                                                    $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                                                    clearInterval(autoCloserefreshIntervalId);
                                                });
                                            } else {
                                                app_ops.manage_manualdownloadlist.profile.handlealertdisplayofappajaxresultsuccessisfalse(app.ajax.result.errors, redownload_manualdownloadlist_clicked_case_Id);
                                            }
                                        });
                                    },
                                    onAction: function(btnName) {
                                        $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                                    },
                                    buttons: {
                                        ok: {
                                            btnClass: 'btn-primary text-white',
                                            keys: ['enter'],
                                            text: eval("app.translations." + app.data.locale + ".okay_text"),
                                            isHidden: true,
                                            isDisabled: true,
                                            action: function() {

                                                /** based on the information on OKAY button click */
                                                var mdl_checkbox_example = this.$content.find('.mdl_checkbox_example').is(':checked');
                                                var mdl_checkbox_new = this.$content.find('.mdl_checkbox_new').is(':checked');
                                                var mdl_checkbox_ready = this.$content.find('.mdl_checkbox_ready').is(':checked');

                                                if (!mdl_checkbox_example && !mdl_checkbox_new && !mdl_checkbox_ready) {
                                                    $.alert('choose at least 1 folder');
                                                    $('.custom_loader_section').animate({
                                                        opacity: 0
                                                    }, {
                                                        duration: 500,
                                                        complete: function() {
                                                            $('.custom_loader_section').css('display', 'none');
                                                            $('.search_keynumber_input').prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                                                        }
                                                    });
                                                    return false;
                                                }

                                                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                                var ajaxdata = {
                                                    'case_id': redownload_manualdownloadlist_clicked_case_Id,
                                                    'mdl_checkbox_example': mdl_checkbox_example,
                                                    'mdl_checkbox_new': mdl_checkbox_new,
                                                    'mdl_checkbox_ready': mdl_checkbox_ready,
                                                    'array_of_job_zips_details': array_of_job_zips_details
                                                };

                                                app.util.fullscreenloading_start();
                                                app.util.nprogressinit();

                                                app.ajax.json(clicked_href, ajaxdata, null, function() {
                                                    /**console.log(app.ajax.result);*/
                                                    if (app.ajax.result.success == true) {
                                                        window.manualdownloadlisttable.ajax.reload(null, false);
                                                        app.util.fullscreenloading_end();
                                                        window.manualdownloadlisttable.fixedHeader.adjust();
                                                        app.util.nprogressdone();
                                                    } else {
                                                        app.util.fullscreenloading_end();
                                                        app.util.nprogressdone();
                                                    }
                                                });
                                            }
                                        },
                                        cancel: {
                                            text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                            isHidden: true,
                                            isDisabled: true,
                                            action: function() {
                                                clearInterval(autoCloserefreshIntervalId);
                                                $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');

                                                $('.custom_loader_section').animate({
                                                    opacity: 0
                                                }, {
                                                    duration: 500,
                                                    complete: function() {
                                                        $('.custom_loader_section').css('display', 'none');
                                                        $('.search_keynumber_input').prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                                                        jc_confirm_manualdownloadlisttable_row_redownload_button.close();
                                                    }
                                                });
                                            }
                                        },
                                    }
                                });
                            });

                            $("i[name*='edit_custom_star_rating_comment_']").on('click', function(event) {
                                event.preventDefault();
                                /**var original_html = $(this).parent().parent().html();*/
                                /**console.log(original_html);*/
                                var clicked_href_original_star_rating_comment = $(this).parent().attr('data-content');
                                /**console.log($(this).parent());*/
                                /**console.log(clicked_href_original_star_rating_comment);*/
                                var part_to_hide_unhide = $(this).parent();
                                /**console.log(part_to_hide_unhide);*/
                                var edit_custom_star_rating_comment_icon = $(this);
                                /**console.log(edit_custom_star_rating_comment_icon);*/
                                /** when we click on this thing we want to show a text input same like the internal notes etc */
                                /** because we remove the element from the DOM we have to renabled the on click function for it */
                                var change_star_rating_custom_note_case_Id = $(this).parent().parent().data('case_id');

                                /** it seems to be sending many trips to the db how to prevent that? */
                                if ($('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id + '').is(":visible") == true) {
                                    return false;
                                }

                                keep_track_of_last_clicked_item_to_react_after_reload = 'change_from_amount';
                                var target = event.target;
                                event.preventDefault();

                                var tr = $(this).parent().parent().closest('tr');
                                var td = $(this).parent().parent();
                                /**console.log(td);*/

                                /** count how many new lines there are */


                                var change_internal_note_original_html = $(this).html();
                                /**console.log(change_internal_note_original_html);*/

                                var row = window.manualdownloadlisttable.row(tr);
                                var thecellvalue = $(this).parent().attr('data-content');
                                /**console.log(thecellvalue);*/

                                var count_lines_in_comment = thecellvalue.split('<br>').length - 1;
                                /**console.log('count_lines_in_comment =' + count_lines_in_comment);*/
                                var td_height = td.height();
                                /**console.log(td_height);*/
                                td_height = td_height + (count_lines_in_comment * 20);


                                thecellvalue = thecellvalue.replace(/(?:<br>)/g, '\r\n');
                                var rowIndex = tr.data('rowindex');

                                var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');
                                keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = change_star_rating_custom_note_case_Id;
                                /**console.log(change_star_rating_custom_note_case_Id);*/
                                var timestamp_Id = $(this).data('date_ts');
                                // var idx = table.cell(this).index().column;
                                // var date_number = table.column(idx).header();
                                // var clcikedcolumnheader_value = $(date_number).html();
                                var input = $('<textarea id="edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id + '" cols="10" rows="5" charswidth="23"  style="white-space: pre-wrap; line-height: 15px; min-height: 30px; width: 100%; height: ' + td_height + 'px; display: block; z-index: 12; resize: vertical; color: black;"></textarea>');
                                input.val(thecellvalue);
                                part_to_hide_unhide.css('display', 'none');
                                td.append(input);


                                $(".popover").popover('hide');

                                $(document).on('keydown', '#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id, function(e) {
                                    var input = $(this);
                                    var oldVal = input.val();
                                    var regex = new RegExp(input.attr('pattern'), 'g');

                                    setTimeout(function() {
                                        var newVal = input.val();
                                        if (!regex.test(newVal)) {
                                            input.val(oldVal);
                                        }
                                    }, 0);
                                    /** if enter key is pressed allow it */
                                    if (e.keyCode == 13) {
                                        // input.blur();

                                    }
                                    /** if esc key is pressed return the thing back to the original ?? */
                                    if (e.keyCode == 27) {
                                        td.html(change_internal_note_original_html);
                                    }
                                });


                                $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).click(function(event) {
                                    event.stopImmediatePropagation();
                                    /***/
                                });
                                $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).focus(function() {
                                    // var save_this = $(this);
                                    // window.setTimeout(function() {
                                    //     save_this.select();
                                    // }, 30);
                                });
                                $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).focus();

                                $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).blur(function() {
                                    var edited_number = $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).val();
                                    edited_number = edited_number.replace(/(?:\r\n|\r|\n)/g, '<br>');
                                    /**console.log(edited_number);*/
                                    if (clicked_href_original_star_rating_comment == edited_number) {
                                        /** console.log('change_from_amount td.html(change_internal_note_original_html)'); */
                                        /**console.log('they were the same');*/
                                        part_to_hide_unhide.css('display', '');
                                        $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).remove();
                                        /** keep_track_of_last_clicked_item_to_react_after_reload = null; */
                                        /** keep_track_of_last_clicked_item_data_attribute_to_react_after_reload = null; */
                                    } else {
                                        /** its different */
                                        /**console.log('its different');*/

                                        /**app.util.nprogressinit();*/
                                        /**app.util.fullscreenloading_start();*/
                                        part_to_hide_unhide.css('display', '');
                                        /** but we replace the contents with the new values */
                                        /** we put back the star rating with the new text */
                                        part_to_hide_unhide.attr('data-content', edited_number);

                                        $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).remove();
                                        // var change_from_amount_replace_html = $("<span class='label label-primary currency_number_format'>" + edited_number + "</span>");
                                        // console.log(change_from_amount_replace_html);
                                        /**console.log('change_from_amount td.html(edited_number)');*/

                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                        var data = {
                                            'case_id': change_star_rating_custom_note_case_Id,
                                            'encrypted_case_id': encrypted_case_id_uploading_to,
                                            'new_star_rating_custom_note': edited_number
                                        };

                                        /** use ajax to send data to php */
                                        app.ajax.json(app.data.change_custom_star_rating_note_for_job, data, null, function() {
                                            /**console.log(app.ajax.result);*/
                                            success_ajax_then_refresh = app.ajax.result.success;
                                            if (app.ajax.result.success == true) {

                                                //td.html(original_html);
                                                part_to_hide_unhide.css('display', '');
                                                /**console.log(part_to_hide_unhide);*/
                                                part_to_hide_unhide.attr('data-content', edited_number);

                                                if (app.ajax.result.star_rating_comment == null) {
                                                    edit_custom_star_rating_comment_icon.css('display', 'none');
                                                    part_to_hide_unhide.attr('data-content', '');
                                                }
                                                // console.log(tr.children().next('.last_updated'));
                                                // console.log(app.ajax.result.updated_at);
                                                tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                                                tr.children().next('.last_updated').html(app.ajax.result.updated_at);


                                            } else {
                                                /**td.html(original_html);*/
                                                $('#edit_star_rating_comment_input_' + change_star_rating_custom_note_case_Id).remove();
                                                part_to_hide_unhide.css('display', '');
                                                part_to_hide_unhide.attr('data-content', clicked_href_original_star_rating_comment);
                                            }
                                        });
                                    }

                                    var refreshTimeout = null;
                                    $('[data-toggle="popover"]').popover({
                                        placement: 'auto bottom',
                                        trigger: "manual",
                                        html: true,
                                        animation: false
                                    }).on("mouseenter", function() {
                                        var _this = this;
                                        var popover_mouseover_function = function(this_elem) {
                                            refreshTimeout = setInterval(function() {
                                                $(this_elem).popover("show");
                                            }, 300);
                                        };
                                        popover_mouseover_function(_this);
                                        $(this).siblings(".popover").on("mouseleave", function() {
                                            $(_this).popover('hide');
                                        });
                                    }).on("mouseleave", function() {
                                        clearInterval(refreshTimeout);
                                        var _this = this;
                                        var popover_mouseleave_function = function() {
                                            setTimeout(function() {
                                                if (!$(".popover:hover").length) {
                                                    $(_this).popover("hide")
                                                } else {
                                                    popover_mouseleave_function();
                                                }
                                            }, 50);
                                        };
                                        popover_mouseleave_function();
                                    });
                                });
                            });

                            var if_input_exactly_follows_regular_expression_pattern = '';
                            $(document).off('keydown drop', '.search_keynumber_input').on('keydown drop', '.search_keynumber_input', function(e) {
                                var input = $(this);
                                var oldVal = input.val();
                                var regex = new RegExp(input.attr('pattern'), 'g');

                                input.trigger('click');

                                setTimeout(function() {
                                    var newVal = input.val();
                                    /**console.log('regex.test('+newVal+')', regex.test(newVal));*/
                                    if (regex.test(newVal)) {
                                        /**input.val(oldVal);*/
                                        if_input_exactly_follows_regular_expression_pattern = true;
                                    } else {
                                        if_input_exactly_follows_regular_expression_pattern = false;
                                    }
                                }, 0);

                                if (e.keyCode == 13) {
                                    /**console.log('enter pressed');*/
                                    input.blur();
                                }
                                if (e.keyCode == 27) {
                                    input.val("");
                                    input.blur();
                                }
                            });
                            $('.search_keynumber_input').off('click').on('click', function(event) {
                                event.stopImmediatePropagation();
                                /***/
                                /**console.log('element search_keynumber_input clicked');*/
                                $('.form_input input:focus+.label-name .content-name, .form_input input:valid+.label-name .content-name').animate({
                                    //"top": "5px",
                                    'fontSize': '10px',
                                    'color': '#008F68',
                                    'background': '#FFFFFF',
                                    'transform': 'translateY(-30%)'
                                }, 100);

                                $('.form_input .label-name-after').animate({
                                    'transform': 'translateX(0px, 0)'
                                }, 50);
                            });
                            $('.search_keynumber_input').off('focus').on('focus', function() {
                                var save_this = $(this);
                                window.setTimeout(function() {
                                    save_this.select();
                                }, 30);
                            });
                            $('.search_keynumber_input').off('blur').on('blur', function() {
                                var input = $(this);
                                /** if the input has a number need to do some validation on it.. to check if it could possibly be a jobid number ..*/
                                /** number needs to be eight digits long.. */
                                /** when they could easily make sure that the number they are trying to put in is correct to start with */
                                if (input.val() !== "" && if_input_exactly_follows_regular_expression_pattern) {
                                    /**console.log(input.val());*/
                                    var caseId_to_manually_download = input.val();

                                    input.prop('disabled', true).css('cursor', 'not-allowed').css('background', '').css('background-color', '-internal-light-dark(rgba(239, 239, 239, 0.3), rgba(59, 59, 59, 0.3))').css('border-color', 'rgba(118, 118, 118, 0.3)');
                                    $('.form_input').css('overflow', 'visible');

                                    /** If we can check if the job has been downloaded before? */
                                    /** but that could fail if after 30 days it gets removed from the shared folder. */
                                    /** we should probably remove it from the manual download list when it is cleared from the shared folder so it can de downloaded again manually */
                                    /** do we really want it to be as real time as possible? */
                                    var jc_confirm_search_keynumber_input_validation = $.confirm({
                                        //title: eval("app.translations." + app.data.locale + ".title_text"),
                                        title: "",
                                        content: '<div class="loader search_keynumber_input_loader" style="margin-left: 15px; height:88%; width:92.4%; display: block; margin-top: 7px; border-radius: 4px;"></div>' +
                                            '<span id="search_keynumber_input_loader_text" style="display: inline-block">Checking for Job ID = ' + caseId_to_manually_download + '    ...     Please Wait</span>' + '\n' +
                                            '<span id="search_keynumber_input_text" style="display: none;">Download Job = ' + caseId_to_manually_download + ' ? The tool found :-</span>\n' +
                                            '<table id="search_keynumber_input_table" cellspacing="0" width="100%" style="display: none; margin-top:12px"><tbody><tr>' +
                                            '<td style="width: 33.3%">' +
                                            '<input type="checkbox" style="margin-bottom: 14px;" class="mdl_checkbox_example form-control" checked>' +
                                            '</td>' +
                                            '<td style="width: 33.3%">' +
                                            '<input type="checkbox" style="margin-bottom: 14px;" class="mdl_checkbox_new form-control" checked>' +
                                            '</td>' +
                                            '<td style="width: 33.3%">' +
                                            '<input type="checkbox" style="margin-bottom: 14px;" class="mdl_checkbox_ready form-control" checked>' +
                                            '</td>' +
                                            '</tr>' +
                                            '<tr>' +
                                            '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_example_size"></span></td>' +
                                            '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_new_size"></span></td>' +
                                            '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_ready_size"></span></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                            '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_example_label">Examples</span></td>' +
                                            '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_new_label">New</span></td>' +
                                            '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_ready_label">Ready</span></td>' +
                                            '</tr>' + 
                                            '<tr>' +
                                            '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_example_label_extra"></span></td>' +
                                            '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_new_label_extra"></span></td>' +
                                            '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_ready_label_extra"></span></td>' +
                                            '</tr>' + 
                                            '</tbody>' +
                                            '</table>',
                                        type: 'orange',
                                        draggable: true,
                                        dragWindowGap: 0,
                                        backgroundDismiss: false,
                                        escapeKey: 'cancel',
                                        animateFromElement: false,
                                        autoClose: false,
                                        onOpen: function() {
                                            $('.custom_loader_section').css('display', '');
                                            $('.custom_loader_section').animate({
                                                opacity: 1
                                            }, {
                                                duration: 500,
                                                complete: function() {

                                                }
                                            });

                                            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

                                            var ajaxdata = {
                                                'case_id': caseId_to_manually_download
                                            };

                                            /** we need to have it do the validation client side. and report back to the client the feasibility of manually downloading the case number they have entered */
                                            /** use the timer function to do an ajax call to the endpoint to check the status of the xml scan. */
                                            app.ajax.json(app.data.manual_download_scan_initiate, ajaxdata, null, function() {
                                                /**console.log(app.ajax.result);*/
                                                if (app.ajax.result.success == true) {
                                                    /** do the validation here, so we can get the details to the form */
                                                    app.ajax.jsonGET(app.data.urlGetZipDetailsOfJob, ajaxdata, null, function() {
                                                        /**console.log(app.ajax.result);*/
                                                        if (app.ajax.result.success == true) {
                                                            /** we setup the form accordingly */
                                                            jc_confirm_search_keynumber_input_validation.buttons.ok.show();
                                                            jc_confirm_search_keynumber_input_validation.buttons.cancel.show();
                                                            jc_confirm_search_keynumber_input_validation.buttons.ok.enable();
                                                            jc_confirm_search_keynumber_input_validation.buttons.cancel.enable();
                                                            $('.search_keynumber_input_loader').css('display', 'none');
                                                            $('#search_keynumber_input_loader_text').css('display', 'none');
                                                            $('#search_keynumber_input_text').css('display', '');
                                                            $('#search_keynumber_input_table').css('display', '');

                                                            array_of_job_zips_details = app.ajax.result.array_of_job_zips_details;

                                                            $.each(app.ajax.result.array_of_job_zips_details, function(idx, val) {
                                                                /** also want to show the user whether there is any difference between the previous downloaded and what is currently on amazon */
                                                                if(idx == 'example'){
                                                                    if(val.size == null){
                                                                        $('.mdl_checkbox_example').remove();
                                                                        $('.mdl_checkbox_example_size').remove();
                                                                        $('.mdl_checkbox_example_label').remove();
                                                                    }else{
                                                                        $('.mdl_checkbox_example_size').html(formatBytes(val.size));
                                                                        if(app.ajax.result.all_caseid_zips_local[caseId_to_manually_download] !== undefined && app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx] !== undefined) {
                                                                            if(val.lastModified > app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx].last_modified){
                                                                                /** it is a newer zip probably. */
                                                                                /** let them download it if they choose to */
                                                                            }else{
                                                                                /** notify them that it is the same as the one previously downloaded */
                                                                                $('.mdl_checkbox_example').prop('checked', false).prop('disabled', true);
                                                                                $('.mdl_checkbox_example_label_extra').append('(unchanged)');
                                                                            }
                                                                        }else{
                                                                            /** it is undefined so still show them the option to choose it to download */
                                                                        }
                                                                    }
                                                                }
                                                                if(idx == 'new'){
                                                                    if(val.size == null){
                                                                        $('.mdl_checkbox_new').remove();
                                                                        $('.mdl_checkbox_new_size').remove();
                                                                        $('.mdl_checkbox_new_label').remove();
                                                                    }else{
                                                                        $('.mdl_checkbox_new_size').html(formatBytes(val.size));
                                                                        if(app.ajax.result.all_caseid_zips_local[caseId_to_manually_download] !== undefined && app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx] !== undefined) {
                                                                            if(val.lastModified > app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx].last_modified){
                                                                                /** it is a newer zip probably. */
                                                                                /** let them download it if they choose to */
                                                                            }else{
                                                                                /** notify them that it is the same as the one previously downloaded */
                                                                                $('.mdl_checkbox_new').prop('checked', false).prop('disabled', true);
                                                                                $('.mdl_checkbox_new_label_extra').append('(unchanged)');
                                                                            }
                                                                        }else{
                                                                            /** it is undefined so still show them the option to choose it to download */
                                                                        }
                                                                    }
                                                                }
                                                                if(idx == 'ready'){
                                                                    if(val.size == null){
                                                                        $('.mdl_checkbox_ready').remove();
                                                                        $('.mdl_checkbox_ready_size').remove();
                                                                        $('.mdl_checkbox_ready_label').remove();
                                                                    }else{
                                                                        $('.mdl_checkbox_ready_size').html(formatBytes(val.size));
                                                                        if(app.ajax.result.all_caseid_zips_local[caseId_to_manually_download] !== undefined && app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx] !== undefined) {
                                                                            if(val.lastModified > app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx].last_modified){
                                                                                /** it is a newer zip probably. */
                                                                                /** let them download it if they choose to */
                                                                            }else{
                                                                                /** notify them that it is the same as the one previously downloaded */
                                                                                $('.mdl_checkbox_ready').prop('checked', false).prop('disabled', true);
                                                                                $('.mdl_checkbox_ready_label_extra').append('(unchanged)');
                                                                            }
                                                                        }else{
                                                                            /** it is undefined so still show them the option to choose it to download */
                                                                        }
                                                                    }
                                                                }
                                                            });

                                                            /**autoClose the long way .. */
                                                            autoClosetimer();
                                                            /** if the user clicks on the zip type checkboxes cancel the autocountdowntimer */
                                                            $('.mdl_checkbox_example').on('click', function() {
                                                                $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                                                                clearInterval(autoCloserefreshIntervalId);
                                                            });
                                                            $('.mdl_checkbox_new').on('click', function() {
                                                                $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                                                                clearInterval(autoCloserefreshIntervalId);
                                                            });
                                                            $('.mdl_checkbox_ready').on('click', function() {
                                                                $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                                                                clearInterval(autoCloserefreshIntervalId);
                                                            });                                                            
                                                        } else {
                                                            app_ops.manage_manualdownloadlist.profile.handlealertdisplayofappajaxresultsuccessisfalse(app.ajax.result.errors, caseId_to_manually_download);
                                                        }
                                                    });
                                                } else {
                                                    app_ops.manage_manualdownloadlist.profile.handlealertdisplayofappajaxresultsuccessisfalse(app.ajax.result.errors, caseId_to_manually_download);
                                                }
                                            });
                                        },
                                        onAction: function(btnName) {
                                            $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                                        },
                                        buttons: {
                                            ok: {
                                                btnClass: 'btn-primary text-white',
                                                keys: ['enter'],
                                                text: eval("app.translations." + app.data.locale + ".okay_text"),
                                                isHidden: true,
                                                isDisabled: true,
                                                action: function() {

                                                    /** based on the information on OKAY button click */
                                                    var mdl_checkbox_example = this.$content.find('.mdl_checkbox_example').is(':checked');
                                                    var mdl_checkbox_new = this.$content.find('.mdl_checkbox_new').is(':checked');
                                                    var mdl_checkbox_ready = this.$content.find('.mdl_checkbox_ready').is(':checked');

                                                    if (!mdl_checkbox_example && !mdl_checkbox_new && !mdl_checkbox_ready) {
                                                        $.alert('choose at least 1 folder');
                                                        $('.custom_loader_section').animate({
                                                            opacity: 0
                                                        }, {
                                                            duration: 500,
                                                            complete: function() {
                                                                $('.custom_loader_section').css('display', 'none');
                                                                input.prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                                                            }
                                                        });
                                                        return false;
                                                    }

                                                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                                    var ajaxdata = {
                                                        'case_id': caseId_to_manually_download,
                                                        'mdl_checkbox_example': mdl_checkbox_example,
                                                        'mdl_checkbox_new': mdl_checkbox_new,
                                                        'mdl_checkbox_ready': mdl_checkbox_ready
                                                    };

                                                    app.util.fullscreenloading_start();
                                                    app.util.nprogressinit();

                                                    app.ajax.json(app.data.manual_download_actually_start_downloading, ajaxdata, null, function() {
                                                        /**console.log(app.ajax.result);*/
                                                        if (app.ajax.result.success == true) {
                                                            window.manualdownloadlisttable.ajax.reload(null, false);
                                                            $('.custom_loader_section').animate({
                                                                opacity: 0
                                                            }, {
                                                                duration: 500,
                                                                complete: function() {
                                                                    $('.custom_loader_section').css('display', 'none');
                                                                    $('.search_keynumber_input').prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                                                                }
                                                            });
                                                        } else {
                                                            app_ops.manage_manualdownloadlist.profile.handlealertdisplayofappajaxresultsuccessisfalse(app.ajax.result.errors, caseId_to_manually_download);
                                                        }
                                                        app.util.fullscreenloading_end();
                                                        app.util.nprogressdone();
                                                    });
                                                }
                                            },
                                            cancel: {
                                                text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                                isHidden: true,
                                                isDisabled: true,
                                                action: function() {
                                                    clearInterval(autoCloserefreshIntervalId);
                                                    $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');

                                                    $('.custom_loader_section').animate({
                                                        opacity: 0
                                                    }, {
                                                        duration: 500,
                                                        complete: function() {
                                                            $('.custom_loader_section').css('display', 'none');
                                                            input.prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                                                            jc_confirm_search_keynumber_input_validation.close();
                                                        }
                                                    });
                                                }
                                            },
                                        }
                                    });
                                } else {
                                    /**console.log('invalid caseid number');*/
                                    $('.form_input').css('overflow', 'hidden');
                                    input.prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');

                                    if (!if_input_exactly_follows_regular_expression_pattern && input.val() !== "") {
                                        $('.search_keynumber_input').css('background', 'repeating-linear-gradient(45deg, #ffffff, #ffffff 10px, #ff9900 10px, #ff9900 20px)');
                                    } else {
                                        $('.search_keynumber_input').css('background', '');

                                        /** if the page has lost focus this will also be triggered but when window is refocused the input is still being focused but the event never triggered .*/
                                        $('.form_input input:focus+.label-name .content-name, .form_input input:valid+.label-name .content-name').animate({
                                            'fontSize': '11px',
                                            'color': '#000',
                                            'background': '',
                                            'transform': 'translateY(0%)'
                                        }, 100);
                                        var width_of_search_keynumber_input = $('.search_keynumber_input').outerWidth();
                                        $('.form_input .label-name-after').animate({
                                            'transform': 'translateX(-' + width_of_search_keynumber_input + 'px, 0)'
                                        }, 50);
                                    }
                                }
                            });
                            $('.search_keynumber_input').off('drop').on("drop", function(event) {
                                var save_this = $(this);
                                window.setTimeout(function() {
                                    save_this.focus();
                                    $('.form_input input:focus+.label-name .content-name, .form_input input:valid+.label-name .content-name').animate({
                                        //"top": "5px",
                                        'fontSize': '10px',
                                        'color': '#008F68',
                                        'background': '#FFFFFF',
                                        'transform': 'translateY(-30%)'
                                    }, 100);

                                    $('.form_input .label-name-after').animate({
                                        'transform': 'translateX(0px, 0)'
                                    }, 50);
                                    save_this.blur();
                                    /**save_this.focus();*/
                                }, 30);
                            });
                        });

                        $("div[name*='edit_custom_star_rating_']").rateit({ max: 5, step: .5 });

                        // $('.rateit').on('beforerated', function(e, value) {
                        //     e.preventDefault();

                        //     var name_attr = $(this).attr('name');
                        //     var edit_icon_selector = $(this).next('.fa');
                        //     /**console.log(edit_icon_selector);*/
                        //     var thehuh = $(this).parent();
                        //     /**console.log(thehuh);*/

                        //     var td = $(this).parent().parent();
                        //     var tr = $(this).parent().parent().closest('tr');
                        //     /**console.log(td);*/

                        //     var change_status_case_Id = $(this).parent().parent().data('case_id');
                        //     var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');

                        //     // console.log(change_status_case_Id);
                        //     // console.log(encrypted_case_id_uploading_to);

                        //     /** prompt if should include a comment */
                        //     $.confirm({
                        //         title: 'Setting ' + value + ' Stars',
                        //         content: '' +
                        //             '<form action="" class="formName">' +
                        //             '<div class="form-group">' +
                        //             '<label>Enter something here</label>' +
                        //             '<textarea class="star_rating_comment form-control" required cols="10" rows="5" charswidth="23"  style="white-space: pre-wrap; line-height: 15px; min-height: 30px; max-height: 600px; width: 100%; height: 200px; display: block; z-index: 12; resize: vertical;"/>' +
                        //             '</textarea>' +
                        //             '</div>' +
                        //             '</form>',
                        //         buttons: {
                        //             formSubmit: {
                        //                 text: 'Submit',
                        //                 btnClass: 'btn-blue',
                        //                 action: function() {
                        //                     var star_rating_comment = this.$content.find('.star_rating_comment').val();

                        //                     /**console.log(star_rating_comment);*/
                        //                     /** here we take the contents of the form and send it as the star comments and dont forget the value of stars */
                        //                     $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                        //                     var data = {
                        //                         'case_id': change_status_case_Id,
                        //                         'encrypted_case_id': encrypted_case_id_uploading_to,
                        //                         'new_star_rating_comment': star_rating_comment,
                        //                         'new_star_rating': value
                        //                     };

                        //                     app.ajax.json(app.data.change_star_rating_for_job, data, null, function() {
                        //                         /**console.log(app.ajax.result);*/
                        //                         success_ajax_then_refresh = app.ajax.result.success;
                        //                         if (app.ajax.result.success == true) {
                        //                             if (app.ajax.result.star_rating_comment == '' || app.ajax.result.star_rating_comment == null) {
                        //                                 edit_icon_selector.css('display', 'none');
                        //                                 $(this).parent().attr('data-content', '----------------------');
                        //                                 /**thehuh.popover('destroy');*/
                        //                             } else {
                        //                                 /** when you put it in place then you need to make the trigger */
                        //                                 edit_icon_selector.css('display', '');
                        //                                 thehuh.attr('data-content', app.ajax.result.star_rating_comment);
                        //                             }
                        //                             /** if the comment is blank then remove the icon for this job id also */
                        //                             /** if the comment is not blank need to show the icon and give it the trigger */
                        //                             $("div[name='" + name_attr + "']").rateit('value', app.ajax.result.star_rating);

                        //                             tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                        //                             tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                        //                         } else {
                        //                             /** we don't change anything put back to what it was and alert  */
                        //                         }
                        //                     });

                        //                     var refreshTimeout = null;
                        //                     $('[data-toggle="popover"]').popover({
                        //                         placement: 'auto bottom',
                        //                         trigger: "manual",
                        //                         html: true,
                        //                         animation: false
                        //                     }).on("mouseenter", function() {
                        //                         var _this = this;
                        //                         var popover_mouseover_function = function(this_elem) {
                        //                             refreshTimeout = setInterval(function() {
                        //                                 $(this_elem).popover("show");
                        //                             }, 300);
                        //                         };
                        //                         popover_mouseover_function(_this);
                        //                         $(this).siblings(".popover").on("mouseleave", function() {
                        //                             $(_this).popover('hide');
                        //                         });
                        //                     }).on("mouseleave", function() {
                        //                         clearInterval(refreshTimeout);
                        //                         var _this = this;
                        //                         var popover_mouseleave_function = function() {
                        //                             setTimeout(function() {
                        //                                 if (!$(".popover:hover").length) {
                        //                                     $(_this).popover("hide")
                        //                                 } else {
                        //                                     popover_mouseleave_function();
                        //                                 }
                        //                             }, 50);
                        //                         };
                        //                         popover_mouseleave_function();
                        //                     });
                        //                 }
                        //             },
                        //             cancel: function() {
                        //                 //close
                        //             },
                        //         },
                        //         onContentReady: function() {
                        //             // bind to events
                        //             var jc = this;
                        //             this.$content.find('form').on('submit', function(e) {
                        //                 // if the user submits the form by pressing enter in the field.
                        //                 e.preventDefault();
                        //                 jc.$$formSubmit.trigger('click'); // reference the button and click it
                        //             });
                        //         }
                        //     });
                        // });


                        // $('.rateit').on('beforereset', function(e) {
                        //     e.preventDefault();

                        //     var name_attr = $(this).attr('name');
                        //     var edit_icon_selector = $(this).next('.fa');
                        //     /**console.log(edit_icon_selector);*/
                        //     var thehuh = $(this).parent();
                        //     /**console.log(thehuh);*/

                        //     var td = $(this).parent().parent();
                        //     var tr = $(this).parent().parent().closest('tr');
                        //     /**console.log(td);*/

                        //     var change_status_case_Id = $(this).parent().parent().data('case_id');
                        //     var encrypted_case_id_uploading_to = tr.data('encrypted_case_id');

                        //     // console.log(change_status_case_Id);
                        //     // console.log(encrypted_case_id_uploading_to);

                        //     $.confirm({
                        //         title: 'Reset Star Rating?',
                        //         content: 'This will also clear the comment',
                        //         type: 'red',
                        //         draggable: true,
                        //         dragWindowGap: 0,
                        //         backgroundDismiss: 'cancel',
                        //         escapeKey: true,
                        //         animateFromElement: false,
                        //         onAction: function(btnName) {
                        //             $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                        //         },
                        //         buttons: {
                        //             ok: {
                        //                 btnClass: 'btn-primary text-white',
                        //                 keys: ['enter'],
                        //                 text: eval("app.translations." + app.data.locale + ".okay_text"),
                        //                 action: function() {

                        //                     $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                        //                     var data = {
                        //                         'case_id': change_status_case_Id,
                        //                         'encrypted_case_id': encrypted_case_id_uploading_to,
                        //                         'new_star_rating_comment': null,
                        //                         'new_star_rating': 0
                        //                     };

                        //                     app.ajax.json(app.data.reset_star_rating_for_job, data, null, function() {
                        //                         /**console.log(app.ajax.result);*/
                        //                         success_ajax_then_refresh = app.ajax.result.success;
                        //                         if (app.ajax.result.success == true) {
                        //                             /** change the value */
                        //                             //app.ajax.result.star_rating_comment

                        //                             if (app.ajax.result.star_rating_comment == '' || app.ajax.result.star_rating_comment == null) {
                        //                                 edit_icon_selector.css('display', 'none');
                        //                                 thehuh.attr('data-content', '');
                        //                                 thehuh.popover('destroy');
                        //                             } else {
                        //                                 /** when you put it in place then you need to make the trigger */
                        //                                 edit_icon_selector.css('display', '');
                        //                                 /** get the comment and put it in the popover */
                        //                                 $(this).parent().attr('data-content', app.ajax.result.star_rating_comment);
                        //                             }
                        //                             /** if the comment is blank then remove the icon for this job id also */
                        //                             /** if the comment is not blank need to show the icon and give it the trigger */
                        //                             $("div[name='" + name_attr + "']").rateit('value', app.ajax.result.star_rating);

                        //                             tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                        //                             tr.children().next('.last_updated').html(app.ajax.result.updated_at);

                        //                         } else {
                        //                             /** do nothing to change it */
                        //                         }
                        //                     });
                        //                 }
                        //             },
                        //             cancel: {
                        //                 text: eval("app.translations." + app.data.locale + ".cancel_text"),
                        //                 action: function() {
                        //                     /** cancel out */
                        //                     /***/
                        //                 }
                        //             },
                        //         }
                        //     });
                        // });

                        // $(".rateit").bind('over', function(event, value) {
                        //     //$('#hover6').text('Hovering over: ' + value);
                        //     /**console.log('Hovering over: ' + value);*/
                        // });

                        app_ops.manage_manualdownloadlist.profile.handle_show_hide_columns_on_this_table();


                        // $(".keyboardshortcutcombinationtolinktosharedfolder").mousedown(function(event) {
                        //     if (event.ctrlKey == true && event.shiftKey == true && event.altKey == true) {
                        //         /** if the button is highlighted it affects this fucntionality */
                        //         if($(event.target).hasClass('highlight_span')){
                        //             if ($(event.target).parent()[0].href !== undefined) {
                        //                 if ($(event.target).parent()[0].href.indexOf("/uploadfiles/") >= 0) {
                        //                     if ($(event.target).parent()[0].href.indexOf("192.168.1.3") >= 0) {
                        //                         // console.log('It has already switched url variable down so do nothing');
                        //                     } else {
                        //                         var _href = $(event.target).parent()[0].href;
                        //                         var alt_href = $(event.target).parent().data('alt_href');
                        //                         /**console.log(_href);*/
                        //                         /**console.log(alt_href);*/
                        //                         $(event.target).parent().attr("href", alt_href);
                        //                         $(event.target).parent().attr("data-backup_href", _href);
                        //                     }
                        //                 }
                        //             }
                        //         }else{
                        //             //console.log('keyboardshortcutcombinationtolinktosharedfolder link with shift mouse down');
                        //             if (event.target.href !== undefined) {
                        //                 if (event.target.href.indexOf("/uploadfiles/") >= 0) {
                        //                     if (event.target.href.indexOf("192.168.1.3") >= 0) {
                        //                         // console.log('It has already switched url variable down so do nothing');
                        //                     } else {
                        //                         var _href = event.target.href;
                        //                         var alt_href = $(this).data('alt_href');
                        //                         $(this).attr("href", alt_href);
                        //                         $(this).attr("data-backup_href", _href);
                        //                     }
                        //                 }
                        //             }
                        //         }
                        //     }
                        // }).mouseup(function(event) {
                        //     //console.log('keyboardshortcutcombinationtolinktosharedfolder link with shift mouse up');
                        // }).mouseleave(function(event) {
                        //     /**console.log(event);*/
                        //     //console.log('keyboardshortcutcombinationtolinktosharedfolder link with shift mouse leave');
                        //     if($(event.target).hasClass('highlight_span')){
                        //         if ($(event.target).parent()[0].href !== undefined) {
                        //             if ($(event.target).parent()[0].href.indexOf("/uploadfiles/") >= 0) {
                        //                 if ($(event.target).parent()[0].href.indexOf("192.168.1.3") >= 0) {
                        //                     var backup_href = $(event.target).parent().data('backup_href');
                        //                     $(event.target).parent().attr("data-backup_href", "");
                        //                     $(event.target).parent().attr("href", backup_href);
                        //                 }
                        //             } else {
                        //                 if ($(event.target).parent()[0].href.indexOf("192.168.1.3") >= 0) {
                        //                     var backup_href = $(event.target).parent().data('backup_href');
                        //                     $(event.target).parent().attr("data-backup_href", "");
                        //                     $(event.target).parent().attr("href", backup_href);
                        //                 }
                        //             }
                        //         }                                
                        //     }else{
                        //         if (event.target.href !== undefined) {
                        //             if (event.target.href.indexOf("/uploadfiles/") >= 0) {
                        //                 if (event.target.href.indexOf("192.168.1.3") >= 0) {
                        //                     var backup_href = $(this).data('backup_href');
                        //                     $(this).attr("data-backup_href", "");
                        //                     $(this).attr("href", backup_href);
                        //                 }
                        //             } else {
                        //                 if (event.target.href.indexOf("192.168.1.3") >= 0) {
                        //                     var backup_href = $(this).data('backup_href');
                        //                     $(this).attr("data-backup_href", "");
                        //                     $(this).attr("href", backup_href);
                        //                 }
                        //             }
                        //         }
                        //     }                            
                        // });
                        app_ops.manage_manualdownloadlist.profile.live_update_download_progress_of_manual_downloaded_jobs(json.data);
                    },
                    initComplete: function(settings, json) {
                        var isSectionAccounting = app.data.section.indexOf("accounting"); //>= 0 if finds //-1 if it does not find
                        var canWRITEaccounting = app.data.auth_user_permissions.indexOf("WRITE accounting"); //>= 0 if finds //-1 if it does not find
                        var mapr_action_column = window.manualdownloadlisttable.column(app.conf.table.filterColumn.managemanualdownloadlistInfo.action);
                        /**console.log(app.data.section);*/
                        /**console.log(isSectionAccounting);*/
                        /**console.log(app.data.auth_user_permissions);*/
                        /**console.log(canWRITEaccounting);*/
                        // if (isSectionAccounting >= 0 && canWRITEaccounting >= 0) {
                        //     mapr_action_column.visible(true);
                        //     $('#manualdownloadlistTable .dataTables_empty').attr('colspan', app.conf.table.filterColumn.managemanualdownloadlistInfo.action + 1);
                        // } else {
                        //     mapr_action_column.visible(false);
                        //     $('#manualdownloadlistTable .dataTables_empty').attr('colspan', app.conf.table.filterColumn.managemanualdownloadlistInfo.action);
                        // }
                    }
                });

                var counting_ctd = 1;
                var $period_datepicker = $('.period_datepicker');
                $period_datepicker.bind('keydown', function(e) {
                    if (e.which == 13) {
                        e.stopImmediatePropagation();
                    }
                    if (e.which == 27) {
                        $(this).blur();
                    }
                }).datepicker({
                    dateFormat: prefered_dateFormat,
                    showMonthAfterYear: true,
                    numberOfMonths: 2,
                    showCurrentAtPos: 1,
                    changeMonth: true,
                    changeYear: true,
                    yearRange: "-2:+2",
                    showOtherMonths: false,
                    selectOtherMonths: false,
                    toggleActive: true,
                    todayHighlight: false,
                    minDate: new Date(minD_YYYY, minD_MM, minD_DD),
                    maxDate: new Date(maxD_YYYY, maxD_MM, maxD_DD),
                    autoclose: true,
                    defaultDate: defaultDateVARIABLE,
                    onSelect: function() {
                        /**you need to format the date before sending */
                        /** from locale to expected YYYY-MM-DD */
                        var theselecteddate = $('.period_datepicker').val();
                        //console.log(theselecteddate);
                        var from = theselecteddate.split(delimiter_for_splitting_variable);
                        //console.log(from);
                        var datetogoto = null;
                        var default_dateYYY = null;
                        var default_dateMM = null;
                        var default_dateDD = null;
                        if (app.data.locale === 'vi') {
                            var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                            default_dateYYY = parseInt(from[2]);
                            default_dateMM = parseInt(from[1]);
                            default_dateDD = parseInt(from[0]);
                        } else if (app.data.locale === 'en') {
                            var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                            default_dateYYY = parseInt(from[2]);
                            default_dateMM = parseInt(from[1]);
                            default_dateDD = parseInt(from[0]);
                        } else if (app.data.locale === 'de') {
                            var datetogoto = parseInt(from[2]) + '-' + parseInt(from[1]) + '-' + parseInt(from[0]);
                            default_dateYYY = parseInt(from[2]);
                            default_dateMM = parseInt(from[1]);
                            default_dateDD = parseInt(from[0]);
                        }

                        //console.log(datetogoto);

                        var d = new Date(datetogoto);
                        var year_full = d.getFullYear();
                        var month_short = d.toLocaleString('default', { month: 'short' });
                        $('#attendance_status_change_period').val(month_short + ', ' + year_full);
                        var data = { '_token': $('meta[name="csrf-token"]').attr('content') };
                        app.ajax.jsonGET(app.data.timesheet_period.gotoUrl + '/' + datetogoto, data, null, function() {
                            NProgress.start();
                            app.util.fullscreenloading_start();

                            //console.log(app.ajax.result);
                            success_ajax_then_refresh = app.ajax.result.success;
                            if (app.ajax.result.success == true) {
                                /** you cannot simply repload the table you need to clear the tab twoweektimesheetshiftplannerInfo and then reinit the table will look bad won't it */
                                /** the headers are also in need of */
                                app.data.total_working_days = null;
                                app.data.total_working_time = null;
                                app.data.total_overtime = null;
                                app.data.total_fines = null;

                                $('#total_working_days').html('--');
                                $('#total_working_time').html('--:--:--');
                                $('#total_overtime').html('--:--:--');
                                $('#total_working_time_as_of_today').html('--:--:--');
                                $('#total_fines').html('--');
                                $('#attendance_status_change_period').val(month_short + ', ' + year_full);
                                defaultDateVARIABLE = new Date(default_dateYYY, default_dateMM, default_dateDD);
                                $period_datepicker.datepicker("option", "defaultDate", defaultDateVARIABLE);
                                $(".ui-state-active").removeClass('ui-state-active');

                                report_date_value = theselecteddate;
                                //console.log('redrawing');
                                window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                                window.manualdownloadlisttable.draw();

                            } else {
                                /** */
                                /** */
                                //console.log('NOT working');
                                NProgress.done();
                                app.util.fullscreenloading_end();
                            }
                        });
                    },
                    onClose: function() {
                        var addressinput = $(this).val();
                        /**var patt = new RegExp("(?:19|20)[0-9]{2}-(?:(?:[1-9]|0[1-9]|1[0-2])-(?:[1-9]|0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");*/
                        var res = patt.test(addressinput);

                        // if (res == true) {
                        //     $('#contract_date').closest('td').removeClass('has-error');
                        //     $('#contract_date').nextAll('.help-block').css('display', 'none');

                        //     $(this).blur();
                        //     counting_ctd = 1;
                        // } else {
                        //     $("#contract_date").closest("td").addClass("has-error");
                        //     if (counting_ctd == 1) {
                        //         $("#contract_date").after('<span class="help-block"><strong>' + eval("app.translations."+app.data.locale+".you_need_to_enter_a_valid_date_in_the_format") + ' ' + prefered_dateFormat_placeholder + ' ' + eval("app.translations."+app.data.locale+".or_choose_from_the_calendar") + '</strong></span>');
                        //     } else {
                        //         // it exists
                        //     }
                        //     counting_ctd++;
                        //     $(this).blur();
                        // }
                    },
                    beforeShow: function(input, obj) {
                        // $period_datepicker.after($period_datepicker.datepicker('widget'));
                        var the_input_top = $('.period_datepicker').offset().top;
                        var the_input_left = $('.period_datepicker').offset().left;
                        setTimeout(function() {
                            $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 353).css('z-index', 102);
                            $('#ui-datepicker-div').find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day').find('a').removeClass('ui-state-active');
                        }, 0);
                    },
                    onChangeMonthYear: function() {
                        var the_input_top = $('.period_datepicker').offset().top;
                        var the_input_left = $('.period_datepicker').offset().left;
                        setTimeout(function() {
                            $('#ui-datepicker-div').css('position', 'realtive').css('top', the_input_top + 16).css('left', the_input_left - 353).css('z-index', 102);
                            $('#ui-datepicker-div').find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day').find('a').removeClass('ui-state-active');
                        }, 0);
                    },
                    beforeShowDay: function(date) {
                        var formated = formatDate(date);
                        var your_date = [];
                        /** the problem is that the array is not completely cleared when it is changed.. on table init clear the app.data.timesheet_period heap */
                        your_dates = Object.keys(app.data.timesheet_period.range_array_days_formated).map(function(key) {
                            return app.data.timesheet_period.range_array_days_formated[key];
                        });
                        // check if date is in your array of dates
                        function formatDate(date) {
                            var d = new Date(date),
                                month = '' + (d.getMonth() + 1),
                                day = '' + d.getDate(),
                                year = d.getFullYear();

                            if (month.length < 2) month = '0' + month;
                            if (day.length < 2) day = '0' + day;

                            return [year, month, day].join('-');
                        }

                        var todaydate = app.data.timesheet_period.today;

                        function formattodayDate(todaydate) {
                            var d = new Date(todaydate),
                                month = '' + (d.getMonth() + 1),
                                day = '' + d.getDate(),
                                year = d.getFullYear();

                            if (month.length < 2) month = '0' + month;
                            if (day.length < 2) day = '0' + day;

                            return [year, month, day].join('-');
                        }


                        var highlight_today = formattodayDate(todaydate);
                        if (formated == highlight_today) {
                            return [true, "ui-state-active shift_planner_datepicker_today", ''];
                        }

                        //console.log('formated='+formated);
                        //console.log('your_dates=' + your_dates);
                        if ($.inArray(formated, your_dates) != -1) {
                            // if it is return the following.
                            return [true, 'ui-state-active', ''];
                        } else {
                            // default
                            return [true, '', ''];
                        }
                    }
                });

                // app_ops.manage_manualdownloadlist.profile.filter.byTeam(window.manualdownloadlisttable);
                // app_ops.manage_manualdownloadlist.profile.filter.byPosition(window.manualdownloadlisttable);
                // app_ops.manage_manualdownloadlist.profile.filter.byStatus(window.manualdownloadlisttable);
                //app_ops.manage_manualdownloadlist.profile.filter.clearallfilter(window.manualdownloadlisttable);
                app_ops.manage_manualdownloadlist.profile.filter.byJobStatus(window.manualdownloadlisttable);
                app_ops.manage_manualdownloadlist.profile.filter.byGlobalSearch(window.manualdownloadlisttable);
                app_ops.manage_manualdownloadlist.profile.filter.byColVis(window.manualdownloadlisttable);
                //app_ops.manage_manualdownloadlist.profile.filter.byAssignee(window.manualdownloadlisttable);
                //app_ops.manage_manualdownloadlist.profile.filter.byEnabledDisabled(window.manualdownloadlisttable);
                var isSectionAccounting = app.data.section.indexOf("accounting"); //>= 0 if finds //-1 if it does not find
                var canWRITEexport = app.data.auth_user_permissions.indexOf("WRITE export"); //>= 0 if finds //-1 if it does not find
                if (isSectionAccounting >= 0 && canWRITEexport >= 0) {
                    //app_ops.manage_manualdownloadlist.profile.filter.exportExcel(window.manualdownloadlisttable);
                }
                $('select#status_filter').multiselect({ columns: 3, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#team_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#position_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#enabled_disabled_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#jobstatus_filter').multiselect({ columns: 1, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });
                $('select#colVis_show_hide_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'None' } });
                $('select#assignees_filter').multiselect({ columns: 2, maxPlaceholderOpts: 0, search: true, texts: { placeholder: 'All' } });

                var filter_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_managemanualdownloadlistTable_table_manualdownloadlistTable'));
                if (filter_load !== null) {
                    // console.log("status selected before = " + filter_load['filters']['status']);
                    // console.log("team selected before = " + filter_load['filters']['team']);
                    // console.log("position selected before = " + filter_load['filters']['position']);
                    if (filter_load['filters']['global_search'] !== "") {
                        $("input[id='global_search_filter']").val(filter_load['filters']['global_search']);
                    }

                    if (filter_load['filters']['status_cb'] !== "") {
                        var array = filter_load['filters']['status_cb'].split('|');
                        $("select#status_filter").val(array);
                        $("select#status_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['team_cb'] !== "") {
                        var array = filter_load['filters']['team_cb'].split('|');
                        $("select#team_filter").val(array);
                        $("select#team_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['position_cb'] !== "") {
                        var array = filter_load['filters']['position_cb'].split('|');
                        $("select#position_filter").val(array);
                        $("select#position_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['editor_level_cb'] !== "") {
                        var array = filter_load['filters']['editor_level_cb'].split('|');
                        $("select#editor_level_filter").val(array);
                        $("select#editor_level_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['job_status_cb'] !== "") {
                        var array = filter_load['filters']['job_status_cb'].split('|');
                        $("select#jobstatus_filter").val(array);
                        $("select#jobstatus_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['byEnabledDisabled'] !== "") {
                        var array = filter_load['filters']['byEnabledDisabled'];
                        $("select#enabled_disabled_filter").val(array);
                        $("select#enabled_disabled_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['byHideShowColumn_val'] !== "") {
                        var array = filter_load['filters']['byHideShowColumn_val'];
                        /**console.log(array);*/
                        $("select#colVis_show_hide_filter").val(array);
                        $("select#colVis_show_hide_filter").multiselect('reload');
                    }

                    if (filter_load['filters']['byShowColumn'] !== "") {
                        var array = filter_load['filters']['byShowColumn'];
                        /**console.log(array);*/
                    }
                    if (filter_load['filters']['byHideShowColumn'] !== "") {
                        var array = filter_load['filters']['byHideShowColumn'];
                        /**console.log(array);*/
                    }

                    if (filter_load['filters']['assignee_cb'] !== "") {
                        var array = filter_load['filters']['assignee_cb'].split('|');
                        $("select#assignees_filter").val(array);
                        $("select#assignees_filter").multiselect('reload');
                    }
                } else {
                    window.byHideShowColumn_value = ['0', '1', '2', '3', '4', '5', '10', '11', '13', '14'];
                    $("select#colVis_show_hide_filter").val(window.byHideShowColumn_value);
                    $("select#colVis_show_hide_filter").multiselect('reload');

                    // $("select#enabled_disabled_filter").val(['1']);
                    // $("select#enabled_disabled_filter").multiselect('reload');
                    // byEnabledDisabled_value = ['1'];
                    $("select#jobstatus_filter").val(['1', '3']);
                    $("select#jobstatus_filter").multiselect('reload');
                    byJobStatus_value = "new|downloaded";
                    /** must draw to get the values including the variables set */
                    window.manualdownloadlisttable.draw();
                }

                $('a[name="export"]').on('click', function() {
                    var srcEl = $(this);
                    let url = srcEl.data('action');
                    let params = window.manualdownloadlisttable.ajax.params();
                    window.open(url + '?' + $.param(params) + '&filename=manualdownloadlist-Info-' + app.data.timesheet_period.when + '&sheetname=' + app.data.timesheet_period.when);
                });
                $('a[name="clearallfilters"]').on('click', function() {
                    array = [];

                    global_search_value = '';
                    byStatus_value = '';
                    byCompanyDepartment_value = '';
                    byCompanyPosition_value = '';
                    byEnabledDisabled_value = '';
                    byJobStatus_value = '';
                    byAssignee_value = '';

                    $("input[id='global_search_filter']").val('');
                    $("select#status_filter, select#team_filter, select#position_filter, select#enabled_disabled_filter, select#assignees_filter").val(array);
                    $("select#status_filter, select#team_filter, select#position_filter, select#enabled_disabled_filter, select#assignees_filter").multiselect('reload');
                    $.xhrPool.abortAll();
                    clearInterval(window.autoPollrefreshIntervalId);
                    clearInterval(window.autoPollrefreshIntervalId_ASAP);
                    window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                    window.manualdownloadlisttable.draw();
                });

                /** on page length change if any ajax requests happening then cancel those before */
                $('#manualdownloadlistTable').on('length.dt', function(e, settings, len) {
                    $.xhrPool.abortAll();
                    clearInterval(window.autoPollrefreshIntervalId);
                    clearInterval(window.autoPollrefreshIntervalId_ASAP);
                    window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                });

                $('#manualdownloadlistTable').on('page.dt', function(e, settings, len) {
                    $.xhrPool.abortAll();
                    clearInterval(window.autoPollrefreshIntervalId);
                    clearInterval(window.autoPollrefreshIntervalId_ASAP);
                    window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                });

                // Custom filter event for byStatus
                $("select[id='enabled_disabled_filter']").on('change', function() {
                    var options_all = $("#enabled_disabled_filter option:selected").map(function() {
                        return $(this).val();
                    }).get();
                    /** needs to be sent as an array of numbers */
                    byEnabledDisabled_value = options_all;
                    $.xhrPool.abortAll();
                    clearInterval(window.autoPollrefreshIntervalId);
                    clearInterval(window.autoPollrefreshIntervalId_ASAP);
                    window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                    window.manualdownloadlisttable.draw();
                });

                $("select[id='status_filter']").on('change', function() {
                    var options_all = $("#status_filter option:selected").map(function() {
                        return $(this).val();
                    }).get();
                    /** needs to be sent as an array of numbers */
                    byStatus_value = options_all;
                    $.xhrPool.abortAll();
                    clearInterval(window.autoPollrefreshIntervalId);
                    clearInterval(window.autoPollrefreshIntervalId_ASAP);
                    window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                    window.manualdownloadlisttable.draw();
                });

                $("select[id='position_filter']").on('change', function() {
                    var options_all = $("#position_filter option:selected").map(function() {
                        return $(this).text();
                    }).get().join('|');
                    //console.log('position=' + options_all);
                    /** needs to be sent as a string pipe delimited */
                    byCompanyPosition_value = options_all;
                    $.xhrPool.abortAll();
                    clearInterval(window.autoPollrefreshIntervalId);
                    clearInterval(window.autoPollrefreshIntervalId_ASAP);
                    window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                    window.manualdownloadlisttable.draw();
                });

                $("select[id='team_filter']").on('change', function() {
                    var options_all = $("#team_filter option:selected").map(function() {
                        return $(this).text();
                    }).get().join('|');
                    //console.log('department=' + options_all);
                    /** needs to be sent as a string pipe delimited */
                    byCompanyDepartment_value = options_all;
                    $.xhrPool.abortAll();
                    clearInterval(window.autoPollrefreshIntervalId);
                    clearInterval(window.autoPollrefreshIntervalId_ASAP);
                    window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                    window.manualdownloadlisttable.draw();
                });

                $("select[id='jobstatus_filter']").on('change', function() {
                    var options_all = $("#jobstatus_filter option:selected").map(function() {
                        return $(this).text();
                    }).get().join('|');
                    //console.log('department=' + options_all);
                    /** needs to be sent as a string pipe delimited */
                    byJobStatus_value = options_all;
                    $.xhrPool.abortAll();
                    clearInterval(window.autoPollrefreshIntervalId);
                    clearInterval(window.autoPollrefreshIntervalId_ASAP);
                    window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                    window.manualdownloadlisttable.draw();
                });

                function delay(callback, ms) {
                    var timer = 0;
                    return function() {
                        var context = this,
                            args = arguments;
                        clearTimeout(timer);
                        timer = setTimeout(function() {
                            callback.apply(context, args);
                        }, ms || 0);
                    };
                }

                $("input[id='global_search_filter']").on("drop search change keyup copy paste cut", delay(function(e) {
                    var keyCodedpressed = app.util.globalSearchkeyCodesPressedAllowed(e.keyCode);
                    if (keyCodedpressed || e.type == 'drop' || e.type == "search" || e.type == "change" || e.keyCode == 13) {
                        global_search_value = $(this).val();
                        if(global_search_value == '!' || global_search_value == '@'){
                            /** its a special seraching command and just by itself and a short delay will error */
                            /** so we don't reload the table unless there is more data to go on */
                        }else{
                            $.xhrPool.abortAll();
                            clearInterval(window.autoPollrefreshIntervalId);
                            clearInterval(window.autoPollrefreshIntervalId_ASAP);
                            window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                            window.manualdownloadlisttable.draw();
                        }
                    }
                }, 300));

                $("select[id='colVis_show_hide_filter']").on('change', function(e) {
                    e.preventDefault();
                    app_ops.manage_manualdownloadlist.profile.handle_show_hide_columns_on_this_table();
                });


                $("select[id='assignees_filter']").on('change', function() {
                    var options_all = $("#assignees_filter option:selected").map(function() {
                        return $(this).text();
                    }).get().join('|');
                    console.log('assignees=' + options_all);
                    /** needs to be sent as a string pipe delimited */
                    byAssignee_value = options_all;
                    $.xhrPool.abortAll();
                    clearInterval(window.autoPollrefreshIntervalId);
                    clearInterval(window.autoPollrefreshIntervalId_ASAP);
                    window.manualdownloadlisttable.settings()[0].jqXHR.abort();
                    window.manualdownloadlisttable.draw();
                });
            },
            handlealertdisplayofappajaxresultsuccessisfalse: function(array_of_errors, caseId_to_manually_download){
                function formatBytes(bytes,decimals) {
                   if(bytes == 0) return '0 Bytes';
                   var k = 1024,
                       dm = decimals || 2,
                       sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
                       i = Math.floor(Math.log(bytes) / Math.log(k));
                   return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
                }
                var autoCloserefreshIntervalId2 = null;
                var countdownstartvalueseconds2 = 10;
                var autoClosetimer2 = function() {
                    autoCloserefreshIntervalId2 = setInterval(function() {
                        /**console.log(countdownstartvalueseconds2)*/
                        countdownstartvalueseconds2 = countdownstartvalueseconds2 - 1;
                        if(countdownstartvalueseconds2 == 0){
                            $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                            $('.jconfirm-buttons').children().closest('.btn-primary').trigger('click');
                            clearInterval(autoCloserefreshIntervalId2);
                            /** for the next time round. */
                            countdownstartvalueseconds2 = 0;
                        }else{
                            $('.jconfirm-buttons').children().closest('.btn-primary').text('OK (' +countdownstartvalueseconds2+ ')');
                        }
                    }, 1000);
                };

                var keeping_track_of_redownload_manualdownloadlist_X_anchor = false;
                var alert_string = 'Notice :-';
                //var alert_string = '';
                $.each(array_of_errors, function(idx, val) {
                    alert_string = alert_string + '<br>' + val;
                    console.log(val);
                    if(val.includes("redownload_manualdownloadlist_X")){
                        keeping_track_of_redownload_manualdownloadlist_X_anchor = true;
                    }
                });
                var jc_alert_re_download_option = $.alert({
                    title: "",
                    content: alert_string,
                    draggable: true,
                    dragWindowGap: 0,
                    backgroundDismiss: false,
                    escapeKey: 'ok',
                    animateFromElement: false,
                    autoClose: false,                    
                    onOpen: function() {
                        if(keeping_track_of_redownload_manualdownloadlist_X_anchor){
                            $("a[name='redownload_manualdownloadlist_X']").off('click').on('click', function(event) {
                                /**console.log('redownload_manualdownloadlist_X attached to click event');*/

                                /** I want the previous confirm or alert dalogue to close first before opening the next one */
                                jc_alert_re_download_option.close();

                                event.preventDefault();
                                var clicked_href = $(this).attr('href');
                                /**console.log(clicked_href);*/

                                /** define outside so it can be used elsewhere in this scope */
                                var array_of_job_zips_details = null;

                                var jc_confirm_choose_zips = $.confirm({
                                    //title: eval("app.translations." + app.data.locale + ".title_text"),
                                    title: "",
                                    content: '<div class="loader search_keynumber_input_loader" style="margin-left: 15px; height:88%; width:92.4%; display: block; margin-top: 7px; border-radius: 4px;"></div>' +
                                        '<span id="search_keynumber_input_loader_text" style="display: inline-block">Checking for Job ID = ' + caseId_to_manually_download + '    ...     Please Wait</span>' + '\n' +
                                        '<span id="search_keynumber_input_text" style="display: none;">'+ eval("app.translations." + app.data.locale + ".are_you_sure_you_want_to_redownload_this_case") + " " + caseId_to_manually_download + ' ? The tool found :-</span>\n' +
                                        '<table id="search_keynumber_input_table" cellspacing="0" width="100%" style="display: none; margin-top:12px"><tbody><tr>' +
                                        '<td style="width: 33.3%">' +
                                        '<input type="checkbox" style="margin-bottom: 14px;" class="mdl_checkbox_example form-control" checked>' +
                                        '</td>' +
                                        '<td style="width: 33.3%">' +
                                        '<input type="checkbox" style="margin-bottom: 14px;" class="mdl_checkbox_new form-control" checked>' +
                                        '</td>' +
                                        '<td style="width: 33.3%">' +
                                        '<input type="checkbox" style="margin-bottom: 14px;" class="mdl_checkbox_ready form-control" checked>' +
                                        '</td>' +
                                        '</tr>' +
                                        '<tr>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_example_size"></span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_new_size"></span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_ready_size"></span></td>' +
                                        '</tr>' +
                                        '<tr>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_example_label">Examples</span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_new_label">New</span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_ready_label">Ready</span></td>' +
                                        '</tr>' +
                                        '<tr>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_example_label_extra"></span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_new_label_extra"></span></td>' +
                                        '<td style="width: 33.3%" class="text-center"><span class="mdl_checkbox_ready_label_extra"></span></td>' +
                                        '</tr>' +
                                        '</tbody>' +
                                        '</table>',
                                    type: 'orange',
                                    draggable: true,
                                    dragWindowGap: 0,
                                    backgroundDismiss: false,
                                    escapeKey: 'cancel',
                                    animateFromElement: false,
                                    autoClose: false,
                                    onOpen: function() {
                                        $('.custom_loader_section').css('display', '');
                                        $('.custom_loader_section').animate({
                                            opacity: 1
                                        }, {
                                            duration: 500,
                                            complete: function() {

                                            }
                                        });

                                        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

                                        var ajaxdata = {
                                            'case_id': caseId_to_manually_download
                                        };

                                        app.ajax.jsonGET(app.data.urlGetZipDetailsOfJob, ajaxdata, null, function() {
                                            /**console.log(app.ajax.result);*/
                                            if (app.ajax.result.success == true) {
                                                /** we setup the form accordingly */
                                                jc_confirm_choose_zips.buttons.ok.show();
                                                jc_confirm_choose_zips.buttons.cancel.show();
                                                jc_confirm_choose_zips.buttons.ok.enable();
                                                jc_confirm_choose_zips.buttons.cancel.enable();
                                                $('.search_keynumber_input_loader').css('display', 'none');
                                                $('#search_keynumber_input_loader_text').css('display', 'none');
                                                $('#search_keynumber_input_text').css('display', '');
                                                $('#search_keynumber_input_table').css('display', '');

                                                array_of_job_zips_details = app.ajax.result.array_of_job_zips_details;

                                                $.each(app.ajax.result.array_of_job_zips_details, function(idx, val) {
                                                    /** also want to show the user whether there is any difference between the previous downloaded and what is currently on amazon */
                                                    /** if there are no zips.. well then we can alert and then exit out */
                                                    if(idx == 'example'){
                                                        if(val.size == null){
                                                            $('.mdl_checkbox_example').remove();
                                                            $('.mdl_checkbox_example_size').remove();
                                                            $('.mdl_checkbox_example_label').remove();
                                                        }else{
                                                            $('.mdl_checkbox_example_size').html(formatBytes(val.size));
                                                            if(app.ajax.result.all_caseid_zips_local[caseId_to_manually_download] !== undefined && app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx] !== undefined) {
                                                                if(val.lastModified > app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx].last_modified){
                                                                    /** it is a newer zip probably. */
                                                                    /** let them download it if they choose to */
                                                                }else{
                                                                    /** notify them that it is the same as the one previously downloaded */
                                                                    $('.mdl_checkbox_example').prop('checked', false).prop('disabled', true);
                                                                    $('.mdl_checkbox_example_label_extra').append('(unchanged)');
                                                                }
                                                            }else{
                                                                /** it is undefined so still show them the option to choose it to download */
                                                            }
                                                        }
                                                    }
                                                    if(idx == 'new'){
                                                        if(val.size == null){
                                                            $('.mdl_checkbox_new').remove();
                                                            $('.mdl_checkbox_new_size').remove();
                                                            $('.mdl_checkbox_new_label').remove();
                                                        }else{
                                                            $('.mdl_checkbox_new_size').html(formatBytes(val.size));
                                                            if(app.ajax.result.all_caseid_zips_local[caseId_to_manually_download] !== undefined && app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx] !== undefined) {
                                                                if(val.lastModified > app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx].last_modified){
                                                                    /** it is a newer zip probably. */
                                                                    /** let them download it if they choose to */
                                                                }else{
                                                                    /** notify them that it is the same as the one previously downloaded */
                                                                    $('.mdl_checkbox_new').prop('checked', false).prop('disabled', true);
                                                                    $('.mdl_checkbox_new_label_extra').append('(unchanged)');
                                                                }
                                                            }else{
                                                                /** it is undefined so still show them the option to choose it to download */
                                                            }
                                                        }
                                                    }
                                                    if(idx == 'ready'){
                                                        if(val.size == null){
                                                            $('.mdl_checkbox_ready').remove();
                                                            $('.mdl_checkbox_ready_size').remove();
                                                            $('.mdl_checkbox_ready_label').remove();
                                                        }else{
                                                            $('.mdl_checkbox_ready_size').html(formatBytes(val.size));
                                                            if(app.ajax.result.all_caseid_zips_local[caseId_to_manually_download] !== undefined && app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx] !== undefined) {
                                                                if(val.lastModified > app.ajax.result.all_caseid_zips_local[caseId_to_manually_download][idx].last_modified){
                                                                    /** it is a newer zip probably. */
                                                                    /** let them download it if they choose to */
                                                                }else{
                                                                    /** notify them that it is the same as the one previously downloaded */
                                                                    $('.mdl_checkbox_ready').prop('checked', false).prop('disabled', true);
                                                                    $('.mdl_checkbox_ready_label_extra').append('(unchanged)');
                                                                }
                                                            }else{
                                                                /** it is undefined so still show them the option to choose it to download */
                                                            }
                                                        }
                                                    }
                                                });
                                                /**autoClose the long way .. */
                                                autoClosetimer2();

                                                /** if the user clicks on the zip type checkboxes cancel the autocountdowntimer */
                                                $('.mdl_checkbox_example').on('click', function() {
                                                    $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                                                    clearInterval(autoCloserefreshIntervalId2);
                                                });
                                                $('.mdl_checkbox_new').on('click', function() {
                                                    $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                                                    clearInterval(autoCloserefreshIntervalId2);
                                                });
                                                $('.mdl_checkbox_ready').on('click', function() {
                                                    $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');
                                                    clearInterval(autoCloserefreshIntervalId2);
                                                });
                                            } else {
                                                app_ops.manage_manualdownloadlist.profile.handlealertdisplayofappajaxresultsuccessisfalse(app.ajax.result.errors, caseId_to_manually_download);
                                            }
                                        });
                                    },
                                    onAction: function(btnName) {
                                        $(".jconfirm.jconfirm-light.jconfirm-open").remove();
                                    },
                                    buttons: {
                                        ok: {
                                            btnClass: 'btn-primary text-white',
                                            keys: ['enter'],
                                            text: eval("app.translations." + app.data.locale + ".okay_text"),
                                            isHidden: true,
                                            isDisabled: true,
                                            action: function() {

                                                /** based on the information on OKAY button click */
                                                var mdl_checkbox_example = this.$content.find('.mdl_checkbox_example').is(':checked');
                                                var mdl_checkbox_new = this.$content.find('.mdl_checkbox_new').is(':checked');
                                                var mdl_checkbox_ready = this.$content.find('.mdl_checkbox_ready').is(':checked');

                                                if (!mdl_checkbox_example && !mdl_checkbox_new && !mdl_checkbox_ready) {
                                                    $.alert('choose at least 1 folder');
                                                    $('.custom_loader_section').animate({
                                                        opacity: 0
                                                    }, {
                                                        duration: 500,
                                                        complete: function() {
                                                            $('.custom_loader_section').css('display', 'none');
                                                            $('.search_keynumber_input').prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                                                        }
                                                    });
                                                    return false;
                                                }

                                                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                                                var ajaxdata = {
                                                    'case_id': caseId_to_manually_download,
                                                    'mdl_checkbox_example': mdl_checkbox_example,
                                                    'mdl_checkbox_new': mdl_checkbox_new,
                                                    'mdl_checkbox_ready': mdl_checkbox_ready,
                                                    'array_of_job_zips_details': array_of_job_zips_details
                                                };

                                                app.util.fullscreenloading_start();
                                                app.util.nprogressinit();

                                                app.ajax.json(clicked_href, ajaxdata, null, function() {
                                                    console.log(app.ajax.result);
                                                    if (app.ajax.result.success == true) {
                                                        window.manualdownloadlisttable.ajax.reload(null, false);
                                                        app.util.fullscreenloading_end();
                                                        window.manualdownloadlisttable.fixedHeader.adjust();
                                                        app.util.nprogressdone();
                                                    } else {
                                                        app.util.fullscreenloading_end();
                                                        app.util.nprogressdone();
                                                    }
                                                });

                                            }
                                        },
                                        cancel: {
                                            text: eval("app.translations." + app.data.locale + ".cancel_text"),
                                            isHidden: true,
                                            isDisabled: true,
                                            action: function() {
                                                clearInterval(autoCloserefreshIntervalId2);
                                                $('.jconfirm-buttons').children().closest('.btn-primary').text('OK');

                                                $('.custom_loader_section').animate({
                                                    opacity: 0
                                                }, {
                                                    duration: 500,
                                                    complete: function() {
                                                        $('.custom_loader_section').css('display', 'none');
                                                        $('.search_keynumber_input').prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                                                        jc_confirm_choose_zips.close();
                                                    }
                                                });
                                            }
                                        }
                                    }
                                });
                            });
                        }
                    },
                    buttons: {
                        ok: {
                            btnClass: 'text-white',
                            keys: ['enter'],
                            text: eval("app.translations." + app.data.locale + ".okay_text"),
                            action: function() {
                                /**console.log('okay clicked on alert');*/
                            }
                        },
                    }
                });

                /** close the original search_keynumber_input confirmation box via the button */
                $('.jconfirm-buttons').children().next('.btn-default').trigger('click');
                $('.custom_loader_section').animate({
                    opacity: 0
                }, {
                    duration: 500,
                    complete: function() {
                        $('.custom_loader_section').css('display', 'none');
                        $('.search_keynumber_input').prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                    }
                });
            },
            handlefixedheaderpinning: function() {
                if (app.data.browser_detected == 'Chrome') {
                    var scrollTimer;
                    var resizeTimer;
                    var remembering_mainbody_padding = '';
                    $(window).scroll(function() {
                        clearTimeout(scrollTimer);
                        scrollTimer = setTimeout(function() {
                            var mainbody_padding = $('.visitor').outerHeight(true);
                            if (remembering_mainbody_padding != mainbody_padding) {
                                window.manualdownloadlisttable.fixedHeader.headerOffset(mainbody_padding);
                                window.manualdownloadlisttable.fixedHeader.adjust();
                                remembering_mainbody_padding = mainbody_padding;
                            }
                        }, 250);
                    });
                    $(window).resize(function() {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(function() {
                            var mainbody_padding = $('.visitor').outerHeight(true);
                            if (remembering_mainbody_padding != mainbody_padding) {
                                window.manualdownloadlisttable.fixedHeader.headerOffset(mainbody_padding);
                                window.manualdownloadlisttable.fixedHeader.adjust();
                                remembering_mainbody_padding = mainbody_padding;
                            }
                        }, 250);
                    });
                } else if (app.data.browser_detected == 'Firefox') {
                    var scrollTimer;
                    var resizeTimer;
                    var remembering_mainbody_padding = '';
                    $(window).scroll(function() {
                        clearTimeout(scrollTimer);
                        scrollTimer = setTimeout(function() {
                            var mainbody_padding = $('.visitor').outerHeight(true);
                            if (remembering_mainbody_padding != mainbody_padding) {
                                window.manualdownloadlisttable.fixedHeader.headerOffset(mainbody_padding);
                                window.manualdownloadlisttable.fixedHeader.adjust();
                                remembering_mainbody_padding = mainbody_padding;
                            }
                        }, 250);
                    });
                    $(window).resize(function() {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(function() {
                            var mainbody_padding = $('.visitor').outerHeight(true);
                            if (remembering_mainbody_padding != mainbody_padding) {
                                window.manualdownloadlisttable.fixedHeader.headerOffset(mainbody_padding);
                                window.manualdownloadlisttable.fixedHeader.adjust();
                                remembering_mainbody_padding = mainbody_padding;
                            }
                        }, 250);
                    });
                } else {}
            },
            get_manualdownloadlistInfo_tab: function() {
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                var ajaxdata = {};

                app.ajax.html(app.data.tabHTML_manualdownloadlistInfo, ajaxdata, null, function() {
                    $('#manualdownloadlistInfo').html(app.ajax.result);
                    /**console.log('#manualdownloadlistInfo_DONE');*/
                });
            },
            foreach_handle_error_display: function(idx, val) {
                // console.log('inside foreach_handle_error_display function');
                // console.log(idx);
                // console.log(val);
                // console.log('running');
                if (val.indexOf('The period modifier field is required when period unit is 2.') >= 0) {

                    var selector_id = 'period_modifier_send';
                    val = 'The period modifier field is required when period unit is Weekly';
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
                if (val.indexOf(' must be a date after ') >= 0) {
                    var splitString = val.split(" must be a date after ");
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

                if (val.indexOf(' is required when ') >= 0) {
                    var splitString = val.split(" is required when ");
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

                if (val.indexOf(' already exisits.') >= 0) {
                    var splitString = val.split(" already exisits.");
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


                if (val.indexOf(' may only contain letters.') >= 0) {
                    var splitString = val.split(" may only contain letters.");
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
            handle_show_hide_columns_on_this_table: function() {
                var all_options = [];
                var all_options_column_groups = [];
                var columns_visible = [];
                var upper_header_columns_visible = [];
                var upper_header_columns_hidden = [];
                var bottom_footer_columns_visible = [];
                var bottom_footer_columns_hidden = [];
                $("select[id='colVis_show_hide_filter'] option").each(function(index) {
                    //console.log($(this).val());
                    /** all avaliable options in the multiple select */
                    all_options[index] = $(this).val();
                    all_options_column_groups[index] = $("select[id='colVis_show_hide_filter'] option[value='" + $(this).val() + "']").attr('data-column');
                });

                $.each($("select[id='colVis_show_hide_filter']").val(), function(index, val) {
                    /**console.log(index);*/
                    //console.log(val);
                    columns_visible[index] = val;
                    upper_header_columns_visible[index] = parseInt(val) + 5;
                    bottom_footer_columns_visible[index] = parseInt(val) - 1;
                    //console.log($("select[id='colVis_show_hide_filter'] option[value='"+val+"']").attr('data-column'));
                    /** each of the values that are present mean they should be visible */
                });

                /**console.log("===================================================");*/
                /**console.log(all_options);*/
                /**console.log(all_options_column_groups);*/
                /**console.log("===================================================");*/
                /**console.log('upper_header_columns_visible');*/
                /**console.log(upper_header_columns_visible);*/
                /**console.log('bottom_footer_columns_visible');*/
                /**console.log(bottom_footer_columns_visible);*/

                /**console.log('columns_visible');*/
                /**console.log(columns_visible);*/
                var columns_hidden = all_options.filter(x => columns_visible.indexOf(x) === -1);
                /**console.log('columns_hidden');*/
                /**console.log(columns_hidden);*/


                $.each(columns_hidden, function(index, val) {
                    upper_header_columns_hidden[index] = parseInt(val) + 5;
                    if (val < 11) {
                        bottom_footer_columns_hidden[index] = parseInt(val) - 1;
                    } else {
                        if (val == 11) {
                            /** dont do it for this item because of the way the footer is made the order differs from the header */
                        } else {
                            bottom_footer_columns_hidden[index] = parseInt(val) - 2;
                        }
                    }
                });

                /**console.log('upper_header_columns_hidden');*/
                /**console.log(upper_header_columns_hidden);*/
                /**console.log('bottom_footer_columns_hidden');*/
                /**console.log(bottom_footer_columns_hidden);*/
                /**console.log("====================== hiding columns by val =============================");*/

                var counter = 0;
                var invisible_array = [];
                $.each(columns_hidden, function(index, val) {
                    /**console.log(index);*/
                    /**console.log(val);*/
                    var _val_index = null;
                    $.each(all_options, function(all_option_index, all_option_val) {
                        if (all_option_val == val) {
                            _val_index = all_option_index;
                        }
                    });

                    var data = all_options_column_groups[_val_index];
                    /**console.log('data - column numbers to hide ');*/
                    data = data.split(',');
                    /**console.log(data);*/

                    $.each(data, function(unimportant_index, column_val) {
                        invisible_array[counter] = parseInt(column_val);
                        counter++;
                    });

                });
                /**console.log(invisible_array);*/
                /**console.log("====================== showing columns by val =============================");*/

                var counter = 0;
                var visible_array = [];
                $.each(columns_visible, function(index, val) {
                    /**console.log(index);*/
                    /**console.log(val);*/
                    var _val_index = null;
                    $.each(all_options, function(all_option_index, all_option_val) {
                        if (all_option_val == val) {
                            _val_index = all_option_index;
                        }
                    });

                    var data = all_options_column_groups[_val_index];
                    /**console.log('data - column numbers to show ');*/
                    data = data.split(',');
                    /**console.log(data);*/

                    $.each(data, function(unimportant_index, column_val) {
                        visible_array[counter] = parseInt(column_val);
                        counter++;
                    });
                });
                /**console.log(visible_array);*/


                window.byHideShowColumn_value = $("select[id='colVis_show_hide_filter']").val();
                /**console.log(byHideShowColumn_value);*/

                window.byShowColumn_value = visible_array;
                window.byHideColumn_value = invisible_array;

                // var make_column_invisible = window.manualdownloadlisttable.columns(invisible_array);
                // make_column_invisible.visible(false);

                // var make_column_visible = window.manualdownloadlisttable.columns(visible_array);
                // make_column_visible.visible(true);
                /**window.manualdownloadlisttable.draw();*/

                /** if you can get the table and go through all the columns checking if it needs to be hidden then remove the class otherwise add the class */

                $.each(window.manualdownloadlisttable.columns().header(), function(key, value) {
                    /**console.log(key, value);*/
                    /**console.log(key);*/
                    if (visible_array.includes(key)) {
                        /**console.log(key + 'included in visible_array');*/
                        /** columns needs to visible change style to display: visible */
                        /** use the value to find the previous th and hide the th that way */
                        /**$(value).css('display', 'visible');*/
                        $(value).removeClass('hidden');
                        /**console.log($(value).parent().prev().children());*/

                        $.each($(value).parent().prev().children(), function(header_visible_key, header_visible_value) {
                            if (upper_header_columns_visible.includes(header_visible_key)) {
                                $(header_visible_value).removeClass('hidden');
                            }
                        });
                    }

                    if (invisible_array.includes(key)) {
                        /**console.log(key + 'included in invisible_array');*/
                        /** columns needs to hidden change style to display: none */
                        /** use the value to find the previous th and show the th that way */
                        /**$(value).css('display', 'none');*/
                        $(value).addClass('hidden');

                        $.each($(value).parent().prev().children(), function(header_invisible_key, header_invisible_value) {
                            if (upper_header_columns_hidden.includes(header_invisible_key)) {
                                $(header_invisible_value).addClass('hidden');
                            }
                        });

                    }
                });

                $.each(window.manualdownloadlisttable.columns().footer(), function(key, value) {
                    /**console.log(key, value);*/
                    /**console.log(key);*/

                    if (visible_array.includes(key)) {
                        /**console.log(key + 'included in visible_array');*/
                        /** columns needs to visible change style to display: visible */
                        /** use the value to find the previous th and hide the th that way */

                        $(value).removeClass('hidden');
                        $.each($(value).parent().next().children(), function(header_visible_key, header_visible_value) {
                            if (bottom_footer_columns_visible.includes(header_visible_key)) {
                                $(header_visible_value).removeClass('hidden');
                            }
                        });
                    }


                    if (invisible_array.includes(key)) {
                        /**console.log(key + 'included in invisible_array');*/
                        /** columns needs to hidden change style to display: none */
                        /** use the value to find the previous th and show the th that way */

                        $(value).addClass('hidden');
                        $.each($(value).parent().next().children(), function(header_invisible_key, header_invisible_value) {
                            /**console.log(header_invisible_key, header_invisible_value);*/
                            if (bottom_footer_columns_hidden.includes(header_invisible_key)) {
                                $(header_invisible_value).addClass('hidden');
                            }
                        });
                    }
                });

                window.manualdownloadlisttable.rows().every(function(rowIdx, tableLoop, rowLoop) {
                    $.each(this.node().children, function(key, value) {
                        /**console.log(key, value);*/
                        if (visible_array.includes(key)) {
                            $(value).removeClass('hidden');
                        }
                        if (invisible_array.includes(key)) {
                            $(value).addClass('hidden');
                        }
                    });
                });

                /**console.log('adjust the fixedheader');*/
                window.manualdownloadlisttable.fixedHeader.adjust();
                /**window.manualdownloadlisttable.draw();*/


                var filter_load = JSON.parse(sessionStorage.getItem('Br24_' + app.env() + '_managemanualdownloadlistTable_table_manualdownloadlistTable'));
                if (filter_load !== null) {
                    var data = filter_load;
                    data['filters'] = {
                        /** checkboxes */
                        "status_cb": filter_load['filters']['status_cb'],
                        "team_cb": filter_load['filters']['team_cb'],
                        "position_cb": filter_load['filters']['position_cb'],
                        "editor_level_cb": filter_load['filters']['editor_level_cb'],
                        "job_status_cb": filter_load['filters']['job_status_cb'],
                        "assignee_cb": filter_load['filters']['assignee_cb'],

                        /** query */
                        "byEnabledDisabled": filter_load['filters']['byEnabledDisabled'],
                        "byStatus": filter_load['filters']['byStatus'],
                        "byTeam": filter_load['filters']['byTeam'],
                        "byPosition": filter_load['filters']['byPosition'],
                        "byEditor_level": filter_load['filters']['byEditor_level'],
                        "byJobStatus": filter_load['filters']['byJobStatus'],
                        "byAssignee": filter_load['filters']['byAssignee'],

                        "byHideShowColumn_val": window.byHideShowColumn_value,
                        "byHideColumn": window.byHideColumn_value,
                        "byShowColumn": window.byShowColumn_value,

                        "locale_date_format": filter_load['filters']['locale_date_format'],

                        "global_search": filter_load['filters']['global_search']
                    };

                    /**console.log('setting the data ');*/
                    /**console.log(data);*/
                    sessionStorage.setItem('Br24_' + app.env() + '_managemanualdownloadlistTable_table_manualdownloadlistTable', JSON.stringify(data));
                }
            },
            sync_preview_required_status: function(case_id, encrypted_case_id_uploading_to, status, tr, checkbox_in_td, change_assignees_value) {
                if (change_assignees_value !== '') {
                    /** we need to protect againt people who change the cell details before clicking the preview contents.. */
                    /** we will just get the assignees from the db in that case */

                    var data = {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        'case_id': case_id,
                        'encrypted_case_id': encrypted_case_id_uploading_to,
                        'status': status,
                        'input_name': checkbox_in_td.attr('name')
                    };

                    /**console.log(app.data.urlSyncReviewRequiredStatus);*/
                    /**console.log(case_id);*/
                    /**console.log(encrypted_case_id_uploading_to);*/
                    /**console.log(status);*/

                    app.ajax.jsonGET(app.data.urlSyncReviewRequiredStatus, data, null, function() {
                        //console.log(app.ajax.result);
                        success_ajax_then_refresh = app.ajax.result.success;
                        if (app.ajax.result.success == true) {
                            app.data.selectize_hashtag_list_formated_json = app.ajax.result.selectize_hashtag_list_formated_json;
                            /**console.log('after');*/
                            /**console.log(select_list_hashtag_list);*/
                            // console.log(tr.children().next('.last_updated'));
                            // console.log(app.ajax.result.updated_at);
                            tr.children().next('.last_updated_by').html('<span style="font-size: 8px;">' + app.ajax.result.last_updated_by_name + '</span>');
                            tr.children().next('.last_updated').html(app.ajax.result.updated_at);
                        } else {
                            /** */
                            /** */
                            /**console.log('NOT working');*/
                            /** need to reset the check box to the original state */
                            if (checkbox_in_td.is(':checked')) {
                                /**console.log('3was not checked');*/
                                checkbox_in_td.prop("checked", false);
                                /**status = 1;*/
                            } else {
                                /**console.log('3was checked');*/
                                checkbox_in_td.prop("checked", true);
                                /**status = 2;*/
                            }
                            var alert_string = 'Something went wrong';
                            $.each(app.ajax.result.errors, function(idx, val) {
                                //app_attendance.manage_overtimes_requester.profile.foreach_handle_error_display(idx, val);
                                alert_string = alert_string + '<br>' + val;
                            });
                            alert_string = alert_string + '<br>' + app.ajax.result.caseId + ' preview required state not changed';
                            $.alert(alert_string);
                        }
                    });
                } else {
                    if (checkbox_in_td.is(':checked')) {
                        /**console.log('3was not checked');*/
                        checkbox_in_td.prop("checked", false);
                        checkbox_in_td.prop("disabled", true);
                        /**status = 1;*/
                    } else {
                        /**console.log('3was checked');*/
                        checkbox_in_td.prop("checked", true);
                        checkbox_in_td.prop("disabled", false);
                        /**status = 2;*/
                    }
                    var alert_string = case_id + ' preview required state not changed. Reason: Job has no assignees.';
                    $.alert(alert_string);
                }
            },
            live_update_download_progress_of_manual_downloaded_jobs: function(json_data) {

                /**console.log(json_data);*/
                /**console.log(app.data.currently_downloading_aria2c);*/

                var currently_downloading_aria2c_arr = Object.keys(app.data.currently_downloading_aria2c).map(function (key) {
                  return { [key]: app.data.currently_downloading_aria2c[key] };
                });
                /**console.log('BEFORE', currently_downloading_aria2c_arr);*/
                

                /** by the end of it all i just want a list of case ids that are downloading and in the list of case_ids on the page */
                /** first need to check if the case_id on the page is in the array */
                var array_of_case_ids_on_page_and_downloading = [];
                $.each(currently_downloading_aria2c_arr, function(generic_index, dl_case_in_progess_per_case_id) {
                    /**console.log(dl_case_in_progess_per_case_id);*/
                    $.each(dl_case_in_progess_per_case_id, function(dl_case_index, dl_type_details) {
                        /**console.log(dl_case_index);*/
                        /**console.log('dl_type_details', dl_type_details);*/
                        $.each(json_data, function(on_page_generic_index, on_page_case_id_details) {
                            /**console.log('on_page_case_id_details', on_page_case_id_details);*/
                            /**console.log(on_page_case_id_details.case_id);*/
                            if(dl_case_index == on_page_case_id_details.case_id){
                                array_of_case_ids_on_page_and_downloading.push(dl_case_index);
                            }
                        });

                    });
                });
                /**console.log('array_of_case_ids_on_page_and_downloading', array_of_case_ids_on_page_and_downloading);*/
                /** give time for the process to wrtie to the log file before keeping the log file busy */
                var delay_to_use = 1500 * array_of_case_ids_on_page_and_downloading.length;
                /**console.log('delay_to_use', delay_to_use);*/

                function bytesToSize(bytes) {
                    var sizes = ['Bytes', 'KB', 'MB', 'GiB', 'TB'];
                    if (bytes == 0) return '0 Byte';
                    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
                    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
                }

                function hmsToSecondsOnly(str) {
                    var p = str.split(':');
                    var s = 0;
                    var m = 1;

                    while (p.length > 0) {
                        s += m * parseInt(p.pop(), 10);
                        m *= 60;
                    }

                    return s;
                }

                function secondsToHms(d) {
                    d = Number(d);
                    var h = Math.floor(d / 3600);
                    var m = Math.floor(d % 3600 / 60);
                    var s = Math.floor(d % 3600 % 60);

                    var hDisplay = h > 0 ? h + (h == 1 ? "h" : "h") : "";
                    var mDisplay = m > 0 ? m + (m == 1 ? "m" : "m") : "";
                    var sDisplay = s >= 0 ? s + (s == 1 ? "s" : "s") : "";
                    return hDisplay + mDisplay + sDisplay;
                }

                window.autoPollrefreshIntervalId = null;
                window.autoPollrefreshIntervalId_ASAP = null;
                var count_iteration_attempts = 1000;

                $.xhrPool_get_allowed_remaining = [];
                window.aklsdf_get_allowed_remaining = [];
                $.xhrPool_get_allowed_remaining.abortAll = function() {
                    $(this).each(function(i, jqXHR) { /**  cycle through list of recorded connection */
                        jqXHR.abort(); /**  aborts connection */
                        $.xhrPool_get_allowed_remaining.splice(i, 1); /**  removes from list by index */
                    });
                }
                //$.xhrPool_get_allowed_remaining.abortAll();


                var autoPolltimer = function() {

                    /** only do once for the beginning to get the display as soon as the page loads */
                    if(count_iteration_attempts == 1000){
                        /** a short delay to allow for the page to load */
                        window.autoPollrefreshIntervalId_ASAP = setInterval(function() {
                            $.each(array_of_case_ids_on_page_and_downloading, function(index_case_id, dl_types_in_progess_per_case_id) {
                                /**console.log(app.data.currently_downloading_aria2c[dl_types_in_progess_per_case_id]);*/

                                $.each(app.data.currently_downloading_aria2c[dl_types_in_progess_per_case_id], function(dl_type_index, dl_type_details) {
                                    if(dl_type_index == 'example'){
                                        var encrypted_case_id_checking_progress_for = dl_type_details["encrypted_case_id"];
                                        var encrypted_type_checking_progress_for = dl_type_details["encrypted_type"];
                                    }
                                    if(dl_type_index == 'new'){
                                        var encrypted_case_id_checking_progress_for = dl_type_details["encrypted_case_id"];
                                        var encrypted_type_checking_progress_for = dl_type_details["encrypted_type"];
                                    }
                                    if(dl_type_index == 'ready'){
                                        var encrypted_case_id_checking_progress_for = dl_type_details["encrypted_case_id"];
                                        var encrypted_type_checking_progress_for = dl_type_details["encrypted_type"];
                                    }

                                    var ajaxdata = {};
                                    var container = null;
                                    var callback = null;

                                    $.ajax({
                                        type: "GET",
                                        url: app.data.urlGetAria2cDownloadProgressDetailsOfCaseID + "/" + encrypted_case_id_checking_progress_for + "/" + encrypted_type_checking_progress_for,
                                        data: ajaxdata,
                                        dataType: 'json',
                                        async: true,
                                        beforeSend: function(jqXHR) {
                                            $.xhrPool_get_allowed_remaining.push(jqXHR); /**  add connection to list */
                                            window.aklsdf_get_allowed_remaining.push(jqXHR);
                                        },
                                        complete: function(jqXHR) {
                                            var i = $.xhrPool_get_allowed_remaining.indexOf(jqXHR); /**  get index for current connection completed */
                                            if (i > -1) {
                                                $.xhrPool_get_allowed_remaining.splice(i, 1); /**  removes from list by index */
                                                window.aklsdf_get_allowed_remaining.splice(i, 1);
                                            }
                                        },                                        
                                        success: function(response) {
                                            if (app.util.isJson(response)) {
                                                app.ajax.result = app.util.parseJson(response);
                                                

                                                if (app.ajax.result.error) {
                                                    this.error = true;
                                                    $.alert({
                                                        title: 'Alert!' + '<span style="color: #FFF; font-size: 7px;">app.ajax.callGET.success</span>',
                                                        content: app.ajax.result.error.msg,
                                                    });
                                                }

                                                if (app.ajax.result.success == true) {
                                                    /***/
                                                    if(app.ajax.result.file_count == null){
                                                        var type_file_count_to_display = "&nbsp;&nbsp;&nbsp;&nbsp;";
                                                    }else{
                                                        var type_file_count_to_display = app.ajax.result.file_count;
                                                    }
                                                    /** put the details as a tool tip to the respective item on the page and make sure that it can be dynamic */
                                                    if(app.ajax.result.type == 'example'){
                                                        var class_selector = '.iecd_'+app.ajax.result.caseId;
                                                        $($(class_selector).parent().parent().parent().parent().first().children().first().children()[0]).html(type_file_count_to_display);
                                                    }
                                                    if(app.ajax.result.type == 'new'){
                                                        var class_selector = '.incd_'+app.ajax.result.caseId;
                                                        $($(class_selector).parent().parent().parent().parent().first().children().first().children()[2]).html(type_file_count_to_display);
                                                    }
                                                    if(app.ajax.result.type == 'ready'){
                                                        var class_selector = '.ircd_'+app.ajax.result.caseId;
                                                        $($(class_selector).parent().parent().parent().parent().first().children().first().children()[4]).html(type_file_count_to_display);
                                                    }

                                                    if (app.ajax.result.latest_progress_details_array != null || app.ajax.result.latest_progress_details_array != undefined) {
                                                        if(app.ajax.result.latest_progress_details_array.length >= 1){
                                                            /**console.log('progress details array', app.ajax.result.latest_progress_details_array);*/
                                                            if(app.ajax.result.state == 'moving_to_jobFolder'){
                                                                /** should be looking for the rsync progress log */
                                                                $(class_selector).parent().parent().parent().parent().first().css('padding-bottom', '');
                                                                $(class_selector).parent().parent().css('display', '');
                                                                $(class_selector).parent().parent().parent().css('display', '').css('padding-top', '');
                                                                /**30,290,409,773 100%   75.83MB/s    0:06:20*/
                                                                var transfered_size_human_readable = bytesToSize(app.ajax.result.latest_progress_details_array[0].replaceAll(new RegExp(",", 'g'),""));
                                                                /**console.log(transfered_size_human_readable);*/
                                                                //var remaining_time_human_readable = secondsToHms(hmsToSecondsOnly(app.ajax.result.latest_progress_details_array[3]));
                                                                var remaining_time_human_readable = secondsToHms(hmsToSecondsOnly(app.ajax.result.latest_progress_details_array[2]));
                                                                /**console.log(remaining_time_human_readable);*/
                                                                /**console.log($(class_selector));*/
                                                                var zip_size_human_readable = bytesToSize(app.ajax.result.zip_size);
                                                                var download_speed = app.ajax.result.latest_progress_details_array[2];
                                                                $(class_selector).html(transfered_size_human_readable+"/"+zip_size_human_readable+"("+app.ajax.result.latest_progress_details_array[1] + ")<br>ETA:" +remaining_time_human_readable + " <i>" + download_speed + "</i>");
                                                            }else if(app.ajax.result.state == 'downloading'){
                                                                /** should be looking for the aria2c progress log */
                                                                $(class_selector).parent().parent().parent().parent().first().css('padding-bottom', '');
                                                                $(class_selector).parent().parent().css('display', '');
                                                                $(class_selector).parent().parent().parent().css('display', '').css('padding-top', '');
                                                                var download_speed = app.ajax.result.latest_progress_details_array[3]+"/s";
                                                                $(class_selector).html(app.ajax.result.latest_progress_details_array[1] + "<br>" +app.ajax.result.latest_progress_details_array[4] + " <i>" + download_speed + "</i>");
                                                            }else if(app.ajax.result.state == 'downloaded'){
                                                                /** should be looking for the unzip -t pv progress log */
                                                                $(class_selector).parent().parent().parent().parent().first().css('padding-bottom', '');
                                                                $(class_selector).parent().parent().css('display', '');
                                                                $(class_selector).parent().parent().parent().css('display', '').css('padding-top', '');
                                                                var download_speed = app.ajax.result.latest_progress_details_array[3];
                                                                $(class_selector).html("Unzip test = " + parseInt(app.ajax.result.latest_progress_details_array[0]) + "/" + parseInt(app.ajax.result.latest_progress_details_array[5]) + "<br><span style='font-size:4px'>" + download_speed + "</span><br>" + app.ajax.result.latest_progress_details_array[4]);
                                                            }else{
                                                                /** should be looking for the unzip -o pv progress log */
                                                                $(class_selector).parent().parent().parent().parent().first().css('padding-bottom', '');
                                                                $(class_selector).parent().parent().css('display', '');
                                                                $(class_selector).parent().parent().parent().css('display', '').css('padding-top', '');
                                                                var download_speed = app.ajax.result.latest_progress_details_array[3];
                                                                $(class_selector).html("Unzipping = " + parseInt(app.ajax.result.latest_progress_details_array[0]) + "/" + parseInt(app.ajax.result.latest_progress_details_array[5]) + "<br><span style='font-size:4px'>" + download_speed + "</span><br>" + app.ajax.result.latest_progress_details_array[4]);
                                                            }
                                                        }else{
                                                            if(app.ajax.result.state != 'notified'){
                                                                $(class_selector).parent().parent().parent().parent().first().css('padding-bottom', '');
                                                                $(class_selector).parent().parent().css('display', '');
                                                                $(class_selector).parent().parent().parent().css('display', '').css('padding-top', '');

                                                                if(app.ajax.result.finished == true && app.ajax.result.state == 'downloaded'){
                                                                    $(class_selector).html(app.ajax.result.state + '... testing zip integrity...');
                                                                }else if(app.ajax.result.finished == true && app.ajax.result.state == 'moving_to_jobFolder'){
                                                                    $(class_selector).html('checking file integrity...');
                                                                }else{
                                                                    $(class_selector).html(app.ajax.result.state);
                                                                }
                                                            }else{
                                                                /** the state is notified you can do some cleaning up .. */
                                                                /** or reload the page would be faster to get the item out from being done. */
                                                                /** perhaps we should check first if the user is doing something like doing another scan before reloading the page */
                                                                $(document).ajaxStop(function () {
                                                                    console.log('all ajax requests finished');
                                                                    window.location.reload();
                                                                });
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    /** PLEASE NO ALERTS AT ALL*/
                                                }

                                            } else {
                                                $(container).html(response);
                                            }
                                            if (typeof call_back == 'function') {
                                                call_back.call();
                                            }
                                        },
                                        error: function(error) {
                                            /**console.log(url);*/
                                            /**console.log(data);*/
                                            /**console.log(error);*/
                                            if (error.status == 500 || error.status == 405 || error.status == 505 || error.status == 503) {
                                                $('.jconfirm-buttons').children().next('.btn-default').trigger('click');
                                                $('.custom_loader_section').animate({
                                                    opacity: 0
                                                }, {
                                                    duration: 500,
                                                    complete: function() {
                                                        $('.custom_loader_section').css('display', 'none');
                                                        $('.search_keynumber_input').prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                                                        clearInterval(window.autoPollrefreshIntervalId);
                                                        clearInterval(window.autoPollrefreshIntervalId_ASAP);
                                                    }
                                                });

                                                if($("#callGeterror").is(":visible")){
                                                    /**alert("The paragraph  is visible.");*/
                                                    /** do not need to alert again like spamming the client */
                                                } else{
                                                    $.alert({
                                                        title: 'Alert!' + '<span id="callGeterror" style="color: #FFF; font-size: 7px;">app.ajax.callGET.error</span>',
                                                        content: error.statusText + ' : ' + error.status,
                                                    });
                                                }
                                            }
                                        }
                                    });
                                });
                            });
                            clearInterval(window.autoPollrefreshIntervalId_ASAP);
                        }, 1000);
                    }

                    window.autoPollrefreshIntervalId = setInterval(function() {
                        /** this abortAll function seems to cancel the Job ID Number scan from actually happening */
                        /** you should do two things if it gets canceled then notify the user .. */
                        /** and firstly don't cancel it */
                        /**$.xhrPool.abortAll();*/

                        count_iteration_attempts = count_iteration_attempts - 1;
                        /**console.log(count_iteration_attempts);*/
                        if(count_iteration_attempts == 0){
                            clearInterval(window.autoPollrefreshIntervalId);
                            /** we can probably reload the window */
                            count_iteration_attempts = 0;
                            if(array_of_case_ids_on_page_and_downloading.length > 0){
                                window.location.reload();
                            }
                        }else{
                            $.each(array_of_case_ids_on_page_and_downloading, function(index_case_id, dl_types_in_progess_per_case_id) {
                                /**console.log(app.data.currently_downloading_aria2c[dl_types_in_progess_per_case_id]);*/

                                $.each(app.data.currently_downloading_aria2c[dl_types_in_progess_per_case_id], function(dl_type_index, dl_type_details) {
                                    if(dl_type_index == 'example'){
                                        var encrypted_case_id_checking_progress_for = dl_type_details["encrypted_case_id"];
                                        var encrypted_type_checking_progress_for = dl_type_details["encrypted_type"];
                                    }
                                    if(dl_type_index == 'new'){
                                        var encrypted_case_id_checking_progress_for = dl_type_details["encrypted_case_id"];
                                        var encrypted_type_checking_progress_for = dl_type_details["encrypted_type"];
                                    }
                                    if(dl_type_index == 'ready'){
                                        var encrypted_case_id_checking_progress_for = dl_type_details["encrypted_case_id"];
                                        var encrypted_type_checking_progress_for = dl_type_details["encrypted_type"];
                                    }

                                    var ajaxdata = {};
                                    var container = null;
                                    var callback = null;

                                    $.ajax({
                                        type: "GET",
                                        url: app.data.urlGetAria2cDownloadProgressDetailsOfCaseID + "/" + encrypted_case_id_checking_progress_for + "/" + encrypted_type_checking_progress_for,
                                        data: ajaxdata,
                                        dataType: 'json',
                                        async: true,
                                        beforeSend: function(jqXHR) {
                                            $.xhrPool_get_allowed_remaining.push(jqXHR); /**  add connection to list */
                                            window.aklsdf_get_allowed_remaining.push(jqXHR);
                                        },
                                        complete: function(jqXHR) {
                                            var i = $.xhrPool_get_allowed_remaining.indexOf(jqXHR); /**  get index for current connection completed */
                                            if (i > -1) {
                                                $.xhrPool_get_allowed_remaining.splice(i, 1); /**  removes from list by index */
                                                window.aklsdf_get_allowed_remaining.splice(i, 1);
                                            }
                                        },
                                        success: function(response) {
                                            if (app.util.isJson(response)) {
                                                app.ajax.result = app.util.parseJson(response);
                                                

                                                if (app.ajax.result.error) {
                                                    this.error = true;
                                                    $.alert({
                                                        title: 'Alert!' + '<span style="color: #FFF; font-size: 7px;">app.ajax.callGET.success</span>',
                                                        content: app.ajax.result.error.msg,
                                                    });
                                                }

                                                if (app.ajax.result.success == true) {
                                                    /** while it is polling you could also update the number of files for this type in the correct location for the case */
                                                    if(app.ajax.result.file_count == null){
                                                        var type_file_count_to_display = "&nbsp;&nbsp;&nbsp;&nbsp;";
                                                    }else{
                                                        var type_file_count_to_display = app.ajax.result.file_count;
                                                    }
                                                    /** put the details as a tool tip to the respective item on the page and make sure that it can be dynamic */
                                                    if(app.ajax.result.type == 'example'){
                                                        var class_selector = '.iecd_'+app.ajax.result.caseId;
                                                        $($(class_selector).parent().parent().parent().parent().first().children().first().children()[0]).html(type_file_count_to_display);
                                                    }
                                                    if(app.ajax.result.type == 'new'){
                                                        var class_selector = '.incd_'+app.ajax.result.caseId;
                                                        $($(class_selector).parent().parent().parent().parent().first().children().first().children()[2]).html(type_file_count_to_display);
                                                    }
                                                    if(app.ajax.result.type == 'ready'){
                                                        var class_selector = '.ircd_'+app.ajax.result.caseId;
                                                        $($(class_selector).parent().parent().parent().parent().first().children().first().children()[4]).html(type_file_count_to_display);
                                                    }

                                                    if (app.ajax.result.latest_progress_details_array != null || app.ajax.result.latest_progress_details_array != undefined) {
                                                        if(app.ajax.result.latest_progress_details_array.length >= 1){
                                                            /**console.log('progress details array', app.ajax.result.latest_progress_details_array);*/
                                                            if(app.ajax.result.state == 'moving_to_jobFolder'){
                                                                /** should be looking for the rsync progress log */
                                                                $(class_selector).parent().parent().parent().parent().first().css('padding-bottom', '');
                                                                $(class_selector).parent().parent().css('display', '');
                                                                $(class_selector).parent().parent().parent().css('display', '').css('padding-top', '');
                                                                /**30,290,409,773 100%   75.83MB/s    0:06:20*/
                                                                var transfered_size_human_readable = bytesToSize(app.ajax.result.latest_progress_details_array[0].replaceAll(new RegExp(",", 'g'),""));
                                                                /**console.log(transfered_size_human_readable);*/
                                                                //var remaining_time_human_readable = secondsToHms(hmsToSecondsOnly(app.ajax.result.latest_progress_details_array[3]));
                                                                var remaining_time_human_readable = secondsToHms(hmsToSecondsOnly(app.ajax.result.latest_progress_details_array[2]));
                                                                /**console.log(remaining_time_human_readable);*/
                                                                /**console.log($(class_selector));*/
                                                                var zip_size_human_readable = bytesToSize(app.ajax.result.zip_size);
                                                                var download_speed = app.ajax.result.latest_progress_details_array[2];
                                                                $(class_selector).html(transfered_size_human_readable+"/"+zip_size_human_readable+"("+app.ajax.result.latest_progress_details_array[1] + ")<br>ETA:" +remaining_time_human_readable + " <i>" + download_speed + "</i>");
                                                            }else if(app.ajax.result.state == 'downloading'){
                                                                /** should be looking for the aria2c progress log */
                                                                $(class_selector).parent().parent().parent().parent().first().css('padding-bottom', '');
                                                                $(class_selector).parent().parent().css('display', '');
                                                                $(class_selector).parent().parent().parent().css('display', '').css('padding-top', '');
                                                                var download_speed = app.ajax.result.latest_progress_details_array[3]+"/s";
                                                                $(class_selector).html(app.ajax.result.latest_progress_details_array[1] + "<br>" +app.ajax.result.latest_progress_details_array[4] + " <i>" + download_speed + "</i>");
                                                            }else if(app.ajax.result.state == 'downloaded'){
                                                                /** should be looking for the unzip -t pv progress log */
                                                                $(class_selector).parent().parent().parent().parent().first().css('padding-bottom', '');
                                                                $(class_selector).parent().parent().css('display', '');
                                                                $(class_selector).parent().parent().parent().css('display', '').css('padding-top', '');
                                                                var download_speed = app.ajax.result.latest_progress_details_array[3];
                                                                $(class_selector).html("Unzip test = " + parseInt(app.ajax.result.latest_progress_details_array[0]) + "/" + parseInt(app.ajax.result.latest_progress_details_array[5]) + "<br><span style='font-size:4px'>" + download_speed + "</span><br>" + app.ajax.result.latest_progress_details_array[4]);
                                                            }else{
                                                                /**console.log(app.ajax.result.state);*/
                                                                /** should be looking for the unzip -o pv progress log */
                                                                $(class_selector).parent().parent().parent().parent().first().css('padding-bottom', '');
                                                                $(class_selector).parent().parent().css('display', '');
                                                                $(class_selector).parent().parent().parent().css('display', '').css('padding-top', '');
                                                                var download_speed = app.ajax.result.latest_progress_details_array[3];
                                                                $(class_selector).html("Unzipping = " + parseInt(app.ajax.result.latest_progress_details_array[0]) + "/" + parseInt(app.ajax.result.latest_progress_details_array[5]) + "<br><span style='font-size:4px'>" + download_speed + "</span><br>" + app.ajax.result.latest_progress_details_array[4]);                                                                
                                                            }
                                                        }else{
                                                            if(app.ajax.result.state != 'notified'){
                                                                $(class_selector).parent().parent().parent().parent().first().css('padding-bottom', '');
                                                                $(class_selector).parent().parent().css('display', '');
                                                                $(class_selector).parent().parent().parent().css('display', '').css('padding-top', '');

                                                                if(app.ajax.result.finished == true && app.ajax.result.state == 'downloaded'){
                                                                    $(class_selector).html(app.ajax.result.state + '... testing zip integrity...');
                                                                }else if(app.ajax.result.finished == true && app.ajax.result.state == 'moving_to_jobFolder'){
                                                                    $(class_selector).html('checking file integrity...');
                                                                }else{
                                                                    $(class_selector).html(app.ajax.result.state);
                                                                }
                                                            }else{
                                                                /** the state is notified you can do some cleaning up .. */
                                                                /** or reload the page would be faster to get the item out from being done. */
                                                                /** perhaps we should check first if the user is doing something like doing another scan before reloading the page */
                                                                $(document).ajaxStop(function () {
                                                                    console.log('all ajax requests finished');
                                                                    window.location.reload();
                                                                });
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    /** PLEASE NO ALERTS AT ALL*/
                                                }

                                            } else {
                                                $(container).html(response);
                                            }
                                            if (typeof call_back == 'function') {
                                                call_back.call();
                                            }
                                        },
                                        error: function(error) {
                                            /**console.log(url);*/
                                            /**console.log(data);*/
                                            /**console.log(error);*/
                                            if (error.status == 500 || error.status == 405 || error.status == 505 || error.status == 503) {
                                                $('.jconfirm-buttons').children().next('.btn-default').trigger('click');
                                                $('.custom_loader_section').animate({
                                                    opacity: 0
                                                }, {
                                                    duration: 500,
                                                    complete: function() {
                                                        $('.custom_loader_section').css('display', 'none');
                                                        $('.search_keynumber_input').prop('disabled', false).css('cursor', '').css('background-color', '').css('border-color', '');
                                                        clearInterval(window.autoPollrefreshIntervalId);
                                                        clearInterval(window.autoPollrefreshIntervalId_ASAP);                                                        
                                                    }
                                                });
                                                if($("#callGeterror").is(":visible")){
                                                    /**alert("The paragraph  is visible.");*/
                                                    /** do not need to alert again like spamming the client */
                                                } else{
                                                    $.alert({
                                                        title: 'Alert!' + '<span id="callGeterror" style="color: #FFF; font-size: 7px;">app.ajax.callGET.error</span>',
                                                        content: error.statusText + ' : ' + error.status,
                                                    });
                                                }
                                            }
                                        }
                                    });
                                });
                            });
                        }
                    }, delay_to_use);
                };
                /** start it */
                autoPolltimer();
            },
            filter: {
                // Add html & event for team filter
                byAssignee: function(table) {
                    var assignees = app.util.build.assignees();
                    var assigneesFilter = $("div#assignees_filter");
                    assigneesFilter.html(assignees);
                },
                byColVis: function(table) {
                    var custom_visibility_buttons = app.util.build.custom_visibility_buttons_manualdl();
                    var colVisButtons = $("div#custom_visibility_buttons");
                    colVisButtons.html(custom_visibility_buttons).css('margin-left', '10px').addClass('dataTables_length');
                },
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
                            table.column(app.conf.table.filterColumn.managemanualdownloadlistInfo.team).search(options_all, true, false).draw();
                        } else {
                            table.column(app.conf.table.filterColumn.managemanualdownloadlistInfo.team).search('^' + options_all + '$', true, false).draw();
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
                            table.column(app.conf.table.filterColumn.managemanualdownloadlistInfo.position).search(options_all, true, false).draw();
                        } else {
                            table.column(app.conf.table.filterColumn.managemanualdownloadlistInfo.position).search('^' + options_all + '$', true, false).draw();
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
                        table.column(app.conf.table.filterColumn.managemanualdownloadlistInfo.status + 1).search(options_all, true, false).draw();
                    });
                },
                byEnabledDisabled: function(table) {
                    var enabled_disabled = app.util.build.enabled_disabled();
                    var enabled_disabledFilter = $("div#enabled_disabled_filter");
                    enabled_disabledFilter.html(enabled_disabled);
                    // Custom filter event
                    $("select[id='enabled_disabled_filter']").on('change', function() {
                        var options_all = $("#enabled_disabled_filter option:selected").map(function() {
                            return $(this).val();
                        }).get().join('|');
                        table.column(app.conf.table.filterColumn.managemanualdownloadlistInfo.enabled_disabled).search(options_all, true, false).draw();
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
                        table.column(app.conf.table.filterColumn.managemanualdownloadlistInfo.sections).search(options_all, true, false).draw();
                    });
                },
                byJobStatus: function(table) {
                    var jobstatus = app.util.build.jobstatus();
                    var jobstatusFilter = $("div#jobstatus_filter");
                    jobstatusFilter.html(jobstatus);
                    // Custom filter event
                    $("select[id='jobstatus_filter']").on('change', function() {
                        var options_all = $("#jobstatus_filter option:selected").map(function() {
                            return $(this).text();
                        }).get().join('|');
                        window.manualdownloadlisttable.column(app.conf.table.filterColumn.managemanualdownloadlistInfo.jobstatus).search(options_all, true, false).draw();
                    });
                },
                exportExcel: function(table) {
                    var a = app.util.build.exportbutton();
                    var position = a.search("data-action=") + 13;
                    var b = app.data.URL_getmanualdownloadlistinfoExportExcel;
                    var exportbuttonmake = [a.slice(0, position), b, a.slice(position)].join('');
                    var exportbuttonlocation = $("div#export_buttonlocation");
                    exportbuttonlocation.html(exportbuttonmake);
                },
                clearallfilter: function(table) {
                    var clearfilter = app.util.build.clearallfiltersbutton();
                    var clearFilterloc = $("div#clear_filter");
                    clearFilterloc.html(clearfilter);
                },
                byGlobalSearch: function(table) {
                    var global_search = app.util.build.global_search();
                    var global_searchFilter = $("div#custom_global_filter");
                    global_searchFilter.html(global_search);
                    // Custom filter event
                },
            }
        },
    },
};
