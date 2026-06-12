# Plan: Sales Management System SaaS (Laravel)

## Context

Membina sistem pengurusan jualan berbentuk SaaS dari kosong (direktori projek masih kosong) merangkumi 15 core component: Dashboard, Customer, Product/Service, Quotation, Invoice, Payment, Sales Order, Receipt, Inventory/Stock, Purchase/Supplier, Expenses, Discount & Pricing, Reports, Delivery Order, dan Settings.

**Keputusan seni bina (telah disahkan pengguna):**
- **Multi-tenancy:** Single database + kolum `company_id` pada semua table tenant, dikuatkuasa melalui global scope.
- **Frontend:** TALL stack — Livewire 3 + Blade + Alpine.js + Tailwind CSS.
- **Billing SaaS:** Tidak termasuk buat masa ini (struktur tenant disediakan supaya boleh ditambah kemudian).
- **Skop:** Semua 15 modul, dibina berperingkat mengikut dependency.

## Tech Stack

| Komponen | Pilihan |
|---|---|
| Framework | Laravel 12 (PHP 8.3+) |
| Frontend | Livewire 3, Alpine.js, Tailwind CSS (via Laravel Breeze Livewire starter) |
| Database | MySQL 8 (SQLite untuk dev/test) |
| Auth & Roles | Breeze + `spatie/laravel-permission` |
| PDF (Quotation/Invoice/DO/Receipt) | `barryvdh/laravel-dompdf` |
| Export Excel/CSV (Reports) | `maatwebsite/excel` |
| Testing | Pest |

## Asas Multi-Tenancy (paling kritikal — dibina dahulu)

1. **Table `companies`** — id, name, registration_no, address, phone, email, logo_path, currency (default MYR), is_active.
2. **`users`** — tambah `company_id` (nullable untuk super-admin) + role melalui spatie/permission (`super-admin`, `admin`, `staff`).
3. **Trait `app/Models/Concerns/BelongsToCompany.php`:**
   - Global scope: `where('company_id', auth()->user()->company_id)`
   - `creating` event: auto-set `company_id` dari user log masuk.
   - Semua model tenant guna trait ini — **tiada query manual company_id di mana-mana controller/component**.
4. **Middleware `EnsureCompanyIsActive`** — sekat akses jika company disabled.
5. **Helper `DocumentNumberService`** (`app/Services/DocumentNumberService.php`) — jana running number per company per jenis dokumen (cth: `QT-2026-0001`, `INV-2026-0001`, `SO-`, `DO-`, `RC-`, `PO-`), guna table `document_sequences` (company_id, type, year, last_number) dengan `lockForUpdate()` untuk elak nombor berganda.

## Skema Database (ringkasan per modul)

Semua table tenant ada `company_id` (FK + index). Senarai utama:

- **customers** — name, email, phone, billing_address, shipping_address, tax_no, credit_limit, is_active
- **categories**, **products** — sku, name, type (`product`/`service`), category_id, unit, cost_price, sell_price, tax_rate, track_stock (bool), is_active
- **price_levels** + **product_prices** — harga berbeza ikut tahap pelanggan (Discount & Pricing)
- **discounts** — name, type (`percent`/`fixed`), value, applies_to (`order`/`product`), date range
- **quotations** + **quotation_items** — customer_id, number, date, valid_until, status (`draft`/`sent`/`accepted`/`rejected`/`expired`), subtotal, discount, tax, total; item: product_id, description, qty, unit_price, discount, total
- **sales_orders** + **sales_order_items** — sama corak, status (`draft`/`confirmed`/`processing`/`delivered`/`completed`/`cancelled`), rujukan `quotation_id` (nullable)
- **delivery_orders** + **delivery_order_items** — sales_order_id, status (`pending`/`delivered`/`returned`), delivered_qty
- **invoices** + **invoice_items** — status (`draft`/`sent`/`partial`/`paid`/`overdue`/`void`), due_date, amount_paid (computed dari payments), rujukan sales_order_id (nullable — boleh direct invoice)
- **payments** — invoice_id, date, amount, method (`cash`/`bank_transfer`/`cheque`/`card`/`ewallet`), reference_no
- **receipts** — payment_id, number (auto-jana selepas payment direkod)
- **suppliers** — corak sama seperti customers
- **purchase_orders** + **purchase_order_items** — supplier_id, status (`draft`/`ordered`/`partial`/`received`/`cancelled`)
- **stock_movements** — product_id, type (`in`/`out`/`adjustment`), qty, reference (polymorphic: DO keluar stok, PO receive masuk stok), balance selepas. Stok semasa = SUM movements (atau cache kolum `quantity_on_hand` pada products)
- **expense_categories**, **expenses** — date, category_id, supplier_id (nullable), amount, description, receipt_attachment
- **settings** — key-value per company (tax default, prefix nombor dokumen, invoice terms, dll)

