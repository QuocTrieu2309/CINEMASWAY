<?php

namespace App\Http\Controllers\API\Revenue;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Service;
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
    // tổng của các rạp theo thangs
    public function totalRevenue()
    {
        try {
            $cinemas = Cinema::with([
                'cinemaScreens.showtimes' => function ($query) {
                    $query->where('deleted', 0);
                },
                'cinemaScreens.showtimes.bookings' => function ($query) {
                    $query->where('deleted', 0);
                },
                'cinemaScreens.showtimes.bookings.bookingServices.service' => function ($query) {
                    $query->where('deleted', 0);
                },
                'cinemaScreens.showtimes.bookings.tickets.seat.seatType' => function ($query) {
                    $query->where('deleted', 0);
                }
            ])->where('deleted', 0)->get();

            $cinemaData = [];

            foreach ($cinemas as $cinema) {
                $totalTicketRevenue = 0;
                $totalTicketQuantity = 0;
                $totalServiceRevenue = 0;
                $serviceDetails = [];

                foreach ($cinema->cinemaScreens as $screen) {
                    foreach ($screen->showtimes as $showtime) {
                        foreach ($showtime->bookings as $booking) {
                            $ticketRevenue = 0;
                            foreach ($booking->tickets as $ticket) {
                                $seatType = $ticket->seat->seatType;
                                $isWeekend = Carbon::parse($showtime->show_date)->isWeekend();
                                $basePrice = $isWeekend ? $seatType->promotion_price : $seatType->price;
                                $isEarlyStatus = $showtime->status === Showtime::STATUS_EARLY;
                                $price = $isEarlyStatus ? $basePrice * 1.5 : $basePrice;
                                $ticketRevenue += $price;
                            }
                            $totalTicketRevenue += $ticketRevenue;
                            $totalTicketQuantity += $booking->quantity;

                            foreach ($booking->bookingServices as $bookingService) {
                                $serviceName = $bookingService->service->name;
                                if (!isset($serviceDetails[$serviceName])) {
                                    $serviceDetails[$serviceName] = [
                                        'service' => $serviceName,
                                        'quantity' => 0,
                                        'subtotal' => 0
                                    ];
                                }
                                $serviceDetails[$serviceName]['quantity'] += $bookingService->quantity;
                                $serviceDetails[$serviceName]['subtotal'] += $bookingService->subtotal;
                                $totalServiceRevenue += $bookingService->subtotal;
                            }
                        }
                    }
                }

                $cinemaData[] = [
                    'cinema' => $cinema->name,
                    'doanh_thu' => [
                        'tickets' => [
                            'quantity' => $totalTicketQuantity,
                            'subtotal' => $totalTicketRevenue,
                        ],
                        'services' => [
                            'quantity' => array_sum(array_column($serviceDetails, 'quantity')),
                            'subtotal' => $totalServiceRevenue,
                        ],
                        'total_income' => $totalTicketRevenue + $totalServiceRevenue
                    ]
                ];
            }

            return ApiResponse(true, $cinemaData, Response::HTTP_OK, 'Thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    // doanh thu theo từng rạp theo ngày
    public function dailyRevenue($cinema_id, Request $request)
    {
        try {
            $cinema = Cinema::with([
                'cinemaScreens.showtimes' => function ($query) {
                    $query->where('deleted', 0);
                },
                'cinemaScreens.showtimes.bookings' => function ($query) {
                    $query->where('deleted', 0);
                },
                'cinemaScreens.showtimes.bookings.bookingServices.service' => function ($query) {
                    $query->where('deleted', 0);
                },
                'cinemaScreens.showtimes.bookings.tickets.seat.seatType' => function ($query) {
                    $query->where('deleted', 0);
                }
            ])->findOrFail($cinema_id);

            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subMonths(11)->startOfMonth();

            $monthlyData = [];

            foreach ($cinema->cinemaScreens as $screen) {
                foreach ($screen->showtimes as $showtime) {
                    foreach ($showtime->bookings as $booking) {
                        $month = $booking->created_at->format('Y-m');
                        if (!isset($monthlyData[$month])) {
                            $monthlyData[$month] = [
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
                            $isEarlyStatus = $showtime->status === Showtime::STATUS_EARLY;
                            $price = $isEarlyStatus ? $basePrice * 1.5 : $basePrice;
                            $ticketRevenue += $price;
                        }
                        $monthlyData[$month]['doanh_thu']['tickets']['ticket_revenue'] += $ticketRevenue;
                        $monthlyData[$month]['doanh_thu']['tickets']['ticket_quantity'] += $booking->quantity;

                        foreach ($booking->bookingServices as $bookingService) {
                            $serviceName = $bookingService->service->name;
                            if (!isset($monthlyData[$month]['doanh_thu']['services']['details'][$serviceName])) {
                                $monthlyData[$month]['doanh_thu']['services']['details'][$serviceName] = [
                                    'service' => $serviceName,
                                    'quantity' => 0,
                                    'subtotal' => 0
                                ];
                            }
                            $monthlyData[$month]['doanh_thu']['services']['details'][$serviceName]['quantity'] += $bookingService->quantity;
                            $monthlyData[$month]['doanh_thu']['services']['details'][$serviceName]['subtotal'] += $bookingService->subtotal;

                            $monthlyData[$month]['doanh_thu']['services']['total_service_revenue'] += $bookingService->subtotal;
                        }

                        $monthlyData[$month]['doanh_thu']['total_income'] = $monthlyData[$month]['doanh_thu']['tickets']['ticket_revenue'] + $monthlyData[$month]['doanh_thu']['services']['total_service_revenue'];
                    }
                }
            }

            // Bổ sung các tháng không có dữ liệu booking với giá trị mặc định bằng 0
            $currentMonth = $endDate->copy()->startOfMonth();
            while ($currentMonth->gte($startDate)) {
                $month = $currentMonth->format('Y-m');
                if (!isset($monthlyData[$month])) {
                    $monthlyData[$month] = [
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
                $currentMonth->subMonth(); // Lùi về một tháng
            }

            // Chuyển dữ liệu thành mảng
            $formattedData = [];
            foreach ($monthlyData as $month => $data) {
                $formattedData[] = [
                    'month' => $month,
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

            // Sắp xếp dữ liệu theo tháng (giảm dần)
            usort($formattedData, function ($a, $b) {
                return strtotime($b['month']) - strtotime($a['month']);
            });

            return ApiResponse(true, $formattedData, Response::HTTP_OK, 'Thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
    // thông kê phim bán được nhiều vé nhất và số lượng vé đã bán của phim chưa hết hạn và showtime(deleted =0 )
    public function revenueFilms()
    {
        try {
            $allMovies = Movie::where('deleted', 0)
                ->whereHas('showtimes')
                ->get();
            $ticketsSold = Showtime::whereHas('bookings')
                ->with(['bookings', 'bookings.tickets.seat.seatType'])
                ->get()
                ->flatMap(function ($showtime) {
                    return $showtime->bookings->map(function ($booking) use ($showtime) {
                        $ticketRevenue = 0;
                        foreach ($booking->tickets as $ticket) {
                            $seatType = $ticket->seat->seatType;
                            $isWeekend = Carbon::parse($showtime->show_date)->isWeekend();
                            $basePrice = $isWeekend ? $seatType->promotion_price : $seatType->price;
                            $isEarlyStatus = $showtime->status === Showtime::STATUS_EARLY;
                            $price = $isEarlyStatus ? $basePrice * 1.5 : $basePrice;
                            $ticketRevenue += $price;
                        }
                        return [
                            'movie_id' => $showtime->movie_id,
                            'tickets_sold' => $booking->quantity,
                            'ticket_revenue' => $ticketRevenue
                        ];
                    });
                })
                ->groupBy('movie_id')
                ->map(function ($items) {
                    return [
                        'tickets_sold' => $items->sum('tickets_sold'),
                        'ticket_revenue' => $items->sum('ticket_revenue')
                    ];
                });

            $results = $allMovies->map(function ($movie) use ($ticketsSold) {
                return [
                    'movie_title' => $movie->title,
                    'tickets_sold' => $ticketsSold->get($movie->id, ['tickets_sold' => 0])['tickets_sold'],
                    'ticket_revenue' => $ticketsSold->get($movie->id, ['ticket_revenue' => 0])['ticket_revenue']
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

    // thống kê doanh số của dịch vụ
    public function serviceRevenue()
    {
        try {
            $services = Service::with(['bookingServices' => function ($query) {
                $query->where('deleted', 0);
            }])
                ->where('deleted', 0)
                ->get();

            $serviceData = $services->map(function ($service) {
                $totalQuantity = 0;
                $totalRevenue = 0;

                foreach ($service->bookingServices as $bookingService) {
                    $totalQuantity += $bookingService->quantity;
                    $totalRevenue += $bookingService->subtotal;
                }

                return [
                    'service' => $service->name,
                    'quantity_sold' => $totalQuantity,
                    'total_revenue' => $totalRevenue,
                ];
            })
                ->sortByDesc('quantity_sold')
                ->values()
                ->all();
            return ApiResponse(true, $serviceData, Response::HTTP_OK, 'Thành công');
        } catch (\Exception $e) {
            return ApiResponse(false, null, Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }
    }
}
