<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reservation Completed</title>
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
                                Hello, {{ $data['name'] }}!
                            </h1>

                            <p style="font-size: 16px; color: #555; line-height: 1.5;">
                                Thank you for dining with us! We truly appreciate your support and the time you spent
                                with us.
                            </p>

                            @if ($data['discount'])
                                <p style="font-size: 16px; color: #555; line-height: 1.5;">
                                    As a token of our gratitude, we're excited to offer you a <strong
                                        style="color: #e67e22;">special {{ $data['discount'] }}% discount</strong> on
                                    your next visit!
                                </p>

                                <p style="font-size: 18px; color: #e74c3c; font-weight: bold; margin: 20px 0;">
                                    Your Discount Code: {{ $data['discount_code'] }}
                                </p>

                                <p style="font-size: 16px; color: #555;">
                                    Simply present this code during your next visit to enjoy your discount.
                                </p>
                            @else
                                <p style="font-size: 16px; color: #555; line-height: 1.5;">
                                    We hope you had a wonderful experience, and we can't wait to welcome you back soon!
                                </p>
                            @endif

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
