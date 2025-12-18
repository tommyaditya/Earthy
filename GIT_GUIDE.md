# 🚀 Git Upload Guide - Tourism Map

## Pertama Kali Upload ke GitHub

### 1. Inisialisasi Git (Jika Belum)
```bash
cd C:\xampp\htdocs\Maps
git init
```

### 2. Tambahkan Remote Repository
```bash
# Ganti dengan URL repository GitHub Anda
git remote add origin https://github.com/username/tourism-map.git
```

### 3. Add & Commit Files
```bash
# Add semua file (kecuali yang ada di .gitignore)
git add .

# Commit dengan message
git commit -m "Initial commit: Tourism Map with PHP Backend & Admin Panel"
```

### 4. Push ke GitHub
```bash
# Jika branch utama 'main'
git branch -M main
git push -u origin main

# Atau jika branch 'master'
git branch -M master
git push -u origin master
```

---

## Update Repository yang Sudah Ada

### Cara 1: Push Perubahan Baru
```bash
# 1. Check status
git status

# 2. Add file yang berubah
git add .

# 3. Commit dengan message yang jelas
git commit -m "feat: Add admin panel and MySQL backend"

# 4. Push ke GitHub
git push origin main
```

### Cara 2: Force Push (Hati-hati!)
⚠️ **WARNING**: Ini akan menimpa history di GitHub!

```bash
# Add all files
git add .

# Commit
git commit -m "refactor: Complete project restructure with backend"

# Force push (menimpa remote)
git push -f origin main
```

### Cara 3: Update dengan Merge
```bash
# Pull dulu untuk merge
git pull origin main

# Resolve conflicts jika ada, lalu:
git add .
git commit -m "merge: Update from local"
git push origin main
```

---

## ⚠️ Penting: File yang TIDAK boleh di-upload

File-file ini sudah ada di `.gitignore`:

### ❌ JANGAN UPLOAD:
- `config/database.php` - Credentials database
- `*.log` - Log files
- `.env` - Environment variables
- `uploads/*` - Uploaded images (gunakan placeholder)
- `sessions/*` - Session files
- `cache/*` - Cache files
- `.DS_Store` - Mac system files
- `Thumbs.db` - Windows system files

### ✅ YANG HARUS DIUPLOAD:
- `config/database.example.php` - Contoh config
- `database/schema.sql` - Database schema
- Semua file `.php`, `.html`, `.css`, `.js`
- `README.md` dan documentation
- `.gitignore`
- `assets/` folder

---

## 🔍 Check Sebelum Push

### 1. Verify .gitignore Working
```bash
# List file yang akan di-commit
git status

# Pastikan config/database.php TIDAK muncul
```

### 2. Check Sensitive Data
```bash
# Search untuk password atau credentials
git grep -i "password" -- ':!*.md' ':!*.sql'
git grep -i "credential"
```

### 3. Test di Browser
- [ ] Website bisa dibuka
- [ ] Map berfungsi
- [ ] Admin login works
- [ ] API endpoints response

---

## 📝 Commit Message Guidelines

Format yang bagus:
```bash
# Format
<type>: <subject>

# Contoh:
git commit -m "feat: Add admin panel with authentication"
git commit -m "fix: Resolve login issue with password hash"
git commit -m "docs: Update installation guide"
git commit -m "style: Improve responsive design"
git commit -m "refactor: Restructure API endpoints"
```

Types:
- `feat`: Fitur baru
- `fix`: Bug fix
- `docs`: Documentation
- `style`: CSS/styling
- `refactor`: Code refactoring
- `test`: Testing
- `chore`: Maintenance

---

## 🌿 Branch Strategy (Optional)

Untuk project yang lebih terorganisir:

```bash
# Main branch untuk production
main

# Development branch
git checkout -b development
git push origin development

# Feature branches
git checkout -b feature/admin-crud
git checkout -b feature/image-upload
git checkout -b fix/login-bug
```

Merge ke main setelah testing:
```bash
git checkout main
git merge feature/admin-crud
git push origin main
```

---

## 🔒 GitHub Repository Settings

### Public Repository
Jika repository public, pastikan:
- [ ] Remove semua credentials dari code
- [ ] Gunakan environment variables
- [ ] Add security warning di README
- [ ] Disable wiki & projects jika tidak dipakai

### Private Repository
Lebih aman untuk development:
- [x] Keep credentials di .gitignore
- [x] Share access hanya dengan team
- [x] Enable 2FA untuk GitHub account

---

## 🎯 Quick Command Reference

```bash
# Clone dari GitHub
git clone https://github.com/username/tourism-map.git

# Pull latest changes
git pull origin main

# Check remote URL
git remote -v

# Change remote URL
git remote set-url origin https://github.com/username/new-repo.git

# Create new branch
git checkout -b new-branch

# Switch branch
git checkout main

# Delete branch
git branch -d branch-name

# View commit history
git log --oneline

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Discard all local changes
git reset --hard HEAD
```

---

## 🐛 Common Issues

### "Remote already exists"
```bash
git remote remove origin
git remote add origin https://github.com/username/repo.git
```

### "Failed to push - rejected"
```bash
# Pull first, then push
git pull origin main --rebase
git push origin main
```

### "Large files rejected"
GitHub limit: 100MB per file
```bash
# Remove from tracking
git rm --cached large-file.zip
echo "*.zip" >> .gitignore
git commit -m "Remove large files"
```

### Accidentally committed database.php
```bash
# Remove from git (keep local file)
git rm --cached config/database.php
git commit -m "Remove database config from tracking"
git push origin main
```

---

## 📦 Create Release (Optional)

Setelah project stable:

1. Buat tag:
```bash
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

2. Di GitHub: Go to Releases → Draft a new release
3. Choose tag: v1.0.0
4. Add release notes
5. Publish release

---

## ✅ Checklist Upload

Sebelum push ke GitHub:

- [ ] `.gitignore` sudah di-commit
- [ ] `config/database.php` tidak tertrack
- [ ] `config/database.example.php` sudah ada
- [ ] `README.md` up to date
- [ ] Tidak ada credentials di code
- [ ] Schema SQL sudah include
- [ ] File system junk removed (Thumbs.db, .DS_Store)
- [ ] Test di local masih works
- [ ] Commit message jelas

Push dengan percaya diri! 🚀

```bash
git add .
git commit -m "feat: Complete tourism map with admin panel"
git push origin main
```

---

**Happy Coding! 🎉**
