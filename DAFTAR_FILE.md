# 📁 Daftar Lengkap File Lab

## ✅ File yang Tersedia (Untuk Mahasiswa)

### 📖 Dokumentasi Utama (5 files)
1. ✅ `README.md` - Entry point, overview lab
2. ✅ `UNTUK_MAHASISWA.md` - Panduan lengkap mahasiswa
3. ✅ `KISI-KISI_PERBAIKAN.md` - ⭐ Struktur solusi & checklist
4. ✅ `CARA_MENGERJAKAN.md` - Step-by-step guide
5. ✅ `KONSEP_SECURITY_BY_DESIGN.md` - Referensi konsep

### 📝 Petunjuk Per Modul (4 files)
6. ✅ `PETUNJUK_MODUL_1.md` - Authentication & Login Security
7. ✅ `PETUNJUK_MODUL_2.md` - Order & Refund System
8. ✅ `PETUNJUK_MODUL_3.md` - E-Wallet System
9. ✅ `PETUNJUK_MODUL_4.md` - Voucher & Promo System

### 📊 Referensi (2 files)
10. ✅ `VERIFIKASI_KODE_TIDAK_AMAN.md` - Daftar semua masalah
11. ✅ `RINGKASAN_LAB.md` - Overview lengkap

### 👨‍🏫 Untuk Dosen/Asisten (1 file)
12. ✅ `PANDUAN_DOSEN.md` - Cara mengajar & grading

### 💻 Kode TIDAK AMAN (10 files)

**Controllers (4 files)**:
13. ✅ `app/Http/Controllers/AuthController.php` - 9 masalah
14. ✅ `app/Http/Controllers/OrderController.php` - 7 masalah
15. ✅ `app/Http/Controllers/WalletController.php` - 8 masalah
16. ✅ `app/Http/Controllers/VoucherController.php` - 10+ masalah

**Models (4 files)**:
17. ✅ `app/Models/User.php` - 4 masalah
18. ✅ `app/Models/Order.php` - 8 masalah
19. ✅ `app/Models/Wallet.php` - 8 masalah
20. ✅ `app/Models/Voucher.php` - 12+ masalah

**Database (4 files)**:
21. ✅ `database/migrations/2024_01_01_000001_create_users_table.php`
22. ✅ `database/migrations/2024_01_01_000002_create_orders_table.php`
23. ✅ `database/migrations/2024_01_01_000003_create_wallets_table.php`
24. ✅ `database/migrations/2024_01_01_000004_create_vouchers_table.php`

**Routes (1 file)**:
25. ✅ `routes/api.php`

**Config (2 files)**:
26. ✅ `composer.json`
27. ✅ `.env.example`

### 🧪 Test Cases (4 files, 29 tests total)
28. ✅ `tests/Feature/Modul1AuthTest.php` - 5 tests
29. ✅ `tests/Feature/Modul2OrderTest.php` - 8 tests
30. ✅ `tests/Feature/Modul3WalletTest.php` - 8 tests
31. ✅ `tests/Feature/Modul4VoucherTest.php` - 8 tests

### 📋 File Tambahan (1 file)
32. ✅ `DAFTAR_FILE.md` - File ini

---

## ❌ File yang TIDAK Ada (Sengaja)

Mahasiswa harus membuat sendiri:

### Modul 1
- ❌ `database/migrations/YYYY_MM_DD_create_login_attempts_table.php`
- ❌ `database/migrations/YYYY_MM_DD_add_lockout_to_users_table.php`
- ❌ `app/Models/LoginAttempt.php`

### Modul 2
- ❌ `app/Enums/OrderStatus.php`
- ❌ `app/ValueObjects/Money.php`
- ❌ `database/migrations/YYYY_MM_DD_create_audit_logs_table.php`
- ❌ `app/Models/AuditLog.php`
- ❌ `app/Events/OrderPaid.php`
- ❌ `app/Events/OrderRefunded.php`
- ❌ `app/Listeners/LogOrderEvent.php`
- ❌ `app/Exceptions/InvalidStateTransition.php`
- ❌ `app/Exceptions/ImmutableFieldException.php`

