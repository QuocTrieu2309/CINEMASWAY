<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Confirmation</title>
    <!-- CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- JavaScript Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .success-message {
            text-align: center;
            margin-bottom: 20px;
        }

        .success-icon {
            font-size: 48px;
            color: #4caf50;
        }

        .success-text {
            font-size: 16px;
            color: #666;
            margin-top: 10px;
        }

        .form-label {
            margin-top: 20px;
            font-weight: bold;
            color: #333;
        }

        .password {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .link-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        .link-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Xác nhận tài khoản Email</h2>
        <div class="success-message">
            <span class="success-icon">&#10004;</span>
            <p class="success-text">Vui lòng truy cập vào đường link bên dưới để tiến hành cài đặt lại mật khẩu. Thời hạn truy cập là 90s kể từ khi nhận được email</p>
            <form action="{{ url('/api/account/check-token') }}" method="post">
                @csrf
                <input type="hidden" name="token" value="{{$token}}">
                <input type="hidden" name="email" value="{{$email}}">
                <label for="password" class="form-label">Vui lòng nhập mật khẩu mới của bạn</label>
                <input type="password" id="password" name="password" class="password" required>
                <button type="submit" class="link-button">Xác Nhận</button>
            </form>
        </div>
    </div>
</body>

</html>
