# AI Skills Penunjang

Daftar skill/tooling AI untuk menjaga konsistensi & kualitas. Dibagi: sudah aktif di sesi
Claude Code (built-in) vs eksternal (install saat app di-scaffold).

## Prasyarat

Repo belum punya `package.json` (masih docs-only). Skill berbasis stack baru berguna setelah
Laravel + Inertia + Vue di-scaffold. Install skill eksternal via Skills CLI (`skills.sh`):

```bash
npx skills add <owner>/<skill>
```

Catatan keamanan: Skills CLI dapat menjalankan shell directive saat install — review dulu isinya.

## Built-in (sudah tersedia di Claude Code)

| Skill | Guna di proyek ini |
|---|---|
| `dataviz` | Chart laporan: Laba Rugi, Cashflow, inventory valuation, dashboard analitik (Fase 3). |
| `verify` | Buktikan financial flow jalan end-to-end sebelum commit (posting balanced, closing guard). |
| `run` | Jalankan app untuk cek perubahan nyata, bukan hanya test. |
| `code-review` | Review diff untuk correctness + simplification. Wajib untuk logic keuangan. |
| `security-review` | Audit keamanan diff. Penting: app keuangan, ada trust boundary & audit log. |
| `simplify` | Bersihkan over-engineering sesuai aturan "jangan abstraction untuk satu implementasi". |

## Eksternal (install setelah scaffold)

Urutan cocok dengan stack di `CLAUDE.md`:

| Skill (contoh) | Untuk |
|---|---|
| `shadcn-vue` (via UI kit) | Komponen UI konsisten. Lihat `docs/04-design/design-system.md`. |
| Laravel | Konvensi Form Request, Action, Policy, Eloquent cast, migration. |
| Inertia | Pola page/props, `useForm`, shared data. |
| Tailwind | Utility + token dari design-system. |
| Pest / PHPUnit | Feature test tiap financial flow (lihat `docs/08-testing`). |
| Playwright | E2E critical paths. |

## Cara Verifikasi Sebuah Skill Sebelum Pakai

1. Konfirmasi command nyata & cocok stack (Vue, bukan React).
2. Cek skill aktif berdasarkan penanda apa (mis. shadcn butuh `components.json`).
3. Review shell directive di `SKILL.md`.
4. Install → uji satu komponen/flow kecil → baru pakai luas.
