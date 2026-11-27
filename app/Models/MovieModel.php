<?php

namespace App\Models;

use App\Core\BaseModel;

/**
 * NHÁNH 2: MOVIES / GENRES
 * Bảng: movies, genres, movie_genres
 * Lưu ý nghiệp vụ #4: Không xóa cứng movies đã phát sinh giao dịch; ưu tiên cập nhật status ('hidden' / 'ended').
 */
class MovieModel extends BaseModel
{
    protected string $table = 'movies';

    public function getMovies(?string $status = null, ?string $keyword = null, ?int $genreId = null): array
    {
        $where = [];
        $params = [];

        if ($status) {
            $where[] = "m.status = :status";
            $params['status'] = $status;
        } else {
            $where[] = "m.status != 'hidden'";
        }

        if ($keyword) {
            $where[] = "(m.title LIKE :kw1 OR m.title_vi LIKE :kw2)";
            $params['kw1'] = "%{$keyword}%";
            $params['kw2'] = "%{$keyword}%";
        }

        if ($genreId !== null && $genreId > 0) {
            $where[] = 'EXISTS (SELECT 1 FROM movie_genres filter_mg WHERE filter_mg.movie_id = m.id AND filter_mg.genre_id = :genre_id)';
            $params['genre_id'] = $genreId;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT m.*, GROUP_CONCAT(g.name SEPARATOR ', ') AS genre_names
                FROM movies m
                LEFT JOIN movie_genres mg ON mg.movie_id = m.id
                LEFT JOIN genres g ON g.id = mg.genre_id
                {$whereSql}
                GROUP BY m.id
                ORDER BY m.release_date DESC, m.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT m.*, GROUP_CONCAT(g.name SEPARATOR ', ') AS genre_names
                FROM movies m
                LEFT JOIN movie_genres mg ON mg.movie_id = m.id
                LEFT JOIN genres g ON g.id = mg.genre_id
                WHERE m.id = :id
                GROUP BY m.id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $movie = $stmt->fetch();
        return $movie ?: null;
    }

    public function getAllGenres(): array
    {
        return $this->db->query("SELECT * FROM genres ORDER BY name ASC")->fetchAll();
    }

    public function getAllMoviesWithGenres(): array
    {
        $sql = "SELECT m.*,
                       GROUP_CONCAT(g.name SEPARATOR ', ') AS genre_names,
                       GROUP_CONCAT(g.id SEPARATOR ',') AS genre_ids_csv
                FROM movies m
                LEFT JOIN movie_genres mg ON mg.movie_id = m.id
                LEFT JOIN genres g ON g.id = mg.genre_id
                GROUP BY m.id
                ORDER BY m.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function syncGenres(int $movieId, array $genreIds): void
    {
        $stmtDel = $this->db->prepare("DELETE FROM movie_genres WHERE movie_id = :mid");
        $stmtDel->execute(['mid' => $movieId]);

        $stmtIns = $this->db->prepare("INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES (:mid, :gid)");
        foreach ($genreIds as $gid) {
            $stmtIns->execute(['mid' => $movieId, 'gid' => (int) $gid]);
        }
    }

    public function deleteOrHideMovie(int $movieId): string
    {
        $stmtBookings = $this->db->prepare("
            SELECT COUNT(*)
            FROM bookings b
            INNER JOIN shows sh ON sh.id = b.show_id
            WHERE sh.movie_id = :mid AND b.status IN ('confirmed', 'pending')
        ");
        $stmtBookings->execute(['mid' => $movieId]);
        $hasActiveBookings = (int) $stmtBookings->fetchColumn() > 0;

        if ($hasActiveBookings) {
            $this->update($movieId, ['status' => 'hidden']);
            return 'hidden';
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM bookings WHERE show_id IN (SELECT id FROM shows WHERE movie_id = :mid)")->execute(['mid' => $movieId]);
            $this->db->prepare("DELETE FROM show_seats WHERE show_id IN (SELECT id FROM shows WHERE movie_id = :mid)")->execute(['mid' => $movieId]);
            $this->db->prepare("DELETE FROM shows WHERE movie_id = :mid")->execute(['mid' => $movieId]);
            $this->db->prepare("DELETE FROM movie_genres WHERE movie_id = :mid")->execute(['mid' => $movieId]);
            $this->db->prepare("DELETE FROM reviews WHERE movie_id = :mid")->execute(['mid' => $movieId]);
            $this->db->prepare("DELETE FROM wishlists WHERE movie_id = :mid")->execute(['mid' => $movieId]);
            $this->db->prepare("DELETE FROM movies WHERE id = :mid")->execute(['mid' => $movieId]);
            $this->db->commit();
            return 'deleted';
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->update($movieId, ['status' => 'hidden']);
            return 'hidden';
        }
    }

    /**
     * Tự chuyển phim coming_soon → now_showing khi đã đến ngày phát hành (release_date).
     * Trả về danh sách id phim vừa được chuyển.
     */
    public function promoteMoviesDueForRelease(): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT id FROM movies
            WHERE status = 'coming_soon'
              AND release_date IS NOT NULL
              AND release_date <= :today
        ");
        $stmt->execute(['today' => $today]);
        $ids = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $upd = $this->db->prepare("UPDATE movies SET status = 'now_showing', updated_at = NOW() WHERE id IN ({$placeholders})");
        $upd->execute($ids);

        return $ids;
    }

    /**
     * Lấy phim đang chiếu + phim có suất chiếu mở bán trong khoảng ngày (phục vụ xuất chiếu sớm).
     * @param array $movieIdsFromShows danh sách movie_id đã có suất trong khoảng xem
     */
    public function getMoviesForSchedule(array $movieIdsFromShows = []): array
    {
        $movieIdsFromShows = array_values(array_unique(array_filter(array_map('intval', $movieIdsFromShows))));

        if (empty($movieIdsFromShows)) {
            return $this->getMovies('now_showing');
        }

        $placeholders = implode(',', array_fill(0, count($movieIdsFromShows), '?'));
        $sql = "SELECT m.*, GROUP_CONCAT(g.name SEPARATOR ', ') AS genre_names
                FROM movies m
                LEFT JOIN movie_genres mg ON mg.movie_id = m.id
                LEFT JOIN genres g ON g.id = mg.genre_id
                WHERE m.status = 'now_showing'
                   OR (m.status = 'coming_soon' AND m.id IN ({$placeholders}))
                GROUP BY m.id
                ORDER BY m.release_date DESC, m.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($movieIdsFromShows);
        return $stmt->fetchAll();
    }
}