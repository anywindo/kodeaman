# Contributing to Lab Security by Design

Terima kasih atas minat Anda untuk berkontribusi! 🎉

## ⚠️ Catatan Penting

Repository ini berisi **kode yang sengaja tidak aman** untuk tujuan pembelajaran. Jangan submit PR untuk "memperbaiki" kode yang tidak aman, karena itu adalah bagian dari lab.

## Jenis Kontribusi yang Diterima

### ✅ Kontribusi yang Diterima

1. **Perbaikan Dokumentasi**
   - Typo atau grammar
   - Penjelasan yang lebih jelas
   - Terjemahan ke bahasa lain
   - Tambahan contoh

2. **Perbaikan Test Cases**
   - Bug di test
   - Test case tambahan
   - Improve test coverage

3. **Tambahan Modul**
   - Modul baru dengan konsep berbeda
   - Harus include: kode tidak aman + test + dokumentasi

4. **Improvement Kisi-Kisi**
   - Hint yang lebih jelas
   - Struktur yang lebih baik
   - Contoh tambahan

### ❌ Kontribusi yang TIDAK Diterima

1. **Solusi Lengkap**
   - Jangan submit kode solusi lengkap
   - Mahasiswa harus mengerjakan sendiri

2. **"Fix" Kode Tidak Aman**
   - Kode sengaja tidak aman
   - Jangan submit PR untuk memperbaiki

3. **Perubahan Struktur Besar**
   - Diskusikan dulu via issue

## Cara Berkontribusi

### 1. Fork Repository

Klik tombol "Fork" di GitHub

### 2. Clone Fork Anda

```bash
git clone https://github.com/YOUR_USERNAME/laravel-security-by-design-lab.git
cd laravel-security-by-design-lab
```

### 3. Buat Branch Baru

```bash
git checkout -b feature/nama-fitur
# atau
git checkout -b fix/nama-bug
# atau
git checkout -b docs/nama-dokumentasi
```

### 4. Buat Perubahan

Edit file yang diperlukan

### 5. Commit

```bash
git add .
git commit -m "feat: deskripsi singkat perubahan

Penjelasan lebih detail jika perlu"
```

**Commit Message Convention**:
- `feat:` - Fitur baru
- `fix:` - Bug fix
- `docs:` - Perubahan dokumentasi
- `test:` - Tambah/update test
- `refactor:` - Refactoring kode
- `style:` - Format, typo, dll

### 6. Push ke Fork

```bash
git push origin feature/nama-fitur
```

### 7. Buat Pull Request

1. Buka repository Anda di GitHub
2. Klik "Pull Request"
3. Isi deskripsi dengan jelas
4. Submit

## Pull Request Guidelines

### Checklist PR

- [ ] Deskripsi jelas tentang perubahan
- [ ] Tidak mengubah kode tidak aman (kecuali ada bug di test)
- [ ] Dokumentasi diupdate jika perlu
- [ ] Test masih berjalan (jika ada perubahan test)
- [ ] Tidak ada file yang tidak perlu (IDE config, dll)

### Template PR

```markdown
## Deskripsi
Jelaskan perubahan yang dibuat

## Jenis Perubahan
- [ ] Dokumentasi
- [ ] Test case
- [ ] Bug fix
- [ ] Fitur baru

## Modul Terkait (jika ada)
- [ ] Modul 1
- [ ] Modul 2
- [ ] Modul 3
- [ ] Modul 4
- [ ] Umum

## Testing
Jelaskan bagaimana Anda test perubahan ini

## Screenshot (jika ada)
```

## Melaporkan Bug

### Bug di Test atau Dokumentasi

Jika menemukan bug di test atau dokumentasi:

1. Buka **Issues** → **New Issue**
2. Pilih template "Bug Report"
3. Isi dengan lengkap:
   - Deskripsi bug
   - Modul terkait
   - Langkah reproduksi
   - Expected vs actual behavior
   - Screenshot jika ada

### Bug di Kode Lab

Jika menemukan bug di kode lab (bukan masalah keamanan yang sengaja):

1. Pastikan itu bug, bukan masalah keamanan yang sengaja
2. Buka issue dengan label `bug`
3. Jelaskan kenapa itu bug, bukan fitur

## Usulan Fitur

Punya ide untuk improve lab?

1. Buka **Issues** → **New Issue**
2. Pilih template "Feature Request"
3. Jelaskan:
   - Apa yang ingin ditambahkan
   - Kenapa itu berguna
   - Bagaimana implementasinya

## Code Style

### PHP
- Follow PSR-12
- Use type hints
- Add docblocks untuk method public

### Markdown
- Use proper headers (# ## ###)
- Add blank line between sections
- Use code blocks dengan syntax highlighting

### Commit Messages
- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit first line to 72 characters
- Reference issues: "Fix #123"

## Review Process

1. **Automated Checks**
   - Tests harus pass
   - No merge conflicts

2. **Manual Review**
   - Maintainer akan review dalam 1-3 hari
   - Mungkin ada request changes
   - Diskusi via PR comments

3. **Merge**
   - Setelah approved, akan di-merge
   - Branch akan di-delete otomatis

## Questions?

Jika ada pertanyaan:
- Buka **Discussions** di GitHub
- Atau buat issue dengan label `question`

## Code of Conduct

### Our Pledge

Kami berkomitmen untuk membuat partisipasi di project ini bebas dari harassment untuk semua orang.

### Our Standards

**Perilaku yang Diterima**:
- Menggunakan bahasa yang ramah
- Menghormati sudut pandang berbeda
- Menerima kritik konstruktif
- Fokus pada yang terbaik untuk komunitas

**Perilaku yang Tidak Diterima**:
- Trolling, komentar menghina
- Harassment publik atau privat
- Publishing informasi privat orang lain
- Perilaku tidak profesional lainnya

### Enforcement

Pelanggaran bisa dilaporkan ke maintainer. Semua laporan akan direview dan diinvestigasi.

## License

Dengan berkontribusi, Anda setuju bahwa kontribusi Anda akan dilisensikan di bawah MIT License yang sama dengan project ini.

---

**Terima kasih atas kontribusi Anda! 🙏**
