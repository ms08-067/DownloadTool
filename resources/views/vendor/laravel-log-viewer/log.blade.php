<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="noindex, nofollow">
  <title>Br24 Download Upload Tool log viewer</title>
  <link rel="stylesheet"
        href="{{ URL::asset('/css/logviewer_bootstrap.css') }}">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
  <style>
    body {
      padding: 25px;
    }

    h1 {
      font-size: 1.5em;
      margin-top: 0;
    }

    #table-log {
        font-size: 0.85rem;
    }

    .sidebar {
        font-size: 0.85rem;
        line-height: 1;
        padding-right:  15px;
    }

    .btn {
        font-size: 0.7rem;
    }

    .stack {
      font-size: 0.85em;
    }

    .date {
      min-width: 75px;
    }

    .text {
      word-break: break-all;
    }

    a.llv-active {
      z-index: 2;
      background-color: #f5f5f5;
      border-left: 1px solid #777;
      border-right: 1px solid #777;
      border-bottom: 1px solid #777;
      border-top: 1px solid #777;
    }

    a.llv-active.list-group-item:last-child {
      z-index: 2;
      /**background-color: #f5f5f5;*/
      border-left: 1px solid #777;
      border-right: 1px solid #777;
      border-bottom: 1px solid #777;
      border-top: 1px solid #777;
    }

    .list-group-item {
      /**word-wrap: break-word;*/
    }

    .log-no-folder {
      word-wrap: break-word;
    }

    .list-group-item-logname {
      white-space: nowrap;
      overflow: hidden; 
      text-overflow: ellipsis;
    }

    .folder {
      padding-top: 15px;
    }

    .div-scroll {
      height: 82vh;
      overflow: hidden auto;
    }
    .nowrap {
      white-space: nowrap;
    }
    .list-group {
        padding: 5px;
        /**max-width: 275px;*/
    }


    /**
    * DARK MODE CSS
    */

    body[data-theme="dark"] {
      background-color: #151515;
      color: #cccccc;
    }

    [data-theme="dark"] a {
      color: #4da3ff;
    }

    [data-theme="dark"] a:hover {
      color: #a8d2ff;
    }

    [data-theme="dark"] .list-group-item {
      background-color: #1d1d1d;
      border-color: #444;
    }

    [data-theme="dark"] a.llv-active {
        background-color: #0468d2;
        border-color: rgba(255, 255, 255, 0.125);
        color: #ffffff;
    }

    [data-theme="dark"] a.list-group-item:focus, [data-theme="dark"] a.list-group-item:hover {
      background-color: #273a4e;
      border-color: rgba(255, 255, 255, 0.125);
      color: #ffffff;
    }

    [data-theme="dark"] .table td, [data-theme="dark"] .table th,[data-theme="dark"] .table thead th {
      border-color:#616161;
    }

    [data-theme="dark"] .page-item.disabled .page-link {
      color: #8a8a8a;
      background-color: #151515;
      border-color: #5a5a5a;
    }

    [data-theme="dark"] .page-link {
      background-color: #151515;
      border-color: #5a5a5a;
    }

    [data-theme="dark"] .page-item.active .page-link {
      color: #fff;
      background-color: #0568d2;
      border-color: #007bff;
    }

    [data-theme="dark"] .page-link:hover {
      color: #ffffff;
      background-color: #0051a9;
      border-color: #0568d2;
    }

    [data-theme="dark"] .form-control {
      border: 1px solid #464646;
      background-color: #151515;
      color: #bfbfbf;
    }

    [data-theme="dark"] .form-control:focus {
      color: #bfbfbf;
      background-color: #212121;
      border-color: #4a4a4a;
    }

    @keyframes slide {
      from { left:0%; transform: translate(0, 0); }
      to { left: -100%; transform: translate(-100%, 0); }
    }
    @-webkit-keyframes slide {
      from { left:0%; transform: translate(0, 0); }
      to { left: -100%; transform: translate(-100%, 0); }
    }

    .side-bar-text-container  {
      position:absolute;
      top:0;
      white-space: nowrap;
      width: 190px;
      max-width: 190px;
      overflow: hidden;
    }

    .side-bar-text  {
      position:relative;
      top:0;
      white-space: nowrap;
      animation-name: slide;
      animation-duration: 4s;
      animation-delay: 2s;
      animation-timing-function: ease-in-out;
      animation-iteration-count: infinite;
      animation-direction: alternate;
      -webkit-animation-name: slide;
      -webkit-animation-duration: 4s;
      -webkit-animation-delay: 2s;
      -webkit-animation-timing-function: ease-in-out;
      -webkit-animation-iteration-count: infinite;
    }
  </style>

  <script>
    function initTheme() {
        const darkThemeSelected = localStorage.getItem('darkSwitch') !== null && localStorage.getItem('darkSwitch') === 'dark';
        darkSwitch.checked = darkThemeSelected;
        darkThemeSelected ? document.body.setAttribute('data-theme', 'dark') :
        document.body.removeAttribute('data-theme');
    }

    function resetTheme() {
        if (darkSwitch.checked) {
            document.body.setAttribute('data-theme', 'dark');
            localStorage.setItem('darkSwitch', 'dark');
        } else {
            document.body.removeAttribute('data-theme');
            localStorage.removeItem('darkSwitch');
        }
    }
  </script>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col sidebar mb-3">
      <h1><i class="fa fa-calendar" aria-hidden="true"></i> Log Viewer</h1>
      <p class="text-muted"><i>Br24 Download Upload Tool</i></p>

      <div class="custom-control custom-switch" style="padding-bottom:20px;">
        <input type="checkbox" class="custom-control-input" id="darkSwitch">
        <label class="custom-control-label" for="darkSwitch" style="margin-top: 6px;">Dark Mode</label>
      </div>

      <div style="font-size: 10px;" class="list-group div-scroll">
        <?php
            \App\Http\Controllers\LaravelLogViewer\LaravelLogViewer::directoryTreeStructure($full_data);
        ?>
      </div>
    </div>
    <div class="col-10 table-container">
      @if ($logs === null)
        <div>
          Log file >50M, please download it.
        </div>
      @else
        <table id="table-log" class="table table-striped" data-ordering-index="{{ $standardFormat ? 2 : 0 }}">
          <thead>
          <tr>
            @if ($standardFormat)
              <th>Level</th>
              <th>Context</th>
              <th>Date</th>
            @else
              <th>Line number</th>
            @endif
            <th>Content</th>
          </tr>
          </thead>
          <tbody>

          @foreach($logs as $key => $log)
            <tr data-display="stack{{{$key}}}">
              @if ($standardFormat)
                <td class="nowrap text-{{{$log['level_class']}}}">
                  <span class="fa fa-{{{$log['level_img']}}}" aria-hidden="true"></span>&nbsp;&nbsp;{{$log['level']}}
                </td>
                <td class="text">{{$log['context']}}</td>
              @endif
              <td class="date">{{{$log['date']}}}</td>
              <td class="text">
                @if ($log['stack'])
                  <button type="button"
                          class="float-right expand btn btn-outline-dark btn-sm mb-2 ml-2"
                          data-display="stack{{{$key}}}">
                    <span class="fa fa-search"></span>
                  </button>
                @endif
                {{{$log['text']}}}
                @if (isset($log['in_file']))
                  <br/>{{{$log['in_file']}}}
                @endif
                @if ($log['stack'])
                  <div class="stack" id="stack{{{$key}}}"
                       style="display: none; white-space: pre-wrap;">{{{ trim($log['stack']) }}}
                  </div>
                @endif
              </td>
            </tr>
          @endforeach

          </tbody>
        </table>
      @endif
      <div class="p-3">
        @if($current_file)
          <a href="?dl={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
            <span class="fa fa-download"></span> Download file
          </a>
          -
          <a id="clean-log" href="?clean={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
            <span class="fa fa-sync"></span> Clean file
          </a>
          -
          <a id="delete-log" href="?del={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
            <span class="fa fa-trash"></span> Delete file
          </a>
          @if(count($files) > 1 || count($folder_files) > 1)
            -
            <a id="delete-all-log" href="?delall=true{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
              <span class="fa fa-trash-alt"></span> Delete all files
            </a>
          @endif
        @endif
      </div>
    </div>
  </div>
