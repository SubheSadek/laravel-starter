<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6; padding:40px 0;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:520px; background:#ffffff; border-radius:10px; overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="background:#2563eb; padding:20px; text-align:center;">
                        <h1 style="color:#ffffff; margin:0; font-size:22px;">
                            {{ $company_name }}
                        </h1>
                        <p style="color:#dbeafe; margin:5px 0 0; font-size:13px;">
                            Secure Email Verification
                        </p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:30px; text-align:center;">
                        <h2 style="margin-bottom:10px; color:#111827;">
                            Hello {{ $user_name }} 👋
                        </h2>

                        <p style="color:#6b7280; font-size:14px; line-height:1.6;">
                            Welcome to <strong>{{ $company_name }}</strong>!
                            Please use the verification code below to confirm your email address and complete your registration.
                        </p>

                        <!-- OTP -->
                        <div style="
                            margin:25px auto;
                            padding:15px 25px;
                            background:#f9fafb;
                            border:1px dashed #2563eb;
                            display:inline-block;
                            font-size:30px;
                            letter-spacing:8px;
                            font-weight:bold;
                            color:#2563eb;
                            border-radius:8px;
                        ">
                            {{ $otp }}
                        </div>

                        <p style="margin-top:20px; font-size:13px; color:#6b7280;">
                            This code will expire in <strong>5 minutes</strong>.
                        </p>

                        <p style="font-size:13px; color:#9ca3af; line-height:1.6;">
                            If you didn’t create an account with us, please ignore this email.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f9fafb; padding:15px; text-align:center; font-size:12px; color:#9ca3af;">
                        © {{ date('Y') }} {{ $company_name }}. All rights reserved.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</
