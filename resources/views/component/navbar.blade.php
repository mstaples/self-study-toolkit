<div id="nav">
<ul>
    @if(Auth::check())
    <li @if(isset($nav) && $nav == 'paths')
            class="current"
        @endif>
        <a href="{{ url('/curriculum') }}">Paths</a>
    </li>
    <li @if(isset($nav) && $nav == 'prompts')
        class="current"
        @endif>
        @if(isset($pathId) && strlen($pathId) > 0)
            <a href="{{ url('/curriculum/prompts/' . $pathId) }}">Prompts</a>
        @else
            <a href="{{ url('/curriculum/prompts/') }}">Prompts</a>
        @endif
    </li>
        <li @if(isset($nav) && $nav == 'editors')
            class="current"
            @endif>
            @if(isset($pathId) && strlen($pathId) > 0)
                <a href="{{ url('/curriculum/editors/' . $pathId) }}">Editors</a>
            @endif
        </li>
    <li @if(isset($nav) && $nav == 'questions')
        class="current"
        @endif>
        @if(isset($questionId) && strlen($questionId) > 0)
            <a href="{{ url('/curriculum/questions/' . $questionId) }}">Questions</a>
        @else
            <a href="{{ url('/curriculum/questions/knowledge') }}">Questions</a>
        @endif
    </li>
        <li @if(isset($nav) && $nav == 'knowledges')
            class="current"
            @endif>
            <a href="{{ url('curriculum/knowledges/') }}">Knowledges</a>
        </li>
    @else
        <li @if(\Request::is('login'))class="current"@endif><a href="{{ route('login') }}">Login</a></li>
        <li @if(\Request::is('register'))class="current"@endif><a href="{{ route('register') }}">Register</a></li>
    @endif
</ul>
    <div id="banner-message">
        @isset ($message)
            @if (strlen($message) > 1)
                <div class="alert alert-primary" role="{{ $message_role }}">
                    <p>{{ $message }}</p>
                </div>
            @endif
        @endisset
    </div>
</div>
