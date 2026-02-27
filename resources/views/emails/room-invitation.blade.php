<p>You have been invited to join the room <strong>{{ $invitation->room->name }}</strong>.</p>
<p>Invited by: {{ $invitation->inviter->name }} ({{ $invitation->inviter->email }})</p>
<p>
    Click here to accept: <a href="{{ $acceptUrl }}">{{ $acceptUrl }}</a>
</p>
@if($invitation->expires_at)
<p>This invitation expires at {{ $invitation->expires_at->toDayDateTimeString() }}.</p>
@endif