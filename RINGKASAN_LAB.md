# Ringkasan Lab Security by Design - Laravel

## Overview

Lab ini dirancang untuk mengajarkan konsep Security by Design melalui praktik langsung memperbaiki kode Laravel yang sengaja dibuat bermasalah. Mahasiswa akan belajar bahwa security bukan sekadar fitur tambahan, tapi harus dibangun ke dalam desain dan arsitektur aplikasi.

## Struktur Lab

### Modul 1: Authentication & Login Security (3-4 jam)
**Fokus**: Deep Model, Domain Rules, Lockout Mechanism

**Masalah yang Diperbaiki**:
- Tidak ada tracking login attempts → brute force possible
- Tidak ada lockout mechanism
- Session tidak ter-bind dengan device
- Shallow model tanpa business logic

**Konsep yang Dipelajari**:
- Deep Model vs Shallow Model
- Domain Rules Enforcement
- Audit Trail
- Session Security

**File Utama**:
- `app/Models/User.php`
- `app/Http/Controllers/AuthController.php`
- `app/Models/LoginAttempt.php` (dibuat mahasiswa)

---

### Modul 2: Order & Refund System (4-5 jam)
**Fokus**: State Machine, Immutability, Value Objects

**Masalah yang Diperbaiki**:
- Primitive obsession (status string, amount double)
- Boolean flag hell (is_paid, is_shipped, dll)
- Anemic domain model
- Temporal coupling tidak enforced
- Amount bisa diubah setelah order dibuat

**Konsep yang Dipelajari**:
- State Machine Pattern
- Value Objects (Money, OrderStatus)
- Immutability
- Domain Events
- Transition Methods

**File Utama**:
- `app/Models/Order.php`
- `app/Http/Controllers/OrderController.php`
- `app/ValueObjects/Money.php` (dibuat mahasiswa)
- `app/Enums/OrderStatus.php` (dibuat mahasiswa)

---

### Modul 3: E-Wallet System (5-6 jam)
**Fokus**: Aggregate, Domain Events, Race Condition Prevention

**Masalah yang Diperbaiki**:
- Saldo bisa negatif
- Tidak ada daily limit
- Race condition pada concurrent transactions
- Transfer tidak atomic
- God object (terlalu banyak tanggung jawab)

**Konsep yang Dipelajari**:
- Aggregate Pattern
- Pessimistic Locking
- Domain Services
- Separation of Concerns
- Anomaly Detection

**File Utama**:
- `app/Models/Wallet.php`
- `app/Http/Controllers/WalletController.php`
- `app/Services/WalletTransferService.php` (dibuat mahasiswa)
- `app/Models/WalletTransaction.php` (dibuat mahasiswa)

---

### Modul 4: Voucher & Promo System (5-6 jam)
**Fokus**: Idempotency, Quota Management, Concurrency Control

**Masalah yang Diperbaiki**:
- Race condition double redemption
- Tidak ada idempotency
- Quota tidak enforced (total, per user)
- Voucher code tidak normalized
- Tidak ada anomaly detection

**Konsep yang Dipelajari**:
- Idempotency Pattern
- Pessimistic Locking (advanced)
- Value Objects (VoucherCode, Discount)
- Quota Management
- Real-time Abuse Detection
- Immutable Records

**File Utama**:
- `app/Models/Voucher.php`
- `app/Http/Controllers/VoucherController.php`
- `app/ValueObjects/VoucherCode.php` (dibuat mahasiswa)
- `app/Models/VoucherRedemption.php` (dibuat mahasiswa)

---

## Konsep Security by Design yang Dicakup

### 1. Model Design
- ✅ Shallow vs Deep Model
- ✅ Anemic vs Rich Domain Model
- ✅ God Object vs Separation of Concerns

### 2. Data Integrity
- ✅ Primitive Obsession → Value Objects
- ✅ Boolean Flag Hell → State Machine
- ✅ Invalid State Representation
- ✅ Immutability

### 3. Concurrency & Consistency
- ✅ Race Condition Prevention
- ✅ Pessimistic Locking
- ✅ Atomic Transactions
- ✅ Idempotency

### 4. Business Rules
- ✅ Domain Rules Enforcement
- ✅ Temporal Coupling
- ✅ State Transitions
- ✅ Quota Management

### 5. Observability & Security
- ✅ Audit Trail
- ✅ Domain Events
- ✅ Anomaly Detection
- ✅ Abuse Prevention

---

## Metodologi Pembelajaran

### 1. Test-Driven
Setiap modul memiliki test cases yang:
- Akan GAGAL pada kode bermasalah
- Akan PASS setelah diperbaiki dengan benar
- Menjelaskan requirement dengan jelas

### 2. Incremental
Modul disusun dari yang sederhana ke kompleks:
1. Modul 1: Dasar (deep model, domain rules)
2. Modul 2: Intermediate (state machine, value objects)
3. Modul 3: Advanced (aggregate, services)
4. Modul 4: Expert (idempotency, concurrency)

