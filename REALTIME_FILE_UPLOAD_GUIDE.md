# Real-Time File Upload Guide

Panduan lengkap untuk fitur upload file, gambar, dan text secara real-time di TickDesk Chat.

## 🚀 Fitur yang Tersedia

### 1. **Text Messages**
- Kirim pesan teks biasa
- Auto-resize textarea
- Enter untuk kirim, Shift+Enter untuk new line
- Link detection otomatis

### 2. **Image Upload**
- **Format**: PNG, JPG, JPEG, WebP
- **Ukuran maksimal**: 10MB per file
- **Preview**: Thumbnail dengan nama file
- **Drag & Drop**: Drop gambar langsung ke chat area

### 3. **File Upload**
- **Format**: DOCX, PDF
- **Ukuran maksimal**: 10MB per file
- **Icon**: Otomatis sesuai tipe file
- **Download**: Klik untuk download

### 4. **Drag & Drop**
- Drop file langsung ke area chat
- Visual feedback saat drag
- Validasi tipe file otomatis
- Maksimal 4 file sekaligus

## 📋 Cara Menggunakan

### Mengirim Pesan Text
1. Ketik pesan di textarea
2. Tekan **Enter** atau klik tombol **Send**
3. Pesan akan muncul secara real-time

### Upload Gambar
1. Klik tombol **Image** atau drag & drop
2. Pilih file gambar (PNG, JPG, JPEG, WebP)
3. Preview akan muncul di bawah textarea
4. Klik **Send** untuk kirim

### Upload File
1. Klik tombol **File** atau drag & drop
2. Pilih file (DOCX, PDF)
3. Preview akan muncul dengan icon file
4. Klik **Send** untuk kirim

### Drag & Drop
1. Drag file dari komputer
2. Drop ke area chat (akan ada visual feedback)
3. File akan otomatis ditambahkan ke preview
4. Klik **Send** untuk kirim

## 🧪 Testing

### Test Firebase
- Klik tombol **"Test Firebase"**
- Verifikasi koneksi Firebase
- Test pengiriman pesan text

### Test File Upload
- Klik tombol **"Test File Upload"**
- Akan mengirim test file dan gambar
- Verifikasi attachment display

## 🔧 Konfigurasi

### File Limits
```javascript
const maxSize = 10 * 1024 * 1024; // 10MB
const maxFiles = 4; // Maksimal 4 file
```

### Supported Formats
```javascript
const allowedImages = ['png', 'jpg', 'jpeg', 'webp'];
const allowedAtts = ['docx', 'pdf'];
```

### File Icons
- **Image**: `bi-file-earmark-image`
- **PDF**: `bi-file-earmark-pdf`
- **Word**: `bi-file-earmark-word`
- **Excel**: `bi-file-earmark-excel`
- **PowerPoint**: `bi-file-earmark-ppt`
- **Archive**: `bi-file-earmark-zip`
- **Text**: `bi-file-earmark-text`
- **Default**: `bi-file-earmark-arrow-down`

## 🎨 UI Features

### Visual Feedback
- **Loading state**: Button berubah saat mengirim
- **Progress indicator**: "Sending..." text
- **Success notification**: Alert hijau saat berhasil
- **Error notification**: Alert merah saat gagal
- **Drag overlay**: Visual feedback saat drag file

### File Display
- **Images**: Thumbnail dengan nama file
- **Files**: Icon + nama + ukuran file
- **Responsive**: Max width untuk mobile
- **Download**: Klik untuk download file

## 🔒 Security Features

### File Validation
- **Type checking**: Validasi ekstensi file
- **Size limit**: Maksimal 10MB per file
- **Count limit**: Maksimal 4 file sekaligus
- **XSS protection**: HTML escaping

### Error Handling
- **File read errors**: Continue dengan file lain
- **Upload failures**: Fallback ke form submission
- **Network errors**: Retry mechanism
- **Invalid files**: User notification

## 📱 Mobile Support

### Touch Interface
- **File picker**: Native file selection
- **Drag & drop**: Disabled on mobile
- **Responsive design**: Optimized for small screens
- **Touch-friendly**: Large touch targets

### Performance
- **Image compression**: Automatic resizing
- **Lazy loading**: Load images on demand
- **Caching**: Browser cache optimization
- **Progressive loading**: Show preview first

## 🐛 Troubleshooting

### Common Issues

1. **File tidak terupload**
   - Check file size (max 10MB)
   - Check file type (PNG, JPG, DOCX, PDF)
   - Check browser console for errors

2. **Drag & drop tidak bekerja**
   - Pastikan browser support drag & drop
   - Check file permissions
   - Disable pada mobile devices

3. **Preview tidak muncul**
   - Check file format
   - Check browser console
   - Refresh halaman

4. **Real-time tidak bekerja**
   - Check Firebase connection
   - Check internet connection
   - Verify service account

### Debug Steps

1. **Open Browser Console**
   - Press F12
   - Check for JavaScript errors
   - Look for Firebase logs

2. **Test Individual Features**
   - Test text message first
   - Test single file upload
   - Test multiple files

3. **Check Network Tab**
   - Verify API calls
   - Check file upload requests
   - Monitor Firebase requests

## 🚀 Performance Tips

### Optimization
- **Compress images**: Use WebP format
- **Limit file count**: Max 4 files per message
- **Use appropriate sizes**: Don't upload unnecessarily large files
- **Clear cache**: Remove old previews

### Best Practices
- **Use descriptive names**: Clear file names
- **Organize files**: Group related files
- **Test before sending**: Verify file integrity
- **Monitor usage**: Check file storage

## 📈 Future Enhancements

### Planned Features
- **File compression**: Automatic image compression
- **Cloud storage**: Firebase Storage integration
- **File sharing**: Direct file links
- **Preview generation**: Thumbnail for documents
- **Version control**: File versioning
- **Search**: File search functionality

### Advanced Features
- **Video upload**: Support for video files
- **Audio messages**: Voice message support
- **Screen sharing**: Screenshot sharing
- **File editing**: In-browser file editing
- **Collaboration**: Real-time file collaboration

## 📞 Support

### Getting Help
1. **Check documentation**: Read this guide
2. **Test features**: Use test buttons
3. **Check console**: Look for error messages
4. **Contact support**: Reach out to development team

### Reporting Issues
- **Describe the problem**: What happened?
- **Include steps**: How to reproduce?
- **Add screenshots**: Visual evidence
- **Check logs**: Console and network logs
