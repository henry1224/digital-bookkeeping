# Design System

## UI Kit: shadcn-vue

Standar komponen memakai **shadcn-vue** (`shadcn-vue.com`) — port Vue 3 resmi dari shadcn/ui,
cocok dengan stack Inertia + Vue + Tailwind. Bukan `shadcn/ui` (React-only).

Aturan:

1. Komponen ditambahkan sebagai source ke `resources/js/Components/ui` via CLI (`npx shadcn-vue@latest add <komponen>`), bukan sebagai dependency runtime.
2. Design token di dokumen ini (colors, radius, spacing) dipetakan ke CSS variable shadcn-vue (`--primary`, `--destructive`, dll) saat `init`.
3. Komponen domain (`resources/js/Features/<module>`) menyusun dari primitif shadcn-vue, tidak menulis ulang primitif.
4. Jangan modifikasi file primitif hasil generate kecuali untuk token/tema; variasi dibuat via composition.

Setup (dijalankan saat app sudah di-scaffold, belum sekarang):

```bash
npx shadcn-vue@latest init      # buat components.json + token
npx shadcn-vue@latest add button input table dialog badge select
```

Skill AI pendamping (opsional, setelah `components.json` ada): `npx skills add shadcn/ui`
— injeksi konvensi komposisi ke asisten. Catatan: skill ini menjalankan shell directive saat
install; review dulu. Karena registry skill saat ini React-first, konfirmasi ketersediaan varian
Vue sebelum dipakai; kalau belum ada, cukup ikuti aturan di dokumen ini.

## Bahasa UI

1. Semua copy yang terlihat user wajib Bahasa Indonesia: menu, judul, label, tombol, placeholder, empty state, alert, toast, modal, email, dan notifikasi.
2. Pesan validasi dan error yang tampil di form wajib Bahasa Indonesia. Error internal boleh Bahasa Inggris hanya jika tidak dikirim ke client.
3. Label data referensi dari database wajib ramah user dan Bahasa Indonesia: status, role, tipe transaksi, kategori, group laporan, dan action audit.
4. Kode internal boleh tetap English/kebab/snake case; UI harus mapping ke label Bahasa Indonesia.
5. Jangan tampilkan enum mentah seperti `pending_approval`; tampilkan `Menunggu Persetujuan`.

## Prinsip

1. Kejelasan finansial lebih penting daripada dekorasi.
2. Table harus mudah dibaca dan mudah diexport.
3. State penting harus eksplisit: Draft, Submitted, Approved, Posted, Closed.
4. Destructive action butuh confirmation dan audit reason.
5. UI harus optimal untuk desktop terlebih dahulu.
6. Form keuangan harus mencegah input ambigu, terutama uang, tanggal, outlet, dan akun.

## Arah Visual Admin

Tone visual: **financial calm** — bersih, tenang, profesional, dan mudah dibaca lama. UI harus terasa seperti cockpit operasional keuangan, bukan landing page.

1. Gunakan kanvas terang hangat (`background`) dengan aksen teal/emerald dari `resources/css/app.css`.
2. Pakai gradient hanya sebagai lapisan halus di hero/header, bukan dekorasi ramai.
3. Utamakan kontras, whitespace, dan hierarchy; hindari card terlalu banyak border tebal.
4. Motion harus terasa smooth tapi tidak mengganggu input data: transisi 150–220ms, `ease-out`, CSS-only.
5. Data finansial tetap prioritas: angka rata kanan, status jelas, action utama konsisten.

## Standar Layout Admin

### App Shell