### 3. Hands-On
Mahasiswa tidak hanya membaca, tapi:
- Mengidentifikasi masalah
- Merancang solusi
- Implementasi
- Testing
- Refactoring

---

## Deliverables

Setiap mahasiswa harus mengumpulkan:

### 1. Kode yang Sudah Diperbaiki
- Semua 4 modul
- Semua test PASS
- Clean code

### 2. Dokumentasi
- README untuk setiap modul
- Penjelasan design decisions
- Diagram (optional tapi recommended)

### 3. Refleksi
- Apa yang dipelajari
- Kesulitan yang dihadapi
- Bagaimana menerapkan di project nyata

---

## Kriteria Penilaian

### Functionality (40%)
- Semua test PASS
- Tidak ada bug
- Edge cases tertangani
- Security requirements terpenuhi

### Code Quality (30%)
- Clean code
- Proper naming
- No code duplication
- Separation of concerns
- SOLID principles

### Security (20%)
- Domain rules enforced
- Invalid state tidak bisa terjadi
- Audit trail lengkap
- Race condition handled
- Immutability enforced

### Documentation (10%)
- Code comments
- README jelas
- Design decisions explained
- Refleksi mendalam

---

## Tools & Technologies

### Required
- PHP 8.1+
- Laravel 10+
- MySQL/PostgreSQL
- Composer
- PHPUnit

### Recommended
- Laravel Pint (code formatting)
- PHPStan (static analysis)
- Git (version control)

---

## Timeline Rekomendasi

### Minggu 1-2: Modul 1 & 2
- Modul 1: Authentication (3-4 jam)
- Modul 2: Order & Refund (4-5 jam)
- Total: ~8 jam

### Minggu 3-4: Modul 3 & 4
- Modul 3: E-Wallet (5-6 jam)
- Modul 4: Voucher (5-6 jam)
- Total: ~11 jam

### Minggu 5: Finalisasi
- Code review
- Refactoring
- Documentation
- Testing edge cases

**Total Estimasi**: 20-25 jam

---

## Tips Sukses

### 1. Jangan Skip Konsep
Setiap modul mengajarkan konsep penting. Jangan langsung coding tanpa memahami konsep.

### 2. Baca Test Dulu
Test cases adalah requirement. Baca dan pahami dulu sebelum coding.

### 3. Commit Sering
Commit setiap progress kecil. Mudah rollback jika ada masalah.

### 4. Diskusi dengan Teman
Diskusi konsep OK, tapi jangan copy-paste kode.

### 5. Gunakan Debugger
Jangan hanya dd() atau var_dump(). Pelajari Xdebug atau Laravel Telescope.

---

## Troubleshooting Common Issues

### Test Gagal Terus
- Baca error message dengan teliti
- Cek apakah migration sudah dijalankan
- Cek apakah model relationship benar
- Debug dengan dd() di test

### Migration Error
```bash
php artisan migrate:fresh
```

### Autoload Error
```bash
composer dump-autoload
```

### Cache Issue
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Extensi Lab (Optional)

Untuk mahasiswa yang ingin challenge lebih:

### Modul 1+
- Implementasi 2FA
- Device fingerprinting
- Suspicious login detection

### Modul 2+
- Partial refund
- Order cancellation dengan rules
- Inventory management

### Modul 3+
- Wallet freeze/unfreeze
- Transaction reversal
- Multi-currency support

### Modul 4+
- Voucher stacking rules
- Referral codes
- Dynamic pricing

---

## Referensi

### Books
- Domain-Driven Design by Eric Evans
- Implementing Domain-Driven Design by Vaughn Vernon
- Clean Code by Robert C. Martin

### Online
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- Laravel Docs: https://laravel.com/docs
- Martin Fowler's Blog: https://martinfowler.com/

### Videos
- Laracasts: Domain-Driven Design series
- Symfony Casts: Design Patterns

---

## FAQ

**Q: Boleh pakai package Laravel seperti Fortify atau Sanctum?**
A: Untuk Modul 1, tidak. Tujuannya adalah memahami konsep, bukan pakai package. Untuk project nyata, silakan pakai package.

**Q: Harus pakai DDD pattern semua?**
A: Tidak harus strict DDD. Yang penting konsep security by design diterapkan.

**Q: Boleh pakai AI untuk bantuan?**
A: Boleh untuk memahami konsep, tapi jangan copy-paste solusi. Harus paham kenapa solusi itu benar.

**Q: Berapa lama seharusnya mengerjakan lab ini?**
A: 20-25 jam total. Jangan terburu-buru, fokus pada pemahaman.

**Q: Apakah harus mengerjakan semua modul?**
A: Ya, semua modul saling berkaitan dan membangun pemahaman bertahap.

---

## Kontak

Jika ada pertanyaan atau kesulitan:
- Diskusi di forum kelas
- Office hours dengan asisten lab
- Email ke [email_asisten]

Good luck! 🚀
