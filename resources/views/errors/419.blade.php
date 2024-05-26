@extends('errors::minimal')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message', __('Page Expired'))

@section('fix')
    <a id="fix419Issue" class="click-me" style="display: none;">Click here to fix the 419 issue</a>
    <a id="logout"  style="display: none;" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit(); document.getElementById('logout').parentNode.parentNode.remove();">
        <i class="fa fa-sign-out"></i>&nbsp;{{ trans('message.partials.header.logout') }}
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        {{ csrf_field() }}
    </form>
    <script>
        $('#fix419Issue').css('color', '#fff').css('cursor', 'pointer');
        $('#fix419Issue').bind('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $(this).fadeOut(100).fadeIn(100).fadeOut(50).fadeIn(100);
            $('#logout').trigger('click');
        });
        $('#fix419Issue').trigger('click');
    </script>
@endsection
