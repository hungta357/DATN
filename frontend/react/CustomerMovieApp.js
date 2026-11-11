/**
 * REACT 18 CUSTOMER FRONTEND APP
 * 1. MovieExplorerApp (Trang chủ khám phá phim, lọc thể loại, xem Trailer & Đặt vé nhanh)
 * 2. SeatBookingEnhancer (Kiểm tra mã giảm giá Coupon realtime kết nối PHP API)
 */
(function () {
    if (typeof React === 'undefined' || typeof ReactDOM === 'undefined') {
        return;
    }
    const { useState, useMemo } = React;
    const h = React.createElement;

    function getAgeBadgeClass(ageRating) {
        const age = String(ageRating || 'P').toUpperCase();
        if (age.includes('13')) return 'age-T13';
        if (age.includes('16')) return 'age-T16';
        if (age.includes('18')) return 'age-T18';
        return 'age-P';
    }

    function formatDateDMY(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }

    function formatTimeHM(dateTimeStr) {
        if (!dateTimeStr) return '';
        const parts = dateTimeStr.split(' ');
        if (parts[1]) return parts[1].substring(0, 5);
        return dateTimeStr;
    }

    function formatDayMonth(dateTimeStr) {
        if (!dateTimeStr) return '';
        const d = new Date(dateTimeStr.replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        return String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0');
    }

    function MovieExplorerApp({ initialData }) {
        const allMovies = initialData.movies || [];
        const genres = initialData.genres || [];
        const showsByMovie = initialData.showsByMovie || {};
        const cinemaInfo = initialData.cinemaInfo || {};

        const [activeTab, setActiveTab] = useState(initialData.currentStatus || 'now_showing');
        const [searchQuery, setSearchQuery] = useState(initialData.keyword || '');
        const [selectedGenre, setSelectedGenre] = useState('');
        const [selectedAge, setSelectedAge] = useState('');
        const [sortBy, setSortBy] = useState('default'); // 'default' | 'imdb' | 'runtime'
        const [bookingModalMovie, setBookingModalMovie] = useState(null);
        const [trailerModalMovie, setTrailerModalMovie] = useState(null);

        const filteredMovies = useMemo(() => {
            let list = [...allMovies];

            if (activeTab !== 'all') {
                list = list.filter(m => m.status === activeTab);
            }

            if (searchQuery.trim()) {
                const kw = searchQuery.toLowerCase();
                list = list.filter(m =>
                    (m.title && m.title.toLowerCase().includes(kw)) ||
                    (m.title_vi && m.title_vi.toLowerCase().includes(kw)) ||
                    (m.director && m.director.toLowerCase().includes(kw))
                );
            }

            if (selectedGenre) {
                list = list.filter(m => m.genre_names && m.genre_names.toLowerCase().includes(selectedGenre.toLowerCase()));
            }

            if (selectedAge) {
                list = list.filter(m => String(m.age_rating || 'P').toUpperCase() === selectedAge);
            }

            if (sortBy === 'imdb') {
                list.sort((a, b) => Number(b.imdb_rating || 0) - Number(a.imdb_rating || 0));
            } else if (sortBy === 'runtime') {
                list.sort((a, b) => Number(b.runtime || 0) - Number(a.runtime || 0));
            }

            return list;
        }, [allMovies, activeTab, searchQuery, selectedGenre, selectedAge, sortBy]);

        return h('div', null,
            // 3 TAB LỚN PHONG CÁCH BETA CINEMAS (CHUYỂN TAB MƯỢT MÀ BẰNG REACT)
            h('div', { className: 'beta-movie-tabs' },
                h('a', {
                    href: '#coming_soon',
                    className: 'beta-movie-tab ' + (activeTab === 'coming_soon' ? 'active' : ''),
                    onClick: (e) => { e.preventDefault(); setActiveTab('coming_soon'); }
                }, 'PHIM SẮP CHIẾU'),
                h('a', {
                    href: '#now_showing',
                    className: 'beta-movie-tab ' + (activeTab === 'now_showing' ? 'active' : ''),
                    onClick: (e) => { e.preventDefault(); setActiveTab('now_showing'); }
                }, 'PHIM ĐANG CHIẾU'),
                h('a', {
                    href: '#all',
                    className: 'beta-movie-tab ' + (activeTab === 'all' ? 'active' : ''),
                    onClick: (e) => { e.preventDefault(); setActiveTab('all'); }
                }, 'TẤT CẢ PHIM TẠI RẠP')
            ),

            // THANH BỘ LỌC THÔNG MINH BẰNG REACT
            h('div', { className: 'card border-0 shadow-sm bg-white p-3 mb-4 rounded-4' },
                h('div', { className: 'row g-2 align-items-center' },
                    h('div', { className: 'col-md-4' },
                        h('div', { className: 'input-group input-group-sm' },
                            h('span', { className: 'input-group-text bg-light border-end-0' },
                                h('i', { className: 'bi bi-search text-primary' })
                            ),
                            h('input', {
                                type: 'text',
                                className: 'form-control border-start-0',
                                placeholder: 'Tìm nhanh tên phim, đạo diễn...',
                                value: searchQuery,
                                onChange: e => setSearchQuery(e.target.value)
                            }),
                            searchQuery && h('button', {
                                type: 'button',
                                className: 'btn btn-outline-secondary',
                                onClick: () => setSearchQuery('')
                            }, 'Xóa')
                        )
                    ),
                    h('div', { className: 'col-6 col-md-3' },
                        h('select', {
                            className: 'form-select form-select-sm',
                            value: selectedGenre,
                            onChange: e => setSelectedGenre(e.target.value)
                        },
                            h('option', { value: '' }, 'Tất cả thể loại'),
                            genres.map(g => h('option', { key: g.id, value: g.name }, g.name))
                        )
                    ),
                    h('div', { className: 'col-6 col-md-2' },
                        h('select', {
                            className: 'form-select form-select-sm',
                            value: selectedAge,
                            onChange: e => setSelectedAge(e.target.value)
                        },
                            h('option', { value: '' }, 'Mọi độ tuổi'),
                            h('option', { value: 'P' }, 'P - Phổ biến'),
                            h('option', { value: 'T13' }, 'T13 - Từ 13 tuổi'),
                            h('option', { value: 'T16' }, 'T16 - Từ 16 tuổi'),
                            h('option', { value: 'T18' }, 'T18 - Từ 18 tuổi')
                        )
                    ),
                    h('div', { className: 'col-md-3' },
                        h('select', {
                            className: 'form-select form-select-sm',
                            value: sortBy,
                            onChange: e => setSortBy(e.target.value)
                        },
                            h('option', { value: 'default' }, 'Sắp xếp: Mới nhất'),
                            h('option', { value: 'imdb' }, 'Sắp xếp: Điểm IMDb cao nhất'),
                            h('option', { value: 'runtime' }, 'Sắp xếp: Thời lượng phim')
                        )
                    )
                )
            ),

            // DANH SÁCH PHIM
            filteredMovies.length === 0
                ? h('div', { className: 'alert alert-info text-center py-4 rounded-4' },
                    'Không tìm thấy bộ phim nào phù hợp với bộ lọc hiện tại.'
                )
                : h('div', { className: 'row g-4' },
                    filteredMovies.map(movie => {
                        const age = String(movie.age_rating || 'P').toUpperCase();
                        const ageClass = getAgeBadgeClass(age);
                        return h('div', { key: movie.id, className: 'col-6 col-md-4 col-lg-3' },
                            h('div', { className: 'card beta-movie-card h-100' },
                                h('div', { className: 'beta-poster-wrap d-block mb-3 position-relative' },
                                    h('span', { className: 'beta-age-badge ' + ageClass }, age),
                                    movie.imdb_rating && h('span', {
                                        className: 'badge bg-dark bg-opacity-75 text-warning position-absolute',
                                        style: { top: '12px', right: '12px', zIndex: 2 }
                                    }, h('i', { className: 'bi bi-star-fill me-1' }), movie.imdb_rating),
                                    h('a', { href: '?route=movie/detail&id=' + movie.id },
                                        h('img', {
                                            src: movie.poster_path || 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600',
                                            alt: movie.title
                                        })
                                    )
                                ),
                                h('div', { className: 'd-flex flex-column flex-grow-1' },
                                    h('a', {
                                        href: '?route=movie/detail&id=' + movie.id,
                                        className: 'beta-movie-title mb-1',
                                        title: movie.title
                                    }, movie.title_vi || movie.title),
                                    h('div', { className: 'small text-muted mb-1' },
                                        h('strong', null, 'Thể loại: '), movie.genre_names || 'Điện ảnh'
                                    ),
                                    h('div', { className: 'small text-muted mb-3' },
                                        h('strong', null, 'Thời lượng: '), (movie.runtime || 120) + ' phút',
                                        movie.release_date ? ' | KC: ' + formatDateDMY(movie.release_date) : ''
                                    ),
                                    h('div', { className: 'mt-auto d-flex gap-2' },
                                        movie.trailer_url && h('button', {
                                            type: 'button',
                                            className: 'btn btn-outline-secondary btn-sm px-2',
                                            title: 'Xem Trailer',
                                            onClick: () => setTrailerModalMovie(movie)
                                        }, h('i', { className: 'bi bi-play-circle-fill text-danger fs-6' })),
                                        movie.status === 'now_showing'
                                            ? h('button', {
                                                type: 'button',
                                                className: 'btn btn-beta-buy flex-grow-1',
                                                onClick: () => setBookingModalMovie(movie)
                                            }, h('i', { className: 'bi bi-ticket-perforated-fill me-1' }), 'MUA VÉ')
                                            : h('a', {
                                                href: '?route=movie/detail&id=' + movie.id,
                                                className: 'btn btn-outline-primary flex-grow-1 oswald-font fw-bold'
                                            }, 'CHI TIẾT')
                                    )
                                )
                            )
                        );
                    })
                ),

            // MODAL CHỌN SUẤT CHIẾU NHANH BẰNG REACT
            bookingModalMovie && h('div', {
                className: 'modal fade show d-block',
                style: { backgroundColor: 'rgba(0,0,0,0.6)' },
                onClick: () => setBookingModalMovie(null)
            },
                h('div', {
                    className: 'modal-dialog modal-lg modal-dialog-centered',
                    onClick: e => e.stopPropagation()
                },
                    h('div', { className: 'modal-content border-0 shadow rounded-4 overflow-hidden' },
                        h('div', {
                            className: 'modal-header text-white',
                            style: { background: 'var(--beta-gradient)' }
                        },
                            h('h5', { className: 'modal-title oswald-font text-uppercase' },
                                h('i', { className: 'bi bi-film me-2' }),
                                'LỊCH CHIẾU - ', (bookingModalMovie.title_vi || bookingModalMovie.title)
                            ),
                            h('button', {
                                type: 'button',
                                className: 'btn-close btn-close-white',
                                onClick: () => setBookingModalMovie(null)
                            })
                        ),
                        h('div', { className: 'modal-body p-4' },
                            h('div', { className: 'd-flex align-items-center justify-content-between pb-3 mb-3 border-bottom' },
                                h('div', null,
                                    h('span', { className: 'oswald-font fs-5 fw-bold', style: { color: 'var(--beta-blue)' } },
                                        h('i', { className: 'bi bi-geo-alt-fill text-danger me-1' }),
                                        'RẠP ', String(cinemaInfo.name || 'CINENOW').toUpperCase()
                                    ),
                                    h('div', { className: 'small text-muted' }, cinemaInfo.address || '')
                                ),
                                h('a', {
                                    href: '?route=movie/detail&id=' + bookingModalMovie.id,
                                    className: 'btn btn-sm btn-outline-primary'
                                }, 'Xem chi tiết & Đánh giá phim')
                            ),
                            (showsByMovie[bookingModalMovie.id] && showsByMovie[bookingModalMovie.id].length > 0)
                                ? h('div', null,
                                    h('h6', { className: 'fw-bold text-uppercase small text-secondary mb-3' },
                                        'Chọn khung giờ chiếu bên dưới để vào sơ đồ chọn ghế:'
                                    ),
                                    h('div', { className: 'd-flex flex-wrap gap-3' },
                                        showsByMovie[bookingModalMovie.id].map(sh =>
                                            h('a', {
                                                key: sh.id,
                                                href: '?route=booking/seats&show_id=' + sh.id,
                                                className: 'beta-time-slot'
                                            },
                                                h('div', { className: 'slot-hour' }, formatTimeHM(sh.start_time)),
                                                h('div', { className: 'small text-primary fw-semibold' },
                                                    formatDayMonth(sh.start_time) + ' • ' + sh.room_name
                                                ),
                                                h('div', { className: 'slot-seats' }, (sh.available_seats || 0) + ' ghế trống')
                                            )
                                        )
                                    )
                                )
                                : h('div', { className: 'alert alert-warning mb-0' },
                                    'Hiện tại rạp chưa mở suất chiếu mới cho phim này. Vui lòng quay lại sau!'
                                )
                        )
                    )
                )
            ),

            // MODAL XEM TRAILER YOUTUBE BẰNG REACT
            trailerModalMovie && h('div', {
                className: 'modal fade show d-block',
                style: { backgroundColor: 'rgba(0,0,0,0.75)' },
                onClick: () => setTrailerModalMovie(null)
            },
                h('div', {
                    className: 'modal-dialog modal-lg modal-dialog-centered',
                    onClick: e => e.stopPropagation()
                },
                    h('div', { className: 'modal-content bg-dark text-white border-0 rounded-4 overflow-hidden' },
                        h('div', { className: 'modal-header border-secondary' },
                            h('h5', { className: 'modal-title oswald-font text-uppercase' },
                                h('i', { className: 'bi bi-play-btn-fill text-danger me-2' }),
                                'TRAILER - ', (trailerModalMovie.title_vi || trailerModalMovie.title)
                            ),
                            h('button', {
                                type: 'button',
                                className: 'btn-close btn-close-white',
                                onClick: () => setTrailerModalMovie(null)
                            })
                        ),
                        h('div', { className: 'modal-body p-0' },
                            h('div', { className: 'ratio ratio-16x9' },
                                h('iframe', {
                                    src: trailerModalMovie.trailer_url,
                                    title: trailerModalMovie.title,
                                    allowFullScreen: true
                                })
                            )
                        )
                    )
                )
            )
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        const homeContainer = document.getElementById('react-movie-explorer-root');
        if (homeContainer && window.__HOME_INITIAL_DATA__) {
            const root = ReactDOM.createRoot(homeContainer);
            root.render(h(MovieExplorerApp, { initialData: window.__HOME_INITIAL_DATA__ }));
        }
    });
})();

