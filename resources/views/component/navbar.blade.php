<div id="nav">
<ul>
    @if(Auth::check())
    <li @if(\Request::is('curriculum'))
            class="current"
        @endif>
        <a href="{{ url('/curriculum') }}">Paths</a>
    </li>
    <li @if(\Request::is('curriculum/prompts/'.$pathId ?? ''))
        class="current"
        @endif>
        <a href="{{ url('/curriculum/prompts/'.$pathId ?? '') }}">Prompts</a>
    </li>
    <li @if(\Request::is('curriculum/segments/'.$promptId ?? ''))
        class="current"
        @endif>
        <a href="{{ url('curriculum/segments/'.$promptId ?? '') }}">Segments</a>
    </li>
    <li @if(\Request::is('curriculum/questions/'.$pathId ?? ''))
        class="current"
        @endif>
        <a href="{{ url('curriculum/questions/'.$pathId ?? '') }}">Questions</a>
    </li>
    <li @if(\Request::is('curriculum/editors/'.$pathId ?? ''))
        class="current"
        @endif>
        <a href="{{ url('curriculum/editors/'.$pathId ?? '') }}">Editors</a>
    </li>
    @else
        <li @if(\Request::is('login'))class="current"@endif><a href="{{ route('login') }}">Login</a></li>
        <li @if(\Request::is('register'))class="current"@endif><a href="{{ route('register') }}">Register</a></li>
    @endif
</ul>
    @isset ($message)
        @if (strlen($message) > 1)
            <div class="alert alert-primary" role="{{ $message_role }}">
                <p>{{ $message }}</p>
            </div>
        @endif
    @endisset
</div>
