<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Course Flyer</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            background: #0f172a;
            color: white;
            width: 1080px;
            height: 1350px;
        }
        .container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }
        .bg-mesh {
            position: absolute;
            top: -200px;
            right: -200px;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.4) 0%, transparent 70%);
            z-index: 1;
        }
        .content {
            position: relative;
            z-index: 10;
            padding: 100px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .expert-tag {
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 6px;
            color: #818cf8;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 120px;
            font-weight: 900;
            line-height: 0.9;
            margin: 0 0 40px 0;
            letter-spacing: -4px;
        }
        .description {
            font-size: 32px;
            line-height: 1.5;
            color: #94a3b8;
            max-width: 800px;
            margin-bottom: 60px;
        }
        .stats {
            display: flex;
            gap: 40px;
            margin-bottom: 80px;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 30px 50px;
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .stat-value {
            font-size: 48px;
            font-weight: 900;
            color: #4f46e5;
        }
        .stat-label {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #64748b;
        }
        .footer {
            padding: 0 100px 100px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .price {
            font-size: 84px;
            font-weight: 900;
        }
        .price span {
            font-size: 32px;
            color: #64748b;
        }
        .cta {
            background: #4f46e5;
            color: white;
            padding: 40px 80px;
            border-radius: 40px;
            font-size: 36px;
            font-weight: 900;
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.4);
        }
        .logo {
            position: absolute;
            top: 100px;
            right: 100px;
            font-size: 48px;
            font-weight: 900;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="bg-mesh"></div>
        <div class="logo">ClassHub<span style="color: #4f46e5;">.</span></div>
        
        <div class="content">
            <div class="expert-tag">Masterclass with {{ $expert->name }}</div>
            <h1>{{ $course->title }}</h1>
            <div class="description">
                {{ $course->description }}
            </div>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value">5.0</div>
                    <div class="stat-label">Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $course->passing_grade }}%</div>
                    <div class="stat-label">Min. Mastery</div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="price">
                <span>$</span>{{ $course->price }}
            </div>
            <div class="cta">JOIN NOW</div>
        </div>
    </div>
</body>
</html>
