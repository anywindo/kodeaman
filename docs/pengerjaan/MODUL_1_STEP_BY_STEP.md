# Modul 1: Authentication & Login Security — Step by Step

## 📋 Ringkasan Masalah

Buka dan baca komentar di file-file berikut sebelum mulai:

| File | Masalah |
|------|---------|
| `app/Models/User.php` | Shallow model — hanya data container, tidak ada business logic |
| `app/Http/Controllers/AuthController.php` | Tidak ada rate limiting, tidak ada logging, session tidak ter-bind |
| `database/migrations/..._create_users_table.php` | Tidak ada field `locked_until` dan `failed_login_attempts` |

Lihat juga `tests/Feature/Modul1AuthTest.php` — semua test harus PASS setelah perbaikan selesai.

---

## 🎯 Target Akhir (dari Test Cases)

| Test | Ekspektasi |
|------|-----------|
| `login_gagal_5x_harus_lockout_15_menit` | POST `/api/login` gagal 5x → respons 429 dengan pesan `Account locked. Try again in 15 minutes.` |
| `login_attempts_harus_tercatat_di_database` | Setiap login gagal → record di tabel `login_attempts` dengan `email` dan `success = false` |
| `session_dari_ip_berbeda_harus_force_logout` | Login dari IP A, akses dari IP B → respons 401 |
| `setelah_lockout_expired_bisa_login_lagi` | User di-lock 15 menit, setelah 16 menit → bisa login (200) |
| `login_berhasil_harus_clear_login_attempts` | Login berhasil → `failed_login_attempts` di-reset ke 0 |

---

## Step 1: Install Laravel Sanctum

Test menggunakan token-based auth (`$this->withToken($token1)`), jadi kita butuh Sanctum. Meskipun sudah ada di Laravel, kita perlu menjalankannya untuk memastikan `package:discover` mendeteksinya.

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

Setelah publish, migration Sanctum untuk tabel `personal_access_tokens` akan muncul di `database/migrations/`.

> **Penjelasan**: Sanctum menyediakan API token sederhana untuk SPA dan mobile app. Kita pakai ini karena test case mengharapkan response `{ "token": "..." }` saat login berhasil, dan `$this->withToken()` untuk autentikasi request berikutnya.

### Aktifkan `HasApiTokens` di User Model

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    // ...
}
```

> **Kenapa?** Test case `session_dari_ip_berbeda_harus_force_logout` memanggil `$response1->json('token')` — ini berarti login harus mengembalikan token Sanctum.

---

## Step 2: Buat Migration untuk Tabel `login_attempts`

Buat file migration baru:

```bash
php artisan make:migration create_login_attempts_table
```

Isi migration (lihat komentar di `PETUNJUK_MODUL_1.md` untuk struktur yang diharapkan):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->boolean('success')->default(false);
            $table->timestamp('attempted_at')->useCurrent();

            // Index gabungan untuk query cepat: 
            // "berapa kali email X gagal login dalam 15 menit terakhir?"
            $table->index(['email', 'attempted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_attempts');
    }
};
```

### Kenapa field-field ini?

| Field | Alasan (dari komentar kode) |
|-------|-----|
| `email` | Test `login_attempts_harus_tercatat_di_database` mengecek `assertDatabaseHas('login_attempts', ['email' => ...])` |
| `ip_address` | Komentar di `AuthController`: *"Tidak ada logging untuk failed attempts"* — IP perlu dicatat untuk audit trail |
| `user_agent` | Komentar di `PETUNJUK_MODUL_1.md`: *"Simpan IP address, user agent, timestamp"* |
| `success` | Test mengecek `'success' => false` — artinya field ini boolean |
| `attempted_at` | Komentar di `KISI-KISI_PERBAIKAN.md`: *"cek apakah >= 5 attempts dalam 15 menit"* — perlu timestamp |

---

## Step 3: Buat Migration untuk Update Tabel `users`

Tabel `users` saat ini tidak punya field lockout. Lihat komentar di migration asli:

```php
// MASALAH: Tidak ada field untuk lockout
// Seharusnya ada:
// $table->timestamp('locked_until')->nullable();
// $table->integer('failed_login_attempts')->default(0);
```

