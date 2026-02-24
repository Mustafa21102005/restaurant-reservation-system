<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Timeout</title>
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
                                Hello, {{ $customer->name }}!
                            </h1>

                            <p style="font-size: 16px; color: #555; line-height: 1.5;">
                                We regret to inform you that your account has been timed out for the following reason:
                            </p>

                            <blockquote
                                style="font-style: italic; color: #777; margin: 20px 0; padding-left: 20px; border-left: 3px solid #3490dc;">
                                {{ $reason }}
                            </blockquote>

                            <p style="font-size: 16px; color: #555; line-height: 1.5;">
                                Your timeout expires at: {{ \Carbon\Carbon::parse($expiresAt)->toDayDateTimeString() }}.
                            </p>

                            <p style="font-size: 16px; color: #555; line-height: 1.5;">
                                If you believe this is a mistake, feel free to contact support.
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