### Modul 3
- ❌ `database/migrations/YYYY_MM_DD_create_wallet_transactions_table.php`
- ❌ `database/migrations/YYYY_MM_DD_add_fields_to_wallets_table.php`
- ❌ `app/Enums/TransactionType.php`
- ❌ `app/Enums/TransactionStatus.php`
- ❌ `app/Models/WalletTransaction.php`
- ❌ `app/Services/WalletTransferService.php`
- ❌ `app/Events/WalletDebited.php`
- ❌ `app/Events/WalletCredited.php`
- ❌ `app/Events/WalletTransferred.php`
- ❌ `app/Events/WalletSuspended.php`
- ❌ `app/Listeners/DetectAnomalousActivity.php`
- ❌ `app/Exceptions/InsufficientBalanceException.php`
- ❌ `app/Exceptions/DailyLimitExceededException.php`

### Modul 4
- ❌ `app/Enums/DiscountType.php`
- ❌ `app/ValueObjects/VoucherCode.php`
- ❌ `app/ValueObjects/Discount.php`
- ❌ `database/migrations/YYYY_MM_DD_create_voucher_redemptions_table.php`
- ❌ `app/Models/VoucherRedemption.php`
- ❌ `app/Services/VoucherRedemptionService.php`
- ❌ `app/Events/VoucherRedeemed.php`
- ❌ `app/Events/VoucherDeactivated.php`
- ❌ `app/Events/VoucherAbuseDetected.php`
- ❌ `app/Listeners/DetectVoucherAbuse.php`
- ❌ `app/Exceptions/VoucherCannotBeRedeemedException.php`

---

## 📊 Statistik

### File yang Tersedia
- **Total**: 32 files
- **Dokumentasi**: 12 files
- **Kode**: 10 files
- **Database**: 4 files
- **Tests**: 4 files
- **Config**: 2 files

### File yang Harus Dibuat Mahasiswa
- **Total**: ~40 files
- **Modul 1**: ~3 files
- **Modul 2**: ~9 files
- **Modul 3**: ~13 files
- **Modul 4**: ~11 files
- **Shared**: ~4 files (Money, exceptions, dll)

### Masalah Keamanan
- **Total**: 60+ masalah
- **Modul 1**: 9 masalah
- **Modul 2**: 15 masalah
- **Modul 3**: 16 masalah
- **Modul 4**: 20+ masalah

### Test Cases
- **Total**: 29 test cases
- **Modul 1**: 5 tests
- **Modul 2**: 8 tests
- **Modul 3**: 8 tests
- **Modul 4**: 8 tests

---

## 🎯 Cara Menggunakan Lab Ini

### Untuk Mahasiswa

1. **Baca dokumentasi** (urutan):
   - `README.md` - overview
   - `UNTUK_MAHASISWA.md` - panduan lengkap
   - `KISI-KISI_PERBAIKAN.md` - struktur solusi
   - `PETUNJUK_MODUL_X.md` - detail per modul

2. **Identifikasi masalah**:
   - Baca kode yang tidak aman
   - Lihat `VERIFIKASI_KODE_TIDAK_AMAN.md`
   - Pahami kenapa tidak aman

3. **Implementasi solusi**:
   - Ikuti kisi-kisi
   - Buat file yang diperlukan
   - Implementasi berdasarkan konsep

4. **Testing**:
   - Run test per modul
   - Debug jika gagal
   - Repeat sampai PASS

### Untuk Dosen/Asisten

1. **Persiapan**:
   - Baca `PANDUAN_DOSEN.md`
   - Setup environment
   - Siapkan grading rubric

2. **Mengajar**:
   - Jelaskan konsep Security by Design
   - Demo live coding (optional)
   - Code review session

3. **Grading**:
   - Cek test PASS
   - Review code quality
   - Cek security implementation
   - Beri feedback

---

## 📞 Support

Jika ada file yang hilang atau error:
1. Cek `DAFTAR_FILE.md` ini
2. Cek git log
3. Contact asisten lab

---

**Status**: ✅ Lab lengkap dan siap digunakan
**Versi**: 1.0
**Terakhir update**: 2024