</div>
<!-- jQuery for Bootstrap -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
<!-- FontAwesome -->
<script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
<!-- Datatables -->
<script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>

<script>
    /**dark mode by https://github.com/coliff/dark-mode-switch*/
    const darkSwitch = document.getElementById('darkSwitch');

    /**this is here so we can get the body dark mode before the page displays*/
    /**otherwise the page will be white for a second... */
    initTheme();
    window.addEventListener('load', function(event) {
        if (darkSwitch) {
            initTheme();
            darkSwitch.addEventListener('change', function(event) {
                resetTheme();
            });
        }
    });

    /**end darkmode js*/
    window.onload = function() {
        var scrollTop = $('.div-scroll').scrollTop();
        var sessionstored_var = sessionStorage.getItem('logviewer_scroll_pos');
        if(sessionstored_var == null){
            sessionStorage.setItem('logviewer_scroll_pos', scrollTop);
        }else{
            $('.div-scroll').scrollTop(sessionstored_var);
        }

        var scrollTimer;
        $('.div-scroll').scroll(function(el) {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function() {
                /**console.log($('.div-scroll').scrollTop());*/
                scrollTop = $('.div-scroll').scrollTop();
                sessionStorage.setItem('logviewer_scroll_pos', scrollTop);
            }, 10);
        });


        /**console.log($('.table-container').height());*/
        /**console.log($('.sidebar').height());*/
        /**console.log($(window).width());*/
        
        var resizeTimer;
        resizeTimer = setTimeout(function() {
            $('.table-container').removeClass("col-12").addClass("col-10");

            if($('.table-container').height() > $('.sidebar').height()){
                $('.table-container').removeClass("col-12").addClass("col-10");
            }else{
                $('.table-container').removeClass("col-10").addClass("col-12");
            }
        }, 10);

        $(window).resize(function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                $('.table-container').removeClass("col-12").addClass("col-10");

                /**console.log($('.table-container').height());*/
                /**console.log($('.sidebar').height());*/
                /**console.log($(window).width());*/

                if($('.table-container').height() > $('.sidebar').height()){
                    $('.table-container').removeClass("col-12").addClass("col-10");
                }else{
                    $('.table-container').removeClass("col-10").addClass("col-12");
                }
            }, 10);
        });
        $('div.list-group-item').css('cursor', 'pointer');
        /**$('div.list-group-item').mouseenter(function() {$(this).css('cursor', 'pointer');}).mouseleave(function() {$(this).css('cursor', 'default');});*/
        $('div.list-group-item').on('click', function () {
          /**console.log($(this).children());*/
          /**console.log($(this).children()[0].href);*/
          if($(this).children()[0].href !== undefined && $(this).children().length <= 1){
            window.location.href = $(this).children()[0].href;
          }
        });
    };

    window.texthighlighted = false;
    $(document).mouseup(function(){
        var highlightedText = "";
        if (window.getSelection) {
            highlightedText = window.getSelection().toString();
        } else if (document.selection && document.selection.type != "Control") {
            highlightedText = document.selection.createRange().text;
        }
        if(highlightedText != ""){
            console.log("text highlighted.");
            window.texthighlighted = true;
        }else{
            window.texthighlighted = false;
        }
    });

    $(document).ready(function () {

        $('.table-container tr').on('click', function () {
          if(window.texthighlighted){
            /** dont open/ close the child row. */
          }else{
            $('#' + $(this).data('display')).toggle();
          }
        });

        $('#table-log').DataTable({
            "lengthMenu": [
            [10, 25, 50, 100, 3000],
            [10, 25, 50, 100, 3000]
            /**[-1, 10, 25, 50, 100, 3000],*/
            /**['All', 10, 25, 50, 100, 3000]*/
            /**[10, 25],*/
            /**[10, 25]*/
            ],
            "order": [$('#table-log').data('orderingIndex'), 'desc'],
            "stateSave": true,
            "stateSaveCallback": function (settings, data) {
                window.localStorage.setItem("datatable", JSON.stringify(data));
            },
            "stateLoadCallback": function (settings) {
                var data = JSON.parse(window.localStorage.getItem("datatable"));
                if (data) data.start = 0;
                return data;
            }
        });
        $('#delete-log, #clean-log, #delete-all-log').click(function () {
            return confirm('Are you sure?');
        });

        $('a[href]').off('click').on('click', function(event) {
            /**event.preventDefault();*/
            /**$(this).attr('href')*/
            /**console.log($(this).attr('href'));*/
        });
    });
</script>
</body>
</html>