1. Sidebar tetap `inset` dan collapsible; active menu wajib punya background accent + indikator kiri/ikon primary.
2. Sidebar menu wajib dikelompokkan per domain: Ringkasan, Master Data, Operasional, Akuntansi/Laporan.
3. Setiap item sidebar memakai icon tile `32px` radius `md`; item aktif memakai primary background pada tile.
4. Item belum tersedia boleh disabled dengan badge `Segera`; jangan arahkan ke route kosong.
5. Topbar/header sticky ringan dengan border bawah dan background transparan blur bila halaman panjang.
6. Content wrapper default: `mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8`.
7. Jarak antar section: 24px (`space-y-6`); antar item dalam card: 16px.
8. Jangan pakai placeholder bawaan starter kit pada halaman yang sudah masuk scope produk.

### Page Header

1. Semua halaman admin punya header konsisten: eyebrow kecil, judul, deskripsi satu kalimat, action utama kanan.
2. Header surface: `rounded-lg border bg-card/80 p-6 shadow-sm` + gradient halus hanya bila membantu konteks.
3. Breadcrumb tetap di topbar; jangan ulang breadcrumb sebagai teks besar di page header.
4. Judul halaman 28–32px, `tracking-tight`, maksimal 1 baris jika memungkinkan.

### Cards & Surfaces

1. Card default: `rounded-lg border bg-card shadow-sm`.
2. Card interaktif boleh hover `-translate-y-0.5 shadow-md`, durasi 200ms.
3. Pakai radius sedang (`rounded-md`/`rounded-lg`); hindari semua surface jadi terlalu bulat.
4. Border pakai `border-border/70`; shadow halus, bukan shadow gelap.

### List Page

1. Pola wajib: Page Header → Table Header Filter Bar → Table → Pagination Footer.
2. Admin list wajib pakai `AdminPageHeader`, `DataTableCard`, `DataTableSearch`, `DataTableFilterSelect`, `DataTablePagination`, dan `RowActionButton`.
3. Search/filter/status ditempatkan di header table card, bukan breadcrumb atau page header.
4. Action utama tetap di page header kanan; action filter/reset tetap di table header.
5. Search table wajib debounce `400ms`; request filter wajib debounce pendek `150ms` dan membatalkan request lama bila ada.
6. Clear search wajib flush langsung dan tidak memicu request ganda.
7. Table header pakai uppercase kecil atau medium label; row hover `hover:bg-emerald-50/60 dark:hover:bg-emerald-950/20`.
8. Empty state berisi judul, penjelasan pendek, dan action jika user punya permission.
9. Row action selalu di kolom kanan; destructive action butuh `ConfirmDeleteDialog`.

### Form & Dialog

1. Dialog create/edit maksimal 640px; form panjang pindah ke halaman, bukan modal tinggi.
2. Input group selalu label → input → error; error area tidak mengubah layout ekstrem.
3. Primary action di kanan, secondary/cancel di kiri atau sebelum primary.
4. Field wajib pakai teks `Wajib`, bukan hanya asterisk/warna.
5. Save/post/approve/reject/close wajib punya loading state dan disable saat processing.
6. Delete wajib pakai `ConfirmDeleteDialog`, bukan `window.confirm`.
7. Delete modal memakai header rose soft, body peringatan singkat, cancel outline, confirm destructive.

### Motion & Responsiveness

1. Gunakan `transition-[color,background-color,border-color,box-shadow,transform] duration-200 ease-out` untuk elemen interaktif.
2. Jangan animasikan table row besar saat data banyak; cukup hover/focus state.
3. Hormati `motion-reduce`: animasi dekoratif harus tetap aman bila dinonaktifkan browser.
4. Desktop-first, tapi list/filter harus tetap usable di layar tablet.

### Jangan Lakukan

1. Jangan pakai link/footer bawaan Laravel starter kit pada admin produksi.
2. Jangan campur palet biru lama dengan palet teal baru tanpa mapping token.
3. Jangan bikin warna status custom per halaman; pakai status badge global.
4. Jangan tambah library animasi/desain baru sebelum CSS + shadcn-vue tidak cukup.

## Design Tokens

### Colors

Sumber kebenaran token runtime: `resources/css/app.css`.

