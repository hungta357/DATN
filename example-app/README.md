# example-app

Source Laravel 12, MySQL và Laravel Sanctum Personal Access Tokens.

## Chạy ứng dụng

1. Tạo database MySQL tên `example_app`, sau đó cập nhật thông tin kết nối trong `.env`.
2. Cài dependency PHP, tạo application key và tạo các bảng:

   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   ```

3. Khởi chạy ứng dụng:

   ```bash
   php artisan serve
   ```

   Đổi cổng nếu cần, ví dụ `php artisan serve --port=8001`.

`SESSION_DRIVER=file` được đặt sẵn nên không cần bảng sessions cho session web. `php artisan migrate` tạo bảng người dùng, Sanctum `personal_access_tokens` và danh mục.

## API Sanctum

API được khai báo tại `routes/api.php`, tự động có tiền tố `/api`. Đăng nhập nhận Bearer token; các endpoint còn lại yêu cầu gửi token trong header `Authorization: Bearer <token>`.

| Method | Endpoint | Mô tả | Auth |
|---|---|---|---|
| POST | `/api/login` | Đăng nhập (`email`, `password`, tùy chọn `device_name`) | Không |
| POST | `/api/logout` | Thu hồi token hiện tại | Có |
| GET | `/api/user` | Thông tin người dùng hiện tại | Có |
| GET | `/api/categories` | Danh sách danh mục (phân trang) | Có |
| POST | `/api/categories` | Tạo danh mục (`name`, tùy chọn `slug`, `description`) | Có |
| GET | `/api/categories/{id}` | Xem danh mục | Có |
| PUT/PATCH | `/api/categories/{id}` | Cập nhật danh mục | Có |
| DELETE | `/api/categories/{id}` | Xóa danh mục | Có |

Thông tin đăng nhập mẫu sau khi seed: `test@example.com` / `password`. Chỉ dùng tài khoản này cho môi trường phát triển.

### Thử bằng Postman

Import `postman/laravel-api.postman_collection.json`, chạy `php artisan db:seed` một lần, rồi chạy request **Login** trước. Collection tự lưu token và ID danh mục sau khi tạo; các request tiếp theo dùng token Bearer đó. Địa chỉ mặc định là `http://127.0.0.1:8000` và có thể đổi biến `baseUrl`.

## Migration, seeder và factory

- Tạo migration: `php artisan make:migration create_ten_bang_table`
- Chạy migration: `php artisan migrate`
- Rollback batch gần nhất: `php artisan migrate:rollback`
- Rollback một số batch gần nhất: `php artisan migrate:rollback --step=2`
- Rollback batch cụ thể: `php artisan migrate:rollback --batch=2`
- Rollback migration theo đường dẫn: `php artisan migrate:rollback --path=database/migrations/ten_file.php`
- Tạo seeder: `php artisan make:seeder TenBangSeeder`
- Chạy một seeder: `php artisan db:seed --class=TenBangSeeder`
- Chạy `DatabaseSeeder`: `php artisan db:seed`
- Tạo factory: `php artisan make:factory TenBangFactory`
- Tạo model kèm factory: `php artisan make:model TenBang -f`

`php artisan migrate:refresh` rollback và chạy lại migrations; thao tác này có thể làm mất dữ liệu. `php artisan migrate:fresh --seed` xóa toàn bộ bảng trước khi tạo lại và seed dữ liệu; chỉ chạy trên database có thể xóa.

## Kiểm tra

```bash
php artisan test
```

## VS Code

Khi mở thư mục trong VS Code, cài các extension được đề xuất trong `.vscode/extensions.json`.
