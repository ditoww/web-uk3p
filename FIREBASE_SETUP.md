# Firebase Setup Guide for Real-Time Chat

This guide will help you set up Firebase for the real-time chat functionality in TickDesk.

## Prerequisites

- Google account
- Firebase project
- Laravel application with proper authentication

## Step 1: Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click "Create a project" or "Add project"
3. Enter project name: `real-time-chat-b1212` (or your preferred name)
4. Enable Google Analytics (optional)
5. Click "Create project"

## Step 2: Enable Firestore Database

1. In Firebase Console, go to "Firestore Database"
2. Click "Create database"
3. Choose "Start in test mode" (for development)
4. Select a location close to your users
5. Click "Done"

## Step 3: Configure Firestore Security Rules

1. In Firestore Database, go to "Rules" tab
2. Replace the default rules with:

```javascript
    rules_version = '2';
    service cloud.firestore {
    match /databases/{database}/documents {
        // Allow read/write access to tickets collection
        match /tickets/{ticketId} {
        allow read, write: if true; // For development - restrict in production
        
        // Allow access to messages subcollection
        match /messages/{messageId} {
            allow read, write: if true; // For development - restrict in production
        }
        }
    }
    }
```

**Note**: These rules allow full access for development. For production, implement proper authentication and authorization.

## Step 4: Get Web App Configuration

1. In Firebase Console, go to "Project settings" (gear icon)
2. Scroll down to "Your apps" section
3. Click "Add app" and select web icon (</>)
4. Register app with a nickname (e.g., "TickDesk Web")
5. Copy the configuration object:

```javascript
const firebaseConfig = {
  apiKey: "your-api-key",
  authDomain: "your-project.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project.appspot.com",
  messagingSenderId: "your-sender-id",
  appId: "your-app-id",
  measurementId: "your-measurement-id"
};
```

## Step 5: Create Service Account

1. In Firebase Console, go to "Project settings"
2. Go to "Service accounts" tab
3. Click "Generate new private key"
4. Download the JSON file
5. Save it as `storage/app/firebase/firebase-service-account.json`

## Step 6: Update Environment Variables

Add these to your `.env` file:

```env
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_PRIVATE_KEY_ID=your-private-key-id
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nYour private key here\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=firebase-adminsdk-xxxxx@your-project.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=your-client-id
FIREBASE_AUTH_URI=https://accounts.google.com/o/oauth2/auth
FIREBASE_TOKEN_URI=https://oauth2.googleapis.com/token
FIREBASE_AUTH_PROVIDER_X509_CERT_URL=https://www.googleapis.com/oauth2/v1/certs
FIREBASE_CLIENT_X509_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-xxxxx%40your-project.iam.gserviceaccount.com
```

## Step 7: Update Firebase Configuration

Update the Firebase configuration in `resources/views/Chatdev/chatdev.blade.php`:

```javascript
const firebaseConfig = {
  apiKey: "your-actual-api-key",
  authDomain: "your-actual-project.firebaseapp.com",
  projectId: "your-actual-project-id",
  storageBucket: "your-actual-project.appspot.com",
  messagingSenderId: "your-actual-sender-id",
  appId: "your-actual-app-id",
  measurementId: "your-actual-measurement-id"
};
```

## Step 8: Test the Setup

1. Start your Laravel application
2. Navigate to the chat page (`/dev/chatdev`)
3. Open browser console
4. Click "Test Firebase" button
5. Check console for success messages

## Troubleshooting

### Common Issues

1. **"Firebase not initialized" error**:
   - Check if Firebase SDK is loaded
   - Verify configuration object
   - Check browser console for errors

2. **"Permission denied" error**:
   - Check Firestore security rules
   - Verify service account permissions
   - Check if database is created

3. **"Network error"**:
   - Check internet connection
   - Verify Firebase project is active
   - Check if Firestore is enabled

### Debug Steps

1. **Check Firebase Console**:
   - Verify project is active
   - Check Firestore database exists
   - Review security rules

2. **Check Browser Console**:
   - Look for Firebase initialization logs
   - Check for JavaScript errors
   - Verify configuration loading

3. **Check Laravel Logs**:
   - Review `storage/logs/laravel.log`
   - Check for PHP errors
   - Verify service account file exists

## Production Considerations

### Security

1. **Update Firestore Rules**:
```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /tickets/{ticketId} {
      allow read, write: if request.auth != null && 
        (request.auth.uid in resource.data.participants);
      
      match /messages/{messageId} {
        allow read, write: if request.auth != null && 
          (request.auth.uid in get(/databases/$(database)/documents/tickets/$(ticketId)).data.participants);
      }
    }
  }
}
```

2. **Environment Variables**:
   - Use environment variables for all sensitive data
   - Never commit API keys to version control
   - Use different Firebase projects for development/production

3. **Authentication**:
   - Implement proper user authentication
   - Use Firebase Auth for user management
   - Validate user permissions on server-side

### Performance

1. **Indexing**:
   - Create composite indexes for queries
   - Monitor query performance
   - Use pagination for large datasets

2. **Caching**:
   - Implement client-side caching
   - Use Firebase offline persistence
   - Cache frequently accessed data

3. **Monitoring**:
   - Set up Firebase Analytics
   - Monitor database usage
   - Track error rates

## Support

For additional help:

1. **Firebase Documentation**: https://firebase.google.com/docs
2. **Firebase Console**: https://console.firebase.google.com/
3. **Firebase Support**: https://firebase.google.com/support
4. **Laravel Documentation**: https://laravel.com/docs

## Next Steps

After successful setup:

1. Test real-time messaging
2. Implement user authentication
3. Add file upload functionality
4. Set up monitoring and analytics
5. Deploy to production environment
