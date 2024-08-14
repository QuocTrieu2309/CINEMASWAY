<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemasGO: Giao dịch thành công</title>
</head>

<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; background-color: #f5f5f5; min-height: 100vh;">
    <div style="padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 90%; max-width: 800px;">
        <h1 style="font-size: 24px; text-align: center; margin: 0; color: #333;">CinemasGO: Giao Dịch Thành Công</h1>
        <div style="text-align: center; margin-bottom: 15px;">
            <img src="https://res.cloudinary.com/dgxqkxclv/image/upload/depositphotos_117056896-stock-illustration-cinema-banners-vector-illustration_jm0a87" alt="CinemaGO" style="width: 100%; height: 400px; object-fit: contain;">
        </div>
        <div style="padding: 10px; border: 1px solid #ddd; margin-bottom: 20px;">
            <h2 style="margin: 0 0 10px; text-align: center; color: #333;">THÔNG TIN VÉ</h2>
            <table style="width: 100%; border-collapse: separate; margin-top: 20px;">

                <tr>
                    <th style="padding: 10px; text-align: center; background-color: #f0f0f0; font-weight: bold; border: 1px solid #ddd;">Tên phim:</th>
                    <td style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ $booking->showtime->movie->title }}</td>
                </tr>
                <tr>
                    <th style="padding: 10px; text-align: center; background-color: #f0f0f0; font-weight: bold; border: 1px solid #ddd;">Rạp:</th>
                    <td style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ $cinema->name }}</td>
                </tr>
                <tr>
                    <th style="padding: 10px; text-align: center; background-color: #f0f0f0; font-weight: bold; border: 1px solid #ddd;">Màn chiếu:</th>
                    <td style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ $booking->showtime->cinemaScreen->screen->name }}</td>
                </tr>
                <tr>
                    <th style="padding: 10px; text-align: center; background-color: #f0f0f0; font-weight: bold; border: 1px solid #ddd;">Ngày chiếu:</th>
                    <td style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ $showDate }}</td>
                </tr>
                <tr>
                    <th style="padding: 10px; text-align: center; background-color: #f0f0f0; font-weight: bold; border: 1px solid #ddd;">Giờ chiếu:</th>
                    <td style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ $showTime }}</td>
                </tr>
                <tr>
                    <th style="padding: 10px; text-align: center; background-color: #f0f0f0; font-weight: bold; border: 1px solid #ddd;">Ghế (số lượng):</th>
                    <td style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ count($seatDetails['seat_ids']) }} x {{ $seatDetails['seat_price'] }}</td>
                </tr>
                <tr>
                    <th style="padding: 10px; text-align: center; background-color: #f0f0f0; font-weight: bold; border: 1px solid #ddd;">Giá ghế:</th>
                    <td style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ $seatDetails['price'] }}</td>
                </tr>
                <tr>
                    <th style="padding: 10px; text-align: center; background-color: #f0f0f0; font-weight: bold; border: 1px solid #ddd;">Ghế ngồi:</th>
                    <td style="padding: 10px; text-align: center; border: 1px solid #ddd;">
                    {{ $seatDetails['seat_types']}} :

                        @foreach ($seatDetails['seat_numbers'] as $seatNumber)
                            {{ $seatNumber }}
                        @endforeach
                    </td>
                </tr>
            </table>
            <h4 style="margin-top: 5px; font-weight: bold; color: #333;">Dịch vụ đã chọn:</h4>
            <table style="width: 100%; border-spacing: 0; border-collapse: collapse;">
                <thead style="border: 1px solid #ddd; border-bottom: 0;">
                    <tr>
                        <th style="padding: 10px; text-align: center; background-color: #f0f0f0; border-bottom: 2px solid #ddd; font-weight: bold;">Dịch vụ</th>
                        <th style="padding: 10px; text-align: center; background-color: #f0f0f0; border-bottom: 2px solid #ddd; font-weight: bold;">Giá tiền</th>
                        <th style="padding: 10px; text-align: center; background-color: #f0f0f0; border-bottom: 2px solid #ddd;">Số lượng</th>
                        <th style="padding: 10px; text-align: center; background-color: #f0f0f0; border-bottom: 2px solid #ddd;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody style="border: 1px solid #ddd;">
                    @foreach ($seatDetails['services'] as $serviceName => $details)
                        <tr>
                            <td style="padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">{{ $serviceName }}</td>
                            <td style="padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">{{ $details['price'] }} VND</td>
                            <td style="padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">{{ $details['quantity'] }}</td>
                            <td style="padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">    {{ number_format($details['total'], 0, ',', '.') }} VND
                            VND</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="padding: 10px; text-align: center; border-left: none !important; border-right: none !important; border-bottom: none !important; font-weight: bold;">
                            Thanh Toán:{{ number_format($totalAmount, 0, ',', '.') }} VND                        </td>
                    </tr>
                </tfoot>
            </table>
            <hr />
            <div style="padding: 5px; text-align: center; width: 95%; padding-left: 2%;">
            <div style="text-align: center; margin-bottom: 20px;">
                    <img style="width: 50%; height: 50px;" src="{{ $barcodeUrl }}" alt="Booking Barcode">
                    <p style="padding:3px; text-align: center;">{{ $booking->ticket_code }}</p>
                 </div>
                <p style="text-align: center; margin: 5px 0;">Vui lòng đưa mã số này đến quầy vé CinemaGO để nhận vé của bạn<br>*Lưu ý: Vui lòng sử dụng loại vé đúng với độ tuổi theo quy định của CinemaGO. Chi tiết xem <a href="#">tại đây!</a></p>
            </div>
        </div>
        <div style="background-color: #720018; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px;">
            <p style="margin: 0;">CinemaGO Cinemas Việt Nam</p>
            <p style="margin: 0;">Trịnh Văn Bô - Nam Từ Liêm - Hà Nội</p>
            <p style="margin: 0;">Email hỗ trợ: <a href="mailto:hoidap@go.vn" style="color: white; text-decoration: underline;">hoidap@go.vn</a></p>
            <p style="margin: 0;">Hotline: 1900 6017</p>
        </div>
    </div>
</body>

</html>
