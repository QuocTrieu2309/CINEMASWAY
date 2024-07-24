<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            padding: 20px;
        }
        .ticket {
            border: 2px dashed #333;
            padding: 20px;
            width: 320px;
            margin: 10px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .ticket:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        .ticket .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 1.2em;
            color: #337ab7;
        }
        .ticket .details {
            margin-bottom: 10px;
        }
        .ticket .details p {
            margin: 5px 0;
        }
        .ticket .details p span {
            font-weight: bold;
            color: #666;
        }
        .ticket .price {
            text-align: right;
            font-weight: bold;
            font-size: 1.1em;
            color: #d9534f;
            margin-bottom: 10px;
        }
        .ticket .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9em;
            color: #555;
            border-top: 1px solid #eee;
            padding-top: 10px;
            background-color: #f9f9f9;
            border-radius: 0 0 10px 10px;
        }
        .ticket .footer p {
            margin: 5px 0;
        }
        .ticket img {
            width: 100%;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    @foreach($tickets as $ticket)
    <div class="ticket">
        <div class="header">
            Công ty Cổ phần CinemaGO<br>
            Rạp Chiếu Phim {{ $ticket['cinema'] }}
        </div>
        <div class="details">
            <p><span>Phim:</span> {{ $ticket['movie_name'] }}</p>
            <p><span>Màn Hình:</span> {{ $ticket['screen'] }}</p>
            <p><span>Suất chiếu:</span> {{ $ticket['show_time'] }}</p>
            <p><span>Ngày chiếu:</span> {{ $ticket['show_date'] }}</p>
            <p><span>Ghế:</span> {{ $ticket['seat_number'] }}</p>
        </div>
        <div class="price">
            Giá vé: {{ number_format($ticket['price'], 0, ',', '.') }} VND
        </div>
        <p><img src="{{$ticket['code']}}"></p>
        <div class="footer">
            Cảm ơn bạn đã chọn CinemaGO! Chúc bạn có một trải nghiệm thú vị.
            <p>Vui lòng giữ vé cẩn thận. Nếu làm mất vé trước khi vào phòng chiếu, chúng tôi sẽ không chịu trách nhiệm!</p>
            <p>Trân trọng,</p>
            <p>Đội ngũ CinemaGO</p>
        </div>
    </div>
    @endforeach
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
