# Firebase Environment Configuration

Berdasarkan file `storage/app/firebase/firebase-service-account.json`, tambahkan konfigurasi berikut ke file `.env` Anda:

```env
# Firebase Configuration
FIREBASE_PROJECT_ID=real-time-chat-b1212
FIREBASE_PRIVATE_KEY_ID=2fdac5454a2086aacfccd01e5fb3a588c2e46bd7
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDwPfajRxyX0vs3\nGC0C9d4Vao383XjWOHda65d9vX2mf8+CifGAEsUoth8WZpBNE4R9balZpcDsRYnf\nMgPUtJvaPbStFSoBoTt53oVFo5idHWK2ktGb+AEcjxd3uUsXnq8367XUdBiu3nZO\naVVVIuTcF7hhrl996X3mC4WAJ89yBYNXfViASebWcAcDZYnpgroieCVlcRRnrFi5\ntufKUoXus/ngHh0f69nK/ZssuuovFnLfD18X3c90kgHzVRzHmRvqdsjnoeK7A8by\nRxF+ROgV434iouocrwutDBDLfeYN/yFpBF5sIJ1Cs14OMPy4oq9urvFpqevUZ4S3\n64dThLHVAgMBAAECggEABtErTid2wel5uW7VMCVRP6L+E8kmZFBxNUJEJ+GYTtYy\nSY5SgljbVrQRnm1woM9HTivYx14duFaiJT7iaZxqY79RK4F951KaQR4LAsEz/m6s\nCL7nyxjhfIb5moBxMg1jBB+LJv6DCMtFPiUHsoUX9hWHj4tcKFLXgLnzotb+WvTh\nA9FLN/JXXTz1qGshhFZ2Fa++lwgdJnXigafizcSRFuZh2IKzO7CKHwjzJ58sfRyk\n1DLFsU3ZzEdL9LamKT/h1LerpAihQfndmIlvjTKTtZ77li7oR9S5RAZRXdE3C6cT\nOHp6JMxmkORwlf4uAXJW7kqvlxC9o3fj0dO+5I2+4QKBgQD87+sxSjA6YaEvi4xo\n95JFMY1eBFyT8X36wvKu9anFFWgN1NHMSjiu0dOwq+3w3ONDq7pggUGFIvAez1h5\nwOnC1lV3vuwiNtNJLXcmzCpBVDT6R5kGBZqiSUzZHzqatY3Bvoh47zX1fDYmMNog\n5XacXDhFln0/I2ebIvXvwkCWqQKBgQDzJrDkXiC+Ra8aRcTylMsAUtDz37iofLzw\nxZF0TRsTbQgXVGllqM70hNUACXNvNa+upg6S7ayFIJzad3ZvvTcF2ZoWJN04VYGK\nJgqAYbGJ3eX2jHWFvOmWHhpW/qP+6sIDcy/G74L+ahK97k26Pds3dwHoG6EhB8ov\njTfvuEX5TQKBgQDhSL/u0ulJ1pM+Scjx5TA+uPf0+WWLz9gpdWG6O8zOYVagmyGS\nF7mM3A70mO4BTnyNVXNXaUj2UDwW9Q8UwqJyi3zsfvzLLJHb58bso4QvU9Ur5oGB\nP5SPX0I3UP1dtr9gBViAfm9NCykfsvtVuYt/kRP9xyAfEGXDY8wxlI76oQKBgBG8\n99ihGUhoMv76qgR8YFbUs9OmtYTtHKd/8j2GxnvjT1zMVqgI6Zgi/Jr1FYu+YeOe\nz59eYWCQ4YB6iJRcPGBb4IbPB6htbHbUkZ2OAvKo0nbrBkPMZUoDRi6kgvg8zgyM\nYabkoCzJNjbO5LK6gnyIXQY+xcFwHQrdBYCic9BdAoGBAL9qWiEp7EqozLsnj6cd\ncloLXcnAbSYLRR3B1bYMFk0/KLUybJXG/n8Bqc2CNs4jbslLEPT342+Mz9BE/Syh\nXCx5UTZytbeMvZ+DP2EMLbvhrn5Kpx4h502pl6coRhVrtfau4dGGR1p4cc5NQfGJ\ntmTyak1JQ4WQRmsvrh/D4V5y\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=firebase-adminsdk-fbsvc@real-time-chat-b1212.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=110430385217315945430
FIREBASE_AUTH_URI=https://accounts.google.com/o/oauth2/auth
FIREBASE_TOKEN_URI=https://oauth2.googleapis.com/token
FIREBASE_AUTH_PROVIDER_X509_CERT_URL=https://www.googleapis.com/oauth2/v1/certs
FIREBASE_CLIENT_X509_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40real-time-chat-b1212.iam.gserviceaccount.com
FIREBASE_UNIVERSE_DOMAIN=googleapis.com
```

## Cara Menambahkan ke .env

1. Buka file `.env` di root project Laravel Anda
2. Tambahkan konfigurasi di atas di bagian bawah file
3. Simpan file

## Catatan Penting

- **Private Key**: Pastikan private key tetap dalam tanda kutip ganda dan tidak ada spasi tambahan
- **Keamanan**: Jangan pernah commit file `.env` ke repository git
- **Production**: Gunakan environment variables yang berbeda untuk production

## Verifikasi Konfigurasi

Setelah menambahkan konfigurasi, Anda dapat memverifikasi dengan:

1. Restart server Laravel Anda
2. Akses halaman chat (`/dev/chatdev`)
3. Klik tombol "Test Firebase"
4. Periksa console browser untuk pesan sukses

## Troubleshooting

Jika ada masalah:

1. **Private Key Error**: Pastikan private key dalam format yang benar dengan `\n` untuk line breaks
2. **Permission Error**: Pastikan service account memiliki akses ke Firestore
3. **Project ID Mismatch**: Pastikan project ID sesuai dengan Firebase project Anda
