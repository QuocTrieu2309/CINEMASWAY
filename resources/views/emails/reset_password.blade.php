<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <!-- CSS Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- JavaScript Bootstrap 5 (được yêu cầu cho các tính năng như dropdown, modal, v.v.) -->
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
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
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
            font-size: 18px;
            color: #4caf50;
            margin-top: 10px;
        }
        .link-button{
            border: 1px solid black;
            padding: 5px 3px;
            display: inline-block;
        }
        .password{
            border: 1px solid black;
            padding: 5px 3px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Xác nhận tài khoản Email</h2>
    <div clasThông báo đăng ký tài khoảns="success-message">
        <span class="success-icon">&#10004;</span>
        <p class="success-text">Vui lòng truy cập vào đường link bên dưới để tiến hành cài đặt lại mật khẩu. Thời hạn truy cập là 90s kể từ khi nhận được email</p>
        <form action="{{ url('/api/account/check-token') }}" method="post">
            @csrf
            <input type="hidden" name="token" value={{$token}}>
            <input type="hidden" name="email" value={{$email}}>
            <label for="">Vui lòng nhập mật khẩu mới của bạn</label>
            <input type="text" name="password" class='password'>
            <button type="submit" class="link-button">Xác Nhận</button>
        </form>
    </div>
</div>
</body>
</html>