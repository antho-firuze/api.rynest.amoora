# API Rynest Amoora

Repositori ini berisi kode sumber untuk **API Rynest Amoora**, sebuah layanan API berspesifikasi tinggi yang dibangun di atas [Webman](https://www.workerman.net/doc/webman)—kerangka kerja PHP berkinerja tinggi berbasis [Workerman](https://www.workerman.net/). Proyek ini dirancang untuk menangani konkurensi tinggi dan komunikasi *real-time* (seperti signaling) dengan efisiensi maksimal.

## 🚀 Fitur Utama

* **High Performance PHP:** Menggunakan arsitektur *non-blocking* dan *event-driven* dari Workerman/Webman.
* **Signaling Server:** Dilengkapi dengan modul signaling (arsitektur visual tersedia di file `signaling.excalidraw`).
* **Dockerized Environment:** Proses pengembangan dan repositori lokal yang konsisten menggunakan Docker dan Docker Compose.

---

## 🛠️ Struktur Repositori

```text
├── docker/                  # Konfigurasi internal Docker (Dockerfile, dll.)
├── webman/                  # Source code utama aplikasi Webman (PHP)
├── docker-compose.yml       # Orkestrasi container Docker
├── signaling.excalidraw     # Skema visual arsitektur signaling server
└── README.md                # Dokumentasi ini

```

---

## 💻 Persyaratan Sistem

Sebelum memulai, pastikan perangkat Anda sudah terpasang:

* [Docker](https://www.docker.com/) & Docker Compose
* PHP $\ge$ 8.0 (jika ingin menjalankan secara lokal tanpa Docker)
* Composer (manajer dependensi PHP)

---

## ⚡ Cara Instalasi & Menjalankan Aplikasi

### 1. Kloning Repositori

```bash
git clone https://github.com/antho-firuze/api.rynest.amoora.git
cd api.rynest.amoora

```

### 2. Jalankan Menggunakan Docker

Kami menyarankan penggunaan Docker untuk mempermudah manajemen *environment*. Cukup jalankan perintah berikut:

```bash
docker-compose up -d

```

*Perintah ini akan membangun (build) container dan menjalankan server Webman di latar belakang.*

### 3. Instalasi Dependensi (Jika Diperlukan)

Masuk ke dalam container aplikasi untuk memasang library PHP via Composer:

```bash
docker-compose exec app composer install

```

*(Sesuaikan `app` dengan nama layanan/service PHP yang tertera di dalam `docker-compose.yml` Anda).*

---

## 📡 Dokumentasi Arsitektur Signaling

Untuk memahami bagaimana alur *signaling* bekerja pada API ini, Anda dapat membuka file `signaling.excalidraw` menggunakan aplikasi web [Excalidraw](https://excalidraw.com/). Cukup *drag & drop* file tersebut ke browser Anda untuk melihat diagram arsitekturnya.

---

## 📖 Pelajari Lebih Lanjut (Webman)

Karena proyek ini berbasis Webman, Anda bisa merujuk ke dokumentasi resmi berikut untuk pengembangan lebih lanjut:

* [Halaman Utama Webman](https://www.workerman.net/doc/webman)
* [Alur Instalasi & Konfigurasi](https://www.workerman.net/doc/webman/install.html)
* [Komunitas & Tanya Jawab](https://www.workerman.net/questions)

---

## 📄 Lisensi

Proyek ini bersifat *open-source* dan dilisensikan di bawah [MIT License](https://www.google.com/search?q=LICENSE).