# Skema Expo — KewirausahaanDigitalFTI

Aplikasi ini dipakai di **acara expo offline** dengan banyak penjual makanan.
Semua keputusan pembelian dan pembayaran di bawah disesuaikan dengan kondisi lapangan.

## 1. Pembeli tanpa login

- Pembeli **tidak perlu daftar / login**. Cukup buka katalog → tambah ke keranjang → checkout.
- Identitas pesanan = **nama pemesan** (wajib, untuk dipanggil di stan) + **no. WA** (opsional, untuk dihubungi penjual).
- Lacak status tanpa akun lewat halaman **Lacak** (`/home/track`) memakai kode grup (`RR-XXXXX`) atau kode ambil (`A1-001`).
- Akun hanya untuk **penjual** (kelola stan, menu, pesanan) dan **admin**.

## 2. Tidak ada pengiriman — ambil di stan, langsung beli

- Tidak ada alamat pengiriman, ongkir, atau kurir. Semua pesanan bertipe `pickup`.
- **Tanpa keranjang.** Pembeli menekan **Beli** pada satu menu → sesuaikan varian →
  langsung ke checkout → satu pesanan = satu **kode ambil** (contoh: `A2-014`).
- Tanpa keranjang lintas stan: pembeli yang ingin pesan di stan lain cukup ulangi alur
  beli langsung di stan tersebut.

## 3. Metode pembayaran: QRIS + Tunai di stan

Dipilih karena relevan dengan lapangan expo:

| Metode | Cara kerja di lapangan |
|---|---|
| **QRIS** | Pembeli scan QR milik stan, lalu tunjukkan bukti ke penjual. Penjual menekan **Tandai lunas** di dashboard. |
| **Tunai di stan** | Pembeli bayar cash saat mengambil pesanan di stan. |

Yang **sengaja tidak dipakai** dan alasannya:

- **Transfer bank** — verifikasi manual antar banyak rekening penjual lambat dan rawan salah di tengah keramaian.
- **COD / e-wallet terpusat** — tidak ada kurir dan tidak ada kasir sentral; rekonsiliasi jadi bottleneck.
- **Payment gateway online** — butuh integrasi, biaya, dan internet stabil di venue; verifikasi manual oleh penjual cukup untuk skala expo.

Status bayar per pesanan: `Belum bayar` → `Lunas` (diverifikasi penjual di dashboard).

## 4. Status pesanan (per stan)

`Menunggu` → `Diproses` → `Siap Diambil` → `Selesai`, atau `Dibatalkan`.
Tidak ada status `Dikirim` karena tidak ada pengiriman.

## 5. Alur hari-H

1. Pengunjung pesan dari HP, dapat kode grup + kode ambil per stan.
2. Datang ke stan, sebutkan kode + nama.
3. Bayar (scan QRIS / tunai) bila masih `Belum bayar`.
4. Penjual ubah status seiring masak → `Siap Diambil` → serahkan → `Selesai`.
5. Stok berkurang otomatis saat checkout; penjual nonaktifkan menu yang habis.

## 6. Checklist panitia

- [ ] Setiap stan punya nomor stan (`booth_number`) dan cetakan QR QRIS sendiri.
- [ ] Setiap penjual bisa login dan membuka dashboard di HP.
- [ ] Sediakan papan petunjuk: "Pesan tanpa login • satu stan per pesanan • simpan/screenshot kode ambil".
- [ ] Internet cadangan (tethering) untuk dashboard penjual bila WiFi venue padat.