| Token | Nilai Runtime | Penggunaan |
|---|---|---|
| background | `hsl(0 0% 100%)` | Page canvas |
| foreground | `hsl(0 0% 3.9%)` | Primary text |
| primary | `hsl(168 76% 36%)` | Main action, active navigation |
| primary-foreground | `hsl(0 0% 100%)` | Text di atas primary |
| secondary | `hsl(160 30% 94%)` | Soft secondary action |
| muted | `hsl(165 25% 96%)` | Filter/table hover/background soft |
| accent | `hsl(166 60% 92%)` | Active nav, subtle highlight |
| destructive | `hsl(0 74% 47%)` | Rejected, failed, destructive |
| border | `hsl(165 20% 88%)` | Borders |
| ring | `hsl(168 76% 40%)` | Focus ring |

### Typography

| Use | Size | Weight |
|---|---:|---:|
| Page title | 28px | 700 |
| Section title | 20px | 600 |
| Card title | 16px | 600 |
| Body | 14px | 400 |
| Label | 12px | 500 |
| Table cell | 13px | 400 |

### Spacing

Gunakan 4px base scale: 4, 8, 12, 16, 24, 32, 48.

### Radius

Small 4px, medium 8px, large 12px.

## Standar Komponen

### Button

Variants: primary, secondary, outline, ghost, danger.
Sizes: sm, md, lg, icon.
States: default, hover, focus, active, disabled, loading.

1. Primary action memakai `primary` dan icon di kiri jika action menambah/menyimpan/memproses data.
2. Secondary action memakai `outline` atau `secondary`, bukan primary kedua dalam satu area.
3. Row action table wajib pakai `RowActionButton` ukuran `36px` (`h-9 w-9`) dengan `title`, `aria-label`, dan teks `sr-only`.
4. Variant row action mengikuti eproc: `edit` = primary tint, `warning` = nonaktifkan/hold, `success` = aktifkan/approve, `danger` = hapus/tolak.
5. Reset/filter clear boleh icon-only `ghost` jika berada di filter bar.
6. Primary page action tetap boleh icon + label eksplisit: `Tambah Outlet`, `Simpan`, `Proses`.

### Input

Types: text, number, currency, date, select, textarea, file upload.
Setiap input harus memiliki label dan validation error area.
Required field harus ditandai dengan teks, bukan hanya warna.

### Money Input

1. Display format: `Rp 1.234.567`.
2. Submit value: string decimal, contoh `1234567.00`.
3. Jangan submit float JavaScript.
4. Error harus jelas jika format invalid.

### Table

Fitur wajib untuk data table:

1. Search
2. Filter
3. Sort
4. Pagination
5. Export jika report-like
6. Row actions di kanan
7. Empty state
8. Loading skeleton
9. Ringkasan total untuk table keuangan bila relevan

### Status Badge

Gunakan label konsisten:

- Draft: gray
- Submitted/Pending: warning
- Approved/Posted: success
- Rejected/Cancelled: danger
- Closed: neutral dark
- Reopened: warning

## Template Layout

1. Login page
2. Dashboard layout: sidebar + topbar + content
3. List page: header + filters + table
4. Create/Edit form page: sections + sticky actions
5. Detail page: summary card + tabs + audit trail
6. Report page: filters + summary cards + table/chart + export actions

## Aturan Form

1. Primary action button di kanan bawah.
2. Cancel/back action di kiri bawah.
3. Required fields ditandai dengan teks, bukan hanya warna.
4. Save draft tersedia pada flow yang mendukung draft.
5. Confirmation wajib untuk posting, approving, rejecting, closing, deleting.
6. Destructive confirmation wajib menyebut objek target dan efek aksi sebelum user klik confirm.
7. Closed period banner harus muncul saat record berada dalam periode tertutup.
8. Field outlet wajib terlihat pada data outlet-scoped kecuali user hanya punya satu outlet.
