<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Not Started</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1050;
            padding: 20px;
        }

        .modal-content-custom {
            background: white;
            border-radius: 16px;
            padding: 30px 25px;
            max-width: 380px;
            width: 100%;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.25);
            text-align: center;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .modal-content-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-wrapper {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
            }
            50% {
                box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
            }
        }

        .icon-wrapper i {
            font-size: 35px;
            color: white;
        }

        .modal-content-custom h2 {
            color: #2d3748;
            font-weight: 800;
            margin-bottom: 12px;
            font-size: 24px;
            letter-spacing: -0.5px;
        }

        .modal-content-custom p {
            color: #718096;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .class-info {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 18px;
            border-radius: 14px;
            margin: 20px 0;
            border-left: 5px solid #667eea;
        }

        .class-info-item {
            margin: 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .class-info-icon {
            font-size: 18px;
            color: #667eea;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 8px;
        }

        .class-info-text {
            text-align: left;
        }

        .class-info-label {
            color: #a0aec0;
            font-weight: 600;
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .class-info-value {
            color: #2d3748;
            font-size: 15px;
            font-weight: 700;
        }

        .alert-box {
            margin-top: 18px;
            padding: 14px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 10px;
            border-left: 4px solid #f59e0b;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-box i {
            color: #f59e0b;
            font-size: 16px;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .alert-text {
            color: #92400e;
            font-weight: 600;
            font-size: 13px;
            text-align: left;
            line-height: 1.4;
        }

        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 11px 35px;
            border-radius: 22px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            margin-top: 8px;
        }

        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-back:active {
            transform: translateY(-1px);
        }

        .spinner-icon {
            display: inline-block;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 576px) {
            .modal-content-custom {
                padding: 25px 20px;
            }

            .modal-content-custom h2 {
                font-size: 20px;
            }

            .icon-wrapper {
                width: 60px;
                height: 60px;
            }

            .icon-wrapper i {
                font-size: 30px;
            }

            .class-info-item {
                flex-direction: column;
                text-align: center;
            }

            .class-info-text {
                text-align: center;
            }

            .alert-box {
                flex-direction: column;
                text-align: center;
            }

            .alert-text {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <div class="modal-content-custom">
            <div class="icon-wrapper">
                <i class="fas fa-clock"></i>
            </div>

            <h2>Class Not Started</h2>

            <p>The instructor hasn't started the class yet. Please wait for the teacher to open the meeting.</p>

            <div class="class-info">
                <div class="class-info-item">
                    <div class="class-info-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="class-info-text">
                        <span class="class-info-label">Subject</span>
                        <span class="class-info-value">{{ $subject }}</span>
                    </div>
                </div>

                @if($day)
                    <div class="class-info-item">
                        <div class="class-info-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="class-info-text">
                            <span class="class-info-label">Date</span>
                            <span class="class-info-value">{{ $day }}</span>
                        </div>
                    </div>
                @endif

                <div class="class-info-item">
                    <div class="class-info-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="class-info-text">
                        <span class="class-info-label">Class</span>
                        <span class="class-info-value">{{ $className }}</span>
                    </div>
                </div>
            </div>

            <div class="alert-box">
                <i class="fas fa-lightbulb"></i>
                <span class="alert-text">
                    <span class="spinner-icon"><i class="fas fa-spinner"></i></span>
                    Try refreshing the page in a few moments
                </span>
            </div>

            <div>
                <a href="javascript:history.back()" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
