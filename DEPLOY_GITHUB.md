# 🚀 Cara Deploy Lab ke GitHub

## Persiapan

### 1. Pastikan Git Terinstall
```bash
git --version
```

Jika belum terinstall, download dari: https://git-scm.com/

### 2. Buat Repository di GitHub

1. Login ke GitHub: https://github.com
2. Klik tombol **"New"** atau **"+"** → **"New repository"**
3. Isi form:
   - **Repository name**: `laravel-security-by-design-lab`
   - **Description**: `Lab untuk belajar Security by Design dengan Laravel - Kode sengaja tidak aman untuk tujuan pembelajaran`
   - **Visibility**: 
     - ✅ **Public** (jika ingin dibagikan ke umum)
     - ✅ **Private** (jika hanya untuk mahasiswa tertentu)
   - ❌ **JANGAN** centang "Add a README file" (kita sudah punya)
   - ❌ **JANGAN** centang "Add .gitignore" (kita akan buat sendiri)
   - ❌ **JANGAN** pilih license dulu
4. Klik **"Create repository"**

---

## Langkah Deploy

### Step 1: Buat .gitignore

Buat file `.gitignore` di root project:

```bash
# File ini sudah dibuat di bawah
```

### Step 2: Initialize Git Repository

Di terminal, jalankan di root project:

```bash
# Initialize git
git init

# Add semua file
git add .

# Commit pertama
git commit -m "Initial commit: Lab Security by Design - Laravel

- 33 files tersedia (dokumentasi + kode tidak aman + tests)
- 60+ masalah keamanan untuk diperbaiki
- 4 modul: Authentication, Order, Wallet, Voucher
- Kode sengaja tidak aman untuk tujuan pembelajaran"
```

### Step 3: Connect ke GitHub

Ganti `YOUR_USERNAME` dan `YOUR_REPO` dengan username dan nama repo Anda:

```bash
# Add remote
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git

# Atau jika pakai SSH:
# git remote add origin git@github.com:YOUR_USERNAME/YOUR_REPO.git

# Push ke GitHub
git branch -M main
git push -u origin main
```

### Step 4: Verifikasi

1. Buka repository di GitHub
2. Pastikan semua file sudah terupload
3. Cek README.md tampil dengan baik

---

## Struktur Repository di GitHub

Setelah deploy, struktur akan terlihat seperti ini:

```
laravel-security-by-design-lab/
├── 📄 README.md (tampil otomatis di halaman utama)
├── 📁 app/
│   ├── Http/Controllers/
│   └── Models/
├── 📁 database/migrations/
├── 📁 routes/
├── 📁 tests/Feature/
├── 📄 UNTUK_MAHASISWA.md
├── 📄 KISI-KISI_PERBAIKAN.md
├── 📄 PETUNJUK_MODUL_1.md
├── 📄 PETUNJUK_MODUL_2.md
├── 📄 PETUNJUK_MODUL_3.md
├── 📄 PETUNJUK_MODUL_4.md
└── ... (file lainnya)
```

---

## Konfigurasi Repository

### 1. Tambahkan Topics (Tags)

Di halaman repository GitHub:
1. Klik ⚙️ **Settings** (atau klik "Add topics" di halaman utama)
2. Tambahkan topics:
   - `laravel`
   - `security`
   - `security-by-design`
   - `ddd`
   - `domain-driven-design`
   - `education`
   - `lab`
   - `php`
   - `learning`

### 2. Edit Description

Di halaman utama repository, klik **"Edit"** di sebelah About, isi:
```
Lab untuk belajar Security by Design dengan Laravel. Kode sengaja tidak aman untuk tujuan pembelajaran. 60+ masalah keamanan, 4 modul, 29 test cases.
```

### 3. Tambahkan Website (Optional)

Jika ada dokumentasi online atau demo, tambahkan URL-nya.

---

## Membuat Releases (Optional tapi Recommended)

### Buat Release v1.0

1. Di GitHub, klik **"Releases"** → **"Create a new release"**
2. Isi form:
   - **Tag version**: `v1.0.0`
   - **Release title**: `v1.0.0 - Initial Release`
   - **Description**:
   ```markdown
   ## Lab Security by Design - Laravel v1.0.0
   
   ### 📦 Yang Tersedia
   - 33 files (dokumentasi + kode tidak aman + tests)
   - 60+ masalah keamanan untuk diperbaiki
   - 4 modul pembelajaran
   - 29 test cases
   - Kisi-kisi perbaikan lengkap
   
   ### 🎯 Modul
   1. **Modul 1**: Authentication & Login Security
   2. **Modul 2**: Order & Refund System
   3. **Modul 3**: E-Wallet System
   4. **Modul 4**: Voucher & Promo System
   
   ### 📚 Dokumentasi
   - README.md - Overview
   - UNTUK_MAHASISWA.md - Panduan mahasiswa
   - KISI-KISI_PERBAIKAN.md - Struktur solusi
   - PANDUAN_DOSEN.md - Panduan dosen
   
   ### ⚠️ Catatan Penting
   **Semua kode SENGAJA TIDAK AMAN untuk tujuan pembelajaran!**
   
   Mahasiswa harus memperbaiki kode berdasarkan prinsip Security by Design.
   ```
3. Klik **"Publish release"**

---

## Untuk Mahasiswa: Cara Clone Repository

### Clone dengan HTTPS
```bash
git clone https://github.com/YOUR_USERNAME/laravel-security-by-design-lab.git
cd laravel-security-by-design-lab
```

### Clone dengan SSH
```bash
git clone git@github.com:YOUR_USERNAME/laravel-security-by-design-lab.git
cd laravel-security-by-design-lab
```

