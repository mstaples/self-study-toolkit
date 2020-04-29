@if(strlen($message) > 2)
    <div class="alert-{{ $message_role }}">
        <p>{{ $message }}</p>
    </div>
@endif
