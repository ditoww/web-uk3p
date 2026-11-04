# Real-Time Chat Implementation Guide

Panduan implementasi real-time chat untuk semua halaman chat menggunakan Firebase.

## 🎯 **Halaman yang Sudah Diimplementasikan**

### ✅ **1. Developer Chat (`/dev/chatdev`)**
- **File**: `resources/views/Chatdev/chatdev.blade.php`
- **Controller**: `ChatDevController@storeRealtime`
- **Route**: `/dev/chatdev/store-realtime`
- **Sender Type**: `dev`

### ✅ **2. Support Chat (`/Support/chat`)**
- **File**: `resources/views/Chatsup/chatsup.blade.php`
- **Controller**: `SupportChatController@storeRealtime`
- **Route**: `/Support/chat/store-realtime`
- **Sender Type**: `support`

### ✅ **3. Support Client Chat (`/support/clientchat`)**
- **File**: `resources/views/Chatsup/supportclientchat.blade.php`
- **Controller**: `SupportClientChatController@storeRealtime`
- **Route**: `/support/clientchat/store-realtime`
- **Sender Type**: `support`

## 🔧 **Komponen yang Diimplementasikan**

### **1. Firebase Configuration**
```html
<!-- Firebase Configuration -->
<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-app.js";
  import { getFirestore, collection, addDoc, onSnapshot, query, orderBy, serverTimestamp } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-firestore.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-analytics.js";

  const firebaseConfig = {
    apiKey: "AIzaSyCaBfevesjOCPT3WbTgFQq-_GOXFng55qw",
    authDomain: "real-time-chat-b1212.firebaseapp.com",
    projectId: "real-time-chat-b1212",
    storageBucket: "real-time-chat-b1212.firebasestorage.app",
    messagingSenderId: "47924502108",
    appId: "1:47924502108:web:68e83ab67e2ce6285fd099",
    measurementId: "G-P9PR6MQYEP"
  };

  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);
  const db = getFirestore(app);

  window.firebaseApp = app;
  window.firebaseDb = db;
  window.firebaseConfig = firebaseConfig;
</script>
```

### **2. User Data Setup**
```html
<script>
  window.userData = {
    id: '{{ Auth::id() }}',
    name: '{{ Auth::user()->name }}',
    avatar: '{{ Auth::user()->profile_picture ?? "" }}'
  };
  window.ticketId = '{{ $selectedTicket->id }}';
</script>
```

### **3. Script Loading Order**
```html
<script src="{{ asset('js/realtime-chat-min.js') }}" defer></script>
<script src="{{ asset('js/chatdev-min.js') }}" defer></script>
```

### **4. CSRF Token**
```html
<meta name="csrf-token" content="{{ csrf_token() }}" />
```

### **5. Form Buttons**
```html
<button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('attachment').click();">
  <i class="bi bi-paperclip"></i> File
</button>
<button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('image').click();">
  <i class="bi bi-image"></i> Image
</button>
<button type="button" id="toggleChatHistory" class="btn btn-sm btn-outline-secondary">
  <i class="bi bi-list"></i> Chat History
</button>
```

### **6. Drag & Drop CSS**
```css
.chat-body.dragover {
  background-color: #ecf1ff;
  border: 2px dashed #0d6efd;
  position: relative;
}
.chat-body.dragover::after {
  content: 'Drop files here to upload';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(13, 110, 253, 0.9);
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  z-index: 1000;
}
```

### **7. Form ID**
```html
<form class="behindable" id="chat-form" action="{{ route('...') }}" method="POST" enctype="multipart/form-data">
```

## 🗄️ **Database Schema**

### **Developer Chat**
- **Table**: `devChats`
- **Fields**: `ticket_id`, `sender`, `response`, `created_at`, `updated_at`
- **Attachments**: `devAttachments`

### **Support Chat**
- **Table**: `devChats`
- **Fields**: `ticket_id`, `sender`, `response`, `created_at`, `updated_at`
- **Attachments**: `devAttachments`

### **Support Client Chat**
- **Table**: `clientChats`
- **Fields**: `ticketID`, `type`, `response`, `check`, `created_at`, `updated_at`
- **Attachments**: `clientAttachments`

## 🔄 **Controller Methods**

