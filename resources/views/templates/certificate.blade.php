<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Achievement</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .container {
            width: 1000px;
            height: 700px;
            margin: auto;
            position: relative;
            background: white;
            border: 20px solid #1e293b;
            box-sizing: border-box;
            background-image: linear-gradient(135deg, #f1f5f9 25%, transparent 25%), linear-gradient(225deg, #f1f5f9 25%, transparent 25%), linear-gradient(45deg, #f1f5f9 25%, transparent 25%), linear-gradient(315deg, #f1f5f9 25%, #ffffff 25%);
            background-position: 10px 0, 10px 0, 0 0, 0 0;
            background-size: 20px 20px;
            background-repeat: repeat;
        }
        .inner-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #e2e8f0;
        }
        .content {
            text-align: center;
            padding: 80px 40px;
            position: relative;
            z-index: 10;
        }
        .badge {
            width: 120px;
            height: 120px;
            background: #4f46e5;
            border-radius: 50%;
            margin: 0 auto 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);
        }
        h1 {
            font-size: 64px;
            font-weight: 900;
            margin: 0;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: -2px;
        }
        .sub-header {
            font-size: 20px;
            color: #64748b;
            font-weight: 700;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
        }
        .recipient-name {
            font-size: 48px;
            font-weight: 900;
            color: #4f46e5;
            margin: 50px 0 20px;
        }
        .description {
            font-size: 18px;
            color: #475569;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
        }
        .course-title {
            font-weight: 900;
            color: #1e293b;
        }
        .footer {
            position: absolute;
            bottom: 80px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-around;
        }
        .signature {
            border-top: 1px solid #e2e8f0;
            width: 200px;
            padding-top: 10px;
            font-size: 14px;
            color: #64748b;
            font-weight: 700;
        }
        .cert-id {
            position: absolute;
            bottom: 20px;
            right: 40px;
            font-size: 10px;
            color: #94a3b8;
            font-weight: 700;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="inner-border"></div>
        <div class="content">
            <div class="badge">★</div>
            <h1 style="color: #0f172a;">Certificate</h1>
            <div class="sub-header">Of Achievement</div>
            
            <div class="recipient-name">{{ $user->name }}</div>
            
            <div class="description">
                This is to certify that the individual mentioned above has successfully completed the 
                <span class="course-title">{{ $course->title }}</span> 
                professional learning path with outstanding performance.
            </div>

            <div class="footer">
                <div class="signature">
                    Expert Signature
                    <br><span style="color: #1e293b; font-size: 16px;">{{ $course->expert->name }}</span>
                </div>
                <div class="signature">
                    Academic Director
                    <br><span style="color: #1e293b; font-size: 16px;">ClassHub.</span>
                </div>
            </div>
        </div>
        <div class="cert-id">CERTIFICATE ID: {{ $certificate->certificate_code }}</div>
    </div>
</body>
</html>
