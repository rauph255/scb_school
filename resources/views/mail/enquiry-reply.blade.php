<!doctype html>
<html lang="en">
<body>
    <p>Dear {{ $recipientName }},</p>
    @foreach(preg_split('/\R{2,}/', trim($replyBody)) ?: [] as $paragraph)
        <p>{!! nl2br(e($paragraph)) !!}</p>
    @endforeach
    <p><strong>Reference:</strong> {{ $referenceCode }}</p>
    <p>Kind regards,<br>{{ config('mail.from.name') }}</p>
</body>
</html>
