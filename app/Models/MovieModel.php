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

    public function syncGenres(int $movieId, array $genreIds): void
    {
        $stmtDel = $this->db->prepare("DELETE FROM movie_genres WHERE movie_id = :mid");
        $stmtDel->execute(['mid' => $movieId]);

        $stmtIns = $this->db->prepare("INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES (:mid, :gid)");
        foreach ($genreIds as $gid) {
            $stmtIns->execute(['mid' => $movieId, 'gid' => (int) $gid]);
        }
    }
}

