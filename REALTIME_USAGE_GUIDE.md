# Panduan Penggunaan Real-Time Chat

Panduan singkat untuk menggunakan fitur real-time chat untuk pengiriman pesan biasa.

## 🚀 **Cara Mengirim Pesan Real-Time**

### **1. Text Message (Pesan Teks)**
```
1. Ketik pesan di textarea
2. Tekan Enter atau klik tombol Send
3. Pesan langsung muncul secara real-time
```

### **2. Upload Gambar**
```
1. Klik tombol "Image" atau drag & drop gambar
2. Pilih file gambar (PNG, JPG, JPEG, WebP)
3. Preview gambar akan muncul
4. Klik "Send"
5. Gambar langsung muncul secara real-time
```

### **3. Upload File**
```
1. Klik tombol "File" atau drag & drop file
2. Pilih file (DOCX, PDF)
3. Preview file akan muncul dengan icon
4. Klik "Send"
5. File langsung muncul secara real-time
```

### **4. Drag & Drop**
```
1. Drag file dari komputer
2. Drop ke area chat (akan ada visual feedback)
3. File otomatis ditambahkan ke preview
4. Klik "Send"
5. File langsung muncul secara real-time
```

## ✅ **Fitur Real-Time yang Sudah Aktif**

- ✅ **Text messages** - Pesan teks real-time
- ✅ **Image upload** - Upload gambar real-time
- ✅ **File upload** - Upload file real-time
- ✅ **Drag & drop** - Drop file real-time
- ✅ **Auto-scroll** - Scroll otomatis ke pesan terbaru
- ✅ **Notifications** - Notifikasi sukses/error
- ✅ **Loading state** - Indikator saat mengirim
- ✅ **Error handling** - Fallback jika real-time gagal

## 🎯 **Perbedaan dengan Tombol Test**

| Fitur | Pengiriman Biasa | Tombol Test |
|-------|------------------|-------------|
| **Text** | Ketik manual | Pesan otomatis |
| **File** | Pilih file manual | File test otomatis |
| **Gambar** | Upload gambar manual | Gambar test otomatis |
| **Real-time** | ✅ Ya | ✅ Ya |
| **Database** | ✅ Tersimpan | ✅ Tersimpan |

## 🔧 **Cara Kerja Real-Time**

1. **User mengirim pesan** → Form submission di-intercept
2. **File diproses** → Convert ke base64/blob URL
3. **Kirim ke Firebase** → Real-time via Firestore
4. **Simpan ke database** → Backup ke database lokal
5. **Display real-time** → Pesan muncul instan
6. **Clear form** → Form dibersihkan

## 🐛 **Jika Real-Time Tidak Bekerja**

### **Checklist:**
- [ ] Firebase test berhasil
- [ ] Internet connection stabil
- [ ] Browser console tidak ada error
- [ ] File size tidak melebihi 10MB
- [ ] File type didukung (PNG, JPG, DOCX, PDF)

### **Fallback:**
Jika real-time gagal, sistem akan otomatis:
1. Fallback ke form submission tradisional
2. Refresh halaman untuk menampilkan pesan
3. Tetap menyimpan ke database

## 📱 **Mobile Support**

- ✅ **Touch interface** - File picker native
- ✅ **Responsive design** - Optimized untuk mobile
- ✅ **Touch-friendly** - Button yang mudah di-tap
- ❌ **Drag & drop** - Disabled pada mobile

## 🎨 **Visual Feedback**

- **Loading**: Button berubah menjadi "Sending..."
- **Success**: Notifikasi hijau "Message sent successfully!"
- **Error**: Notifikasi merah jika gagal
- **Drag**: Visual feedback saat drag file

## 🚀 **Tips Penggunaan**

1. **Untuk text cepat**: Tekan Enter
2. **Untuk new line**: Shift + Enter
3. **Untuk multiple files**: Drag beberapa file sekaligus
4. **Untuk preview**: Lihat preview sebelum kirim
5. **Untuk cancel**: Klik X pada preview file

## ✅ **Verifikasi Real-Time**

Untuk memastikan real-time bekerja:
1. Buka chat di 2 browser berbeda
2. Kirim pesan dari browser pertama
3. Pesan harus muncul otomatis di browser kedua
4. Tidak perlu refresh halaman

---

**Real-time chat sudah aktif untuk semua pengiriman pesan biasa!** 🎉
