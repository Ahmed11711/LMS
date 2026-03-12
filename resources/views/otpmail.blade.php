<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f7f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7f9;
            padding-bottom: 40px;
        }
        .main-card {
            background-color: #ffffff;
            margin: 40px auto;
            width: 100%;
            max-width: 600px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #e1e8ed;
        }
        .header {
            background-color: #1a202c; /* لون داكن احترافي */
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .content {
            padding: 40px;
            text-align: center;
            line-height: 1.6;
            color: #4a5568;
        }
        .otp-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2d3748;
        }
        .otp-number {
            display: inline-block;
            background-color: #edf2f7;
            color: #2b6cb0;
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 8px;
            padding: 15px 30px;
            border-radius: 8px;
            margin: 25px 0;
            border: 1px dashed #cbd5e0;
        }
        .footer {
            padding: 20px;
            background-color: #f8fafc;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
            border-top: 1px solid #edf2f7;
        }
        .accent-bar {
            height: 4px;
            background: linear-gradient(90deg, #3182ce, #63b3ed);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <div class="accent-bar"></div>
            
            <div class="header">
                <h1 style="margin:0; font-size: 24px;">DARAB ACADEMY</h1>
            </div>

            <div class="content">
                <p style="font-size: 20px; margin-bottom: 20px;">مرحباً بك!</p>
                <p class="otp-title">لقد طلبت رمز التحقق الخاص بك لمرة واحدة (OTP)</p>
                
                <div class="otp-number">
                    {{ $otp ?? 150 }}
                </div>

                <p style="margin-top: 20px;">هذا الرمز صالح لمدة <b>5 دقائق</b> فقط.</p>
                <p style="font-size: 14px; color: #718096;">إذا لم تطلب هذا الرمز، يمكنك تجاهل هذا الإيميل بأمان.</p>
            </div>

            <div class="footer">
                <p>جميع الحقوق محفوظة &copy; {{ date('Y') }} - {{ $tenantName }}</p>
                <p>تم الإرسال بواسطة نظام الأكاديمية الذكي</p>
            </div>
        </div>
    </div>
</body>
</html>