### Setup Project
```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate key
php artisan key:generate

# Setup database
php artisan migrate

# Run tests (akan GAGAL karena kode tidak aman)
php artisan test
```

---

## Membuat Branch untuk Solusi (Optional)

Jika ingin menyimpan solusi di branch terpisah:

```bash
# Buat branch solusi (jangan push ke main)
git checkout -b solution

# Implementasi solusi
# ... (mahasiswa mengerjakan)

# Commit
git add .
git commit -m "Solution: Modul 1 completed"

# Push ke branch solution
git push origin solution
```

**Catatan**: Branch `main` tetap berisi kode tidak aman, branch `solution` berisi solusi.

---

## Proteksi Branch Main (Recommended)

Untuk mencegah mahasiswa push solusi ke main:

1. Di GitHub, buka **Settings** → **Branches**
2. Klik **"Add rule"**
3. Branch name pattern: `main`
4. Centang:
   - ✅ **Require pull request reviews before merging**
   - ✅ **Require status checks to pass before merging**
5. Save

---

## Membuat GitHub Classroom (Optional)

Jika menggunakan GitHub Classroom untuk assignment:

### 1. Setup GitHub Classroom

1. Buka: https://classroom.github.com/
2. Login dengan akun GitHub
3. Klik **"New classroom"**
4. Pilih organization atau buat baru
5. Beri nama classroom: "Security by Design Lab"

### 2. Buat Assignment

1. Klik **"New assignment"**
2. Isi form:
   - **Assignment title**: "Lab Security by Design - Laravel"
   - **Deadline**: (sesuai jadwal)
   - **Repository visibility**: Private
   - **Grant students admin access**: ✅ (agar bisa push)
3. **Template repository**: Pilih repository lab yang sudah dibuat
4. **Enable feedback pull requests**: ✅
5. **Enable autograding**: ✅ (optional, bisa setup test runner)
6. Create assignment

### 3. Share Link ke Mahasiswa

GitHub Classroom akan generate link seperti:
```
https://classroom.github.com/a/XXXXXXXX
```

Mahasiswa klik link → otomatis fork repository → bisa mulai mengerjakan.

---

## Autograding dengan GitHub Actions (Advanced)

Buat file `.github/workflows/tests.yml`:

```yaml
name: Tests

on:
  push:
    branches: [ main, solution ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, xml, ctype, json, bcmath
        
    - name: Install Dependencies
      run: composer install --prefer-dist --no-progress
      
    - name: Copy .env
      run: cp .env.example .env
      
    - name: Generate key
      run: php artisan key:generate
      
    - name: Run tests
      run: php artisan test
```

Ini akan otomatis run test setiap kali ada push/PR.

---

## Tips untuk Dosen

### 1. Buat Organization

Buat GitHub Organization untuk kelas:
- Nama: "Security-by-Design-2024" (atau sesuai tahun)
- Semua repository lab di organization ini
- Mudah manage access mahasiswa

### 2. Gunakan GitHub Projects

Untuk tracking progress mahasiswa:
1. Buka **Projects** → **New project**
2. Template: "Board"
3. Kolom: "To Do", "In Progress", "Review", "Done"
4. Add issues untuk setiap modul

### 3. Buat Issues Template

Buat `.github/ISSUE_TEMPLATE/bug_report.md`:

```markdown
---
name: Bug Report
about: Laporkan bug di test atau kode
title: '[BUG] '
labels: bug
assignees: ''
---

## Deskripsi Bug
Jelaskan bug yang ditemukan

## Modul
- [ ] Modul 1
- [ ] Modul 2
- [ ] Modul 3
- [ ] Modul 4

## Langkah Reproduksi
1. ...
2. ...

## Expected Behavior
Apa yang seharusnya terjadi

## Actual Behavior
Apa yang terjadi

## Screenshot (jika ada)
```

---

## Troubleshooting

### Error: Permission Denied (publickey)

Jika pakai SSH dan error:
```bash
# Generate SSH key
ssh-keygen -t ed25519 -C "your_email@example.com"

# Add ke ssh-agent
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519

# Copy public key
cat ~/.ssh/id_ed25519.pub

# Paste ke GitHub Settings → SSH Keys
```

### Error: Large Files

Jika ada file besar (>100MB):
```bash
# Install Git LFS
git lfs install

# Track large files
git lfs track "*.zip"
git lfs track "*.sql"

# Add .gitattributes
git add .gitattributes
git commit -m "Add Git LFS"
```

### Error: Already Exists

Jika folder sudah ada git:
```bash
# Remove existing git
rm -rf .git

# Start fresh
git init
```

---

## Checklist Deploy

Sebelum share ke mahasiswa, pastikan:

- [ ] Repository sudah public/private sesuai kebutuhan
- [ ] README.md tampil dengan baik
- [ ] Semua file terupload
- [ ] .gitignore sudah benar
- [ ] Topics/tags sudah ditambahkan
- [ ] Description sudah diisi
- [ ] Release v1.0.0 sudah dibuat
- [ ] Branch protection sudah disetup (jika perlu)
- [ ] GitHub Classroom sudah disetup (jika pakai)
- [ ] Link sudah dishare ke mahasiswa

---

## Link Repository

Setelah deploy, share link ini ke mahasiswa:

```
Repository: https://github.com/YOUR_USERNAME/laravel-security-by-design-lab

Cara clone:
git clone https://github.com/YOUR_USERNAME/laravel-security-by-design-lab.git

Dokumentasi:
- README.md - Mulai di sini
- UNTUK_MAHASISWA.md - Panduan lengkap
- KISI-KISI_PERBAIKAN.md - Struktur solusi
```

---

**Selamat! Lab sudah siap digunakan di GitHub! 🎉**
