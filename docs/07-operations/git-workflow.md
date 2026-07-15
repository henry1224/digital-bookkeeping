# Standar Git Workflow

Base branch: `main`. Semua kerja lewat branch, tidak commit langsung ke `main`. Ikuti standar `perumda-eproc`: `origin/main` sebagai upstream default dan branch task memakai prefix plural (`features/...`, `docs/...`).

## Aturan Wajib

1. **Sebelum mulai perintah/tugas baru**: pastikan working tree bersih (`git status`). Jika ada perubahan belum tercommit, commit dulu di branch aktif.
2. Buat branch baru dari `main` yang up-to-date (`origin/main` bila remote sudah ada).
3. Kerjakan tugas → commit.
4. Merge ke `main` (no-ff) → push `origin main`.
5. **Jangan hapus branch tugas setelah merge.** Branch tetap disimpan sebagai arsip pekerjaan.
6. Branch baru berikutnya selalu dari `main` terbaru.
7. Satu branch = satu tugas. Jangan campur dokumen, feature, dan bug dalam satu branch.

## Penamaan Branch

| Jenis kerja | Pola branch | Contoh |
|---|---|---|
| Dokumen dibuat/diperbaharui | `docs/{nama-perbaikan}` | `docs/rbac-superset` |
| Code fitur baru/diperbaiki | `features/{phase}/{nama-menu}` | `features/phase-1/daily-sales` |
| Perbaikan bug | `bug/{nama-bug}` | `bug/journal-unbalanced` |
| Perbaikan non-bug (refactor/tweak/config) | `fix/{nama-perbaikan}` | `fix/money-cast-precision` |
| Perbaikan tampilan/style | `style/{nama-perbaikan}` | `style/login-card-polish` |

Aturan token:
- `{phase}` = `phase-1`, `phase-2`, atau `phase-3` (ikut roadmap `docs/09-modules/overview.md`).
- `{nama-*}` = kebab-case, ringkas, tanpa spasi (konsisten dgn konvensi Routes di CLAUDE.md).
- `{nama-menu}` untuk feature = nama modul/menu, mis. `bank-book`, `purchase-order`, `period-closing`.

## Penamaan Commit

Format: `{tipe}: {ringkasan}` (imperative, huruf kecil).

`{tipe}` = `docs`, `feat`, `bug`, `fix`, `test`, `chore`. Cocokkan dengan jenis branch.

Contoh:
- `docs: tambah standar git workflow`
- `feat(mvp): daily sales posting balanced journal`
- `bug: perbaiki debit=credit pada manual journal`
- `fix: presisi decimal money cast`

## Siklus Standar (per tugas)

```bash
# 0. pastikan bersih, kalau ada perubahan menggantung -> commit dulu di branch aktif
git status

# 1. branch baru dari main terbaru
git checkout main
git pull --ff-only          # jika origin sudah ada
git checkout -b docs/nama-perbaikan

# 2. kerja, lalu commit
git add -A
git commit -m "docs: ringkasan perubahan"

# 3. merge ke main (no-ff jaga jejak branch)
git checkout main
git merge --no-ff docs/nama-perbaikan -m "merge: docs nama-perbaikan"

# 4. push main
git push origin main

# 5. branch tugas tetap disimpan sebagai arsip
# Jangan jalankan `git branch -d docs/nama-perbaikan` kecuali user meminta bersih-bersih branch.
```

## Catatan

- `--no-ff` dipakai agar tiap tugas punya merge commit sebagai batas jelas di history.
- Branch tugas yang sudah merge tetap disimpan. Hapus branch hanya jika user eksplisit meminta cleanup.
- Kalau ada remote `main` (bukan `master`), ganti kata `master` di atas dengan branch default aktual repo (`git symbolic-ref --short HEAD` saat fresh clone).
- Financial transactions tidak boleh hard-delete (CLAUDE.md) — aturan ini soal kode, bukan git; tetap berlaku terpisah.