### **ChatDevController@storeRealtime**
```php
public function storeRealtime(Request $request)
{
    $userId = Auth::id();
    $ticketId = $request->get('ticket');
    $message = $request->input('message');
    $attachments = $request->input('attachments', []);

    // Store in devChats table
    $chatId = DB::table('devChats')->insertGetId([
        'ticket_id' => $ticketId,
        'sender' => 'dev',
        'response' => $message,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Handle attachments
    foreach ($attachments as $attachment) {
        DB::table('devAttachments')->insert([
            'chatID' => $chatId,
            'fileName' => $attachment['name'],
            'filePath' => $attachment['url'],
            'fileExtension' => $attachment['type'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return response()->json(['success' => true, 'chat_id' => $chatId]);
}
```

### **SupportChatController@storeRealtime**
```php
public function storeRealtime(Request $request)
{
    // Similar to ChatDevController but with sender = 'support'
    $chatId = DB::table('devChats')->insertGetId([
        'ticket_id' => $ticketId,
        'sender' => 'support',
        'response' => $message,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
```

### **SupportClientChatController@storeRealtime**
```php
public function storeRealtime(Request $request)
{
    // Uses clientChats table with type = 'support'
    $chatId = DB::table('clientChats')->insertGetId([
        'ticketID' => $ticketId,
        'type' => 'support',
        'response' => $message,
        'check' => 'sent',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
```

## 🛣️ **Routes**

```php
// Developer Chat
Route::post('/dev/chatdev/store-realtime', [ChatDevController::class, 'storeRealtime'])->name('Chatdev.storeRealtime');

// Support Chat
Route::post('/Support/chat/store-realtime', [SupportChatController::class, 'storeRealtime'])->middleware('support')->name('Chatsup.storeRealtime');

// Support Client Chat
Route::post('/support/clientchat/store-realtime', [SupportClientChatController::class, 'storeRealtime'])->middleware('support')->name('SupportClientChat.storeRealtime');
```

## 🎨 **Message Styling**

### **Developer Chat**
- **Own Messages**: `.message.dev` (blue background)
- **Other Messages**: `.message.user` (gray background)

### **Support Chat**
- **Own Messages**: `.message.support` (blue background)
- **Other Messages**: `.message.user` (gray background)

### **Support Client Chat**
- **Own Messages**: `.message.support` (blue background)
- **Other Messages**: `.message.client` (gray background)

## 🔧 **JavaScript Logic**

### **Page Detection**
```javascript
const isDevChat = location.pathname.includes('/dev/chatdev');
const isSupportChat = location.pathname.includes('/Support/chat');
const isClientChat = location.pathname.includes('/support/clientchat');
```

### **Endpoint Selection**
```javascript
let endpoint = '/dev/chatdev/store-realtime'; // default
if (location.pathname.includes('/Support/chat')) {
    endpoint = '/Support/chat/store-realtime';
} else if (location.pathname.includes('/support/clientchat')) {
    endpoint = '/support/clientchat/store-realtime';
}
```

### **Sender Type Detection**
```javascript
let senderType = 'dev'; // default
if (location.pathname.includes('/Support/chat')) {
    senderType = 'support';
} else if (location.pathname.includes('/support/clientchat')) {
    senderType = 'support';
}
```

## ✅ **Testing**

### **1. Manual Testing**
- Ketik pesan dan tekan Enter
- Upload file/gambar menggunakan tombol File/Image
- Drag & drop file ke area chat
- Cek real-time di browser lain

### **2. Real-time Verification**
- Buka chat di 2 browser berbeda
- Kirim pesan dari browser pertama
- Pesan harus muncul otomatis di browser kedua
- Tidak perlu refresh halaman

## 🐛 **Troubleshooting**

### **Common Issues**
1. **Firebase not initialized**: Cek console untuk error
2. **CSRF token missing**: Pastikan meta tag ada di head
3. **Route not found**: Cek route registration
4. **Database error**: Cek controller method
5. **Real-time not working**: Cek Firebase configuration

### **Debug Steps**
1. **Open Browser Console** (F12)
2. **Check Network Tab** untuk API calls
3. **Check Console Logs** untuk error messages
4. **Verify Firebase Connection** dengan melihat log initialization
5. **Check Database** untuk stored messages

## 🚀 **Features**

### ✅ **Implemented**
- Real-time message sending
- File upload support
- Image upload support
- Drag & drop functionality
- Auto-scroll to bottom
- Loading states
- Error handling
- Fallback to traditional submission
- Cross-page compatibility
- Mobile support

### 🔄 **Real-time Features**
- Instant message display
- No page refresh required
- Firebase Firestore integration
- Database persistence
- Attachment support
- User avatar display
- Timestamp formatting
- Message styling

---

**Real-time chat sudah diimplementasikan di semua halaman chat!** 🎉