Buat migration baru:

```bash
php artisan make:migration add_lockout_fields_to_users_table
```

Isi:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('locked_until')->nullable()->after('password');
            $table->integer('failed_login_attempts')->default(0)->after('locked_until');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['locked_until', 'failed_login_attempts']);
        });
    }
};
```

### Kenapa field-field ini?

| Field | Alasan (dari komentar & test) |
|-------|-----|
| `locked_until` | Komentar di `User.php`: *"Tidak ada konsep lockout"* — test `setelah_lockout_expired_bisa_login_lagi` memanggil `$user->lockUntil(now()->addMinutes(15))` |
| `failed_login_attempts` | Test `login_berhasil_harus_clear_login_attempts` mengecek `$user->fresh()->failed_login_attempts` harus 0 |

Jalankan migration:

```bash
php artisan migrate
```

---

## Step 4: Buat Model `LoginAttempt`

Komentar di `PETUNJUK_MODUL_1.md` menunjukkan struktur yang diharapkan:

```php
// LoginAttempt.php
class LoginAttempt extends Model {
    public static function recordFailure(string $email, string $ip): void;
    public static function shouldLockout(string $email): bool;
    public static function clearAttempts(string $email): void;
}
```

Buat file `app/Models/LoginAttempt.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    // Kisi-kisi hint: Gunakan $timestamps = false karena pakai attempted_at custom
    public $timestamps = false;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'success',
        'attempted_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    /**
     * Catat percobaan login GAGAL.
     * 
     * Dipanggil saat Auth::attempt() return false.
     * Test: login_attempts_harus_tercatat_di_database
     * → assertDatabaseHas('login_attempts', ['email' => ..., 'success' => false])
     */
    public static function recordFailure(string $email, string $ip, ?string $userAgent = null): void
    {
        self::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'success' => false,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Catat percobaan login BERHASIL.
     * 
     * Untuk audit trail — tahu kapan user terakhir login.
     */
    public static function recordSuccess(string $email, string $ip, ?string $userAgent = null): void
    {
        self::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'success' => true,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Cek apakah email ini harus di-lockout.
     * 
     * Logika: apakah ada >= 5 percobaan GAGAL dalam 15 menit terakhir?
     * 
     * Dari kisi-kisi: "cek apakah >= 5 attempts dalam 15 menit"
     * Dari test: login_gagal_5x_harus_lockout_15_menit
     */
    public static function shouldLockout(string $email): bool
    {
        $attempts = self::where('email', $email)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes(15))
            ->count();

        return $attempts >= 5;
    }

    /**
     * Hapus semua record gagal untuk email ini.
     * 
     * Dipanggil setelah login berhasil.
     * Dari kisi-kisi: "clearAttempts(string $email): void - static method"
     */
    public static function clearAttempts(string $email): void
    {
        self::where('email', $email)
            ->where('success', false)
            ->delete();
    }
}
```

### Penjelasan Konsep: Deep Model vs Shallow Model

Perhatikan bahwa semua logic (kapan harus lockout? bagaimana record attempt?) ada **di model**, bukan di controller. Ini adalah **Deep Model** — model yang berisi business logic, bukan hanya data container.

> **Sebelum (Shallow):** Controller langsung cek, tidak ada tracking.
> **Sesudah (Deep):** Model `LoginAttempt` bertanggung jawab atas semua aturan terkait login attempts.

---

## Step 5: Update Model `User`

Buka `app/Models/User.php`. Lihat komentar yang ada:

```php
// MASALAH: Tidak ada method untuk:
// - attemptLogin()
// - lockAccount()
// - isLocked()
// - canAttemptLogin()
// Model ini hanya data container, tidak ada domain logic
```

Dan dari `PETUNJUK_MODUL_1.md`:

```php
class User extends Model {
    public function isLocked(): bool;
    public function lockUntil(Carbon $until): void;
    public function canAttemptLogin(): bool;
}
```

Ubah file menjadi:

```php
<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        // PERBAIKAN: Cast locked_until ke datetime 
        // agar bisa dibandingkan dengan now()
        'locked_until' => 'datetime',
    ];

    /**
     * Cek apakah akun sedang terkunci.
     * 
     * Dari komentar: "Tidak ada konsep lockout"
     * Test: setelah_lockout_expired_bisa_login_lagi
     * → lock 15 menit, travel 16 menit, harus bisa login
     * 
     * Artinya: locked jika locked_until masih di MASA DEPAN.
     */
    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * Kunci akun sampai waktu tertentu.
     * 
     * Test memanggil: $user->lockUntil(now()->addMinutes(15))
     */
    public function lockUntil(Carbon $until): void
    {
        $this->locked_until = $until;
        $this->save();
    }

    /**
     * Buka kunci akun dan reset counter.
     */
    public function unlock(): void
    {
        $this->locked_until = null;
        $this->failed_login_attempts = 0;
        $this->save();
    }

    /**
     * Cek apakah user boleh coba login.
     * 
     * Dari kisi-kisi: "return !$this->isLocked()"
     */
    public function canAttemptLogin(): bool
    {
        return !$this->isLocked();
    }

    /**
     * Tambah counter gagal login, lock jika sudah >= 5.
     * 
     * Dari kisi-kisi: "increment counter, lock jika >= 5"
     * Test: login_gagal_5x_harus_lockout_15_menit
     */
    public function incrementFailedAttempts(): void
    {
        $this->failed_login_attempts++;
        
        if ($this->failed_login_attempts >= 5) {
            $this->locked_until = now()->addMinutes(15);
        }
        
        $this->save();
    }

    /**
     * Reset counter gagal login ke 0.
     * 
     * Test: login_berhasil_harus_clear_login_attempts
     * → assertequals(0, $user->fresh()->failed_login_attempts)
     */
    public function clearFailedAttempts(): void
    {
        $this->failed_login_attempts = 0;
        $this->save();
    }
}
```

### Penjelasan: Kenapa Logic di Model?

Komentar asli berbunyi: *"Validasi hanya di controller, tidak di domain model"* — ini masalah **Anemic Domain Model**.

Dengan memindahkan logic ke model:
- **Tidak bisa dibypass**: Siapapun yang pakai User model pasti melalui aturan yang sama
- **Mudah di-test**: Bisa test `isLocked()` tanpa harus test seluruh HTTP flow
- **Single source of truth**: Aturan "lock setelah 5x gagal" hanya ada di satu tempat

---

## Step 6: Update `AuthController` untuk Login via API

Buka `app/Http/Controllers/AuthController.php`. Lihat semua komentar masalah:

```php
// MASALAH: Tidak ada rate limiting, bisa brute force
// MASALAH: Tidak ada logging untuk failed attempts
// MASALAH: Session tidak di-bind ke device fingerprint
// MASALAH: Validasi hanya di controller
// MASALAH: User model hanya anemic data container
```

Ubah file menjadi:

```php
<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login via API — mengembalikan JSON, bukan redirect.
     * 
     * Flow sesuai kisi-kisi:
     * 1. Ambil email dari request
     * 2. Cek LoginAttempt::shouldLockout($email) → 429
     * 3. Cari user by email  
     * 4. Cek $user->isLocked() → 429
     * 5. Coba Auth::attempt()
     * 6. Gagal → record failure, increment failed attempts
     * 7. Berhasil → record success, clear attempts, return token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $ip = $request->ip();

        // PERBAIKAN 1: Cek lockout dari login attempts
        // (menjawab masalah: "Tidak ada rate limiting, bisa brute force")
        if (LoginAttempt::shouldLockout($email)) {
            return response()->json([
                'message' => 'Account locked. Try again in 15 minutes.'
            ], 429);
        }

        // PERBAIKAN 2: Cek apakah user ada dan apakah akunnya terkunci
        $user = User::where('email', $email)->first();

        if ($user && $user->isLocked()) {
            return response()->json([
                'message' => 'Account locked. Try again in 15 minutes.'
            ], 429);
        }

        // PERBAIKAN 3: Coba login dengan tracking
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Record gagal di tabel login_attempts (audit trail)
            LoginAttempt::recordFailure($email, $ip, $request->userAgent());

            // Increment counter di user (jika user ada)
            if ($user) {
                $user->incrementFailedAttempts();
            }

            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // === LOGIN BERHASIL ===

        $user = Auth::user();

        // PERBAIKAN 4: Record sukses dan clear attempts
        // (menjawab: "login berhasil → clear login attempts")
        LoginAttempt::recordSuccess($email, $ip, $request->userAgent());
        LoginAttempt::clearAttempts($email);
        $user->clearFailedAttempts();

        // PERBAIKAN 5: Buat token Sanctum yang terikat ke IP
        // (menjawab: "Session tidak di-bind ke device fingerprint")
        // Simpan IP di dalam token abilities/metadata agar bisa 
        // divalidasi di middleware nanti
        $token = $user->createToken('auth-token', ['*'], null);
        
        // Simpan IP login di personal_access_tokens
        $token->accessToken->forceFill([
            'ip_address' => $ip,
        ])->save();

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth-token');

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
        ], 201);
    }

    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
