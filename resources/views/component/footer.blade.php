<div class="container">
    <div class="row justify-content-center" id="copyright">
    @if(Auth::check())
        <ul class="links">
            <li><a href="{{ url('password/reset') }}">reset password</a></li>
            <li><a href="{{ url('logout') }}">logout</a></li>
        </ul>
    @endif
    </div>
</div>
