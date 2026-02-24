<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reservation Updated</title>
</head>

<body style="background-color: #f0f2f5; margin: 0; padding: 40px 0; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 5px 10px rgba(0,0,0,0.05);">
                    <tr>
                        <td align="center">
                            <h1 style="font-size: 24px; color: #333; margin-bottom: 20px;">
                                Hello, {{ $reservation->user->name }}
                            </h1>

                            <p style="font-size: 16px; color: #555; line-height: 1.5;">
                                Your reservation has been <strong>updated successfully</strong>.<br><br>

                                <strong>Reservation Details:</strong><br>
                                <span><strong>Table Number:</strong> {{ $reservation->table->id }}</span><br>
                                <span><strong>Date & Time:</strong>
                                    {{ \Carbon\Carbon::parse($reservation->datetime)->format('F j, Y \a\t g:i A') }}</span><br>
                                @if ($reservation->info)
                                    <span><strong>Additional Info:</strong> {{ $reservation->info }}</span><br>
                                @endif
                            </p>

                            <div style="margin-top: 30px;">
                                <a href="{{ route('home') }}"
                                    style="display: inline-block; background-color: #3490dc; color: #ffffff; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-size: 16px;">
                                    Visit Our Website
                                </a>
                            </div>

                            <p style="margin-top: 40px; font-size: 14px; color: #999;">
                                Best regards,<br> Maillard
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>

</html>