```

### Apa yang berubah dan kenapa?

| Sebelum (Masalah) | Sesudah (Perbaikan) |
|---|---|
| `Auth::attempt()` tanpa cek apapun | Cek `shouldLockout()` dan `isLocked()` dulu |
| Tidak ada logging | `LoginAttempt::recordFailure()` dan `recordSuccess()` |
| Session-based auth (mudah dicuri) | Token-based auth dengan Sanctum + IP binding |
| Return redirect (bukan API) | Return JSON response |
| Tidak ada audit trail | Setiap attempt tercatat di database |

---

## Step 7: Tambah Kolom `ip_address` ke `personal_access_tokens`

Test `session_dari_ip_berbeda_harus_force_logout` mengharapkan token terikat ke IP. Kita simpan IP di tabel `personal_access_tokens` Sanctum.

```bash
php artisan make:migration add_ip_address_to_personal_access_tokens_table
```

Isi:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('abilities');
        });
    }

    public function down()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
};
```

Jalankan:

```bash
php artisan migrate
```

---

## Step 8: Buat Middleware untuk Validasi IP Session

Test `session_dari_ip_berbeda_harus_force_logout` mengharapkan akses dari IP berbeda di-reject dengan 401.

Buat middleware baru:

```bash
php artisan make:middleware ValidateTokenIp
```

