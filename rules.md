# Aturan (Rules) Pengembangan & Sistem

Dokumen ini berisi panduan dan aturan ketat yang diterapkan dalam aplikasi "AI Social Media Caption Generator". Dokumen ini dapat Anda sertakan sebagai dokumentasi proyek (UTS).

## 1. Aturan Prompting AI (Constraint & Etika)
- **Tanpa Nama Personal:** Sistem DILARANG KERAS menyertakan nama personal atau nama orang dalam caption atau branding yang dihasilkan. Instruksi ini secara *hardcoded* (ditanamkan langsung) pada prompt sistem backend sebelum dikirimkan ke server AI (Gemini maupun Groq), sehingga tidak bisa dihindari oleh pengguna.

## 2. Aturan Pemrograman (Code Rules)
- **PHP Murni (Native):** Sistem dibangun seutuhnya menggunakan PHP asli (Vanilla PHP) tanpa menggunakan framework pihak ketiga seperti Laravel atau CodeIgniter.
- **Paradigma OOP:** Menggunakan paradigma Object-Oriented Programming (Pemrograman Berorientasi Objek) untuk struktur logika.
- **Design Pattern (Strategy Pattern):** Mewajibkan penggunaan antar-muka / *Interface* (`AiProviderInterface`). Hal ini memastikan sistem bersifat dinamis dan tidak *tightly-coupled* (bergantung langsung) pada satu penyedia AI saja. Penggantian layanan AI dari Gemini ke Groq (atau model AI lain nantinya) dapat dilakukan tanpa mengubah fungsi eksekusi utama pada class `CaptionGenerator`.

## 3. Aturan Desain Antarmuka (UI Rules)
- **Tailwind CSS:** Pengaturan gaya visual antarmuka harus sepenuhnya menggunakan Tailwind CSS via CDN.
- **Inline Minimalis:** Tidak ada file CSS eksternal. Seluruh kustomisasi (seperti gradien latar belakang dan efek *glassmorphism*) diletakkan di dalam tag `<style>` secara minimalis di halaman `index.php`.
- **User Experience (UX):** Halaman memiliki transisi halus, indikator keberhasilan (badge sukses) serta penanganan notifikasi error yang bersih.

## 4. Aturan Keamanan (Security Rules)
- **Error Handling Terisolasi:** Kegagalan saat memanggil API eksternal ditangkap menggunakan struktur `try-catch`. Pengguna hanya akan menerima pesan error ramah, tanpa membocorkan kerentanan logika program.
- **Server-Side API Call:** API Key untuk layanan AI sepenuhnya dijaga dan dieksekusi di sisi *backend* (PHP cURL). JavaScript di sisi *client* tidak memiliki wewenang untuk melihat maupun mengeksekusi panggilan AI ini.
