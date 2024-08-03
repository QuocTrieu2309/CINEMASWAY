<?php

namespace App\Http\Controllers\API\Revenue;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class RevenueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // tổng của các rạp
    public function totalRevenue(Request $request)
    {
        $this->limit = $this->handleLimit($request->get('limit'), 13);
        $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), 'date');
        $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), 'desc');
        try {
            // Lấy tất cả các rạp
            $cinemas = Cinema::with(['cinemaScreens.showtimes.bookings.bookingServices.service'])->get();

            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subDays(59);

            $dailyData = [];

            foreach ($cinemas as $cinema) {
                foreach ($cinema->cinemaScreens as $screen) {
                    foreach ($screen->showtimes as $showtime) {
                        foreach ($showtime->bookings as $booking) {
                            $date = $booking->created_at->format('Y-m-d');
                            if (!isset($dailyData[$date])) {
                                $dailyData[$date] = [
                                    'doanh_thu' => [
                                        'tickets' => [
                                            'ticket_revenue' => 0,
                                            'ticket_quantity' => 0,
                                        ],
                                        'services' => [
                                            'total_service_revenue' => 0,
                                            'details' => []
                                        ],
                                        'total_income' => 0
                                    ]
                                ];
                            }
                            $ticketRevenue = 0;
                        foreach ($booking->tickets as $ticket) {
                            $seatType = $ticket->seat->seatType;
                            $isWeekend = Carbon::parse($showtime->show_date)->isWeekend();
                            $basePrice = $isWeekend ? $seatType->promotion_price : $seatType->price;
                            // Kiểm tra trạng thái showtime
                            $isEarlyStatus = $showtime->status === Showtime::STATUS_EARLY;
                            $price = $isEarlyStatus ? $basePrice * 1.5 : $basePrice;
                            $ticketRevenue += $price;
                        }
                            $dailyData[$date]['doanh_thu']['tickets']['ticket_revenue'] += $ticketRevenue;
                            $dailyData[$date]['doanh_thu']['tickets']['ticket_quantity'] += $booking->quantity;
                            foreach ($booking->bookingServices as $bookingService) {
                                $serviceName = $bookingService->service->name;
                                if (!isset($dailyData[$date]['doanh_thu']['services']['details'][$serviceName])) {
                                    $dailyData[$date]['doanh_thu']['services']['details'][$serviceName] = [
                                        'service' => $serviceName,
                                        'quantity' => 0,
                                        'subtotal' => 0
                                    ];
                                }
                                $dailyData[$date]['doanh_thu']['services']['details'][$serviceName]['quantity'] += $bookingService->quantity;
                                $dailyData[$date]['doanh_thu']['services']['details'][$serviceName]['subtotal'] += $bookingService->subtotal;
                                $dailyData[$date]['doanh_thu']['services']['total_service_revenue'] += $bookingService->subtotal;
                            }

                            $dailyData[$date]['doanh_thu']['total_income'] = $dailyData[$date]['doanh_thu']['tickets']['ticket_revenue'] + $dailyData[$date]['doanh_thu']['services']['total_service_revenue'];
                        }
                    }
                }
            }
            $currentDate = $endDate->copy(); // Bắt đầu từ ngày hôm nay
            while ($currentDate->gte($startDate)) { // Lặp ngược lại cho đến ngày bắt đầu
                $date = $currentDate->format('Y-m-d');
                if (!isset($dailyData[$date])) {
                    $dailyData[$date] = [
                        'doanh_thu' => [
                            'tickets' => [
                                'ticket_revenue' => 0,
                                'ticket_quantity' => 0,
                            ],
                            'services' => [
                                'total_service_revenue' => 0,
                                'details' => []
                            ],
                            'total_income' => 0
                        ]
                    ];
                }
                $currentDate->subDay(); // Lùi về một ngày
            }
            // Chuyển dữ liệu thành mảng
            $formattedData = [];
            foreach ($dailyData as $date => $data) {
                $formattedData[] = [
                    'date' => $date,
                    'doanh_thu' => [
                        'tickets' => $data['doanh_thu']['tickets'],
                        'services' => [
                            'total_service_revenue' => $data['doanh_thu']['services']['total_service_revenue'],
                            'details' => array_values($data['doanh_thu']['services']['details']),
                        ],
                        'total_income' => $data['doanh_thu']['total_income']
                    ]
                ];
            }

            // Sắp xếp dữ liệu theo ngày
            usort($formattedData, function ($a, $b) {
                return ($this->sort === 'desc')
                    ? strtotime($b['date']) - strtotime($a['date'])
                    : strtotime($a['date']) - strtotime($b['date']);
            });

            // Phân trang
            $totalDays = count($formattedData);
            $offset = ($this->limit * ($request->input('page', 1) - 1));
            $paginatedData = array_slice($formattedData, $offset, $this->limit);

            return ApiResponse(true, [
                'data' => $paginatedData,
                'current_page' => $request->input('page', 1),
                'per_page' => $this->limit,
                'total' => $totalDays,
                'last_page' => ceil($totalDays / $this->limit),
            ], Response::HTTP_OK, 'Thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    // doanh thu theo từng rạp theo ngày
    public function dailyRevenue($cinema_id, Request $request)
    {
        $this->limit = $this->handleLimit($request->get('limit'), 13);
        $this->order = $this->handleFilter(Config::get('paginate.orders'), $request->get('order'), 'date');
        $this->sort = $this->handleFilter(Config::get('paginate.sorts'), $request->get('sort'), 'desc');

        try {
            $cinema = Cinema::with(['cinemaScreens.showtimes.bookings.bookingServices.service'])
                ->findOrFail($cinema_id);

            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subDays(59);

            $dailyData = [];

            foreach ($cinema->cinemaScreens as $screen) {
                foreach ($screen->showtimes as $showtime) {
                    foreach ($showtime->bookings as $booking) {
                        $date = $booking->created_at->format('Y-m-d');
                        if (!isset($dailyData[$date])) {
                            $dailyData[$date] = [
                                'cinema' => $cinema->name,
                                'doanh_thu' => [
                                    'tickets' => [
                                        'ticket_revenue' => 0,
                                        'ticket_quantity' => 0,
                                    ],
                                    'services' => [
                                        'total_service_revenue' => 0,
                                        'details' => []
                                    ],
                                    'total_income' => 0
                                ]
                            ];
                        }

                        $ticketRevenue = 0;
                        foreach ($booking->tickets as $ticket) {
                            $seatType = $ticket->seat->seatType;
                            $isWeekend = Carbon::parse($showtime->show_date)->isWeekend();
                            $basePrice = $isWeekend ? $seatType->promotion_price : $seatType->price;
                            // Kiểm tra trạng thái showtime
                            $isEarlyStatus = $showtime->status === Showtime::STATUS_EARLY;
                            $price = $isEarlyStatus ? $basePrice * 1.5 : $basePrice;
                            $ticketRevenue += $price;
                        }
                        $dailyData[$date]['doanh_thu']['tickets']['ticket_revenue'] += $ticketRevenue;
                        $dailyData[$date]['doanh_thu']['tickets']['ticket_quantity'] += $booking->quantity;

                        foreach ($booking->bookingServices as $bookingService) {
                            $serviceName = $bookingService->service->name;
                            if (!isset($dailyData[$date]['doanh_thu']['services']['details'][$serviceName])) {
                                $dailyData[$date]['doanh_thu']['services']['details'][$serviceName] = [
                                    'service' => $serviceName,
                                    'quantity' => 0,
                                    'subtotal' => 0
                                ];
                            }
                            $dailyData[$date]['doanh_thu']['services']['details'][$serviceName]['quantity'] += $bookingService->quantity;
                            $dailyData[$date]['doanh_thu']['services']['details'][$serviceName]['subtotal'] += $bookingService->subtotal;

                            $dailyData[$date]['doanh_thu']['services']['total_service_revenue'] += $bookingService->subtotal;
                        }

                        $dailyData[$date]['doanh_thu']['total_income'] = $dailyData[$date]['doanh_thu']['tickets']['ticket_revenue'] + $dailyData[$date]['doanh_thu']['services']['total_service_revenue'];
                    }
                }
            }

            // Bổ sung các ngày không có dữ liệu booking với giá trị mặc định bằng 0
            $currentDate = $endDate->copy(); // Bắt đầu từ ngày hôm nay
            while ($currentDate->gte($startDate)) { // Lặp ngược lại cho đến ngày bắt đầu
                $date = $currentDate->format('Y-m-d');
                if (!isset($dailyData[$date])) {
                    $dailyData[$date] = [
                        'cinema' => $cinema->name,
                        'doanh_thu' => [
                            'tickets' => [
                                'ticket_revenue' => 0,
                                'ticket_quantity' => 0,
                            ],
                            'services' => [
                                'total_service_revenue' => 0,
                                'details' => []
                            ],
                            'total_income' => 0
                        ]
                    ];
                }
                $currentDate->subDay(); // Lùi về một ngày
            }
            // Chuyển dữ liệu thành mảng
            $formattedData = [];
            foreach ($dailyData as $date => $data) {
                $formattedData[] = [
                    'date' => $date,
                    'cinema' => $data['cinema'],
                    'doanh_thu' => [
                        'tickets' => $data['doanh_thu']['tickets'],
                        'services' => [
                            'total_service_revenue' => $data['doanh_thu']['services']['total_service_revenue'],
                            'details' => array_values($data['doanh_thu']['services']['details']),
                        ],
                        'total_income' => $data['doanh_thu']['total_income']
                    ]
                ];
            }

            // Sắp xếp dữ liệu theo ngày
            usort($formattedData, function ($a, $b) {
                return ($this->sort === 'desc')
                    ? strtotime($b['date']) - strtotime($a['date'])
                    : strtotime($a['date']) - strtotime($b['date']);
            });

            // Phân trang
            $totalDays = count($formattedData);
            $offset = ($this->limit * ($request->input('page', 1) - 1));
            $paginatedData = array_slice($formattedData, $offset, $this->limit);

            return ApiResponse(true, [
                'data' => $paginatedData,
                'current_page' => $request->input('page', 1),
                'per_page' => $this->limit,
                'total' => $totalDays,
                'last_page' => ceil($totalDays / $this->limit),
            ], Response::HTTP_OK, 'Thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }

    // thông kê phim bán được nhiều vé nhất và số lượng vé đã bán của phim hiện đang chiếu
    public function revenueFilms()
    {
        try {
            $allMovies = Movie::whereHas('showtimes', function ($query) {
                $query->where('show_date', '>=', Carbon::now()->format('Y-m-d'));
            })->get();
            // Truy vấn tổng số vé bán ra cho mỗi phim
            $ticketsSold = Showtime::whereHas('bookings')
                ->with('bookings')
                ->get()
                ->flatMap(function ($showtime) {
                    return $showtime->bookings->map(function ($booking) use ($showtime) {
                        return [
                            'movie_id' => $showtime->movie_id,
                            'tickets_sold' => $booking->quantity
                        ];
                    });
                })
                ->groupBy('movie_id')
                ->map(function ($items) {
                    return $items->sum('tickets_sold');
                });

            $results = $allMovies->map(function ($movie) use ($ticketsSold) {
                return [
                    'movie_title' => $movie->title,
                    'tickets_sold' => $ticketsSold->get($movie->id, 0)
                ];
            })
                ->sortByDesc('tickets_sold')
                ->values()
                ->all();
            return ApiResponse(true, $results, Response::HTTP_OK, 'Thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