## Struktur Kod

```
app/
├── Livewire/           # satu folder per modul: Customers/, Products/, Quotations/, ...
│   └── Quotations/     # Index.php (table+search+filter), Form.php (create/edit), Show.php
├── Models/             # + Concerns/BelongsToCompany.php
├── Services/           # DocumentNumberService, StockService, ReportService
├── Enums/              # QuotationStatus, InvoiceStatus, PaymentMethod, ... (PHP backed enums)
└── Policies/           # per model, semak company_id + role
resources/views/
├── components/layouts/app.blade.php   # sidebar layout
├── livewire/...
└── pdf/                # quotation.blade.php, invoice.blade.php, delivery-order.blade.php, receipt.blade.php
```

Corak konsisten setiap modul CRUD: Livewire `Index` (jadual + carian + pagination + filter status) dan `Form` (modal/halaman create-edit dengan validation), route dalam `routes/web.php` di bawah middleware `auth` + `EnsureCompanyIsActive`.

## Fasa Pembinaan (ikut dependency)

### Fasa 0 — Scaffolding & Foundation
1. `laravel new` + Breeze (Livewire stack), Pest, pasang packages (spatie/permission, dompdf, maatwebsite/excel).
2. Migration `companies`, ubah `users` (+company_id), seed roles & demo company + admin user.
3. Trait `BelongsToCompany`, middleware, layout app (sidebar navigasi 15 modul), registration flow: daftar user → cipta company sekali.
4. **Settings (modul #15):** profil company (logo, alamat), tetapan cukai default, prefix nombor dokumen, pengurusan user & role dalam company.
5. `DocumentNumberService` + table `document_sequences`.

### Fasa 1 — Master Data
6. **Customer Management** — CRUD + carian + import-ready structure. *(Modul rujukan: bina dahulu dengan kemas, modul lain ikut corak sama.)*
7. **Product / Service Management** — kategori + CRUD produk/servis, toggle track_stock.
8. **Purchase / Supplier (bahagian supplier)** — CRUD supplier.
9. **Discount & Pricing** — price levels, product prices per level, CRUD discounts. Service `PricingService::resolvePrice(product, customer)` dipakai oleh Quotation/SO/Invoice.

### Fasa 2 — Aliran Jualan (dokumen berangkai)
10. **Quotation** — CRUD + line items (Livewire dynamic rows: pilih produk → auto harga dari PricingService, kira subtotal/diskaun/cukai/total secara live), PDF, tukar status, butang **"Convert to Sales Order"**.
11. **Sales Order** — CRUD + dicipta dari quotation (copy items), status flow, butang **"Create Delivery Order"** & **"Create Invoice"**.
12. **Delivery Order** — dicipta dari SO, rekod delivered qty, PDF; bila status `delivered` → trigger stock OUT melalui `StockService`.
13. **Invoice** — dari SO atau direct, due date, PDF, status auto (`partial`/`paid` dikira dari payments, `overdue` via scheduled command harian).
14. **Payment Management** — rekod bayaran terhadap invoice (boleh partial), auto-update status invoice.
15. **Receipt** — auto-jana selepas payment direkod, nombor sendiri, PDF.

### Fasa 3 — Inventori & Perbelanjaan
16. **Inventory / Stock** — `StockService` (recordIn/recordOut/adjust), halaman senarai stok semasa, sejarah pergerakan per produk, stock adjustment manual, amaran low-stock.
17. **Purchase Order** — CRUD PO kepada supplier, "Receive" → stock IN.
18. **Expenses** — kategori + CRUD perbelanjaan, lampiran resit.

### Fasa 4 — Dashboard & Reports
19. **Dashboard** — kad ringkasan (jualan bulan ini, invoice belum bayar/overdue, quotation pending, low stock), carta jualan 12 bulan (Chart.js via CDN/Alpine), senarai transaksi terkini.
20. **Reports** (`ReportService` + export Excel/PDF):
    - Sales report (ikut tempoh / pelanggan / produk)
    - Outstanding invoices / aging report
    - Payment collection report
    - Stock report & stock movement
    - Expenses report
    - Profit ringkas (jualan − kos − perbelanjaan)

## Verification

- **Pest tests** setiap fasa: unit test untuk `DocumentNumberService` (nombor berurutan, tak berganda), `StockService`, `PricingService`; feature test tenancy — **user company A tidak nampak data company B** (test paling penting); feature test aliran Quotation→SO→DO→Invoice→Payment→Receipt hujung-ke-hujung.
- **Manual:** `php artisan serve` + `npm run dev`, daftar 2 company berbeza, sahkan isolasi data, jana satu kitaran penuh dokumen jualan dan semak PDF setiap dokumen.
- `php artisan migrate:fresh --seed` mesti lulus bersih pada setiap fasa.