Edit `app/Http/Middleware/ValidateTokenIp.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateTokenIp
{
    /**
     * Cek apakah IP request sama dengan IP saat token dibuat.
     * 
     * Menjawab masalah: "Session bisa dicuri dan dipakai di device lain"
     * Test: session_dari_ip_berbeda_harus_force_logout
     * → Login dari IP 192.168.1.1, akses dari 192.168.1.2 → 401
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && $token->ip_address && $token->ip_address !== $request->ip()) {
            // IP berbeda — kemungkinan session hijacking
            // Revoke token untuk keamanan
            $token->delete();
            
            return response()->json([
                'message' => 'Session invalid. IP mismatch detected.'
            ], 401);
        }

        return $next($request);
    }
}
```

---

## Step 9: Daftarkan Routes API

Saat ini `routes/api.php` hampir kosong. Test memanggil:
- `POST /api/login`  
- `GET /api/user`

Edit `routes/api.php`:

```php
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route publik (tidak perlu auth)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Route yang perlu auth + validasi IP
Route::middleware(['auth:sanctum', \App\Http\Middleware\ValidateTokenIp::class])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

### Kenapa begini?

| Route | Alasan |
|-------|--------|
| `POST /api/login` | Test memanggil `$this->postJson('/api/login', ...)` |
| `GET /api/user` | Test memanggil `$this->withToken($token1)->getJson('/api/user', ...)` untuk cek IP |
| Middleware `ValidateTokenIp` | Hanya diterapkan di route yang butuh auth — login sendiri tentu tidak perlu cek IP |

---

## Step 10: Buat/Update UserFactory

Test menggunakan `User::factory()->create()`. Kamu perlu memastikan factory sudah ada.

Buat file `database/factories/UserFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Default password untuk testing
            'remember_token' => Str::random(10),
            'locked_until' => null,
            'failed_login_attempts' => 0,
        ];
    }
}
```

> **Penting**: Default password adalah `'password'` — lihat test `setelah_lockout_expired_bisa_login_lagi` yang pakai `'password' => bcrypt('password')` lalu login dengan `'password' => 'password'`.

---

## Step 11: Jalankan Test

```bash
php artisan test --filter=Modul1AuthTest
```

### Checklist Test

- [ ] `login_gagal_5x_harus_lockout_15_menit` — 429 setelah 5x gagal
- [ ] `login_attempts_harus_tercatat_di_database` — record ada di tabel `login_attempts`
- [ ] `session_dari_ip_berbeda_harus_force_logout` — 401 dari IP berbeda
- [ ] `setelah_lockout_expired_bisa_login_lagi` — 200 setelah lockout expired
- [ ] `login_berhasil_harus_clear_login_attempts` — `failed_login_attempts` = 0

---

## 🔍 Troubleshooting

### Test gagal: "Table login_attempts doesn't exist"
→ Kamu belum menjalankan `php artisan migrate`. Atau jika pakai SQLite untuk testing, pastikan `phpunit.xml` atau `.env.testing` menggunakan `:memory:` database.

### Test gagal: "Column not found: locked_until"
→ Migration `add_lockout_fields_to_users_table` belum dijalankan.

### Test gagal di `session_dari_ip_berbeda`: "Expected 401, got 200"
→ Cek apakah middleware `ValidateTokenIp` sudah terdaftar dan diterapkan ke route `/api/user`. Pastikan juga kolom `ip_address` di `personal_access_tokens` sudah ada.

### Test gagal di `setelah_lockout_expired`: "Expected 200, got 429"
→ Cek logic `isLocked()` — harus pakai `isFuture()`, bukan hanya cek `!= null`. Setelah expired, `locked_until` sudah di masa lalu sehingga `isFuture()` return false.

### Test gagal: "Call to undefined method User::factory()"
→ Pastikan `UserFactory` ada di `database/factories/` dan model `User` menggunakan trait `Illuminate\Database\Eloquent\Factories\HasFactory`.

---

## 📚 Konsep yang Dipelajari

### 1. Shallow Model → Deep Model
**Sebelum**: `User.php` hanya punya `$fillable` — tidak ada aturan bisnis.
**Sesudah**: `User.php` punya `isLocked()`, `incrementFailedAttempts()`, `canAttemptLogin()` — model bertanggung jawab atas aturannya sendiri.

### 2. Audit Trail  
**Sebelum**: Login gagal/berhasil tidak tercatat — attacker bisa brute force tanpa terdeteksi.
**Sesudah**: Model `LoginAttempt` mencatat setiap percobaan login dengan IP, user agent, dan timestamp.

### 3. Session Ownership
**Sebelum**: Session bisa dicuri dan dipakai di device/IP lain.
**Sesudah**: Token terikat ke IP address, middleware memvalidasi setiap request.

### 4. Temporal Lockout
**Sebelum**: Tidak ada batasan percobaan login.
**Sesudah**: 5x gagal → akun terkunci 15 menit. Setelah expired, otomatis terbuka.

---

## 📁 Daftar File yang Dibuat/Diubah

| Aksi | File |
|------|------|
| **BARU** | `database/migrations/xxxx_create_login_attempts_table.php` |
| **BARU** | `database/migrations/xxxx_add_lockout_fields_to_users_table.php` |
| **BARU** | `database/migrations/xxxx_add_ip_address_to_personal_access_tokens_table.php` |
| **BARU** | `app/Models/LoginAttempt.php` |
| **BARU** | `app/Http/Middleware/ValidateTokenIp.php` |
| **BARU** | `database/factories/UserFactory.php` |
| **UBAH** | `app/Models/User.php` — tambah trait, casts, dan method-method domain |
| **UBAH** | `app/Http/Controllers/AuthController.php` — ubah ke API-based dengan tracking |
| **UBAH** | `routes/api.php` — tambah route login, register, user, logout |
