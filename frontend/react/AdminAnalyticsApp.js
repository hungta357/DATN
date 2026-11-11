/**
 * REACT 18 ADMIN ANALYTICS DASHBOARD
 * Kết nối trực tiếp với PHP Backend API (?route=api/admin/stats & ?route=api/admin/checkin)
 */
(function () {
    if (typeof React === 'undefined' || typeof ReactDOM === 'undefined') {
        return;
    }
    const { useState, useEffect, useMemo } = React;
    const h = React.createElement;

    function formatVND(val) {
        return Number(val || 0).toLocaleString('vi-VN') + 'đ';
    }

    function AdminAnalyticsApp({ initialData }) {
        const [data, setData] = useState(initialData || {});
        const [loading, setLoading] = useState(false);
        const [subTab, setSubTab] = useState('all'); // 'all' | 'movies' | 'rooms_payments' | 'customers'
        const [movieSort, setMovieSort] = useState('revenue'); // 'revenue' | 'viewers' | 'occupancy'
        const [movieSearch, setMovieSearch] = useState('');
        const [qrInput, setQrInput] = useState('');
        const [qrResult, setQrResult] = useState(null);
        const [qrLoading, setQrLoading] = useState(false);

        const summary = data.summary || {};
        const dailyRevenue = data.daily_revenue || [];
        const movieStats = data.movie_stats || [];
        const roomStats = data.room_stats || [];
        const paymentStats = data.payment_stats || [];
        const concessionStats = data.concession_stats || [];
        const topCustomers = data.top_customers || [];

        const refreshStats = () => {
            setLoading(true);
            fetch('?route=api/admin/stats')
                .then(res => res.json())
                .then(json => {
                    if (json && json.success && json.data) {
                        setData(json.data);
                    }
                })
                .catch(() => {})
                .finally(() => setLoading(false));
        };

        const handleQuickCheckIn = (e) => {
            e.preventDefault();
            if (!qrInput.trim()) return;
            setQrLoading(true);
            setQrResult(null);

            fetch('?route=api/admin/checkin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ticket_code: qrInput.trim() })
            })
                .then(res => res.json())
                .then(json => {
                    setQrResult(json);
                    if (json.success) {
                        setQrInput('');
                        refreshStats();
                    }
                })
                .catch(() => {
                    setQrResult({ success: false, message: 'Lỗi kết nối máy chủ.' });
                })
                .finally(() => setQrLoading(false));
        };

        const filteredMovies = useMemo(() => {
            let list = [...movieStats];
            if (movieSearch.trim()) {
                const kw = movieSearch.toLowerCase();
                list = list.filter(m =>
                    (m.title && m.title.toLowerCase().includes(kw)) ||
                    (m.title_vi && m.title_vi.toLowerCase().includes(kw))
                );
            }
            list.sort((a, b) => {
                if (movieSort === 'viewers') return Number(b.tickets_sold) - Number(a.tickets_sold);
                if (movieSort === 'occupancy') return Number(b.occupancy_rate) - Number(a.occupancy_rate);
                return Number(b.total_revenue) - Number(a.total_revenue);
            });
            return list;
        }, [movieStats, movieSort, movieSearch]);

        const maxDailyRev = useMemo(() => {
            if (!dailyRevenue.length) return 1;
            return Math.max(...dailyRevenue.map(d => Number(d.total_revenue) || 0), 1);
        }, [dailyRevenue]);

        const ticketShare = summary.total_revenue > 0
            ? Math.round((summary.ticket_revenue / (summary.ticket_revenue + summary.concession_revenue || 1)) * 100)
            : 0;
        const concessionShare = summary.total_revenue > 0 ? (100 - ticketShare) : 0;

        return h('div', { className: 'react-admin-analytics' },
            // Thanh điều khiển React Analytics
            h('div', { className: 'card border-0 shadow-sm bg-white mb-4 p-3 rounded-3' },
                h('div', { className: 'd-flex flex-wrap justify-content-between align-items-center gap-2' },
                    h('div', { className: 'd-flex align-items-center gap-2 flex-wrap' },
                        h('span', { className: 'badge bg-primary px-3 py-2' },
                            h('i', { className: 'bi bi-cpu-fill me-1' }), 'React 18 Live Analytics'
                        ),
                        h('div', { className: 'btn-group btn-group-sm' },
                            h('button', {
                                type: 'button',
                                className: 'btn ' + (subTab === 'all' ? 'btn-dark fw-bold' : 'btn-outline-secondary'),
                                onClick: () => setSubTab('all')
                            }, 'Tất cả thống kê'),
                            h('button', {
                                type: 'button',
                                className: 'btn ' + (subTab === 'movies' ? 'btn-dark fw-bold' : 'btn-outline-secondary'),
                                onClick: () => setSubTab('movies')
                            }, 'Khách xem & Doanh thu theo Phim'),
                            h('button', {
                                type: 'button',
                                className: 'btn ' + (subTab === 'rooms_payments' ? 'btn-dark fw-bold' : 'btn-outline-secondary'),
                                onClick: () => setSubTab('rooms_payments')
                            }, 'Phòng chiếu, Bắp nước & Thanh toán'),
                            h('button', {
                                type: 'button',
                                className: 'btn ' + (subTab === 'customers' ? 'btn-dark fw-bold' : 'btn-outline-secondary'),
                                onClick: () => setSubTab('customers')
                            }, 'Top Khách hàng & Soát vé QR')
                        )
                    ),
                    h('div', { className: 'd-flex align-items-center gap-2' },
                        h('span', { className: 'text-muted small' }, 'Cập nhật lúc: ', h('strong', null, data.generated_at || 'Vừa xong')),
                        h('button', {
                            type: 'button',
                            className: 'btn btn-sm btn-outline-primary fw-semibold',
                            onClick: refreshStats,
                            disabled: loading
                        },
                            h('i', { className: 'bi bi-arrow-clockwise me-1' }),
                            loading ? 'Đang tải...' : 'Làm mới số liệu'
                        )
                    )
                )
            ),

            // HÀNG 1: 4 THẺ KPI CHÍNH (DOANH THU, LƯỢNG KHÁCH XEM, TỶ LỆ LẤP ĐẦY, BẮP NƯỚC)
            h('div', { className: 'row g-3 mb-4' },
                // KPI 1: Tổng thu nhập
                h('div', { className: 'col-md-6 col-xl-3' },
                    h('div', { className: 'card border-0 shadow-sm h-100 p-3 bg-white border-start border-4 border-success' },
                        h('div', { className: 'd-flex justify-content-between align-items-start' },
                            h('div', null,
                                h('div', { className: 'text-muted small fw-semibold text-uppercase' }, 'Tổng Thu Nhập Thực Tế'),
                                h('h3', { className: 'fw-bold text-success my-1' }, formatVND(summary.total_revenue)),
                                h('div', { className: 'small text-secondary' },
                                    'Vé: ', h('strong', null, formatVND(summary.ticket_revenue)),
                                    ' | Bắp nước: ', h('strong', null, formatVND(summary.concession_revenue))
                                )
                            ),
                            h('div', { className: 'rounded-3 bg-success-subtle text-success p-2 fs-4' },
                                h('i', { className: 'bi bi-cash-stack' })
                            )
                        ),
                        summary.total_discount > 0 && h('div', { className: 'mt-2 pt-2 border-top small text-danger' },
                            'Đã giảm giá khuyến mãi: -' + formatVND(summary.total_discount)
                        )
                    )
                ),

                // KPI 2: Tổng lượng khách xem phim & Soát vé QR
                h('div', { className: 'col-md-6 col-xl-3' },
                    h('div', { className: 'card border-0 shadow-sm h-100 p-3 bg-white border-start border-4 border-primary' },
                        h('div', { className: 'd-flex justify-content-between align-items-start' },
                            h('div', null,
                                h('div', { className: 'text-muted small fw-semibold text-uppercase' }, 'Tổng Lượng Khách Xem'),
                                h('h3', { className: 'fw-bold text-primary my-1' }, Number(summary.total_viewers || 0).toLocaleString('vi-VN') + ' khách'),
                                h('div', { className: 'small text-secondary' },
                                    'Đã soát vé QR vào rạp: ',
                                    h('strong', { className: 'text-success' }, summary.checked_in_tickets || 0),
                                    ' / ', summary.total_viewers || 0, ' vé'
                                )
                            ),
                            h('div', { className: 'rounded-3 bg-primary-subtle text-primary p-2 fs-4' },
                                h('i', { className: 'bi bi-people-fill' })
                            )
                        ),
                        h('div', { className: 'mt-2 pt-2 border-top small text-muted' },
                            'Từ ', h('strong', null, summary.confirmed_bookings || 0), ' đơn đặt vé thành công'
                        )
                    )
                ),

                // KPI 3: Tỷ lệ lấp đầy ghế
                h('div', { className: 'col-md-6 col-xl-3' },
                    h('div', { className: 'card border-0 shadow-sm h-100 p-3 bg-white border-start border-4 border-warning' },
                        h('div', { className: 'd-flex justify-content-between align-items-start' },
                            h('div', { className: 'w-100 me-2' },
                                h('div', { className: 'text-muted small fw-semibold text-uppercase' }, 'Tỷ Lệ Lấp Đầy Phòng Chiếu'),
                                h('h3', { className: 'fw-bold text-warning my-1' }, (summary.occupancy_rate || 0) + '%'),
                                h('div', { className: 'progress my-2', style: { height: '7px' } },
                                    h('div', {
                                        className: 'progress-bar bg-warning',
                                        style: { width: Math.min(100, summary.occupancy_rate || 0) + '%' }
                                    })
                                ),
                                h('div', { className: 'small text-secondary' },
                                    'Đã đặt: ', h('strong', null, summary.booked_seats || 0),
                                    ' / ', summary.total_show_seats || 0, ' ghế mở bán'
                                )
                            ),
                            h('div', { className: 'rounded-3 bg-warning-subtle text-warning p-2 fs-4' },
                                h('i', { className: 'bi bi-grid-3x3-gap-fill' })
                            )
                        )
                    )
                ),

                // KPI 4: Cơ cấu nguồn thu & Ví thành viên
                h('div', { className: 'col-md-6 col-xl-3' },
                    h('div', { className: 'card border-0 shadow-sm h-100 p-3 bg-white border-start border-4 border-info' },
                        h('div', { className: 'd-flex justify-content-between align-items-start' },
                            h('div', null,
                                h('div', { className: 'text-muted small fw-semibold text-uppercase' }, 'Thành Viên & Ví Nội Bộ'),
                                h('h3', { className: 'fw-bold text-info my-1' }, (summary.total_users || 0) + ' tài khoản'),
                                h('div', { className: 'small text-secondary' },
                                    'Tổng số dư Ví: ', h('strong', { className: 'text-dark' }, formatVND(summary.total_wallet_balance))
                                )
                            ),
                            h('div', { className: 'rounded-3 bg-info-subtle text-info p-2 fs-4' },
                                h('i', { className: 'bi bi-wallet2' })
                            )
                        ),
                        h('div', { className: 'mt-2 pt-2 border-top small text-muted' },
                            'Tỷ trọng thu: Vé ', h('strong', null, ticketShare + '%'), ' • Bắp nước ', h('strong', null, concessionShare + '%')
                        )
                    )
                )
            ),

            // BIỂU ĐỒ DOANH THU THEO NGÀY & CƠ CẤU THANH TOÁN
            (subTab === 'all' || subTab === 'rooms_payments') && h('div', { className: 'row g-4 mb-4' },
                // Biểu đồ cột Doanh thu & Lượng khách theo ngày
                h('div', { className: 'col-lg-8' },
                    h('div', { className: 'card border-0 shadow-sm bg-white h-100' },
                        h('div', { className: 'card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3' },
                            h('span', null, h('i', { className: 'bi bi-bar-chart-line-fill text-primary me-2' }), 'Biểu Đồ Thu Nhập & Lượng Khách Xem Theo Ngày'),
                            h('span', { className: 'badge bg-light text-dark border' }, dailyRevenue.length + ' ngày có giao dịch')
                        ),
                        h('div', { className: 'card-body' },
                            dailyRevenue.length === 0
                                ? h('div', { className: 'text-center text-muted py-5' }, 'Chưa có dữ liệu doanh thu theo ngày.')
                                : h('div', null,
                                    h('div', {
                                        className: 'd-flex align-items-end gap-3 pt-4 pb-2 px-2 border-bottom',
                                        style: { minHeight: '220px', overflowX: 'auto' }
                                    },
                                        dailyRevenue.map((item, idx) => {
                                            const rev = Number(item.total_revenue || 0);
                                            const heightPct = Math.max(12, Math.round((rev / maxDailyRev) * 150));
                                            return h('div', {
                                                key: idx,
                                                className: 'text-center flex-fill',
                                                style: { minWidth: '75px' }
                                            },
                                                h('div', { className: 'small fw-bold text-success mb-1', style: { fontSize: '11px' } },
                                                    formatVND(rev)
                                                ),
                                                h('div', { className: 'badge bg-primary-subtle text-primary mb-1', style: { fontSize: '10px' } },
                                                    item.viewers_count + ' khách'
                                                ),
                                                h('div', {
                                                    className: 'mx-auto rounded-top',
                                                    title: `Ngày ${item.report_date}: ${formatVND(rev)} (${item.viewers_count} khách xem)`,
                                                    style: {
                                                        width: '38px',
                                                        height: heightPct + 'px',
                                                        background: 'linear-gradient(180deg, #0284c7 0%, #03599d 100%)',
                                                        transition: 'height 0.3s ease'
                                                    }
                                                }),
                                                h('div', { className: 'small text-muted mt-2 fw-semibold', style: { fontSize: '11px' } },
                                                    item.report_date
                                                )
                                            );
                                        })
                                    ),
                                    h('div', { className: 'table-responsive mt-3' },
                                        h('table', { className: 'table table-sm table-bordered small mb-0 align-middle' },
                                            h('thead', { className: 'table-light' },
                                                h('tr', null,
                                                    h('th', null, 'Ngày'),
                                                    h('th', null, 'Số đơn'),
                                                    h('th', null, 'Số khách xem (Vé)'),
                                                    h('th', null, 'Tiền vé'),
                                                    h('th', null, 'Tiền bắp nước'),
                                                    h('th', null, 'Tổng thu nhập')
                                                )
                                            ),
                                            h('tbody', null,
                                                dailyRevenue.map((d, i) =>
                                                    h('tr', { key: i },
                                                        h('td', { className: 'fw-semibold' }, d.report_date),
                                                        h('td', null, d.bookings_count + ' đơn'),
                                                        h('td', null, h('span', { className: 'badge bg-primary' }, d.viewers_count + ' khách')),
                                                        h('td', null, formatVND(d.ticket_revenue)),
                                                        h('td', null, formatVND(d.concession_revenue)),
                                                        h('td', { className: 'fw-bold text-success' }, formatVND(d.total_revenue))
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                        )
                    )
                ),

                // Cột phải: Thống kê phương thức thanh toán & Soát vé QR nhanh
                h('div', { className: 'col-lg-4' },
                    h('div', { className: 'card border-0 shadow-sm bg-white mb-3' },
                        h('div', { className: 'card-header bg-white fw-bold py-3' },
                            h('i', { className: 'bi bi-credit-card-2-front-fill text-info me-2' }),
                            'Doanh Thu Theo Phương Thức Thanh Toán'
                        ),
                        h('div', { className: 'card-body p-0' },
                            h('table', { className: 'table table-sm mb-0 small align-middle' },
                                h('thead', { className: 'table-light' },
                                    h('tr', null,
                                        h('th', { className: 'ps-3' }, 'Kênh TT'),
                                        h('th', null, 'Giao dịch'),
                                        h('th', { className: 'text-end pe-3' }, 'Tổng thu')
                                    )
                                ),
                                h('tbody', null,
                                    paymentStats.map((pm, idx) =>
                                        h('tr', { key: idx },
                                            h('td', { className: 'ps-3 fw-semibold' },
                                                pm.method_name, ' ',
                                                h('code', { className: 'small' }, '(' + pm.method_code + ')')
                                            ),
                                            h('td', null, pm.tx_count + ' lượt'),
                                            h('td', { className: 'text-end pe-3 fw-bold text-danger' }, formatVND(pm.total_amount))
                                        )
                                    )
                                )
                            )
                        )
                    ),

                    // Hộp Soát vé QR Realtime bằng React
                    h('div', { className: 'card border-0 shadow-sm bg-white' },
                        h('div', { className: 'card-header bg-success text-white fw-bold py-2' },
                            h('i', { className: 'bi bi-qr-code-scan me-2' }),
                            'Soát Vé QR Nhanh (Realtime Check-in)'
                        ),
                        h('div', { className: 'card-body' },
                            h('form', { onSubmit: handleQuickCheckIn },
                                h('div', { className: 'input-group input-group-sm mb-2' },
                                    h('input', {
                                        type: 'text',
                                        className: 'form-control',
                                        placeholder: 'Nhập mã vé (VD: TK-BK...)',
                                        value: qrInput,
                                        onChange: e => setQrInput(e.target.value)
                                    }),
                                    h('button', {
                                        type: 'submit',
                                        className: 'btn btn-success fw-bold',
                                        disabled: qrLoading
                                    }, qrLoading ? 'Đang kiểm tra...' : 'Check-in')
                                )
                            ),
                            qrResult && h('div', {
                                className: 'alert small mb-0 py-2 ' + (qrResult.success ? 'alert-success' : 'alert-danger')
                            }, qrResult.message)
                        )
                    )
                )
            ),

            // BẢNG THỐNG KÊ CHI TIẾT THEO TỪNG BỘ PHIM (KHÁCH XEM, TỶ LỆ LẤP ĐẦY, DOANH THU)
            (subTab === 'all' || subTab === 'movies') && h('div', { className: 'card border-0 shadow-sm bg-white mb-4' },
                h('div', { className: 'card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2' },
                    h('div', { className: 'fw-bold' },
                        h('i', { className: 'bi bi-film text-danger me-2' }),
                        'Thống Kê Chi Tiết Lượng Khách Xem & Thu Nhập Theo Từng Bộ Phim'
                    ),
                    h('div', { className: 'd-flex flex-wrap gap-2 align-items-center' },
                        h('input', {
                            type: 'text',
                            className: 'form-control form-control-sm',
                            style: { width: '210px' },
                            placeholder: 'Tìm nhanh tên phim...',
                            value: movieSearch,
                            onChange: e => setMovieSearch(e.target.value)
                        }),
                        h('select', {
                            className: 'form-select form-select-sm',
                            style: { width: '200px' },
                            value: movieSort,
                            onChange: e => setMovieSort(e.target.value)
                        },
                            h('option', { value: 'revenue' }, 'Sắp xếp: Doanh thu cao nhất'),
                            h('option', { value: 'viewers' }, 'Sắp xếp: Đông khách xem nhất'),
                            h('option', { value: 'occupancy' }, 'Sắp xếp: Tỷ lệ lấp đầy ghế')
                        )
                    )
                ),
                h('div', { className: 'table-responsive' },
                    h('table', { className: 'table table-hover align-middle small mb-0' },
                        h('thead', { className: 'table-light' },
                            h('tr', null,
                                h('th', { className: 'ps-3' }, 'Bộ phim'),
                                h('th', null, 'Trạng thái'),
                                h('th', { className: 'text-center' }, 'Số suất chiếu'),
                                h('th', { className: 'text-center' }, 'Lượng khách xem (Vé bán)'),
                                h('th', { className: 'text-center' }, 'Khách đã Check-in QR'),
                                h('th', { style: { minWidth: '150px' } }, 'Tỷ lệ lấp đầy ghế'),
                                h('th', { className: 'text-end' }, 'Doanh thu vé'),
                                h('th', { className: 'text-end pe-3' }, 'Tổng thu nhập đơn')
                            )
                        ),
                        h('tbody', null,
                            filteredMovies.map(m =>
                                h('tr', { key: m.id },
                                    h('td', { className: 'ps-3' },
                                        h('div', { className: 'd-flex align-items-center gap-2' },
                                            h('img', {
                                                src: m.poster_path || 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=200',
                                                style: { width: '38px', height: '54px', objectFit: 'cover', borderRadius: '6px' }
                                            }),
                                            h('div', null,
                                                h('div', { className: 'fw-bold text-dark' }, m.title),
                                                h('div', { className: 'text-muted', style: { fontSize: '12px' } }, m.title_vi || '')
                                            )
                                        )
                                    ),
                                    h('td', null,
                                        h('span', {
                                            className: 'badge ' + (m.status === 'now_showing' ? 'bg-success' : 'bg-secondary')
                                        }, m.status)
                                    ),
                                    h('td', { className: 'text-center fw-semibold' }, m.show_count + ' suất'),
                                    h('td', { className: 'text-center' },
                                        h('span', { className: 'badge bg-primary fs-6' }, m.tickets_sold + ' khách')
                                    ),
                                    h('td', { className: 'text-center' },
                                        h('span', { className: 'badge bg-success-subtle text-success border border-success-subtle' },
                                            m.checked_in_count + ' / ' + m.tickets_sold + ' vé'
                                        )
                                    ),
                                    h('td', null,
                                        h('div', { className: 'd-flex justify-content-between small mb-1' },
                                            h('span', { className: 'fw-semibold' }, m.occupancy_rate + '%'),
                                            h('span', { className: 'text-muted' }, m.tickets_sold + '/' + m.total_capacity + ' ghế')
                                        ),
                                        h('div', { className: 'progress', style: { height: '6px' } },
                                            h('div', {
                                                className: 'progress-bar bg-info',
                                                style: { width: Math.min(100, m.occupancy_rate) + '%' }
                                            })
                                        )
                                    ),
                                    h('td', { className: 'text-end fw-semibold' }, formatVND(m.ticket_revenue)),
                                    h('td', { className: 'text-end pe-3 fw-bold text-danger fs-6' }, formatVND(m.total_revenue))
                                )
                            )
                        )
                    )
                )
            ),

            // THỐNG KÊ THEO PHÒNG CHIẾU & TOP COMBO BẮP NƯỚC BÁN CHẠY
            (subTab === 'all' || subTab === 'rooms_payments') && h('div', { className: 'row g-4 mb-4' },
                h('div', { className: 'col-md-6' },
                    h('div', { className: 'card border-0 shadow-sm bg-white h-100' },
                        h('div', { className: 'card-header bg-white fw-bold py-3' },
                            h('i', { className: 'bi bi-building-check text-primary me-2' }),
                            'Hiệu Suất Khai Thác Theo Từng Phòng Chiếu'
                        ),
                        h('div', { className: 'table-responsive' },
                            h('table', { className: 'table table-sm align-middle small mb-0' },
                                h('thead', { className: 'table-light' },
                                    h('tr', null,
                                        h('th', { className: 'ps-3' }, 'Phòng chiếu'),
                                        h('th', null, 'Suất chiếu'),
                                        h('th', null, 'Khách xem'),
                                        h('th', null, 'Lấp đầy'),
                                        h('th', { className: 'text-end pe-3' }, 'Doanh thu')
                                    )
                                ),
                                h('tbody', null,
                                    roomStats.map(r =>
                                        h('tr', { key: r.id },
                                            h('td', { className: 'ps-3 fw-bold' },
                                                r.name, ' ',
                                                h('span', { className: 'badge bg-info text-dark' }, String(r.room_type).toUpperCase())
                                            ),
                                            h('td', null, r.show_count + ' suất'),
                                            h('td', null, r.tickets_sold + ' khách'),
                                            h('td', null, h('span', { className: 'badge bg-warning text-dark' }, r.occupancy_rate + '%')),
                                            h('td', { className: 'text-end pe-3 fw-bold text-success' }, formatVND(r.revenue))
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),

                h('div', { className: 'col-md-6' },
                    h('div', { className: 'card border-0 shadow-sm bg-white h-100' },
                        h('div', { className: 'card-header bg-white fw-bold py-3' },
                            h('i', { className: 'bi bi-cup-straw text-warning me-2' }),
                            'Thống Kê Doanh Thu Combo Bắp Nước'
                        ),
                        h('div', { className: 'table-responsive' },
                            h('table', { className: 'table table-sm align-middle small mb-0' },
                                h('thead', { className: 'table-light' },
                                    h('tr', null,
                                        h('th', { className: 'ps-3' }, 'Tên Combo / Sản phẩm'),
                                        h('th', null, 'Đã bán'),
                                        h('th', null, 'Tồn kho'),
                                        h('th', { className: 'text-end pe-3' }, 'Doanh thu')
                                    )
                                ),
                                h('tbody', null,
                                    concessionStats.map(c =>
                                        h('tr', { key: c.id },
                                            h('td', { className: 'ps-3 fw-semibold' }, c.name),
                                            h('td', null, h('span', { className: 'badge bg-primary' }, c.sold_qty + ' phần')),
                                            h('td', null, c.stock),
                                            h('td', { className: 'text-end pe-3 fw-bold text-danger' }, formatVND(c.revenue))
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            ),

            // TOP KHÁCH HÀNG CHI TIÊU CAO NHẤT
            (subTab === 'all' || subTab === 'customers') && h('div', { className: 'card border-0 shadow-sm bg-white mb-4' },
                h('div', { className: 'card-header bg-white fw-bold py-3' },
                    h('i', { className: 'bi bi-award-fill text-warning me-2' }),
                    'Top Khách Hàng Đặt Vé & Chi Tiêu Nhiều Nhất'
                ),
                h('div', { className: 'table-responsive' },
                    h('table', { className: 'table table-sm align-middle small mb-0' },
                        h('thead', { className: 'table-light' },
                            h('tr', null,
                                h('th', { className: 'ps-3' }, 'Hạng'),
                                h('th', null, 'Khách hàng'),
                                h('th', null, 'Email / SĐT'),
                                h('th', { className: 'text-center' }, 'Số đơn thành công'),
                                h('th', { className: 'text-center' }, 'Số vé đã mua'),
                                h('th', { className: 'text-end' }, 'Số dư Ví'),
                                h('th', { className: 'text-end pe-3' }, 'Tổng chi tiêu')
                            )
                        ),
                        h('tbody', null,
                            topCustomers.map((u, idx) =>
                                h('tr', { key: u.id },
                                    h('td', { className: 'ps-3 fw-bold' }, '#' + (idx + 1)),
                                    h('td', { className: 'fw-semibold text-primary' }, u.name),
                                    h('td', null, u.email, u.phone ? ' • ' + u.phone : ''),
                                    h('td', { className: 'text-center' }, u.bookings_count + ' đơn'),
                                    h('td', { className: 'text-center' }, h('span', { className: 'badge bg-info text-dark' }, u.tickets_bought + ' vé')),
                                    h('td', { className: 'text-end' }, formatVND(u.wallet_balance)),
                                    h('td', { className: 'text-end pe-3 fw-bold text-success' }, formatVND(u.total_spent))
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('react-admin-analytics-root');
        if (container && window.__ADMIN_ANALYTICS_DATA__) {
            const root = ReactDOM.createRoot(container);
            root.render(h(AdminAnalyticsApp, { initialData: window.__ADMIN_ANALYTICS_DATA__ }));
        }
    });
})();

