<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reservation Warning</title>
</head>

<body style="background-color: #f0f2f5; margin: 0; padding: 40px 0; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 5px 10px rgba(0,0,0,0.05);">
                    <tr>
                        <td align="center">
                            <h1 style="font-size: 24px; color: #d9534f; margin-bottom: 20px;">
                                Urgent: Your Reservation Will Expire Soon
                            </h1>

                            <p style="font-size: 16px; color: #555; line-height: 1.5;">
                                Hello, {{ $reservation->user->name }}.<br><br>
                                This is a friendly reminder that your reservation is about to expire.<br>

                                <strong>Reservation Details:</strong><br>
                                Date:
                                {{ \Carbon\Carbon::parse($reservation->datetime)->format('F j, Y \a\t g:i A') }}<br>
                                Number of Guests: {{ $reservation->table->capacity }}<br>
                                Table Number: {{ $reservation->table->id }}<br><br>

                                If you have any issues, please contact us as soon as possible.
                            </p>

                            <div style="margin-top: 30px;">
                                <a href="{{ route('home') }}"
                                    style="display: inline-block; background-color: #3490dc; color: #ffffff; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-size: 16px;">
                                    Visit Our Website
                                </a>
                            </div>

                            <p style="margin-top: 40px; font-size: 14px; color: #999;">
                                Thank you,<br> Maillard
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>

</html>
